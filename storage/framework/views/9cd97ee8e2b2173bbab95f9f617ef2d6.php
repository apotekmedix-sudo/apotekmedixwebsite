<?php
    $rawLogo = mb_strtolower(trim((string) $medicine->logo));
    $logoColor = match ($rawLogo) {
        'hijau', 'green', 'logo hijau' => 'green',
        'merah', 'red', 'logo merah', 'k' => 'red',
        'biru', 'blue', 'logo biru' => 'blue',
        default => ($medicine->isRedLogo() ? 'red' : null),
    };
?>
<?php if($logoColor): ?>
    <span class="product-logo-marker product-logo-marker--<?php echo e($logoColor); ?>"
          role="img" aria-label="Logo <?php echo e(['green' => 'hijau', 'red' => 'merah', 'blue' => 'biru'][$logoColor]); ?>"><?php echo e($logoColor === 'red' ? 'K' : ''); ?></span>
<?php endif; ?>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/partials/product-logo-marker.blade.php ENDPATH**/ ?>