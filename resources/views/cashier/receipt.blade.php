<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->id }} - Apotek Medix</title>
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
        <a href="{{ route('cashier.dashboard') }}">Kembali ke dashboard</a>
        <button type="button" onclick="window.print()">Cetak struk</button>
    </div>
    <main class="receipt">
        <header class="center">
            <img class="logo" src="{{ asset('LOGO APOTEK MEDIX.PNG') }}" alt="Logo Apotek Medix">
            <div class="store">APOTEK MEDIX</div>
            <div class="muted">ITC Cempaka Mas LT.1 No. 88, Jakarta Pusat</div>
            <div class="muted">WhatsApp: {{ config('contacts.customer_service.display_phone') }}</div>
        </header>
        <div class="rule"></div>
        <div class="row"><span>No. Pesanan</span><strong>#{{ $order->id }}</strong></div>
        <div class="row"><span>Tanggal</span><span id="cashierReceiptRealtimeClock">{{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</span></div>
        <div class="row"><span>Waktu Pesan</span><span>{{ $order->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</span></div>
        <div class="row"><span>Kanal</span><span>{{ $order->transaction_channel === 'offline' ? 'Offline / Kios' : (in_array($order->buyer_type, ['apotik', 'dokter', 'pbf']) || filled($order->pbf_name) ? 'B2B' : 'B2C') }}</span></div>
        <div class="row"><span>Pelanggan</span><span>{{ $order->buyer_name ?: '-' }}</span></div>
        <div class="row"><span>Kasir</span><span>{{ auth()->user()->name }}</span></div>
        <div class="rule"></div>
        <table class="items">
            <tbody>
            @forelse($items as $item)
                @php
                    $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                    $price = (int) ($item['harga'] ?? $item['harga_modal'] ?? $item['price'] ?? 0);
                    $discount = min((int) ($item['potongan'] ?? $item['discount'] ?? 0), $quantity * $price);
                    $lineTotal = max(0, ($quantity * $price) - $discount);
                @endphp
                <tr>
                    <td>{{ $item['nama_obat'] ?? $item['name'] ?? 'Produk' }}<br><span class="muted">{{ $quantity }} {{ strtolower($item['unit_label'] ?? 'box') }} × Rp {{ number_format($price, 0, ',', '.') }}</span></td>
                    <td class="price">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="center muted">Tidak ada rincian produk</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="rule"></div>
        <div class="row total"><span>TOTAL</span><span>Rp {{ number_format($order->effective_total, 0, ',', '.') }}</span></div>
        <div class="row"><span>Metode bayar</span><span>{{ $order->payment_method ?: '-' }}</span></div>
        @if($order->isTempoPayment())
            <div class="row"><span>Jatuh tempo</span><span>{{ $order->due_date_formatted }}</span></div>
        @endif
        <div class="row"><span>Status pembayaran</span><strong>{{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}</strong></div>
        <div class="rule"></div>
        <div class="center thanks">Terima kasih telah berbelanja di Apotek Medix.<br>Semoga lekas sehat!</div>
    </main>
    <script>
        (() => {
            const el = document.getElementById('cashierReceiptRealtimeClock');
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
