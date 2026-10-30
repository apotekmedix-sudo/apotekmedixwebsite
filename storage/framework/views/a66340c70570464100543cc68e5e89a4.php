

<?php $__env->startSection('title', 'Sesi Konsultasi - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .session-page{max-width:900px;margin:2rem auto;padding:0 1rem 3rem;color:#172b4d}
    .session-card{padding:clamp(1rem,3vw,1.6rem);border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 12px 32px #0f172a0d}
    .session-card h1{margin:.25rem 0;color:#10243e;font-size:1.5rem}.session-muted{color:#52657e;line-height:1.55}
    .session-status{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .65rem;border-radius:999px;background:#fff7ed;color:#9a3412;font-size:.78rem;font-weight:800}
    .session-payment{margin:1rem 0;padding:1rem;border:1px solid #dbe4f0;border-radius:12px;background:#f8fafc}
    .session-qris{display:block;width:min(260px,100%);margin:.75rem auto;border:1px solid #dbe4f0;border-radius:8px;background:#fff}
    .session-chat{display:flex;flex-direction:column;height:min(62vh,650px);min-height:360px;margin-top:1rem;border:1px solid #cbd5e1;border-radius:12px;overflow:hidden}
    .session-chat-head{padding:.85rem 1rem;background:#0f766e;color:#fff;font-weight:800}
    .session-messages{flex:1;overflow:auto;padding:1rem;background:#f8fafc}
    .session-message{max-width:82%;margin:0 0 .75rem;padding:.65rem .8rem;border-radius:12px;background:#fff;border:1px solid #e2e8f0;white-space:pre-wrap;overflow-wrap:anywhere}
    .session-message.mine{margin-left:auto;background:#dcfce7;border-color:#bbf7d0}
    .session-message small{display:block;margin-bottom:.2rem;color:#475569;font-size:.7rem;font-weight:700}
    .session-compose{display:flex;gap:.5rem;padding:.75rem;border-top:1px solid #e2e8f0;background:#fff}
    .session-compose textarea{flex:1;min-width:0;min-height:44px;max-height:120px;padding:.6rem;border:1px solid #cbd5e1;border-radius:8px;font:inherit}
    .session-compose button{padding:.6rem .9rem;border:0;border-radius:8px;background:#08734f;color:#fff;font-weight:800}
    .session-warning{padding:.8rem 1rem;border:1px solid #fed7aa;border-radius:9px;background:#fff7ed;color:#9a3412}
    .session-prescription{margin-top:1rem;padding:1rem;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4}
    .session-prescription h2{margin:0 0 .45rem;color:#166534;font-size:1.05rem}
    .session-prescription li{margin:.35rem 0;line-height:1.5}
    .session-prescription button{padding:.65rem .9rem;border:0;border-radius:8px;background:#08734f;color:#fff;font:inherit;font-weight:800;cursor:pointer}
    .session-prescription button:disabled{opacity:.55;cursor:not-allowed}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="session-page">
    <section class="session-card">
        <span class="session-status" id="consultationStatus"><i class="fa-solid fa-clock"></i> <?php echo e($consultation->status === 'awaiting_payment' ? 'Menunggu verifikasi pembayaran' : ($consultation->status === 'active' ? ($consultation->starts_at ? 'Sesi konsultasi aktif' : 'Menunggu dokter masuk room') : ucfirst($consultation->status))); ?></span>
        <h1>Konsultasi dengan <?php echo e($consultation->doctorProfile->display_name); ?></h1>
        <p class="session-muted"><?php echo e($consultation->doctorProfile->specialty); ?> · Sesi konsultasi <?php echo e($consultation->session_duration_minutes); ?> menit</p>
        <?php if($consultation->status === 'awaiting_payment'): ?>
            <div id="pendingPayment" class="session-payment">
                <h2 style="margin-top:0;color:#10243e">Selesaikan pembayaran</h2>
                <p>Total: <strong>Rp <?php echo e(number_format($consultation->amount, 0, ',', '.')); ?></strong></p>
                <p>Metode: <strong><?php echo e($consultation->payment_method === 'qris' ? 'QRIS' : 'Transfer'); ?></strong></p>
                <p>Cara pembayaran: <strong><?php echo e($consultation->payment_channel === 'online' ? 'Online' : 'Offline di kasir'); ?></strong></p>
                <?php if($consultation->payment_method === 'qris' && $settings?->qris_image_path): ?>
                    <img class="session-qris" src="<?php echo e($settings->qris_image_url); ?>" alt="QRIS pembayaran">
                <?php elseif($consultation->payment_method === 'transfer'): ?>
                    <p><strong><?php echo e($settings?->bank_name); ?>:</strong> <?php echo e($settings?->account_number); ?><br>Atas nama <?php echo e($settings?->account_holder); ?></p>
                <?php endif; ?>
                <?php if($consultation->payment_channel === 'offline'): ?>
                    <p class="session-muted">Tunjukkan bukti pembayaran kepada kasir. Konsultasi tetap menunggu verifikasi admin.</p>
                <?php elseif($consultation->payment_proof_path): ?>
                    <p class="session-muted">Bukti pembayaran sudah dikirim dan sedang menunggu verifikasi admin.</p>
                <?php endif; ?>
                <?php if($settings?->payment_instructions): ?><p class="session-muted"><?php echo e($settings->payment_instructions); ?></p><?php endif; ?>
                <p class="session-warning">Timer sesi <?php echo e($consultation->session_duration_minutes); ?> menit dimulai saat dokter masuk ke room. Batas pembayaran: <strong id="paymentDeadline"><?php echo e($consultation->payment_due_at?->format('d-m-Y H:i')); ?></strong> WIB.</p>
                <p class="session-muted">Jangan bagikan tautan konsultasi ini kepada orang lain. Tautan tersebut memberikan akses ke sesi dan riwayat chat Anda.</p>
            </div>
        <?php elseif($consultation->status === 'cancelled'): ?>
            <?php if($consultation->payment_status === 'rejected'): ?>
                <div class="session-warning">
                    <strong>Pembayaran ditolak.</strong>
                    <p>Jika saldo Anda sudah terpotong, pengembaliannya akan diproses manual oleh admin. Silakan kembali ke daftar dokter dan hubungi admin untuk bantuan.</p>
                    <a href="<?php echo e(route('consultations.doctors')); ?>">Kembali ke daftar dokter</a>
                    <a href="https://wa.me/<?php echo e(config('contacts.customer_service.whatsapp')); ?>?text=<?php echo e(rawurlencode('Halo Admin Apotek Medix, saya perlu bantuan pengembalian dana konsultasi #' . $consultation->id . '.')); ?>" target="_blank" rel="noopener noreferrer">Hubungi admin</a>
                </div>
            <?php else: ?>
                <p class="session-warning">Konsultasi ini dibatalkan atau batas pembayaran telah lewat.</p>
            <?php endif; ?>
        <?php elseif($consultation->status === 'active' || $consultation->status === 'completed'): ?>
            <?php if (! ($hasRoomAccess)): ?>
                <p class="session-warning">Pembayaran sudah dikonfirmasi. Segarkan halaman ini untuk membuka percakapan pasien.</p>
            <?php else: ?>
            <div class="session-warning">Sisa waktu: <strong id="consultationTimer"><?php if($consultation->ends_at): ?><?php echo e(sprintf('%02d:00', $consultation->session_duration_minutes)); ?><?php else: ?> Menunggu dokter bergabung <?php endif; ?></strong>. <span id="consultationTimerHint"><?php echo e($consultation->ends_at ? 'Sesi berakhir otomatis setelah ' . $consultation->session_duration_minutes . ' menit.' : 'Timer dimulai saat dokter masuk ke room.'); ?></span></div>
            <?php if($consultation->payment_channel === 'online'): ?>
            <section class="session-prescription" id="issuedPrescription" hidden aria-live="polite">
                <h2>Resep dari dokter</h2>
                <p style="margin:.2rem 0">Nomor resep: <strong id="prescriptionNumber"></strong></p>
                <ul id="prescriptionItems"></ul>
                <p id="prescriptionStatus" style="margin:.5rem 0"></p>
                <button id="addPrescriptionToCart" type="button">Masukkan produk ke keranjang</button>
                <p id="prescriptionCartError" role="alert" style="margin:.5rem 0 0;color:#991b1b" hidden></p>
            </section>
            <?php endif; ?>
            <div class="session-chat">
                <div class="session-chat-head">Chat dokter · <span id="sessionState"><?php echo e($consultation->status === 'active' ? ($consultation->starts_at ? 'Sesi aktif' : 'Menunggu dokter') : 'Sesi selesai'); ?></span></div>
                <div id="sessionMessages" class="session-messages" aria-live="polite"></div>
                <p id="chatError" role="alert" style="display:none;margin:0;padding:.6rem .8rem;background:#fef2f2;color:#991b1b;font-size:.85rem"></p>
                <form id="patientMessageForm" class="session-compose">
                    <textarea id="patientMessageText" maxlength="2000" placeholder="Tulis pesan..." <?php if(!$consultation->isActive()): echo 'disabled'; endif; ?>></textarea>
                    <button type="submit" <?php if(!$consultation->isActive()): echo 'disabled'; endif; ?>>Kirim</button>
                </form>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
<?php if($consultation->status === 'awaiting_payment' || $hasRoomAccess): ?>
<script>
(() => {
    <?php if($token): ?>
    const updatesUrl = <?php echo json_encode(route('consultations.patient.updates', ['token' => $token]), 512) ?>;
    const messageUrl = <?php echo json_encode(route('consultations.patient.messages.store', ['token' => $token]), 512) ?>;
    <?php else: ?>
    const updatesUrl = <?php echo json_encode(route('consultations.room.updates', $consultation), 512) ?>;
    const messageUrl = <?php echo json_encode(route('consultations.room.messages.store', $consultation), 512) ?>;
    <?php endif; ?>
    const token = <?php echo json_encode(csrf_token(), 15, 512) ?>;
    const checkoutUrl = <?php echo json_encode(route('checkout.index'), 15, 512) ?>;
    const container = document.getElementById('sessionMessages');
    let lastId = 0;
    let endsAt = <?php echo json_encode($consultation->ends_at?->toIso8601String(), 15, 512) ?>;
    let activePrescription = null;
    const formatRemaining = () => {
        const timer = document.getElementById('consultationTimer');
        if (!timer || !endsAt) return;
        const seconds = Math.max(0, Math.floor((new Date(endsAt).getTime() - Date.now()) / 1000));
        timer.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
        if (!seconds) {
            document.getElementById('sessionState').textContent = 'Sesi selesai';
            const textarea = document.getElementById('patientMessageText');
            const button = document.querySelector('#patientMessageForm button');
            if (textarea) textarea.disabled = true;
            if (button) button.disabled = true;
        }
    };
    const addMessage = message => {
        if (!container || message.id <= lastId) return;
        const item = document.createElement('article');
        item.className = `session-message${message.sender_type === 'patient' ? ' mine' : ''}`;
        const sender = document.createElement('small');
        sender.textContent = message.sender_name;
        const body = document.createElement('div');
        body.textContent = message.body;
        item.append(sender, body);
        container.append(item);
        container.scrollTop = container.scrollHeight;
        lastId = message.id;
    };
    const renderPrescription = prescription => {
        const panel = document.getElementById('issuedPrescription');
        if (!panel || !prescription) return;
        activePrescription = prescription;
        document.getElementById('prescriptionNumber').textContent = prescription.number;
        const list = document.getElementById('prescriptionItems');
        list.replaceChildren();
        prescription.items.forEach(item => {
            const row = document.createElement('li');
            row.textContent = `${item.name} · ${item.quantity} ${item.unit_label} · ${item.instructions}`;
            list.append(row);
        });
        const status = document.getElementById('prescriptionStatus');
        const button = document.getElementById('addPrescriptionToCart');
        if (prescription.used) {
            status.textContent = 'Resep ini sudah digunakan untuk checkout.';
            button.disabled = true;
        } else {
            status.textContent = 'Produk dan jumlah keranjang harus mengikuti resep dokter. Nomor resep akan dikirim saat checkout.';
            button.disabled = false;
        }
        panel.hidden = false;
    };
    document.getElementById('addPrescriptionToCart')?.addEventListener('click', () => {
        const error = document.getElementById('prescriptionCartError');
        error.hidden = true;
        if (!activePrescription || activePrescription.used) return;
        try {
            const storageKey = 'apotekmedix_cart_b2c';
            const cart = JSON.parse(localStorage.getItem(storageKey) || '[]');
            if (!Array.isArray(cart)) throw new Error('Keranjang tidak valid. Muat ulang halaman lalu coba kembali.');
            activePrescription.items.forEach(item => {
                const conflictingItems = cart.filter(existing => Number(existing.id) === Number(item.id));
                if (conflictingItems.some(existing => (existing.saleUnit || 'box') !== (item.sale_unit || 'box'))) {
                    throw new Error(`Hapus satuan lain untuk ${item.name} dari keranjang sebelum memasukkan resep.`);
                }
                const existing = conflictingItems[0];
                if (existing && Number(existing.qty || 0) > Number(item.quantity)) {
                    throw new Error(`Jumlah ${item.name} di keranjang melebihi jumlah yang disetujui dokter. Sesuaikan keranjang lalu coba lagi.`);
                }
                const prescribedCartItem = {
                    id: Number(item.id),
                    name: item.name,
                    brand: item.brand || '',
                    img: item.image || '',
                    price: Number(item.price),
                    qty: Number(item.quantity),
                    note: item.instructions,
                    discount: 0,
                    saleUnit: item.sale_unit || 'box',
                    unitLabel: item.unit_label,
                    unitLabels: {[item.sale_unit || 'box']: item.unit_label},
                    unitPrices: {[item.sale_unit || 'box']: Number(item.price)},
                    unitFactors: {[item.sale_unit || 'box']: 1},
                    packageUnitLabel: item.unit_label,
                    availableQty: Number(item.available_quantity),
                    requiresPrescription: Boolean(item.requires_prescription),
                    consultationPrescriptionNumber: activePrescription.number
                };
                if (existing) Object.assign(existing, prescribedCartItem);
                else cart.push(prescribedCartItem);
            });
            localStorage.setItem(storageKey, JSON.stringify(cart));
            localStorage.setItem('apotekmedix_active_cart_context', JSON.stringify({
                storageKey,
                priceChannel: 'b2c',
                wholesaleOrder: false,
                adminPbfOrder: false,
                continueShoppingUrl: <?php echo json_encode(route('home'), 15, 512) ?>
            }));
            window.location.assign(checkoutUrl);
        } catch (exception) {
            error.textContent = exception.message || 'Produk resep tidak dapat dimasukkan ke keranjang.';
            error.hidden = false;
        }
    });
    const poll = async () => {
        const response = await fetch(`${updatesUrl}?after_id=${lastId}`, {headers:{'Accept':'application/json'}});
        if (!response.ok) throw new Error('Status konsultasi tidak dapat diperbarui.');
        const data = await response.json();
        data.messages.forEach(addMessage);
        if (data.prescription) renderPrescription(data.prescription);
        if (data.ends_at) {
            endsAt = data.ends_at;
            document.getElementById('consultationStatus').innerHTML = '<i class="fa-solid fa-clock"></i> Sesi konsultasi aktif';
            document.getElementById('sessionState').textContent = 'Sesi aktif';
            document.getElementById('consultationTimerHint').textContent = `Sesi berakhir otomatis setelah <?php echo e($consultation->session_duration_minutes); ?> menit.`;
        }
        if (data.status === 'active' && document.getElementById('pendingPayment')) {
            window.location.reload();
            return;
        }
        if (data.status === 'cancelled' && document.getElementById('pendingPayment')) {
            window.location.reload();
            return;
        }
        if (data.status === 'completed') {
            const state = document.getElementById('sessionState');
            if (state) state.textContent = 'Sesi selesai';
            document.getElementById('consultationStatus').innerHTML = '<i class="fa-solid fa-clock"></i> Sesi selesai';
        }
        formatRemaining();
    };
    document.getElementById('patientMessageForm')?.addEventListener('submit', async event => {
        event.preventDefault();
        const textarea = document.getElementById('patientMessageText');
        const button = event.currentTarget.querySelector('button');
        const error = document.getElementById('chatError');
        const message = textarea.value.trim();
        if (!message) return;
        button.disabled = true;
        error.style.display = 'none';
        try {
            const response = await fetch(messageUrl, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
                body:JSON.stringify({body:message})
            });
            if (response.status === 409) throw new Error('Sesi konsultasi telah berakhir.');
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Pesan tidak dapat dikirim.');
            addMessage(result);
            textarea.value = '';
        } catch (exception) {
            error.textContent = exception.message || 'Pesan tidak dapat dikirim. Periksa koneksi lalu coba lagi.';
            error.style.display = 'block';
        } finally {
            button.disabled = Boolean(endsAt && new Date(endsAt).getTime() <= Date.now());
        }
    });
    const runPoll = () => poll().catch(error => {
        console.error('Gagal memperbarui sesi konsultasi:', error);
    });
    formatRemaining();
    runPoll();
    window.setInterval(runPoll, 4000);
    window.setInterval(formatRemaining, 1000);
})();
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/consultations/patient-session.blade.php ENDPATH**/ ?>