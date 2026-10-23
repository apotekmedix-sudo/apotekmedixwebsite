<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Apotek Medix - Apotik Online Terpercaya')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- FIX CURSOR - MUST BE FIRST TO OVERRIDE EVERYTHING -->
    <style>
        /* FORCE RESET CURSOR - HIGHEST PRIORITY */
        html, html *, html *::before, html *::after,
        body, body *, body *::before, body *::after {
            cursor: auto !important;
        }
        a, a *, button, button *, [role="button"], [onclick], 
        input[type="button"], input[type="submit"], input[type="reset"], select {
            cursor: pointer !important;
        }
        input[type="text"], input[type="email"], input[type="password"],
        input[type="search"], input[type="tel"], input[type="url"],
        input[type="number"], textarea {
            cursor: text !important;
        }
        [disabled], .disabled {
            cursor: not-allowed !important;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/fix-cursor.css') }}">
    
    <!-- Fix Cursor Script - Load ASAP -->
    <script>
        // Immediate cursor fix - runs before page loads
        (function() {
            'use strict';
            function fix() {
                document.documentElement.style.setProperty('cursor', 'auto', 'important');
                if (document.body) {
                    document.body.style.setProperty('cursor', 'auto', 'important');
                }
            }
            fix();
            setInterval(fix, 100);
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fix);
            }
        })();
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - fallback -->
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        :root {
            --primary-color: #10b981;
            --secondary-color: #059669;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
        }
        
        /* Navbar */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            background-color: white !important;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
            color: var(--primary-color) !important;
        }
        
        .navbar-brand i {
            margin-right: 8px;
        }
        
        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: white;
            color: var(--primary-color);
            border: none;
            padding: 12px 32px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        
        .btn-primary:hover {
            background-color: #f3f4f6;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .btn-outline-primary {
            border: 2px solid white;
            color: white;
            background-color: transparent;
            padding: 10px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background-color: white;
            color: var(--primary-color);
        }
        
        /* Cards */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .card-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        /* Section Titles */
        .section-title {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .section-subtitle {
            color: var(--text-light);
            text-align: center;
            margin-bottom: 50px;
            font-size: 16px;
        }
        
        /* Feature Section */
        .feature-section {
            padding: 80px 0;
        }
        
        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 40px;
        }
        
        .feature-icon {
            font-size: 32px;
            color: var(--primary-color);
            margin-right: 20px;
            min-width: 40px;
        }
        
        .feature-content h3 {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .feature-content p {
            color: var(--text-light);
            line-height: 1.6;
        }
        
        /* Footer */
        footer {
            background-color: var(--text-dark);
            color: white;
            padding: 60px 0 20px 0;
            margin-top: 80px;
        }
        
        .footer-section h5 {
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .footer-link {
            color: #d1d5db;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .footer-link:hover {
            color: var(--primary-color);
            margin-left: 5px;
        }
        
        .footer-bottom {
            border-top: 1px solid #374151;
            margin-top: 30px;
            padding-top: 30px;
            text-align: center;
            color: #9ca3af;
        }
        
        /* Badge */
        .badge-custom {
            background-color: #d1fae5;
            color: var(--secondary-color);
            padding: 8px 16px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        /* Contact Form */
        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }
            
            .hero p {
                font-size: 16px;
            }
            
            .section-title {
                font-size: 28px;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-pills"></i>Apotek Medix
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('about')) active @endif" href="{{ route('about') }}">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('products')) active @endif" href="{{ route('products.index') }}">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('services')) active @endif" href="{{ route('services') }}">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('contact')) active @endif" href="{{ route('contact') }}">Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-3 footer-section mb-4">
                    <h5><i class="fas fa-pills"></i> Apotek Medix</h5>
                    <p>Apotik online terpercaya menyediakan obat dan suplemen berkualitas dengan harga terjangkau.</p>
                    <div class="mt-3">
                        <a href="#" class="footer-link"><i class="fab fa-facebook"></i> Facebook</a>
                        <a href="#" class="footer-link"><i class="fab fa-instagram"></i> Instagram</a>
                        <a href="#" class="footer-link"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>
                <div class="col-md-3 footer-section">
                    <h5>Navigasi</h5>
                    <a href="{{ route('home') }}" class="footer-link">Beranda</a>
                    <a href="{{ route('about') }}" class="footer-link">Tentang Kami</a>
                    <a href="{{ route('products.index') }}" class="footer-link">Produk</a>
                    <a href="{{ route('services') }}" class="footer-link">Layanan</a>
                </div>
                <div class="col-md-3 footer-section">
                    <h5>Layanan</h5>
                    <a href="#" class="footer-link">Konsultasi Apoteker</a>
                    <a href="#" class="footer-link">Pengiriman Gratis</a>
                    <a href="#" class="footer-link">Program Loyalitas</a>
                    <a href="#" class="footer-link">Resep Online</a>
                </div>
                <div class="col-md-3 footer-section">
                    <h5>Hubungi Kami</h5>
                    <p class="footer-link">
                        <i class="fas fa-phone"></i> (021) 1234-5678
                    </p>
                    <p class="footer-link">
                        <i class="fas fa-envelope"></i> apotekmedix.mitramedika@gmail.com
                    </p>
                    <p class="footer-link">
                        <i class="fas fa-map-marker-alt"></i> Jakarta, Indonesia
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Apotek Medix. All rights reserved. | Privacy Policy | Terms & Conditions</p>
            </div>
        </div>
    </footer>

    <!-- Fix Cursor Script -->
    <script src="{{ asset('js/fix-cursor-override.js') }}"></script>
    <script src="{{ asset('js/fix-cursor.js') }}"></script>
    
    @yield('scripts')
</body>
</html>
