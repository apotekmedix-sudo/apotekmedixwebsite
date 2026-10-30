@extends('layouts.frontend')

@section('title', 'File KUNJUNGAN - Apotek Medix')

@section('styles')
<style>
    .kunjungan-page{max-width:1000px;margin:0 auto;padding:calc(var(--navbar-height, 65px) + 1.5rem) 1rem 3rem;color:#172b4d}
    .kunjungan-card{padding:clamp(1rem,3vw,2rem);border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 12px 32px #0f172a0d}
    .kunjungan-header,.kunjungan-toolbar,.kunjungan-file{display:flex;align-items:center;justify-content:space-between;gap:1rem}
    .kunjungan-header{margin-bottom:1.25rem}
    .kunjungan-header h1{margin:0;color:#10243e;font-size:1.6rem}
    .kunjungan-header p{margin:.35rem 0 0;color:#52657e}
    .kunjungan-form{display:grid;gap:.65rem;margin:1.25rem 0;padding:1rem;border:1px dashed #94a3b8;border-radius:12px;background:#f8fafc}
    .kunjungan-form label{font-weight:700;color:#334155}
    .kunjungan-form input[type=file]{width:100%;padding:.65rem;border:1px solid #cbd5e1;border-radius:8px;background:#fff}
    .kunjungan-note{margin:0;color:#64748b;font-size:.85rem}
    .kunjungan-button{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;padding:.65rem .9rem;border:0;border-radius:8px;background:#08734f;color:#fff;font:inherit;font-weight:800;text-decoration:none;cursor:pointer;white-space:nowrap}
    .kunjungan-button:hover{background:#065f46;color:#fff}
    .kunjungan-button-secondary{background:#475569}
    .kunjungan-search{display:flex;flex-wrap:wrap;gap:.55rem;margin:1.35rem 0}
    .kunjungan-search input,.kunjungan-search select{flex:1 1 200px;min-width:0;padding:.7rem;border:1px solid #cbd5e1;border-radius:8px;background:#fff;font:inherit}
    .kunjungan-message{margin:.75rem 0;padding:.7rem .85rem;border:1px solid #bbf7d0;border-radius:9px;background:#f0fdf4;color:#166534}
    .kunjungan-error{margin:.75rem 0;padding:.7rem .85rem;border:1px solid #fecaca;border-radius:9px;background:#fef2f2;color:#991b1b}
    .kunjungan-files{display:grid;gap:.6rem}
    .kunjungan-file{padding:.75rem;border:1px solid #e2e8f0;border-radius:10px}
    .kunjungan-file-info{min-width:0}
    .kunjungan-file-name{display:block;overflow-wrap:anywhere;font-weight:750;color:#10243e}
    .kunjungan-file-meta{display:block;margin-top:.2rem;color:#64748b;font-size:.82rem}
    .kunjungan-empty{padding:1.25rem;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;text-align:center}
    .kunjungan-pagination{margin-top:1rem}
    @media(max-width:600px){.kunjungan-header,.kunjungan-file{align-items:flex-start;flex-direction:column}.kunjungan-toolbar{width:100%}.kunjungan-toolbar .kunjungan-button{flex:1}}
</style>
@endsection

@section('content')
<main class="kunjungan-page">
    <section class="kunjungan-card">
        <header class="kunjungan-header">
            <div>
                <h1>Arsip File KUNJUNGAN</h1>
                <p>Unggah, cari, urutkan, dan unduh file.</p>
            </div>
            <form method="POST" action="{{ route('kunjungan.leave') }}">
                @csrf
                <button class="kunjungan-button kunjungan-button-secondary" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Keluar</button>
            </form>
        </header>

        @if(session('success'))<p class="kunjungan-message" role="status">{{ session('success') }}</p>@endif
        @if(session('error'))<p class="kunjungan-error" role="alert">{{ session('error') }}</p>@endif

        <form class="kunjungan-form" action="{{ route('kunjungan.files.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="kunjunganFiles">Pilih file untuk diunggah</label>
            <input id="kunjunganFiles" name="files[]" type="file" multiple required>
            <p class="kunjungan-note">Maksimal 5 file sekaligus, ukuran tiap file maksimal 20 MB.</p>
            @error('files')<span class="kunjungan-error" role="alert">{{ $message }}</span>@enderror
            @foreach($errors->get('files.*') as $fileErrors)
                @foreach($fileErrors as $fileError)<span class="kunjungan-error" role="alert">{{ $fileError }}</span>@endforeach
            @endforeach
            <button class="kunjungan-button" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Unggah file</button>
        </form>

        <form class="kunjungan-search" action="{{ route('kunjungan.room') }}" method="GET">
            <input name="search" type="search" value="{{ $search }}" maxlength="255" placeholder="Cari nama file..." aria-label="Cari nama file">
            <select name="sort" aria-label="Urutkan file">
                <option value="newest" @selected($sort === 'newest')>Terbaru ke terlama</option>
                <option value="oldest" @selected($sort === 'oldest')>Terlama ke terbaru</option>
                <option value="name_asc" @selected($sort === 'name_asc')>Nama A–Z</option>
                <option value="name_desc" @selected($sort === 'name_desc')>Nama Z–A</option>
            </select>
            <button class="kunjungan-button" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        </form>

        <div class="kunjungan-toolbar">
            <strong>{{ $files->total() }} file ditemukan</strong>
        </div>
        <section class="kunjungan-files" aria-label="Daftar file KUNJUNGAN">
            @forelse($files as $file)
                <article class="kunjungan-file">
                    <div class="kunjungan-file-info">
                        <span class="kunjungan-file-name">{{ $file->original_name }}</span>
                        <span class="kunjungan-file-meta">
                            {{ number_format($file->size_bytes / 1_048_576, 2, ',', '.') }} MB
                            · {{ $file->created_at->timezone(config('kunjungan.timezone'))->format('d-m-Y H:i') }}
                        </span>
                    </div>
                    <a class="kunjungan-button" href="{{ route('kunjungan.files.download', $file) }}"><i class="fa-solid fa-download"></i> Download</a>
                </article>
            @empty
                <p class="kunjungan-empty">Belum ada file{{ $search !== '' ? ' yang cocok dengan pencarian ini' : '' }}.</p>
            @endforelse
        </section>
        @if($files->hasPages())<div class="kunjungan-pagination">{{ $files->links() }}</div>@endif
    </section>
</main>
@endsection
