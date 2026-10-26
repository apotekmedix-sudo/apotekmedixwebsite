

<?php $__env->startSection('title', 'Belanja B2B - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .products-header.pbf-page-header {
        background: linear-gradient(135deg, #0643a8 0%, #0757c9 58%, #0875d9 100%);
        padding: 2.25rem 0 !important;
        position: relative;
        overflow: hidden;
        color: #ffffff;
    }
    .products-header.pbf-page-header::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 350px; height: 350px;
        background: radial-gradient(circle, rgba(8, 217, 232, 0.22) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .products-header .header-deco-icon {
        position: absolute;
        color: rgba(255,255,255,0.08);
        pointer-events: none;
        animation: headerIconFloat 6s ease-in-out infinite;
    }
    .products-header .header-deco-icon-1 { bottom: 10px; right: 12%; font-size: 4rem; animation-delay: 0s; }
    .products-header .header-deco-icon-2 { top: 15px;   right: 28%; font-size: 3rem; animation-delay: 2s; }
    .products-header .header-deco-icon-3 { bottom: 20px; right: 40%; font-size: 2.5rem; animation-delay: 4s; }
    @keyframes headerIconFloat {
        0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.08; }
        50%       { transform: translateY(-12px) rotate(8deg); opacity: 0.14; }
    }

    .pbf-header-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }
    .pbf-header-copy {
        flex: 1 1 460px;
        min-width: 0;
    }
    .products-header h1 i {
        color: #ffffff !important;
        background: transparent !important;
        box-shadow: none !important;
        border: 0 !important;
    }
    .products-header h1 {
        font-size: clamp(1.85rem, 3.5vw, 2.6rem);
        font-weight: 800;
        color: #ffffff !important;
        margin: 0 0 0.45rem;
        line-height: 1.15;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .products-header p {
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 0.96rem;
        line-height: 1.55;
        margin: 0;
        max-width: 680px;
    }
    .breadcrumb-custom {
        display: flex;
        gap: 0.45rem;
        align-items: center;
        margin-bottom: 0.65rem;
        font-size: 0.86rem;
    }
    .breadcrumb-custom a {
        color: rgba(255, 255, 255, 0.82) !important;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    .breadcrumb-custom a:hover { color: #ffffff !important; }
    .breadcrumb-custom span { color: rgba(255, 255, 255, 0.55) !important; }
    .breadcrumb-custom .current {
        color: #86efac !important;
        font-weight: 700;
    }

    /* Verified Partner Status Pill inside Header */
    .pbf-verified-pill {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: rgba(255, 255, 255, 0.13) !important;
        border: 1.5px solid rgba(255, 255, 255, 0.3) !important;
        backdrop-filter: blur(10px);
        padding: 0.75rem 1rem 0.75rem 0.9rem;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(0, 20, 65, 0.2);
        flex-shrink: 0;
    }
    .pbf-verified-info {
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .pbf-verified-shield {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: rgba(16, 185, 129, 0.22) !important;
        border: 1px solid rgba(110, 231, 183, 0.55) !important;
        color: #6ee7b7 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .pbf-verified-text-group {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }
    .pbf-verified-eyebrow {
        font-size: 0.66rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #6ee7b7 !important;
        line-height: 1.2;
    }
    .pbf-verified-status {
        font-size: 0.82rem;
        font-weight: 700;
        color: #ffffff !important;
        line-height: 1.3;
    }
    .pbf-logout-form {
        margin: 0;
        flex-shrink: 0;
    }
    .pbf-logout-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        background: #ffffff !important;
        color: #be123c !important;
        border: 1.5px solid #fecdd3 !important;
        padding: 0.5rem 0.95rem !important;
        border-radius: 10px !important;
        font-size: 0.78rem !important;
        font-weight: 800 !important;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
        white-space: nowrap;
    }
    .pbf-logout-btn i {
        color: #e11d48 !important;
    }
    .pbf-logout-btn:hover {
        background: #fff1f2 !important;
        color: #9f1239 !important;
        border-color: #fda4af !important;
        transform: translateY(-1px);
    }

    /* ===== B2B WELCOME MODAL NOTIFICATION ===== */
    .pbf-welcome-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 4600;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(5px);
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
    }
    .pbf-welcome-overlay.open {
        display: flex;
    }
    .pbf-welcome-dialog {
        position: relative;
        width: min(430px, 100%);
        background: #ffffff !important;
        color: #0f172a !important;
        border-radius: 22px !important;
        border: 1px solid #d1fae5 !important;
        box-shadow: 0 25px 65px rgba(15, 23, 42, 0.35) !important;
        padding: 2.1rem 1.75rem 1.75rem !important;
        text-align: center;
        overflow: hidden;
        animation: pbfWelcomePop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .pbf-welcome-dialog::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 6px;
        background: linear-gradient(90deg, #059669 0%, #10b981 50%, #08d9e8 100%);
    }
    @keyframes pbfWelcomePop {
        from { opacity: 0; transform: translateY(14px) scale(0.95); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .pbf-welcome-close {
        position: absolute;
        top: 0.9rem;
        right: 0.9rem;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        color: #64748b !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pbf-welcome-close:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }
    .pbf-welcome-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%) !important;
        border: 5px solid #ecfdf5 !important;
        color: #059669 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        margin: 0 auto 1rem;
        box-shadow: 0 10px 24px rgba(16, 185, 129, 0.22);
    }
    .pbf-welcome-icon i {
        color: #059669 !important;
    }
    .pbf-welcome-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.75rem;
        border-radius: 999px;
        background: #ecfdf5 !important;
        border: 1px solid #a7f3d0 !important;
        color: #047857 !important;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.65rem;
    }
    .pbf-welcome-badge i {
        color: #059669 !important;
    }
    .pbf-welcome-title {
        font-size: 1.32rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        margin: 0 0 0.45rem !important;
        line-height: 1.25;
    }
    .pbf-welcome-text {
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        color: #065f46 !important;
        background: #f0fdf4 !important;
        border: 1px solid #bbf7d0 !important;
        border-radius: 12px;
        padding: 0.65rem 0.9rem;
        margin: 0 0 0.75rem !important;
        line-height: 1.45;
    }
    .pbf-welcome-sub {
        font-size: 0.83rem !important;
        color: #64748b !important;
        margin: 0 0 1.35rem !important;
        line-height: 1.55;
    }
    .pbf-welcome-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.82rem 1.2rem !important;
        border: 0 !important;
        border-radius: 12px !important;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
        font-size: 0.92rem !important;
        font-weight: 800 !important;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.28) !important;
        transition: all 0.2s ease;
    }
    .pbf-welcome-btn i,
    .pbf-welcome-btn span {
        color: #ffffff !important;
    }
    .pbf-welcome-btn:hover {
        background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;
        transform: translateY(-1px);
    }

    .products-main { background: transparent; padding: 1.5rem 0 5rem !important; min-height: 60vh; }

    .filter-bar {
        background: white; border-radius: 16px; padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb; display: flex; gap: 0.75rem;
        flex-wrap: wrap; align-items: flex-end;
    }
    .filter-group { flex: 1; min-width: 160px; }
    .filter-label { display: block; font-weight: 600; font-size: 0.8rem; color: #374151; margin-bottom: 0.35rem; }
    .filter-input, .filter-select {
        width: 100%; height: 42px; padding: 0.55rem 0.9rem; border: 1.5px solid #e5e7eb;
        border-radius: 10px; font-size: 0.9rem; color: #374151;
        background: #f9fafb; transition: all 0.2s; outline: none;
    }
    .filter-input:focus, .filter-select:focus {
        border-color: #1E88E5; background: white;
        box-shadow: 0 0 0 3px rgba(30,136,229,0.1);
    }
    .btn-filter {
        height: 42px; padding: 0 1.4rem; background: linear-gradient(135deg, #1E88E5, #1565C0);
        color: white; border: none; border-radius: 10px; cursor: pointer;
        font-weight: 700; font-size: 0.9rem; transition: all 0.3s; white-space: nowrap;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;
    }
    .btn-filter:hover { background: linear-gradient(135deg, #1565C0, #0D47A1); transform: translateY(-1px); }
    .btn-reset {
        height: 42px; padding: 0 1rem; background: white; color: #6b7280;
        border: 1.5px solid #e5e7eb; border-radius: 10px; cursor: pointer;
        font-weight: 600; font-size: 0.9rem; text-decoration: none; white-space: nowrap; transition: all 0.2s;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-reset:hover { border-color: #ef4444; color: #ef4444; }

    .result-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem; }
    .result-info p { color: #6b7280; font-size: 0.875rem; margin: 0; }

    .medicines-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.5rem; margin-bottom: 2.5rem;
    }
    .medicine-card {
        background: white; border-radius: 16px; overflow: hidden;
        border: 1px solid #e5e7eb; transition: all 0.3s;
        display: flex; flex-direction: column;
    }
    .medicine-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 35px rgba(30,136,229,0.12);
        border-color: #90caf9;
    }
    .medicine-image {
        width: 100%; height: 180px;
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        display: flex; align-items: center; justify-content: center;
        font-size: 3rem; overflow: hidden;
    }
    .medicine-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
    .medicine-image img.product-placeholder-logo { width: 72%; height: 72%; object-fit: contain; }
    .medicine-card:hover .medicine-image img { transform: scale(1.05); }
    .medicine-body { padding: 1.1rem; flex: 1; display: flex; flex-direction: column; }
    .medicine-company {
        display: inline-block; background: #e3f2fd; color: #1565C0;
        padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.72rem;
        font-weight: 700; margin-bottom: 0.5rem; letter-spacing: 0.3px;
    }
    .medicine-desc { color: #374151; font-size: 0.9rem; margin: 0 0 0.6rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .medicine-meta { font-size: 0.78rem; color: #6b7280; margin-bottom: 0.45rem; }
    .medicine-name {
        font-size: 0.95rem; font-weight: 700; margin-bottom: 0.5rem; color: #1f2937;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; line-height: 1.4; flex: 1;
    }
    .medicine-price { font-size: 1.15rem; font-weight: 800; color: #1E88E5; margin-bottom: 0.5rem; }
    .stock-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.85rem; }
    .stock-available { background: #d1fae5; color: #065f46; }
    .stock-low       { background: #fef3c7; color: #92400e; }
    .stock-out       { background: #fee2e2; color: #7f1d1d; }
    .medicine-btn {
        display: block; width: 100%; padding: 0.65rem;
        background: linear-gradient(135deg, #1E88E5, #1565C0);
        color: white; border: none; border-radius: 10px; cursor: pointer;
        font-weight: 700; font-size: 0.875rem; text-align: center;
        text-decoration: none; transition: all 0.3s;
    }
    .medicine-btn:hover {
        background: linear-gradient(135deg, #1565C0, #0D47A1);
        transform: translateY(-2px); color: white;
        box-shadow: 0 4px 12px rgba(30,136,229,0.3);
    }
    .btn-cart {
        display: block; width: 100%; padding: 0.55rem;
        background: white; color: #1E88E5;
        border: 2px solid #1E88E5; border-radius: 10px; cursor: pointer;
        font-weight: 700; font-size: 0.82rem; text-align: center;
        text-decoration: none; transition: all 0.3s; margin-top: 0.5rem;
    }
    .btn-cart:hover { background: #e3f2fd; transform: translateY(-1px); }
    .btn-cart.added { background: #d1fae5; color: #065f46; border-color: #34d399; }

    .empty-state { text-align: center; padding: 5rem 2rem; background: white; border-radius: 16px; border: 1px solid #e5e7eb; }
    .empty-state h3 { font-size: 1.4rem; font-weight: 700; color: #1f2937; margin: 1rem 0 0.5rem; }
    .empty-state p  { color: #6b7280; }

    .pagination-wrap { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; }
    .pagination-wrap .info { color: #6b7280; font-size: 0.875rem; }
    .pagination-btns { display: flex; gap: 0.35rem; align-items: center; }
    .page-btn {
        padding: 0.4rem 0.75rem; border-radius: 0.4rem; background: white;
        color: #374151; font-size: 0.875rem; text-decoration: none;
        border: 1px solid #e5e7eb; min-width: 36px; text-align: center; transition: all 0.2s;
    }
    .page-btn:hover  { background: #1E88E5; color: white; border-color: #1E88E5; }
    .page-btn.active { background: #1E88E5; color: white; border-color: #1E88E5; font-weight: 700; }
    .page-btn.disabled { background: #f3f4f6; color: #d1d5db; cursor: not-allowed; pointer-events: none; }

    @media (max-width: 768px) {
        .pbf-verified-pill {
            width: 100%;
            justify-content: space-between;
        }
        .filter-bar { flex-direction: column; padding: 1rem; gap: 0.75rem; }
        .filter-group { width: 100%; min-width: unset; }
        .filter-bar > div:last-child { width: 100%; display: flex; gap: 0.5rem; }
        .btn-filter, .btn-reset { flex: 1; text-align: center; }
        .medicines-grid { grid-template-columns: repeat(2, 1fr); gap: 0.85rem; }
    }

    @media (max-width: 480px) {
        .pbf-verified-pill {
            flex-direction: column;
            align-items: stretch;
            gap: 0.65rem;
        }
        .pbf-logout-btn {
            width: 100%;
            justify-content: center;
        }
        .medicines-grid { grid-template-columns: repeat(2, 1fr); gap: 0.65rem; }
        .medicine-image { height: 120px; }
        .medicine-body { padding: 0.65rem; }
        .medicine-name { font-size: 0.82rem; }
        .medicine-price { font-size: 0.9rem; }
        .medicine-btn, .btn-cart { font-size: 0.78rem; padding: 0.5rem; }
    }
</style>
<?php echo $__env->make('partials.product-card-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="products-header pbf-page-header">
    <div class="container pbf-header-flex">
        <div class="pbf-header-copy">
            <div class="breadcrumb-custom">
                <a href="<?php echo e(route('home')); ?>"><i class="fa-solid fa-house"></i> Home</a>
                <span>/</span>
                <span class="current">Belanja B2B</span>
            </div>
            <h1><i class="fa-solid fa-boxes-stacked"></i> Belanja B2B</h1>
            <p><?php echo e($total); ?> produk PBF tersedia dari Apotek Medix untuk mitra apotek dan distributor yang membutuhkan stok terpercaya.</p>
        </div>

        <div class="pbf-verified-pill">
            <div class="pbf-verified-info">
                <span class="pbf-verified-shield" aria-hidden="true">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <div class="pbf-verified-text-group">
                    <span class="pbf-verified-eyebrow">Status Mitra B2B</span>
                    <span class="pbf-verified-status">Anda masuk sebagai mitra PBF terverifikasi</span>
                </div>
            </div>
            <form method="POST" action="<?php echo e(route('products.pbf.logout')); ?>" class="pbf-logout-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="pbf-logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar Akses
                </button>
            </form>
        </div>
    </div>
    <i class="fa-solid fa-pills header-deco-icon header-deco-icon-1"></i>
    <i class="fa-solid fa-capsules header-deco-icon header-deco-icon-2"></i>
    <i class="fa-solid fa-syringe header-deco-icon header-deco-icon-3"></i>
</div>


<?php if(session('pbf_success')): ?>
<div class="pbf-welcome-overlay open" id="pbfWelcomeOverlay" role="dialog" aria-modal="true" aria-labelledby="pbfWelcomeTitle" onclick="if(event.target===this)closePbfWelcomeModal()">
    <div class="pbf-welcome-dialog">
        <button type="button" class="pbf-welcome-close" onclick="closePbfWelcomeModal()" aria-label="Tutup notifikasi">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="pbf-welcome-icon" aria-hidden="true">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <span class="pbf-welcome-badge">
            <i class="fa-solid fa-shield-halved"></i> Mitra PBF Terverifikasi
        </span>
        <h3 class="pbf-welcome-title" id="pbfWelcomeTitle">Selamat Datang!</h3>
        <p class="pbf-welcome-text"><?php echo e(session('pbf_success')); ?></p>
        <p class="pbf-welcome-sub">Anda kini dapat menelusuri katalog dan memesan produk B2B dengan harga khusus mitra apotek &amp; distributor.</p>
        <button type="button" class="pbf-welcome-btn" onclick="closePbfWelcomeModal()">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>Mulai Belanja Sekarang</span>
        </button>
    </div>
</div>
<script>
    function closePbfWelcomeModal() {
        var overlay = document.getElementById('pbfWelcomeOverlay');
        if (overlay) {
            overlay.classList.remove('open');
        }
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePbfWelcomeModal();
        }
    });
</script>
<?php endif; ?>

<div class="products-main products-pbf">
    <div class="container">

        <form method="GET" action="<?php echo e(route('products.pbf')); ?>" class="filter-bar">
            <div class="filter-group" style="flex: 2; min-width: 200px;">
                <label class="filter-label"><i class="fa-solid fa-magnifying-glass"></i> Cari Produk</label>
                <input type="text" name="search" class="filter-input"
                       placeholder="Cari nama obat, indikasi, komposisi, sediaan (sirup/tablet), dosis, golongan..."
                       value="<?php echo e($search); ?>">
            </div>
            <div class="filter-group">
                <label class="filter-label"><i class="fa-solid fa-tag"></i> Kategori</label>
                <select name="kategori_produk" class="filter-select">
                    <option value="">Semua Kategori</option>
                    <?php $__currentLoopData = $kategoriOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $icon = match($k) { 'OBAT' => '💊', 'SKINCARE & KOSMETIK' => '✨', 'ALAT KESEHATAN' => '🩺', default => '📦' }; ?>
                        <option value="<?php echo e($k); ?>" <?php if(($kategori_produk ?? '') === $k): echo 'selected'; endif; ?>><?php echo e($icon); ?> <?php echo e($k); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label"><i class="fa-solid fa-building"></i> Perusahaan</label>
                <select name="perusahaan" class="filter-select">
                    <option value="">Semua Perusahaan</option>
                    <?php $__currentLoopData = $perusahaanList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p); ?>" <?php if($perusahaan === $p): echo 'selected'; endif; ?>><?php echo e($p); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label"><i class="fa-solid fa-arrow-up-wide-short"></i> Urutkan</label>
                <select name="sort" class="filter-select">
                    <option value="terbaru"    <?php if($sort === 'terbaru'): echo 'selected'; endif; ?>>Terbaru</option>
                    <option value="harga_asc"  <?php if($sort === 'harga_asc'): echo 'selected'; endif; ?>>Harga Terendah</option>
                    <option value="harga_desc" <?php if($sort === 'harga_desc'): echo 'selected'; endif; ?>>Harga Tertinggi</option>
                    <option value="nama"       <?php if($sort === 'nama'): echo 'selected'; endif; ?>>Nama A–Z</option>
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                <?php if($search || ($kategori_produk ?? '') || $perusahaan || ($indikasi ?? '') || ($komposisi ?? '') || ($sediaan ?? '') || ($dosis ?? '') || ($golongan ?? '') || $sort !== 'terbaru'): ?>
                    <a href="<?php echo e(route('products.pbf')); ?>" class="btn-reset">✕ Reset</a>
                <?php endif; ?>
            </div>

        </form>

        <div class="result-info">
            <p>
                Menampilkan <strong><?php echo e($medicines->firstItem() ?? 0); ?>–<?php echo e($medicines->lastItem() ?? 0); ?></strong>
                dari <strong><?php echo e($medicines->total()); ?></strong> produk
                <?php if($search): ?> · "<strong><?php echo e($search); ?></strong>" <?php endif; ?>
                <?php if($kategori_produk ?? ''): ?> · <strong><?php echo e($kategori_produk); ?></strong> <?php endif; ?>
                <?php if($perusahaan): ?> · <strong><?php echo e($perusahaan); ?></strong> <?php endif; ?>
            </p>
        </div>

        <?php if($medicines->count() > 0): ?>
            <div class="medicines-grid">
                <?php $__currentLoopData = $medicines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="medicine-card">
                        <div class="medicine-card-brand">Apotek Medix</div>
                        <div class="medicine-image">
                            <?php echo $__env->make('partials.product-gallery-image', ['medicine' => $medicine], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php echo $__env->make('partials.product-logo-marker', ['medicine' => $medicine], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                        <div class="medicine-body">
                            <div class="medicine-card-principal"><?php echo e($medicine->brand ?: $medicine->kategori); ?></div>
                            <h3 class="medicine-name"><a href="<?php echo e(route('medicines.show', $medicine->id)); ?>"><?php echo e($medicine->nama_obat); ?></a></h3>

                            <?php
                                $basePrice = $medicine->getChannelBasePrice('b2b');
                                $promoPrice = $medicine->getChannelPromoPrice('b2b');
                            ?>
                            <div class="medicine-card-details">
                                <p title="<?php echo e($medicine->komposisi ?: $medicine->nama_obat); ?>"><strong>Komposisi:</strong> <?php echo e(\Illuminate\Support\Str::limit($medicine->komposisi ?: $medicine->nama_obat, 15, '...')); ?></p>
                                <p title="<?php echo e($medicine->indikasi ?: '—'); ?>"><strong>Indikasi:</strong> <?php echo e(\Illuminate\Support\Str::limit($medicine->indikasi ?: '—', 15, '...')); ?></p>
                                <p title="<?php echo e($medicine->golongan ?: '—'); ?>"><strong>Golongan:</strong> <?php echo e(\Illuminate\Support\Str::limit($medicine->golongan ?: '—', 15, '...')); ?></p>
                            </div>
                            <?php echo $__env->make('partials.product-unit-selector', [
                                'medicine' => $medicine,
                                'unitSelectId' => 'pbf-unit-' . $medicine->id,
                                'priceChannel' => 'b2b',
                                'showUnitChoices' => false,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php
                                $stockUnits = (int) $medicine->available_stock_units;
                                $stock = $stockUnits / max(1, $medicine->units_per_box);
                                $stockDisplay = rtrim(rtrim(number_format($stock, 6, ',', '.'), '0'), ',');
                            ?>
                            <span class="stock-badge <?php echo e($stock <= 0 ? 'stock-out' : ($stock <= 10 ? 'stock-low' : 'stock-available')); ?>" data-stock-badge data-stock-product-id="<?php echo e($medicine->id); ?>" data-stock-unit-factor="<?php echo e(max(1, $medicine->units_per_box)); ?>">
                                <i class="fa-solid <?php echo e($stock <= 0 ? 'fa-circle-xmark' : 'fa-boxes-stacked'); ?>"></i>
                                <?php echo e($stock > 0 ? 'Stok: ' . $stockDisplay : 'Stok habis'); ?>

                            </span>
                            <div class="medicine-meta medicine-meta-row">
                                <span class="medicine-meta-sediaan">
                                    <?php if($medicine->sediaan_label): ?>
                                        <i class="fa-solid fa-cube"></i> <span>Sediaan: <?php echo e($medicine->sediaan_label); ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="medicine-sold-badge" data-sold-product-id="<?php echo e($medicine->id); ?>">
                                    <i class="fa-solid fa-fire-flame-curved"></i> Terjual <?php echo e(number_format((int) $medicine->terjual, 0, ',', '.')); ?>

                                </span>
                            </div>
                            <?php if($basePrice > 0): ?>
                                <button class="btn-cart" data-stock-cart <?php if($stock <= 0): echo 'disabled'; endif; ?>
                                        data-product-id="<?php echo e($medicine->id); ?>"
                                        data-package-unit-label="<?php echo e($medicine->package_unit_label); ?>"
                                        data-product-name="<?php echo e($medicine->nama_obat); ?>"
                                        data-product-price="<?php echo e($promoPrice); ?>"
                                        data-product-image="<?php echo e($medicine->image_url ?: ''); ?>"
                                        data-product-brand="<?php echo e($medicine->brand ?: $medicine->kategori); ?>"
                                        onclick="addToCart(<?php echo e($medicine->id); ?>, this.dataset.productName, Number(this.dataset.productPrice), this.dataset.productImage, this.dataset.productBrand, this, 'pbf-unit-<?php echo e($medicine->id); ?>')">
                                    <i class="fa-solid fa-cart-plus"></i> Masukkan ke Keranjang
                                </button>
                            <?php else: ?>
                                <button class="btn-cart" type="button" disabled style="opacity:.55;cursor:not-allowed;">
                                    Harga belum tersedia
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="pagination-wrap">
                <p class="info">Halaman <?php echo e($medicines->currentPage()); ?> dari <?php echo e($medicines->lastPage()); ?></p>
                <div class="pagination-btns">
                    <?php if($medicines->onFirstPage()): ?>
                        <span class="page-btn disabled">‹</span>
                    <?php else: ?>
                        <a href="<?php echo e($medicines->previousPageUrl()); ?>" class="page-btn">‹</a>
                    <?php endif; ?>

                    <?php $__currentLoopData = $medicines->getUrlRange(1, $medicines->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $medicines->currentPage()): ?>
                            <span class="page-btn active"><?php echo e($page); ?></span>
                        <?php elseif($page == 1 || $page == $medicines->lastPage() || abs($page - $medicines->currentPage()) <= 2): ?>
                            <a href="<?php echo e($url); ?>" class="page-btn"><?php echo e($page); ?></a>
                        <?php elseif(abs($page - $medicines->currentPage()) == 3): ?>
                            <span class="page-btn disabled">…</span>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php if($medicines->hasMorePages()): ?>
                        <a href="<?php echo e($medicines->nextPageUrl()); ?>" class="page-btn">›</a>
                    <?php else: ?>
                        <span class="page-btn disabled">›</span>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open" style="font-size:3.5rem;color:#d1d5db;"></i>
                <h3>Produk tidak ditemukan</h3>
                <p>
                    <?php if($search || ($kategori_produk ?? '')): ?>
                        Coba ubah kata kunci atau filter pencarian.
                    <?php else: ?>
                        Belum ada produk tersedia.
                    <?php endif; ?>
                </p>
                <?php if($search || ($kategori_produk ?? '') || $perusahaan): ?>
                    <a href="<?php echo e(route('products.pbf')); ?>" class="btn-reset" style="display:inline-block;margin-top:1rem;">✕ Hapus Filter</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
window.cartSettings = {
    storageKey: 'apotekmedix_cart_pbf',
    wholesaleOrder: true,
    receiptStoreName: 'Apotek Medix',
    receiptStoreAddress: 'Jl. Pendawa Jl. Sersan Muis No.99, 2 Ilir, Kec. Ilir Tim. II, Kota Palembang\nSumatera Selatan 30118',
    receiptFilePrefix: 'struk-pt-surya-sharone-abadi',
    continueShoppingUrl: <?php echo json_encode(route('products.pbf'), 15, 512) ?>
};
</script>
<?php echo $__env->make('partials.shopping-access-lock', ['channel' => 'b2b'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('partials.cart', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\products_pbf.blade.php ENDPATH**/ ?>