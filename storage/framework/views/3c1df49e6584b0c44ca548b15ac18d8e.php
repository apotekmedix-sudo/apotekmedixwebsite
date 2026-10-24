

<?php $__env->startSection('title', ($internalOrder ?? false) ? 'Belanja Internal - Apotek Medix' : 'Produk Obat - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .products-header {
        background: linear-gradient(135deg, #0D47A1 0%, #1565C0 50%, #1E88E5 100%);
        padding: 4rem 0;
        position: relative;
        overflow: hidden;
    }
    .products-header::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 350px; height: 350px;
        background: radial-gradient(circle, rgba(124,179,66,0.18) 0%, transparent 70%);
        border-radius: 50%;
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
    .products-header h1 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800; color: white;
        margin-bottom: 0.5rem; position: relative;
    }
    .products-header p { color: rgba(255,255,255,0.8); font-size: 1rem; position: relative; }
    .breadcrumb-custom { display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1rem; position: relative; }
    .breadcrumb-custom a { color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem; }
    .breadcrumb-custom a:hover { color: white; }
    .breadcrumb-custom span { color: rgba(255,255,255,0.5); font-size: 0.9rem; }
    .breadcrumb-custom .current { color: #a5d65a; font-size: 0.9rem; font-weight: 600; }

    .products-main { background: transparent; padding: 2.5rem 0 5rem; min-height: 60vh; }

    .filter-bar {
        background: white; border-radius: 16px; padding: 1.25rem 1.5rem;
        margin-bottom: 2rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb; display: flex; gap: 0.75rem;
        flex-wrap: wrap; align-items: flex-end;
    }
    .filter-group { flex: 1; min-width: 160px; }
    .filter-label { display: block; font-weight: 600; font-size: 0.8rem; color: #374151; margin-bottom: 0.35rem; }
    .filter-input, .filter-select {
        width: 100%; padding: 0.6rem 0.9rem; border: 1.5px solid #e5e7eb;
        border-radius: 10px; font-size: 0.9rem; color: #374151;
        background: #f9fafb; transition: all 0.2s; outline: none;
    }
    .filter-input:focus, .filter-select:focus {
        border-color: #1E88E5; background: white;
        box-shadow: 0 0 0 3px rgba(30,136,229,0.1);
    }
    .btn-filter {
        padding: 0.6rem 1.4rem; background: linear-gradient(135deg, #1E88E5, #1565C0);
        color: white; border: none; border-radius: 10px; cursor: pointer;
        font-weight: 600; font-size: 0.9rem; transition: all 0.3s; white-space: nowrap;
    }
    .btn-filter:hover { background: linear-gradient(135deg, #1565C0, #0D47A1); transform: translateY(-2px); }
    .btn-reset {
        padding: 0.6rem 1rem; background: white; color: #6b7280;
        border: 1.5px solid #e5e7eb; border-radius: 10px; cursor: pointer;
        font-weight: 600; font-size: 0.9rem; text-decoration: none; white-space: nowrap; transition: all 0.2s;
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

/* CART DRAWER */
.cart-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
.cart-overlay.open { opacity: 1; pointer-events: all; }
.cart-drawer { position: fixed; top: 0; right: -420px; width: 420px; max-width: 100vw; height: 100vh; background: #fff; z-index: 2001; display: flex; flex-direction: column; box-shadow: -8px 0 40px rgba(0,0,0,0.15); transition: right 0.35s cubic-bezier(.4,0,.2,1); }
.cart-drawer.open { right: 0; }
.cart-head { background: linear-gradient(135deg, #0D47A1, #1E88E5); padding: 1.25rem 1.5rem; color: #fff; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.cart-head h2 { font-size: 1.1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.cart-close-btn { background: rgba(255,255,255,0.2); border: none; color: #fff; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.cart-close-btn:hover { background: rgba(255,255,255,0.35); }
.cart-body { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }
.cart-empty-msg { text-align: center; padding: 3rem 1rem; color: #9ca3af; }
.cart-empty-msg i { font-size: 3rem; display: block; margin-bottom: 0.75rem; }
.cart-item-row { display: flex; gap: 0.75rem; align-items: flex-start; padding: 0.85rem 0; border-bottom: 1px solid #f3f4f6; }
.cart-item-thumb { width: 52px; height: 52px; border-radius: 10px; flex-shrink: 0; background: linear-gradient(135deg,#e3f2fd,#bbdefb); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.cart-item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name { font-size: 0.84rem; font-weight: 700; color: #1f2937; margin-bottom: 0.2rem; line-height: 1.3; }
.cart-item-price { font-size: 0.8rem; color: #1E88E5; font-weight: 700; }
.cart-qty-row { display: flex; align-items: center; gap: 0.4rem; margin-top: 0.4rem; }
.qty-btn { width: 26px; height: 26px; border-radius: 6px; border: 1.5px solid #e5e7eb; background: #fff; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #374151; transition: all 0.2s; }
.qty-btn:hover { border-color: #1E88E5; color: #1E88E5; }
.qty-num { font-size: 0.85rem; font-weight: 700; min-width: 20px; text-align: center; }
.cart-item-del { background: none; border: none; color: #d1d5db; cursor: pointer; font-size: 0.9rem; padding: 0.2rem; flex-shrink: 0; transition: color 0.2s; }
.cart-item-del:hover { color: #ef4444; }
.cart-foot { padding: 1.25rem 1.5rem; border-top: 2px solid #f3f4f6; flex-shrink: 0; background: #fafbff; }
.cart-total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.cart-total-row span { font-size: 0.9rem; color: #6b7280; }
.cart-total-row strong { font-size: 1.2rem; color: #1E88E5; font-weight: 800; }
.btn-order-wa { display: flex; align-items: center; justify-content: center; gap: 0.6rem; width: 100%; padding: 0.85rem; background: #25D366; color: #fff; border: none; border-radius: 12px; cursor: pointer; font-weight: 700; font-size: 1rem; transition: all 0.3s; }
.btn-order-wa:hover { background: #1ebe5d; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,0.4); }
.btn-clear-cart { display: block; width: 100%; padding: 0.5rem; background: none; border: none; color: #9ca3af; font-size: 0.8rem; cursor: pointer; margin-top: 0.5rem; transition: color 0.2s; }
.btn-clear-cart:hover { color: #ef4444; }

/* ORDER MODAL */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 3000; }
.modal-box { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 92%; max-width: 480px; max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 20px; z-index: 3001; box-shadow: 0 25px 60px rgba(0,0,0,0.25); }
.modal-head { background: linear-gradient(135deg,#1565C0,#1E88E5); padding: 1.25rem 1.5rem; border-radius: 20px 20px 0 0; display: flex; justify-content: space-between; align-items: center; }
.modal-head h3 { color: #fff; margin: 0; font-size: 1rem; font-weight: 700; }
.modal-head p { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.75rem; }
.modal-close { background: rgba(255,255,255,0.2); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1rem; }
.modal-summary { padding: 1rem 1.5rem; background: #f8faff; border-bottom: 1px solid #e5e7eb; font-size: 0.85rem; color: #374151; }
.modal-form { padding: 1.25rem 1.5rem 1.5rem; }
.form-lbl { display: block; font-size: 0.78rem; font-weight: 700; color: #374151; margin-bottom: 0.3rem; }
.form-inp { width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; margin-bottom: 0.75rem; }
.form-inp:focus { border-color: #1E88E5; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-error { display: none; background: #fee2e2; color: #7f1d1d; padding: 0.6rem; border-radius: 8px; font-size: 0.8rem; margin-bottom: 0.75rem; }
.btn-submit-wa { width: 100%; padding: 0.85rem; background: linear-gradient(135deg,#25D366,#1ebe5d); color: #fff; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }


    /* ===== CART DRAWER ===== */
    .cart-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.45);
        z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s;
    }
    .cart-overlay.open { opacity: 1; pointer-events: all; }
    .cart-drawer {
        position: fixed; top: 0; right: -420px; width: 420px; max-width: 100vw;
        height: 100vh; background: white; z-index: 2001;
        display: flex; flex-direction: column;
        box-shadow: -8px 0 40px rgba(0,0,0,0.15);
        transition: right 0.35s cubic-bezier(.4,0,.2,1);
    }
    .cart-drawer.open { right: 0; }
    .cart-header {
        background: linear-gradient(135deg, #0D47A1, #1E88E5);
        padding: 1.25rem 1.5rem; color: white;
        display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    }
    .cart-header h2 { font-size: 1.1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .cart-close { background: rgba(255,255,255,0.2); border: none; color: white; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
    .cart-close:hover { background: rgba(255,255,255,0.35); }
    .cart-items { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }
    .cart-empty { text-align: center; padding: 3rem 1rem; color: #9ca3af; }
    .cart-empty i { font-size: 3rem; display: block; margin-bottom: 0.75rem; }
    .cart-item {
        display: flex; gap: 0.75rem; align-items: flex-start;
        padding: 0.85rem 0; border-bottom: 1px solid #f3f4f6;
    }
    .cart-item-img {
        width: 52px; height: 52px; border-radius: 10px; flex-shrink: 0;
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .cart-item-img img { width: 100%; height: 100%; object-fit: cover; }
    .cart-item-info { flex: 1; min-width: 0; }
    .cart-item-name { font-size: 0.85rem; font-weight: 700; color: #1f2937; margin-bottom: 0.2rem; line-height: 1.3; }
    .cart-item-price { font-size: 0.82rem; color: #1E88E5; font-weight: 700; }
    .cart-item-qty { display: flex; align-items: center; gap: 0.4rem; margin-top: 0.4rem; }
    .qty-btn { width: 26px; height: 26px; border-radius: 6px; border: 1.5px solid #e5e7eb; background: white; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-weight: 700; color: #374151; }
    .qty-btn:hover { border-color: #1E88E5; color: #1E88E5; }
    .qty-num { font-size: 0.85rem; font-weight: 700; min-width: 20px; text-align: center; }
    .cart-item-remove { background: none; border: none; color: #d1d5db; cursor: pointer; font-size: 0.9rem; padding: 0.2rem; transition: color 0.2s; flex-shrink: 0; }
    .cart-item-remove:hover { color: #ef4444; }
    .cart-footer { padding: 1.25rem 1.5rem; border-top: 2px solid #f3f4f6; flex-shrink: 0; background: #fafbff; }
    .cart-total { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .cart-total span { font-size: 0.9rem; color: #6b7280; }
    .cart-total strong { font-size: 1.2rem; color: #1E88E5; font-weight: 800; }
    .btn-wa {
        display: flex; align-items: center; justify-content: center; gap: 0.6rem;
        width: 100%; padding: 0.85rem; background: #25D366; color: white;
        border: none; border-radius: 12px; cursor: pointer; font-weight: 700;
        font-size: 1rem; text-decoration: none; transition: all 0.3s;
    }
    .btn-wa:hover { background: #1ebe5d; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,0.4); color: white; }
    .btn-wa:disabled { background: #d1d5db; cursor: not-allowed; transform: none; box-shadow: none; }
    .btn-clear { display: block; width: 100%; padding: 0.5rem; background: none; border: none; color: #9ca3af; font-size: 0.8rem; cursor: pointer; margin-top: 0.5rem; transition: color 0.2s; }
    .btn-clear:hover { color: #ef4444; }

    /* Cart badge di navbar */
    .cart-nav-btn {
        position: relative; background: none; border: none; cursor: pointer;
        color: white; padding: 0.5rem 0.6rem; border-radius: 0.375rem;
        font-size: 1rem; display: flex; align-items: center; transition: background 0.2s;
    }
    .cart-nav-btn:hover { background: rgba(255,255,255,0.2); }
    .cart-badge {
        position: absolute; top: 2px; right: 2px;
        background: #ef4444; color: white; font-size: 0.6rem; font-weight: 800;
        width: 16px; height: 16px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        display: none;
    }

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
        .filter-bar { flex-direction: column; padding: 1rem; gap: 0.75rem; }
        .filter-group { width: 100%; min-width: unset; }
        .filter-bar > div:last-child { width: 100%; display: flex; gap: 0.5rem; }
        .btn-filter, .btn-reset { flex: 1; text-align: center; }
        .medicines-grid { grid-template-columns: repeat(2, 1fr); gap: 0.85rem; }
        .cart-drawer { width: 100%; max-width: 100%; right: -100%; }
        .cart-drawer.open { right: 0; }
    }

    @media (max-width: 480px) {
        .medicines-grid { grid-template-columns: repeat(2, 1fr); gap: 0.65rem; }
        .medicine-image { height: 120px; }
        .medicine-body { padding: 0.65rem; }
        .medicine-name { font-size: 0.82rem; }
        .medicine-price { font-size: 0.9rem; }
        .medicine-btn, .btn-cart { font-size: 0.78rem; padding: 0.5rem; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="products-header">
    <div class="container">
        <div class="breadcrumb-custom">
            <a href="<?php echo e(route('home')); ?>"><i class="fa-solid fa-house"></i> Home</a>
            <span>/</span>
            <span class="current">Produk</span>
        </div>
        <?php if($internalOrder ?? false): ?>
            <form action="<?php echo e(route('shopping.internal.logout')); ?>" method="POST" style="position:absolute;top:1rem;right:1rem;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-reset">Keluar dari belanja internal</button>
            </form>
        <?php endif; ?>
        <h1>
            <i class="fa-solid <?php echo e(($internalOrder ?? false) ? 'fa-cash-register' : 'fa-pills'); ?>"></i>
            <?php echo e(($internalOrder ?? false) ? 'Belanja Internal' : 'Katalog Produk'); ?>

        </h1>
        <p>
            <?php if($internalOrder ?? false): ?>
                Pilih produk untuk transaksi internal dan buat struk pembayaran di kasir.
            <?php else: ?>
                <?php echo e($total); ?> produk tersedia dari berbagai perusahaan farmasi terpercaya
            <?php endif; ?>
        </p>
    </div>
    <i class="fa-solid fa-pills header-deco-icon header-deco-icon-1"></i>
    <i class="fa-solid fa-capsules header-deco-icon header-deco-icon-2"></i>
    <i class="fa-solid fa-syringe header-deco-icon header-deco-icon-3"></i>
</div>

<div class="products-main">
    <div class="container">

        <form method="GET" action="<?php echo e(($internalOrder ?? false) ? route('shopping.internal') : route('products.index')); ?>" class="filter-bar">
            <?php if($kategoriPilihan ?? false): ?>
                <input type="hidden" name="kategori_pilihan" value="<?php echo e($kategoriPilihan); ?>">
            <?php endif; ?>
            <div class="filter-group" style="flex: 2; min-width: 200px;">
                <label class="filter-label"><i class="fa-solid fa-magnifying-glass"></i> Cari Produk</label>
                <input type="text" name="search" class="filter-input"
                       placeholder="Nama produk atau deskripsi..."
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
                <?php if($search || ($kategori_produk ?? '') || $perusahaan || $sort !== 'terbaru'): ?>
                    <a href="<?php echo e(($internalOrder ?? false) ? route('shopping.internal') : route('products.index')); ?>" class="btn-reset">✕ Reset</a>
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
                        <div class="medicine-image">
                            <?php echo $__env->make('partials.product-gallery-image', ['medicine' => $medicine], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                        <div class="medicine-body">
                            
                            <span class="medicine-company"><?php echo e($medicine->kategori); ?></span>
                            <h3 class="medicine-name"><a href="<?php echo e(route('medicines.show', $medicine->id)); ?>"><?php echo e($medicine->nama_obat); ?></a></h3>
                            
                            <?php
                                $priceChannel = ($internalOrder ?? false) ? 'internal' : 'b2c';
                                $basePrice = $medicine->getChannelBasePrice($priceChannel);
                                $promoPrice = $medicine->getChannelPromoPrice($priceChannel);
                                $promoPercent = $medicine->getPromoPercent($priceChannel);
                            ?>
                            <div class="medicine-price" id="product-price-<?php echo e($medicine->id); ?>">
                                <?php if($promoPercent > 0): ?><small style="display:block;color:#94a3b8;text-decoration:line-through;">Rp <?php echo e(number_format($basePrice, 0, ',', '.')); ?></small><?php endif; ?>
                                Rp <?php echo e(number_format($promoPrice, 0, ',', '.')); ?>

                            </div>
                            <?php echo $__env->make('partials.product-unit-selector', ['medicine' => $medicine, 'unitSelectId' => 'product-unit-' . $medicine->id, 'priceTargetId' => 'product-price-' . $medicine->id, 'priceChannel' => $priceChannel], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php $stock = (int) ($medicine->stock_base_units ?? $medicine->stok); ?>
                            <span class="product-stock <?php echo e($stock <= 0 ? 'empty' : ($stock <= 10 ? 'low' : 'available')); ?>">
                                <i class="fa-solid <?php echo e($stock <= 0 ? 'fa-circle-xmark' : 'fa-boxes-stacked'); ?>"></i>
                                <?php echo e($stock > 0 ? 'Stok: ' . $medicine->stock_breakdown : 'Stok habis'); ?>

                            </span>
                            <?php if($medicine->sediaan_label): ?>
                                <div class="medicine-meta" style="display:flex;align-items:center;gap:0.35rem;margin-bottom:0.6rem;">
                                    <i class="fa-solid fa-cube"></i> <span>Sediaan: <?php echo e($medicine->sediaan_label); ?></span>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo e(route('medicines.show', $medicine->id)); ?>" class="medicine-btn">
                                Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <button class="btn-cart" data-package-unit-label="<?php echo e($medicine->package_unit_label); ?>" onclick="addToCart(<?php echo e($medicine->id); ?>, '<?php echo e(addslashes($medicine->nama_obat)); ?>', <?php echo e($promoPrice); ?>, '<?php echo e($medicine->gambar ? url('storage/'.$medicine->gambar) : ''); ?>', '<?php echo e(addslashes($medicine->brand ?: $medicine->kategori)); ?>', this, 'product-unit-<?php echo e($medicine->id); ?>')">
                                <i class="fa-solid fa-cart-plus"></i> Tambah ke Keranjang
                            </button>
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
                        <a href="<?php echo e($medicines->nextPageUrl()); ?>" class="page-btn"></a>
                    <?php else: ?>
                        <span class="page-btn disabled"></span>
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
                    <a href="<?php echo e(($internalOrder ?? false) ? route('shopping.internal') : route('products.index')); ?>" class="btn-reset" style="display:inline-block;margin-top:1rem;">✕ Hapus Filter</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>window.cartSettings = { storageKey: '<?php echo e(($internalOrder ?? false) ? 'apotekmedix_cart_internal' : 'apotekmedix_cart_retail'); ?>', kioskOrder: <?php echo json_encode($internalOrder ?? false, 15, 512) ?> };</script>
<?php echo $__env->make('partials.cart', ['cartKioskOrder' => $internalOrder ?? false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\products.blade.php ENDPATH**/ ?>