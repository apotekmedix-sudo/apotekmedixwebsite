@extends('layouts.frontend')

@section('title', 'Cari Dokter - Apotek Medix')

@section('styles')
<style>
    body.public-theme main.consult-page{max-width:1160px;margin:1.5rem auto;padding:1.25rem 1rem 3rem;color:#172b4d;background:#fff!important;border-radius:12px}
    body.public-theme main.consult-page .consult-heading{margin-bottom:1rem}
    body.public-theme main.consult-page .consult-heading h1{margin:.3rem 0;color:#10243e!important;font-size:clamp(1.4rem,3vw,1.8rem)}
    body.public-theme main.consult-page .consult-muted{color:#52657e!important;line-height:1.55}
    body.public-theme main.consult-page .consult-kicker{color:#08734f!important;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
    body.public-theme main.consult-page .specialty-search{position:relative;display:block;margin:0 0 1.5rem}
    body.public-theme main.consult-page .specialty-search input{width:100%;min-height:42px;padding:.65rem 2.5rem .65rem .8rem;border:1px solid #cbd5e1;border-radius:5px;background:#fff;color:#172b4d;font-size:.9rem}
    body.public-theme main.consult-page .specialty-search input::placeholder{color:#8292a8}
    body.public-theme main.consult-page .specialty-search i{position:absolute;top:50%;right:.9rem;color:#52657e;transform:translateY(-50%)}
    body.public-theme main.consult-page .specialty-list{display:grid;grid-template-columns:repeat(9,minmax(0,1fr));gap:1.1rem .35rem;margin:.75rem 0 1.5rem}
    body.public-theme main.consult-page .specialty-list>a.specialty-chip,
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:visited{display:flex;min-height:124px;flex-direction:column;align-items:center;justify-content:flex-start;gap:.45rem;padding:.4rem .2rem;border:1px solid transparent;border-radius:10px;background:#fff!important;color:#172b4d!important;text-align:center;text-decoration:none;font-size:.82rem;font-weight:600;line-height:1.3}
    body.public-theme main.consult-page .specialty-list>a.specialty-chip>span:not(.specialty-icon){color:#172b4d!important}
    body.public-theme main.consult-page .specialty-icon{display:grid;width:52px;height:52px;flex:none;place-items:center;border-radius:50%;background:#dbe7f8!important;color:#be123c!important;font-size:1.35rem}
    body.public-theme main.consult-page .specialty-icon>i{color:#be123c!important}
    body.public-theme main.consult-page .specialty-count{color:#475569!important;font-size:.7rem;font-weight:500;line-height:1.25}
    body.public-theme main.consult-page .specialty-count.empty{color:#9a3412!important;font-weight:700}
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:hover,
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:focus-visible{border-color:#60a5fa;background:#eff6ff!important;color:#003eaa!important;outline:3px solid #bfdbfe;outline-offset:1px}
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:hover>span:not(.specialty-icon),
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:focus-visible>span:not(.specialty-icon){color:#003eaa!important}
    body.public-theme main.consult-page .specialty-chip:hover .specialty-icon,
    body.public-theme main.consult-page .specialty-chip:focus-visible .specialty-icon{background:#bfdbfe!important;color:#9f1239!important}
    body.public-theme main.consult-page .specialty-chip:hover .specialty-icon>i,
    body.public-theme main.consult-page .specialty-chip:focus-visible .specialty-icon>i{color:#9f1239!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+1) .specialty-icon{background:#dbeafe!important;color:#1d4ed8!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+1) .specialty-icon>i{color:#1d4ed8!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+2) .specialty-icon{background:#ccfbf1!important;color:#0f766e!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+2) .specialty-icon>i{color:#0f766e!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+3) .specialty-icon{background:#ede9fe!important;color:#6d28d9!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+3) .specialty-icon>i{color:#6d28d9!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+4) .specialty-icon{background:#ffedd5!important;color:#c2410c!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+4) .specialty-icon>i{color:#c2410c!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+5) .specialty-icon{background:#dcfce7!important;color:#15803d!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+5) .specialty-icon>i{color:#15803d!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+6) .specialty-icon{background:#fce7f3!important;color:#be185d!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+6) .specialty-icon>i{color:#be185d!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+7) .specialty-icon{background:#cffafe!important;color:#0e7490!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+7) .specialty-icon>i{color:#0e7490!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n) .specialty-icon{background:#fef3c7!important;color:#a16207!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n) .specialty-icon>i{color:#a16207!important}
    body.public-theme main.consult-page .specialty-selection{display:flex;align-items:center;gap:.65rem;margin-bottom:1rem}
    body.public-theme main.consult-page a.specialty-back,
    body.public-theme main.consult-page a.specialty-back:visited{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .9rem;border:1px solid #0755c8;border-radius:8px;background:#0755c8!important;color:#fff!important;font-weight:700;text-decoration:none}
    body.public-theme main.consult-page a.specialty-back>i{color:#fff!important}
    body.public-theme main.consult-page a.specialty-back:hover,
    body.public-theme main.consult-page a.specialty-back:focus-visible{border-color:#063583;background:#063583!important;color:#fff!important;text-decoration:none}
    body.public-theme main.consult-page a.specialty-back:hover>i,
    body.public-theme main.consult-page a.specialty-back:focus-visible>i{color:#fff!important}
    body.public-theme main.consult-page .doctor-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
    body.public-theme main.consult-page .doctor-card{display:grid;grid-template-columns:92px 1fr;gap:1rem;padding:1rem;border:1px solid #dbe4f0;border-radius:14px;background:#fff;box-shadow:0 8px 20px #0f172a08}
    body.public-theme main.consult-page .doctor-photo{width:92px;height:110px;object-fit:cover;border:1px solid #dbe4f0;border-radius:10px;background:#f1f5f9}
    body.public-theme main.consult-page .doctor-card h2{margin:.1rem 0;color:#10243e;font-size:1.05rem}
    body.public-theme main.consult-page .doctor-card h2 a{color:#10243e!important;text-decoration:none}
    body.public-theme main.consult-page .doctor-card h2 a:hover{text-decoration:underline;color:#0755c8!important}
    body.public-theme main.consult-page .doctor-specialty{margin:.15rem 0;color:#52657e;font-size:.86rem}
    body.public-theme main.consult-page .doctor-meta{display:flex;flex-wrap:wrap;gap:.4rem;margin:.5rem 0;color:#334155;font-size:.76rem}
    body.public-theme main.consult-page .doctor-meta span{padding:.25rem .45rem;border-radius:6px;background:#f1f5f9}
    body.public-theme main.consult-page .doctor-price{margin:.5rem 0;color:#10243e;font-size:1.05rem;font-weight:800}
    body.public-theme main.consult-page .doctor-actions{display:flex;justify-content:space-between;align-items:center;gap:.6rem}
    body.public-theme main.consult-page .consult-primary{display:inline-flex;justify-content:center;align-items:center;gap:.4rem;min-height:38px;padding:.55rem .8rem;border:0;border-radius:8px;background:#e6004c;color:#fff!important;font-weight:800;text-decoration:none}
    body.public-theme main.consult-page .doctor-empty{padding:1rem;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;color:#334155;text-align:center}
    body.public-theme main.consult-page .doctor-availability{display:inline-block;padding:.2rem .45rem;border-radius:999px;background:#dcfce7;color:#166534;font-size:.72rem;font-weight:700}
    body.public-theme main.consult-page .doctor-availability.unavailable{background:#fff1f2;color:#9f1239}
    body.public-theme main.consult-page .consult-pagination{margin-top:1rem}
    @media(max-width:900px){body.public-theme main.consult-page .specialty-list{grid-template-columns:repeat(6,minmax(0,1fr))}}
    @media(max-width:700px){body.public-theme main.consult-page .doctor-grid{grid-template-columns:1fr}body.public-theme main.consult-page .doctor-card{grid-template-columns:76px 1fr}body.public-theme main.consult-page .doctor-photo{width:76px;height:96px}}
    @media(max-width:480px){body.public-theme main.consult-page{margin:.75rem .5rem;padding:1rem .65rem 2rem}body.public-theme main.consult-page .specialty-list{grid-template-columns:repeat(3,minmax(0,1fr));gap:.55rem .25rem}body.public-theme main.consult-page .specialty-chip{min-height:128px;font-size:.76rem}body.public-theme main.consult-page .specialty-icon{width:48px;height:48px}}
</style>
@endsection

@section('content')
<main class="consult-page">
    <label class="specialty-search">
        <input id="specialty-search" type="search" placeholder="Cari dokter, spesialis atau gejala" autocomplete="off" aria-label="Cari dokter atau spesialisasi">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
    </label>
    <header class="consult-heading">
        <span class="consult-kicker">Konsultasi online · Langkah 2 dari 3</span>
        <h1>{{ $selectedSpecialty !== '' ? $selectedSpecialty : 'Cari Dokter atau Spesialisasi' }}</h1>
        <p class="consult-muted">{{ $selectedSpecialty !== '' ? 'Pilih dokter dari kategori ini. Dokter yang sedang offline atau melayani pasien tetap ditampilkan, tetapi belum dapat menerima sesi baru.' : 'Pilih kategori yang sesuai dengan kebutuhan konsultasi Anda.' }}</p>
    </header>
    @if($selectedSpecialty !== '')
        <div class="specialty-selection">
            <a class="specialty-back" href="{{ route('consultations.doctors') }}"><i class="fa-solid fa-arrow-left"></i> Semua kategori</a>
        </div>
    @elseif($specialties->isNotEmpty())
        <nav class="specialty-list" aria-label="Kategori spesialisasi dokter">
            @foreach($specialties as $category)
                @php
                    $specialty = $category->specialty;
                    $specialtySearchTerms = match (true) {
                        str_contains(mb_strtolower($specialty), 'umum') => 'demam batuk flu sakit kepala pusing',
                        str_contains(mb_strtolower($specialty), 'anak') => 'bayi balita tumbuh kembang',
                        str_contains(mb_strtolower($specialty), 'kulit') => 'jerawat ruam alergi gatal',
                        str_contains(mb_strtolower($specialty), 'dalam') => 'diabetes hipertensi kolesterol lambung',
                        str_contains(mb_strtolower($specialty), 'tht') => 'telinga hidung tenggorokan sinus',
                        str_contains(mb_strtolower($specialty), 'kandungan') => 'hamil keputihan menstruasi kontrasepsi',
                        str_contains(mb_strtolower($specialty), 'paru') => 'sesak napas batuk',
                        str_contains(mb_strtolower($specialty), 'psiki'), str_contains(mb_strtolower($specialty), 'kecemasan') => 'mental depresi cemas insomnia',
                        str_contains(mb_strtolower($specialty), 'mata') => 'penglihatan mata',
                        str_contains(mb_strtolower($specialty), 'ortop') => 'tulang sendi',
                        str_contains(mb_strtolower($specialty), 'gigi') => 'gusi sakit gigi',
                        str_contains(mb_strtolower($specialty), 'saraf') => 'stroke migrain sakit kepala',
                        str_contains(mb_strtolower($specialty), 'jantung') => 'nyeri dada jantung',
                        default => '',
                    };
                    $specialtyIcon = match (true) {
                        str_contains(mb_strtolower($specialty), 'umum') => 'fa-user-doctor',
                        str_contains(mb_strtolower($specialty), 'anak') => 'fa-baby',
                        str_contains(mb_strtolower($specialty), 'kulit'), str_contains(mb_strtolower($specialty), 'jerawat') => 'fa-hand',
                        str_contains(mb_strtolower($specialty), 'dalam') => 'fa-heart-pulse',
                        str_contains(mb_strtolower($specialty), 'telinga'), str_contains(mb_strtolower($specialty), 'tht') => 'fa-ear-listen',
                        str_contains(mb_strtolower($specialty), 'kandung'), str_contains(mb_strtolower($specialty), 'reproduksi') => 'fa-person-pregnant',
                        str_contains(mb_strtolower($specialty), 'paru') => 'fa-lungs',
                        str_contains(mb_strtolower($specialty), 'psiki'), str_contains(mb_strtolower($specialty), 'psikolog') => 'fa-brain',
                        str_contains(mb_strtolower($specialty), 'mata') => 'fa-eye',
                        str_contains(mb_strtolower($specialty), 'ortop') => 'fa-bone',
                        str_contains(mb_strtolower($specialty), 'gigi') => 'fa-tooth',
                        str_contains(mb_strtolower($specialty), 'saraf') => 'fa-brain',
                        str_contains(mb_strtolower($specialty), 'bedah') => 'fa-user-doctor',
                        str_contains(mb_strtolower($specialty), 'jantung') => 'fa-heart-pulse',
                        str_contains(mb_strtolower($specialty), 'gizi') => 'fa-apple-whole',
                        str_contains(mb_strtolower($specialty), 'hewan') => 'fa-paw',
                        str_contains(mb_strtolower($specialty), 'laktasi'), str_contains(mb_strtolower($specialty), 'bidanku'), str_contains(mb_strtolower($specialty), 'hamil') => 'fa-person-pregnant',
                        str_contains(mb_strtolower($specialty), 'rambut'), str_contains(mb_strtolower($specialty), 'jerawat'), str_contains(mb_strtolower($specialty), 'ruam'), str_contains(mb_strtolower($specialty), 'kulit') => 'fa-hand',
                        str_contains(mb_strtolower($specialty), 'lab') => 'fa-vial',
                        str_contains(mb_strtolower($specialty), 'terapi'), str_contains(mb_strtolower($specialty), 'rehabilitasi') => 'fa-person-walking',
                        default => 'fa-user-doctor',
                    };
                @endphp
                <a class="specialty-chip" data-search="{{ mb_strtolower($specialty . ' ' . $specialtySearchTerms) }}" href="{{ route('consultations.doctors', ['specialty' => $specialty]) }}">
                    <span class="specialty-icon"><i class="fa-solid {{ $specialtyIcon }}" aria-hidden="true"></i></span>
                    <span>{{ $specialty }}</span>
                    @if($category->doctors_count > 0)
                        <span class="specialty-count">{{ $category->doctors_count }} dokter terdaftar</span>
                    @else
                        <span class="specialty-count empty">Dokter Belum Tersedia dan akan segera Tersedia</span>
                    @endif
                </a>
            @endforeach
        </nav>
        <p id="specialty-search-empty" class="consult-muted" hidden>Tidak ada kategori atau dokter yang cocok dengan pencarian.</p>
    @else
        <div class="doctor-empty">Dokter Belum Tersedia dan akan segera Tersedia.</div>
    @endif
    @if($selectedSpecialty !== '')
        @if($doctors->isEmpty())
            <div class="doctor-empty">Dokter Belum Tersedia dan akan segera Tersedia untuk kategori {{ $selectedSpecialty }}.</div>
        @else
            <section class="doctor-grid" aria-label="Daftar dokter {{ $selectedSpecialty }}">
                @foreach($doctors as $doctor)
                    @php
                        $doctorAvailable = $doctor->is_online && (int) $doctor->open_consultations_count === 0;
                    @endphp
                    <article class="doctor-card" data-search="{{ mb_strtolower($doctor->display_name . ' ' . $doctor->specialty) }}">
                        @if($doctor->photo_url)<img class="doctor-photo" src="{{ $doctor->photo_url }}" alt="Foto {{ $doctor->display_name }}">@else<div class="doctor-photo" aria-hidden="true"></div>@endif
                        <div>
                            <h2><a href="{{ route('consultations.doctor', $doctor) }}">{{ $doctor->display_name }}</a></h2>
                            <p class="doctor-specialty">{{ $doctor->specialty }}</p>
                            <div class="doctor-meta">
                                <span><i class="fa-solid fa-circle-check" style="color:#15803d"></i> STR diverifikasi</span>
                                <span><i class="fa-solid fa-briefcase"></i> {{ $doctor->experience_years }} tahun pengalaman</span>
                                @if($doctor->practice_city)<span><i class="fa-solid fa-location-dot"></i> {{ $doctor->practice_city }}</span>@endif
                                <span class="doctor-availability {{ $doctorAvailable ? '' : 'unavailable' }}">{{ $doctorAvailable ? 'Tersedia' : 'Belum tersedia' }}</span>
                            </div>
                            <div class="doctor-actions">
                                <span class="doctor-price">Rp {{ number_format($doctor->consultation_price, 0, ',', '.') }} <small style="font-size:.72rem;font-weight:500">/ {{ $doctor->session_duration_minutes }} menit</small></span>
                                @if($doctorAvailable)
                                    <a class="consult-primary" href="{{ route('consultations.details', $doctor) }}"><i class="fa-solid fa-comments"></i> Chat</a>
                                @else
                                    <button class="consult-primary" type="button" disabled style="background:#94a3b8;cursor:not-allowed">Chat belum tersedia</button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
            <div class="consult-pagination">{{ $doctors->links() }}</div>
        @endif
    @endif
</main>
<script>
    document.getElementById('specialty-search')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase('id');
        const categories = [...document.querySelectorAll('.specialty-chip')];
        const doctors = [...document.querySelectorAll('.doctor-card')];
        categories.forEach((category) => {
            category.hidden = !category.dataset.search.includes(query);
        });
        const hasCategoryMatch = categories.some((category) => !category.hidden);
        doctors.forEach((doctor) => {
            doctor.hidden = query !== '' && !hasCategoryMatch && !doctor.dataset.search.includes(query);
        });
        const hasDoctorMatch = doctors.some((doctor) => !doctor.hidden);
        const emptyMessage = document.getElementById('specialty-search-empty');
        if (emptyMessage) emptyMessage.hidden = query === '' || hasCategoryMatch || hasDoctorMatch;
    });
</script>
@endsection
