<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Room Konsultasi - Apotek Medix'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#172b4d;font-family:Arial,sans-serif}
        .room-topbar{padding:.9rem 1rem;background:#0f766e;color:#fff;font-weight:800}
        .room-main{max-width:980px;margin:0 auto;padding:1.25rem 1rem 3rem}
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
<header class="room-topbar">Apotek Medix · Room Konsultasi</header>
<main class="room-main"><?php echo $__env->yieldContent('content'); ?></main>
<?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/layouts/consultation-room.blade.php ENDPATH**/ ?>