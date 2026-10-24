

<?php $__env->startSection('title', 'Kode Akses GASS - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .gass-access { max-width: 520px; margin: 0 auto; padding: calc(var(--navbar-height, 65px) + 1.5rem) 1rem 3rem; }
    .gass-access-card { padding: clamp(1.25rem, 4vw, 2.25rem); background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; box-shadow: 0 12px 36px rgba(31, 41, 55, .1); }
    .gass-access-heading { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .gass-access-heading img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; }
    .gass-access-heading h1 { color: #1565c0; font-size: clamp(1.4rem, 4vw, 2rem); }
    .gass-access-heading p { color: #64748b; }
    .gass-access-form { display: grid; gap: .75rem; margin-top: 1.5rem; }
    .gass-access-form label { font-weight: 700; color: #334155; }
    .gass-access-form input { width: 100%; padding: .8rem; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; }
    .gass-access-button { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: fit-content; padding: .8rem 1.1rem; border: 0; border-radius: 10px; background: #1565c0; color: #fff; font-weight: 700; }
    .gass-access-button:hover { background: #0d47a1; }
    .gass-access-error { color: #b91c1c; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="gass-access">
    <div class="gass-access-card">
        <header class="gass-access-heading">
            <img src="<?php echo e(asset('logo gass.jpeg')); ?>" alt="Logo GASS">
            <div>
                <h1>Ruang File GASS</h1>
                <p>Masukkan kode akses sebelum masuk ke ruang file.</p>
            </div>
        </header>

        <form class="gass-access-form" action="<?php echo e(route('gass.access')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <label for="access_code">Kode akses</label>
            <input id="access_code" name="access_code" type="password" required autocomplete="current-password" autofocus>
            <?php $__errorArgs = ['access_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="gass-access-error"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <button class="gass-access-button" type="submit">Masuk ke Ruang GASS</button>
        </form>
    </div>
</section>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\gass\access.blade.php ENDPATH**/ ?>