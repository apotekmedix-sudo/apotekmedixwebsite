@extends('layouts.frontend')

@section('title', 'Pilih Pasien - Chat Dokter Apotek Medix')

@section('styles')
<style>
    body.public-theme main.patient-select-page{max-width:760px;min-height:calc(100vh - var(--navbar-height,65px));margin:1rem auto;padding:1.25rem 1rem;color:#172b4d;background:#f8fafc!important}
    body.public-theme main.patient-select-page h1{margin:.35rem 0;color:#10243e!important;font-size:1.5rem}
    body.public-theme main.patient-select-page .patient-select-head{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1rem}
    body.public-theme main.patient-select-page .patient-select-head p{margin:0;color:#52657e!important}
    body.public-theme main.patient-select-page .profile-add{color:#be123c!important;font-weight:700;text-decoration:none}
    body.public-theme main.patient-select-page .profile-add:hover{text-decoration:underline}
    body.public-theme main.patient-select-page .patient-select-info{margin:1rem 0;padding:.85rem 1rem;border:1px solid #bae6fd;border-radius:8px;background:#effaff!important;color:#164e63!important;line-height:1.5}
    body.public-theme main.patient-select-page .patient-select-info,
    body.public-theme main.patient-select-page .patient-select-info strong,
    body.public-theme main.patient-select-page .patient-select-info i{color:#164e63!important}
    body.public-theme main.patient-select-page .patient-profile-option{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:.75rem 0;padding:1rem 1.15rem;border:1px solid #d1d5db;border-radius:8px;background:#fff!important;color:#172b4d!important;box-shadow:0 2px 5px #0f172a12;cursor:pointer}
    body.public-theme main.patient-select-page .patient-profile-option:has(input:checked){border-color:#e6004c;box-shadow:0 0 0 2px #fce7f3}
    body.public-theme main.patient-select-page .patient-profile-option span,
    body.public-theme main.patient-select-page .patient-profile-option strong{display:block;color:#172b4d!important}
    body.public-theme main.patient-select-page .patient-profile-option small{display:block;margin-top:.3rem;color:#475569!important}
    body.public-theme main.patient-select-page .patient-profile-option input{width:18px;height:18px;accent-color:#e6004c}
    body.public-theme main.patient-select-page .patient-select-actions{margin-top:1rem}
    body.public-theme main.patient-select-page .patient-select-actions button{width:100%;min-height:46px;border:0;border-radius:5px;background:#0755c8!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font:inherit;font-weight:800}
    body.public-theme main.patient-select-page .patient-select-actions button:hover{background:#063583!important}
    @media(max-width:600px){body.public-theme main.patient-select-page{margin:.5rem;padding:1rem .75rem}.patient-select-head{align-items:flex-start!important}}
</style>
@endsection

@section('content')
<main class="patient-select-page">
    <header class="patient-select-head">
        <div>
            <p>Konsultasi online · Langkah 1 dari 2</p>
            <h1>Pilih Pasien</h1>
        </div>
        <a class="profile-add" href="{{ route('consultations.patient.create', $doctor) }}">+ Profil Baru</a>
    </header>

    <div class="patient-select-info">
        <strong><i class="fa-solid fa-circle-info"></i> Pilih pasien yang perlu penanganan</strong><br>
        Pilihan pasien memengaruhi informasi kesehatan yang akan dicantumkan pada konsultasi.
    </div>

    @if($errors->any())
        <div role="alert" style="margin:1rem 0;padding:.75rem 1rem;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;color:#991b1b">
            @foreach($errors->all() as $error)<p style="margin:0">{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('consultations.patient.select', $doctor) }}">
        @csrf
        @foreach($profiles as $profileId => $profile)
            <label class="patient-profile-option">
                <span>
                    <strong>{{ $profile['name'] }}</strong>
                    <small>{{ $profile['gender'] }} · Lahir {{ \Illuminate\Support\Carbon::parse($profile['birth_date'])->format('d-m-Y') }}</small>
                </span>
                <input type="radio" name="patient_profile" value="{{ $profileId }}" @checked($profileId === $selectedProfileId || ($selectedProfileId === null && $loop->first)) required>
            </label>
        @endforeach
        <div class="patient-select-actions">
            <button type="submit">Lanjut</button>
        </div>
    </form>
</main>
@endsection
