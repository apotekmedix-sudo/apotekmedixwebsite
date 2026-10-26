<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register - Apotek Medix</title>

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

        .register-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px;
            padding: 3rem;
            overflow: hidden;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .register-header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #1E88E5;
        }

        .register-header p {
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

        .form-errors {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .btn-register {
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

        .btn-register:hover {
            background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30,136,229,0.4);
        }

        .register-footer {
            margin-top: 1.5rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .register-footer a {
            color: #1E88E5;
            text-decoration: none;
            font-weight: 600;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .register-header h1 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="register-logo">
                <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Apotek Medix" style="max-height: 80px; max-width: 100%; object-fit: contain;">
            </div>
            <h1>Apotek Medix Admin</h1>
            <p>Daftar akun admin baru</p>
        </div>

        <form action="<?php echo e(route('register.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap</label>
                <input 
                    type="text" 
                    id="name"
                    name="name" 
                    class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="John Doe"
                    required
                    value="<?php echo e(old('name')); ?>"
                >
                <?php $__errorArgs = ['name'];
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
                    required
                >
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

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                <input 
                    type="password" 
                    id="password_confirmation"
                    name="password_confirmation" 
                    class="form-control"
                    placeholder="••••••••"
                    required
                >
            </div>

            <button type="submit" class="btn-register">Daftar</button>
        </form>

        <div class="register-footer">
            <p>Sudah punya akun?</p>
            <p><a href="<?php echo e(route('login')); ?>">Masuk ke admin panel</a></p>
            <p style="margin-top: 1.5rem;">
                <a href="<?php echo e(route('home')); ?>">← Kembali ke home</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\admin\register.blade.php ENDPATH**/ ?>