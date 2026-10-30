

<?php $__env->startSection('title', 'Notifikasi & Jatuh Tempo - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', '🔔 Notifikasi & Jatuh Tempo'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .notif-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.15rem;
        margin-bottom: 1.5rem;
    }
    .notif-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem 1.35rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        border-top: 4px solid #0052cc;
    }
    .notif-card.overdue { border-top-color: #dc2626; background: #fffafa; }
    .notif-card.due-soon { border-top-color: #d97706; background: #fffdf5; }
    .notif-card.unpaid { border-top-color: #0284c7; }
    .notif-card.paid { border-top-color: #10b981; }
    .notif-card-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 0.45rem;
    }
    .notif-card-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.15;
    }
    .notif-card-sub {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.35rem;
        font-weight: 600;
    }
    .notif-tabs {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .notif-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 1rem;
        border-radius: 999px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        font-size: 0.84rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.18s;
    }
    .notif-tab:hover {
        border-color: #0052cc;
        color: #0052cc;
        background: #eff6ff;
    }
    .notif-tab.active {
        background: #0052cc;
        border-color: #0052cc;
        color: #ffffff;
    }
    .notif-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .notif-badge-pill.overdue { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .notif-badge-pill.due-soon { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .notif-badge-pill.upcoming { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .notif-badge-pill.paid { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .notif-actions {
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .btn-wa-remind {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.38rem 0.75rem;
        border-radius: 8px;
        background: #10b981;
        color: #ffffff;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        border: none;
    }
    .btn-wa-remind:hover { background: #059669; color: #ffffff; }
    .btn-mark-paid {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.38rem 0.75rem;
        border-radius: 8px;
        background: #0052cc;
        color: #ffffff;
        font-size: 0.78rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
    }
    .btn-mark-paid:hover { background: #003d99; }
</style>

<div class="notif-metrics">
    <div class="notif-card overdue">
        <div class="notif-card-label">🚨 Jatuh Tempo / Terlambat</div>
        <div class="notif-card-value" style="color:#dc2626;"><?php echo e(number_format($metrics['overdue_count'])); ?></div>
        <div class="notif-card-sub">Total: Rp <?php echo e(number_format($metrics['overdue_total'], 0, ',', '.')); ?></div>
    </div>
    <div class="notif-card due-soon">
        <div class="notif-card-label">⚠️ Segera Jatuh Tempo (≤ 3 Hari)</div>
        <div class="notif-card-value" style="color:#d97706;"><?php echo e(number_format($metrics['due_soon_count'])); ?></div>
        <div class="notif-card-sub">Perlu diingatkan segera</div>
    </div>
    <div class="notif-card unpaid">
        <div class="notif-card-label">📅 Total Tagihan Tempo Aktif</div>
        <div class="notif-card-value" style="color:#0284c7;"><?php echo e(number_format($metrics['unpaid_count'])); ?></div>
        <div class="notif-card-sub">Piutang: Rp <?php echo e(number_format($metrics['unpaid_total'], 0, ',', '.')); ?></div>
    </div>
    <div class="notif-card paid">
        <div class="notif-card-label">✅ Tempo Sudah Lunas</div>
        <div class="notif-card-value" style="color:#10b981;"><?php echo e(number_format($metrics['paid_count'])); ?></div>
        <div class="notif-card-sub">Pembayaran tempo selesai</div>
    </div>
</div>

<div class="notif-tabs">
    <a href="<?php echo e(route('admin.notifications.index', ['filter' => 'unpaid'])); ?>" class="notif-tab <?php echo e($filter === 'unpaid' ? 'active' : ''); ?>">
        Belum Lunas (<?php echo e($metrics['unpaid_count']); ?>)
    </a>
    <a href="<?php echo e(route('admin.notifications.index', ['filter' => 'overdue'])); ?>" class="notif-tab <?php echo e($filter === 'overdue' ? 'active' : ''); ?>">
        🚨 Lewat / Hari Ini Jatuh Tempo (<?php echo e($metrics['overdue_count']); ?>)
    </a>
    <a href="<?php echo e(route('admin.notifications.index', ['filter' => 'due_soon'])); ?>" class="notif-tab <?php echo e($filter === 'due_soon' ? 'active' : ''); ?>">
        ⚠️ Segera ≤ 3 Hari (<?php echo e($metrics['due_soon_count']); ?>)
    </a>
    <a href="<?php echo e(route('admin.notifications.index', ['filter' => 'paid'])); ?>" class="notif-tab <?php echo e($filter === 'paid' ? 'active' : ''); ?>">
        ✅ Sudah Lunas (<?php echo e($metrics['paid_count']); ?>)
    </a>
    <a href="<?php echo e(route('admin.notifications.index', ['filter' => 'all'])); ?>" class="notif-tab <?php echo e($filter === 'all' ? 'active' : ''); ?>">
        Semua Tempo
    </a>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
        <div>
            <h2 style="font-size:1.15rem;font-weight:800;color:#0f172a;margin:0;">Daftar Tagihan & Notifikasi Pembayaran Tempo</h2>
            <p style="font-size:0.82rem;color:#64748b;margin:0.2rem 0 0;">Pantau batas waktu pembayaran Tempo pelanggan/mitra, kirim pengingat WhatsApp, atau tandai lunas.</p>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Pesanan</th>
                    <th>Pelanggan / Mitra</th>
                    <th>Waktu Pesan (WIB)</th>
                    <th>Durasi Tempo</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Status Jatuh Tempo</th>
                    <th>Total Tagihan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $filteredOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $dueStatus = $order->tempo_due_status;
                        $badgeClass = match ($dueStatus) {
                            'overdue', 'due_today' => 'overdue',
                            'due_soon' => 'due-soon',
                            'paid' => 'paid',
                            default => 'upcoming',
                        };
                        $termDays = max(1, (int) ($order->payment_term_days ?: 14));
                        $invoiceUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('orders.invoice', now()->addDays(7), ['order' => $order->id]);
                        $rawPhone = preg_replace('/[^0-9]/', '', (string) $order->phone);
                        if (str_starts_with($rawPhone, '0')) {
                            $rawPhone = '62' . substr($rawPhone, 1);
                        }
                        $waText = rawurlencode(
                            "Halo *{$order->buyer_name}*,\n\n" .
                            "Kami dari *Apotek Medix* ingin mengingatkan tagihan pembayaran *Tempo ({$termDays} hari)* untuk:\n" .
                            "- No. Pesanan: *#{$order->id}*\n" .
                            "- Tanggal Pesan: " . ($order->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') ?? '-') . " WIB\n" .
                            "- Tanggal Jatuh Tempo: *{$order->due_date_formatted}* ({$order->tempo_due_label})\n" .
                            "- Total Tagihan: *Rp " . number_format($order->effective_total, 0, ',', '.') . "*\n\n" .
                            "Mohon konfirmasi apabila pembayaran sudah dilakukan. Terima kasih atas kerja samanya!"
                        );
                    ?>
                    <tr>
                        <td>
                            <strong style="color:#0f172a;">#<?php echo e($order->id); ?></strong>
                            <div style="font-size:0.75rem;color:#64748b;"><?php echo e($order->buyer_type === 'apotik' ? 'Apotek/Mitra' : ucfirst($order->buyer_type ?? 'Umum')); ?></div>
                        </td>
                        <td>
                            <strong><?php echo e($order->buyer_name ?: '-'); ?></strong>
                            <div style="font-size:0.78rem;color:#64748b;"><?php echo e($order->phone ?: '-'); ?></div>
                        </td>
                        <td style="font-size:0.82rem;color:#475569;">
                            <?php echo e($order->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s')); ?> WIB
                        </td>
                        <td>
                            <span style="font-weight:700;color:#1e293b;"><?php echo e($termDays); ?> Hari</span>
                        </td>
                        <td>
                            <strong style="color:#0f172a;"><?php echo e($order->due_date_formatted); ?></strong>
                        </td>
                        <td>
                            <span class="notif-badge-pill <?php echo e($badgeClass); ?>">
                                <?php if(in_array($dueStatus, ['overdue', 'due_today'])): ?>
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                <?php elseif($dueStatus === 'due_soon'): ?>
                                    <i class="fa-solid fa-clock"></i>
                                <?php elseif($dueStatus === 'paid'): ?>
                                    <i class="fa-solid fa-circle-check"></i>
                                <?php else: ?>
                                    <i class="fa-solid fa-calendar-day"></i>
                                <?php endif; ?>
                                <?php echo e($order->tempo_due_label); ?>

                            </span>
                            <?php if($order->paid_at): ?>
                                <div style="font-size:0.72rem;color:#15803d;margin-top:0.25rem;font-weight:600;">
                                    Lunas: <?php echo e($order->paid_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i')); ?> WIB
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:800;color:#0f172a;">
                            Rp <?php echo e(number_format($order->effective_total, 0, ',', '.')); ?>

                        </td>
                        <td>
                            <div class="notif-actions">
                                <?php if($order->payment_status !== 'paid'): ?>
                                    <form action="<?php echo e(route('admin.notifications.mark-paid', $order)); ?>" method="POST" onsubmit="return confirm('Tandai tagihan Tempo #<?php echo e($order->id); ?> (<?php echo e(addslashes($order->buyer_name)); ?>) sebagai LUNAS?')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn-mark-paid">
                                            <i class="fa-solid fa-check"></i> Tandai Lunas
                                        </button>
                                    </form>
                                    <?php if($rawPhone): ?>
                                        <a href="https://wa.me/<?php echo e($rawPhone); ?>?text=<?php echo e($waText); ?>" target="_blank" rel="noopener" class="btn-wa-remind" title="Kirim pengingat jatuh tempo via WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i> Ingatkan WA
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="<?php echo e($invoiceUrl); ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-file-invoice"></i> Invoice
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:2.5rem;color:#64748b;">
                            Tidak ada tagihan Tempo pada kategori ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/notifications/index.blade.php ENDPATH**/ ?>