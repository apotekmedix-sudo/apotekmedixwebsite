

<?php $__env->startSection('title', $medicine->nama_obat . ' - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<?php echo $__env->make('partials.product-card-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
    /* ===== DETAIL PAGE HEADER ===== */
    .detail-page-header {
        background: linear-gradient(135deg, #0D47A1 0%, #1565C0 50%, #1E88E5 100%);
        padding: 2.5rem 0;
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
        font-weight: 800;
        color: #ffffff !important;
        margin-bottom: 0.4rem;
        position: relative;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .detail-page-header p {
        color: rgba(255,255,255,0.88) !important;
        font-size: 0.95rem;
        position: relative;
    }
    .breadcrumb-custom {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.75rem;
        position: relative;
        flex-wrap: wrap;
    }
    .breadcrumb-custom a {
        color: rgba(255,255,255,0.8) !important;
        text-decoration: none;
        font-size: 0.875rem;
    }
    .breadcrumb-custom a:hover { color: #ffffff !important; }
    .breadcrumb-custom span { color: rgba(255,255,255,0.6) !important; font-size: 0.875rem; }
    .breadcrumb-custom .current { color: #bbf7d0 !important; font-size: 0.875rem; font-weight: 700; }

    /* ===== DETAIL WRAPPER ===== */
    .detail-wrapper {
        max-width: 1020px;
        margin: 2.25rem auto;
        padding: 0 1rem;
    }

    .detail-container {
        background: #ffffff !important;
        color: #0f172a !important;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
        border: 1px solid #dbeafe;
        margin-bottom: 2rem;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1.25fr;
        gap: 0;
    }

    .detail-image-col {
        padding: 2rem;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .detail-image {
        width: 100%;
        aspect-ratio: 1;
        max-width: 340px;
        background: #ffffff;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 5rem;
        overflow: hidden;
        position: relative;
        margin-bottom: 0.85rem;
        border: 1px solid #dbeafe;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .detail-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .detail-image img.detail-gallery-main {
        transition: opacity .2s ease;
        object-fit: contain;
    }
    .detail-gallery-thumbnails {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: .55rem;
        max-width: 340px;
        width: 100%;
    }
    .detail-gallery-thumb {
        width: 58px;
        height: 58px;
        padding: 0;
        overflow: hidden;
        border: 2px solid transparent;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color .2s, transform .2s;
    }
    .detail-gallery-thumb:hover,
    .detail-gallery-thumb.active {
        transform: translateY(-2px);
        border-color: #1E88E5;
    }
    .detail-gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Deskripsi di bawah foto (detail) */
    .detail-desc {
        margin-top: 1rem;
        max-width: 340px;
        width: 100%;
        background: #ffffff !important;
        border: 1px solid #cfe2ff;
        padding: 1rem 1.1rem;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(14,165,233,0.06);
        color: #1e293b !important;
    }
    .detail-desc h3 {
        font-size: 0.95rem;
        font-weight: 800;
        margin: 0 0 0.45rem;
        color: #0f172a !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .detail-desc .icon {
        color: #1E88E5 !important;
        font-size: 1.05rem;
    }
    .detail-desc p {
        line-height: 1.6;
        font-size: 0.9rem;
        color: #334155 !important;
        margin: 0;
        white-space: pre-wrap;
    }

    .detail-info {
        padding: 2rem;
        background: #ffffff !important;
        color: #0f172a !important;
        border-left: 1px solid #e2e8f0;
    }

    .detail-info h1 {
        font-size: clamp(1.35rem, 2.5vw, 1.75rem);
        margin-bottom: 0.85rem;
        color: #0f172a !important;
        font-weight: 800;
        line-height: 1.3;
    }

    .detail-category {
        display: inline-block;
        background: #e0f2fe !important;
        color: #0369a1 !important;
        padding: 0.32rem 0.9rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 800;
        margin-bottom: 0.85rem;
        border: 1px solid #bae6fd;
    }

    .price-section {
        margin: 1rem 0 1.25rem;
        padding: 0;
        background: transparent !important;
        border: 0;
    }

    .price-label {
        font-size: 0.88rem;
        color: #475569 !important;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .price {
        font-size: clamp(1.5rem, 3vw, 1.95rem);
        font-weight: 800;
        color: #0f172a !important;
        line-height: 1.2;
    }

    .stock-info {
        display: flex;
        gap: 0.65rem;
        margin-bottom: 1.2rem;
        flex-wrap: wrap;
    }

    .stock-item {
        flex: 1;
        min-width: 110px;
        padding: 0.75rem 0.65rem;
        background: #f8fafc !important;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        text-align: center;
    }

    .stock-item-label {
        font-size: 0.74rem;
        color: #475569 !important;
        margin-bottom: 0.25rem;
        font-weight: 700;
    }

    .stock-item-value {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a !important;
    }

    .detail-meta-list {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        margin-bottom: 1.25rem;
        padding: 0.95rem 1.1rem;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .detail-meta-row {
        font-size: 0.88rem;
        line-height: 1.5;
        color: #1e293b !important;
    }

    .detail-meta-row strong {
        color: #0f172a !important;
        font-weight: 800;
        margin-right: 0.3rem;
    }

    .detail-meta-row span {
        color: #334155 !important;
        font-weight: 500;
    }

    /* Purchase Box */
    .detail-purchase-box {
        background: transparent !important;
        border-radius: 0;
        padding: 0;
        margin: 1.25rem 0;
        border: 0;
        color: #0f172a !important;
    }

    .detail-purchase-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a !important;
        margin: 0 0 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-purchase-title i {
        color: #1E88E5 !important;
    }

    .detail-unit-pills {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 0.55rem;
        margin-bottom: 0.95rem;
    }

    .detail-unit-pill {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 0.6rem 0.75rem;
        border-radius: 10px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff !important;
        color: #0f172a !important;
        cursor: pointer;
        transition: all 0.18s ease;
        text-align: left;
    }

    .detail-unit-pill:hover {
        border-color: #1E88E5;
        background: #f0f9ff !important;
    }

    .detail-unit-pill.active {
        border-color: #1E88E5;
        background: #e0f2fe !important;
        box-shadow: 0 0 0 2px rgba(30, 136, 229, 0.18);
    }

    .detail-unit-pill-name {
        font-size: 0.78rem;
        font-weight: 800;
        color: #0f172a !important;
    }

    .detail-unit-pill-price {
        font-size: 0.85rem;
        font-weight: 800;
        color: #0369a1 !important;
        margin-top: 0.1rem;
    }

    .detail-unit-pill-stock {
        font-size: 0.7rem;
        font-weight: 600;
        color: #475569 !important;
        margin-top: 0.1rem;
    }

    .detail-qty-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #1e293b !important;
        margin-bottom: 0.4rem;
    }

    .detail-qty-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.95rem;
    }

    .detail-qty-btn {
        width: 38px;
        height: 38px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 1.05rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.18s ease;
    }

    .detail-qty-btn:hover {
        border-color: #1E88E5;
        color: #1E88E5 !important;
        background: #f0f9ff !important;
    }

    .detail-qty-input {
        width: 74px;
        height: 38px;
        text-align: center;
        padding: 0.35rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 800;
        background: #ffffff !important;
        color: #0f172a !important;
    }

    .detail-total-bar {
        margin-bottom: 1rem;
        padding: 0.25rem 0;
        background: transparent !important;
        border: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .detail-total-label {
        font-size: 0.84rem;
        color: #0369a1 !important;
        font-weight: 700;
    }

    .detail-total-value {
        font-size: 1.08rem;
        font-weight: 800;
        color: #0f172a !important;
    }

    .detail-action-buttons {
        display: grid;
        grid-template-columns: 1fr 1.35fr;
        gap: 0.75rem;
    }

    .btn-detail-buy,
    .btn-detail-cart {
        padding: 0.82rem 1rem;
        border-radius: 11px;
        font-size: 0.9rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        border: none;
        text-decoration: none;
        line-height: 1.25;
    }

    .btn-detail-buy {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 6px 16px rgba(22, 163, 74, 0.25);
    }

    .btn-detail-buy:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(22, 163, 74, 0.34);
        color: #ffffff !important;
    }

    .btn-detail-cart {
        background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 6px 16px rgba(30, 136, 229, 0.25);
    }

    .btn-detail-cart:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(30, 136, 229, 0.34);
        color: #ffffff !important;
    }

    .btn-detail-cart.added {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;
    }

    .btn-detail-buy *,
    .btn-detail-cart * {
        color: #ffffff !important;
    }

    .btn-detail-buy:disabled,
    .btn-detail-cart:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }

    .btn-back-catalog {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.7rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff !important;
        color: #334155 !important;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-back-catalog:hover {
        border-color: #1E88E5;
        color: #1E88E5 !important;
        background: #f0f9ff !important;
    }

    /* Related Products */
    .related-section {
        background: #ffffff !important;
        color: #0f172a !important;
        border-radius: 16px;
        padding: 1.75rem 2rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
        border: 1px solid #dbeafe;
    }

    .related-section h2 {
        font-size: 1.1rem;
        font-weight: 800;
        margin-bottom: 1.25rem;
        color: #0f172a !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .related-section h2 i { color: #1E88E5 !important; }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
        gap: 1rem;
    }

    .related-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        text-decoration: none;
        color: #0f172a !important;
        transition: all 0.25s;
    }

    .related-card:hover {
        box-shadow: 0 8px 25px rgba(30,136,229,0.14);
        transform: translateY(-4px);
        border-color: #90caf9;
    }

    .related-image {
        width: 100%;
        height: 125px;
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
        font-size: 0.84rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: #0f172a !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .related-price {
        font-size: 0.92rem;
        font-weight: 800;
        color: #1E88E5 !important;
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
        .detail-image-col {
            padding: 1.25rem;
        }
        .detail-image {
            max-width: 240px;
            margin: 0 auto 0.85rem;
        }
        .detail-desc {
            max-width: 100%;
        }
        .detail-info {
            padding: 1.25rem;
            border-left: none;
            border-top: 1px solid #e2e8f0;
        }
        .related-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
        .related-section { padding: 1.25rem; }
    }

    @media (max-width: 480px) {
        .detail-wrapper { margin: 1rem auto; padding: 0 0.5rem; }
        .detail-image-col { padding: 1rem; }
        .detail-image { max-width: 190px; }
        .detail-info { padding: 1rem; }
        .price-section { padding: 1rem; }
        .detail-action-buttons {
            grid-template-columns: 1fr;
        }
        .related-section { padding: 1rem; }
        .related-grid { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
        .related-image { height: 105px; }
        .related-body { padding: 0.65rem; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $internalAccess = $internalAccess ?? \App\Models\ShoppingAccessCode::hasActiveInternalSession();
    $priceChannel = $internalAccess ? 'internal' : 'b2c';
    $b2cAccess = $b2cAccess ?? \App\Models\ShoppingAccessCode::hasActiveB2cSession();
    $redLogoAccess = $redLogoAccess ?? ($b2cAccess || $internalAccess);
    $isRedLogoLocked = $medicine->isRedLogo() && !$redLogoAccess;
    $unitPriceValues = [
        'box' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'box')),
    ];
    $unitFactors = [
        'box' => max(1, (int) $medicine->units_per_box),
    ];
    $unitLabels = [
        'box' => $medicine->package_unit_label,
    ];
    $stockUnits = max(0, (int) ($medicine->available_stock_units ?? $medicine->stock_base_units ?? $medicine->stok));
    $detailStockQuantity = $stockUnits / max(1, $medicine->units_per_box);
    $detailStockDisplay = rtrim(rtrim(number_format($detailStockQuantity, 6, ',', '.'), '0'), ',');
    $defaultUnit = array_key_first($unitPriceValues) ?: 'box';
?>

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
                    <?php echo $__env->make('partials.product-logo-marker', ['medicine' => $medicine], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.85rem;">
                    <span class="detail-category" style="margin-bottom:0;"><?php echo e($medicine->kategori); ?></span>
                    <span class="medicine-sold-badge" data-sold-product-id="<?php echo e($medicine->id); ?>" style="margin-left:0;font-size:0.78rem;padding:0.28rem 0.75rem;">
                        <i class="fa-solid fa-fire-flame-curved"></i> Terjual <?php echo e(number_format((int) $medicine->terjual, 0, ',', '.')); ?>

                    </span>
                </div>
                <h1><?php echo e($medicine->nama_obat); ?></h1>

                <div class="price-section">
                    <div class="price" id="detailUnitPriceDisplay">Rp <?php echo e(number_format($unitPriceValues[$defaultUnit] ?? $medicine->harga, 0, ',', '.')); ?></div>
                </div>

                <div class="stock-info">
                    <span hidden aria-hidden="true" data-stock-badge data-stock-product-id="<?php echo e($medicine->id); ?>" data-stock-unit-factor="<?php echo e(max(1, $medicine->units_per_box)); ?>">Stok</span>
                    <div class="stock-item">
                        <div class="stock-item-label">Stok</div>
                        <div class="stock-item-value"><?php echo e($detailStockDisplay); ?></div>
                    </div>
                </div>

                <div class="detail-meta-list">
                    <?php if(!empty($medicine->golongan)): ?>
                        <div class="detail-meta-row"><strong>Golongan:</strong> <span><?php echo e($medicine->golongan); ?></span></div>
                    <?php endif; ?>

                    <?php if(!empty($medicine->sediaan)): ?>
                        <div class="detail-meta-row"><strong>Sediaan:</strong> <span><?php echo e($medicine->sediaan_label); ?></span></div>
                    <?php endif; ?>

                    <?php if(!empty($medicine->dosis)): ?>
                        <div class="detail-meta-row"><strong>Dosis / Aturan Pakai:</strong> <span><?php echo e($medicine->dosis); ?></span></div>
                    <?php endif; ?>

                    <?php if(!empty($medicine->komposisi)): ?>
                        <div class="detail-meta-row"><strong>Komposisi:</strong> <span><?php echo nl2br(e($medicine->komposisi)); ?></span></div>
                    <?php endif; ?>

                    <?php if(!empty($medicine->indikasi)): ?>
                        <div class="detail-meta-row"><strong>Indikasi:</strong> <span><?php echo nl2br(e($medicine->indikasi)); ?></span></div>
                    <?php endif; ?>
                </div>

                <!-- Pembelian Produk -->
                <div class="detail-purchase-box <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>">
                    <h3 class="detail-purchase-title">
                        <i class="fa-solid fa-bag-shopping"></i> Pilih Jumlah & Pembelian
                    </h3>

                    <?php if(count($unitPriceValues) > 1): ?>
                        <span class="detail-qty-label">Pilih Satuan Kemasan</span>
                        <div class="detail-unit-pills" role="group" aria-label="Pilih satuan kemasan">
                            <?php $__currentLoopData = $unitPriceValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitKey => $uPrice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $uFactor = max(1, (int) ($unitFactors[$unitKey] ?? 1));
                                    $uStock  = intdiv($stockUnits, $uFactor);
                                    $uLabel  = $unitLabels[$unitKey] ?? ucfirst($unitKey);
                                ?>
                                <button type="button"
                                        class="detail-unit-pill <?php echo e((!$isRedLogoLocked && $unitKey === $defaultUnit) ? 'active' : ''); ?> <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>"
                                        data-unit="<?php echo e($unitKey); ?>"
                                        data-price="<?php echo e($uPrice); ?>"
                                        data-label="<?php echo e($uLabel); ?>"
                                        data-stock="<?php echo e($uStock); ?>"
                                        onclick="selectDetailUnit(this)">
                                    <span class="detail-unit-pill-name"><?php echo e($uLabel); ?></span>
                                    <span class="detail-unit-pill-price">Rp <?php echo e(number_format($uPrice, 0, ',', '.')); ?></span>
                                    <span class="detail-unit-pill-stock">Stok: <?php echo e(number_format($uStock, 0, ',', '.')); ?></span>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="detail-qty-label" for="qtyInput">Jumlah Pembelian</label>
                        <div class="detail-qty-controls">
                            <button type="button" class="detail-qty-btn <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>" onclick="changeDetailQty(-1)" aria-label="Kurangi jumlah">−</button>
                            <input type="number" id="qtyInput" class="detail-qty-input <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>" value="1" min="1" max="<?php echo e(max(1, intdiv($stockUnits, max(1, (int) ($unitFactors[$defaultUnit] ?? 1))))); ?>"
                                oninput="updateDetailTotal()">
                            <button type="button" class="detail-qty-btn <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>" onclick="changeDetailQty(1)" aria-label="Tambah jumlah">+</button>
                        </div>
                    </div>

                    <div class="detail-total-bar <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>">
                        <span class="detail-total-label">Total Harga</span>
                        <span id="totalHarga" class="detail-total-value">Rp <?php echo e(number_format($unitPriceValues[$defaultUnit] ?? $medicine->harga, 0, ',', '.')); ?></span>
                    </div>

                    <div class="detail-action-buttons">
                        <button type="button"
                                class="btn-detail-buy <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>"
                                id="btnDetailBuy"
                                onclick="detailBuyNow(this)"
                                <?php if($stockUnits < 1): echo 'disabled'; endif; ?>>
                            <i class="fa-solid fa-bag-shopping"></i> Beli
                        </button>
                        <button type="button"
                                class="btn-detail-cart <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>"
                                id="btnDetailCart"
                                onclick="detailAddToCart(this)"
                                <?php if($stockUnits < 1): echo 'disabled'; endif; ?>>
                            <i class="fa-solid fa-cart-plus"></i> Tambahkan ke Keranjang
                        </button>
                    </div>
                </div>

                <a href="<?php echo e(route('products.index')); ?>" class="btn-back-catalog">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog
                </a>
            </div>
        </div>
    </div>

    <?php if($relatedMedicines->count() > 0): ?>
    <div class="related-section">
        <h2><i class="fa-solid fa-pills"></i> Produk Serupa &mdash; <?php echo e($medicine->kategori); ?></h2>
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
                        <div class="related-price">Rp <?php echo e(number_format($related->getChannelPromoPrice($priceChannel), 0, ',', '.')); ?></div>
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
window.cartSettings = {
    storageKey: 'apotekmedix_cart_retail'
};
</script>
<?php echo $__env->make('partials.cart', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script>
document.querySelectorAll('.detail-gallery-thumb').forEach(button => {
    button.addEventListener('click', () => {
        const mainImage = document.getElementById('detailGalleryMain');
        if (!mainImage) return;
        mainImage.src = button.dataset.imageUrl;
        document.querySelectorAll('.detail-gallery-thumb').forEach(thumb => thumb.classList.toggle('active', thumb === button));
    });
});

const DETAIL_PRODUCT = {
    id: <?php echo e((int) $medicine->id); ?>,
    name: <?php echo json_encode($medicine->nama_obat, 15, 512) ?>,
    brand: <?php echo json_encode($medicine->brand ?: $medicine->kategori, 15, 512) ?>,
    img: <?php echo json_encode($medicine->image_url ?? '', 15, 512) ?>,
    isRedLogo: <?php echo e($medicine->isRedLogo() ? 'true' : 'false'); ?>,
    isRedLogoLocked: <?php echo e($isRedLogoLocked ? 'true' : 'false'); ?>,
    stockUnits: <?php echo e((int) $stockUnits); ?>,
    packageUnitLabel: <?php echo json_encode($medicine->package_unit_label, 15, 512) ?>,
    unitPrices: <?php echo json_encode($unitPriceValues, 15, 512) ?>,
    unitFactors: <?php echo json_encode($unitFactors, 15, 512) ?>,
    unitLabels: <?php echo json_encode($unitLabels, 15, 512) ?>,
    selectedUnit: <?php echo json_encode($defaultUnit, 15, 512) ?>
};

function formatRupiah(angka) {
    return 'Rp ' + Math.round(Number(angka) || 0).toLocaleString('id-ID');
}

function getSelectedUnitAvailableQty() {
    const unit = DETAIL_PRODUCT.selectedUnit || 'box';
    const factor = Number(DETAIL_PRODUCT.unitFactors[unit] || 1);
    return factor > 0 ? Math.floor(DETAIL_PRODUCT.stockUnits / factor) : 0;
}

function selectDetailUnit(btn) {
    if (DETAIL_PRODUCT.isRedLogoLocked) {
        if (typeof openRedLogoModal === 'function') openRedLogoModal(DETAIL_PRODUCT.name);
        return;
    }
    const unit = btn.dataset.unit;
    if (!unit) return;
    DETAIL_PRODUCT.selectedUnit = unit;

    document.querySelectorAll('.detail-unit-pill').forEach(pill => {
        pill.classList.toggle('active', pill === btn);
    });

    const unitPrice = Number(DETAIL_PRODUCT.unitPrices[unit] || 0);
    const availableQty = getSelectedUnitAvailableQty();

    const priceDisplayEl = document.getElementById('detailUnitPriceDisplay');
    if (priceDisplayEl) priceDisplayEl.textContent = formatRupiah(unitPrice);

    const qtyInput = document.getElementById('qtyInput');
    if (qtyInput) {
        qtyInput.max = String(Math.max(1, availableQty));
        if (parseInt(qtyInput.value, 10) > availableQty && availableQty > 0) {
            qtyInput.value = String(availableQty);
        }
    }
    updateDetailTotal();
}

function changeDetailQty(delta) {
    if (DETAIL_PRODUCT.isRedLogoLocked) {
        if (typeof openRedLogoModal === 'function') openRedLogoModal(DETAIL_PRODUCT.name);
        return;
    }
    const input = document.getElementById('qtyInput');
    if (!input) return;
    const maxQty = Math.max(1, getSelectedUnitAvailableQty());
    let val = (parseInt(input.value, 10) || 1) + delta;
    if (val < 1) val = 1;
    if (val > maxQty) val = maxQty;
    input.value = val;
    updateDetailTotal();
}

function updateDetailTotal() {
    const input = document.getElementById('qtyInput');
    const totalEl = document.getElementById('totalHarga');
    if (!input || !totalEl) return;
    const maxQty = Math.max(1, getSelectedUnitAvailableQty());
    const qty = Math.max(1, Math.min(parseInt(input.value, 10) || 1, maxQty));
    input.value = qty;
    const unit = DETAIL_PRODUCT.selectedUnit || 'box';
    const unitPrice = Number(DETAIL_PRODUCT.unitPrices[unit] || 0);
    totalEl.textContent = formatRupiah(unitPrice * qty);
}

function commitDetailItemToCart() {
    if (DETAIL_PRODUCT.isRedLogoLocked) {
        if (typeof openRedLogoModal === 'function') {
            openRedLogoModal(DETAIL_PRODUCT.name);
        }
        return false;
    }

    const unit = DETAIL_PRODUCT.selectedUnit || 'box';
    const unitLabel = DETAIL_PRODUCT.unitLabels[unit] || unit;
    const unitPrice = Number(DETAIL_PRODUCT.unitPrices[unit] || 0);
    const availableQty = getSelectedUnitAvailableQty();
    const qtyInput = document.getElementById('qtyInput');
    const qty = Math.max(1, parseInt(qtyInput?.value, 10) || 1);

    if (availableQty < 1 || qty > availableQty) {
        alert('Stok untuk satuan ' + unitLabel + ' tidak mencukupi.');
        return false;
    }

    const existing = cart.find(item => item.id === DETAIL_PRODUCT.id && (item.saleUnit || 'box') === unit);
    if (existing) {
        const nextQty = Math.min(availableQty, existing.qty + qty);
        existing.qty = nextQty;
        existing.price = unitPrice;
        existing.brand = DETAIL_PRODUCT.brand;
        existing.img = DETAIL_PRODUCT.img;
        existing.unitLabel = unitLabel;
        existing.unitLabels = DETAIL_PRODUCT.unitLabels;
        existing.unitPrices = DETAIL_PRODUCT.unitPrices;
        existing.unitFactors = DETAIL_PRODUCT.unitFactors;
        existing.availableQty = availableQty;
        existing.stockUnits = DETAIL_PRODUCT.stockUnits;
        existing.packageUnitLabel = DETAIL_PRODUCT.packageUnitLabel;
    } else {
        cart.push({
            id: DETAIL_PRODUCT.id,
            name: DETAIL_PRODUCT.name,
            price: unitPrice,
            img: DETAIL_PRODUCT.img,
            brand: DETAIL_PRODUCT.brand || '',
            qty: qty,
            note: '',
            discount: 0,
            saleUnit: unit,
            unitLabel: unitLabel,
            unitLabels: DETAIL_PRODUCT.unitLabels,
            unitPrices: DETAIL_PRODUCT.unitPrices,
            unitFactors: DETAIL_PRODUCT.unitFactors,
            availableQty: availableQty,
            stockUnits: DETAIL_PRODUCT.stockUnits,
            packageUnitLabel: DETAIL_PRODUCT.packageUnitLabel
        });
    }

    if (typeof save === 'function') save();
    if (typeof render === 'function') render();
    return true;
}

function detailAddToCart(btn) {
    if (!commitDetailItemToCart()) return;
    if (btn) {
        const origHtml = btn.dataset.origHtml || btn.innerHTML;
        btn.dataset.origHtml = origHtml;
        btn.classList.add('added');
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Ditambahkan!';
        setTimeout(() => {
            btn.classList.remove('added');
            btn.innerHTML = origHtml;
        }, 1500);
    }
}

function detailBuyNow(btn) {
    if (!commitDetailItemToCart()) return;
    window.location.href = <?php echo json_encode(route('checkout.index'), 15, 512) ?>;
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\medicines\detail.blade.php ENDPATH**/ ?>