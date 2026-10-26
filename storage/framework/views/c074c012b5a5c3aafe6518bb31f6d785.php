<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Apotek Medix'); ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    
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
    <link rel="stylesheet" href="<?php echo e(asset('css/fix-cursor.css')); ?>">
    
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <style>
        /* ===== BOOTSTRAP GRID REPLACEMENT ===== */
        *, *::before, *::after { box-sizing: border-box; }
        html, body { overflow-x: clip; max-width: 100%; }
        body { margin: 0; }
        img, svg, video { max-width: 100%; height: auto; display: block; }
        button, input, textarea, select { max-width: 100%; }
        .container { width: 100%; max-width: 100%; padding-left: 1rem; padding-right: 1rem; margin-left: auto; margin-right: auto; }
        @media (max-width: 480px) {
            .container { padding-left: 0.75rem; padding-right: 0.75rem; }
        }
        .row { display: flex; flex-wrap: wrap; margin-left: -0.75rem; margin-right: -0.75rem; }
        .row > * { padding-left: 0.75rem; padding-right: 0.75rem; width: 100%; }
        .col-lg-6 { flex: 0 0 auto; }
        .col-lg-5 { flex: 0 0 auto; }
        .col-lg-7 { flex: 0 0 auto; }
        @media (min-width: 992px) {
            .col-lg-6 { width: 50%; }
            .col-lg-5 { width: 41.6667%; }
            .col-lg-7 { width: 58.3333%; }
            .d-none { display: none !important; }
            .d-lg-block { display: block !important; }
            .align-items-center { align-items: center !important; }
        }
        @media (max-width: 991px) {
            .col-lg-6, .col-lg-5, .col-lg-7 { width: 100%; }
            .d-none { display: none !important; }
        }
        .g-5 { gap: 1.5rem; }
        .g-3 { gap: 1rem; }

        /* ===== GLOBAL SECTION DECORATIONS ===== */

        /* Header pages */
        .about-header, .contact-header, .act-header,
        .news-page-header, .products-header, .page-header {
            position: relative;
            overflow: hidden;
        }

        /* Floating deco icons di semua header */
        .header-deco-icon {
            position: absolute;
            color: rgba(255,255,255,0.08);
            pointer-events: none;
            animation: headerIconFloat 6s ease-in-out infinite;
        }
        .header-deco-icon-1 { bottom: 10px; right: 12%; font-size: 4rem;   animation-delay: 0s; }
        .header-deco-icon-2 { top: 15px;   right: 28%; font-size: 3rem;   animation-delay: 2s; }
        .header-deco-icon-3 { bottom: 20px; right: 40%; font-size: 2.5rem; animation-delay: 4s; }

        @keyframes headerIconFloat {
            0%, 100% { transform: translateY(0) rotate(0deg);   opacity: 0.08; }
            50%       { transform: translateY(-12px) rotate(8deg); opacity: 0.15; }
        }

        /* Stats bar */
        .stats-bar { position: relative; overflow: hidden; }
        .stats-bar::before {
            content: '';
            position: absolute;
            top: -40px; left: -40px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(30,136,229,0.07) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .stats-bar::after {
            content: '';
            position: absolute;
            bottom: -40px; right: -40px;
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(124,179,66,0.07) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        /* Products section */
        .products-section { position: relative; overflow: hidden; }
        .products-section::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(30,136,229,0.05) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .products-section::after {
            content: '';
            position: absolute;
            bottom: 40px; left: -80px;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(124,179,66,0.05) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        /* About section */
        .about-section { position: relative; overflow: hidden; }
        .about-section::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(30,136,229,0.04) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .about-section::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(124,179,66,0.05) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        /* About/contact/act main */
        .about-main, .contact-main, .act-main { position: relative; overflow: hidden; }
        .about-main::before, .contact-main::before, .act-main::before {
            content: '';
            position: absolute;
            top: 0; right: -100px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(30,136,229,0.03) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        /* Section card subtle deco */
        .section-card { position: relative; overflow: hidden; }
        .section-card::before {
            content: '';
            position: absolute;
            top: -30px; right: -30px;
            width: 120px; height: 120px;
            background: radial-gradient(circle, rgba(30,136,229,0.04) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        /* Footer */
        .footer { position: relative; overflow: hidden; }
        .footer::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(124,179,66,0.05) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .footer::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(30,136,229,0.04) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        .product-stock {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            width: fit-content;
            padding: 0.2rem 0.6rem;
            margin-bottom: 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .product-stock.available { background: #d1fae5; color: #065f46; }
        .product-stock.low { background: #fef3c7; color: #92400e; }
        .product-stock.empty { background: #fee2e2; color: #991b1b; }

        /* ===== CARD DECORATIONS ===== */
        .medicine-card, .news-card, .news-preview-card, .photo-card,
        .feature-item, .section-card, .vm-card, .value-item,
        .stat-box, .info-card, .form-card, .detail-container,
        .news-detail-content, .about-image-main, .float-stat {
            position: relative;
            overflow: hidden;
        }

        /* Blob kanan atas - biru */
        .medicine-card::before,
        .news-card::before,
        .news-preview-card::before,
        .feature-item::before,
        .value-item::before,
        .info-card::before,
        .form-card::before,
        .detail-container::before,
        .news-detail-content::before {
            content: '';
            position: absolute;
            top: -25px; right: -25px;
            width: 90px; height: 90px;
            background: radial-gradient(circle, rgba(30,136,229,0.07) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* Blob kiri bawah - hijau */
        .medicine-card::after,
        .news-card::after,
        .news-preview-card::after,
        .photo-card::after,
        .feature-item::after,
        .section-card::after,
        .vm-card::after,
        .stat-box::after,
        .info-card::after,
        .form-card::after,
        .detail-container::after,
        .news-detail-content::after,
        .about-image-main::after,
        .float-stat::after {
            content: '';
            position: absolute;
            bottom: -20px; right: -20px;
            width: 75px; height: 75px;
            background: radial-gradient(circle, rgba(124,179,66,0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* Section card blob lebih besar */
        .section-card::before {
            content: '';
            position: absolute;
            top: -30px; right: -30px;
            width: 120px; height: 120px;
            background: radial-gradient(circle, rgba(30,136,229,0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* ===== PARTIKEL STATIS - dikurangi opacity agar tidak ganggu konten ===== */
        .med-particles {
            position: fixed;
            top: 65px;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
            opacity: 0.45;
        }

        .mp {
            position: absolute;
            transform: rotate(var(--r, 0deg)) scale(var(--s, 1));
            opacity: var(--o, 0.25);
        }

        .mp-capsule {
            width: 60px; height: 26px;
            border-radius: 13px;
            background: linear-gradient(90deg, #1E88E5 50%, #7CB342 50%);
            box-shadow: 0 2px 8px rgba(30,136,229,0.25), inset 0 1px 3px rgba(255,255,255,0.4);
        }

        .mp-tablet {
            width: 42px; height: 26px;
            border-radius: 13px;
            background: #bbdefb;
            border: 2.5px solid #42a5f5;
            position: relative;
            box-shadow: 0 2px 8px rgba(66,165,245,0.2);
        }
        .mp-tablet::after {
            content: '';
            position: absolute;
            top: 50%; left: 18%; right: 18%;
            height: 2px;
            background: #42a5f5;
            transform: translateY(-50%);
            border-radius: 1px;
        }

        .mp-pill {
            width: 24px; height: 24px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #ffffff, #66bb6a);
            border: 2px solid rgba(102,187,106,0.6);
            box-shadow: 0 2px 8px rgba(124,179,66,0.25);
        }

        .mp-cross {
            width: 28px; height: 28px;
            position: relative;
            filter: drop-shadow(0 2px 4px rgba(30,136,229,0.3));
        }
        .mp-cross::before,
        .mp-cross::after {
            content: '';
            position: absolute;
            background: #1E88E5;
            border-radius: 3px;
        }
        .mp-cross::before {
            width: 10px; height: 28px;
            left: 50%; transform: translateX(-50%);
        }
        .mp-cross::after {
            width: 28px; height: 10px;
            top: 50%; transform: translateY(-50%);
        }

        .mp-icon {
            font-size: 1.6rem;
            color: #1565C0;
            transform: rotate(var(--r, 0deg)) scale(var(--s, 1));
            filter: drop-shadow(0 2px 4px rgba(21,101,192,0.2));
        }

        /* Variasi warna ikon */
        .mp-icon:nth-child(odd)  { color: #388e3c; }
        .mp-icon:nth-child(3n)   { color: #0288d1; }
        .mp-icon:nth-child(4n)   { color: #1976d2; }
    </style>
    
    <style>
        :root {
            --primary: #0F172A;
            --secondary: #1E293B;
            --accent: #84CC16;
            --cyan: #06B6D4;
            --dark: #1E293B;
            --light: #F8FAFC;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            margin: 0 !important;
            padding: 0 !important;
            font-size: 16px;
            line-height: 1.5;
            min-height: 100%;
        }

        body {
            margin: 0 !important;
            padding: calc(var(--navbar-height, 65px) - 1.5rem) 0 0 !important;
            font-size: 16px;
            line-height: 1.5;
            min-height: 100%;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: var(--navbar-height, 65px);
            background: #FFFFFF;
            background-attachment: fixed;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #F8FAFC;
            color: var(--dark);
        }

        main {
            flex: 1 0 auto;
        }

        /* Semua konten di atas canvas */
        main, footer {
            position: relative;
            z-index: 1;
        }
        .float-wrap {
            position: relative;
            z-index: 999;
        }
        /* Cart dan overlay harus di atas segalanya */
        #cartDrawer, .cart-overlay {
            z-index: 2001 !important;
        }

        /* Global card style - minimal, non-overriding */
        .farma-sidebar, .disease-card, .farma-stat-card {
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(8px);
        }

        /* Section backgrounds */
        .products-main, .farma-main, .act-main,
        .news-main, .about-main, .contact-main,
        .products-section, .features-section,
        .search-section-wrap {
            background: transparent !important;
        }

        .stats-bar, .about-section, .visi-misi-section,
        .team-section, .news-preview-section {
            background: rgba(255,255,255,0.55) !important;
            backdrop-filter: blur(6px);
        }

        /* Navbar */
        .navbar {
            background: #0F172A;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.08);
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            margin: 0 !important;
            z-index: 1000;
            border-bottom: 2px solid #06B6D4;
        }

        /*
         * Global fix: semua page header (bukan section tengah halaman)
         * otomatis dapat padding-top yang cukup agar tidak tertutup navbar.
         * Berlaku untuk: produk, contact, medicines detail, dll.
         */
        .category-page-header,
        .products-header,
        .contact-header,
        .farma-header,
        .act-header,
        .news-page-header,
        .medicines-detail-header,
        .about-page-header {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem;
        }

        .products-main { padding-top: 1rem !important; }

        /* about-hero adalah section pertama di halaman tentang kami */
        .about-hero {
            padding-top: calc(var(--navbar-height, 65px) + 2rem) !important;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.3s;
            flex-shrink: 0;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-brand img {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            height: 45px;
            object-fit: contain;
            margin-left: -8px;
        }
        .navbar-brand span {
            color: white;
            font-size: 1.05rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .navbar-menu {
            display: flex;
            gap: 0;
            align-items: center;
            list-style: none;
            flex-wrap: nowrap;
            justify-content: flex-end;
        }

        .navbar-menu a,
        .navbar-menu .logout-btn {
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.5rem 0.6rem;
            border-radius: 0.375rem;
            font-size: 0.82rem;
            white-space: nowrap;
        }

        .navbar-menu a:hover,
        .navbar-menu .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .navbar-menu .logout-btn {
            background: rgba(220, 38, 38, 0.2);
            border: 1px solid rgba(220, 38, 38, 0.4);
            cursor: pointer;
            border: none;
            width: auto;
            text-align: center;
            font-size: 1rem;
        }

        .navbar-menu .logout-btn:hover {
            background: rgba(220, 38, 38, 0.3);
        }

        .navbar-menu .admin-link {
            color: #7CB342;
        }

        /* Dropdown nav */
        .navbar-menu .has-dropdown { position: relative; }
        .navbar-menu .dropdown-menu-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            min-width: 200px;
            padding: 0.5rem 0;
            z-index: 999;
            list-style: none;
            margin: 0;
            border: 1px solid #e5e7eb;
        }
        .navbar-menu .has-dropdown:hover .dropdown-menu-nav { display: block; }
        .navbar-menu .dropdown-menu-nav li a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            color: #374151 !important;
            font-size: 0.875rem;
            font-weight: 600;
            background: transparent !important;
            border-radius: 0;
            white-space: nowrap;
        }
        .navbar-menu .dropdown-menu-nav li a:hover {
            background: #f3f4f6 !important;
            color: #1E88E5 !important;
        }

        .admin-login-item a {
            padding: 0.5rem 0.6rem;
            font-size: 1rem;
        }

        /* Cart button di navbar */
        .cart-nav-btn {
            position: relative; background: none; border: none; cursor: pointer;
            color: white; padding: 0.5rem 0.6rem; border-radius: 0.375rem;
            font-size: 1rem; display: flex; align-items: center; transition: background 0.2s;
            flex-shrink: 0;
        }
        .cart-nav-btn:hover { background: rgba(255,255,255,0.2); }
        .cart-badge {
            position: absolute; top: 2px; right: 2px;
            background: #ef4444; color: white; font-size: 0.6rem; font-weight: 800;
            width: 16px; height: 16px; border-radius: 50%;
            display: none; align-items: center; justify-content: center;
        }

        /* Hamburger Menu */
        .hamburger-menu {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            background: none;
            border: none;
            z-index: 1001;
            padding: 0.5rem;
        }

        .hamburger-menu span {
            width: 25px;
            height: 3px;
            background: white;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .hamburger-menu.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .hamburger-menu.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger-menu.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Footer */
        .footer {
            background: #0F172A !important;
            color: white;
            padding: 3rem 0;
            margin-top: auto;
            margin-bottom: -1.5rem;
            position: relative;
            z-index: 1;
        }

        .footer-content {
            display: grid;
            grid-template-columns: minmax(220px, 1.25fr) repeat(2, minmax(150px, .8fr)) minmax(240px, 1fr);
            gap: clamp(1.25rem, 3vw, 2.5rem);
            margin-bottom: 2.25rem;
        }

        .footer .container { max-width: 1280px; }
        .footer-content > div { min-width: 0; }
        .footer-content > div:first-child > p { max-width: 34ch; }
        .footer ul { margin: 0; padding: 0; }
        .footer ul li { line-height: 1.6; }
        .footer ul a.footer-contact-link { color:#d1d5db;text-decoration:none;display:flex;align-items:flex-start;gap:.65rem;overflow-wrap:anywhere; }
        .footer ul a.footer-contact-link:hover { color:#fff; }
        .footer-contact-link i { color:#10B981;margin-top:.25rem;flex:0 0 1rem;text-align:center; }
        .footer-address-link { align-items:flex-start; }
        .footer-bottom { padding-top:1.35rem; }

        @media (max-width: 900px) {
            .footer-content { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 560px) {
            .footer { padding:2.25rem 0 1.5rem; }
            .footer-content { grid-template-columns:1fr 1fr;gap:1.5rem 1rem;margin-bottom:1.5rem; }
            .footer-content > div:first-child { grid-column:1 / -1; }
            .footer-content > div:first-child > p { max-width:42ch; }
            .footer .footer-content h3 { margin-bottom:.75rem;font-size:1rem; }
            .footer ul li { font-size:.88rem; }
            .footer-contact-link { font-size:.85rem; }
            .footer-bottom { padding-top:1rem;font-size:.85rem; }
        }
        @media (max-width: 360px) {
            .footer-content { grid-template-columns:1fr; }
            .footer-content > div:first-child { grid-column:auto; }
        }

        .footer h3 {
            margin-bottom: 1rem;
            color: #10B981;
        }

        .footer .footer-content h3 span:first-of-type { color: #0052CC !important; }
        .footer .footer-content h3 span:last-of-type { color: #10B981 !important; }
        .footer .footer-content > div:not(:first-child) i,
        .footer .footer-content h3 > i { color: #10B981 !important; }
        .footer .footer-legal-note { color: #d1d5db; font-size: .82rem; line-height: 1.6; }

        .footer ul {
            list-style: none;
        }

        .footer ul li {
            margin-bottom: 0.5rem;
        }

        .footer ul a {
            color: #d1d5db;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer ul a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #374151;
            padding-top: 2rem;
            text-align: center;
            color: #9ca3af;
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid var(--primary);
        }

        .alert-error {
            background: #fee2e2;
            color: #7f1d1d;
            border-left: 4px solid #ef4444;
        }

        @media (max-width: 768px) {
            .hamburger-menu {
                display: flex;
            }

            .navbar-container {
                padding: 0.5rem 1rem;
            }

            .navbar-menu {
                position: fixed;
                left: 0;
                top: var(--navbar-height, 56px);
                width: 100%;
                background: #0F172A;
                flex-direction: column;
                justify-content: flex-start;
                gap: 0;
                padding: 0 1rem;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.35s ease, padding 0.35s ease;
                z-index: 999;
                box-shadow: 0 8px 24px rgba(15,23,42,0.3);
            }

            .navbar-menu.active {
                max-height: 100vh;
                padding: 1rem 1rem 1.5rem;
            }

            .navbar-menu li {
                width: 100%;
                margin-bottom: 0.25rem;
            }

            .navbar-menu a,
            .navbar-menu .logout-btn {
                padding: 0.875rem 1rem;
                font-size: 1rem;
                display: block;
            }

            .navbar-brand img {
                height: 45px;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand img {
                height: 38px;
            }

            .navbar-container {
                padding: 0.5rem 0.75rem;
            }

            .navbar-menu {
                padding: 0 0.75rem;
            }

            .navbar-menu.active {
                padding: 0.75rem 0.75rem 1.25rem;
            }

            .navbar-menu a,
            .navbar-menu .logout-btn {
                padding: 0.75rem 0.875rem;
                font-size: 0.95rem;
            }
        }
    </style>

    <?php echo $__env->yieldContent('styles'); ?>
    <style>
        .medicine-image,
        .product-img,
        .prod-img,
        .ordering-image {
            position: relative;
            overflow: hidden;
        }
        .product-gallery-link {
            position: absolute;
            inset: 0;
            display: block;
            overflow: hidden;
            color: inherit;
        }
        .product-gallery-photo,
        .product-gallery-link .product-placeholder-logo {
            position: absolute;
            inset: 0;
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain !important;
            transition: opacity .7s cubic-bezier(.22, 1, .36, 1);
            will-change: opacity;
        }
        .product-gallery-photo {
            pointer-events: none;
            transform: none !important;
        }
        .product-gallery-primary { opacity: 1; }
        .product-gallery-secondary { opacity: 0; }
        .product-gallery-link:hover .product-gallery-primary,
        .product-gallery-link:focus-visible .product-gallery-primary { opacity: 0; }
        .product-gallery-link:hover .product-gallery-secondary,
        .product-gallery-link:focus-visible .product-gallery-secondary { opacity: 1; }
        .medicine-name a,.prod-name a,.product-name a { color:inherit; text-decoration:none; }
        .medicine-name a:hover,.prod-name a:hover,.product-name a:hover { color:#1d4ed8; }
        .promo-gallery-secondary { position:absolute; inset:0; width:100%; height:100%; padding:1rem; object-fit:contain; opacity:0; transition:opacity .7s cubic-bezier(.22,1,.36,1); will-change:opacity; pointer-events:none; }
        a:hover .promo-gallery-secondary,a:focus-visible .promo-gallery-secondary { opacity:1; }
        @media (prefers-reduced-motion: reduce) {
            .product-gallery-photo,.promo-gallery-secondary { transition-duration:.01ms; }
        }
        body { color: #1E293B; background: #F8FAFC; }
        .navbar { background: #0F172A !important; border-bottom-color: #06B6D4 !important; }
        .navbar-menu.active { background: #0F172A !important; }
        .footer { background: #0F172A !important; }
        .products-header,
        .contact-header,
        .about-page-header,
        .category-page-header,
        .detail-page-header,
        .medicines-detail-header,
        .farma-header,
        .act-header,
        .news-page-header {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 72%, #12394A 100%) !important;
        }
        .btn-detail,
        .btn-primary,
        .search-engine-box button,
        .search-box button,
        .btn-verify,
        .btn-submit-wa {
            background: #0F172A !important;
            border-color: #06B6D4 !important;
            color: #fff !important;
        }
        .btn-cart {
            border-color: #06B6D4 !important;
            color: #0E6470 !important;
        }
        .btn-cart:hover { background: #ECFEFF !important; }
        .prod-price,
        .sec-link,
        .topbar-title { color: #0E7490 !important; }
        :focus-visible { outline-color: #06B6D4; }

        .navbar-container {
            max-width: 1600px;
            min-height: 68px;
            padding: 0.35rem 1.25rem;
            gap: 0.75rem;
        }
        .navbar-brand {
            min-width: 0;
            gap: 0.5rem;
            transform: none;
        }
        .navbar-brand:hover { transform: none; }
        .navbar-brand img {
            width: 44px;
            height: 44px;
            margin-left: 0;
            flex: 0 0 44px;
            object-fit: contain;
        }
        .navbar-brand span {
            display: inline-block;
            width: auto;
            line-height: 1.2;
            letter-spacing: 0.01em;
        }
        .navbar-menu {
            margin: 0;
            padding-left: 0;
            gap: 0.15rem;
        }
        .navbar-menu a,
        .navbar-menu .logout-btn {
            padding: 0.4rem 0.45rem;
        }

        @media (max-width: 768px) {
            .navbar-container { min-height: 64px; padding: 0.4rem 1rem; }
            .navbar-menu { gap: 0; }
            .navbar-menu a,
            .navbar-menu .logout-btn { padding: 0.875rem 1rem; }
            .navbar-brand img {
                width: 42px;
                height: 42px;
                flex-basis: 42px;
            }
            .navbar-brand span { font-size: 1rem; }
        }
        @media (max-width: 480px) {
            .navbar-container { padding: 0.4rem 0.75rem; }
            .navbar-brand { gap: 0.5rem; }
            .navbar-brand img {
                width: 38px;
                height: 38px;
                flex-basis: 38px;
            }
            .navbar-brand span { font-size: 0.95rem; }
        }
    </style>
    <link rel="stylesheet" href="<?php echo e(asset('css/medix-public-theme.css')); ?>?v=<?php echo e(@filemtime(public_path('css/medix-public-theme.css')) ?: '2'); ?>">
</head>
<body class="public-theme">

    <!-- ===== PARTIKEL STATIS MEDIS ===== -->
    <div class="med-particles" aria-hidden="true">
        <!-- Kapsul -->
        <span class="mp mp-capsule" style="top:4%;left:3%;--r:15deg;--s:0.9;--o:0.30;"></span>
        <span class="mp mp-capsule" style="top:18%;left:88%;--r:-20deg;--s:1.1;--o:0.28;"></span>
        <span class="mp mp-capsule" style="top:35%;left:12%;--r:30deg;--s:0.8;--o:0.32;"></span>
        <span class="mp mp-capsule" style="top:55%;left:92%;--r:-10deg;--s:1.0;--o:0.27;"></span>
        <span class="mp mp-capsule" style="top:72%;left:5%;--r:25deg;--s:0.95;--o:0.30;"></span>
        <span class="mp mp-capsule" style="top:88%;left:78%;--r:-35deg;--s:0.85;--o:0.28;"></span>
        <span class="mp mp-capsule" style="top:45%;left:50%;--r:12deg;--s:0.75;--o:0.25;"></span>
        <span class="mp mp-capsule" style="top:62%;left:35%;--r:-18deg;--s:1.05;--o:0.27;"></span>

        <!-- Tablet bulat -->
        <span class="mp mp-tablet" style="top:8%;left:22%;--s:1.0;--o:0.30;"></span>
        <span class="mp mp-tablet" style="top:28%;left:68%;--s:1.2;--o:0.27;"></span>
        <span class="mp mp-tablet" style="top:50%;left:80%;--s:0.9;--o:0.29;"></span>
        <span class="mp mp-tablet" style="top:78%;left:42%;--s:1.1;--o:0.28;"></span>
        <span class="mp mp-tablet" style="top:92%;left:15%;--s:0.85;--o:0.26;"></span>
        <span class="mp mp-tablet" style="top:15%;left:55%;--s:0.95;--o:0.27;"></span>

        <!-- Tanda plus / palang medis -->
        <span class="mp mp-cross" style="top:6%;left:45%;--s:1.1;--o:0.32;"></span>
        <span class="mp mp-cross" style="top:22%;left:8%;--s:0.9;--o:0.30;"></span>
        <span class="mp mp-cross" style="top:40%;left:95%;--s:1.0;--o:0.28;"></span>
        <span class="mp mp-cross" style="top:60%;left:60%;--s:1.2;--o:0.30;"></span>
        <span class="mp mp-cross" style="top:80%;left:25%;--s:0.95;--o:0.32;"></span>
        <span class="mp mp-cross" style="top:95%;left:55%;--s:0.85;--o:0.27;"></span>
        <span class="mp mp-cross" style="top:33%;left:38%;--s:0.8;--o:0.26;"></span>

        <!-- Pil kecil bulat -->
        <span class="mp mp-pill" style="top:12%;left:75%;--s:1.1;--o:0.33;"></span>
        <span class="mp mp-pill" style="top:30%;left:28%;--s:1.3;--o:0.30;"></span>
        <span class="mp mp-pill" style="top:48%;left:18%;--s:1.0;--o:0.28;"></span>
        <span class="mp mp-pill" style="top:65%;left:85%;--s:0.9;--o:0.31;"></span>
        <span class="mp mp-pill" style="top:82%;left:62%;--s:1.2;--o:0.29;"></span>
        <span class="mp mp-pill" style="top:96%;left:38%;--s:1.05;--o:0.27;"></span>
        <span class="mp mp-pill" style="top:20%;left:42%;--s:0.8;--o:0.26;"></span>

        <!-- Ikon FA medis -->
        <i class="mp mp-icon fa-solid fa-pills"         style="top:10%;left:60%;--s:1.3;--o:0.28;"></i>
        <i class="mp mp-icon fa-solid fa-capsules"      style="top:25%;left:82%;--s:1.1;--o:0.27;"></i>
        <i class="mp mp-icon fa-solid fa-syringe"       style="top:42%;left:6%;--r:45deg;--s:1.2;--o:0.27;"></i>
        <i class="mp mp-icon fa-solid fa-stethoscope"   style="top:58%;left:72%;--s:1.05;--o:0.26;"></i>
        <i class="mp mp-icon fa-solid fa-heart-pulse"   style="top:75%;left:15%;--s:1.2;--o:0.28;"></i>
        <i class="mp mp-icon fa-solid fa-flask"         style="top:88%;left:90%;--s:1.1;--o:0.27;"></i>
        <i class="mp mp-icon fa-solid fa-dna"           style="top:5%;left:35%;--s:1.3;--o:0.26;"></i>
        <i class="mp mp-icon fa-solid fa-microscope"    style="top:38%;left:55%;--s:1.0;--o:0.25;"></i>
        <i class="mp mp-icon fa-solid fa-tablets"       style="top:68%;left:48%;--s:1.1;--o:0.27;"></i>
        <i class="mp mp-icon fa-solid fa-virus"         style="top:85%;left:8%;--s:0.95;--o:0.25;"></i>
    </div>

    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?php echo e(route('login')); ?>" class="navbar-brand" title="Admin Login">
                <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Apotek Medix">
                <span>Apotek Medix</span>
            </a>
            
            <!-- Hamburger Menu Button -->
            <button class="hamburger-menu" id="hamburgerBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="<?php echo e(route('home')); ?>"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="<?php echo e(route('products.apotek')); ?>"><i class="fa-solid fa-store"></i> Belanja B2C</a></li>
                <li><a href="<?php echo e(route('about')); ?>"><i class="fa-solid fa-circle-info"></i> Tentang Kami</a></li>
                <li><a href="<?php echo e(route('contact')); ?>"><i class="fa-solid fa-headset"></i> Hubungi Kami</a></li>

                <?php if(auth()->guard()->check()): ?>
                    <?php if(auth()->user()->isAdmin()): ?>
                        <li><a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-link"><i class="fa-solid fa-gauge"></i> Admin Panel</a></li>
                        <li>
                            <form action="<?php echo e(route('logout')); ?>" method="POST" style="margin: 0;">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
                            </form>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            
            <button class="cart-nav-btn" id="cartNavBtn" onclick="if(typeof openCart==='function'){openCart();}else{window.location.href='<?php echo e(route('cart.index')); ?>';}" title="Keranjang Belanja" aria-label="Buka keranjang belanja">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-badge" id="cartBadgeNav">0</span>
            </button>
        </div>
    </nav>

    <!-- Alert Messages -->
    <?php if($message = Session::get('success')): ?>
        <div class="container">
            <div class="alert alert-success">
                <?php echo e($message); ?>

            </div>
        </div>
    <?php endif; ?>

    <?php if($message = Session::get('error')): ?>
        <div class="container">
            <div class="alert alert-error">
                <?php echo e($message); ?>

            </div>
        </div>
    <?php endif; ?>

    <!-- Content -->
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h3><i class="fa-solid fa-pills" style="color:#10B981;"></i> <span style="color:#0052CC;">Apotek</span><span style="color:#10B981;"> Medix</span></h3>
                    <p style="color:#d1d5db; line-height:1.8; margin-bottom:1.25rem;">Belanja obat dan kebutuhan kesehatan secara online dari Apotek Medix.</p>
                    <div style="display:flex; gap:0.75rem;">
                        <a href="https://facebook.com/apotekmedix" target="_blank" rel="noopener" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#1877f2'" onmouseout="this.style.background='rgba(255,255,255,0.1)'"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/apotekmedix" target="_blank" rel="noopener" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#e1306c'" onmouseout="this.style.background='rgba(255,255,255,0.1)'"><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://wa.me/6285882717844" aria-label="WhatsApp Apotek Medix" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#25d366'" onmouseout="this.style.background='rgba(255,255,255,0.1)'"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="https://pin.it/69pbGoR2f" target="_blank" rel="noopener" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#e60023'" onmouseout="this.style.background='rgba(255,255,255,0.1)'" title="Pinterest"><i class="fa-brands fa-pinterest-p"></i></a>
                        <a href="https://www.youtube.com/@apotekmedix" target="_blank" rel="noopener" style="width:36px;height:36px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#ff0000'" onmouseout="this.style.background='rgba(255,255,255,0.1)'" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h3>Kategori</h3>
                    <ul>
                        <li><a href="<?php echo e(route('products.index', ['kategori_produk' => 'OBAT', 'search' => 'antibiotik'])); ?>"><i class="fa-solid fa-capsules fa-fw" style="margin-right:0.5rem;color:#10B981;"></i>Antibiotik</a></li>
                        <li><a href="<?php echo e(route('products.index', ['search' => 'vitamin'])); ?>"><i class="fa-solid fa-tablets fa-fw" style="margin-right:0.5rem;color:#10B981;"></i>Vitamin</a></li>
                        <li><a href="<?php echo e(route('products.index', ['search' => 'suplemen'])); ?>"><i class="fa-solid fa-flask fa-fw" style="margin-right:0.5rem;color:#10B981;"></i>Suplemen</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Informasi</h3>
                    <ul>
                        <li><a href="<?php echo e(route('contact')); ?>"><i class="fa-solid fa-headset fa-fw" style="margin-right:0.5rem;color:#10B981;"></i>Bantuan & Kontak</a></li>
                        <li><a href="<?php echo e(route('about')); ?>"><i class="fa-solid fa-circle-info fa-fw" style="margin-right:0.5rem;color:#10B981;"></i>Tentang Kami</a></li>
                        <li class="footer-legal-note"><i class="fa-solid fa-shield-halved fa-fw" style="margin-right:0.5rem;color:#10B981;"></i>Periksa nomor izin edar pada kemasan atau detail produk. Hubungi kami bila perlu bantuan verifikasi.</li>
                    </ul>
                </div>
                <div>
                    <h3>Kontak</h3>
                    <ul>
                        <li style="margin-bottom:0.75rem;">
                            <a href="tel:+6285882717844" class="footer-contact-link">
                                <i class="fa-solid fa-phone"></i>
                                <span>0858 8271 7844</span>
                            </a>
                        </li>
                        <li style="margin-bottom:0.75rem;">
                            <a href="mailto:apotekmedix@gmail.com" class="footer-contact-link">
                                <i class="fa-solid fa-envelope"></i>
                                <span>apotekmedix@gmail.com</span>
                            </a>
                        </li>
                        <li>
                            <a href="https://share.google/bUEmuBOhbhyQWmrmD" target="_blank" rel="noopener noreferrer" class="footer-contact-link footer-address-link">
                                <i class="fa-solid fa-location-dot"></i>
                                <span>Jl. Raya Ciawi Prapatan No.256 C, RT.04/RW.04, Sindangsari, Kec. Bogor Timur, Kota Bogor, Jawa Barat 16146</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Apotek Medix. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Floating Buttons -->
    <style>
        .float-wrap { position:fixed; bottom:1.75rem; right:1.75rem; z-index:999; }
        .float-btn-wa {
            width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center;
            background:#16A34A; color:#fff; font-size:1.85rem; text-decoration:none;
            border:3px solid #fff;
            box-shadow:0 4px 16px rgba(15,23,42,.32), 0 0 0 2px #15803D;
            transition:transform 0.2s, box-shadow 0.2s, background-color 0.2s;
        }
        .float-btn-wa:hover { background:#15803D; transform:scale(1.08); box-shadow:0 6px 20px rgba(15,23,42,.38), 0 0 0 2px #166534; }
        .float-btn-wa:focus-visible { outline:3px solid #0052CC; outline-offset:4px; }
        @media (max-width: 768px) {
            .float-wrap { bottom:1rem; right:1rem; }
            .float-btn-wa { width:52px; height:52px; font-size:1.6rem; }
        }
    </style>

    <div class="float-wrap">
        <a href="https://wa.me/6285882717844?text=Halo%20Apotek%20Medix%2C%20saya%20ingin%20bertanya%20tentang%20produk%20obat."
           target="_blank" rel="noopener" class="float-btn-wa" aria-label="Chat WhatsApp Apotek Medix">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
    </div>

    <?php echo $__env->yieldContent('scripts'); ?>

    <script>
        // ===== SMOOTH SCROLL =====
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // ===== SCROLL REVEAL =====
        const revealStyle = document.createElement('style');
        revealStyle.textContent = `
            .reveal {
                opacity: 0;
                transform: translateY(32px);
                transition: opacity 0.6s ease, transform 0.6s ease;
            }
            .reveal.visible {
                opacity: 1;
                transform: translateY(0);
            }
            .reveal-left {
                opacity: 0;
                transform: translateX(-40px);
                transition: opacity 0.6s ease, transform 0.6s ease;
            }
            .reveal-left.visible {
                opacity: 1;
                transform: translateX(0);
            }
            .reveal-right {
                opacity: 0;
                transform: translateX(40px);
                transition: opacity 0.6s ease, transform 0.6s ease;
            }
            .reveal-right.visible {
                opacity: 1;
                transform: translateX(0);
            }
            .reveal-scale {
                opacity: 0;
                transform: scale(0.92);
                transition: opacity 0.55s ease, transform 0.55s ease;
            }
            .reveal-scale.visible {
                opacity: 1;
                transform: scale(1);
            }
        `;
        document.head.appendChild(revealStyle);

        // Auto-tag elemen yang perlu dianimasikan
        function tagRevealElements() {
            const selectors = [
                // Cards
                '.feature-card', '.medicine-card', '.news-preview-card',
                '.news-card', '.value-card', '.team-card', '.related-card',
                '.vm-card', '.stat-card',
                // Sections & blocks
                '.about-section', '.about-text', '.about-image-stack',
                '.price-section', '.description-section',
                '.detail-container > .detail-grid',
                '.related-section',
                // Stats bar items
                '.stat-item',
                // Footer columns
                '.footer-content > div',
            ];

            selectors.forEach(sel => {
                document.querySelectorAll(sel).forEach((el, i) => {
                    if (!el.classList.contains('reveal') &&
                        !el.classList.contains('reveal-left') &&
                        !el.classList.contains('reveal-right') &&
                        !el.classList.contains('reveal-scale')) {
                        el.classList.add('reveal');
                        // Stagger delay untuk grid items
                        el.style.transitionDelay = (i % 4) * 0.1 + 's';
                    }
                });
            });

            // Kolom kiri/kanan di row Bootstrap
            document.querySelectorAll('.row > .col-lg-5, .row > .col-md-5').forEach(el => {
                if (!el.querySelector('.reveal-left') && !el.classList.contains('reveal-left')) {
                    el.classList.add('reveal-left');
                }
            });
            document.querySelectorAll('.row > .col-lg-7, .row > .col-md-7').forEach(el => {
                if (!el.classList.contains('reveal-right')) {
                    el.classList.add('reveal-right');
                }
            });
        }

        // IntersectionObserver untuk trigger animasi
        function initObserver() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

            document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale')
                .forEach(el => observer.observe(el));
        }

        // Jalankan setelah DOM siap
        document.addEventListener('DOMContentLoaded', () => {
            tagRevealElements();
            initObserver();
        });

        // Fallback jika DOMContentLoaded sudah lewat
        if (document.readyState !== 'loading') {
            tagRevealElements();
            initObserver();
        }
    </script>

    <script>
        // Cart badge sync - gunakan key khusus jika halaman mendefinisikan cartSettings
        (function() {
            let savedCtx = {};
            try { savedCtx = JSON.parse(localStorage.getItem('apotekmedix_active_cart_context') || '{}'); } catch (e) {}
            const storageKey = (window.cartSettings && window.cartSettings.storageKey)
                ? window.cartSettings.storageKey
                : (savedCtx.storageKey || 'apotekmedix_cart_retail');
            const cart = JSON.parse(localStorage.getItem(storageKey) || '[]');
            const total = cart.reduce(function(s, i) { return s + i.qty; }, 0);
            
            const badge = document.getElementById('cartBadgeNav');
            const btn   = document.getElementById('cartNavBtn');
            if (badge) { badge.textContent = total; badge.style.display = total > 0 ? 'flex' : 'none'; }
            if(btn) btn.style.display = 'flex';
        })();

        // Hamburger Menu Toggle
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const navbarMenu = document.getElementById('navbarMenu');

        function setNavbarHeight() {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                document.documentElement.style.setProperty('--navbar-height', navbar.offsetHeight + 'px');
            }
        }
        setNavbarHeight();
        window.addEventListener('resize', setNavbarHeight);

        hamburgerBtn.addEventListener('click', () => {
            setNavbarHeight();
            hamburgerBtn.classList.toggle('active');
            navbarMenu.classList.toggle('active');
        });

        // Close menu when clicking on a link
        const menuLinks = navbarMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                hamburgerBtn.classList.remove('active');
                navbarMenu.classList.remove('active');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar')) {
                hamburgerBtn.classList.remove('active');
                navbarMenu.classList.remove('active');
            }
        });
    </script>
    
    <!-- Fix Cursor Script -->
    <script src="<?php echo e(asset('js/fix-cursor-override.js')); ?>"></script>
    <script src="<?php echo e(asset('js/fix-cursor.js')); ?>"></script>
</body>
</html>
<?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/layouts/frontend.blade.php ENDPATH**/ ?>