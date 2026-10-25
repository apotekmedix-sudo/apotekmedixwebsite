<?php
    $logoColor = match (mb_strtolower(trim((string) $medicine->logo))) {
        'hijau', 'green' => 'green',
        'merah', 'red' => 'red',
        'biru', 'blue' => 'blue',
        default => null,
    };
?>
<?php if($logoColor): ?>
    <span class="product-logo-marker product-logo-marker--<?php echo e($logoColor); ?>"
          role="img" aria-label="Logo <?php echo e(['green' => 'hijau', 'red' => 'merah', 'blue' => 'biru'][$logoColor]); ?>"></span>
<?php endif; ?>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\partials\product-logo-marker.blade.php ENDPATH**/ ?>