

<?php $__env->startSection('title', 'Tambah Produk - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', '➕ Tambah Produk'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .form-card { background:white; border-radius:0.75rem; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #f0f0f0; overflow:hidden; }
    .form-card-header { padding:1rem 1.5rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:0.6rem; }
    .form-card-header h3 { font-size:0.95rem; font-weight:700; color:#1f2937; margin:0; }
    .header-icon { width:32px; height:32px; background:#e3f2fd; border-radius:0.4rem; display:flex; align-items:center; justify-content:center; color:#1E88E5; font-size:0.9rem; }
    .form-body { padding:1.5rem; display:flex; flex-direction:column; gap:1rem; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .form-group { margin-bottom:0; }
    .form-label { display:block; font-size:0.8rem; font-weight:700; color:#374151; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.04em; }
    .form-label .req { color:#ef4444; margin-left:2px; }
    .form-input { width:100%; padding:0.6rem 0.85rem; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.9rem; color:#1f2937; background:#fafafa; transition:all 0.2s; }
    .form-input:focus { outline:none; border-color:#1E88E5; background:white; box-shadow:0 0 0 3px rgba(30,136,229,0.08); }
    .form-input.is-invalid { border-color:#ef4444; }
    .form-error { font-size:0.78rem; color:#ef4444; margin-top:0.3rem; display:flex; align-items:center; gap:0.3rem; }
    .upload-zone { border:2px dashed #d1d5db; border-radius:0.6rem; padding:1.75rem 1rem; text-align:center; background:#fafafa; cursor:pointer; transition:all 0.2s; }
    .upload-zone:hover, .upload-zone.drag-over { border-color:#1E88E5; background:#f0f7ff; }
    .upload-zone .upload-icon { font-size:2rem; margin-bottom:0.5rem; }
    .upload-zone p { font-size:0.85rem; color:#6b7280; margin:0 0 0.75rem; }
    .upload-zone small { font-size:0.75rem; color:#9ca3af; display:block; margin-top:0.5rem; }
    .btn-choose { display:inline-flex; align-items:center; gap:0.35rem; padding:0.45rem 1rem; background:white; border:1px solid #d1d5db; border-radius:0.4rem; font-size:0.82rem; font-weight:600; color:#374151; cursor:pointer; transition:all 0.2s; }
    .btn-choose:hover { background:#f3f4f6; border-color:#9ca3af; }
    .img-preview-wrap { margin-top:0.75rem; display:none; border-radius:0.5rem; overflow:hidden; border:1px solid #e5e7eb; position:relative; }
    .img-preview-wrap img { width:100%; max-height:220px; object-fit:contain; display:block; background:#f9fafb; }
    .img-preview-label { position:absolute; top:0.5rem; left:0.5rem; background:rgba(0,0,0,0.55); color:white; font-size:0.7rem; font-weight:600; padding:0.2rem 0.5rem; border-radius:0.3rem; }
    .form-footer { padding:1rem 1.5rem; border-top:1px solid #f3f4f6; display:flex; gap:0.6rem; align-items:center; }
    .btn-save { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.5rem; background:#1E88E5; color:white; border:none; border-radius:0.5rem; font-size:0.9rem; font-weight:700; cursor:pointer; transition:all 0.2s; }
    .btn-save:hover { background:#1565C0; transform:translateY(-1px); }
    .btn-cancel { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.25rem; background:white; color:#6b7280; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.9rem; font-weight:600; text-decoration:none; transition:all 0.2s; }
    .btn-cancel:hover { background:#f9fafb; color:#374151; }
    .two-col-layout { display:grid; grid-template-columns:1fr; gap:1.25rem; align-items:start; }
    /* Kategori selector cards */
    .kat-selector { display:grid; grid-template-columns:repeat(3,1fr); gap:0.6rem; }
    .kat-card { border:2px solid #e5e7eb; border-radius:0.6rem; padding:0.75rem 0.5rem; text-align:center; cursor:pointer; transition:all 0.2s; background:white; }
    .kat-card:hover { border-color:#1E88E5; }
    .kat-card.selected { border-color:#1E88E5; background:#e3f2fd; }
    .kat-card input[type=radio] { display:none; }
    .kat-card .kat-icon { font-size:1.5rem; display:block; margin-bottom:0.3rem; }
    .kat-card .kat-label { font-size:0.72rem; font-weight:700; color:#374151; line-height:1.3; }
    .kat-card.selected .kat-label { color:#1565C0; }
    @media (max-width:600px) { .form-grid { grid-template-columns:1fr; } .form-body { padding:1rem; } .form-footer { padding:1rem; } .kat-selector { grid-template-columns:1fr 1fr; } }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#9ca3af;margin-bottom:1.25rem;">
    <a href="<?php echo e(route('admin.produk.index')); ?>" style="color:#1E88E5;text-decoration:none;font-weight:600;">🛒 Produk Kami</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
    <span style="color:#374151;font-weight:600;">Tambah Produk</span>
</div>

<form action="<?php echo e(route('admin.produk.store')); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div class="two-col-layout">

        <div class="form-card">
            <div class="form-card-header">
                <div class="header-icon"><i class="fa-solid fa-circle-info"></i></div>
                <h3>Informasi Produk</h3>
            </div>
            <div class="form-body">

                <div class="form-group">
                    <label class="form-label">Principle / Pabrik <span class="req">*</span></label>
                    <input type="text" name="kategori" class="form-input <?php echo e($errors->has('kategori') ? 'is-invalid' : ''); ?>"
                           placeholder="Contoh: KIMIA FARMA / WARDAH / OMRON" value="<?php echo e(old('kategori')); ?>" required>
                    <?php $__errorArgs = ['kategori'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Logo / Penanda</label>
                        <input type="text" name="logo" class="form-input" placeholder="Contoh: HIJAU" value="<?php echo e(old('logo')); ?>">
                        <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Golongan</label>
                        <input type="text" name="golongan" class="form-input" placeholder="Contoh: Obat Bebas" value="<?php echo e(old('golongan')); ?>">
                        <?php $__errorArgs = ['golongan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Produk <span class="req">*</span></label>
                    <input type="text" name="nama_obat" class="form-input <?php echo e($errors->has('nama_obat') ? 'is-invalid' : ''); ?>"
                           placeholder="Contoh: Paracetamol 500mg" value="<?php echo e(old('nama_obat')); ?>" required>
                    <?php $__errorArgs = ['nama_obat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <?php echo $__env->make('admin.partials.product-image-uploader', ['medicine' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Satuan Kemasan</label>
                        <input type="text" id="product-package-unit" name="satuan" class="form-input" placeholder="Contoh: BOX, BOTOL, TUBE" value="<?php echo e(old('satuan')); ?>">
                        <?php $__errorArgs = ['satuan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kemasan</label>
                        <input type="text" name="kemasan" class="form-input" placeholder="Contoh: 10 blister @ 10 tablet" value="<?php echo e(old('kemasan')); ?>">
                        <?php $__errorArgs = ['kemasan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Sediaan</label>
                    <input type="text" name="sediaan"
                           class="form-input <?php echo e($errors->has('sediaan') ? 'is-invalid' : ''); ?>"
                           placeholder="Contoh: fls, box, tube, pcs atau ketik bebas"
                           value="<?php echo e(old('sediaan')); ?>">
                    <?php $__errorArgs = ['sediaan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Modal (Rp)</label>
                        <input type="number" name="harga_modal" class="form-input" placeholder="4000" step="1" min="0" value="<?php echo e(old('harga_modal')); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Jual Internal (Rp) <span class="req">*</span></label>
                        <input type="number" name="harga_internal" class="form-input <?php echo e($errors->has('harga_internal') ? 'is-invalid' : ''); ?>"
                               placeholder="5000" step="1" min="0" value="<?php echo e(old('harga_internal', old('harga'))); ?>">
                        <?php $__errorArgs = ['harga_internal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Jual B2B (Rp)</label>
                        <input type="number" name="harga_grosir" class="form-input" step="1" min="0" value="<?php echo e(old('harga_grosir')); ?>">
                        <?php $__errorArgs = ['harga_grosir'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Jual B2C (Rp)</label>
                        <input type="number" name="harga_retail" class="form-input" step="1" min="0" value="<?php echo e(old('harga_retail')); ?>">
                        <?php $__errorArgs = ['harga_retail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stok (<span data-stock-unit-label><?php echo e(old('satuan') ?: 'kemasan'); ?></span>) <span class="req">*</span></label>
                        <input type="number" name="stok" class="form-input <?php echo e($errors->has('stok') ? 'is-invalid' : ''); ?>"
                               placeholder="100" min="0" step="1" value="<?php echo e(old('stok')); ?>" required>
                        <small>Jumlah mengikuti satuan kemasan di atas, misalnya box, botol, atau tube.</small>
                        <?php $__errorArgs = ['stok'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <?php echo $__env->make('admin.partials.packaging-fields', ['medicine' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Komposisi</label>
                    <textarea name="komposisi" class="form-input" rows="3"
                              placeholder="Contoh: Paracetamol 500 mg, Aqua, Glycerin..."><?php echo e(old('komposisi')); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Indikasi / Kegunaan</label>
                    <textarea name="indikasi" class="form-input" rows="3"
                              placeholder="Contoh: Meredakan demam dan nyeri ringan hingga sedang..."><?php echo e(old('indikasi')); ?></textarea>
                </div>

            </div>
            <div class="form-footer">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Produk
                </button>
                <a href="<?php echo e(route('admin.produk.index')); ?>" class="btn-cancel">
                    <i class="fa-solid fa-xmark"></i> Batal
                </a>
            </div>
        </div>

    </div>
</form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.getElementById('product-package-unit')?.addEventListener('input', function () {
        const label = this.value.trim() || 'kemasan';
        document.querySelectorAll('[data-stock-unit-label]').forEach((element) => {
            element.textContent = label;
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\admin\produk\create.blade.php ENDPATH**/ ?>