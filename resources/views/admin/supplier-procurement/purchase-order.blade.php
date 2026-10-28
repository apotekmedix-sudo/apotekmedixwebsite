<!DOCTYPE html>
<html lang="id">
@php
    $profile = $order->purchase_order_profile ?: 'medix';
    $identity = $profile === 'medikpedia'
        ? [
            'name' => 'APOTEK MEDIKPEDIA',
            'address' => 'ITC CEMPAKA MAS LT.1 NO.88, KEL. SUMUR BATU, KEC. KEMAYORAN, JAKARTA PUSAT',
            'phone' => '085890007359',
            'postal_code' => '10640',
            'website' => 'medikpedia.com',
            'logo' => 'LOGO MEDIKPEDIA.png',
            'sia' => '13042600890450001',
            'sipa' => '1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026',
            'signer' => 'Apt. Hendri Setiyono, S Farm',
            'title' => 'Apoteker',
            'stamp' => 'stempel apotek.png',
            'pharmacist_stamp' => 'stempel apoteker.png',
        ]
        : [
            'name' => 'APOTEK MEDIX',
            'address' => 'Jl. Raya Tajur No. 256 C, Bogor Timur, Kota Bogor, 16146',
            'phone' => '085890007359',
            'postal_code' => null,
            'website' => null,
            'logo' => 'LOGO APOTEK MEDIX.PNG',
            'sia' => '13042600890450001',
            'sipa' => '1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026',
            'signer' => 'Apt. Hendri Setiyono, S Farm',
            'title' => 'Apoteker Penanggung Jawab',
            'stamp' => null,
            'pharmacist_stamp' => null,
        ];
    $type = $order->purchase_order_type ?: 'regular';
    $title = match ($type) {
        'precursor' => 'SURAT PESANAN OBAT MENGANDUNG PREKURSOR FARMASI',
        'oot' => 'SURAT PESANAN OBAT-OBAT TERTENTU',
        default => 'SURAT PESANAN OBAT',
    };
    $subtitle = match ($type) {
        'precursor' => 'PREKURSOR FARMASI',
        'oot' => 'OOT',
        default => 'OBAT REGULER',
    };
    $hasRequestedDiscount = $order->items->contains(fn ($item) => (float) $item->requested_discount > 0);
    $spellNumber = function (int $number) use (&$spellNumber): string {
        $words = [
            0 => 'nol', 1 => 'satu', 2 => 'dua', 3 => 'tiga', 4 => 'empat',
            5 => 'lima', 6 => 'enam', 7 => 'tujuh', 8 => 'delapan', 9 => 'sembilan',
            10 => 'sepuluh', 11 => 'sebelas',
        ];
        if ($number < 12) return $words[$number];
        if ($number < 20) return $spellNumber($number - 10).' belas';
        if ($number < 100) return $spellNumber(intdiv($number, 10)).' puluh'.($number % 10 ? ' '.$spellNumber($number % 10) : '');
        if ($number < 200) return 'seratus'.($number > 100 ? ' '.$spellNumber($number - 100) : '');
        if ($number < 1000) return $spellNumber(intdiv($number, 100)).' ratus'.($number % 100 ? ' '.$spellNumber($number % 100) : '');
        if ($number < 2000) return 'seribu'.($number > 1000 ? ' '.$spellNumber($number - 1000) : '');
        if ($number < 1000000) return $spellNumber(intdiv($number, 1000)).' ribu'.($number % 1000 ? ' '.$spellNumber($number % 1000) : '');
        return $spellNumber(intdiv($number, 1000000)).' juta'.($number % 1000000 ? ' '.$spellNumber($number % 1000000) : '');
    };
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SP {{ $order->order_number }} - Apotek Medix</title>
    <style>
        * { box-sizing:border-box; }
        body { margin:0; color:#172b3f; background:#edf1f4; font:12px Arial,Helvetica,sans-serif; }
        .toolbar { width:min(800px,calc(100% - 28px)); margin:18px auto 0; display:flex; justify-content:flex-end; }
        .toolbar button { padding:10px 16px; border:0; border-radius:7px; background:#0755c8; color:#fff; font-weight:700; cursor:pointer; }
        .document { width:min(800px,calc(100% - 28px)); min-height:1050px; margin:10px auto 30px; padding:30px 34px 28px; border:1px solid #64748b; background:#fff; box-shadow:0 5px 22px rgba(0,0,0,.12); }
        .head { display:flex; align-items:center; gap:14px; padding-bottom:8px; border-bottom:3px solid #29445b; }
        .brand-logo { width:58px; height:58px; object-fit:contain; }
        .head h2 { margin:0 0 4px; color:#233d54; font-size:20px; text-align:left; }
        .head .address { color:#334155; font-size:11px; }
        .head .contact { margin-top:3px; color:#334155; font-size:10px; }
        h1 { margin:17px 0 2px; text-align:center; font-size:19px; line-height:1.25; }
        .order-number { margin-bottom:12px; }
        .subtitle { margin:0 0 10px; text-align:center; font-size:13px; font-weight:700; }
        .intro { margin-bottom:3px; }
        .detail-row { display:grid; grid-template-columns:152px 10px 1fr; margin:3px 0; line-height:1.3; }
        .section-title { margin:12px 0 6px; font-weight:700; }
        table { width:100%; border-collapse:collapse; table-layout:fixed; }
        th,td { padding:6px 5px; height:31px; border:1px solid #718096; text-align:left; vertical-align:top; overflow-wrap:anywhere; }
        th { background:#e6edf4; color:#27394a; font-size:10px; }
        td { color:#1f2937; font-size:10px; }
        th:first-child,td:first-child { width:5%; text-align:center; }
        .regular-table th:nth-child(2) { width:22%; }
        .regular-table th:nth-child(3) { width:14%; }
        .regular-table th:nth-child(4) { width:9%; }
        .regular-table th:nth-child(5) { width:10%; }
        .regular-table th:nth-child(6) { width:19%; }
        .regular-table th:nth-child(7) { width:21%; }
        .special-table th:nth-child(2) { width:20%; }
        .special-table th:nth-child(3) { width:19%; }
        .special-table th:nth-child(4) { width:14%; }
        .special-table th:nth-child(5) { width:11%; }
        .special-table th:nth-child(6) { width:16%; }
        .special-table th:nth-child(7) { width:15%; }
        .number { text-align:right; white-space:nowrap; }
        .order-totals { margin:8px 0 0 auto; width:min(340px,100%); text-align:right; }
        .order-discount-total { color:#64748b; font-size:10px; line-height:1.4; }
        .order-grand-total { margin-top:3px; padding-top:5px; border-top:1px solid #cbd5e1; font-size:14px; font-weight:800; }
        .notes { margin-top:11px; color:#475569; font-size:10px; line-height:1.45; white-space:pre-line; }
        .signature { width:275px; margin:16px 0 0 auto; text-align:left; line-height:1.8; }
        .signature-space { position:relative; width:275px; height:105px; }
        .signature-stamp { position:absolute; top:30%; width:310px; height:160px; object-fit:cover; object-position:center; mix-blend-mode:multiply; transform:translateY(-50%); }
        .signature-stamp-pharmacist { left:-200px; z-index:1; }
        .signature-stamp-pharmacy { left:20px; z-index:1; }
        .signature-name { font-weight:700; }
        @media print {
            body { background:#fff; }
            .toolbar { display:none; }
            .document { width:100%; min-height:0; margin:0; border:1px solid #64748b; box-shadow:none; }
            @page { size:A4; margin:10mm; }
        }
        @media(max-width:600px) {
            .document { min-height:0; padding:20px 14px; }
            h1 { font-size:16px; }
            .detail-row { grid-template-columns:125px 8px 1fr; }
            th,td { padding:4px 3px; font-size:9px; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>
    <main class="document">
        <header class="head">
            <img class="brand-logo" src="{{ asset($identity['logo']) }}" alt="Logo {{ $identity['name'] }}">
            <div>
                <h2>{{ $identity['name'] }}</h2>
                <div class="address">{{ $identity['address'] }}</div>
                @if($identity['phone'] || $identity['postal_code'] || $identity['website'])
                    <div class="contact">
                        @if($identity['phone']) Telepon: {{ $identity['phone'] }} @endif
                        @if($identity['postal_code']) · Kode Pos: {{ $identity['postal_code'] }} @endif
                        @if($identity['website']) · Website: {{ $identity['website'] }} @endif
                    </div>
                @endif
            </div>
        </header>
        <h1>{{ $title }}</h1>
        <div class="subtitle">({{ $subtitle }})</div>
        <div class="order-number">Nomor: {{ $order->order_number }}</div>
        <div class="intro">Yang bertanda tangan di bawah ini:</div>
        <div class="detail-row"><b>Nama Apoteker</b><span>:</span><span>{{ $order->requester_name ?: $identity['signer'] }}</span></div>
        <div class="detail-row"><b>Jabatan</b><span>:</span><span>{{ $order->apj ?: $identity['title'] }}</span></div>
        <div class="detail-row"><b>Nomor SIA</b><span>:</span><span>{{ $order->sia ?: $identity['sia'] }}</span></div>
        <div class="detail-row"><b>Nomor SIPA</b><span>:</span><span>{{ $order->sipa ?: $identity['sipa'] }}</span></div>
        <div class="section-title">Mengajukan pesanan kepada:</div>
        <div class="detail-row"><b>Nama PBF/Distributor</b><span>:</span><span>{{ $order->supplier_name ?: '-' }}</span></div>
        <div class="detail-row"><b>Alamat</b><span>:</span><span>{{ $order->supplier_address ?: '-' }}</span></div>
        <div class="detail-row"><b>Nomor telepon</b><span>:</span><span>{{ $order->supplier_phone ?: '-' }}</span></div>
        <div class="section-title">
            @if($type === 'precursor')
                Obat mengandung Prekursor Farmasi yang dipesan:
            @elseif($type === 'oot')
                Obat-Obat Tertentu yang dipesan:
            @else
                Dengan rincian obat yang dipesan sebagai berikut:
            @endif
        </div>
        <table class="{{ $type === 'regular' ? 'regular-table' : 'special-table' }}">
            <thead>
                @if($type === 'precursor')
                    <tr><th style="width:5%">No.</th><th>Nama Obat</th><th>Zat Aktif Prekursor</th><th>Bentuk / Kekuatan</th><th>Jumlah</th><th class="number">Harga Modal</th><th class="number">Subtotal</th></tr>
                @elseif($type === 'oot')
                    <tr><th style="width:5%">No.</th><th>Nama Obat</th><th>Zat Aktif OOT</th><th>Bentuk / Kekuatan</th><th>Jumlah</th><th class="number">Harga Modal</th><th class="number">Subtotal</th></tr>
                @else
                    <tr><th style="width:5%">No.</th><th>Nama Produk</th><th>Bentuk / Kekuatan</th><th>Satuan</th><th>Jumlah</th><th class="number">Harga Modal</th><th class="number">Subtotal</th></tr>
                @endif
            </thead>
            <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->product_name }}
                    </td>
                    @if(in_array($type, ['oot', 'precursor'], true))
                        <td>{{ $item->active_ingredient ?: '—' }}</td>
                        <td>{{ collect([$item->dosage_form, $item->strength])->filter()->implode(' / ') ?: '—' }}</td>
                        <td>{{ $type === 'precursor' ? number_format($item->quantity, 0, ',', '.') . ' (' . ucfirst($spellNumber((int) $item->quantity)) . ')' : number_format($item->quantity, 0, ',', '.') }} {{ $item->unit_label }}</td>
                        <td class="number">Rp {{ number_format((float) $item->unit_cost, 0, ',', '.') }}</td>
                        <td class="number">
                            Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}
                            @if((float) $item->requested_discount > 0)
                                <br><small>Ajuan potongan: Rp {{ number_format((float) $item->requested_discount, 0, ',', '.') }}</small>
                            @endif
                        </td>
                    @else
                        <td>{{ collect([$item->dosage_form, $item->active_ingredient, $item->strength])->filter()->implode(' / ') ?: '—' }}</td>
                        <td>{{ $item->unit_label }}</td>
                        <td>{{ number_format($item->quantity, 0, ',', '.') }}</td>
                        <td class="number">Rp {{ number_format((float) $item->unit_cost, 0, ',', '.') }}</td>
                        <td class="number">
                            Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}
                            @if((float) $item->requested_discount > 0)
                                <br><small>Ajuan potongan: Rp {{ number_format((float) $item->requested_discount, 0, ',', '.') }}</small>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="order-totals">
            @if($hasRequestedDiscount)
                <div class="order-discount-total">Total ajuan potongan: Rp {{ number_format((float) $order->items->sum(fn ($item) => (float) $item->requested_discount), 0, ',', '.') }}</div>
            @endif
            <div class="order-grand-total">Total: Rp {{ number_format((float) $order->items->sum(fn ($item) => (float) $item->line_total), 0, ',', '.') }}</div>
        </div>
        @if($hasRequestedDiscount)
            <div class="notes"><strong>Catatan:</strong> Ajuan potongan dicantumkan per produk dan belum mengurangi subtotal awal; potongan menunggu persetujuan supplier.</div>
        @endif
        @if($order->notes)<div class="notes"><strong>Catatan:</strong><br>{{ $order->notes }}</div>@endif
        <div class="signature">
            {{ $profile === 'medikpedia' ? 'Jakarta' : 'Bogor' }}, {{ $order->ordered_at?->translatedFormat('d F Y') }}
            <br>Pemesan,
            <div class="signature-space">
                @if($identity['pharmacist_stamp'])
                    <img class="signature-stamp signature-stamp-pharmacist" src="{{ asset($identity['pharmacist_stamp']) }}" alt="Stempel Apoteker">
                @endif
                @if($identity['stamp'])
                    <img class="signature-stamp signature-stamp-pharmacy" src="{{ asset($identity['stamp']) }}" alt="Stempel Apotek Medikpedia">
                @endif
            </div>
            <div class="signature-name">{{ $order->requester_name ?: $identity['signer'] }}</div>
            {{ $order->apj ?: $identity['title'] }} / No. SIPA {{ $order->sipa ?: '—' }}
        </div>
    </main>
</body>
</html>
