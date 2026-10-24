<?php
    $gallery = $medicine->image_gallery;
?>
<a class="product-gallery-link" href="<?php echo e(route('medicines.show', $medicine->id)); ?>" aria-label="Lihat detail <?php echo e($medicine->nama_obat); ?>">
    <?php if($gallery): ?>
        <img class="product-gallery-photo product-gallery-primary" src="<?php echo e($medicine->image_url); ?>" alt="<?php echo e($medicine->nama_obat); ?>" loading="lazy">
        <?php if(count($gallery) > 1): ?>
            <img class="product-gallery-photo product-gallery-secondary" src="<?php echo e(url('storage/' . $gallery[1])); ?>" alt="<?php echo e($medicine->nama_obat); ?> - foto kedua" loading="lazy">
        <?php endif; ?>
    <?php else: ?>
        <img class="product-placeholder-logo" src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Logo Apotek Medix" loading="lazy">
    <?php endif; ?>
</a>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\partials\product-gallery-image.blade.php ENDPATH**/ ?>