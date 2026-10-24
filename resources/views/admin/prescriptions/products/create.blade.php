@extends('layouts.admin')

@section('title', 'Tambah Produk Resep - Admin Apotek Medix')
@section('page-title', 'Tambah Produk Resep')

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
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group { margin-bottom: 0; }
    .form-label {
        display: block; font-size: 0.8rem; font-weight: 700; color: #374151;
        margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.04em;
    }
    .form-label .req { color: #ef4444; margin-left: 2px; }
    .form-input {
        width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #e5e7eb;
        border-radius: 0.5rem; font-size: 0.9rem; color: #1f2937;
        background: #fafafa; transition: all 0.2s;
    }
    .form-input:focus {
        outline: none; border-color: #dc2626; background: white;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.08);
    }
    .form-input.is-invalid { border-color: #ef4444; }
    .form-error {
        font-size: 0.78rem; color: #ef4444; margin-top: 0.3rem;
        display: flex; align-items: center; gap: 0.3rem;
    }
    .upload-zone {
        border: 2px dashed #d1d5db; border-radius: 0.6rem; padding: 1.75rem 1rem;
        text-align: center; background: #fafafa; cursor: pointer; transition: all 0.2s;
    }
    .upload-zone:hover, .upload-zone.drag-over { border-color: #dc2626; background: #fff5f5; }
    .upload-zone .upload-icon { font-size: 2rem; margin-bottom: 0.5rem; }
    .upload-zone p { font-size: 0.85rem; color: #6b7280; margin: 0 0 0.75rem; }
    .upload-zone small { font-size: 0.75rem; color: #9ca3af; display: block; margin-top: 0.5rem; }
    .btn-choose {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.45rem 1rem; background: white; border: 1px solid #d1d5db;
        border-radius: 0.4rem; font-size: 0.82rem; font-weight: 600;
        color: #374151; cursor: pointer; transition: all 0.2s;
    }
    .btn-choose:hover { background: #f3f4f6; border-color: #9ca3af; }
    .img-preview-wrap {
        margin-top: 0.75rem; display: none; border-radius: 0.5rem;
        overflow: hidden; border: 1px solid #e5e7eb; position: relative;
    }
    .img-preview-wrap img {
        width: 100%; max-height: 220px; object-fit: contain;
        display: block; background: #f9fafb;
    }
    .img-preview-label {
        position: absolute; top: 0.5rem; left: 0.5rem;
        background: rgba(0,0,0,0.55); color: white; font-size: 0.7rem;
        font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 0.3rem;
    }
    .form-footer {
        padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6;
        display: flex; gap: 0.6rem; align-items: center;
    }
    .btn-save {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.6rem 1.5rem; background: #dc2626; color: white; border: none;
        border-radius: 0.5rem; font-size: 0.9rem; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
    }
    .btn-save:hover { background: #b91c1c; transform: translateY(-1px); }
    .btn-cancel {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.6rem 1.25rem; background: white; color: #6b7280;
        border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.9rem;
        font-weight: 600; text-decoration: none; transition: all 0.2s;
    }
    .btn-cancel:hover { background: #f9fafb; color: #374151; }
    .two-col-layout {
        display: grid; grid-template-columns: 1fr 340px;
        gap: 1.25rem; align-items: start;
    }
    @media (max-width: 900px) { .two-col-layout { grid-template-columns: 1fr; } }
    @media (max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-body { padding: 1rem; }
        .form-footer { padding: 1rem; }
    }
</style>
@endsection

@section('content')

<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#9ca3af;margin-bottom:1.25rem;">
    <a href="{{ route('admin.prescription-products.index') }}" style="color:#dc2626;text-decoration:none;font-weight:600;">
        <i class="fa-solid fa-file-prescription"></i> Produk Resep
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
    <span style="color:#374151;font-weight:600;">Tambah Produk Baru</span>
</div>

<form action="{{ route('admin.prescription-products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="two-col-layout">

        <div class="form-card">
            <div class="form-card-header">
                <div class="header-icon"><i class="fa-solid fa-circle-info"></i></div>
                <h3>Informasi Produk Resep</h3>
            </div>
            <div class="form-body" style="display:flex;flex-direction:column;gap:1rem;">

                <div class="form-group">
                    <label class="form-label">Nama Produk <span class="req">*</span></label>
                    <input type="text" name="nama_obat"
                           class="form-input {{ $errors->has('nama_obat') ? 'is-invalid' : '' }}"
                           placeholder="Contoh: Amoxicillin 500mg"
                           value="{{ old('nama_obat') }}" required>
                    @error('nama_obat')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Sediaan</label>
                    <input type="text" name="sediaan"
                           class="form-input {{ $errors->has('sediaan') ? 'is-invalid' : '' }}"
                           placeholder="Contoh: fls, box, tube, pcs atau ketik bebas"
                           value="{{ old('sediaan') }}">
                    @error('sediaan')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Pabrik <span class="req">*</span></label>
                    <select name="kategori"
                            class="form-input {{ $errors->has('kategori') ? 'is-invalid' : '' }}" required>
                        <option value="">— Pilih Pabrik —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ old('kategori') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Retail (Rp) <span class="req">*</span></label>
                        <input type="number" name="harga"
                               class="form-input {{ $errors->has('harga') ? 'is-invalid' : '' }}"
                               placeholder="15000" step="1" min="0"
                               value="{{ old('harga') }}" required>
                        @error('harga')
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stok <span class="req">*</span></label>
                        <input type="number" name="stok"
                               class="form-input {{ $errors->has('stok') ? 'is-invalid' : '' }}"
                               placeholder="50" min="0"
                               value="{{ old('stok') }}" required>
                        @error('stok')
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Komposisi <span class="req">*</span></label>
                    <input type="text" name="komposisi"
                           class="form-input {{ $errors->has('komposisi') ? 'is-invalid' : '' }}"
                           placeholder="Contoh: Amoxicillin 500 mg"
                           value="{{ old('komposisi') }}" required>
                    @error('komposisi')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Indikasi <span class="req">*</span></label>
                    <input type="text" name="indikasi"
                           class="form-input {{ $errors->has('indikasi') ? 'is-invalid' : '' }}"
                           placeholder="Contoh: Infeksi bakteri"
                           value="{{ old('indikasi') }}" required>
                    @error('indikasi')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Golongan <span class="req">*</span></label>
                    <select name="golongan"
                            class="form-input {{ $errors->has('golongan') ? 'is-invalid' : '' }}" required>
                        <option value="">— Pilih Golongan —</option>
                        <option value="BEBAS" {{ old('golongan') == 'BEBAS' ? 'selected' : '' }}>BEBAS</option>
                        <option value="KERAS" {{ old('golongan') == 'KERAS' ? 'selected' : '' }}>KERAS</option>
                    </select>
                    @error('golongan')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

            </div>
            <div class="form-footer">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Produk
                </button>
                <a href="{{ route('admin.prescription-products.index') }}" class="btn-cancel">
                    <i class="fa-solid fa-xmark"></i> Batal
                </a>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="header-icon"><i class="fa-solid fa-image"></i></div>
                <h3>Foto Produk</h3>
            </div>
            <div class="form-body">
                <div class="upload-zone" id="dropZone" onclick="document.getElementById('gambar').click()">
                    <div class="upload-icon">📸</div>
                    <p>Klik atau drag & drop gambar di sini</p>
                    <button type="button" class="btn-choose" onclick="event.stopPropagation();document.getElementById('gambar').click()">
                        <i class="fa-solid fa-folder-open"></i> Pilih File
                    </button>
                    <small>JPG, PNG, GIF — Maks. 10MB</small>
                </div>
                <input type="file" id="gambar" name="gambar" accept="image/*" style="display:none;">
                @error('gambar')
                    <div class="form-error" style="margin-top:0.5rem;"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror

                <div class="img-preview-wrap" id="imgPreviewWrap">
                    <span class="img-preview-label">Preview</span>
                    <img id="previewImg" src="" alt="Preview">
                </div>

                <div style="margin-top:1rem;padding:0.75rem;background:#fff5f5;border-radius:0.5rem;border:1px solid #fee2e2;">
                    <p style="font-size:0.78rem;color:#6b7280;margin:0;line-height:1.6;">
                        <i class="fa-solid fa-circle-info" style="color:#dc2626;margin-right:0.3rem;"></i>
                        Foto opsional. Jika tidak diupload, akan ditampilkan ikon default.
                    </p>
                </div>
            </div>
        </div>

    </div>
</form>

<script>
    const input = document.getElementById('gambar');
    const dropZone = document.getElementById('dropZone');
    const previewWrap = document.getElementById('imgPreviewWrap');
    const previewImg = document.getElementById('previewImg');

    function showPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewWrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', e => showPreview(e.target.files[0]));

    ['dragenter','dragover'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault(); e.stopPropagation();
        dropZone.classList.add('drag-over');
    }));
    ['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault(); e.stopPropagation();
        dropZone.classList.remove('drag-over');
    }));
    dropZone.addEventListener('drop', e => {
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            showPreview(file);
        }
    });
</script>
@endsection

