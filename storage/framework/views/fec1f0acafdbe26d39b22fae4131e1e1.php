

<?php $__env->startSection('title', 'Pilih Pasien - Chat Dokter Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    body.public-theme main.patient-select-page{max-width:760px;min-height:calc(100vh - var(--navbar-height,65px));margin:1rem auto;padding:1.25rem 1rem;color:#172b4d;background:#f8fafc!important}
    body.public-theme main.patient-select-page h1{margin:.35rem 0;color:#10243e!important;font-size:1.5rem}
    body.public-theme main.patient-select-page .patient-select-head{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1rem}
    body.public-theme main.patient-select-page .patient-select-head p{margin:0;color:#52657e!important}
    body.public-theme main.patient-select-page .profile-add{color:#be123c!important;font-weight:700;text-decoration:none}
    body.public-theme main.patient-select-page .profile-add:hover{text-decoration:underline}
    body.public-theme main.patient-select-page .patient-select-info{margin:1rem 0;padding:.85rem 1rem;border:1px solid #bae6fd;border-radius:8px;background:#effaff!important;color:#164e63!important;line-height:1.5}
    body.public-theme main.patient-select-page .patient-select-info,
    body.public-theme main.patient-select-page .patient-select-info strong,
    body.public-theme main.patient-select-page .patient-select-info i{color:#164e63!important}
    body.public-theme main.patient-select-page .patient-profile-option{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:.75rem 0;padding:1rem 1.15rem;border:1px solid #d1d5db;border-radius:8px;background:#fff!important;color:#172b4d!important;box-shadow:0 2px 5px #0f172a12;cursor:pointer}
    body.public-theme main.patient-select-page .patient-profile-option:has(input:checked){border-color:#e6004c;box-shadow:0 0 0 2px #fce7f3}
    body.public-theme main.patient-select-page .patient-profile-option span,
    body.public-theme main.patient-select-page .patient-profile-option strong{display:block;color:#172b4d!important}
    body.public-theme main.patient-select-page .patient-profile-option small{display:block;margin-top:.3rem;color:#475569!important}
    body.public-theme main.patient-select-page .patient-profile-option input{width:18px;height:18px;accent-color:#e6004c}
    body.public-theme main.patient-select-page .patient-select-actions{margin-top:1rem}
    body.public-theme main.patient-select-page .patient-select-actions button{width:100%;min-height:46px;border:0;border-radius:5px;background:#0755c8!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font:inherit;font-weight:800}
    body.public-theme main.patient-select-page .patient-select-actions button:hover{background:#063583!important}
    @media(max-width:600px){body.public-theme main.patient-select-page{margin:.5rem;padding:1rem .75rem}.patient-select-head{align-items:flex-start!important}}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="patient-select-page">
    <header class="patient-select-head">
        <div>
            <p>Konsultasi online · Langkah 1 dari 2</p>
            <h1>Pilih Pasien</h1>
        </div>
        <a class="profile-add" href="<?php echo e(route('consultations.patient.create', $doctor)); ?>">+ Profil Baru</a>
    </header>

    <div class="patient-select-info">
        <strong><i class="fa-solid fa-circle-info"></i> Pilih pasien yang perlu penanganan</strong><br>
        Pilihan pasien memengaruhi informasi kesehatan yang akan dicantumkan pada konsultasi.
    </div>

    <?php if($errors->any()): ?>
        <div role="alert" style="margin:1rem 0;padding:.75rem 1rem;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;color:#991b1b">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p style="margin:0"><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('consultations.patient.select', $doctor)); ?>">
        <?php echo csrf_field(); ?>
        <?php $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profileId => $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="patient-profile-option">
                <span>
                    <strong><?php echo e($profile['name']); ?></strong>
                    <small><?php echo e($profile['gender']); ?> · Lahir <?php echo e(\Illuminate\Support\Carbon::parse($profile['birth_date'])->format('d-m-Y')); ?></small>
                </span>
                <input type="radio" name="patient_profile" value="<?php echo e($profileId); ?>" <?php if($profileId === $selectedProfileId || ($selectedProfileId === null && $loop->first)): echo 'checked'; endif; ?> required>
            </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="patient-select-actions">
            <button type="submit">Lanjut</button>
        </div>
    </form>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/consultations/select-patient.blade.php ENDPATH**/ ?>