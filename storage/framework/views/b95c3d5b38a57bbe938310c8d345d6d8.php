<?php $__env->startSection('title', 'Checkout & Pembayaran - Apotek Medix'); ?>

<?php
  $fullName = trim((string) ($defaultShippingAddress?->name ?? (auth()->check() ? auth()->user()->name : '')));
  $nameParts = $fullName !== '' ? preg_split('/\s+/', $fullName, 2) : [];
  $defaultFirstName = count($nameParts) > 1 ? $nameParts[0] : '';
  $defaultLastName = count($nameParts) > 1 ? $nameParts[1] : ($nameParts[0] ?? '');
  $defaultContact = $defaultShippingAddress?->email ?? (auth()->check() ? auth()->user()->email : ($defaultShippingAddress?->phone ?? ''));
?>

<?php $__env->startSection('styles'); ?>
<style>
  /* ===== BANNER HEADER APOTEK MEDIX ===== */
  .products-header.checkout-banner {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 72%, #12394A 100%) !important;
    padding: 2.15rem 0 1.9rem !important;
    position: relative;
    overflow: hidden;
    border-bottom: 2px solid rgba(6, 182, 212, 0.35);
  }
  .products-header.checkout-banner::before {
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
  .co-banner-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .breadcrumb-custom {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 0.6rem;
    font-size: 0.86rem;
    flex-wrap: wrap;
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
  .co-banner-title {
    font-size: clamp(1.65rem, 2.8vw, 2.25rem);
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 0.3rem;
    letter-spacing: -0.01em;
  }
  .co-banner-sub {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.92rem;
    margin: 0;
  }
  .co-back-cart-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.1rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(6, 182, 212, 0.55);
    color: #ffffff;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    backdrop-filter: blur(6px);
  }
  .co-back-cart-btn:hover {
    background: rgba(6, 182, 212, 0.2);
    border-color: #06B6D4;
    color: #ffffff;
    transform: translateY(-1px);
  }

  /* ===== MAIN 2-COLUMN CHECKOUT CONTAINER ===== */
  .co-main-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1.5rem 5rem;
    position: relative;
    z-index: 1;
  }
  .co-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
    gap: 2rem;
    align-items: start;
  }

  /* ===== LEFT CARD (FORM) ===== */
  .co-left-col {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 2.25rem 2.35rem;
    position: relative;
    overflow: hidden;
  }
  .co-left-col::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #0F172A 0%, #06B6D4 60%, #10B981 100%);
  }
  .co-left-inner {
    width: 100%;
  }

  /* ===== RIGHT CARD (ORDER SUMMARY) ===== */
  .co-right-col {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 2rem 1.85rem;
    position: sticky;
    top: 90px;
    overflow: hidden;
  }
  .co-right-col::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #06B6D4 0%, #0E7490 50%, #0F172A 100%);
  }
  .co-right-inner {
    width: 100%;
  }
  .co-summary-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
    border-bottom: 1.5px solid #f1f5f9;
  }
  .co-summary-head h3 {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 700;
    color: #0F172A;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .co-summary-head h3 i {
    color: #0E7490;
  }

  /* ===== SECTIONS & HEADINGS ===== */
  .co-section {
    margin-bottom: 2rem;
    padding-bottom: 1.75rem;
    border-bottom: 1px solid #f1f5f9;
  }
  .co-section:last-of-type {
    border-bottom: none;
    padding-bottom: 0;
  }
  .co-sec-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 0.9rem;
  }
  .co-sec-title {
    font-size: 1.18rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0;
  }
  .co-sec-sub {
    font-size: 0.83rem;
    color: #64748b;
    margin: -0.45rem 0 0.9rem;
  }
  .co-sub-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0F172A;
    margin: 1.35rem 0 0.65rem;
  }
  .co-login-link {
    font-size: 0.85rem;
    font-weight: 600;
    color: #0E7490;
    text-decoration: underline;
    text-underline-offset: 3px;
  }
  .co-login-link:hover {
    color: #0F172A;
  }

  /* ===== INPUTS & FLOATING FIELDS ===== */
  .co-field {
    position: relative;
    margin-bottom: 0.75rem;
  }
  .co-input, .co-select {
    width: 100%;
    height: 48px;
    padding: 0.75rem 0.95rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #0F172A;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
  }
  .co-input:focus, .co-select:focus {
    border-color: #06B6D4;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.14);
  }
  .co-field.has-label .co-select,
  .co-field.has-label .co-input {
    padding-top: 1.35rem;
    padding-bottom: 0.35rem;
  }
  .co-float-lbl {
    position: absolute;
    top: 6px;
    left: 0.95rem;
    font-size: 0.69rem;
    font-weight: 600;
    color: #64748b;
    pointer-events: none;
  }
  .co-field-icon {
    position: absolute;
    right: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
    pointer-events: none;
  }
  .co-help-wrap {
    position: absolute;
    right: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 0.95rem;
    cursor: pointer;
    z-index: 5;
  }
  .co-help-wrap:hover,
  .co-help-wrap:focus {
    color: #0F172A;
    outline: none;
  }
  .co-help-tooltip {
    position: absolute;
    bottom: calc(100% + 10px);
    right: -12px;
    width: 215px;
    background: #18181b;
    color: #ffffff;
    font-size: 0.76rem;
    font-weight: 500;
    line-height: 1.4;
    text-align: center;
    padding: 0.6rem 0.75rem;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
    opacity: 0;
    visibility: hidden;
    transform: translateY(4px);
    transition: all 0.16s ease;
    pointer-events: none;
  }
  .co-help-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    right: 16px;
    border-width: 6px;
    border-style: solid;
    border-color: #18181b transparent transparent transparent;
  }
  .co-help-wrap:hover .co-help-tooltip,
  .co-help-wrap:focus .co-help-tooltip,
  .co-field:focus-within .co-help-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }
  .co-billing-panel {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 1rem;
  }
  .co-billing-panel .co-input {
    background: #ffffff;
  }
  .co-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
  }

  /* ===== CHECKBOX ===== */
  .co-check-label {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.85rem;
    color: #334155;
    margin-top: 0.15rem;
    user-select: none;
  }
  .co-check-label input[type="checkbox"] {
    width: 17px;
    height: 17px;
    accent-color: #0E7490;
    border-radius: 4px;
    cursor: pointer;
  }

  /* ===== DELIVERY SEGMENTED TOGGLE ===== */
  .co-delivery-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 4px;
    gap: 4px;
    margin-bottom: 1rem;
  }
  .co-delivery-tab {
    height: 44px;
    border: 1.5px solid transparent;
    border-radius: 9px;
    background: transparent;
    color: #475569;
    font-size: 0.88rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.18s;
  }
  .co-delivery-tab.active {
    background: #ffffff;
    border-color: #06B6D4;
    color: #0F172A;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
  }
  .co-delivery-tab.active i {
    color: #0E7490;
  }

  /* ===== SHIPPING METHOD BOX ===== */
  .co-ship-box {
    background: #f8fafc;
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    padding: 1rem 1.05rem;
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.45;
  }
  .co-ship-box.ready {
    background: #ECFEFF;
    border: 1.5px solid #06B6D4;
    color: #0F172A;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
  }

  /* ===== PAYMENT OPTIONS IN RIGHT COLUMN ===== */
  .co-right-payment-box {
    margin-top: 1.5rem;
    padding-top: 1.4rem;
    border-top: 1.5px solid #e2e8f0;
  }
  .co-right-pay-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.35rem;
  }
  .co-right-pay-head h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0F172A;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .co-right-pay-head h3 i {
    color: #0E7490;
  }
  .co-right-pay-sub {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0 0 0.85rem;
  }
  .co-pay-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
    margin-bottom: 0.85rem;
  }
  .co-pay-opt {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.78rem 0.9rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 11px;
    background: #f8fafc;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 600;
    text-align: left;
    transition: all 0.16s ease;
  }
  .co-pay-opt i {
    font-size: 1.05rem;
    color: #64748b;
    width: 20px;
    text-align: center;
    transition: color 0.16s ease;
  }
  .co-pay-opt:hover {
    border-color: #06B6D4;
    background: #ffffff;
  }
  .co-pay-opt.active {
    background: #ECFEFF;
    border-color: #06B6D4;
    color: #0F172A;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(6, 182, 212, 0.14);
  }
  .co-pay-opt.active i {
    color: #0E7490;
  }

  /* ===== BILLING ADDRESS RADIO GROUP ===== */
  .co-radio-group {
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    overflow: visible;
    background: #ffffff;
  }
  .co-radio-row:first-child {
    border-radius: 10px 10px 0 0;
  }
  .co-radio-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.92rem 1.05rem;
    font-size: 0.88rem;
    font-weight: 500;
    color: #0F172A;
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.15s;
  }
  .co-radio-row:last-child {
    border-bottom: none;
  }
  .co-radio-row.active {
    background: #ECFEFF;
    font-weight: 600;
  }
  .co-radio-row input[type="radio"] {
    width: 17px;
    height: 17px;
    accent-color: #0E7490;
    margin: 0;
  }

  /* ===== SUBMIT BUTTON & FOOTER ===== */
  .co-submit-btn {
    width: 100%;
    height: 52px;
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    color: #ffffff;
    border: 1.5px solid #06B6D4;
    border-radius: 12px;
    font-size: 0.98rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    margin-top: 0.75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.16);
    transition: all 0.2s;
  }
  .co-submit-btn:hover {
    background: linear-gradient(135deg, #1E293B 0%, #0E7490 100%);
    transform: translateY(-2px);
    box-shadow: 0 12px 26px rgba(6, 182, 212, 0.24);
  }
  .co-submit-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
  }
  .co-error-box {
    display: none;
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    color: #991b1b;
    padding: 0.9rem 1rem;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 500;
    margin-top: 1rem;
  }
  .co-footer-links {
    margin-top: 1.75rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  .co-footer-links a {
    font-size: 0.8rem;
    font-weight: 600;
    color: #0E7490;
    text-decoration: underline;
    text-underline-offset: 3px;
  }

  /* ===== RIGHT COLUMN SUMMARY ITEMS ===== */
  .co-sum-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.15rem;
    padding-bottom: 1.15rem;
    border-bottom: 1px solid #f1f5f9;
  }
  .co-sum-item:last-child {
    border-bottom: none;
    padding-bottom: 0.25rem;
  }
  .co-sum-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
  }
  .co-sum-thumb {
    width: 68px;
    height: 68px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
    border: 1.5px solid #e2e8f0;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
  }
  .co-sum-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
  }
  .co-sum-qty-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    min-width: 23px;
    height: 23px;
    padding: 0 6px;
    border-radius: 999px;
    background: #0F172A;
    border: 1.5px solid #06B6D4;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
  }
  .co-sum-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0F172A;
    line-height: 1.35;
  }
  .co-sum-meta {
    font-size: 0.76rem;
    font-weight: 600;
    color: #0E7490;
    margin-top: 3px;
  }
  .co-sum-price {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0F172A;
    white-space: nowrap;
  }

  .co-disc-row {
    display: flex;
    gap: 0.65rem;
    margin: 1.25rem 0 1.35rem;
    padding-top: 0.75rem;
    border-top: 1.5px solid #f1f5f9;
  }
  .co-disc-inp {
    flex: 1;
    height: 46px;
    padding: 0 0.9rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
    font-size: 0.88rem;
    color: #0F172A;
    outline: none;
    transition: border-color 0.15s, background 0.15s;
  }
  .co-disc-inp:focus {
    border-color: #06B6D4;
    background: #ffffff;
  }
  .co-disc-btn {
    height: 46px;
    padding: 0 1.2rem;
    border: 1.5px solid #06B6D4;
    border-radius: 10px;
    background: #ECFEFF;
    color: #0E7490;
    font-size: 0.86rem;
    font-weight: 700;
    transition: all 0.15s;
  }
  .co-disc-btn:hover {
    background: #0F172A;
    color: #ffffff;
  }

  .co-totals-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.88rem;
    color: #475569;
    margin-bottom: 0.65rem;
  }
  .co-totals-grand {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-top: 1rem;
    padding-top: 1.1rem;
    border-top: 1.5px solid #e2e8f0;
  }
  .co-totals-grand-lbl {
    font-size: 1.12rem;
    font-weight: 800;
    color: #0F172A;
  }
  .co-totals-grand-val {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0E7490;
  }
  .co-totals-grand-val small {
    font-size: 0.74rem;
    font-weight: 600;
    color: #64748b;
    margin-right: 0.35rem;
  }

  /* ===== SUCCESS PANEL ===== */
  .co-success-card {
    display: none;
    background: #f8fafc;
    border: 1.5px solid #06B6D4;
    border-radius: 14px;
    padding: 1.75rem;
    margin-top: 0.5rem;
  }
  .co-action-btn {
    width: 100%;
    padding: 0.88rem 1rem;
    border-radius: 10px;
    font-size: 0.92rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
    margin-bottom: 0.75rem;
    border: none;
    transition: transform 0.15s;
  }
  .co-action-btn:hover {
    transform: translateY(-1px);
  }

  @media (max-width: 960px) {
    .co-layout {
      grid-template-columns: 1fr;
    }
    .co-right-col {
      position: static;
    }
    .co-left-col {
      padding: 1.65rem 1.25rem;
    }
    .co-row-2 {
      grid-template-columns: 1fr;
    }
  }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="products-header checkout-banner">
  <i class="fa-solid fa-credit-card header-deco-icon header-deco-icon-1"></i>
  <i class="fa-solid fa-truck-fast header-deco-icon header-deco-icon-2"></i>
  <i class="fa-solid fa-shield-heart header-deco-icon header-deco-icon-3"></i>
  <div class="co-banner-inner">
    <div>
      <div class="breadcrumb-custom">
        <a href="<?php echo e(route('home')); ?>"><i class="fa-solid fa-house"></i> Beranda</a>
        <span>/</span>
        <a href="<?php echo e(route('cart.index')); ?>">Keranjang Anda</a>
        <span>/</span>
        <span class="current">Pembayaran &amp; Checkout</span>
      </div>
      <h1 class="co-banner-title">Checkout &amp; Pembayaran</h1>
      <p class="co-banner-sub">Lengkapi alamat pengantaran dan metode pembayaran untuk menyelesaikan pesanan Anda.</p>
    </div>
    <a href="<?php echo e(route('cart.index')); ?>" class="co-back-cart-btn">
      <i class="fa-solid fa-bag-shopping"></i> Kembali ke Keranjang
    </a>
  </div>
