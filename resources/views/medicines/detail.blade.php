@extends('layouts.frontend')

@section('title', $medicine->nama_obat . ' - Apotek Medix')

@section('styles')
@include('partials.product-card-styles')
@php
    $detailOrigin = $detailOrigin ?? 'products';
    $detailReturnUrl = match ($detailOrigin) {
        'home' => route('home', filled(request()->query('search'))
            ? ['search' => request()->query('search')]
            : []),
        'b2b' => route('products.pbf'),
        'products' => route('products.index'),
        'apotek' => route('products.apotek'),
        default => route('products.index'),
    };
    $detailReturnLabel = match ($detailOrigin) {
        'home' => 'Kembali ke Beranda',
        'b2b' => 'Kembali ke Belanja B2B',
        'products' => 'Kembali ke Katalog',
        'apotek' => 'Kembali ke Produk Apotek',
        default => 'Kembali ke Katalog',
    };
@endphp
<style>
    /* ===== DETAIL PAGE HEADER ===== */
    .detail-page-header {
        background: linear-gradient(135deg, #0D47A1 0%, #1565C0 50%, #1E88E5 100%);
        padding: 2.5rem 0;
        position: relative;
        overflow: hidden;
    }
    .detail-page-header::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(124,179,66,0.18) 0%, transparent 70%);
        border-radius: 50%;
    }
    .detail-page-header h1 {
        font-size: clamp(1.4rem, 3vw, 2rem);
        font-weight: 800;
        color: #ffffff !important;
        margin-bottom: 0.4rem;
        position: relative;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .detail-page-header p {
        color: rgba(255,255,255,0.88) !important;
        font-size: 0.95rem;
        position: relative;
    }
    .breadcrumb-custom {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.75rem;
        position: relative;
        flex-wrap: wrap;
    }
    .breadcrumb-custom a {
        color: rgba(255,255,255,0.8) !important;
        text-decoration: none;
        font-size: 0.875rem;
    }
    .breadcrumb-custom a:hover { color: #ffffff !important; }
    .breadcrumb-custom span { color: rgba(255,255,255,0.6) !important; font-size: 0.875rem; }
    .breadcrumb-custom .current { color: #bbf7d0 !important; font-size: 0.875rem; font-weight: 700; }

    /* ===== DETAIL WRAPPER ===== */
    .detail-wrapper {
        max-width: 1020px;
        margin: 2.25rem auto;
        padding: 0 1rem;
    }

    .detail-container {
        background: #ffffff !important;
        color: #0f172a !important;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
        border: 1px solid #dbeafe;
        margin-bottom: 2rem;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1.25fr;
        gap: 0;
    }

    .detail-image-col {
        min-width: 0;
        padding: 2rem;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .detail-image {
        width: 100%;
        aspect-ratio: 1;
        max-width: 340px;
        background: #ffffff;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 5rem;
        overflow: hidden;
        position: relative;
        margin-bottom: 0.85rem;
        border: 1px solid #dbeafe;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .detail-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .detail-image img.detail-gallery-main {
        transition: opacity .2s ease;
        object-fit: contain;
    }
    .detail-gallery-thumbnails {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: .55rem;
        max-width: 340px;
        width: 100%;
    }
    .detail-gallery-thumb {
        width: 58px;
        height: 58px;
        padding: 0;
        overflow: hidden;
        border: 2px solid transparent;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color .2s, transform .2s;
    }
    .detail-gallery-thumb:hover,
    .detail-gallery-thumb.active {
        transform: translateY(-2px);
        border-color: #1E88E5;
    }
    .detail-gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Deskripsi di bawah foto (detail) */
    .detail-desc {
        margin-top: 1rem;
        max-width: 340px;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        background: #ffffff !important;
        border: 1px solid #cfe2ff;
        padding: 1rem 1.1rem;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(14,165,233,0.06);
        color: #1e293b !important;
    }
    .detail-desc h3 {
        font-size: 0.95rem;
        font-weight: 800;
        margin: 0 0 0.45rem;
        color: #0f172a !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .detail-desc .icon {
        color: #1E88E5 !important;
        font-size: 1.05rem;
    }
    .detail-desc p {
        line-height: 1.6;
        font-size: 0.9rem;
        color: #334155 !important;
        margin: 0;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .detail-meta-row {
        overflow-wrap: anywhere;
    }

    .detail-info {
        min-width: 0;
        padding: 2rem;
        background: #ffffff !important;
        color: #0f172a !important;
        border-left: 1px solid #e2e8f0;
    }

    .detail-info h1 {
        font-size: clamp(1.35rem, 2.5vw, 1.75rem);
        margin-bottom: 0.85rem;
        color: #0f172a !important;
        font-weight: 800;
        line-height: 1.3;
    }

    .detail-category {
        display: inline-block;
        background: #e0f2fe !important;
        color: #0369a1 !important;
        padding: 0.32rem 0.9rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 800;
        margin-bottom: 0.85rem;
        border: 1px solid #bae6fd;
    }

    .price-section {
        margin: 1rem 0 1.25rem;
        padding: 0;
        background: transparent !important;
        border: 0;
    }

    .price-label {
        font-size: 0.88rem;
        color: #475569 !important;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .price {
        font-size: clamp(1.5rem, 3vw, 1.95rem);
        font-weight: 800;
        color: #0f172a !important;
        line-height: 1.2;
    }

    .stock-info {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.85rem;
        flex-wrap: wrap;
    }

    .stock-item {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        flex: 0 0 auto;
        min-width: 0;
        padding: 0.45rem 0.75rem;
        background: #f8fafc !important;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        text-align: left;
    }

    .stock-item-label {
        font-size: 0.74rem;
        color: #475569 !important;
        margin: 0;
        font-weight: 700;
    }

    .stock-item-value {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a !important;
    }

    .detail-manufacturer {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: 0.35rem 0.75rem;
        border: 1px solid #bae6fd;
        border-radius: 999px;
        background: #e0f2fe !important;
        color: #0369a1 !important;
        font-size: 0.8rem;
        font-weight: 800;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .detail-meta-list {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        margin-bottom: 1.25rem;
        padding: 0.95rem 1.1rem;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .detail-image-col .detail-meta-list {
        width: 100%;
        max-width: 340px;
        margin: 1rem 0 0;
        text-align: left;
    }

    .detail-meta-row {
        display: grid;
        grid-template-columns: minmax(110px, 30%) minmax(0, 1fr);
        gap: 0.65rem;
        font-size: 0.88rem;
        line-height: 1.5;
        color: #1e293b !important;
    }

    .detail-meta-row strong {
        color: #0f172a !important;
        font-weight: 800;
        margin-right: 0;
    }

    .detail-meta-row span {
        color: #334155 !important;
        font-weight: 500;
        overflow-wrap: anywhere;
    }
    .detail-meta-text-dialog {
        width: min(92vw, 560px);
        max-height: min(80vh, 680px);
        padding: 1.25rem;
        border: 1px solid #dbeafe;
        border-radius: 16px;
        color: #172033;
        background: #fff;
        box-shadow: 0 20px 55px rgba(15, 23, 42, .28);
    }
    .detail-meta-text-dialog::backdrop { background: rgba(15, 23, 42, .65); }
    .detail-meta-text-dialog h2 { margin: 0 2.5rem .85rem 0; color: #0f172a; font-size: 1.1rem; }
    .detail-meta-text-dialog p { margin: 0; color: #334155; line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere; }
    .detail-meta-text-dialog button {
        position: absolute;
        top: .7rem;
        right: .7rem;
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 50%;
        color: #334155;
        background: #f1f5f9;
        font-size: 1.2rem;
        cursor: pointer;
    }
    .detail-meta-row > span.is-collapsible {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
    }
    .detail-meta-more {
        grid-column: 2;
        justify-self: start;
        padding: .25rem 0;
        border: 0;
        color: #0755c8;
        background: transparent;
        font: inherit;
        font-size: .8rem;
        font-weight: 800;
        cursor: pointer;
    }

    /* Purchase Box */
    .detail-purchase-box {
        background: #f8fafc !important;
        border-radius: 12px;
        padding: 1rem;
        margin: 1rem 0;
        border: 1px solid #e2e8f0;
        color: #0f172a !important;
    }

    .detail-purchase-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a !important;
        margin: 0 0 0.7rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-purchase-title i {
        color: #1E88E5 !important;
    }

    .detail-unit-pills {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 0.55rem;
        margin-bottom: 0.7rem;
    }

    .detail-unit-pill {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 0.6rem 0.75rem;
        border-radius: 10px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff !important;
        color: #0f172a !important;
        cursor: pointer;
        transition: all 0.18s ease;
        text-align: left;
    }

    .detail-unit-pill:hover {
        border-color: #1E88E5;
        background: #f0f9ff !important;
    }

    .detail-unit-pill.active {
        border-color: #1E88E5;
        background: #e0f2fe !important;
        box-shadow: 0 0 0 2px rgba(30, 136, 229, 0.18);
    }

    .detail-unit-pill-name {
        font-size: 0.78rem;
        font-weight: 800;
        color: #0f172a !important;
    }

    .detail-unit-pill-price {
        font-size: 0.85rem;
        font-weight: 800;
        color: #0369a1 !important;
        margin-top: 0.1rem;
    }

    .detail-unit-pill-stock {
        font-size: 0.7rem;
        font-weight: 600;
        color: #475569 !important;
        margin-top: 0.1rem;
    }

    .detail-qty-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #1e293b !important;
        margin-bottom: 0.4rem;
    }

    .detail-qty-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.7rem;
    }

    .detail-qty-btn {
        width: 38px;
        height: 38px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 1.05rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.18s ease;
    }

    .detail-qty-btn:hover {
        border-color: #1E88E5;
        color: #1E88E5 !important;
        background: #f0f9ff !important;
    }

    .detail-qty-input {
        width: 74px;
        height: 38px;
        text-align: center;
        padding: 0.35rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 800;
        background: #ffffff !important;
        color: #0f172a !important;
    }

    .detail-total-bar {
        margin-bottom: 0.7rem;
        padding: 0.25rem 0;
        background: transparent !important;
        border: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detail-total-label {
        font-size: 0.84rem;
        color: #0369a1 !important;
        font-weight: 700;
    }

    .detail-total-value {
        font-size: 1.08rem;
        font-weight: 800;
        color: #0f172a !important;
    }

    .detail-action-buttons {
        display: grid;
        grid-template-columns: 1fr 1.35fr;
        gap: 0.55rem;
    }

    .btn-detail-buy,
    .btn-detail-cart {
        padding: 0.72rem 0.85rem;
        border-radius: 11px;
        font-size: 0.9rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        border: none;
        text-decoration: none;
        line-height: 1.25;
    }

    .btn-detail-buy {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 6px 16px rgba(22, 163, 74, 0.25);
    }

    .btn-detail-buy:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(22, 163, 74, 0.34);
        color: #ffffff !important;
    }

    .btn-detail-cart {
        background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 6px 16px rgba(30, 136, 229, 0.25);
    }

    .btn-detail-cart:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(30, 136, 229, 0.34);
        color: #ffffff !important;
    }

    .btn-detail-cart.added {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;
    }

    .btn-detail-buy *,
    .btn-detail-cart * {
        color: #ffffff !important;
    }

    .btn-detail-buy:disabled,
    .btn-detail-cart:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }

    .btn-back-catalog {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.7rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff !important;
        color: #334155 !important;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-back-catalog:hover {
        border-color: #1E88E5;
        color: #1E88E5 !important;
        background: #f0f9ff !important;
    }

    /* Related Products */
    .related-section {
        background: #ffffff !important;
        color: #0f172a !important;
        border-radius: 16px;
        padding: 1.75rem 2rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
        border: 1px solid #dbeafe;
    }

    .related-section h2 {
        font-size: 1.1rem;
        font-weight: 800;
        margin-bottom: 1.25rem;
        color: #0f172a !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .related-section h2 i { color: #1E88E5 !important; }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
        gap: 1rem;
    }

    .related-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        text-decoration: none;
        color: #0f172a !important;
        transition: all 0.25s;
    }

    .related-card:hover {
        box-shadow: 0 8px 25px rgba(30,136,229,0.14);
        transform: translateY(-4px);
        border-color: #90caf9;
    }

    .related-image {
        width: 100%;
        height: 125px;
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        overflow: hidden;
    }

    .related-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .related-card:hover .related-image img { transform: scale(1.05); }

    .related-body { padding: 0.85rem; }

    .related-name {
        font-size: 0.84rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: #0f172a !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .related-price {
        font-size: 0.92rem;
        font-weight: 800;
        color: #1E88E5 !important;
    }

    @media (max-width: 768px) {
        .detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.45rem;
            padding: 0 0 .75rem;
        }
        .detail-grid > .detail-image-col,
        .detail-grid > .detail-info {
            display: contents;
        }
        .detail-grid > .detail-image-col,
        .detail-grid > .detail-info,
        .detail-grid > .detail-image-col > *,
        .detail-grid > .detail-info > * {
            min-width: 0;
        }
        .detail-container {
            overflow: hidden;
            background: #fff !important;
        }
        .detail-grid > .detail-image-col,
        .detail-grid > .detail-info {
            background: transparent !important;
            border: 0 !important;
            padding: 0 !important;
        }
        .detail-image {
            order: 1;
            margin: .85rem auto .25rem;
        }
        .detail-gallery-thumbnails {
            order: 2;
            margin: 0 auto .35rem;
        }
        .detail-heading-row {
            order: 3;
            padding: 0 1rem;
            margin: .25rem 0 0 !important;
        }
        .detail-product-name {
            order: 4;
            padding: 0 1rem;
            margin: 0 !important;
        }
        .stock-info {
            order: 5;
            padding: 0 1rem;
            margin: 0 !important;
        }
        .price-section {
            order: 6;
            padding: 0 1rem !important;
            margin: .1rem 0 .25rem !important;
        }
        .detail-meta-list--image {
            order: 7;
        }
        .detail-meta-list--info {
            order: 8;
        }
        .detail-meta-list {
            width: auto !important;
            max-width: none !important;
            gap: .55rem;
            margin: 0 .75rem !important;
            padding: .25rem .25rem 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .detail-meta-row {
            grid-template-columns: minmax(90px, 30%) minmax(0, 1fr);
            gap: .5rem;
            padding: 0 .25rem;
        }
        .detail-purchase-box {
            order: 9;
            margin: .35rem 1rem 0 !important;
        }
        .btn-back-catalog {
            order: 10;
            margin: 0 1rem;
        }
        .related-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
        .related-section { padding: 1.25rem; }
    }

    @media (max-width: 480px) {
        .detail-wrapper { margin: 1rem auto; padding: 0 0.5rem; }
        .detail-image { max-width: 190px; }
        .detail-action-buttons {
            grid-template-columns: 1fr;
        }
        .related-section { padding: 1rem; }
        .related-grid { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
        .related-image { height: 105px; }
        .related-body { padding: 0.65rem; }
    }
</style>
@endsection

@section('content')
@php
    $internalAccess = $internalAccess ?? \App\Models\ShoppingAccessCode::hasActiveInternalSession();
    $b2bAccess = $b2bAccess ?? false;
    $priceChannel = $b2bAccess ? 'b2b' : ($internalAccess ? 'internal' : 'b2c');
    $detailCartStorageKey = match ($priceChannel) {
        'b2b' => 'apotekmedix_cart_b2b',
        'internal' => 'apotekmedix_cart_internal',
        default => 'apotekmedix_cart_b2c',
    };
    $b2cAccess = $b2cAccess ?? \App\Models\ShoppingAccessCode::hasActiveB2cSession();
    $redLogoAccess = $redLogoAccess ?? ($b2cAccess || $internalAccess);
    $isRedLogoLocked = $medicine->isRedLogo() && !$redLogoAccess;
    $unitPriceValues = [
        'box' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'box')),
    ];
    $unitFactors = [
        'box' => max(1, (int) $medicine->units_per_box),
    ];
    $unitLabels = [
        'box' => $medicine->package_unit_label,
    ];
    $stockUnits = max(0, (int) ($medicine->available_stock_units ?? $medicine->stock_base_units ?? $medicine->stok));
    $detailStockQuantity = $stockUnits / max(1, $medicine->units_per_box);
    $detailStockDisplay = rtrim(rtrim(number_format($detailStockQuantity, 6, ',', '.'), '0'), ',');
    $defaultUnit = array_key_first($unitPriceValues) ?: 'box';
@endphp

<div class="detail-page-header">
    <div class="container">
        <div class="breadcrumb-custom">
            <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Home</a>
            <span>/</span>
            <a href="{{ $detailReturnUrl }}">{{ $detailOrigin === 'b2b' ? 'Belanja B2B' : 'Produk' }}</a>
            <span>/</span>
            <span class="current">{{ Str::limit($medicine->nama_obat, 30) }}</span>
        </div>
        <h1>{{ $medicine->nama_obat }}</h1>
        <p><i class="fa-solid fa-building fa-sm"></i> Pabrik: {{ $medicine->kategori ?: ($medicine->brand ?: '—') }}</p>
    </div>
</div>

<div class="detail-wrapper">
    <div class="detail-container">
        <div class="detail-grid">
            <!-- Image Column -->
            <div class="detail-image-col">
                <div class="detail-image">
                    @include('partials.product-logo-marker', ['medicine' => $medicine])
                    @if($medicine->image_gallery)
                        <img class="detail-gallery-main" id="detailGalleryMain" src="{{ $medicine->image_url }}" alt="{{ $medicine->nama_obat }}">
                    @else
                        <i class="fa-solid fa-pills" style="color:#90caf9;font-size:4rem;"></i>
                    @endif
                </div>
                @if(count($medicine->image_gallery) > 1)
                    <div class="detail-gallery-thumbnails" aria-label="Foto produk lainnya">
                        @foreach($medicine->image_gallery as $index => $path)
                            <button type="button" class="detail-gallery-thumb {{ $index === 0 ? 'active' : '' }}" data-image-url="{{ $index === 0 ? $medicine->image_url : url('storage/' . $path) }}" aria-label="Tampilkan foto produk {{ $index + 1 }}">
                                <img src="{{ $index === 0 ? $medicine->image_url : url('storage/' . $path) }}" alt="Foto {{ $index + 1 }} {{ $medicine->nama_obat }}">
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="detail-meta-list detail-meta-list--image">
                    <div class="detail-meta-row"><strong>Deskripsi:</strong> <span>{!! nl2br(e($medicine->deskripsi ?: '—')) !!}</span></div>
                    <div class="detail-meta-row"><strong>Golongan:</strong> <span>{{ $medicine->golongan ?: '—' }}</span></div>
                    <div class="detail-meta-row"><strong>Kategori:</strong> <span>{{ $medicine->kategori_produk ?: '—' }}</span></div>
                </div>
            </div>

            <!-- Info Column -->
            <div class="detail-info">
                <div class="detail-heading-row" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.85rem;">
                    <span class="detail-manufacturer">Pabrik / Brand: {{ $medicine->kategori ?: ($medicine->brand ?: '—') }}</span>
                </div>
                <h1 class="detail-product-name">{{ $medicine->nama_obat }}</h1>

                <div class="price-section">
                    <div class="price" id="detailUnitPriceDisplay">Rp {{ number_format($unitPriceValues[$defaultUnit] ?? $medicine->harga, 0, ',', '.') }}</div>
                </div>

                <div class="stock-info">
                    <span hidden aria-hidden="true" data-stock-badge data-stock-product-id="{{ $medicine->id }}" data-stock-unit-factor="{{ max(1, $medicine->units_per_box) }}">Stok</span>
                    <div class="stock-item">
                        <div class="stock-item-label">Stok</div>
                        <div class="stock-item-value">{{ $detailStockDisplay }}</div>
                    </div>
                    <span class="medicine-sold-badge" data-sold-product-id="{{ $medicine->id }}" style="margin-left:0;font-size:0.78rem;padding:0.28rem 0.75rem;">
                        <i class="fa-solid fa-fire-flame-curved"></i> Terjual {{ number_format((int) $medicine->terjual, 0, ',', '.') }}
                    </span>
                </div>

                <div class="detail-meta-list detail-meta-list--info">
                    <div class="detail-meta-row"><strong>Komposisi:</strong> <span>{!! nl2br(e($medicine->komposisi ?: '—')) !!}</span></div>
                    <div class="detail-meta-row"><strong>Indikasi:</strong> <span>{!! nl2br(e($medicine->indikasi ?: '—')) !!}</span></div>
                    <div class="detail-meta-row"><strong>Satuan:</strong> <span>{{ $medicine->satuan ?: '—' }}</span></div>
                    <div class="detail-meta-row"><strong>Kemasan:</strong> <span>{{ $medicine->kemasan ?: '—' }}</span></div>
                    <div class="detail-meta-row"><strong>Sediaan:</strong> <span>{{ $medicine->sediaan ? $medicine->sediaan_label : '—' }}</span></div>
                    @if(filled($medicine->dosis))
                        <div class="detail-meta-row"><strong>Dosis / Aturan Pakai:</strong> <span>{{ $medicine->dosis }}</span></div>
                    @endif
                </div>

                <!-- Pembelian Produk -->
                <div class="detail-purchase-box {{ $isRedLogoLocked ? 'is-red-logo-locked' : '' }}">
                    <h3 class="detail-purchase-title">
                        <i class="fa-solid fa-bag-shopping"></i> Pembelian
                    </h3>

                    @if(count($unitPriceValues) > 1)
                        <span class="detail-qty-label">Pilih Satuan Kemasan</span>
                        <div class="detail-unit-pills" role="group" aria-label="Pilih satuan kemasan">
                            @foreach($unitPriceValues as $unitKey => $uPrice)
                                @php
                                    $uFactor = max(1, (int) ($unitFactors[$unitKey] ?? 1));
                                    $uStock  = intdiv($stockUnits, $uFactor);
                                    $uLabel  = $unitLabels[$unitKey] ?? ucfirst($unitKey);
                                @endphp
                                <button type="button"
                                        class="detail-unit-pill {{ (!$isRedLogoLocked && $unitKey === $defaultUnit) ? 'active' : '' }} {{ $isRedLogoLocked ? 'is-red-logo-locked' : '' }}"
                                        data-unit="{{ $unitKey }}"
                                        data-price="{{ $uPrice }}"
                                        data-label="{{ $uLabel }}"
                                        data-stock="{{ $uStock }}"
                                        onclick="selectDetailUnit(this)">
                                    <span class="detail-unit-pill-name">{{ $uLabel }}</span>
                                    <span class="detail-unit-pill-price">Rp {{ number_format($uPrice, 0, ',', '.') }}</span>
                                    <span class="detail-unit-pill-stock">Stok: {{ number_format($uStock, 0, ',', '.') }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="detail-total-bar {{ $isRedLogoLocked ? 'is-red-logo-locked' : '' }}">
                        <span class="detail-total-label">Harga per satuan</span>
                        <span id="totalHarga" class="detail-total-value">Rp {{ number_format($unitPriceValues[$defaultUnit] ?? $medicine->harga, 0, ',', '.') }}</span>
                    </div>

                    <div class="detail-action-buttons">
                        <button type="button"
                                class="btn-detail-buy {{ $isRedLogoLocked ? 'is-red-logo-locked' : '' }}"
                                id="btnDetailBuy"
                                onclick="detailBuyNow(this)"
                                @disabled($stockUnits < 1)>
                            <i class="fa-solid fa-bag-shopping"></i> Beli
                        </button>
                        <button type="button"
                                class="btn-detail-cart {{ $isRedLogoLocked ? 'is-red-logo-locked' : '' }}"
                                id="btnDetailCart"
                                onclick="detailAddToCart(this)"
                                @disabled($stockUnits < 1)>
                            <i class="fa-solid fa-cart-plus"></i> Tambahkan ke Keranjang
                        </button>
                    </div>
                </div>

                <a href="{{ $detailReturnUrl }}" class="btn-back-catalog">
                    <i class="fa-solid fa-arrow-left"></i> {{ $detailReturnLabel }}
                </a>
            </div>
        </div>
    </div>

    @if($relatedMedicines->count() > 0)
    <div class="related-section">
        <h2><i class="fa-solid fa-pills"></i> Produk Serupa</h2>
        <div class="related-grid">
            @foreach($relatedMedicines as $related)
                <a href="{{ route('medicines.show', ['id' => $related->id, 'from' => $detailOrigin, 'search' => $detailOrigin === 'home' ? request()->query('search') : null]) }}" class="related-card">
                    <div class="related-image">
                        @if($related->gambar)
                            <img src="{{ url('storage/' . $related->gambar) }}" alt="{{ $related->nama_obat }}">
                        @else
                            <i class="fa-solid fa-pills" style="color:#90caf9;font-size:1.75rem;"></i>
                        @endif
                    </div>
                    <div class="related-body">
                        <div class="related-name">{{ $related->nama_obat }}</div>
                        <div class="related-price">Rp {{ number_format($related->getChannelPromoPrice($priceChannel), 0, ',', '.') }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
<dialog class="detail-meta-text-dialog" id="detailMetaTextDialog" aria-labelledby="detailMetaTextTitle">
    <button type="button" id="closeDetailMetaTextDialog" aria-label="Tutup">&times;</button>
    <h2 id="detailMetaTextTitle"></h2>
    <p id="detailMetaTextContent"></p>
</dialog>

@endsection

@section('scripts')
<script>
window.cartSettings = {
    storageKey: @json($detailCartStorageKey),
    priceChannel: @json($priceChannel),
    wholesaleOrder: @json($priceChannel === 'b2b'),
    priceMode: @json($priceChannel)
};
</script>
@include('partials.cart')
<script>
document.querySelectorAll('.detail-gallery-thumb').forEach(button => {
    button.addEventListener('click', () => {
        const mainImage = document.getElementById('detailGalleryMain');
        if (!mainImage) return;
        mainImage.src = button.dataset.imageUrl;
        document.querySelectorAll('.detail-gallery-thumb').forEach(thumb => thumb.classList.toggle('active', thumb === button));
    });
});

(() => {
    const dialog = document.getElementById('detailMetaTextDialog');
    const title = document.getElementById('detailMetaTextTitle');
    const content = document.getElementById('detailMetaTextContent');
    const closeButton = document.getElementById('closeDetailMetaTextDialog');
    if (!dialog || !title || !content || !closeButton) return;

    const addLongTextButtons = () => {
        document.querySelectorAll('.detail-meta-row').forEach(row => {
            const value = row.querySelector(':scope > span');
            if (!value) return;

            const existingButton = row.querySelector('.detail-meta-more');
            value.classList.add('is-collapsible');
            const lineHeight = Number.parseFloat(window.getComputedStyle(value).lineHeight);
            value.classList.remove('is-collapsible');
            const fullHeight = value.getBoundingClientRect().height;
            value.classList.add('is-collapsible');
            if (Number.isFinite(lineHeight) && fullHeight <= lineHeight * 4 + 1) {
                value.classList.remove('is-collapsible');
                existingButton?.remove();
                return;
            }

            if (!existingButton) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'detail-meta-more';
                button.textContent = 'Lihat selengkapnya';
                button.addEventListener('click', () => {
                    title.textContent = row.querySelector('strong')?.innerText.replace(/:$/, '').trim() || 'Informasi produk';
                    content.textContent = value.innerText.trim();
                    dialog.showModal();
                });
                row.append(button);
            }
        });
    };

    closeButton.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
    });
    addLongTextButtons();
    window.addEventListener('resize', addLongTextButtons);
})();

const DETAIL_PRODUCT = {
    id: {{ (int) $medicine->id }},
    name: @json($medicine->nama_obat),
    brand: @json($medicine->brand ?: $medicine->kategori),
    img: @json($medicine->image_url ?? ''),
    isRedLogo: {{ $medicine->isRedLogo() ? 'true' : 'false' }},
    isRedLogoLocked: {{ $isRedLogoLocked ? 'true' : 'false' }},
    stockUnits: {{ (int) $stockUnits }},
    packageUnitLabel: @json($medicine->package_unit_label),
    unitPrices: @json($unitPriceValues),
    unitFactors: @json($unitFactors),
    unitLabels: @json($unitLabels),
    selectedUnit: @json($defaultUnit)
};

function formatRupiah(angka) {
    return 'Rp ' + Math.round(Number(angka) || 0).toLocaleString('id-ID');
}

function getSelectedUnitAvailableQty() {
    const unit = DETAIL_PRODUCT.selectedUnit || 'box';
    const factor = Number(DETAIL_PRODUCT.unitFactors[unit] || 1);
    return factor > 0 ? Math.floor(DETAIL_PRODUCT.stockUnits / factor) : 0;
}

function selectDetailUnit(btn) {
    if (DETAIL_PRODUCT.isRedLogoLocked) {
        if (typeof openRedLogoModal === 'function') openRedLogoModal(DETAIL_PRODUCT.name);
        return;
    }
    const unit = btn.dataset.unit;
    if (!unit) return;
    DETAIL_PRODUCT.selectedUnit = unit;

    document.querySelectorAll('.detail-unit-pill').forEach(pill => {
        pill.classList.toggle('active', pill === btn);
    });

    const unitPrice = Number(DETAIL_PRODUCT.unitPrices[unit] || 0);

    const priceDisplayEl = document.getElementById('detailUnitPriceDisplay');
    if (priceDisplayEl) priceDisplayEl.textContent = formatRupiah(unitPrice);
    const priceTotalEl = document.getElementById('totalHarga');
    if (priceTotalEl) priceTotalEl.textContent = formatRupiah(unitPrice);
}

function commitDetailItemToCart() {
    if (DETAIL_PRODUCT.isRedLogoLocked) {
        if (typeof openRedLogoModal === 'function') {
            openRedLogoModal(DETAIL_PRODUCT.name);
        }
        return false;
    }

    const unit = DETAIL_PRODUCT.selectedUnit || 'box';
    const unitLabel = DETAIL_PRODUCT.unitLabels[unit] || unit;
    const unitPrice = Number(DETAIL_PRODUCT.unitPrices[unit] || 0);
    const availableQty = getSelectedUnitAvailableQty();
    const qty = 1;

    if (availableQty < 1 || qty > availableQty) {
        alert('Stok untuk satuan ' + unitLabel + ' tidak mencukupi.');
        return false;
    }

    const existing = cart.find(item => item.id === DETAIL_PRODUCT.id && (item.saleUnit || 'box') === unit);
    if (existing) {
        if (existing.qty + qty > availableQty) {
            alert('Stok untuk satuan ' + unitLabel + ' tidak mencukupi.');
            return false;
        }
        existing.qty += qty;
        existing.price = unitPrice;
        existing.brand = DETAIL_PRODUCT.brand;
        existing.img = DETAIL_PRODUCT.img;
        existing.unitLabel = unitLabel;
        existing.unitLabels = DETAIL_PRODUCT.unitLabels;
        existing.unitPrices = DETAIL_PRODUCT.unitPrices;
        existing.unitFactors = DETAIL_PRODUCT.unitFactors;
        existing.availableQty = availableQty;
        existing.stockUnits = DETAIL_PRODUCT.stockUnits;
        existing.packageUnitLabel = DETAIL_PRODUCT.packageUnitLabel;
    } else {
        cart.push({
            id: DETAIL_PRODUCT.id,
            name: DETAIL_PRODUCT.name,
            price: unitPrice,
            img: DETAIL_PRODUCT.img,
            brand: DETAIL_PRODUCT.brand || '',
            qty: qty,
            note: '',
            discount: 0,
            saleUnit: unit,
            unitLabel: unitLabel,
            unitLabels: DETAIL_PRODUCT.unitLabels,
            unitPrices: DETAIL_PRODUCT.unitPrices,
            unitFactors: DETAIL_PRODUCT.unitFactors,
            availableQty: availableQty,
            stockUnits: DETAIL_PRODUCT.stockUnits,
            packageUnitLabel: DETAIL_PRODUCT.packageUnitLabel
        });
    }

    if (typeof save === 'function') save();
    if (typeof render === 'function') render();
    return true;
}

function detailAddToCart(btn) {
    if (!commitDetailItemToCart()) return;
    if (btn) {
        const origHtml = btn.dataset.origHtml || btn.innerHTML;
        btn.dataset.origHtml = origHtml;
        btn.classList.add('added');
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Ditambahkan!';
        setTimeout(() => {
            btn.classList.remove('added');
            btn.innerHTML = origHtml;
        }, 1500);
    }
}

function detailBuyNow(btn) {
    if (!commitDetailItemToCart()) return;
    window.location.href = @json(route('checkout.index'));
}
</script>
@endsection
