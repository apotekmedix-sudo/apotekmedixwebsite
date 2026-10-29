<?php
    $priceChannel = $priceChannel ?? 'b2c';
    $showUnitChoices = false;
    $priceTargetId = $priceTargetId ?? null;
    $redLogoAccess = $redLogoAccess
        ?? (($b2cAccess ?? \App\Models\ShoppingAccessCode::hasActiveB2cSession())
            || \App\Models\ShoppingAccessCode::hasActiveInternalSession());
    $isRedLogoLocked = in_array($priceChannel, ['b2c', 'internal'], true)
        && $medicine->isRedLogo()
        && !$redLogoAccess;
    $unitKeys = ['box'];
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
        data-price-channel="<?php echo e($priceChannel); ?>"
        data-sales-channel="<?php echo e(in_array($priceChannel, ['b2c', 'internal'], true) ? 'b2c' : $priceChannel); ?>"
        data-red-logo="<?php echo e($medicine->isRedLogo() ? '1' : '0'); ?>"
        data-red-logo-locked="<?php echo e($isRedLogoLocked ? '1' : '0'); ?>"
        data-stock-units="<?php echo e($stockUnits); ?>">
    <?php $__currentLoopData = $availableUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($unit); ?>" data-price="<?php echo e($unitPriceValues[$unit]); ?>" <?php if($unit === 'box'): echo 'selected'; endif; ?>>Rp <?php echo e(number_format($unitPriceValues[$unit], 0, ',', '.')); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>

<?php if($availableUnits): ?>
    <div class="product-unit-section product-unit-section--single <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>">
        <div class="product-unit-grid product-unit-grid--single">
            <?php $__currentLoopData = $availableUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $availableQuantity = intdiv($stockUnits, max(1, (int) $unitFactors[$unit]));
                    $unitBasePrice = $unitBasePriceValues[$unit];
                    $isSelected = !$isRedLogoLocked && $loop->first;
                ?>
                <div class="product-unit-card product-unit-card--single <?php echo e($isSelected ? 'is-selected' : ''); ?> <?php echo e($isRedLogoLocked ? 'is-red-logo-locked' : ''); ?>" data-cart-unit="<?php echo e($unit); ?>">
                    <button type="button" class="product-unit-choice<?php echo e($isRedLogoLocked ? ' is-red-logo-locked' : ''); ?>" aria-pressed="<?php echo e($isSelected ? 'true' : 'false'); ?>"
                            data-unit-price="<?php echo e($unitPriceValues[$unit]); ?>"
                            data-price-target="<?php echo e($priceTargetId ?? ''); ?>"
                            data-red-logo-locked="<?php echo e($isRedLogoLocked ? '1' : '0'); ?>"
                            data-product-name="<?php echo e($medicine->nama_obat); ?>"
                            onclick="selectProductCardUnit(this)">
                        <?php if($promoPercent > 0 && $unitBasePrice > $unitPriceValues[$unit]): ?>
                            <small class="product-unit-old-price">Rp <?php echo e(number_format($unitBasePrice, 0, ',', '.')); ?></small>
                        <?php endif; ?>
                        <strong class="product-unit-price" <?php if($priceTargetId): ?> id="<?php echo e($priceTargetId); ?>" <?php endif; ?>>Rp <?php echo e(number_format($unitPriceValues[$unit], 0, ',', '.')); ?></strong>
                    </button>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php else: ?>
    <small class="product-unit-unavailable">Harga satuan produk belum tersedia.</small>
<?php endif; ?>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\partials\product-unit-selector.blade.php ENDPATH**/ ?>