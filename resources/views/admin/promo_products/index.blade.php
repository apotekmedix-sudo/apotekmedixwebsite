@extends('layouts.admin')
@section('title', 'Promo Produk - Admin Apotek Medix')
@section('page-title', 'ðŸ·ï¸ Promo Produk')

@section('styles')
<style>
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; gap:1rem; flex-wrap:wrap; }
.page-header h2 { font-size:1.1rem; font-weight:700; color:#1f2937; margin:0 0 0.2rem; }
.page-header p  { font-size:0.85rem; color:#6b7280; margin:0; }
.btn-add { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.25rem; background:#1E88E5; color:#fff; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none; transition:all 0.2s; }
.btn-add:hover { background:#1565C0; color:#fff; transform:translateY(-1px); }

.promo-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1.25rem; }
.promo-card { background:#fff; border-radius:1rem; overflow:hidden; border:1px solid #e5e7eb; box-shadow:0 1px 4px rgba(0,0,0,0.06); transition:box-shadow 0.2s; }
.promo-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.1); }
.promo-img-wrap { width:100%; aspect-ratio:1/1; overflow:hidden; background:#e3f2fd; position:relative; }
.promo-img { width:100%; height:100%; object-fit:cover; display:block; }
.promo-body { padding:0.85rem; }
.promo-title { font-size:0.88rem; font-weight:700; color:#1f2937; margin:0 0 0.2rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.promo-sub   { font-size:0.76rem; color:#6b7280; margin:0 0 0.5rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.promo-meta  { display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap; margin-bottom:0.75rem; }
.badge-aktif   { padding:0.18rem 0.55rem; border-radius:20px; font-size:0.7rem; font-weight:700; background:#d1fae5; color:#065f46; }
.badge-nonaktif{ padding:0.18rem 0.55rem; border-radius:20px; font-size:0.7rem; font-weight:700; background:#fee2e2; color:#991b1b; }
.badge-urutan  { padding:0.18rem 0.55rem; border-radius:20px; font-size:0.7rem; font-weight:600; background:#e3f2fd; color:#1565C0; }
.promo-actions { display:flex; gap:0.4rem; align-items:center; flex-wrap:wrap; }
.btn-edit { display:inline-flex; align-items:center; gap:0.25rem; padding:0.35rem 0.7rem; background:#e3f2fd; color:#1565C0; border-radius:0.4rem; font-size:0.75rem; font-weight:600; text-decoration:none; transition:all 0.2s; border:none; cursor:pointer; }
.btn-edit:hover { background:#1E88E5; color:#fff; }
.btn-del  { display:inline-flex; align-items:center; gap:0.25rem; padding:0.35rem 0.7rem; background:#fee2e2; color:#991b1b; border-radius:0.4rem; font-size:0.75rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; }
.btn-del:hover  { background:#ef4444; color:#fff; }
.btn-toggle { display:inline-flex; align-items:center; gap:0.25rem; padding:0.35rem 0.7rem; border-radius:0.4rem; font-size:0.75rem; font-weight:600; border:none; cursor:pointer; transition:all 0.2s; }
.btn-toggle-on  { background:#fef3c7; color:#92400e; }
.btn-toggle-on:hover  { background:#f59e0b; color:#fff; }
.btn-toggle-off { background:#d1fae5; color:#065f46; }
.btn-toggle-off:hover { background:#10b981; color:#fff; }

.empty-state { text-align:center; padding:4rem 2rem; background:#fff; border-radius:1rem; border:1px solid #e5e7eb; }
.empty-state i { font-size:3rem; color:#d1d5db; display:block; margin-bottom:1rem; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal-box { background:#fff; border-radius:1rem; padding:2rem; max-width:360px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.2); text-align:center; }
.modal-box h3 { font-size:1rem; font-weight:700; margin:0 0 0.5rem; }
.modal-box p  { font-size:0.875rem; color:#6b7280; margin:0 0 1.5rem; }
.modal-actions { display:flex; gap:0.6rem; justify-content:center; }
.btn-cancel { padding:0.6rem 1.5rem; background:#fff; color:#374151; border:1.5px solid #e5e7eb; border-radius:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; }
.btn-danger { padding:0.6rem 1.5rem; background:#ef4444; color:#fff; border:none; border-radius:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Kelola Promo Produk</h2>
        <p>Foto promo ditampilkan sebagai grid 1:1 kecil di halaman utama website</p>
    </div>
    <a href="{{ route('admin.promo-products.create') }}" class="btn-add">
        <i class="fa-solid fa-plus"></i> Tambah Promo
    </a>
</div>

@if(session('success'))
<div style="background:#d1fae5;color:#065f46;padding:0.85rem 1.25rem;border-radius:0.5rem;margin-bottom:1.25rem;font-size:0.875rem;font-weight:600;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif
@if(session('warning'))
<div style="background:#fef3c7;color:#92400e;padding:0.85rem 1.25rem;border-radius:0.5rem;margin-bottom:1.25rem;font-size:0.875rem;font-weight:600;">
    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}
</div>
@endif

@if($promos->count())
<div class="promo-grid">
    @foreach($promos as $promo)
    <div class="promo-card">
        <div class="promo-img-wrap">
            <img src="{{ url('storage/'.$promo->gambar) }}" alt="{{ $promo->judul }}" class="promo-img">
        </div>
        <div class="promo-body">
            <h3 class="promo-title">{{ $promo->judul }}</h3>
            @if($promo->subjudul)<p class="promo-sub">{{ $promo->subjudul }}</p>@endif
            <div class="promo-meta">
                <span class="{{ $promo->aktif ? 'badge-aktif' : 'badge-nonaktif' }}">
                    {{ $promo->aktif ? 'â— Aktif' : 'â—‹ Nonaktif' }}
                </span>
                <span class="badge-urutan">#{{ $promo->urutan }}</span>
            </div>
            <div class="promo-actions">
                <a href="{{ route('admin.promo-products.edit', $promo) }}" class="btn-edit">
                    <i class="fa-solid fa-pen"></i> Edit
                </a>
                <button class="btn-toggle {{ $promo->aktif ? 'btn-toggle-on' : 'btn-toggle-off' }}"
                    onclick="togglePromo({{ $promo->id }}, this)">
                    <i class="fa-solid {{ $promo->aktif ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                    {{ $promo->aktif ? 'Off' : 'On' }}
                </button>
                <button class="btn-del" onclick="confirmDelete({{ $promo->id }}, '{{ addslashes($promo->judul) }}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state">
    <i class="fa-solid fa-tag"></i>
    <h3 style="color:#374151;margin-bottom:0.5rem;">Belum ada promo produk</h3>
    <p>Tambahkan foto promo untuk ditampilkan di halaman utama.</p>
    <a href="{{ route('admin.promo-products.create') }}" class="btn-add" style="margin-top:1rem;display:inline-flex;">
        <i class="fa-solid fa-plus"></i> Tambah Promo Pertama
    </a>
</div>
@endif

{{-- Delete Modal --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div style="font-size:2.5rem;margin-bottom:0.75rem;">ðŸ—‘ï¸</div>
        <h3>Hapus Promo?</h3>
        <p id="deleteMsg">Promo ini akan dihapus permanen.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Batal</button>
            <form id="deleteForm" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(id, judul) {
    document.getElementById('deleteMsg').textContent = 'Promo "' + judul + '" akan dihapus permanen.';
    document.getElementById('deleteForm').action = '/admin/promo-products/' + id;
    document.getElementById('deleteModal').classList.add('show');
}
function closeModal() { document.getElementById('deleteModal').classList.remove('show'); }

async function togglePromo(id, btn) {
    const res  = await fetch('/admin/promo-products/' + id + '/toggle', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    });
    const data = await res.json();
    const aktif = data.aktif;
    btn.className = 'btn-toggle ' + (aktif ? 'btn-toggle-on' : 'btn-toggle-off');
    btn.innerHTML = `<i class="fa-solid ${aktif ? 'fa-eye-slash' : 'fa-eye'}"></i> ${aktif ? 'Off' : 'On'}`;
    const badge = btn.closest('.promo-card').querySelector('.badge-aktif, .badge-nonaktif');
    badge.className = aktif ? 'badge-aktif' : 'badge-nonaktif';
    badge.textContent = aktif ? 'â— Aktif' : 'â—‹ Nonaktif';
}
</script>
@endsection

