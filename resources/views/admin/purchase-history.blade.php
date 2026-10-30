@extends('layouts.admin')

@section('title', 'Riwayat Pembelian - Admin Apotek Medix')
@section('page-title', '🧾 Riwayat Pembelian')

@section('content')

<nav class="transaction-tabs" aria-label="Jenis riwayat transaksi">
    <a href="{{ route('admin.purchase-history.index', ['tab' => 'sales']) }}" class="{{ $tab === 'sales' ? 'active' : '' }}"><i class="fa-solid fa-receipt"></i> Riwayat Penjualan</a>
    <a href="{{ route('admin.purchase-history.index', ['tab' => 'purchases']) }}" class="{{ $tab === 'purchases' ? 'active' : '' }}"><i class="fa-solid fa-truck-ramp-box"></i> Riwayat Pembelian</a>
</nav>
@if($tab === 'purchases')
@if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<ul class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card" style="border-top-color:#8b5cf6;">
        <div class="stat-label">Total Nilai Pesanan Supplier</div>
        <div class="stat-value" style="color:#8b5cf6;">{{ 'Rp ' . number_format($total_pembelian, 0, ',', '.') }}</div>
    </div>
</div>
<div class="card">
    <div class="transaction-toolbar">
        <div class="transaction-heading">Semua Riwayat Pembelian</div>
        <div class="transaction-toolbar-actions">
            <a class="btn btn-primary btn-sm" href="{{ route('admin.purchase-history.export', array_merge(request()->query(), ['tab' => 'purchases'])) }}"><i class="fa-solid fa-download"></i> Download Excel</a>
            <a class="btn btn-primary btn-sm" href="{{ route('admin.supplier-procurement.index') }}"><i class="fa-solid fa-plus"></i> Buat Pesanan</a>
        </div>
    </div>
    <form method="GET" action="{{ route('admin.purchase-history.index') }}" class="purchase-history-filters">
        <input type="hidden" name="tab" value="purchases">
        <label>Tanggal
            <input type="date" name="purchase_date" value="{{ request('purchase_date') }}">
        </label>
        <label>Nomor SP
            <input type="search" name="order_number" value="{{ request('order_number') }}" placeholder="Cari nomor SP">
        </label>
        <label>Supplier / PBF
            <input type="search" name="supplier" list="purchaseSupplierNames" value="{{ request('supplier') }}" placeholder="Cari nama supplier">
            <datalist id="purchaseSupplierNames">
                @foreach($supplierNames as $supplierName)<option value="{{ $supplierName }}">@endforeach
            </datalist>
        </label>
        <label>Jumlah produk
            <input type="number" name="product_count" min="0" value="{{ request('product_count') }}" placeholder="Semua">
        </label>
        <label>Total modal minimal
            <input type="number" name="total_min" min="0" step="1" value="{{ request('total_min') }}" placeholder="Rp minimal">
        </label>
        <label>Total modal maksimal
            <input type="number" name="total_max" min="0" step="1" value="{{ request('total_max') }}" placeholder="Rp maksimal">
        </label>
        <label>Status
            <select name="purchase_status">
                <option value="">Semua status</option>
                <option value="ordered" @selected(request('purchase_status') === 'ordered')>Dipesan</option>
                <option value="partially_received" @selected(request('purchase_status') === 'partially_received')>Diterima sebagian</option>
                <option value="received" @selected(request('purchase_status') === 'received')>Diterima lengkap</option>
                <option value="returned" @selected(request('purchase_status') === 'returned')>Dikembalikan</option>
                <option value="cancelled" @selected(request('purchase_status') === 'cancelled')>Dibatalkan</option>
            </select>
        </label>
        <label>Urutkan tanggal
            <select name="purchase_sort">
                <option value="newest" @selected(request('purchase_sort', 'newest') === 'newest')>Terbaru ke lama</option>
                <option value="oldest" @selected(request('purchase_sort') === 'oldest')>Terlama ke baru</option>
            </select>
        </label>
        <div class="purchase-history-filter-actions">
            <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari / Filter</button>
            <a class="btn btn-secondary btn-sm" href="{{ route('admin.purchase-history.index', ['tab' => 'purchases']) }}">Reset</a>
        </div>
    </form>
    <div class="table-container transaction-table-container">
        <table class="table transaction-table">
            <thead><tr><th>Tanggal</th><th>No. SP</th><th>Supplier / PBF</th><th>Produk</th><th>Total Modal</th><th>Status</th><th>Dokumen</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($supplierOrders as $row)
                @php
                    $purchase = $row['model'];
                    $isSupplierOrder = $row['source'] === 'supplier';
                    $purchaseStatusLabels = ['ordered' => 'Dipesan', 'partially_received' => 'Diterima sebagian', 'received' => 'Diterima lengkap', 'cancelled' => 'Dibatalkan', 'returned' => 'Dikembalikan'];
                    $purchaseStatusColors = ['ordered' => '#92400e', 'partially_received' => '#92400e', 'received' => '#166534', 'cancelled' => '#991b1b', 'returned' => '#991b1b'];
                    $purchaseStatusBgs = ['ordered' => '#fef3c7', 'partially_received' => '#fffbeb', 'received' => '#dcfce7', 'cancelled' => '#fee2e2', 'returned' => '#fee2e2'];
                @endphp
                <tr>
                    <td>{{ $row['date']?->translatedFormat('d M Y') }}</td>
                    <td><strong>{{ $row['order_number'] }}</strong></td>
                    <td>{{ $row['supplier'] }}</td>
                    <td>{{ $row['product_count'] }} produk</td>
                    <td style="font-weight:700;">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                    <td><span style="display:inline-block;padding:.25rem .65rem;border-radius:999px;font-size:.75rem;font-weight:700;color:{{ $purchaseStatusColors[$row['status']] ?? '#334155' }};background:{{ $purchaseStatusBgs[$row['status']] ?? '#f1f5f9' }}">{{ $purchaseStatusLabels[$row['status']] ?? $row['status'] }}</span></td>
                    <td>
                        @if($isSupplierOrder)
                            <a class="btn btn-secondary btn-sm" href="{{ route('admin.supplier-procurement.orders.purchase-order', $purchase) }}" target="_blank" rel="noopener"><i class="fa-solid fa-file-lines"></i> Lihat SP</a>
                        @else
                            <a class="btn btn-secondary btn-sm" href="{{ route('admin.purchase-history.purchase-order', $purchase) }}" target="_blank" rel="noopener"><i class="fa-solid fa-file-lines"></i> Lihat SP</a>
                        @endif
                    </td>
                    <td style="min-width:175px;">
                        @if($isSupplierOrder && in_array($row['status'], ['ordered', 'partially_received'], true))
                            <form method="POST" action="{{ route('admin.supplier-procurement.orders.receive', $purchase) }}" onsubmit="return confirm('Selesaikan pesanan ini dan tambahkan seluruh sisa barang ke stok?');" style="margin-bottom:.4rem;">
                                @csrf
                                <input type="hidden" name="from_purchase_history" value="1">
                                @foreach($purchase->items as $item)
                                    @php
                                        $remainingQuantity = max(0, $item->quantity - $item->received_quantity);
                                    @endphp
                                    <input type="hidden" name="received_now[{{ $item->id }}]" value="{{ $remainingQuantity }}">
                                @endforeach
                                <button class="btn btn-success btn-sm" type="submit"><i class="fa-solid fa-check"></i> Selesai</button>
                            </form>
                            <details>
                                <summary class="btn btn-secondary btn-sm" style="cursor:pointer;list-style:none;"><i class="fa-solid fa-list-ol"></i> Tidak sesuai jumlah</summary>
                                <form method="POST" action="{{ route('admin.supplier-procurement.orders.receive', $purchase) }}" style="margin-top:.65rem;min-width:300px;">
                                    @csrf
                                    <input type="hidden" name="from_purchase_history" value="1">
                                    <p style="margin:.25rem 0 .5rem;font-size:.78rem;color:#64748b;">Isi jumlah yang benar-benar diterima untuk setiap produk. Stok bertambah sesuai angka ini.</p>
                                    <div style="display:grid;gap:.55rem;">
                                        @foreach($purchase->items as $item)
                                            @php
                                                $remainingQuantity = max(0, $item->quantity - $item->received_quantity);
                                            @endphp
                                            @if($remainingQuantity > 0)
                                                <label style="display:grid;gap:.25rem;font-size:.78rem;font-weight:700;">
                                                    {{ $item->product_name }}<small style="font-weight:400;color:#64748b;">Pesan {{ $item->quantity }} · Sudah diterima {{ $item->received_quantity }} · Sisa {{ $remainingQuantity }} {{ $item->unit_label }}</small>
                                                    <input type="number" name="received_now[{{ $item->id }}]" min="0" max="{{ $remainingQuantity }}" value="0" required style="width:100%;padding:.4rem;border:1px solid #cbd5e1;border-radius:.4rem;">
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                    <button class="btn btn-primary btn-sm" type="submit" style="margin-top:.6rem;"><i class="fa-solid fa-boxes-stacked"></i> Simpan penerimaan</button>
                                </form>
                            </details>
                            @if($row['status'] === 'ordered')
                                <form method="POST" action="{{ route('admin.supplier-procurement.orders.return', $purchase) }}" onsubmit="return confirm('Kembalikan pesanan ini? Status akan menjadi Dikembalikan dan stok tidak akan bertambah.');" style="margin-top:.4rem;">
                                    @csrf
                                    <button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-rotate-left"></i> Kembalikan</button>
                                </form>
                            @endif
                        @elseif($isSupplierOrder && $row['status'] === 'returned')
                            <form method="POST" action="{{ route('admin.supplier-procurement.orders.destroy', $purchase) }}" onsubmit="return confirm('Hapus permanen riwayat pesanan yang dikembalikan ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i> Hapus riwayat</button>
                            </form>
                        @elseif(!$isSupplierOrder && in_array($row['status'], ['ordered', 'partially_received'], true))
                            @php
                                $legacyItems = is_array($purchase->items) ? $purchase->items : [];
                            @endphp
                            <form method="POST" action="{{ route('admin.purchase-history.receive', $purchase) }}" onsubmit="return confirm('Selesaikan pesanan ini dan tambahkan seluruh sisa barang ke stok?');" style="margin-bottom:.4rem;">
                                @csrf
                                @foreach($legacyItems as $itemIndex => $item)
                                    @php
                                        $remainingQuantity = max(0, (int) ($item['quantity'] ?? $item['qty'] ?? 0) - (int) ($item['received_quantity'] ?? 0));
                                    @endphp
                                    <input type="hidden" name="received_now[{{ $itemIndex }}]" value="{{ $remainingQuantity }}">
                                @endforeach
                                <button class="btn btn-success btn-sm" type="submit"><i class="fa-solid fa-check"></i> Selesai</button>
                            </form>
                            <details>
                                <summary class="btn btn-secondary btn-sm" style="cursor:pointer;list-style:none;"><i class="fa-solid fa-list-ol"></i> Tidak sesuai jumlah</summary>
                                <form method="POST" action="{{ route('admin.purchase-history.receive', $purchase) }}" style="margin-top:.65rem;min-width:300px;">
                                    @csrf
                                    <p style="margin:.25rem 0 .5rem;font-size:.78rem;color:#64748b;">Isi jumlah yang benar-benar diterima untuk setiap produk.</p>
                                    <div style="display:grid;gap:.55rem;">
                                        @foreach($legacyItems as $itemIndex => $item)
                                            @php
                                                $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                                                $alreadyReceived = (int) ($item['received_quantity'] ?? 0);
                                                $remainingQuantity = max(0, $quantity - $alreadyReceived);
                                            @endphp
                                            @if($remainingQuantity > 0)
                                                <label style="display:grid;gap:.25rem;font-size:.78rem;font-weight:700;">
                                                    {{ $item['nama_obat'] ?? $item['name'] ?? 'Produk' }}
                                                    <small style="font-weight:400;color:#64748b;">Pesan {{ $quantity }} · Sudah diterima {{ $alreadyReceived }} · Sisa {{ $remainingQuantity }}</small>
                                                    <input type="number" name="received_now[{{ $itemIndex }}]" min="0" max="{{ $remainingQuantity }}" value="0" required style="width:100%;padding:.4rem;border:1px solid #cbd5e1;border-radius:.4rem;">
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                    <button class="btn btn-primary btn-sm" type="submit" style="margin-top:.6rem;"><i class="fa-solid fa-boxes-stacked"></i> Simpan penerimaan</button>
                                </form>
                            </details>
                            @if($row['status'] === 'ordered')
                                <form method="POST" action="{{ route('admin.purchase-history.return', $purchase) }}" onsubmit="return confirm('Kembalikan pesanan ini? Stok tidak akan bertambah.');" style="margin-top:.4rem;">
                                    @csrf
                                    <button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-rotate-left"></i> Kembalikan</button>
                                </form>
                            @endif
                        @elseif(!$isSupplierOrder && $row['status'] === 'returned')
                            <form method="POST" action="{{ route('admin.purchase-history.destroy-returned', $purchase) }}" onsubmit="return confirm('Hapus permanen riwayat pesanan yang dikembalikan ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i> Hapus riwayat</button>
                            </form>
                        @else
                            <span style="color:#64748b;font-size:.78rem;">Tidak ada aksi</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#6b7280;padding:2rem;">Tidak ada riwayat yang sesuai dengan pencarian.</td></tr>
            @endforelse
            @if($supplierOrders->isEmpty() && $legacyPbfOrders->isEmpty())
                <tr><td colspan="8" style="text-align:center;color:#6b7280;padding:2rem;">Belum ada riwayat pembelian.</td></tr>
            @endif
            </tbody>
        </table>
    </div>
    @if($supplierOrders->total() > 0)
        <div class="transaction-pagination">
            <p class="transaction-pagination-summary">Menampilkan {{ number_format($supplierOrders->firstItem(), 0, ',', '.') }}–{{ number_format($supplierOrders->lastItem(), 0, ',', '.') }} dari {{ number_format($supplierOrders->total(), 0, ',', '.') }} pesanan</p>
            @if($supplierOrders->hasPages())
                <nav class="transaction-pagination-nav" aria-label="Navigasi halaman riwayat pembelian">
                    @if($supplierOrders->onFirstPage())
                        <span class="transaction-page-link is-disabled" aria-disabled="true">‹ Sebelumnya</span>
                    @else
                        <a class="transaction-page-link" rel="prev" href="{{ $supplierOrders->previousPageUrl() }}">‹ Sebelumnya</a>
                    @endif
                    @if($supplierOrders->hasMorePages())
                        <a class="transaction-page-link" rel="next" href="{{ $supplierOrders->nextPageUrl() }}">Berikutnya ›</a>
                    @else
                        <span class="transaction-page-link is-disabled" aria-disabled="true">Berikutnya ›</span>
                    @endif
                </nav>
            @endif
        </div>
    @endif
