@extends('layouts.admin')

@section('title', 'Produk Pemesanan PBF - Admin Apotek Medix')
@section('page-title', '📦 Produk Pemesanan PBF')

@section('styles')
<style>
    .ordering-head { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .ordering-head h2 { margin:0 0 .25rem; color:#1f2937; font-size:1.15rem; }
    .ordering-head p { margin:0; color:#6b7280; font-size:.85rem; }
    .ordering-search { display:flex; gap:.55rem; max-width:430px; width:100%; }
    .ordering-search input { flex:1; min-width:0; padding:.65rem .8rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.88rem; }
    .ordering-search button { border:0; border-radius:.5rem; padding:.65rem 1rem; background:#1e88e5; color:white; font-weight:700; cursor:pointer; }
    .ordering-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:1rem; }
    .ordering-card { background:white; border:1px solid #e5e7eb; border-radius:.75rem; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); display:flex; flex-direction:column; }
    .ordering-image { height:145px; background:linear-gradient(135deg,#e3f2fd,#f3f8fc); display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .ordering-image img { width:100%; height:100%; object-fit:cover; }
    .ordering-image i { font-size:2.8rem; color:#90caf9; }
    .ordering-body { padding:.85rem; display:flex; flex:1; flex-direction:column; }
    .ordering-principle { color:#6b7280; font-size:.72rem; font-weight:700; text-transform:uppercase; }
    .ordering-name { margin:.25rem 0 .5rem; font-size:.92rem; color:#1f2937; line-height:1.3; }
    .ordering-price { color:#1e88e5; font-size:1.05rem; font-weight:800; margin-top:auto; }
    .ordering-button { border:0; border-radius:.5rem; padding:.6rem; background:#25d366; color:white; font-weight:700; cursor:pointer; width:100%; }
    .ordering-empty { padding:4rem 1rem; text-align:center; color:#6b7280; background:white; border-radius:.75rem; border:1px solid #e5e7eb; }
    .ordering-pages { margin-top:1.25rem; display:flex; justify-content:center; gap:.35rem; }
    .ordering-pages a, .ordering-pages span { min-width:32px; padding:.45rem .65rem; text-align:center; border:1px solid #e5e7eb; border-radius:.4rem; text-decoration:none; color:#374151; background:white; }
    .ordering-pages .active { background:#1e88e5; color:white; border-color:#1e88e5; }
    .ordering-cart-button { position:fixed; right:22px; bottom:22px; z-index:2100; display:inline-flex; align-items:center; gap:.4rem; border:0; border-radius:999px; padding:.65rem .85rem; background:#1e88e5; color:white; box-shadow:0 6px 18px rgba(30,136,229,.35); font-weight:700; cursor:pointer; font-size:.82rem; white-space:nowrap; }
    .ordering-cart-button .cart-badge { position:static; display:inline-flex; align-items:center; justify-content:center; flex:0 0 20px; width:20px; height:20px; margin:0; padding:0; border-radius:50% !important; background:#ef4444; color:#fff; font-size:.7rem; line-height:20px; }
    @media(max-width:520px) { .ordering-search { max-width:none; } .ordering-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem; } .ordering-image { height:115px; } .ordering-body { padding:.65rem; } .ordering-name { font-size:.8rem; } }
</style>
@endsection

@section('content')
<div class="ordering-head" style="flex-direction:column;align-items:stretch;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;flex-wrap:wrap;width:100%;">
        <div><h2>Produk Pemesanan</h2><p>{{ $total }} produk dengan harga modal untuk pemesanan PBF</p></div>
    </div>
    <form method="GET" action="{{ route('admin.produk-pbf.index') }}" style="background:white;padding:1rem;border-radius:.75rem;border:1px solid #e5e7eb;width:100%;">
        <div class="ordering-search" style="max-width:none;">
            <input name="search" value="{{ $search }}" placeholder="Cari nama produk, principle, indikasi, komposisi, sediaan (sirup/tablet), dosis, atau golongan..."><button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        </div>
        @include('partials.product-attribute-filters', ['resetUrl' => route('admin.produk-pbf.index')])
    </form>
</div>
@if($medicines->count())
    <div class="ordering-grid">
        @foreach($medicines as $medicine)
            <article class="ordering-card">
                <div class="ordering-image">@if($medicine->image_url)<img src="{{ $medicine->image_url }}" alt="{{ $medicine->nama_obat }}">@else<i class="fa-solid fa-pills"></i>@endif</div>
                <div class="ordering-body">
                    <span class="ordering-principle">{{ $medicine->brand ?: $medicine->kategori ?: 'Produk' }}</span>
                    <h3 class="ordering-name">{{ $medicine->nama_obat }}</h3>
                    <div class="ordering-price">{{ $medicine->getFormattedCatalogPrice('harga_modal') }}</div>
                    <button class="ordering-button" data-package-unit-label="{{ $medicine->package_unit_label }}" onclick="addToCart({{ $medicine->id }}, '{{ addslashes($medicine->nama_obat) }}', {{ $medicine->harga_modal }}, '{{ $medicine->image_url ?: '' }}', '{{ addslashes($medicine->brand ?: $medicine->kategori) }}', this)"><i class="fa-solid fa-cart-plus"></i> Tambah Pesanan</button>
                </div>
            </article>
        @endforeach
    </div>
    <div class="ordering-pages">
        @if($medicines->onFirstPage())<span>Previous</span>@else<a href="{{ $medicines->previousPageUrl() }}">Previous</a>@endif
        @foreach($medicines->getUrlRange(1, $medicines->lastPage()) as $page => $url)
            @if($page === 1 || $page === $medicines->lastPage() || abs($page - $medicines->currentPage()) <= 2)
                @if($page === $medicines->currentPage())<span class="active">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
            @elseif($page === 2 || $page === $medicines->lastPage() - 1)
                <span>...</span>
            @endif
        @endforeach
        @if($medicines->hasMorePages())<a href="{{ $medicines->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif
    </div>
@else
    <div class="ordering-empty"><i class="fa-solid fa-box-open" style="font-size:2.5rem"></i><p>Belum ada produk dengan harga modal.</p></div>
@endif
<button class="ordering-cart-button" onclick="openCart()"><i class="fa-solid fa-cart-shopping"></i> Keranjang <span class="cart-badge" id="adminCartBadge">0</span></button>
@endsection

@section('scripts')
<script>window.cartSettings = { storageKey: 'apotekmedix_cart_pbf_admin', receiptStoreName: 'APOTEK MEDIX', receiptStoreAddress: 'ITC Cempaka Mas LT.1 No.88, Jakarta Pusat', receiptFilePrefix: 'surat-pesanan-pbf', adminPbfOrder: true, adminPurchaseOrderBase: '{{ str_replace('/0/', '/__ORDER__/', route('admin.purchase-history.purchase-order', ['order' => 0])) }}' };</script>
@include('partials.cart')
@endsection
