<?php
    $formatMoney = fn ($amount) => 'Rp ' . number_format((int) $amount, 0, ',', '.');
    $originalTotal = (int) ($order->original_total ?: $order->total);
    $discountedTotal = (int) ($order->discounted_total ?? $order->total);
    $total = $discountedTotal;
    $discountTotal = max(0, $originalTotal - $discountedTotal);
    $numberToWords = function (int $number) use (&$numberToWords): string {
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($number < 12) return $words[$number];
        if ($number < 20) return $numberToWords($number - 10) . ' belas';
        if ($number < 100) return $numberToWords(intdiv($number, 10)) . ' puluh ' . $numberToWords($number % 10);
        if ($number < 200) return 'seratus ' . $numberToWords($number - 100);
        if ($number < 1000) return $numberToWords(intdiv($number, 100)) . ' ratus ' . $numberToWords($number % 100);
        if ($number < 2000) return 'seribu ' . $numberToWords($number - 1000);
        if ($number < 1000000) return $numberToWords(intdiv($number, 1000)) . ' ribu ' . $numberToWords($number % 1000);
        if ($number < 1000000000) return $numberToWords(intdiv($number, 1000000)) . ' juta ' . $numberToWords($number % 1000000);
        return $numberToWords(intdiv($number, 1000000000)) . ' milyar ' . $numberToWords($number % 1000000000);
    };
    $terbilang = trim(preg_replace('/\s+/', ' ', $numberToWords($total))) . ' rupiah';
    $buyerType = match ($order->buyer_type) {
        'apotik' => 'Apotik',
        'dokter' => 'Dokter',
        'pbf' => 'PBF',
        default => 'Umum',
    };
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo e($order->id); ?> - Apotek Medix</title>
    <style>
        :root { --ink: #17202a; --muted: #667085; --blue: #0879bd; --green: #8bcf43; --line: #b9c2ca; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #edf1f3; color: var(--ink); font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .toolbar { max-width: 930px; margin: 22px auto 0; display: flex; justify-content: flex-end; gap: 8px; }
        .toolbar button, .toolbar a { border: 0; background: #0879bd; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: 700; cursor: pointer; }
        .toolbar a { background: #fff; color: #0879bd; }
        .invoice { width: min(930px, calc(100% - 28px)); margin: 12px auto 30px; padding: 38px 42px 34px; background: #fff; box-shadow: 0 8px 30px rgba(16, 24, 40, .12); }
        .masthead { display: grid; grid-template-columns: 120px 1fr; align-items: center; gap: 18px; text-align: center; }
        .masthead img { width: 112px; height: 82px; object-fit: contain; margin: auto; }
        .masthead h1 { margin: 0 0 5px; color: #15508c; font-size: 17px; letter-spacing: .2px; }
        .masthead p { margin: 3px 0; font-size: 11px; }
        .masthead .tagline { font-weight: 700; color: #53616d; }
        .rule { height: 3px; margin: 17px 0 11px; background: var(--green); }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 5px 38px; margin-bottom: 14px; }
        .meta-group { display: grid; grid-template-columns: 122px 12px 1fr; line-height: 1.35; }
        .meta-label { font-weight: 700; }
        .meta-value { overflow-wrap: anywhere; }
        .section-title { margin: 7px 0 0; padding: 5px 8px; background: var(--green); color: #fff; font-size: 13px; font-weight: 800; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #18232b; padding: 4px 5px; height: 23px; overflow-wrap: anywhere; }
        th { background: #0799ce; color: white; font-size: 10px; text-align: center; }
        td { font-size: 11px; }
        td.center, th.center { text-align: center; }
        td.money { text-align: right; white-space: nowrap; }
        .product-col { width: 27%; } .desc-col { width: 24%; } .unit-col { width: 10%; } .qty-col { width: 8%; } .price-col { width: 12%; } .disc-col { width: 9%; } .total-col { width: 13%; }
        .item-note { display: block; color: var(--muted); font-size: 10px; margin-top: 2px; }
        .bottom { display: grid; grid-template-columns: 1fr 265px; gap: 22px; align-items: start; margin-top: 1px; }
        .terbilang { font-style: italic; font-weight: 700; padding-top: 5px; }
        .summary td { height: 23px; font-weight: 700; }
        .summary td:first-child { width: 45%; }
        .summary .grand td { font-size: 13px; background: #f1f7fb; }
        .notes { margin-top: 22px; }
        .notes h3, .payment h3 { margin: 0 0 7px; font-size: 12px; }
        .notes ol { margin: 0; padding-left: 22px; font-style: italic; font-weight: 700; line-height: 1.7; }
        .footer { display: grid; grid-template-columns: 1fr 220px; gap: 25px; margin-top: 18px; align-items: end; }
        .payment { line-height: 1.6; }
        .payment-row { display: grid; grid-template-columns: 100px 12px 1fr; }
        .signature { text-align: center; font-weight: 700; padding-top: 2px; }
        .signature-label { display: block; margin-bottom: 4px; }
        .signature img { width: 205px; height: 58px; object-fit: contain; margin: 0 auto; }
        .signature-address { display: block; margin-top: -1px; font-size: 9px; white-space: nowrap; }
        .signature-name { display: block; margin-top: 4px; }
        .status { display: inline-block; margin-top: 8px; padding: 4px 9px; border-radius: 99px; background: <?php echo e($order->approval_status === 'approved' ? '#dcfce7' : ($order->approval_status === 'rejected' ? '#fee2e2' : '#fff7d6')); ?>; color: <?php echo e($order->approval_status === 'approved' ? '#15803d' : ($order->approval_status === 'rejected' ? '#b91c1c' : '#946200')); ?>; }
        @media (max-width: 680px) { .invoice { padding: 22px 15px; } .masthead { grid-template-columns: 1fr; gap: 4px; } .meta, .bottom, .footer { grid-template-columns: 1fr; gap: 14px; } .meta-group { grid-template-columns: 105px 10px 1fr; } .table-wrap { overflow-x: auto; } table { min-width: 690px; } .footer { text-align: left; } }
        @media print { body { background: white; } .toolbar { display: none; } .invoice { width: 100%; margin: 0; padding: 18mm 12mm; box-shadow: none; } @page { size: A4; margin: 0; } }
    </style>
</head>
<body>
    <div class="toolbar"><a href="<?php echo e(url('/')); ?>">Kembali</a><button onclick="window.print()">Cetak / Simpan PDF</button></div>
    <main class="invoice">
        <header class="masthead">
            <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Logo Apotek Medix">
            <div>
                <h1>APOTEK MEDIX</h1>
                <p class="tagline">Melayani pembelian apotek dan retail</p>
                <p>ITC Cempaka Mas LT.1 No. 88, RT.4 RW.8, Kel. Sumur Batu, Kec. Kemayoran, Jakarta Pusat</p>
                <p>Website: www.apotekmedix.com - Whatsapp: 085890007359</p>
            </div>
        </header>
        <div class="rule"></div>
        <section class="meta">
            <div class="meta-group"><span class="meta-label">NAMA PELANGGAN</span><span>:</span><span class="meta-value"><?php echo e($order->buyer_name ?: '-'); ?></span></div>
            <?php if($order->requester_name): ?><div class="meta-group"><span class="meta-label">NAMA PEMESAN</span><span>:</span><span class="meta-value"><?php echo e($order->requester_name); ?></span></div><?php endif; ?>
            <?php if($order->outlet_name): ?><div class="meta-group"><span class="meta-label">NAMA OUTLET</span><span>:</span><span class="meta-value"><?php echo e($order->outlet_name); ?></span></div><?php endif; ?>
            <div class="meta-group"><span class="meta-label">TANGGAL</span><span>:</span><span class="meta-value"><?php echo e($order->created_at?->format('d/m/Y')); ?></span></div>
            <div class="meta-group"><span class="meta-label">JENIS PEMBELI</span><span>:</span><span class="meta-value"><?php echo e($buyerType); ?></span></div>
            <div class="meta-group"><span class="meta-label">NO. INVOICE</span><span>:</span><span class="meta-value">INV/<?php echo e($order->created_at?->format('Ymd')); ?>/<?php echo e(str_pad($order->id, 5, '0', STR_PAD_LEFT)); ?></span></div>
            <div class="meta-group"><span class="meta-label">TRANSAKSI</span><span>:</span><span class="meta-value"><?php echo e($order->transaction_channel === 'offline' ? 'Offline / Pesanan Kios' : 'Online'); ?></span></div>
            <?php if($order->transaction_channel === 'offline'): ?>
                <div class="meta-group"><span class="meta-label">STATUS BAYAR</span><span>:</span><span class="meta-value"><?php echo e($order->payment_status === 'paid' ? 'LUNAS' : 'MENUNGGU PEMBAYARAN KASIR'); ?></span></div>
            <?php endif; ?>
            <div class="meta-group"><span class="meta-label">ALAMAT</span><span>:</span><span class="meta-value"><?php echo e(collect([$order->address, $order->kecamatan, $order->kota])->filter()->join(', ') ?: '-'); ?></span></div>
            <?php
                $paymentDisplay = $order->payment_method ?: '-';
                if ($order->payment_method === 'Tempo' && !empty($order->payment_term_days)) {
                    $paymentDisplay = 'Tempo (' . $order->payment_term_days . ' hari)';
                }
            ?>
            <div class="meta-group"><span class="meta-label">PEMBAYARAN</span><span>:</span><span class="meta-value"><?php echo e($paymentDisplay); ?></span></div>
            <div class="meta-group"><span class="meta-label">NOMOR TELEPON</span><span>:</span><span class="meta-value"><?php echo e($order->phone ?: '-'); ?></span></div>
            <?php if($order->sia || $order->sipa): ?>
                <div class="meta-group"><span class="meta-label">SIA / SIPA</span><span>:</span><span class="meta-value"><?php echo e($order->sia ?: '-'); ?> / <?php echo e($order->sipa ?: '-'); ?></span></div>
            <?php endif; ?>
        </section>
        <div class="section-title">INVOICE</div>
        <div class="table-wrap">
            <table>
                <colgroup><col class="qty-col"><col class="product-col"><col class="desc-col"><col class="unit-col"><col class="qty-col"><col class="price-col"><col class="disc-col"><col class="total-col"></colgroup>
                <thead><tr><th>NO</th><th>NAMA PRODUK</th><th>KETERANGAN</th><th>SEDIAAN</th><th>QTY</th><th>HARGA</th><th>DISC.%</th><th>TOTAL</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $name = $item['nama_obat'] ?? $item['name'] ?? 'Produk';
                            $qty = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                            $price = (int) ($item['harga'] ?? $item['harga_modal'] ?? $item['price'] ?? 0);
                            $discount = (int) ($item['potongan'] ?? $item['discount'] ?? 0);
                            $discount = min($discount, $qty * $price);
                            $lineTotal = max(0, ($qty * $price) - $discount);
                        ?>
                        <tr><td class="center"><?php echo e($index + 1); ?></td><td><?php echo e($name); ?> <?php if(!empty($item['brand'])): ?><span class="item-note"><?php echo e($item['brand']); ?></span><?php endif; ?></td><td><?php echo e($item['catatan'] ?? $item['note'] ?? '-'); ?></td><td class="center"><?php echo e($item['unit_label'] ?? $item['sediaan'] ?? 'Pcs'); ?></td><td class="center"><?php echo e($qty); ?></td><td class="money"><?php echo e($formatMoney($price)); ?></td><td class="money"><?php echo e($discount ? $formatMoney($discount) : '-'); ?></td><td class="money"><?php echo e($formatMoney($lineTotal)); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="center">Tidak ada produk</td></tr>
                    <?php endif; ?>
                    <?php for($blank = count($items); $blank < 8; $blank++): ?><tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr><?php endfor; ?>
                </tbody>
            </table>
        </div>
        <div class="bottom">
            <div class="terbilang">Terbilang : <?php echo e(ucwords($terbilang)); ?></div>
            <table class="summary"><tr><td>SUBTOTAL</td><td class="money"><?php echo e($formatMoney($originalTotal)); ?></td></tr><tr><td>POTONGAN</td><td class="money"><?php echo e($discountTotal ? '- ' . $formatMoney($discountTotal) : '-'); ?></td></tr><tr class="grand"><td>TOTAL</td><td class="money"><?php echo e($formatMoney($total)); ?></td></tr></table>
        </div>
        <section class="notes"><h3>Catatan :</h3><ol><li>Harga dan ketersediaan produk mengikuti konfirmasi apotek.</li><li>Mohon simpan invoice ini sebagai bukti pemesanan.</li></ol></section>
        <footer class="footer"><div class="payment"><h3>INFORMASI PEMBAYARAN :</h3><div class="payment-row"><b>BANK</b><span>:</span><span>BCA (BANK CENTRAL ASIA)</span></div><div class="payment-row"><b>NO. REKENING</b><span>:</span><span>6540126787</span></div><div class="payment-row"><b>ATAS NAMA</b><span>:</span><span>RULY HAYKAL</span></div><p><i>Harap melakukan konfirmasi pembayaran melalui WhatsApp : 085890007359</i></p></div><div class="signature"><span class="signature-label">Hormat Kami,</span><img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Apotek Medix"><span class="signature-address">ITC Cempaka Mas Lt. 1 Blok B, Jakarta</span><span class="signature-name">Apotek Medix</span></div></footer>
    </main>
</body>
</html>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\orders\invoice.blade.php ENDPATH**/ ?>