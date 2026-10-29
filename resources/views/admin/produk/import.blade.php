@extends('layouts.admin')

@section('title', 'Import Produk - Admin Apotek Medix')
@section('page-title', '📥 Import Produk')

@section('styles')
<style>
    .form-card { background:white; border-radius:0.75rem; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #f0f0f0; overflow:hidden; }
    .form-card-header { padding:1rem 1.5rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:0.6rem; }
    .form-card-header h3 { font-size:0.95rem; font-weight:700; color:#1f2937; margin:0; }
    .header-icon { width:32px; height:32px; background:#e3f2fd; border-radius:0.4rem; display:flex; align-items:center; justify-content:center; color:#1E88E5; font-size:0.9rem; }
    .form-body { padding:1.5rem; }
    .form-footer { padding:1rem 1.5rem; border-top:1px solid #f3f4f6; display:flex; gap:0.6rem; align-items:center; }
    .btn-save { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.5rem; background:#1E88E5; color:white; border:none; border-radius:0.5rem; font-size:0.9rem; font-weight:700; cursor:pointer; transition:all 0.2s; }
    .btn-save:hover { background:#1565C0; transform:translateY(-1px); }
    .btn-save:disabled { background:#d1d5db; cursor:not-allowed; transform:none; }
    .btn-cancel { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.25rem; background:white; color:#6b7280; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.9rem; font-weight:600; text-decoration:none; transition:all 0.2s; }
    .btn-cancel:hover { background:#f9fafb; color:#374151; }
    .upload-zone { border:2px dashed #d1d5db; border-radius:0.6rem; padding:2rem 1rem; text-align:center; background:#fafafa; cursor:pointer; transition:all 0.2s; }
    .upload-zone:hover, .upload-zone.drag-over { border-color:#1E88E5; background:#f0f7ff; }
    .upload-zone .upload-icon { font-size:2.5rem; margin-bottom:0.5rem; }
    .upload-zone p { font-size:0.85rem; color:#6b7280; margin:0 0 0.75rem; }
    .upload-zone small { font-size:0.75rem; color:#9ca3af; display:block; margin-top:0.5rem; }
    .btn-choose { display:inline-flex; align-items:center; gap:0.35rem; padding:0.45rem 1rem; background:white; border:1px solid #d1d5db; border-radius:0.4rem; font-size:0.82rem; font-weight:600; color:#374151; cursor:pointer; transition:all 0.2s; }
    .btn-choose:hover { background:#f3f4f6; border-color:#9ca3af; }
    .info-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:0.75rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; }
    .info-box h3 { color:#1d4ed8; margin:0 0 0.75rem; font-size:0.95rem; font-weight:700; }
    .info-box ol { color:#1e40af; margin:0; padding-left:1.25rem; line-height:1.8; font-size:0.9rem; }
    .template-table { margin-top:1rem; background:#f9fafb; border-radius:0.5rem; padding:1rem; overflow-x:auto; }
    .template-table table { min-width:1900px; border-collapse:collapse; font-size:0.8rem; }
    .template-table thead tr { background:#e5e7eb; }
    .template-table th { padding:0.4rem 0.75rem; text-align:left; border:1px solid #d1d5db; font-weight:700; color:#374151; }
    .template-table td { padding:0.4rem 0.75rem; border:1px solid #d1d5db; color:#6b7280; }
    .import-mode { width:100%; max-width:620px; padding:0.7rem 0.85rem; border:1px solid #d1d5db; border-radius:0.5rem; color:#1f2937; background:#fff; font-size:0.9rem; }
    .mode-description { margin:0.55rem 0 0; color:#6b7280; font-size:0.82rem; line-height:1.6; }
    .import-progress { display:none; margin-top:1rem; padding:1rem; border:1px solid #bfdbfe; border-radius:.65rem; background:#f8fbff; }
    .import-progress.is-visible { display:block; }
    .import-progress-heading { display:flex; justify-content:space-between; gap:.75rem; align-items:center; color:#1f2937; font-size:.88rem; font-weight:700; }
    .import-progress-track { height:12px; margin:.75rem 0 .55rem; overflow:hidden; border-radius:999px; background:#dbeafe; }
    .import-progress-bar { width:0;height:100%;border-radius:inherit;background:linear-gradient(90deg,#1e88e5,#10b981);transition:width .25s ease; }
    .import-progress-meta { display:flex;justify-content:space-between;gap:.5rem;color:#475569;font-size:.78rem; }
    .import-progress-note { margin:.6rem 0 0;color:#64748b;font-size:.76rem;line-height:1.5; }
</style>
@endsection

@section('content')

<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#9ca3af;margin-bottom:1.25rem;">
    <a href="{{ route('admin.produk.index') }}" style="color:#1E88E5;text-decoration:none;font-weight:600;">🛒 Produk Kami</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
    <span style="color:#374151;font-weight:600;">Import Produk</span>
</div>

<div class="info-box">
    <h3><i class="fa-solid fa-circle-info"></i> Petunjuk Import</h3>
    <ol>
        <li>Pilih mode import sesuai tujuan: tambah/lengkapi produk atau edit massal dari hasil export</li>
        <li>Untuk edit massal, download file Export Produk di bawah, edit kolomnya, lalu pilih mode Edit massal</li>
        <li>Kolom <strong>KATEGORI</strong> menerima semua nama kategori pada dropdown produk, termasuk emoji; isi sesuai nama tampilannya atau slug kategorinya.</li>
        <li>Isi jumlah stok sesuai satuan pada kolom <strong>SATUAN</strong> di <strong>STOK SATUAN</strong>, misalnya stok 5 botol diisi <strong>5</strong>.</li>
        <li>Isi satu harga B2C untuk satuan kemasan produk pada kolom <strong>HARGA JUAL B2C</strong>. Template ini tidak lagi meminta harga atau rasio per strip/tablet.</li>
        <li>Isi kolom <strong>DESKRIPSI</strong>, <strong>KOMPOSISI</strong>, dan <strong>INDIKASI</strong> secara terpisah sesuai informasi produk.</li>
        <li>Isi <strong>LINK FOTO 1</strong> dan <strong>LINK FOTO 2</strong> dengan URL HTTPS langsung ke file gambar publik. Foto akan diunduh dan disimpan di website saat import; ukuran maksimal 5 MB per foto. Import diproses satu produk per langkah agar progres produk yang sudah selesai tetap tersimpan jika koneksi terputus. Foto yang gagal akan dilaporkan beserta nama produk dan tautannya.</li>
        <li>Kolom opsional boleh dikosongkan; baris tanpa nama produk akan dilewati.</li>
        <li>Upload file <strong>.csv</strong>, <strong>.xls</strong>, atau <strong>.xlsx</strong></li>
        <li>Mode tambah/lengkapi mencocokkan nama produk: data yang kosong dilengkapi tanpa menimpa data yang sudah terisi; nama yang belum ada menjadi produk baru</li>
        <li>Mode edit massal hanya memperbarui produk berdasarkan ID pada kolom NO; data tidak akan membuat produk baru</li>
    </ol>
    <p style="color:#059669;margin:0.75rem 0 0;font-size:0.875rem;font-weight:600;">
        ✅ Template berisi 19 kolom: kategori, deskripsi, komposisi, indikasi, data produk, harga, dan dua kolom LINK FOTO
    </p>
</div>

{{-- Template --}}
<div class="form-card" style="margin-bottom:1.5rem;">
    <div class="form-card-header">
        <div class="header-icon"><i class="fa-solid fa-file-excel"></i></div>
        <h3>Template File</h3>
    </div>
    <div class="form-body">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
            <a href="{{ route('admin.produk.import.template') }}" class="btn-save" style="background:#10b981;text-decoration:none;">
                <i class="fa-solid fa-download"></i> Download Template Excel
            </a>
            <a href="{{ route('admin.produk.export') }}" class="btn-save" style="background:#2563eb;text-decoration:none;">
                <i class="fa-solid fa-file-export"></i> Download Export Produk untuk Edit Massal
            </a>
        </div>
        <div class="template-table">
            <table>
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>PRINCIPLE</th>
                        <th>NAMA PRODUK</th>
                        <th>LOGO</th>
                        <th>GOLONGAN</th>
                        <th>KATEGORI</th>
                        <th>KOMPOSISI</th>
                        <th>INDIKASI</th>
                        <th>DESKRIPSI</th>
                        <th>SATUAN</th>
                        <th>KEMASAN</th>
                        <th>SEDIAAN</th>
                        <th>STOK SATUAN</th>
                        <th>MODAL</th>
                        <th>HARGA INTERNAL</th>
                        <th>HARGA JUAL B2B</th>
                        <th>HARGA JUAL B2C</th>
                        <th>LINK FOTO 1</th>
                        <th>LINK FOTO 2</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>PT KALBE FARMA</td>
                        <td>Promag Tablet</td>
                        <td>HIJAU</td>
                        <td>Obat Bebas</td>
                        <td>obat-resep</td>
                        <td>Antasida &amp; Simetikon</td>
                        <td>Membantu meredakan sakit maag</td>
                        <td>Membantu meredakan rasa tidak nyaman akibat asam lambung.</td>
                        <td>BOX</td>
                        <td>10 blister @ 10 tablet</td>
                        <td>TABLET</td>
                        <td>10</td><td>117771</td><td>120000</td>
                        <td>135437</td><td>153102</td><td>https://contoh.com/foto-produk-1.jpg</td><td>https://contoh.com/foto-produk-2.jpg</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>PT PFIZER</td>
                        <td>Viagra 100mg</td>
                        <td>MERAH</td>
                        <td>Obat Keras</td>
                        <td>obat-resep</td>
                        <td>Sildenafil</td>
                        <td>Disfungsi ereksi</td>
                        <td>Obat resep untuk terapi sesuai petunjuk tenaga kesehatan.</td>
                        <td>BOX</td>
                        <td>1 blister @ 4 tablet</td>
                        <td>TABLET</td>
                        <td>1</td><td>200000</td><td>210000</td>
                        <td>205000</td><td>220000</td><td></td><td></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>KONIMEX</td>
                        <td>Anakod Sirup</td>
                        <td>BIRU</td>
                        <td>Bebas Terbatas</td>
                        <td>flu-batuk</td>
                        <td>Paracetamol</td>
                        <td>Membantu meredakan batuk dan flu</td>
                        <td>Sirup untuk membantu meredakan gejala flu dan batuk.</td>
                        <td>BOTOL</td>
                        <td></td>
                        <td>SIRUP</td>
                        <td>1</td><td>12000</td><td>13000</td>
                        <td>12500</td><td>14000</td><td></td><td></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>GSK</td>
                        <td>Voltaren Gel</td>
                        <td>MERAH</td>
                        <td>Obat Keras</td>
                        <td>obat-resep</td>
                        <td>Diclofenac</td>
                        <td>Membantu meredakan nyeri otot</td>
                        <td>Gel untuk membantu meredakan nyeri otot sesuai petunjuk.</td>
                        <td>TUBE</td>
                        <td></td>
                        <td>KRIM</td>
                        <td>5</td><td>50000</td><td>52000</td>
                        <td>51000</td><td>55000</td><td></td><td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Upload --}}
<div class="form-card">
    <div class="form-card-header">
        <div class="header-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <h3>Upload File</h3>
    </div>
    <div class="form-body">
        <label for="importMode" style="display:block;font-size:0.85rem;font-weight:700;color:#374151;margin-bottom:0.45rem;">Mode Import</label>
        <select id="importMode" class="import-mode" onchange="updateImportMode()">
            <option value="complete">1. Tambah / lengkapi produk</option>
            <option value="bulk_edit">2. Edit massal dari hasil export</option>
        </select>
        <p class="mode-description" id="modeDescription">
            Nama yang sama akan dicocokkan. Kolom yang sudah berisi data tidak ditimpa; kolom kosong akan dilengkapi. Nama baru akan ditambahkan sebagai produk baru.
        </p>
        @if($errors->any())
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:0.5rem;padding:1rem;margin-bottom:1rem;color:#7f1d1d;font-size:0.875rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('file') }}
            </div>
        @endif
        <div id="dropZone" class="upload-zone" onclick="document.getElementById('fileInput').click()"
             ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
            <div class="upload-icon">📂</div>
            <p>Klik atau drag & drop file di sini</p>
            <button type="button" class="btn-choose" onclick="event.stopPropagation();document.getElementById('fileInput').click()">
                <i class="fa-solid fa-folder-open"></i> Pilih File
            </button>
            <small>Format: CSV, XLS, XLSX — Maks. 10MB</small>
            <p style="color:#9ca3af;font-size:0.875rem;margin:0.75rem 0 0;" id="fileLabel"></p>
        </div>
        <input type="file" id="fileInput" name="file" accept=".csv,.xls,.xlsx,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" style="display:none;" onchange="updateFileLabel(this)">
        <section class="import-progress" id="importProgress" aria-live="polite" aria-hidden="true">
            <div class="import-progress-heading">
                <span id="importProgressStatus">Menyiapkan import...</span>
                <span id="importProgressPercent">0%</span>
            </div>
            <div class="import-progress-track" id="importProgressTrack" role="progressbar" aria-label="Progres import produk" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="import-progress-bar" id="importProgressBar"></div>
            </div>
            <div class="import-progress-meta">
                <span id="importProgressWorkload"></span>
                <span id="importProgressEta">Menghitung estimasi...</span>
            </div>
            <p class="import-progress-note" id="importProgressNote">Progres unggah file akurat. Progres pemrosesan dan sisa waktu merupakan perkiraan berdasarkan jumlah produk dan foto.</p>
        </section>
    </div>
    <div class="form-footer" style="justify-content:flex-end; flex-wrap:wrap;">
        <form action="{{ route('admin.produk.import.process') }}" method="POST" enctype="multipart/form-data" id="submitForm" style="display:none;">
            @csrf
            <input type="file" id="fileInputHidden" name="file" style="display:none;">
            <input type="hidden" id="importModeHidden" name="mode" value="complete">
        </form>
        <button type="button" class="btn-save" id="submitBtn" onclick="submitForm()" disabled style="opacity:0.5;cursor:not-allowed;">
            <i class="fa-solid fa-cloud-arrow-up"></i> Tambah / Lengkapi Produk
        </button>
        <a href="{{ route('admin.produk.index') }}" class="btn-cancel">
            <i class="fa-solid fa-xmark"></i> Batal
        </a>
    </div>
</div>

<script>
function updateFileLabel(input) {
    const label = document.getElementById('fileLabel');
    const btn   = document.getElementById('submitBtn');
    const zone  = document.getElementById('dropZone');
    if (input.files && input.files[0]) {
        btn.onclick = submitForm;
        label.textContent = '✅ ' + input.files[0].name;
        label.style.color = '#059669';
        zone.style.borderColor = '#059669';
        zone.style.background  = '#f0fdf4';
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor  = 'pointer';
    }
}
function handleDragOver(e) { e.preventDefault(); document.getElementById('dropZone').classList.add('drag-over'); }
function handleDragLeave(e) { document.getElementById('dropZone').classList.remove('drag-over'); }
function handleDrop(e) {
    e.preventDefault();
    const input = document.getElementById('fileInput');
    if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; updateFileLabel(input); }
    document.getElementById('dropZone').classList.remove('drag-over');
}
function submitForm() {
    const fileInput = document.getElementById('fileInput');
    if (!fileInput.files.length) return;

    const file = fileInput.files[0];
    const form = document.getElementById('submitForm');
    const button = document.getElementById('submitBtn');
    const progress = document.getElementById('importProgress');
    const progressBar = document.getElementById('importProgressBar');
    const progressTrack = document.getElementById('importProgressTrack');
    const progressStatus = document.getElementById('importProgressStatus');
    const progressPercent = document.getElementById('importProgressPercent');
    const progressWorkload = document.getElementById('importProgressWorkload');
    const progressEta = document.getElementById('importProgressEta');
    const progressNote = document.getElementById('importProgressNote');
    const formatDuration = seconds => {
        const total = Math.max(0, Math.ceil(seconds));
        const minutes = Math.floor(total / 60);
        const remainingSeconds = total % 60;
        if (minutes === 0) return `${remainingSeconds} dtk`;
        return remainingSeconds ? `${minutes} m ${remainingSeconds} dtk` : `${minutes} m`;
    };
    const setProgress = (value, status) => {
        const percent = Math.max(0, Math.min(100, Math.round(value)));
        progressBar.style.width = `${percent}%`;
        progressTrack.setAttribute('aria-valuenow', String(percent));
        progressPercent.textContent = `${percent}%`;
        progressStatus.textContent = status;
    };
    const estimateWorkload = async selectedFile => {
        const extension = selectedFile.name.split('.').pop().toLowerCase();
        if (extension === 'csv' || extension === 'xls') {
            const text = await selectedFile.text();
            const delimiter = (text.split(/\r?\n/, 1)[0].match(/;/g) || []).length > (text.split(/\r?\n/, 1)[0].match(/,/g) || []).length ? ';' : ',';
            const rows = [];
            let row = [];
            let cell = '';
            let quoted = false;
            for (let index = 0; index < text.length; index++) {
                const character = text[index];
                if (character === '"' && quoted && text[index + 1] === '"') {
                    cell += '"';
                    index++;
                } else if (character === '"') {
                    quoted = !quoted;
                } else if (character === delimiter && !quoted) {
                    row.push(cell);
                    cell = '';
                } else if ((character === '\n' || character === '\r') && !quoted) {
                    if (character === '\r' && text[index + 1] === '\n') index++;
                    row.push(cell);
                    if (row.some(value => value.trim() !== '')) rows.push(row);
                    row = [];
                    cell = '';
                } else {
                    cell += character;
                }
            }
            row.push(cell);
            if (row.some(value => value.trim() !== '')) rows.push(row);
            return countImportWorkload(rows);
        }

        const buffer = await selectedFile.arrayBuffer();
        const view = new DataView(buffer);
        let endRecord = -1;
        for (let offset = buffer.byteLength - 22; offset >= Math.max(0, buffer.byteLength - 65557); offset--) {
            if (view.getUint32(offset, true) === 0x06054b50) {
                endRecord = offset;
                break;
            }
        }
        if (endRecord < 0) throw new Error('Direktori XLSX tidak ditemukan.');
        const entries = view.getUint16(endRecord + 10, true);
        let directoryOffset = view.getUint32(endRecord + 16, true);
        const decoder = new TextDecoder();
        const zipEntries = new Map();
        for (let entryIndex = 0; entryIndex < entries; entryIndex++) {
            if (view.getUint32(directoryOffset, true) !== 0x02014b50) throw new Error('Struktur XLSX tidak valid.');
            const method = view.getUint16(directoryOffset + 10, true);
            const compressedSize = view.getUint32(directoryOffset + 20, true);
            const nameLength = view.getUint16(directoryOffset + 28, true);
            const extraLength = view.getUint16(directoryOffset + 30, true);
            const commentLength = view.getUint16(directoryOffset + 32, true);
            const localOffset = view.getUint32(directoryOffset + 42, true);
            const name = decoder.decode(new Uint8Array(buffer, directoryOffset + 46, nameLength));
            const localNameLength = view.getUint16(localOffset + 26, true);
            const localExtraLength = view.getUint16(localOffset + 28, true);
            const dataOffset = localOffset + 30 + localNameLength + localExtraLength;
            zipEntries.set(name, { method, data: buffer.slice(dataOffset, dataOffset + compressedSize) });
            directoryOffset += 46 + nameLength + extraLength + commentLength;
        }
        const readZipText = async name => {
            const entry = zipEntries.get(name);
            if (!entry) return '';
            let data = entry.data;
            if (entry.method === 8) {
                if (!window.DecompressionStream) throw new Error('Browser tidak mendukung pembacaan XLSX untuk estimasi.');
                data = await new Response(new Blob([data]).stream().pipeThrough(new DecompressionStream('deflate-raw'))).arrayBuffer();
            } else if (entry.method !== 0) {
                throw new Error('Format kompresi XLSX tidak didukung untuk estimasi.');
            }
            return decoder.decode(data);
        };
        const sharedXml = await readZipText('xl/sharedStrings.xml');
        const sheetXml = await readZipText('xl/worksheets/sheet1.xml');
        const parseXml = xml => new DOMParser().parseFromString(xml, 'application/xml');
        const sharedDocument = parseXml(sharedXml);
        const sharedStrings = Array.from(sharedDocument.getElementsByTagNameNS('*', 'si'), item =>
            Array.from(item.getElementsByTagNameNS('*', 't'), text => text.textContent || '').join('')
        );
        const sheetDocument = parseXml(sheetXml);
        const worksheetRows = Array.from(sheetDocument.getElementsByTagNameNS('*', 'sheetData')[0]?.children || []);
        const rows = worksheetRows.map(xmlRow => {
            const cells = [];
            for (const xmlCell of Array.from(xmlRow.children)) {
                const columnLetters = (xmlCell.getAttribute('r') || '').match(/^[A-Z]+/)?.[0] || 'A';
                let columnIndex = 0;
                for (const letter of columnLetters) columnIndex = columnIndex * 26 + letter.charCodeAt(0) - 64;
                const valueElement = xmlCell.getElementsByTagNameNS('*', 'v')[0];
                let value = valueElement?.textContent || '';
                if (xmlCell.getAttribute('t') === 's') value = sharedStrings[Number(value)] || '';
                else if (xmlCell.getAttribute('t') === 'inlineStr') {
                    value = Array.from(xmlCell.getElementsByTagNameNS('*', 't'), text => text.textContent || '').join('');
                }
                cells[columnIndex - 1] = value;
            }
            return cells;
        });
        return countImportWorkload(rows);
    };
    const countImportWorkload = rows => {
        const normalize = value => String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
        const header = rows[0] || [];
        const photoColumns = header.map((value, index) => /^(LINKFOTO|GAMBARTAMBAHAN|GAMBAR|IMAGE)/.test(normalize(value)) ? index : -1).filter(index => index >= 0);
        const nameColumn = header.findIndex(value => ['NAMAPRODUK', 'NAMA', 'PRODUK'].includes(normalize(value)));
        const dataRows = rows.slice(1).filter(row => String(row[nameColumn] || '').trim() !== '');
        const photoCount = dataRows.reduce((total, row) => total + photoColumns.filter(index => String(row[index] || '').trim() !== '').length, 0);
        return { products: dataRows.length, photos: photoCount };
    };

    button.disabled = true;
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengimpor...';
    document.getElementById('importModeHidden').value = document.getElementById('importMode').value;
    progress.classList.add('is-visible');
    progress.setAttribute('aria-hidden', 'false');
    setProgress(0, 'Menganalisis file...');

    const sendRequest = (url, data, onUploadProgress = null) => new Promise((resolve, reject) => {
        const request = new XMLHttpRequest();
        request.open('POST', url);
        request.timeout = 45000;
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.setRequestHeader('Accept', 'application/json');
        if (onUploadProgress) {
            request.upload.addEventListener('progress', event => {
                if (event.lengthComputable) onUploadProgress(event.loaded / event.total * 100);
            });
        }
        request.addEventListener('load', () => {
            let response = {};
            try {
                response = JSON.parse(request.responseText);
            } catch (error) {
                reject(new Error(`Respons server tidak dapat dibaca (HTTP ${request.status}).`));
                return;
            }
            if (request.status < 200 || request.status >= 300) {
                reject(new Error(response.message || `Server mengembalikan HTTP ${request.status}.`));
                return;
            }
            resolve(response);
        });
        request.addEventListener('error', () => reject(new Error('Koneksi terputus saat menghubungi server.')));
        request.addEventListener('timeout', () => reject(new Error('Server terlalu lama merespons. Progres yang sudah selesai tetap tersimpan.')));
        request.addEventListener('abort', () => reject(new Error('Permintaan import dibatalkan.')));
        request.send(data);
    });

    estimateWorkload(file).catch(error => ({
        products: Math.max(1, Math.round(file.size / 250)),
        photos: Math.max(0, Math.round(file.size / 250 * 0.35)),
        fallbackReason: error.message
    })).then(async workload => {
        const formData = new FormData(form);
        formData.set('file', file);
        formData.set('chunked', '1');

        progressWorkload.textContent = `${workload.products} produk · ${workload.photos} foto`;
        progressEta.textContent = 'Menyiapkan import bertahap...';
        if (workload.fallbackReason) {
            progressNote.textContent = `Browser tidak dapat membaca isi file untuk menghitung jumlah produk dan foto (${workload.fallbackReason}). Estimasi berikut dihitung kasar dari ukuran file; progres unggah tetap akurat.`;
        }
        setProgress(0, 'Mengunggah file...');

        let initialized = null;
        let next = 0;
        let total = 0;
        const startedAt = Date.now();
        const processBatches = async () => {
            while (next < total) {
                setProgress(20 + (next / Math.max(total, 1)) * 75, `Memproses produk ${next + 1} dari ${total} dan mengunduh fotonya...`);
                const batch = new FormData();
                batch.append('_token', form.querySelector('[name="_token"]').value);
                batch.append('token', initialized.token);
                const result = await sendRequest(@json(route('admin.produk.import.batch')), batch);
                if (result.complete) {
                    setProgress(100, 'Import selesai. Membuka hasil...');
                    progressEta.textContent = `Waktu proses: ${formatDuration((Date.now() - startedAt) / 1000)}`;
                    window.location.assign(result.redirect_url);
                    return;
                }

                next = result.next;
                const elapsedSeconds = (Date.now() - startedAt) / 1000;
                const secondsPerProduct = elapsedSeconds / Math.max(next, 1);
                progressEta.textContent = `Perkiraan sisa: ${formatDuration(secondsPerProduct * (total - next))}`;
            }
        };

        try {
            initialized = await sendRequest(form.action, formData, percent => {
                setProgress(percent * 0.2, `Mengunggah file... ${Math.round(percent)}%`);
            });
            total = initialized.total;
            progressWorkload.textContent = `${total} produk · ${workload.photos} foto`;
            await processBatches();
        } catch (error) {
            progressNote.textContent = `${error.message} Produk yang sudah selesai diproses tetap tersimpan.`;
            setProgress(95, 'Import terhenti sebelum seluruh produk selesai.');
            button.disabled = false;
            if (initialized) {
                button.onclick = async () => {
                    button.disabled = true;
                    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Melanjutkan...';
                    try {
                        await processBatches();
                    } catch (resumeError) {
                        progressNote.textContent = `${resumeError.message} Produk yang sudah selesai diproses tetap tersimpan. Anda dapat mencoba melanjutkan lagi.`;
                        button.disabled = false;
                        button.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Lanjutkan Import';
                    }
                };
                button.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Lanjutkan Import';
            } else {
                button.onclick = submitForm;
                button.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Coba Import Kembali';
            }
        }
    });
}
function updateImportMode() {
    const mode = document.getElementById('importMode').value;
    const description = document.getElementById('modeDescription');
    const button = document.getElementById('submitBtn');
    if (mode === 'bulk_edit') {
        description.textContent = 'Gunakan file hasil Download Export Produk. Data dicocokkan dengan ID di kolom NO; nilai yang Anda edit akan diterapkan, dan baris dengan ID tidak dikenal akan ditolak.';
        button.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Terapkan Edit Massal';
    } else {
        description.textContent = 'Nama yang sama akan dicocokkan. Kolom yang sudah berisi data tidak ditimpa; kolom kosong akan dilengkapi. Nama baru akan ditambahkan sebagai produk baru.';
        button.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Tambah / Lengkapi Produk';
    }
}
</script>
@endsection
