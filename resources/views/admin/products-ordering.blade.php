@extends('layouts.admin')

@section('title', 'Pesan ke PBF - Admin Apotek Medix')
@section('page-title', '📦 Pesan ke PBF')

@section('styles')
<style>
    .ordering-head { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .ordering-head h2 { margin:0 0 .25rem; color:#1f2937; font-size:1.15rem; }
    .ordering-head p { margin:0; color:#6b7280; font-size:.85rem; }
    .supplier-manage-toggle { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; border:1px solid #bfdbfe; border-radius:.5rem; padding:.62rem .85rem; background:#eff6ff; color:#1d4ed8; font-weight:700; cursor:pointer; text-decoration:none; white-space:nowrap; transition:background .2s,border-color .2s; }
    .supplier-manage-toggle:hover { background:#dbeafe; border-color:#93c5fd; }
    .supplier-notice { margin:0 0 1rem; padding:.7rem .85rem; border:1px solid #bfdbfe; border-left:4px solid #1e88e5; border-radius:.5rem; background:#eff6ff; color:#1e3a8a; font-size:.85rem; }
    .supplier-errors { margin:0 0 1rem; padding:.7rem .85rem .7rem 2rem; border-radius:.5rem; background:#fee2e2; color:#991b1b; font-size:.85rem; }
    @media (max-width: 760px) { .ordering-filter-form { grid-template-columns:1fr !important; } .ordering-filter-form .supplier-manage-toggle { justify-content:center; } }
    .ordering-search { display:flex; gap:.55rem; max-width:430px; width:100%; }
    .ordering-search input { flex:1; min-width:0; padding:.65rem .8rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.88rem; }
    .ordering-search button { border:0; border-radius:.5rem; padding:.65rem 1rem; background:#1e88e5; color:white; font-weight:700; cursor:pointer; }
    .ordering-filter-form { grid-template-columns:minmax(230px,.9fr) minmax(0,1.6fr) auto; }
    .ordering-filter-form > * { min-width:0; }
    .ordering-filter-form select { width:100%; min-width:0; }
    .ordering-filter-form .ordering-search { max-width:none; min-width:0; }
    .ordering-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:1rem; }
    .ordering-card { background:white; border:1px solid #e5e7eb; border-radius:.75rem; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); display:flex; flex-direction:column; }
    .ordering-image { position:relative; height:145px; background:linear-gradient(135deg,#e3f2fd,#f3f8fc); display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .ordering-image img { width:100%; height:100%; object-fit:cover; }
    .ordering-image i { font-size:2.8rem; color:#90caf9; }
    .ordering-image .product-logo-marker { position:absolute; z-index:2; top:.55rem; right:.55rem; width:1.35rem; height:1.35rem; border:2px solid #0f172a; border-radius:50%; box-shadow:0 1px 3px rgba(15,23,42,.25); }
    .ordering-image .product-logo-marker--green { background:#22a447; }
    .ordering-image .product-logo-marker--red { display:inline-flex; align-items:center; justify-content:center; background:#dc2626; color:#0f172a; font-size:.72rem; font-weight:900; line-height:1; }
    .ordering-image .product-logo-marker--blue { background:#2563eb; }
    .ordering-body { padding:.85rem; display:flex; flex:1; flex-direction:column; }
    .ordering-principle { color:#6b7280; font-size:.72rem; font-weight:700; text-transform:uppercase; }
    .ordering-name { margin:.25rem 0 .5rem; font-size:.92rem; color:#1f2937; line-height:1.3; }
    .ordering-meta { display:grid; gap:.2rem; margin:0 0 .6rem; color:#64748b; font-size:.73rem; line-height:1.4; }
    .ordering-meta p { margin:0; overflow-wrap:anywhere; }
    .ordering-meta strong { color:#334155; }
    .ordering-price { color:#1e88e5; font-size:1.05rem; font-weight:800; margin-top:auto; }
    .ordering-button { border:0; border-radius:.5rem; padding:.6rem; background:#25d366; color:white; font-weight:700; cursor:pointer; width:100%; }
    .ordering-readonly { margin-top:.75rem; padding:.55rem; border:1px solid #cbd5e1; border-radius:.5rem; background:#f8fafc; color:#64748b; font-size:.78rem; font-weight:700; text-align:center; }
    .ordering-empty { padding:4rem 1rem; text-align:center; color:#6b7280; background:white; border-radius:.75rem; border:1px solid #e5e7eb; }
    .ordering-pages { margin-top:1.25rem; display:flex; justify-content:center; gap:.35rem; }
    .ordering-pages a, .ordering-pages span { min-width:32px; padding:.45rem .65rem; text-align:center; border:1px solid #e5e7eb; border-radius:.4rem; text-decoration:none; color:#374151; background:white; }
    .ordering-pages .active { background:#1e88e5; color:white; border-color:#1e88e5; }
    .ordering-cart-button { position:fixed; right:22px; bottom:22px; z-index:2100; display:inline-flex; align-items:center; gap:.4rem; border:0; border-radius:999px; padding:.65rem .85rem; background:#1e88e5; color:white; box-shadow:0 6px 18px rgba(30,136,229,.35); font-weight:700; cursor:pointer; font-size:.82rem; white-space:nowrap; }
    .ordering-cart-button .cart-badge { position:static; display:inline-flex; align-items:center; justify-content:center; flex:0 0 20px; width:20px; height:20px; margin:0; padding:0; border-radius:50% !important; background:#ef4444; color:#fff; font-size:.7rem; line-height:20px; }
    @media(max-width:700px) { .ordering-search { max-width:none; } }
    @media(max-width:520px) { .ordering-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem; } .ordering-image { height:115px; } .ordering-body { padding:.65rem; } .ordering-name { font-size:.8rem; } }
</style>
@endsection

@section('content')
<div class="ordering-head" style="flex-direction:column;align-items:stretch;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;flex-wrap:wrap;width:100%;">
        <div><h2>Pesan ke PBF</h2><p>{{ $total }} harga produk tersedia{{ $selectedSupplier ? ' dari ' . $selectedSupplier->name . ' untuk pengadaan Apotek Medix' : ' dari seluruh supplier' }}. Pilih supplier tertentu untuk membuat SP.</p></div>
    </div>
    <form method="GET" action="{{ route('admin.produk-pbf.index') }}" class="ordering-filter-form" style="display:grid;align-items:end;gap:.7rem;background:white;padding:1rem;border-radius:.75rem;border:1px solid #e5e7eb;width:100%;">
        <label style="display:grid;gap:.3rem;color:#475569;font-size:.78rem;font-weight:700;">Harga supplier
            <select name="supplier_id" onchange="this.form.requestSubmit()" style="min-height:42px;padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:.5rem;color:#1e293b;font:inherit;">
                <option value="">Semua Produk — semua harga supplier</option>
                @foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected($selectedSupplier?->id === $supplier->id)>{{ $supplier->name }}</option>@endforeach
            </select>
        </label>
        <div class="ordering-search" style="max-width:none;">
            <input name="search" value="{{ $search }}" placeholder="Cari nama, indikasi, komposisi, golongan, sediaan, dosis..."><button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        </div>
        <a class="supplier-manage-toggle" href="{{ route('admin.supplier-procurement.index', ['tab' => 'offers']) }}"><i class="fa-solid fa-tags"></i> Kelola harga supplier</a>
    </form>
</div>
@if($selectedSupplier)
    <div class="supplier-notice" role="status">Harga dan pesanan menggunakan data <strong>{{ $selectedSupplier->name }}</strong>. Hanya produk yang sudah memiliki harga aktif di supplier ini yang ditampilkan.</div>
@else
    <div class="supplier-notice" role="status">Semua Produk menampilkan harga aktif dari seluruh supplier. Produk yang sama dapat muncul beberapa kali dengan harga berbeda. Tampilan ini hanya untuk membandingkan harga dan tidak bisa digunakan untuk transaksi; pilih satu supplier untuk membuat SP.</div>
@endif
@if(session('status'))
    <div class="supplier-notice" role="status">{{ session('status') }}</div>
@endif
@if($errors->any())
    <ul class="supplier-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
@endif
@if($productOffers->count())
    <div class="ordering-grid">
        @foreach($productOffers as $supplierOffer)
            @php($medicine = $supplierOffer->medicine)
            <article class="ordering-card">
                <div class="ordering-image">
                    @if($medicine->image_url)<img src="{{ $medicine->image_url }}" alt="{{ $medicine->nama_obat }}">@else<i class="fa-solid fa-pills"></i>@endif
                    @include('partials.product-logo-marker', ['medicine' => $medicine])
                </div>
                <div class="ordering-body">
                    <span class="ordering-principle">{{ $medicine->brand ?: $medicine->kategori ?: 'Produk' }}</span>
                    <h3 class="ordering-name">{{ $medicine->nama_obat }}</h3>
                    <div class="ordering-meta">
                        <p><strong>Logo:</strong> {{ $medicine->logo ?: '—' }}</p>
                        <p title="{{ $medicine->golongan ?: '—' }}"><strong>Golongan:</strong> {{ $medicine->golongan ?: '—' }}</p>
                    </div>
                    <div class="ordering-price">Rp {{ number_format((float) $supplierOffer->purchase_price, 0, ',', '.') }} <small>/ {{ $medicine->package_unit_label }}</small></div>
                    <div class="ordering-meta"><p><strong>Supplier:</strong> {{ $supplierOffer->supplier->name }}</p></div>
                    @if($selectedSupplier)
                        <div class="ordering-meta"><p><strong>Kode supplier:</strong> {{ $supplierOffer->supplier_code ?: '—' }}</p><p><strong>Minimum:</strong> {{ $supplierOffer->minimum_quantity }} {{ $medicine->package_unit_label }}</p></div>
                        <button class="ordering-button" data-package-unit-label="{{ $medicine->package_unit_label }}" onclick="addToCart({{ $medicine->id }}, '{{ addslashes($medicine->nama_obat) }}', {{ $supplierOffer->purchase_price }}, '{{ $medicine->image_url ?: '' }}', '{{ addslashes($medicine->brand ?: $medicine->kategori) }}', this, null, {{ $supplierOffer->id }}, {{ $supplierOffer->minimum_quantity }})"><i class="fa-solid fa-cart-plus"></i> Tambah Pesanan</button>
                    @else
                        <div class="ordering-readonly"><i class="fa-solid fa-eye"></i> Perbandingan harga saja</div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    <div class="ordering-pages">
        @if($productOffers->onFirstPage())<span>Previous</span>@else<a href="{{ $productOffers->previousPageUrl() }}">Previous</a>@endif
        @foreach($productOffers->getUrlRange(1, $productOffers->lastPage()) as $page => $url)
            @if($page === 1 || $page === $productOffers->lastPage() || abs($page - $productOffers->currentPage()) <= 2)
                @if($page === $productOffers->currentPage())<span class="active">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
            @elseif($page === 2 || $page === $productOffers->lastPage() - 1)
                <span>...</span>
            @endif
        @endforeach
        @if($productOffers->hasMorePages())<a href="{{ $productOffers->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif
    </div>
@else
    <div class="ordering-empty"><i class="fa-solid fa-box-open" style="font-size:2.5rem"></i><p>{{ $selectedSupplier ? 'Belum ada harga produk aktif untuk supplier ini. Tambahkan harga melalui Kelola harga supplier.' : 'Belum ada harga supplier aktif. Tambahkan harga melalui Kelola harga supplier.' }}</p></div>
@endif
@if($selectedSupplier)
    <button class="ordering-cart-button" onclick="openCart()"><i class="fa-solid fa-cart-shopping"></i> Keranjang <span class="cart-badge" id="adminCartBadge">0</span></button>
@endif
@endsection

@section('scripts')
@if($selectedSupplier)
<script>window.cartSettings = { storageKey: 'apotekmedix_cart_pbf_admin_supplier_{{ $selectedSupplier->id }}', receiptStoreName: 'APOTEK MEDIX', receiptStoreAddress: 'Jl. Raya Tajur No. 256 C, Bogor Timur, Kota Bogor, 16146', receiptFilePrefix: 'surat-pesanan-apotek-medix', adminPbfOrder: true, selectedSupplier: @json($selectedSupplier), supplierOrderUrl: @json(route('admin.supplier-procurement.orders.store')), supplierPurchaseOrderBase: @json(route('admin.supplier-procurement.index')), adminPurchaseOrderBase: '{{ str_replace('/0/', '/__ORDER__/', route('admin.purchase-history.purchase-order', ['order' => 0])) }}', continueShoppingUrl: @json(route('admin.produk-pbf.index')), suppliers: @json($suppliers) };</script>
@endif
@if($selectedSupplier)
@include('partials.cart', ['cartAdminPbfOrder' => true])
@endif
@endsection
