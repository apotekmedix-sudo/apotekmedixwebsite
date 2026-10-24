<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Apotek Medix')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Font Awesome (icons for admin UI) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #0F172A;
            --secondary: #1E293B;
            --accent: #84CC16;
            --dark: #1E293B;
            --light: #F8FAFC;
            --sidebar: #0F172A;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            color: var(--dark);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, #0F172A 0%, #111C31 100%);
            color: white;
            width: 250px;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 12px rgba(0,0,0,0.3);
        }

        .sidebar-brand {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--accent);
        }

        .sidebar-menu a.active {
            background: rgba(16,185,129,0.1);
            color: var(--accent);
            border-left-color: var(--accent);
        }

        .sidebar-menu a i {
            font-size: 1.25rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        /* Topbar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, white 0%, #F5F7FA 100%);
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary);
        }

        .topbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(30, 136, 229, 0.3);
        }

        .logout-btn {
            padding: 0.5rem 1rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        /* Alert */
        .alert {
            padding: 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left-color: var(--primary);
        }

        .alert-error {
            background: #fee2e2;
            color: #7f1d1d;
            border-left-color: #ef4444;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-top: 4px solid var(--primary);
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead {
            background: var(--light);
        }

        .table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid #e5e7eb;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        /* Buttons */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
        }

        .form-errors {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1000;
                width: 260px;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-overlay.open {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding: 0;
                padding-bottom: 70px;
                display: flex;
                flex-direction: column;
            }

            /* Topbar sticky di atas */
            .topbar {
                position: sticky;
                top: 0;
                z-index: 100;
                border-radius: 0;
                margin-bottom: 0;
                padding: 0.85rem 1rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }

            .topbar-title {
                font-size: 1rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 160px;
            }

            /* Sembunyikan nama user, tampilkan avatar saja */
            .user-info > div:last-child {
                display: none;
            }

            .hamburger-btn {
                display: flex !important;
            }

            /* Konten dalam padding */
            .main-content > *:not(.topbar) {
                padding-left: 0.85rem;
                padding-right: 0.85rem;
            }

            .main-content > .alert {
                margin-top: 0.85rem;
            }

            .main-content > .stats-grid {
                margin-top: 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.65rem;
                margin-bottom: 1rem;
            }

            .stat-card {
                padding: 0.9rem 1rem;
                border-radius: 0.65rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.72rem;
            }

            .card {
                padding: 1rem;
                border-radius: 0.75rem;
                margin-bottom: 1rem;
            }

            .card-title {
                font-size: 0.95rem;
                margin-bottom: 0.85rem;
            }

            /* Quick actions grid 2 kolom */
            .quick-actions-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 0.6rem;
            }

            .quick-actions-grid .btn {
                text-align: center;
                font-size: 0.78rem;
                padding: 0.6rem 0.4rem;
                line-height: 1.3;
            }

            /* Table scroll horizontal */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 0.65rem;
            }

            .table {
                font-size: 0.78rem;
                min-width: 480px;
            }

            .table th,
            .table td {
                padding: 0.55rem 0.65rem;
                white-space: nowrap;
            }

            .bottom-nav {
                display: flex !important;
            }

            .logout-btn {
                font-size: 0.78rem;
                padding: 0.4rem 0.65rem;
            }
        }

        @media (max-width: 480px) {
            .topbar-title {
                font-size: 0.9rem;
                max-width: 120px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            .stat-card {
                padding: 0.75rem;
            }

            .stat-value {
                font-size: 1.3rem;
            }

            .stat-label {
                font-size: 0.68rem;
            }

            .main-content > *:not(.topbar) {
                padding-left: 0.65rem;
                padding-right: 0.65rem;
            }
        }

        /* Hamburger button - hidden on desktop */
        .hamburger-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Bottom navigation - hidden on desktop */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            z-index: 998;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
        }

        .bottom-nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
            padding: 0.4rem 0.75rem;
            text-decoration: none;
            color: #6b7280;
            font-size: 0.65rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s;
            min-width: 55px;
        }

        .bottom-nav-item span:first-child {
            font-size: 1.3rem;
        }

        .bottom-nav-item.active,
        .bottom-nav-item:hover {
            color: var(--primary);
        }

        .bottom-nav-item.active span:first-child {
            transform: scale(1.15);
        }
    </style>

    @yield('styles')
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img src="{{ asset('LOGO APOTEK MEDIX.PNG') }}" alt="Apotek Medix" style="height: 35px; object-fit: contain;">
                <span style="white-space: nowrap;">Admin</span>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="@if(Route::current()->getName() == 'admin.dashboard') active @endif">
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.produk.index') }}" class="@if(Route::current()->getName() === 'admin.produk.index') active @endif">
                        <span>🛒</span>
                        <span>Produk Kami</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.produk-pbf.index') }}" class="@if(Route::current()->getName() === 'admin.produk-pbf.index') active @endif">
                        <span>📦</span>
                        <span>Produk Pemesanan PBF</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.purchase-history.index') }}" class="@if(str_contains(Route::current()->getName() ?? '', 'admin.purchase-history')) active @endif">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Transaksi</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('cashier.dashboard') }}" class="@if(str_contains(Route::current()->getName() ?? '', 'cashier.')) active @endif">
                        <i class="fa-solid fa-cash-register"></i>
                        <span>Dashboard Kasir</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.customers.index') }}" class="@if(str_contains(Route::current()->getName() ?? '', 'admin.customers')) active @endif">
                        <i class="fa-solid fa-users"></i>
                        <span>Data Customer</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.access-codes.index') }}" class="@if(str_contains(Route::current()->getName() ?? '', 'admin.access-codes')) active @endif">
                        <i class="fa-solid fa-key"></i>
                        <span>Aktivasi Kode Belanja</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.banners.index') }}" class="@if(str_contains(Route::current()->getName() ?? '', 'admin.banners')) active @endif">
                        <span>🖼️</span>
                        <span>Banner Promo</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.promotions.index') }}" class="@if(str_contains(Route::current()->getName() ?? '', 'admin.promotions') || str_contains(Route::current()->getName() ?? '', 'admin.discount-codes')) active @endif">
                        <i class="fa-solid fa-tags"></i>
                        <span>Promo & Diskon</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('home') }}">
                        <span>🏠</span>
                        <span>Kembali ke Home</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <button class="hamburger-btn" onclick="toggleSidebar()">☰</button>
                    <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="topbar-right">
                    <div class="user-info">
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <div>
                            <div style="font-weight: 600;">{{ auth()->user()->name }}</div>
                            <div style="font-size: 0.875rem; color: #6b7280;">Admin</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </div>

            <!-- Alert Messages -->
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    ✓ {{ $message }}
                </div>
            @endif

            @if ($message = Session::get('error'))
                <div class="alert alert-error">
                    ✕ {{ $message }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error" style="margin-bottom:1rem;">
                    <strong>⚠️ Terdapat kesalahan:</strong>
                    <ul style="margin:0.5rem 0 0 1.25rem;padding:0;">
                        @foreach ($errors->all() as $error)
                            <li style="font-size:0.875rem;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Content -->
            @yield('content')
        </main>
    </div>

    @yield('scripts')

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('open');
        }
    </script>

    <!-- Bottom Navigation (mobile only) -->
    <nav class="bottom-nav">
        <div class="bottom-nav-items">
            <a href="{{ route('admin.dashboard') }}" class="bottom-nav-item @if(Route::current()->getName() == 'admin.dashboard') active @endif">
                <span>📊</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.produk.index') }}" class="bottom-nav-item @if(str_contains(Route::current()->getName() ?? '', 'admin.produk')) active @endif">
                <span>🛒</span>
                <span>Produk</span>
            </a>
            <a href="{{ route('home') }}" class="bottom-nav-item">
                <span>🏠</span>
                <span>Home</span>
            </a>
        </div>
    </nav>
</body>
</html>
