<?php
    $priceChannel = $priceChannel ?? 'b2c';
    $unitPriceValues = [
        'tablet' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'tablet')),
        'strip' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'strip')),
        'box' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'box')),
    ];
    $unitBasePriceValues = [
        'tablet' => $medicine->getChannelBasePrice($priceChannel, 'tablet'),
        'strip' => $medicine->getChannelBasePrice($priceChannel, 'strip'),
        'box' => $medicine->getChannelBasePrice($priceChannel, 'box'),
    ];
    $unitFactors = [
        'tablet' => 1,
        'strip' => (int) $medicine->tablets_per_strip,
        'box' => $medicine->units_per_box,
    ];
    $unitLabels = [
        'tablet' => 'Tablet',
        'strip' => 'Strip',
        'box' => $medicine->package_unit_label,
    ];
    $availableUnits = array_filter(
        ['tablet', 'strip', 'box'],
        fn ($unit) => $unitBasePriceValues[$unit] > 0 && $unitFactors[$unit] > 0
    );
?>
<?php if($medicine->unit_packaging_enabled): ?>
    <?php if($availableUnits): ?>
    <label for="<?php echo e($unitSelectId); ?>" style="display:block;margin:0.5rem 0 0.25rem;font-size:.78rem;font-weight:700;color:#475569;">Beli per satuan</label>
    <select id="<?php echo e($unitSelectId); ?>" onchange="updateCardUnitPrice('<?php echo e($unitSelectId); ?>', '<?php echo e($priceTargetId); ?>')"
            data-unit-prices="<?php echo e(json_encode($unitPriceValues)); ?>"
            data-unit-factors="<?php echo e(json_encode($unitFactors)); ?>"
            data-unit-labels="<?php echo e(json_encode($unitLabels)); ?>"
            data-stock-units="<?php echo e((int) $medicine->stock_base_units); ?>"
            style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.5rem;background:#fff;color:#1e293b;">
        <?php $__currentLoopData = $availableUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($unit); ?>" data-price="<?php echo e($unitPriceValues[$unit]); ?>" <?php if($unit === 'box'): echo 'selected'; endif; ?>><?php echo e($unitLabels[$unit]); ?> · Rp <?php echo e(number_format($unitPriceValues[$unit], 0, ',', '.')); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <?php else: ?>
        <small style="display:block;margin-top:.5rem;color:#b45309;">Harga satuan produk belum tersedia.</small>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/partials/product-unit-selector.blade.php ENDPATH**/ ?>