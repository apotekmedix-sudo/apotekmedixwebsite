<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Kios - Kasir Apotek Medix</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f8fafc;color:#1e293b;font-family:Arial,sans-serif}
        header{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1rem max(1rem,calc((100% - 1200px)/2));background:#0f172a;color:white}
        header h1{margin:0;font-size:1.25rem}main{max-width:1200px;margin:1.5rem auto;padding:0 1rem}
        .notice{padding:.85rem 1rem;margin-bottom:1rem;border-radius:.5rem;background:#dcfce7;color:#166534}
        .error{background:#fee2e2;color:#991b1b}.card{overflow-x:auto;padding:1rem;border:1px solid #e2e8f0;border-radius:.75rem;background:white}
        table{width:100%;border-collapse:collapse;min-width:760px}th,td{padding:.8rem;text-align:left;border-bottom:1px solid #e2e8f0;vertical-align:top}
        th{background:#f1f5f9;color:#475569;font-size:.8rem;text-transform:uppercase}ul{margin:0;padding-left:1.2rem}
        a,button{display:inline-block;padding:.55rem .8rem;border:0;border-radius:.45rem;background:#0e7490;color:white;text-decoration:none;font-weight:700;cursor:pointer}
        .receipt{background:#e0f2fe;color:#075985}.logout{background:transparent;border:1px solid #94a3b8;color:white}
        .empty{text-align:center;padding:3rem;color:#64748b}
    </style>
</head>
<body>
<header>
    <h1>Pesanan Kios — Kasir {{ auth()->user()->name }}</h1>
    <form action="{{ route('cashier.logout') }}" method="POST">@csrf<button class="logout" type="submit">Keluar</button></form>
</header>
<main>
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="notice error">{{ session('error') }}</div>@endif
    <div class="card">
        @if($orders->isEmpty())
            <div class="empty">Belum ada pesanan kios yang menunggu pembayaran.</div>
        @else
            <table>
                <thead><tr><th>Struk / Waktu</th><th>Pelanggan</th><th>Produk</th><th>Total</th><th>Aksi Kasir</th></tr></thead>
                <tbody>
                @foreach($orders as $order)
                    @php
                        $items = is_array($order->items) ? $order->items : [];
                        $receiptUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('orders.invoice', now()->addDays(30), ['order' => $order->id]);
                    @endphp
                    <tr>
                        <td>#{{ $order->id }}<br>{{ $order->created_at?->format('d M Y H:i') }}</td>
                        <td>{{ $order->buyer_name }}<br>{{ $order->phone ?: '-' }}</td>
                        <td><ul>@foreach($items as $item)<li>{{ $item['nama_obat'] ?? $item['name'] ?? 'Produk' }} × {{ $item['quantity'] ?? $item['qty'] ?? 0 }} {{ strtolower($item['unit_label'] ?? 'box') }}</li>@endforeach</ul></td>
                        <td><strong>Rp {{ number_format($order->effective_total, 0, ',', '.') }}</strong></td>
                        <td>
                            <a class="receipt" href="{{ $receiptUrl }}" target="_blank" rel="noopener">Lihat struk</a>
                            <form action="{{ route('cashier.orders.paid', $order) }}" method="POST" style="margin-top:.5rem;" onsubmit="return confirm('Terima pembayaran tunai untuk pesanan #{{ $order->id }}?')">
                                @csrf
                                <button type="submit">Pembayaran diterima</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="margin-top:1rem;">{{ $orders->links() }}</div>
        @endif
    </div>
</main>
</body>
</html>
