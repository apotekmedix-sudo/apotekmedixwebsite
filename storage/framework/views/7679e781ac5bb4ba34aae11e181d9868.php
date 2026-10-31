

<?php $__env->startSection('title', 'Cari Dokter - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    body.public-theme main.consult-page{max-width:1160px;margin:1.5rem auto;padding:1.25rem 1rem 3rem;color:#172b4d;background:#fff!important;border-radius:12px}
    body.public-theme main.consult-page .consult-heading{margin-bottom:1rem}
    body.public-theme main.consult-page .consult-heading h1{margin:.3rem 0;color:#10243e!important;font-size:clamp(1.4rem,3vw,1.8rem)}
    body.public-theme main.consult-page .consult-muted{color:#52657e!important;line-height:1.55}
    body.public-theme main.consult-page .consult-kicker{color:#08734f!important;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
    body.public-theme main.consult-page .specialty-search{position:relative;display:block;margin:0 0 1.5rem}
    body.public-theme main.consult-page .specialty-search input{width:100%;min-height:42px;padding:.65rem 2.5rem .65rem .8rem;border:1px solid #cbd5e1;border-radius:5px;background:#fff;color:#172b4d;font-size:.9rem}
    body.public-theme main.consult-page .specialty-search input::placeholder{color:#8292a8}
    body.public-theme main.consult-page .specialty-search i{position:absolute;top:50%;right:.9rem;color:#52657e;transform:translateY(-50%)}
    body.public-theme main.consult-page .specialty-list{display:grid;grid-template-columns:repeat(9,minmax(0,1fr));gap:1.1rem .35rem;margin:.75rem 0 1.5rem}
    body.public-theme main.consult-page .specialty-list>a.specialty-chip,
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:visited{display:flex;min-height:124px;flex-direction:column;align-items:center;justify-content:flex-start;gap:.45rem;padding:.4rem .2rem;border:1px solid transparent;border-radius:10px;background:#fff!important;color:#172b4d!important;text-align:center;text-decoration:none;font-size:.82rem;font-weight:600;line-height:1.3}
    body.public-theme main.consult-page .specialty-list>a.specialty-chip>span:not(.specialty-icon){color:#172b4d!important}
    body.public-theme main.consult-page .specialty-icon{display:grid;width:52px;height:52px;flex:none;place-items:center;border-radius:50%;background:#dbe7f8!important;color:#be123c!important;font-size:1.35rem}
    body.public-theme main.consult-page .specialty-icon>i{color:#be123c!important}
    body.public-theme main.consult-page .specialty-count{color:#475569!important;font-size:.7rem;font-weight:500;line-height:1.25}
    body.public-theme main.consult-page .specialty-count.empty{color:#9a3412!important;font-weight:700}
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:hover,
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:focus-visible{border-color:#60a5fa;background:#eff6ff!important;color:#003eaa!important;outline:3px solid #bfdbfe;outline-offset:1px}
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:hover>span:not(.specialty-icon),
    body.public-theme main.consult-page .specialty-list>a.specialty-chip:focus-visible>span:not(.specialty-icon){color:#003eaa!important}
    body.public-theme main.consult-page .specialty-chip:hover .specialty-icon,
    body.public-theme main.consult-page .specialty-chip:focus-visible .specialty-icon{background:#bfdbfe!important;color:#9f1239!important}
    body.public-theme main.consult-page .specialty-chip:hover .specialty-icon>i,
    body.public-theme main.consult-page .specialty-chip:focus-visible .specialty-icon>i{color:#9f1239!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+1) .specialty-icon{background:#dbeafe!important;color:#1d4ed8!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+1) .specialty-icon>i{color:#1d4ed8!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+2) .specialty-icon{background:#ccfbf1!important;color:#0f766e!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+2) .specialty-icon>i{color:#0f766e!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+3) .specialty-icon{background:#ede9fe!important;color:#6d28d9!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+3) .specialty-icon>i{color:#6d28d9!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+4) .specialty-icon{background:#ffedd5!important;color:#c2410c!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+4) .specialty-icon>i{color:#c2410c!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+5) .specialty-icon{background:#dcfce7!important;color:#15803d!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+5) .specialty-icon>i{color:#15803d!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+6) .specialty-icon{background:#fce7f3!important;color:#be185d!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+6) .specialty-icon>i{color:#be185d!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+7) .specialty-icon{background:#cffafe!important;color:#0e7490!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n+7) .specialty-icon>i{color:#0e7490!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n) .specialty-icon{background:#fef3c7!important;color:#a16207!important}
    body.public-theme main.consult-page .specialty-list>.specialty-chip:nth-child(8n) .specialty-icon>i{color:#a16207!important}
    body.public-theme main.consult-page .specialty-selection{display:flex;align-items:center;gap:.65rem;margin-bottom:1rem}
    body.public-theme main.consult-page a.specialty-back,
    body.public-theme main.consult-page a.specialty-back:visited{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .9rem;border:1px solid #0755c8;border-radius:8px;background:#0755c8!important;color:#fff!important;font-weight:700;text-decoration:none}
    body.public-theme main.consult-page a.specialty-back>i{color:#fff!important}
    body.public-theme main.consult-page a.specialty-back:hover,
    body.public-theme main.consult-page a.specialty-back:focus-visible{border-color:#063583;background:#063583!important;color:#fff!important;text-decoration:none}
    body.public-theme main.consult-page a.specialty-back:hover>i,
    body.public-theme main.consult-page a.specialty-back:focus-visible>i{color:#fff!important}
    body.public-theme main.consult-page .doctor-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
    body.public-theme main.consult-page .doctor-card{display:grid;grid-template-columns:92px 1fr;gap:1rem;padding:1rem;border:1px solid #dbe4f0;border-radius:14px;background:#fff;box-shadow:0 8px 20px #0f172a08}
    body.public-theme main.consult-page .doctor-photo{width:92px;height:110px;object-fit:cover;border:1px solid #dbe4f0;border-radius:10px;background:#f1f5f9}
    body.public-theme main.consult-page .doctor-card h2{margin:.1rem 0;color:#10243e;font-size:1.05rem}
    body.public-theme main.consult-page .doctor-card h2 a{color:#10243e!important;text-decoration:none}
    body.public-theme main.consult-page .doctor-card h2 a:hover{text-decoration:underline;color:#0755c8!important}
    body.public-theme main.consult-page .doctor-specialty{margin:.15rem 0;color:#52657e;font-size:.86rem}
    body.public-theme main.consult-page .doctor-meta{display:flex;flex-wrap:wrap;gap:.4rem;margin:.5rem 0;color:#334155;font-size:.76rem}
    body.public-theme main.consult-page .doctor-meta span{padding:.25rem .45rem;border-radius:6px;background:#f1f5f9}
    body.public-theme main.consult-page .doctor-price{margin:.5rem 0;color:#10243e;font-size:1.05rem;font-weight:800}
    body.public-theme main.consult-page .doctor-actions{display:flex;justify-content:space-between;align-items:center;gap:.6rem}
    body.public-theme main.consult-page .consult-primary{display:inline-flex;justify-content:center;align-items:center;gap:.4rem;min-height:38px;padding:.55rem .8rem;border:0;border-radius:8px;background:#e6004c;color:#fff!important;font-weight:800;text-decoration:none}
    body.public-theme main.consult-page .doctor-empty{padding:1rem;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;color:#334155;text-align:center}
    body.public-theme main.consult-page .doctor-availability{display:inline-block;padding:.2rem .45rem;border-radius:999px;background:#dcfce7;color:#166534;font-size:.72rem;font-weight:700}
    body.public-theme main.consult-page .doctor-availability.unavailable{background:#fff1f2;color:#9f1239}
    body.public-theme main.consult-page .consult-pagination{margin-top:1rem}
    @media(max-width:900px){body.public-theme main.consult-page .specialty-list{grid-template-columns:repeat(6,minmax(0,1fr))}}
    @media(max-width:700px){body.public-theme main.consult-page .doctor-grid{grid-template-columns:1fr}body.public-theme main.consult-page .doctor-card{grid-template-columns:76px minmax(0,1fr)}body.public-theme main.consult-page .doctor-photo{width:76px;height:96px}body.public-theme main.consult-page .doctor-actions{align-items:stretch;flex-direction:column;gap:.35rem}body.public-theme main.consult-page .doctor-price{margin:.25rem 0}body.public-theme main.consult-page .doctor-actions .consult-primary{width:100%;min-height:44px;white-space:nowrap}}
    @media(max-width:480px){body.public-theme main.consult-page{margin:.75rem .5rem;padding:1rem .65rem 2rem}body.public-theme main.consult-page .specialty-list{grid-template-columns:repeat(3,minmax(0,1fr));gap:.55rem .25rem}body.public-theme main.consult-page .specialty-chip{min-height:128px;font-size:.76rem}body.public-theme main.consult-page .specialty-icon{width:48px;height:48px}}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="consult-page">
    <label class="specialty-search">
        <input id="specialty-search" type="search" placeholder="Cari dokter, spesialis atau gejala" autocomplete="off" aria-label="Cari dokter atau spesialisasi">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
    </label>
    <header class="consult-heading">
        <span class="consult-kicker">Konsultasi online · Langkah 2 dari 3</span>
        <h1>Pilih Dokter</h1>
        <p class="consult-muted">Pilih dokter yang sesuai. Status Online/Offline hanya sebagai informasi; permintaan konsultasi tetap dilanjutkan melalui WhatsApp.</p>
    </header>
    <?php if($doctors->isEmpty()): ?>
        <div class="doctor-empty">Dokter belum tersedia saat ini.</div>
    <?php else: ?>
        <section class="doctor-grid" aria-label="Daftar dokter">
            <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="doctor-card" data-search="<?php echo e(mb_strtolower($doctor->display_name . ' ' . $doctor->specialty)); ?>">
                    <?php if($doctor->photo_url): ?><img class="doctor-photo" src="<?php echo e($doctor->photo_url); ?>" alt="Foto <?php echo e($doctor->display_name); ?>"><?php else: ?><div class="doctor-photo" aria-hidden="true"></div><?php endif; ?>
                    <div>
                        <h2><a href="<?php echo e(route('consultations.doctor', $doctor)); ?>"><?php echo e($doctor->display_name); ?></a></h2>
                        <p class="doctor-specialty"><?php echo e($doctor->specialty); ?></p>
                        <div class="doctor-meta">
                            <span><i class="fa-solid fa-circle-check" style="color:#15803d"></i> STR diverifikasi</span>
                            <span><i class="fa-solid fa-briefcase"></i> <?php echo e($doctor->experience_years); ?> tahun pengalaman</span>
                            <?php if($doctor->practice_city): ?><span><i class="fa-solid fa-location-dot"></i> <?php echo e($doctor->practice_city); ?></span><?php endif; ?>
                            <span class="doctor-availability"><?php echo e($doctor->is_online ? 'Status: Online' : 'Status: Offline'); ?></span>
                        </div>
                        <div class="doctor-actions">
                            <span class="doctor-price">Rp <?php echo e(number_format($doctor->consultation_price, 0, ',', '.')); ?> <small style="font-size:.72rem;font-weight:500">/ <?php echo e($doctor->session_duration_minutes); ?> menit</small></span>
                            <a class="consult-primary" href="<?php echo e(route('consultations.payment', $doctor)); ?>"><i class="fa-solid fa-comments"></i> Chat Dokter</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>
        <p id="specialty-search-empty" class="consult-muted" hidden>Tidak ada dokter yang cocok dengan pencarian.</p>
        <div class="consult-pagination"><?php echo e($doctors->links()); ?></div>
    <?php endif; ?>
</main>
<script>
    document.getElementById('specialty-search')?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase('id');
        const doctors = [...document.querySelectorAll('.doctor-card')];
        doctors.forEach((doctor) => {
            doctor.hidden = query !== '' && !doctor.dataset.search.includes(query);
        });
        const hasDoctorMatch = doctors.some((doctor) => !doctor.hidden);
        const emptyMessage = document.getElementById('specialty-search-empty');
        if (emptyMessage) emptyMessage.hidden = query === '' || hasDoctorMatch;
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/consultations/doctors.blade.php ENDPATH**/ ?>