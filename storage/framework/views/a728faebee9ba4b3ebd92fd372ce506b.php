<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Belanja B2B - Apotek Medix</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:1rem;background:#f8fafc;color:#1e293b;font-family:Arial,sans-serif}
        main{width:min(100%,420px);padding:2rem;border:1px solid #e2e8f0;border-radius:1rem;background:#fff;box-shadow:0 18px 48px #0f172a18}
        .icon{display:grid;width:56px;height:56px;place-items:center;margin-bottom:1rem;border-radius:16px;background:#eff6ff;color:#0052cc;font-size:1.5rem}
        h1{margin:0 0 .5rem;color:#0f172a;font-size:1.5rem}
        p{color:#64748b;line-height:1.5}
        label{display:block;margin:1.25rem 0 .4rem;font-weight:700}
        input{width:100%;padding:.8rem;border:1px solid #cbd5e1;border-radius:.5rem;font:inherit;letter-spacing:.15em}
        input:focus{outline:3px solid #bfdbfe;border-color:#0052cc}
        button{width:100%;margin-top:1.25rem;padding:.85rem;border:0;border-radius:.5rem;background:#0052cc;color:#fff;font:inherit;font-weight:700;cursor:pointer}
        button:hover{background:#003eaa}
        .error{margin:.75rem 0 0;color:#b91c1c;font-size:.9rem}
        .back{display:block;margin-top:1rem;text-align:center;color:#0052cc;text-decoration:none}
    </style>
</head>
<body>
<main>
    <div class="icon" aria-hidden="true">🔒</div>
    <h1>Belanja B2B</h1>
    <p>Masukkan kode aktivasi terbaru dari administrator untuk membuka katalog khusus mitra dan distributor.</p>
    <form action="<?php echo e(route('products.pbf.verify')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <label for="kode">Kode akses</label>
        <input id="kode" name="kode" type="password" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" autocomplete="one-time-code" aria-describedby="codeHint" required autofocus>
        <small id="codeHint">Masukkan kode angka 5 digit.</small>
        <?php $__errorArgs = ['kode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error" role="alert"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <button type="submit">Verifikasi &amp; Masuk</button>
    </form>
    <a class="back" href="<?php echo e(route('home')); ?>">Kembali ke website</a>
</main>
</body>
</html>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/products_pbf_gate.blade.php ENDPATH**/ ?>