


<?php $__env->startSection('title', 'Tentang Kami - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
/* ===== HERO ===== */
.about-hero {
    position: relative;
    min-height: 440px;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #0D47A1 0%, #1565C0 55%, #1E88E5 100%);
}
.about-hero-bg {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 85% 20%, rgba(255,255,255,.12), transparent 32%);
}
.about-hero-content {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 5rem 0 4rem;
}
.about-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(30,136,229,.25);
    border: 1px solid rgba(30,136,229,.55);
    color: #90caf9;
    padding: .45rem 1.1rem;
    border-radius: 999px;
    font-size: .85rem;
    font-weight: 700;
    margin-bottom: 1.2rem;
    backdrop-filter: blur(4px);
}
.about-logo {
    display: block;
    width: min(180px, 45vw);
    max-height: 100px;
    object-fit: contain;
    padding: .65rem;
    margin-bottom: 1.5rem;
    border-radius: 14px;
    background: #fff;
}
.about-hero h1 {
    font-size: clamp(2.2rem, 5vw, 3.4rem);
    font-weight: 900;
    color: #fff;
    line-height: 1.08;
    margin-bottom: 1.1rem;
}
.about-hero h1 .accent { color: #64b5f6; }
.about-hero p {
    color: rgba(255,255,255,.82);
    font-size: 1.05rem;
    max-width: 600px;
    line-height: 1.8;
    margin-bottom: 2rem;
}
.hero-btns { display: flex; gap: .75rem; flex-wrap: wrap; }
.btn-hero-primary {
    display: inline-flex; align-items: center; gap: .5rem;
    background: #1E88E5; color: #fff; padding: .85rem 1.8rem;
    border-radius: 50px; text-decoration: none; font-weight: 700;
    font-size: .95rem; transition: all .25s;
    box-shadow: 0 8px 24px rgba(30,136,229,.45);
}
.btn-hero-primary:hover { background: #1565C0; transform: translateY(-2px); color:#fff; }
.btn-hero-outline {
    display: inline-flex; align-items: center; gap: .5rem;
    background: transparent; color: #fff; padding: .85rem 1.6rem;
    border-radius: 50px; text-decoration: none; font-weight: 700;
    font-size: .95rem; border: 2px solid rgba(255,255,255,.45);
    transition: all .25s;
}
.btn-hero-outline:hover { background: rgba(255,255,255,.12); border-color: #fff; color:#fff; }

/* ===== STATS STRIP ===== */
.stats-strip {
    background: #0D47A1;
    padding: 1.5rem 0;
}
.stats-strip-inner {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
}
.stat-strip-item {
    text-align: center;
    padding: .75rem 1rem;
    border-right: 1px solid rgba(255,255,255,.15);
}
.stat-strip-item:last-child { border-right: none; }
.stat-strip-item .num {
    font-size: 2rem; font-weight: 900; color: #fff; display: block; line-height: 1.1;
}
.stat-strip-item .lbl { font-size: .8rem; color: rgba(255,255,255,.7); font-weight: 500; }

/* ===== SECTION COMMON ===== */
.section-tag {
    display: inline-flex; align-items: center; gap: .45rem;
    background: #e3f2fd; color: #1565C0;
    padding: .45rem 1rem; border-radius: 999px;
    font-size: .82rem; font-weight: 700; margin-bottom: 1rem;
}
.section-title {
    font-size: clamp(1.65rem, 3vw, 2.2rem);
    font-weight: 800; color: #1f2937; line-height: 1.1;
    margin-bottom: .9rem;
}
.section-lead { font-size: 1rem; color: #4b5563; line-height: 1.8; }

/* ===== WHO WE ARE ===== */
.who-section { padding: 5rem 0; background: #f8faff; }
.who-grid { display: grid; grid-template-columns: 1fr; gap: 3.5rem; align-items: center; max-width: 900px; margin: 0 auto; }
.who-checks { margin: 1.5rem 0; display: flex; flex-direction: column; gap: .65rem; }
.who-check {
    display: flex; align-items: flex-start; gap: .65rem;
    font-size: .92rem; color: #374151; line-height: 1.6;
}
.who-check i { color: #1E88E5; margin-top: 3px; flex-shrink: 0; font-size: .9rem; }

/* ===== VISI MISI ===== */
.vm-section { padding: 5rem 0; background: #f0f7ff; }
.vm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2.5rem; }
.vm-card {
    border-radius: 24px; padding: 2.5rem;
    position: relative; overflow: hidden;
}
.vm-card-visi {
    background: linear-gradient(135deg, #0D47A1 0%, #1565C0 60%, #1E88E5 100%);
    color: white;
}
.vm-card-misi {
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 60%, #43a047 100%);
    color: white;
}
.vm-card::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,.08);
    border-radius: 50%;
}
.vm-card .vm-icon {
    width: 60px; height: 60px; background: rgba(255,255,255,.18);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: #fff; margin-bottom: 1.25rem;
}
.vm-card h3 { font-size: 1.3rem; font-weight: 800; margin-bottom: .9rem; color: #fff; }
.vm-card p  { color: rgba(255,255,255,.88); font-size: .95rem; line-height: 1.8; margin: 0; }

/* ===== LAYANAN ===== */
.layanan-section { padding: 5rem 0; background: #fff; }
.layanan-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-top: 2.5rem; }
.layanan-card {
    border-radius: 20px; overflow: hidden; border: 1px solid #e5e7eb;
    box-shadow: 0 4px 20px rgba(0,0,0,.06); transition: all .3s;
}
.layanan-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(30,136,229,.15); border-color: #93c5fd; }
.layanan-card-body { padding: 1.5rem; }
.layanan-icon {
    width: 46px; height: 46px; border-radius: 12px;
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.1rem; margin-bottom: .9rem;
}
.layanan-card-body h4 { font-size: 1.05rem; font-weight: 700; color: #1f2937; margin-bottom: .5rem; }
.layanan-card-body p  { font-size: .88rem; color: #6b7280; line-height: 1.65; margin: 0; }

/* ===== NILAI ===== */
.nilai-section { padding: 5rem 0; background: #f8faff; }
.nilai-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-top: 2.5rem; }
.nilai-card {
    text-align: center; padding: 2.25rem 1.5rem;
    border-radius: 20px; border: 1px solid #e5e7eb;
    background: white; transition: all .3s;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
}
.nilai-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(30,136,229,.12); border-color: #93c5fd; }
.nilai-icon {
    width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 1.25rem;
    display: flex; align-items: center; justify-content: center; font-size: 1.7rem;
}
.nilai-icon-red   { background: #fef2f2; color: #ef4444; }
.nilai-icon-blue  { background: #eff6ff; color: #1E88E5; }
.nilai-icon-green { background: #f0fdf4; color: #22c55e; }
.nilai-card h4 { font-size: 1rem; font-weight: 700; color: #1f2937; margin-bottom: .5rem; }
.nilai-card p  { font-size: .87rem; color: #6b7280; line-height: 1.65; margin: 0; }

/* ===== CTA ===== */
.cta-section {
    position: relative; padding: 5rem 0;
    overflow: hidden;
}
.cta-bg {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, #0D47A1, #1565C0 60%, #1E88E5);
}
.cta-content {
    position: relative; z-index: 2; text-align: center;
}
.cta-content h2 { font-size: clamp(1.75rem,3.5vw,2.5rem); font-weight: 800; color: #fff; margin-bottom: .75rem; }
.cta-content p  { color: rgba(255,255,255,.8); font-size: 1rem; margin-bottom: 2rem; max-width: 500px; margin-left: auto; margin-right: auto; }
.btn-cta-wa {
    display: inline-flex; align-items: center; gap: .6rem;
    background: #25D366; color: #fff; padding: 1rem 2.25rem;
    border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 1rem;
    transition: all .3s; box-shadow: 0 10px 28px rgba(37,211,102,.45); margin: .35rem;
}
.btn-cta-wa:hover { background: #1ebe5d; transform: translateY(-3px); color:#fff; }
.btn-cta-produk {
    display: inline-flex; align-items: center; gap: .6rem;
    background: rgba(255,255,255,.12); color: #fff; padding: 1rem 2rem;
    border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 1rem;
    border: 2px solid rgba(255,255,255,.4); transition: all .3s; margin: .35rem;
    backdrop-filter: blur(4px);
}
.btn-cta-produk:hover { background: rgba(255,255,255,.22); border-color: #fff; color:#fff; }

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .vm-grid { grid-template-columns: 1fr; }
    .layanan-grid { grid-template-columns: 1fr; }
    .nilai-grid { grid-template-columns: 1fr 1fr; }
    .stats-strip-inner { grid-template-columns: repeat(2, 1fr); }
    .stat-strip-item:nth-child(2) { border-right: none; }
    .stat-strip-item:nth-child(3) { border-top: 1px solid rgba(255,255,255,.15); }
}
@media (max-width: 600px) {
    .about-hero { min-height: 420px; }
    .about-hero h1 { font-size: 1.85rem; }
    .hero-btns { flex-direction: column; }
    .hero-btns a { width: 100%; justify-content: center; }
    .nilai-grid { grid-template-columns: 1fr; }
    .mitra-grid { gap: 1.5rem; }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<section class="about-hero">
    <div class="about-hero-bg"></div>
    <div class="about-hero-content">
        <div class="container">
            <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Logo Apotek Medix" class="about-logo">
            <div class="about-hero-badge">
                <i class="fa-solid fa-award"></i> Terpercaya Sejak 2016
            </div>
            <h1>Kesehatan Anda,<br>Prioritas <span class="accent">Apotek Medix</span></h1>
            <p>Distributor farmasi & apotik online yang menjadi mitra strategis bagi praktisi kesehatan dan masyarakat Indonesia — produk berkualitas, harga terjangkau, layanan 24/7.</p>
            <div class="hero-btns">
                <a href="<?php echo e(route('products.index')); ?>" class="btn-hero-primary">
                    <i class="fa-solid fa-cart-shopping"></i> Lihat Produk Kami
                </a>
                <a href="<?php echo e(route('contact')); ?>" class="btn-hero-outline">
                    <i class="fa-solid fa-headset"></i> Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</section>


<div class="stats-strip">
    <div class="container">
        <div class="stats-strip-inner">
            <div class="stat-strip-item">
                <span class="num">2016</span>
                <span class="lbl">Mulai Melayani</span>
            </div>
            <div class="stat-strip-item">
                <span class="num">100+</span>
                <span class="lbl">Brand Partner</span>
            </div>
            <div class="stat-strip-item">
                <span class="num">50+</span>
                <span class="lbl">Kota Jangkauan</span>
            </div>
            <div class="stat-strip-item">
                <span class="num">24/7</span>
                <span class="lbl">Layanan Aktif</span>
            </div>
        </div>
    </div>
</div>


<section class="who-section">
    <div class="container">
        <div class="who-grid">
            <div class="who-text">
                <div class="section-tag"><i class="fa-solid fa-building"></i> Profil Perusahaan</div>
                <h2 class="section-title">Distributor & Apotik<br>Terpercaya di Indonesia</h2>
                <p class="section-lead">
                    <strong>APOTEK MEDIX</strong> hadir sebagai solusi distribusi dan apotek kesehatan yang menjembatani kebutuhan masyarakat dengan para praktisi kesehatan — RS, Apotek, Dokter, Bidan, Klinik — untuk kebutuhan medis berkualitas dengan harga terjangkau.
                </p>
                <p class="section-lead" style="margin-top:.75rem;">
                    Sejak 2016, kami mengelola rantai distribusi secara mandiri melalui kerjasama dengan PBF lokal dan nasional untuk memastikan keamanan dan keaslian produk hingga ke tangan mitra dan masyarakat.
                </p>
                <div class="who-checks">
                    <div class="who-check"><i class="fa-solid fa-circle-check"></i><span><strong>Distributor Resmi</strong> — izin lengkap BPOM & Kementerian Kesehatan</span></div>
                    <div class="who-check"><i class="fa-solid fa-circle-check"></i><span><strong>Produk Original</strong> — tersertifikasi dan berstandar GMP internasional</span></div>
                    <div class="who-check"><i class="fa-solid fa-circle-check"></i><span><strong>Jaringan Luas</strong> — distribusi ke 50+ kota di seluruh Indonesia</span></div>
                    <div class="who-check"><i class="fa-solid fa-circle-check"></i><span><strong>Harga Kompetitif</strong> — langsung dari distributor tanpa markup berlebih</span></div>
                    <div class="who-check"><i class="fa-solid fa-circle-check"></i><span><strong>Apotik Online 24/7</strong> — konsultasi dan pemesanan sepanjang waktu</span></div>
                    <div class="who-check"><i class="fa-solid fa-circle-check"></i><span><strong>Konsultasi Profesional</strong> — tim apoteker berpengalaman siap membantu</span></div>
                </div>
                <div style="padding:.85rem 1.1rem;background:#eff6ff;border-radius:12px;border-left:4px solid #1E88E5;font-size:.88rem;color:#374151;">
                    <i class="fa-solid fa-envelope" style="color:#1E88E5;margin-right:.4rem;"></i>
                    <strong>Email:</strong>
                    <a href="mailto:apotekmedix@gmail.com" style="color:#1E88E5;text-decoration:none;font-weight:600;">apotekmedix@gmail.com</a>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="vm-section">
    <div class="container">
        <div style="text-align:center;">
            <div class="section-tag" style="background:#e8f5e9;color:#2e7d32;"><i class="fa-solid fa-bullseye"></i> Arah & Tujuan</div>
            <h2 class="section-title">Visi & Misi Kami</h2>
            <p class="section-lead" style="max-width:560px;margin:0 auto;">Landasan yang memandu setiap langkah kami dalam melayani masyarakat Indonesia.</p>
        </div>
        <div class="vm-grid">
            <div class="vm-card vm-card-visi">
                <div class="vm-icon"><i class="fa-solid fa-eye"></i></div>
                <h3>Visi</h3>
                <p>Menjadi distributor farmasi dan apotik online terpercaya yang terdepan dalam memberikan solusi kesehatan berkualitas, terjangkau, dan mudah diakses untuk seluruh masyarakat Indonesia.</p>
            </div>
            <div class="vm-card vm-card-misi">
                <div class="vm-icon"><i class="fa-solid fa-rocket"></i></div>
                <h3>Misi</h3>
                <p>Mendistribusikan produk farmasi berkualitas tinggi melalui jaringan yang luas, menyediakan layanan apotik online 24/7 dengan konsultasi profesional, dan memastikan akses kesehatan yang merata di seluruh Indonesia.</p>
            </div>
        </div>
    </div>
</section>


<section class="layanan-section">
    <div class="container">
        <div style="text-align:center;">
            <div class="section-tag"><i class="fa-solid fa-star"></i> Layanan Kami</div>
            <h2 class="section-title">Apa yang Kami Tawarkan</h2>
            <p class="section-lead" style="max-width:520px;margin:0 auto;">Solusi kesehatan lengkap — dari distribusi hingga layanan konsultasi profesional.</p>
        </div>
        <div class="layanan-grid">
            <div class="layanan-card">
                <div class="layanan-card-body">
                    <div class="layanan-icon"><i class="fa-solid fa-truck"></i></div>
                    <h4>Distributor Resmi</h4>
                    <p>Kami adalah distributor resmi berbagai brand farmasi ternama dengan izin lengkap dari BPOM dan Kementerian Kesehatan RI.</p>
                </div>
            </div>
            <div class="layanan-card">
                <div class="layanan-card-body">
                    <div class="layanan-icon"><i class="fa-solid fa-store"></i></div>
                    <h4>Apotik Online 24/7</h4>
                    <p>Layanan apotik online dengan konsultasi gratis dari apoteker berpengalaman dan pengiriman ke seluruh Indonesia.</p>
                </div>
            </div>
            <div class="layanan-card">
                <div class="layanan-card-body">
                    <div class="layanan-icon"><i class="fa-solid fa-user-doctor"></i></div>
                    <h4>Konsultasi Profesional</h4>
                    <p>Tim apoteker dan tenaga kesehatan berpengalaman siap membantu Anda 24 jam sehari, 7 hari seminggu via WhatsApp.</p>
                </div>
            </div>
            <div class="layanan-card">
                <div class="layanan-card-body">
                    <div class="layanan-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h4>Kualitas Terjamin</h4>
                    <p>Semua produk tersimpan dalam kondisi optimal, memiliki sertifikat halal dan standar cold chain untuk produk yang membutuhkan.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="nilai-section">
    <div class="container">
        <div style="text-align:center;">
            <div class="section-tag" style="background:#fdf4ff;color:#7e22ce;"><i class="fa-solid fa-gem"></i> Nilai Kami</div>
            <h2 class="section-title">Yang Kami Pegang Teguh</h2>
            <p class="section-lead" style="max-width:480px;margin:0 auto;">Nilai-nilai ini menjadi kompas dalam setiap keputusan dan pelayanan kami.</p>
        </div>
        <div class="nilai-grid">
            <div class="nilai-card">
                <div class="nilai-icon nilai-icon-red"><i class="fa-solid fa-heart"></i></div>
                <h4>Kepercayaan</h4>
                <p>Produk asli dan layanan yang jujur selalu menjadi prioritas kami. Kepercayaan Anda adalah aset terbesar yang kami jaga.</p>
            </div>
            <div class="nilai-card">
                <div class="nilai-icon nilai-icon-blue"><i class="fa-solid fa-shield-halved"></i></div>
                <h4>Kualitas</h4>
                <p>Seleksi ketat dan sertifikasi farmasi lengkap untuk setiap produk. Tidak ada kompromi dalam hal mutu dan keamanan.</p>
            </div>
            <div class="nilai-card">
                <div class="nilai-icon nilai-icon-green"><i class="fa-solid fa-handshake"></i></div>
                <h4>Kemanusiaan</h4>
                <p>Kesehatan adalah hak semua orang. Kami hadir untuk semua kalangan — dari masyarakat umum hingga praktisi kesehatan.</p>
            </div>
        </div>
    </div>
</section>


<section class="cta-section">
    <div class="cta-bg"></div>
    <div class="container">
        <div class="cta-content">
            <div class="section-tag" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);margin-bottom:1.25rem;">
                <i class="fa-solid fa-headset"></i> Siap Membantu Anda
            </div>
            <h2>Ada Pertanyaan?<br>Hubungi Kami Sekarang</h2>
            <p>Tim kami siap membantu Anda menemukan produk yang tepat dan menjawab setiap pertanyaan Anda kapan saja.</p>
            <div>
                <a href="https://wa.me/6285882717844" target="_blank" rel="noopener" class="btn-cta-wa">
                    <i class="fa-brands fa-whatsapp" style="font-size:1.2rem;"></i> Chat via WhatsApp
                </a>
                <a href="<?php echo e(route('contact')); ?>" class="btn-cta-produk">
                    <i class="fa-solid fa-envelope"></i> Halaman Kontak
                </a>
            </div>
        </div>
    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/about.blade.php ENDPATH**/ ?>