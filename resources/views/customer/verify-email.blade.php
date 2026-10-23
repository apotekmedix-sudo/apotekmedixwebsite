@extends('layouts.frontend')

@section('title', 'Verifikasi OTP - Apotek Medix')

@section('styles')
<style>
    .customer-auth-page { min-height:calc(100vh - var(--navbar-height, 66px));display:grid;place-items:center;padding:1.5rem 1rem;background:linear-gradient(180deg,#f8fafc,#fff); }
    .customer-auth-card { width:min(100%,440px);padding:clamp(1.5rem,5vw,2.25rem);border:1px solid #e2e8f0;border-radius:1.25rem;background:#fff;box-shadow:0 18px 50px rgba(15,23,42,.09); }
    .customer-auth-brand { display:flex;align-items:center;justify-content:center;width:56px;height:56px;margin:0 auto 1rem;border-radius:17px;background:#eff6ff;color:#0052cc;font-size:1.45rem; }
    .customer-auth-card h1 { margin:0;color:#1e293b;text-align:center;font-size:clamp(1.55rem,5vw,1.9rem);font-weight:800;letter-spacing:-.03em; }
    .customer-auth-intro { margin:.55rem 0 1.4rem;color:#64748b;text-align:center;line-height:1.6; }
    .customer-auth-intro strong { color:#1e293b;overflow-wrap:anywhere; }
    .customer-auth-notice { margin:0 0 1rem;padding:.8rem .9rem;border:1px solid #a5f3fc;border-radius:.75rem;background:#ecfeff;color:#155e75;font-size:.9rem;line-height:1.5; }
    .customer-auth-error { margin:0 0 1rem;padding:.8rem .9rem;border:1px solid #fecaca;border-radius:.75rem;background:#fef2f2;color:#b91c1c;font-size:.9rem;line-height:1.5; }
    .customer-otp-label { display:block;margin-bottom:.6rem;color:#334155;font-size:.9rem;font-weight:700; }
    .customer-otp-digits { display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:clamp(.4rem,2vw,.65rem); }
    .customer-otp-digit { width:100%;height:clamp(48px,13vw,58px);border:1px solid #cbd5e1;border-radius:.7rem;background:#fff;color:#1e293b;font-size:1.4rem;font-weight:800;text-align:center;transition:border-color .2s,box-shadow .2s; }
    .customer-otp-digit:focus { border-color:#0052cc;outline:0;box-shadow:0 0 0 4px rgba(0,82,204,.12); }
    .customer-otp-hint { margin:.55rem 0 0;color:#64748b;font-size:.8rem;text-align:center; }
    .customer-otp-submit { display:flex;width:100%;min-height:50px;align-items:center;justify-content:center;gap:.55rem;margin-top:1.2rem;padding:.8rem 1rem;border:0;border-radius:.75rem;background:#0052cc;color:#fff;font:inherit;font-weight:800;cursor:pointer;transition:background .2s,transform .2s; }
    .customer-otp-submit:hover { transform:translateY(-1px);background:#003eaa; }
    .customer-otp-submit:focus-visible,.customer-resend-button:focus-visible,.customer-change-email:focus-visible { outline:3px solid #00d1b2;outline-offset:3px; }
    .customer-resend { margin:1.1rem 0 0;color:#64748b;font-size:.88rem;text-align:center; }
    .customer-resend-button,.customer-change-email { padding:0;border:0;background:transparent;color:#0052cc;font:inherit;font-weight:700;text-decoration:none;cursor:pointer; }
    .customer-resend-button:not(:disabled):hover,.customer-change-email:hover { text-decoration:underline; }
    .customer-resend-button:disabled { color:#94a3b8;cursor:not-allowed; }
    .customer-auth-trust { display:flex;align-items:center;justify-content:center;gap:.45rem;margin:1.1rem 0 0;color:#64748b;font-size:.82rem;text-align:center; }
    .customer-auth-trust i { color:#10b981; }
    @media(max-width:360px) { .customer-otp-digits { gap:.3rem; }.customer-otp-digit { border-radius:.55rem;font-size:1.2rem; } }
</style>
@endsection

@section('content')
<div class="customer-auth-page">
    <section class="customer-auth-card" aria-labelledby="customer-otp-title">
        <div class="customer-auth-brand" aria-hidden="true"><i class="fa-solid fa-envelope-circle-check"></i></div>
        <h1 id="customer-otp-title">Verifikasi kode OTP</h1>
        <p class="customer-auth-intro">Masukkan 6 digit kode yang dikirim ke <strong>{{ $email }}</strong>.</p>

        @if(session('status'))
            <p class="customer-auth-notice" role="status">{{ session('status') }}</p>
        @endif
        @if($errors->any())
            <p class="customer-auth-error" role="alert">{{ $errors->first() }}</p>
        @endif

        <form action="{{ route('customer.email-verification.verify') }}" method="POST" id="customer-otp-form">
            @csrf
            <label class="customer-otp-label" for="otp-digit-1">Kode OTP</label>
            <div class="customer-otp-digits" role="group" aria-label="Enam digit kode OTP">
                @for($digit = 1; $digit <= 6; $digit++)
                    <input
                        class="customer-otp-digit"
                        id="otp-digit-{{ $digit }}"
                        name="code_digits[]"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="1"
                        autocomplete="{{ $digit === 1 ? 'one-time-code' : 'off' }}"
                        aria-label="Digit {{ $digit }}"
                        required
                    >
                @endfor
            </div>
            <input type="hidden" name="code" id="customer-otp-code">
            <p class="customer-otp-hint">Kode berlaku selama 10 menit.</p>
            <button class="customer-otp-submit" type="submit">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                Verifikasi &amp; Masuk
            </button>
        </form>

        <form action="{{ route('customer.email-verification.resend') }}" method="POST" class="customer-resend" id="customer-resend-form">
            @csrf
            <span id="customer-resend-label">Belum menerima kode?</span>
            <button class="customer-resend-button" id="customer-resend-button" type="submit" disabled>
                Kirim ulang kode dalam <span id="customer-resend-countdown">01:00</span>
            </button>
        </form>
        <p class="customer-resend"><a class="customer-change-email" href="{{ route('customer.login') }}">Ganti email</a></p>
        <p class="customer-auth-trust"><i class="fa-solid fa-lock" aria-hidden="true"></i> Jangan bagikan kode OTP kepada siapa pun</p>
    </section>
</div>
@endsection

@section('scripts')
<script>
(() => {
    const form = document.getElementById('customer-otp-form');
    if (!form) return;

    const inputs = Array.from(form.querySelectorAll('.customer-otp-digit'));
    const hiddenCode = document.getElementById('customer-otp-code');

    function updateCode() {
        hiddenCode.value = inputs.map((input) => input.value).join('');
    }

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            const digits = input.value.replace(/\D/g, '');
            if (digits.length > 1) {
                digits.slice(0, inputs.length - index).split('').forEach((digit, offset) => {
                    inputs[index + offset].value = digit;
                });
                inputs[Math.min(index + digits.length, inputs.length) - 1]?.focus();
            } else {
                input.value = digits.slice(-1);
                if (input.value && inputs[index + 1]) inputs[index + 1].focus();
            }
            updateCode();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !input.value && inputs[index - 1]) {
                inputs[index - 1].focus();
            }
            if (event.key === 'ArrowLeft' && inputs[index - 1]) inputs[index - 1].focus();
            if (event.key === 'ArrowRight' && inputs[index + 1]) inputs[index + 1].focus();
        });

        input.addEventListener('paste', (event) => {
            const pasted = event.clipboardData?.getData('text').replace(/\D/g, '').slice(0, inputs.length);
            if (!pasted) return;
            event.preventDefault();
            pasted.split('').forEach((digit, digitIndex) => {
                if (inputs[digitIndex]) inputs[digitIndex].value = digit;
            });
            updateCode();
            inputs[Math.min(pasted.length, inputs.length) - 1]?.focus();
        });
    });

    form.addEventListener('submit', (event) => {
        updateCode();
        if (!/^\d{6}$/.test(hiddenCode.value)) {
            event.preventDefault();
            inputs.find((input) => !input.value)?.focus();
        }
    });

    const resendButton = document.getElementById('customer-resend-button');
    const countdown = document.getElementById('customer-resend-countdown');
    const resendAvailableAt = {{ $resendAvailableAt }};

    function updateResendCountdown() {
        const secondsLeft = Math.max(0, resendAvailableAt - Math.floor(Date.now() / 1000));
        resendButton.disabled = secondsLeft > 0;
        if (secondsLeft === 0) {
            resendButton.textContent = 'Kirim ulang kode';
            return;
        }
        countdown.textContent = String(Math.floor(secondsLeft / 60)).padStart(2, '0') + ':' + String(secondsLeft % 60).padStart(2, '0');
    }

    updateResendCountdown();
    window.setInterval(updateResendCountdown, 1000);
    inputs[0]?.focus();
})();
</script>
@endsection
