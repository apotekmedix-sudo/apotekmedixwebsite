@extends('layouts.frontend')

@section('title', $doctor->display_name . ' - Chat Dokter Apotek Medix')

@section('styles')
<style>
    body.public-theme main.doctor-consult-page{max-width:850px;margin:2rem auto;padding:0 1rem 3rem;color:#172b4d!important}
    body.public-theme main.doctor-consult-page .doctor-profile{overflow:hidden;border:1px solid #dbe4f0!important;border-radius:18px;background:#fff!important;box-shadow:0 12px 32px #0f172a0d}
    body.public-theme main.doctor-consult-page .doctor-cover{display:grid;grid-template-columns:150px 1fr;align-items:center;gap:1.25rem;padding:1.25rem;background:#f1f6fc!important}
    body.public-theme main.doctor-consult-page .doctor-photo{width:150px;height:170px;object-fit:cover;border:1px solid #dbe4f0;border-radius:12px;background:#fff}
    body.public-theme main.doctor-consult-page .doctor-profile h1{margin:.2rem 0;color:#10243e!important;font-size:1.6rem}
    body.public-theme main.doctor-consult-page .doctor-specialty{color:#475569!important}
    body.public-theme main.doctor-consult-page .doctor-detail{padding:1.25rem;background:#fff!important}
    body.public-theme main.doctor-consult-page .doctor-detail h2{color:#10243e!important;font-size:1.05rem}
    body.public-theme main.doctor-consult-page .doctor-row{padding:.75rem 0;border-bottom:1px solid #e2e8f0;color:#334155!important;line-height:1.55}
    body.public-theme main.doctor-consult-page .doctor-row strong{color:#172b4d!important}
    body.public-theme main.doctor-consult-page .doctor-price{padding:.9rem 1.25rem;background:#eff6ff!important;color:#10243e!important;font-size:1.2rem;font-weight:800}
    body.public-theme main.doctor-consult-page .doctor-price small{color:#475569!important}
    body.public-theme main.doctor-consult-page .verified-badge{display:inline-block;padding:.25rem .5rem;border-radius:6px;background:#dcfce7!important;color:#166534!important;font-size:.75rem;font-weight:800}
    body.public-theme main.doctor-consult-page .verified-badge i{color:#166534!important}
    body.public-theme main.doctor-consult-page a.consult-primary,
    body.public-theme main.doctor-consult-page a.consult-primary:link,
    body.public-theme main.doctor-consult-page a.consult-primary:visited{display:inline-flex;justify-content:center;align-items:center;gap:.5rem;min-height:44px;padding:.7rem 1rem;border:1px solid #0755c8!important;border-radius:9px;background:#0755c8!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font-weight:800;text-decoration:none}
    body.public-theme main.doctor-consult-page a.consult-primary *,
    body.public-theme main.doctor-consult-page a.consult-primary:visited *{color:#fff!important;-webkit-text-fill-color:#fff!important}
    body.public-theme main.doctor-consult-page a.consult-primary:hover,
    body.public-theme main.doctor-consult-page a.consult-primary:focus-visible,
    body.public-theme main.doctor-consult-page a.consult-primary:hover *{border-color:#063583!important;background:#063583!important;color:#fff!important;-webkit-text-fill-color:#fff!important}
    body.public-theme main.doctor-consult-page .consult-actions{padding:0 1.25rem 1.25rem;background:#fff!important}
    body.public-theme main.doctor-consult-page .consult-muted{color:#475569!important;line-height:1.55}
    body.public-theme main.doctor-consult-page .consult-actions>a:not(.consult-primary){color:#0755c8!important}
    @media(max-width:560px){.doctor-cover{grid-template-columns:90px 1fr;gap:.8rem}.doctor-photo{width:90px;height:110px}.doctor-profile h1{font-size:1.25rem}}
</style>
@endsection

@section('content')
<main class="doctor-consult-page">
    <article class="doctor-profile">
        <header class="doctor-cover">
            @if($doctor->photo_url)<img class="doctor-photo" src="{{ $doctor->photo_url }}" alt="Foto {{ $doctor->display_name }}">@else<div class="doctor-photo"></div>@endif
            <div>
                <span class="verified-badge"><i class="fa-solid fa-circle-check"></i> STR terverifikasi</span>
                <h1>{{ $doctor->display_name }}</h1>
                <p class="doctor-specialty">{{ $doctor->specialty }} · {{ $doctor->experience_years }} tahun pengalaman</p>
            </div>
        </header>
        <div class="doctor-price">Rp {{ number_format($doctor->consultation_price, 0, ',', '.') }} <small style="font-size:.8rem;font-weight:500">untuk sesi chat {{ $doctor->session_duration_minutes }} menit</small></div>
        <section class="doctor-detail">
            <h2>Profil dokter</h2>
            @if($doctor->bio)<div class="doctor-row">{{ $doctor->bio }}</div>@endif
            @if($doctor->education)<div class="doctor-row"><strong>Alumnus</strong><br>{{ $doctor->education }}</div>@endif
            @if($doctor->practice_place || $doctor->practice_city)<div class="doctor-row"><strong>Tempat praktik</strong><br>{{ collect([$doctor->practice_place, $doctor->practice_city])->filter()->join(', ') }}</div>@endif
            <div class="doctor-row"><strong>Nomor STR</strong><br>{{ $doctor->str_number }}</div>
            <p class="consult-muted">Pembayaran diperiksa admin. Timer {{ $doctor->session_duration_minutes }} menit mulai berjalan saat dokter masuk ke room chat.</p>
        </section>
        <div class="consult-actions">
            @if($doctorAvailable)
                <a class="consult-primary" href="{{ route('consultations.details', $doctor) }}"><i class="fa-solid fa-comments"></i> Chat</a>
            @else
                <p class="consult-muted">Dokter sedang tidak tersedia untuk sesi baru.</p>
                <a href="{{ route('consultations.doctors') }}">Kembali ke daftar dokter</a>
            @endif
        </div>
    </article>
</main>
@endsection
