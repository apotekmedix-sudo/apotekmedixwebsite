@extends('layouts.admin')

@section('title', 'Import Produk Resep - Admin Apotek Medix')
@section('page-title', 'Import Produk Resep')

@section('styles')
<style>
    .form-card {
        background: white; border-radius: 0.75rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden;
    }
    .form-card-header {
        padding: 1rem 1.5rem; border-bottom: 1px solid #f3f4f6;
        display: flex; align-items: center; gap: 0.6rem;
    }
    .form-card-header h3 { font-size: 0.95rem; font-weight: 700; color: #1f2937; margin: 0; }
    .form-card-header .header-icon {
        width: 32px; height: 32px; background: #fee2e2; border-radius: 0.4rem;
        display: flex; align-items: center; justify-content: center;
        color: #dc2626; font-size: 0.9rem;
    }
    .form-body { padding: 1.5rem; }
    .form-label {
        display: block; font-size: 0.8rem; font-weight: 700; color: #374151;
        margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.04em;
    }
    .form-error {
        font-size: 0.78rem; color: #ef4444; margin-top: 0.3rem;
        display: flex; align-items: center; gap: 0.3rem;
    }
    .upload-zone {
        border: 2px dashed #d1d5db; border-radius: 0.6rem; padding: 2rem 1rem;
        text-align: center; background: #fafafa; cursor: pointer; transition: all 0.2s;
    }
    .upload-zone:hover, .upload-zone.drag-over { border-color: #dc2626; background: #fff5f5; }
    .upload-zone .upload-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
    .upload-zone p { font-size: 0.85rem; color: #6b7280; margin: 0 0 0.75rem; }
    .upload-zone small { font-size: 0.75rem; color: #9ca3af; display: block; margin-top: 0.5rem; }
    .btn-choose {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.45rem 1rem; background: white; border: 1px solid #d1d5db;
        border-radius: 0.4rem; font-size: 0.82rem; font-weight: 600;
        color: #374151; cursor: pointer; transition: all 0.2s;
    }
    .btn-choose:hover { background: #f3f4f6; border-color: #9ca3af; }
    .form-footer {
        padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6;
        display: flex; gap: 0.6rem; align-items: center;
    }
    .btn-save {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.6rem 1.5rem; background: #dc2626; color: white; border: none;
        border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.2s;
    }
    .btn-save:hover { background: #b91c1c; transform: translateY(-1px); }
    .btn-save:disabled { background: #d1d5db; cursor: not-allowed; transform: none; }
    .btn-cancel {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.6rem 1.25rem; background: white; color: #6b7280;
        border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.9rem;
        font-weight: 600; text-decoration: none; transition: all 0.2s;
    }
    .btn-cancel:hover { background: #f9fafb; color: #374151; }
    .info-box {
        background: #fff5f5; border: 1px solid #fecaca; border-radius: 0.75rem;
        padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;
    }
    .info-box h3 { color: #b91c1c; margin: 0 0 0.75rem; font-size: 0.95rem; font-weight: 700; }
    .info-box ol {
        color: #7f1d1d; margin: 0; padding-left: 1.25rem;
        line-height: 1.8; font-size: 0.9rem;
    }
    .info-box li { margin-bottom: 0.5rem; }
    .info-box .success-note {
        color: #059669; margin: 0.75rem 0 0; font-size: 0.875rem; font-weight: 600;
    }
    .template-table {
        margin-top: 1rem; background: #f9fafb; border-radius: 0.5rem;
        padding: 1rem; overflow-x: auto;
    }
    .template-table table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    .template-table thead tr { background: #e5e7eb; }
    .template-table th {
        padding: 0.4rem 0.75rem; text-align: left; border: 1px solid #d1d5db;
        font-weight: 700; color: #374151;
    }
    .template-table td { padding: 0.4rem 0.75rem; border: 1px solid #d1d5db; color: #6b7280; }
    .breadcrumb-custom {
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 0.82rem; color: #9ca3af; margin-bottom: 1.25rem;
    }
    .breadcrumb-custom a { color: #dc2626; text-decoration: none; font-weight: 600; }
    .breadcrumb-custom i { font-size: 0.65rem; }
    .breadcrumb-custom span { color: #374151; font-weight: 600; }
    @media (max-width: 600px) {
        .form-body { padding: 1rem; }
        .form-footer { padding: 1rem; }
        .upload-zone { padding: 1.5rem 0.75rem; }
    }
</style>
@endsection

@section('content')

<div class="breadcrumb-custom">
    <a href="{{ route('admin.prescription-products.index') }}">
        <i class="fa-solid fa-file-prescription"></i> Produk Resep
    </a>
    <i class="fa-solid fa-chevron-right"></i>
    <span>Import Data Produk Resep</span>
</div>

<div class="info-box">
    <h3><i class="fa-solid fa-circle-info"></i> Petunjuk Import</h3>
    <ol>
        <li>Download Template Excel (.xlsx) di bawah</li>
        <li>Buka file CSV dengan <strong>Microsoft Excel</strong></li>
        <li>Isi data produk di sheet <strong>"Data Produk Resep"</strong></li>
        <li>Upload file <strong>.csv</strong> hasil template</li>
        <li>Semua produk yang diimpor otomatis ditandai sebagai <strong>Produk Resep (KERAS)</strong></li>
    </ol>
    <p class="success-note">
        ✅ Format kolom: <strong>PABRIK | NAMA PRODUK | SEDIAAN | RETAIL | KOMPOSISI | INDIKASI</strong>
    </p>
</div>

{{-- Download Template --}}
<div class="form-card" style="margin-bottom: 1.5rem;">
    <div class="form-card-header">
        <div class="header-icon"><i class="fa-solid fa-file-excel"></i></div>
        <h3>Template File</h3>
    </div>
    <div class="form-body">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
            <a href="{{ route('admin.prescription-products.import.template') }}"
               style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.6rem 1.5rem;background:#10b981;color:white;border-radius:0.5rem;font-size:0.9rem;font-weight:700;text-decoration:none;transition:all 0.2s;"
               onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                <i class="fa-solid fa-download"></i> Download Template Excel (.xlsx)
            </a>
            <span style="color:#6b7280;font-size:0.875rem;">
                Langsung rapi saat dibuka di Microsoft Excel
            </span>
        </div>

        <div class="template-table">
            <table>
                <thead>
                    <tr>
                        <th>PABRIK</th>
                        <th>NAMA PRODUK</th>
                        <th>DESKRIPSI</th>
                        <th>RETAIL</th>
                        <th>KOMPOSISI</th>
                        <th>INDIKASI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>KALBE</td>
                        <td>Amoxicillin 500mg</td>
                        <td>Antibiotik resep untuk infeksi bakteri</td>
                        <td>15000</td>
                        <td>Amoxicillin 500 mg</td>
                        <td>Infeksi bakteri</td>
                    </tr>
                    <tr>
                        <td>BERNOFARM</td>
                        <td>Ciprofloxacin 500mg</td>
                        <td>Antibiotik oral untuk infeksi berat</td>
                        <td>25000</td>
                        <td>Ciprofloxacin 500 mg</td>
                        <td>Infeksi bakteri</td>
                    </tr>
                    <tr>
                        <td>DEXA</td>
                        <td>Metformin 500mg</td>
                        <td>Obat diabetes untuk kontrol gula darah</td>
                        <td>12000</td>
                        <td>Metformin 500 mg</td>
                        <td>Diabetes tipe 2</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;padding:0.75rem;background:#fef3c7;border-radius:0.5rem;border:1px solid #fde68a;">
            <p style="font-size:0.78rem;color:#92400e;margin:0;line-height:1.6;">
                <i class="fa-solid fa-lightbulb" style="margin-right:0.3rem;"></i>
                <strong>Catatan:</strong> Semua produk yang diimpor akan otomatis menjadi <strong>Obat Resep (KERAS)</strong>.
            </p>
        </div>
    </div>
</div>

{{-- Upload Form --}}
<div class="form-card">
    <div class="form-card-header">
        <div class="header-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <h3>Upload File</h3>
    </div>
    <div class="form-body">
        @if($errors->any())
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:0.5rem;padding:1rem;margin-bottom:1rem;color:#7f1d1d;font-size:0.875rem;">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('file') }}
            </div>
        @endif

        <div id="dropZone" class="upload-zone" onclick="document.getElementById('fileInput').click()"
             ondragover="handleDragOver(event)"
             ondragleave="handleDragLeave(event)"
             ondrop="handleDrop(event)">
            <div class="upload-icon">📂</div>
            <p>Klik atau drag & drop file di sini</p>
            <button type="button" class="btn-choose" onclick="event.stopPropagation();document.getElementById('fileInput').click()">
                <i class="fa-solid fa-folder-open"></i> Pilih File
            </button>
            <small>Format: CSV, XLS, XLSX — Maks. 2MB</small>
            <p style="color:#9ca3af;font-size:0.875rem;margin:0.75rem 0 0;" id="fileLabel"></p>
        </div>

        <input type="file" id="fileInput" name="file" accept=".csv,.xls,.xlsx,text/csv"
               style="display:none;" onchange="updateFileLabel(this)">

        <div style="margin-top:1rem;padding:0.75rem;background:#fff5f5;border-radius:0.5rem;border:1px solid #fee2e2;">
            <p style="font-size:0.78rem;color:#6b7280;margin:0;line-height:1.6;">
                <i class="fa-solid fa-circle-info" style="color:#dc2626;margin-right:0.3rem;"></i>
                Pastikan file sudah diisi dengan data yang benar sebelum diupload.
            </p>
        </div>
    </div>
    <div class="form-footer">
        <form action="{{ route('admin.prescription-products.import.process') }}" method="POST"
              enctype="multipart/form-data" id="submitForm" style="display:none;">
            @csrf
            <input type="file" id="fileInputHidden" name="file" style="display:none;">
        </form>
        <button type="button" class="btn-save" id="submitBtn" onclick="submitForm()" disabled
                style="opacity:0.5;cursor:not-allowed;">
            <i class="fa-solid fa-cloud-arrow-up"></i> Import Sekarang
        </button>
        <a href="{{ route('admin.prescription-products.index') }}" class="btn-cancel">
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
        const name = input.files[0].name;
        label.textContent = '✅ ' + name;
        label.style.color = '#059669';
        zone.style.borderColor = '#059669';
        zone.style.background  = '#f0fdf4';
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor  = 'pointer';
    }
}

function handleDragOver(e) {
    e.preventDefault();
    const zone = document.getElementById('dropZone');
    zone.style.borderColor = '#dc2626';
    zone.style.background  = '#fff5f5';
    zone.classList.add('drag-over');
}

function handleDragLeave(e) {
    const zone = document.getElementById('dropZone');
    zone.style.borderColor = '#d1d5db';
    zone.style.background  = '#fafafa';
    zone.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    const input = document.getElementById('fileInput');
    const dt    = e.dataTransfer;
    if (dt.files.length) {
        input.files = dt.files;
        updateFileLabel(input);
    }
}

function submitForm() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput.files.length > 0) {
        const form = document.getElementById('submitForm');
        const hiddenInput = document.getElementById('fileInputHidden');
        const dt = new DataTransfer();
        dt.items.add(fileInput.files[0]);
        hiddenInput.files = dt.files;
        form.submit();
    }
}
</script>
@endsection

