

<?php $__env->startSection('title', 'Aktivasi Kode Belanja - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', 'Aktivasi Kode Belanja'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .activation-intro{color:#64748b;margin-top:-.75rem;line-height:1.6}
    .activation-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}
    .activation-card{padding:20px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
    .activation-icon{display:grid;width:44px;height:44px;place-items:center;border-radius:12px;background:#eff6ff;color:#0052cc;font-size:20px}
    .activation-card h2{margin:14px 0 6px;font-size:18px}
    .activation-card p{color:#64748b;line-height:1.55;font-size:13px}
    .activation-status{display:inline-block;margin:4px 0 14px;padding:5px 9px;border-radius:999px;background:#fff7ed;color:#c2410c;font-size:11px;font-weight:700}
    .activation-status.active{background:#dcfce7;color:#15803d}
    .activation-expiry{display:block;margin:-8px 0 14px;color:#64748b;font-size:12px}
    .activation-card button{border:0;border-radius:8px;padding:10px 13px;background:#0052cc;color:#fff;font-weight:700;cursor:pointer}
    .code-table{width:100%;border-collapse:collapse;margin-top:20px}
    .code-table th,.code-table td{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:middle}
    .code-table th{background:#f8fafc;color:#475569;font-size:12px}
    .code-value{font:700 16px ui-monospace,SFMono-Regular,Consolas,monospace;letter-spacing:.12em;color:#0f172a}
    .code-edit{display:flex;align-items:center;gap:6px;min-width:220px}
    .code-edit input{width:100px;padding:7px;border:1px solid #cbd5e1;border-radius:6px;letter-spacing:.1em}
    .code-edit button,.code-delete{border:0;border-radius:7px;padding:8px 10px;font-weight:700;cursor:pointer}
    .code-edit button{background:#eaf2ff;color:#0052cc}.code-delete{background:#fff1f2;color:#be123c}
    .code-actions form{margin:0}.code-actions{display:flex;gap:6px}
    .code-empty{text-align:center;padding:16px;color:#64748b}
    .activation-warning{margin-top:16px;padding:12px 14px;border-radius:9px;background:#fffbeb;color:#92400e;font-size:13px;line-height:1.5}
    .activation-success{margin-bottom:16px;padding:12px 14px;border-radius:9px;background:#dcfce7;color:#166534}
    .activation-error{margin-bottom:16px;padding:12px 14px;border-radius:9px;background:#fee2e2;color:#991b1b}
    @media(max-width:700px){.code-table{min-width:720px}.code-table-wrap{overflow-x:auto}}
</style>

<div class="card">
    <div class="card-title">Aktivasi kode akses</div>
    <p class="activation-intro">Buat kode angka 5 digit untuk akses katalog B2B dan produk khusus B2C. Kode berlaku 30 hari sejak dibuat dan kode baru langsung menggantikan kode sebelumnya.</p>

    <?php if(session('success')): ?><div class="activation-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="activation-error" role="alert"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="activation-grid">
        <?php $__currentLoopData = [
            'b2b' => ['title' => 'Belanja B2B', 'description' => 'Kode angka 5 digit untuk membuka katalog khusus mitra dan distributor.', 'icon' => 'fa-boxes-stacked'],
            'b2c' => ['title' => 'Produk Khusus B2C', 'description' => 'Kode angka 5 digit untuk membuka produk B2C dengan logo merah.', 'icon' => 'fa-unlock-keyhole'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel => $details): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="activation-card">
                <div class="activation-icon"><i class="fa-solid <?php echo e($details['icon']); ?>" aria-hidden="true"></i></div>
                <h2><?php echo e($details['title']); ?></h2>
                <p><?php echo e($details['description']); ?></p>
                <?php
                    $channelCode = $codes[$channel] ?? null;
                    $channelReadableCode = $channelCode?->readCode();
                    $channelNeedsRegeneration = $channelCode !== null
                        && $channelCode->expires_at?->isFuture() === true
                        && $channelReadableCode === null;
                    $codeIsActive = $channelCode !== null
                        && $channelCode->expires_at?->isFuture() === true
                        && $channelReadableCode !== null;
                ?>
                <span class="activation-status <?php echo e($codeIsActive ? 'active' : ''); ?>">
                    <?php echo e($codeIsActive ? 'Kode aktif' : ($channelNeedsRegeneration ? 'Kode tidak dapat dibaca — buat ulang' : (isset($codes[$channel]) ? 'Kode lama / kedaluwarsa — perbarui' : 'Kode belum dibuat'))); ?>

                </span>
                <?php if($codeIsActive): ?>
                    <span class="activation-expiry">Berlaku sampai <?php echo e($codes[$channel]->expires_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s')); ?> WIB</span>
                <?php endif; ?>
                <form action="<?php echo e(route('admin.access-codes.generate', $channel)); ?>" method="POST" onsubmit="return confirm('<?php echo e($codeIsActive ? 'Kode lama langsung tidak berlaku. Buat kode baru 5 digit dengan masa berlaku 30 hari?' : 'Buat kode aktivasi 5 digit yang berlaku 30 hari?'); ?>')">
                    <?php echo csrf_field(); ?>
                    <button type="submit"><?php echo e(isset($codes[$channel]) ? 'Buat Ulang Kode 5 Digit' : 'Buat Kode 5 Digit'); ?></button>
                </form>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="card-title" style="margin-top:24px;">Daftar Kode Akses</div>
    <p style="color:#64748b;">Kode aktif selalu tampil di tabel ini. Edit kode memperbarui masa berlakunya selama 30 hari; hapus atau ubah kode langsung membatalkan sesi lama.</p>
    <div class="code-table-wrap">
        <table class="code-table">
            <thead><tr><th>Kanal</th><th>Kode</th><th>Status</th><th>Berlaku sampai</th><th>Terakhir diperbarui</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $readableCode = $code->readCode();
                        $isUnreadableCode = $readableCode === null && $code->expires_at?->isFuture() === true;
                        $codeIsActive = $code->expires_at?->isFuture() === true && $readableCode !== null;
                    ?>
                    <tr>
                        <td><strong><?php echo e(['b2b' => 'Belanja B2B', 'b2c' => 'Produk Khusus B2C'][$code->channel] ?? 'Belanja'); ?></strong></td>
                        <td><code class="code-value"><?php echo e($readableCode ?: 'Tidak dapat ditampilkan — buat ulang'); ?></code></td>
                        <td><span class="activation-status <?php echo e($codeIsActive ? 'active' : ''); ?>"><?php echo e($codeIsActive ? 'Aktif' : ($isUnreadableCode ? 'Tidak dapat dibaca — buat ulang' : 'Kedaluwarsa')); ?></span></td>
                        <td><?php echo e($code->expires_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') ? $code->expires_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') . ' WIB' : '-'); ?></td>
                        <td><?php echo e($code->updated_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') ? $code->updated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') . ' WIB' : '-'); ?></td>
                        <td>
                            <div class="code-actions">
                                <form class="code-edit" action="<?php echo e(route('admin.access-codes.update', $code)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <label class="sr-only" for="code-<?php echo e($code->id); ?>">Kode baru untuk <?php echo e($code->channel); ?></label>
                                    <input id="code-<?php echo e($code->id); ?>" name="code" type="text" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" placeholder="5 digit" required>
                                    <button type="submit">Edit</button>
                                </form>
                                <form action="<?php echo e(route('admin.access-codes.destroy', $code)); ?>" method="POST" onsubmit="return confirm('Hapus kode akses ini? Kode tidak lagi bisa dipakai untuk login.')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="code-delete" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td class="code-empty" colspan="6">Belum ada kode akses. Buat kode melalui tombol di atas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="activation-warning">
        <strong>Catatan keamanan:</strong> kode ditampilkan untuk administrator dan disimpan terenkripsi di database. Gunakan hanya untuk kanal yang berwenang. Kode dan sesi akses berlaku sampai 30 hari sejak kode dibuat.
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/admin/access-codes/index.blade.php ENDPATH**/ ?>