<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Room Konsultasi - Apotek Medix')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#172b4d;font-family:Arial,sans-serif}
        .room-topbar{padding:.9rem 1rem;background:#0f766e;color:#fff;font-weight:800}
        .room-main{max-width:980px;margin:0 auto;padding:1.25rem 1rem 3rem}
    </style>
    @yield('styles')
</head>
<body>
<header class="room-topbar">Apotek Medix · Room Konsultasi</header>
<main class="room-main">@yield('content')</main>
@yield('scripts')
</body>
</html>
