{{--
  =====================================================================
  SHARED CART PARTIAL - resources/views/partials/cart.blade.php
  Include ini di @section('scripts') semua halaman yang butuh keranjang:
      @include('partials.cart')
  =====================================================================
--}}
@php
  $isKioskOrder = !empty($cartKioskOrder);
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
</style>

{{-- ===== CART DRAWER HTML ===== --}}
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-head">
    <h2><i class="fa-solid fa-cart-shopping"></i> Keranjang</h2>
    <button class="cart-close-btn" onclick="closeCart()"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="cart-body" id="cartBody">
    <div class="cart-empty-msg"><i class="fa-solid fa-cart-shopping"></i><p>Keranjang masih kosong</p></div>
  </div>
  <div class="cart-foot" id="cartFoot" style="display:none;">
    <div class="cart-total-row"><span>Total Pesanan</span><strong id="cartTotalAmt">Rp 0</strong></div>
    <button class="btn-order-wa" onclick="openOrder()">Lanjutkan Pesanan</button>
    <button class="btn-clear-cart" onclick="clearCart()">Kosongkan keranjang</button>
  </div>
</div>

{{-- ===== ORDER MODAL ===== --}}
<div class="modal-overlay" id="orderOverlay" onclick="closeOrder()"></div>
<div class="modal-box" id="orderModal">
  <div class="modal-head">
    <div><h3><i class="fa-solid {{ $isKioskOrder ? 'fa-store' : 'fa-file-invoice' }}"></i> {{ $isKioskOrder ? 'Pesanan Internal' : 'Form Pemesanan' }}</h3><p>{{ $isKioskOrder ? 'Tunjukkan struk pesanan kepada kasir untuk pembayaran.' : 'Lengkapi data untuk melanjutkan' }}</p></div>
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
    @if($isKioskOrder)
    <input type="hidden" id="f_jenis" value="umum">
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
    <div id="pbfDestinationFields" style="display:none;">
      <label class="form-lbl">Nama PBF <span style="color:#ef4444;">*</span></label>
      <input id="f_pbf_name" type="text" class="form-inp" placeholder="Nama PBF tujuan">
      <label class="form-lbl">Alamat PBF <span style="color:#ef4444;">*</span></label>
      <textarea id="f_pbf_address" rows="2" class="form-inp" style="resize:vertical;" placeholder="Alamat PBF tujuan"></textarea>
    </div>
    <div id="signatoryFields" style="display:none;">
      <label class="form-lbl">Nama Penandatangan <span style="color:#ef4444;">*</span></label>
      <input id="f_signer_name" type="text" class="form-inp" value="Apt. Hendri Setiyono, S Farm" placeholder="Nama apoteker">
      <label class="form-lbl">Jabatan <span style="color:#ef4444;">*</span></label>
      <input id="f_signer_title" type="text" class="form-inp" value="Apoteker" placeholder="Apoteker">
      <label class="form-lbl">No. SIA <span style="color:#ef4444;">*</span></label>
      <input id="f_signer_sia" type="text" class="form-inp" value="13042600890450001" placeholder="Nomor SIA">
      <label class="form-lbl">No. SIPA <span style="color:#ef4444;">*</span></label>
      <input id="f_signer_sipa" type="text" class="form-inp" value="1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026" placeholder="Nomor SIPA">
    </div>
    <div id="buyerFieldsUmum" class="standard-buyer-fields" @if(!empty($cartWholesaleOrder)) style="display:none;" @elseif($isKioskOrder) style="display:block;" @endif>
      <label class="form-lbl">Nama Pemesan <span style="color:#ef4444;">*</span></label>
      <input id="f_nama" type="text" class="form-inp" placeholder="Nama lengkap" value="{{ $defaultShippingAddress?->name }}">
      <label class="form-lbl">No. HP / WA <span style="color:#ef4444;">*</span></label>
      <input id="f_hp" type="tel" class="form-inp" placeholder="08xxxxxxxxxx" value="{{ $defaultShippingAddress?->phone }}">
      @unless($isKioskOrder)
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
      @endunless
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
    @if(empty($cartWholesaleOrder) && !$isKioskOrder)
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
    @if($isKioskOrder)
      <p style="padding:.75rem;background:#ecfeff;border-radius:.6rem;color:#155e75;font-size:.85rem;">
        <i class="fa-solid fa-circle-info"></i> Pembayaran tunai dilakukan di kasir. Pesanan akan disimpan dan stok diperbarui setelah dibuat.
      </p>
    @endif
    <div class="form-error" id="formErr"></div>
    <button class="btn-submit-wa" onclick="submitOrder()">
      <i class="fa-solid {{ $isKioskOrder ? 'fa-receipt' : 'fa-paper-plane' }}"></i> {{ $isKioskOrder ? 'Buat Struk Pesanan' : 'Lanjutkan' }}
    </button>
  </div>
  <div class="modal-form" id="nextActionPanel" style="display:none;">
    <p id="orderSuccessMessage" style="color:#374151;font-size:0.9rem;margin-bottom:1rem;">Pesanan berhasil dicatat! Pilih tindakan selanjutnya:</p>
    <a id="invoiceLink" href="#" target="_blank" rel="noopener" style="width:100%;padding:0.75rem;background:#e8f5e9;color:#187b35;border:1.5px solid #86d39a;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;margin-bottom:0.75rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;text-decoration:none;">
      <i class="fa-solid fa-file-invoice"></i> Lihat Invoice
    </a>
    <button id="whatsappOrderButton" onclick="openWhatsAppOrder()" style="width:100%;padding:0.75rem;background:#25D366;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;">
      <i class="fa-brands fa-whatsapp" style="font-size:1.2rem;"></i> Chat WhatsApp
    </button>
  </div>
