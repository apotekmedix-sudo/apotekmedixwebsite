

<?php $__env->startSection('title', 'Pratinjau File - Ruang GASS'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .gass-preview { max-width: 1180px; margin: 0 auto; padding: calc(var(--navbar-height, 65px) + 1.5rem) 1rem 3rem; }
    .gass-preview-card { overflow: hidden; background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; box-shadow: 0 12px 36px rgba(31, 41, 55, .1); }
    .gass-preview-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
    .gass-preview-title { min-width: 0; }
    .gass-preview-title h1 { color: #1565c0; font-size: clamp(1.15rem, 3vw, 1.65rem); overflow-wrap: anywhere; }
    .gass-preview-title p { margin-top: .25rem; color: #64748b; font-size: .9rem; }
    .gass-preview-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .gass-preview-button { display: inline-flex; align-items: center; justify-content: center; gap: .45rem; padding: .7rem 1rem; border: 0; border-radius: 10px; background: #1565c0; color: #fff; font-weight: 700; text-decoration: none; cursor: pointer; }
    .gass-preview-button:hover { background: #0d47a1; }
    .gass-preview-button-secondary { background: #475569; }
    .gass-preview-button-danger { background: #b91c1c; }
    .gass-preview-body { min-height: 420px; padding: clamp(1rem, 3vw, 2rem); background: #f1f5f9; }
    .gass-preview-stage { display: flex; min-height: 380px; align-items: center; justify-content: center; overflow: auto; padding: 1rem; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; }
    .gass-preview-stage img { display: block; max-width: 100%; max-height: 72vh; width: auto; height: auto; object-fit: contain; }
    .gass-preview-stage video { display: block; max-width: 100%; max-height: 72vh; width: min(100%, 960px); background: #0f172a; }
    .gass-preview-stage audio { width: min(100%, 680px); }
    .gass-preview-frame { display: block; width: 100%; height: min(75vh, 900px); border: 0; background: #fff; }
    .gass-preview-text { width: 100%; max-height: 70vh; overflow: auto; white-space: pre-wrap; overflow-wrap: anywhere; color: #1e293b; font: .95rem/1.6 ui-monospace, Consolas, monospace; }
    .gass-office { display: block; width: 100%; overflow: auto; }
    .gass-office-message { margin: .25rem 0 1rem; color: #64748b; }
    .gass-sheet-tabs { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
    .gass-sheet-tab { padding: .5rem .8rem; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: #334155; cursor: pointer; }
    .gass-sheet-tab.active { border-color: #1565c0; background: #eaf3ff; color: #0d47a1; }
    .gass-sheet-table { width: 100%; border-collapse: collapse; background: #fff; }
    .gass-sheet-table th, .gass-sheet-table td { min-width: 100px; max-width: 360px; padding: .6rem .75rem; border: 1px solid #dbe3ed; text-align: left; white-space: pre-wrap; overflow-wrap: anywhere; vertical-align: top; }
    .gass-sheet-table th { position: sticky; top: 0; background: #eaf3ff; color: #1e3a5f; }
    .gass-docx { width: min(100%, 850px); min-height: 500px; margin: 0 auto; padding: clamp(1.25rem, 5vw, 3rem); box-shadow: 0 2px 12px rgba(15, 23, 42, .1); color: #1e293b; line-height: 1.7; overflow-wrap: anywhere; }
    .gass-docx img { max-width: 100%; height: auto; }
    .gass-docx table { max-width: 100%; border-collapse: collapse; }
    .gass-docx td, .gass-docx th { padding: .4rem; border: 1px solid #cbd5e1; }
    .gass-preview-status { padding: 1rem; color: #475569; text-align: center; }
    .gass-preview-status.error { color: #b91c1c; }
    @media (max-width: 600px) {
        .gass-preview-header { padding: 1rem; }
        .gass-preview-actions { width: 100%; }
        .gass-preview-button { flex: 1 1 auto; }
        .gass-preview-body { min-height: 320px; padding: .6rem; }
        .gass-preview-stage { min-height: 300px; padding: .5rem; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="gass-preview">
    <article class="gass-preview-card" data-gass-preview
        data-preview-type="<?php echo e($previewType); ?>"
        data-content-url="<?php echo e($contentUrl); ?>">
        <header class="gass-preview-header">
            <div class="gass-preview-title">
                <h1><?php echo e($file->original_name); ?></h1>
                <p><?php echo e(strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION))); ?> · <?php echo e(number_format($file->size_bytes / 1048576, 2)); ?> MB</p>
            </div>
            <nav class="gass-preview-actions" aria-label="Aksi file">
                <a class="gass-preview-button" href="<?php echo e($downloadUrl); ?>"><i class="fa-solid fa-download"></i> Unduh</a>
                <?php if (! ($shared)): ?>
                    <a class="gass-preview-button gass-preview-button-secondary" href="<?php echo e(route('gass.room')); ?>"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
                    <form action="<?php echo e(route('gass.files.destroy', $file)); ?>" method="POST" onsubmit="return confirm('Hapus file ini? Tindakan ini tidak dapat dikembalikan.');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="gass-preview-button gass-preview-button-danger" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
                    </form>
                <?php endif; ?>
            </nav>
        </header>
        <div class="gass-preview-body">
            <div class="gass-preview-stage" data-preview-stage>
                <?php if($previewType === 'image'): ?>
                    <img src="<?php echo e($contentUrl); ?>" alt="<?php echo e($file->original_name); ?>">
                <?php elseif($previewType === 'video'): ?>
                    <video src="<?php echo e($contentUrl); ?>" controls playsinline preload="metadata">Browser tidak mendukung pemutar video.</video>
                <?php elseif($previewType === 'audio'): ?>
                    <audio src="<?php echo e($contentUrl); ?>" controls preload="metadata">Browser tidak mendukung pemutar audio.</audio>
                <?php elseif($previewType === 'pdf'): ?>
                    <iframe class="gass-preview-frame" src="<?php echo e($contentUrl); ?>" title="Pratinjau <?php echo e($file->original_name); ?>"></iframe>
                <?php elseif(in_array($previewType, ['text', 'excel', 'word'], true)): ?>
                    <div class="gass-office" data-office-preview></div>
                <?php else: ?>
                    <div class="gass-preview-status">Pratinjau belum tersedia untuk format ini. File tetap dapat diunduh.</div>
                <?php endif; ?>
            </div>
            <?php if(in_array($previewType, ['text', 'excel', 'word'], true)): ?>
                <p class="gass-preview-status" data-preview-status role="status">Memuat pratinjau...</p>
            <?php endif; ?>
        </div>
    </article>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php if(in_array($previewType, ['text', 'excel', 'word'], true)): ?>
        <?php echo app('Illuminate\Foundation\Vite')('resources/js/gass-preview.js'); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/gass/preview.blade.php ENDPATH**/ ?>