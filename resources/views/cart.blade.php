@extends('layouts.frontend')

@section('title', 'Keranjang Anda - Apotek Medix')

@section('styles')
<style>
  .products-header.cart-banner {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 72%, #12394A 100%) !important;
    padding: 2.25rem 0 2rem !important;
    position: relative;
    overflow: hidden;
    border-bottom: 2px solid rgba(6, 182, 212, 0.35);
  }
  .products-header.cart-banner::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.18) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }
  .cart-banner-inner {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 1.5rem;
    position: relative;
    z-index: 1;
  }
  .breadcrumb-custom {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 0.65rem;
    font-size: 0.88rem;
  }
  .breadcrumb-custom a {
    color: rgba(255, 255, 255, 0.75);
    text-decoration: none;
    transition: color 0.2s;
  }
  .breadcrumb-custom a:hover {
    color: #ffffff;
  }
  .breadcrumb-custom span {
    color: rgba(255, 255, 255, 0.45);
  }
  .breadcrumb-custom .current {
    color: #a5d65a;
    font-weight: 600;
  }
  .cart-banner-title {
    font-size: clamp(1.75rem, 3vw, 2.35rem);
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 0.35rem;
    letter-spacing: -0.01em;
  }
  .cart-banner-sub {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.95rem;
    margin: 0;
  }

  .cart-page-wrap {
    max-width: 1180px;
    margin: 0 auto;
    padding: 2.25rem 1.5rem 5rem;
    position: relative;
    z-index: 1;
    color: #1E293B;
  }
  .cart-card-surface {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 2.25rem 2.5rem;
    position: relative;
    overflow: hidden;
  }
  .cart-card-surface::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #0F172A 0%, #06B6D4 55%, #10B981 100%);
  }

  .cart-page-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 1rem;
    margin-bottom: 2rem;
  }
  .cart-page-title {
    font-size: clamp(1.65rem, 2.6vw, 2.15rem);
    font-weight: 700;
    color: #0F172A;
    margin: 0;
    letter-spacing: -0.01em;
  }
  .cart-continue-link {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: #0E7490;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 4px;
    transition: color 0.2s;
  }
  .cart-continue-link:hover {
    color: #0F172A;
  }

  .cart-table-head {
    display: grid;
    grid-template-columns: minmax(0, 1.85fr) minmax(200px, 0.75fr) minmax(130px, 0.4fr);
    padding-bottom: 0.9rem;
    border-bottom: 1.5px solid #e2e8f0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.11em;
    text-transform: uppercase;
    color: #64748b;
  }
  .cart-table-head .col-total {
    text-align: right;
  }

  .cart-items-list {
    padding: 0.25rem 0;
  }
  .cart-row {
    display: grid;
    grid-template-columns: minmax(0, 1.85fr) minmax(200px, 0.75fr) minmax(130px, 0.4fr);
    align-items: center;
    gap: 1.5rem;
    padding: 1.65rem 0;
    border-bottom: 1px solid #f1f5f9;
  }
  .cart-row:last-child {
    border-bottom: none;
  }

  .cart-prod-cell {
    display: flex;
    align-items: flex-start;
    gap: 1.4rem;
    min-width: 0;
  }
  .cart-prod-thumb {
    width: 108px;
    height: 108px;
    flex-shrink: 0;
    background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    transition: transform 0.2s, border-color 0.2s;
  }
  .cart-prod-thumb:hover {
    border-color: #06B6D4;
    transform: translateY(-2px);
  }
  .cart-prod-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
  }

  .cart-prod-meta {
    min-width: 0;
    padding-top: 0.15rem;
  }
  .cart-prod-badge {
    display: inline-block;
    background: #ECFEFF;
    color: #0E7490;
    border: 1px solid #A5F3FC;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    margin-bottom: 0.35rem;
  }
  .cart-prod-name {
    font-size: 1.02rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0 0 0.3rem;
    line-height: 1.38;
  }
  .cart-prod-name a {
    color: inherit;
    text-decoration: none;
    transition: color 0.15s;
  }
  .cart-prod-name a:hover {
    color: #0E7490;
  }
  .cart-prod-price {
    font-size: 0.9rem;
    font-weight: 600;
    color: #0E7490;
    margin-bottom: 0.35rem;
  }
  .cart-unit-select {
    margin-top: 0.25rem;
    padding: 0.38rem 0.65rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    color: #1E293B;
    background: #f8fafc;
    outline: none;
  }
  .cart-unit-select:focus {
    border-color: #06B6D4;
    background: #ffffff;
  }
  .cart-extra-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    margin-top: 0.45rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    background: none;
    border: none;
    padding: 0;
    text-decoration: underline;
    text-underline-offset: 3px;
    cursor: pointer;
  }
  .cart-extra-toggle:hover {
    color: #0E7490;
  }
  .cart-extra-box {
    margin-top: 0.6rem;
    max-width: 340px;
    display: none;
    gap: 0.5rem;
    flex-direction: column;
  }
  .cart-extra-box.open {
    display: flex;
  }
  .cart-extra-inp {
    width: 100%;
    padding: 0.48rem 0.7rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.82rem;
    color: #1E293B;
    outline: none;
  }
  .cart-extra-inp:focus {
    border-color: #06B6D4;
  }

  .cart-qty-cell {
    display: flex;
    align-items: center;
    gap: 0.85rem;
  }
  .cart-qty-box {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    width: 142px;
    height: 46px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    overflow: hidden;
    transition: border-color 0.15s;
  }
  .cart-qty-box:focus-within {
    border-color: #06B6D4;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.12);
  }
  .cart-qty-btn {
    width: 42px;
    height: 100%;
    border: none;
    background: #f8fafc;
    color: #0F172A;
    font-size: 1.05rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
  }
  .cart-qty-btn:hover {
    background: #ECFEFF;
    color: #0E7490;
  }
  .cart-qty-input {
    width: 56px;
    height: 100%;
    border: none;
    background: transparent;
    text-align: center;
    font-size: 0.94rem;
    color: #0F172A;
    font-weight: 700;
    outline: none;
    -moz-appearance: textfield;
  }
  .cart-qty-input::-webkit-outer-spin-button,
  .cart-qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  .cart-remove-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid transparent;
    color: #64748b;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
  }
  .cart-remove-btn:hover {
    color: #ef4444;
    background: #fef2f2;
    border-color: #fecaca;
  }

  .cart-total-cell {
    text-align: right;
    font-size: 1.06rem;
    color: #0F172A;
    font-weight: 700;
    white-space: nowrap;
  }

  .cart-page-footer {
    border-top: 1.5px solid #e2e8f0;
    padding-top: 1.85rem;
    margin-top: 0.5rem;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
  }
  .cart-est-row {
    display: flex;
    align-items: baseline;
    gap: 1.5rem;
    margin-bottom: 0.5rem;
  }
  .cart-est-label {
    font-size: 1.02rem;
    color: #475569;
    font-weight: 600;
  }
  .cart-est-value {
    font-size: 1.35rem;
    color: #0F172A;
    font-weight: 800;
  }
  .cart-tax-note {
    font-size: 0.84rem;
    color: #64748b;
    margin: 0 0 1.4rem;
    text-align: right;
  }
  .cart-tax-note span {
    text-decoration: underline;
    text-underline-offset: 3px;
    color: #0E7490;
  }
  .cart-checkout-btn {
    width: 350px;
    max-width: 100%;
    height: 50px;
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    color: #ffffff;
    border: 1.5px solid #06B6D4;
    border-radius: 10px;
    font-size: 0.98rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.16);
    transition: all 0.2s;
  }
  .cart-checkout-btn:hover {
    background: linear-gradient(135deg, #1E293B 0%, #0E7490 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(6, 182, 212, 0.24);
  }

  .cart-empty-state {
    text-align: center;
    padding: 4rem 1.5rem;
  }
  .cart-empty-icon {
    width: 78px;
    height: 78px;
    margin: 0 auto 1.25rem;
    border-radius: 50%;
    background: #ECFEFF;
    border: 1.5px solid #A5F3FC;
    color: #0E7490;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
  }
  .cart-empty-state h2 {
    font-size: 1.65rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0 0 0.5rem;
  }
  .cart-empty-state p {
    color: #64748b;
    font-size: 0.95rem;
    margin: 0 0 1.65rem;
  }
  .cart-empty-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.85rem 2.25rem;
    background: #0F172A;
    border: 1.5px solid #06B6D4;
    color: #ffffff;
    text-decoration: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 700;
    transition: all 0.2s;
  }
  .cart-empty-btn:hover {
    background: #0E7490;
    color: #ffffff;
    transform: translateY(-2px);
  }
  .pbf-popup-backdrop,.pbf-invoice-backdrop { display:none; position:fixed; inset:0; z-index:4000; padding:20px; background:rgba(15,23,42,.72); }
  .pbf-popup-backdrop.open,.pbf-invoice-backdrop.open { display:flex; align-items:center; justify-content:center; }
  .pbf-popup { width:min(780px,100%); max-height:94vh; overflow:auto; border-radius:18px; background:#fff; box-shadow:0 24px 70px rgba(0,0,0,.35); }
  .pbf-popup-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding:22px 26px; color:#fff; background:linear-gradient(125deg,#0f4c81,#087f9c); }
  .pbf-popup-kicker { display:block; margin-bottom:7px; color:#b8f2e6; font-size:.68rem; font-weight:800; letter-spacing:.12em; }
  .pbf-popup-head h2 { margin:0; color:#fff; font-size:1.22rem; }
  .pbf-popup-head p { margin:6px 0 0; color:rgba(255,255,255,.82); font-size:.84rem; }
  .pbf-popup-close { flex:0 0 38px; width:38px; height:38px; border:0; border-radius:50%; color:#fff; background:rgba(255,255,255,.18); font-size:1.1rem; cursor:pointer; }
  .pbf-popup-body { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.05fr); }
  .pbf-popup-summary { padding:22px; background:#f3f8fb; border-right:1px solid #e2e8f0; }
  .pbf-popup-section-title { margin:0 0 13px; color:#0f4c81; font-size:.82rem; font-weight:800; }
  .pbf-popup-summary-item { display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid #dce7ed; color:#334155; font-size:.8rem; }
  .pbf-popup-summary-item strong { flex:0 0 auto; color:#087f9c; text-align:right; }
  .pbf-popup-total { display:flex; justify-content:space-between; gap:12px; margin-top:14px; padding-top:12px; border-top:2px solid #cbdbe5; color:#334155; font-size:.82rem; font-weight:800; }
  .pbf-popup-total strong { color:#087f9c; font-size:1.05rem; }
  .pbf-popup-form { padding:22px 24px; }
  .pbf-popup-form label { display:block; margin:0 0 6px; color:#334155; font-size:.76rem; font-weight:800; }
  .pbf-popup-form label span { color:#dc2626; }
  .pbf-popup-form input,.pbf-popup-form textarea { width:100%; margin:0 0 13px; padding:10px 12px; border:1px solid #cbd5e1; border-radius:9px; outline:none; color:#0f172a; font:inherit; font-size:.85rem; }
  .pbf-popup-form input:focus,.pbf-popup-form textarea:focus { border-color:#0891b2; box-shadow:0 0 0 3px rgba(8,145,178,.12); }
  .pbf-popup-form textarea { resize:vertical; }
  .pbf-popup-fields-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .pbf-popup-error { display:none; margin:0 0 12px; padding:10px 12px; border-radius:8px; background:#fee2e2; color:#991b1b; font-size:.8rem; }
  .pbf-popup-note { margin:0 0 14px; padding:10px; border-radius:8px; background:#ecfeff; color:#155e75; font-size:.75rem; line-height:1.5; }
  .pbf-popup-submit { width:100%; padding:12px 15px; border:0; border-radius:10px; background:#087f9c; color:#fff; font-size:.9rem; font-weight:800; cursor:pointer; }
  .pbf-popup-submit:disabled { opacity:.65; cursor:wait; }
  .pbf-popup-result { padding:38px 28px; text-align:center; }
  .pbf-popup-result i { color:#16a34a; font-size:3rem; }
  .pbf-popup-result h3 { margin:10px 0; color:#0f172a; }
  .pbf-popup-result p { margin:0 auto 20px; max-width:460px; color:#64748b; line-height:1.6; font-size:.9rem; }
  .pbf-invoice-backdrop { z-index:4100; }
  .pbf-invoice-panel { display:flex; flex-direction:column; width:min(1100px,100%); height:94vh; overflow:hidden; border-radius:14px; background:#fff; box-shadow:0 25px 60px rgba(0,0,0,.3); }
  .pbf-invoice-toolbar { display:flex; flex:0 0 auto; justify-content:space-between; align-items:center; gap:12px; padding:10px 14px; color:#1e293b; font-weight:700; }
  .pbf-invoice-toolbar button { padding:8px 12px; border:0; border-radius:8px; background:#e2e8f0; color:#1e293b; font-weight:700; cursor:pointer; }
  .pbf-invoice-panel iframe { flex:1 1 auto; width:100%; min-height:0; border:0; }

  @media (max-width: 768px) {
    .cart-page-wrap {
      padding: 1.5rem 1rem 3.5rem;
    }
    .cart-card-surface {
      padding: 1.5rem 1.15rem;
    }
    .cart-table-head {
      grid-template-columns: 1fr auto;
    }
    .cart-table-head .col-qty {
      display: none;
    }
    .cart-row {
      grid-template-columns: 1fr auto;
      align-items: start;
      gap: 0.85rem 1rem;
      padding: 1.35rem 0;
    }
    .cart-prod-cell {
      grid-column: 1 / 2;
      gap: 1rem;
    }
    .cart-prod-thumb {
      width: 86px;
      height: 86px;
    }
    .cart-total-cell {
      grid-column: 2 / 3;
      grid-row: 1 / 2;
      padding-top: 0.25rem;
    }
    .cart-qty-cell {
      grid-column: 1 / -1;
      padding-left: calc(86px + 1rem);
    }
    .cart-page-footer {
      align-items: stretch;
    }
    .cart-est-row {
      justify-content: space-between;
    }
    .cart-tax-note {
      text-align: left;
    }
    .cart-checkout-btn {
      width: 100%;
    }
    .pbf-popup-backdrop,.pbf-invoice-backdrop { padding:0; }
    .pbf-popup { width:100%; height:100%; max-height:100dvh; overflow:auto; border-radius:0; }
    .pbf-popup-head { padding:18px; }
    .pbf-popup-head h2 { font-size:1.05rem; }
    .pbf-popup-body { grid-template-columns:1fr; }
    .pbf-popup-summary { padding:16px 18px; border-right:0; border-bottom:1px solid #e2e8f0; }
    .pbf-popup-form { padding:18px; }
    .pbf-invoice-panel { width:100%; height:100dvh; border-radius:0; }
  }
</style>
@endsection

@section('content')
<section class="products-header cart-banner">
  <i class="fa-solid fa-pills header-deco-icon header-deco-icon-1"></i>
  <i class="fa-solid fa-basket-shopping header-deco-icon header-deco-icon-2"></i>
  <i class="fa-solid fa-capsules header-deco-icon header-deco-icon-3"></i>
  <div class="cart-banner-inner">
    <div class="breadcrumb-custom">
      <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Beranda</a>
      <span>/</span>
      <span class="current">Keranjang Anda</span>
    </div>
    <h1 class="cart-banner-title" id="cartBannerTitle">Keranjang Belanja</h1>
    <p class="cart-banner-sub" id="cartBannerDescription">Periksa kembali daftar produk dan jumlah pesanan Anda sebelum melanjutkan ke pembayaran.</p>
  </div>
</section>

<div class="cart-page-wrap">
  <div class="cart-card-surface">
    <div id="cartNonEmptyView" style="display:none;">
      <div class="cart-page-header">
        <h2 class="cart-page-title">Keranjang Anda</h2>
        <a href="{{ route('products.index') }}" id="continueShoppingLink" class="cart-continue-link">
          Lanjutkan belanja <i class="fa-solid fa-arrow-right" style="font-size:0.82rem;"></i>
        </a>
      </div>

      <div class="cart-table-head">
        <div class="col-prod">PRODUK</div>
        <div class="col-qty">JUMLAH</div>
        <div class="col-total">TOTAL</div>
      </div>

      <div class="cart-items-list" id="cartPageItems"></div>

      <div class="cart-page-footer">
        <div class="cart-est-row">
          <span class="cart-est-label">Estimasi total</span>
          <span class="cart-est-value" id="cartPageGrandTotal">Rp0</span>
        </div>
        <p class="cart-tax-note" id="cartCheckoutNote">
          Pajak, diskon, dan <span>biaya pengiriman</span> dihitung saat checkout.
        </p>
        <button id="cartCheckoutButton" type="button" class="cart-checkout-btn" onclick="goToCheckoutPage()">
          <span>Check out</span>
          <i class="fa-solid fa-shield-halved"></i>
        </button>
      </div>
    </div>

    <div id="cartEmptyView" class="cart-empty-state" style="display:none;">
      <div class="cart-empty-icon">
        <i class="fa-solid fa-cart-shopping"></i>
      </div>
      <h2>Keranjang Anda kosong</h2>
      <p>Belum ada produk obat atau alat kesehatan di dalam keranjang belanja Anda.</p>
      <a href="{{ route('products.index') }}" id="emptyContinueShoppingLink" class="cart-empty-btn">
        <i class="fa-solid fa-bag-shopping"></i> Lanjutkan belanja
      </a>
    </div>
  </div>
</div>
<div class="pbf-popup-backdrop" id="pbfPopupBackdrop" onclick="closePbfPopup(event)">
  <section class="pbf-popup" role="dialog" aria-modal="true" aria-labelledby="pbfPopupTitle">
    <header class="pbf-popup-head">
      <div>
        <span class="pbf-popup-kicker">PENGADAAN STOK APOTEK MEDIX</span>
        <h2 id="pbfPopupTitle"><i class="fa-solid fa-file-signature"></i> Buat Surat Pesanan</h2>
        <p>Isi data distributor dan penanggung jawab untuk pengadaan keluar Apotek Medix.</p>
      </div>
      <button class="pbf-popup-close" type="button" aria-label="Tutup" onclick="closePbfPopup()"><i class="fa-solid fa-xmark"></i></button>
    </header>
    <div id="pbfPopupFormView" class="pbf-popup-body">
      <div class="pbf-popup-summary">
        <h3 class="pbf-popup-section-title">Ringkasan pengadaan</h3>
        <div id="pbfPopupItems"></div>
        <div class="pbf-popup-total"><span>Estimasi harga modal</span><strong id="pbfPopupTotal">Rp0</strong></div>
      </div>
      <div class="pbf-popup-form">
        <h3 class="pbf-popup-section-title">Tujuan distributor</h3>
        <label for="pbfPopupDistributor">Nama distributor / PBF <span>*</span></label>
        <input id="pbfPopupDistributor" type="text" placeholder="Nama distributor atau PBF tujuan" autocomplete="organization">
        <label for="pbfPopupAddress">Alamat distributor <span>*</span></label>
        <textarea id="pbfPopupAddress" rows="2" placeholder="Alamat lengkap distributor"></textarea>
        <h3 class="pbf-popup-section-title" style="margin-top:6px;padding-top:14px;border-top:1px solid #e2e8f0;">Penanggung jawab Apotek Medix</h3>
        <label for="pbfPopupSigner">Nama penandatangan <span>*</span></label>
        <input id="pbfPopupSigner" type="text" value="Apt. Hendri Setiyono, S Farm">
        <label for="pbfPopupTitleField">Jabatan <span>*</span></label>
        <input id="pbfPopupTitleField" type="text" value="Apoteker">
        <div class="pbf-popup-fields-row">
          <div><label for="pbfPopupSia">No. SIA <span>*</span></label><input id="pbfPopupSia" type="text" value="13042600890450001"></div>
          <div><label for="pbfPopupSipa">No. SIPA <span>*</span></label><input id="pbfPopupSipa" type="text" value="1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026"></div>
        </div>
        <div class="pbf-popup-error" id="pbfPopupError" role="alert"></div>
        <p class="pbf-popup-note"><i class="fa-solid fa-circle-info"></i> Surat pesanan dibuat atas nama Apotek Medix, bukan sebagai pesanan pelanggan.</p>
        <button class="pbf-popup-submit" id="pbfPopupSubmit" type="button" onclick="submitPbfPurchaseOrder()"><i class="fa-solid fa-file-signature"></i> Buat Surat Pesanan</button>
      </div>
    </div>
    <div id="pbfPopupResult" class="pbf-popup-result" style="display:none;">
      <i class="fa-solid fa-circle-check"></i>
      <h3>Surat pesanan berhasil dibuat</h3>
      <p>Buka surat untuk meninjau dan mencetak sebelum dikirim ke distributor.</p>
      <button class="pbf-popup-submit" type="button" onclick="showPbfInvoice()"><i class="fa-solid fa-file-invoice"></i> Lihat Invoice</button>
    </div>
  </section>
</div>
<div class="pbf-invoice-backdrop" id="pbfInvoiceBackdrop" onclick="closePbfInvoice(event)">
  <div class="pbf-invoice-panel">
    <div class="pbf-invoice-toolbar"><span>Surat Pesanan Apotek Medix</span><button type="button" onclick="closePbfInvoice()">Kembali</button></div>
    <iframe id="pbfInvoiceFrame" title="Surat Pesanan Apotek Medix"></iframe>
  </div>
</div>
@endsection

@section('scripts')
<script>
const PRODUCT_CATALOG = @json($productCatalog ?? []);
const FALLBACK_LOGO_URL = '{{ asset("LOGO APOTEK MEDIX.PNG") }}';
const CART_CONTEXT_KEY = 'apotekmedix_active_cart_context';

let activeCtx = {
  storageKey: 'apotekmedix_cart_retail',
  kioskOrder: false,
  wholesaleOrder: false,
  adminPbfOrder: false,
  continueShoppingUrl: '{{ route("products.index") }}'
};

try {
  const storedCtx = JSON.parse(localStorage.getItem(CART_CONTEXT_KEY) || '{}');
  if (storedCtx && storedCtx.storageKey) {
    activeCtx = Object.assign({}, activeCtx, storedCtx);
  }
} catch (e) {}

let cartItems = JSON.parse(localStorage.getItem(activeCtx.storageKey) || '[]');
if (!cartItems.length) {
  for (const key of ['apotekmedix_cart_retail', 'apotekmedix_cart', 'apotekmedix_cart_apotek', 'apotekmedix_cart_internal', 'apotekmedix_cart_pbf']) {
    const candidate = JSON.parse(localStorage.getItem(key) || '[]');
    if (candidate.length) {
      activeCtx.storageKey = key;
      cartItems = candidate;
      localStorage.setItem(CART_CONTEXT_KEY, JSON.stringify(activeCtx));
      break;
    }
  }
}

function resolveProductImage(item) {
  const catalogEntry = PRODUCT_CATALOG[item.id] || PRODUCT_CATALOG[String(item.id)];
  if (catalogEntry && catalogEntry.image_url) {
    item.img = catalogEntry.image_url;
    return catalogEntry.image_url;
  }
  if (item.img && !String(item.img).includes('LOGO APOTEK MEDIX')) {
    return item.img;
  }
  return item.img || FALLBACK_LOGO_URL;
}

function resolveProductCategory(item) {
  const catalogEntry = PRODUCT_CATALOG[item.id] || PRODUCT_CATALOG[String(item.id)];
  return item.company || (catalogEntry && catalogEntry.kategori) || '';
}

function formatRp(amount) {
  return 'Rp' + Math.round(Number(amount || 0)).toLocaleString('id-ID');
}

function escapeHtml(text) {
  return String(text || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function saveCartPage() {
  localStorage.setItem(activeCtx.storageKey, JSON.stringify(cartItems));
  const totalQty = cartItems.reduce((sum, item) => sum + (Number(item.qty) || 0), 0);
  const badge = document.getElementById('cartBadgeNav');
  if (badge) {
    badge.textContent = totalQty;
    badge.style.display = totalQty > 0 ? 'flex' : 'none';
  }
}

function renderCartPage() {
  const nonEmptyView = document.getElementById('cartNonEmptyView');
  const emptyView = document.getElementById('cartEmptyView');
  const listEl = document.getElementById('cartPageItems');
  const grandTotalEl = document.getElementById('cartPageGrandTotal');
  const continueLink = document.getElementById('continueShoppingLink');
  const emptyContinueLink = document.getElementById('emptyContinueShoppingLink');

  if (continueLink && activeCtx.continueShoppingUrl) continueLink.href = activeCtx.continueShoppingUrl;
  if (emptyContinueLink && activeCtx.continueShoppingUrl) emptyContinueLink.href = activeCtx.continueShoppingUrl;

  if (!cartItems.length) {
    nonEmptyView.style.display = 'none';
    emptyView.style.display = 'block';
    return;
  }

  emptyView.style.display = 'none';
  nonEmptyView.style.display = 'block';

  let html = '';
  let grandTotal = 0;
  let updatedCatalogImg = false;

  cartItems.forEach((item) => {
    const prevImg = item.img;
    const imgUrl = resolveProductImage(item);
    if (item.img !== prevImg) updatedCatalogImg = true;

    const categoryBadge = resolveProductCategory(item);
    const unit = item.saleUnit || 'box';
    const unitLabel = item.unitLabel || item.unitLabels?.[unit] || unit.charAt(0).toUpperCase() + unit.slice(1);
    const subtotal = Number(item.price || 0) * Number(item.qty || 1);
    const disc = activeCtx.kioskOrder ? 0 : Number(item.discount || 0);
    const lineTotal = Math.max(0, subtotal - disc);
    grandTotal += lineTotal;

    const thumbHtml = `<img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(item.name)}" onerror="this.onerror=null;this.src='${escapeHtml(FALLBACK_LOGO_URL)}';">`;

    const hasMultiUnits = item.unitPrices && Object.keys(item.unitPrices).length > 1;
    const unitSelectorHtml = hasMultiUnits
      ? `<div>
          <select class="cart-unit-select" onchange="changeItemUnit(${item.id}, '${unit}', this.value)">
            ${Object.entries(item.unitPrices).map(([uKey, uPrice]) => {
              const uLbl = item.unitLabels?.[uKey] || (uKey.charAt(0).toUpperCase() + uKey.slice(1));
              return `<option value="${uKey}" ${uKey === unit ? 'selected' : ''}>${escapeHtml(uLbl)} (${formatRp(uPrice)})</option>`;
            }).join('')}
          </select>
        </div>`
      : '';

    const hasExtraData = Boolean(item.note || disc > 0);
    const extraId = `extra-${item.id}-${unit}`;

    html += `
      <div class="cart-row">
        <div class="cart-prod-cell">
          <a href="/medicines/${item.id}" class="cart-prod-thumb" title="${escapeHtml(item.name)}">${thumbHtml}</a>
          <div class="cart-prod-meta">
            ${categoryBadge ? `<span class="cart-prod-badge">${escapeHtml(categoryBadge)}</span>` : ''}
            <h3 class="cart-prod-name">
              <a href="/medicines/${item.id}">${escapeHtml(item.name)}</a>
            </h3>
            <div class="cart-prod-price">${formatRp(item.price)}${unitLabel ? ' / ' + escapeHtml(unitLabel) : ''}</div>
            ${unitSelectorHtml}
            ${activeCtx.adminPbfOrder ? '' : `
              <button type="button" class="cart-extra-toggle" onclick="toggleExtraBox('${extraId}')">
                <i class="fa-regular fa-pen-to-square"></i>
                <span>${hasExtraData ? 'Ubah catatan / potongan' : 'Tambah catatan produk'}</span>
              </button>
              <div id="${extraId}" class="cart-extra-box ${hasExtraData ? 'open' : ''}">
                <input type="text" class="cart-extra-inp" placeholder="Catatan untuk produk ini..." value="${escapeHtml(item.note || '')}" onchange="updateItemNote(${item.id}, '${unit}', this.value)">
                ${activeCtx.kioskOrder ? '' : `
                <input type="number" min="0" class="cart-extra-inp" placeholder="Ajuan potongan harga (Rp)" value="${disc > 0 ? disc : ''}" onchange="updateItemDiscount(${item.id}, '${unit}', this.value)">
                `}
              </div>
            `}
          </div>
        </div>

        <div class="cart-qty-cell">
          <div class="cart-qty-box">
            <button type="button" class="cart-qty-btn" onclick="stepItemQty(${item.id}, -1, '${unit}')" aria-label="Kurangi jumlah">&minus;</button>
            <input type="number" class="cart-qty-input" min="1" ${item.availableQty != null ? `max="${item.availableQty}"` : ''} value="${item.qty}" onchange="inputItemQty(${item.id}, this.value, '${unit}')" aria-label="Jumlah">
            <button type="button" class="cart-qty-btn" onclick="stepItemQty(${item.id}, 1, '${unit}')" aria-label="Tambah jumlah">+</button>
          </div>
          <button type="button" class="cart-remove-btn" onclick="deleteCartItem(${item.id}, '${unit}')" title="Hapus produk" aria-label="Hapus produk">
            <i class="fa-regular fa-trash-can"></i>
          </button>
        </div>

        <div class="cart-total-cell">
          ${disc > 0 ? `<div style="font-size:0.78rem;color:#94a3b8;text-decoration:line-through;font-weight:500;">${formatRp(subtotal)}</div>` : ''}
          ${formatRp(lineTotal)}
        </div>
      </div>
    `;
  });

  if (updatedCatalogImg) {
    localStorage.setItem(activeCtx.storageKey, JSON.stringify(cartItems));
  }

  listEl.innerHTML = html;
  grandTotalEl.textContent = formatRp(grandTotal);
}

function toggleExtraBox(id) {
  const el = document.getElementById(id);
  if (el) el.classList.toggle('open');
}

function stepItemQty(id, delta, unit = 'box') {
  const item = cartItems.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!item) return;
  const nextQty = Number(item.qty || 1) + delta;
  if (delta > 0 && item.availableQty != null && nextQty > item.availableQty) {
    alert('Stok untuk satuan ' + (item.unitLabel || unit) + ' tidak mencukupi.');
    return;
  }
  if (nextQty <= 0) {
    deleteCartItem(id, unit);
    return;
  }
  item.qty = nextQty;
  saveCartPage();
  renderCartPage();
}

function inputItemQty(id, value, unit = 'box') {
  const item = cartItems.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!item) return;
  let nextQty = Math.max(1, parseInt(value, 10) || 1);
  if (item.availableQty != null && nextQty > item.availableQty) {
    nextQty = item.availableQty;
    alert('Jumlah disesuaikan dengan stok yang tersedia.');
  }
  item.qty = nextQty;
  saveCartPage();
  renderCartPage();
}

function deleteCartItem(id, unit = 'box') {
  cartItems = cartItems.filter(i => !(i.id === id && (i.saleUnit || 'box') === unit));
  saveCartPage();
  renderCartPage();
}

function updateItemNote(id, unit, note) {
  const item = cartItems.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!item) return;
  item.note = String(note || '').trim();
  saveCartPage();
}

function updateItemDiscount(id, unit, value) {
  if (activeCtx.kioskOrder) return;
  const item = cartItems.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!item) return;
  const raw = Math.max(0, parseInt(String(value).replace(/[^0-9]/g, ''), 10) || 0);
  item.discount = Math.min(raw, item.price * item.qty);
  saveCartPage();
  renderCartPage();
}

function changeItemUnit(id, oldUnit, newUnit) {
  const item = cartItems.find(i => i.id === id && (i.saleUnit || 'box') === oldUnit);
  if (!item || !item.unitPrices || !item.unitFactors) return;
  const factor = item.unitFactors[newUnit];
  const available = item.stockUnits === null ? null : Math.floor(Number(item.stockUnits) / factor);
  if (available !== null && available < 1) {
    alert('Stok untuk satuan tersebut sudah habis.');
    renderCartPage();
    return;
  }
  const existing = cartItems.find(i => i.id === id && (i.saleUnit || 'box') === newUnit);
  if (existing) {
    existing.unitLabel = item.unitLabels?.[newUnit] || newUnit.charAt(0).toUpperCase() + newUnit.slice(1);
    existing.unitLabels = item.unitLabels;
    existing.qty += item.qty;
    existing.availableQty = available;
    if (available !== null && existing.qty > available) {
      existing.qty = available;
    }
    cartItems = cartItems.filter(i => i !== item);
  } else {
    item.saleUnit = newUnit;
    item.unitLabel = item.unitLabels?.[newUnit] || newUnit.charAt(0).toUpperCase() + newUnit.slice(1);
    item.price = Number(item.unitPrices[newUnit]);
    item.availableQty = available;
    if (available !== null && item.qty > available) {
      item.qty = available;
    }
  }
  saveCartPage();
  renderCartPage();
}

function openCart() {
  renderCartPage();
}

function goToCheckoutPage() {
  if (!cartItems.length) return;
  if (activeCtx.adminPbfOrder) {
    openPbfPurchaseOrderPopup();
    return;
  }
  window.location.href = '{{ route("checkout.index") }}';
}

function openPbfPurchaseOrderPopup() {
  const items = document.getElementById('pbfPopupItems');
  let total = 0;
  items.innerHTML = cartItems.map((item, index) => {
    const subtotal = Number(item.price || 0) * Number(item.qty || 0);
    total += subtotal;
    const unitLabel = item.unitLabel || item.unitLabels?.[item.saleUnit || 'box'] || 'Pcs';
    return `<div class="pbf-popup-summary-item">
      <span>${index + 1}. ${escapeHtml(item.name)} <span style="color:#64748b;">× ${Number(item.qty)} ${escapeHtml(unitLabel)}</span></span>
      <strong>${formatRp(subtotal)}</strong>
    </div>`;
  }).join('');
  document.getElementById('pbfPopupTotal').textContent = formatRp(total);
  document.getElementById('pbfPopupFormView').style.display = 'grid';
  document.getElementById('pbfPopupResult').style.display = 'none';
  document.getElementById('pbfPopupError').style.display = 'none';
  document.getElementById('pbfPopupSubmit').disabled = false;
  document.getElementById('pbfPopupBackdrop').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closePbfPopup(event) {
  if (event && event.target !== event.currentTarget) return;
  const backdrop = document.getElementById('pbfPopupBackdrop');
  if (!backdrop) return;
  backdrop.classList.remove('open');
  closePbfInvoice();
  document.body.style.overflow = '';
}

function showPbfInvoice() {
  const invoiceBase = activeCtx.adminPurchaseOrderBase;
  if (!invoiceBase || !window.createdPbfOrderId) {
    throw new Error('Tautan surat pesanan tidak tersedia.');
  }
  document.getElementById('pbfInvoiceFrame').src = invoiceBase.replace('__ORDER__', window.createdPbfOrderId);
  document.getElementById('pbfInvoiceBackdrop').classList.add('open');
}

function closePbfInvoice(event) {
  if (event && event.target !== event.currentTarget) return;
  const backdrop = document.getElementById('pbfInvoiceBackdrop');
  if (!backdrop) return;
  backdrop.classList.remove('open');
  document.getElementById('pbfInvoiceFrame').src = 'about:blank';
}

async function submitPbfPurchaseOrder() {
  const errorPanel = document.getElementById('pbfPopupError');
  const submitButton = document.getElementById('pbfPopupSubmit');
  const distributor = document.getElementById('pbfPopupDistributor').value.trim();
  const address = document.getElementById('pbfPopupAddress').value.trim();
  const signer = document.getElementById('pbfPopupSigner').value.trim();
  const title = document.getElementById('pbfPopupTitleField').value.trim();
  const sia = document.getElementById('pbfPopupSia').value.trim();
  const sipa = document.getElementById('pbfPopupSipa').value.trim();
  errorPanel.style.display = 'none';
  if (!distributor || !address || !signer || !title || !sia || !sipa) {
    errorPanel.textContent = 'Lengkapi data distributor dan semua data penandatangan.';
    errorPanel.style.display = 'block';
    return;
  }

  const total = cartItems.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0);
  const payload = {
    buyer_type: 'apotik',
    buyer_name: 'Apotek Medix',
    requester_name: signer,
    apj: title,
    sia,
    sipa,
    phone: '085890007359',
    address: 'ITC Cempaka Mas LT.1 No.88, Jakarta Pusat',
    pbf_name: distributor,
    pbf_address: address,
    items: cartItems.map(item => ({
      id: item.id,
      nama_obat: item.name,
      quantity: item.qty,
      harga: item.price,
      sale_unit: item.saleUnit || 'box',
      catatan: item.note || ''
    })),
    total,
    original_total: total,
    discounted_total: total,
    approval_status: 'pending',
    transaction_channel: 'online',
    is_pbf_purchase_order: true
  };

  submitButton.disabled = true;
  submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Membuat surat pesanan...';
  try {
    const response = await fetch('{{ route("orders.history.store") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });
    const result = await response.json();
    if (!response.ok) {
      const messages = Object.values(result.errors || {}).flat().join(' ');
      throw new Error(messages || result.message || 'Surat pesanan gagal dibuat.');
    }

    window.createdPbfOrderId = result.id;
    cartItems = [];
    saveCartPage();
    renderCartPage();
    document.getElementById('pbfPopupFormView').style.display = 'none';
    document.getElementById('pbfPopupResult').style.display = '';
  } catch (error) {
    errorPanel.textContent = error instanceof Error ? error.message : 'Surat pesanan gagal dibuat.';
    errorPanel.style.display = 'block';
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fa-solid fa-file-signature"></i> Buat Surat Pesanan';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  saveCartPage();
  renderCartPage();
  if (activeCtx.adminPbfOrder) {
    document.getElementById('cartBannerTitle').textContent = 'Keranjang Pengadaan Apotek Medix';
    document.getElementById('cartBannerDescription').textContent = 'Atur jumlah produk untuk pengadaan keluar Apotek Medix, lalu buat surat pesanan ke distributor.';
    document.querySelector('.cart-page-title').textContent = 'Daftar Produk Pengadaan';
    document.getElementById('cartCheckoutNote').textContent = 'Pengadaan ini menggunakan harga modal dan akan dibuatkan surat pesanan atas nama Apotek Medix.';
    document.querySelector('#cartCheckoutButton span').textContent = 'Buat Surat Pesanan';
    document.querySelector('#cartCheckoutButton i').className = 'fa-solid fa-file-signature';
  }
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closePbfInvoice();
      closePbfPopup();
    }
  });
});
</script>
@endsection
