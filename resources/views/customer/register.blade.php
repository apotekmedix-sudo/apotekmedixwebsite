@extends('layouts.frontend')

@section('title', 'Daftar Akun - Apotek Medix')

@section('styles')
<style>
    .customer-auth-wrap { width:min(100% - 2rem, 460px); margin:2rem auto; }
    .customer-card { padding:2rem; border:1px solid #e2e8f0; border-radius:1rem; background:#fff; box-shadow:0 12px 32px rgba(15,23,42,.08); }
    .customer-card h1 { margin:0 0 .5rem; color:#0f172a; font-size:1.6rem; }
    .customer-card > p { margin:0 0 1.5rem; color:#64748b; }
    .customer-field { margin-bottom:1rem; }
    .customer-field label { display:block; margin-bottom:.4rem; color:#334155; font-weight:700; }
    .customer-field input { width:100%; padding:.75rem .85rem; border:1px solid #cbd5e1; border-radius:.55rem; font:inherit; }
    .customer-field input:focus { outline:2px solid #06b6d4; border-color:#0891b2; }
    .customer-submit { width:100%; padding:.8rem; border:0; border-radius:.55rem; background:#0f172a; color:white; font:inherit; font-weight:700; cursor:pointer; }
    .customer-error { margin:.35rem 0 0; color:#b91c1c; font-size:.85rem; }
    .customer-auth-footer { margin-top:1rem!important; text-align:center; }
    .customer-auth-footer a { color:#0e7490; font-weight:700; }
</style>
@endsection

@section('content')
<div class="customer-auth-wrap">
    <section class="customer-card">
        <h1>Buat akun pelanggan</h1>
        <p>Daftar untuk menyimpan alamat dan mempermudah checkout berikutnya.</p>

        @if($errors->any())
            <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('customer.register.post') }}" method="POST">
            @csrf
            <div class="customer-field">
                <label for="customer-name">Nama</label>
                <input id="customer-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
                @error('name')<p class="customer-error">{{ $message }}</p>@enderror
            </div>
            <div class="customer-field">
                <label for="customer-email">Email</label>
                <input id="customer-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email')<p class="customer-error">{{ $message }}</p>@enderror
            </div>
            <div class="customer-field">
                <label for="customer-password">Kata sandi (minimal 8 karakter)</label>
                <input id="customer-password" type="password" name="password" autocomplete="new-password" required>
                @error('password')<p class="customer-error">{{ $message }}</p>@enderror
            </div>
            <div class="customer-field">
                <label for="customer-password-confirmation">Ulangi kata sandi</label>
                <input id="customer-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <button class="customer-submit" type="submit">Daftar</button>
        </form>
        <p class="customer-auth-footer">Sudah punya akun? <a href="{{ route('customer.login') }}">Masuk</a></p>
    </section>
</div>
@endsection