</section>

<!-- MAIN CHECKOUT SPLIT VIEW -->
<div class="co-main-wrap">
  <div class="co-layout">
    <!-- LEFT COLUMN: FORMS -->
    <div class="co-left-col">
      <div class="co-left-inner">

        <div id="coFormWrapper">
          <!-- Fields khusus Admin PBF Purchase Order (hanya muncul jika mode Admin PBF) -->
          <div id="coAdminPbfFields" class="co-section" style="display:none;">
            <div class="co-sec-head">
              <h2 class="co-sec-title">Data PBF Tujuan</h2>
            </div>
            <div class="co-field">
              <input type="text" id="co_pbf_name" class="co-input" placeholder="Nama PBF tujuan *">
            </div>
            <div class="co-field">
              <input type="text" id="co_pbf_address" class="co-input" placeholder="Alamat PBF tujuan *">
            </div>
            <div class="co-row-2">
              <div class="co-field">
                <input type="text" id="co_signer_name" class="co-input" value="Apt. Hendri Setiyono, S Farm" placeholder="Nama Apoteker *">
              </div>
              <div class="co-field">
                <input type="text" id="co_signer_title" class="co-input" value="Apoteker" placeholder="Jabatan *">
              </div>
            </div>
            <div class="co-row-2">
              <div class="co-field">
                <input type="text" id="co_signer_sia" class="co-input" value="13042600890450001" placeholder="Nomor SIA *">
              </div>
              <div class="co-field">
                <input type="text" id="co_signer_sipa" class="co-input" value="1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026" placeholder="Nomor SIPA *">
              </div>
            </div>
          </div>

          <!-- PENGANTARAN -->
          <div class="co-section">
            <div class="co-sec-head">
              <h2 class="co-sec-title">Pengantaran</h2>
            </div>

            <div class="co-delivery-tabs" id="coDeliveryTabs">
              <button type="button" class="co-delivery-tab active" id="tabShipBtn" onclick="setDeliveryMode('ship')">
                <i class="fa-solid fa-truck-fast"></i> Kirim
              </button>
              <button type="button" class="co-delivery-tab" id="tabPickupBtn" onclick="setDeliveryMode('pickup')">
                <i class="fa-solid fa-location-dot"></i> Ambil di tempat
              </button>
            </div>

            <div class="co-field">
              <input type="text" id="co_full_name" class="co-input" placeholder="Nama lengkap" value="<?php echo e($fullName); ?>">
            </div>

            <div id="coShippingAddressFields">
              <div class="co-field">
                <input type="text" id="co_address" class="co-input" placeholder="Alamat lengkap" value="<?php echo e($defaultShippingAddress?->address); ?>" oninput="updateShippingMethodStatus()">
              </div>
            </div>

            <div id="coPickupLocationBox" class="co-ship-box ready" style="display:none;margin-bottom:0.75rem;">
              <div>
                <div style="font-weight:700;color:#0F172A;">Apotek Medix Bogor</div>
                <div style="font-size:0.8rem;color:#475569;font-weight:500;">Jl. Raya Ciawi Prapatan No.256 C, Kota Bogor, Jawa Barat 16146</div>
              </div>
              <span style="font-weight:800;color:#0E7490;">Gratis</span>
            </div>

            <div class="co-field" style="margin-bottom:0;">
              <input type="tel" id="co_phone" class="co-input" placeholder="Nomor HP / WhatsApp" value="<?php echo e($defaultShippingAddress?->phone); ?>" style="padding-right:2.5rem;">
              <span class="co-help-wrap" tabindex="0" aria-label="Info nomor telepon">
                <i class="fa-regular fa-circle-question"></i>
                <span class="co-help-tooltip">Jika kami ingin menghubungi Anda terkait pesanan</span>
              </span>
            </div>

            <h3 class="co-sub-title">Alamat penagihan</h3>
            <div class="co-radio-group">
              <label class="co-radio-row active" id="radioBillSameRow">
                <input type="radio" name="billing_addr_mode" value="same" checked onchange="onBillingModeChange()">
                <span>Sama dengan alamat pengiriman</span>
              </label>
              <label class="co-radio-row" id="radioBillDiffRow">
                <input type="radio" name="billing_addr_mode" value="different" onchange="onBillingModeChange()">
                <span>Pakai alamat penagihan lain</span>
              </label>

              <div id="coBillingCustomFields" class="co-billing-panel" style="display:none;border-radius:0 0 10px 10px;">
                <div class="co-field">
                  <input type="text" id="co_billing_name" class="co-input" placeholder="Nama lengkap">
                </div>
                <div class="co-field">
                  <input type="text" id="co_billing_address" class="co-input" placeholder="Alamat penagihan lengkap">
                </div>
                <div class="co-field" style="margin-bottom:0;">
                  <input type="tel" id="co_billing_phone" class="co-input" placeholder="No. Telepon (opsional)" style="padding-right:2.5rem;">
                  <span class="co-help-wrap" tabindex="0" aria-label="Info nomor telepon">
                    <i class="fa-regular fa-circle-question"></i>
                    <span class="co-help-tooltip">Jika kami ingin menghubungi Anda terkait pesanan</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- PANEL KONFIRMASI SETELAH ORDER BERHASIL -->
        <div id="coSuccessPanel" class="co-success-card">
          <div style="display:flex;align-items:center;gap:0.85rem;margin-bottom:0.9rem;">
            <div style="width:46px;height:46px;border-radius:50%;background:#d1fae5;color:#065f46;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
              <i class="fa-solid fa-check"></i>
            </div>
            <div>
              <h2 style="margin:0;font-size:1.18rem;font-weight:800;color:#0F172A;">Pesanan Berhasil Dibuat!</h2>
              <p id="coOrderNumberLabel" style="margin:2px 0 0;font-size:0.85rem;color:#0E7490;font-weight:700;">ID Pesanan #--</p>
            </div>
          </div>
          <p id="coSuccessDesc" style="font-size:0.9rem;color:#334155;line-height:1.55;margin-bottom:1.25rem;">
            Pesanan Anda telah tercatat di sistem Apotek Medix. Silakan buka invoice atau lanjutkan konfirmasi melalui WhatsApp:
          </p>

          <a id="coInvoiceBtn" href="#" target="_blank" rel="noopener" class="co-action-btn" style="background:#0F172A;border:1.5px solid #06B6D4;color:#ffffff;">
            <i class="fa-solid fa-file-invoice"></i> Lihat Invoice / Cetak Struk
          </a>
          <button type="button" id="coWhatsappBtn" onclick="openWhatsAppCheckout()" class="co-action-btn" style="background:#10B981;color:#ffffff;">
            <i class="fa-brands fa-whatsapp" style="font-size:1.15rem;"></i> Konfirmasi Pembayaran via WhatsApp
          </button>
          <a href="<?php echo e(route('products.index')); ?>" class="co-action-btn" style="background:#ffffff;color:#0F172A;border:1.5px solid #cbd5e1;margin-bottom:0;">
            Kembali ke Katalog Produk
          </a>
        </div>

        <div class="co-footer-links">
          <a href="<?php echo e(route('about')); ?>">Ketentuan dan kebijakan Apotek Medix</a>
          <a href="<?php echo e(route('cart.index')); ?>">Ubah isi keranjang</a>
        </div>

      </div>
    </div>

    <!-- RIGHT COLUMN: ORDER SUMMARY & PAYMENT -->
    <div class="co-right-col">
      <div class="co-right-inner">
        <div class="co-summary-head">
          <h3><i class="fa-solid fa-receipt"></i> Ringkasan Pesanan</h3>
          <a href="<?php echo e(route('cart.index')); ?>" style="font-size:0.8rem;font-weight:600;color:#0E7490;text-decoration:underline;">Ubah</a>
        </div>

        <div id="coSummaryItems"></div>

        <div class="co-disc-row" style="margin-bottom:0.5rem;">
          <input type="text" id="coDiscountCodeInput" class="co-disc-inp" placeholder="Masukkan kode diskon" style="text-transform:uppercase;">
          <button type="button" id="coDiscountApplyBtn" class="co-disc-btn" onclick="applyCheckoutDiscount()">Pakai</button>
        </div>
        <div id="coDiscountMsgBox" style="display:none;font-size:0.8rem;font-weight:600;margin-bottom:1rem;padding:0.55rem 0.75rem;border-radius:8px;"></div>

        <div class="co-totals-line">
          <span>Subtotal &middot; <span id="coTotalItemsCount">0</span> item</span>
          <span id="coSubtotalDisplay" style="font-weight:600;color:#0F172A;">Rp 0,00</span>
        </div>

        <div class="co-totals-line" id="coDiscountRow" style="display:none;color:#059669;font-weight:600;">
          <span>Diskon</span>
          <span id="coDiscountDisplay">-Rp 0,00</span>
        </div>

        <div class="co-totals-line">
          <span>Pengiriman <i class="fa-regular fa-circle-question" style="color:#94a3b8;font-size:0.78rem;"></i></span>
          <span id="coShippingSummaryText" style="color:#64748b;font-size:0.82rem;">Masukkan alamat pengiriman</span>
        </div>

        <div class="co-totals-grand">
          <span class="co-totals-grand-lbl">Total</span>
          <span class="co-totals-grand-val"><small>IDR</small><span id="coGrandTotalDisplay">Rp 0,00</span></span>
        </div>

        <!-- PEMBAYARAN DI SEBELAH KANAN -->
        <div id="coPaymentRightWrapper" class="co-right-payment-box">
          <div class="co-right-pay-head">
            <h3><i class="fa-solid fa-wallet"></i> Pembayaran</h3>
            <span style="font-size:0.75rem;color:#0E7490;font-weight:600;"><i class="fa-solid fa-lock"></i> Aman</span>
          </div>
          <p class="co-right-pay-sub">Pilih metode pembayaran pesanan Anda:</p>

          <input type="hidden" id="co_payment_method" value="QRIS">

          <div class="co-pay-grid" id="coPayOptionsGrid">
            <button type="button" class="co-pay-opt active" data-pay-method="QRIS" onclick="selectPaymentMethod('QRIS')">
              <i class="fa-solid fa-qrcode"></i>
              <span>QRIS</span>
            </button>
            <button type="button" class="co-pay-opt" data-pay-method="Transfer" onclick="selectPaymentMethod('Transfer')">
              <i class="fa-solid fa-building-columns"></i>
              <span>Transfer</span>
            </button>
            <button type="button" class="co-pay-opt" data-pay-method="Cash" onclick="selectPaymentMethod('Cash')">
              <i class="fa-solid fa-money-bill-wave"></i>
              <span>Cash</span>
            </button>
            <button type="button" class="co-pay-opt" id="coTempoOption" data-pay-method="Tempo" onclick="selectPaymentMethod('Tempo')">
              <i class="fa-solid fa-calendar-day"></i>
              <span>Tempo</span>
            </button>
          </div>

          <div id="coTempoDaysWrap" style="display:none;margin-bottom:0.75rem;">
            <input type="number" id="co_payment_term_days" min="1" step="1" class="co-input" placeholder="Jumlah hari tempo (contoh: 14)" style="height:44px;background:#fff;">
          </div>

          <div id="coErrorBox" class="co-error-box"></div>

          <button type="button" id="coSubmitBtn" class="co-submit-btn" onclick="submitCheckoutOrder()">
            <span>Bayar sekarang</span>
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
  const PRODUCT_CATALOG = <?php echo json_encode($productCatalog ?? [], 15, 512) ?>;
  const FALLBACK_LOGO_URL = '<?php echo e(asset("LOGO APOTEK MEDIX.PNG")); ?>';
  const WA_NUMBER = '6285882717844';
  const CART_CONTEXT_KEY = 'apotekmedix_active_cart_context';

  let activeCtx = {
    storageKey: 'apotekmedix_cart_retail',
    kioskOrder: false,
    wholesaleOrder: false,
    adminPbfOrder: false,
    adminPurchaseOrderBase: null,
    continueShoppingUrl: '<?php echo e(route("products.index")); ?>'
  };

  try {
    const saved = JSON.parse(localStorage.getItem(CART_CONTEXT_KEY) || '{}');
    if (saved && saved.storageKey) {
      activeCtx = Object.assign({}, activeCtx, saved);
    }
  } catch (e) {}

  let checkoutCart = JSON.parse(localStorage.getItem(activeCtx.storageKey) || '[]');
  if (!checkoutCart.length) {
    for (const key of ['apotekmedix_cart_retail', 'apotekmedix_cart', 'apotekmedix_cart_apotek', 'apotekmedix_cart_internal', 'apotekmedix_cart_pbf']) {
      const candidate = JSON.parse(localStorage.getItem(key) || '[]');
      if (candidate.length) {
        activeCtx.storageKey = key;
        checkoutCart = candidate;
        break;
      }
    }
  }

  let deliveryMode = activeCtx.kioskOrder ? 'pickup' : 'ship';
  let extraVoucherDiscount = 0;
  let appliedVoucher = null;

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

  function formatIdrDecimal(num) {
    return 'Rp ' + Math.round(Number(num || 0)).toLocaleString('id-ID') + ',00';
  }

  function formatRpPlain(num) {
    return 'Rp ' + Math.round(Number(num || 0)).toLocaleString('id-ID');
  }

  function escapeHtml(text) {
    return String(text || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function renderCheckoutSummary() {
    const itemsContainer = document.getElementById('coSummaryItems');
    const countEl = document.getElementById('coTotalItemsCount');
    const subEl = document.getElementById('coSubtotalDisplay');
    const discRow = document.getElementById('coDiscountRow');
    const discEl = document.getElementById('coDiscountDisplay');
    const grandEl = document.getElementById('coGrandTotalDisplay');

    if (!checkoutCart.length) {
      itemsContainer.innerHTML = `<div style="padding:1rem 0;color:#64748b;font-size:0.9rem;">Keranjang belanja kosong. <a href="<?php echo e(route('cart.index')); ?>" style="color:#0E7490;font-weight:700;text-decoration:underline;">Kembali ke keranjang</a></div>`;
      countEl.textContent = '0';
      subEl.textContent = 'Rp 0,00';
      grandEl.textContent = 'Rp 0,00';
      return;
    }

    let html = '';
    let subtotal = 0;
    let itemDiscounts = 0;
    let totalQty = 0;
    let updatedCatalogImg = false;

    checkoutCart.forEach((item) => {
      const prevImg = item.img;
      const imgUrl = resolveProductImage(item);
      if (item.img !== prevImg) updatedCatalogImg = true;

      const qty = Number(item.qty || 1);
      const price = Number(item.price || 0);
      const lineSub = price * qty;
      const disc = activeCtx.kioskOrder ? 0 : Number(item.discount || 0);
      const unit = item.saleUnit || 'box';
      const unitLabel = item.unitLabel || item.unitLabels?.[unit] || '';

      subtotal += lineSub;
      itemDiscounts += disc;
      totalQty += qty;

      const thumbHtml = `<img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(item.name)}" onerror="this.onerror=null;this.src='${escapeHtml(FALLBACK_LOGO_URL)}';">`;

      html += `
        <div class="co-sum-item">
          <div class="co-sum-left">
            <div class="co-sum-thumb">
              ${thumbHtml}
              <span class="co-sum-qty-badge">${qty}</span>
            </div>
            <div>
              <div class="co-sum-name">${escapeHtml(item.name)}</div>
              ${unitLabel ? `<div class="co-sum-meta">${escapeHtml(unitLabel)}${item.note ? ' &middot; ' + escapeHtml(item.note) : ''}</div>` : ''}
            </div>
          </div>
          <div class="co-sum-price">${formatIdrDecimal(lineSub)}</div>
        </div>
      `;
    });

    if (updatedCatalogImg) {
      localStorage.setItem(activeCtx.storageKey, JSON.stringify(checkoutCart));
    }

    const totalDiscount = Math.min(subtotal, itemDiscounts + extraVoucherDiscount);
    const grandTotal = Math.max(0, subtotal - totalDiscount);

    itemsContainer.innerHTML = html;
    countEl.textContent = totalQty;
    subEl.textContent = formatIdrDecimal(subtotal);

    const badge = document.getElementById('cartBadgeNav');
    if (badge) {
      badge.textContent = totalQty;
      badge.style.display = totalQty > 0 ? 'flex' : 'none';
    }

    if (totalDiscount > 0) {
      discRow.style.display = 'flex';
      discEl.textContent = '-' + formatIdrDecimal(totalDiscount);
    } else {
      discRow.style.display = 'none';
    }

    grandEl.textContent = formatIdrDecimal(grandTotal);
  }

  async function applyCheckoutDiscount() {
    const inp = document.getElementById('coDiscountCodeInput');
    const btn = document.getElementById('coDiscountApplyBtn');
    const msgBox = document.getElementById('coDiscountMsgBox');
    const val = (inp?.value || '').trim().toUpperCase();

    if (!val) {
      extraVoucherDiscount = 0;
      appliedVoucher = null;
      if (msgBox) msgBox.style.display = 'none';
      renderCheckoutSummary();
      return;
    }

    const subtotalAfterItemDisc = checkoutCart.reduce((s, i) => {
      const sub = Number(i.price || 0) * Number(i.qty || 1);
      const disc = activeCtx.kioskOrder ? 0 : Number(i.discount || 0);
      return s + Math.max(0, sub - disc);
    }, 0);

    if (btn) {
      btn.disabled = true;
      btn.textContent = '...';
    }

    try {
      const res = await fetch('<?php echo e(route("checkout.validate-discount")); ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          code: val,
          subtotal: subtotalAfterItemDisc
        })
      });

      const data = await res.json();
      if (!res.ok || !data.valid) {
        extraVoucherDiscount = 0;
        appliedVoucher = null;
        if (msgBox) {
          msgBox.style.display = 'block';
          msgBox.style.background = '#fef2f2';
          msgBox.style.border = '1px solid #fecaca';
          msgBox.style.color = '#991b1b';
          msgBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${escapeHtml(data.message || 'Kode diskon tidak valid.')}`;
        }
        renderCheckoutSummary();
        return;
      }

      appliedVoucher = data;
      extraVoucherDiscount = Number(data.discount_amount || 0);
      if (msgBox) {
        const quotaInfo = data.is_unlimited
          ? 'Tanpa batas kuota'
          : `Sisa kuota: ${data.remaining_quota} user`;
        msgBox.style.display = 'block';
        msgBox.style.background = '#ecfdf5';
        msgBox.style.border = '1px solid #a7f3d0';
        msgBox.style.color = '#065f46';
        msgBox.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${escapeHtml(data.message)} <span style="font-weight:500;color:#047857;">(${quotaInfo})</span>`;
      }
      renderCheckoutSummary();
    } catch (e) {
      if (msgBox) {
        msgBox.style.display = 'block';
        msgBox.style.background = '#fef2f2';
        msgBox.style.border = '1px solid #fecaca';
        msgBox.style.color = '#991b1b';
        msgBox.textContent = 'Gagal memeriksa kode diskon.';
      }
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Pakai';
      }
    }
  }

  function setDeliveryMode(mode) {
    deliveryMode = mode;
    document.getElementById('tabShipBtn').classList.toggle('active', mode === 'ship');
    document.getElementById('tabPickupBtn').classList.toggle('active', mode === 'pickup');
    document.getElementById('coShippingAddressFields').style.display = mode === 'ship' ? '' : 'none';
    document.getElementById('coPickupLocationBox').style.display = mode === 'pickup' ? 'flex' : 'none';
    updateShippingMethodStatus();
  }

  function updateShippingMethodStatus() {
    const summaryShip = document.getElementById('coShippingSummaryText');
    if (!summaryShip) return;
    if (deliveryMode === 'pickup') {
      summaryShip.textContent = 'Ambil di Apotek (Gratis)';
      return;
    }
    const addr = document.getElementById('co_address')?.value.trim() || '';
    if (addr) {
      summaryShip.textContent = 'Dihitung saat konfirmasi';
    } else {
      summaryShip.textContent = 'Masukkan alamat pengiriman';
    }
  }

  function onBuyerTypeChange() {
    onPaymentMethodChange();
  }

  function selectPaymentMethod(method) {
    const input = document.getElementById('co_payment_method');
    if (input) input.value = method;
    document.querySelectorAll('#coPayOptionsGrid .co-pay-opt').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.payMethod === method);
    });
    onPaymentMethodChange();
  }

  function onPaymentMethodChange() {
    const payMethod = document.getElementById('co_payment_method').value;
    const tempoWrap = document.getElementById('coTempoDaysWrap');
    if (tempoWrap) {
      tempoWrap.style.display = payMethod === 'Tempo' ? 'block' : 'none';
    }
  }

  function onBillingModeChange() {
    const mode = document.querySelector('input[name="billing_addr_mode"]:checked')?.value || 'same';
    document.getElementById('radioBillSameRow')?.classList.toggle('active', mode === 'same');
    document.getElementById('radioBillDiffRow')?.classList.toggle('active', mode === 'different');
    const customFields = document.getElementById('coBillingCustomFields');
    if (customFields) customFields.style.display = mode === 'different' ? 'block' : 'none';
  }

  async function submitCheckoutOrder() {
    const errBox = document.getElementById('coErrorBox');
    const submitBtn = document.getElementById('coSubmitBtn');
    errBox.style.display = 'none';

    if (!checkoutCart.length) {
      errBox.textContent = 'Keranjang Anda masih kosong.';
      errBox.style.display = 'block';
      return;
    }

    const jenis = activeCtx.adminPbfOrder || activeCtx.wholesaleOrder
      ? 'apotik'
      : 'umum';

    const fullName = document.getElementById('co_full_name').value.trim();
    const phoneVal = document.getElementById('co_phone').value.trim();

    const originalTotal = checkoutCart.reduce((s, it) => s + (Number(it.price) * Number(it.qty)), 0);
    const itemDiscounts = checkoutCart.reduce((s, it) => s + (Number(it.discount) || 0), 0);
    const totalDiscount = Math.min(originalTotal, itemDiscounts + extraVoucherDiscount);
    const discountedTotal = Math.max(0, originalTotal - totalDiscount);

    const paymentMethod = activeCtx.kioskOrder
      ? 'Tunai'
      : document.getElementById('co_payment_method').value;
    const paymentTermDays = Number(document.getElementById('co_payment_term_days')?.value || 0);

    const mappedItems = checkoutCart.map((it) => ({
      id: it.id,
      nama_obat: it.name,
      quantity: Number(it.qty || 1),
      harga: Number(it.price || 0),
      sale_unit: it.saleUnit || 'box',
      catatan: it.note || '',
      potongan: Number(it.discount || 0)
    }));

    const payload = {
      buyer_type: jenis,
      items: mappedItems,
      total: discountedTotal,
      original_total: originalTotal,
      discounted_total: discountedTotal,
      discount_code: appliedVoucher ? appliedVoucher.code : null,
      approval_status: 'pending',
      payment_method: paymentMethod,
      payment_term_days: paymentMethod === 'Tempo' ? paymentTermDays : null,
      transaction_channel: activeCtx.kioskOrder ? 'offline' : 'online',
      is_pbf_purchase_order: Boolean(activeCtx.adminPbfOrder)
    };

    if (activeCtx.adminPbfOrder) {
      payload.pbf_name = document.getElementById('co_pbf_name').value.trim();
      payload.pbf_address = document.getElementById('co_pbf_address').value.trim();
      payload.buyer_name = 'Apotek Medix';
      payload.phone = '085890007359';
      payload.address = 'ITC Cempaka Mas LT.1 No.88, Jakarta Pusat';
      payload.requester_name = document.getElementById('co_signer_name').value.trim();
      payload.apj = document.getElementById('co_signer_title').value.trim();
      payload.sia = document.getElementById('co_signer_sia').value.trim();
      payload.sipa = document.getElementById('co_signer_sipa').value.trim();
      if (!payload.pbf_name || !payload.pbf_address) {
        errBox.textContent = 'Nama PBF dan alamat PBF wajib diisi.';
        errBox.style.display = 'block';
        return;
      }
    } else {
      const addressVal = document.getElementById('co_address').value.trim();
      const billingMode = document.querySelector('input[name="billing_addr_mode"]:checked')?.value || 'same';
      const billingNameVal = billingMode === 'different'
        ? (document.getElementById('co_billing_name')?.value.trim() || '')
        : '';
      const billingAddrVal = billingMode === 'different'
        ? (document.getElementById('co_billing_address')?.value.trim() || '')
        : '';
      const billingPhoneVal = billingMode === 'different'
        ? (document.getElementById('co_billing_phone')?.value.trim() || '')
        : '';
      const baseAddress = deliveryMode === 'pickup' ? 'Ambil di Apotek Medix' : addressVal;
      const billingDetails = [
        billingNameVal,
        billingAddrVal,
        billingPhoneVal ? `Telp: ${billingPhoneVal}` : ''
      ].filter(Boolean).join(', ');

      payload.buyer_name = fullName;
      payload.phone = phoneVal;
      payload.address = activeCtx.kioskOrder
        ? ''
        : (billingDetails ? `${baseAddress} (Alamat Penagihan: ${billingDetails})` : baseAddress);

      if (billingMode === 'different' && (!billingNameVal || !billingAddrVal)) {
        errBox.textContent = 'Nama lengkap dan alamat penagihan wajib diisi jika memakai alamat penagihan lain.';
        errBox.style.display = 'block';
        return;
      }

      if (jenis === 'apotik' || jenis === 'dokter' || activeCtx.wholesaleOrder) {
        payload.requester_name = fullName;
        payload.outlet_name = fullName;
      }

      if (!payload.buyer_name || !payload.phone) {
        errBox.textContent = 'Nama lengkap dan nomor HP wajib diisi.';
        errBox.style.display = 'block';
        return;
      }
      if (!activeCtx.kioskOrder && deliveryMode === 'ship' && !addressVal) {
        errBox.textContent = 'Alamat pengiriman wajib diisi.';
        errBox.style.display = 'block';
        return;
      }
    }

    if (paymentMethod === 'Tempo' && (!Number.isInteger(paymentTermDays) || paymentTermDays < 1)) {
      errBox.textContent = 'Jumlah hari tempo wajib diisi untuk metode pembayaran Tempo.';
      errBox.style.display = 'block';
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Memproses pesanan...</span> <i class="fa-solid fa-spinner fa-spin"></i>';

    try {
      const response = await fetch('<?php echo e(route("orders.history.store")); ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        const errJson = await response.json();
        throw (errJson.errors || new Error(errJson.message || 'Gagal menyimpan pesanan.'));
      }

      const result = await response.json();
      window.completedOrder = {
        ...payload,
        id: result.id,
        invoice_url: result.invoice_url,
        cartSnapshot: [...checkoutCart]
      };

      localStorage.setItem(activeCtx.storageKey, '[]');

      document.getElementById('coOrderNumberLabel').textContent = `ID Pesanan #${result.id}`;
      document.getElementById('coInvoiceBtn').href = activeCtx.adminPurchaseOrderBase
        ? activeCtx.adminPurchaseOrderBase.replace('__ORDER__', result.id)
        : result.invoice_url;

      if (activeCtx.kioskOrder) {
        document.getElementById('coSuccessDesc').textContent = 'Struk pesanan internal telah dibuat. Tunjukkan struk ini kepada kasir untuk menyelesaikan pembayaran.';
        document.getElementById('coWhatsappBtn').style.display = 'none';
      }

      document.getElementById('coFormWrapper').style.display = 'none';
      const payRight = document.getElementById('coPaymentRightWrapper');
      if (payRight) payRight.style.display = 'none';
      document.getElementById('coSuccessPanel').style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
      let msg = 'Terjadi kesalahan saat memproses pesanan.';
      if (error && error.message) msg = error.message;
      else if (typeof error === 'object') msg = Object.values(error).map(e => Array.isArray(e) ? e.join(', ') : e).join('<br>');
      errBox.innerHTML = msg;
      errBox.style.display = 'block';
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<span>Bayar sekarang</span> <i class="fa-solid fa-arrow-right"></i>';
    }
  }

  function openWhatsAppCheckout() {
    const ord = window.completedOrder;
    if (!ord) return;
    let total = 0;
    let msg = `*Halo Apotek Medix, saya ingin konfirmasi pesanan #${ord.id}:*\n`;
    (ord.cartSnapshot || []).forEach((it, idx) => {
      const sub = it.price * it.qty;
      const disc = it.discount || 0;
      const afterDisc = sub - disc;
      total += afterDisc;
      const unit = it.saleUnit || 'box';
      const unitLabel = it.unitLabel || it.unitLabels?.[unit] || unit;
      msg += `${idx + 1}. *${it.name}*\n   Qty: ${it.qty} ${unitLabel} x ${formatRpPlain(it.price)} = ${formatRpPlain(sub)}\n`;
    });
    msg += `---\n*Total: ${formatRpPlain(ord.total)}*\n\n*Data Pemesan:*\n`;
    msg += `- Nama: ${ord.buyer_name}\n- Telepon: ${ord.phone}\n- Pengantaran: ${ord.address || 'Ambil di Apotek'}\n- Pembayaran: ${ord.payment_method}\n\nTerima kasih!`;
    window.open(`https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(msg)}`, '_blank');
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (activeCtx.adminPbfOrder) {
      document.getElementById('coAdminPbfFields').style.display = 'block';
    }
    if (activeCtx.kioskOrder) {
      setDeliveryMode('pickup');
    } else {
      updateShippingMethodStatus();
    }
    onBuyerTypeChange();
    renderCheckoutSummary();
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/checkout.blade.php ENDPATH**/ ?>