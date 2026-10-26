<?php
    $formatMoney = fn ($amount) => 'Rp ' . number_format((int) $amount, 0, ',', '.');
    $formatNum = fn ($amount) => number_format((int) $amount, 0, ',', '.');
    $originalTotal = (int) ($order->original_total ?: $order->total);
    $discountedTotal = (int) ($order->discounted_total ?? $order->total);
    $total = $discountedTotal;
    $discountTotal = max(0, $originalTotal - $discountedTotal);
    $orderDate = ($order->created_at ?? now())->timezone('Asia/Jakarta');
    $invoiceNo = 'INV/AM/' . $orderDate->format('Y/m') . '/' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
    $paymentDisplay = $order->payment_method ?: 'Tunai';
    if ($order->payment_method === 'Cash') {
        $paymentDisplay = 'Tunai';
    } elseif ($order->payment_method === 'Tempo' && !empty($order->payment_term_days)) {
        $paymentDisplay = 'Tempo (' . $order->payment_term_days . ' Hari)';
    }
    $customerAddress = collect([$order->address, $order->kecamatan, $order->kota])->filter()->join(', ') ?: '-';
    $outletName = $order->outlet_name ?: ($order->buyer_name ?: '-');
    $pharmacistOrDoctor = $order->requester_name ?: ($order->apj ?: ($order->buyer_name ?: '-'));
    $isB2bOrder = in_array($order->buyer_type, ['pbf', 'apotik', 'dokter'], true) || filled($order->pbf_name);
    $returnUrl = $isB2bOrder ? route('products.pbf') : route('home');
    $returnLabel = $isB2bOrder ? 'Kembali ke Belanja B2B' : 'Kembali ke Beranda';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo e($invoiceNo); ?> - Apotek Medix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --teal-dark: #134e5e;
            --teal-mid: #1d6376;
            --teal-soft: #e3eff4;
            --teal-row: #eaf2f5;
            --teal-sum: #d1e4ec;
            --border-mid: #6b8e9b;
            --border-light: #bfd2da;
            --ink: #0f172a;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 20px 14px 40px;
            background: #e2e8f0;
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', Arial, Helvetica, sans-serif;
            font-size: 12.5px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .toolbar {
            max-width: 1020px;
            margin: 0 auto 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toolbar-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .toolbar button,
        .toolbar a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 0;
            background: var(--teal-dark);
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(19, 78, 94, 0.2);
        }
        .toolbar a {
            background: #ffffff;
            color: var(--teal-dark);
            border: 1px solid #cbd5e1;
            box-shadow: none;
        }
        .toolbar button:hover { background: #0e3b47; }
        .toolbar a:hover { background: #f8fafc; }

        .invoice-sheet {
            position: relative;
            width: min(1020px, 100%);
            margin: 0 auto;
            padding: 28px 34px 32px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 12px 36px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        /* Watermark di tengah halaman */
        .invoice-watermark {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 340px;
            height: 340px;
            object-fit: contain;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
        }
        .invoice-content {
            position: relative;
            z-index: 1;
        }

        /* 1. TOP HEADER */
        .top-header {
            display: grid;
            grid-template-columns: 285px 1fr 290px;
            align-items: center;
            gap: 18px;
            padding-bottom: 14px;
            border-bottom: 2.5px solid var(--teal-dark);
        }
        .brand-logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-logo-wrap img {
            width: 78px;
            height: 78px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .brand-wordmark {
            display: flex;
            flex-direction: column;
            line-height: 0.95;
        }
        .brand-wordmark-top {
            font-size: 19px;
            font-weight: 700;
            letter-spacing: 3px;
            color: #166075;
        }
        .brand-wordmark-main {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: -1px;
            color: var(--teal-dark);
        }
        .header-contact {
            border-left: 1.5px solid #476775;
            border-right: 1.5px solid #476775;
            padding: 2px 18px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 12px;
            line-height: 1.38;
            color: #1e293b;
        }
        .contact-row {
            display: flex;
            align-items: flex-start;
            gap: 9px;
        }
        .contact-row i {
            color: var(--teal-dark);
            font-size: 13px;
            margin-top: 2px;
            width: 15px;
            text-align: center;
            flex-shrink: 0;
        }
        .apj-card {
            background: var(--teal-soft);
            border-radius: 10px;
            padding: 8px 10px 10px;
        }
        .apj-header {
            background: var(--teal-dark);
            color: #ffffff;
            font-weight: 800;
            font-size: 11.5px;
            letter-spacing: 0.3px;
            padding: 6px 12px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 8px;
        }
        .apj-list {
            margin: 0;
            padding: 0 6px;
            list-style: none;
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.55;
        }

        /* 2. BANNER & INVOICE META ROW */
        .banner-meta-row {
            display: grid;
            grid-template-columns: 1fr 315px;
            gap: 20px;
            align-items: stretch;
            margin: 16px 0;
        }
        .invoice-pill-banner {
            display: flex;
            align-items: stretch;
            background: var(--teal-soft);
            border-radius: 14px;
            overflow: hidden;
        }
        .invoice-pill-left {
            background: var(--teal-dark);
            color: #ffffff;
            padding: 14px 28px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 1.5px;
            line-height: 1;
        }
        .invoice-pill-right {
            padding: 12px 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .invoice-pill-right h2 {
            margin: 0;
            font-size: 21px;
            font-weight: 800;
            color: var(--teal-dark);
            letter-spacing: 0.4px;
        }
        .invoice-pill-right span {
            margin-top: 3px;
            font-size: 14px;
            font-weight: 500;
            color: #1e4e5d;
            letter-spacing: 2px;
        }
        .invoice-meta-card {
            background: var(--teal-soft);
            border-radius: 10px;
            padding: 11px 16px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 4px;
        }
        .meta-line {
            display: grid;
            grid-template-columns: 92px 10px 1fr;
            font-size: 12px;
            line-height: 1.35;
        }
        .meta-line strong {
            font-weight: 700;
            color: #0f172a;
        }

        /* 3. CUSTOMER & SIA/SIPA INFO BOXES */
        .info-boxes-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }
        .info-box {
            border: 1.5px solid var(--border-mid);
            border-radius: 6px;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.85);
        }
        .info-box-title {
            margin: 0 0 7px;
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .info-grid {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .info-line {
            display: grid;
            grid-template-columns: 120px 10px 1fr;
            align-items: baseline;
            font-size: 12px;
            line-height: 1.45;
        }
        .info-line-label {
            color: #1e293b;
            font-weight: 600;
        }
        .info-line-val {
            color: #0f172a;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        /* 4. ITEMS TABLE */
        .items-table-wrap {
            margin-bottom: 16px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid var(--border-light);
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.items-table thead th {
            background: var(--teal-dark);
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 9px 8px;
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.25);
        }
        table.items-table thead th:last-child {
            border-right: 0;
        }
        table.items-table tbody td {
            padding: 8px 9px;
            font-size: 12px;
            color: #0f172a;
            border-bottom: 1px solid var(--border-light);
            border-right: 1px solid var(--border-light);
            overflow-wrap: anywhere;
        }
        table.items-table tbody td:last-child {
            border-right: 0;
        }
        table.items-table tbody tr:nth-child(odd) td {
            background: rgba(255, 255, 255, 0.9);
        }
        table.items-table tbody tr:nth-child(even) td {
            background: rgba(234, 242, 245, 0.9);
        }
        .col-no { width: 5%; text-align: center; }
        .col-name { width: 23%; text-align: left; }
        .col-form { width: 17%; text-align: left; }
        .col-pack { width: 16%; text-align: left; }
        .col-qty { width: 7%; text-align: center; }
        .col-price { width: 12%; text-align: right; }
        .col-disc { width: 9%; text-align: center; }
        .col-sub { width: 11%; text-align: right; }

        /* 5. BOTTOM SECTION */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1.12fr;
            gap: 18px;
            align-items: start;
        }
        .notes-box {
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.9);
            margin-bottom: 14px;
        }
        .notes-box h3 {
            margin: 0 0 8px;
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .notes-box ol {
            margin: 0;
            padding-left: 18px;
            font-size: 11.5px;
            line-height: 1.55;
            color: #1e293b;
        }
        .notes-box li {
            margin-bottom: 3px;
        }
        .slogan-banner {
            background: var(--teal-soft);
            border-radius: 10px;
            padding: 14px 20px 12px;
            text-align: center;
        }
        .slogan-text {
            font-family: 'Caveat', 'Brush Script MT', 'Segoe Script', cursive;
            font-size: 38px;
            font-weight: 700;
            color: var(--teal-dark);
            line-height: 1.05;
            display: inline-block;
            position: relative;
            padding-bottom: 4px;
        }
        .slogan-text::after {
            content: '';
            display: block;
            width: 92%;
            height: 2.5px;
            margin: 2px auto 0;
            background: linear-gradient(90deg, var(--teal-dark) 0%, rgba(19, 78, 94, 0.15) 100%);
            border-radius: 99px;
        }

        /* Summary Table & Signatures */
        .totals-card {
            border: 1.5px solid var(--border-mid);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        table.totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.totals-table td {
            padding: 7px 14px;
            font-size: 12.5px;
            font-weight: 700;
            border-bottom: 1px solid var(--border-light);
        }
        table.totals-table td:first-child {
            width: 55%;
            border-right: 1px solid var(--border-light);
        }
        table.totals-table td:last-child {
            text-align: right;
            font-weight: 800;
        }
        table.totals-table tr.row-tinted td:first-child {
            background: var(--teal-sum);
        }
        table.totals-table tr.row-tinted td:last-child {
            background: #ffffff;
        }
        table.totals-table tr.row-white td {
            background: #ffffff;
        }
        table.totals-table tr.row-grand td {
            background: var(--teal-dark);
            color: #ffffff;
            font-size: 14.5px;
            font-weight: 800;
            padding: 10px 14px;
            border-bottom: 0;
        }
        table.totals-table tr.row-grand td:first-child {
            border-right: 1px solid rgba(255, 255, 255, 0.3);
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            text-align: center;
            align-items: end;
            padding-top: 2px;
        }
        .sig-col {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sig-greeting {
            font-size: 12px;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .sig-company {
            font-size: 12.5px;
            font-weight: 800;
            color: var(--teal-dark);
            margin-bottom: 6px;
        }
        .sig-logo-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 2px auto 6px;
        }
        .sig-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
        }
        .sig-logo-text {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #166075;
            margin-top: 2px;
            line-height: 1;
        }
        .sig-logo-main {
            font-size: 18px;
            font-weight: 900;
            color: var(--teal-dark);
            line-height: 1;
        }
        .sig-spacer {
            height: 92px;
        }
        .sig-line {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        @media (max-width: 820px) {
            .top-header,
            .banner-meta-row,
            .info-boxes-row,
            .bottom-grid {
                grid-template-columns: 1fr;
            }
            .header-contact {
                border-left: 0;
                border-right: 0;
                border-top: 1px solid #cbd5e1;
                border-bottom: 1px solid #cbd5e1;
                padding: 10px 0;
            }
            .items-table-wrap {
                overflow-x: auto;
            }
            table.items-table {
                min-width: 720px;
            }
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }
            body {
                background: #ffffff;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .invoice-sheet {
                width: 100%;
                margin: 0;
                padding: 10px 14px;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="<?php echo e($returnUrl); ?>"><i class="fa-solid fa-arrow-left"></i> <?php echo e($returnLabel); ?></a>
        <div class="toolbar-actions">
            <button type="button" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <main class="invoice-sheet">
        <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="" class="invoice-watermark" aria-hidden="true">

        <div class="invoice-content">
            <!-- 1. TOP HEADER -->
            <header class="top-header">
                <div class="brand-logo-wrap">
                    <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Logo Apotek Medix">
                    <div class="brand-wordmark">
                        <span class="brand-wordmark-top">APOTEK</span>
                        <span class="brand-wordmark-main">medix</span>
                    </div>
                </div>
                <div class="header-contact">
                    <div class="contact-row">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>Jl. Raya Ciawi Ruko No. 256C,<br>Kelurahan Sindangsari, Kecamatan Bogor Timur,<br>Kota Bogor, Provinsi Jawa Barat 16146</span>
                    </div>
                    <div class="contact-row">
                        <i class="fa-solid fa-phone"></i>
                        <span>HP. 085882717844</span>
                    </div>
                    <div class="contact-row">
                        <i class="fa-solid fa-globe"></i>
                        <span>www.apotekmedix.com</span>
                    </div>
                </div>
                <div class="apj-card">
                    <div class="apj-header">APOTEKER PENANGGUNG JAWAB :</div>
                    <ol class="apj-list">
                        <li>1. Apt. Safwan Nugraha, S. Farm</li>
                        <li>2. Apt. M. Syaiful Hidayat, S. Farm</li>
                    </ol>
                </div>
            </header>

            <!-- 2. BANNER & INVOICE META ROW -->
            <section class="banner-meta-row">
                <div class="invoice-pill-banner">
                    <div class="invoice-pill-left">INVOICE</div>
                    <div class="invoice-pill-right">
                        <h2>FAKTUR PENJUALAN</h2>
                        <span>APOTEK MEDIX</span>
                    </div>
                </div>
                <div class="invoice-meta-card">
                    <div class="meta-line">
                        <strong>No. Invoice</strong><span>:</span><span><?php echo e($invoiceNo); ?></span>
                    </div>
                    <div class="meta-line">
                        <strong>Tanggal</strong><span>:</span><span id="invoiceRealtimeClock"><?php echo e($orderDate->translatedFormat('d F Y')); ?></span>
                    </div>
                    <div class="meta-line">
                        <strong>Kasir</strong><span>:</span><span><?php echo e($order->cashier?->name ?? 'Admin'); ?></span>
                    </div>
                    <div class="meta-line">
                        <strong>Metode Bayar</strong><span>:</span><span><?php echo e($paymentDisplay); ?></span>
                    </div>
                    <?php if($order->isTempoPayment()): ?>
                        <div class="meta-line" style="color:#b91c1c;font-weight:700;">
                            <strong>Jatuh Tempo</strong><span>:</span><span><?php echo e($order->due_date_formatted); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- 3. CUSTOMER & SIA/SIPA INFO BOXES (READ-ONLY) -->
            <section class="info-boxes-row">
                <div class="info-box">
                    <h3 class="info-box-title">DATA PELANGGAN</h3>
                    <div class="info-grid">
                        <div class="info-line">
                            <span class="info-line-label">Nama Outlet</span><span>:</span>
                            <span class="info-line-val"><?php echo e($outletName); ?></span>
                        </div>
                        <div class="info-line">
                            <span class="info-line-label">Alamat</span><span>:</span>
                            <span class="info-line-val"><?php echo e($customerAddress); ?></span>
                        </div>
                        <div class="info-line">
                            <span class="info-line-label">No. Telp</span><span>:</span>
                            <span class="info-line-val"><?php echo e($order->phone ?: '-'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="info-box">
                    <h3 class="info-box-title">DATA APOTEKER / DOKTER &amp; IZIN</h3>
                    <div class="info-grid">
                        <div class="info-line">
                            <span class="info-line-label">Apoteker / Dokter</span><span>:</span>
                            <span class="info-line-val"><?php echo e($pharmacistOrDoctor); ?></span>
                        </div>
                        <div class="info-line">
                            <span class="info-line-label">No. SIA</span><span>:</span>
                            <span class="info-line-val"><?php echo e($order->sia ?: '-'); ?></span>
                        </div>
                        <div class="info-line">
                            <span class="info-line-label">No. SIPA</span><span>:</span>
                            <span class="info-line-val"><?php echo e($order->sipa ?: '-'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4. ITEMS TABLE -->
            <div class="items-table-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="col-no">No.</th>
                            <th class="col-name">Nama Barang</th>
                            <th class="col-form">Bentuk / Kekuatan</th>
                            <th class="col-pack">Kemasan</th>
                            <th class="col-qty">Qty</th>
                            <th class="col-price">Harga Satuan<br>(Rp)</th>
                            <th class="col-disc">Diskon<br>(Rp)</th>
                            <th class="col-sub">Subtotal<br>(Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $name = $item['nama_obat'] ?? $item['name'] ?? 'Produk';
                                $qty = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                                $price = (int) ($item['harga'] ?? $item['harga_modal'] ?? $item['price'] ?? 0);
                                $discount = (int) ($item['potongan'] ?? $item['discount'] ?? 0);
                                $discount = min($discount, $qty * $price);
                                $lineTotal = max(0, ($qty * $price) - $discount);
                                $bentukKekuatan = trim(collect([$item['sediaan'] ?? null, $item['dosis'] ?? null])->filter()->join(' '))
                                    ?: ($item['catatan'] ?? $item['note'] ?? 'Tablet');
                                $kemasan = $item['kemasan'] ?? $item['unit_label'] ?? 'Box';
                            ?>
                            <tr>
                                <td class="col-no"><?php echo e($index + 1); ?></td>
                                <td class="col-name"><?php echo e($name); ?></td>
                                <td class="col-form"><?php echo e($bentukKekuatan); ?></td>
                                <td class="col-pack"><?php echo e($kemasan); ?></td>
                                <td class="col-qty"><?php echo e($qty); ?></td>
                                <td class="col-price"><?php echo e($formatNum($price)); ?></td>
                                <td class="col-disc"><?php echo e($discount > 0 ? $formatNum($discount) : '0'); ?></td>
                                <td class="col-sub"><?php echo e($formatNum($lineTotal)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" style="text-align:center;padding:18px;">Tidak ada produk</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- 5. BOTTOM SECTION -->
            <section class="bottom-grid">
                <div>
                    <div class="notes-box">
                        <h3>KETERANGAN :</h3>
                        <ol>
                            <li>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan, kecuali terdapat kesalahan dari pihak apotek.</li>
                            <li>Simpan struk/invoice ini sebagai bukti pembelian.</li>
                            <li>Untuk informasi obat lebih lanjut, silakan konsultasikan dengan apoteker kami.</li>
                        </ol>
                    </div>
                    <div class="slogan-banner">
                        <span class="slogan-text">Sehat Bersama Medix</span>
                    </div>
                </div>

                <div>
                    <div class="totals-card">
                        <table class="totals-table">
                            <tr class="row-tinted">
                                <td>Total Harga</td>
                                <td><?php echo e($formatMoney($originalTotal)); ?></td>
                            </tr>
                            <tr class="row-white">
                                <td>Total Diskon</td>
                                <td><?php echo e($formatMoney($discountTotal)); ?></td>
                            </tr>
                            <tr class="row-tinted">
                                <td>Subtotal</td>
                                <td><?php echo e($formatMoney($total)); ?></td>
                            </tr>
                            <tr class="row-white">
                                <td>Pajak (jika ada)</td>
                                <td>Rp 0</td>
                            </tr>
                            <tr class="row-grand">
                                <td>TOTAL PEMBAYARAN</td>
                                <td><?php echo e($formatMoney($total)); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="signatures-grid">
                        <div class="sig-col">
                            <span class="sig-greeting">Hormat kami,</span>
                            <span class="sig-company">APOTEK MEDIX</span>
                            <div class="sig-logo-block">
                                <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Apotek Medix" class="sig-logo">
                                <span class="sig-logo-text">APOTEK</span>
                                <span class="sig-logo-main">medix</span>
                            </div>
                            <span class="sig-line">( .................................................. )</span>
                        </div>
                        <div class="sig-col">
                            <span class="sig-greeting">Pelanggan,</span>
                            <div class="sig-spacer"></div>
                            <span class="sig-line">( .................................... )</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
<?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/orders/invoice.blade.php ENDPATH**/ ?>