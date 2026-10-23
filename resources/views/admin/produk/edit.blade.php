@extends('layouts.admin')

@section('title', 'Edit Produk - Admin Apotek Medix')
@section('page-title', 'âœï¸ Edit: ' . $medicine->nama_obat)

@section('styles')
<style>
    .form-card { background:white; border-radius:0.75rem; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #f0f0f0; overflow:hidden; }
    .form-card-header { padding:1rem 1.5rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:0.6rem; }
    .form-card-header h3 { font-size:0.95rem; font-weight:700; color:#1f2937; margin:0; }
    .header-icon { width:32px; height:32px; background:#e3f2fd; border-radius:0.4rem; display:flex; align-items:center; justify-content:center; color:#1E88E5; font-size:0.9rem; }
    .form-body { padding:1.5rem; display:flex; flex-direction:column; gap:1rem; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .form-group { margin-bottom:0; }
    .form-label { display:block; font-size:0.8rem; font-weight:700; color:#374151; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.04em; }
    .form-label .req { color:#ef4444; margin-left:2px; }
    .form-input { width:100%; padding:0.6rem 0.85rem; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.9rem; color:#1f2937; background:#fafafa; transition:all 0.2s; }
    .form-input:focus { outline:none; border-color:#1E88E5; background:white; box-shadow:0 0 0 3px rgba(30,136,229,0.08); }
    .form-input.is-invalid { border-color:#ef4444; }
    .form-error { font-size:0.78rem; color:#ef4444; margin-top:0.3rem; display:flex; align-items:center; gap:0.3rem; }
    .upload-zone { border:2px dashed #d1d5db; border-radius:0.6rem; padding:1.75rem 1rem; text-align:center; background:#fafafa; cursor:pointer; transition:all 0.2s; }
    .upload-zone:hover, .upload-zone.drag-over { border-color:#1E88E5; background:#f0f7ff; }
    .upload-zone .upload-icon { font-size:2rem; margin-bottom:0.5rem; }
    .upload-zone p { font-size:0.85rem; color:#6b7280; margin:0 0 0.75rem; }
    .upload-zone small { font-size:0.75rem; color:#9ca3af; display:block; margin-top:0.5rem; }
    .btn-choose { display:inline-flex; align-items:center; gap:0.35rem; padding:0.45rem 1rem; background:white; border:1px solid #d1d5db; border-radius:0.4rem; font-size:0.82rem; font-weight:600; color:#374151; cursor:pointer; transition:all 0.2s; }
    .btn-choose:hover { background:#f3f4f6; border-color:#9ca3af; }
    .img-preview-wrap { margin-top:0.75rem; display:none; border-radius:0.5rem; overflow:hidden; border:1px solid #e5e7eb; position:relative; }
    .img-preview-wrap img { width:100%; max-height:220px; object-fit:contain; display:block; background:#f9fafb; }
    .img-preview-label { position:absolute; top:0.5rem; left:0.5rem; background:rgba(0,0,0,0.55); color:white; font-size:0.7rem; font-weight:600; padding:0.2rem 0.5rem; border-radius:0.3rem; }
    .current-image { margin-bottom:1rem; border-radius:0.5rem; overflow:hidden; border:1px solid #e5e7eb; }
    .current-image img { width:100%; max-height:220px; object-fit:contain; display:block; background:#f9fafb; }
    .form-footer { padding:1rem 1.5rem; border-top:1px solid #f3f4f6; display:flex; gap:0.6rem; align-items:center; }
    .btn-save { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.5rem; background:#1E88E5; color:white; border:none; border-radius:0.5rem; font-size:0.9rem; font-weight:700; cursor:pointer; transition:all 0.2s; }
    .btn-save:hover { background:#1565C0; transform:translateY(-1px); }
    .btn-cancel { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.25rem; background:white; color:#6b7280; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.9rem; font-weight:600; text-decoration:none; transition:all 0.2s; }
    .btn-cancel:hover { background:#f9fafb; color:#374151; }
    .two-col-layout { display:grid; grid-template-columns:1fr 340px; gap:1.25rem; align-items:start; }
    .kat-selector { display:grid; grid-template-columns:repeat(3,1fr); gap:0.6rem; }
    .kat-card { border:2px solid #e5e7eb; border-radius:0.6rem; padding:0.75rem 0.5rem; text-align:center; cursor:pointer; transition:all 0.2s; background:white; }
    .kat-card:hover { border-color:#1E88E5; }
    .kat-card.selected { border-color:#1E88E5; background:#e3f2fd; }
    .kat-card input[type=radio] { display:none; }
    .kat-card .kat-icon { font-size:1.5rem; display:block; margin-bottom:0.3rem; }
    .kat-card .kat-label { font-size:0.72rem; font-weight:700; color:#374151; line-height:1.3; }
    .kat-card.selected .kat-label { color:#1565C0; }
    @media (max-width:900px) { .two-col-layout { grid-template-columns:1fr; } }
    @media (max-width:600px) { .form-grid { grid-template-columns:1fr; } .form-body { padding:1rem; } .form-footer { padding:1rem; } .kat-selector { grid-template-columns:1fr 1fr; } }
</style>
@endsection

@section('content')

<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#9ca3af;margin-bottom:1.25rem;">
    <a href="{{ route('admin.produk.index') }}" style="color:#1E88E5;text-decoration:none;font-weight:600;">ðŸ›’ Produk Kami</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
    <span style="color:#374151;font-weight:600;">Edit Produk</span>
</div>

<form action="{{ route('admin.produk.update', $medicine->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="kategori_produk" value="{{ request('kategori_produk') }}">
    <input type="hidden" name="pabrik" value="{{ request('pabrik') }}">
    <input type="hidden" name="page" value="{{ request('page') }}">
    <div class="two-col-layout">

        {{-- Kiri: Info --}}
        <div class="form-card">
            <div class="form-card-header">
                <div class="header-icon"><i class="fa-solid fa-circle-info"></i></div>
                <h3>Informasi Produk</h3>
            </div>
            <div class="form-body">

                <div class="form-group">
                    <label class="form-label">Distributor</label>
                    <input type="text" name="distributor" class="form-input {{ $errors->has('distributor') ? 'is-invalid' : '' }}"
                           value="{{ old('distributor', $medicine->distributor) }}">
                    @error('distributor')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Principle / Pabrik <span class="req">*</span></label>
                    <input type="text" name="kategori" class="form-input {{ $errors->has('kategori') ? 'is-invalid' : '' }}"
                           value="{{ old('kategori', $medicine->kategori) }}" required>
                    @error('kategori')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Produk <span class="req">*</span></label>
                    <input type="text" name="nama_obat" class="form-input {{ $errors->has('nama_obat') ? 'is-invalid' : '' }}"
                           value="{{ old('nama_obat', $medicine->nama_obat) }}" required>
                    @error('nama_obat')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Satuan / Sediaan</label>
                    <input type="text" name="sediaan"
                           class="form-input {{ $errors->has('sediaan') ? 'is-invalid' : '' }}"
                           placeholder="Contoh: fls, box, tube, pcs atau ketik bebas"
                           value="{{ old('sediaan', $medicine->sediaan) }}">
                    @error('sediaan')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Kelompok</label>
                    <select name="kelompok" class="form-input {{ $errors->has('kelompok') ? 'is-invalid' : '' }}">
                        <option value="">â€” Pilih Kelompok â€”</option>
                        <option value="PBF"    {{ old('kelompok', $medicine->kelompok) == 'PBF'    ? 'selected' : '' }}>PBF</option>
                        <option value="APOTEK" {{ old('kelompok', $medicine->kelompok) == 'APOTEK' ? 'selected' : '' }}>APOTEK</option>
                    </select>
                    @error('kelompok')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Produk</label>
                    <textarea name="deskripsi" class="form-input {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" rows="3"
                              placeholder="Contoh: Obat pereda demam dan nyeri ringan.">{{ old('deskripsi', $medicine->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Modal (Rp)</label>
                        <input type="number" name="harga_modal" class="form-input" step="1" min="0" value="{{ old('harga_modal', $medicine->harga_modal) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Retail / box (Rp)</label>
                        <input type="number" name="harga" class="form-input {{ $errors->has('harga') ? 'is-invalid' : '' }}"
                               step="1" min="0" value="{{ old('harga', $medicine->harga) }}">
                        @error('harga')
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stok (box) <span class="req">*</span></label>
                        <input type="number" name="stok" class="form-input {{ $errors->has('stok') ? 'is-invalid' : '' }}"
                               min="0" step="0.000001" value="{{ old('stok', $medicine->stock_boxes) }}" required>
                        <small>Stok dicatat dalam box; sisa satuan kecil tampil setelah terjadi penjualan.</small>
                        @error('stok')
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    @include('admin.partials.packaging-fields', ['medicine' => $medicine])
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-input {{ $errors->has('sku') ? 'is-invalid' : '' }}"
                               value="{{ old('sku', $medicine->sku) }}">
                        @error('sku')
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Terjual</label>
                        <input type="number" name="terjual" class="form-input {{ $errors->has('terjual') ? 'is-invalid' : '' }}"
                               min="0" value="{{ old('terjual', $medicine->terjual ?? 0) }}">
                        @error('terjual')
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grade</label>
                        <input type="text" name="grade" class="form-input {{ $errors->has('grade') ? 'is-invalid' : '' }}"
                               value="{{ old('grade', $medicine->grade) }}">
                        @error('grade')
                            <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Komposisi</label>
                    <textarea name="komposisi" class="form-input" rows="3"
                              placeholder="Contoh: Paracetamol 500 mg, Aqua, Glycerin...">{{ old('komposisi', $medicine->komposisi) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Indikasi / Kegunaan</label>
                    <textarea name="indikasi" class="form-input" rows="3"
                              placeholder="Contoh: Meredakan demam dan nyeri ringan hingga sedang...">{{ old('indikasi', $medicine->indikasi) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori Produk / Resep <span class="req">*</span></label>
                    <div class="kat-selector">
                        @foreach($kategoriOptions as $kat)
                            @php
                                $icon = match($kat) { 'OBAT' => 'ðŸ’Š', 'SKINCARE & KOSMETIK' => 'âœ¨', 'ALAT KESEHATAN' => 'ðŸ©º', default => 'ðŸ“¦' };
                                $isSelected = old('kategori_produk', $medicine->kategori_produk) === $kat;
                            @endphp
                            <label class="kat-card {{ $isSelected ? 'selected' : '' }}" onclick="selectKat(this)">
                                <input type="radio" name="kategori_produk" value="{{ $kat }}" {{ $isSelected ? 'checked' : '' }}>
                                <span class="kat-icon">{{ $icon }}</span>
                                <span class="kat-label">{{ $kat }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('kategori_produk')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

            </div>
            <div class="form-footer">
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.produk.index', array_filter(['search' => request('search'), 'kategori_produk' => request('kategori_produk'), 'pabrik' => request('pabrik'), 'page' => request('page')])) }}" class="btn-cancel">
                    <i class="fa-solid fa-xmark"></i> Batal
                </a>
            </div>
        </div>

        {{-- Kanan: Foto --}}
        <div class="form-card">
            <div class="form-card-header">
                <div class="header-icon"><i class="fa-solid fa-image"></i></div>
                <h3>Logo (LOGO)</h3>
            </div>
            <div class="form-body">
                @if($medicine->gambar)
                    <div class="current-image" id="currentImageWrap">
                        <div style="font-size:0.875rem;color:#6b7280;margin-bottom:0.5rem;">Foto saat ini:</div>
                        <img src="{{ url('storage/' . $medicine->gambar) }}" alt="{{ $medicine->nama_obat }}">
                    </div>
                    <div style="display:flex;gap:0.5rem;align-items:center;margin-bottom:1rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.45rem 0.85rem;background:#fee2e2;border:1px solid #fca5a5;border-radius:0.4rem;font-size:0.82rem;font-weight:600;color:#b91c1c;">
                            <input type="checkbox" name="delete_gambar" value="1" id="deleteGambar"
                                   style="width:14px;height:14px;accent-color:#ef4444;"
                                   onchange="toggleDeletePhoto(this)">
                            <i class="fa-solid fa-trash"></i> Hapus Foto
                        </label>
                    </div>
                @endif
                <div class="upload-zone" id="dropZone" onclick="document.getElementById('gambar').click()">
                    <div class="upload-icon">ðŸ“¸</div>
                    <p>Klik atau drag & drop gambar di sini</p>
                    <button type="button" class="btn-choose" onclick="event.stopPropagation();document.getElementById('gambar').click()">
                        <i class="fa-solid fa-folder-open"></i> Pilih File
                    </button>
                    <small>JPG, PNG, GIF â€” Maks. 10MB</small>
                </div>
                <input type="file" id="gambar" name="gambar" accept="image/*" style="display:none;">
                @error('gambar')
                    <div class="form-error" style="margin-top:0.5rem;"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
                <div class="img-preview-wrap" id="imgPreviewWrap">
                    <span class="img-preview-label">Preview</span>
                    <img id="previewImg" src="" alt="Preview">
                </div>
            </div>
        </div>

    </div>
</form>

<script>
function selectKat(el) {
    document.querySelectorAll('.kat-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type=radio]').checked = true;
}
function toggleDeletePhoto(cb) {
    const wrap = document.getElementById('currentImageWrap');
    if (wrap) { wrap.style.opacity = cb.checked ? '0.35' : '1'; wrap.style.filter = cb.checked ? 'grayscale(1)' : 'none'; }
}
const input = document.getElementById('gambar');
const dropZone = document.getElementById('dropZone');
const previewWrap = document.getElementById('imgPreviewWrap');
const previewImg = document.getElementById('previewImg');
function showPreview(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => { previewImg.src = e.target.result; previewWrap.style.display = 'block'; };
    reader.readAsDataURL(file);
}
input.addEventListener('change', e => showPreview(e.target.files[0]));
['dragenter','dragover'].forEach(ev => dropZone.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); dropZone.classList.add('drag-over'); }));
['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('drag-over'); }));
dropZone.addEventListener('drop', e => {
    const file = e.dataTransfer.files[0];
    if (file) { const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files; showPreview(file); }
});
</script>
@endsection
