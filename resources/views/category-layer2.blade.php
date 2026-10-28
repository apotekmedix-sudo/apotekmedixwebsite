@extends('layouts.frontend')
@section('title', 'Kategori ' . ucfirst($mainCategory) . ' - ' . ucfirst($subCategory) . ' - Apotek Medix')

@section('styles')
<style>
/* ===== PAGE HEADER ===== */
.category-page-header {
    background: linear-gradient(135deg, #0D47A1 0%, #1565C0 50%, #1E88E5 100%);
    padding: 2rem 0 1rem;
    border-bottom: 1px solid #dbeafe;
    position: relative;
    overflow: hidden;
}

.category-page-header::before,
.category-page-header::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.category-page-header::before {
    width: 220px;
    height: 220px;
    background: rgba(124, 179, 66, 0.16);
    top: -70px;
    right: -40px;
}

.category-page-header::after {
    width: 160px;
    height: 160px;
    background: rgba(255, 255, 255, 0.12);
    bottom: -50px;
    left: -30px;
}

.category-page-header h1 {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 800;
    color: #fff;
    margin-bottom: 0.4rem;
    position: relative;
}

.category-page-header p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
    position: relative;
    margin: 0;
}

.breadcrumb-custom {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 0.75rem;
    position: relative;
}

.breadcrumb-custom a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.breadcrumb-custom a:hover {
    color: #fff;
}

.breadcrumb-custom span {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.85rem;
}

.breadcrumb-custom .current {
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
}

