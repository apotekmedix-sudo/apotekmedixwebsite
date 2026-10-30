

<?php $__env->startSection('title', 'Akses File KUNJUNGAN - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .kunjungan-page{max-width:560px;margin:0 auto;padding:calc(var(--navbar-height, 65px) + 1.5rem) 1rem 3rem}
    .kunjungan-card{padding:clamp(1.25rem,4vw,2rem);border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 12px 32px #0f172a0d}
    .kunjungan-card h1{margin:0 0 .45rem;color:#10243e;font-size:1.55rem}
    .kunjungan-card p{color:#52657e;line-height:1.55}
    .kunjungan-form{display:grid;gap:.7rem;margin-top:1.25rem}
    .kunjungan-form label{font-weight:700;color:#334155}
    .kunjungan-form input{width:100%;padding:.75rem;border:1px solid #cbd5e1;border-radius:9px;font:inherit}
    .kunjungan-button{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;width:fit-content;padding:.7rem 1rem;border:0;border-radius:9px;background:#08734f;color:#fff;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}
    .kunjungan-button:hover{background:#065f46;color:#fff}
    .kunjungan-error{color:#b91c1c}
    .kunjungan-message{margin-bottom:1rem;padding:.7rem .85rem;border:1px solid #bbf7d0;border-radius:9px;background:#f0fdf4;color:#166534}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="kunjungan-page">
    <section class="kunjungan-card">
        <h1>Akses File KUNJUNGAN</h1>
        <p>Masukkan kode akses untuk membuka ruang penyimpanan file.</p>
        <?php if(session('success')): ?><p class="kunjungan-message" role="status"><?php echo e(session('success')); ?></p><?php endif; ?>
        <form class="kunjungan-form" action="<?php echo e(route('kunjungan.access')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <label for="access_code">Kode akses</label>
            <input id="access_code" name="access_code" type="password" required maxlength="100" autocomplete="current-password" autofocus>
            <?php $__errorArgs = ['access_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="kunjungan-error" role="alert"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <button class="kunjungan-button" type="submit"><i class="fa-solid fa-lock-open"></i> Masuk ke KUNJUNGAN</button>
        </form>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/kunjungan/access.blade.php ENDPATH**/ ?>