</div>
@else
{{-- Total Omset --}}
<div class="transaction-history-page">
<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card" style="border-top-color:#8b5cf6;">
        <div class="stat-label">Total Omzet Penjualan</div>
        <div class="stat-value" style="color:#8b5cf6;">{{ 'Rp ' . number_format($total_omzet, 0, ',', '.') }}</div>
    </div>
</div>

<nav class="transaction-tabs fulfillment-tabs" aria-label="Pemisahan transaksi berdasarkan saluran">
    <a href="{{ route('admin.purchase-history.index', ['tab' => 'sales', 'fulfillment' => 'all']) }}" class="{{ $fulfillment === 'all' ? 'active' : '' }}">Semua Transaksi</a>
    <a href="{{ route('admin.purchase-history.index', ['tab' => 'sales', 'fulfillment' => 'internal']) }}" class="{{ $fulfillment === 'internal' ? 'active' : '' }}">Internal</a>
    <a href="{{ route('admin.purchase-history.index', ['tab' => 'sales', 'fulfillment' => 'b2b']) }}" class="{{ $fulfillment === 'b2b' ? 'active' : '' }}">B2B</a>
    <a href="{{ route('admin.purchase-history.index', ['tab' => 'sales', 'fulfillment' => 'offline']) }}" class="{{ $fulfillment === 'offline' ? 'active' : '' }}"><i class="fa-solid fa-location-dot"></i> Offline</a>
    <a href="{{ route('admin.purchase-history.index', ['tab' => 'sales', 'fulfillment' => 'online']) }}" class="{{ $fulfillment === 'online' ? 'active' : '' }}"><i class="fa-solid fa-truck-fast"></i> Online</a>
