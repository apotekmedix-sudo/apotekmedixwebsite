

<?php $__env->startSection('title', 'Pesan ke PBF - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', '📦 Pesan ke PBF'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .ordering-head { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .ordering-head h2 { margin:0 0 .25rem; color:#1f2937; font-size:1.15rem; }
    .ordering-head p { margin:0; color:#6b7280; font-size:.85rem; }
    .supplier-manage-toggle { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; border:1px solid #bfdbfe; border-radius:.5rem; padding:.62rem .85rem; background:#eff6ff; color:#1d4ed8; font-weight:700; cursor:pointer; text-decoration:none; white-space:nowrap; transition:background .2s,border-color .2s; }
    .supplier-manage-toggle:hover { background:#dbeafe; border-color:#93c5fd; }
    .supplier-notice { margin:0 0 1rem; padding:.7rem .85rem; border:1px solid #bfdbfe; border-left:4px solid #1e88e5; border-radius:.5rem; background:#eff6ff; color:#1e3a8a; font-size:.85rem; }
    .supplier-errors { margin:0 0 1rem; padding:.7rem .85rem .7rem 2rem; border-radius:.5rem; background:#fee2e2; color:#991b1b; font-size:.85rem; }
    @media (max-width: 760px) { .ordering-filter-form { grid-template-columns:1fr !important; } .ordering-filter-form .supplier-manage-toggle { justify-content:center; } }
    .ordering-search { display:flex; gap:.55rem; max-width:430px; width:100%; }
    .ordering-search input { flex:1; min-width:0; padding:.65rem .8rem; border:1px solid #d1d5db; border-radius:.5rem; font-size:.88rem; }
    .ordering-search button { border:0; border-radius:.5rem; padding:.65rem 1rem; background:#1e88e5; color:white; font-weight:700; cursor:pointer; }
    .ordering-filter-form { grid-template-columns:minmax(230px,.9fr) minmax(0,1.6fr) auto; }
    .ordering-filter-form > * { min-width:0; }
    .ordering-filter-form select { width:100%; min-width:0; }
    .ordering-filter-form .ordering-search { max-width:none; min-width:0; }
    .ordering-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:1rem; }
    .ordering-card { background:white; border:1px solid #e5e7eb; border-radius:.75rem; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); display:flex; flex-direction:column; }
    .ordering-image { position:relative; height:145px; background:linear-gradient(135deg,#e3f2fd,#f3f8fc); display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .ordering-image img { width:100%; height:100%; object-fit:cover; }
    .ordering-image i { font-size:2.8rem; color:#90caf9; }
    .ordering-image .product-logo-marker { position:absolute; z-index:2; top:.55rem; right:.55rem; width:1.35rem; height:1.35rem; border:2px solid #0f172a; border-radius:50%; box-shadow:0 1px 3px rgba(15,23,42,.25); }
    .ordering-image .product-logo-marker--green { background:#22a447; }
    .ordering-image .product-logo-marker--red { display:inline-flex; align-items:center; justify-content:center; background:#dc2626; color:#0f172a; font-size:.72rem; font-weight:900; line-height:1; }
    .ordering-image .product-logo-marker--blue { background:#2563eb; }
    .ordering-body { padding:.85rem; display:flex; flex:1; flex-direction:column; }
    .ordering-principle { color:#6b7280; font-size:.72rem; font-weight:700; text-transform:uppercase; }
    .ordering-name { margin:.25rem 0 .5rem; font-size:.92rem; color:#1f2937; line-height:1.3; }
    .ordering-meta { display:grid; gap:.2rem; margin:0 0 .6rem; color:#64748b; font-size:.73rem; line-height:1.4; }
    .ordering-meta p { margin:0; overflow-wrap:anywhere; }
    .ordering-meta strong { color:#334155; }
    .ordering-price { color:#1e88e5; font-size:1.05rem; font-weight:800; margin-top:auto; }
    .ordering-button { border:0; border-radius:.5rem; padding:.6rem; background:#25d366; color:white; font-weight:700; cursor:pointer; width:100%; }
    .ordering-readonly { margin-top:.75rem; padding:.55rem; border:1px solid #cbd5e1; border-radius:.5rem; background:#f8fafc; color:#64748b; font-size:.78rem; font-weight:700; text-align:center; }
    .ordering-empty { padding:4rem 1rem; text-align:center; color:#6b7280; background:white; border-radius:.75rem; border:1px solid #e5e7eb; }
    .ordering-pages { margin-top:1.25rem; display:flex; justify-content:center; gap:.35rem; }
    .ordering-pages a, .ordering-pages span { min-width:32px; padding:.45rem .65rem; text-align:center; border:1px solid #e5e7eb; border-radius:.4rem; text-decoration:none; color:#374151; background:white; }
    .ordering-pages .active { background:#1e88e5; color:white; border-color:#1e88e5; }
    .ordering-cart-button { position:fixed; right:22px; bottom:22px; z-index:2100; display:inline-flex; align-items:center; gap:.4rem; border:0; border-radius:999px; padding:.65rem .85rem; background:#1e88e5; color:white; box-shadow:0 6px 18px rgba(30,136,229,.35); font-weight:700; cursor:pointer; font-size:.82rem; white-space:nowrap; }
    .ordering-cart-button .cart-badge { position:static; display:inline-flex; align-items:center; justify-content:center; flex:0 0 20px; width:20px; height:20px; margin:0; padding:0; border-radius:50% !important; background:#ef4444; color:#fff; font-size:.7rem; line-height:20px; }
    @media(max-width:700px) { .ordering-search { max-width:none; } }
    @media(max-width:520px) { .ordering-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem; } .ordering-image { height:115px; } .ordering-body { padding:.65rem; } .ordering-name { font-size:.8rem; } }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="ordering-head" style="flex-direction:column;align-items:stretch;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;flex-wrap:wrap;width:100%;">
        <div><h2>Pesan ke PBF</h2><p><?php echo e($total); ?> harga produk tersedia<?php echo e($selectedSupplier ? ' dari ' . $selectedSupplier->name . ' untuk pengadaan Apotek Medix' : ' dari seluruh supplier'); ?>. Pilih supplier tertentu untuk membuat SP.</p></div>
    </div>
    <form method="GET" action="<?php echo e(route('admin.produk-pbf.index')); ?>" class="ordering-filter-form" style="display:grid;align-items:end;gap:.7rem;background:white;padding:1rem;border-radius:.75rem;border:1px solid #e5e7eb;width:100%;">
        <label style="display:grid;gap:.3rem;color:#475569;font-size:.78rem;font-weight:700;">Harga supplier
            <select name="supplier_id" onchange="this.form.requestSubmit()" style="min-height:42px;padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:.5rem;color:#1e293b;font:inherit;">
                <option value="">Semua Produk — semua harga supplier</option>
                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($supplier->id); ?>" <?php if($selectedSupplier?->id === $supplier->id): echo 'selected'; endif; ?>><?php echo e($supplier->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <div class="ordering-search" style="max-width:none;">
            <input name="search" value="<?php echo e($search); ?>" placeholder="Cari nama, indikasi, komposisi, golongan, sediaan, dosis..."><button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        </div>
        <a class="supplier-manage-toggle" href="<?php echo e(route('admin.supplier-procurement.index', ['tab' => 'offers'])); ?>"><i class="fa-solid fa-tags"></i> Kelola harga supplier</a>
    </form>
</div>
<?php if($selectedSupplier): ?>
    <div class="supplier-notice" role="status">Harga dan pesanan menggunakan data <strong><?php echo e($selectedSupplier->name); ?></strong>. Hanya produk yang sudah memiliki harga aktif di supplier ini yang ditampilkan.</div>
<?php else: ?>
    <div class="supplier-notice" role="status">Semua Produk menampilkan harga aktif dari seluruh supplier. Produk yang sama dapat muncul beberapa kali dengan harga berbeda. Tampilan ini hanya untuk membandingkan harga dan tidak bisa digunakan untuk transaksi; pilih satu supplier untuk membuat SP.</div>
<?php endif; ?>
<?php if(session('status')): ?>
    <div class="supplier-notice" role="status"><?php echo e(session('status')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <ul class="supplier-errors" role="alert"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
<?php endif; ?>
<?php if($productOffers->count()): ?>
    <div class="ordering-grid">
        <?php $__currentLoopData = $productOffers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplierOffer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($medicine = $supplierOffer->medicine); ?>
            <article class="ordering-card">
                <div class="ordering-image">
                    <?php if($medicine->image_url): ?><img src="<?php echo e($medicine->image_url); ?>" alt="<?php echo e($medicine->nama_obat); ?>"><?php else: ?><i class="fa-solid fa-pills"></i><?php endif; ?>
                    <?php echo $__env->make('partials.product-logo-marker', ['medicine' => $medicine], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="ordering-body">
                    <span class="ordering-principle"><?php echo e($medicine->brand ?: $medicine->kategori ?: 'Produk'); ?></span>
                    <h3 class="ordering-name"><?php echo e($medicine->nama_obat); ?></h3>
                    <div class="ordering-meta">
                        <p><strong>Logo:</strong> <?php echo e($medicine->logo ?: '—'); ?></p>
                        <p title="<?php echo e($medicine->golongan ?: '—'); ?>"><strong>Golongan:</strong> <?php echo e($medicine->golongan ?: '—'); ?></p>
                    </div>
                    <div class="ordering-price">Rp <?php echo e(number_format((float) $supplierOffer->purchase_price, 0, ',', '.')); ?> <small>/ <?php echo e($medicine->package_unit_label); ?></small></div>
                    <div class="ordering-meta"><p><strong>Supplier:</strong> <?php echo e($supplierOffer->supplier->name); ?></p></div>
                    <?php if($selectedSupplier): ?>
                        <div class="ordering-meta"><p><strong>Kode supplier:</strong> <?php echo e($supplierOffer->supplier_code ?: '—'); ?></p><p><strong>Minimum:</strong> <?php echo e($supplierOffer->minimum_quantity); ?> <?php echo e($medicine->package_unit_label); ?></p></div>
                        <button class="ordering-button" data-package-unit-label="<?php echo e($medicine->package_unit_label); ?>" onclick="addToCart(<?php echo e($medicine->id); ?>, '<?php echo e(addslashes($medicine->nama_obat)); ?>', <?php echo e($supplierOffer->purchase_price); ?>, '<?php echo e($medicine->image_url ?: ''); ?>', '<?php echo e(addslashes($medicine->brand ?: $medicine->kategori)); ?>', this, null, <?php echo e($supplierOffer->id); ?>, <?php echo e($supplierOffer->minimum_quantity); ?>)"><i class="fa-solid fa-cart-plus"></i> Tambah Pesanan</button>
                    <?php else: ?>
                        <div class="ordering-readonly"><i class="fa-solid fa-eye"></i> Perbandingan harga saja</div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="ordering-pages">
        <?php if($productOffers->onFirstPage()): ?><span>Previous</span><?php else: ?><a href="<?php echo e($productOffers->previousPageUrl()); ?>">Previous</a><?php endif; ?>
        <?php $__currentLoopData = $productOffers->getUrlRange(1, $productOffers->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($page === 1 || $page === $productOffers->lastPage() || abs($page - $productOffers->currentPage()) <= 2): ?>
                <?php if($page === $productOffers->currentPage()): ?><span class="active"><?php echo e($page); ?></span><?php else: ?><a href="<?php echo e($url); ?>"><?php echo e($page); ?></a><?php endif; ?>
            <?php elseif($page === 2 || $page === $productOffers->lastPage() - 1): ?>
                <span>...</span>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if($productOffers->hasMorePages()): ?><a href="<?php echo e($productOffers->nextPageUrl()); ?>">Next</a><?php else: ?><span>Next</span><?php endif; ?>
    </div>
<?php else: ?>
    <div class="ordering-empty"><i class="fa-solid fa-box-open" style="font-size:2.5rem"></i><p><?php echo e($selectedSupplier ? 'Belum ada harga produk aktif untuk supplier ini. Tambahkan harga melalui Kelola harga supplier.' : 'Belum ada harga supplier aktif. Tambahkan harga melalui Kelola harga supplier.'); ?></p></div>
<?php endif; ?>
<?php if($selectedSupplier): ?>
    <button class="ordering-cart-button" onclick="openCart()"><i class="fa-solid fa-cart-shopping"></i> Keranjang <span class="cart-badge" id="adminCartBadge">0</span></button>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php if($selectedSupplier): ?>
<script>window.cartSettings = { storageKey: 'apotekmedix_cart_pbf_admin_supplier_<?php echo e($selectedSupplier->id); ?>', receiptStoreName: 'APOTEK MEDIX', receiptStoreAddress: 'Jl. Raya Tajur No. 256 C, Bogor Timur, Kota Bogor, 16146', receiptFilePrefix: 'surat-pesanan-apotek-medix', adminPbfOrder: true, selectedSupplier: <?php echo json_encode($selectedSupplier, 15, 512) ?>, supplierOrderUrl: <?php echo json_encode(route('admin.supplier-procurement.orders.store'), 15, 512) ?>, supplierPurchaseOrderBase: <?php echo json_encode(route('admin.supplier-procurement.index'), 15, 512) ?>, adminPurchaseOrderBase: '<?php echo e(str_replace('/0/', '/__ORDER__/', route('admin.purchase-history.purchase-order', ['order' => 0]))); ?>', continueShoppingUrl: <?php echo json_encode(route('admin.produk-pbf.index'), 15, 512) ?>, suppliers: <?php echo json_encode($suppliers, 15, 512) ?> };</script>
<?php endif; ?>
<?php if($selectedSupplier): ?>
<?php echo $__env->make('partials.cart', ['cartAdminPbfOrder' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/products-ordering.blade.php ENDPATH**/ ?>