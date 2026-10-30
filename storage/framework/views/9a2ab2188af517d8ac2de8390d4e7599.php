

<?php $__env->startSection('title', 'File KUNJUNGAN - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .kunjungan-page{max-width:1000px;margin:0 auto;padding:calc(var(--navbar-height, 65px) + 1.5rem) 1rem 3rem;color:#172b4d}
    .kunjungan-card{padding:clamp(1rem,3vw,2rem);border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 12px 32px #0f172a0d}
    .kunjungan-header,.kunjungan-toolbar,.kunjungan-file{display:flex;align-items:center;justify-content:space-between;gap:1rem}
    .kunjungan-header{margin-bottom:1.25rem}
    .kunjungan-header h1{margin:0;color:#10243e;font-size:1.6rem}
    .kunjungan-header p{margin:.35rem 0 0;color:#52657e}
    .kunjungan-form{display:grid;gap:.65rem;margin:1.25rem 0;padding:1rem;border:1px dashed #94a3b8;border-radius:12px;background:#f8fafc}
    .kunjungan-form label{font-weight:700;color:#334155}
    .kunjungan-form input[type=file]{width:100%;padding:.65rem;border:1px solid #cbd5e1;border-radius:8px;background:#fff}
    .kunjungan-note{margin:0;color:#64748b;font-size:.85rem}
    .kunjungan-button{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;padding:.65rem .9rem;border:0;border-radius:8px;background:#08734f;color:#fff;font:inherit;font-weight:800;text-decoration:none;cursor:pointer;white-space:nowrap}
    .kunjungan-button:hover{background:#065f46;color:#fff}
    .kunjungan-button-secondary{background:#475569}
    .kunjungan-search{display:flex;flex-wrap:wrap;gap:.55rem;margin:1.35rem 0}
    .kunjungan-search input,.kunjungan-search select{flex:1 1 200px;min-width:0;padding:.7rem;border:1px solid #cbd5e1;border-radius:8px;background:#fff;font:inherit}
    .kunjungan-message{margin:.75rem 0;padding:.7rem .85rem;border:1px solid #bbf7d0;border-radius:9px;background:#f0fdf4;color:#166534}
    .kunjungan-error{margin:.75rem 0;padding:.7rem .85rem;border:1px solid #fecaca;border-radius:9px;background:#fef2f2;color:#991b1b}
    .kunjungan-files{display:grid;gap:.6rem}
    .kunjungan-file{padding:.75rem;border:1px solid #e2e8f0;border-radius:10px}
    .kunjungan-file-info{min-width:0}
    .kunjungan-file-actions{display:flex;flex-wrap:wrap;gap:.5rem}
    .kunjungan-file-name{display:block;overflow-wrap:anywhere;font-weight:750;color:#10243e}
    .kunjungan-file-meta{display:block;margin-top:.2rem;color:#64748b;font-size:.82rem}
    .kunjungan-empty{padding:1.25rem;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;text-align:center}
    .kunjungan-pagination{margin-top:1rem}
    @media(max-width:600px){.kunjungan-header,.kunjungan-file{align-items:flex-start;flex-direction:column}.kunjungan-toolbar{width:100%}.kunjungan-toolbar .kunjungan-button{flex:1}}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="kunjungan-page">
    <section class="kunjungan-card">
        <header class="kunjungan-header">
            <div>
                <h1>Arsip File KUNJUNGAN</h1>
                <p>Unggah, cari, urutkan, lihat, dan unduh file.</p>
            </div>
            <form method="POST" action="<?php echo e(route('kunjungan.leave')); ?>">
                <?php echo csrf_field(); ?>
                <button class="kunjungan-button kunjungan-button-secondary" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Keluar</button>
            </form>
        </header>

        <?php if(session('success')): ?><p class="kunjungan-message" role="status"><?php echo e(session('success')); ?></p><?php endif; ?>
        <?php if(session('error')): ?><p class="kunjungan-error" role="alert"><?php echo e(session('error')); ?></p><?php endif; ?>

        <form class="kunjungan-form" action="<?php echo e(route('kunjungan.files.upload')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <label for="kunjunganFiles">Pilih file untuk diunggah</label>
            <input id="kunjunganFiles" name="files[]" type="file" multiple required>
            <p class="kunjungan-note">Maksimal 5 file sekaligus, ukuran tiap file maksimal 20 MB.</p>
            <?php $__errorArgs = ['files'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="kunjungan-error" role="alert"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php $__currentLoopData = $errors->get('files.*'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fileErrors): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $fileErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fileError): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="kunjungan-error" role="alert"><?php echo e($fileError); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <button class="kunjungan-button" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Unggah file</button>
        </form>

        <form class="kunjungan-search" action="<?php echo e(route('kunjungan.room')); ?>" method="GET">
            <input name="search" type="search" value="<?php echo e($search); ?>" maxlength="255" placeholder="Cari nama file..." aria-label="Cari nama file">
            <select name="sort" aria-label="Urutkan file">
                <option value="newest" <?php if($sort === 'newest'): echo 'selected'; endif; ?>>Terbaru ke terlama</option>
                <option value="oldest" <?php if($sort === 'oldest'): echo 'selected'; endif; ?>>Terlama ke terbaru</option>
                <option value="name_asc" <?php if($sort === 'name_asc'): echo 'selected'; endif; ?>>Nama A–Z</option>
                <option value="name_desc" <?php if($sort === 'name_desc'): echo 'selected'; endif; ?>>Nama Z–A</option>
            </select>
            <button class="kunjungan-button" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        </form>

        <div class="kunjungan-toolbar">
            <strong><?php echo e($files->total()); ?> file ditemukan</strong>
        </div>
        <section class="kunjungan-files" aria-label="Daftar file KUNJUNGAN">
            <?php $__empty_1 = true; $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="kunjungan-file">
                    <div class="kunjungan-file-info">
                        <span class="kunjungan-file-name"><?php echo e($file->original_name); ?></span>
                        <span class="kunjungan-file-meta">
                            <?php echo e(number_format($file->size_bytes / 1_048_576, 2, ',', '.')); ?> MB
                            · <?php echo e($file->created_at->timezone(config('kunjungan.timezone'))->format('d-m-Y H:i')); ?>

                        </span>
                    </div>
                    <div class="kunjungan-file-actions">
                        <?php if(strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf' && strtolower((string) $file->mime_type) === 'application/pdf'): ?>
                            <a class="kunjungan-button kunjungan-button-secondary" href="<?php echo e(route('kunjungan.files.preview', $file)); ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-eye"></i> Lihat PDF</a>
                        <?php endif; ?>
                        <a class="kunjungan-button" href="<?php echo e(route('kunjungan.files.download', $file)); ?>"><i class="fa-solid fa-download"></i> Download</a>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="kunjungan-empty">Belum ada file<?php echo e($search !== '' ? ' yang cocok dengan pencarian ini' : ''); ?>.</p>
            <?php endif; ?>
        </section>
        <?php if($files->hasPages()): ?><div class="kunjungan-pagination"><?php echo e($files->links()); ?></div><?php endif; ?>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/kunjungan/room.blade.php ENDPATH**/ ?>