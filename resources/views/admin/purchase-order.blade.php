@php
    $type = $order->purchase_order_type ?: 'regular';
    $profile = $order->purchase_order_profile ?: 'medix';
    $identity = $profile === 'medikpedia'
        ? [
            'name' => 'APOTEK MEDIKPEDIA',
            'address' => 'ITC CEMPAKA MAS LT.1 NO.88, KEL. SUMUR BATU, KEC. KEMAYORAN, JAKARTA PUSAT',
            'phone' => '08159111911',
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
            'phone' => null,
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
    $spellNumber = function (int $number) use (&$spellNumber): string {
        $words = [
            0 => 'nol', 1 => 'satu', 2 => 'dua', 3 => 'tiga', 4 => 'empat',
            5 => 'lima', 6 => 'enam', 7 => 'tujuh', 8 => 'delapan', 9 => 'sembilan',
            10 => 'sepuluh', 11 => 'sebelas',
        ];
        if ($number < 12) {
            return $words[$number];
        }
        if ($number < 20) {
            return $spellNumber($number - 10).' belas';
        }
        if ($number < 100) {
            return $spellNumber(intdiv($number, 10)).' puluh'.($number % 10 ? ' '.$spellNumber($number % 10) : '');
        }
        if ($number < 200) {
            return 'seratus'.($number > 100 ? ' '.$spellNumber($number - 100) : '');
        }
        if ($number < 1000) {
            return $spellNumber(intdiv($number, 100)).' ratus'.($number % 100 ? ' '.$spellNumber($number % 100) : '');
        }
        if ($number < 2000) {
            return 'seribu'.($number > 1000 ? ' '.$spellNumber($number - 1000) : '');
        }
        if ($number < 1000000) {
            return $spellNumber(intdiv($number, 1000)).' ribu'.($number % 1000 ? ' '.$spellNumber($number % 1000) : '');
        }
        return $spellNumber(intdiv($number, 1000000)).' juta'.($number % 1000000 ? ' '.$spellNumber($number % 1000000) : '');
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ $identity['name'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #172b3f; background: #edf1f4; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .toolbar { width: min(800px, calc(100% - 28px)); margin: 18px auto 0; display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: 8px; }
        .toolbar button { border: 0; border-radius: 4px; padding: 9px 14px; color: #fff; font-weight: 700; cursor: pointer; }
        .toolbar .print-button { background: #344054; }
        .toolbar label { font-weight: 700; }
        .toolbar select { padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff; color: #172b3f; }
        .paper { width: min(800px, calc(100% - 28px)); min-height: 1050px; margin: 10px auto 30px; padding: 30px 34px 28px; border: 1px solid #64748b; background: #fff; box-shadow: 0 5px 22px rgba(0,0,0,.12); }
        .head { display:flex; align-items:center; gap:14px; border-bottom: 3px solid #29445b; padding-bottom: 8px; }
        .brand-logo { width:58px; height:58px; object-fit:contain; }
        .head h2 { margin: 0 0 4px; color: #233d54; font-size: 20px; }
        .head .address { color: #334155; font-size: 11px; }
        .head .contact { margin-top: 3px; color: #334155; font-size: 10px; }
        h1 { margin: 17px 0 2px; text-align: center; font-size: 19px; line-height: 1.25; }
        .subtitle { margin: 0 0 10px; text-align: center; font-size: 13px; font-weight: 700; }
        .order-number { margin-bottom: 12px; }
        .intro { margin-bottom: 3px; }
        .detail-row { display: grid; grid-template-columns: 152px 10px 1fr; margin: 3px 0; line-height: 1.3; }
        .section-title { margin: 12px 0 6px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #718096; padding: 6px 5px; height: 31px; text-align: left; overflow-wrap: anywhere; }
        th { background: #e6edf4; color: #27394a; font-size: 10px; }
        td { color: #1f2937; font-size: 11px; }
        th:first-child, td:first-child { width: 7%; text-align: center; }
        .regular-table th:nth-child(2) { width: 24%; }
        .regular-table th:nth-child(3) { width: 27%; }
        .regular-table th:nth-child(4) { width: 20%; }
        .regular-table th:nth-child(5) { width: 22%; }
        .special-table th:nth-child(2) { width: 21%; }
        .special-table th:nth-child(3) { width: 25%; }
        .special-table th:nth-child(4) { width: 22%; }
        .special-table th:nth-child(5) { width: 25%; }
        .center { text-align: center; }
        .notes { margin-top: 11px; color: #475569; font-size: 10px; line-height: 1.45; }
        .signature { width: 275px; margin: 16px 0 0 auto; text-align: left; line-height: 1.8; }
        .signature-space { position:relative; width:300px; height:105px; }
        .signature-stamp { position:absolute; top:30%; width:1320px; height:640px; object-fit:cover; object-position:center; mix-blend-mode:multiply; transform:translateY(-50%); }
        .signature-stamp-pharmacist { left:-2000px; z-index:1; }
        .signature-stamp-pharmacy { left:-100px ; z-index:1; }
        .signature-name { font-weight: 700; }
        @media (max-width: 620px) {
            .paper { min-height: 0; padding: 20px 14px; }
            h1 { font-size: 16px; }
            .detail-row { grid-template-columns: 125px 8px 1fr; }
            th, td { padding: 4px 3px; font-size: 9px; }
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .paper { width: 100%; min-height: 0; margin: 0; padding: 0; border: 0; box-shadow: none; }
            .head { gap: 2mm; padding-bottom: 1mm; }
            .brand-logo { width: 14mm; height: 14mm; }
            h1 { margin: 3mm 0 1mm; font-size: 14pt; }
            .subtitle { margin: 0 0 2mm; font-size: 9pt; }
            table { width: 100%; }
            th, td { padding: 1mm .8mm; font-size: 8pt; line-height: 1.2; }
            .detail-row { grid-template-columns: 38mm 3mm 1fr; margin: 1mm 0; font-size: 8.5pt; line-height: 1.2; }
            .section-title { margin: 2mm 0 1mm; font-size: 8.5pt; }
            .notes { margin-top: 2mm; font-size: 7.5pt; line-height: 1.25; }
            .signature { width: 70mm; margin: 3mm 0 0 auto; font-size: 8pt; line-height: 1.3; }
            .signature-space { width: 70mm; height: 14mm; }
            .signature-stamp { width: 45mm; height: 24mm; }
            .signature-stamp-pharmacist { left: -25mm; }
            .signature-stamp-pharmacy { left: 4mm; }
            .signature, .notes, tr, .detail-row { break-inside: avoid; page-break-inside: avoid; }
            thead { display: table-header-group; }
            th, td, .detail-row > span:last-child { overflow-wrap: anywhere; }
        }
        @media print {
            body[data-paper-size="a5"] { font-size: 7pt; }
            body[data-paper-size="a5"] h1 { font-size: 10pt; }
            body[data-paper-size="a5"] .subtitle,
            body[data-paper-size="a5"] .section-title,
            body[data-paper-size="a5"] .detail-row { font-size: 7pt; }
            body[data-paper-size="a5"] .detail-row { grid-template-columns: 27mm 2mm 1fr; }
            body[data-paper-size="a5"] th,
            body[data-paper-size="a5"] td { padding: .8mm .5mm; font-size: 6.5pt; line-height: 1.15; }
            body[data-paper-size="a5"] .notes { font-size: 6.5pt; }
            body[data-paper-size="a5"] .signature { width: 45mm; font-size: 7pt; }
            body[data-paper-size="a5"] .signature-space { width: 45mm; height: 12mm; }
            body[data-paper-size="f4"] .detail-row { font-size: 8pt; }
            body[data-paper-size="f4"] th,
            body[data-paper-size="f4"] td { font-size: 7.5pt; }
            body[data-paper-size="f5"] { font-size: 7.5pt; }
            body[data-paper-size="f5"] h1 { font-size: 10.5pt; }
            body[data-paper-size="f5"] .subtitle,
            body[data-paper-size="f5"] .section-title,
            body[data-paper-size="f5"] .detail-row { font-size: 7.5pt; }
            body[data-paper-size="f5"] .detail-row { grid-template-columns: 29mm 2mm 1fr; }
            body[data-paper-size="f5"] th,
            body[data-paper-size="f5"] td { padding: 1mm .7mm; font-size: 7pt; line-height: 1.2; }
            body[data-paper-size="f5"] .notes { font-size: 7pt; }
            body[data-paper-size="f5"] .signature { width: 48mm; font-size: 7.5pt; }
            body[data-paper-size="f5"] .signature-space { width: 48mm; height: 12mm; }
        }
    </style>
    <style id="print-page-size"></style>
</head>
<body>
    <div class="toolbar">
        <label for="paper-size">Ukuran kertas:</label>
        <select id="paper-size" aria-label="Ukuran kertas untuk cetak SP">
            <option value="210mm 297mm" data-paper-size="a4" data-margin="7mm">A4</option>
            <option value="148mm 210mm" data-paper-size="a5" data-margin="5mm">A5</option>
            <option value="215mm 330mm" data-paper-size="f4" data-margin="7mm">F4</option>
            <option value="165mm 215mm" data-paper-size="f5" data-margin="5mm">F5</option>
        </select>
        <button class="print-button" type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <main class="paper">
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
        <div class="order-number">Nomor SP: {{ $order->purchase_order_profile === 'medikpedia' ? 'MDK' : 'MDX' }}/SP/{{ strtoupper($type) }}/{{ $order->created_at?->format('Ymd') }}/{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
        <div class="intro">Yang bertanda tangan di bawah ini:</div>
        <div class="detail-row"><b>Nama Apoteker</b><span>:</span><span>{{ $order->requester_name ?: $identity['signer'] }}</span></div>
        <div class="detail-row"><b>Jabatan</b><span>:</span><span>{{ $order->apj ?: $identity['title'] }}</span></div>
        <div class="detail-row"><b>Nomor SIA</b><span>:</span><span>{{ $order->sia ?: $identity['sia'] }}</span></div>
        <div class="detail-row"><b>Nomor SIPA</b><span>:</span><span>{{ $order->sipa ?: $identity['sipa'] }}</span></div>
        <div class="section-title">Mengajukan pesanan kepada:</div>
        <div class="detail-row"><b>Nama PBF/Distributor</b><span>:</span><span>{{ $order->pbf_name ?: '-' }}</span></div>
        <div class="detail-row"><b>Alamat</b><span>:</span><span>{{ $order->pbf_address ?: '-' }}</span></div>
        <div class="detail-row"><b>Nomor telepon</b><span>:</span><span>{{ $order->pbf_phone ?: '-' }}</span></div>
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
                    <tr><th>No.</th><th>Nama Obat</th><th>Zat Aktif Prekursor</th><th>Bentuk / Kekuatan</th><th>Jumlah angka &amp; huruf</th></tr>
                @elseif($type === 'oot')
                    <tr><th>No.</th><th>Nama Obat</th><th>Zat Aktif OOT</th><th>Bentuk / Kekuatan</th><th>Jumlah</th></tr>
                @else
                    <tr><th>No.</th><th>Nama Obat</th><th>Bentuk / Kekuatan</th><th>Satuan</th><th>Jumlah</th></tr>
                @endif
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    @php
                        $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                        $name = $item['nama_obat'] ?? $item['name'] ?? '-';
                        $activeIngredient = $item['komposisi'] ?? '-';
                        $formStrength = collect([$item['sediaan'] ?? null, $item['komposisi'] ?? null, $item['dosis'] ?? null])->filter()->implode(' / ') ?: '-';
                        $unit = $item['unit_label'] ?? $item['package_unit_label'] ?? 'Kemasan';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $name }}</td>
                        @if($type === 'precursor')
                            <td>{{ $activeIngredient }}</td>
                            <td>{{ $formStrength }}</td>
                            <td>{{ $quantity }} ({{ ucfirst($spellNumber($quantity)) }}) {{ $unit }}</td>
                        @elseif($type === 'oot')
                            <td>{{ $activeIngredient }}</td>
                            <td>{{ $formStrength }}</td>
                            <td>{{ $quantity }} {{ $unit }}</td>
                        @else
                            <td>{{ $formStrength }}</td>
                            <td>{{ $unit }}</td>
                            <td>{{ $quantity }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="notes">
            @if($type === 'precursor')
                Prekursor dipesan terpisah dari pesanan barang lain. Cantumkan jumlah dalam angka dan huruf serta satuan/isi kemasan.
                Pastikan format, jumlah rangkap, identitas, dan ketentuan penandatanganan mengikuti regulasi terbaru dan persyaratan PBF.
            @elseif($type === 'oot')
                OOT dipesan terpisah dari pesanan obat lain. Gunakan hanya untuk obat yang masuk kategori OOT sesuai ketentuan yang berlaku.
                Cantumkan identitas sarana pemesan, nomor izin, alamat, kontak, serta tanda tangan dan stempel sesuai persyaratan.
            @else
                Catatan: isi nama obat, bentuk sediaan, kekuatan, kemasan, dan jumlah dengan jelas.
                Identitas izin apotek dan informasi kontak dapat ditambahkan pada kop sesuai dokumen perizinan.
            @endif
        </div>
        <div class="signature">
            {{ $profile === 'medikpedia' ? 'Jakarta' : 'Bogor' }}, {{ $order->created_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}<br>
            Pemesan,
            <div class="signature-space">
                @if($identity['pharmacist_stamp'])
                    <img class="signature-stamp signature-stamp-pharmacist" src="{{ asset($identity['pharmacist_stamp']) }}" alt="Stempel Apoteker">
                @endif
                @if($identity['stamp'])
                    <img class="signature-stamp signature-stamp-pharmacy" src="{{ asset($identity['stamp']) }}" alt="Stempel Apotek Medikpedia">
                @endif
            </div>
            <div class="signature-name">({{ $order->requester_name ?: $identity['signer'] }})</div>
            {{ $order->apj ?: $identity['title'] }} / No. SIPA {{ $order->sipa ?: $identity['sipa'] }}
        </div>
    </main>
    <script>
        const paperSize = document.getElementById('paper-size');
        const printPageSize = document.getElementById('print-page-size');
        const updatePrintPageSize = () => {
            const selectedSize = paperSize.selectedOptions[0];
            document.body.dataset.paperSize = selectedSize.dataset.paperSize;
            printPageSize.textContent = `@page { size: ${paperSize.value}; margin: ${selectedSize.dataset.margin}; }`;
        };
        paperSize.addEventListener('change', updatePrintPageSize);
        updatePrintPageSize();
    </script>
</body>
</html>
