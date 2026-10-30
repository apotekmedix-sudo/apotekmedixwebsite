@extends('layouts.frontend')

@section('title', 'Akses File KUNJUNGAN - Apotek Medix')

@section('styles')
<style>
    .kunjungan-page{max-width:560px;margin:0 auto;padding:calc(var(--navbar-height, 65px) + 1.5rem) 1rem 3rem}
    .kunjungan-card{padding:clamp(1.25rem,4vw,2rem);border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 12px 32px #0f172a0d}
    .kunjungan-card h1{margin:0 0 .45rem;color:#10243e;font-size:1.55rem}
    .kunjungan-card p{color:#52657e;line-height:1.55}
    .kunjungan-form{display:grid;gap:.7rem;margin-top:1.25rem}
    .kunjungan-form label{font-weight:700;color:#334155}
    .kunjungan-form input{width:100%;padding:.75rem;border:1px solid #cbd5e1;border-radius:9px;font:inherit}
    .kunjungan-button{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;width:fit-content;padding:.7rem 1rem;border:0;border-radius:9px;background:#08734f;color:#fff;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}
    .kunjungan-button:hover{background:#065f46;color:#fff}
    .kunjungan-error{color:#b91c1c}
    .kunjungan-message{margin-bottom:1rem;padding:.7rem .85rem;border:1px solid #bbf7d0;border-radius:9px;background:#f0fdf4;color:#166534}
</style>
@endsection

@section('content')
<main class="kunjungan-page">
    <section class="kunjungan-card">
        <h1>Akses File KUNJUNGAN</h1>
        <p>Masukkan kode akses untuk membuka ruang penyimpanan file.</p>
        @if(session('success'))<p class="kunjungan-message" role="status">{{ session('success') }}</p>@endif
        <form class="kunjungan-form" action="{{ route('kunjungan.access') }}" method="POST">
            @csrf
            <label for="access_code">Kode akses</label>
            <input id="access_code" name="access_code" type="password" required maxlength="100" autocomplete="current-password" autofocus>
            @error('access_code')<span class="kunjungan-error" role="alert">{{ $message }}</span>@enderror
            <button class="kunjungan-button" type="submit"><i class="fa-solid fa-lock-open"></i> Masuk ke KUNJUNGAN</button>
        </form>
    </section>
</main>
@endsection
