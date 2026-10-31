

<?php $__env->startSection('title', 'Data Pasien - Chat Dokter Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .consult-page{max-width:920px;margin:2rem auto;padding:0 1rem 3rem;color:#172b4d}
    .consult-card{padding:clamp(1.1rem,3vw,2rem);border:1px solid #dbe4f0;border-radius:18px;background:#fff;box-shadow:0 12px 32px #0f172a0d}
    .consult-kicker{color:#08734f;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
    .consult-card h1{margin:.45rem 0;color:#10243e;font-size:clamp(1.5rem,4vw,2rem)}
    .consult-muted{color:#52657e;line-height:1.6}
    .consult-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
    .consult-field label{display:block;margin-bottom:.35rem;color:#243b53;font-size:.88rem;font-weight:700}
    .consult-field input,.consult-field select,.consult-field textarea{width:100%;min-height:44px;padding:.65rem .75rem;border:1px solid #b8c5d6;border-radius:9px;background:#fff;color:#172b4d;font:inherit}
    .consult-field input:focus,.consult-field select:focus,.consult-field textarea:focus{outline:3px solid #bfdbfe;border-color:#1d4ed8}
    .consult-check{display:flex;align-items:flex-start;gap:.65rem;margin:1.1rem 0;color:#334155;font-size:.88rem;line-height:1.5}
    .consult-check input{margin-top:.25rem}
    .consult-alert{padding:.8rem 1rem;border:1px solid #fecaca;border-radius:9px;background:#fef2f2;color:#991b1b}
    .consult-notice{margin:1rem 0;padding:.85rem 1rem;border:1px solid #fed7aa;border-radius:9px;background:#fff7ed;color:#9a3412;line-height:1.55}
    .consult-primary{display:inline-flex;justify-content:center;align-items:center;gap:.5rem;min-height:46px;padding:.7rem 1.1rem;border:0;border-radius:9px;background:#08734f;color:#fff;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}
    .consult-primary:hover{background:#065f46}
    .consult-errors{margin-bottom:1rem;padding:.8rem 1rem  .8rem 2rem;border:1px solid #fecaca;border-radius:9px;background:#fef2f2;color:#991b1b}
    @media(max-width:620px){.consult-grid{grid-template-columns:1fr}}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="consult-page">
    <section class="consult-card">
        <span class="consult-kicker"><?php echo e(($isAdditionalProfile ?? false) ? 'Konsultasi online · Profil pasien' : 'Konsultasi online · Data pasien'); ?></span>
        <h1><?php echo e(($isAdditionalProfile ?? false) ? 'Tambah profil pasien' : 'Data pasien'); ?></h1>
        <p class="consult-muted"><?php echo e(($isAdditionalProfile ?? false) ? 'Tambahkan profil untuk pasien yang akan menjalani konsultasi.' : (($isNewConsultation ?? false) ? 'Lengkapi data pasien dan informasi kesehatan untuk konsultasi dengan ' . $doctor->display_name . '. Anda akan melanjutkan ke pembayaran setelah mengirim formulir.' : 'Lengkapi data diri dan informasi konsultasi terlebih dahulu. Setelah formulir dikirim, Anda dapat memilih dokter yang tersedia.')); ?></p>
        <?php if($errors->any()): ?>
            <ul class="consult-errors" role="alert"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(($isAdditionalProfile ?? false) || ($isNewConsultation ?? false) ? route('consultations.patient.profile.store', $doctor) : route('consultations.patient.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($isNewConsultation ?? false): ?><input type="hidden" name="doctor_consultation_intake" value="1"><?php endif; ?>
            <div class="consult-grid">
                <div class="consult-field"><label for="patient_name">Nama lengkap *</label><input id="patient_name" name="patient_name" value="<?php echo e(old('patient_name')); ?>" placeholder="Contoh: Budi Santoso" maxlength="150" autocomplete="name" required></div>
                <div class="consult-field"><label for="patient_birth_date">Tanggal lahir * <small>(contoh: 15 Agustus 1990)</small></label><input id="patient_birth_date" name="patient_birth_date" type="date" value="<?php echo e(old('patient_birth_date')); ?>" max="<?php echo e(now()->toDateString()); ?>" required></div>
                <div class="consult-field"><label for="patient_gender">Jenis kelamin *</label><select id="patient_gender" name="patient_gender" required><option value="">Pilih jenis kelamin</option><option value="Laki-laki" <?php if(old('patient_gender') === 'Laki-laki'): echo 'selected'; endif; ?>>Laki-laki</option><option value="Perempuan" <?php if(old('patient_gender') === 'Perempuan'): echo 'selected'; endif; ?>>Perempuan</option></select><small>Contoh: Laki-laki atau Perempuan</small></div>
                <div class="consult-field"><label for="patient_phone">Nomor ponsel *</label><input id="patient_phone" name="patient_phone" type="tel" value="<?php echo e(old('patient_phone')); ?>" placeholder="Contoh: 081234567890" maxlength="50" autocomplete="tel" required></div>
                <div class="consult-field"><label for="patient_email">Email *</label><input id="patient_email" name="patient_email" type="email" value="<?php echo e(old('patient_email')); ?>" placeholder="Contoh: nama@email.com" maxlength="255" autocomplete="email" required></div>
                <div class="consult-field"><label for="patient_address">Alamat lengkap *</label><input id="patient_address" name="patient_address" value="<?php echo e(old('patient_address')); ?>" placeholder="Contoh: Jl. Raya Tajur No. 10, Bogor" maxlength="500" autocomplete="street-address" required></div>
            </div>
            <?php if (! ($isAdditionalProfile ?? false)): ?>
                <section style="margin-top:1.25rem;padding:1rem;border:1px solid #dbe4f0;border-radius:12px;background:#f8fafc">
                    <h2 style="margin:0 0 .85rem;color:#10243e;font-size:1.05rem">Informasi konsultasi</h2>
                    <div class="consult-grid">
                        <div class="consult-field"><label for="height_cm">Tinggi badan (cm) · opsional</label><input id="height_cm" name="height_cm" type="number" min="30" max="250" value="<?php echo e(old('height_cm')); ?>" placeholder="Contoh: 170"></div>
                        <div class="consult-field"><label for="weight_kg">Berat badan (kg) · opsional</label><input id="weight_kg" name="weight_kg" type="number" min="2" max="300" step="0.1" value="<?php echo e(old('weight_kg')); ?>" placeholder="Contoh: 65"></div>
                        <div class="consult-field" style="grid-column:1/-1"><label for="complaint">Keluhan yang ingin dikonsultasikan (sertakan juga obat yang diinginkan jika ada) *</label><textarea id="complaint" name="complaint" minlength="5" maxlength="3000" placeholder="Contoh: Demam sejak kemarin disertai sakit kepala. Obat yang diinginkan: Paracetamol 500 mg (akan dikonsultasikan dahulu dengan dokter)." required><?php echo e(old('complaint')); ?></textarea></div>
                        <div class="consult-field" style="grid-column:1/-1"><label for="drug_allergies">Alergi obat * (tulis “Tidak ada yang diketahui” jika tidak ada)</label><textarea id="drug_allergies" name="drug_allergies" maxlength="2000" placeholder="Contoh: Tidak ada yang diketahui." required><?php echo e(old('drug_allergies')); ?></textarea></div>
                    </div>
                </section>
            <?php endif; ?>
            <div class="consult-notice"><strong>Jika kondisi darurat</strong>, seperti sesak berat, penurunan kesadaran, atau perdarahan hebat, segera ke IGD atau hubungi layanan darurat. Konsultasi WhatsApp bukan pengganti pertolongan gawat darurat.</div>
            <label class="consult-check"><input type="checkbox" name="privacy_consent" value="1" <?php if(old('privacy_consent')): echo 'checked'; endif; ?> required><span>Saya menyetujui data pribadi dan kesehatan yang saya berikan digunakan untuk konsultasi ini. Setelah pembayaran diverifikasi, data formulir termasuk keluhan dan alergi akan dikirim kepada dokter melalui WhatsApp. Nama, nomor telepon, dan alamat digunakan admin untuk verifikasi pembayaran.</span></label>
            <?php if($isAdditionalProfile ?? false): ?>
                <div style="display:flex;flex-wrap:wrap;gap:.75rem">
                    <a class="consult-primary" href="<?php echo e(route('consultations.details', $doctor)); ?>" style="background:#475569">Kembali</a>
                    <button type="submit" class="consult-primary"><i class="fa-solid fa-user-plus"></i> Simpan profil pasien</button>
                </div>
            <?php else: ?>
                <button type="submit" class="consult-primary"><i class="fa-solid fa-user-doctor"></i> <?php echo e(($isNewConsultation ?? false) ? 'Lanjut ke pembayaran' : 'Lanjut pilih dokter'); ?></button>
            <?php endif; ?>
        </form>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/consultations/patient-intake.blade.php ENDPATH**/ ?>