</div>

{{-- ===== CART JAVASCRIPT ===== --}}
<script>
// Keranjang disimpan per konteks halaman melalui CART_CONFIG.storageKey.
const WA = '6285882717844';
const CART_DEFAULT_SETTINGS = {
  storageKey: 'apotekmedix_cart',
  receiptStoreName: 'APOTEK MEDIX',
  receiptStoreAddress: 'Jl. Raya Ciawi Prapatan No.256 C, RT.04/RW.04, Sindangsari, Kec. Bogor Timur, Kota Bogor, Jawa Barat 16146',
  receiptFilePrefix: 'struk-apotekmedix'
};
const CART_CONFIG = Object.assign({}, CART_DEFAULT_SETTINGS, window.cartSettings || {});
let cart = JSON.parse(localStorage.getItem(CART_CONFIG.storageKey) || '[]');
if (CART_CONFIG.kioskOrder) cart = cart.map(item => ({ ...item, discount: 0 }));

function save() {
  localStorage.setItem(CART_CONFIG.storageKey, JSON.stringify(cart));
  syncBadge();
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
  const selectedCard = button.closest('.product-unit-card');
  const unitSection = selectedCard?.closest('.product-unit-section');
  if (!selectedCard || !unitSection) return;

  unitSection.querySelectorAll('.product-unit-card').forEach(card => {
    const selected = card === selectedCard;
    card.classList.toggle('is-selected', selected);
    card.querySelector('.product-unit-choice')?.setAttribute('aria-pressed', String(selected));
    const quantityInput = card.querySelector('[data-cart-quantity]');
    if (!quantityInput) return;
    if (!selected) quantityInput.value = '0';
    card.querySelectorAll('.product-unit-quantity button').forEach(control => {
      control.disabled = !selected || Number(quantityInput.max) < 1;
    });
    quantityInput.disabled = !selected || Number(quantityInput.max) < 1;
  });

  const unitSelect = selectedCard.closest('.medicine-card')?.querySelector('.product-unit-data');
  if (unitSelect) unitSelect.value = selectedCard.dataset.cartUnit;
}

function updateProductCardQty(input, adjustment = 0) {
  const max = Math.max(0, Number(input.max) || 0);
  const current = parseInt(input.value, 10) || 0;
  input.value = Math.min(max, Math.max(0, current + adjustment));
  input.closest('.product-unit-card')?.classList.toggle('has-quantity', Number(input.value) > 0);
}

function adjustProductCardQty(button, adjustment) {
  const input = button.closest('.product-unit-card')?.querySelector('input[data-cart-quantity]');
  if (input && !input.disabled) updateProductCardQty(input, adjustment);
}

