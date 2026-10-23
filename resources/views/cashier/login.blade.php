<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Kasir - Apotek Medix</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:1rem;background:#f8fafc;color:#1e293b;font-family:Arial,sans-serif}
        main{width:min(100%,420px);padding:2rem;border:1px solid #e2e8f0;border-radius:1rem;background:#fff;box-shadow:0 18px 48px #0f172a18}
        h1{margin:0 0 .5rem;color:#0f172a}p{color:#64748b}label{display:block;margin:1rem 0 .4rem;font-weight:700}
        input{width:100%;padding:.75rem;border:1px solid #cbd5e1;border-radius:.5rem;font:inherit}
        button{width:100%;margin-top:1.25rem;padding:.8rem;border:0;border-radius:.5rem;background:#0f172a;color:white;font:inherit;font-weight:700;cursor:pointer}
        .error{color:#b91c1c}.back{display:block;margin-top:1rem;text-align:center;color:#0e7490}
    </style>
</head>
<body>
<main>
    <h1>Masuk Kasir</h1>
    <p>Masuk untuk menerima pembayaran pesanan dari kios toko.</p>
    @if(session('error'))<p class="error">{{ session('error') }}</p>@endif
    @if($errors->any())<p class="error">{{ $errors->first() }}</p>@endif
    <form action="{{ route('cashier.login.post') }}" method="POST">
        @csrf
        <label for="email">Email kasir</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
        <label for="password">Kata sandi</label>
        <input id="password" type="password" name="password" autocomplete="current-password" required>
        <button type="submit">Masuk</button>
    </form>
    <a class="back" href="{{ route('home') }}">Kembali ke website</a>
</main>
</body>
</html>
