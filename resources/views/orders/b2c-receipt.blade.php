@php
    $formatMoney = fn ($amount) => 'Rp ' . number_format((int) $amount, 0, ',', '.');
    $originalTotal = (int) ($order->original_total ?: $order->total);
    $total = (int) ($order->discounted_total ?? $order->total);
    $voucherDiscount = min((int) ($order->discount_code_amount ?? 0), max(0, $originalTotal - $total));
    $productDiscount = max(0, $originalTotal - $total - $voucherDiscount);
    $paymentMethod = $order->payment_method ?: 'Belum dipilih';
    $paymentStatus = $order->payment_status === 'paid' ? 'Lunas' : 'Menunggu pembayaran';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->id }} - Apotek Medix</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px 12px; color: #172033; background: #f1f5f9; font: 12px/1.45 Arial, sans-serif; }
        .receipt { width: 100%; max-width: 380px; margin: 0 auto; padding: 22px 20px; background: #fff; box-shadow: 0 4px 20px #0f172a1a; }
        .brand { text-align: center; }
        .brand img { display: block; width: 92px; height: 66px; margin: 0 auto 5px; object-fit: contain; }
        .brand h1 { margin: 0; color: #163c9c; font-size: 21px; letter-spacing: .5px; }
        .brand p { margin: 2px 0; }
        .brand .address { margin-top: 7px; font-size: 10px; }
        .pharmacists { margin: 9px 0 0; font-size: 10px; }
        .divider { margin: 12px 0; border: 0; border-top: 1px dashed #64748b; }
        .receipt-title { margin: 0 0 10px; text-align: center; font-size: 15px; letter-spacing: 1px; }
        .details { display: grid; grid-template-columns: 82px 1fr; gap: 3px 7px; }
        .details .label { font-weight: 700; }
        .items { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 10px; }
        .items th, .items td { padding: 5px 2px; border-bottom: 1px solid #dbe2ea; overflow-wrap: anywhere; }
        .items th { text-align: left; font-size: 9px; }
        .items th:nth-child(1) { width: 38%; }
        .items th:nth-child(2) { width: 9%; text-align: center; }
        .items th:nth-child(3) { width: 20%; text-align: right; }
        .items th:nth-child(4) { width: 13%; text-align: right; }
        .items th:nth-child(5) { width: 20%; text-align: right; }
        .items td:nth-child(2) { text-align: center; }
        .items td:nth-child(n+3) { text-align: right; }
        .items .product-name { font-weight: 700; }
        .summary { margin: 10px 0 0 auto; width: 100%; }
        .summary td { padding: 3px 0; }
        .summary td:last-child { text-align: right; }
        .summary .grand-total td { padding-top: 7px; border-top: 1px solid #172033; font-size: 14px; font-weight: 800; }
        .payment { display: flex; justify-content: space-between; gap: 10px; }
        .thanks { margin: 14px 0 0; text-align: center; font-weight: 700; }
        .well-wishes { margin: 14px 0 0; text-align: center; font-size: 11px; }
        .tagline { display: block; width: fit-content; margin: 3px auto 0; color: #163c9c; font-family: "Segoe Script", "Brush Script MT", cursive; font-size: 21px; font-style: italic; font-weight: 600; }
        .print-button { display: block; margin: 16px auto 0; padding: 9px 16px; border: 0; border-radius: 6px; color: #fff; background: #163c9c; cursor: pointer; }
        .back-button { display: block; width: fit-content; margin: 10px auto 0; color: #163c9c; font-weight: 700; text-decoration: none; }
        .home-button { display: block; width: fit-content; margin: 10px auto 0; padding: 9px 14px; border-radius: 6px; color: #fff; background: #163c9c; font-weight: 700; text-decoration: none; }
        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { width: 80mm; padding: 0; background: #fff; font-size: 10px; }
            .receipt { width: 80mm; max-width: none; padding: 4mm; box-shadow: none; }
            .brand img { width: 78px; height: 54px; }
            .print-button, .back-button, .home-button { display: none; }
        }
    </style>
</head>
<body>
    <main class="receipt">
        <header class="brand">
            <img src="{{ asset('LOGO APOTEK MEDIX.PNG') }}" alt="Logo Apotek Medix">
            <h1>APOTEK MEDIX</h1>
            <p>Jl. Raya Ciawi Ruko No. 256C, Sindangsari</p>
            <p>Bogor Timur, Kota Bogor, Jawa Barat 16146</p>
            <p>HP 0858 9000 7359 · apotekmedix.com</p>
            <p class="pharmacists">APJ: Apt. Safwan Nugraha, S. Farm<br>Apt. M. Syaiful Hidayat, S. Farm</p>
        </header>

        <hr class="divider">
        <h2 class="receipt-title">STRUK PENJUALAN</h2>
        <section class="details" aria-label="Detail transaksi">
            <span class="label">No. Transaksi</span><span>TRX/{{ $order->created_at?->timezone('Asia/Jakarta')->format('Ymd') }}/{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
            <span class="label">Tanggal</span><span id="receiptRealtimeClock">{{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</span>
            <span class="label">Waktu Pesan</span><span>{{ $order->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') ?? '-' }} WIB</span>
            <span class="label">Kasir</span><span>{{ $cashierName ?? 'Online' }}</span>
            <span class="label">Pelanggan</span><span>{{ $order->buyer_name ?: 'Umum' }}</span>
            @if($order->phone)
                <span class="label">No. Telepon</span><span>{{ $order->phone }}</span>
            @endif
        </section>

        <hr class="divider">
        <table class="items">
            <thead>
                <tr><th>Nama Barang</th><th>Qty</th><th>Harga</th><th>Disc.</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @php
                        $name = $item['nama_obat'] ?? $item['name'] ?? 'Produk';
                        $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                        $price = (int) ($item['harga'] ?? $item['harga_modal'] ?? $item['price'] ?? 0);
                        $discount = min((int) ($item['potongan'] ?? $item['discount'] ?? 0), $quantity * $price);
                        $lineTotal = max(0, ($quantity * $price) - $discount);
                    @endphp
                    <tr>
                        <td class="product-name">{{ $name }}</td>
                        <td>{{ $quantity }}</td>
                        <td>{{ number_format($price, 0, ',', '.') }}</td>
                        <td>{{ $discount ? number_format($discount, 0, ',', '.') : '-' }}</td>
                        <td>{{ number_format($lineTotal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Tidak ada produk</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="summary">
            <tr><td>Subtotal</td><td>{{ $formatMoney($originalTotal) }}</td></tr>
            @if($productDiscount)
                <tr><td>Diskon produk</td><td>- {{ $formatMoney($productDiscount) }}</td></tr>
            @endif
            @if($voucherDiscount)
                <tr><td>Diskon voucher</td><td>- {{ $formatMoney($voucherDiscount) }}</td></tr>
            @endif
            <tr class="grand-total"><td>Total</td><td>{{ $formatMoney($total) }}</td></tr>
        </table>

        <hr class="divider">
        <div class="payment"><strong>Pembayaran</strong><span>{{ $paymentMethod }}</span></div>
        @if($order->isTempoPayment())
            <div class="payment"><strong>Jatuh Tempo</strong><span>{{ $order->due_date_formatted }}</span></div>
        @endif
        <div class="payment"><strong>Status</strong><span>{{ $paymentStatus }}</span></div>
        <p class="well-wishes">Semoga lekas sembuh</p>
        <span class="tagline">Sehat Bersama Medix</span>
        <button class="print-button" type="button" onclick="window.print()">Cetak Struk</button>
        @if(!($adminReceipt ?? false))
            <a class="home-button" href="{{ route('home') }}">Kembali ke Beranda</a>
        @endif
        @if($adminReceipt ?? false)
            <a class="back-button" href="{{ route('cashier.dashboard') }}">Kembali ke dashboard admin</a>
        @endif
    </main>
    <script>
        (() => {
            const el = document.getElementById('receiptRealtimeClock');
            const updateReceiptClock = () => {
                if (!el) return;
                const now = new Date();
                const parts = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                }).formatToParts(now).reduce((acc, p) => (acc[p.type] = p.value, acc), {});
                el.textContent = `${parts.day}/${parts.month}/${parts.year} ${parts.hour}:${parts.minute}:${parts.second} WIB`;
            };
            updateReceiptClock();
            window.setInterval(updateReceiptClock, 1000);
            window.addEventListener('beforeprint', updateReceiptClock);
        })();
    </script>
    @if(request()->boolean('print'))
        <script>window.addEventListener('load', () => window.setTimeout(() => window.print(), 350));</script>
    @endif
</body>
</html>
