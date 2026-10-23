@extends('layouts.admin')
@section('title', 'Tambah Promo Produk - Admin Apotek Medix')
@section('page-title', 'ðŸ·ï¸ Tambah Promo Produk')

@section('styles')
<style>
.form-card { background:#fff; border-radius:1rem; padding:1.75rem; border:1px solid #e5e7eb; box-shadow:0 1px 4px rgba(0,0,0,0.06); max-width:600px; }
.form-group { margin-bottom:1.25rem; }
.form-label { display:block; font-size:0.82rem; font-weight:700; color:#374151; margin-bottom:0.4rem; }
.form-label .req { color:#ef4444; }
.form-control { width:100%; padding:0.65rem 0.9rem; border:1.5px solid #e5e7eb; border-radius:0.5rem; font-size:0.9rem; color:#374151; transition:border-color 0.2s; background:#fafafa; }
.form-control:focus { outline:none; border-color:#1E88E5; background:#fff; box-shadow:0 0 0 3px rgba(30,136,229,0.08); }
.form-hint { font-size:0.75rem; color:#9ca3af; margin-top:0.3rem; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.toggle-wrap { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; background:#f8faff; border-radius:0.5rem; border:1.5px solid #e5e7eb; }
.toggle-wrap label { font-size:0.875rem; font-weight:600; color:#374151; cursor:pointer; }
.toggle-switch { position:relative; width:44px; height:24px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; cursor:pointer; transition:0.3s; }
.toggle-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:0.3s; }
.toggle-switch input:checked + .toggle-slider { background:#1E88E5; }
.toggle-switch input:checked + .toggle-slider::before { transform:translateX(20px); }

/* Square preview */
.img-preview-wrap { width:180px; height:180px; border-radius:0.75rem; border:2px dashed #d1d5db; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f3f4f6; cursor:pointer; transition:all 0.2s; margin-top:0.5rem; }
.img-preview-wrap:hover { border-color:#90caf9; background:#e3f2fd; }
.img-preview-wrap img { width:100%; height:100%; object-fit:cover; display:none; }
.img-preview-wrap .placeholder-text { display:flex; flex-direction:column; align-items:center; gap:0.4rem; color:#9ca3af; font-size:0.8rem; text-align:center; padding:1rem; }
.img-preview-wrap .placeholder-text i { font-size:2rem; }

.btn-actions { display:flex; gap:0.75rem; margin-top:1.5rem; flex-wrap:wrap; }
.btn-save { padding:0.7rem 2rem; background:#1E88E5; color:#fff; border:none; border-radius:0.5rem; font-size:0.9rem; font-weight:700; cursor:pointer; transition:all 0.2s; display:inline-flex; align-items:center; gap:0.4rem; }
.btn-save:hover { background:#1565C0; transform:translateY(-1px); }
.btn-back { padding:0.7rem 1.5rem; background:#fff; color:#374151; border:1.5px solid #e5e7eb; border-radius:0.5rem; font-size:0.9rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.2s; }
.btn-back:hover { background:#f9fafb; color:#374151; }
@media(max-width:600px) { .form-row { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')
<div style="margin-bottom:1.25rem;">
    <a href="{{ route('admin.promo-products.index') }}" style="color:#6b7280;text-decoration:none;font-size:0.85rem;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Promo
    </a>
</div>

<div class="form-card">
    @if(isset($tableReady) && !$tableReady)
    <div style="background:#fef3c7;color:#92400e;padding:0.85rem 1.1rem;border-radius:0.5rem;margin-bottom:1.25rem;font-size:0.85rem;">
        <strong>Fitur promo belum aktif.</strong>
        Tabel <code>promo_products</code> belum ada. Jalankan <code>php artisan migrate</code> terlebih dahulu.
    </div>
    @endif

    @if($errors->any())
    <div style="background:#fee2e2;color:#7f1d1d;padding:0.85rem 1.1rem;border-radius:0.5rem;margin-bottom:1.25rem;font-size:0.85rem;">
        <strong>Ada kesalahan:</strong>
        <ul style="margin:0.4rem 0 0 1rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.promo-products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label class="form-label">Judul Promo <span class="req">*</span></label>
            <input type="text" name="judul" class="form-control" placeholder="Contoh: Promo Vitamin C Murah" value="{{ old('judul') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Subjudul / Keterangan Singkat</label>
            <input type="text" name="subjudul" class="form-control" placeholder="Contoh: Diskon 30% minggu ini" value="{{ old('subjudul') }}">
        </div>

        <div class="form-group">
            <label class="form-label">Foto Promo <span class="req">*</span></label>
            <input type="file" name="gambar" id="gambarInput" accept="image/*" class="form-control" onchange="previewImg(this)" required>
            <div class="img-preview-wrap" onclick="document.getElementById('gambarInput').click()" id="previewWrap">
                <img id="imgPreview" src="#" alt="Preview">
                <div class="placeholder-text" id="placeholder">
                    <i class="fa-solid fa-image"></i>
                    <span>Klik untuk pilih foto</span>
                    <span style="font-size:0.7rem;">Rasio 1:1 (persegi)</span>
                    <span style="font-size:0.7rem;">JPG, PNG, WEBP</span>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">URL Tujuan (opsional)</label>
                <input type="text" name="url_tujuan" class="form-control" placeholder="/products atau https://..." value="{{ old('url_tujuan') }}">
                <p class="form-hint">Link ketika foto diklik</p>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Label Tombol</label>
                <input type="text" name="label_tombol" class="form-control" placeholder="Lihat Sekarang" value="{{ old('label_tombol', 'Lihat Sekarang') }}">
            </div>
        </div>

        <div class="form-row" style="margin-top:1.25rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Urutan Tampil</label>
                <input type="number" name="urutan" class="form-control" placeholder="0" min="0" value="{{ old('urutan', 0) }}">
                <p class="form-hint">Angka kecil tampil lebih dulu</p>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Status</label>
                <div class="toggle-wrap">
                    <label class="toggle-switch">
                        <input type="checkbox" name="aktif" value="1" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <label>Aktif (tampil di website)</label>
                </div>
            </div>
        </div>

        <div class="btn-actions">
            <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Simpan Promo</button>
            <a href="{{ route('admin.promo-products.index') }}" class="btn-back"><i class="fa-solid fa-xmark"></i> Batal</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function previewImg(input) {
    const preview = document.getElementById('imgPreview');
    const placeholder = document.getElementById('placeholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

