@extends('layouts.admin')

@section('title', 'Produk Kami - Admin Apotek Medix')
@section('page-title', 'Produk Kami')

@section('styles')
<style>
    .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; gap:1rem; flex-wrap:wrap; }
    .page-header-left h2 { font-size:1.1rem; font-weight:700; color:#1f2937; margin:0 0 0.25rem; }
    .page-header-left p  { font-size:0.85rem; color:#6b7280; margin:0; }
    .page-header-actions { display:flex; gap:0.6rem; flex-wrap:wrap; }
    .btn-icon { display:inline-flex; align-items:center; gap:0.4rem; padding:0.55rem 1.1rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all 0.2s; white-space:nowrap; }
    .btn-icon-primary { background:#1E88E5; color:white; }
    .btn-icon-primary:hover { background:#1565C0; color:white; transform:translateY(-1px); }
    .btn-icon-outline { background:white; color:#374151; border:1px solid #d1d5db; }
    .btn-icon-outline:hover { background:#f9fafb; border-color:#9ca3af; color:#374151; }
    .btn-icon-success { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
    .btn-icon-success:hover { background:#2e7d32; color:white; }

    /* Kategori tabs */
    .kat-tabs { display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .kat-tab { padding:0.45rem 1rem; border-radius:20px; font-size:0.82rem; font-weight:700; text-decoration:none; border:2px solid #e5e7eb; color:#6b7280; background:white; transition:all 0.2s; }
    .kat-tab:hover { border-color:#1E88E5; color:#1E88E5; }
    .kat-tab.active { background:#1E88E5; color:white; border-color:#1E88E5; }
    .kat-count { background:rgba(255,255,255,0.25); border-radius:20px; padding:0.05rem 0.45rem; font-size:0.72rem; margin-left:0.3rem; }
    .kat-tab:not(.active) .kat-count { background:#f3f4f6; color:#9ca3af; }

    .search-card { background:white; border-radius:0.75rem; padding:1rem 1.25rem; margin-bottom:1.25rem; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #f0f0f0; }
    .search-row { display:flex; gap:0.75rem; align-items:flex-end; flex-wrap:wrap; }
    .search-field { flex:1; min-width:180px; }
    .search-field label { display:block; font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.35rem; text-transform:uppercase; letter-spacing:0.04em; }
    .search-input-wrap { position:relative; }
    .search-input-wrap i { position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:0.85rem; }
    .search-input-wrap input, .search-input-wrap select { width:100%; padding:0.55rem 0.75rem 0.55rem 2.1rem; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.875rem; background:#fafafa; transition:all 0.2s; color:#1f2937; }
    .search-input-wrap select { padding-left:0.75rem; }
    .search-input-wrap input:focus, .search-input-wrap select:focus { outline:none; border-color:#1E88E5; background:white; box-shadow:0 0 0 3px rgba(30,136,229,0.08); }
    .search-actions { display:flex; gap:0.5rem; align-items:flex-end; }
    .btn-search { padding:0.55rem 1.25rem; background:#1E88E5; color:white; border:none; border-radius:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; transition:background 0.2s; display:inline-flex; align-items:center; gap:0.4rem; }
    .btn-search:hover { background:#1565C0; }
    .btn-reset { padding:0.55rem 0.9rem; background:white; color:#6b7280; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; transition:all 0.2s; }
    .btn-reset:hover { background:#f9fafb; color:#374151; }

    .data-table-wrap { background:white; border-radius:0.75rem; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #f0f0f0; overflow-x:auto; }
    .data-table { width:100%; min-width:1850px; border-collapse:collapse; font-size:0.875rem; }
    .data-table thead tr { background:#f8faff; border-bottom:2px solid #e5e7eb; }
    .data-table th { padding:0.85rem 1rem; text-align:left; font-size:0.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap; }
    .data-table td { padding:0.85rem 1rem; border-bottom:1px solid #f3f4f6; color:#374151; vertical-align:middle; }
    .data-table tbody tr:last-child td { border-bottom:none; }
    .data-table tbody tr:hover { background:#fafbff; }

    .med-img { width:88px; height:88px; border-radius:0.5rem; object-fit:cover; border:1px solid #e5e7eb; display:block; }
    .med-img-placeholder { width:88px; height:88px; padding:0.5rem; border-radius:0.5rem; background:linear-gradient(135deg,#f8fafc,#e0f2fe); display:flex; align-items:center; justify-content:center; border:1px solid #e5e7eb; }
    .med-img-placeholder img { width:100%; height:100%; object-fit:contain; }
    .kat-tab i { margin-right:0.25rem; }
    .med-name { font-weight:600; color:#1f2937; }
    .med-desc { color:#374151; font-size:0.82rem; margin-top:0.45rem; max-width:240px; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
    .med-meta { font-size:0.76rem; color:#6b7280; margin-top:0.25rem; max-width:240px; }

    .kat-badge { display:inline-block; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.72rem; font-weight:700; }
    .kat-lengkap   { background:#e3f2fd; color:#1565C0; }
    .kat-skincare  { background:#fce4ec; color:#c2185b; }
    .kat-alkes     { background:#e8f5e9; color:#2e7d32; }

    .stock-badge { display:inline-flex; align-items:center; padding:0.25rem 0.65rem; border-radius:20px; font-size:0.78rem; font-weight:700; }
    .stock-ok    { background:#d1fae5; color:#065f46; }
    .stock-low   { background:#fef3c7; color:#92400e; }
    .stock-empty { background:#fee2e2; color:#991b1b; }
    .price-text  { font-weight:600; color:#1E88E5; }
    .inline-number { width:92px; padding:0.35rem 0.45rem; border:1px solid #dbe3ef; border-radius:0.4rem; color:#1E88E5; font-size:0.82rem; font-weight:600; background:#fff; }
    .price-unit-fields { display:grid; gap:0.35rem; min-width:92px; }
    .price-unit-field { display:grid; gap:0.15rem; }
    .price-unit-field label { font-size:0.68rem; font-weight:600; color:#64748b; }
    .inline-number.price-unit-input { width:78px; padding:0.28rem 0.35rem; font-size:0.75rem; }
    .inline-number:focus { outline:none; border-color:#1E88E5; box-shadow:0 0 0 3px rgba(30,136,229,0.1); }
    .inline-save { display:inline-flex; align-items:center; gap:0.3rem; margin-top:0.35rem; padding:0.3rem 0.55rem; border:0; border-radius:0.4rem; background:#e8f5e9; color:#2e7d32; font-size:0.72rem; font-weight:700; cursor:pointer; }
    .inline-save:hover { background:#2e7d32; color:#fff; }

    .converted-stock-pills { display:grid; gap:0.22rem; margin-top:0.4rem; min-width:148px; }
    .converted-stock-pill { display:flex; align-items:center; justify-content:space-between; gap:0.4rem; padding:0.2rem 0.45rem; border-radius:0.35rem; font-size:0.72rem; font-weight:700; background:#f8fafc; border:1px solid #e2e8f0; color:#334155; }
    .converted-stock-pill--box { background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; }
    .converted-stock-pill--strip { background:#f0f9ff; border-color:#bae6fd; color:#0369a1; }
    .converted-stock-pill--tablet { background:#ecfdf5; border-color:#a7f3d0; color:#047857; }

    .action-wrap { display:flex; gap:0.4rem; }
    .btn-edit, .btn-del { display:inline-flex; align-items:center; gap:0.3rem; padding:0.35rem 0.75rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all 0.2s; }
    .bulk-actions { display:flex; justify-content:space-between; align-items:center; padding:0.9rem 1.1rem; background:#f8faff; border-bottom:1px solid #e5e7eb; gap:0.75rem; flex-wrap:wrap; }
    .bulk-actions-left { display:flex; align-items:center; gap:0.6rem; font-size:0.85rem; font-weight:600; color:#374151; }
    .bulk-actions-left input[type="checkbox"] { width:16px; height:16px; accent-color:#1E88E5; }
    .bulk-btn { display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 0.95rem; border-radius:0.45rem; font-size:0.8rem; font-weight:700; border:none; cursor:pointer; }
    .bulk-btn:disabled { opacity:0.55; cursor:not-allowed; }
    .bulk-btn-danger { background:#fee2e2; color:#991b1b; }
    .bulk-btn-danger:hover:not(:disabled) { background:#ef4444; color:white; }
    .btn-edit { background:#e3f2fd; color:#1565C0; }
    .btn-edit:hover { background:#1E88E5; color:white; }
    .btn-del  { background:#fee2e2; color:#991b1b; }
    .btn-del:hover  { background:#ef4444; color:white; }

    /* Modal konfirmasi hapus */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
    .modal-overlay.show { display:flex; }
    .modal-box { background:white; border-radius:1rem; padding:2rem; max-width:380px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.2); text-align:center; }
    .modal-icon { font-size:2.5rem; margin-bottom:0.75rem; }
    .modal-box h3 { font-size:1rem; font-weight:700; color:#1f2937; margin:0 0 0.5rem; }
    .modal-box p  { font-size:0.875rem; color:#6b7280; margin:0 0 1.5rem; }
    .modal-actions { display:flex; gap:0.6rem; justify-content:center; }
    .btn-modal-cancel { padding:0.6rem 1.5rem; background:white; color:#374151; border:1.5px solid #e5e7eb; border-radius:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; transition:all 0.2s; }
    .btn-modal-cancel:hover { background:#f9fafb; }
    .btn-modal-confirm { padding:0.6rem 1.5rem; background:#ef4444; color:white; border:none; border-radius:0.5rem; font-size:0.875rem; font-weight:700; cursor:pointer; transition:all 0.2s; }
    .btn-modal-confirm:hover { background:#dc2626; }

    .pagination-wrap { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-top:1px solid #f3f4f6; flex-wrap:wrap; gap:0.75rem; }
    .pagination-info { font-size:0.8rem; color:#6b7280; }
    .pagination-pages { display:flex; gap:0.3rem; }
    .page-btn { min-width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:0.4rem; font-size:0.8rem; font-weight:600; text-decoration:none; border:1px solid #e5e7eb; color:#374151; background:white; transition:all 0.2s; padding:0 0.5rem; }
    .page-btn:hover { background:#1E88E5; color:white; border-color:#1E88E5; }
    .page-btn.active { background:#1E88E5; color:white; border-color:#1E88E5; }
    .page-btn.disabled { background:#f9fafb; color:#d1d5db; cursor:not-allowed; pointer-events:none; }

    .empty-state { padding:4rem 2rem; text-align:center; background:white; border-radius:0.75rem; border:1px solid #f0f0f0; }
    .empty-icon { margin-bottom:1rem; color:#0e7490; font-size:3rem; }
    .empty-state h3 { font-size:1rem; font-weight:700; color:#1f2937; margin-bottom:0.5rem; }
    .empty-state p { font-size:0.875rem; color:#6b7280; margin-bottom:1.5rem; }

    @media (max-width:768px) {
        .page-header { flex-direction:column; }
        .data-table-wrap { overflow-x:auto; }
        .data-table { min-width:600px; }
        .search-row { flex-direction:column; }
        .search-field { min-width:100%; }
    }
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h2><i class="fa-solid {{ ($mode ?? '') === 'pbf' ? 'fa-boxes-stacked' : 'fa-pills' }}" aria-hidden="true"></i> {{ ($mode ?? '') === 'pbf' ? 'Produk Pemesanan PBF - Harga Modal' : 'Daftar Produk' }}</h2>
        <p>Total <strong>{{ $total }}</strong> produk terdaftar</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('admin.produk.import') }}" class="btn-icon btn-icon-outline">
            <i class="fa-solid fa-file-import"></i> Import Excel
        </a>
        <a href="{{ route('admin.business-data.export') }}" class="btn-icon btn-icon-success">
            <i class="fa-solid fa-database"></i> Backup Data Bisnis
        </a>
        <a href="{{ route('admin.produk.create') }}" class="btn-icon btn-icon-primary">
            <i class="fa-solid fa-plus"></i> Tambah Produk
        </a>
    </div>
</div>

{{-- Kategori Tabs --}}
<div class="kat-tabs">
    <a href="{{ route('admin.produk.index', array_merge(request()->except('kategori_produk'), [])) }}"
       class="kat-tab {{ !$kategori_produk ? 'active' : '' }}">
        <i class="fa-solid fa-layer-group" aria-hidden="true"></i> Semua
        <span class="kat-count">{{ $total }}</span>
    </a>
    @foreach($kategoriOptions as $kat)
        @php
            $count = \App\Models\Medicine::where('kategori_produk', $kat)->count();
            $icon  = match($kat) {
                'OBAT'                => 'fa-pills',
                'SKINCARE & KOSMETIK' => 'fa-sparkles',
                'ALAT KESEHATAN'      => 'fa-kit-medical',
                default               => 'fa-box',
            };
        @endphp
        <a href="{{ route('admin.produk.index', array_merge(request()->except('kategori_produk'), ['kategori_produk' => $kat])) }}"
           class="kat-tab {{ $kategori_produk === $kat ? 'active' : '' }}">
            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $kat }}
            <span class="kat-count">{{ $count }}</span>
        </a>
    @endforeach
</div>

{{-- Search --}}
<div class="search-card">
    <form method="GET" action="{{ route('admin.produk.index') }}">
        @if($kategori_produk)
            <input type="hidden" name="kategori_produk" value="{{ $kategori_produk }}">
        @endif
        <div class="search-row">
            <div class="search-field">
                <label>Cari Produk</label>
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama produk, indikasi, komposisi, sediaan (sirup/tablet), dosis, golongan...">
                </div>
            </div>
            <div class="search-actions">
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                @if($search || $pabrik || request('indikasi') || request('komposisi') || request('sediaan') || request('dosis') || request('golongan'))
                    <a href="{{ route('admin.produk.index', $kategori_produk ? ['kategori_produk' => $kategori_produk] : []) }}" class="btn-reset">
                        <i class="fa-solid fa-xmark"></i> Reset
                    </a>
                @endif
            </div>
        </div>

    </form>
</div>

@if($medicines->count() > 0)
    <div class="data-table-wrap">
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.produk.destroyMany') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="kategori_produk" value="{{ $kategori_produk }}">
            <input type="hidden" name="pabrik" value="{{ $pabrik }}">
            <input type="hidden" name="page" value="{{ request('page', 1) }}">

            <div class="bulk-actions">
                <label class="bulk-actions-left">
                    <input type="checkbox" id="selectAllProducts">
                    Pilih semua produk di halaman ini
                </label>
                <button type="submit" class="bulk-btn bulk-btn-danger" id="bulkDeleteBtn" disabled
                        onclick="return confirm('Yakin ingin menghapus produk terpilih?');">
                    <i class="fa-solid fa-trash"></i> Hapus Terpilih <span id="bulkDeleteCount">(0)</span>
                </button>
            </div>

            <table class="data-table">
            <thead>
                <tr>
                    <th style="width:48px;"><i class="fa-regular fa-square-check" aria-label="Pilih produk"></i></th>
                    <th style="width:140px;">Foto</th>
                    <th>Nama Produk</th>
                    <th>Principle / Logo / Golongan</th>
                    <th>Komposisi / Indikasi</th>
                    <th>Satuan / Kemasan</th>
                    <th>Sediaan</th>
                    <th>Kategori</th>
                    <th>Modal</th>
                    <th>Harga Internal</th>
                    <th>Harga B2B</th>
                    <th>Harga B2C</th>
                    <th>Harga Strip / Tablet</th>
                    <th>Stok (Box / Strip / Tablet)</th>
                    <th>Ditambahkan</th>
                    <th style="width:130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicines as $medicine)
                @php
                    $converted = $medicine->getConvertedUnitStocksForUnits($medicine->available_stock_units);
                @endphp
                <tr data-product-row="{{ $medicine->id }}" data-unit-label="{{ $medicine->package_unit_label }}">
                    <td style="text-align:center;">
                        <input type="checkbox" class="product-checkbox" name="produk_ids[]" value="{{ $medicine->id }}">
                    </td>
                    <td style="vertical-align:top;">
                        @if($medicine->gambar)
                            <img src="{{ url('storage/' . $medicine->gambar) }}" alt="{{ $medicine->nama_obat }}" class="med-img">
                        @else
                            <div class="med-img-placeholder">
                                <img src="{{ asset('LOGO APOTEK MEDIX.PNG') }}" alt="Apotek Medix">
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="med-name">{{ $medicine->nama_obat }}</div>
                        @if($medicine->kemasan)<div class="med-meta">{{ $medicine->kemasan }}</div>@endif
                    </td>
                    <td>
                        <div>{{ $medicine->kategori ?: '-' }}</div>
                        <div class="med-meta">Logo: {{ $medicine->logo ?: '-' }} | Golongan: {{ $medicine->golongan ?: '-' }}</div>
                    </td>
                    <td>
                        <div class="med-desc">{{ $medicine->komposisi ?: '-' }}</div>
                        <div class="med-meta">{{ $medicine->indikasi ?: '-' }}</div>
                        @if($medicine->dosis)<div class="med-meta" style="color:#0369a1;font-weight:600;">Dosis: {{ $medicine->dosis }}</div>@endif
                    </td>
                    <td>
                        <div style="font-weight:700;">{{ $medicine->satuan ?: $medicine->package_unit_label }}</div>
                        <div class="price-unit-fields" style="margin-top:0.35rem;">
                            <div class="price-unit-field">
                                <label for="strips-per-box-{{ $medicine->id }}">1 {{ $medicine->package_unit_label }} = ... Strip</label>
                                <input id="strips-per-box-{{ $medicine->id }}" class="inline-number price-unit-input js-row-strips" type="number" name="strips_per_box" form="inline-update-{{ $medicine->id }}" value="{{ $medicine->unit_packaging_enabled ? ($medicine->strips_per_box ?? 0) : 0 }}" min="0" step="1" aria-label="Strip per {{ $medicine->package_unit_label }} {{ $medicine->nama_obat }}">
                            </div>
                            <div class="price-unit-field">
                                <label for="tablets-per-strip-{{ $medicine->id }}">1 Strip = ... Tablet</label>
                                <input id="tablets-per-strip-{{ $medicine->id }}" class="inline-number price-unit-input js-row-tablets" type="number" name="tablets_per_strip" form="inline-update-{{ $medicine->id }}" value="{{ $medicine->unit_packaging_enabled ? ($medicine->tablets_per_strip ?? 0) : 0 }}" min="0" step="1" aria-label="Tablet per strip {{ $medicine->nama_obat }}">
                            </div>
                        </div>
                        @if($medicine->unit_packaging_enabled)
                            <small class="med-meta" style="display:block;margin-top:0.25rem;">{{ $medicine->strips_per_box }} strip / {{ $medicine->tablets_per_strip * $medicine->strips_per_box }} tablet per {{ strtolower($medicine->package_unit_label) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($medicine->sediaan)
                            <span style="display:inline-block;padding:0.25rem 0.5rem;background:#e0f2fe;color:#0369a1;border-radius:4px;font-size:0.75rem;font-weight:600;text-transform:uppercase;">
                                {{ $medicine->sediaan }}
                            </span>
                        @else
                            <span style="font-size:0.75rem;color:#9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $cls = match($medicine->kategori_produk) {
                                'SKINCARE & KOSMETIK' => 'kat-skincare',
                                'ALAT KESEHATAN'      => 'kat-alkes',
                                default               => 'kat-lengkap',
                            };
                        @endphp
                        <span class="kat-badge {{ $cls }}">{{ $medicine->kategori_produk }}</span>
                    </td>
                    <td>
                        <input class="inline-number" type="number" name="harga_modal" form="inline-update-{{ $medicine->id }}" value="{{ $medicine->harga_modal ?: $medicine->harga }}" min="0" step="0.01" aria-label="Harga modal {{ $medicine->nama_obat }}">
                    </td>
                    <td>
                        <input class="inline-number" type="number" name="harga_internal" form="inline-update-{{ $medicine->id }}" value="{{ $medicine->getChannelBasePrice('internal') }}" min="0" step="0.01" aria-label="Harga jual internal {{ $medicine->nama_obat }}">
                    </td>
                    <td>
                        <input class="inline-number" type="number" name="harga_grosir" form="inline-update-{{ $medicine->id }}" value="{{ $medicine->harga_grosir ?: $medicine->harga_modal ?: $medicine->harga }}" min="0" step="0.01" aria-label="Harga jual B2B {{ $medicine->nama_obat }}">
                    </td>
                    <td>
                        <input class="inline-number" type="number" name="harga_retail" form="inline-update-{{ $medicine->id }}" value="{{ $medicine->price_box ?? ($medicine->harga_retail ?: $medicine->harga) }}" min="0" step="0.01" aria-label="Harga jual B2C {{ $medicine->nama_obat }}">
                    </td>
                    <td>
                        <div class="price-unit-fields">
                            <div class="price-unit-field">
                                <label for="price-strip-{{ $medicine->id }}">Strip</label>
                                <input id="price-strip-{{ $medicine->id }}" class="inline-number price-unit-input" type="number" name="price_strip" form="inline-update-{{ $medicine->id }}" value="{{ $medicine->price_strip ?? 0 }}" min="0" step="0.01" aria-label="Harga strip {{ $medicine->nama_obat }}">
                            </div>
                            <div class="price-unit-field">
                                <label for="price-tablet-{{ $medicine->id }}">Tablet</label>
                                <input id="price-tablet-{{ $medicine->id }}" class="inline-number price-unit-input" type="number" name="price_tablet" form="inline-update-{{ $medicine->id }}" value="{{ $medicine->price_tablet ?? 0 }}" min="0" step="0.01" aria-label="Harga tablet {{ $medicine->nama_obat }}">
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="price-unit-field">
                            <label for="stok-box-{{ $medicine->id }}">Stok {{ $medicine->package_unit_label }}</label>
                            <input id="stok-box-{{ $medicine->id }}" class="inline-number js-row-stock" type="number" name="stok" form="inline-update-{{ $medicine->id }}" value="{{ rtrim(rtrim(number_format($medicine->stock_packages, 6, '.', ''), '0'), '.') }}" min="0" step="0.000001" aria-label="Stok {{ $medicine->package_unit_label }} {{ $medicine->nama_obat }}">
                        </div>
                        <div class="converted-stock-pills" data-converted-stock-pills>
                            <div class="converted-stock-pill converted-stock-pill--box">
                                <span>{{ $medicine->package_unit_label }}:</span>
                                <strong data-pill-box>{{ number_format($converted['box'], 0, ',', '.') }} {{ $medicine->package_unit_label }}</strong>
                            </div>
                            <div class="converted-stock-pill converted-stock-pill--strip" @if(!$medicine->unit_packaging_enabled) style="display:none;" @endif data-pill-strip-wrap>
                                <span>Strip:</span>
                                <strong data-pill-strip>{{ number_format($converted['strip'], 0, ',', '.') }} Strip</strong>
                            </div>
                            <div class="converted-stock-pill converted-stock-pill--tablet" @if(!$medicine->unit_packaging_enabled) style="display:none;" @endif data-pill-tablet-wrap>
                                <span>Tablet:</span>
                                <strong data-pill-tablet>{{ number_format($converted['tablet'], 0, ',', '.') }} Tablet</strong>
                            </div>
                        </div>
                        <small style="display:block;margin-top:.25rem;color:#64748b;" data-row-breakdown>Sisa: {{ $medicine->stock_breakdown }}</small>
                    </td>
                    <td style="font-size:0.82rem;color:#9ca3af;">{{ $medicine->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="action-wrap">
                            <a href="{{ route('admin.produk.edit', ['produk' => $medicine->id, 'search' => $search, 'kategori_produk' => $kategori_produk, 'pabrik' => $pabrik, 'page' => request('page')]) }}" class="btn-edit">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            <button type="button" class="btn-del"
                                onclick="confirmDelete({{ $medicine->id }}, '{{ addslashes($medicine->nama_obat) }}')">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                            <button type="submit" class="inline-save" form="inline-update-{{ $medicine->id }}">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </form>

        <div class="pagination-wrap">
            <div class="pagination-info">
                Menampilkan {{ $medicines->firstItem() }}&ndash;{{ $medicines->lastItem() }} dari {{ $medicines->total() }} produk
            </div>
            <div class="pagination-pages">
                @if($medicines->onFirstPage())
                    <span class="page-btn disabled">&lsaquo;</span>
                @else
                    <a href="{{ $medicines->previousPageUrl() }}" class="page-btn">&lsaquo;</a>
                @endif
                @foreach($medicines->getUrlRange(1, $medicines->lastPage()) as $page => $url)
                    @if(abs($page - $medicines->currentPage()) <= 2 || $page == 1 || $page == $medicines->lastPage())
                        @if($page == $medicines->currentPage())
                            <span class="page-btn active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                        @endif
                    @elseif(abs($page - $medicines->currentPage()) == 3)
                        <span class="page-btn disabled" style="border:none;background:none;">&hellip;</span>
                    @endif
                @endforeach
                @if($medicines->hasMorePages())
                    <a href="{{ $medicines->nextPageUrl() }}" class="page-btn">&rsaquo;</a>
                @else
                    <span class="page-btn disabled">&rsaquo;</span>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></div>
        <h3>Belum ada produk</h3>
        <p>Mulai tambahkan produk atau import dari file Excel/CSV.</p>
        <div style="display:flex;gap:0.6rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('admin.produk.import') }}" class="btn-icon btn-icon-outline">
                <i class="fa-solid fa-file-import"></i> Import Excel
            </a>
            <a href="{{ route('admin.produk.create') }}" class="btn-icon btn-icon-primary">
                <i class="fa-solid fa-plus"></i> Tambah Produk
            </a>
        </div>
    </div>
@endif

@endsection

@section('scripts')
{{-- Modal Konfirmasi Hapus --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></div>
        <h3>Hapus Produk?</h3>
        <p id="deleteModalText">Produk ini akan dihapus permanen dan tidak bisa dikembalikan.</p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeDeleteModal()">Batal</button>
            <button class="btn-modal-confirm" onclick="submitDelete()">
                <i class="fa-solid fa-trash"></i> Ya, Hapus
            </button>
        </div>
    </div>
</div>

{{-- Hidden form untuk DELETE --}}
<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="search" value="{{ $search }}">
    <input type="hidden" name="kategori_produk" value="{{ $kategori_produk }}">
    <input type="hidden" name="pabrik" value="{{ $pabrik }}">
    <input type="hidden" name="page" value="{{ request('page', 1) }}">
</form>

@foreach($medicines as $medicine)
    <form id="inline-update-{{ $medicine->id }}" method="POST" action="{{ route('admin.produk.update-inline', $medicine->id) }}" style="display:none;">
        @csrf
    </form>
@endforeach

<script>
const deleteRoutes = {
    @foreach($medicines as $medicine)
    {{ $medicine->id }}: '{{ route('admin.produk.destroy', $medicine->id) }}',
    @endforeach
};

function confirmDelete(id, name) {
    document.getElementById('deleteModalText').textContent =
        'Produk "' + name + '" akan dihapus permanen dan tidak bisa dikembalikan.';
    document.getElementById('deleteForm').action = deleteRoutes[id];
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

function submitDelete() {
    document.getElementById('deleteForm').submit();
}

const selectionStorageKey = 'admin_produk_selected_ids';

function getStoredSelectedIds() {
    try {
        const stored = localStorage.getItem(selectionStorageKey);
        if (!stored) return [];
        return JSON.parse(stored).filter(function (id) {
            return Number.isInteger(id);
        });
    } catch (e) {
        return [];
    }
}

function saveStoredSelectedIds(ids) {
    const uniqueIds = [...new Set(ids.map(function (id) { return Number(id); }))];
    localStorage.setItem(selectionStorageKey, JSON.stringify(uniqueIds));
}

function updateBulkDeleteState() {
    const selectedIds = getStoredSelectedIds();
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    const bulkCount = document.getElementById('bulkDeleteCount');
    const selectAll = document.getElementById('selectAllProducts');

    if (bulkBtn) {
        bulkBtn.disabled = selectedIds.length === 0;
    }

    if (bulkCount) {
        bulkCount.textContent = '(' + selectedIds.length + ')';
    }

    if (selectAll) {
        const totalCheckboxes = document.querySelectorAll('.product-checkbox');
        selectAll.checked = totalCheckboxes.length > 0 && Array.from(totalCheckboxes).every(function (checkbox) {
            return selectedIds.includes(Number(checkbox.value));
        });
    }
}

function syncSelectionsFromStorage() {
    const selectedIds = getStoredSelectedIds();
    document.querySelectorAll('.product-checkbox').forEach(function (checkbox) {
        checkbox.checked = selectedIds.includes(Number(checkbox.value));
    });
    updateBulkDeleteState();
}

if (document.getElementById('selectAllProducts')) {
    document.getElementById('selectAllProducts').addEventListener('change', function () {
        document.querySelectorAll('.product-checkbox').forEach(function (checkbox) {
            checkbox.checked = this.checked;
        }, this);

        const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(function (checkbox) {
            return Number(checkbox.value);
        });
        saveStoredSelectedIds(selectedIds);
        updateBulkDeleteState();
    });
}

document.querySelectorAll('.product-checkbox').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(function (item) {
            return Number(item.value);
        });
        saveStoredSelectedIds(selectedIds);
        updateBulkDeleteState();
    });
});

const bulkDeleteForm = document.getElementById('bulkDeleteForm');
if (bulkDeleteForm) {
    bulkDeleteForm.addEventListener('submit', function () {
        localStorage.removeItem(selectionStorageKey);
    });
}

syncSelectionsFromStorage();

// Live kalkulasi konversi stok per baris saat admin mengubah stok / strip / tablet
document.querySelectorAll('tr[data-product-row]').forEach(function (row) {
    const stockInput = row.querySelector('.js-row-stock');
    const stripsInput = row.querySelector('.js-row-strips');
    const tabletsInput = row.querySelector('.js-row-tablets');
    const unitLabel = row.dataset.unitLabel || 'Box';
    const boxEl = row.querySelector('[data-pill-box]');
    const stripWrap = row.querySelector('[data-pill-strip-wrap]');
    const stripEl = row.querySelector('[data-pill-strip]');
    const tabletWrap = row.querySelector('[data-pill-tablet-wrap]');
    const tabletEl = row.querySelector('[data-pill-tablet]');

    function updateRowStockPreview() {
        const stockBoxes = Math.max(0, parseFloat(stockInput?.value || '0') || 0);
        const stripsPerBox = Math.max(0, parseInt(stripsInput?.value || '0', 10) || 0);
        const tabletsPerStrip = Math.max(0, parseInt(tabletsInput?.value || '0', 10) || 0);
        const fmt = (n) => Number(n || 0).toLocaleString('id-ID');

        if (stripsPerBox > 0 && tabletsPerStrip > 0) {
            const unitsPerBox = stripsPerBox * tabletsPerStrip;
            const totalTablets = Math.round(stockBoxes * unitsPerBox);
            const totalStrips = Math.floor(totalTablets / tabletsPerStrip);
            const wholeBoxes = Math.floor(totalTablets / unitsPerBox);
            if (boxEl) boxEl.textContent = `${fmt(wholeBoxes)} ${unitLabel}`;
            if (stripWrap) stripWrap.style.display = 'flex';
            if (stripEl) stripEl.textContent = `${fmt(totalStrips)} Strip`;
            if (tabletWrap) tabletWrap.style.display = 'flex';
            if (tabletEl) tabletEl.textContent = `${fmt(totalTablets)} Tablet`;
        } else {
            const wholeBoxes = Math.floor(stockBoxes);
            if (boxEl) boxEl.textContent = `${fmt(wholeBoxes)} ${unitLabel}`;
            if (stripWrap) stripWrap.style.display = 'none';
            if (tabletWrap) tabletWrap.style.display = 'none';
        }
    }

    stockInput?.addEventListener('input', updateRowStockPreview);
    stripsInput?.addEventListener('input', updateRowStockPreview);
    tabletsInput?.addEventListener('input', updateRowStockPreview);
});

// Tutup modal jika klik overlay
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
@endsection
