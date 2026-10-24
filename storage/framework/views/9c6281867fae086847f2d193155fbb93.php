<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Kasir - Apotek Medix</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:1rem;background:#f8fafc;color:#1e293b;font-family:Arial,sans-serif}
        main{width:min(100%,420px);padding:2rem;border:1px solid #e2e8f0;border-radius:1rem;background:#fff;box-shadow:0 18px 48px #0f172a18}
        h1{margin:0 0 .5rem;color:#0f172a}p{color:#64748b}label{display:block;margin:1rem 0 .4rem;font-weight:700}
        input{width:100%;padding:.75rem;border:1px solid #cbd5e1;border-radius:.5rem;font:inherit}
        .password-field{position:relative}.password-field input{padding-right:5.5rem}
        .toggle-password{position:absolute;right:.4rem;top:50%;transform:translateY(-50%);padding:.45rem .55rem;border:0;border-radius:.35rem;background:transparent;color:#0052cc;font-size:.8rem;font-weight:700;cursor:pointer}
        .toggle-password:focus-visible{outline:2px solid #0052cc;outline-offset:2px}
        button{width:100%;margin-top:1.25rem;padding:.8rem;border:0;border-radius:.5rem;background:#0f172a;color:white;font:inherit;font-weight:700;cursor:pointer}
        .error{color:#b91c1c}.back{display:block;margin-top:1rem;text-align:center;color:#0e7490}
    </style>
</head>
<body>
<main>
    <h1>Masuk Kasir</h1>
    <p>Masuk dengan akun kasir untuk menerima pembayaran pesanan.</p>
    <?php if(session('error')): ?><p class="error"><?php echo e(session('error')); ?></p><?php endif; ?>
    <?php if($errors->any()): ?><p class="error"><?php echo e($errors->first()); ?></p><?php endif; ?>
    <form action="<?php echo e(route('cashier.login.post')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <label for="email">Email kasir</label>
        <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" autocomplete="username" required>
        <label for="password">Kata sandi</label>
        <div class="password-field">
            <input id="password" type="password" name="password" value="<?php echo e(old('password')); ?>" autocomplete="current-password" required>
            <button class="toggle-password" type="button" aria-label="Tampilkan kata sandi" aria-pressed="false" aria-controls="password">Lihat</button>
        </div>
        <button type="submit">Masuk</button>
    </form>
    <a class="back" href="<?php echo e(route('home')); ?>">Kembali ke website</a>
</main>
<script>
    document.querySelector('.toggle-password').addEventListener('click', (event) => {
        const button = event.currentTarget;
        const password = document.getElementById(button.getAttribute('aria-controls'));
        const isVisible = password.type === 'text';
        password.type = isVisible ? 'password' : 'text';
        button.textContent = isVisible ? 'Lihat' : 'Sembunyikan';
        button.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        button.setAttribute('aria-pressed', String(!isVisible));
    });
</script>
</body>
</html>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/cashier/login.blade.php ENDPATH**/ ?>