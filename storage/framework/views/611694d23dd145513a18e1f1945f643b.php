

<?php $__env->startSection('title', 'Riwayat Chat Konsultasi - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', 'Riwayat Chat Konsultasi'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .consult-chat-admin{display:grid;gap:1rem;color:#1e293b}
    .consult-chat-card{padding:1rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}
    .consult-chat-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap}
    .consult-chat-heading h2{margin:0 0 .4rem;color:#172b4d;font-size:1.05rem}
    .consult-chat-meta{margin:0;color:#64748b;font-size:.85rem;line-height:1.6}
    .consult-chat-actions{display:flex;gap:.5rem;flex-wrap:wrap}
    .consult-chat-button{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem .75rem;border:0;border-radius:7px;background:#0755c8;color:#fff;font-size:.82rem;font-weight:800;text-decoration:none;cursor:pointer}
    .consult-chat-button.red{background:#b91c1c}
    .consult-chat-transcript{display:grid;gap:.65rem}
    .consult-chat-message{max-width:min(85%,720px);padding:.75rem .85rem;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;white-space:pre-wrap;overflow-wrap:anywhere}
    .consult-chat-message.doctor{justify-self:end;border-color:#bfdbfe;background:#eff6ff}
    .consult-chat-message.patient{justify-self:start}
    .consult-chat-message header{display:flex;justify-content:space-between;gap:1rem;margin-bottom:.35rem;color:#475569;font-size:.72rem;font-weight:800}
    .consult-chat-message p{margin:0;color:#1e293b;line-height:1.5}
    .consult-chat-empty{margin:0;padding:1rem;border:1px dashed #cbd5e1;border-radius:9px;color:#64748b;text-align:center}
    @media(max-width:600px){.consult-chat-message{max-width:100%;width:100%}.consult-chat-message header{flex-direction:column;gap:.15rem}}
</style>

<main class="consult-chat-admin">
    <section class="consult-chat-card consult-chat-heading">
        <div>
            <h2><?php echo e($consultation->patient_name); ?> · <?php echo e($consultation->doctorProfile->display_name); ?></h2>
            <p class="consult-chat-meta">
                Sesi selesai <?php echo e($consultation->completed_at?->format('d-m-Y H:i') ?? '-'); ?><br>
                <?php echo e($consultation->patient_phone); ?> · <?php echo e($consultation->patient_email); ?>

            </p>
        </div>
        <div class="consult-chat-actions">
            <a class="consult-chat-button" href="<?php echo e(route('admin.consultations.index', ['tab' => 'transactions'])); ?>">Kembali ke transaksi</a>
            <form method="POST" action="<?php echo e(route('admin.consultations.delete', $consultation)); ?>" onsubmit="return confirm('Hapus sesi dan seluruh riwayat chat ini secara permanen?')">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button class="consult-chat-button red" type="submit">Hapus sesi</button>
            </form>
        </div>
    </section>

    <section class="consult-chat-card">
        <h2 style="margin:0 0 .85rem;color:#172b4d;font-size:1rem">Riwayat percakapan</h2>
        <div class="consult-chat-transcript">
            <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="consult-chat-message <?php echo e($message->sender_type === 'doctor' ? 'doctor' : 'patient'); ?>">
                    <header>
                        <span><?php echo e($message->sender_type === 'doctor' ? $consultation->doctorProfile->display_name : $consultation->patient_name); ?></span>
                        <time datetime="<?php echo e($message->created_at?->toIso8601String()); ?>"><?php echo e($message->created_at?->format('d-m-Y H:i')); ?></time>
                    </header>
                    <p><?php echo e($message->body); ?></p>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="consult-chat-empty">Belum ada pesan pada sesi ini.</p>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/consultations/chat-history.blade.php ENDPATH**/ ?>