

<?php $__env->startSection('title', ($doctor ? 'Edit Dokter' : 'Tambah Dokter') . ' - Apotek Medix'); ?>
<?php $__env->startSection('page-title', $doctor ? 'Edit Dokter' : 'Tambah Dokter'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .doctor-admin-card{max-width:980px;padding:1.2rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}
    .doctor-admin-card h2{margin:0 0 .4rem;color:#172b4d}.doctor-admin-muted{color:#64748b;font-size:.88rem;line-height:1.5}
    .doctor-admin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem}
    .doctor-admin-field label{display:block;margin-bottom:.3rem;color:#334155;font-size:.82rem;font-weight:700}
    .doctor-admin-field input,.doctor-admin-field textarea,.doctor-admin-field select{width:100%;min-height:40px;padding:.55rem .7rem;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:#172b4d;font:inherit}
    .doctor-admin-field textarea{min-height:90px}
    .doctor-admin-checks{display:flex;flex-wrap:wrap;gap:1rem;margin:1rem 0;color:#334155}
    .doctor-admin-checks label{display:flex;align-items:center;gap:.45rem}
    .doctor-admin-button{display:inline-flex;align-items:center;gap:.4rem;padding:.65rem .9rem;border:0;border-radius:7px;background:#0755c8;color:#fff;font-weight:800;text-decoration:none}
    .doctor-admin-button:hover{background:#0647a8;color:#fff}
    .doctor-admin-button.secondary{border:1px solid #cbd5e1;background:#fff;color:#334155}
    .doctor-admin-button.secondary:hover{border-color:#94a3b8;background:#f8fafc;color:#172b4d}
    .doctor-admin-button:focus-visible{outline:3px solid #93c5fd;outline-offset:2px}
    .doctor-admin-errors{padding:.75rem 1rem;border-radius:8px;background:#fef2f2;color:#991b1b}
    @media(max-width:680px){.doctor-admin-grid{grid-template-columns:1fr}}
</style>

<section class="doctor-admin-card">
    <h2><?php echo e($doctor ? 'Ubah profil dokter' : 'Tambah profil dokter'); ?></h2>
    <p class="doctor-admin-muted">Dokter tidak memakai akun, email, atau kata sandi. Setelah admin mengonfirmasi pembayaran, tautan room sekali pakai dikirim melalui WhatsApp.</p>
    <?php if($errors->any()): ?><ul class="doctor-admin-errors" role="alert"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul><?php endif; ?>
    <form method="POST" action="<?php echo e($doctor ? route('admin.consultations.doctors.update', $doctor) : route('admin.consultations.doctors.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php if($doctor): ?><?php echo method_field('PUT'); ?><?php endif; ?>
        <div class="doctor-admin-grid">
            <div class="doctor-admin-field"><label for="display_name">Nama dokter *</label><input id="display_name" name="display_name" value="<?php echo e(old('display_name', $doctor?->display_name)); ?>" maxlength="150" required></div>
            <div class="doctor-admin-field"><label for="specialty">Spesialisasi *</label><select id="specialty" name="specialty" required><option value="">Pilih kategori spesialisasi</option><?php $__currentLoopData = $specialties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specialty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($specialty); ?>" <?php if(old('specialty', $doctor?->specialty) === $specialty): echo 'selected'; endif; ?>><?php echo e($specialty); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
            <div class="doctor-admin-field"><label for="whatsapp_phone">Nomor WhatsApp dokter *</label><input id="whatsapp_phone" name="whatsapp_phone" type="tel" value="<?php echo e(old('whatsapp_phone', $doctor?->whatsapp_phone)); ?>" maxlength="30" placeholder="08xxxxxxxxxx atau +628xxxxxxxxxx" required></div>
            <div class="doctor-admin-field"><label for="photo">Foto profil (JPG/PNG/WEBP, maks. 2 MB)</label><input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><?php if($doctor?->photo_url): ?><img src="<?php echo e($doctor->photo_url); ?>" alt="Foto dokter saat ini" style="display:block;width:72px;height:80px;margin-top:.5rem;object-fit:cover;border-radius:8px"><?php endif; ?></div>
            <div class="doctor-admin-field"><label for="str_number">Nomor STR *</label><input id="str_number" name="str_number" value="<?php echo e(old('str_number', $doctor?->str_number)); ?>" maxlength="100" required></div>
            <div class="doctor-admin-field"><label for="experience_years">Pengalaman (tahun) *</label><input id="experience_years" name="experience_years" type="number" min="0" max="80" value="<?php echo e(old('experience_years', $doctor?->experience_years ?? 0)); ?>" required></div>
            <div class="doctor-admin-field"><label for="consultation_price">Tarif per sesi (Rp) *</label><input id="consultation_price" name="consultation_price" type="number" min="1000" max="10000000" step="1000" value="<?php echo e(old('consultation_price', $doctor?->consultation_price)); ?>" required></div>
            <div class="doctor-admin-field"><label for="session_duration_minutes">Waktu per sesi (menit) *</label><input id="session_duration_minutes" name="session_duration_minutes" type="number" min="5" max="240" step="1" value="<?php echo e(old('session_duration_minutes', $doctor?->session_duration_minutes ?? 15)); ?>" required><small class="doctor-admin-muted">Isi 5–240 menit. Default 15 menit.</small></div>
            <div class="doctor-admin-field"><label for="education">Pendidikan</label><input id="education" name="education" value="<?php echo e(old('education', $doctor?->education)); ?>" maxlength="255"></div>
            <div class="doctor-admin-field"><label for="practice_place">Tempat praktik</label><input id="practice_place" name="practice_place" value="<?php echo e(old('practice_place', $doctor?->practice_place)); ?>" maxlength="255"></div>
            <div class="doctor-admin-field"><label for="practice_city">Kota praktik</label><input id="practice_city" name="practice_city" value="<?php echo e(old('practice_city', $doctor?->practice_city)); ?>" maxlength="150"></div>
            <div class="doctor-admin-field" style="grid-column:1/-1"><label for="bio">Profil dokter</label><textarea id="bio" name="bio" maxlength="3000"><?php echo e(old('bio', $doctor?->bio)); ?></textarea></div>
        </div>
        <div class="doctor-admin-checks">
            <label><input type="checkbox" name="str_verified" value="1" <?php if(old('str_verified', $doctor?->str_verified)): echo 'checked'; endif; ?>> STR sudah diverifikasi admin</label>
            <label><input type="checkbox" name="is_active" value="1" <?php if(old('is_active', $doctor?->is_active)): echo 'checked'; endif; ?>> Profil aktif</label>
            <label><input type="checkbox" name="is_online" value="1" <?php if(old('is_online', $doctor?->is_online)): echo 'checked'; endif; ?>> Tersedia online</label>
        </div>
        <button class="doctor-admin-button" type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan dokter</button>
        <a class="doctor-admin-button secondary" href="<?php echo e(route('admin.consultations.index')); ?>"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/consultations/doctor-form.blade.php ENDPATH**/ ?>