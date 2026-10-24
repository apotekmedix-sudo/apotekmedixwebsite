@extends('layouts.admin')

@section('title', 'Data Customer - Admin Apotek Medix')
@section('page-title', 'Data Customer')

@section('content')
<style>
    .customer-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
    .customer-summary-card{padding:16px;background:#fff;border:1px solid #e2e8f0;border-radius:12px}
    .customer-summary-card span{display:block;color:#64748b;font-size:12px;font-weight:600}
    .customer-summary-card strong{display:block;margin-top:6px;color:#1e293b;font-size:22px}
    .customer-heading{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}
    .customer-add,.customer-action{display:inline-block;padding:8px 11px;border-radius:8px;background:#0052cc;color:#fff;text-decoration:none;font-size:12px;font-weight:700}
    .customer-export{background:#15803d}
    .customer-tools{display:flex;flex-wrap:wrap;gap:8px}
    .customer-import{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:14px;padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc}
    .customer-import input[type=file]{max-width:260px;font-size:12px}
    .customer-import button{padding:8px 11px;border:0;border-radius:8px;background:#0052cc;color:#fff;font-size:12px;font-weight:700;cursor:pointer}
    .customer-import-hint{color:#64748b;font-size:12px}
    .customer-import-errors{margin:0 0 14px;padding:10px 14px;border-radius:8px;background:#fff1f2;color:#9f1239;font-size:13px}
    .customer-filters{display:flex;flex-wrap:wrap;align-items:end;gap:12px;margin:16px 0}
    .customer-filters label{display:block;margin-bottom:5px;color:#475569;font-size:12px;font-weight:700}
    .customer-filters input,.customer-filters select{min-width:190px;padding:9px 11px;border:1px solid #cbd5e1;border-radius:8px;background:#fff}
    .customer-filters button,.customer-actions button{padding:9px 12px;border:0;border-radius:8px;background:#0052cc;color:#fff;font-weight:700;cursor:pointer}
    .channel-badge{display:inline-block;padding:4px 9px;border-radius:999px;font-size:11px;font-weight:700;white-space:nowrap}
    .channel-internal{background:#dbeafe;color:#1d4ed8}.channel-b2b{background:#cffafe;color:#0e7490}.channel-b2c{background:#dcfce7;color:#15803d}
    .customer-subtext{display:block;margin-top:4px;color:#64748b;font-size:12px}
    .customer-actions{display:flex;gap:6px;align-items:center}.customer-actions form{margin:0}.customer-action.edit{background:#eaf2ff;color:#0052cc}.customer-actions button.delete{background:#fff1f2;color:#be123c}
    .flash-success{padding:12px 14px;margin-bottom:14px;border-radius:8px;background:#dcfce7;color:#166534}
    @media(max-width:700px){.customer-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.customer-filters>*{width:100%}.customer-filters input,.customer-filters select{width:100%}.customer-heading{align-items:flex-start;flex-direction:column}}
</style>

@if(session('success'))<div class="flash-success">{{ session('success') }}</div>@endif
@if($errors->any())
    <ul class="customer-import-errors" role="alert">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
@endif

<section class="customer-summary" aria-label="Ringkasan customer dan kanal">
    <div class="customer-summary-card"><span>MASTER CUSTOMER</span><strong>{{ number_format($summary['total']) }}</strong></div>
    <div class="customer-summary-card"><span>CUSTOMER INTERNAL</span><strong>{{ number_format($summary['internal']) }}</strong></div>
    <div class="customer-summary-card"><span>CUSTOMER B2B</span><strong>{{ number_format($summary['b2b']) }}</strong></div>
    <div class="customer-summary-card"><span>CUSTOMER B2C</span><strong>{{ number_format($summary['b2c']) }}</strong></div>
</section>

<div class="card">
    <div class="customer-heading">
        <div>
            <div class="card-title">Master Customer</div>
            <p style="color:#64748b;margin-top:-.75rem;">Data customer otomatis dihimpun dari pesanan Internal, B2B, dan B2C. Pesanan berulang dengan identitas yang cocok akan masuk ke satu profil.</p>
        </div>
        <div class="customer-tools">
            <a class="customer-add customer-export" href="{{ route('admin.customers.export.master') }}">Export Master CSV</a>
            <a class="customer-add customer-export" href="{{ route('admin.customers.export') }}">Export Customer per Kanal</a>
            <a class="customer-add" href="{{ route('admin.customers.create') }}">+ Tambah Customer</a>
        </div>
    </div>
    <form class="customer-import" method="POST" action="{{ route('admin.customers.import') }}" enctype="multipart/form-data">
        @csrf
        <a class="customer-add" href="{{ route('admin.customers.import.template') }}">Download Template CSV</a>
        <input type="file" name="file" accept=".csv,.txt,text/csv" aria-label="Pilih file CSV customer" required>
        <button type="submit">Import Customer</button>
        <span class="customer-import-hint">Gunakan format template CSV. ID yang ada akan diperbarui; ID kosong akan menambah customer baru.</span>
    </form>

    <form class="customer-filters" method="GET" action="{{ route('admin.customers.index') }}">
        <div>
            <label for="customer-search">Cari customer</label>
            <input id="customer-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, telepon, email, outlet, atau ID pesanan">
        </div>
        <div>
            <label for="customer-channel">Kanal pesanan</label>
            <select id="customer-channel" name="channel">
                <option value="all">Semua customer</option>
                <option value="internal" @selected(($filters['channel'] ?? '') === 'internal')>Pernah belanja Internal</option>
                <option value="b2b" @selected(($filters['channel'] ?? '') === 'b2b')>Pernah belanja B2B</option>
                <option value="b2c" @selected(($filters['channel'] ?? '') === 'b2c')>Pernah belanja B2C</option>
            </select>
        </div>
        <button type="submit">Terapkan</button>
        @if(!empty($filters['search']) || (!empty($filters['channel']) && $filters['channel'] !== 'all'))
            <a href="{{ route('admin.customers.index') }}">Hapus filter</a>
        @endif
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Customer / Pemesan</th>
                    <th>Kontak dan alamat</th>
                    <th>Riwayat kanal</th>
                    <th>Total pesanan</th>
                    <th>Pesanan terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <strong>{{ $customer->name }}</strong>
                            @if($customer->requester_name)<span class="customer-subtext">Pemesan: {{ $customer->requester_name }}</span>@endif
                            @if($customer->email)<span class="customer-subtext">{{ $customer->email }}</span>@endif
                            <span class="customer-subtext">{{ strtoupper($customer->buyer_type) }}</span>
                        </td>
                        <td>
                            {{ $customer->phone ?: '-' }}
                            <span class="customer-subtext">{{ collect([$customer->address, $customer->kecamatan, $customer->kota])->filter()->join(', ') ?: 'Alamat tidak dicantumkan' }}</span>
                        </td>
                        <td>
                            @if($customer->internal_orders_count)<span class="channel-badge channel-internal">Internal {{ $customer->internal_orders_count }}</span>@endif
                            @if($customer->b2b_orders_count)<span class="channel-badge channel-b2b">B2B {{ $customer->b2b_orders_count }}</span>@endif
                            @if($customer->b2c_orders_count)<span class="channel-badge channel-b2c">B2C {{ $customer->b2c_orders_count }}</span>@endif
                            @if(!$customer->purchase_histories_count)<span class="customer-subtext">Belum ada pesanan</span>@endif
                        </td>
                        <td>Rp {{ number_format((int) $customer->total_pesanan, 0, ',', '.') }}</td>
                        <td>{{ $customer->last_order_at?->format('d M Y, H:i') ?: '-' }}</td>
                        <td>
                            <div class="customer-actions">
                                <a class="customer-action edit" href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
                                <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Hapus profil customer ini? Riwayat pesanan tetap tersimpan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="delete" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;padding:2rem;color:#64748b;">Belum ada customer yang sesuai. Profil akan otomatis dibuat saat ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem;">{{ $customers->links() }}</div>
</div>
@endsection
