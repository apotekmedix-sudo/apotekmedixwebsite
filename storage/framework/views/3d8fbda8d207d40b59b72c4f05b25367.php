<style>
  .red-rx-checkout-dialog {
    position: fixed;
    top: 50%;
    left: 50%;
    margin: 0;
    transform: translate(-50%, -50%);
    width: min(560px, calc(100% - 2rem));
    max-height: min(85vh, 760px);
    overflow-y: auto;
    padding: 0;
    border: 0;
    border-radius: 18px;
    color: #1f2937;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .32);
  }
  .red-rx-checkout-dialog::backdrop { background: rgba(15, 23, 42, .68); }
  .red-rx-checkout-content { padding: 1.4rem; }
  .red-rx-checkout-content h2 { display: flex; align-items: flex-start; gap: .55rem; margin: 0 0 .45rem; color: #991b1b; font-size: 1.2rem; }
  .red-rx-checkout-content h2 i { flex: 0 0 auto; margin-top: .15rem; }
  .red-rx-checkout-content > p { margin: 0 0 1rem; color: #4b5563; line-height: 1.55; }
  .red-rx-checkout-products { display: grid; gap: .6rem; max-height: 32vh; overflow-y: auto; margin-bottom: 1rem; }
  .red-rx-checkout-product { display: flex; align-items: center; gap: .75rem; padding: .6rem; border: 1px solid #e5e7eb; border-radius: 12px; }
  .red-rx-checkout-product img { width: 52px; height: 52px; object-fit: contain; border-radius: 8px; background: #f8fafc; }
  .red-rx-checkout-product strong { color: #1e293b !important; opacity: 1 !important; font-size: .9rem; font-weight: 700; }
  .red-rx-checkout-error { display: none; margin: 0 0 .8rem; color: #b91c1c; font-size: .85rem; }
  .red-rx-checkout-actions { display: grid; grid-template-columns: 1fr 1fr; gap: .55rem; }
  .red-rx-checkout-actions button, .red-rx-checkout-chat {
    display: flex; justify-content: center; align-items: center; gap: .45rem; min-height: 44px;
    padding: .65rem .8rem; border: 0; border-radius: 10px; font-weight: 700; text-decoration: none; cursor: pointer;
  }
  .red-rx-checkout-yes { background: #15803d; color: #fff; }
  .red-rx-checkout-no { background: #f1f5f9; color: #334155; }
  .red-rx-checkout-chat { margin-top: .55rem; background: #16a34a; color: #fff; }
  .red-rx-checkout-close { display: block; margin: .7rem auto 0; padding: .35rem .7rem; border: 0; background: transparent; color: #64748b; cursor: pointer; }
  @media (max-width: 420px) { .red-rx-checkout-actions { grid-template-columns: 1fr; } }
</style>

<dialog class="red-rx-checkout-dialog" id="redRxCheckoutDialog" aria-labelledby="redRxCheckoutTitle">
  <div class="red-rx-checkout-content">
    <h2 id="redRxCheckoutTitle"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true" style="color:#b91c1c !important"></i><span style="color:#7f1d1d !important;-webkit-text-fill-color:#7f1d1d !important;opacity:1 !important">Produk yang Anda pilih memerlukan resep dokter</span></h2>
    <p>Pastikan Anda memiliki resep dokter yang sah untuk produk berikut sebelum melanjutkan ke checkout.</p>
    <div class="red-rx-checkout-products" id="redRxCheckoutProducts"></div>
    <p class="red-rx-checkout-error" id="redRxCheckoutError" role="alert"></p>
    <div class="red-rx-checkout-actions">
      <button class="red-rx-checkout-yes" id="redRxCheckoutYes" type="button" onclick="confirmRedLogoPrescription()">
        <i class="fa-solid fa-check"></i> Ada, lanjut checkout
      </button>
      <button class="red-rx-checkout-no" type="button" onclick="removeRedLogoProductsFromCart()">
        <i class="fa-solid fa-trash"></i> Tidak, hapus produk
      </button>
    </div>
    <a class="red-rx-checkout-chat" id="redRxCheckoutChat" href="<?php echo e(route('consultations.intake')); ?>">
      <i class="fa-solid fa-user-doctor"></i> Chat Dokter
    </a>
    <button class="red-rx-checkout-close" type="button" onclick="document.getElementById('redRxCheckoutDialog').close()">Tutup</button>
  </div>
</dialog>

<script>
(() => {
  let redRxCheckoutContext = null;
  const dialog = document.getElementById('redRxCheckoutDialog');
  if (!dialog) return;

  window.confirmRedLogoCheckout = (items, context) => {
    if (!context.b2cFullAccess || context.priceChannel !== 'b2c' || context.wholesaleOrder) return false;
    const catalog = context.catalog || {};
    const redItems = items.filter(item => item.requiresPrescription === true
      || catalog[item.id]?.has_red_logo === true
      || catalog[String(item.id)]?.has_red_logo === true);
    if (!redItems.length) return false;

    redRxCheckoutContext = { items, redItems, storageKey: context.storageKey };
    const products = document.getElementById('redRxCheckoutProducts');
    products.innerHTML = redItems.map(item => {
      const catalogItem = catalog[item.id] || catalog[String(item.id)] || {};
      const image = item.img || catalogItem.image_url || '';
      return `<div class="red-rx-checkout-product" style="background:#fff;color:#1e293b">
        <img src="${escapeHtml(image)}" alt="${escapeHtml(item.name || catalogItem.nama_obat || 'Produk')}" onerror="this.style.visibility='hidden'">
        <strong style="color:#1e293b !important;-webkit-text-fill-color:#1e293b !important;opacity:1 !important;font-size:.9rem;font-weight:700">${escapeHtml(item.name || catalogItem.nama_obat || 'Produk')}</strong>
      </div>`;
    }).join('');

    document.getElementById('redRxCheckoutError').style.display = 'none';
    dialog.showModal();
    return true;
  };

  window.confirmRedLogoPrescription = async () => {
    if (!redRxCheckoutContext) return;
    const button = document.getElementById('redRxCheckoutYes');
    const error = document.getElementById('redRxCheckoutError');
    button.disabled = true;
    error.style.display = 'none';
    try {
      const response = await fetch(<?php echo json_encode(route('checkout.confirm-prescription'), 15, 512) ?>, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': <?php echo json_encode(csrf_token(), 15, 512) ?>,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          product_ids: [...new Set(redRxCheckoutContext.redItems.map(item => Number(item.id)))]
        })
      });
      const result = await response.json();
      if (!response.ok) throw new Error(result.message || 'Konfirmasi resep tidak dapat disimpan.');
      window.location.assign(result.checkout_url);
    } catch (exception) {
      error.textContent = exception.message;
      error.style.display = 'block';
      button.disabled = false;
    }
  };

  window.removeRedLogoProductsFromCart = () => {
    if (!redRxCheckoutContext) return;
    const redIds = new Set(redRxCheckoutContext.redItems.map(item => String(item.id)));
    const remaining = redRxCheckoutContext.items.filter(item => !redIds.has(String(item.id)));
    localStorage.setItem(redRxCheckoutContext.storageKey, JSON.stringify(remaining));
    dialog.close();
    window.location.reload();
  };
})();
</script>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/partials/red-logo-checkout-confirmation.blade.php ENDPATH**/ ?>