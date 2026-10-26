

<?php $__env->startSection('title', 'Profil Pelanggan - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .account-page { width:min(100% - 2rem, 1120px); margin:2.5rem auto 4rem; color:#1e293b; }
    .account-layout { display:grid; grid-template-columns:210px minmax(0,1fr); gap:clamp(1.5rem,5vw,4.5rem); align-items:start; }
    .account-nav { position:sticky; top:110px; display:grid; gap:.35rem; }
    .account-nav a { padding:.8rem .9rem; border-radius:.65rem; color:#64748b; font-weight:700; text-decoration:none; }
    .account-nav a:hover,.account-nav a.active { background:#ecfeff; color:#0e7490; }
    .account-main { min-width:0; }
    .account-heading { margin:0 0 1.6rem; }
    .account-heading h1 { margin:0 0 .35rem; color:#0f172a; font-size:1.8rem; }
    .account-heading p { margin:0; color:#64748b; }
    .account-section { margin:0 0 2rem; }
    .account-section-heading { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:.75rem; }
    .account-section-heading h2 { margin:0; font-size:1.08rem; color:#0f172a; }
    .account-card { padding:1.1rem 1.25rem; border:1px solid #e2e8f0; border-radius:1rem; background:#fff; box-shadow:0 8px 24px rgba(15,23,42,.06); }
    .account-contact-row { display:grid; grid-template-columns:130px minmax(0,1fr); gap:1rem; padding:.45rem 0; }
    .account-label { color:#64748b; }
    .account-value { overflow-wrap:anywhere; text-align:right; font-weight:600; }
    .account-address { display:grid; grid-template-columns:50px minmax(0,1fr) 24px; gap:.9rem; align-items:center; }
    .account-address-icon { width:50px;height:50px;display:grid;place-items:center;border-radius:.8rem;background:#f1f5f9;color:#0e7490;font-size:1.15rem; }
    .account-address-copy { min-width:0; line-height:1.55; }
    .account-address-name { display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.15rem;font-weight:800;color:#0f172a; }
    .account-default-tag { padding:.1rem .45rem;border-radius:999px;background:#cffafe;color:#0e7490;font-size:.7rem; }
    .account-address-detail { color:#475569;overflow-wrap:anywhere; }
    .account-arrow { color:#94a3b8; }
    .account-action { padding:.5rem .85rem;border:1px solid #cbd5e1;border-radius:.65rem;background:#fff;color:#0e7490;font:inherit;font-weight:700;text-decoration:none;cursor:pointer; }
    .account-action:hover { border-color:#0891b2;background:#ecfeff; }
    .account-form-panel { display:none;margin-top:.9rem; }
    .account-form-panel.open { display:block; }
    .account-profile-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.9rem; }
    .account-field { min-width:0; }
    .account-field-full { grid-column:1/-1; }
    .account-field label { display:block;margin-bottom:.35rem;color:#334155;font-size:.88rem;font-weight:700; }
    .account-field input,.account-field textarea { width:100%;padding:.72rem .8rem;border:1px solid #cbd5e1;border-radius:.55rem;font:inherit;color:#1e293b; }
    .account-field textarea { min-height:88px;resize:vertical; }
    .account-field input:focus,.account-field textarea:focus,.location-search:focus { outline:2px solid #a5f3fc;border-color:#0891b2; }
    .account-help { display:block;margin-top:.35rem;color:#64748b;font-size:.76rem; }
    .account-default-check { display:flex;align-items:flex-start;gap:.55rem;margin:1rem 0;color:#334155; }
    .account-default-check input { margin-top:.18rem;accent-color:#0e7490; }
    .account-submit { padding:.75rem 1rem;border:0;border-radius:.55rem;background:#0f172a;color:#fff;font:inherit;font-weight:700;cursor:pointer; }
    .account-submit:hover { background:#164e63; }
    .account-logout { margin-top:1.2rem; }
    .account-error { margin:.3rem 0 0;color:#b91c1c;font-size:.82rem; }
    .location-search-wrap { position:relative; }
    .location-search { width:100%;padding:.72rem .8rem;border:1px solid #cbd5e1;border-radius:.55rem;font:inherit; }
    .location-results { position:absolute;z-index:10;top:calc(100% + .3rem);left:0;right:0;display:none;max-height:240px;overflow:auto;border:1px solid #cbd5e1;border-radius:.6rem;background:white;box-shadow:0 12px 26px rgba(15,23,42,.14); }
    .location-result { display:block;width:100%;padding:.7rem .8rem;border:0;border-bottom:1px solid #f1f5f9;background:white;color:#334155;text-align:left;font:inherit;cursor:pointer; }
    .location-result:hover { background:#ecfeff; }
    .location-status { margin:.4rem 0 0;color:#64748b;font-size:.78rem; }
    .location-attribution { margin:.65rem 0 0;color:#64748b;font-size:.72rem; }
    .order-list { display:grid;gap:.75rem; }
    .order-card { display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:.9rem 1rem;border:1px solid #e2e8f0;border-radius:.7rem; }
    .order-meta { color:#64748b;font-size:.82rem; }
    .order-total { margin-top:.2rem;font-weight:800;color:#0f172a; }
    .order-status { display:inline-flex;margin-top:.35rem;padding:.18rem .55rem;border-radius:999px;background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:700; }
    .order-invoice { color:#0e7490;font-weight:700;text-decoration:none;white-space:nowrap; }
    .account-empty { color:#64748b;text-align:center;padding:1.5rem; }
    @media(max-width:700px) {
        .account-layout { grid-template-columns:1fr;gap:1rem; }
        .account-nav { position:static;display:flex;overflow:auto;border-bottom:1px solid #e2e8f0; }
        .account-nav a { flex:0 0 auto; }
        .account-contact-row { grid-template-columns:90px minmax(0,1fr); }
    }
    @media(max-width:520px) {
        .account-page { margin-top:1.25rem; }
        .account-card { padding:1rem; }
        .account-profile-grid { grid-template-columns:1fr; }
        .account-field-full { grid-column:auto; }
        .order-card { align-items:flex-start; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $addressSaved = filled($customer->address);
    $showForms = $errors->any() || !$addressSaved;
    $addressLine = collect([$customer->address, $customer->apartment, $customer->city, $customer->province, $customer->postal_code, 'Indonesia'])->filter()->implode(', ');
?>
<div class="account-page">
    <div class="account-layout">
        <nav class="account-nav" aria-label="Navigasi akun">
            <a href="#orders" class="<?php echo e(request('section') === 'orders' ? 'active' : ''); ?>">Pesanan</a>
            <a href="#profile" class="<?php echo e(request('section') !== 'orders' ? 'active' : ''); ?>">Profil</a>
        </nav>

        <main class="account-main">
            <header class="account-heading" id="profile">
                <h1>Profil</h1>
                <p>Kelola informasi kontak dan alamat pengiriman akun Apotek Medix.</p>
            </header>

            <?php if(session('success')): ?>
                <div class="alert alert-success" role="status"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="alert alert-error" role="alert"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            <form id="customer-account-form" action="<?php echo e(route('customer.account.update')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <section class="account-section">
                    <div class="account-section-heading">
                        <h2>Kontak</h2>
                        <button type="button" class="account-action" data-toggle-panel="contact-form">Edit</button>
                    </div>
                    <div class="account-card">
                        <div class="account-contact-row">
                            <span class="account-label">Email</span>
                            <span class="account-value"><?php echo e(old('email', $customer->email)); ?></span>
                        </div>
                        <div class="account-contact-row">
                            <span class="account-label">Nomor telepon</span>
                            <span class="account-value"><?php echo e(old('phone', $customer->phone) ?: 'Belum ditambahkan'); ?></span>
                        </div>
                    </div>
                    <div id="contact-form" class="account-card account-form-panel <?php echo e($showForms ? 'open' : ''); ?>">
                        <div class="account-profile-grid">
                            <div class="account-field">
                                <label for="customer-name">Nama</label>
                                <input id="customer-name" name="name" value="<?php echo e(old('name', $customer->name)); ?>" autocomplete="name" required>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="account-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="account-field">
                                <label for="customer-email">Email</label>
                                <input id="customer-email" name="email" type="email" value="<?php echo e(old('email', $customer->email)); ?>" autocomplete="email" required>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="account-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="account-field account-field-full">
                                <label for="customer-phone">Nomor telepon</label>
                                <input id="customer-phone" name="phone" type="tel" value="<?php echo e(old('phone', $customer->phone)); ?>" autocomplete="tel" required>
                                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="account-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="account-section">
                    <div class="account-section-heading">
                        <h2>Alamat</h2>
                        <button type="button" class="account-action" data-toggle-panel="address-form"><?php echo e($addressSaved ? 'Edit' : 'Tambah'); ?></button>
                    </div>
                    <div class="account-card account-address">
                        <div class="account-address-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="account-address-copy">
                            <div class="account-address-name">
                                <?php echo e(old('name', $customer->name)); ?>

                                <?php if($customer->is_default_address): ?><span class="account-default-tag">Utama</span><?php endif; ?>
                            </div>
                            <div class="account-address-detail"><?php echo e($addressLine ?: 'Tambahkan alamat untuk mempermudah checkout.'); ?></div>
                        </div>
                        <i class="fa-solid fa-chevron-right account-arrow" aria-hidden="true"></i>
                    </div>

                    <div id="address-form" class="account-card account-form-panel <?php echo e($showForms ? 'open' : ''); ?>">
                        <div class="account-profile-grid">
                            <div class="account-field account-field-full">
                                <label for="location-search">Cari alamat / lokasi</label>
                                <div class="location-search-wrap">
                                    <input id="location-search" class="location-search" type="search" autocomplete="off" placeholder="Contoh: Jalan Sudirman, Jakarta">
                                    <div id="location-results" class="location-results" role="listbox"></div>
                                </div>
                                <p id="location-status" class="location-status" aria-live="polite"></p>
                                <p class="location-attribution">Pencarian lokasi menggunakan OpenStreetMap/Nominatim, bukan Google Maps. Data peta © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap contributors</a>. Anda juga dapat mengisi alamat secara manual.</p>
                            </div>
                            <div class="account-field account-field-full">
                                <label for="customer-address">Alamat lengkap</label>
                                <textarea id="customer-address" name="address" autocomplete="street-address" required><?php echo e(old('address', $customer->address)); ?></textarea>
                                <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="account-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="account-field">
                                <label for="customer-apartment">Apartemen / unit (opsional)</label>
                                <input id="customer-apartment" name="apartment" value="<?php echo e(old('apartment', $customer->apartment)); ?>" autocomplete="address-line2">
                            </div>
                            <div class="account-field">
                                <label for="customer-city">Kota / Kabupaten</label>
                                <input id="customer-city" name="city" value="<?php echo e(old('city', $customer->city)); ?>" autocomplete="address-level2" required>
                                <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="account-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="account-field">
                                <label for="customer-province">Provinsi</label>
                                <input id="customer-province" name="province" value="<?php echo e(old('province', $customer->province)); ?>" autocomplete="address-level1" required>
                                <?php $__errorArgs = ['province'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="account-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="account-field">
                                <label for="customer-postal-code">Kode pos</label>
                                <input id="customer-postal-code" name="postal_code" value="<?php echo e(old('postal_code', $customer->postal_code)); ?>" autocomplete="postal-code" required>
                                <?php $__errorArgs = ['postal_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="account-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <label class="account-default-check">
                            <input type="checkbox" name="is_default_address" value="1" <?php if(old('is_default_address', $customer->is_default_address)): echo 'checked'; endif; ?>>
                            <span>Ini alamat default saya untuk checkout</span>
                        </label>
                    </div>
                </section>

                <div id="account-save-actions" style="<?php echo e($showForms ? '' : 'display:none;'); ?>">
                    <button class="account-submit" type="submit">Simpan perubahan</button>
                </div>
            </form>

            <section class="account-section" id="orders">
                <div class="account-section-heading">
                    <h2>Pesanan</h2>
                    <a href="<?php echo e(route('products.index')); ?>" class="account-action">Belanja produk</a>
                </div>
                <div class="order-list">
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $invoiceUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('orders.invoice', now()->addDays(30), ['order' => $order->id]); ?>
                        <article class="order-card">
                            <div>
                                <strong>Pesanan #<?php echo e($order->id); ?></strong>
                                <div class="order-meta"><?php echo e($order->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s')); ?> WIB · <?php echo e($order->transaction_channel === 'offline' ? 'Pesanan toko' : 'Online'); ?></div>
                                <div class="order-total">Rp <?php echo e(number_format($order->effective_total, 0, ',', '.')); ?></div>
                                <span class="order-status"><?php echo e($order->transaction_channel === 'offline' ? ($order->payment_status === 'paid' ? 'Lunas' : 'Menunggu pembayaran kasir') : 'Pesanan online'); ?></span>
                            </div>
                            <a class="order-invoice" href="<?php echo e($invoiceUrl); ?>" target="_blank" rel="noopener">Lihat struk</a>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="account-card account-empty">Pesanan yang dibuat saat Anda masuk ke akun ini akan ditampilkan di sini.</div>
                    <?php endif; ?>
                </div>
                <?php if($orders->hasPages()): ?>
                    <div style="margin-top:1rem;"><?php echo e($orders->links()); ?></div>
                <?php endif; ?>
            </section>

            <form action="<?php echo e(route('customer.logout')); ?>" method="POST" class="account-logout">
                <?php echo csrf_field(); ?>
                <button class="account-action" type="submit">Keluar</button>
                <span style="margin-left:.75rem;color:#64748b;">Keluar dari akun Apotek Medix</span>
            </form>
        </main>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(() => {
    const saveActions = document.getElementById('account-save-actions');
    document.querySelectorAll('[data-toggle-panel]').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.account-form-panel').forEach(panel => panel.classList.add('open'));
            if (saveActions) saveActions.style.display = 'block';
        });
    });

    const search = document.getElementById('location-search');
    const results = document.getElementById('location-results');
    const status = document.getElementById('location-status');
    if (!search || !results || !status) return;

    let debounceTimer;
    let requestController;
    search.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        if (requestController) requestController.abort();
        results.replaceChildren();
        results.style.display = 'none';

        const query = search.value.trim();
        if (query.length < 3) {
            status.textContent = 'Ketik minimal 3 karakter untuk mencari lokasi.';
            return;
        }

        status.textContent = 'Mencari lokasi...';
        debounceTimer = setTimeout(async () => {
            requestController = new AbortController();
            try {
                const response = await fetch(`<?php echo e(route('customer.locations.search')); ?>?q=${encodeURIComponent(query)}`, {
                    headers: { 'Accept': 'application/json' },
                    signal: requestController.signal
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Pencarian lokasi gagal.');
                if (search.value.trim() !== query) return;
                results.replaceChildren();
                if (!data.locations.length) {
                    status.textContent = 'Lokasi tidak ditemukan. Silakan lengkapi alamat secara manual.';
                    return;
                }

                data.locations.forEach(location => {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.className = 'location-result';
                    option.textContent = location.label;
                    option.addEventListener('click', () => {
                        document.getElementById('customer-address').value = location.address || '';
                        document.getElementById('customer-city').value = location.city || '';
                        document.getElementById('customer-province').value = location.province || '';
                        document.getElementById('customer-postal-code').value = location.postal_code || '';
                        search.value = location.label;
                        results.style.display = 'none';
                        status.textContent = 'Lokasi dipilih. Periksa dan lengkapi nomor rumah, unit, serta detail alamat sebelum menyimpan.';
                    });
                    results.append(option);
                });
                results.style.display = 'block';
                status.textContent = 'Pilih lokasi yang sesuai dari daftar.';
            } catch (error) {
                if (error.name === 'AbortError') return;
                status.textContent = error.message || 'Pencarian lokasi gagal. Silakan isi alamat secara manual.';
            }
        }, 500);
    });

    document.addEventListener('click', event => {
        if (!event.target.closest('.location-search-wrap')) results.style.display = 'none';
    });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/customer/account.blade.php ENDPATH**/ ?>