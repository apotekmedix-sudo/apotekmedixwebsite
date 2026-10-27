

<?php $__env->startSection('title', 'Hubungi Kami - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .contact-header {
        background: linear-gradient(135deg, #0D47A1 0%, #1565C0 50%, #1E88E5 100%);
        padding: 4rem 0;
        position: relative;
        overflow: hidden;
    }
    .contact-header::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 350px; height: 350px;
        background: radial-gradient(circle, rgba(124,179,66,0.18) 0%, transparent 70%);
        border-radius: 50%;
    }
    .contact-header .header-deco-icon {
        position: absolute;
        color: rgba(255,255,255,0.08);
        pointer-events: none;
        animation: headerIconFloat 6s ease-in-out infinite;
    }
    .contact-header .header-deco-icon-1 { bottom: 10px; right: 12%; font-size: 4rem; animation-delay: 0s; }
    .contact-header .header-deco-icon-2 { top: 15px;   right: 28%; font-size: 3rem; animation-delay: 2s; }
    .contact-header .header-deco-icon-3 { bottom: 20px; right: 40%; font-size: 2.5rem; animation-delay: 4s; }
    @keyframes headerIconFloat {
        0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.08; }
        50%       { transform: translateY(-12px) rotate(8deg); opacity: 0.14; }
    }
    .contact-header h1 { font-size: clamp(2rem,4vw,3rem); font-weight: 800; color: white; margin-bottom: 0.5rem; position: relative; }
    .contact-header p  { color: rgba(255,255,255,0.8); font-size: 1rem; position: relative; }
    .breadcrumb-custom { display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1rem; position: relative; }
    .breadcrumb-custom a { color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem; }
    .breadcrumb-custom a:hover { color: white; }
    .breadcrumb-custom span { color: rgba(255,255,255,0.5); font-size: 0.9rem; }
    .breadcrumb-custom .current { color: #a5d65a; font-size: 0.9rem; font-weight: 600; }

    .contact-main { background: #f8faff; padding: 3rem 0 5rem; }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.6fr;
        gap: 2rem;
        align-items: start;
    }

    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb;
    }

    .info-item {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        padding: 1.1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .info-item:last-child { border-bottom: none; padding-bottom: 0; }
    .info-item:first-child { padding-top: 0; }

    .info-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .icon-blue   { background: #e3f2fd; color: #1E88E5; }
    .icon-green  { background: #f0fdf4; color: #25D366; }
    .icon-orange { background: #fff7ed; color: #f59e0b; }
    .icon-purple { background: #f5f3ff; color: #7c3aed; }

    .info-text h4 { font-size: 0.875rem; font-weight: 700; color: #374151; margin-bottom: 0.25rem; }
    .info-text p, .info-text a { font-size: 0.9rem; color: #6b7280; margin: 0; line-height: 1.7; text-decoration: none; }
    .info-text a:hover { color: #1E88E5; }

    .social-row { display: flex; gap: 0.6rem; margin-top: 1.25rem; }
    .social-btn {
        width: 38px; height: 38px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        color: white; text-decoration: none; font-size: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .social-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.2); color: white; }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb;
    }

    .form-card h3 { font-size: 1.2rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }

    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%; padding: 0.65rem 0.9rem;
        border: 1.5px solid #e5e7eb; border-radius: 10px;
        font-size: 0.9rem; color: #374151; background: #f9fafb;
        transition: all 0.2s; outline: none; font-family: inherit;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #1E88E5; background: white;
        box-shadow: 0 0 0 3px rgba(30,136,229,0.1);
    }
    .form-group textarea { resize: vertical; min-height: 130px; }

    .btn-send {
        width: 100%; padding: 0.85rem;
        background: linear-gradient(135deg, #25D366, #1ebe5d);
        color: white; border: none; border-radius: 10px;
        font-size: 1rem; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 0.6rem;
        transition: all 0.3s;
    }
    .btn-send:hover { background: linear-gradient(135deg, #1ebe5d, #17a34a); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,0.35); }

    /* Dokumen Card */
    .doc-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.25rem;
    }
    .doc-card-title {
        font-size: 1rem; font-weight: 700; color: #1f2937;
        margin-bottom: 1.1rem; display: flex; align-items: center; gap: 0.5rem;
    }
    .doc-type {
        border-radius: 12px;
        padding: 1rem 1.1rem;
        margin-bottom: 0.75rem;
        border: 1px solid transparent;
    }
    .doc-type:last-child { margin-bottom: 0; }
    .doc-type-blue   { background: #eff6ff; border-color: #bfdbfe; }
    .doc-type-green  { background: #f0fdf4; border-color: #bbf7d0; }
    .doc-type-orange { background: #fff7ed; border-color: #fed7aa; }
    .doc-type-header {
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 0.875rem; font-weight: 700; margin-bottom: 0.55rem;
    }
    .doc-type-blue   .doc-type-header { color: #1d4ed8; }
    .doc-type-green  .doc-type-header { color: #15803d; }
    .doc-type-orange .doc-type-header { color: #b45309; }
    .doc-type-icon {
        width: 28px; height: 28px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; flex-shrink: 0;
    }
    .doc-type-blue   .doc-type-icon { background: #dbeafe; color: #1d4ed8; }
    .doc-type-green  .doc-type-icon { background: #dcfce7; color: #15803d; }
    .doc-type-orange .doc-type-icon { background: #ffedd5; color: #b45309; }
    .doc-list {
        list-style: none; padding: 0; margin: 0;
        display: flex; flex-wrap: wrap; gap: 0.4rem;
    }
    .doc-list li {
        font-size: 0.78rem; font-weight: 600; padding: 0.25rem 0.65rem;
        border-radius: 20px;
    }
    .doc-type-blue   .doc-list li { background: #dbeafe; color: #1e40af; }
    .doc-type-green  .doc-list li { background: #dcfce7; color: #166534; }
    .doc-type-orange .doc-list li { background: #ffedd5; color: #92400e; }

    /* Map */
    .map-section { margin-top: 2.5rem; }
    .map-section h3 { font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .map-wrap { position:relative; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); border:1px solid #e5e7eb; }
    .map-directions { position:absolute; top:10px; left:10px; z-index:1; margin:0; }
    .map-directions-link {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:.55rem;
        min-height:36px;
        padding:.5rem .75rem;
        border:1px solid #dadce0;
        border-radius:6px;
        background:#fff;
        color:#0052cc;
        font-size:.85rem;
        font-weight:600;
        line-height:1.4;
        text-decoration:none;
        box-shadow:0 1px 4px rgba(0,0,0,.2);
        transition:background .2s,border-color .2s;
    }
    .map-directions-link:hover {
        background:#f8faff;
        border-color:#c6c9cc;
        color:#003eaa;
    }
    .map-directions-link:focus-visible {
        outline:3px solid #93c5fd;
        outline-offset:2px;
    }

    @media (max-width: 768px) {
        .contact-main { padding: 1.75rem 0 3rem; }
        .contact-grid { grid-template-columns: 1fr; gap: 1.25rem; }
        .form-row { grid-template-columns: 1fr; gap: 0; }
        .form-card { padding: 1.25rem; }
        .info-card { padding: 1.25rem; }
    }

    @media (max-width: 480px) {
        .form-group textarea { min-height: 100px; }
        .map-wrap iframe { height: 240px; }
        .social-btn { width: 34px; height: 34px; font-size: 0.9rem; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="contact-header">
    <div class="container">
        <div class="breadcrumb-custom">
            <a href="<?php echo e(route('home')); ?>"><i class="fa-solid fa-house"></i> Home</a>
            <span>/</span>
            <span class="current">Hubungi Kami</span>
        </div>
        <h1><i class="fa-solid fa-headset"></i> Hubungi Kami</h1>
        <p>Kami siap membantu Anda — hubungi melalui WhatsApp, telepon, atau email</p>
    </div>
    <i class="fa-solid fa-headset header-deco-icon header-deco-icon-1"></i>
    <i class="fa-solid fa-phone header-deco-icon header-deco-icon-2"></i>
    <i class="fa-solid fa-envelope header-deco-icon header-deco-icon-3"></i>
</div>

<div class="contact-main">
    <div class="container">
        <div class="contact-grid">

            
            <div>
                
                <div class="info-card">
                    <div class="info-item">
                        <div class="info-icon icon-blue"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="info-text">
                            <h4>Alamat</h4>
                            <p>Jl. Raya Ciawi Prapatan No.256 C, RT.04/RW.04, Sindangsari, Kec. Bogor Timur, Kota Bogor, Jawa Barat 16146</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon icon-green"><i class="fa-brands fa-whatsapp"></i></div>
                        <div class="info-text">
                            <h4>WhatsApp</h4>
                            <a href="https://wa.me/6285890007359" target="_blank" rel="noopener">0858 9000 7359</a>
                            <p style="margin-top:0.2rem;font-size:0.8rem;">Klik untuk chat langsung</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon icon-orange"><i class="fa-solid fa-phone"></i></div>
                        <div class="info-text">
                            <h4>Telepon</h4>
                            <a href="tel:+6285890007359">0858 9000 7359</a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon icon-orange"><i class="fa-solid fa-clock"></i></div>
                        <div class="info-text">
                            <h4>Jam Operasional</h4>
                            <p>Senin–Sabtu: 08.00–20.00<br>Minggu: Tutup</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon icon-blue"><i class="fa-solid fa-globe"></i></div>
                        <div class="info-text">
                            <h4>Situs Web</h4>
                            <a href="https://apotekmedix.com" target="_blank" rel="noopener">apotekmedix.com</a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon icon-purple"><i class="fa-solid fa-envelope"></i></div>
                        <div class="info-text">
                            <h4>Email</h4>
                            <a href="mailto:apotekmedix@gmail.com">apotekmedix@gmail.com</a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon icon-purple"><i class="fa-solid fa-location-crosshairs"></i></div>
                        <div class="info-text">
                            <h4>Koordinat Lokasi</h4>
                            <a href="https://www.google.com/maps?q=-6.6456763,106.8409757" target="_blank" rel="noopener">-6.6456763, 106.8409757</a>
                            <p style="margin-top:0.2rem;font-size:0.8rem;">Plus Code: 9R3R+P9 Sindangsari, Kota Bogor</p>
                        </div>
                    </div>
                </div>

                <div class="contact-social-card" style="margin-top:1.25rem; background:white; border-radius:16px; padding:1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border:1px solid #e5e7eb;">
                    <h4 class="contact-social-title" style="font-size:0.9rem;font-weight:700;color:#374151;margin-bottom:0.75rem;"><i class="fa-solid fa-share-nodes" style="color:#1E88E5;margin-right:0.4rem;"></i> Ikuti Kami</h4>
                    <div class="social-row">
                        <a href="#" class="social-btn" style="background:#1877f2;" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/apotekmedix" target="_blank" rel="noopener" class="social-btn" style="background:linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://wa.me/6285890007359" target="_blank" rel="noopener" class="social-btn" style="background:#25D366;" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="https://pin.it/69pbGoR2f" target="_blank" rel="noopener" class="social-btn" style="background:#E60023;" title="Pinterest"><i class="fa-brands fa-pinterest-p"></i></a>
                        <a href="https://www.youtube.com/@apotekmedix" target="_blank" rel="noopener" class="social-btn" style="background:#FF0000;" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

            </div>

            
            <div>
                <div class="form-card">
                    <h3><i class="fa-solid fa-paper-plane" style="color:#1E88E5;margin-right:0.5rem;"></i> Kirim Pesan via WhatsApp</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap *</label>
                            <input type="text" id="nama" placeholder="Nama Anda">
                        </div>
                        <div class="form-group">
                            <label for="telepon">Nomor Telepon</label>
                            <input type="tel" id="telepon" placeholder="08xx-xxxx-xxxx">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subjek">Subjek *</label>
                        <select id="subjek">
                            <option value="">-- Pilih subjek --</option>
                            <option value="Pertanyaan Pemesanan">Pertanyaan Pemesanan</option>
                            <option value="Pertanyaan Produk">Pertanyaan Produk</option>
                            <option value="Masalah Pengiriman">Masalah Pengiriman</option>
                            <option value="Kerjasama">Kerjasama</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pesan">Pesan *</label>
                        <textarea id="pesan" placeholder="Tuliskan pesan Anda di sini..."></textarea>
                    </div>

                    <p id="formError" style="color:#ef4444;font-size:0.85rem;margin-bottom:0.75rem;display:none;">
                        <i class="fa-solid fa-circle-exclamation"></i> Nama, subjek, dan pesan wajib diisi.
                    </p>

                    <button class="btn-send" onclick="kirimWA()">
                        <i class="fa-brands fa-whatsapp" style="font-size:1.2rem;"></i> Kirim via WhatsApp
                    </button>
                </div>

                
                <div class="map-section">
                    <h3><i class="fa-solid fa-map-location-dot" style="color:#1E88E5;"></i> Apotek Medix di Google Maps</h3>
                    <div class="map-wrap">
                        <p class="map-directions">
                            <a class="map-directions-link" href="https://www.google.com/maps/place/Apotek+Medix/@-6.6456763,106.8409757,17z/" target="_blank" rel="noopener">
                                Buka di Maps <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </a>
                        </p>
                        <iframe
                            src="https://www.google.com/maps?q=-6.6456763,106.8409757&z=17&output=embed"
                            width="100%" height="320" style="border:0;display:block;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function kirimWA() {
    const nama   = document.getElementById('nama').value.trim();
    const telp   = document.getElementById('telepon').value.trim();
    const subjek = document.getElementById('subjek').value;
    const pesan  = document.getElementById('pesan').value.trim();
    const errEl  = document.getElementById('formError');

    if (!nama || !subjek || !pesan) {
        errEl.style.display = 'block';
        return;
    }
    errEl.style.display = 'none';

    const teks =
`Halo Apotek Medix!

👤 *Nama*    : ${nama}
📱 *Telepon* : ${telp || '-'}
📌 *Subjek*  : ${subjek}

💬 *Pesan*:
${pesan}`;

    window.open('https://wa.me/6285890007359?text=' + encodeURIComponent(teks), '_blank', 'noopener');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/contact.blade.php ENDPATH**/ ?>