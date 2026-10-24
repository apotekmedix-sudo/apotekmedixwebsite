@php
    $money = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
    $buyerType = match ($order->buyer_type) {
        'apotik' => 'Apotik',
        'dokter' => 'Dokter',
        default => 'Umum',
    };
    $total = 0;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pesanan {{ $order->id }} - Apotek Medix</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; background: #e9edf0; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .toolbar { width: min(920px, calc(100% - 28px)); margin: 18px auto 0; display: flex; justify-content: flex-end; gap: 8px; }
        .toolbar button { border: 0; border-radius: 4px; padding: 9px 14px; color: #fff; font-weight: 700; cursor: pointer; }
        .toolbar .print-button { background: #344054; }
        .toolbar .download-button { background: #1168a5; }
        .paper { width: min(920px, calc(100% - 28px)); margin: 10px auto 30px; padding: 25px 30px 35px; background: #fff; box-shadow: 0 5px 22px rgba(0,0,0,.12); }
        .head { text-align: center; padding-bottom: 7px; font-family: "Times New Roman", Times, serif; }
        .head h2 { margin: 0 0 2px; font-size: 18px; line-height: 1.1; letter-spacing: .1px; }
        .head .address { font-size: 12px; font-weight: 700; line-height: 1.15; text-transform: uppercase; }
        h1 { margin: 16px 0 3px; text-align: center; font-family: "Times New Roman", Times, serif; font-size: 17px; }
        .order-number { text-align: center; font-family: "Times New Roman", Times, serif; font-weight: 700; border-bottom: 3px double #111; padding-bottom: 5px; margin-bottom: 14px; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 5px 35px; margin-bottom: 13px; }
        .detail-row { display: grid; grid-template-columns: 112px 10px 1fr; line-height: 1.35; }
        .detail-row b { font-weight: 700; }
        .recipient { margin: 0; padding-left: 10px; min-height: 67px; }
        .recipient-title { font-weight: 700; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #111; padding: 4px 5px; height: 22px; }
        th { background: #069bd1; text-align: center; font-size: 11px; }
        td { font-size: 11px; }
        .center { text-align: center; } .right { text-align: right; white-space: nowrap; }
        .no { width: 5%; } .principle { width: 18%; } .product { width: 44%; } .qty { width: 9%; } .price { width: 12%; } .line-total { width: 12%; }
        .footer-note { margin-top: 16px; display: grid; grid-template-columns: 1fr 245px; gap: 20px; }
        .signature { text-align: center; font-weight: 700; padding-top: 22px; }
        .signature img { display: block; width: 175px; height: 52px; object-fit: contain; margin: 6px auto 0; }
        .signature small { display: block; font-size: 9px; margin-top: -1px; }
        .signature strong { display: block; margin-top: 4px; }
        @media (max-width: 650px) { .paper { padding: 18px 12px; } .details { grid-template-columns: 1fr; } .recipient { margin: 10px 0 0; padding: 0; } .footer-note { grid-template-columns: 1fr; } .table-wrap { overflow-x: auto; } table { min-width: 650px; } }
        @media print { body { background: #fff; } .toolbar { display: none; } .paper { width: 100%; margin: 0; box-shadow: none; } @page { size: A4; margin: 0; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="print-button" onclick="window.print()">Print</button>
        <button class="download-button" onclick="downloadPdf()">Download PDF</button>
    </div>
    <main class="paper">
        <header class="head"><h2>APOTEK MEDIX</h2><div class="address">ITC CEMPAKA MAS LT.1 NO.88, KEL. SUMUR BATU, KEC. KEMAYORAN, JAKARTA PUSAT</div></header>
        <h1>SURAT PESANAN</h1>
        <div class="order-number">NO. PESANAN : MDK - PO.{{ $order->created_at?->format('ymd') }}.{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
        <div class="details">
            <div><div>Yang bertanda tangan di bawah ini :</div><div class="detail-row"><b>Tanggal</b><span>:</span><span>{{ $order->created_at?->format('d/m/Y') }}</span></div><div class="detail-row"><b>Nama</b><span>:</span><span>{{ $order->requester_name ?: 'Apt. Hendri Setiyono, S Farm' }}</span></div><div class="detail-row"><b>Jabatan</b><span>:</span><span>{{ $order->apj ?: 'Apoteker' }}</span></div><div class="detail-row"><b>No. SIA</b><span>:</span><span>{{ $order->sia ?: '13042600890450001' }}</span></div><div class="detail-row"><b>No. SIPA</b><span>:</span><span>{{ $order->sipa ?: '1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026' }}</span></div></div>
            <div class="recipient"><div class="recipient-title">Mengajukan pesanan obat kepada :</div><div class="detail-row"><b>Nama PBF</b><span>:</span><span>{{ $order->pbf_name ?: '-' }}</span></div><div class="detail-row"><b>Alamat</b><span>:</span><span>{{ $order->pbf_address ?: '-' }}</span></div></div>
        </div>
        <div class="table-wrap"><table><colgroup><col class="no"><col class="principle"><col class="product"><col class="qty"><col class="price"><col class="line-total"></colgroup><thead><tr><th>NO</th><th>PRINCIPLE</th><th>NAMA PRODUK</th><th>QTY</th><th>MODAL</th><th>TOTAL</th></tr></thead><tbody>
            @forelse($items as $index => $item)
                @php $qty = (int) ($item['quantity'] ?? $item['qty'] ?? 0); $price = (int) ($item['harga_modal'] ?? $item['modal'] ?? $item['harga'] ?? $item['price'] ?? 0); $lineTotal = $qty * $price; $total += $lineTotal; @endphp
                <tr><td class="center">{{ $index + 1 }}</td><td>{{ $item['brand'] ?? $item['principle'] ?? '-' }}</td><td>{{ $item['nama_obat'] ?? $item['name'] ?? '-' }}</td><td class="center">{{ $qty }}</td><td class="right">{{ $money($price) }}</td><td class="right">{{ $money($lineTotal) }}</td></tr>
            @empty <tr><td colspan="6" class="center">Belum ada produk</td></tr>@endforelse
            @for($blank = count($items); $blank < 12; $blank++)<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>@endfor
        </tbody></table></div>
        <div class="footer-note"><div><p><b>Catatan :</b></p><p>Mohon diproses sesuai dengan jumlah dan harga modal yang tercantum.</p></div><div class="signature">Hormat Kami,<img src="{{ asset('LOGO APOTEK MEDIX.PNG') }}" alt="Apotek Medix"><small>ITC Cempaka Mas Lt. 1, Jakarta</small><strong>Apotek Medix</strong></div></div>
    </main>
</body>
<script>
    function downloadPdf() {
        document.title = 'Surat-Pesanan-Apotek Medix-{{ $order->id }}';
        window.print();
    }
</script>
</html>
