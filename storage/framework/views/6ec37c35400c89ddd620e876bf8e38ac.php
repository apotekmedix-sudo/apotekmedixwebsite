<?php if($paginator->total() > 0): ?>
    <div class="inventory-pagination">
        <p class="inventory-pagination-summary">
            Menampilkan <?php echo e(number_format($paginator->firstItem(), 0, ',', '.')); ?>–<?php echo e(number_format($paginator->lastItem(), 0, ',', '.')); ?>

            dari <?php echo e(number_format($paginator->total(), 0, ',', '.')); ?> <?php echo e($label); ?>

        </p>
        <?php if($paginator->hasPages()): ?>
            <nav class="inventory-pagination-nav" aria-label="Halaman <?php echo e($label); ?>">
                <?php if($paginator->onFirstPage()): ?>
                    <span class="inventory-page-link is-disabled" aria-disabled="true">‹ Sebelumnya</span>
                <?php else: ?>
                    <a class="inventory-page-link" href="<?php echo e($paginator->previousPageUrl()); ?>">‹ Sebelumnya</a>
                <?php endif; ?>

                <?php $__currentLoopData = range(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page === $paginator->currentPage()): ?>
                        <span class="inventory-page-link is-current" aria-current="page"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <a class="inventory-page-link" href="<?php echo e($paginator->url($page)); ?>"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($paginator->hasMorePages()): ?>
                    <a class="inventory-page-link" href="<?php echo e($paginator->nextPageUrl()); ?>">Berikutnya ›</a>
                <?php else: ?>
                    <span class="inventory-page-link is-disabled" aria-disabled="true">Berikutnya ›</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\admin\inventory\pagination.blade.php ENDPATH**/ ?>