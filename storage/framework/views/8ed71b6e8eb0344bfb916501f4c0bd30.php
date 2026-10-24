

<?php $__env->startSection('title', $medicine->nama_obat . ' - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* ===== DETAIL PAGE HEADER ===== */
    .detail-page-header {
        background: linear-gradient(135deg, #0D47A1 0%, #1565C0 50%, #1E88E5 100%);
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
    }
    .detail-page-header::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(124,179,66,0.18) 0%, transparent 70%);
        border-radius: 50%;
    }
    .detail-page-header h1 {
        font-size: clamp(1.4rem, 3vw, 2rem);
        font-weight: 800; color: white;
        margin-bottom: 0.4rem; position: relative;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .detail-page-header p { color: rgba(255,255,255,0.8); font-size: 0.95rem; position: relative; }
    .breadcrumb-custom { display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.75rem; position: relative; flex-wrap: wrap; }
    .breadcrumb-custom a { color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.875rem; }
    .breadcrumb-custom a:hover { color: white; }
    .breadcrumb-custom span { color: rgba(255,255,255,0.5); font-size: 0.875rem; }
    .breadcrumb-custom .current { color: #a5d65a; font-size: 0.875rem; font-weight: 600; }

    /* ===== DETAIL WRAPPER ===== */
    .detail-wrapper {
        max-width: 1000px;
        margin: 2.5rem auto;
        padding: 0 1rem;
    }

    .detail-container {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(99,102,241,0.08), 0 1px 4px rgba(0,0,0,0.05);
        border: 1px solid rgba(99,102,241,0.13);
        margin-bottom: 2rem;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 0;
    }

    .detail-image-col {
        padding: 2rem;
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .detail-image {
        width: 100%;
        aspect-ratio: 1;
        max-width: 340px;
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 5rem;
        overflow: hidden;
        position: static;
        margin-bottom: 0.85rem;
        border: 1px solid #e5e7eb;
    }

    .detail-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .detail-image img.detail-gallery-main { transition:opacity .2s ease; }
    .detail-image img.detail-gallery-main { object-fit:contain; }
    .detail-gallery-thumbnails { display:flex; flex-wrap:wrap; justify-content:center; gap:.55rem; max-width:340px; width:100%; }
    .detail-gallery-thumb { width:58px; height:58px; padding:0; overflow:hidden; border:2px solid transparent; border-radius:10px; background:#fff; cursor:pointer; transition:border-color .2s,transform .2s; }
    .detail-gallery-thumb:hover,.detail-gallery-thumb.active { transform:translateY(-2px); border-color:#1E88E5; }
    .detail-gallery-thumb img { width:100%; height:100%; object-fit:cover; }

    .detail-info {
        padding: 2rem;
        border-left: 1px solid #f3f4f6;
    }

    .detail-info h1 {
        font-size: clamp(1.3rem, 2.5vw, 1.75rem);
        margin-bottom: 0.75rem;
        color: #1f2937;
        font-weight: 800;
        line-height: 1.3;
    }

    .detail-category {
        display: inline-block;
        background: #e3f2fd;
        color: #1565C0;
        padding: 0.3rem 0.85rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
    }

    .price-section {
        margin: 1.25rem 0;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border-radius: 12px;
        border-left: 4px solid #1E88E5;
    }

        /* Deskripsi di bawah foto (detail) */
        .detail-desc {
            margin-top: 1rem;
            max-width: 340px;
            width: 100%;
            background: #ffffff;
            border: 1px solid #e6eefc;
            padding: 0.9rem;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(14,165,233,0.06);
            color: #374151;
        }
        .detail-desc h3 {
            font-size: 0.95rem;
            font-weight: 700;
            margin: 0 0 0.45rem;
            color: #0f172a;
            display:flex; align-items:center; gap:0.5rem;
        }
        .detail-desc .icon { color: #1E88E5; font-size:1.05rem; }
        .detail-desc p {
            line-height: 1.6;
            font-size: 0.95rem;
            color: #4b5563;
            margin: 0;
            white-space: pre-wrap;
        }
        @media (max-width: 768px) {
            .detail-desc { max-width: 320px; padding: 0.8rem; }
        }

    .price-label {
        font-size: 0.78rem;
        color: #1565C0;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    .price {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 800;
        color: #1565C0;
        line-height: 1.2;
    }

    .stock-info {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .stock-item {
        flex: 1;
        padding: 0.85rem;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        text-align: center;
    }

    .stock-item-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.35rem;
        font-weight: 600;
    }

    .stock-item-value {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1f2937;
    }

    .description-section {
        margin: 1.25rem 0;
    }

    .description-section h3 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.6rem;
        color: #1f2937;
    }

    .description-section p {
        line-height: 1.7;
        color: #4b5563;
        font-size: 0.9rem;
    }

    /* Related Products */
    .related-section {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 24px rgba(99,102,241,0.08), 0 1px 4px rgba(0,0,0,0.05);
        border: 1px solid rgba(99,102,241,0.13);
    }

    .related-section h2 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .related-section h2 i { color: #1E88E5; }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 1rem;
    }

    .related-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s;
    }

    .related-card:hover {
        box-shadow: 0 8px 25px rgba(30,136,229,0.12);
        transform: translateY(-4px);
        border-color: #90caf9;
        color: inherit;
    }

    .related-image {
        width: 100%;
        height: 120px;
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        overflow: hidden;
    }

    .related-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .related-card:hover .related-image img { transform: scale(1.05); }

    .related-body { padding: 0.85rem; }

    .related-name {
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: #1f2937;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .related-price {
        font-size: 0.9rem;
        font-weight: 800;
        color: #1E88E5;
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-image-col {
            padding: 1.25rem;
        }

        .detail-image {
            position: static;
            max-width: 220px;
            margin: 0 auto;
        }

        .detail-info {
            padding: 1.25rem;
            border-left: none;
            border-top: 1px solid #f3f4f6;
        }

        .stock-info { flex-direction: row; }

        .related-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .related-section { padding: 1.25rem; }
    }

    @media (max-width: 480px) {
        .detail-wrapper { margin: 1rem auto; padding: 0 0.5rem; }
        .detail-image-col { padding: 1rem; }
        .detail-image { max-width: 180px; }
        .detail-info { padding: 1rem; }
        .price-section { padding: 1rem; }
        .price { font-size: clamp(1.3rem, 5vw, 1.75rem); }
        .stock-item { padding: 0.65rem 0.5rem; }
        .related-section { padding: 1rem; }
        .related-grid { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
        .related-image { height: 100px; }
        .related-body { padding: 0.65rem; }
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

<div class="detail-page-header">
    <div class="container">
        <div class="breadcrumb-custom">
            <a href="<?php echo e(route('home')); ?>"><i class="fa-solid fa-house"></i> Home</a>
            <span>/</span>
            <a href="<?php echo e(route('products.index')); ?>">Produk</a>
            <span>/</span>
            <span class="current"><?php echo e(Str::limit($medicine->nama_obat, 30)); ?></span>
        </div>
        <h1><?php echo e($medicine->nama_obat); ?></h1>
        <p><i class="fa-solid fa-building fa-sm"></i> <?php echo e($medicine->kategori); ?></p>
    </div>
</div>

<div class="detail-wrapper">
    <div class="detail-container">
        <div class="detail-grid">
            <!-- Image Column -->
            <div class="detail-image-col">
                <div class="detail-image">
                    <?php if($medicine->image_gallery): ?>
                        <img class="detail-gallery-main" id="detailGalleryMain" src="<?php echo e($medicine->image_url); ?>" alt="<?php echo e($medicine->nama_obat); ?>">
                    <?php else: ?>
                        <i class="fa-solid fa-pills" style="color:#90caf9;font-size:4rem;"></i>
                    <?php endif; ?>
                </div>
                <?php if(count($medicine->image_gallery) > 1): ?>
                    <div class="detail-gallery-thumbnails" aria-label="Foto produk lainnya">
                        <?php $__currentLoopData = $medicine->image_gallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" class="detail-gallery-thumb <?php echo e($index === 0 ? 'active' : ''); ?>" data-image-url="<?php echo e($index === 0 ? $medicine->image_url : url('storage/' . $path)); ?>" aria-label="Tampilkan foto produk <?php echo e($index + 1); ?>">
                                <img src="<?php echo e($index === 0 ? $medicine->image_url : url('storage/' . $path)); ?>" alt="Foto <?php echo e($index + 1); ?> <?php echo e($medicine->nama_obat); ?>">
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <?php if($medicine->deskripsi): ?>
                <div class="detail-desc">
                    <h3><i class="fa-solid fa-circle-info icon"></i>Deskripsi Produk</h3>
                    <p><?php echo nl2br(e($medicine->deskripsi)); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info Column -->
            <div class="detail-info">
                <span class="detail-category"><?php echo e($medicine->kategori); ?></span>
                <h1><?php echo e($medicine->nama_obat); ?></h1>

                <div class="price-section">
                    <div class="price-label">Harga Satuan</div>
                    <div class="price"><?php echo e($medicine->getFormattedPrice()); ?></div>
                </div>
                <?php $detailConverted = $medicine->getConvertedUnitStocksForUnits($medicine->available_stock_units); ?>
                <div class="stock-info">
                    <div class="stock-item">
                        <div class="stock-item-label">Stok <?php echo e($medicine->package_unit_label); ?></div>
                        <div class="stock-item-value"><?php echo e(number_format($detailConverted['box'], 0, ',', '.')); ?> <?php echo e($medicine->package_unit_label); ?></div>
                    </div>
                    <?php if($medicine->unit_packaging_enabled): ?>
                        <div class="stock-item">
                            <div class="stock-item-label">Stok Strip</div>
                            <div class="stock-item-value"><?php echo e(number_format($detailConverted['strip'], 0, ',', '.')); ?> Strip</div>
                        </div>
                        <div class="stock-item">
                            <div class="stock-item-label">Stok Tablet</div>
                            <div class="stock-item-value"><?php echo e(number_format($detailConverted['tablet'], 0, ',', '.')); ?> Tablet</div>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="display:none;">
                </div>

                <?php if(!empty($medicine->golongan)): ?>
                    <div style="margin-bottom:0.85rem;"><strong>Golongan:</strong> <span style="color:#374151;"><?php echo e($medicine->golongan); ?></span></div>
                <?php endif; ?>

                <?php if(!empty($medicine->sediaan)): ?>
                    <div style="margin-bottom:0.85rem;"><strong>Sediaan:</strong> <span style="color:#374151;"><?php echo e($medicine->sediaan_label); ?></span></div>
                <?php endif; ?>

                <?php if(!empty($medicine->dosis)): ?>
                    <div style="margin-bottom:0.85rem;"><strong>Dosis / Aturan Pakai:</strong> <span style="color:#374151;"><?php echo e($medicine->dosis); ?></span></div>
                <?php endif; ?>

                <?php if(!empty($medicine->komposisi)): ?>
                    <div style="margin-bottom:0.85rem;"><strong>Komposisi:</strong> <span style="color:#374151;"><?php echo nl2br(e($medicine->komposisi)); ?></span></div>
                <?php endif; ?>
                <?php if(!empty($medicine->indikasi)): ?>
                    <div style="margin-bottom:0.85rem;"><strong>Indikasi:</strong> <span style="color:#374151;"><?php echo nl2br(e($medicine->indikasi)); ?></span></div>
                <?php endif; ?>

                <!-- Form Pemesanan -->
                <div style="background:#f9fafb;border-radius:12px;padding:1.25rem;margin:1.25rem 0;border:1px solid #e5e7eb;">
                    <h3 style="font-size:0.95rem;font-weight:700;color:#1f2937;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                        <i class="fa-brands fa-whatsapp" style="color:#25D366;font-size:1.1rem;"></i> Form Pemesanan
                    </h3>

                    <div style="margin-bottom:0.85rem;">
                        <label style="display:block;font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:0.35rem;">Jumlah Pembelian</label>
                        <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                            <button type="button" onclick="changeQty(-1)" style="width:34px;height:34px;border:1px solid #d1d5db;border-radius:8px;background:white;font-size:1rem;cursor:pointer;font-weight:700;color:#374151;flex-shrink:0;">-</button>
                            <input type="number" id="qtyInput" value="1" min="1" max="999999"
                                style="width:64px;text-align:center;padding:0.35rem;border:1px solid #d1d5db;border-radius:8px;font-size:0.95rem;font-weight:700;"
                                oninput="updateTotal()">
                            <button type="button" onclick="changeQty(1)" style="width:34px;height:34px;border:1px solid #d1d5db;border-radius:8px;background:white;font-size:1rem;cursor:pointer;font-weight:700;color:#374151;flex-shrink:0;">+</button>
                        </div>
                    </div>

                    <div style="margin-bottom:0.85rem;padding:0.65rem 1rem;background:#e3f2fd;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:0.82rem;color:#1565C0;font-weight:600;">Total Harga</span>
                        <span id="totalHarga" style="font-size:1rem;font-weight:800;color:#1565C0;"><?php echo e($medicine->getFormattedPrice()); ?></span>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label style="display:block;font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:0.35rem;">
                            Alamat Pengiriman <span style="color:#ef4444;">*</span>
                        </label>
                        <textarea id="alamatInput" rows="3" placeholder="Jl. Merdeka No. 10, RT 02/RW 03, Kel. Menteng, Jakarta Pusat 10310"
                            style="width:100%;padding:0.6rem 0.8rem;border:1.5px solid #e5e7eb;border-radius:8px;font-size:0.875rem;resize:vertical;font-family:inherit;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#25D366'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
                        <p id="alamatError" style="color:#ef4444;font-size:0.78rem;margin-top:0.2rem;display:none;">
                            <i class="fa-solid fa-circle-exclamation"></i> Alamat pengiriman wajib diisi.
                        </p>
                    </div>

                    <button onclick="pesanWA()"
                        style="width:100%;padding:0.85rem;background:#25D366;color:white;border:none;border-radius:10px;font-size:0.95rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;transition:all 0.3s;">
                        <i class="fa-brands fa-whatsapp" style="font-size:1.1rem;"></i> Pesan via WhatsApp
                    </button>
                </div>

                <a href="<?php echo e(route('products.index')); ?>" style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.65rem;border:1.5px solid #e5e7eb;border-radius:10px;color:#6b7280;text-decoration:none;font-weight:600;font-size:0.875rem;transition:all 0.2s;">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog
                </a>
            </div>
        </div>
    </div>

    <?php if($relatedMedicines->count() > 0): ?>
    <div class="related-section">
        <h2><i class="fa-solid fa-pills"></i> Produk Serupa � <?php echo e($medicine->kategori); ?></h2>
        <div class="related-grid">
            <?php $__currentLoopData = $relatedMedicines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('medicines.show', $related->id)); ?>" class="related-card">
                    <div class="related-image">
                        <?php if($related->gambar): ?>
                            <img src="<?php echo e(url('storage/' . $related->gambar)); ?>" alt="<?php echo e($related->nama_obat); ?>">
                        <?php else: ?>
                            <i class="fa-solid fa-pills" style="color:#90caf9;font-size:1.75rem;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="related-body">
                        <div class="related-name"><?php echo e($related->nama_obat); ?></div>
                        <div class="related-price"><?php echo e($related->getFormattedPrice()); ?></div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<script>
document.querySelectorAll('.detail-gallery-thumb').forEach(button => {
    button.addEventListener('click', () => {
        const mainImage = document.getElementById('detailGalleryMain');
        if (!mainImage) return;
        mainImage.src = button.dataset.imageUrl;
        document.querySelectorAll('.detail-gallery-thumb').forEach(thumb => thumb.classList.toggle('active', thumb === button));
    });
});

    const hargaSatuan = <?php echo e($medicine->harga); ?>;
    const stokMax     = 999999;
    const namaObat    = <?php echo json_encode($medicine->nama_obat, 15, 512) ?>;
    const kategori    = <?php echo json_encode($medicine->kategori, 15, 512) ?>;

    function formatRupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function changeQty(delta) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > stokMax) val = stokMax;
        input.value = val;
        updateTotal();
    }

    function updateTotal() {
        const qty = Math.max(1, Math.min(parseInt(document.getElementById('qtyInput').value) || 1, stokMax));
        document.getElementById('qtyInput').value = qty;
        document.getElementById('totalHarga').textContent = formatRupiah(hargaSatuan * qty);
    }

    function pesanWA() {
        const qty    = parseInt(document.getElementById('qtyInput').value) || 1;
        const alamat = document.getElementById('alamatInput').value.trim();
        const errEl  = document.getElementById('alamatError');

        if (!alamat) {
            errEl.style.display = 'block';
            document.getElementById('alamatInput').focus();
            return;
        }
        errEl.style.display = 'none';

        const total          = formatRupiah(hargaSatuan * qty);
        const hargaSatuanFmt = formatRupiah(hargaSatuan);

        const pesan = 'Halo Apotek Medix, saya ingin memesan:\n\n' +
            'Produk     : ' + namaObat + '\n' +
            'Perusahaan : ' + kategori + '\n' +
            'Harga      : ' + hargaSatuanFmt + ' / pcs\n' +
            'Jumlah     : ' + qty + ' pcs\n' +
            'Total      : ' + total + '\n\n' +
            'Alamat Pengiriman:\n' + alamat + '\n\n' +
            'Mohon konfirmasi info pengiriman. Terima kasih!';

        window.open('https://wa.me/6285882717844?text=' + encodeURIComponent(pesan), '_blank', 'noopener');
    }

    document.querySelector('.detail-info a[href]').addEventListener('mouseover', function() {
        this.style.borderColor = '#1E88E5';
        this.style.color = '#1E88E5';
    });
    document.querySelector('.detail-info a[href]').addEventListener('mouseout', function() {
        this.style.borderColor = '#e5e7eb';
        this.style.color = '#6b7280';
    });
    document.querySelector('button[onclick="pesanWA()"]').addEventListener('mouseover', function() {
        this.style.background = '#1ebe5d';
        this.style.transform = 'translateY(-2px)';
    });
    document.querySelector('button[onclick="pesanWA()"]').addEventListener('mouseout', function() {
        this.style.background = '#25D366';
        this.style.transform = 'translateY(0)';
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/medicines/detail.blade.php ENDPATH**/ ?>