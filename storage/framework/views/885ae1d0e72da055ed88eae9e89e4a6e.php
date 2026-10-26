
<?php $__env->startSection('title', 'Banner Promo - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', '🖼️ Banner Promo Slideshow'); ?>

<?php $__env->startSection('styles'); ?>
<style>
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; gap:1rem; flex-wrap:wrap; }
.page-header h2 { font-size:1.1rem; font-weight:700; color:#1f2937; margin:0 0 0.2rem; }
.page-header p  { font-size:0.85rem; color:#6b7280; margin:0; }
.btn-add { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.25rem; background:#1E88E5; color:#fff; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; transition:all 0.2s; }
.btn-add:hover { background:#1565C0; color:#fff; transform:translateY(-1px); }

.banner-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:1.25rem; }
.banner-card { background:#fff; border-radius:1rem; overflow:hidden; border:1px solid #e5e7eb; box-shadow:0 1px 4px rgba(0,0,0,0.06); transition:box-shadow 0.2s; }
.banner-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.1); }
.banner-img { width:100%; height:160px; object-fit:cover; display:block; background:#e3f2fd; }
.banner-body { padding:1rem; }
.banner-title { font-size:0.95rem; font-weight:700; color:#1f2937; margin:0 0 0.45rem; }
.banner-title-empty { color:#9ca3af; font-style:italic; font-weight:600; }
.banner-meta  { display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.85rem; }
.badge-aktif   { padding:0.2rem 0.65rem; border-radius:20px; font-size:0.72rem; font-weight:700; background:#d1fae5; color:#065f46; }
.badge-nonaktif{ padding:0.2rem 0.65rem; border-radius:20px; font-size:0.72rem; font-weight:700; background:#fee2e2; color:#991b1b; }
.banner-actions { display:flex; gap:0.5rem; align-items:center; }
.btn-edit { display:inline-flex; align-items:center; gap:0.3rem; padding:0.4rem 0.85rem; background:#e3f2fd; color:#1565C0; border-radius:0.4rem; font-size:0.78rem; font-weight:600; text-decoration:none; transition:all 0.2s; border:none; cursor:pointer; }
.btn-edit:hover { background:#1E88E5; color:#fff; }
.btn-del  { display:inline-flex; align-items:center; gap:0.3rem; padding:0.4rem 0.85rem; background:#fee2e2; color:#991b1b; border-radius:0.4rem; font-size:0.78rem; font-weight:600; text-decoration:none; transition:all 0.2s; border:none; cursor:pointer; }
.btn-del:hover  { background:#ef4444; color:#fff; }
.btn-toggle { display:inline-flex; align-items:center; gap:0.3rem; padding:0.4rem 0.85rem; border-radius:0.4rem; font-size:0.78rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; }
.btn-toggle-on  { background:#fef3c7; color:#92400e; }
.btn-toggle-on:hover  { background:#f59e0b; color:#fff; }
.btn-toggle-off { background:#d1fae5; color:#065f46; }
.btn-toggle-off:hover { background:#10b981; color:#fff; }

.empty-state { text-align:center; padding:4rem 2rem; background:#fff; border-radius:1rem; border:1px solid #e5e7eb; }
.empty-state i { font-size:3rem; color:#d1d5db; display:block; margin-bottom:1rem; }
.empty-state p { color:#6b7280; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal-box { background:#fff; border-radius:1rem; padding:2rem; max-width:360px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.2); text-align:center; }
.modal-box h3 { font-size:1rem; font-weight:700; margin:0 0 0.5rem; }
.modal-box p  { font-size:0.875rem; color:#6b7280; margin:0 0 1.5rem; }
.modal-actions { display:flex; gap:0.6rem; justify-content:center; }
.btn-cancel { padding:0.6rem 1.5rem; background:#fff; color:#374151; border:1.5px solid #e5e7eb; border-radius:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; }
.btn-danger { padding:0.6rem 1.5rem; background:#ef4444; color:#fff; border:none; border-radius:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h2>Kelola Banner Promo</h2>
        <p>Banner ditampilkan sebagai slideshow otomatis di halaman utama website</p>
    </div>
    <a href="<?php echo e(route('admin.banners.create')); ?>" class="btn-add">
        <i class="fa-solid fa-plus"></i> Tambah Banner
    </a>
</div>

<?php if(session('success')): ?>
<div style="background:#d1fae5;color:#065f46;padding:0.85rem 1.25rem;border-radius:0.5rem;margin-bottom:1.25rem;font-size:0.875rem;font-weight:600;">
    <i class="fa-solid fa-circle-check"></i> <?php echo e(session('success')); ?>

</div>
<?php endif; ?>

<?php if($banners->count()): ?>
<div class="banner-grid">
    <?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="banner-card">
        <img src="<?php echo e(url('storage/'.$banner->gambar)); ?>" alt="<?php echo e($banner->judul ?: 'Banner Promo'); ?>" class="banner-img">
        <div class="banner-body">
            <?php if($banner->judul): ?>
                <h3 class="banner-title"><?php echo e($banner->judul); ?></h3>
            <?php else: ?>
                <h3 class="banner-title banner-title-empty">Banner #<?php echo e($loop->iteration); ?> (Tanpa Judul)</h3>
            <?php endif; ?>
            <div class="banner-meta">
                <span class="<?php echo e($banner->aktif ? 'badge-aktif' : 'badge-nonaktif'); ?>">
                    <?php echo e($banner->aktif ? '● Aktif' : '○ Nonaktif'); ?>

                </span>
                <?php if($banner->url_tujuan): ?>
                <span style="font-size:0.72rem;color:#6b7280;" title="<?php echo e($banner->url_tujuan); ?>">
                    <i class="fa-solid fa-link"></i> <?php echo e(\Illuminate\Support\Str::limit($banner->url_tujuan, 32)); ?>

                </span>
                <?php endif; ?>
            </div>
            <div class="banner-actions">
                <a href="<?php echo e(route('admin.banners.edit', $banner)); ?>" class="btn-edit">
                    <i class="fa-solid fa-pen"></i> Edit
                </a>
                <button class="btn-toggle <?php echo e($banner->aktif ? 'btn-toggle-on' : 'btn-toggle-off'); ?>"
                    onclick="toggleBanner(<?php echo e($banner->id); ?>, this)">
                    <i class="fa-solid <?php echo e($banner->aktif ? 'fa-eye-slash' : 'fa-eye'); ?>"></i>
                    <?php echo e($banner->aktif ? 'Nonaktifkan' : 'Aktifkan'); ?>

                </button>
                <button class="btn-del" onclick="confirmDelete(<?php echo e($banner->id); ?>, '<?php echo e(addslashes($banner->judul ?: 'Banner #' . $loop->iteration)); ?>')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php else: ?>
<div class="empty-state">
    <i class="fa-solid fa-image"></i>
    <h3 style="color:#374151;margin-bottom:0.5rem;">Belum ada banner</h3>
    <p>Tambahkan banner promo untuk ditampilkan di halaman utama.</p>
    <a href="<?php echo e(route('admin.banners.create')); ?>" class="btn-add" style="margin-top:1rem;display:inline-flex;">
        <i class="fa-solid fa-plus"></i> Tambah Banner Pertama
    </a>
</div>
<?php endif; ?>


<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div style="font-size:2.5rem;margin-bottom:0.75rem;">🗑️</div>
        <h3>Hapus Banner?</h3>
        <p id="deleteMsg">Banner ini akan dihapus permanen.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Batal</button>
            <form id="deleteForm" method="POST">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-danger">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function confirmDelete(id, judul) {
    document.getElementById('deleteMsg').textContent = 'Banner "' + judul + '" akan dihapus permanen.';
    document.getElementById('deleteForm').action = '/admin/banners/' + id;
    document.getElementById('deleteModal').classList.add('show');
}
function closeModal() { document.getElementById('deleteModal').classList.remove('show'); }

async function toggleBanner(id, btn) {
    const res  = await fetch('/admin/banners/' + id + '/toggle', { method:'POST', headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>','Accept':'application/json'} });
    const data = await res.json();
    const aktif = data.aktif;
    btn.className = 'btn-toggle ' + (aktif ? 'btn-toggle-on' : 'btn-toggle-off');
    btn.innerHTML = `<i class="fa-solid ${aktif ? 'fa-eye-slash' : 'fa-eye'}"></i> ${aktif ? 'Nonaktifkan' : 'Aktifkan'}`;
    const badge = btn.closest('.banner-card').querySelector('.badge-aktif, .badge-nonaktif');
    badge.className = aktif ? 'badge-aktif' : 'badge-nonaktif';
    badge.textContent = aktif ? '● Aktif' : '○ Nonaktif';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/banners/index.blade.php ENDPATH**/ ?>