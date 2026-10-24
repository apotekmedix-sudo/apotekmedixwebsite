

<?php $__env->startSection('title', 'Promo Diskon Produk - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', 'Promo Diskon Produk'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-title">Atur Diskon per Kanal</div>
    <p style="margin:0 0 1rem;color:#64748b;">Atur persentase promo secara terpisah untuk harga Internal, B2B, dan B2C. Isi 0 untuk menonaktifkan promo pada kanal tersebut.</p>

    <?php if(session('success')): ?>
        <div role="status" style="margin-bottom:1rem;padding:.8rem 1rem;border-radius:10px;background:#ecfdf5;color:#047857;"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div role="alert" style="margin-bottom:1rem;padding:.8rem 1rem;border-radius:10px;background:#fef2f2;color:#b91c1c;"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <form method="GET" action="<?php echo e(route('admin.promotions.index')); ?>" style="display:flex;gap:.6rem;margin-bottom:1rem;">
        <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Cari nama produk atau SKU" style="flex:1;min-width:0;padding:.65rem .8rem;border:1px solid #cbd5e1;border-radius:8px;">
        <button class="btn btn-primary" type="submit">Cari</button>
        <?php if($search): ?>
            <a class="btn btn-secondary" href="<?php echo e(route('admin.promotions.index')); ?>">Reset</a>
        <?php endif; ?>
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga Internal<br><small>Diskon %</small></th>
                    <th>Harga B2B<br><small>Diskon %</small></th>
                    <th>Harga B2C<br><small>Diskon %</small></th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $medicines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $channelPrices = [
                            'internal' => [
                                'field' => 'promo_internal_percent',
                                'percent' => $medicine->promo_internal_percent,
                                'base' => $medicine->getChannelBasePrice('internal'),
                            ],
                            'b2b' => [
                                'field' => 'promo_b2b_percent',
                                'percent' => $medicine->promo_b2b_percent,
                                'base' => $medicine->getChannelBasePrice('b2b'),
                            ],
                            'b2c' => [
                                'field' => 'promo_b2c_percent',
                                'percent' => $medicine->promo_b2c_percent,
                                'base' => $medicine->getChannelBasePrice('b2c'),
                            ],
                        ];
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo e($medicine->nama_obat); ?></strong>
                            <div style="font-size:.78rem;color:#64748b;"><?php echo e($medicine->sku ?: $medicine->kategori); ?> · <?php echo e($medicine->kelompok === 'PBF' ? 'Produk B2B' : 'Produk Internal/B2C'); ?></div>
                            <form id="promotion-<?php echo e($medicine->id); ?>" method="POST" action="<?php echo e(route('admin.promotions.update', $medicine)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="search" value="<?php echo e($search); ?>">
                            </form>
                        </td>
                        <?php $__currentLoopData = $channelPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel => $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td>
                                <div class="promo-channel-price" data-base-price="<?php echo e($price['base']); ?>" style="min-width:155px;">
                                    <div style="margin-bottom:.35rem;color:#64748b;font-size:.76rem;">Harga asli: <span>Rp <?php echo e(number_format($price['base'], 0, ',', '.')); ?></span></div>
                                    <label style="display:flex;align-items:center;gap:.35rem;margin-bottom:.4rem;">
                                        <input class="promo-percent-input" form="promotion-<?php echo e($medicine->id); ?>" type="number" name="<?php echo e($price['field']); ?>" value="<?php echo e($price['percent']); ?>" min="0" max="100" step="1" required aria-label="Diskon <?php echo e($channel); ?> untuk <?php echo e($medicine->nama_obat); ?>" style="width:72px;padding:.45rem;border:1px solid #cbd5e1;border-radius:7px;">
                                        <span>% diskon</span>
                                    </label>
                                    <strong class="promo-calculated-price" aria-live="polite" style="display:block;color:#047857;font-size:.8rem;">Harga promo: Rp <?php echo e(number_format($medicine->getChannelPromoPrice($channel), 0, ',', '.')); ?></strong>
                                </div>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td><button form="promotion-<?php echo e($medicine->id); ?>" class="btn btn-primary btn-sm" type="submit">Simpan</button></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:#64748b;">Produk tidak ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem;"><?php echo e($medicines->links()); ?></div>
</div>
<script>
document.querySelectorAll('.promo-channel-price').forEach((priceBox) => {
    const input = priceBox.querySelector('.promo-percent-input');
    const output = priceBox.querySelector('.promo-calculated-price');
    const basePrice = Number(priceBox.dataset.basePrice || 0);

    const updateCalculatedPrice = () => {
        const percentage = Math.min(100, Math.max(0, Number(input.value) || 0));
        const discountedPrice = Math.round(basePrice * (100 - percentage)) / 100;
        output.textContent = 'Harga promo: ' + new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(discountedPrice);
    };

    input.addEventListener('input', updateCalculatedPrice);
    input.addEventListener('change', updateCalculatedPrice);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/promotions/index.blade.php ENDPATH**/ ?>