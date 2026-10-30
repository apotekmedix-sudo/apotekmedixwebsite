<div class="supplier-management-actions">
    <button class="procurement-btn" type="button" onclick="toggleSupplierForm()"><i class="fa-solid fa-plus"></i> Tambah Supplier</button>
    <button class="procurement-btn secondary" type="button" onclick="openSupplierManager()"><i class="fa-solid fa-address-book"></i> Kelola Supplier</button>
    <a class="procurement-btn secondary" href="<?php echo e(route('admin.produk-pbf.index')); ?>"><i class="fa-solid fa-file-signature"></i> Pesan ke PBF</a>
</div>
<div class="supplier-form-panel <?php echo e($errors->has('name') || $errors->has('address') || $errors->has('phone') ? 'open' : ''); ?>" id="supplierFormPanel">
    <h3>Tambah supplier PBF</h3>
    <form class="supplier-form" method="POST" action="<?php echo e(route('admin.produk-pbf.suppliers.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="supplier_return_to" value="procurement">
        <div>
            <label for="newSupplierName">Nama supplier</label>
            <input id="newSupplierName" name="name" value="<?php echo e(old('name')); ?>" maxlength="255" required placeholder="Contoh: PT Distributor Sehat">
        </div>
        <div>
            <label for="newSupplierAddress">Alamat supplier</label>
            <textarea id="newSupplierAddress" name="address" rows="1" maxlength="2000" required placeholder="Alamat lengkap supplier"><?php echo e(old('address')); ?></textarea>
        </div>
        <div>
            <label for="newSupplierPhone">Nomor telepon supplier</label>
            <input id="newSupplierPhone" name="phone" type="tel" value="<?php echo e(old('phone')); ?>" maxlength="50" required placeholder="Contoh: 021-12345678">
        </div>
        <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Supplier</button>
    </form>
</div>
<div class="supplier-modal-backdrop <?php echo e(request()->boolean('manage_suppliers') || $errors->supplierImport->any() ? 'open' : ''); ?>" id="supplierManagerBackdrop" onclick="closeSupplierManager(event)">
    <section class="supplier-modal" role="dialog" aria-modal="true" aria-labelledby="supplierManagerTitle">
        <header class="supplier-modal-head">
            <div><h3 id="supplierManagerTitle">Kelola Supplier PBF</h3><p><?php echo e($suppliers->count()); ?> supplier tersimpan</p></div>
            <button class="supplier-modal-close" type="button" aria-label="Tutup" onclick="closeSupplierManager()"><i class="fa-solid fa-xmark"></i></button>
        </header>
        <div class="supplier-modal-body">
            <label class="supplier-manager-search" for="supplierManagerSearch">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="supplierManagerSearch" type="search" placeholder="Cari nama, alamat, atau telepon supplier..." autocomplete="off">
                <span class="supplier-manager-count" id="supplierManagerCount"><?php echo e($suppliers->count()); ?> supplier</span>
            </label>
            <div class="supplier-data-tools">
                <a href="<?php echo e(route('admin.produk-pbf.suppliers.export')); ?>"><i class="fa-solid fa-file-export"></i> Export Supplier</a>
                <a href="<?php echo e(route('admin.produk-pbf.suppliers.import.template')); ?>"><i class="fa-solid fa-download"></i> Template Excel</a>
                <form method="POST" action="<?php echo e(route('admin.produk-pbf.suppliers.import')); ?>" enctype="multipart/form-data" style="display:contents;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="supplier_return_to" value="procurement">
                    <input type="file" name="file" accept=".csv,.txt,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" aria-label="Pilih file Excel atau CSV supplier" required>
                    <button type="submit"><i class="fa-solid fa-file-import"></i> Import Supplier</button>
                </form>
                <?php if($errors->supplierImport->any()): ?>
                    <ul class="supplier-import-errors" role="alert">
                        <?php $__currentLoopData = $errors->supplierImport->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </div>
            <p class="supplier-import-hint">Gunakan template Excel. File .xlsx dan CSV dapat diimpor. NAMA wajib diisi; alamat dan telepon boleh kosong.</p>
            <?php if($suppliers->isEmpty()): ?>
                <div class="supplier-list-empty"><i class="fa-solid fa-address-book"></i><p>Belum ada supplier. Tambahkan supplier untuk mulai mengelola daftar.</p></div>
            <?php else: ?>
                <div class="supplier-list">
                    <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="supplier-row" data-supplier-search="<?php echo e(\Illuminate\Support\Str::lower($supplier->name . ' ' . $supplier->address . ' ' . $supplier->phone)); ?>">
                            <div class="supplier-row-main">
                                <div><h4 class="supplier-row-name"><?php echo e($supplier->name); ?></h4><p class="supplier-row-address"><?php echo e($supplier->address); ?></p><p class="supplier-row-phone"><i class="fa-solid fa-phone"></i> <?php echo e($supplier->phone ?: 'Nomor telepon belum tersedia'); ?></p></div>
                                <div class="supplier-row-actions">
                                    <button type="button" onclick="toggleSupplierEdit(<?php echo e($supplier->id); ?>)"><i class="fa-solid fa-pen"></i> Edit</button>
                                    <form method="POST" action="<?php echo e(route('admin.produk-pbf.suppliers.destroy', $supplier)); ?>" onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <input type="hidden" name="supplier_return_to" value="procurement">
                                        <button class="supplier-delete-button" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                            <form class="supplier-edit-form" id="supplierEdit<?php echo e($supplier->id); ?>" method="POST" action="<?php echo e(route('admin.produk-pbf.suppliers.update', $supplier)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="supplier_return_to" value="procurement">
                                <label for="supplierName<?php echo e($supplier->id); ?>">Nama supplier</label>
                                <input id="supplierName<?php echo e($supplier->id); ?>" name="name" value="<?php echo e($supplier->name); ?>" maxlength="255" required>
                                <label for="supplierAddress<?php echo e($supplier->id); ?>">Alamat supplier</label>
                                <textarea id="supplierAddress<?php echo e($supplier->id); ?>" name="address" rows="2" maxlength="2000" required><?php echo e($supplier->address); ?></textarea>
                                <label for="supplierPhone<?php echo e($supplier->id); ?>">Nomor telepon supplier</label>
                                <input id="supplierPhone<?php echo e($supplier->id); ?>" name="phone" type="tel" value="<?php echo e($supplier->phone); ?>" maxlength="50" required>
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
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/partials/pbf-supplier-management.blade.php ENDPATH**/ ?>