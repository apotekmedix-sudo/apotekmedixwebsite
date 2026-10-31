@extends('layouts.frontend')

@section('title', 'Akses Konsultasi - Apotek Medix')

@section('styles')
<style>
    .access-page{max-width:760px;margin:1.5rem auto;padding:0 1rem 3rem;color:#172b4d}
    .access-card{padding:clamp(1rem,3vw,1.5rem);border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 12px 32px #0f172a0d}
    .access-doctor{display:flex;align-items:center;gap:1rem;margin-bottom:1rem;padding:1rem;border:1px solid #dbe4f0;border-radius:12px;background:#f8fafc}
    .access-doctor img{width:72px;height:82px;object-fit:cover;border:1px solid #dbe4f0;border-radius:9px}
    .access-doctor h1{margin:0;color:#10243e;font-size:1.25rem}
    .access-muted{color:#52657e;line-height:1.55}
    .access-notice{margin:1rem 0;padding:.85rem 1rem;border:1px solid #fed7aa;border-radius:9px;background:#fff7ed;color:#9a3412;line-height:1.55}
    .access-code{width:100%;max-width:240px;min-height:48px;padding:.65rem .8rem;border:1px solid #b8c5d6;border-radius:9px;font:inherit;font-size:1.25rem;letter-spacing:.25em;text-align:center}
    .access-actions{display:flex;flex-wrap:wrap;gap:.65rem;margin-top:.85rem}
    .access-button{min-height:44px;padding:.65rem .9rem;border:0;border-radius:8px;background:#08734f;color:#fff;font:inherit;font-weight:800;cursor:pointer}
    body.public-theme main.access-page .access-card a.access-confirm,
    body.public-theme main.access-page .access-card a.access-confirm:visited{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;width:fit-content;max-width:100%;min-height:46px;margin:.5rem 0 1rem;padding:.7rem 1rem;border:1px solid #0755c8;border-radius:8px;background:#0755c8;color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:.95rem;font-weight:800;line-height:1.35;text-align:center;text-decoration:none;visibility:visible;opacity:1}
    body.public-theme main.access-page .access-card a.access-confirm *,
    body.public-theme main.access-page .access-card a.access-confirm:visited *{color:#fff!important;-webkit-text-fill-color:#fff!important}
    body.public-theme main.access-page .access-card a.access-confirm:hover{border-color:#063583;background:#063583;color:#fff!important;-webkit-text-fill-color:#fff!important}
    .access-button:disabled{background:#94a3b8;cursor:not-allowed}
    .access-error{color:#991b1b}
</style>
@endsection

@section('content')
<main class="access-page">
    <section class="access-card">
        <header class="access-doctor">
            @if($consultation->doctorProfile->photo_url)<img src="{{ $consultation->doctorProfile->photo_url }}" alt="Foto {{ $consultation->doctorProfile->display_name }}">@endif
            <div>
                <h1>Konsultasi dengan {{ $consultation->doctorProfile->display_name }}</h1>
                <p class="access-muted" style="margin:.25rem 0">{{ $consultation->doctorProfile->specialty }} · {{ $consultation->session_duration_minutes }} menit</p>
            </div>
        </header>

        @php
            $canEnterAccessCode = $consultation->payment_status === 'paid'
                && $consultation->status === 'awaiting_whatsapp'
                && $consultation->access_code_hash
                && $consultation->access_code_expires_at?->isFuture();
        @endphp
        @if($consultation->payment_status === 'pending')
            <h2>Menunggu konfirmasi WhatsApp</h2>
            <p class="access-muted">Kirim konfirmasi dan bukti pembayaran melalui WhatsApp. Setelah admin mengonfirmasi melalui chat, admin akan mengirim kode akses. Masukkan kode tersebut di halaman ini untuk mengaktifkan konsultasi.</p>
            @if($consultation->payment_due_at)<p class="access-muted">Batas verifikasi pembayaran: {{ $consultation->payment_due_at->format('d-m-Y H:i') }} WIB.</p>@endif
            <a class="access-confirm" href="{{ $adminWhatsappUrl }}" target="_blank" rel="noopener noreferrer">
                Konfirmasi Pembayaran
            </a>
        @elseif($consultation->payment_status === 'paid' && $consultation->status === 'awaiting_whatsapp')
            @if($canEnterAccessCode)
                <h2>Masukkan kode akses konsultasi</h2>
                <p class="access-muted">Kode 5 digit hanya dapat digunakan satu kali dan berlaku sampai {{ $consultation->access_code_expires_at->format('d-m-Y H:i') }} WIB.</p>
            @else
                <h2>Kode akses kedaluwarsa</h2>
                <p class="access-muted">Hubungi admin Apotek Medix untuk meminta kode akses baru.</p>
            @endif
        @elseif($consultation->payment_status === 'paid' && $consultation->access_code_used_at)
            <h2>Konsultasi sudah dimulai</h2>
            <p class="access-muted">Kode akses telah digunakan. Jika WhatsApp belum terbuka, hubungi admin Apotek Medix.</p>
        @elseif($consultation->payment_status === 'rejected')
            <h2>Pembayaran ditolak.</h2>
            <p class="access-muted">Jika saldo Anda sudah terpotong, pengembaliannya akan diproses manual oleh admin. Silakan hubungi admin Apotek Medix untuk bantuan.</p>
            <a href="{{ route('consultations.doctors') }}">Kembali ke daftar dokter</a>
            <a href="https://wa.me/{{ config('contacts.customer_service.whatsapp') }}?text={{ rawurlencode('Halo Admin Apotek Medix, saya perlu bantuan pembayaran konsultasi #' . $consultation->id . '.') }}" target="_blank" rel="noopener noreferrer">Hubungi admin</a>
        @else
            <h2>Konsultasi tidak tersedia</h2>
            <p class="access-muted">Status konsultasi ini sudah berakhir. Hubungi admin Apotek Medix jika Anda memerlukan bantuan.</p>
        @endif

        @if($consultation->payment_status === 'pending' || $canEnterAccessCode)
            @if($errors->has('access_code'))<p class="access-error" role="alert">{{ $errors->first('access_code') }}</p>@endif
            <form id="accessCodeForm" method="POST" action="{{ route('consultations.patient.whatsapp.start', ['token' => $token]) }}" data-verify-url="{{ route('consultations.patient.access-code.verify', ['token' => $token]) }}">
                @csrf
                <label for="accessCode">Kode akses konsultasi</label><br>
                <input class="access-code" id="accessCode" name="access_code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="0[0-9]{4}" maxlength="5" placeholder="0••••" required>
                <p class="access-muted">Masukkan kode 5 digit yang dikirim admin melalui WhatsApp. Kode yang valid akan mengonfirmasi pembayaran dan berlaku 24 jam.</p>
                <p id="accessCodeFeedback" class="access-muted" aria-live="polite"></p>
                <div class="access-actions">
                    <button class="access-button" id="verifyAccessCode" type="button" disabled>Verifikasi kode</button>
                    <button class="access-button" id="startWhatsApp" type="submit" disabled>Mulai Konsultasi</button>
                </div>
            </form>
        @endif
    </section>
</main>
@if($consultation->payment_status === 'pending' || $canEnterAccessCode)
<script>
(() => {
    const form = document.getElementById('accessCodeForm');
    const input = document.getElementById('accessCode');
    const verifyButton = document.getElementById('verifyAccessCode');
    const startButton = document.getElementById('startWhatsApp');
    const feedback = document.getElementById('accessCodeFeedback');
    const csrf = form.querySelector('input[name="_token"]').value;
    let verifiedCode = null;
    const isCodeFormatValid = () => /^0[0-9]{4}$/.test(input.value);

    input.addEventListener('input', () => {
        verifiedCode = null;
        input.readOnly = false;
        startButton.disabled = true;
        verifyButton.disabled = !isCodeFormatValid();
        feedback.textContent = '';
        feedback.classList.remove('access-error');
    });

    verifyButton.addEventListener('click', async () => {
        if (!isCodeFormatValid()) return;
        verifyButton.disabled = true;
        feedback.textContent = 'Memeriksa kode...';
        try {
            const response = await fetch(form.dataset.verifyUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ access_code: input.value }),
            });
            const result = await response.json();
            if (!response.ok || result.valid !== true) {
                throw new Error(result.message || 'Kode akses tidak valid.');
            }
            verifiedCode = input.value;
            input.readOnly = true;
            startButton.disabled = false;
            feedback.textContent = result.message || 'Kode valid. Pembayaran dikonfirmasi. Lanjutkan untuk membuka WhatsApp dokter.';
        } catch (error) {
            feedback.textContent = error.message || 'Kode tidak dapat diverifikasi. Coba lagi.';
            feedback.classList.add('access-error');
            verifyButton.disabled = !isCodeFormatValid();
        }
    });

    form.addEventListener('submit', event => {
        if (!verifiedCode || verifiedCode !== input.value) {
            event.preventDefault();
            startButton.disabled = true;
            verifyButton.disabled = !isCodeFormatValid();
            feedback.textContent = 'Verifikasi kode yang benar sebelum memulai konsultasi.';
            feedback.classList.add('access-error');
        }
    });
})();
</script>
@endif
@endsection
