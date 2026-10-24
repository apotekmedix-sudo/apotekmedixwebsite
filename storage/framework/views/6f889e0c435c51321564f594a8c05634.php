

<?php $__env->startSection('title', 'Dashboard - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', '📊 Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<div class="stats-grid" style="margin-top:1rem;">
    <div class="stat-card">
        <div class="stat-label">Total Produk</div>
        <div class="stat-value" id="stat-total"><?php echo e($totalProduk); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Stok</div>
        <div class="stat-value" style="color:#1E88E5;" id="stat-stok"><?php echo e(number_format($totalStok)); ?></div>
    </div>
    <div class="stat-card" style="border-top-color:#f59e0b;">
        <div class="stat-label">Stok Rendah (&lt;5)</div>
        <div class="stat-value" style="color:#f59e0b;" id="stat-lowstok"><?php echo e($lowStok); ?></div>
    </div>
    <div class="stat-card" style="border-top-color:#8b5cf6;">
        <div class="stat-label">Total Omzet</div>
        <div class="stat-value" style="color:#8b5cf6;"><?php echo e('Rp ' . number_format($totalOmzet, 0, ',', '.')); ?></div>
    </div>
</div>


<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;">
    <?php $__currentLoopData = $perKategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kat => $jumlah): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $icon  = match($kat) { 'OBAT' => '💊', 'SKINCARE & KOSMETIK' => '✨', 'ALAT KESEHATAN' => '🩺', default => '📦' };
            $color = match($kat) { 'OBAT' => '#1E88E5', 'SKINCARE & KOSMETIK' => '#c2185b', 'ALAT KESEHATAN' => '#2e7d32', default => '#6b7280' };
        ?>
        <div class="stat-card" style="border-top-color:<?php echo e($color); ?>;">
            <div class="stat-label"><?php echo e($icon); ?> <?php echo e($kat); ?></div>
            <div class="stat-value" style="color:<?php echo e($color); ?>;"><?php echo e($jumlah); ?></div>
            <div style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">produk</div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;font-size:0.8rem;color:#6b7280;">
    <span id="realtime-dot" style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;animation:pulse 2s infinite;"></span>
    <span id="realtime-status">Memuat data realtime...</span>
    <span id="realtime-time" style="margin-left:auto;"></span>
</div>
<style>
@keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.5; transform:scale(1.3); } }
</style>


<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <div class="card-title" style="margin:0;">🆕 Produk Terbaru</div>
        <a href="<?php echo e(route('admin.produk.index')); ?>" style="font-size:0.8rem;color:#1E88E5;text-decoration:none;">Lihat semua →</a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Pabrik/Merek</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Ditambahkan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="produk-tbody">
                <?php $__empty_1 = true; $__currentLoopData = $latestProduk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($p->nama_obat); ?></strong></td>
                        <td>
                            <?php $icon = match($p->kategori_produk) { 'SKINCARE & KOSMETIK' => '✨', 'ALAT KESEHATAN' => '🩺', default => '💊' }; ?>
                            <span style="font-size:0.82rem;"><?php echo e($icon); ?> <?php echo e($p->kategori_produk); ?></span>
                        </td>
                        <td style="font-size:0.82rem;color:#6b7280;"><?php echo e($p->kategori); ?></td>
                        <td><?php echo e($p->getFormattedPrice()); ?></td>
                        <td>
                            <?php if(($p->stock_base_units ?? $p->stok) > 10): ?>
                                <span style="color:#10b981;font-weight:700;"><?php echo e($p->stock_breakdown); ?></span>
                            <?php elseif(($p->stock_base_units ?? $p->stok) > 0): ?>
                                <span style="color:#f59e0b;font-weight:700;"><?php echo e($p->stock_breakdown); ?></span>
                            <?php else: ?>
                                <span style="color:#ef4444;font-weight:700;">Habis</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.82rem;color:#9ca3af;"><?php echo e($p->created_at->format('d M Y H:i')); ?></td>
                        <td><a href="<?php echo e(route('admin.produk.edit', $p->id)); ?>" class="btn btn-secondary btn-sm">Edit</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" style="text-align:center;color:#6b7280;padding:2rem;">Belum ada produk.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <div class="card-title" style="margin:0;">🧾 Riwayat Pembelian</div>
        <a href="<?php echo e(route('admin.purchase-history.index')); ?>" style="font-size:0.8rem;color:#1E88E5;text-decoration:none;">Lihat semua →</a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pembeli</th>
                    <th>Jenis</th>
                    <th>Total</th>
                    <th>Nomor</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="font-size:0.82rem;color:#6b7280;"><?php echo e($order->created_at->format('d M Y H:i')); ?></td>
                        <td><strong><?php echo e($order->buyer_name); ?></strong></td>
                        <td><?php echo e($order->buyer_type === 'apotik' ? 'Apotik' : 'Umum'); ?></td>
                        <td><?php echo e('Rp ' . number_format($order->total, 0, ',', '.')); ?></td>
                        <td style="font-size:0.82rem;color:#6b7280;"><?php echo e($order->sia ?? $order->sipa ?? '-'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" style="text-align:center;color:#6b7280;padding:2rem;">Belum ada pembelian.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="card">
    <div class="card-title">⚡ Aksi Cepat</div>
    <div class="quick-actions-grid" style="display:flex;gap:0.75rem;flex-wrap:wrap;">
        <a href="<?php echo e(route('admin.produk.create')); ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Produk
        </a>
        <a href="<?php echo e(route('admin.produk.import')); ?>" class="btn btn-secondary">
            <i class="fa-solid fa-file-import"></i> Import Excel
        </a>
        <a href="<?php echo e(route('admin.produk.index')); ?>" class="btn btn-secondary">
            <i class="fa-solid fa-list"></i> Semua Produk
        </a>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
async function fetchStats() {
    try {
        const res  = await fetch('<?php echo e(route("admin.dashboard.stats")); ?>');
        const data = await res.json();
        document.getElementById('stat-total').textContent   = data.total;
        document.getElementById('stat-lowstok').textContent = data.lowStok;
        document.getElementById('realtime-status').textContent = 'Realtime aktif';
        document.getElementById('realtime-dot').style.background = '#10b981';
        const now = new Date();
        document.getElementById('realtime-time').textContent = 'Update: ' + now.toLocaleTimeString('id-ID');
    } catch (e) {
        document.getElementById('realtime-status').textContent = 'Gagal memuat data';
        document.getElementById('realtime-dot').style.background = '#ef4444';
    }
}
fetchStats();
setInterval(fetchStats, 15000);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>