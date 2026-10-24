@extends('layouts.admin')

@section('title', 'Manajemen Produk Resep - Admin Apotek Medix')
@section('page-title', 'Manajemen Produk Resep')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .page-header-left h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.25rem;
    }
    .page-header-left p {
        font-size: 0.85rem;
        color: #6b7280;
        margin: 0;
    }
    .page-header-actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }
    .btn-icon {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 1.1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-icon-primary { background: #dc2626; color: white; }
    .btn-icon-primary:hover { background: #b91c1c; color: white; transform: translateY(-1px); }
    .btn-icon-outline { background: white; color: #374151; border: 1px solid #d1d5db; }
    .btn-icon-outline:hover { background: #f9fafb; border-color: #9ca3af; color: #374151; }

    .search-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
    }
    .search-row { display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap; }
    .search-field { flex: 1; min-width: 180px; }
    .search-field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .search-input-wrap { position: relative; }
    .search-input-wrap i {
        position: absolute; left: 0.75rem; top: 50%;
        transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem;
    }
    .search-input-wrap input,
    .search-input-wrap select {
        width: 100%;
        padding: 0.55rem 0.75rem 0.55rem 2.1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        background: #fafafa;
        transition: all 0.2s;
        color: #1f2937;
    }
    .search-input-wrap select { padding-left: 0.75rem; }
    .search-input-wrap input:focus,
    .search-input-wrap select:focus {
        outline: none; border-color: #dc2626; background: white;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.08);
    }
    .search-actions { display: flex; gap: 0.5rem; align-items: flex-end; }
    .btn-search {
        padding: 0.55rem 1.25rem; background: #dc2626; color: white;
        border: none; border-radius: 0.5rem; font-size: 0.875rem;
        font-weight: 600; cursor: pointer; transition: background 0.2s;
        display: inline-flex; align-items: center; gap: 0.4rem;
    }
    .btn-search:hover { background: #b91c1c; }
    .btn-reset {
        padding: 0.55rem 0.9rem; background: white; color: #6b7280;
        border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem;
        font-weight: 600; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 0.3rem; transition: all 0.2s;
    }
    .btn-reset:hover { background: #f9fafb; color: #374151; }

    .data-table-wrap {
        background: white; border-radius: 0.75rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden;
    }
    .data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
    .data-table thead tr { background: #fff5f5; border-bottom: 2px solid #e5e7eb; }
    .data-table th {
        padding: 0.85rem 1rem; text-align: left; font-size: 0.75rem;
        font-weight: 700; color: #6b7280; text-transform: uppercase;
        letter-spacing: 0.05em; white-space: nowrap;
    }
    .data-table td {
        padding: 0.85rem 1rem; border-bottom: 1px solid #f3f4f6;
        color: #374151; vertical-align: middle;
    }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover { background: #fff8f8; }

    .med-img { width: 44px; height: 44px; border-radius: 0.5rem; object-fit: cover; border: 1px solid #e5e7eb; }
    .med-img-placeholder {
        width: 44px; height: 44px; border-radius: 0.5rem;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; border: 1px solid #e5e7eb;
    }
    .med-name { font-weight: 600; color: #1f2937; }

    .stock-badge {
        display: inline-flex; align-items: center;
        padding: 0.25rem 0.65rem; border-radius: 20px;
        font-size: 0.78rem; font-weight: 700;
    }
    .stock-ok    { background: #d1fae5; color: #065f46; }
    .stock-low   { background: #fef3c7; color: #92400e; }
    .stock-empty { background: #fee2e2; color: #991b1b; }

    .price-text { font-weight: 600; color: #dc2626; }

    .action-wrap { display: flex; gap: 0.4rem; }
    .btn-edit, .btn-del {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.35rem 0.75rem; border-radius: 0.4rem; font-size: 0.78rem;
        font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s;
    }
    .btn-edit { background: #fee2e2; color: #b91c1c; }
    .btn-edit:hover { background: #dc2626; color: white; }
    .btn-del  { background: #f3f4f6; color: #6b7280; }
    .btn-del:hover  { background: #ef4444; color: white; }

    .pagination-wrap {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-top: 1px solid #f3f4f6;
        flex-wrap: wrap; gap: 0.75rem;
    }
    .pagination-info { font-size: 0.8rem; color: #6b7280; }
    .pagination-pages { display: flex; gap: 0.3rem; }
    .page-btn {
        min-width: 32px; height: 32px; display: inline-flex; align-items: center;
        justify-content: center; border-radius: 0.4rem; font-size: 0.8rem; font-weight: 600;
        text-decoration: none; border: 1px solid #e5e7eb; color: #374151;
        background: white; transition: all 0.2s; padding: 0 0.5rem;
    }
    .page-btn:hover { background: #dc2626; color: white; border-color: #dc2626; }
    .page-btn.active { background: #dc2626; color: white; border-color: #dc2626; }
    .page-btn.disabled { background: #f9fafb; color: #d1d5db; cursor: not-allowed; pointer-events: none; }

    .empty-state {
        padding: 4rem 2rem; text-align: center; background: white;
        border-radius: 0.75rem; border: 1px solid #f0f0f0;
    }
    .empty-icon { font-size: 3rem; margin-bottom: 1rem; }
    .empty-state h3 { font-size: 1rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; }
    .empty-state p { font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem; }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; }
        .data-table-wrap { overflow-x: auto; }
        .data-table { min-width: 600px; }
        .search-row { flex-direction: column; }
        .search-field { min-width: 100%; }
    }
</style>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h2><i class="fa-solid fa-file-prescription" style="color:#dc2626;margin-right:0.4rem;"></i>Produk Resep</h2>
        <p>Total <strong>{{ $medicines->total() }}</strong> produk resep terdaftar
            @if($search || $kategori)
                &mdash; <span style="color:#dc2626;">hasil filter aktif</span>
            @endif
        </p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('admin.prescription-products.import') }}" class="btn-icon btn-icon-outline">
            <i class="fa-solid fa-file-import"></i> Import Excel
        </a>
        <a href="{{ route('admin.prescription-products.create') }}" class="btn-icon btn-icon-primary">
            <i class="fa-solid fa-plus"></i> Tambah Produk
        </a>
    </div>
</div>

<div class="search-card">
    <form method="GET" action="{{ route('admin.prescription-products.index') }}">
        <div class="search-row">
            <div class="search-field">
                <label>Cari Produk</label>
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama produk, deskripsi...">
                </div>
            </div>
            <div class="search-field" style="max-width:220px;">
                <label>Perusahaan</label>
                <div class="search-input-wrap">
                    <select name="kategori">
                        <option value="">Semua Perusahaan</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $kategori === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="search-actions">
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                @if($search || $kategori)
                    <a href="{{ route('admin.prescription-products.index') }}" class="btn-reset">
                        <i class="fa-solid fa-xmark"></i> Reset
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:0.5rem;padding:0.85rem 1rem;margin-bottom:1rem;color:#065f46;font-size:0.875rem;display:flex;align-items:center;gap:0.5rem;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif

@if($medicines->count() > 0)
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:56px;">Foto</th>
                    <th>Nama Produk</th>
                    <th>Sediaan</th>
                    <th>Perusahaan</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Ditambahkan</th>
                    <th style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicines as $medicine)
                <tr>
                    <td>
                        @if($medicine->gambar)
                            <img src="{{ url('storage/' . $medicine->gambar) }}"
                                 alt="{{ $medicine->nama_obat }}" class="med-img">
                        @else
                            <div class="med-img-placeholder">💊</div>
                        @endif
                    </td>
                    <td>
                        <div class="med-name">{{ $medicine->nama_obat }}</div>
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
                        <span style="font-size:0.82rem;color:#6b7280;">{{ $medicine->kategori }}</span>
                    </td>
                    <td>
                        <span class="price-text">Rp {{ number_format($medicine->harga, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        @if($medicine->stok > 10)
                            <span class="stock-badge stock-ok">{{ $medicine->stok }}</span>
                        @elseif($medicine->stok > 0)
                            <span class="stock-badge stock-low">{{ $medicine->stok }}</span>
                        @else
                            <span class="stock-badge stock-empty">Habis</span>
                        @endif
                    </td>
                    <td style="font-size:0.82rem;color:#9ca3af;">
                        {{ $medicine->created_at->format('d M Y') }}
                    </td>
                    <td>
                        <div class="action-wrap">
                            <a href="{{ route('admin.prescription-products.edit', ['product' => $medicine->id, 'search' => $search, 'kategori' => $kategori, 'page' => request('page')]) }}" class="btn-edit">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            <form action="{{ route('admin.prescription-products.destroy', ['product' => $medicine->id, 'search' => $search, 'kategori' => $kategori, 'page' => request('page')]) }}" method="POST"
                                  onsubmit="return confirm('Hapus produk resep ini?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="search" value="{{ $search }}">
                                <input type="hidden" name="kategori" value="{{ $kategori }}">
                                <input type="hidden" name="page" value="{{ request('page') }}">
                                <button type="submit" class="btn-del">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrap">
            <div class="pagination-info">
                Menampilkan {{ $medicines->firstItem() }}–{{ $medicines->lastItem() }} dari {{ $medicines->total() }} produk
            </div>
            <div class="pagination-pages">
                @if($medicines->onFirstPage())
                    <span class="page-btn disabled">‹</span>
                @else
                    <a href="{{ $medicines->previousPageUrl() }}" class="page-btn">‹</a>
                @endif

                @foreach($medicines->getUrlRange(1, $medicines->lastPage()) as $page => $url)
                    @if(abs($page - $medicines->currentPage()) <= 2 || $page == 1 || $page == $medicines->lastPage())
                        @if($page == $medicines->currentPage())
                            <span class="page-btn active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                        @endif
                    @elseif(abs($page - $medicines->currentPage()) == 3)
                        <span class="page-btn disabled" style="border:none;background:none;">…</span>
                    @endif
                @endforeach

                @if($medicines->hasMorePages())
                    <a href="{{ $medicines->nextPageUrl() }}" class="page-btn">›</a>
                @else
                    <span class="page-btn disabled">›</span>
                @endif
            </div>
        </div>
    </div>

@else
    <div class="empty-state">
        @if($search || $kategori)
            <div class="empty-icon">🔍</div>
            <h3>Tidak ada hasil</h3>
            <p>Tidak ada produk yang cocok dengan <strong>"{{ $search ?: $kategori }}"</strong>.</p>
            <a href="{{ route('admin.prescription-products.index') }}" class="btn-icon btn-icon-outline">
                <i class="fa-solid fa-xmark"></i> Hapus Filter
            </a>
        @else
            <div class="empty-icon">💊</div>
            <h3>Belum ada produk resep</h3>
            <p>Mulai tambahkan produk resep atau import dari file Excel/CSV.</p>
            <div style="display:flex;gap:0.6rem;justify-content:center;flex-wrap:wrap;">
                <a href="{{ route('admin.prescription-products.import') }}" class="btn-icon btn-icon-outline">
                    <i class="fa-solid fa-file-import"></i> Import Excel
                </a>
                <a href="{{ route('admin.prescription-products.create') }}" class="btn-icon btn-icon-primary">
                    <i class="fa-solid fa-plus"></i> Tambah Produk
                </a>
            </div>
        @endif
    </div>
@endif

@endsection

