

<?php $__env->startSection('title', 'Tambah Produk Supplier - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', 'Tambah Produk Supplier'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .procurement-page { display:grid; gap:1.25rem; color:#1e293b; }
    .procurement-card { padding:1.2rem; border:1px solid #e2e8f0; border-radius:.85rem; background:#fff; box-shadow:0 4px 14px rgba(15,23,42,.05); }
    .procurement-card h2,.procurement-card h3 { margin:0 0 .45rem; color:#172b4d; }
    .procurement-muted { margin:0 0 1rem; color:#64748b; font-size:.88rem; line-height:1.5; }
    .procurement-notice { padding:.8rem 1rem; border:1px solid #a7f3d0; border-radius:.65rem; background:#ecfdf5; color:#065f46; }
    .procurement-errors { padding:.8rem 1rem .8rem 2rem; border:1px solid #fecaca; border-radius:.65rem; background:#fef2f2; color:#991b1b; }
    .procurement-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem; }
    .procurement-field label { display:block; margin-bottom:.3rem; color:#334155; font-size:.8rem; font-weight:700; }
    .procurement-field input,.procurement-field select,.procurement-field textarea { width:100%; min-height:40px; padding:.55rem .7rem; border:1px solid #cbd5e1; border-radius:.5rem; background:#fff; color:#172b4d; font:inherit; font-size:.88rem; }
    .procurement-field textarea { min-height:80px; }
    .procurement-product-search { position:relative; }
    .procurement-product-results { position:absolute; z-index:20; top:100%; left:0; right:0; max-height:260px; overflow:auto; border:1px solid #cbd5e1; border-radius:.5rem; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.12); }
    .procurement-product-results button { display:block; width:100%; padding:.65rem .75rem; border:0; border-bottom:1px solid #e2e8f0; background:#fff; color:#172b4d; text-align:left; cursor:pointer; }
    .procurement-product-results button:hover,.procurement-product-results button:focus { background:#eff6ff; }
    .procurement-product-results small,.procurement-selection-hint { display:block; margin-top:.25rem; color:#64748b; font-size:.78rem; }
    .procurement-selection-hint.is-existing { color:#166534; }
    .procurement-filter-form { display:grid; grid-template-columns:minmax(220px,2fr) minmax(180px,1fr) auto; gap:.6rem; align-items:end; margin:0 0 .9rem; }
    .procurement-actions { display:flex; flex-wrap:wrap; align-items:center; gap:.55rem; margin-top:.85rem; }
    .procurement-pagination { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.75rem; margin-top:.9rem; color:#64748b; font-size:.84rem; }
    .procurement-pagination-links { display:flex; gap:.5rem; }
    .procurement-pagination-link { display:inline-flex; align-items:center; justify-content:center; min-height:36px; padding:.45rem .75rem; border:1px solid #bfdbfe; border-radius:.45rem; background:#eff6ff; color:#1d4ed8; font-weight:700; text-decoration:none; }
    .procurement-pagination-link.disabled { border-color:#e2e8f0; background:#f8fafc; color:#94a3b8; }
    .procurement-btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; min-height:38px; padding:.55rem .8rem; border:0; border-radius:.5rem; background:#0755c8; color:#fff; font-weight:700; text-decoration:none; cursor:pointer; }
    .procurement-btn.secondary { border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; }
    .procurement-btn.success { background:#15803d; }
    .procurement-table-wrap { overflow:auto; }
    .procurement-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .procurement-table th,.procurement-table td { padding:.7rem; border-bottom:1px solid #e2e8f0; text-align:left; vertical-align:middle; }
    .procurement-table th { background:#f8fafc; color:#475569; white-space:nowrap; }
    .procurement-table td { color:#26384f; }
    .procurement-table input[type=number] { width:100px; min-height:36px; padding:.4rem; border:1px solid #cbd5e1; border-radius:.4rem; color:#172b4d; }
    .procurement-toolbar { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.8rem; margin-bottom:.8rem; }
    .procurement-toolbar form { display:flex; flex-wrap:wrap; align-items:end; gap:.5rem; }
    .procurement-total { color:#0755c8; font-weight:800; white-space:nowrap; }
    .procurement-badge { display:inline-flex; padding:.25rem .55rem; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:.75rem; font-weight:800; }
    .procurement-empty { padding:1.5rem; border:1px dashed #cbd5e1; border-radius:.65rem; color:#64748b; text-align:center; }
    @media(max-width:680px) { .procurement-form-grid { grid-template-columns:1fr; } .procurement-card { padding:.9rem; } }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="procurement-page">
    <?php if(session('status')): ?><div class="procurement-notice" role="status"><?php echo e(session('status')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><ul class="procurement-errors" role="alert"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul><?php endif; ?>

    <section class="procurement-card">
        <h2>Tambah Produk Supplier</h2>
        <p class="procurement-muted">Atur harga modal dan kode produk per supplier di halaman ini. Untuk memilih supplier, memesan barang, dan membuat SP, buka halaman Pesan ke PBF.</p>
        <div class="procurement-actions">
            <a class="procurement-btn" href="<?php echo e(route('admin.produk-pbf.index')); ?>"><i class="fa-solid fa-file-signature"></i> Pesan ke PBF</a>
        </div>
    </section>

        <section class="procurement-card">
            <h2>Opsi 1: Kelola manual</h2>
            <p class="procurement-muted">Satu produk katalog dapat memiliki harga berbeda di banyak supplier. Data ini hanya tersedia untuk admin. Cari produk berdasarkan nama atau SKU; sistem hanya menampilkan hasil teratas agar halaman tetap ringan.</p>
            <?php if($suppliers->isEmpty()): ?>
                <p class="procurement-empty">Tambahkan supplier terlebih dahulu di <a href="<?php echo e(route('admin.produk-pbf.index')); ?>">Pesan ke PBF</a>.</p>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('admin.supplier-procurement.offers.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="procurement-form-grid">
                        <div class="procurement-field">
                            <label for="offerSupplier">Supplier</label>
                            <select id="offerSupplier" name="supplier_id" required>
                                <option value="">Pilih supplier</option>
                                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($supplier->id); ?>" <?php if(old('supplier_id') == $supplier->id): echo 'selected'; endif; ?>><?php echo e($supplier->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="procurement-field procurement-product-search">
                            <label for="offerMedicineSearch">Cari produk katalog</label>
                            <input id="offerMedicineSearch" type="search" placeholder="Ketik minimal 2 huruf nama atau SKU" autocomplete="off" aria-controls="offerMedicineResults" aria-autocomplete="list">
                            <input id="offerMedicineId" name="medicine_id" type="hidden" value="<?php echo e(old('medicine_id')); ?>" required>
                            <div id="offerMedicineResults" class="procurement-product-results" role="listbox" hidden></div>
                            <small id="offerMedicineHint" class="procurement-selection-hint" aria-live="polite">Belum ada produk dipilih.</small>
                        </div>
                        <div class="procurement-field">
                            <label for="supplierCode">Kode produk supplier</label>
                            <input id="supplierCode" name="supplier_code" value="<?php echo e(old('supplier_code')); ?>" maxlength="100">
                        </div>
                        <div class="procurement-field">
                            <label for="purchasePrice">Harga modal per <span id="offerUnitLabel">unit produk</span></label>
                            <input id="purchasePrice" name="purchase_price" type="number" min="0.01" step="0.01" value="<?php echo e(old('purchase_price')); ?>" required>
                        </div>
                        <div class="procurement-field">
                            <label for="minimumQuantity">Minimum beli (<span id="minimumUnitLabel">unit produk</span>)</label>
                            <input id="minimumQuantity" name="minimum_quantity" type="number" min="1" value="<?php echo e(old('minimum_quantity', 1)); ?>" required>
                        </div>
                    </div>
                    <div class="procurement-actions">
                        <button class="procurement-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan harga supplier</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>

        <section class="procurement-card">
            <div class="procurement-toolbar">
                <div><h2>Opsi 2: Edit lewat Excel</h2><p class="procurement-muted" style="margin:0">Pilih supplier lalu unduh daftar produk katalog. Nama produk dan ID produk sudah terisi; harga supplier yang sudah tercatat ikut ditampilkan. Ubah harga/kode/minimum, lalu unggah file yang sama. Baris dengan harga kosong akan dilewati.</p></div>
            </div>
            <form method="GET" action="<?php echo e(route('admin.supplier-procurement.offers.template')); ?>" class="procurement-actions" style="margin:0 0 1rem">
                <div class="procurement-field">
                    <label for="templateSupplier">Supplier untuk file Excel</label>
                    <select id="templateSupplier" name="supplier_id" required>
                        <option value="">Pilih supplier</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button class="procurement-btn secondary" type="submit"><i class="fa-solid fa-download"></i> Ekspor daftar produk & harga</button>
            </form>
            <form method="POST" action="<?php echo e(route('admin.supplier-procurement.offers.import')); ?>" enctype="multipart/form-data" class="procurement-actions">
                <?php echo csrf_field(); ?>
                <div class="procurement-field"><label for="offerImport">Pilih file XLSX atau CSV</label><input id="offerImport" type="file" name="file" accept=".xlsx,.csv,.txt" required></div>
                <button class="procurement-btn" type="submit"><i class="fa-solid fa-file-import"></i> Impor perubahan Excel</button>
            </form>
        </section>

        <section class="procurement-card">
            <h2>Daftar harga supplier</h2>
            <form method="GET" action="<?php echo e(route('admin.supplier-procurement.index')); ?>" class="procurement-filter-form">
                <div class="procurement-field">
                    <label for="offerListSearch">Cari produk, SKU, kode supplier</label>
                    <input id="offerListSearch" type="search" name="offer_search" value="<?php echo e(request('offer_search')); ?>" placeholder="Ketik nama, SKU, atau kode supplier">
                </div>
                <div class="procurement-field">
                    <label for="offerListSupplier">Filter supplier</label>
                    <select id="offerListSupplier" name="offer_supplier_id">
                        <option value="">Semua supplier</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($supplier->id); ?>" <?php if(request('offer_supplier_id') == $supplier->id): echo 'selected'; endif; ?>><?php echo e($supplier->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button class="procurement-btn secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            </form>
            <div class="procurement-table-wrap">
                <table class="procurement-table">
                    <thead><tr><th>Produk</th><th>Supplier</th><th>Kode supplier</th><th>Harga modal</th><th>Minimum</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $offers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($offer->medicine?->nama_obat ?? 'Produk dihapus'); ?></strong><br><small><?php echo e($offer->medicine?->sku ?: 'Tanpa SKU'); ?></small></td>
                            <td><?php echo e($offer->supplier?->name ?? 'Supplier dihapus'); ?></td>
                            <td><?php echo e($offer->supplier_code ?: '—'); ?></td>
                            <td class="procurement-total">Rp <?php echo e(number_format((float) $offer->purchase_price, 0, ',', '.')); ?></td>
                            <td><?php echo e($offer->minimum_quantity); ?></td>
                            <td><span class="procurement-badge"><?php echo e($offer->is_active ? 'Aktif' : 'Nonaktif'); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6"><div class="procurement-empty">Belum ada harga produk supplier.</div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($offers instanceof \Illuminate\Contracts\Pagination\Paginator || $offers instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator): ?>
                <?php if($offers->hasPages()): ?>
                    <nav class="procurement-pagination" aria-label="Halaman daftar harga supplier">
                        <span>Menampilkan <?php echo e($offers->firstItem()); ?>–<?php echo e($offers->lastItem()); ?> dari <?php echo e($offers->total()); ?> produk</span>
                        <div class="procurement-pagination-links">
                            <?php if($offers->onFirstPage()): ?>
                                <span class="procurement-pagination-link disabled" aria-disabled="true">Sebelumnya</span>
                            <?php else: ?>
                                <a class="procurement-pagination-link" rel="prev" href="<?php echo e($offers->previousPageUrl()); ?>">Sebelumnya</a>
                            <?php endif; ?>
                            <?php if($offers->hasMorePages()): ?>
                                <a class="procurement-pagination-link" rel="next" href="<?php echo e($offers->nextPageUrl()); ?>">Next</a>
                            <?php else: ?>
                                <span class="procurement-pagination-link disabled" aria-disabled="true">Next</span>
                            <?php endif; ?>
                        </div>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const productSearch = document.getElementById('offerMedicineSearch');
    if (productSearch) {
        const supplierSelect = document.getElementById('offerSupplier');
        const productId = document.getElementById('offerMedicineId');
        const results = document.getElementById('offerMedicineResults');
        const hint = document.getElementById('offerMedicineHint');
        const supplierCode = document.getElementById('supplierCode');
        const purchasePrice = document.getElementById('purchasePrice');
        const minimumQuantity = document.getElementById('minimumQuantity');
        const unitLabel = document.getElementById('offerUnitLabel');
        const minimumUnitLabel = document.getElementById('minimumUnitLabel');
        let searchTimer;
        let currentRequest;

        const updateProduct = (medicine) => {
            productId.value = medicine.id;
            productSearch.value = medicine.name + (medicine.sku ? ` · ${medicine.sku}` : '');
            unitLabel.textContent = medicine.unit || 'unit produk';
            minimumUnitLabel.textContent = `jumlah ${medicine.unit || 'unit produk'}`;
            const existing = medicine.offer;
            supplierCode.value = existing?.supplier_code || '';
            purchasePrice.value = existing?.purchase_price || '';
            minimumQuantity.value = existing?.minimum_quantity || 1;
            hint.textContent = existing
                ? `Harga supplier ini sudah ada: Rp ${Number(existing.purchase_price).toLocaleString('id-ID')} per ${medicine.unit}. Nilai dapat diperbarui.`
                : 'Belum ada harga untuk pasangan produk dan supplier ini.';
            hint.classList.toggle('is-existing', Boolean(existing));
            results.hidden = true;
            results.replaceChildren();
        };

        const searchProducts = async (query, selectedId = '') => {
            if (currentRequest) currentRequest.abort();
            currentRequest = new AbortController();
            const params = new URLSearchParams({ q: query });
            if (selectedId) params.set('medicine_id', selectedId);
            if (supplierSelect.value) params.set('supplier_id', supplierSelect.value);
            const response = await fetch(`<?php echo e(route('admin.supplier-procurement.medicines.search')); ?>?${params}`, {
                headers: { 'Accept': 'application/json' },
                signal: currentRequest.signal,
            });
            if (!response.ok) throw new Error('Pencarian produk gagal. Silakan coba lagi.');
            return (await response.json()).data;
        };

        productSearch.addEventListener('input', () => {
            clearTimeout(searchTimer);
            productId.value = '';
            hint.textContent = 'Pilih produk dari hasil pencarian.';
            hint.classList.remove('is-existing');
            supplierCode.value = '';
            purchasePrice.value = '';
            minimumQuantity.value = 1;
            const query = productSearch.value.trim();
            if (query.length < 2) {
                results.hidden = true;
                return;
            }
            searchTimer = setTimeout(async () => {
                try {
                    const medicines = await searchProducts(query);
                    results.replaceChildren();
                    medicines.forEach((medicine) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.setAttribute('role', 'option');
                        button.textContent = medicine.name + (medicine.sku ? ` · ${medicine.sku}` : '');
                        const detail = document.createElement('small');
                        detail.textContent = medicine.offer
                            ? `${medicine.unit} · harga supplier saat ini Rp ${Number(medicine.offer.purchase_price).toLocaleString('id-ID')}`
                            : medicine.unit;
                        button.appendChild(detail);
                        button.addEventListener('click', () => updateProduct(medicine));
                        results.appendChild(button);
                    });
                    if (medicines.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'procurement-empty';
                        empty.textContent = 'Produk tidak ditemukan.';
                        results.appendChild(empty);
                    }
                    results.hidden = false;
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        results.replaceChildren();
                        const message = document.createElement('div');
                        message.className = 'procurement-errors';
                        message.textContent = error.message;
                        results.appendChild(message);
                        results.hidden = false;
                    }
                }
            }, 250);
        });

        supplierSelect.addEventListener('change', async () => {
            if (!productId.value) return;
            try {
                const medicines = await searchProducts('', productId.value);
                if (medicines[0]) updateProduct(medicines[0]);
            } catch (error) {
                hint.textContent = error.message;
                hint.classList.remove('is-existing');
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.procurement-product-search')) results.hidden = true;
        });

        if (productId.value) {
            searchProducts('', productId.value)
                .then((medicines) => {
                    if (medicines[0]) updateProduct(medicines[0]);
                })
                .catch((error) => {
                    hint.textContent = error.message;
                });
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/supplier-procurement/index.blade.php ENDPATH**/ ?>