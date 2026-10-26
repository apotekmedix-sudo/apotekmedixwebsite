<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Apotek Medix</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/medix-public-theme.css')); ?>">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f2937;
        }

        .login-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px;
            padding: 3rem;
            overflow: hidden;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #1E88E5;
        }

        .login-header p {
            color: #6b7280;
            font-size: 0.975rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1f2937;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #1E88E5;
            box-shadow: 0 0 0 3px rgba(30,136,229,0.1);
        }

        .password-field {
            position: relative;
        }

        .password-field .form-control {
            padding-right: 5.5rem;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 0.5rem;
            transform: translateY(-50%);
            border: 0;
            border-radius: 0.35rem;
            padding: 0.45rem 0.55rem;
            background: transparent;
            color: #0052cc;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }

        .toggle-password:focus-visible {
            outline: 2px solid #0052cc;
            outline-offset: 2px;
        }

        .form-errors {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(30,136,229,0.3);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30,136,229,0.4);
        }

        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .login-footer a {
            color: #1E88E5;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-error {
            background: #fee2e2;
            color: #7f1d1d;
            border-left-color: #ef4444;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left-color: #3b82f6;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .login-header h1 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body class="public-theme public-theme-standalone">
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Apotek Medix" style="max-height: 100px; max-width: 150px; object-fit: contain;">
            </div>
            <h1>Apotek Medix Admin</h1>
            <p>Masuk ke panel administrasi</p>
        </div>

        <?php if($message = Session::get('error')): ?>
            <div class="alert alert-error">
                ✕ <?php echo e($message); ?>

            </div>
        <?php endif; ?>

        <?php if($message = Session::get('success')): ?>
            <div class="alert alert-info">
                ✓ <?php echo e($message); ?>

            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('login.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="admin@apotekmedix.com"
                    required
                    value="<?php echo e(old('email')); ?>"
                >
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="form-errors"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="password-field">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="••••••••"
                        value="<?php echo e(old('password')); ?>"
                        autocomplete="current-password"
                        required
                    >
                    <button class="toggle-password" type="button" aria-label="Tampilkan password" aria-pressed="false" aria-controls="password">Lihat</button>
                </div>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="form-errors"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="btn-login">Masuk</button>
        </form>

        <div class="login-footer">
            <p style="margin-top: 1.5rem;">
                <a href="<?php echo e(route('home')); ?>">← Kembali ke home</a>
            </p>
        </div>
    </div>
    <script>
        document.querySelector('.toggle-password').addEventListener('click', (event) => {
            const button = event.currentTarget;
            const password = document.getElementById(button.getAttribute('aria-controls'));
            const isVisible = password.type === 'text';
            password.type = isVisible ? 'password' : 'text';
            button.textContent = isVisible ? 'Lihat' : 'Sembunyikan';
            button.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
            button.setAttribute('aria-pressed', String(!isVisible));
        });
    </script>
</body>
</html>
<?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/admin/login.blade.php ENDPATH**/ ?>