

<?php $__env->startSection('title', 'Ringkasan Pembayaran - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    body.public-theme main.payment-page{max-width:820px;margin:1.25rem auto;padding:1rem 1rem 3rem;color:#172b4d;background:#f8fafc!important}
    body.public-theme main.payment-page h1,body.public-theme main.payment-page h2{color:#10243e!important}
    body.public-theme main.payment-page .payment-back{display:inline-flex;align-items:center;gap:.45rem;margin-bottom:1rem;color:#0755c8!important;font-weight:700;text-decoration:none}
    body.public-theme main.payment-page .payment-back:hover{text-decoration:underline}
    body.public-theme main.payment-page .payment-patient{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:.75rem 0;border-bottom:1px solid #dbe4f0;color:#334155}
    body.public-theme main.payment-page .payment-patient a{color:#0755c8!important;font-size:.85rem;font-weight:700;text-decoration:none}
    body.public-theme main.payment-page .payment-panel{margin:.75rem 0;padding:1rem;border:1px solid #dbe4f0!important;border-radius:8px;background:#fff!important;color:#172b4d!important;box-shadow:0 2px 6px #0f172a0a}
    body.public-theme main.payment-page .payment-doctor{display:flex;align-items:center;gap:.9rem}
    body.public-theme main.payment-page .payment-doctor img{width:68px;height:76px;object-fit:cover;border:1px solid #dbe4f0;border-radius:6px}
    body.public-theme main.payment-page .payment-doctor strong{color:#172b4d!important}
    body.public-theme main.payment-page .payment-doctor p{margin:.2rem 0 0;color:#52657e!important}
    body.public-theme main.payment-page .payment-line{display:flex;justify-content:space-between;gap:1rem;padding:.3rem 0;color:#475569!important}
    body.public-theme main.payment-page .payment-line strong{color:#334155!important}
    body.public-theme main.payment-page .payment-total{margin-top:.3rem;padding-top:.6rem;border-top:1px solid #e2e8f0;color:#10243e!important;font-size:1.05rem;font-weight:800}
    body.public-theme main.payment-page .payment-total strong{color:#10243e!important}
    body.public-theme main.payment-page .payment-muted{color:#52657e!important;line-height:1.5}
    body.public-theme main.payment-page .consult-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
    body.public-theme main.payment-page .consult-field label{display:block;margin-bottom:.35rem;color:#243b53;font-size:.88rem;font-weight:700}
    body.public-theme main.payment-page .consult-field input,body.public-theme main.payment-page .consult-field textarea{width:100%;min-height:44px;padding:.65rem .75rem;border:1px solid #b8c5d6;border-radius:8px;background:#fff;color:#172b4d;font:inherit}
    body.public-theme main.payment-page .consult-field textarea{min-height:95px;resize:vertical}
    body.public-theme main.payment-page .payment-methods{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;margin:.75rem 0}
    body.public-theme main.payment-page .payment-channels{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;margin:.75rem 0}
    body.public-theme main.payment-page .payment-option{display:flex;align-items:center;gap:.55rem;min-height:50px;padding:.7rem;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#243b53;font-weight:700}
    body.public-theme main.payment-page .payment-option input{accent-color:#0755c8}
    body.public-theme main.payment-page .qris-preview{display:block;width:min(220px,100%);height:auto;margin:.75rem auto;border:1px solid #dbe4f0;border-radius:8px;background:#fff}
    body.public-theme main.payment-page .payment-method-panel[hidden]{display:none!important}
    body.public-theme main.payment-page .payment-verification{text-align:center}
    body.public-theme main.payment-page .consult-check{display:flex;align-items:flex-start;gap:.6rem;margin:1rem 0;color:#334155!important;line-height:1.5}
    body.public-theme main.payment-page .consult-check span{color:#334155!important}
    body.public-theme main.payment-page .consult-warning{padding:.75rem 1rem;border:1px solid #fdba74!important;border-radius:9px;background:#fff7ed!important;color:#7c2d12!important;line-height:1.55}
    body.public-theme main.payment-page .consult-warning strong{color:#7c2d12!important}
    body.public-theme main.payment-page button.consult-primary{display:flex;width:100%;justify-content:center;align-items:center;gap:.5rem;min-height:48px;padding:.7rem 1rem;border:1px solid #0755c8!important;border-radius:6px;background:#0755c8!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font:inherit;font-weight:800;cursor:pointer}
    body.public-theme main.payment-page button.consult-primary *{background:transparent!important;color:#fff!important;-webkit-text-fill-color:#fff!important}
    body.public-theme main.payment-page button.consult-primary:hover{background:#063583!important;border-color:#063583!important;color:#fff!important;-webkit-text-fill-color:#fff!important}
    body.public-theme main.payment-page .consult-errors{padding:.8rem 1rem .8rem 2rem;border:1px solid #fecaca;border-radius:9px;background:#fef2f2;color:#991b1b}
    body.public-theme main.payment-page .consult-warning{padding:.75rem 1rem;border:1px solid #fed7aa;border-radius:9px;background:#fff7ed;color:#9a3412;line-height:1.55}
    @media(max-width:620px){body.public-theme main.payment-page .consult-grid,body.public-theme main.payment-page .payment-methods,body.public-theme main.payment-page .payment-channels{grid-template-columns:1fr}}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $patient = session('doctor_consultation.patient');
    $qrisConfigured = filled($settings?->qris_image_path);
    $bankConfigured = filled($settings?->bank_name) && filled($settings?->account_number) && filled($settings?->account_holder);
    $defaultPaymentMethod = old('payment_method', $qrisConfigured ? 'qris' : 'transfer');
    if (
        !in_array($defaultPaymentMethod, ['qris', 'transfer'], true)
        || ($defaultPaymentMethod === 'qris' && !$qrisConfigured)
        || ($defaultPaymentMethod === 'transfer' && !$bankConfigured)
    ) {
        $defaultPaymentMethod = $qrisConfigured ? 'qris' : ($bankConfigured ? 'transfer' : null);
    }
?>
<main class="payment-page">
    <a class="payment-back" href="<?php echo e(route('consultations.details', $doctor)); ?>"><i class="fa-solid fa-arrow-left"></i> Kembali ke pilih pasien</a>
    <div class="payment-patient">
        <span>Nama Pasien:</span>
        <span><strong><?php echo e($patient['name']); ?></strong> <a href="<?php echo e(route('consultations.details', $doctor)); ?>">Ganti</a></span>
    </div>
    <section class="payment-panel payment-doctor">
        <?php if($doctor->photo_url): ?><img src="<?php echo e($doctor->photo_url); ?>" alt="Foto <?php echo e($doctor->display_name); ?>"><?php endif; ?>
        <div><strong><?php echo e($doctor->display_name); ?></strong><p><?php echo e($doctor->specialty); ?></p></div>
    </section>
    <section class="payment-panel" aria-label="Rincian pembayaran">
        <div class="payment-line"><span>Biaya sesi <?php echo e($doctor->session_duration_minutes); ?> menit</span><strong>Rp <?php echo e(number_format($doctor->consultation_price, 0, ',', '.')); ?></strong></div>
        <div class="payment-line payment-total"><span>Pembayaranmu</span><strong>Rp <?php echo e(number_format($doctor->consultation_price, 0, ',', '.')); ?></strong></div>
    </section>
    <?php if($errors->any()): ?><ul class="consult-errors" role="alert"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" action="<?php echo e(route('consultations.store', $doctor)); ?>">
            <?php echo csrf_field(); ?>
            <section class="payment-panel">
                <h2 style="margin:0 0 .75rem;font-size:1.05rem">Pilih metode pembayaran</h2>
                <p class="payment-muted">Pembayaran diverifikasi admin. Timer <?php echo e($doctor->session_duration_minutes); ?> menit mulai saat dokter masuk ke room chat.</p>
                <div class="payment-methods">
                    <?php if($qrisConfigured): ?>
                        <label class="payment-option"><input type="radio" name="payment_method" value="qris" <?php if($defaultPaymentMethod === 'qris'): echo 'checked'; endif; ?> required> QRIS</label>
                    <?php endif; ?>
                    <?php if($bankConfigured): ?>
                        <label class="payment-option"><input type="radio" name="payment_method" value="transfer" <?php if($defaultPaymentMethod === 'transfer'): echo 'checked'; endif; ?> required> Transfer bank</label>
                    <?php endif; ?>
                </div>
                <?php if($qrisConfigured): ?>
                    <div class="payment-method-panel" data-payment-method-panel="qris" <?php if (! ($defaultPaymentMethod === 'qris')): ?> hidden <?php endif; ?>>
                        <img class="qris-preview" src="<?php echo e($settings->qris_image_url); ?>" alt="QRIS pembayaran konsultasi">
                    </div>
                <?php endif; ?>
                <?php if($bankConfigured): ?>
                    <div class="payment-method-panel payment-muted" data-payment-method-panel="transfer" <?php if (! ($defaultPaymentMethod === 'transfer')): ?> hidden <?php endif; ?>>
                        <strong><?php echo e($settings->bank_name); ?>:</strong> <strong><?php echo e($settings->account_number); ?></strong>
                    </div>
                <?php endif; ?>
                <p class="payment-muted payment-verification"><strong>Atas nama <?php echo e($settings?->account_holder ?: 'Apotek Medix'); ?></strong><br>Tunggu Verifikasi Admin</p>
                <?php if($settings?->payment_instructions): ?><p class="payment-muted"><?php echo e($settings->payment_instructions); ?></p><?php endif; ?>
                <?php if (! ($qrisConfigured || $bankConfigured)): ?>
                    <div class="consult-warning">Metode pembayaran belum tersedia. Silakan hubungi admin Apotek Medix.</div>
                <?php endif; ?>
                <h2 style="margin:1rem 0 .4rem;font-size:1.05rem">Cara pembayaran</h2>
                <div class="payment-channels">
                    <label class="payment-option"><input type="radio" name="payment_channel" value="online" <?php if(old('payment_channel', 'online') === 'online'): echo 'checked'; endif; ?> required> Online</label>
                    <label class="payment-option"><input type="radio" name="payment_channel" value="offline" <?php if(old('payment_channel') === 'offline'): echo 'checked'; endif; ?> required> Offline di kasir</label>
                </div>
                <div class="consult-field">
                    <label for="payment_proof">Bukti pembayaran (maks. 5 MB) <span id="paymentProofRequirement">(wajib untuk pembayaran online)</span></label>
                    <input id="payment_proof" name="payment_proof" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" <?php if(old('payment_channel', 'online') === 'online'): echo 'required'; endif; ?>>
                    <p class="payment-muted" id="paymentProofHelp">Untuk pembayaran online, unggah bukti transfer/QRIS. Jika membayar langsung di apotek, bukti cukup ditunjukkan kepada kasir.</p>
                </div>
            </section>
            <div class="consult-warning"><strong>Perhatian:</strong> layanan chat bukan untuk kondisi gawat darurat dan tidak menggantikan pemeriksaan langsung bila diperlukan.</div>
            <label class="consult-check"><input type="checkbox" name="privacy_consent" value="1" <?php if(old('privacy_consent')): echo 'checked'; endif; ?> required><span>Saya menyatakan informasi yang diberikan benar dan menyetujui data kesehatan diproses untuk konsultasi.</span></label>
            <button type="submit" class="consult-primary"><i class="fa-solid fa-lock"></i> Konfirmasi Konsultasi</button>
    </form>
</main>
<script>
    const proofInput = document.getElementById('payment_proof');
    const proofRequirement = document.getElementById('paymentProofRequirement');
    const proofHelp = document.getElementById('paymentProofHelp');
    const updatePaymentChannel = () => {
        const channel = document.querySelector('input[name="payment_channel"]:checked')?.value;
        const isOnline = channel === 'online';
        proofInput.required = isOnline;
        proofRequirement.textContent = isOnline ? '(wajib untuk pembayaran online)' : '(opsional untuk pembayaran offline)';
        proofHelp.textContent = isOnline
            ? 'Untuk pembayaran online, unggah bukti transfer/QRIS. Pembayaran tetap menunggu verifikasi admin.'
            : 'Jika membayar langsung di apotek, bukti cukup ditunjukkan kepada kasir. Konsultasi tetap menunggu verifikasi admin.';
    };
    document.querySelectorAll('input[name="payment_channel"]').forEach((input) => {
        input.addEventListener('change', updatePaymentChannel);
    });
    document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
        input.addEventListener('change', () => {
            document.querySelectorAll('[data-payment-method-panel]').forEach((panel) => {
                panel.hidden = panel.dataset.paymentMethodPanel !== input.value;
            });
        });
    });
    updatePaymentChannel();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/consultations/details.blade.php ENDPATH**/ ?>