/* ===== SEARCH & FILTER BAR ===== */
.search-filter-section {
    background: linear-gradient(180deg, #f8fbff 0%, #f3f8ff 100%);
    padding: 1.5rem 0;
    border-bottom: 1px solid #dbeafe;
    position: sticky;
    top: var(--navbar-height, 65px);
    z-index: 900;
    box-shadow: 0 2px 10px rgba(21, 101, 192, 0.06);
}

.search-filter-row {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-box-category {
    flex: 1;
    min-width: 250px;
    display: flex;
    gap: 0.5rem;
    background: #fff;
    border-radius: 14px;
    padding: 0.5rem;
    border: 1.5px solid #cfe6ff;
    box-shadow: 0 6px 16px rgba(30, 136, 229, 0.08);
}

.search-box-category input {
    flex: 1;
    border: none;
    outline: none;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
    color: #374151;
    background: transparent;
}

.search-box-category button {
    padding: 0.55rem 1.2rem;
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    color: #fff;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.search-box-category button:hover {
    background: linear-gradient(135deg, #1565C0, #0D47A1);
}

.cart-btn-header {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    color: #fff;
    padding: 0.65rem 1.25rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    position: relative;
    box-shadow: 0 8px 18px rgba(30, 136, 229, 0.16);
}

.cart-btn-header:hover {
    background: linear-gradient(135deg, #1565C0, #0D47A1);
    transform: translateY(-2px);
}

.cart-badge-header {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background: #ef4444;
    border-radius: 50%;
    font-size: 0.7rem;
    font-weight: 800;
}

/* ===== MAIN LAYOUT ===== */
.category-main-wrap {
    padding: 2rem 0;
}

.category-content {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 1.5rem;
}

/* ===== SIDEBAR ===== */
.category-sidebar {
    background: linear-gradient(180deg, #fefefe 0%, #f8fbff 100%);
    border-radius: 16px;
    padding: 1.5rem;
    border: 1.5px solid #dbeafe;
    height: fit-content;
    box-shadow: 0 8px 24px rgba(21, 101, 192, 0.06);
}

.sidebar-section {
    margin-bottom: 1.5rem;
}

.sidebar-section:last-child {
    margin-bottom: 0;
}

.sidebar-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sidebar-title i {
    color: #ffa500;
    font-size: 1.1rem;
}

.sidebar-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.65rem 0;
    color: #374151;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
    cursor: pointer;
    border-left: 3px solid transparent;
    padding-left: 0.75rem;
}

.sidebar-item:hover {
    color: #1E88E5;
    border-left-color: #1E88E5;
    padding-left: 1rem;
}

.sidebar-item.active {
    background: #f0f9ff;
    background: linear-gradient(90deg, #e3f2fd 0%, #f0f9ff 100%);
    color: #1565C0;
    border-left-color: #7CB342;
    font-weight: 700;
    padding-left: 1rem;
}

.sidebar-item i {
    font-size: 0.95rem;
    flex-shrink: 0;
}

/* ===== PRODUCT GRID ===== */
.products-wrapper {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.25rem;
}

.product-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    border: 1.5px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
}

.product-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 12px 40px rgba(30, 136, 229, 0.12);
    border-color: #90caf9;
}

.product-img {
    width: 100%;
    height: 160px;
    background: linear-gradient(135deg, #eef7ff, #dfeeff);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    min-height: 180px;
}

.product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.product-card:hover .product-img img {
    transform: scale(1.08);
}

.product-img .no-img-icon {
    font-size: 2.5rem;
    color: #90caf9;
}

.product-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: linear-gradient(135deg, #1E88E5, #7CB342);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
}

.product-body {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.product-desc { color: #374151; font-size: 0.88rem; margin: 0 0 0.5rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.product-meta { font-size: 0.78rem; color: #6b7280; margin-bottom: 0.45rem; }

.product-brand {
    font-size: 0.7rem;
    font-weight: 700;
    color: #1565C0;
    background: #e3f2fd;
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    margin-bottom: 0.6rem;
}

.product-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.6rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.3;
    flex: 1;
}

.product-price {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0D47A1;
    margin-bottom: 0.6rem;
}

.product-stock {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 0.8rem;
}

.stock-ok {
    background: #d1fae5;
    color: #065f46;
}

.stock-low {
    background: #fef3c7;
    color: #92400e;
}

.stock-out {
    background: #fee2e2;
    color: #7f1d1d;
}

.product-actions {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.7rem;
}

.btn-detail,
.btn-add-cart {
    width: 100%;
}

.btn-detail {
    display: block;
    padding: 0.75rem 0.85rem;
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    color: #fff;
    border: none;
    border-radius: 9px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.82rem;
    text-align: center;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-detail:hover {
    background: linear-gradient(135deg, #1565C0, #0D47A1);
    transform: translateY(-2px);
}

.btn-add-cart {
    display: block;
    padding: 0.75rem;
    background: #fff;
    color: #1E88E5;
    border: 1.5px solid #1E88E5;
    border-radius: 9px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.82rem;
    text-align: center;
    transition: all 0.2s;
}

.btn-add-cart:hover {
    background: #f0f9ff;
}

/* ===== PAGINATION ===== */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
    margin-top: 2rem;
    padding: 0;
    list-style: none;
}

.pagination .page-item {
    display: inline-flex;
}

.pagination .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    padding: 0 0.65rem;
    border-radius: 999px;
    border: 1px solid #dbeafe;
    background: #fff;
    color: #1565C0;
    font-size: 0.82rem;
    line-height: 1;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination .page-link:hover {
    background: #e3f2fd;
    border-color: #90caf9;
    color: #0D47A1;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    border-color: #1565C0;
    color: #fff;
    box-shadow: 0 6px 16px rgba(30, 136, 229, 0.2);
}

.pagination .page-item.disabled .page-link {
    background: #f8fafc;
    color: #9ca3af;
    border-color: #e5e7eb;
    cursor: not-allowed;
    box-shadow: none;
}

.pagination .page-link svg,
.pagination .page-link i {
    font-size: 0.8rem;
    width: 0.8rem;
    height: 0.8rem;
    line-height: 1;
}

/* ===== EMPTY STATE ===== */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 2rem;
    background: #fff;
    border-radius: 14px;
    border: 1.5px solid #e5e7eb;
}

.empty-state i {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #9ca3af;
    font-size: 0.9rem;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .category-content {
        grid-template-columns: 1fr;
    }

    .category-sidebar {
        order: 2;
    }

    .products-wrapper {
        order: 1;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .search-filter-row {
        flex-direction: column;
    }

    .search-box-category {
        width: 100%;
    }

    .cart-btn-header {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .products-wrapper {
        grid-template-columns: 1fr;
    }

    .product-card {
        border-radius: 12px;
    }

    .product-img {
        height: 150px;
        min-height: 150px;
    }

    .product-body {
        padding: 0.85rem;
    }

    .product-actions {
        gap: 0.55rem;
    }

    .category-page-header h1 {
        font-size: 1.25rem;
    }

    .category-main-wrap {
        padding: 1rem 0;
    }

    .search-filter-section {
        padding: 1rem 0;
    }
}
</style>
@endsection

@section('scripts')
<script>window.cartSettings = { storageKey: 'apotekmedix_cart_retail' };</script>
@include('partials.cart')
@endsection

@section('content')

{{-- PAGE HEADER --}}
<div class="category-page-header">
    <div class="container">
        <div class="breadcrumb-custom">
            <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Home</a>
            <span>/</span>
            <a href="{{ route('products.index') }}">Katalog</a>
            <span>/</span>
            <span class="current">{{ ucfirst($mainCategory) }}</span>
        </div>
        <h1><i class="fa-solid fa-list"></i> {{ ucfirst($mainCategory) }} - {{ ucfirst(str_replace('-', ' ', $subCategory)) }}</h1>
        <p>Temukan produk pilihan sesuai kebutuhan Anda</p>
    </div>
</div>

{{-- SEARCH & FILTER BAR --}}
<div class="search-filter-section">
    <div class="container">
        <div class="search-filter-row">
            <form method="GET" style="flex:1;width:100%;">
                <div class="search-box-category" style="max-width:none;width:100%;">
                    <input type="hidden" name="main" value="{{ $mainCategory }}">
                    <input type="hidden" name="sub" value="{{ $subCategory }}">
                    <input type="text" name="search" placeholder="Cari obat, deskripsi, indikasi, komposisi, sediaan (sirup/tablet), dosis, golongan..." value="{{ request('search') }}">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
                @include('partials.product-attribute-filters', ['resetUrl' => route('category.layer2', ['main' => $mainCategory, 'sub' => $subCategory])])
            </form>
            <button class="cart-btn-header" onclick="if(typeof openCart==='function'){openCart();}">
                <i class="fa-solid fa-cart-shopping"></i> Keranjang
                <span class="cart-badge-header" id="cartBadgeHeader">0</span>
            </button>
        </div>
    </div>
</div>

{{-- MAIN CONTENT --}}
<div class="category-main-wrap">
    <div class="container">
        <div class="category-content">
            {{-- SIDEBAR --}}
            <div class="category-sidebar">
                <div class="sidebar-section">
                    <div class="sidebar-title">
                        <i class="fa-solid fa-filter"></i> Kategori
                    </div>
                    <a href="{{ route('category.layer2', ['main' => 'apotik', 'sub' => 'oral']) }}" 
                       class="sidebar-item {{ $mainCategory === 'apotik' && $subCategory === 'oral' ? 'active' : '' }}">
                        <i class="fa-solid fa-pills"></i>
                        <span>Obat Oral</span>
                    </a>
                    <a href="{{ route('category.layer2', ['main' => 'apotik', 'sub' => 'injeksi']) }}"
                       class="sidebar-item {{ $mainCategory === 'apotik' && $subCategory === 'injeksi' ? 'active' : '' }}">
                        <i class="fa-solid fa-syringe"></i>
                        <span>Obat Injeksi</span>
                    </a>
                    <a href="{{ route('category.layer2', ['main' => 'apotik', 'sub' => 'luar']) }}"
                       class="sidebar-item {{ $mainCategory === 'apotik' && $subCategory === 'luar' ? 'active' : '' }}">
                        <i class="fa-solid fa-bottle-droplet"></i>
                        <span>Obat Luar</span>
                    </a>
                    <a href="{{ route('category.layer2', ['main' => 'apotik', 'sub' => 'otc']) }}"
                       class="sidebar-item {{ $mainCategory === 'apotik' && $subCategory === 'otc' ? 'active' : '' }}">
                        <i class="fa-solid fa-tablets"></i>
                        <span>Obat OTC</span>
                    </a>
                    <a href="{{ route('category.layer2', ['main' => 'apotik', 'sub' => 'susu']) }}"
                       class="sidebar-item {{ $mainCategory === 'apotik' && $subCategory === 'susu' ? 'active' : '' }}">
                        <i class="fa-solid fa-bottle-droplet"></i>
                        <span>Susu</span>
                    </a>
                    <a href="{{ route('category.layer2', ['main' => 'apotik', 'sub' => 'suplemen']) }}"
                       class="sidebar-item {{ $mainCategory === 'apotik' && $subCategory === 'suplemen' ? 'active' : '' }}">
                        <i class="fa-solid fa-heart"></i>
                        <span>Suplemen</span>
                    </a>
                    <a href="{{ route('category.layer2', ['main' => 'apotik', 'sub' => 'herbal']) }}"
                       class="sidebar-item {{ $mainCategory === 'apotik' && $subCategory === 'herbal' ? 'active' : '' }}">
                        <i class="fa-solid fa-leaf"></i>
                        <span>Herbal</span>
                    </a>
                    <a href="{{ route('category.layer2', ['main' => 'kecantikan', 'sub' => 'skincare']) }}"
                       class="sidebar-item {{ $mainCategory === 'kecantikan' && $subCategory === 'skincare' ? 'active' : '' }}">
                        <i class="fa-solid fa-droplet"></i>
                        <span>Skincare</span>
                    </a>
                    <a href="{{ route('category.layer2', ['main' => 'alkes', 'sub' => 'ortopedi']) }}"
                       class="sidebar-item {{ $mainCategory === 'alkes' && $subCategory === 'ortopedi' ? 'active' : '' }}">
                        <i class="fa-solid fa-bone"></i>
                        <span>Alkes Ortopedi</span>
                    </a>
                </div>
            </div>

            {{-- PRODUCTS --}}
            <div>
                @if($medicines->count() > 0)
                    <div class="products-wrapper">
                        @foreach($medicines as $med)
                        <div class="product-card">
                            <div class="product-img">
                                @include('partials.product-gallery-image', ['medicine' => $med])
                                @include('partials.product-logo-marker', ['medicine' => $med])
                                @if($med->kategori_produk)
                                    <span class="product-badge">{{ strtoupper(substr($med->kategori_produk, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="product-body">
                                
                                @if($med->kategori)
                                    <span class="product-brand">{{ $med->kategori }}</span>
                                @endif
                                <h3 class="product-name"><a href="{{ route('medicines.show', ['id' => $med->id, 'from' => 'products']) }}">{{ $med->nama_obat }}</a></h3>
                                
                                @php $stock = $med->available_stock_units; @endphp
                                <span class="product-stock {{ $stock <= 0 ? 'empty' : ($stock <= 10 ? 'low' : 'available') }}" data-stock-badge data-stock-product-id="{{ $med->id }}">
                                    <i class="fa-solid {{ $stock <= 0 ? 'fa-circle-xmark' : 'fa-boxes-stacked' }}"></i>
                                    {{ $stock > 0 ? 'Stok: ' . $med->stock_breakdown : 'Stok habis' }}
                                </span>
                                @if($med->sediaan_label)
                                    <div style="font-size:0.75rem;color:#6b7280;margin-bottom:0.35rem;display:flex;align-items:center;gap:0.35rem;">
                                      <i class="fa-solid fa-cube"></i> <span>{{ $med->sediaan_label }}</span>
                                    </div>
                                @endif
                                <div class="product-actions">
                                    <a href="{{ route('medicines.show', ['id' => $med->id, 'from' => 'products']) }}" class="btn-detail">Lihat Detail</a>
                                    @include('partials.product-unit-selector', ['medicine' => $med, 'unitSelectId' => 'category-unit-' . $med->id, 'priceTargetId' => 'category-price-' . $med->id])
                                    <button class="btn-add-cart" data-stock-cart @disabled($stock <= 0) data-package-unit-label="{{ $med->package_unit_label }}" onclick="addToCart({{ $med->id }}, '{{ addslashes($med->nama_obat) }}', {{ $med->harga_retail ?: $med->harga }}, '{{ $med->image_url ?: '' }}', '{{ addslashes($med->brand ?: $med->kategori) }}', this, 'category-unit-{{ $med->id }}')">
                                        <i class="fa-solid fa-cart-plus"></i> Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    {{-- PAGINATION --}}
                    <div style="margin-top: 2rem; text-align: center;">
                        {{ $medicines->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <h3>Tidak ada produk</h3>
                        <p>Maaf, produk yang Anda cari tidak tersedia saat ini</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
