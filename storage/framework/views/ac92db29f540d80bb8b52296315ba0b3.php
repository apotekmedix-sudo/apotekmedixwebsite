<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?php echo e($order->id); ?> - Apotek Medix</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f1f5f9;color:#0f172a;font-family:Arial,Helvetica,sans-serif}
        .toolbar{display:flex;justify-content:center;gap:10px;padding:16px}.toolbar a,.toolbar button{border:0;border-radius:8px;padding:10px 14px;background:#0052cc;color:white;font-weight:700;text-decoration:none;cursor:pointer}
        .toolbar a{background:white;color:#0052cc;border:1px solid #cbd5e1}
        .receipt{width:80mm;min-height:120mm;margin:0 auto 24px;padding:6mm 5mm;background:#fff;box-shadow:0 8px 26px #0f172a18;font-size:11px}
        .center{text-align:center}.logo{width:42mm;height:18mm;object-fit:contain}.store{font-weight:800;font-size:15px;margin:2px 0}.muted{color:#475569;line-height:1.45}
        .rule{border-top:1px dashed #475569;margin:10px 0}.row{display:flex;justify-content:space-between;gap:8px;margin:4px 0}.row span:last-child{text-align:right}
        .items{width:100%;border-collapse:collapse}.items td{padding:4px 0;vertical-align:top}.items .qty{white-space:nowrap;padding-left:6px}.items .price{text-align:right;white-space:nowrap;padding-left:6px}
        .total{font-size:13px;font-weight:800}.thanks{margin-top:14px;line-height:1.5}.no-print{display:block}
        @media print{body{background:#fff}.toolbar{display:none}.receipt{width:80mm;min-height:0;margin:0;padding:4mm;box-shadow:none}@page{size:80mm auto;margin:0}}
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="<?php echo e(route('cashier.dashboard')); ?>">Kembali ke dashboard</a>
        <button type="button" onclick="window.print()">Cetak struk</button>
    </div>
    <main class="receipt">
        <header class="center">
            <img class="logo" src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Logo Apotek Medix">
            <div class="store">APOTEK MEDIX</div>
            <div class="muted">ITC Cempaka Mas LT.1 No. 88, Jakarta Pusat</div>
            <div class="muted">WhatsApp: 085890007359</div>
        </header>
        <div class="rule"></div>
        <div class="row"><span>No. Pesanan</span><strong>#<?php echo e($order->id); ?></strong></div>
        <div class="row"><span>Tanggal</span><span><?php echo e($order->created_at?->format('d/m/Y H:i')); ?></span></div>
        <div class="row"><span>Kanal</span><span><?php echo e($order->transaction_channel === 'offline' ? 'Offline / Kios' : (in_array($order->buyer_type, ['apotik', 'dokter', 'pbf']) || filled($order->pbf_name) ? 'B2B' : 'B2C')); ?></span></div>
        <div class="row"><span>Pelanggan</span><span><?php echo e($order->buyer_name ?: '-'); ?></span></div>
        <div class="row"><span>Kasir</span><span><?php echo e(auth()->user()->name); ?></span></div>
        <div class="rule"></div>
        <table class="items">
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                    $price = (int) ($item['harga'] ?? $item['harga_modal'] ?? $item['price'] ?? 0);
                    $discount = min((int) ($item['potongan'] ?? $item['discount'] ?? 0), $quantity * $price);
                    $lineTotal = max(0, ($quantity * $price) - $discount);
                ?>
                <tr>
                    <td><?php echo e($item['nama_obat'] ?? $item['name'] ?? 'Produk'); ?><br><span class="muted"><?php echo e($quantity); ?> <?php echo e(strtolower($item['unit_label'] ?? 'box')); ?> × Rp <?php echo e(number_format($price, 0, ',', '.')); ?></span></td>
                    <td class="price">Rp <?php echo e(number_format($lineTotal, 0, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="2" class="center muted">Tidak ada rincian produk</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="rule"></div>
        <div class="row total"><span>TOTAL</span><span>Rp <?php echo e(number_format($order->effective_total, 0, ',', '.')); ?></span></div>
        <div class="row"><span>Metode bayar</span><span><?php echo e($order->payment_method ?: '-'); ?></span></div>
        <div class="row"><span>Status pembayaran</span><strong><?php echo e($order->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS'); ?></strong></div>
        <div class="rule"></div>
        <div class="center thanks">Terima kasih telah berbelanja di Apotek Medix.<br>Semoga lekas sehat!</div>
    </main>
    <?php if(request()->boolean('print')): ?>
        <script>window.addEventListener('load', () => window.setTimeout(() => window.print(), 350));</script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/cashier/receipt.blade.php ENDPATH**/ ?>