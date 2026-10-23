@extends('layouts.frontend')

@section('title', 'Masuk Akun - Apotek Medix')

@section('styles')
<style>
    .customer-auth-page { min-height:calc(100vh - var(--navbar-height, 66px));display:grid;place-items:center;padding:1.5rem 1rem;background:linear-gradient(180deg,#f8fafc,#fff); }
    .customer-auth-card { width:min(100%,440px);padding:clamp(1.5rem,5vw,2.25rem);border:1px solid #e2e8f0;border-radius:1.25rem;background:#fff;box-shadow:0 18px 50px rgba(15,23,42,.09); }
    .customer-auth-brand { display:flex;align-items:center;justify-content:center;width:56px;height:56px;margin:0 auto 1rem;border-radius:17px;background:#eff6ff;color:#0052cc;font-size:1.45rem; }
    .customer-auth-card h1 { margin:0;text-align:center;color:#1e293b;font-size:clamp(1.55rem,5vw,1.9rem);font-weight:800;letter-spacing:-.03em; }
    .customer-auth-intro { margin:.55rem 0 1.5rem;color:#64748b;text-align:center;line-height:1.6; }
    .customer-auth-notice { margin:0 0 1rem;padding:.8rem .9rem;border:1px solid #a5f3fc;border-radius:.75rem;background:#ecfeff;color:#155e75;font-size:.9rem;line-height:1.5; }
    .customer-auth-error { margin:0 0 1rem;padding:.8rem .9rem;border:1px solid #fecaca;border-radius:.75rem;background:#fef2f2;color:#b91c1c;font-size:.9rem;line-height:1.5; }
    .customer-auth-label { display:block;margin-bottom:.45rem;color:#334155;font-size:.9rem;font-weight:700; }
    .customer-email-control { display:flex;align-items:center;gap:.7rem;padding:0 .9rem;border:1px solid #cbd5e1;border-radius:.75rem;background:#fff;transition:border-color .2s,box-shadow .2s; }
    .customer-email-control:focus-within { border-color:#0052cc;box-shadow:0 0 0 4px rgba(0,82,204,.12); }
    .customer-email-control i { color:#64748b; }
    .customer-email-input { width:100%;min-width:0;padding:.9rem 0;border:0;outline:0;background:transparent;color:#1e293b;font:inherit; }
    .customer-email-input::placeholder { color:#94a3b8; }
    .customer-email-error { margin:.4rem 0 0;color:#b91c1c;font-size:.84rem; }
    .customer-otp-submit { display:flex;width:100%;min-height:50px;align-items:center;justify-content:center;gap:.55rem;margin-top:1.15rem;padding:.8rem 1rem;border:0;border-radius:.75rem;background:#0052cc;color:#fff;font:inherit;font-weight:800;cursor:pointer;transition:background .2s,transform .2s; }
    .customer-otp-submit:hover { transform:translateY(-1px);background:#003eaa; }
    .customer-otp-submit:focus-visible,.customer-auth-footer a:focus-visible { outline:3px solid #00d1b2;outline-offset:3px; }
    .customer-auth-trust { display:flex;align-items:center;justify-content:center;gap:.45rem;margin:1.1rem 0 0;color:#64748b;font-size:.82rem;text-align:center; }
    .customer-auth-trust i { color:#10b981; }
    .customer-auth-footer { margin:1.3rem 0 0;color:#64748b;font-size:.9rem;text-align:center; }
    .customer-auth-footer a { color:#0052cc;font-weight:700;text-decoration:none; }
    .customer-auth-footer a:hover { text-decoration:underline; }
</style>
@endsection

@section('content')
<div class="customer-auth-page">
    <section class="customer-auth-card" aria-labelledby="customer-login-title">
        <div class="customer-auth-brand" aria-hidden="true"><i class="fa-solid fa-heart-pulse"></i></div>
        <h1 id="customer-login-title">Masuk ke Apotek Medix</h1>
        <p class="customer-auth-intro">Masukkan email Anda. Kami akan mengirimkan kode OTP untuk masuk dengan aman.</p>

        @if(session('status'))
            <p class="customer-auth-notice" role="status">{{ session('status') }}</p>
        @endif
        @if($errors->has('email'))
            <p class="customer-auth-error" role="alert">{{ $errors->first('email') }}</p>
        @elseif($errors->any())
            <p class="customer-auth-error" role="alert">{{ $errors->first() }}</p>
        @endif

        <form action="{{ route('customer.login.post') }}" method="POST">
            @csrf
            <label class="customer-auth-label" for="customer-email">Email</label>
            <div class="customer-email-control">
                <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                <input
                    class="customer-email-input"
                    id="customer-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@gmail.com"
                    autocomplete="email"
                    inputmode="email"
                    required
                    autofocus
                >
            </div>
            <button class="customer-otp-submit" type="submit">
                <i class="fa-regular fa-paper-plane" aria-hidden="true"></i>
                Kirim Kode OTP
            </button>
        </form>

        <p class="customer-auth-trust"><i class="fa-solid fa-lock" aria-hidden="true"></i> Masuk aman melalui verifikasi email</p>
        <p class="customer-auth-footer">Belum punya akun? <a href="{{ route('customer.register') }}">Daftar</a></p>
    </section>
</div>
@endsection