</nav>

{{-- Tabel Riwayat Penjualan --}}
<div class="card">
    <div class="transaction-toolbar">
        <div class="transaction-heading">
            {{ match ($fulfillment) { 'internal' => 'Transaksi Internal', 'b2b' => 'Transaksi B2B', 'online' => 'Transaksi Online', 'offline' => 'Transaksi Offline', default => 'Semua Transaksi' } }}
        </div>
        <div class="transaction-toolbar-actions">
            <a href="{{ route('admin.purchase-history.export', array_merge(request()->query(), ['tab' => 'sales'])) }}" class="btn btn-primary btn-sm" title="Download sebagai Excel/CSV">
                <i class="fa-solid fa-download"></i> Download
            </a>
            <form id="deleteAllForm" method="POST" action="{{ route('admin.purchase-history.destroyAll') }}" onsubmit="return confirm('Yakin ingin menghapus SEMUA riwayat penjualan? Pesanan pembelian supplier tidak akan dihapus.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus Semua</button>
            </form>
        </div>
    </div>
    <div class="table-container transaction-table-container">
        <table class="table transaction-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Nama Pembeli</th>
                    <th>Jenis</th>
                    <th>Saluran</th>
                    <th>Pembayaran</th>
                    <th>Status Kasir</th>
                    <th>Total</th>
                    <th style="width: 15%;">Items</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td style="font-size:0.82rem;color:#6b7280;">{{ $order->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }} WIB</td>
                        <td>
                            <strong>{{ $order->buyer_name ?: ($order->outlet_name ?: '-') }}</strong>
                            @if($order->requester_name && $order->requester_name !== $order->buyer_name)
                                <div style="font-size:0.75rem;color:#64748b;">{{ $order->requester_name }}</div>
                            @endif
                        </td>
                        <td>
                            @if(in_array($order->buyer_type, ['pbf', 'apotik', 'dokter'], true))
                                <span class="badge badge-primary">B2B ({{ strtoupper($order->buyer_type) }})</span>
                            @else
                                <span class="badge badge-secondary">Umum (B2C)</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $order->transaction_channel === 'offline' ? 'badge-primary' : 'badge-secondary' }}">
                                {{ $order->transaction_channel === 'offline' ? 'Offline' : 'Online' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $paymentLabel = $order->payment_method;
                                if ($paymentLabel === 'Tempo' && !empty($order->payment_term_days)) {
                                    $paymentLabel .= ' (' . $order->payment_term_days . ' hari)';
                                }
                            @endphp
                            @if($order->payment_method)
                                <span style="background:#eff6ff;color:#1d4ed8;padding:0.2rem 0.55rem;border-radius:20px;font-size:0.78rem;font-weight:600;">{{ $paymentLabel }}</span>
                                @if($order->isTempoPayment())
                                    <div style="margin-top:0.3rem;font-size:0.74rem;font-weight:700;color:{{ in_array($order->tempo_due_status, ['overdue', 'due_today']) ? '#dc2626' : ($order->tempo_due_status === 'due_soon' ? '#d97706' : '#0369a1') }};">
                                        Jatuh Tempo: {{ $order->due_date_formatted }}<br>
                                        <span>({{ $order->tempo_due_label }})</span>
                                    </div>
                                @endif
                            @else
                                <span style="color:#d1d5db;font-size:0.8rem;font-style:italic;">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusLabel = 'Selesai';
                                $statusColor = '#166534';
                                $statusBg = '#dcfce7';
                            @endphp
                            <div style="display:flex;flex-direction:column;gap:0.3rem;align-items:flex-start;">
                                <span style="display:inline-block;padding:0.25rem 0.65rem;border-radius:999px;font-size:0.75rem;font-weight:700;color:{{ $statusColor }};background:{{ $statusBg }};">
                                    {{ $statusLabel }}
                                </span>
                                @if($order->cashier)
                                    <small style="color:#64748b;font-size:0.72rem;">Kasir: {{ $order->cashier->name }}</small>
                                @endif
                            </div>
                        </td>
                        <td style="font-weight: 700;">{{ 'Rp ' . number_format($order->effective_total, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $itemsArray = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
                                $itemsArray = is_array($itemsArray) ? $itemsArray : [];
                            @endphp
                            @if(count($itemsArray) > 0)
                                <ul style="padding-left: 15px; margin: 0; font-size: 0.8rem; line-height: 1.5;">
                                    @foreach(array_slice($itemsArray, 0, 3) as $item)
                                        @php
                                            $potongan = (int) ($item['potongan'] ?? 0);
                                        @endphp
                                        <li>
                                            {{ $item['nama_obat'] ?? $item['name'] ?? 'N/A' }}
                                            <span style="color:#6b7280;font-weight:500;">(×{{ $item['quantity'] ?? $item['qty'] ?? 0 }} {{ strtolower($item['unit_label'] ?? 'box') }})</span>
                                            @if($potongan > 0)
                                                <span style="display:inline-block;margin-left:0.3rem;padding:0.15rem 0.45rem;border-radius:999px;background:#fee2e2;color:#b91c1c;font-size:0.72rem;font-weight:700;">Potongan Rp {{ number_format($potongan, 0, ',', '.') }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    @if(count($itemsArray) > 3)
                                        <li style="color:#6b7280; margin-top: 0.3rem;"><em>+{{ count($itemsArray) - 3 }} item lainnya</em></li>
                                    @endif
                                </ul>
                            @else
                                <span style="color:#9ca3af; font-style: italic;">Tidak ada data</span>
                            @endif
                        </td>
                        <td style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                            <a class="btn btn-secondary btn-sm" href="{{ URL::temporarySignedRoute('orders.invoice', now()->addDays(30), ['order' => $order->id]) }}" target="_blank" rel="noopener">
                                <i class="fa-solid fa-eye"></i> Lihat
                            </a>
                            <form method="POST" action="{{ route('admin.purchase-history.destroy', $order->id) }}" onsubmit="return confirmDeleteOne(event, '{{ addslashes($order->buyer_name) }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:#6b7280;padding:2rem;">
                            Belum ada riwayat penjualan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($orders->total() > 0)
        <div class="transaction-pagination">
            <p class="transaction-pagination-summary">
                Menampilkan {{ number_format($orders->firstItem(), 0, ',', '.') }}–{{ number_format($orders->lastItem(), 0, ',', '.') }} dari {{ number_format($orders->total(), 0, ',', '.') }} transaksi

            </p>
            @if($orders->hasPages())
                <nav class="transaction-pagination-nav" aria-label="Navigasi halaman transaksi">
                    @if($orders->onFirstPage())
                        <span class="transaction-page-link is-disabled" aria-disabled="true">‹ Sebelumnya</span>
                    @else
                        <a class="transaction-page-link" href="{{ $orders->previousPageUrl() }}">‹ Sebelumnya</a>
                    @endif

                    @foreach(range(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page)
                        @if($page === $orders->currentPage())
                            <span class="transaction-page-link is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="transaction-page-link" href="{{ $orders->url($page) }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($orders->hasMorePages())
                        <a class="transaction-page-link" href="{{ $orders->nextPageUrl() }}">Berikutnya ›</a>
                    @else
                        <span class="transaction-page-link is-disabled" aria-disabled="true">Berikutnya ›</span>
                    @endif
                </nav>
            @endif
        </div>
    @endif
</div>
</div>

{{-- Modal Detil --}}
<div id="detail-modal" class="modal-backdrop" style="display:none;" onclick="closeDetailModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h5 class="modal-title">Detail Pembelian</h5>
            <button type="button" class="modal-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="detail-content">
                {{-- Content will be injected by JS --}}
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('styles')
<style>
.transaction-history-page {
    width: 100%;
    min-width: 0;
    max-width: 100%;
}
.transaction-tabs { display:flex;flex-wrap:wrap;gap:.6rem;margin-bottom:1.25rem; }
.transaction-tabs a { display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1rem;border:1px solid #dbe3ec;border-radius:.6rem;background:#fff;color:#334155;font-weight:700;text-decoration:none; }
.transaction-tabs a.active { border-color:#1d4ed8;background:#eff6ff;color:#1d4ed8; }
.fulfillment-tabs { margin: 0 0 1.25rem; }
.transaction-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}
.transaction-heading {
    color: #1e293b;
    font-size: 1.3rem;
    font-weight: 700;
}
.purchase-history-filters {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: .75rem;
    margin: 0 0 1rem;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: .65rem;
    background: #f8fafc;
}
.purchase-history-filters label {
    display: grid;
    gap: .3rem;
    color: #475569;
    font-size: .78rem;
    font-weight: 700;
}
.purchase-history-filters input,
.purchase-history-filters select {
    width: 100%;
    min-height: 38px;
    padding: .45rem .6rem;
    border: 1px solid #cbd5e1;
    border-radius: .45rem;
    background: #fff;
    color: #1e293b;
    font: inherit;
    font-size: .84rem;
}
.purchase-history-filter-actions {
    display: flex;
    align-items: end;
    gap: .5rem;
}
.transaction-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.transaction-table-container {
    max-width: 100%;
    overflow-x: auto;
    overscroll-behavior-x: contain;
}
.transaction-table {
    min-width: 1120px;
}
.transaction-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem 1rem;
    margin-top: 1.25rem;
}
.transaction-pagination-summary {
    margin: 0;
    color: #64748b;
    font-size: 0.875rem;
}
.transaction-pagination-nav {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
}
.transaction-page-link {
    display: inline-flex;
    min-width: 2.25rem;
    min-height: 2.25rem;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.65rem;
    border: 1px solid #dbe2ea;
    border-radius: 0.5rem;
    background: #fff;
    color: #334155;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
}
.transaction-page-link:hover {
    border-color: #94a3b8;
    background: #f8fafc;
}
.transaction-page-link.is-current {
    border-color: #0f172a;
    background: #0f172a;
    color: #fff;
}
.transaction-page-link.is-disabled {
    color: #94a3b8;
    background: #f8fafc;
}
@media (max-width: 768px) {
    .transaction-toolbar {
        align-items: flex-start;
        flex-direction: column;
    }
    .transaction-toolbar-actions {
        width: 100%;
    }
    .transaction-pagination {
        align-items: flex-start;
        flex-direction: column;
    }
    .transaction-pagination-nav {
        width: 100%;
    }
}
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1040;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: #fff;
    border-radius: 0.5rem;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    animation: slide-down 0.3s ease-out;
    overflow: hidden;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}
.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}
.modal-close {
    border: none;
    background: transparent;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}
.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    overflow-x: hidden;
}
.detail-grid {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 0.75rem 1.5rem;
}
.detail-label {
    font-weight: 600;
    color: #4b5563;
}
.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
.items-table th, .items-table td {
    padding: 0.5rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}
.items-table th {
    background-color: #f9fafb;
}

@keyframes slide-down {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
@endsection

@section('scripts')
@if($tab === 'sales')
<script>
    const ordersData = {
        @foreach($orders as $order)
            {{ $order->id }}: @json($order),
        @endforeach
    };

    function fetchAndShowDetailModal(orderId) {
        const order = ordersData[orderId];
        if (!order) {
            alert('Data order tidak ditemukan');
            return;
        }
        showDetailModal(order);
    }

    function showDetailModal(order) {
        const modal = document.getElementById('detail-modal');
        const content = document.getElementById('detail-content');

        let items = order.items || [];
        if (typeof items === 'string') {
            try {
                items = JSON.parse(items);
            } catch (e) {
                items = [];
            }
        }

        const itemsHtml = (items && Array.isArray(items) && items.length > 0)
            ? items.map((item) => {
                const namaObat = item.nama_obat || item.name || 'N/A';
                const qty = item.quantity || item.qty || 0;
                const harga = item.harga || item.price || 0;
                const subtotal = qty * harga;
                const potongan = Number(item.potongan || 0);
                const potonganHtml = potongan > 0
                    ? `<span style="display:inline-block;margin-top:0.25rem;padding:0.18rem 0.5rem;border-radius:999px;background:#fee2e2;color:#b91c1c;font-size:0.72rem;font-weight:700;">Potongan Rp ${Number(potongan).toLocaleString('id-ID')}</span>`
                    : '<span style="color:#9ca3af;font-size:0.8rem;">Tidak ada potongan</span>';

                return `
                    <tr>
                        <td>${namaObat}</td>
                        <td>${qty}</td>
                        <td>Rp ${Number(harga).toLocaleString('id-ID')}</td>
                        <td>Rp ${Number(subtotal).toLocaleString('id-ID')}</td>
                        <td>${potonganHtml}</td>
                    </tr>
                `;
            }).join('')
            : '<tr><td colspan="5" style="text-align:center;color:#9ca3af;">Tidak ada data item</td></tr>';

        const statusText = 'Selesai';
        const statusColor = '#166534';
        const statusBg = '#dcfce7';

        let detailHtml = `
            <div class="detail-grid">
                <div class="detail-label">Nama Pembeli / Outlet</div>
                <div>: <strong>${order.buyer_name || order.outlet_name || '-'}</strong></div>

                ${order.requester_name ? `<div class="detail-label">Apoteker / Dokter</div><div>: ${order.requester_name}</div>` : ''}

                <div class="detail-label">Jenis</div>
                <div>: ${['pbf', 'apotik', 'dokter'].includes(order.buyer_type) ? 'B2B (' + String(order.buyer_type).toUpperCase() + ')' : 'Umum (B2C)'}</div>

                <div class="detail-label">Kontak</div>
                <div>: ${order.phone || '-'}</div>

                <div class="detail-label">Alamat</div>
                <div>: ${order.address || '-'}${order.kecamatan ? ', ' + order.kecamatan : ''}${order.kota ? ', ' + order.kota : ''}</div>

                <div class="detail-label">Waktu Pesan</div>
                <div>: ${order.created_at_wib || '-'}</div>

                <div class="detail-label">Waktu Diverifikasi</div>
                <div>: ${order.verified_at_wib || '-'}</div>

                <div class="detail-label">Waktu Dibayar</div>
                <div>: ${order.paid_at_wib || '-'}</div>

                <div class="detail-label">Waktu Dicetak</div>
                <div>: ${order.printed_at_wib || '-'}</div>

                ${order.due_date_formatted ? `<div class="detail-label">Jatuh Tempo</div><div>: <strong style="color:#dc2626;">${order.due_date_formatted} (${order.tempo_due_label || '-'})</strong></div>` : ''}
        `;

        if (order.sia || order.sipa || ['pbf', 'apotik', 'dokter'].includes(order.buyer_type)) {
            detailHtml += `
                <div class="detail-label">SIA</div>
                <div>: ${order.sia || '-'}</div>
                <div class="detail-label">SIPA</div>
                <div>: ${order.sipa || '-'}</div>
            `;
        }

        detailHtml += `
            </div>
            <h6 style="margin-top: 1.5rem; margin-bottom: 0.5rem; font-weight: 600;">Daftar Item</h6>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Nama Obat</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Potongan</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>
            <div style="margin-top: 1rem; padding: 0.75rem 0.9rem; background: #f8fafc; border-radius: 8px;">
                <div style="font-weight: 700; font-size: 1.05rem;">Status Kasir: <span style="display:inline-block;padding:0.25rem 0.6rem;border-radius:999px;font-size:0.8rem;font-weight:700;color:${statusColor};background:${statusBg};">${statusText}</span></div>
                <div style="margin-top: 0.35rem; font-weight: 700; font-size: 1.05rem;">Total Efektif: Rp ${Number(order.effective_total || 0).toLocaleString('id-ID')}</div>
            </div>
            ${order.payment_method ? `<div style="margin-top:0.75rem;padding:0.6rem 0.9rem;background:#eff6ff;border-radius:8px;font-size:0.9rem;color:#1d4ed8;"><i class="fa-solid fa-credit-card" style="margin-right:0.4rem;"></i> <strong>Metode Pembayaran:</strong> ${order.payment_method === 'Tempo' && order.payment_term_days ? `${order.payment_method} (${order.payment_term_days} hari)` : order.payment_method}</div>` : ''}
        `;

        content.innerHTML = detailHtml;
        modal.style.display = 'flex';
    }

    function closeDetailModal() {
        const modal = document.getElementById('detail-modal');
        modal.style.display = 'none';
    }

    function confirmDeleteOne(e, buyerName) {
        if (!confirm('Hapus riwayat pembelian untuk "' + buyerName + '" ?')) {
            e.preventDefault();
            return false;
        }
        return true;
    }
</script>
@endif
@endsection
