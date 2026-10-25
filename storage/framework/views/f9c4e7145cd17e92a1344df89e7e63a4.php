<?php
    $priceChannel = $priceChannel ?? 'b2c';
    $showUnitChoices = $showUnitChoices ?? true;
    $unitKeys = $showUnitChoices ? ['box', 'strip', 'tablet'] : ['box'];
    $unitPriceValues = [
        'box' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'box')),
    ];
    $unitBasePriceValues = [
        'box' => $medicine->getChannelBasePrice($priceChannel, 'box'),
    ];
    $unitFactors = [
        'box' => $medicine->units_per_box,
    ];
    $unitLabels = [
        'box' => $medicine->package_unit_label,
    ];
    if ($showUnitChoices) {
        $unitPriceValues = [
            'tablet' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'tablet')),
            'strip' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'strip')),
        ] + $unitPriceValues;
        $unitBasePriceValues = [
            'tablet' => $medicine->getChannelBasePrice($priceChannel, 'tablet'),
            'strip' => $medicine->getChannelBasePrice($priceChannel, 'strip'),
        ] + $unitBasePriceValues;
        $unitFactors = [
            'tablet' => 1,
            'strip' => (int) $medicine->tablets_per_strip,
        ] + $unitFactors;
        $unitLabels = [
            'tablet' => 'Tablet',
            'strip' => 'Strip',
        ] + $unitLabels;
    }
    $availableUnits = $medicine->usesTabletPackaging()
        ? array_filter(
            $unitKeys,
            fn ($unit) => $unitBasePriceValues[$unit] > 0 && $unitFactors[$unit] > 0
        )
        : ($unitBasePriceValues['box'] > 0 ? ['box'] : []);
    $stockUnits = max(0, (int) ($medicine->available_stock_units ?? $medicine->stock_base_units ?? $medicine->stok));
    $promoPercent = $medicine->getPromoPercent($priceChannel);
?>

<select id="<?php echo e($unitSelectId); ?>" class="product-unit-data" data-stock-product-id="<?php echo e($medicine->id); ?>" aria-hidden="true" tabindex="-1"
        data-unit-prices="<?php echo e(json_encode($unitPriceValues)); ?>"
        data-unit-factors="<?php echo e(json_encode($unitFactors)); ?>"
        data-unit-labels="<?php echo e(json_encode($unitLabels)); ?>"
        data-stock-units="<?php echo e($stockUnits); ?>">
    <?php $__currentLoopData = $availableUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($unit); ?>" data-price="<?php echo e($unitPriceValues[$unit]); ?>" <?php if($unit === 'box'): echo 'selected'; endif; ?>><?php echo e($unitLabels[$unit]); ?> · Rp <?php echo e(number_format($unitPriceValues[$unit], 0, ',', '.')); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>

<?php if($availableUnits): ?>
    <div class="product-unit-section">
        <span class="product-unit-heading"><?php echo e($showUnitChoices ? 'Beli per satuan & jumlah' : 'Harga per kemasan & jumlah'); ?></span>
        <div class="product-unit-grid product-unit-grid-<?php echo e(count($availableUnits)); ?>">
            <?php $__currentLoopData = $availableUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $availableQuantity = intdiv($stockUnits, max(1, (int) $unitFactors[$unit]));
                    $unitBasePrice = $unitBasePriceValues[$unit];
                ?>
                <div class="product-unit-card" data-cart-unit="<?php echo e($unit); ?>">
                    <button type="button" class="product-unit-choice" aria-pressed="false"
                            data-unit-price="<?php echo e($unitPriceValues[$unit]); ?>"
                            data-price-target="<?php echo e($priceTargetId ?? ''); ?>"
                            onclick="selectProductCardUnit(this)">
                        <span class="product-unit-name"><?php echo e($unitLabels[$unit]); ?></span>
                        <?php if($promoPercent > 0 && $unitBasePrice > $unitPriceValues[$unit]): ?>
                            <small class="product-unit-old-price">Rp <?php echo e(number_format($unitBasePrice, 0, ',', '.')); ?></small>
                        <?php endif; ?>
                        <strong class="product-unit-price">Rp <?php echo e(number_format($unitPriceValues[$unit], 0, ',', '.')); ?></strong>
                        <small class="product-unit-stock" data-unit-stock-badge="<?php echo e($unit); ?>">Stok: <?php echo e(number_format($availableQuantity, 0, ',', '.')); ?></small>
                    </button>
                    <div class="product-unit-quantity">
                        <button type="button" aria-label="Kurangi <?php echo e($unitLabels[$unit]); ?>"
                                onclick="adjustProductCardQty(this, -1)" disabled>−</button>
                        <input type="number" min="0" max="<?php echo e($availableQuantity); ?>" step="1" value="0"
                               data-cart-quantity data-stock-unit="<?php echo e($unit); ?>"
                               aria-label="Jumlah <?php echo e($unitLabels[$unit]); ?>"
                               oninput="updateProductCardQty(this)" disabled>
                        <button type="button" aria-label="Tambah <?php echo e($unitLabels[$unit]); ?>"
                                onclick="adjustProductCardQty(this, 1)" disabled>+</button>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php else: ?>
    <small class="product-unit-unavailable">Harga satuan produk belum tersedia.</small>
<?php endif; ?>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\partials\product-unit-selector.blade.php ENDPATH**/ ?>