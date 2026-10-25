<!-- Category Breadcrumb Helper -->
<div style="padding: 1rem 0; background: #f8faff; border-bottom: 1px solid #e5e7eb;">
    <div class="container">
        <div style="display: flex; gap: 0.5rem; align-items: center; font-size: 0.85rem; color: #6b7280;">
            <a href="<?php echo e(route('home')); ?>" style="color: #1E88E5; text-decoration: none; font-weight: 600;">
                <i class="fa-solid fa-home"></i> Home
            </a>
            <span>/</span>
            <a href="<?php echo e(route('products.index')); ?>" style="color: #1E88E5; text-decoration: none; font-weight: 600;">
                Katalog
            </a>
            <?php if(isset($mainCategory)): ?>
            <span>/</span>
            <span style="color: #374151; font-weight: 600;"><?php echo e(ucfirst($mainCategory)); ?></span>
            <?php endif; ?>
            <?php if(isset($subCategory)): ?>
            <span>/</span>
            <span style="color: #1f2937; font-weight: 700;"><?php echo e(ucfirst(str_replace('-', ' ', $subCategory))); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\partials\category-breadcrumb.blade.php ENDPATH**/ ?>