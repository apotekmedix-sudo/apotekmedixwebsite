@extends('layouts.admin')

@section('title', 'Laporan Stok - Admin Apotek Medix')
@section('page-title', 'Laporan Stok')

@section('content')
<style>
    .inventory-intro{margin:-.7rem 0 1rem;color:#64748b;line-height:1.6}
    .inventory-notice{margin-bottom:1rem;padding:12px 14px;border:1px solid #bfdbfe;border-radius:10px;background:#eff6ff;color:#1e40af;font-size:.85rem;line-height:1.55}
    .inventory-search{display:flex;gap:.6rem;margin:1rem 0}
    .inventory-search input{flex:1;min-width:0;padding:.65rem .8rem;border:1px solid #cbd5e1;border-radius:8px}
    .inventory-search button{padding:.65rem 1rem;border:0;border-radius:8px;background:#0052cc;color:#fff;font-weight:700}
    .inventory-table-wrap{overflow-x:auto;margin-bottom:1.25rem}
    .inventory-table{width:100%;min-width:850px;border-collapse:collapse;background:#fff}
    .inventory-table th,.inventory-table td{padding:.7rem .8rem;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:top}
    .inventory-table th{background:#f8fafc;color:#334155;font-size:.76rem}
    .inventory-table td{color:#475569;font-size:.82rem}
    .inventory-table strong{color:#0f172a}
    .inventory-muted{display:block;margin-top:.25rem;color:#64748b;font-size:.73rem}
    .inventory-quantity{white-space:nowrap;font-weight:700}
    .inventory-negative{color:#b91c1c!important}
    .inventory-positive{color:#047857!important}
    .inventory-section-title{margin:1.5rem 0 .75rem;color:#0f172a;font-size:1.05rem}
</style>

<p class="inventory-intro">Semua jumlah ditampilkan dalam satuan utama produk. Stok fisik adalah jumlah stok saat ini, dipesan adalah stok yang ditahan untuk pesanan belum selesai, dan stok tersedia adalah stok fisik dikurangi stok yang dipesan.</p>
<form class="inventory-search" method="GET" action="{{ route('admin.inventory.index') }}">
    <input name="search" value="{{ $search }}" placeholder="Cari nama produk">
    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
</form>

<h2 class="inventory-section-title">Saldo stok saat ini</h2>
<div class="inventory-table-wrap">
    <table class="inventory-table">
        <thead><tr><th>Produk</th><th>Satuan</th><th>Stok fisik</th><th>Dipesan</th><th>Stok tersedia</th></tr></thead>
        <tbody>
        @forelse($products as $product)
            @php
                $physical = (int) $product->physical_stock_units;
                $reserved = (int) $product->reserved_stock_units;
                $available = (int) $product->available_stock_units;
                $formatMainUnitQuantity = function (int $units) use ($product): string {
                    $quantity = $units / max(1, $product->units_per_box);
                    $formatted = rtrim(rtrim(number_format($quantity, 6, ',', '.'), '0'), ',');

                    return $formatted . ' ' . $product->package_unit_label;
                };
            @endphp
            <tr>
                <td><strong>{{ $product->nama_obat }}</strong><span class="inventory-muted">ID {{ $product->id }}</span></td>
                <td>{{ $product->package_unit_label }}</td>
                <td><span class="inventory-quantity">{{ $formatMainUnitQuantity($physical) }}</span></td>
                <td><span class="inventory-quantity">{{ $formatMainUnitQuantity($reserved) }}</span></td>
                <td><span class="inventory-quantity">{{ $formatMainUnitQuantity($available) }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5">Tidak ada produk yang ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $products->links() }}

<h2 class="inventory-section-title">Riwayat mutasi stok</h2>
<div class="inventory-table-wrap">
    <table class="inventory-table">
        <thead><tr><th>Waktu</th><th>Produk</th><th>Jenis mutasi</th><th>Perubahan stok fisik</th><th>Perubahan stok dipesan</th><th>Stok fisik sebelum → sesudah</th><th>Referensi / catatan</th><th>Oleh</th></tr></thead>
        <tbody>
        @forelse($movements as $movement)
            @php
                $unitProduct = $movement->medicine;
                $typeLabels = [
                    'opening' => 'Saldo awal',
                    'adjustment' => 'Penyesuaian',
                    'reserve' => 'Reservasi',
                    'release' => 'Lepas reservasi',
                    'sale' => 'Penjualan',
                    'return' => 'Pengembalian',
                ];
                $formatMovementQuantity = function ($units) use ($unitProduct): string {
                    if (!$unitProduct) {
                        return number_format((int) $units, 0, ',', '.') . ' unit';
                    }

                    $quantity = (int) $units / max(1, $unitProduct->units_per_box);
                    $formatted = rtrim(rtrim(number_format($quantity, 6, ',', '.'), '0'), ',');

                    return $formatted . ' ' . $unitProduct->package_unit_label;
                };
            @endphp
            <tr>
                <td>{{ $movement->created_at?->format('d M Y H:i') }}</td>
                <td><strong>{{ $unitProduct?->nama_obat ?? 'Produk dihapus' }}</strong></td>
                <td>{{ $typeLabels[$movement->movement_type] ?? ucfirst($movement->movement_type) }}</td>
                <td class="inventory-quantity {{ $movement->physical_delta < 0 ? 'inventory-negative' : ($movement->physical_delta > 0 ? 'inventory-positive' : '') }}">
                    {{ $movement->physical_delta > 0 ? '+' : '' }}{{ $formatMovementQuantity($movement->physical_delta) }}
                </td>
                <td class="inventory-quantity {{ $movement->reserved_delta < 0 ? 'inventory-positive' : ($movement->reserved_delta > 0 ? 'inventory-negative' : '') }}">
                    {{ $movement->reserved_delta > 0 ? '+' : '' }}{{ $formatMovementQuantity($movement->reserved_delta) }}
                </td>
                <td>{{ $formatMovementQuantity($movement->physical_before) }}<span class="inventory-muted">→ {{ $formatMovementQuantity($movement->physical_after) }}</span></td>
                <td>{{ $movement->reference_id ? 'Pesanan #' . $movement->reference_id : '-' }}<span class="inventory-muted">{{ $movement->note }}</span></td>
                <td>{{ $movement->user?->name ?? 'Sistem' }}</td>
            </tr>
        @empty
            <tr><td colspan="8">Belum ada riwayat mutasi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $movements->links() }}
@endsection