function addToCart(id, name, price, img, brand, btn, unitSelectId = null) {
  const unitSelect = unitSelectId ? document.getElementById(unitSelectId) : null;
  const productCard = btn?.closest('.medicine-card');
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

    const addition = {
      unit: selectedCard.dataset.cartUnit,
      qty: parseInt(selectedCard.querySelector('[data-cart-quantity]')?.value, 10) || 0
    };
    if (!addition.qty) {
      alert('Pilih jumlah produk yang ingin dimasukkan ke keranjang.');
      return;
    }

    const unit = addition.unit;
    const unitLabel = unitLabels[unit] || unit.charAt(0).toUpperCase() + unit.slice(1);
    const factor = Number(unitFactors[unit] || 0);
    const availableQty = factor > 0 ? Math.floor(stockUnits / factor) : 0;
    if (!availableQty || addition.qty > availableQty) {
      alert('Stok untuk satuan ' + unitLabel + ' tidak mencukupi.');
      return;
    }

    const existing = cart.find(item => item.id === id);
    cart = cart.filter(item => item.id !== id);
    if (existing) {
      existing.name = name;
      existing.price = Number(unitPrices[unit] || 0);
      existing.qty = addition.qty;
      existing.brand = brand;
      existing.img = img;
      existing.discount = 0;
      existing.unitLabels = unitLabels;
      existing.unitLabel = unitLabel;
      existing.unitPrices = unitPrices;
      existing.unitFactors = unitFactors;
      existing.saleUnit = unit;
      existing.availableQty = availableQty;
      existing.stockUnits = stockUnits;
      cart.push(existing);
    } else {
      cart.push({
        id, name, price: Number(unitPrices[unit] || 0), img, brand: brand || '',
        qty: addition.qty, note: '', discount: 0, saleUnit: unit, unitLabel,
        unitLabels, unitPrices, unitFactors, availableQty, stockUnits
      });
    }

    save();
    render();
    openCart();
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
    ex.unitLabels = unitLabels;
    ex.unitLabel = unitLabel;
  } else cart.push({
    id, name, price: unitPrice, img, brand: brand || '', qty: 1, note: '', discount: 0,
    saleUnit, unitLabel, unitLabels, unitPrices, unitFactors, availableQty,
    stockUnits: unitSelect ? Number(unitSelect.dataset.stockUnits || 0) : null
  });
  console.log('Cart item:', cart.find(i => i.id === id && (i.saleUnit || 'box') === saleUnit));
  save(); render();
  openCart();
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
  if (CART_CONFIG.kioskOrder) return;
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
    const key = `${it.id}-${unit}`;
    total += (it.price * it.qty) - (it.discount || 0);
    const imgHtml = it.img
      ? `<img src="${it.img}" alt="">`
      : '<i class="fa-solid fa-pills" style="color:#90caf9;font-size:1.4rem;"></i>';
    html += `<div class="cart-item-row">
      <div class="cart-item-thumb">${imgHtml}</div>
      <div class="cart-item-info">
        <div class="cart-item-name">${it.name}</div>
          <div class="cart-item-price">${rp(it.price)} / ${unitLabel}</div>
          ${it.unitPrices ? `<select onchange="changeCartUnit(${it.id}, '${unit}', this.value)" style="width:100%;margin-top:.4rem;padding:.4rem;border:1px solid #cbd5e1;border-radius:8px;">
            ${Object.entries(it.unitPrices).map(([value, unitPrice]) => `<option value="${value}" ${value === unit ? 'selected' : ''}>${it.unitLabels?.[value] || value.charAt(0).toUpperCase() + value.slice(1)} · ${rp(unitPrice)}</option>`).join('')}
          </select>` : ''}
          <div class="cart-qty-row">
            <button class="qty-btn" onclick="changeQty(${it.id},-1,'${unit}')">−</button>
            <input type="number" class="qty-num" min="1" ${it.availableQty != null ? `max="${it.availableQty}"` : ''} value="${it.qty}" onchange="setQty(${it.id}, this.value,'${unit}')"
              style="width:4rem;text-align:center;border:1px solid #d1d5db;border-radius:8px;padding:0.4rem;" />
            <button class="qty-btn" onclick="changeQty(${it.id},1,'${unit}')">+</button>
          </div>
          <textarea class="cart-note" placeholder="Catatan produk..." onchange="setNote(${it.id}, this.value,'${unit}')"
          style="width:100%;margin-top:0.75rem;padding:0.65rem 0.85rem;border:1px solid #e5e7eb;border-radius:12px;font-size:0.9rem;min-height:72px;resize:vertical;">${it.note || ''}</textarea>
        ${CART_CONFIG.kioskOrder ? '' : `<div style="margin-top:.6rem;">
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
        </div>`}
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
  document.getElementById('cartDrawer').classList.add('open');
  document.getElementById('cartOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
  render();
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
  let html = '', total = 0;
  cart.forEach((it, i) => {
    const sub = it.price * it.qty;
    const disc = it.discount || 0;
    const afterDisc = sub - disc;
    total += afterDisc;
    const unit = it.saleUnit || 'box';
    const unitLabel = it.unitLabel || it.unitLabels?.[unit] || unit.charAt(0).toUpperCase() + unit.slice(1);
    html += `<div style="padding:0.35rem 0;border-bottom:1px solid #f3f4f6;font-size:0.83rem;">
      <div style="display:flex;justify-content:space-between;gap:0.75rem;">
        <span>${i+1}. ${it.name} <span style="color:#9ca3af;">&times;${it.qty} ${unitLabel}</span></span>
        <span style="font-weight:700;color:#1E88E5;">${disc > 0 ? `<span style="text-decoration:line-through;color:#9ca3af;font-size:.78rem;">${rp(sub)}</span> ${rp(afterDisc)}` : rp(sub)}</span>
      </div>`;
    if (disc > 0) html += `<div style="color:#ef4444;font-size:.78rem;">Potongan: -${rp(disc)}</div>`;
    if (it.note)  html += `<div style="margin-top:0.35rem;color:#6b7280;font-size:0.8rem;">Catatan: ${escapeHtml(it.note)}</div>`;
    html += `</div>`;
  });
  document.getElementById('orderSummary').innerHTML = html;
  document.getElementById('orderTotal').textContent = rp(total);
  document.getElementById('orderFormPanel').style.display = '';
  document.getElementById('nextActionPanel').style.display = 'none';
  document.getElementById('formErr').style.display = 'none';
  document.getElementById('orderOverlay').style.display = 'block';
  document.getElementById('orderModal').style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function closeOrder() {
  document.getElementById('orderOverlay').style.display = 'none';
  document.getElementById('orderModal').style.display = 'none';
  document.body.style.overflow = '';
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

async function submitOrder() {
  const jenis = CART_CONFIG.adminPbfOrder || CART_CONFIG.wholesaleOrder ? 'apotik' : (document.getElementById('f_jenis')?.value || 'umum');
  const err = document.getElementById('formErr');
  err.style.display = 'none';

  const originalTotal = cart.reduce((s, it) => s + (it.price * it.qty), 0);
  const discountedTotal = cart.reduce((s, it) => s + (it.price * it.qty) - (it.discount || 0), 0);
  const paymentMethod = CART_CONFIG.kioskOrder ? 'Tunai' : (document.getElementById('f_payment')?.value.trim() || '');
  const paymentTermDays = Number(document.getElementById('f_payment_term_days')?.value || 0);
  const payload = {
    buyer_type: jenis,
    items: cart.map(it => ({ id: it.id, nama_obat: it.name, quantity: it.qty, harga: it.price, sale_unit: it.saleUnit || 'box', catatan: it.note || '', potongan: it.discount || 0 })),
    total: discountedTotal,
    original_total: originalTotal,
    discounted_total: discountedTotal,
    approval_status: 'pending',
    payment_method: paymentMethod,
    payment_term_days: paymentMethod === 'Tempo' ? paymentTermDays : null,
    transaction_channel: CART_CONFIG.kioskOrder ? 'offline' : 'online',
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
    if (CART_CONFIG.kioskOrder) {
      payload.address = '';
    } else {
      const addressParts = [
        document.getElementById('f_alamat').value.trim(),
        document.getElementById('f_apartemen').value.trim(),
        document.getElementById('f_kota').value.trim(),
        document.getElementById('f_provinsi').value.trim(),
        document.getElementById('f_kode_pos').value.trim(),
      ].filter(Boolean);
      payload.address = addressParts.join(', ');
      payload.kota = document.getElementById('f_kota').value.trim();
    }
    if (!payload.buyer_name || !payload.phone || (!CART_CONFIG.kioskOrder && !payload.address)) {
      err.textContent = CART_CONFIG.kioskOrder
        ? 'Nama dan nomor telepon wajib diisi.'
        : 'Nama, No. HP, dan Alamat wajib diisi.';
      err.style.display = 'block'; return;
    }
  }

  if (!CART_CONFIG.wholesaleOrder && !payload.payment_method) {
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
    if (CART_CONFIG.kioskOrder) {
      document.getElementById('orderSuccessMessage').textContent = 'Struk pesanan siap. Tunjukkan kepada kasir untuk membayar dan menyelesaikan transaksi.';
      document.getElementById('invoiceLink').innerHTML = '<i class="fa-solid fa-receipt"></i> Buka / Cetak Struk';
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
  if (CART_CONFIG.wholesaleOrder) {
    const standardFields = document.getElementById('standardBuyerFields');
    if (standardFields) standardFields.style.display = 'none';
    document.getElementById('buyerFieldsUmum').style.display = 'none';
    document.getElementById('buyerFieldsApotik').style.display = '';
  }
  if (CART_CONFIG.adminPbfOrder) {
    document.getElementById('standardBuyerFields').style.display = 'none';
    document.querySelectorAll('.standard-buyer-fields').forEach((field) => field.style.display = 'none');
  }
  if (window.location.hash === '#keranjang') openCart();
  const navBtn = document.getElementById('cartNavBtn');
  if (navBtn) navBtn.onclick = () => openCart();
  // Load jsPDF
  const s = document.createElement('script');
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
  document.head.appendChild(s);
});
</script>
