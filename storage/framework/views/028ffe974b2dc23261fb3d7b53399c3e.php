

<?php $__env->startSection('title', 'Pesan ke PBF - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', '📦 Pesan ke PBF'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .ordering-head { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .ordering-head h2 { margin:0 0 .25rem; color:#1f2937; font-size:1.15rem; }
    .ordering-head p { margin:0; color:#6b7280; font-size:.85rem; }
    .ordering-heading-actions { display:flex; flex-direction:column; align-items:flex-end; gap:.6rem; }
    .supplier-actions { display:flex; justify-content:flex-end; gap:.55rem; }
    .supplier-toggle { border:0; border-radius:.5rem; padding:.65rem .9rem; background:#1e88e5; color:#fff; font-weight:700; cursor:pointer; white-space:nowrap; transition:background .2s,box-shadow .2s; }
    .supplier-toggle:hover { background:#1565c0; box-shadow:0 4px 12px rgba(30,136,229,.25); }
    .supplier-manage-toggle { border:1px solid #bfdbfe; border-radius:.5rem; padding:.62rem .85rem; background:#eff6ff; color:#1d4ed8; font-weight:700; cursor:pointer; white-space:nowrap; transition:background .2s,border-color .2s; }
    .supplier-manage-toggle:hover { background:#dbeafe; border-color:#93c5fd; }
    .supplier-form-panel { display:none; margin:-.2rem 0 1.15rem; padding:1rem; border:1px solid #cbd5e1; border-radius:.75rem; background:#fff; box-shadow:0 2px 8px rgba(15,23,42,.06); }
    .supplier-form-panel.open { display:block; }
    .supplier-form-panel h3 { margin:0 0 .8rem; color:#1f2937; font-size:.95rem; }
    .supplier-form { display:grid; grid-template-columns:minmax(180px,.8fr) minmax(250px,1.4fr) auto; gap:.65rem; align-items:end; }
    .supplier-form label { display:block; margin-bottom:.3rem; color:#475569; font-size:.75rem; font-weight:700; }
    .supplier-form input,.supplier-form textarea { width:100%; padding:.65rem .75rem; border:1px solid #cbd5e1; border-radius:.5rem; font:inherit; font-size:.85rem; }
    .supplier-form button { border:0; border-radius:.5rem; padding:.68rem .95rem; background:#1e88e5; color:#fff; font-weight:700; cursor:pointer; white-space:nowrap; transition:background .2s,box-shadow .2s; }
    .supplier-form button:hover { background:#1565c0; box-shadow:0 4px 12px rgba(30,136,229,.25); }
    .supplier-notice { margin:0 0 1rem; padding:.7rem .85rem; border:1px solid #bfdbfe; border-left:4px solid #1e88e5; border-radius:.5rem; background:#eff6ff; color:#1e3a8a; font-size:.85rem; }
    .supplier-errors { margin:0 0 1rem; padding:.7rem .85rem .7rem 2rem; border-radius:.5rem; background:#fee2e2; color:#991b1b; font-size:.85rem; }
    .supplier-modal-backdrop { display:none; position:fixed; inset:0; z-index:5000; align-items:center; justify-content:center; padding:20px; background:rgba(15,23,42,.65); }
    .supplier-modal-backdrop.open { display:flex; }
    .supplier-modal { display:flex; flex-direction:column; width:min(760px,100%); max-height:90vh; overflow:hidden; border:1px solid #dbeafe; border-radius:16px; background:#fff; box-shadow:0 24px 70px rgba(15,23,42,.3); }
    .supplier-modal-head { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 1.2rem; border-bottom:1px solid #dbeafe; background:linear-gradient(135deg,#eff6ff,#fff); }
    .supplier-modal-head h3 { margin:0; color:#1e3a8a; font-size:1rem; }
    .supplier-modal-head p { margin:.25rem 0 0; color:#64748b; font-size:.78rem; }
    .supplier-modal-close { width:34px; height:34px; border:0; border-radius:50%; background:#dbeafe; color:#1d4ed8; cursor:pointer; }
    .supplier-modal-body { flex:1; min-height:0; overflow-y:auto; padding:1rem 1.2rem; }
    .supplier-manager-search { position:sticky; top:-1rem; z-index:2; display:flex; align-items:center; gap:.55rem; margin:-1rem -1.2rem .85rem; padding:.85rem 1.2rem; border-bottom:1px solid #e2e8f0; background:rgba(255,255,255,.96); }
    .supplier-manager-search i { color:#64748b; }
    .supplier-manager-search input { flex:1; min-width:0; padding:.65rem .8rem; border:1px solid #cbd5e1; border-radius:.55rem; color:#1e293b; font:inherit; font-size:.85rem; outline:none; }
    .supplier-manager-search input:focus { border-color:#1e88e5; box-shadow:0 0 0 3px rgba(30,136,229,.12); }
    .supplier-manager-count { flex:0 0 auto; color:#64748b; font-size:.75rem; }
    .supplier-data-tools { display:flex; flex-wrap:wrap; align-items:center; gap:.55rem; margin:0 0 .85rem; padding:.75rem; border:1px solid #e2e8f0; border-radius:.65rem; background:#f8fafc; }
    .supplier-data-tools a,.supplier-data-tools button { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; min-height:36px; padding:.5rem .7rem; border:1px solid #bfdbfe; border-radius:.45rem; background:#eff6ff; color:#1d4ed8; font-size:.75rem; font-weight:700; text-decoration:none; cursor:pointer; }
    .supplier-data-tools button { border-color:#1e88e5; background:#1e88e5; color:#fff; }
    .supplier-data-tools input[type=file] { min-width:160px; max-width:220px; color:#475569; font-size:.72rem; }
    .supplier-import-errors { flex:1 0 100%; margin:0; padding:.65rem .65rem .65rem 1.8rem; border-radius:.45rem; background:#fee2e2; color:#991b1b; font-size:.75rem; }
    .supplier-list { display:grid; gap:.7rem; }
    .supplier-row { padding:.85rem; border:1px solid #e2e8f0; border-radius:10px; background:#fff; }
    .supplier-row-main { display:flex; justify-content:space-between; gap:1rem; align-items:center; }
    .supplier-row-name { margin:0; color:#1e293b; font-size:.88rem; font-weight:800; }
    .supplier-row-address { margin:.25rem 0 0; color:#64748b; font-size:.78rem; line-height:1.45; white-space:pre-line; }
    .supplier-row-actions { display:flex; flex:0 0 auto; gap:.4rem; }
    .supplier-row-actions button { padding:.45rem .65rem; border:1px solid #bfdbfe; border-radius:7px; background:#eff6ff; color:#1d4ed8; font-size:.76rem; font-weight:700; cursor:pointer; }
    .supplier-row-actions .supplier-delete-button { border-color:#fecaca; background:#fef2f2; color:#b91c1c; }
    .supplier-edit-form { display:none; margin-top:.8rem; padding-top:.8rem; border-top:1px solid #e2e8f0; }
    .supplier-edit-form.open { display:block; }
    .supplier-edit-form label { display:block; margin:.55rem 0 .25rem; color:#475569; font-size:.74rem; font-weight:700; }
    .supplier-edit-form input,.supplier-edit-form textarea { width:100%; padding:.6rem .7rem; border:1px solid #cbd5e1; border-radius:7px; font:inherit; font-size:.83rem; }
    .supplier-edit-actions { display:flex; justify-content:flex-end; gap:.5rem; margin-top:.7rem; }
    .supplier-edit-actions button { padding:.5rem .75rem; border:0; border-radius:7px; background:#1e88e5; color:#fff; font-weight:700; cursor:pointer; }
    .supplier-edit-actions button[type="button"] { background:#e2e8f0; color:#334155; }
    .supplier-list-empty { padding:2rem 1rem; color:#64748b; text-align:center; }
    .supplier-search-empty { display:none; padding:2rem 1rem; color:#64748b; text-align:center; }
    .ordering-search { display:flex; gap:.55rem; max-width:430px; width:100%; }
    .ordering-search input { flex:1; min-width:0; padding:.65rem .8rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.88rem; }
    .ordering-search button { border:0; border-radius:.5rem; padding:.65rem 1rem; background:#1e88e5; color:white; font-weight:700; cursor:pointer; }
    .ordering-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:1rem; }
    .ordering-card { background:white; border:1px solid #e5e7eb; border-radius:.75rem; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); display:flex; flex-direction:column; }
    .ordering-image { height:145px; background:linear-gradient(135deg,#e3f2fd,#f3f8fc); display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .ordering-image img { width:100%; height:100%; object-fit:cover; }
    .ordering-image i { font-size:2.8rem; color:#90caf9; }
    .ordering-body { padding:.85rem; display:flex; flex:1; flex-direction:column; }
    .ordering-principle { color:#6b7280; font-size:.72rem; font-weight:700; text-transform:uppercase; }
    .ordering-name { margin:.25rem 0 .5rem; font-size:.92rem; color:#1f2937; line-height:1.3; }
    .ordering-price { color:#1e88e5; font-size:1.05rem; font-weight:800; margin-top:auto; }
    .ordering-button { border:0; border-radius:.5rem; padding:.6rem; background:#25d366; color:white; font-weight:700; cursor:pointer; width:100%; }
    .ordering-empty { padding:4rem 1rem; text-align:center; color:#6b7280; background:white; border-radius:.75rem; border:1px solid #e5e7eb; }
    .ordering-pages { margin-top:1.25rem; display:flex; justify-content:center; gap:.35rem; }
    .ordering-pages a, .ordering-pages span { min-width:32px; padding:.45rem .65rem; text-align:center; border:1px solid #e5e7eb; border-radius:.4rem; text-decoration:none; color:#374151; background:white; }
    .ordering-pages .active { background:#1e88e5; color:white; border-color:#1e88e5; }
    .ordering-cart-button { position:fixed; right:22px; bottom:22px; z-index:2100; display:inline-flex; align-items:center; gap:.4rem; border:0; border-radius:999px; padding:.65rem .85rem; background:#1e88e5; color:white; box-shadow:0 6px 18px rgba(30,136,229,.35); font-weight:700; cursor:pointer; font-size:.82rem; white-space:nowrap; }
    .ordering-cart-button .cart-badge { position:static; display:inline-flex; align-items:center; justify-content:center; flex:0 0 20px; width:20px; height:20px; margin:0; padding:0; border-radius:50% !important; background:#ef4444; color:#fff; font-size:.7rem; line-height:20px; }
    @media(max-width:700px) { .ordering-heading-actions { width:100%; align-items:stretch; } .supplier-actions { justify-content:flex-start; } .ordering-search { max-width:none; } .supplier-form { grid-template-columns:1fr; } .supplier-form button { width:100%; } }
    @media(max-width:520px) { .supplier-actions { flex-wrap:wrap; } .supplier-modal-backdrop { padding:0; } .supplier-modal { width:100%; height:100%; max-height:100dvh; border-radius:0; } .supplier-row-main { align-items:flex-start; flex-direction:column; } }
    @media(max-width:520px) { .ordering-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem; } .ordering-image { height:115px; } .ordering-body { padding:.65rem; } .ordering-name { font-size:.8rem; } }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="ordering-head" style="flex-direction:column;align-items:stretch;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;flex-wrap:wrap;width:100%;">
        <div><h2>Pesan ke PBF</h2><p><?php echo e($total); ?> produk tersedia untuk pengadaan Apotek Medix ke distributor. Pesanan dibuat menggunakan surat pesanan resmi Apotek Medix.</p></div>
        <div class="ordering-heading-actions">
            <div class="supplier-actions">
                <button class="supplier-toggle" type="button" onclick="toggleSupplierForm()"><i class="fa-solid fa-plus"></i> Tambah Supplier</button>
                <button class="supplier-manage-toggle" type="button" onclick="openSupplierManager()"><i class="fa-solid fa-address-book"></i> Kelola Supplier</button>
            </div>
        </div>
    </div>
    <form method="GET" action="<?php echo e(route('admin.produk-pbf.index')); ?>" style="background:white;padding:1rem;border-radius:.75rem;border:1px solid #e5e7eb;width:100%;">
        <div class="ordering-search" style="max-width:none;">
            <input name="search" value="<?php echo e($search); ?>" placeholder="Cari nama, indikasi, komposisi, golongan, sediaan, dosis..."><button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        </div>

    </form>
</div>
<?php if(session('status')): ?>
    <div class="supplier-notice" role="status"><?php echo e(session('status')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <ul class="supplier-errors" role="alert"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
<?php endif; ?>
<div class="supplier-form-panel <?php echo e($errors->any() ? 'open' : ''); ?>" id="supplierFormPanel">
    <h3>Tambah supplier PBF</h3>
    <form class="supplier-form" method="POST" action="<?php echo e(route('admin.produk-pbf.suppliers.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="search" value="<?php echo e($search); ?>">
        <div>
            <label for="newSupplierName">Nama supplier</label>
            <input id="newSupplierName" name="name" value="<?php echo e(old('name')); ?>" maxlength="255" required placeholder="Contoh: PT Distributor Sehat">
        </div>
        <div>
            <label for="newSupplierAddress">Alamat supplier</label>
            <textarea id="newSupplierAddress" name="address" rows="1" maxlength="2000" required placeholder="Alamat lengkap supplier"><?php echo e(old('address')); ?></textarea>
        </div>
        <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Supplier</button>
    </form>
</div>
<div class="supplier-modal-backdrop <?php echo e(request()->boolean('manage_suppliers') ? 'open' : ''); ?>" id="supplierManagerBackdrop" onclick="closeSupplierManager(event)">
    <section class="supplier-modal" role="dialog" aria-modal="true" aria-labelledby="supplierManagerTitle">
        <header class="supplier-modal-head">
            <div><h3 id="supplierManagerTitle">Kelola Supplier PBF</h3><p><?php echo e($suppliers->count()); ?> supplier tersimpan</p></div>
            <button class="supplier-modal-close" type="button" aria-label="Tutup" onclick="closeSupplierManager()"><i class="fa-solid fa-xmark"></i></button>
        </header>
        <div class="supplier-modal-body">
            <label class="supplier-manager-search" for="supplierManagerSearch">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="supplierManagerSearch" type="search" placeholder="Cari nama atau alamat supplier..." autocomplete="off">
                <span class="supplier-manager-count" id="supplierManagerCount"><?php echo e($suppliers->count()); ?> supplier</span>
            </label>
            <div class="supplier-data-tools">
                <a href="<?php echo e(route('admin.produk-pbf.suppliers.export')); ?>"><i class="fa-solid fa-file-export"></i> Export Supplier</a>
                <a href="<?php echo e(route('admin.produk-pbf.suppliers.import.template')); ?>"><i class="fa-solid fa-download"></i> Template CSV</a>
                <form method="POST" action="<?php echo e(route('admin.produk-pbf.suppliers.import')); ?>" enctype="multipart/form-data" style="display:contents;">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="file" accept=".csv,.txt,text/csv" aria-label="Pilih file CSV supplier" required>
                    <button type="submit"><i class="fa-solid fa-file-import"></i> Import Supplier</button>
                </form>
                <?php if($errors->supplierImport->any()): ?>
                    <ul class="supplier-import-errors" role="alert">
                        <?php $__currentLoopData = $errors->supplierImport->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php if($suppliers->isEmpty()): ?>
                <div class="supplier-list-empty"><i class="fa-solid fa-address-book"></i><p>Belum ada supplier. Tambahkan supplier untuk mulai mengelola daftar.</p></div>
            <?php else: ?>
                <div class="supplier-list">
                    <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="supplier-row" data-supplier-search="<?php echo e(\Illuminate\Support\Str::lower($supplier->name . ' ' . $supplier->address)); ?>">
                            <div class="supplier-row-main">
                                <div><h4 class="supplier-row-name"><?php echo e($supplier->name); ?></h4><p class="supplier-row-address"><?php echo e($supplier->address); ?></p></div>
                                <div class="supplier-row-actions">
                                    <button type="button" onclick="toggleSupplierEdit(<?php echo e($supplier->id); ?>)"><i class="fa-solid fa-pen"></i> Edit</button>
                                    <form method="POST" action="<?php echo e(route('admin.produk-pbf.suppliers.destroy', $supplier)); ?>" onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="supplier-delete-button" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                            <form class="supplier-edit-form" id="supplierEdit<?php echo e($supplier->id); ?>" method="POST" action="<?php echo e(route('admin.produk-pbf.suppliers.update', $supplier)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <label for="supplierName<?php echo e($supplier->id); ?>">Nama supplier</label>
                                <input id="supplierName<?php echo e($supplier->id); ?>" name="name" value="<?php echo e($supplier->name); ?>" maxlength="255" required>
                                <label for="supplierAddress<?php echo e($supplier->id); ?>">Alamat supplier</label>
                                <textarea id="supplierAddress<?php echo e($supplier->id); ?>" name="address" rows="2" maxlength="2000" required><?php echo e($supplier->address); ?></textarea>
                                <div class="supplier-edit-actions">
                                    <button type="button" onclick="toggleSupplierEdit(<?php echo e($supplier->id); ?>)">Batal</button>
                                    <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
                                </div>
                            </form>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="supplier-search-empty" id="supplierSearchEmpty">Supplier tidak ditemukan.</div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php if($medicines->count()): ?>
    <div class="ordering-grid">
        <?php $__currentLoopData = $medicines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="ordering-card">
                <div class="ordering-image"><?php if($medicine->image_url): ?><img src="<?php echo e($medicine->image_url); ?>" alt="<?php echo e($medicine->nama_obat); ?>"><?php else: ?><i class="fa-solid fa-pills"></i><?php endif; ?></div>
                <div class="ordering-body">
                    <span class="ordering-principle"><?php echo e($medicine->brand ?: $medicine->kategori ?: 'Produk'); ?></span>
                    <h3 class="ordering-name"><?php echo e($medicine->nama_obat); ?></h3>
                    <div class="ordering-price"><?php echo e($medicine->getFormattedCatalogPrice('harga_modal')); ?></div>
                    <button class="ordering-button" data-package-unit-label="<?php echo e($medicine->package_unit_label); ?>" onclick="addToCart(<?php echo e($medicine->id); ?>, '<?php echo e(addslashes($medicine->nama_obat)); ?>', <?php echo e($medicine->harga_modal); ?>, '<?php echo e($medicine->image_url ?: ''); ?>', '<?php echo e(addslashes($medicine->brand ?: $medicine->kategori)); ?>', this)"><i class="fa-solid fa-cart-plus"></i> Tambah Pesanan</button>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="ordering-pages">
        <?php if($medicines->onFirstPage()): ?><span>Previous</span><?php else: ?><a href="<?php echo e($medicines->previousPageUrl()); ?>">Previous</a><?php endif; ?>
        <?php $__currentLoopData = $medicines->getUrlRange(1, $medicines->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($page === 1 || $page === $medicines->lastPage() || abs($page - $medicines->currentPage()) <= 2): ?>
                <?php if($page === $medicines->currentPage()): ?><span class="active"><?php echo e($page); ?></span><?php else: ?><a href="<?php echo e($url); ?>"><?php echo e($page); ?></a><?php endif; ?>
            <?php elseif($page === 2 || $page === $medicines->lastPage() - 1): ?>
                <span>...</span>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if($medicines->hasMorePages()): ?><a href="<?php echo e($medicines->nextPageUrl()); ?>">Next</a><?php else: ?><span>Next</span><?php endif; ?>
    </div>
<?php else: ?>
    <div class="ordering-empty"><i class="fa-solid fa-box-open" style="font-size:2.5rem"></i><p>Belum ada produk dengan harga modal.</p></div>
<?php endif; ?>
<button class="ordering-cart-button" onclick="openCart()"><i class="fa-solid fa-cart-shopping"></i> Keranjang <span class="cart-badge" id="adminCartBadge">0</span></button>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>window.cartSettings = { storageKey: 'apotekmedix_cart_pbf_admin', receiptStoreName: 'APOTEK MEDIX', receiptStoreAddress: 'ITC Cempaka Mas LT.1 No.88, Jakarta Pusat', receiptFilePrefix: 'surat-pesanan-apotek-medix', adminPbfOrder: true, adminPurchaseOrderBase: '<?php echo e(str_replace('/0/', '/__ORDER__/', route('admin.purchase-history.purchase-order', ['order' => 0]))); ?>', suppliers: <?php echo json_encode($suppliers, 15, 512) ?> };
function toggleSupplierForm() { document.getElementById('supplierFormPanel').classList.toggle('open'); }
function openSupplierManager() { document.getElementById('supplierManagerBackdrop').classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeSupplierManager(event) { if (event && event.target !== event.currentTarget) return; document.getElementById('supplierManagerBackdrop').classList.remove('open'); document.body.style.overflow = ''; }
function toggleSupplierEdit(id) { document.getElementById('supplierEdit' + id).classList.toggle('open'); }
const supplierManagerSearch = document.getElementById('supplierManagerSearch');
if (supplierManagerSearch) {
    supplierManagerSearch.addEventListener('input', () => {
        const query = supplierManagerSearch.value.trim().toLocaleLowerCase();
        const rows = Array.from(document.querySelectorAll('.supplier-row'));
        let visibleCount = 0;
        rows.forEach(row => {
            const matches = row.dataset.supplierSearch.includes(query);
            row.hidden = !matches;
            if (matches) visibleCount++;
        });
        document.getElementById('supplierManagerCount').textContent = `${visibleCount} supplier`;
        document.getElementById('supplierSearchEmpty').style.display = visibleCount ? 'none' : 'block';
    });
}
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeSupplierManager(); });
</script>
<?php echo $__env->make('partials.cart', ['cartAdminPbfOrder' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/products-ordering.blade.php ENDPATH**/ ?>