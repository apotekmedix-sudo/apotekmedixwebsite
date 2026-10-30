{{--
  =====================================================================
  SHARED CART PARTIAL - resources/views/partials/cart.blade.php
  Include ini di @section('scripts') semua halaman yang butuh keranjang:
      @include('partials.cart')
  =====================================================================
--}}
@php
  $isAdminPbfOrder = !empty($cartAdminPbfOrder);
  $defaultShippingAddress = auth()->check()
    && auth()->user()->isUser()
    && auth()->user()->is_default_address
      ? auth()->user()
      : null;
@endphp

{{-- ===== CART CSS ===== --}}
<style>
.cart-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
.cart-overlay.open { opacity: 1; pointer-events: all; }
.cart-drawer { position: fixed; top: 0; right: -420px; width: 420px; max-width: 100vw; height: 100vh; background: #fff; z-index: 2001; display: flex; flex-direction: column; box-shadow: -8px 0 40px rgba(0,0,0,0.15); transition: right 0.35s cubic-bezier(.4,0,.2,1); }
.cart-drawer.open { right: 0; }
.cart-head { background: linear-gradient(135deg, #0D47A1, #1E88E5); padding: 1.25rem 1.5rem; color: #fff; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.cart-head h2 { font-size: 1.1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.cart-close-btn { background: rgba(255,255,255,0.2); border: none; color: #fff; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.cart-close-btn:hover { background: rgba(255,255,255,0.35); }
.cart-body { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }
.cart-empty-msg { text-align: center; padding: 3rem 1rem; color: #9ca3af; }
.cart-empty-msg i { font-size: 3rem; display: block; margin-bottom: 0.75rem; }
.cart-item-row { display: flex; gap: 0.75rem; align-items: flex-start; padding: 0.85rem 0; border-bottom: 1px solid #f3f4f6; }
.cart-item-thumb { width: 52px; height: 52px; border-radius: 10px; flex-shrink: 0; background: linear-gradient(135deg,#e3f2fd,#bbdefb); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.cart-item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name { font-size: 0.84rem; font-weight: 700; color: #1f2937; margin-bottom: 0.2rem; line-height: 1.3; }
.cart-item-price { font-size: 0.8rem; color: #1E88E5; font-weight: 700; }
.cart-item-price-unavailable { color:#b91c1c; font-size:.78rem; font-weight:700; }
.cart-qty-row { display: flex; align-items: center; gap: 0.4rem; margin-top: 0.4rem; }
.qty-btn { width: 26px; height: 26px; border-radius: 6px; border: 1.5px solid #e5e7eb; background: #fff; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #374151; transition: all 0.2s; }
.qty-btn:hover { border-color: #1E88E5; color: #1E88E5; }
.qty-num { font-size: 0.85rem; font-weight: 700; min-width: 20px; text-align: center; }
.cart-item-del { background: none; border: none; color: #d1d5db; cursor: pointer; font-size: 0.9rem; padding: 0.2rem; flex-shrink: 0; transition: color 0.2s; }
.cart-item-del:hover { color: #ef4444; }
.cart-foot { padding: 1.25rem 1.5rem; border-top: 2px solid #f3f4f6; flex-shrink: 0; background: #fafbff; }
.cart-total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.cart-total-row span { font-size: 0.9rem; color: #6b7280; }
.cart-total-row strong { font-size: 1.2rem; color: #1E88E5; font-weight: 800; }
.btn-order-wa { display: flex; align-items: center; justify-content: center; gap: 0.6rem; width: 100%; padding: 0.85rem; background: #25D366; color: #fff; border: none; border-radius: 12px; cursor: pointer; font-weight: 700; font-size: 1rem; transition: all 0.3s; }
.btn-order-wa:hover { background: #1ebe5d; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,0.4); }
.btn-clear-cart { display: block; width: 100%; padding: 0.5rem; background: none; border: none; color: #9ca3af; font-size: 0.8rem; cursor: pointer; margin-top: 0.5rem; transition: color 0.2s; }
.btn-clear-cart:hover { color: #ef4444; }
/* ORDER MODAL */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 3000; }
.modal-box { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 92%; max-width: 480px; max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 20px; z-index: 3001; box-shadow: 0 25px 60px rgba(0,0,0,0.25); }
.pbf-checkout-overlay,.pbf-invoice-overlay { display:none; position:fixed; inset:0; z-index:4000; padding:2vh 2vw; background:rgba(15,23,42,.72); }
.pbf-checkout-overlay.open,.pbf-invoice-overlay.open { display:flex; align-items:center; justify-content:center; }
.pbf-checkout-modal { width:min(760px,100%); max-height:96vh; overflow:auto; border-radius:20px; background:#fff; box-shadow:0 25px 70px rgba(0,0,0,.32); }
.pbf-checkout-header { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; padding:22px 26px; color:#fff; background:linear-gradient(125deg,#0f4c81,#087f9c); }
.pbf-checkout-kicker { display:block; margin-bottom:7px; color:#b8f2e6; font-size:.68rem; font-weight:800; letter-spacing:.12em; }
.pbf-checkout-header h2 { margin:0; color:#fff; font-size:1.25rem; }
.pbf-checkout-header p { margin:6px 0 0; color:rgba(255,255,255,.82); font-size:.84rem; }
.pbf-checkout-close { flex:0 0 38px; width:38px; height:38px; border:0; border-radius:50%; color:#fff; background:rgba(255,255,255,.18); font-size:1.1rem; cursor:pointer; }
.pbf-checkout-content { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.1fr); }
.pbf-checkout-summary { padding:22px; background:#f3f8fb; border-right:1px solid #e2e8f0; }
.pbf-checkout-section-heading { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; color:#0f4c81; font-size:.82rem; font-weight:800; }
.pbf-checkout-summary-item { display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid #dce7ed; color:#334155; font-size:.8rem; }
.pbf-checkout-summary-item strong { flex:0 0 auto; color:#087f9c; text-align:right; }
.pbf-checkout-total { display:flex; justify-content:space-between; gap:12px; margin-top:14px; padding-top:12px; border-top:2px solid #cbdbe5; color:#334155; font-size:.82rem; font-weight:800; }
.pbf-checkout-total strong { color:#087f9c; font-size:1.05rem; }
.pbf-checkout-fields { padding:22px 24px; }
.pbf-checkout-fields label { display:block; margin:0 0 6px; color:#334155; font-size:.76rem; font-weight:800; }
.pbf-checkout-fields label span { color:#dc2626; }
.pbf-checkout-fields input,.pbf-checkout-fields textarea,.pbf-checkout-fields select { width:100%; margin:0 0 13px; padding:10px 12px; border:1px solid #cbd5e1; border-radius:9px; outline:none; color:#0f172a; font:inherit; font-size:.85rem; }
.pbf-checkout-fields input:focus,.pbf-checkout-fields textarea:focus,.pbf-checkout-fields select:focus { border-color:#0891b2; box-shadow:0 0 0 3px rgba(8,145,178,.12); }
.pbf-checkout-fields textarea { resize:vertical; }
.pbf-supplier-picker { position:relative; display:flex; margin:0 0 13px; border:1px solid #cbd5e1; border-radius:9px; background:#fff; transition:border-color .18s,box-shadow .18s; }
.pbf-supplier-picker:focus-within,.pbf-supplier-picker.is-open { border-color:#1e88e5; box-shadow:0 0 0 3px rgba(30,136,229,.12); }
.pbf-supplier-picker input { flex:1; min-width:0; margin:0; border:0; box-shadow:none!important; background:transparent; }
.pbf-supplier-picker input:focus { border-color:transparent; }
.pbf-supplier-toggle { flex:0 0 42px; border:0; border-radius:0 9px 9px 0; background:transparent; color:#64748b; cursor:pointer; transition:color .18s,background .18s; }
.pbf-supplier-toggle:hover,.pbf-supplier-picker.is-open .pbf-supplier-toggle { color:#1e88e5; background:#eff6ff; }
.pbf-supplier-toggle i { transition:transform .18s; }
.pbf-supplier-picker.is-open .pbf-supplier-toggle i { transform:rotate(180deg); }
.pbf-supplier-menu { position:absolute; z-index:20; top:calc(100% + 7px); left:0; right:0; display:none; max-height:240px; overflow:auto; padding:6px; border:1px solid #e2e8f0; border-radius:12px; background:#fff; box-shadow:0 14px 32px rgba(15,23,42,.16); }
.pbf-supplier-picker.is-open .pbf-supplier-menu { display:block; animation:pbfSupplierMenuIn .14s ease-out; }
.pbf-supplier-option { display:flex; width:100%; flex-direction:column; gap:3px; padding:10px 12px; border:0; border-radius:8px; background:#fff; color:#1e293b; text-align:left; cursor:pointer; transition:background .15s,color .15s; }
.pbf-supplier-option:hover,.pbf-supplier-option.is-active { background:#eff6ff; color:#1d4ed8; }
.pbf-supplier-option-name { font-size:.84rem; font-weight:750; }
.pbf-supplier-option-address { color:#64748b; font-size:.73rem; line-height:1.4; }
.pbf-supplier-empty { padding:12px; color:#64748b; font-size:.8rem; text-align:center; }
@keyframes pbfSupplierMenuIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }
.pbf-signatory-heading { margin-top:5px; padding-top:14px; border-top:1px solid #e2e8f0; }
.pbf-checkout-field-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.pbf-checkout-error { display:none; margin:0 0 12px; padding:10px 12px; border-radius:8px; background:#fee2e2; color:#991b1b; font-size:.8rem; }
.pbf-checkout-note { margin:0 0 14px; padding:10px; border-radius:8px; background:#ecfeff; color:#155e75; font-size:.75rem; line-height:1.5; }
.pbf-checkout-submit { width:100%; padding:12px 15px; border:0; border-radius:10px; background:#087f9c; color:#fff; font-size:.9rem; font-weight:800; cursor:pointer; }
.pbf-checkout-submit:disabled { opacity:.65; cursor:wait; }
.pbf-checkout-result { padding:40px 28px; text-align:center; }
.pbf-result-icon { color:#16a34a; font-size:3rem; }
.pbf-checkout-result h3 { margin:10px 0; color:#0f172a; }
.pbf-checkout-result p { margin:0 auto 20px; max-width:450px; color:#64748b; line-height:1.6; font-size:.9rem; }
.pbf-invoice-overlay { z-index:4100; }
.purchase-order-preview-panel { display:flex; flex-direction:column; width:min(1100px,100%); height:96vh; overflow:hidden; border-radius:14px; background:#fff; box-shadow:0 25px 60px rgba(0,0,0,.3); }
.purchase-order-preview-toolbar { display:flex; flex:0 0 auto; justify-content:space-between; align-items:center; gap:12px; padding:10px 14px; color:#1e293b; font-weight:700; }
.purchase-order-preview-toolbar button { padding:8px 12px; border:0; border-radius:8px; background:#e2e8f0; color:#1e293b; font-weight:700; cursor:pointer; }
.purchase-order-preview-panel iframe { display:block; flex:1 1 auto; width:100%; height:0; min-height:0; border:0; }
.modal-head { background: linear-gradient(135deg,#1565C0,#1E88E5); padding: 1.25rem 1.5rem; border-radius: 20px 20px 0 0; display: flex; justify-content: space-between; align-items: center; }
.modal-head h3 { color: #fff; margin: 0; font-size: 1rem; font-weight: 700; }
.modal-head p { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.75rem; }
.modal-close { background: rgba(255,255,255,0.2); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1rem; }
.modal-summary { padding: 1rem 1.5rem; background: #f8faff; border-bottom: 1px solid #e5e7eb; font-size: 0.85rem; color: #374151; }
.modal-form { padding: 1.25rem 1.5rem 1.5rem; }
.form-lbl { display: block; font-size: 0.78rem; font-weight: 700; color: #374151; margin-bottom: 0.3rem; }
.form-inp { width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; margin-bottom: 0.75rem; }
.form-inp:focus { border-color: #1E88E5; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-error { display: none; background: #fee2e2; color: #7f1d1d; padding: 0.6rem; border-radius: 8px; font-size: 0.8rem; margin-bottom: 0.75rem; }
.btn-submit-wa { width: 100%; padding: 0.85rem; background: linear-gradient(135deg,#25D366,#1ebe5d); color: #fff; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-cart { display: block; width: 100%; padding: 0.55rem; background: white; color: #1E88E5; border: 2px solid #1E88E5; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 0.82rem; text-align: center; text-decoration: none; transition: all 0.3s; margin-top: 0.5rem; }
.btn-cart:hover { background: #e3f2fd; transform: translateY(-1px); }
.btn-cart.added { background: #d1fae5; color: #065f46; border-color: #34d399; }
@media (max-width: 768px) {
  .cart-drawer { width: 100%; max-width: 100%; right: -100%; }
  .cart-drawer.open { right: 0; }
  .form-row { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .cart-head { padding: 0.95rem 1rem; }
  .cart-head h2 { font-size: 0.98rem; }
  .cart-close-btn { width: 30px; height: 30px; font-size: 0.9rem; }
  .cart-body { padding: 0.75rem 0.9rem; }
  .cart-item-row { gap: 0.6rem; padding: 0.7rem 0; }
  .cart-item-thumb { width: 44px; height: 44px; }
  .cart-item-name { font-size: 0.78rem; }
  .qty-btn { width: 24px; height: 24px; font-size: 0.82rem; }
  .cart-foot { padding: 0.9rem 1rem; }
  .cart-total-row strong { font-size: 1rem; }
  .btn-order-wa { padding: 0.8rem; font-size: 0.92rem; }
}
@media (max-width: 700px) {
  .pbf-checkout-overlay,.pbf-invoice-overlay { padding:0; }
  .pbf-checkout-modal { width:100%; height:100%; max-height:100dvh; overflow:auto; border-radius:0; }
  .pbf-checkout-header { padding:18px; }
  .pbf-checkout-header h2 { font-size:1.05rem; }
  .pbf-checkout-content { grid-template-columns:1fr; }
  .pbf-checkout-summary { padding:16px 18px; border-right:0; border-bottom:1px solid #e2e8f0; }
  .pbf-checkout-fields { padding:18px; }
  .purchase-order-preview-panel { width:100%; height:100dvh; border-radius:0; }
}
</style>

{{-- ===== CART DRAWER HTML ===== --}}
<style>
  .red-logo-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 4500;
    background: rgba(15, 23, 42, 0.62);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  .red-logo-modal-overlay.open {
    display: flex;
  }
  .red-logo-modal-box {
    width: min(440px, 100%);
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
    border: 1px solid #fee2e2;
    overflow: hidden;
    animation: redLogoPopIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }
  @keyframes redLogoPopIn {
    from { opacity: 0; transform: translateY(10px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }
  .red-logo-modal-head {
    background: linear-gradient(135deg, #b91c1c, #dc2626);
    padding: 1.25rem 1.4rem;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
  }
  .red-logo-modal-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .red-logo-k-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #dc2626;
    border: 2.5px solid #0f172a;
    box-shadow: 0 0 0 3px #ffffff;
    color: #0f172a;
    font-weight: 900;
    font-size: 1.2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .red-logo-modal-head h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.25;
  }
  .red-logo-modal-head p {
    margin: 0.15rem 0 0;
    font-size: 0.76rem;
    color: rgba(255, 255, 255, 0.88);
  }
  .red-logo-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    font-size: 1rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .red-logo-modal-close:hover {
    background: rgba(255, 255, 255, 0.32);
  }
  .red-logo-modal-body {
    padding: 1.35rem 1.4rem 1.4rem;
    text-align: center;
  }
  .red-logo-alert-box {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    color: #7f1d1d;
    font-size: 0.88rem;
    line-height: 1.55;
    margin-bottom: 1.15rem;
  }
  .red-logo-alert-box strong {
    color: #991b1b;
    font-weight: 800;
  }
  .red-logo-btn-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
    margin-bottom: 0.75rem;
  }
  .red-logo-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.78rem 0.75rem;
    border-radius: 11px;
    font-size: 0.85rem;
    font-weight: 800;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .red-logo-action-btn:hover {
    transform: translateY(-2px);
  }
  .red-logo-btn-doctor {
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(2, 132, 199, 0.28);
  }
  .red-logo-btn-doctor:hover { color: #ffffff; }
  .red-logo-btn-pharmacist {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(22, 163, 74, 0.28);
  }
  .red-logo-btn-pharmacist:hover { color: #ffffff; }
  .red-logo-dismiss-btn {
    width: 100%;
    padding: 0.6rem;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
  }
  .red-logo-dismiss-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
  }
  @media (max-width: 460px) {
    .red-logo-btn-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="red-logo-modal-overlay" id="redLogoModalOverlay" onclick="if(event.target === this) closeRedLogoModal()" role="dialog" aria-modal="true" aria-labelledby="redLogoModalTitle">
  <div class="red-logo-modal-box">
    <div class="red-logo-modal-head">
      <div class="red-logo-modal-title-wrap">
        <span class="red-logo-k-badge" aria-hidden="true">K</span>
        <div>
          <h3 id="redLogoModalTitle">Harus dengan Resep Dokter</h3>
          <p>Peringatan Obat Keras (Logo Merah)</p>
        </div>
      </div>
      <button type="button" class="red-logo-modal-close" onclick="closeRedLogoModal()" aria-label="Tutup">&#x2715;</button>
    </div>
    <div class="red-logo-modal-body">
      <div class="red-logo-alert-box">
        Produk <strong id="redLogoProductName">Obat ini</strong> termasuk golongan <strong>Obat Keras (Logo Merah)</strong> yang pembeliannya <strong>harus dengan resep dokter</strong>.<br>
        Silakan hubungi Dokter atau Apoteker kami untuk konsultasi dan pemesanan resep.
      </div>
      <div class="red-logo-btn-grid">
        <a id="btnHubungiDokter" href="{{ route('consultations.intake') }}" class="red-logo-action-btn red-logo-btn-doctor">
          <i class="fa-solid fa-user-doctor"></i> Hubungi Dokter
        </a>
        <a id="btnHubungiApoteker" href="https://wa.me/{{ config('contacts.pharmacist.whatsapp') }}" target="_blank" rel="noopener" class="red-logo-action-btn red-logo-btn-pharmacist">
          <i class="fa-solid fa-prescription-bottle-medical"></i> Hubungi Apoteker
        </a>
      </div>
      <button type="button" class="red-logo-dismiss-btn" onclick="closeRedLogoModal()">Tutup</button>
    </div>
  </div>
</div>
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-head">
    <h2><i class="fa-solid fa-cart-shopping"></i> {{ $isAdminPbfOrder ? 'Keranjang Pengadaan' : 'Keranjang' }}</h2>
    <button class="cart-close-btn" onclick="closeCart()"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="cart-body" id="cartBody">
    <div class="cart-empty-msg"><i class="fa-solid fa-cart-shopping"></i><p>Keranjang masih kosong</p></div>
  </div>
  <div class="cart-foot" id="cartFoot" style="display:none;">
    <div class="cart-total-row"><span>{{ $isAdminPbfOrder ? 'Estimasi harga modal' : 'Total Pesanan' }}</span><strong id="cartTotalAmt">Rp 0</strong></div>
    <button class="btn-order-wa" onclick="openOrder()">{{ $isAdminPbfOrder ? 'Buat Surat Pesanan' : 'Lanjutkan Pesanan' }}</button>
    <button class="btn-clear-cart" onclick="clearCart()">Kosongkan keranjang</button>
  </div>
</div>

@unless($isAdminPbfOrder)
{{-- ===== STANDARD CHECKOUT MODAL ===== --}}
<div class="modal-overlay" id="orderOverlay" onclick="closeOrder()"></div>
<div class="modal-box" id="orderModal">
  <div class="modal-head">
    <div><h3><i class="fa-solid fa-file-invoice"></i> Form Pemesanan</h3><p>{{ $isAdminPbfOrder ? 'Isi data distributor dan penandatangan Apotek Medix.' : 'Lengkapi data untuk melanjutkan' }}</p></div>
    <button class="modal-close" onclick="closeOrder()">&#x2715;</button>
  </div>
  <div class="modal-summary">
    <p style="font-size:0.72rem;font-weight:700;color:#6b7280;margin:0 0 0.5rem;text-transform:uppercase;">Ringkasan Pesanan</p>
    <div id="orderSummary"></div>
    <div style="display:flex;justify-content:space-between;margin-top:0.5rem;padding-top:0.5rem;border-top:1px solid #e5e7eb;">
      <span style="font-weight:700;color:#374151;font-size:0.85rem;">Total</span>
      <span id="orderTotal" style="font-weight:800;color:#1E88E5;font-size:1rem;"></span>
    </div>
  </div>
  <div class="modal-form" id="orderFormPanel">
    @if(empty($cartWholesaleOrder))
    @if($isAdminPbfOrder)
    <input type="hidden" id="f_jenis" value="apotik">
    @else
    <div id="standardBuyerFields">
    <label class="form-lbl">Jenis Pembeli <span style="color:#ef4444;">*</span></label>
    <select id="f_jenis" class="form-inp" onchange="toggleBuyerType()">
      <option value="umum">Umum</option>
      <option value="apotik">Apotik</option>
      <option value="dokter">Dokter</option>
    </select>
    </div>
    @endif
    @endif
    <div id="pbfDestinationFields" @if(!$isAdminPbfOrder) style="display:none;" @endif>
      <label class="form-lbl">Nama distributor / PBF tujuan <span style="color:#ef4444;">*</span></label>
      <input id="f_pbf_name" type="text" class="form-inp" placeholder="Nama distributor atau PBF tujuan">
      <label class="form-lbl">Alamat distributor <span style="color:#ef4444;">*</span></label>
      <textarea id="f_pbf_address" rows="2" class="form-inp" style="resize:vertical;" placeholder="Alamat lengkap distributor"></textarea>
    </div>
    <div id="signatoryFields" @if(!$isAdminPbfOrder) style="display:none;" @endif>
      <label class="form-lbl">Nama Penandatangan <span style="color:#ef4444;">*</span></label>
      <input id="f_signer_name" type="text" class="form-inp" value="Apt. Hendri Setiyono, S Farm" placeholder="Nama apoteker">
      <label class="form-lbl">Jabatan <span style="color:#ef4444;">*</span></label>
      <input id="f_signer_title" type="text" class="form-inp" value="Apoteker" placeholder="Apoteker">
      <label class="form-lbl">No. SIA <span style="color:#ef4444;">*</span></label>
      <input id="f_signer_sia" type="text" class="form-inp" value="13042600890450001" placeholder="Nomor SIA">
      <label class="form-lbl">No. SIPA <span style="color:#ef4444;">*</span></label>
      <input id="f_signer_sipa" type="text" class="form-inp" value="1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026" placeholder="Nomor SIPA">
    </div>
    <div id="buyerFieldsUmum" class="standard-buyer-fields" @if(!empty($cartWholesaleOrder)) style="display:none;" @endif>
      <label class="form-lbl">Nama Pemesan <span style="color:#ef4444;">*</span></label>
      <input id="f_nama" type="text" class="form-inp" placeholder="Nama lengkap" value="{{ $defaultShippingAddress?->name }}">
      <label class="form-lbl">No. HP / WA <span style="color:#ef4444;">*</span></label>
      <input id="f_hp" type="tel" class="form-inp" placeholder="08xxxxxxxxxx" value="{{ $defaultShippingAddress?->phone }}">
      <label class="form-lbl">Alamat Lengkap <span style="color:#ef4444;">*</span></label>
      <textarea id="f_alamat" rows="3" class="form-inp" style="resize:vertical;" placeholder="Jl. nama jalan, no. rumah, RT/RW...">{{ $defaultShippingAddress?->address }}</textarea>
      <div class="form-row">
        <div><label class="form-lbl">Apartemen / Unit</label><input id="f_apartemen" type="text" class="form-inp" placeholder="Opsional" value="{{ $defaultShippingAddress?->apartment }}"></div>
        <div><label class="form-lbl">Kota / Kabupaten</label><input id="f_kota" type="text" class="form-inp" placeholder="Kota / Kabupaten" value="{{ $defaultShippingAddress?->city }}"></div>
      </div>
      <div class="form-row">
        <div><label class="form-lbl">Provinsi</label><input id="f_provinsi" type="text" class="form-inp" placeholder="Provinsi" value="{{ $defaultShippingAddress?->province }}"></div>
        <div><label class="form-lbl">Kode Pos</label><input id="f_kode_pos" type="text" class="form-inp" placeholder="Kode pos" value="{{ $defaultShippingAddress?->postal_code }}"></div>
      </div>
    </div>
    <div id="buyerFieldsApotik" class="standard-buyer-fields" @if(!empty($cartWholesaleOrder)) style="display:block;" @else style="display:none;" @endif>
      <label class="form-lbl">Nama Pemesan <span style="color:#ef4444;">*</span></label>
      <input id="f_penanggung_jawab" type="text" class="form-inp" placeholder="Nama pemesan">
      <label class="form-lbl">Nama Outlet <span style="color:#ef4444;">*</span></label>
      <input id="f_outlet" type="text" class="form-inp" placeholder="Nama outlet">
      <label class="form-lbl">No. HP / WA <span style="color:#ef4444;">*</span></label>
      <input id="f_hp_apotik" type="tel" class="form-inp" placeholder="08xxxxxxxxxx">
      <label class="form-lbl">Alamat <span style="color:#ef4444;">*</span></label>
      <textarea id="f_alamat_apotik" rows="3" class="form-inp" style="resize:vertical;" placeholder="Alamat lengkap apotik"></textarea>
    </div>
    @if(empty($cartWholesaleOrder) && !$isAdminPbfOrder)
    <label class="form-lbl">Metode Pembayaran <span style="color:#ef4444;">*</span></label>
    <select id="f_payment" class="form-inp" onchange="toggleTempoFields()">
      <option value="">Pilih metode pembayaran</option>
      <option value="Transfer Bank BCA">Transfer Bank BCA</option>
      <option value="Transfer Bank BRI">Transfer Bank BRI</option>
      <option value="Transfer Bank Mandiri">Transfer Bank Mandiri</option>
      <option value="Transfer Bank BNI">Transfer Bank BNI</option>
      <option value="QRIS">QRIS</option>
      <option value="COD (Bayar di Tempat)">COD (Bayar di Tempat)</option>
      <option value="Tunai">Tunai</option>
      <option value="Tempo">Tempo</option>
    </select>
    <div id="tempoFields" style="display:none;">
      <label class="form-lbl">Jumlah Hari Tempo <span style="color:#ef4444;">*</span></label>
      <input id="f_payment_term_days" type="number" min="1" step="1" class="form-inp" placeholder="Contoh: 14">
    </div>
    @endif
    @if($isAdminPbfOrder)
      <p style="padding:.75rem;background:#eff6ff;border-radius:.6rem;color:#1e40af;font-size:.85rem;">
        <i class="fa-solid fa-circle-info"></i> Pesanan ini untuk pengadaan stok Apotek Medix. Tidak ada pembayaran online pada langkah ini; periksa surat pesanan sebelum dikirim ke distributor.
      </p>
    @endif
    <div class="form-error" id="formErr"></div>
    @if($isAdminPbfOrder)
      <div style="display:flex;gap:.6rem;">
        <button class="btn-submit-wa" onclick="submitOrder()"><i class="fa-solid fa-file-signature"></i> Buat Surat Pesanan</button>
      </div>
    @else
    <button class="btn-submit-wa" onclick="submitOrder()">
      <i class="fa-solid fa-paper-plane"></i> Lanjutkan
    </button>
    @endif
  </div>
  <div class="modal-form" id="nextActionPanel" style="display:none;">
    <p id="orderSuccessMessage" style="color:#374151;font-size:0.9rem;margin-bottom:1rem;">{{ $isAdminPbfOrder ? 'Surat pesanan berhasil dibuat. Pilih tindakan selanjutnya:' : 'Pesanan berhasil dicatat! Pilih tindakan selanjutnya:' }}</p>
    <a id="invoiceLink" href="#" @unless($isAdminPbfOrder) target="_blank" rel="noopener" @endunless onclick="openInvoice(event)" style="width:100%;padding:0.75rem;background:#e8f5e9;color:#187b35;border:1.5px solid #86d39a;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;margin-bottom:0.75rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;text-decoration:none;">
      <i class="fa-solid fa-file-invoice"></i> Lihat Invoice
    </a>
    <button id="whatsappOrderButton" @if($isAdminPbfOrder) style="display:none;" @else style="width:100%;padding:0.75rem;background:#25D366;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;" @endif onclick="openWhatsAppOrder()">
      <i class="fa-brands fa-whatsapp" style="font-size:1.2rem;"></i> Chat WhatsApp
    </button>
  </div>
</div>
@endunless
@if($isAdminPbfOrder)
<div class="pbf-checkout-overlay" id="pbfCheckoutOverlay" onclick="closePbfCheckout(event)">
  <section class="pbf-checkout-modal" id="pbfOrderModal" role="dialog" aria-modal="true" aria-labelledby="pbfOrderTitle">
    <header class="pbf-checkout-header">
      <div>
        <span class="pbf-checkout-kicker">PENGADAAN STOK APOTEK MEDIX</span>
        <h2 id="pbfOrderTitle"><i class="fa-solid fa-file-signature"></i> Surat Pesanan ke Distributor</h2>
        <p>Lengkapi data tujuan dan penanggung jawab sebelum membuat surat pesanan.</p>
      </div>
      <button class="pbf-checkout-close" type="button" aria-label="Tutup" onclick="closePbfCheckout()"><i class="fa-solid fa-xmark"></i></button>
    </header>
    <div class="pbf-checkout-content" id="pbfCheckoutForm">
      <div class="pbf-checkout-summary">
        <div class="pbf-checkout-section-heading"><span>Ringkasan pengadaan</span><span id="pbfOrderItemCount"></span></div>
        <div id="pbfOrderSummary"></div>
        <div class="pbf-checkout-total"><span>Estimasi total harga modal</span><strong id="pbfOrderTotal">Rp 0</strong></div>
      </div>
      <div class="pbf-checkout-fields">
        <div class="pbf-checkout-section-heading">Identitas surat pesanan &amp; tujuan distributor</div>
        <label for="pbfOrderProfile">Identitas apotek <span>*</span></label>
        <select id="pbfOrderProfile">
          <option value="medix">Apotek Medix</option>
          <option value="medikpedia">Apotek Medikpedia</option>
        </select>
        <label for="pbfOrderType">Jenis surat pesanan <span>*</span></label>
        <select id="pbfOrderType">
          <option value="regular">SP Biasa (Obat Reguler)</option>
          <option value="oot">SP OOT (Obat-Obat Tertentu)</option>
          <option value="precursor">SP Prekursor</option>
        </select>
        <label for="pbfOrderDistributor">Nama distributor / PBF <span>*</span></label>
        <div class="pbf-supplier-picker" id="pbfSupplierPicker">
          <input id="pbfOrderDistributor" type="text" role="combobox" placeholder="Pilih atau ketik nama supplier" autocomplete="off" aria-autocomplete="list" aria-controls="pbfSupplierMenu" aria-expanded="false">
          <button class="pbf-supplier-toggle" id="pbfSupplierToggle" type="button" aria-label="Tampilkan daftar supplier" aria-expanded="false"><i class="fa-solid fa-chevron-down"></i></button>
          <div class="pbf-supplier-menu" id="pbfSupplierMenu" role="listbox"></div>
        </div>
        <label for="pbfOrderAddress">Alamat distributor <span>*</span></label>
        <textarea id="pbfOrderAddress" rows="2" placeholder="Alamat lengkap distributor"></textarea>
        <label for="pbfOrderPhone">Nomor telepon distributor <span>*</span></label>
        <input id="pbfOrderPhone" type="tel" maxlength="50" placeholder="Nomor telepon PBF">
        <div class="pbf-checkout-section-heading pbf-signatory-heading" id="pbfSignerHeading">Penanggung jawab Apotek Medix</div>
        <label for="pbfOrderSigner">Nama penandatangan <span>*</span></label>
        <input id="pbfOrderSigner" type="text" value="Apt. Hendri Setiyono, S Farm">
        <label for="pbfOrderTitleField">Jabatan <span>*</span></label>
        <input id="pbfOrderTitleField" type="text" value="Apoteker Penanggung Jawab">
        <div class="pbf-checkout-field-row">
          <div><label for="pbfOrderSia">No. SIA <span>*</span></label><input id="pbfOrderSia" type="text" value="13042600890450001"></div>
          <div><label for="pbfOrderSipa">No. SIPA <span>*</span></label><input id="pbfOrderSipa" type="text" value="1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026"></div>
        </div>
        <div class="pbf-checkout-error" id="pbfCheckoutError" role="alert"></div>
        <p class="pbf-checkout-note"><i class="fa-solid fa-circle-info"></i> Pengadaan ini dibuat atas nama Apotek Medix. Setelah disimpan, surat pesanan dapat ditinjau dan dicetak sebelum dikirim ke distributor.</p>
        <button class="pbf-checkout-submit" id="pbfSubmitOrderButton" type="button" onclick="submitPbfOrder()"><i class="fa-solid fa-file-signature"></i> Buat Surat Pesanan</button>
      </div>
    </div>
    <div class="pbf-checkout-result" id="pbfCheckoutResult" style="display:none;">
      <div class="pbf-result-icon"><i class="fa-solid fa-circle-check"></i></div>
      <h3>Surat pesanan berhasil dibuat</h3>
      <p>Surat tersimpan atas nama Apotek Medix. Buka untuk meninjau atau mencetak sebelum dikirim ke distributor.</p>
      <a id="pbfInvoiceLink" href="#" hidden></a>
      <button class="pbf-checkout-submit" id="pbfViewInvoiceButton" type="button" onclick="openPbfInvoice()"><i class="fa-solid fa-file-invoice"></i> Lihat / cetak SP</button>
    </div>
  </section>
</div>
<div class="pbf-invoice-overlay" id="pbfInvoiceOverlay" onclick="closePbfInvoice(event)">
  <div class="purchase-order-preview-panel">
    <div class="purchase-order-preview-toolbar">
      <span>Surat Pesanan Apotek Medix</span>
      <button type="button" onclick="closePbfInvoice()">Kembali</button>
    </div>
    <iframe id="pbfInvoiceFrame" title="Surat Pesanan Apotek Medix"></iframe>
  </div>
</div>
@endif

@include('partials.red-logo-checkout-confirmation')

{{-- ===== CART JAVASCRIPT ===== --}}
<script>
// Keranjang disimpan per konteks halaman melalui CART_CONFIG.storageKey.
const WA = @json(config('contacts.customer_service.whatsapp'));
const PHARMACIST_WA = @json(config('contacts.pharmacist.whatsapp'));
const CART_DEFAULT_SETTINGS = {
  storageKey: 'apotekmedix_cart_b2c',
  priceChannel: 'b2c',
  receiptStoreName: 'APOTEK MEDIX',
  receiptStoreAddress: 'Jl. Raya Ciawi Prapatan No.256 C, RT.04/RW.04, Sindangsari, Kec. Bogor Timur, Kota Bogor, Jawa Barat 16146',
  receiptFilePrefix: 'struk-apotekmedix',
  b2cFullAccess: false,
  continueShoppingUrl: @json(route('home'))
};
const CART_CONFIG = Object.assign({}, CART_DEFAULT_SETTINGS, window.cartSettings || {});
let cart = JSON.parse(localStorage.getItem(CART_CONFIG.storageKey) || '[]');
function persistCartContext() {
  localStorage.setItem('apotekmedix_active_cart_context', JSON.stringify({
    storageKey: CART_CONFIG.storageKey,
    priceChannel: CART_CONFIG.priceChannel || (CART_CONFIG.wholesaleOrder ? 'b2b' : (CART_CONFIG.priceMode || 'b2c')),
    wholesaleOrder: Boolean(CART_CONFIG.wholesaleOrder),
    adminPbfOrder: Boolean(CART_CONFIG.adminPbfOrder),
    adminPurchaseOrderBase: CART_CONFIG.adminPurchaseOrderBase || null,
    receiptStoreName: CART_CONFIG.receiptStoreName,
    receiptStoreAddress: CART_CONFIG.receiptStoreAddress,
    receiptFilePrefix: CART_CONFIG.receiptFilePrefix,
    continueShoppingUrl: CART_CONFIG.continueShoppingUrl
  }));
}
persistCartContext();
function getCartPackageLabel(item) {
  return String(item.packageUnitLabel || item.unitLabels?.box || (item.saleUnit === 'box' ? item.unitLabel : '') || 'Box').trim() || 'Box';
}
function isCartBoxPackage(item) {
  return getCartPackageLabel(item).toLocaleLowerCase() === 'box';
}
function normalizeCartPackageUnits(item) {
  const packageLabel = getCartPackageLabel(item);
  item.packageUnitLabel = packageLabel;
  if (isCartBoxPackage(item)) return item;

  const packagePrice = Number(item.unitPrices?.box ?? item.price ?? 0);
  item.saleUnit = 'box';
  item.unitLabel = packageLabel;
  item.price = packagePrice;
  item.unitLabels = { box: packageLabel };
  item.unitPrices = { box: packagePrice };
  item.unitFactors = { box: 1 };
  if (item.stockUnits !== null && item.stockUnits !== undefined && Number.isFinite(Number(item.stockUnits))) {
    item.availableQty = Math.max(0, Number(item.stockUnits));
  }

  return item;
}
cart = cart.map(item => {
  normalizeCartPackageUnits(item);
  if (CART_CONFIG.adminPbfOrder) item.discount = 0;
  return item;
});
function save() {
  localStorage.setItem(CART_CONFIG.storageKey, JSON.stringify(cart));
  persistCartContext();
  syncBadge();
}

if (CART_CONFIG.syncProductPrices) {
  document.querySelectorAll('.product-unit-data[data-sales-channel="b2c"]').forEach(select => {
    const item = cart.find(cartItem => String(cartItem.id) === String(select.dataset.stockProductId));
    if (!item) return;

    const unitPrices = JSON.parse(select.dataset.unitPrices || '{}');
    item.internalPriceUnavailable = CART_CONFIG.priceMode === 'internal' && Number(unitPrices.box || 0) <= 0;
    item.unitPrices = unitPrices;
    item.unitFactors = JSON.parse(select.dataset.unitFactors || '{}');
    item.unitLabels = JSON.parse(select.dataset.unitLabels || '{}');
    item.packageUnitLabel = item.unitLabels.box || item.packageUnitLabel;
    item.price = Number(unitPrices[item.saleUnit || 'box'] || unitPrices.box || 0);
    item.discount = 0;
  });
  save();
}

function syncBadge() {
  const tot = cart.reduce((s, i) => s + i.qty, 0);
  syncProductButtons();
  // Update semua elemen badge (navbar, home, dll)
  document.querySelectorAll('.cart-badge, #cartBadgeNav, #cartBadgeHome, #cartBadgeHeader').forEach(b => {
    if (!b) return;
    b.textContent = tot;
    b.style.display = tot > 0 ? 'flex' : 'none';
  });
  const navBtn = document.getElementById('cartNavBtn');
  if (navBtn) navBtn.style.display = 'flex';
  const cartBtnHome = document.querySelector('.cart-btn-home');
  if (cartBtnHome) {
    const badge = cartBtnHome.querySelector('.cart-badge-home');
    if (badge) { badge.textContent = tot; badge.style.display = tot > 0 ? 'inline-flex' : 'none'; }
  }
}

function syncProductButtons() {
  const cartProductIds = new Set(cart.filter(item => item.qty > 0).map(item => String(item.id)));
  document.querySelectorAll('.btn-cart[data-product-id]').forEach(button => {
    const added = cartProductIds.has(button.dataset.productId);
    button.classList.toggle('added', added);
    button.innerHTML = added
      ? '<i class="fa-solid fa-check"></i> Sudah di Keranjang'
      : '<i class="fa-solid fa-cart-plus"></i> Masukkan ke Keranjang';
  });
}

function rp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

function selectProductCardUnit(button) {
  if (button.dataset.redLogoLocked === '1' || button.classList.contains('is-red-logo-locked')) {
    openRedLogoModal(button.dataset.productName);
    return;
  }
  const selectedCard = button.closest('.product-unit-card');
  const unitSection = selectedCard?.closest('.product-unit-section');
  if (!selectedCard || !unitSection) return;

  unitSection.querySelectorAll('.product-unit-card').forEach(card => {
    const selected = card === selectedCard;
    card.classList.toggle('is-selected', selected);
    card.querySelector('.product-unit-choice')?.setAttribute('aria-pressed', String(selected));
  });

  const productCard = selectedCard.closest('.medicine-card, .prod-card, .discounted-product-card');
  const unitSelect = productCard?.querySelector('.product-unit-data');
  if (unitSelect) unitSelect.value = selectedCard.dataset.cartUnit;
  const priceTarget = button.dataset.priceTarget
    ? document.getElementById(button.dataset.priceTarget)
    : null;
  if (priceTarget) priceTarget.textContent = rp(button.dataset.unitPrice || 0);
}

async function refreshAvailableProductStock() {
  const stockBadges = Array.from(document.querySelectorAll('[data-stock-badge][data-stock-product-id]'));
  const productIds = [...new Set(stockBadges.map(badge => badge.dataset.stockProductId))];
  if (!productIds.length) return;

  const url = new URL(@json(route('products.stock-availability')), window.location.origin);
  productIds.forEach(id => url.searchParams.append('ids[]', id));
  const response = await fetch(url, {
    cache: 'no-store',
    headers: { Accept: 'application/json', 'Cache-Control': 'no-cache' }
  });
  if (!response.ok) throw new Error('Gagal memperbarui stok produk.');
  const payload = await response.json();

  Object.entries(payload.stock).forEach(([id, stock]) => {
    if (stock.terjual !== undefined) {
      document.querySelectorAll(`[data-sold-product-id="${id}"]`).forEach(el => {
        el.innerHTML = `<i class="fa-solid fa-fire-flame-curved"></i> Terjual ${Number(stock.terjual || 0).toLocaleString('id-ID')}`;
      });
    }
    document.querySelectorAll(`[data-stock-product-id="${id}"]`).forEach(element => {
      if (element.matches('[data-stock-badge]')) {
        const stockFactor = Math.max(1, Number(element.dataset.stockUnitFactor) || 1);
        const availableQuantity = stock.units / stockFactor;
        const available = availableQuantity > 0;
        const stockDisplay = Number(availableQuantity.toFixed(6)).toLocaleString('id-ID', { maximumFractionDigits: 6 });
        if (element.classList.contains('stock-badge')) {
          element.classList.toggle('stock-out', !available);
          element.classList.toggle('stock-low', available && availableQuantity <= 10);
          element.classList.toggle('stock-available', availableQuantity > 10);
        } else {
          element.classList.toggle('empty', !available);
          element.classList.toggle('low', available && availableQuantity <= 10);
          element.classList.toggle('available', availableQuantity > 10);
        }
        const icon = element.querySelector('i');
        if (icon) icon.className = `fa-solid ${available ? 'fa-boxes-stacked' : 'fa-circle-xmark'}`;
        element.lastChild.textContent = available ? ` Stok: ${stockDisplay}` : ' Stok habis';
      }
      if (element.matches('.product-unit-data')) {
        element.dataset.stockUnits = String(stock.units);
        const card = element.closest('.medicine-card, .prod-card, .discounted-product-card');
        card?.querySelectorAll('[data-stock-cart]').forEach(button => {
          button.disabled = stock.units < 1;
          button.setAttribute('aria-disabled', String(stock.units < 1));
        });
      }
    });
    if (window.DETAIL_PRODUCT && Number(window.DETAIL_PRODUCT.id) === Number(id)) {
      window.DETAIL_PRODUCT.stockUnits = Number(stock.units || 0);
      const outOfStock = window.DETAIL_PRODUCT.stockUnits < 1;
      document.querySelectorAll('#btnDetailBuy, #btnDetailCart').forEach(button => {
        button.disabled = outOfStock;
      });
      document.querySelectorAll('.detail-unit-pill[data-unit]').forEach(button => {
        const factor = Number(window.DETAIL_PRODUCT.unitFactors[button.dataset.unit] || 1);
        const availableQuantity = Math.floor(window.DETAIL_PRODUCT.stockUnits / factor);
        button.dataset.stock = String(availableQuantity);
        const stockLabel = button.querySelector('.detail-unit-pill-stock');
        if (stockLabel) stockLabel.textContent = `Stok: ${availableQuantity.toLocaleString('id-ID')}`;
      });
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  refreshAvailableProductStock().catch(error => console.error(error));
  window.setInterval(() => {
    if (!document.hidden) refreshAvailableProductStock().catch(error => console.error(error));
  }, 5000);
});

function openRedLogoModal(productName) {
  const overlay = document.getElementById('redLogoModalOverlay');
  if (!overlay) return;
  const nameLabel = (productName || 'Obat ini').trim();
  const nameEl = document.getElementById('redLogoProductName');
  if (nameEl) nameEl.textContent = nameLabel;

  const pharmacistBtn = document.getElementById('btnHubungiApoteker');
  if (pharmacistBtn) {
    const pharmacistMsg = `Halo Apoteker Apotek Medix, saya ingin berkonsultasi / menebus resep dokter untuk obat: ${nameLabel}`;
    pharmacistBtn.href = `https://wa.me/${PHARMACIST_WA}?text=${encodeURIComponent(pharmacistMsg)}`;
  }

  overlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeRedLogoModal() {
  const overlay = document.getElementById('redLogoModalOverlay');
  if (overlay) overlay.classList.remove('open');
  document.body.style.overflow = '';
}

function addToCart(id, name, price, img, brand, btn, unitSelectId = null, supplierOfferId = null, minimumQuantity = 1) {
  var unitSelect = unitSelectId ? document.getElementById(unitSelectId) : null;
  if (btn?.disabled) return;
  if (unitSelect && Number(unitSelect.dataset.stockUnits || 0) < 1) {
    alert('Stok produk sedang habis dan tidak dapat dimasukkan ke keranjang.');
    return;
  }
  const productCard = btn?.closest('.medicine-card, .prod-card, .discounted-product-card, .product-card');
  const isB2CChannel = !CART_CONFIG.kioskOrder
    && !CART_CONFIG.wholesaleOrder
    && !CART_CONFIG.adminPbfOrder
    && (unitSelect?.dataset.salesChannel === 'b2c'
      || (!unitSelect?.dataset.salesChannel && (!unitSelect?.dataset.priceChannel || unitSelect.dataset.priceChannel === 'b2c')));
  const isRedLogoLocked = unitSelect?.dataset.redLogoLocked !== undefined
    ? unitSelect.dataset.redLogoLocked === '1'
    : (btn?.classList.contains('is-red-logo-locked') || unitSelect?.dataset.redLogo === '1' || btn?.dataset.redLogo === '1');
  const requiresPrescription = unitSelect?.dataset.redLogo === '1'
    || btn?.dataset.requiresPrescription === '1'
    || btn?.dataset.redLogo === '1';

  if (isB2CChannel && isRedLogoLocked) {
    openRedLogoModal(name || btn?.dataset.productName);
    return;
  }
  var unitSelect = unitSelectId ? document.getElementById(unitSelectId) : null;

  const cardUnits = productCard?.querySelectorAll('.product-unit-card[data-cart-unit]') || [];
  if (cardUnits.length) {
    const unitPrices = unitSelect ? JSON.parse(unitSelect.dataset.unitPrices || '{}') : {};
    const unitFactors = unitSelect ? JSON.parse(unitSelect.dataset.unitFactors || '{}') : {};
    const unitLabels = unitSelect ? JSON.parse(unitSelect.dataset.unitLabels || '{}') : {};
    const stockUnits = unitSelect ? Number(unitSelect.dataset.stockUnits || 0) : 0;
    const selectedCard = Array.from(cardUnits).find(card => card.classList.contains('is-selected'));

    if (!selectedCard) {
      alert('Pilih satuan produk terlebih dahulu.');
      return;
    }

    const addition = { unit: selectedCard.dataset.cartUnit, qty: 1 };

    const unit = addition.unit;
    const unitLabel = unitLabels[unit] || unit.charAt(0).toUpperCase() + unit.slice(1);
    const factor = Number(unitFactors[unit] || 0);
    const availableQty = factor > 0 ? Math.floor(stockUnits / factor) : 0;
    if (!availableQty || addition.qty > availableQty) {
      alert('Stok untuk satuan ' + unitLabel + ' tidak mencukupi.');
      return;
    }

    const existing = cart.find(item => item.id === id && (item.saleUnit || 'box') === unit);
    if (existing && existing.qty + addition.qty > availableQty) {
      alert('Stok untuk satuan ' + unitLabel + ' tidak mencukupi.');
      return;
    }
    if (existing) {
      existing.name = name;
      existing.price = Number(unitPrices[unit] || 0);
      existing.supplierOfferId = supplierOfferId;
      existing.minimumQuantity = Number(minimumQuantity) || 1;
      existing.qty += addition.qty;
      existing.brand = brand;
      existing.img = img;
      existing.requiresPrescription = requiresPrescription;
      existing.discount = 0;
      existing.unitLabels = unitLabels;
      existing.unitLabel = unitLabel;
      existing.unitPrices = unitPrices;
      existing.unitFactors = unitFactors;
      existing.packageUnitLabel = unitLabels.box || fallbackUnitLabel;
      existing.saleUnit = unit;
      existing.availableQty = availableQty;
      existing.stockUnits = stockUnits;
    } else {
      cart.push({
        id, name, price: Number(unitPrices[unit] || 0), img, brand: brand || '',
        qty: addition.qty, note: '', discount: 0, saleUnit: unit, unitLabel,
        unitLabels, unitPrices, unitFactors, availableQty, stockUnits,
        requiresPrescription,
        packageUnitLabel: unitLabels.box || fallbackUnitLabel,
        supplierOfferId, minimumQuantity: Number(minimumQuantity) || 1
      });
    }

    save();
    render();

    return;
  }

  const selectedOption = unitSelect?.selectedOptions?.[0];
  const saleUnit = unitSelect?.value || 'box';
  const unitPrices = unitSelect ? JSON.parse(unitSelect.dataset.unitPrices || '{}') : null;
  const unitFactors = unitSelect ? JSON.parse(unitSelect.dataset.unitFactors || '{}') : null;
  const fallbackUnitLabel = btn?.dataset.packageUnitLabel || 'Box';
  const unitLabels = unitSelect
    ? JSON.parse(unitSelect.dataset.unitLabels || '{}')
    : { box: fallbackUnitLabel };
  const unitLabel = unitLabels?.[saleUnit] || saleUnit.charAt(0).toUpperCase() + saleUnit.slice(1);
  const packageUnitLabel = unitLabels?.box || fallbackUnitLabel;
  const unitPrice = selectedOption ? Number(selectedOption.dataset.price) : Number(price);
  const unitFactor = unitFactors?.[saleUnit] || 1;
  const availableQty = unitSelect ? Math.floor(Number(unitSelect.dataset.stockUnits || 0) / unitFactor) : null;
  const ex = cart.find(i => i.id === id && (i.saleUnit || 'box') === saleUnit);
  console.log('addToCart:', { id, name, brand, img });
  if (availableQty !== null && (!availableQty || (ex?.qty || 0) + 1 > availableQty)) {
    alert('Stok untuk satuan ' + unitLabel + ' tidak mencukupi.');
    return;
  }
  if (ex) {
    ex.qty++;
    ex.brand = brand;
    ex.requiresPrescription = requiresPrescription;
    ex.supplierOfferId = supplierOfferId;
    ex.minimumQuantity = Number(minimumQuantity) || 1;
    ex.internalPriceUnavailable = CART_CONFIG.priceMode === 'internal' && unitPrice <= 0;
    ex.unitLabels = unitLabels;
    ex.unitLabel = unitLabel;
    ex.packageUnitLabel = packageUnitLabel;
  } else cart.push({
    id, name, price: unitPrice, img, brand: brand || '', qty: 1, note: '', discount: 0,
    saleUnit, unitLabel, unitLabels, unitPrices, unitFactors, availableQty, packageUnitLabel,
    stockUnits: unitSelect ? Number(unitSelect.dataset.stockUnits || 0) : null,
    requiresPrescription,
    supplierOfferId, minimumQuantity: Number(minimumQuantity) || 1,
    internalPriceUnavailable: CART_CONFIG.priceMode === 'internal' && unitPrice <= 0
  });
  console.log('Cart item:', cart.find(i => i.id === id && (i.saleUnit || 'box') === saleUnit));
  save(); render();
  if (btn && !btn.dataset.productId) {
    const origHtml = btn.dataset.origHtml || btn.innerHTML;
    btn.dataset.origHtml = origHtml;
    btn.classList.add('added');
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Ditambahkan!';
    setTimeout(() => {
      btn.classList.remove('added');
      btn.innerHTML = origHtml;
    }, 1400);
  }
}

function removeItem(id, unit = 'box') { cart = cart.filter(i => !(i.id === id && (i.saleUnit || 'box') === unit)); save(); render(); }
// Alias for compatibility
function removeFromCart(id) { removeItem(id); }

function changeQty(id, d, unit = 'box') {
  const it = cart.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!it) return;
  const nextQty = it.qty + d;
  if (d > 0 && it.availableQty != null && nextQty > it.availableQty) {
    alert('Stok untuk satuan ' + (it.unitLabel || unit) + ' tidak mencukupi.');
    return;
  }
  it.qty = nextQty;
  if (it.qty <= 0) removeItem(id, unit); else { save(); render(); }
}

function setQty(id, value, unit = 'box') {
  const it = cart.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!it) return;
  if (it.availableQty === 0) {
    removeItem(id, unit);
    alert('Stok untuk satuan tersebut sudah habis.');
    return;
  }
  it.qty = Math.max(1, parseInt(value, 10) || 1);
  if (it.availableQty != null && it.qty > it.availableQty) {
    it.qty = it.availableQty;
    alert('Jumlah disesuaikan dengan stok yang tersedia.');
  }
  save(); render();
}

function setNote(id, value, unit = 'box') {
  const it = cart.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!it) return;
  it.note = String(value || '').trim();
  save();
}

function setDiscount(id, value, unit = 'box') {
  const it = cart.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!it) return;
  const raw = String(value).replace(/[^0-9]/g, '');
  it.discount = Math.min(parseInt(raw, 10) || 0, it.price * it.qty);
  save();
  // update live preview
  const previewEl = document.getElementById('disc-preview-' + id + '-' + unit);
  if (previewEl) {
    const subtotal   = it.price * it.qty;
    const afterDisc  = subtotal - it.discount;
    if (it.discount > 0) {
      previewEl.innerHTML = `<span style="text-decoration:line-through;color:#9ca3af;">${rp(subtotal)}</span>
        <span style="color:#ef4444;font-weight:700;font-size:.78rem;">-${rp(it.discount)}</span>
        <span style="color:#10b981;font-weight:800;">${rp(afterDisc)}</span>`;
    } else {
      previewEl.innerHTML = '';
    }
    // update total di footer
    const totalAfter = cart.reduce((s, i) => s + (i.price * i.qty) - (i.discount || 0), 0);
    const tot = document.getElementById('cartTotalAmt');
    if (tot) tot.textContent = rp(totalAfter);
  }
}

function setCartPrice(id, value, unit = 'box') {
  const item = cart.find(i => i.id === id && (i.saleUnit || 'box') === unit);
  if (!item) return;

  const raw = String(value).replace(/[^0-9]/g, '');
  item.price = parseInt(raw, 10) || 0;
  save();

  const priceLabel = document.querySelector(`[data-cart-price="${id}-${unit}"]`);
  if (priceLabel) priceLabel.textContent = `${rp(item.price)} / ${item.unitLabel || unit}`;

  const total = cart.reduce((sum, cartItem) => sum + (cartItem.price * cartItem.qty) - (cartItem.discount || 0), 0);
  const totalLabel = document.getElementById('cartTotalAmt');
  if (totalLabel) totalLabel.textContent = rp(total);
}

function clearCart() { cart = []; save(); render(); }

function render() {
  const body = document.getElementById('cartBody');
  const foot = document.getElementById('cartFoot');
  const tot  = document.getElementById('cartTotalAmt');
  if (!body || !foot || !tot) return;

  if (!cart.length) {
    foot.style.display = 'none';
    body.innerHTML = '<div class="cart-empty-msg"><i class="fa-solid fa-cart-shopping"></i><p>Keranjang masih kosong</p></div>';
    return;
  }
  foot.style.display = '';
  let html = '', total = 0;
  cart.forEach(it => {
    const unit = it.saleUnit || 'box';
    const unitLabel = it.unitLabel || it.unitLabels?.[unit] || unit.charAt(0).toUpperCase() + unit.slice(1);
    const packageUnitLabel = getCartPackageLabel(it);
    const unitOptions = isCartBoxPackage(it)
      ? Object.entries(it.unitPrices || {})
      : [['box', Number(it.unitPrices?.box ?? it.price)]];
    const key = `${it.id}-${unit}`;
    total += (it.price * it.qty) - (it.discount || 0);
    const imgHtml = it.img
      ? `<img src="${it.img}" alt="">`
      : '<i class="fa-solid fa-pills" style="color:#90caf9;font-size:1.4rem;"></i>';
    html += `<div class="cart-item-row">
      <div class="cart-item-thumb">${imgHtml}</div>
      <div class="cart-item-info">
        <div class="cart-item-name">${it.name}</div>
          ${it.internalPriceUnavailable
            ? '<div class="cart-item-price-unavailable" role="alert">Harga internal produk ini belum diatur.</div>'
            : `<div class="cart-item-price" data-cart-price="${key}">${rp(it.price)} / ${unitLabel}</div>`}
          ${it.unitPrices ? `<select onchange="changeCartUnit(${it.id}, '${unit}', this.value)" style="width:100%;margin-top:.4rem;padding:.4rem;border:1px solid #cbd5e1;border-radius:8px;">
            ${unitOptions.map(([value, unitPrice]) => `<option value="${value}" ${value === unit ? 'selected' : ''}>${value === 'box' ? packageUnitLabel : (it.unitLabels?.[value] || value.charAt(0).toUpperCase() + value.slice(1))} · ${rp(unitPrice)}</option>`).join('')}
          </select>` : ''}
          <div class="cart-qty-row">
            <button class="qty-btn" onclick="changeQty(${it.id},-1,'${unit}')">−</button>
            <input type="number" class="qty-num" min="1" ${it.availableQty != null ? `max="${it.availableQty}"` : ''} value="${it.qty}" onchange="setQty(${it.id}, this.value,'${unit}')"
              style="width:4rem;text-align:center;border:1px solid #d1d5db;border-radius:8px;padding:0.4rem;" />
            <button class="qty-btn" onclick="changeQty(${it.id},1,'${unit}')">+</button>
          </div>
          @unless($isAdminPbfOrder)
          <textarea class="cart-note" placeholder="Catatan produk..." onchange="setNote(${it.id}, this.value,'${unit}')"
          style="width:100%;margin-top:0.75rem;padding:0.65rem 0.85rem;border:1px solid #e5e7eb;border-radius:12px;font-size:0.9rem;min-height:72px;resize:vertical;">${it.note || ''}</textarea>
          @endunless
        @if($isAdminPbfOrder)
          <label style="display:block;margin-top:.6rem;font-size:.78rem;font-weight:700;color:#6b7280;">
            Harga per ${unitLabel}
          </label>
          <div style="display:flex;align-items:center;gap:.5rem;margin-top:.3rem;">
            <span style="font-size:.82rem;color:#374151;flex-shrink:0;">Rp</span>
            <input type="text" inputmode="numeric" value="${Math.round(Number(it.price) || 0)}"
              oninput="setCartPrice(${it.id}, this.value,'${unit}')"
              aria-label="Harga ${it.name} per ${unitLabel}"
              style="flex:1;min-width:0;padding:.45rem .7rem;border:1.5px solid #93c5fd;border-radius:10px;font-size:.88rem;outline:none;color:#374151;background:#fff;"
            />
          </div>
        @else
        <div style="${CART_CONFIG.priceMode === 'internal' ? 'display:none;' : 'margin-top:.6rem;'}">
          <label style="font-size:.78rem;font-weight:700;color:#6b7280;display:block;margin-bottom:.3rem;">
            <i class="fa-solid fa-tag" style="color:#ef4444;margin-right:.25rem;"></i>Ajuan Potongan Harga
          </label>
          <div style="display:flex;align-items:center;gap:.5rem;">
            <span style="font-size:.82rem;color:#374151;flex-shrink:0;">Rp</span>
            <input type="text" inputmode="numeric"
              placeholder="0"
              value="${it.discount > 0 ? it.discount : ''}"
              oninput="setDiscount(${it.id}, this.value,'${unit}')"
              style="flex:1;padding:.45rem .7rem;border:1.5px solid #fca5a5;border-radius:10px;font-size:.88rem;outline:none;color:#374151;background:#fff9f9;"
            />
          </div>
          <div id="disc-preview-${key}" style="margin-top:.4rem;display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;min-height:1.2rem;font-size:.84rem;">
            ${it.discount > 0 ? `<span style="text-decoration:line-through;color:#9ca3af;">${rp(it.price * it.qty)}</span><span style="color:#ef4444;font-weight:700;font-size:.78rem;">-${rp(it.discount)}</span><span style="color:#10b981;font-weight:800;">${rp(it.price * it.qty - it.discount)}</span>` : ''}
          </div>
        </div>
        @endif
      </div>
      <button class="cart-item-del" onclick="removeItem(${it.id},'${unit}')"><i class="fa-solid fa-trash"></i></button>
    </div>`;
  });
  body.innerHTML = html;
  tot.textContent = rp(total);
}

function updateCardUnitPrice(selectId, targetId) {
  const select = document.getElementById(selectId);
  const target = document.getElementById(targetId);
  if (select && target) target.textContent = rp(Number(select.selectedOptions[0]?.dataset.price || 0));
}

function changeCartUnit(id, oldUnit, newUnit) {
  const item = cart.find(i => i.id === id && (i.saleUnit || 'box') === oldUnit);
  if (!item || !item.unitPrices || !item.unitFactors) return;
  if (!isCartBoxPackage(item) && newUnit !== 'box') {
    render();
    return;
  }
  const factor = item.unitFactors[newUnit];
  const available = item.stockUnits === null ? null : Math.floor(Number(item.stockUnits) / factor);
  if (available !== null && available < 1) {
    alert('Stok untuk satuan tersebut sudah habis.');
    render();
    return;
  }
  const existing = cart.find(i => i.id === id && (i.saleUnit || 'box') === newUnit);
  if (existing) {
    existing.unitLabel = item.unitLabels?.[newUnit] || newUnit.charAt(0).toUpperCase() + newUnit.slice(1);
    existing.unitLabels = item.unitLabels;
    existing.packageUnitLabel = item.packageUnitLabel || getCartPackageLabel(item);
    existing.qty += item.qty;
    existing.availableQty = available;
    existing.discount = Math.min((existing.discount || 0) + (item.discount || 0), existing.price * existing.qty);
    if (available !== null && existing.qty > available) {
      existing.qty = available;
      alert('Jumlah disesuaikan dengan stok yang tersedia.');
    }
    cart = cart.filter(i => i !== item);
  } else {
    item.saleUnit = newUnit;
    item.unitLabel = item.unitLabels?.[newUnit] || newUnit.charAt(0).toUpperCase() + newUnit.slice(1);
    item.packageUnitLabel = item.packageUnitLabel || getCartPackageLabel(item);
    item.price = Number(item.unitPrices[newUnit]);
    item.availableQty = available;
    if (available !== null && item.qty > available) {
      item.qty = available;
      alert('Jumlah disesuaikan dengan stok yang tersedia.');
    }
  }
  save();
  render();
}

function openCart() {
  save();
  if (CART_CONFIG.adminPbfOrder) {
    document.getElementById('cartDrawer').classList.add('open');
    document.getElementById('cartOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    render();
    return;
  }
  window.location.href = '{{ route("cart.index") }}';
}

function closeCart() {
  document.getElementById('cartDrawer').classList.remove('open');
  document.getElementById('cartOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function escapeHtml(text) {
  return String(text || '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function openOrder() {
  if (!cart.length) return;
  save();
  if (CART_CONFIG.adminPbfOrder) {
    openPbfCheckout();
    return;
  }
  if (window.confirmRedLogoCheckout(cart, {
    storageKey: CART_CONFIG.storageKey,
    priceChannel: CART_CONFIG.priceChannel || 'b2c',
    b2cFullAccess: CART_CONFIG.b2cFullAccess,
    wholesaleOrder: CART_CONFIG.wholesaleOrder
  })) return;
  window.location.href = '{{ route("checkout.index") }}';
}

function openPbfCheckout() {
  const summary = document.getElementById('pbfOrderSummary');
  let total = 0;
  summary.innerHTML = cart.map((item, index) => {
    const subtotal = item.price * item.qty;
    total += subtotal;
    return `<div class="pbf-checkout-summary-item">
      <span>${index + 1}. ${escapeHtml(item.name)} <span style="color:#64748b;">× ${item.qty} ${escapeHtml(item.unitLabel || 'Pcs')}</span></span>
      <strong>${rp(subtotal)}</strong>
    </div>`;
  }).join('');
  document.getElementById('pbfOrderItemCount').textContent = `${cart.reduce((count, item) => count + item.qty, 0)} item`;
  document.getElementById('pbfOrderTotal').textContent = rp(total);
  document.getElementById('pbfCheckoutForm').style.display = '';
  document.getElementById('pbfCheckoutResult').style.display = 'none';
  document.getElementById('pbfCheckoutError').style.display = 'none';
  document.getElementById('pbfSubmitOrderButton').disabled = false;
  document.getElementById('pbfCheckoutOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeOrder() {
  document.getElementById('orderOverlay').style.display = 'none';
  document.getElementById('orderModal').style.display = 'none';
  document.body.style.overflow = '';
}

function closePbfCheckout(event) {
  if (event && event.target !== event.currentTarget) return;
  const overlay = document.getElementById('pbfCheckoutOverlay');
  if (!overlay) return;

  overlay.classList.remove('open');
  closePbfInvoice();
  document.body.style.overflow = '';
}

function openPbfInvoice() {
  const overlay = document.getElementById('pbfInvoiceOverlay');
  const frame = document.getElementById('pbfInvoiceFrame');
  const invoiceUrl = document.getElementById('pbfInvoiceLink')?.href;
  if (!overlay || !frame || !invoiceUrl || invoiceUrl === window.location.href || invoiceUrl.endsWith('#')) {
    throw new Error('Surat pesanan belum tersedia untuk ditampilkan.');
  }

  frame.src = invoiceUrl;
  overlay.classList.add('open');
}

function closePbfInvoice(event) {
  if (event && event.target !== event.currentTarget) return;
  const overlay = document.getElementById('pbfInvoiceOverlay');
  if (!overlay) return;

  overlay.classList.remove('open');
  document.getElementById('pbfInvoiceFrame').src = 'about:blank';
}

function openInvoice(event) {
  if (!CART_CONFIG.adminPbfOrder) return;
  event.preventDefault();
  openPbfInvoice();
}

function toggleBuyerType() {
  const typeInput = document.getElementById('f_jenis');
  if (!typeInput) return;
  const type = typeInput.value;
  document.getElementById('buyerFieldsUmum').style.display   = type === 'umum'   ? '' : 'none';
  document.getElementById('buyerFieldsApotik').style.display = ['apotik', 'dokter'].includes(type) ? '' : 'none';
  const pbfFields = document.getElementById('pbfDestinationFields');
  if (pbfFields) pbfFields.style.display = CART_CONFIG.adminPbfOrder ? '' : 'none';
  const signatoryFields = document.getElementById('signatoryFields');
  if (signatoryFields) signatoryFields.style.display = CART_CONFIG.adminPbfOrder ? '' : 'none';
  toggleTempoFields();
}

function toggleTempoFields() {
  const paymentSelect = document.getElementById('f_payment');
  const tempoFields = document.getElementById('tempoFields');
  if (!paymentSelect || !tempoFields) return;
  const typeInput = document.getElementById('f_jenis');
  const buyerType = typeInput ? typeInput.value : (CART_CONFIG.wholesaleOrder || CART_CONFIG.adminPbfOrder ? 'apotik' : 'umum');
  const isTempo = paymentSelect.value === 'Tempo';
  const allowTempo = ['apotik', 'dokter'].includes(buyerType);
  tempoFields.style.display = isTempo && allowTempo ? '' : 'none';
  if (!allowTempo && isTempo) {
    paymentSelect.value = '';
  }
}

function escapePdfText(text) {
  return String(text || '').replace(/\\/g, '\\\\').replace(/\(/g, '\\(').replace(/\)/g, '\\)');
}

function buildReceiptPdf(orderData) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ unit: 'pt', format: 'a4', orientation: 'portrait' });
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  const leftMargin = 40;
  const receiptWidth = pageWidth - leftMargin * 2;
  const labelWidth = 120;
  const valueX = leftMargin + labelWidth;
  const rightX = leftMargin + receiptWidth;
  const unitPriceColWidth = 100;
  const subtotalColWidth = 80;
  const subtotalX = rightX;
  const unitPriceX = rightX - subtotalColWidth - 8;
  const qtyX = unitPriceX - 60;
  const bottomMargin = 50;
  let y = 80;
  let pageNum = 1;

  const line = (text, options = {}) => {
    const align = options.align || 'left';
    const x = align === 'center' ? leftMargin + receiptWidth / 2 : align === 'right' ? rightX : leftMargin;
    doc.text(text, x, y, { align });
    y += (options.spacing || 18);
  };

  const drawLine = () => { doc.setDrawColor(180,180,180); doc.line(leftMargin, y, rightX, y); y += 8; };

  const ensureSpace = (space = 0) => {
    if (y + space > pageHeight - bottomMargin) {
      doc.addPage();
      pageNum += 1;
      y = 80;
      drawReceiptHeader();
      startProductSection();
    }
  };

  const addressLines = CART_CONFIG.receiptStoreAddress.includes('\n')
    ? CART_CONFIG.receiptStoreAddress.split(/\n/).filter(Boolean)
    : CART_CONFIG.receiptStoreAddress.split(/,\s*/);

  const drawReceiptHeader = () => {
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.text(CART_CONFIG.receiptStoreName, leftMargin + receiptWidth / 2, y, { align: 'center' });
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.text(`Halaman ${pageNum}`, rightX, 40, { align: 'right' });
    y += 28;
    doc.setFontSize(12);
    addressLines.forEach((lineText) => {
      doc.text(lineText, leftMargin + receiptWidth / 2, y, { align: 'center' });
      y += 14;
    });
    if (CART_CONFIG.receiptStoreName === CART_DEFAULT_SETTINGS.receiptStoreName) {
      doc.setFontSize(13);
      doc.text('Apotik Online Terpercaya', leftMargin + receiptWidth / 2, y, { align: 'center' });
      y += 14;
      doc.text('www.apotekmedix.com', leftMargin + receiptWidth / 2, y, { align: 'center' });
      y += 16;
    } else {
      y += 16;
    }
    drawLine();
  };

  const startProductSection = () => {
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.text('DAFTAR PRODUK', leftMargin + receiptWidth / 2, y, { align: 'center' });
    y += 21;
    doc.setFont('helvetica', 'normal');
  };

  drawReceiptHeader();

  const writeMeta = (label, value) => {
    doc.setFontSize(10);
    doc.text(label + ' :', leftMargin, y);
    const maxValueWidth = receiptWidth - labelWidth - 10;
    const valueLines = doc.splitTextToSize(String(value || '-'), maxValueWidth);
    doc.text(valueLines, valueX, y);
    y += 16 * valueLines.length;
  };

  writeMeta('ID Pesanan', `#${orderData.id || '-'}`);
  writeMeta('Tanggal', new Date().toLocaleString('id-ID'));
  writeMeta('Jenis Pembeli', orderData.buyer_type === 'apotik' ? 'Apotik' : orderData.buyer_type === 'pbf' ? 'PBF' : 'Umum');
  if (orderData.buyer_name) writeMeta(orderData.buyer_type === 'pbf' ? 'Nama Pemesan' : 'Pembeli', orderData.buyer_name);
  if (orderData.no_izin_pbf) writeMeta('No. Izin PBF', orderData.no_izin_pbf);
  if (orderData.apj) writeMeta('APJ', orderData.apj);
  if (orderData.npwp) writeMeta('No. NPWP', orderData.npwp);
  if (orderData.phone) writeMeta(orderData.buyer_type === 'pbf' ? 'Nomor Tlp' : 'No. HP', orderData.phone);
  if (orderData.address) {
    const addressParts = [orderData.address];
    if (orderData.kecamatan) addressParts.push(orderData.kecamatan);
    if (orderData.kota) addressParts.push(orderData.kota);
    writeMeta('Alamat', addressParts.filter(Boolean).join(', '));
  }
  if (orderData.buyer_type === 'apotik') {
    if (orderData.sia) writeMeta('SIA', orderData.sia);
    if (orderData.sipa) writeMeta('SIPA', orderData.sipa);
  }
  drawLine();
  startProductSection();

  let total = 0;
  const cartItems = orderData.cartItems || cart;
  cartItems.forEach(it => {
    const subtotal = it.price * it.qty;
    const afterDiscount = subtotal - (it.discount || 0);
    total += afterDiscount;
    const nameWrapWidth = qtyX - leftMargin - 12;
    
    // Tampilkan nama produk
    const nameLines = doc.splitTextToSize(it.name, nameWrapWidth);
    nameLines.forEach((ln, idx) => {
      ensureSpace(18);
      const textX = leftMargin + (idx === 0 ? 0 : 8);
      doc.text(ln, textX, y);
      if (idx === 0) {
        const unit = it.saleUnit || 'box';
        doc.text(`${it.qty} ${it.unitLabel || it.unitLabels?.[unit] || unit.charAt(0).toUpperCase() + unit.slice(1)}`, qtyX, y, { align: 'right' });
        doc.text(`x ${rp(it.price)}`, unitPriceX, y, { align: 'right' });
        doc.text(rp(afterDiscount), subtotalX, y, { align: 'right' });
      }
      y += 18;
    });
    
    // Tampilkan Pabrik/Brand di baris berikutnya
    if (it.brand && String(it.brand).trim() !== '') {
      ensureSpace(12);
      doc.setFontSize(8);
      doc.setTextColor(100, 116, 139);
      doc.text(`Pabrik: ${it.brand}`, leftMargin + 8, y);
      doc.setTextColor(0, 0, 0);
      y += 11;
    }
    
    // Tampilkan Potongan Harga jika ada
    if (it.discount && it.discount > 0) {
      ensureSpace(12);
      doc.setFontSize(8);
      doc.setTextColor(239, 68, 68);
      doc.text(`Potongan: -${rp(it.discount)}`, leftMargin + 8, y);
      doc.setTextColor(0, 0, 0);
      y += 11;
    }
    
    y += 4;
  });

  ensureSpace(80);
  drawLine();
  y += 12;
  doc.setFont('helvetica', 'bold'); doc.setFontSize(18);
  doc.text('TOTAL', leftMargin, y);
  doc.text(rp(total), rightX, y, { align: 'right' }); y += 36;
  drawLine();
  if (orderData.payment_method) {
    const paymentLabel = orderData.payment_method === 'Tempo' && orderData.payment_term_days
      ? `Tempo (${orderData.payment_term_days} hari)`
      : orderData.payment_method;
    doc.setFont('helvetica', 'normal'); doc.setFontSize(8);
    line(`Pembayaran  : ${paymentLabel}`, { spacing: 13 });
    drawLine();
  }
  doc.setFont('helvetica', 'normal'); doc.setFontSize(8);
  line('Terima kasih telah berbelanja!', { align: 'center', spacing: 11 });
  line('Hubungi kami via WhatsApp: 085890007359', { align: 'center', spacing: 11 });
  return doc.output('bloburl');
}

async function submitPbfOrder() {
  const errorPanel = document.getElementById('pbfCheckoutError');
  const submitButton = document.getElementById('pbfSubmitOrderButton');
  const distributor = document.getElementById('pbfOrderDistributor').value.trim();
  const address = document.getElementById('pbfOrderAddress').value.trim();
  const supplierPhone = document.getElementById('pbfOrderPhone').value.trim();
  const purchaseOrderType = document.getElementById('pbfOrderType').value;
  const signer = document.getElementById('pbfOrderSigner').value.trim();
  const title = document.getElementById('pbfOrderTitleField').value.trim();
  const sia = document.getElementById('pbfOrderSia').value.trim();
  const sipa = document.getElementById('pbfOrderSipa').value.trim();

  errorPanel.style.display = 'none';
  if (!cart.length) {
    errorPanel.textContent = 'Keranjang pengadaan masih kosong.';
    errorPanel.style.display = 'block';
    return;
  }
  if (!distributor || !address || !supplierPhone || !signer || !title || !sia || !sipa) {
    errorPanel.textContent = 'Lengkapi nama, alamat, nomor telepon distributor, serta semua data penandatangan.';
    errorPanel.style.display = 'block';
    return;
  }
  const invalidPrice = cart.find(item => !Number.isFinite(Number(item.price)) || Number(item.price) <= 0);
  if (invalidPrice) {
    errorPanel.textContent = `Harga ${invalidPrice.name} harus lebih dari Rp 0.`;
    errorPanel.style.display = 'block';
    return;
  }

  if (CART_CONFIG.selectedSupplier) {
    const invalidMinimum = cart.find(item =>
      !item.supplierOfferId || item.qty < Number(item.minimumQuantity || 1)
    );
    if (invalidMinimum) {
      errorPanel.textContent = `${invalidMinimum.name}: jumlah minimal pembelian ${invalidMinimum.minimumQuantity} ${invalidMinimum.unitLabel || 'unit'}.`;
      errorPanel.style.display = 'block';
      return;
    }

    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Membuat surat pesanan...';
    try {
      const response = await fetch(CART_CONFIG.supplierOrderUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          supplier_id: CART_CONFIG.selectedSupplier.id,
          quantities: Object.fromEntries(cart.map(item => [item.supplierOfferId, item.qty])),
          unit_prices: Object.fromEntries(cart.map(item => [item.supplierOfferId, item.price])),
          purchase_order_type: purchaseOrderType,
          purchase_order_profile: document.getElementById('pbfOrderProfile').value,
          requester_name: signer,
          apj: title,
          sia,
          sipa
        })
      });
      const result = await response.json();
      if (!response.ok) {
        const messages = Object.values(result.errors || {}).flat().join(' ');
        throw new Error(messages || result.message || 'Surat pesanan gagal dibuat.');
      }

      document.getElementById('pbfInvoiceLink').href = result.purchase_order_url;
      document.getElementById('pbfCheckoutForm').style.display = 'none';
      document.getElementById('pbfCheckoutResult').style.display = '';
      cart = [];
      save();
      render();
      return;
    } catch (error) {
      errorPanel.textContent = error instanceof Error ? error.message : 'Surat pesanan gagal dibuat.';
      errorPanel.style.display = 'block';
      return;
    } finally {
      submitButton.disabled = false;
      submitButton.innerHTML = '<i class="fa-solid fa-file-signature"></i> Buat Surat Pesanan';
    }
  }

  const originalTotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
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
    pbf_phone: supplierPhone,
    purchase_order_type: purchaseOrderType,
    purchase_order_profile: document.getElementById('pbfOrderProfile').value,
    items: cart.map(item => ({
      id: item.id,
      nama_obat: item.name,
      quantity: item.qty,
      harga: item.price,
      sale_unit: item.saleUnit || 'box',
      catatan: item.note || ''
    })),
    total: originalTotal,
    original_total: originalTotal,
    discounted_total: originalTotal,
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

    window.pbfOrderPayload = { ...payload, id: result.id };
    const invoiceUrl = CART_CONFIG.adminPurchaseOrderBase
      ? CART_CONFIG.adminPurchaseOrderBase.replace('__ORDER__', result.id)
      : result.invoice_url;
    document.getElementById('pbfInvoiceLink').href = invoiceUrl;
    document.getElementById('pbfCheckoutForm').style.display = 'none';
    document.getElementById('pbfCheckoutResult').style.display = '';
    cart = [];
    save();
    render();
  } catch (error) {
    errorPanel.textContent = error instanceof Error ? error.message : 'Surat pesanan gagal dibuat.';
    errorPanel.style.display = 'block';
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fa-solid fa-file-signature"></i> Buat Surat Pesanan';
  }
}

async function submitOrder() {
  const jenis = CART_CONFIG.adminPbfOrder || CART_CONFIG.wholesaleOrder ? 'apotik' : (document.getElementById('f_jenis')?.value || 'umum');
  const err = document.getElementById('formErr');
  err.style.display = 'none';

  const originalTotal = cart.reduce((s, it) => s + (it.price * it.qty), 0);
  const discountedTotal = cart.reduce((s, it) => s + (it.price * it.qty) - (it.discount || 0), 0);
  const paymentMethod = document.getElementById('f_payment')?.value.trim() || '';
  const paymentTermDays = Number(document.getElementById('f_payment_term_days')?.value || 0);
  const payload = {
    buyer_type: jenis,
    price_channel: CART_CONFIG.adminPbfOrder
      ? null
      : (CART_CONFIG.priceChannel || (CART_CONFIG.wholesaleOrder ? 'b2b' : (CART_CONFIG.priceMode || 'b2c'))),
    items: cart.map(it => ({
      id: it.id,
      nama_obat: it.name,
      quantity: it.qty,
      harga: it.price,
      sale_unit: it.saleUnit || 'box',
      catatan: it.note || '',
      ...(!CART_CONFIG.adminPbfOrder ? { potongan: it.discount || 0 } : {})
    })),
    total: discountedTotal,
    original_total: originalTotal,
    discounted_total: discountedTotal,
    approval_status: 'pending',
    payment_method: paymentMethod,
    payment_term_days: paymentMethod === 'Tempo' ? paymentTermDays : null,
    transaction_channel: 'online',
    is_pbf_purchase_order: Boolean(CART_CONFIG.adminPbfOrder),
  };

  if (CART_CONFIG.adminPbfOrder) {
    payload.pbf_name = document.getElementById('f_pbf_name').value.trim();
    payload.pbf_address = document.getElementById('f_pbf_address').value.trim();
    if (!payload.pbf_name || !payload.pbf_address) {
      err.textContent = 'Nama PBF dan alamat PBF wajib diisi.'; err.style.display = 'block'; return;
    }
  }

  if (CART_CONFIG.adminPbfOrder) {
    payload.buyer_name = 'Apotek Medix';
    payload.phone = '085890007359';
    payload.address = 'ITC Cempaka Mas LT.1 No.88, Jakarta Pusat';
    payload.requester_name = document.getElementById('f_signer_name').value.trim();
    payload.apj = document.getElementById('f_signer_title').value.trim();
    payload.sia = document.getElementById('f_signer_sia').value.trim();
    payload.sipa = document.getElementById('f_signer_sipa').value.trim();
    if (!payload.requester_name || !payload.apj || !payload.sia || !payload.sipa) {
      err.textContent = 'Data penandatangan (nama, jabatan, SIA, dan SIPA) wajib diisi.'; err.style.display = 'block'; return;
    }
  } else if (CART_CONFIG.wholesaleOrder || jenis === 'apotik' || jenis === 'dokter') {
    payload.requester_name = document.getElementById('f_penanggung_jawab').value.trim();
    payload.outlet_name = document.getElementById('f_outlet').value.trim();
    payload.buyer_name = payload.requester_name;
    payload.phone    = document.getElementById('f_hp_apotik').value.trim();
    payload.address  = document.getElementById('f_alamat_apotik').value.trim();
    if (!payload.requester_name || !payload.outlet_name || !payload.phone || !payload.address) {
      err.textContent = 'Nama pemesan, nama outlet, alamat, dan nomor telepon wajib diisi.'; err.style.display = 'block'; return;
    }
  } else {
    payload.buyer_name = document.getElementById('f_nama').value.trim();
    payload.phone    = document.getElementById('f_hp').value.trim();
    const addressParts = [
      document.getElementById('f_alamat').value.trim(),
      document.getElementById('f_apartemen').value.trim(),
      document.getElementById('f_kota').value.trim(),
      document.getElementById('f_provinsi').value.trim(),
      document.getElementById('f_kode_pos').value.trim(),
    ].filter(Boolean);
    payload.address = addressParts.join(', ');
    payload.kota = document.getElementById('f_kota').value.trim();
    if (!payload.buyer_name || !payload.phone || !payload.address) {
      err.textContent = 'Nama, No. HP, dan Alamat wajib diisi.';
      err.style.display = 'block'; return;
    }
  }

  if (!CART_CONFIG.wholesaleOrder && !CART_CONFIG.adminPbfOrder && !payload.payment_method) {
    err.textContent = 'Metode pembayaran wajib diisi.'; err.style.display = 'block'; return;
  }

  if (payload.payment_method === 'Tempo') {
    const allowTempo = ['apotik', 'dokter'].includes(jenis);
    if (!allowTempo) {
      err.textContent = 'Metode Tempo hanya tersedia untuk pembeli Apotik dan Dokter.'; err.style.display = 'block'; return;
    }
    if (!Number.isInteger(payload.payment_term_days) || payload.payment_term_days < 1) {
      err.textContent = 'Jumlah hari tempo wajib diisi untuk metode Tempo.'; err.style.display = 'block'; return;
    }
  }

  try {
    const response = await fetch('{{ route("orders.history.store") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    });
    if (!response.ok) { const e = await response.json(); throw (e.errors || new Error('Gagal menyimpan pesanan.')); }
    const result = await response.json();
    window.orderPayload = { ...payload, id: result.id, cartItems: cart };
    document.getElementById('invoiceLink').href = CART_CONFIG.adminPurchaseOrderBase
      ? CART_CONFIG.adminPurchaseOrderBase.replace('__ORDER__', result.id)
      : result.invoice_url;
    if (CART_CONFIG.adminPbfOrder) {
      document.getElementById('orderSuccessMessage').textContent = 'Surat pesanan berhasil dibuat. Pilih tindakan selanjutnya:';
      document.getElementById('invoiceLink').innerHTML = '<i class="fa-solid fa-file-invoice"></i> Lihat Invoice';
      document.getElementById('whatsappOrderButton').style.display = 'none';
      cart = [];
      save();
      render();
    }
    document.getElementById('orderFormPanel').style.display = 'none';
    document.getElementById('nextActionPanel').style.display = 'block';
  } catch (error) {
    let msg = 'Terjadi kesalahan yang tidak diketahui.';
    if (error && error.message) msg = error.message;
    if (typeof error === 'object' && !error.message) msg = Object.values(error).map(e => e.join(', ')).join('<br>');
    err.innerHTML = msg; err.style.display = 'block';
  }
}

function downloadReceiptAndClose() {
  const pdfUrl = buildReceiptPdf(window.orderPayload || {});
  const a = document.createElement('a');
  a.href = pdfUrl;
  a.download = `${CART_CONFIG.receiptFilePrefix}-${window.orderPayload?.id || Date.now()}.pdf`;
  document.body.appendChild(a); a.click(); document.body.removeChild(a);
  setTimeout(() => URL.revokeObjectURL(pdfUrl), 100);
}

function openWhatsAppOrder() {
  let total = 0;
  let msg = '*Halo Apotek Medix, saya ingin memesan:*\n';
  cart.forEach((it, i) => {
    const sub = it.price * it.qty;
    const disc = it.discount || 0;
    const afterDisc = sub - disc;
    total += afterDisc;
    const unit = it.saleUnit || 'box';
    const unitLabel = it.unitLabel || it.unitLabels?.[unit] || unit.charAt(0).toUpperCase() + unit.slice(1);
    msg += `${i+1}. *${it.name}*\n   Qty: ${it.qty} ${unitLabel} x ${rp(it.price)} = ${rp(sub)}\n`;
    if (disc > 0) msg += `   Potongan: -${rp(disc)} → *${rp(afterDisc)}*\n`;
    if (it.note) msg += `   Catatan: ${it.note}\n`;
  });
  msg += `---\n*Total: ${rp(total)}*\n\n*Data Pemesanan:*\n`;
  if (CART_CONFIG.adminPbfOrder) {
    msg += `- Pengirim: Apotek Medix\n- Nama PBF: ${window.orderPayload?.pbf_name || '-'}\n- Alamat PBF: ${window.orderPayload?.pbf_address || '-'}\n`;
  } else if (window.orderPayload?.buyer_type === 'apotik' || window.orderPayload?.buyer_type === 'dokter') {
    msg += `- Nama: ${window.orderPayload.buyer_name}\n- Nama Pemesan: ${window.orderPayload.requester_name}\n- Nama Outlet: ${window.orderPayload.outlet_name}\n- HP/WA: ${window.orderPayload.phone}\n- Alamat: ${window.orderPayload.address}\n`;
  } else {
    msg += `- Nama: ${window.orderPayload?.buyer_name}\n- HP/WA: ${window.orderPayload?.phone}\n- Alamat: ${window.orderPayload?.address}\n`;
  }
  if (window.orderPayload?.payment_method) {
    const paymentLabel = window.orderPayload.payment_method === 'Tempo' && window.orderPayload.payment_term_days
      ? `Tempo (${window.orderPayload.payment_term_days} hari)`
      : window.orderPayload.payment_method;
    msg += `*Metode Pembayaran: ${paymentLabel}*\n`;
  }
  msg += '\nTerima kasih';
  window.open(`https://wa.me/${WA}?text=${encodeURIComponent(msg)}`, '_blank');
}

document.addEventListener('DOMContentLoaded', () => {
  syncBadge();
  toggleBuyerType();
  const supplierPicker = document.getElementById('pbfSupplierPicker');
  const supplierMenu = document.getElementById('pbfSupplierMenu');
  const supplierToggle = document.getElementById('pbfSupplierToggle');
  const supplierName = document.getElementById('pbfOrderDistributor');
  const supplierAddress = document.getElementById('pbfOrderAddress');
  const supplierPhone = document.getElementById('pbfOrderPhone');
  const suppliers = Array.isArray(CART_CONFIG.suppliers) ? CART_CONFIG.suppliers : [];
  const orderProfile = document.getElementById('pbfOrderProfile');
  const signerHeading = document.getElementById('pbfSignerHeading');
  if (orderProfile && signerHeading) {
    orderProfile.addEventListener('change', () => {
      signerHeading.textContent = orderProfile.value === 'medikpedia'
        ? 'Penanggung jawab Apotek Medikpedia'
        : 'Penanggung jawab Apotek Medix';
    });
  }
  if (supplierPicker && supplierMenu && supplierToggle && supplierName && supplierAddress && supplierPhone) {
    let activeSupplierIndex = -1;
    let autoFilledAddress = '';
    let autoFilledPhone = '';
    if (CART_CONFIG.selectedSupplier) {
      supplierName.value = CART_CONFIG.selectedSupplier.name;
      supplierAddress.value = CART_CONFIG.selectedSupplier.address || '';
      supplierPhone.value = CART_CONFIG.selectedSupplier.phone || '';
      supplierName.readOnly = true;
      supplierAddress.readOnly = true;
      supplierPhone.readOnly = true;
      autoFilledAddress = supplierAddress.value;
      autoFilledPhone = supplierPhone.value;
      supplierToggle.hidden = true;
    }

    const closeSupplierMenu = () => {
      supplierPicker.classList.remove('is-open');
      supplierName.setAttribute('aria-expanded', 'false');
      supplierToggle.setAttribute('aria-expanded', 'false');
      activeSupplierIndex = -1;
    };

    const renderSupplierOptions = () => {
      const query = supplierName.value.trim().toLocaleLowerCase();
      const matches = suppliers.filter(supplier =>
        `${supplier.name} ${supplier.address} ${supplier.phone || ''}`.toLocaleLowerCase().includes(query)
      );
      supplierMenu.innerHTML = matches.length
        ? matches.map((supplier, index) => `
          <button class="pbf-supplier-option${index === activeSupplierIndex ? ' is-active' : ''}" type="button" role="option" aria-selected="${supplier.name.toLocaleLowerCase() === query}">
            <span class="pbf-supplier-option-name">${escapeHtml(supplier.name)}</span>
            <span class="pbf-supplier-option-address">${escapeHtml(supplier.address)}</span>
            ${supplier.phone ? `<span class="pbf-supplier-option-address">${escapeHtml(supplier.phone)}</span>` : ''}
          </button>
        `).join('')
        : '<div class="pbf-supplier-empty">Supplier tidak ditemukan. Nama yang diketik tetap bisa digunakan.</div>';

      supplierMenu.querySelectorAll('.pbf-supplier-option').forEach((option, index) => {
        option.addEventListener('click', () => {
          const selected = matches[index];
          supplierName.value = selected.name;
          supplierAddress.value = selected.address;
          supplierPhone.value = selected.phone || '';
          autoFilledAddress = selected.address;
          autoFilledPhone = selected.phone || '';
          closeSupplierMenu();
        });
      });
      return matches;
    };

    const openSupplierMenu = () => {
      supplierPicker.classList.add('is-open');
      supplierName.setAttribute('aria-expanded', 'true');
      supplierToggle.setAttribute('aria-expanded', 'true');
      renderSupplierOptions();
    };

    supplierName.addEventListener('input', () => {
      const selected = suppliers.find(supplier =>
        supplier.name.toLocaleLowerCase() === supplierName.value.trim().toLocaleLowerCase()
      );
      if (selected) {
        supplierAddress.value = selected.address;
        supplierPhone.value = selected.phone || '';
        autoFilledAddress = selected.address;
        autoFilledPhone = selected.phone || '';
      } else if (autoFilledAddress && supplierAddress.value === autoFilledAddress) {
        supplierAddress.value = '';
        autoFilledAddress = '';
      }
      if (!selected && autoFilledPhone && supplierPhone.value === autoFilledPhone) {
        supplierPhone.value = '';
        autoFilledPhone = '';
      }
      activeSupplierIndex = -1;
      openSupplierMenu();
    });

    supplierName.addEventListener('focus', openSupplierMenu);
    supplierName.addEventListener('keydown', event => {
      const options = supplierMenu.querySelectorAll('.pbf-supplier-option');
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        if (!supplierPicker.classList.contains('is-open')) openSupplierMenu();
        const direction = event.key === 'ArrowDown' ? 1 : -1;
        activeSupplierIndex = options.length
          ? (activeSupplierIndex + direction + options.length) % options.length
          : -1;
        renderSupplierOptions();
      } else if (event.key === 'Enter' && supplierPicker.classList.contains('is-open') && activeSupplierIndex >= 0) {
        event.preventDefault();
        options[activeSupplierIndex]?.click();
      } else if (event.key === 'Escape') {
        closeSupplierMenu();
      }
    });
    supplierToggle.addEventListener('click', () => {
      if (supplierPicker.classList.contains('is-open')) {
        closeSupplierMenu();
      } else {
        supplierName.focus();
        openSupplierMenu();
      }
    });
    document.addEventListener('click', event => {
      if (!supplierPicker.contains(event.target)) closeSupplierMenu();
    });
  }
  if (CART_CONFIG.wholesaleOrder) {
    const standardFields = document.getElementById('standardBuyerFields');
    if (standardFields) standardFields.style.display = 'none';
    document.getElementById('buyerFieldsUmum').style.display = 'none';
    document.getElementById('buyerFieldsApotik').style.display = '';
  }
  if (CART_CONFIG.adminPbfOrder) {
    const standardBuyerFields = document.getElementById('standardBuyerFields');
    if (standardBuyerFields) standardBuyerFields.style.display = 'none';
    document.querySelectorAll('.standard-buyer-fields').forEach((field) => field.style.display = 'none');
  }
  if (window.location.hash === '#keranjang') openCart();
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closePbfInvoice();
      closePbfCheckout();
    }
  });
  const navBtn = document.getElementById('cartNavBtn');
  if (navBtn) navBtn.onclick = () => openCart();
  // Load jsPDF
  const s = document.createElement('script');
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
  document.head.appendChild(s);
});
</script>
