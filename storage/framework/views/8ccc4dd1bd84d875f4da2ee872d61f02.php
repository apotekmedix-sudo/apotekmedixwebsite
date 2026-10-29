
<?php $__env->startSection('title', 'Apotek Medix - Apotik Online Terpercaya'); ?>
<?php $__env->startSection('styles'); ?>
<style>
/* ==============================================
   HOME PAGE - Clean GoApotik Style
   ============================================== */
.home-brand-hero {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    max-width: 1200px;
    min-height: 340px;
    margin: 1.5rem auto 2rem;
    padding: 2.5rem clamp(1.5rem, 4vw, 3rem);
    overflow: hidden;
    border-radius: 18px;
    color: #fff;
    background:
        radial-gradient(ellipse at 84% 18%, rgba(8, 217, 232, 0.28), transparent 37%),
        radial-gradient(ellipse at 12% 110%, rgba(19, 220, 130, 0.16), transparent 42%),
        linear-gradient(112deg, #063d9f 0%, #0754bd 60%, #0875c9 100%);
    box-shadow: 0 18px 42px rgba(3, 34, 89, 0.2);
}
.home-brand-hero::before {
    position: absolute;
    top: -200px;
    right: 5%;
    width: 650px;
    height: 650px;
    border: 1px solid rgba(255, 255, 255, 0.17);
    border-radius: 50%;
    content: "";
}
.home-brand-hero::after {
    position: absolute;
    right: 10%;
    bottom: -220px;
    width: 480px;
    height: 480px;
    border: 1px solid rgba(255, 255, 255, 0.13);
    border-radius: 50%;
    box-shadow: 0 0 0 36px rgba(255,255,255,0.035), 0 0 0 72px rgba(255,255,255,0.025);
    content: "";
}
.home-brand-hero-copy { position: relative; z-index: 1; width: min(100%, 700px); }
.home-brand-hero-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
    color: #7fffe2;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.home-brand-hero h1 {
    margin: 0;
    color: #fff;
    font-size: clamp(2rem, 4.1vw, 3.45rem);
    font-weight: 800;
    line-height: 1.08;
    letter-spacing: -0.03em;
}
.home-brand-hero p {
    max-width: 600px;
    margin: 0.85rem 0 1.25rem;
    color: rgba(255,255,255,0.9);
    font-size: clamp(1rem, 1.6vw, 1.25rem);
    line-height: 1.5;
}
.home-hero-chat-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    margin-top: 1rem;
}
.home-hero-chat-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .55rem;
    min-height: 46px;
    padding: .7rem 1.1rem;
    border: 1px solid rgba(255,255,255,.9);
    border-radius: 12px;
    background: #fff;
    color: #0755c8;
    font: inherit;
    font-weight: 800;
    box-shadow: 0 6px 16px rgba(0,24,89,.2);
}
.home-hero-chat-button--pharmacist { color: #08734f; }
.home-product-search {
    display: grid;
    grid-template-columns: minmax(250px, 2fr) repeat(3, minmax(160px, 1fr)) auto;
    gap: 1rem;
    align-items: end;
    width: 100%;
    margin: 0 auto;
    padding: 1.25rem 1.5rem;
    border: 1px solid #00c8e5;
    border-radius: 24px;
    background: linear-gradient(112deg, #0754bd 0%, #0875c9 100%);
}
.home-search-section {
    position: relative;
    z-index: 2;
    padding: 1rem 0;
    background: #0754bd;
    scroll-margin-top: 96px;
}
.home-search-label {
    display: block;
    margin-bottom: .5rem;
    color: #fff;
    font-size: .95rem;
    font-weight: 800;
}
.home-search-field {
    min-width: 0;
}
.home-product-search input,
.home-product-search select {
    display: block;
    width: 100%;
    min-width: 0;
    height: 52px;
    padding: .75rem 1rem;
    border: 2px solid #cbd5e1;
    border-radius: 14px;
    background: #fff;
    color: #1e293b;
    font: inherit;
}
.home-product-search input:focus,
.home-product-search select:focus { outline: 3px solid rgba(0, 220, 238, .55); outline-offset: 1px; border-color: #00c8e5; }
.home-product-search button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    min-width: 144px;
    height: 52px;
    padding: .7rem 1.25rem;
    border: 0;
    border-radius: 14px;
    background: #07805f;
    color: #fff;
    font: inherit;
    font-weight: 800;
    cursor: pointer;
}
.home-product-search button:hover { background: #056b50; }
.home-search-clear {
    display: inline-block;
    grid-column: 1 / -1;
    margin: -.5rem 0 0 .25rem;
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
}
@media (max-width: 1050px) {
    .home-product-search {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .home-search-field:first-child {
        grid-column: 1 / -1;
    }
}
@media (max-width: 600px) {
    .home-search-section {
        padding: .5rem 0;
    }
    .home-product-search {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .5rem;
        padding: .7rem;
        border-radius: 14px;
    }
    .home-search-field:first-child {
        grid-column: 1 / -1;
    }
    .home-search-label {
        margin-bottom: .2rem;
        font-size: .78rem;
    }
    .home-product-search input,
    .home-product-search select {
        height: 42px;
        padding: .5rem .65rem;
        border-radius: 11px;
        font-size: .84rem;
    }
    .home-product-search button {
        grid-column: 2;
        grid-row: 3;
        width: 100%;
        height: 42px;
        min-width: 0;
        padding: .5rem .75rem;
        border-radius: 11px;
    }
    .home-search-clear {
        grid-column: 1 / -1;
        margin: 0;
        font-size: .78rem;
    }
}
.home-brand-hero-mark {
    position: relative;
    z-index: 1;
    display: grid;
    width: 310px;
    height: 310px;
    flex: 0 0 310px;
    place-items: center;
}
.home-brand-hero-circle {
    position: absolute;
    inset: 8%;
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.26), rgba(255,255,255,0.04) 68%);
    box-shadow: 0 0 0 16px rgba(255,255,255,0.06), 0 20px 60px rgba(0,48,74,0.16);
}
.home-brand-hero-mark img { position: relative; width: 72%; height: 72%; object-fit: contain; filter: drop-shadow(0 12px 18px rgba(0,38,68,0.2)); }
@media (max-width: 768px) {
    .home-brand-hero { min-height: 0; margin: 1rem 1rem 1.5rem; padding: 2rem 1.5rem; }
    .home-brand-hero-mark { width: 160px; height: 160px; flex-basis: 160px; }
    .home-brand-hero-copy { width: min(100%, 560px); }
    .home-brand-hero p { font-size: 1rem; }
}
@media (max-width: 480px) {
    .home-brand-hero { display: block; margin: 0.75rem 0.75rem 1.25rem; padding: 1.5rem 1rem; border-radius: 14px; }
    .home-brand-hero-mark { position: absolute; top: 0.75rem; right: 0.75rem; width: 64px; height: 64px; }
    .home-brand-hero-circle { inset: 0; }
    .home-brand-hero-mark img { width: 70%; height: 70%; }
    .home-brand-hero-label { font-size: 0.72rem; }
    .home-brand-hero h1 { max-width: 290px; padding-right: 2.1rem; font-size: 1.8rem; }
    .home-brand-hero p { max-width: 330px; font-size: 0.92rem; }
}

<style>
/* SEARCH */
.search-section { padding: 1.25rem 0 0.75rem; background: #f8faff; }
.search-box {
    display: flex; gap: 0.5rem; background: #fff;
    border-radius: 14px; padding: 0.5rem;
    box-shadow: 0 2px 16px rgba(30,136,229,0.1);
    border: 1.5px solid #e5e7eb; max-width: 700px; margin: 0 auto;
}
.search-box input {
    flex: 1; border: none; outline: none;
    padding: 0.5rem 0.75rem; font-size: 0.95rem; color: #374151; background: transparent;
}
.search-box button {
    padding: 0.55rem 1.4rem;
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    color: #fff; border: none; border-radius: 10px; cursor: pointer;
    font-weight: 700; font-size: 0.88rem; white-space: nowrap;
    display: flex; align-items: center; gap: 0.4rem; transition: all 0.2s;
}
.search-box button:hover { background: linear-gradient(135deg, #1565C0, #0D47A1); }
</style>
<style>
/* QUICK CATEGORY */
.quick-section { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 0; display: none; }
.quick-row { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 4px; }
.quick-row::-webkit-scrollbar { height: 3px; }
.quick-row::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }
.quick-btn {
    display: flex; flex-direction: column; align-items: center; gap: 0.35rem;
    padding: 0.5rem 1rem; border-radius: 12px; text-decoration: none;
    color: #374151; white-space: nowrap; flex-shrink: 0;
    border: 1.5px solid #e5e7eb; background: #fff; min-width: 75px;
    transition: all 0.2s; font-size: 0;
}
.quick-btn:hover { background: #e3f2fd; border-color: #90caf9; color: #1565C0; }
.quick-btn.active { background: linear-gradient(135deg,#1E88E5,#1565C0); border-color: transparent; color: #fff; }
.quick-btn i { font-size: 1.25rem; }
.quick-btn span { font-size: 0.7rem; font-weight: 600; }

/* PROMO CARDS */
.promo-section { 
    padding: 2.5rem 0; 
    background: linear-gradient(135deg, #0F172A 0%, #17243A 58%, #12394A 100%);
    width: 100%;
    margin: 0;
    position: relative;
}

.promo-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.03) 0%, transparent 50%);
    pointer-events: none;
}

.promo-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
    gap: 2rem;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
    justify-items: center;
    position: relative;
    z-index: 1;
}

.promo-card {
    border-radius: 24px; 
    padding: 2rem; 
    color: #fff;
    text-decoration: none; 
    display: flex; 
    flex-direction: column;
    align-items: flex-start; 
    justify-content: space-between;
    gap: 1.5rem;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); 
    position: relative; 
    overflow: hidden;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
    min-height: 200px;
    width: 100%;
    max-width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
}

.promo-card::before {
    content: ''; 
    position: absolute; 
    inset: 0; 
    background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 50%, rgba(0,0,0,0.1) 100%); 
    border-radius: 24px;
    pointer-events: none;
}

.promo-card::after {
    content: ''; 
    position: absolute; 
    right: -80px; 
    bottom: -80px;
    width: 220px; 
    height: 220px; 
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); 
    border-radius: 50%;
}

.promo-card:hover { 
    transform: translateY(-12px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.2);
}

.promo-card-content {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    width: 100%;
    position: relative;
    z-index: 2;
}

.promo-card-icon-wrap {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

  .promo-wholesale-logo {
    width: 56px;
    height: 56px;
    object-fit: contain;
  }

.promo-card-text {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex: 1;
    position: relative;
    z-index: 2;
}
.promo-1 { background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%); }
.promo-2 { background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); }
.promo-3 { background: linear-gradient(135deg, #6a1b9a 0%, #8e24aa 100%); }

.promo-contact {
    background: linear-gradient(135deg, #1565C0 0%, #1E88E5 100%);
}

.promo-wholesale {
  background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
}

.promo-goapotik {
    background: linear-gradient(135deg, #0066b3 0%, #0099ff 50%, #00d4ff 100%);
}

.promo-goapotik-logo {
    height: 80px;
    object-fit: contain;
    flex-shrink: 0;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}

.promo-card > i {
    font-size: 4rem;
    opacity: 0.95;
    flex-shrink: 0;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
}

.promo-card h4 {
    font-size: 1.5rem;
    font-weight: 900;
    margin: 0;
    line-height: 1.1;
    color: #fff;
    letter-spacing: -0.5px;
}

.promo-card p {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.92);
    margin: 0;
    font-weight: 500;
    line-height: 1.5;
    opacity: 0.95;
}

/* SECTION HEADER */
.sec-head { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.25rem; }
.sec-head-left { display: flex; flex-direction: column; gap: 0.25rem; }
.sec-tag { display: inline-block; background: #e3f2fd; color: #1565C0; padding: 0.2rem 0.75rem; border-radius: 50px; font-size: 0.72rem; font-weight: 600; }
.sec-title { font-size: 1.2rem; font-weight: 800; color: #1f2937; margin: 0; }
.sec-link { font-size: 0.82rem; color: #1E88E5; text-decoration: none; font-weight: 600; white-space: nowrap; }
.sec-link:hover { text-decoration: underline; }
</style>
<style>
/* PRODUCT GRID */
.prod-section { padding: 1.5rem 0; }
.prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(185px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.prod-card {
    background: #fff; border-radius: 14px; overflow: hidden;
    border: 1.5px solid #e5e7eb; display: flex; flex-direction: column;
    transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
}
.prod-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(30,136,229,0.12); border-color: #90caf9; }
.prod-img {
    width: 100%; height: 148px;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; position: relative;
}
.prod-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
.prod-img img.product-placeholder-logo { width: 72%; height: 72%; object-fit: contain; }
.prod-card:hover .prod-img img { transform: scale(1.06); }
.prod-img .no-img-icon { font-size: 2.5rem; color: #90caf9; }
.prod-badge-label {
    position: absolute; top: 8px; left: 8px;
    background: #1E88E5; color: #fff;
    font-size: 0.62rem; font-weight: 700; padding: 0.18rem 0.45rem; border-radius: 6px;
}
.prod-badge-grade-a {
    position: absolute; top: 8px; right: 8px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    font-size: 0.62rem; font-weight: 700; padding: 0.2rem 0.5rem;
    border-radius: 6px;
    display: inline-flex; align-items: center; gap: 0.2rem;
    box-shadow: 0 2px 6px rgba(217,119,6,.35);
}
.prod-body { padding: 0.85rem; flex: 1; display: flex; flex-direction: column; }
.prod-brand-tag {
    font-size: 0.66rem; font-weight: 700; color: #1565C0; background: #e3f2fd;
    display: inline-block; padding: 0.15rem 0.5rem; border-radius: 20px; margin-bottom: 0.4rem;
}
.prod-name {
    font-size: 0.86rem; font-weight: 700; color: #1f2937; margin-bottom: 0.4rem;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden; line-height: 1.35; flex: 1;
}
  .prod-desc { color: #374151; font-size: 0.86rem; margin: 0 0 0.45rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
  .prod-meta { font-size: 0.72rem; color: #6b7280; margin-bottom: 0.35rem; }
.prod-price { font-size: 1rem; font-weight: 800; color: #1E88E5; margin-bottom: 0.35rem; }
.stock-ok  { font-size: 0.65rem; font-weight: 600; color: #065f46; background: #d1fae5; padding: 0.15rem 0.5rem; border-radius: 20px; display: inline-block; margin-bottom: 0.6rem; }
.stock-low { font-size: 0.65rem; font-weight: 600; color: #92400e; background: #fef3c7; padding: 0.15rem 0.5rem; border-radius: 20px; display: inline-block; margin-bottom: 0.6rem; }
.stock-out { font-size: 0.65rem; font-weight: 600; color: #7f1d1d; background: #fee2e2; padding: 0.15rem 0.5rem; border-radius: 20px; display: inline-block; margin-bottom: 0.6rem; }
.btn-detail {
    display: block; width: 100%; padding: 0.5rem;
    background: linear-gradient(135deg, #1E88E5, #1565C0); color: #fff;
    border: none; border-radius: 9px; cursor: pointer; font-weight: 700;
    font-size: 0.78rem; text-align: center; text-decoration: none; transition: all 0.25s;
}
.btn-detail:hover { background: linear-gradient(135deg, #1565C0, #0D47A1); transform: translateY(-1px); color: #fff; }
.btn-cart {
    display: block; width: 100%; padding: 0.42rem;
    background: #fff; color: #1E88E5;
    border: 1.5px solid #1E88E5; border-radius: 9px; cursor: pointer;
    font-weight: 700; font-size: 0.72rem; text-align: center;
    text-decoration: none; transition: all 0.2s; margin-top: 0.4rem;
}
.btn-cart:hover { background: #e3f2fd; }
.btn-cart.added { background: #d1fae5; color: #065f46; border-color: #34d399; }
</style>
<style>
/* WHY US */
.why-section { background: #fff; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; padding: 1.25rem 0; }
.why-grid { display: flex; justify-content: center; gap: 2.5rem; flex-wrap: wrap; }
.why-item { display: flex; align-items: center; gap: 0.75rem; }
.why-icon { width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.why-text h4 { font-size: 0.84rem; font-weight: 700; color: #1f2937; margin: 0 0 0.1rem; }
.why-text p  { font-size: 0.73rem; color: #6b7280; margin: 0; line-height: 1.4; }

/* CTA */
.cta-section { padding: 1.25rem 0; }
.cta-box {
    background: linear-gradient(135deg, #0F172A, #1E293B);
    border-radius: 20px; padding: 2rem 2.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;
}
.cta-box h3 { font-size: 1.25rem; font-weight: 800; color: #fff; margin: 0 0 0.3rem; }
.cta-box p  { color: rgba(255,255,255,0.85); font-size: 0.9rem; margin: 0; }
.btn-wa {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: #25D366; color: #fff; padding: 0.75rem 1.75rem;
    border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 0.95rem;
    transition: all 0.25s; white-space: nowrap; flex-shrink: 0;
}
.btn-wa:hover { background: #1ebe5d; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,0.4); color: #fff; }

/* CATEGORY GRID */
.cat-section { padding: 1.5rem 0 1rem; }
.cat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; }
.cat-card {
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    padding: 1rem 0.5rem; background: #fff; border-radius: 14px;
    border: 1.5px solid #e5e7eb; text-decoration: none; color: #374151;
    transition: all 0.25s; text-align: center;
}
.cat-card:hover { background: #e3f2fd; border-color: #90caf9; color: #1565C0; transform: translateY(-3px); box-shadow: 0 6px 18px rgba(30,136,229,0.1); }
.cat-icon { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; }
.cat-card > span { font-size: 0.7rem; font-weight: 600; line-height: 1.3; }

/* ABOUT STRIP */
.about-strip { padding: 1rem 0 2.5rem; }
.about-box {
    background: #fff; border-radius: 20px; padding: 1.75rem 2rem;
    border: 1.5px solid #e5e7eb;
    display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}
.about-logo { height: 72px; object-fit: contain; flex-shrink: 0; }
.about-info { flex: 1; min-width: 200px; }
.about-info h3 { font-size: 1.05rem; font-weight: 800; color: #1f2937; margin: 0 0 0.4rem; }
.about-info p  { font-size: 0.85rem; color: #6b7280; line-height: 1.7; margin: 0 0 0.85rem; }
.btn-about {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: #e3f2fd; color: #1565C0; padding: 0.45rem 1.1rem;
    border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 0.82rem; transition: all 0.2s;
}
.btn-about:hover { background: #1565C0; color: #fff; }
.about-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.65rem; flex-shrink: 0; }
.about-stat-item { text-align: center; padding: 0.65rem 1rem; background: #f8faff; border-radius: 12px; border: 1px solid #e5e7eb; }
.about-stat-item .n { font-size: 1.3rem; font-weight: 800; color: #1E88E5; display: block; line-height: 1.2; }
.about-stat-item .l { font-size: 0.68rem; color: #6b7280; }
.about-stat-item:nth-child(even) .n { color: #7CB342; }

/* CART DRAWER */
.cart-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
.cart-overlay.open { opacity: 1; pointer-events: all; }
.cart-drawer { position: fixed; top: 0; right: -420px; width: 420px; max-width: 100vw; height: 100vh; background: #fff; z-index: 2001; display: flex; flex-direction: column; box-shadow: -8px 0 40px rgba(0,0,0,0.15); transition: right 0.35s cubic-bezier(.4,0,.2,1); }
.cart-drawer.open { right: 0; }
.cart-head { background: linear-gradient(135deg, #0F172A, #1E293B); padding: 1.25rem 1.5rem; color: #fff; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
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
</style>
<style>
/* ORDER MODAL */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 3000; }
.modal-box { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 92%; max-width: 480px; max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 20px; z-index: 3001; box-shadow: 0 25px 60px rgba(0,0,0,0.25); }
.modal-head { background: linear-gradient(135deg,#1565C0,#1E88E5); padding: 1.25rem 1.5rem; border-radius: 20px 20px 0 0; display: flex; justify-content: space-between; align-items: center; }
.modal-head h3 { color: #fff; margin: 0; font-size: 1rem; font-weight: 700; }
.modal-head p { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.75rem; }
.modal-close { background: rgba(255,255,255,0.2); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1rem; }
.modal-summary { padding: 1rem 1.5rem; background: #f8faff; border-bottom: 1px solid #e5e7eb; font-size: 0.85rem; color: #374151; }
.modal-form { padding: 1.25rem 1.5rem; }
.form-lbl { display: block; font-size: 0.78rem; font-weight: 700; color: #374151; margin-bottom: 0.3rem; }
.form-inp { width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; margin-bottom: 0.75rem; }
.form-inp:focus { border-color: #1E88E5; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-error { display: none; background: #fee2e2; color: #7f1d1d; padding: 0.6rem; border-radius: 8px; font-size: 0.8rem; margin-bottom: 0.75rem; }
.btn-submit-wa { width: 100%; padding: 0.85rem; background: linear-gradient(135deg,#25D366,#1ebe5d); color: #fff; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }

/* RESPONSIVE */
@media (max-width: 992px) {
    .cat-grid { grid-template-columns: repeat(4,1fr); }
    .why-grid { gap: 1.5rem; }
    .hero-img-wrap { display: none; }
    .promo-section { padding: 2.2rem 0; }
    .promo-grid { gap: 1.75rem; }
    .promo-card { min-height: 190px; padding: 1.8rem; }
}
@media (max-width: 768px) {
    .hero { padding: 2rem 0 1.75rem; }
    .promo-grid { grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .promo-card { padding: 1.75rem 1.5rem; min-height: 180px; }
    .promo-card-icon-wrap { width: 70px; height: 70px; border-radius: 16px; }
    .promo-card > i { font-size: 3rem; }
    .promo-goapotik-logo { height: 70px; }
    .promo-card h4 { font-size: 1.2rem; }
    .promo-card p { font-size: 0.9rem; }
    .cat-grid { grid-template-columns: repeat(3,1fr); }
    .prod-grid { grid-template-columns: repeat(2,1fr); }
    .cta-box { flex-direction: column; text-align: center; padding: 1.5rem; }
    .stats-strip-row { flex-wrap: wrap; }
    .stat-cell { min-width: 50%; border-bottom: 1px solid #e5e7eb; }
    .cart-drawer { width: 100%; max-width: 100%; right: -100%; }
    .cart-drawer.open { right: 0; }
    .about-box { flex-direction: column; align-items: flex-start; }
    .about-stats { width: 100%; grid-template-columns: repeat(4,1fr); }
}
@media (max-width: 480px) {
    .promo-grid { grid-template-columns: 1fr; gap: 0.75rem; padding: 0 0.5rem; }
    .promo-card { padding: 1.5rem 1.25rem; min-height: 160px; }
    .promo-card-icon-wrap { width: 60px; height: 60px; border-radius: 14px; }
    .promo-card > i { font-size: 2.5rem; }
    .promo-goapotik-logo { height: 60px; }
    .promo-card h4 { font-size: 1rem; }
    .promo-card p { font-size: 0.85rem; }
    .cat-grid { grid-template-columns: repeat(3,1fr); }
    .prod-grid { grid-template-columns: repeat(2,1fr); }
    .why-grid { flex-direction: column; align-items: center; gap: 1rem; }
    .hero-btns { flex-direction: column; }
    .about-stats { grid-template-columns: repeat(2,1fr); }
    .form-row { grid-template-columns: 1fr; }
    .cart-head { padding: 0.95rem 1rem; }
    .cart-head h2 { font-size: 0.98rem; }
    .cart-close-btn { width: 30px; height: 30px; font-size: 0.9rem; }
    .cart-body { padding: 0.75rem 0.9rem; }
    .cart-item-row { gap: 0.6rem; padding: 0.7rem 0; }
    .cart-item-thumb { width: 44px; height: 44px; }
    .cart-item-name { font-size: 0.78rem; }
    .cart-item-price { font-size: 0.76rem; }
    .qty-btn { width: 24px; height: 24px; font-size: 0.82rem; }
    .qty-num { font-size: 0.8rem; }
    .cart-foot { padding: 0.9rem 1rem; }
    .cart-total-row { margin-bottom: 0.75rem; }
    .cart-total-row strong { font-size: 1rem; }
    .btn-order-wa { padding: 0.8rem; font-size: 0.92rem; border-radius: 10px; }
    .btn-clear-cart { margin-top: 0.35rem; font-size: 0.75rem; }
}

/* Homepage refresh: healthcare-first, mobile-first, and aligned to the Medix logo. */
.home-brand-hero {
    max-width: 1280px;
    min-height: 390px;
    margin: 0.25rem auto 0.75rem;
    padding: clamp(1.5rem, 4vw, 3.25rem);
    border-radius: 24px;
    background:
        linear-gradient(90deg, rgba(0, 48, 132, .94) 0%, rgba(0, 70, 158, .82) 48%, rgba(0, 82, 204, .24) 100%),
        radial-gradient(circle at 82% 18%, rgba(0, 209, 178, .32), transparent 34%),
        url("<?php echo e(asset('BACKGROUND MEDIX DEKSTOP.jpeg')); ?>") center right / cover no-repeat,
        linear-gradient(118deg, #003eaa 0%, #0052cc 56%, #0878d1 100%);
    box-shadow: 0 18px 44px rgba(0, 82, 204, .2);
}
.home-brand-hero::before { border-color: rgba(255,255,255,.13); }
.home-brand-hero::after { border-color: rgba(255,255,255,.1); }
.home-brand-hero-label { color:#a7f3d0; }
.home-brand-hero-copy { width:min(100%,760px); }
.home-brand-hero h1 { max-width:690px;font-size:clamp(2.15rem,4.6vw,3.8rem);letter-spacing:-.04em; }
.home-brand-hero p { max-width:620px;color:rgba(255,255,255,.88); }
.home-hero-search { display:flex;align-items:center;gap:.55rem;width:min(100%,680px);margin:1.25rem 0 1.15rem;padding:.42rem .45rem .42rem 1rem;border:1px solid rgba(255,255,255,.75);border-radius:14px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.16); }
.home-hero-search > i { color:#64748b; }
.home-hero-search input { flex:1;min-width:0;padding:.78rem .4rem;border:0;outline:0;background:transparent;color:#1e293b;font:inherit; }
.home-hero-search button { display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.78rem 1.2rem;border:0;border-radius:10px;background:#0052cc;color:#fff;font:inherit;font-weight:800;cursor:pointer; }
.home-hero-search button:hover { background:#003eaa; }
.home-brand-hero-mark { width:270px;height:270px;flex-basis:270px; }
.home-brand-hero-circle { border-color:rgba(255,255,255,.48);background:radial-gradient(circle,rgba(255,255,255,.32),rgba(255,255,255,.06) 70%); }
.home-brand-hero-mark img { width:70%;height:70%; }
.home-service-links { display:block;width:min(100% - 2rem,1200px);margin:0 auto 1.6rem; }
.home-service-items { display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.8rem; }
.home-service-item { display:flex;align-items:center;gap:.8rem;min-width:0;min-height:74px;padding:.85rem 1rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff;box-shadow:0 4px 14px rgba(15,23,42,.04); }
body.public-theme main .home-service-links { padding:0!important;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important; }
body.public-theme main .home-service-links > .home-service-item--action { justify-content:center;width:min(100%,300px)!important;min-height:68px;margin:1rem auto 1rem;padding:.7rem 1.25rem;gap:.85rem;color:#0052cc!important;text-decoration:none;background:#fff!important;border:1px solid rgba(255,255,255,.9)!important;border-radius:18px;box-shadow:0 10px 24px rgba(0,26,90,.2)!important;transition:transform .2s,box-shadow .2s,border-color .2s,background-color .2s; }
body.public-theme main .home-service-links > .home-service-item--action:hover { transform:translateY(-2px);border-color:#b8f3e5!important;background:#f7fffc!important;box-shadow:0 14px 28px rgba(0,26,90,.26)!important; }
body.public-theme main .home-service-links > .home-service-item--action > strong { flex:0 1 auto;color:#0052cc!important;font-size:1rem;font-weight:750;line-height:1.2;white-space:nowrap; }
body.public-theme main .home-service-links > .home-service-item--action > i { margin-left:.1rem;color:#0052cc!important;transition:transform .2s; }
body.public-theme main .home-service-links > .home-service-item--action:hover > i { transform:translateX(3px); }
body.public-theme main .home-service-links > .home-service-item--action .home-trust-icon { flex-basis:46px;width:46px;height:46px;border-radius:14px;background:#e8faf3!important;color:#078a68!important;font-size:1.15rem; }
body.public-theme main .home-service-links > .home-service-item--action:focus-visible { outline:3px solid #00d1b2;outline-offset:3px; }
body.public-theme main .home-service-items .home-service-item { color:#f7fbff!important;background:rgba(255,255,255,.1)!important;border:1px solid rgba(255,255,255,.3)!important;box-shadow:0 8px 20px rgba(0,26,90,.12)!important; }
body.public-theme main .home-service-items .home-service-item strong { color:#fff!important; }
body.public-theme main .home-service-items .home-service-item div > span { color:#dbeafe!important; }
body.public-theme main .home-service-items .home-trust-icon { background:rgba(255,255,255,.95)!important;color:#0052cc!important; }
body.public-theme main .home-service-items .home-service-item div > a { color:#7fffe2!important; }
body.public-theme main .home-service-items .home-service-item div > a:hover { color:#fff!important; }
.home-service-items .home-service-item > div { flex:1;min-width:0; }
body.public-theme main .home-service-items .home-service-item > div > strong { display:block;line-height:1.35; }
body.public-theme main .home-service-items .home-service-item > div > span { display:block;margin-top:.25rem;line-height:1.45; }
body.public-theme main .home-service-items .home-service-item > div > a { display:inline-flex;align-items:center;gap:.4rem;margin-top:.35rem;line-height:1.35; }
.home-trust-icon { display:grid;flex:0 0 42px;width:42px;height:42px;place-items:center;border-radius:12px;background:#ecfdf5;color:#10b981;font-size:1.1rem; }
.home-trust-item strong { display:block;color:#1e293b;font-size:.88rem; }
.home-trust-item div > span { display:block;margin-top:.15rem;color:#64748b;font-size:.76rem;line-height:1.4; }
.home-trust-item div > a { display:inline-flex;align-items:center;gap:.4rem;margin-top:.2rem;color:#0052cc;font-size:.78rem;font-weight:700;text-decoration:none; }
.home-trust-item div > a:hover { color:#003eaa;text-decoration:underline; }
.home-trust-item div > a:focus-visible { outline:3px solid #00d1b2;outline-offset:3px;border-radius:3px; }
.search-engine-section { display:none; }
.category-selection-section { padding:1rem 0;background:#fff;box-shadow:none; }
.category-grid { grid-template-columns:repeat(3,minmax(0,180px));gap:.75rem; }
.category-icon-btn { color:#1e293b;text-transform:none;letter-spacing:0; }
.category-icon-btn:hover { color:#0052cc; }
.category-icon { width:58px;height:58px;border-radius:16px;background:#eff6ff!important;box-shadow:none; }
.category-icon.obat,.category-icon.alkes,.category-icon.kecantikan,.category-icon.nutrisi,.category-icon.apotik,.category-icon.pbf,.category-icon.jasa { background:#eff6ff; }
.category-icon i { color:#0052cc; }
.promo-products-section { padding:2.5rem 0;background:#f8fafc;border-block:1px solid #e2e8f0; }
.promo-products-header .sec-tag { border:1px solid #bae6fd;background:#ecfeff;color:#0e7490; }
.promo-products-header .sec-title { color:#1e293b;text-shadow:none; }
.promo-products-track-wrap { margin:0;padding:.25rem 0;scroll-snap-type:x mandatory;scrollbar-color:#94a3b8 #e2e8f0; }
.promo-products-track { gap:1rem; }
.promo-photo-card { width:clamp(220px,30vw,340px);height:clamp(135px,18vw,190px);border:1px solid #e2e8f0;border-radius:16px;scroll-snap-align:start;box-shadow:0 8px 22px rgba(15,23,42,.08); }
.promo-photo-card::after { display:none; }
.promo-photo-card::before { background:linear-gradient(135deg,rgba(0,82,204,.12),rgba(0,209,178,.1)); }
.why-cards-section { padding:3rem 0;background:#f8fafc; }
.why-card { border-color:#e2e8f0;box-shadow:0 8px 22px rgba(15,23,42,.05); }
.why-card-icon-wrap { background:#ecfeff;color:#0052cc; }
.cta-box { background:linear-gradient(120deg,#0052cc,#0878d1); }
.cat-section { padding:2.5rem 0 1.5rem;background:#fff; }
.cat-card { border-color:#e2e8f0;box-shadow:0 4px 12px rgba(15,23,42,.035); }
.cat-card:hover { background:#eff6ff;border-color:#93c5fd;color:#0052cc; }
.cat-icon { background:#eff6ff!important; }
.cat-icon i { color:#0052cc!important; }
.about-box { border-color:#e2e8f0;box-shadow:0 8px 24px rgba(15,23,42,.05); }
.prod-section { background:#f8fafc!important; }
.prod-card { border-color:#e2e8f0;box-shadow:0 4px 14px rgba(15,23,42,.04); }
.prod-card:hover { border-color:#93c5fd;box-shadow:0 12px 28px rgba(0,82,204,.1); }
.prod-img { background:linear-gradient(135deg,#eff6ff,#ecfeff); }
.prod-badge-label { background:#0052cc; }
.prod-brand-tag,.sec-tag { background:#eff6ff;color:#0052cc; }
.prod-price { color:#0052cc; }
.btn-detail { background:#0052cc; }
.btn-detail:hover { background:#003eaa; }
.btn-cart { border-color:#0052cc;color:#0052cc; }
.btn-cart:hover { background:#eff6ff; }
.sec-link { color:#0052cc; }
.featured-track .product-unit-section { margin:.55rem 0; text-align:left; }
.featured-track .product-unit-heading { color:#64748b; font-size:.7rem; }
.featured-track .product-unit-grid { grid-template-columns:repeat(auto-fit,minmax(74px,1fr)); }
.featured-track .product-unit-grid-1 { grid-template-columns:minmax(92px,1fr); }
.featured-track .product-unit-card { gap:.35rem; padding:.45rem; }
.featured-track .product-unit-card.is-selected { border-color:#1e88e5; background:#eff6ff; box-shadow:0 0 0 1px #1e88e5; }
.featured-track .product-unit-choice { min-height:2.7rem; }
.featured-track .product-unit-name,.featured-track .product-unit-price { font-size:.72rem; }
.featured-track .product-unit-quantity { border-color:#dbe3ec; }
.featured-track .product-unit-quantity button { width:1.35rem; height:1.35rem; cursor:pointer; }
.featured-track .product-unit-quantity input { width:2.2rem; }
.featured-track .prod-body { gap:.35rem; }
.featured-track .prod-name { min-height:2.4em; }
.featured-track .product-stock { align-self:flex-start; margin:.15rem 0; }
.home-product-section-description { margin:.35rem 0 0; color:#64748b; font-size:.82rem; }
.discounted-products-section { padding:2.75rem 0; background:#f8fafc; border-block:1px solid #e2e8f0; }
.discounted-products-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; align-items:stretch; }
.discounted-product-card { position:relative; display:flex; min-width:0; flex-direction:column; overflow:hidden; border:1px solid #e2e8f0; border-radius:14px; background:#fff; box-shadow:0 5px 16px rgba(15,23,42,.06); transition:transform .2s,box-shadow .2s,border-color .2s; }
.discounted-product-card:hover { transform:translateY(-3px); border-color:#bfdbfe; box-shadow:0 12px 26px rgba(15,23,42,.1); }
.discounted-product-image { position:relative; display:flex; height:155px; align-items:center; justify-content:center; overflow:hidden; background:linear-gradient(135deg,#eff6ff,#ecfeff); }
.discounted-product-image img { width:100%; height:100%; object-fit:contain; padding:1rem; }
.discounted-product-badge { position:absolute; top:.65rem; left:.65rem; z-index:1; padding:.3rem .55rem; border-radius:999px; background:#ef4444; color:#fff; font-size:.72rem; font-weight:800; }
.discounted-product-body { display:flex; flex:1; flex-direction:column; padding:.85rem; }
.discounted-product-brand { overflow:hidden; margin-bottom:.35rem; color:#1d4ed8; font-size:.7rem; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
.discounted-product-name { min-height:2.5em; margin:0 0 .3rem; color:#1e293b; font-size:.9rem; line-height:1.35; }
.discounted-product-prices { display:flex; align-items:baseline; gap:.45rem; flex-wrap:wrap; margin:.1rem 0 .25rem; }
.discounted-product-old-price { color:#94a3b8; font-size:.74rem; text-decoration:line-through; }
.discounted-product-current-price { color:#087f9c; font-size:1rem; font-weight:800; }
.discounted-product-body .product-unit-section { margin:.4rem 0; }
.discounted-product-body .product-unit-grid { grid-template-columns:repeat(auto-fit,minmax(68px,1fr)); }
.discounted-product-body .product-unit-grid-1 { grid-template-columns:minmax(92px,1fr); }
.discounted-product-body .product-unit-choice { min-height:2.5rem; }
.discounted-product-body .product-unit-name,.discounted-product-body .product-unit-price { font-size:.7rem; }
.discounted-product-body .product-stock { align-self:flex-start; margin:.35rem 0 .55rem; }
.discounted-product-actions { display:grid; gap:.45rem; margin-top:auto; }
.discounted-product-actions a,.discounted-product-actions button { display:flex; min-height:38px; align-items:center; justify-content:center; gap:.4rem; padding:.55rem; border:0; border-radius:8px; font-size:.78rem; font-weight:700; text-align:center; text-decoration:none; cursor:pointer; }
.discounted-product-actions a { color:#1d4ed8; background:#eff6ff; }
.discounted-product-actions a:hover { background:#dbeafe; }
.discounted-product-actions button { color:#fff; background:#0f172a; }
.discounted-product-actions button:hover { background:#1e293b; }
@media(max-width:900px) {
    .discounted-products-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media(max-width:600px) {
    .discounted-products-section { padding:2rem 0; }
    .discounted-products-grid { grid-template-columns:minmax(0,1fr); gap:.85rem; }
    .discounted-product-image { height:160px; }
    .discounted-product-body { padding:.8rem; }
    .discounted-product-name { font-size:.95rem; }
    .discounted-product-body .product-unit-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
}
@media(max-width:768px) {
    .home-brand-hero { margin:.25rem .8rem .5rem;padding:1.75rem 1.25rem;background:linear-gradient(180deg,rgba(0,48,132,.72),rgba(0,64,150,.86)),radial-gradient(circle at 82% 18%,rgba(0,209,178,.26),transparent 34%),url("<?php echo e(asset('BACKGROUND MEDIX MOBILE.jpeg')); ?>") center 55% / cover no-repeat,linear-gradient(118deg,#003eaa 0%,#0052cc 56%,#0878d1 100%); }
    .home-brand-hero-mark { width:150px;height:150px;flex-basis:150px; }
    .home-service-items { grid-template-columns:1fr;gap:.55rem; }
    .home-service-item { min-height:64px;padding:.75rem .85rem; }
    .promo-products-section { padding:1.8rem 0; }
    .category-selection-inner { padding:0 1rem; }
}
@media(max-width:480px) {
    .home-brand-hero { display:block;margin:.2rem .65rem .5rem;padding:1.3rem 1rem;border-radius:18px; }
    .home-brand-hero-mark { top:.65rem;right:.65rem;width:60px;height:60px; }
    .home-brand-hero h1 { max-width:290px;padding-right:2rem;font-size:1.78rem; }
    .home-brand-hero p { max-width:330px;font-size:.9rem; }
    .home-hero-chat-actions { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem;margin-top:.85rem; }
    .home-hero-chat-button { min-width:0;padding:.65rem .45rem;font-size:.82rem; }
    .home-hero-search { gap:.25rem;margin:.95rem 0;padding:.3rem .32rem .3rem .65rem; }
    .home-hero-search input { padding:.65rem .25rem;font-size:.83rem; }
    .home-hero-search button { gap:.35rem;padding:.68rem .75rem;font-size:.8rem; }
    .home-hero-search button span { display:inline; }
    .category-grid { grid-template-columns:repeat(3,minmax(0,1fr));gap:.35rem; }
    .category-icon-btn { padding:.65rem .25rem;gap:.4rem;font-size:.72rem; }
    .category-icon { width:46px;height:46px;font-size:1.3rem; }
    .cat-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .promo-photo-card { width:235px;height:136px; }
}
</style>
<?php echo $__env->make('partials.product-card-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<?php if(isset($banners) && $banners->count() > 0): ?>
<section class="home-banner-wrap" aria-label="Banner Promo Apotek Medix">
    <div class="home-banner-slider" id="homeBannerSlider">
        <div class="home-banner-track" id="homeBannerTrack">
            <?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="home-banner-slide <?php echo e($i === 0 ? 'active' : ''); ?>" data-index="<?php echo e($i); ?>">
                    <?php if(!empty($banner->url_tujuan)): ?>
                        <a href="<?php echo e($banner->url_tujuan); ?>" class="home-banner-link">
                    <?php else: ?>
                        <div class="home-banner-link">
                    <?php endif; ?>
                        <img src="<?php echo e(url('storage/' . $banner->gambar)); ?>"
                             alt="<?php echo e($banner->judul ?: 'Banner Promo ' . ($i + 1)); ?>"
                             class="home-banner-img"
                             loading="<?php echo e($i === 0 ? 'eager' : 'lazy'); ?>">
                        <?php if(!empty($banner->judul)): ?>
                            <div class="home-banner-caption">
                                <span class="home-banner-title"><?php echo e($banner->judul); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php if(!empty($banner->url_tujuan)): ?>
                        </a>
                    <?php else: ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($banners->count() > 1): ?>
            <button type="button" class="home-banner-nav home-banner-prev" onclick="window.slideHomeBanner(-1)" aria-label="Banner sebelumnya">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" class="home-banner-nav home-banner-next" onclick="window.slideHomeBanner(1)" aria-label="Banner berikutnya">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="home-banner-dots" id="homeBannerDots">
                <?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button"
                            class="home-banner-dot <?php echo e($i === 0 ? 'active' : ''); ?>"
                            onclick="window.goToHomeBanner(<?php echo e($i); ?>)"
                            aria-label="Pilih banner <?php echo e($i + 1); ?>"></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.home-banner-wrap {
    max-width: 1280px;
    margin: 0.35rem auto 1rem;
    padding: 0;
}
.home-banner-slider {
    position: relative;
    width: 100%;
    aspect-ratio: 3998 / 1224;
    border-radius: 22px;
    overflow: hidden;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.14);
}
.home-banner-track {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.65s cubic-bezier(0.25, 0.8, 0.25, 1);
    will-change: transform;
}
.home-banner-slide {
    flex: 0 0 100%;
    width: 100%;
    height: 100%;
    position: relative;
}
.home-banner-link {
    display: block;
    width: 100%;
    height: 100%;
    position: relative;
    text-decoration: none;
}
.home-banner-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}
.home-banner-caption {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 1.5rem 1.75rem 1.35rem;
    background: linear-gradient(to top, rgba(15, 23, 42, 0.72) 0%, rgba(15, 23, 42, 0.28) 60%, transparent 100%);
    display: flex;
    align-items: flex-end;
    pointer-events: none;
}
.home-banner-title {
    color: #ffffff;
    font-size: clamp(1rem, 2vw, 1.45rem);
    font-weight: 800;
    line-height: 1.25;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.45);
}
.home-banner-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.45);
    background: rgba(15, 23, 42, 0.42);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.2s ease;
    backdrop-filter: blur(6px);
}
.home-banner-nav:hover {
    background: rgba(0, 82, 204, 0.88);
    border-color: #ffffff;
    transform: translateY(-50%) scale(1.06);
}
.home-banner-prev { left: 1rem; }
.home-banner-next { right: 1rem; }
.home-banner-dots {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 7px;
    z-index: 10;
    padding: 5px 10px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.35);
    backdrop-filter: blur(4px);
}
.home-banner-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.55);
    border: none;
    padding: 0;
    cursor: pointer;
    transition: all 0.3s ease;
}
.home-banner-dot.active {
    width: 24px;
    background: #38bdf8;
}
@media (max-width: 768px) {
    .home-banner-wrap { margin: 0.25rem 0.8rem 0.75rem; }
    .home-banner-slider { border-radius: 16px; aspect-ratio: 16 / 7; }
    .home-banner-nav { width: 32px; height: 32px; font-size: 0.8rem; }
    .home-banner-prev { left: 0.5rem; }
    .home-banner-next { right: 0.5rem; }
    .home-banner-caption { padding: 1rem 1rem 1.2rem; }
}
@media (max-width: 480px) {
    .home-banner-wrap { margin: 0.2rem 0.65rem 0.65rem; }
    .home-banner-slider { border-radius: 14px; aspect-ratio: 16 / 8; }
}
</style>

<script>
(function () {
    const slider = document.getElementById('homeBannerSlider');
    const track = document.getElementById('homeBannerTrack');
    if (!slider || !track) return;

    const slides = slider.querySelectorAll('.home-banner-slide');
    const dots = slider.querySelectorAll('.home-banner-dot');
    const total = slides.length;
    if (total <= 1) return;

    let current = 0;
    let timer = null;
    const INTERVAL_MS = 4500; // 4.5 detik: pas, tidak terlalu cepat & tidak terlalu lambat

    function updateSlide(index) {
        current = (index + total) % total;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';
        slides.forEach((s, i) => s.classList.toggle('active', i === current));
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
    }

    function startAutoSlide() {
        stopAutoSlide();
        timer = setInterval(function () {
            updateSlide(current + 1);
        }, INTERVAL_MS);
    }

    function stopAutoSlide() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    window.slideHomeBanner = function (dir) {
        updateSlide(current + dir);
        startAutoSlide();
    };

    window.goToHomeBanner = function (index) {
        updateSlide(index);
        startAutoSlide();
    };

    slider.addEventListener('mouseenter', stopAutoSlide);
    slider.addEventListener('mouseleave', startAutoSlide);

    let touchStartX = 0;
    slider.addEventListener('touchstart', function (e) {
        touchStartX = e.touches[0].clientX;
        stopAutoSlide();
    }, { passive: true });

    slider.addEventListener('touchend', function (e) {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 45) {
            updateSlide(current + (diff > 0 ? 1 : -1));
        }
        startAutoSlide();
    }, { passive: true });

    startAutoSlide();
})();
</script>
<?php endif; ?>


<section class="home-brand-hero" aria-labelledby="homeHeroTitle">
    <div class="home-brand-hero-copy">
        <span class="home-brand-hero-label"><i class="fa-solid fa-heart-pulse"></i> Apotek Medix</span>
        <h1 id="homeHeroTitle">Solusi Kesehatan Terpercaya</h1>
        <p>Beli obat, suplemen, dan produk kesehatan dengan mudah. Tim Apotek Medix siap membantu Anda.</p>
        <div class="home-hero-chat-actions" aria-label="Pilihan konsultasi">
            <button type="button" class="home-hero-chat-button">
                <i class="fa-solid fa-user-doctor" aria-hidden="true"></i>
                Chat Dokter
            </button>
            <button type="button" class="home-hero-chat-button home-hero-chat-button--pharmacist">
                <i class="fa-solid fa-user-nurse" aria-hidden="true"></i>
                Chat Apoteker
            </button>
        </div>
    </div>
    <div class="home-brand-hero-mark" aria-hidden="true">
        <span class="home-brand-hero-circle"></span>
        <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="">
    </div>
</section>

<style>
/* ===== BANNER SLIDESHOW ===== */
.banner-slider {
    position: relative;
    width: 100%;
    aspect-ratio: 3998 / 1224;
    overflow: hidden;
    background: linear-gradient(135deg, #0F172A 0%, #17243A 58%, #12394A 100%);
    line-height: 0;
}
.banner-search-panel {
    position: relative;
    z-index: 2;
    margin-top: 0;
    padding: 1rem 0 0.75rem;
    background: linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(255,255,255,0.9) 100%);
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    order: 3;
}
.banner-search-panel .container {
    display: flex;
    justify-content: center;
}
.banner-search-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: min(100%, 760px);
    pointer-events: auto;
}
.banner-search-box {
    flex: 1;
    background: rgba(255,255,255,0.96);
    border: 1px solid rgba(255,255,255,0.9);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    backdrop-filter: blur(8px);
}
.banner-cart-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border: none;
    border-radius: 999px;
    background: #7CB342;
    color: #fff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.22);
    cursor: pointer;
    position: relative;
}
.banner-cart-btn:hover { background: #689F38; }
.banner-cart-btn .cart-badge {
    position: absolute;
    top: -4px;
    right: -4px;
}
.banner-track { position: relative; width: 100%; height: 100%; }
.banner-slide {
    display: none;
    position: relative;
    width: 100%;
    height: 100%;
}
.home-b2c-grid-4x5 .prod-card[hidden] { display: none !important; }
.banner-slide.active { display: block; }
.banner-link { display: block; text-decoration: none; }
.banner-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
    transition: transform 7s ease;
}
.banner-slide.active .banner-img { transform: scale(1.0); }
.banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.2) 50%, transparent 80%);
    display: flex;
    align-items: center;
}
.banner-text {
    padding: 0 2.5rem;
    max-width: 560px;
    line-height: 1.4;
}
.banner-title-text {
    font-size: clamp(1.25rem, 3vw, 2rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.5rem;
    text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    line-height: 1.2;
}
.banner-sub-text {
    font-size: clamp(0.82rem, 1.5vw, 1rem);
    color: rgba(255,255,255,0.9);
    margin: 0 0 1rem;
    text-shadow: 0 1px 4px rgba(0,0,0,0.3);
}
.banner-btn-label {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #7CB342;
    color: #fff;
    padding: 0.55rem 1.25rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    transition: background 0.2s;
}
.banner-link:hover .banner-btn-label { background: #689F38; }

/* Nav buttons */
.banner-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.25);
    border: none;
    color: #fff;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: background 0.2s;
    backdrop-filter: blur(4px);
}
.banner-nav:hover { background: rgba(255,255,255,0.45); }
.banner-prev { left: 1rem; }
.banner-next { right: 1rem; }

/* Dots */
.banner-dots {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    z-index: 10;
}
.banner-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.45);
    border: 1px solid rgba(255,255,255,0.6);
    cursor: pointer;
    transition: all 0.3s;
    padding: 0;
}
.banner-dot.active {
    background: #7CB342;
    width: 22px;
    border-radius: 4px;
    border-color: #7CB342;
}

@media (max-width: 768px) {
    .banner-slider { aspect-ratio: 16/9; margin-top: 60px; }
    .banner-text { padding: 0 1.25rem; max-width: 90%; }
    .banner-title-text { font-size: 1rem; }
    .banner-sub-text { display: none; }
    .banner-btn-label { font-size: 0.75rem; padding: 0.4rem 0.85rem; }
    .banner-nav { width: 26px; height: 26px; font-size: 0.7rem; top: 50%; }
    .banner-search-panel { padding: 0.8rem 0 0.6rem; }
    .banner-search-row { gap: 0.6rem; }
    .promo-section { padding: 2rem 0; }
    .promo-grid { grid-template-columns: 1fr 1fr; gap: 1.25rem; padding: 0 0.75rem; }
    .promo-card { padding: 1.5rem 1.25rem; min-height: 180px; flex-direction: column; }
    .promo-card-content { gap: 1rem; }
    .promo-card-icon-wrap { width: 70px; height: 70px; border-radius: 16px; font-size: 2.5rem; }
    .promo-card-text { gap: 0.35rem; }
    .promo-card > i { font-size: 2.8rem; }
    .promo-goapotik-logo { height: 70px; }
    .promo-card h4 { font-size: 1.15rem; }
    .promo-card p { font-size: 0.88rem; }
}
@media (max-width: 480px) {
    .banner-slider { aspect-ratio: 4/3; margin-top: 55px; }
    .banner-nav { top: 50%; }
    .banner-prev { left: 0.4rem; }
    .banner-next { right: 0.4rem; }
    .banner-search-overlay .container { padding: 0 0.75rem; }
    .banner-search-row { flex-direction: column; align-items: stretch; gap: 0.5rem; }
    .banner-cart-btn { width: 100%; height: 46px; border-radius: 999px; }
    .promo-section { padding: 1.5rem 0; }
    .promo-grid { grid-template-columns: 1fr; gap: 0.8rem; padding: 0 0.5rem; }
    .promo-card { padding: 1.25rem; min-height: 140px; flex-direction: column; }
    .promo-card-content { gap: 0.75rem; }
    .promo-card-icon-wrap { width: 60px; height: 60px; border-radius: 14px; font-size: 2rem; }
    .promo-card-text { gap: 0.3rem; }
    .promo-card > i { font-size: 2.2rem; }
    .promo-goapotik-logo { height: 60px; }
    .promo-card h4 { font-size: 1rem; margin: 0; }
    .promo-card p { font-size: 0.8rem; }
    .banner-search-panel { padding: 0.6rem 0 0.5rem; }
    .banner-search-box input { font-size: 0.9rem; }
    .banner-search-box button { font-size: 0.75rem; padding: 0.6rem 1rem; }
}

.search-engine-section {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 68%, #12394A 100%);
    padding: 2.5rem 0;
    margin: 0;
    box-shadow: 0 8px 32px rgba(15, 23, 42, 0.18);
    width: 100%;
}

  .search-engine-section.no-banner {
    padding-top: 2.75rem;
    padding-bottom: 2.75rem;
  }

.search-engine-wrapper {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 2rem;
  width: 100%;
}

.search-engine-box {
    flex: 1;
  min-width: 0;
    max-width: 600px;
    display: flex;
    gap: 0.75rem;
    background: #fff;
    border-radius: 16px;
    padding: 0.75rem;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
    border: none;
    transition: all 0.3s ease;
}

.search-engine-box > i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
}

.search-engine-box:hover,
.search-engine-box:focus-within {
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.25);
    transform: translateY(-2px);
}

.search-engine-box input {
    flex: 1;
    border: none;
    outline: none;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    color: #374151;
    background: transparent;
}

.search-engine-box input::placeholder {
    color: #9ca3af;
}

.search-engine-box button {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    color: #fff;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.9rem;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
}

.search-engine-box button:hover {
    background: linear-gradient(135deg, #1565C0, #0D47A1);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(30, 136, 229, 0.4);
}

.cart-btn-home {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    background: #fff;
    color: #0E6470;
    padding: 0.75rem 1.75rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    cursor: pointer;
    position: relative;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.cart-btn-home:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(30, 136, 229, 0.25);
}

.cart-badge-home {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    background: #ef4444;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 800;
    color: #fff;
}

@media (max-width: 768px) {
    .search-engine-section {
        padding: 1.5rem 0;
    }

    .search-engine-section.no-banner {
      padding: 1.5rem 0;
    }

    .search-engine-wrapper {
        gap: 0.75rem;
      padding: 0 1rem;
    }

    .search-engine-box {
        min-width: 100%;
        max-width: 100%;
    }

    .cart-btn-home {
        width: 100%;
        justify-content: center;
        font-size: 0.85rem;
        padding: 0.65rem 1.25rem;
    }
}

@media (max-width: 480px) {
    .search-engine-section {
        padding: 1rem 0;
    }

    .search-engine-wrapper {
      padding: 0 0.75rem;
    }

    .search-engine-box {
        min-width: 100%;
        max-width: 100%;
        padding: 0.6rem;
    }

    .search-engine-box input {
        padding: 0.6rem 0.75rem;
        font-size: 0.85rem;
    }

    .search-engine-box button {
        padding: 0.6rem 1rem;
        font-size: 0.8rem;
        gap: 0.3rem;
    }

    .search-engine-box button i {
        font-size: 0.85rem;
    }

    .search-engine-box button span {
        display: none;
    }

    .cart-btn-home {
        width: 100%;
        padding: 0.6rem 1rem;
        font-size: 0.75rem;
    }
}
</style>


<div class="quick-section">
  <div class="container">
    <div class="quick-row">
      <a href="<?php echo e(route('home')); ?>#homeSearchSection" class="quick-btn active"><i class="fa-solid fa-th-large"></i><span>Semua</span></a>
      <a href="<?php echo e(route('home', ['kategori_pilihan' => 'otc'])); ?>#homeSearchSection" class="quick-btn"><i class="fa-solid fa-pills" style="color:#1E88E5;"></i><span>OTC</span></a>
      <a href="<?php echo e(route('home', ['kategori_pilihan' => 'vitamin-suplemen'])); ?>#homeSearchSection" class="quick-btn"><i class="fa-solid fa-capsules" style="color:#00a98f;"></i><span>Vitamin & Suplemen</span></a>
      <a href="<?php echo e(route('home', ['kategori_pilihan' => 'pil-kb-hormonal'])); ?>#homeSearchSection" class="quick-btn"><i class="fa-solid fa-spa" style="color:#1E88E5;"></i><span>Pil KB & Hormonal</span></a>
      <a href="<?php echo e(route('home', ['kategori_pilihan' => 'alat-kesehatan'])); ?>#homeSearchSection" class="quick-btn"><i class="fa-solid fa-stethoscope" style="color:#1E88E5;"></i><span>Alat Kesehatan</span></a>
    </div>
  </div>
</div>

<?php if(!$homeHasFilters): ?>
<section class="home-category-section" aria-labelledby="homeCategoryTitle">
    <div class="container">
        <div class="home-category-heading">
            <div>
                <span class="home-section-eyebrow">Pilih kebutuhan Anda</span>
                <h2 id="homeCategoryTitle">Kategori Produk</h2>
            </div>
            <div class="home-category-controls">
                <a href="<?php echo e(route('home')); ?>#homeSearchSection">Semua produk <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                <button type="button" class="home-category-scroll" data-direction="-1" aria-label="Kategori sebelumnya">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="home-category-scroll" data-direction="1" aria-label="Kategori berikutnya">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="home-category-grid" id="homeCategoryGrid">
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'obat-resep'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-prescription-bottle-medical" aria-hidden="true"></i></span><strong>Obat Resep</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'otc'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-pills" aria-hidden="true"></i></span><strong>OTC</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'pil-kb-hormonal'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-tablets" aria-hidden="true"></i></span><strong>Pil KB &amp; Hormonal</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'alat-kesehatan'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-stethoscope" aria-hidden="true"></i></span><strong>Alat Kesehatan</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'vitamin-suplemen'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-capsules" aria-hidden="true"></i></span><strong>Vitamin &amp; Suplement</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'flu-batuk'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-head-side-cough" aria-hidden="true"></i></span><strong>Flu &amp; Batuk</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'ibu-anak'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-baby" aria-hidden="true"></i></span><strong>Ibu &amp; Anak</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'obat-herbal'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-leaf" aria-hidden="true"></i></span><strong>Obat Herbal</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'obat-diabetes'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-droplet" aria-hidden="true"></i></span><strong>Obat Diabetes</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'obat-hipertensi'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-heart-pulse" aria-hidden="true"></i></span><strong>Obat Hipertensi</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'obat-kolesterol'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-heart-circle-check" aria-hidden="true"></i></span><strong>Obat Kolesterol</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'injeksi-infus'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-syringe" aria-hidden="true"></i></span><strong>Injeksi &amp; Infus</strong>
            </a>
            <a href="<?php echo e(route('home', ['kategori_pilihan' => 'skincare-kosmetik'])); ?>#homeSearchSection" class="home-category-card">
                <span><i class="fa-solid fa-pump-soap" aria-hidden="true"></i></span><strong>Skincare &amp; Kosmetik</strong>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="home-search-section" id="homeSearchSection" aria-label="Cari produk">
    <div class="container">
        <form class="home-product-search" method="GET" action="<?php echo e(route('home')); ?>" role="search">
            <div class="home-search-field">
                <label class="home-search-label" for="homeProductSearchInput"><i class="fa-solid fa-magnifying-glass"></i> Cari Produk</label>
                <input id="homeProductSearchInput" type="search" name="search" value="<?php echo e($search); ?>" placeholder="Cari nama obat, deskripsi, komposisi, indikasi, atau golongan..." aria-label="Kata kunci produk">
            </div>
            <div class="home-search-field">
                <label class="home-search-label" for="homeCategoryFilter"><i class="fa-solid fa-tag"></i> Kategori</label>
                <select id="homeCategoryFilter" name="kategori_pilihan">
                    <option value="">Semua Kategori</option>
                    <?php $__currentLoopData = $kategoriPilihanOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if($kategoriPilihan === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="home-search-field">
                <label class="home-search-label" for="homeCompanyFilter"><i class="fa-solid fa-building"></i> Perusahaan</label>
                <select id="homeCompanyFilter" name="perusahaan">
                    <option value="">Semua Perusahaan</option>
                    <?php $__currentLoopData = $companyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($company); ?>" <?php if($perusahaan === $company): echo 'selected'; endif; ?>><?php echo e($company); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="home-search-field">
                <label class="home-search-label" for="homeSortFilter"><i class="fa-solid fa-arrow-up-wide-short"></i> Urutkan</label>
                <select id="homeSortFilter" name="sort">
                    <option value="terbaru" <?php if($sort === 'terbaru'): echo 'selected'; endif; ?>>Terbaru</option>
                    <option value="harga_asc" <?php if($sort === 'harga_asc'): echo 'selected'; endif; ?>>Harga Terendah</option>
                    <option value="harga_desc" <?php if($sort === 'harga_desc'): echo 'selected'; endif; ?>>Harga Tertinggi</option>
                    <option value="nama" <?php if($sort === 'nama'): echo 'selected'; endif; ?>>Nama A–Z</option>
                </select>
            </div>
            <button type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span>Cari</span></button>
            <?php if($homeHasFilters): ?>
                <a class="home-search-clear" href="<?php echo e(route('home')); ?>">Hapus pencarian</a>
            <?php endif; ?>
        </form>
    </div>
</section>


<style>
/* ===== HOME B2C HORIZONTAL PRODUCT CARDS ===== */
.home-b2c-section { padding: 2.75rem 0; }
.home-b2c-section--alt { background: #f8fafc; border-block: 1px solid #e2e8f0; }
.featured-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem;
}
.home-b2c-catalog-heading { display:flex; align-items:center; gap:.65rem; flex-wrap:nowrap; }
.home-b2c-transaction-label,.home-b2b-catalog-link { display:inline-flex;align-items:center;justify-content:center;gap:.5rem;min-height:42px;padding:.55rem .95rem;border:1px solid #bfdbfe;border-radius:999px;background:#fff;color:#0052cc;font-size:.9rem;font-weight:750;line-height:1.2;white-space:nowrap; }
.home-b2b-catalog-link { text-decoration:none;transition:background-color .2s,border-color .2s; }
.home-b2b-catalog-link:hover { border-color:#93c5fd;background:#eff6ff; }
.home-b2b-catalog-link:focus-visible { outline:3px solid #00d1b2;outline-offset:3px; }
.home-b2c-grid-4x5,
.discounted-products-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 1rem;
    align-items: stretch;
}
.home-b2c-grid-4x5 .prod-card,
.discounted-products-grid .discounted-product-card {
    display: grid !important;
    grid-template-columns: minmax(140px, 22%) minmax(0, 1fr) !important;
    grid-template-rows: auto 1fr !important;
    min-width: 0;
    height: 100%;
}
.home-b2c-grid-4x5 .medicine-card-brand,
.discounted-products-grid .medicine-card-brand {
    grid-column: 1 / -1 !important;
    grid-row: 1 !important;
}
.home-b2c-grid-4x5 .prod-img,
.discounted-products-grid .discounted-product-image {
    grid-column: 1 !important;
    grid-row: 2 !important;
    align-self: start;
    width: auto;
    height: 180px;
}
.home-b2c-grid-4x5 .prod-body,
.discounted-products-grid .discounted-product-body {
    grid-column: 2 !important;
    grid-row: 2 !important;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(180px, 28%);
    align-content: start;
    align-items: start;
    column-gap: 1rem;
    min-width: 0;
}
.home-b2c-grid-4x5 .medicine-card-principal,
.home-b2c-grid-4x5 .prod-name,
.home-b2c-grid-4x5 .medicine-card-details,
.discounted-products-grid .medicine-card-principal,
.discounted-products-grid .discounted-product-name,
.discounted-products-grid .medicine-card-details {
    grid-column: 1 / -1;
    min-width: 0;
}
.home-b2c-grid-4x5 .prod-name {
    display: block;
    min-height: 0;
    overflow: visible;
    overflow-wrap: anywhere;
    flex: initial;
    -webkit-line-clamp: unset;
}
.home-b2c-grid-4x5 .product-unit-section,
.discounted-products-grid .product-unit-section { grid-column: 1; }
.home-b2c-grid-4x5 .product-stock,
.home-b2c-grid-4x5 .medicine-meta-row,
.home-b2c-grid-4x5 .btn-cart,
.discounted-products-grid .product-stock,
.discounted-products-grid .medicine-meta-row,
.discounted-products-grid .discounted-product-actions {
    grid-column: 2;
}
.home-b2c-grid-4x5 .btn-cart { max-width: 320px; }
.home-b2c-grid-4x5 .medicine-card-details p:nth-child(n + 2),
.discounted-products-grid .medicine-card-details p:nth-child(n + 2) {
    display: none !important;
}
.discounted-products-grid .discounted-product-badge { z-index: 2; }
.home-b2c-grid-4x5 .product-unit-section { margin: .55rem 0; text-align: left; }
.home-b2c-grid-4x5 .product-unit-heading { color: #64748b; font-size: .7rem; }
.home-b2c-grid-4x5 .product-unit-grid { grid-template-columns: repeat(auto-fit, minmax(74px, 1fr)); }
.home-b2c-grid-4x5 .product-unit-grid-1 { grid-template-columns: minmax(92px, 1fr); }
.home-b2c-grid-4x5 .product-unit-card { gap: .35rem; padding: .45rem; }
.home-b2c-grid-4x5 .product-unit-card.is-selected { border-color: #1e88e5; background: #eff6ff; box-shadow: 0 0 0 1px #1e88e5; }
.home-b2c-grid-4x5 .product-unit-choice { min-height: 2.7rem; }
.home-b2c-grid-4x5 .product-unit-name,
.home-b2c-grid-4x5 .product-unit-price { font-size: .72rem; }
.home-b2c-grid-4x5 .product-unit-quantity { border-color: #dbe3ec; }
.home-b2c-grid-4x5 .product-unit-quantity button { width: 1.35rem; height: 1.35rem; cursor: pointer; }
.home-b2c-grid-4x5 .product-unit-quantity input { width: 2.2rem; }
.home-b2c-grid-4x5 .prod-body { gap: .35rem; }
.home-b2c-grid-4x5 .prod-name { min-height: 2.4em; }
.home-b2c-grid-4x5 .product-stock { align-self: flex-start; margin: .15rem 0; }
.featured-cta { text-align: center; margin-top: 1.75rem; }
.home-show-more-products {
    display: inline-flex; align-items: center; justify-content: center; gap: .6rem;
    min-width: 230px; padding: .8rem 1.8rem; border: 0; border-radius: 50px;
    background: linear-gradient(135deg, #1E88E5, #1565C0); color: #fff;
    font: inherit; font-size: .95rem; font-weight: 700; cursor: pointer;
    box-shadow: 0 6px 18px rgba(21, 101, 192, .22);
    transition: transform .2s, box-shadow .2s, background .2s;
}
.home-show-more-products:hover { transform: translateY(-2px); box-shadow: 0 9px 22px rgba(21, 101, 192, .3); }
.home-show-more-products:focus-visible { outline: 3px solid #93c5fd; outline-offset: 3px; }

@media (max-width: 992px) {
    .home-b2c-grid-4x5,
    .discounted-products-grid {
        grid-template-columns: minmax(0, 1fr) !important;
    }
}
@media (max-width: 600px) {
    .home-b2c-grid-4x5,
    .discounted-products-grid {
        grid-template-columns: minmax(0, 1fr) !important;
        gap: .75rem;
    }
    .home-b2c-grid-4x5 .prod-card,
    .discounted-products-grid .discounted-product-card {
        grid-template-columns: minmax(96px, 30%) minmax(0, 1fr) !important;
    }
    .home-b2c-grid-4x5 .prod-body,
    .discounted-products-grid .discounted-product-body {
        grid-template-columns: minmax(0, 1fr);
        column-gap: 0;
    }
    .home-b2c-grid-4x5 .product-unit-section,
    .home-b2c-grid-4x5 .product-stock,
    .home-b2c-grid-4x5 .medicine-meta-row,
    .home-b2c-grid-4x5 .btn-cart,
    .discounted-products-grid .product-unit-section,
    .discounted-products-grid .product-stock,
    .discounted-products-grid .medicine-meta-row,
    .discounted-products-grid .discounted-product-actions {
        grid-column: 1;
    }
    .home-b2c-grid-4x5 .prod-img,
    .discounted-products-grid .discounted-product-image { height: 150px; }
    .home-product-search input { height: 42px; padding: .5rem .65rem; font-size: .84rem; }
}
</style>

<?php if(!$homeHasFilters && isset($discountedProducts) && $discountedProducts->isNotEmpty()): ?>

<section class="discounted-products-section home-b2c-section home-b2c-section--alt">
  <div class="container">
    <div class="featured-header">
      <div class="sec-head-left">
        <span class="sec-tag"><?php echo e($internalAccess ? 'Harga Internal' : '🏷️ Promo B2C'); ?></span>
        <h2 class="sec-title" style="margin:0;"><?php echo e($internalAccess ? 'Produk Harga Internal' : 'Produk Promo'); ?></h2>
        <p class="home-product-section-description"><?php echo e($internalAccess ? 'Harga internal berlaku untuk produk-produk pilihan ini.' : 'Harga retail spesial untuk belanja B2C.'); ?></p>
      </div>
    </div>
    <div class="discounted-products-grid home-b2c-grid-4x5">
      <?php $__currentLoopData = $discountedProducts->take(20); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promoMedicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $promoPriceChannel = $internalAccess ? 'internal' : 'b2c';
          $promoPrice = $promoMedicine->getChannelPromoPrice($promoPriceChannel);
          $promoUnitId = 'home-promo-unit-' . $promoMedicine->id;
        ?>
        <article class="discounted-product-card">
          <?php if(!$internalAccess): ?><span class="discounted-product-badge product-discount-badge">Diskon <?php echo e($promoMedicine->getPromoPercent('b2c')); ?>%</span><?php endif; ?>
          <div class="medicine-card-brand">Apotek Medix</div>
          <a class="discounted-product-image" href="<?php echo e(route('medicines.show', ['id' => $promoMedicine->id, 'from' => 'home'])); ?>" aria-label="Lihat <?php echo e($promoMedicine->nama_obat); ?>">
            <?php echo $__env->make('partials.product-logo-marker', ['medicine' => $promoMedicine], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if($promoMedicine->gambar): ?>
              <img src="<?php echo e($promoMedicine->image_url); ?>" alt="<?php echo e($promoMedicine->nama_obat); ?>" loading="lazy">
              <?php if(count($promoMedicine->image_gallery) > 1): ?>
                <img src="<?php echo e(url('storage/' . $promoMedicine->image_gallery[1])); ?>" alt="<?php echo e($promoMedicine->nama_obat); ?> - foto kedua" loading="lazy" class="promo-gallery-secondary">
              <?php endif; ?>
            <?php else: ?>
              <img src="<?php echo e(asset('LOGO APOTEK MEDIX.PNG')); ?>" alt="Apotek Medix" loading="lazy">
            <?php endif; ?>
          </a>
          <div class="discounted-product-body">
            <?php if($promoMedicine->brand ?: $promoMedicine->kategori): ?>
              <div class="medicine-card-principal"><?php echo e($promoMedicine->brand ?: $promoMedicine->kategori); ?></div>
            <?php else: ?>
              <div class="medicine-card-principal" aria-hidden="true"></div>
            <?php endif; ?>
            <h3 class="discounted-product-name"><a href="<?php echo e(route('medicines.show', ['id' => $promoMedicine->id, 'from' => 'home'])); ?>" style="color:inherit;text-decoration:none;"><?php echo e($promoMedicine->nama_obat); ?></a></h3>
            <div class="medicine-card-details">
              <p title="<?php echo e($promoMedicine->komposisi ?: '—'); ?>"><strong>Komposisi:</strong> <?php echo e(\Illuminate\Support\Str::limit($promoMedicine->komposisi ?: '—', 15, '...')); ?></p>
            </div>
            <?php echo $__env->make('partials.product-unit-selector', ['medicine' => $promoMedicine, 'unitSelectId' => $promoUnitId, 'priceTargetId' => 'home-price-b-' . $promoMedicine->id, 'priceChannel' => $promoPriceChannel, 'redLogoAccess' => $redLogoAccess], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php
              $promoStockUnits = (int) $promoMedicine->available_stock_units;
              $promoStock = $promoStockUnits / max(1, $promoMedicine->units_per_box);
              $promoStockDisplay = rtrim(rtrim(number_format($promoStock, 6, ',', '.'), '0'), ',');
            ?>
            <span class="product-stock <?php echo e($promoStock <= 0 ? 'empty' : ($promoStock <= 10 ? 'low' : 'available')); ?>" data-stock-badge data-stock-product-id="<?php echo e($promoMedicine->id); ?>" data-stock-unit-factor="<?php echo e(max(1, $promoMedicine->units_per_box)); ?>">
              <i class="fa-solid <?php echo e($promoStock <= 0 ? 'fa-circle-xmark' : 'fa-boxes-stacked'); ?>"></i>
              <?php echo e($promoStock > 0 ? 'Stok: ' . $promoStockDisplay : 'Stok habis'); ?>

            </span>
            <div class="medicine-meta medicine-meta-row">
              <span class="medicine-meta-sediaan">
                <?php if($promoMedicine->sediaan_label): ?>
                  <i class="fa-solid fa-cube"></i> <span><?php echo e($promoMedicine->sediaan_label); ?></span>
                <?php endif; ?>
              </span>
              <span class="medicine-sold-badge" data-sold-product-id="<?php echo e($promoMedicine->id); ?>">
                <i class="fa-solid fa-fire-flame-curved"></i> Terjual <?php echo e(number_format((int) $promoMedicine->terjual, 0, ',', '.')); ?>

              </span>
            </div>
            <button class="btn-cart <?php echo e(($promoMedicine->isRedLogo() && !($redLogoAccess ?? false)) ? 'is-red-logo-locked' : ''); ?>" data-stock-cart <?php if($promoStock <= 0): echo 'disabled'; endif; ?> data-package-unit-label="<?php echo e($promoMedicine->package_unit_label); ?>" data-requires-prescription="<?php echo e($promoMedicine->isRedLogo() ? '1' : '0'); ?>" data-sales-channel="b2c" onclick="addToCart(<?php echo e($promoMedicine->id); ?>, '<?php echo e(addslashes($promoMedicine->nama_obat)); ?>', <?php echo e($promoPrice); ?>, '<?php echo e($promoMedicine->image_url ?: ''); ?>', '<?php echo e(addslashes($promoMedicine->brand ?: $promoMedicine->kategori)); ?>', this, '<?php echo e($promoUnitId); ?>')"><i class="fa-solid fa-cart-plus"></i> Masukkan ke Keranjang</button>
          </div>
        </article>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if(!$homeHasFilters && isset($featuredProducts) && $featuredProducts->isNotEmpty()): ?>

<section class="featured-section home-b2c-section">
  <div class="container">
    <div class="featured-header">
      <div class="sec-head-left">
        <span class="sec-tag">🔥 Terlaris B2C</span>
        <h2 class="sec-title" style="margin:0;">Produk Terlaris</h2>
        <p class="home-product-section-description">Produk pilihan terlaris untuk belanja retail B2C.</p>
      </div>
    </div>

    <div class="home-b2c-grid-4x5">
      <?php $__currentLoopData = $featuredProducts->take(20); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $med): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="prod-card">
        <div class="medicine-card-brand">Apotek Medix</div>
        <div class="prod-img">
          <?php echo $__env->make('partials.product-gallery-image', ['medicine' => $med], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php echo $__env->make('partials.product-logo-marker', ['medicine' => $med], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php if($med->kategori_produk): ?>
            <span class="prod-badge-label"><?php echo e($med->kategori_produk==='SKINCARE & KOSMETIK'?'✨':($med->kategori_produk==='ALAT KESEHATAN'?'🩺':'💊')); ?></span>
          <?php endif; ?>
          <?php if(!$internalAccess && $med->getPromoPercent('b2c') > 0): ?>
            <span class="product-discount-badge">Diskon <?php echo e($med->getPromoPercent('b2c')); ?>%</span>
          <?php endif; ?>
        </div>
        <div class="prod-body">
          <div class="medicine-card-principal"><?php echo e($med->brand ?: $med->kategori); ?></div>
          <h3 class="prod-name"><a href="<?php echo e(route('medicines.show', ['id' => $med->id, 'from' => 'home'])); ?>"><?php echo e($med->nama_obat); ?></a></h3>
          <div class="medicine-card-details">
            <p title="<?php echo e($med->komposisi ?: '—'); ?>"><strong>Komposisi:</strong> <?php echo e(\Illuminate\Support\Str::limit($med->komposisi ?: '—', 15, '...')); ?></p>
          </div>
          <?php
            $homePriceChannel = $internalAccess ? 'internal' : 'b2c';
            $homePromoPrice = $med->getChannelPromoPrice($homePriceChannel);
          ?>
          <?php echo $__env->make('partials.product-unit-selector', ['medicine' => $med, 'unitSelectId' => 'home-unit-a-' . $med->id, 'priceTargetId' => 'home-price-current-a-' . $med->id, 'priceChannel' => $homePriceChannel, 'redLogoAccess' => $redLogoAccess], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php
            $stockUnits = (int) $med->available_stock_units;
            $stock = $stockUnits / max(1, $med->units_per_box);
            $stockDisplay = rtrim(rtrim(number_format($stock, 6, ',', '.'), '0'), ',');
          ?>
          <span class="product-stock <?php echo e($stock <= 0 ? 'empty' : ($stock <= 10 ? 'low' : 'available')); ?>" data-stock-badge data-stock-product-id="<?php echo e($med->id); ?>" data-stock-unit-factor="<?php echo e(max(1, $med->units_per_box)); ?>">
            <i class="fa-solid <?php echo e($stock <= 0 ? 'fa-circle-xmark' : 'fa-boxes-stacked'); ?>"></i>
            <?php echo e($stock > 0 ? 'Stok: ' . $stockDisplay : 'Stok habis'); ?>

          </span>
          <div class="medicine-meta medicine-meta-row">
            <span class="medicine-meta-sediaan">
              <?php if($med->sediaan_label): ?>
                <i class="fa-solid fa-cube"></i> <span><?php echo e($med->sediaan_label); ?></span>
              <?php endif; ?>
            </span>
            <span class="medicine-sold-badge" data-sold-product-id="<?php echo e($med->id); ?>">
              <i class="fa-solid fa-fire-flame-curved"></i> Terjual <?php echo e(number_format((int) $med->terjual, 0, ',', '.')); ?>

            </span>
          </div>
          <button class="btn-cart <?php echo e(($med->isRedLogo() && !($redLogoAccess ?? false)) ? 'is-red-logo-locked' : ''); ?>" data-stock-cart <?php if($stock <= 0): echo 'disabled'; endif; ?> data-package-unit-label="<?php echo e($med->package_unit_label); ?>" data-requires-prescription="<?php echo e($med->isRedLogo() ? '1' : '0'); ?>" data-sales-channel="b2c" onclick="addToCart(<?php echo e($med->id); ?>,'<?php echo e(addslashes($med->nama_obat)); ?>',<?php echo e($homePromoPrice); ?>,'<?php echo e($med->image_url ?: ''); ?>','<?php echo e(addslashes($med->brand ?: $med->kategori)); ?>',this,'home-unit-a-<?php echo e($med->id); ?>')">
            <i class="fa-solid fa-cart-plus"></i> Masukkan ke Keranjang
          </button>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
</section>
<?php endif; ?>


<section class="all-b2c-products-section home-b2c-section home-b2c-section--alt" id="homeProductSearchResults">
  <div class="container">
    <div class="featured-header">
      <div class="sec-head-left">
        <div class="home-b2c-catalog-heading">
          <span class="home-b2c-transaction-label"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Transaksi B2C</span>
          <a class="home-b2b-catalog-link" href="<?php echo e(route('products.pbf')); ?>" aria-label="Transaksi B2B">
            <i class="fa-solid fa-box-open" aria-hidden="true"></i> <span class="home-b2b-transaction-text">Transaksi B2B</span>
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
        <h2 class="sec-title" style="margin:0;"><?php echo e($homeHasFilters ? 'Hasil Pencarian' : 'Semua Produk'); ?></h2>
        <p class="home-product-section-description">
          <?php if($homeHasFilters): ?>
            <?php echo e($allB2cProducts->count()); ?> produk ditemukan
            <?php if($search !== ''): ?> untuk "<?php echo e($search); ?>" <?php endif; ?>
            <?php if($kategoriPilihan): ?> pada kategori <?php echo e($kategoriPilihanOptions[$kategoriPilihan]); ?> <?php endif; ?>
            <?php if($perusahaan): ?> dari <?php echo e($perusahaan); ?> <?php endif; ?>.
          <?php else: ?>
            Semua produk retail B2C yang tersedia di Apotek Medix.
          <?php endif; ?>
        </p>
      </div>
    </div>

    <?php if(isset($allB2cProducts) && $allB2cProducts->count() > 0): ?>
    <div class="home-b2c-grid-4x5" id="homeAllB2cProductsGrid">
      <?php $__currentLoopData = $allB2cProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productIndex => $med): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="prod-card" <?php if(!$homeHasFilters && $productIndex >= 10): ?> hidden data-additional-home-product <?php endif; ?>>
        <div class="medicine-card-brand">Apotek Medix</div>
        <div class="prod-img">
          <?php echo $__env->make('partials.product-gallery-image', ['medicine' => $med], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php echo $__env->make('partials.product-logo-marker', ['medicine' => $med], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php if($med->kategori_produk): ?>
            <span class="prod-badge-label"><?php echo e($med->kategori_produk==='SKINCARE & KOSMETIK'?'✨':($med->kategori_produk==='ALAT KESEHATAN'?'🩺':'💊')); ?></span>
          <?php endif; ?>
          <?php if(!$internalAccess && $med->getPromoPercent('b2c') > 0): ?>
            <span class="product-discount-badge">Diskon <?php echo e($med->getPromoPercent('b2c')); ?>%</span>
          <?php endif; ?>
        </div>
        <div class="prod-body">
          <div class="medicine-card-principal"><?php echo e($med->brand ?: $med->kategori); ?></div>
          <h3 class="prod-name"><a href="<?php echo e(route('medicines.show', ['id' => $med->id, 'from' => 'home', 'search' => $search ?: null])); ?>"><?php echo e($med->nama_obat); ?></a></h3>
          <div class="medicine-card-details">
            <p title="<?php echo e($med->komposisi ?: '—'); ?>"><strong>Komposisi:</strong> <?php echo e(\Illuminate\Support\Str::limit($med->komposisi ?: '—', 15, '...')); ?></p>
          </div>
          <?php
            $allPriceChannel = $internalAccess ? 'internal' : 'b2c';
            $allPromoPrice = $med->getChannelPromoPrice($allPriceChannel);
          ?>
          <?php echo $__env->make('partials.product-unit-selector', ['medicine' => $med, 'unitSelectId' => 'home-unit-all-' . $med->id, 'priceTargetId' => 'home-price-current-all-' . $med->id, 'priceChannel' => $allPriceChannel, 'redLogoAccess' => $redLogoAccess], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php
            $allStockUnits = (int) $med->available_stock_units;
            $allStock = $allStockUnits / max(1, $med->units_per_box);
            $allStockDisplay = rtrim(rtrim(number_format($allStock, 6, ',', '.'), '0'), ',');
          ?>
          <span class="product-stock <?php echo e($allStock <= 0 ? 'empty' : ($allStock <= 10 ? 'low' : 'available')); ?>" data-stock-badge data-stock-product-id="<?php echo e($med->id); ?>" data-stock-unit-factor="<?php echo e(max(1, $med->units_per_box)); ?>">
            <i class="fa-solid <?php echo e($allStock <= 0 ? 'fa-circle-xmark' : 'fa-boxes-stacked'); ?>"></i>
            <?php echo e($allStock > 0 ? 'Stok: ' . $allStockDisplay : 'Stok habis'); ?>

          </span>
          <div class="medicine-meta medicine-meta-row">
            <span class="medicine-meta-sediaan">
              <?php if($med->sediaan_label): ?>
                <i class="fa-solid fa-cube"></i> <span><?php echo e($med->sediaan_label); ?></span>
              <?php endif; ?>
            </span>
            <span class="medicine-sold-badge" data-sold-product-id="<?php echo e($med->id); ?>">
              <i class="fa-solid fa-fire-flame-curved"></i> Terjual <?php echo e(number_format((int) $med->terjual, 0, ',', '.')); ?>

            </span>
          </div>
          <button class="btn-cart <?php echo e(($med->isRedLogo() && !($redLogoAccess ?? false)) ? 'is-red-logo-locked' : ''); ?>" data-product-id="<?php echo e($med->id); ?>" data-stock-cart <?php if($allStock <= 0): echo 'disabled'; endif; ?> data-package-unit-label="<?php echo e($med->package_unit_label); ?>" data-requires-prescription="<?php echo e($med->isRedLogo() ? '1' : '0'); ?>" data-sales-channel="b2c" onclick="addToCart(<?php echo e($med->id); ?>,'<?php echo e(addslashes($med->nama_obat)); ?>',<?php echo e($allPromoPrice); ?>,'<?php echo e($med->image_url ?: ''); ?>','<?php echo e(addslashes($med->brand ?: $med->kategori)); ?>',this,'home-unit-all-<?php echo e($med->id); ?>')">
            <i class="fa-solid fa-cart-plus"></i> Masukkan ke Keranjang
          </button>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:16px;border:1px solid #e5e7eb;">
      <i class="fa-solid fa-box-open" style="font-size:3rem;color:#d1d5db;display:block;margin-bottom:1rem;"></i>
      <p style="color:#6b7280;margin:0;"><?php echo e($homeHasFilters ? 'Produk tidak ditemukan. Coba ubah kata kunci atau filter pencarian.' : 'Belum ada produk B2C tersedia saat ini.'); ?></p>
    </div>
    <?php endif; ?>

    <?php if(!$homeHasFilters && $allB2cProducts->count() > 10): ?>
    <div class="featured-cta">
      <button type="button" class="home-show-more-products" id="homeShowMoreProducts"
              aria-expanded="false" aria-controls="homeAllB2cProductsGrid">
        <span>Tampilkan produk lainnya</span>
        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
      </button>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php if(!$homeHasFilters): ?>

<section class="home-service-links" aria-label="Informasi layanan Apotek Medix">
    <div class="home-service-items">
        <div class="home-service-item">
            <span class="home-trust-icon"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i></span>
            <div><strong>Belanja lebih tenang</strong><span>Informasi produk dan satuan tampil jelas.</span></div>
        </div>
        <div class="home-service-item">
            <span class="home-trust-icon"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></span>
            <div><strong>Praktis untuk kebutuhan Anda</strong><span>Pilih produk dan lanjutkan checkout online.</span></div>
        </div>
        <div class="home-service-item">
            <span class="home-trust-icon"><i class="fa-solid fa-user-doctor" aria-hidden="true"></i></span>
            <div><strong>Hubungi Customer Services</strong><a href="https://wa.me/6285890007359?text=Halo%20Apotek%20Medix%2C%20saya%20perlu%20bantuan." target="_blank" rel="noopener">Hubungi CS <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></div>
        </div>
    </div>
</section>
<?php echo $__env->make('components.category-selection', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php if(isset($promoProducts) && $promoProducts->count()): ?>
<style>
/* =============================================
   PROMO PRODUK — Simple & Elegant Background
   ============================================= */
.promo-products-section {
    padding: 3rem 0;
    background: linear-gradient(135deg, #1E88E5 0%, #2e7d32 100%);
    position: relative;
    overflow: hidden;
    border-top: none;
    border-bottom: none;
}

.promo-products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 0.75rem;
    flex-wrap: wrap;
    position: relative;
    z-index: 2;
}

.promo-products-header .sec-tag {
    font-size: 0.875rem;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.promo-products-header .sec-title {
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    margin: 0;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    letter-spacing: -0.5px;
}

/* Horizontal scroll grid */
.promo-products-track-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.5) rgba(255, 255, 255, 0.1);
    margin: 0 -1rem;
    padding: 0.5rem 1rem;
    position: relative;
    z-index: 2;
}

.promo-products-track-wrap::-webkit-scrollbar {
    height: 8px;
}

.promo-products-track-wrap::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.promo-products-track-wrap::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 4px;
}

.promo-products-track-wrap::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.8);
}

.promo-products-track {
    display: flex;
    gap: 1.2rem;
    width: max-content;
    padding: 0.5rem 0;
}

/* Individual promo photo card — Clean Design */
.promo-photo-card {
    width: 165px;
    height: 165px;
    border-radius: 18px;
    overflow: hidden;
    flex-shrink: 0;
    position: relative;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
    background: #fff;
    border: 2px solid rgba(255, 255, 255, 0.4);
}

.promo-photo-card:hover {
    transform: translateY(-8px) scale(1.05);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
    border-color: rgba(255, 255, 255, 0.8);
}

.promo-photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.promo-photo-card:hover img { transform: scale(1.08); }

/* Simple overlay on hover */
.promo-photo-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(30, 136, 229, 0.3) 0%, rgba(56, 192, 155, 0.2) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.promo-photo-card:hover::before { opacity: 1; }

/* Star badge */
.promo-photo-card::after {
    content: '★';
    position: absolute;
    top: 10px;
    right: 10px;
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #fff100 0%, #ffb300 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(255, 179, 0, 0.4);
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    opacity: 0;
    transition: opacity 0.3s ease;
    transform: scale(0);
}

.promo-photo-card:hover::after { 
    opacity: 1; 
    transform: scale(1);
}

@media (max-width: 768px) {
    .promo-products-section { padding: 2.5rem 0; }
    .promo-products-header .sec-title { font-size: 1.6rem; }
    .promo-photo-card { width: 145px; height: 145px; border-radius: 16px; }
    .promo-products-track { gap: 0.95rem; }
}

@media (max-width: 600px) {
    .promo-products-section { padding: 2rem 0; }
    .promo-photo-card { width: 130px; height: 130px; border-radius: 14px; }
    .promo-products-track { gap: 0.8rem; }
    .promo-products-header .sec-title { font-size: 1.4rem; }
    .promo-products-header .sec-tag { font-size: 0.75rem; padding: 0.4rem 0.8rem; }
}

@media (max-width: 400px) {
    .promo-photo-card { width: 115px; height: 115px; border-radius: 12px; }
    .promo-products-track { gap: 0.6rem; }
    .promo-photo-card::after { width: 28px; height: 28px; font-size: 14px; top: 6px; right: 6px; }
}
</style>

<section class="promo-products-section">
  <div class="container">
    <div class="promo-products-header">
      <div class="sec-head-left">
        <span class="sec-tag">🏷️ PENAWARAN EKSKLUSIF</span>
        <h2 class="sec-title">Promo Spesial Hari Ini</h2>
      </div>
      <?php if($promoProducts->count() > 1): ?>
      <div class="promo-products-controls" aria-label="Kontrol promo">
        <button type="button" class="promo-scroll-button" data-promo-direction="-1" aria-label="Promo sebelumnya">
          <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="promo-scroll-button" data-promo-direction="1" aria-label="Promo berikutnya">
          <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
      </div>
      <?php endif; ?>
    </div>

    <div class="promo-products-track-wrap" id="promoTrackWrap" tabindex="0" aria-label="Daftar promo Apotek Medix">
      <div class="promo-products-track" id="promoTrack">
        <?php $__currentLoopData = $promoProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php if($promo->url_tujuan): ?>
            <a href="<?php echo e($promo->url_tujuan); ?>" class="promo-photo-card" title="<?php echo e($promo->judul); ?>" data-tooltip="<?php echo e($promo->judul); ?>">
          <?php else: ?>
            <span class="promo-photo-card" title="<?php echo e($promo->judul); ?>" data-tooltip="<?php echo e($promo->judul); ?>">
          <?php endif; ?>
            <img src="<?php echo e(url('storage/'.$promo->gambar)); ?>" alt="<?php echo e($promo->judul); ?>" loading="lazy">
          <?php if($promo->url_tujuan): ?>
            </a>
          <?php else: ?>
            </span>
          <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>
<?php endif; ?>



<style>
/* =============================================
   KENAPA PILIH KAMI — Modern Card Section
   ============================================= */
.why-cards-section {
    margin-top: 0;
    padding: 5rem 0 5.5rem;
    background: linear-gradient(160deg, #f0f7ff 0%, #eef5ff 50%, #f6fbff 100%);
    position: relative;
    overflow: hidden;
}
.why-cards-section::before {
    content: '';
    position: absolute;
    top: -120px; left: -120px;
    width: 420px; height: 420px;
    background: radial-gradient(circle, rgba(30,136,229,.10) 0%, transparent 70%);
    pointer-events: none;
}
.why-cards-section::after {
    content: '';
    position: absolute;
    bottom: -100px; right: -80px;
    width: 360px; height: 360px;
    background: radial-gradient(circle, rgba(124,179,66,.09) 0%, transparent 70%);
    pointer-events: none;
}

/* Section heading */
.why-section-head {
    text-align: center;
    margin-bottom: 2.5rem;
}
.why-section-tag {
    display: inline-flex; align-items: center; gap: .45rem;
    background: linear-gradient(135deg, #1E88E5, #1565C0);
    color: #fff;
    padding: .45rem 1.2rem;
    border-radius: 999px;
    font-size: .8rem; font-weight: 700;
    letter-spacing: .04em;
    box-shadow: 0 6px 20px rgba(30,136,229,.3);
    margin-bottom: .85rem;
}
.why-section-title {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 .5rem;
    line-height: 1.2;
}
.why-section-sub {
    font-size: .95rem;
    color: #64748b;
    margin: 0;
}

/* Grid */
.why-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
    position: relative;
    z-index: 1;
}

/* Card */
.why-card {
    background: #fff;
    border-radius: 20px;
    padding: 2.25rem 2rem;
    border: 1.5px solid rgba(148, 163, 184, 0.15);
    box-shadow: 0 4px 24px rgba(15,23,42,.06), 0 1px 4px rgba(15,23,42,.04);
    display: flex;
    flex-direction: column;
    gap: 0;
    transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
    position: relative;
    overflow: hidden;
}
/* Coloured top accent bar */
.why-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 20px 20px 0 0;
}
.why-card:nth-child(1)::before { background: linear-gradient(90deg, #1E88E5 0%, #64b5f6 100%); }
.why-card:nth-child(2)::before { background: linear-gradient(90deg, #2e7d32 0%, #66bb6a 100%); }
.why-card:nth-child(3)::before { background: linear-gradient(90deg, #e65100 0%, #ffb74d 100%); }

/* Hover lift */
.why-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 48px rgba(15,23,42,.12), 0 4px 12px rgba(15,23,42,.06);
    border-color: rgba(30,136,229,.25);
}

/* Icon circle */
.why-card-icon-wrap {
    width: 64px; height: 64px;
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.65rem;
    margin-bottom: 1.25rem;
    flex-shrink: 0;
    transition: transform .3s ease;
}
.why-card:hover .why-card-icon-wrap { transform: scale(1.08) rotate(-4deg); }
.why-card:nth-child(1) .why-card-icon-wrap { background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #1565C0; }
.why-card:nth-child(2) .why-card-icon-wrap { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #2e7d32; }
.why-card:nth-child(3) .why-card-icon-wrap { background: linear-gradient(135deg, #fff3e0, #ffe0b2); color: #e65100; }

/* Title */
.why-card h4 {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 .65rem;
    line-height: 1.3;
}

/* Body text */
.why-card p {
    font-size: .92rem;
    color: #64748b;
    line-height: 1.75;
    margin: 0 0 1.25rem;
    flex: 1;
}

/* Badge pill */
.why-card-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    width: fit-content;
    font-size: .76rem; font-weight: 700;
    padding: .4rem .85rem;
    border-radius: 999px;
}
.why-card:nth-child(1) .why-card-badge { background: #e3f2fd; color: #1565C0; }
.why-card:nth-child(2) .why-card-badge { background: #e8f5e9; color: #1b5e20; }
.why-card:nth-child(3) .why-card-badge { background: #fff3e0; color: #bf360c; }

/* ---- Responsive ---- */
@media (max-width: 900px) {
    .why-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.1rem; }
    .why-cards-section { padding: 3.5rem 0 4rem; }
}
@media (max-width: 600px) {
    .why-cards-section { padding: 2.5rem 0 3rem; }
    .why-section-head { margin-bottom: 1.75rem; }
    .why-cards-grid {
        grid-template-columns: 1fr;
        gap: .9rem;
    }
    /* On mobile show cards side-by-side (2 cols) for the top two */
    .why-cards-grid .why-card:nth-child(1),
    .why-cards-grid .why-card:nth-child(2) {
        /* still 1 col on very small screens */
    }
    .why-card {
        padding: 1.5rem 1.25rem;
        border-radius: 16px;
        flex-direction: row;
        align-items: flex-start;
        gap: 1rem;
    }
    .why-card-icon-wrap {
        width: 50px; height: 50px;
        border-radius: 14px;
        font-size: 1.3rem;
        margin-bottom: 0;
        flex-shrink: 0;
    }
    .why-card-content { flex: 1; }
    .why-card h4 { font-size: .98rem; margin-bottom: .4rem; }
    .why-card p { font-size: .84rem; line-height: 1.6; margin-bottom: .75rem; }
    .why-card-badge { font-size: .7rem; padding: .3rem .65rem; }
}
@media (max-width: 400px) {
    .why-card { padding: 1.2rem 1rem; }
    .why-card-icon-wrap { width: 44px; height: 44px; font-size: 1.15rem; }
}
</style>

<?php if(!$homeHasFilters): ?>
<section class="why-cards-section">
  <div class="container">

    
    <div class="why-section-head">
      <div>
        <span class="why-section-tag"><i class="fa-solid fa-star"></i> Kenapa Pilih Kami?</span>
      </div>
      <h2 class="why-section-title">Keunggulan Apotek Medix</h2>
      <p class="why-section-sub">Kami hadir sebagai mitra terpercaya untuk kebutuhan farmasi Anda</p>
    </div>

    <div class="why-cards-grid">

      
      <div class="why-card">
        <div class="why-card-icon-wrap"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="why-card-content">
          <h4>Produk Original & Terjamin</h4>
          <p>Semua produk bersertifikat BPOM, berstandar GMP, dan dijamin keasliannya langsung dari pabrikan resmi terpercaya.</p>
          <span class="why-card-badge"><i class="fa-solid fa-circle-check"></i> Bersertifikat BPOM</span>
        </div>
      </div>

      
      <div class="why-card">
        <div class="why-card-icon-wrap"><i class="fa-solid fa-truck-fast"></i></div>
        <div class="why-card-content">
          <h4>Pengiriman Cepat & Aman</h4>
          <p>Langsung dari distributor ke seluruh Indonesia. Sistem logistik handal dengan cold chain untuk produk berkebutuhan khusus.</p>
          <span class="why-card-badge"><i class="fa-solid fa-location-dot"></i> Seluruh Indonesia</span>
        </div>
      </div>

      
      <div class="why-card">
        <div class="why-card-icon-wrap"><i class="fa-solid fa-tag"></i></div>
        <div class="why-card-content">
          <h4>Harga Distributor Terbaik</h4>
          <p>Harga langsung dari distributor tanpa markup berlebih. Hemat lebih banyak dengan berbelanja langsung dari sumbernya.</p>
          <span class="why-card-badge"><i class="fa-solid fa-percent"></i> Harga Terjangkau</span>
        </div>
      </div>

    </div>
  </div>
</section>
<?php endif; ?>

<style>
.promo-products-section { background:#f8fafc;color:#1e293b; }
.promo-products-header .sec-tag { border:1px solid #bae6fd;background:#ecfeff;color:#0052cc; }
.promo-products-header .sec-title { color:#1e293b;text-shadow:none; }
.promo-products-track-wrap { margin:0;padding:.25rem 0;scroll-snap-type:x mandatory;scrollbar-color:#94a3b8 #e2e8f0; }
.promo-products-track { gap:1rem; }
.promo-photo-card { width:clamp(220px,30vw,340px);height:clamp(135px,18vw,190px);border:1px solid #e2e8f0;border-radius:16px;scroll-snap-align:start;box-shadow:0 8px 22px rgba(15,23,42,.08); }
.promo-photo-card::after { display:none; }
.promo-scroll-button { display:inline-grid;width:42px;height:42px;place-items:center;border:1px solid #cbd5e1;border-radius:50%;background:#fff;color:#0052cc;font-size:1rem;cursor:pointer;transition:background .2s,color .2s,border-color .2s; }
.promo-scroll-button:hover { border-color:#0052cc;background:#0052cc;color:#fff; }
.promo-scroll-button:focus-visible { outline:3px solid #00d1b2;outline-offset:2px; }
.home-brand-hero { background:radial-gradient(circle at 82% 18%,rgba(0,209,178,.32),transparent 34%),linear-gradient(118deg,#003eaa 0%,#0052cc 56%,#0878d1 100%); }
.home-category-section { padding:1.5rem 0 2rem;background:#fff; }
.home-category-heading { display:flex;align-items:end;justify-content:space-between;gap:1rem;margin-bottom:1rem; }
.home-category-heading h2 { margin:.2rem 0 0;color:#1e293b;font-size:clamp(1.3rem,2vw,1.7rem); }
.home-section-eyebrow { color:#0052cc;font-size:.75rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase; }
.home-category-heading > a { color:#0052cc;font-size:.85rem;font-weight:700;text-decoration:none;white-space:nowrap; }
.home-category-controls { display:flex;align-items:center;gap:.5rem; }
.home-category-controls > a { margin-right:.35rem;color:#0052cc;font-size:.85rem;font-weight:700;text-decoration:none;white-space:nowrap; }
.home-category-scroll { display:inline-grid;width:36px;height:36px;place-items:center;border:1px solid #cbd5e1;border-radius:50%;background:#fff;color:#0052cc;cursor:pointer; }
.home-category-scroll:hover { border-color:#0052cc;background:#0052cc;color:#fff; }
.home-category-grid { display:grid;grid-auto-columns:minmax(145px,1fr);grid-auto-flow:column;gap:.8rem;overflow-x:auto;overscroll-behavior-inline:contain;scroll-snap-type:x mandatory;scrollbar-width:thin;scrollbar-color:#bfdbfe transparent;padding:.15rem .1rem .65rem; }
.home-category-card { display:flex;min-height:154px;flex-direction:column;align-items:center;justify-content:center;gap:.65rem;padding:.75rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:#1e293b;text-align:center;text-decoration:none;transition:border-color .2s,transform .2s,box-shadow .2s;scroll-snap-align:start; }
.home-category-card:hover { transform:translateY(-2px);border-color:#93c5fd;box-shadow:0 8px 18px rgba(0,82,204,.08); }
.home-category-card > span { display:grid;width:76px;height:76px;place-items:center;border-radius:50%;background:#0052cc!important;color:#fff!important;font-size:1.8rem; }
.home-category-card > span i { color:#fff!important; }
.home-category-card strong { max-width:145px;font-size:.9rem;line-height:1.35;font-weight:600; }
.home-category-card:focus-visible,.home-category-heading a:focus-visible,.home-category-scroll:focus-visible { outline:3px solid #00d1b2;outline-offset:3px;border-radius:5px; }
.category-selection-section,.cat-section { background:#fff; }
.category-icon { background:#eff6ff!important; }
.category-icon i,.cat-icon i { color:#0052cc!important; }
.featured-section { background:#fff; }
.featured-section .sec-link,.featured-cta a { color:#0052cc; }
.featured-nav-btn { color:#0052cc; }
.prod-section,.prod-grid { background:#f8fafc; }
@media(max-width:768px) {
    .home-service-items { grid-template-columns:1fr; }
    .home-service-links { margin-top:1rem; }
    .home-category-grid { grid-auto-columns:minmax(130px,38vw);gap:.6rem; }
    .home-category-card { min-height:140px; }
    .promo-products-controls { display:flex;gap:.5rem; }
    .promo-scroll-button { width:38px;height:38px; }
}
@media(max-width:480px) {
    .home-service-links { width:calc(100% - 1.3rem); }
    .home-hero-search button span { display:inline; }
    .home-b2c-catalog-heading { gap:.4rem; }
    .home-b2c-transaction-label,.home-b2b-catalog-link { min-height:44px;gap:.35rem;padding:.45rem .65rem;font-size:.78rem; }
    .home-category-heading { align-items:center; }
    .home-category-controls > a { display:none; }
    .home-category-scroll { width:32px;height:32px; }
    .home-category-grid { grid-auto-columns:minmax(122px,42vw); }
    .home-category-card { min-height:132px;padding:.6rem .35rem; }
    .home-category-card > span { width:64px;height:64px;font-size:1.5rem; }
    .home-category-card strong { font-size:.78rem; }
}
@media(prefers-reduced-motion:reduce) {
    .promo-scroll-button { transition:none; }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>window.cartSettings = { storageKey: <?php echo json_encode($internalAccess ? 'apotekmedix_cart_internal' : 'apotekmedix_cart_b2c', 15, 512) ?>, priceChannel: <?php echo json_encode($internalAccess ? 'internal' : 'b2c', 15, 512) ?>, syncProductPrices: true, priceMode: <?php echo json_encode($internalAccess ? 'internal' : 'b2c', 15, 512) ?> };</script>
<?php echo $__env->make('partials.cart', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script>
(() => {
    <?php if($homeHasFilters): ?>
    if (window.location.hash !== '#homeSearchSection') {
        document.getElementById('homeProductSearchResults')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    <?php endif; ?>

    const showMoreProductsButton = document.getElementById('homeShowMoreProducts');
    if (showMoreProductsButton) {
        const productsGrid = document.getElementById(showMoreProductsButton.getAttribute('aria-controls'));
        const allProducts = productsGrid
            ? Array.from(productsGrid.querySelectorAll('.prod-card'))
            : [];
        let expanded = false;

        const updateVisibleProducts = () => {
            const visibleLimit = 10;
            allProducts.forEach((product, index) => {
                product.hidden = !expanded && index >= visibleLimit;
            });

            showMoreProductsButton.hidden = allProducts.length <= visibleLimit;
            showMoreProductsButton.setAttribute('aria-expanded', String(expanded));
            showMoreProductsButton.querySelector('span').textContent = expanded
                ? 'Tampilkan lebih sedikit'
                : 'Tampilkan produk lainnya';
            showMoreProductsButton.querySelector('i').className = expanded
                ? 'fa-solid fa-chevron-up'
                : 'fa-solid fa-chevron-down';
        };

        updateVisibleProducts();
        showMoreProductsButton.addEventListener('click', () => {
            expanded = !expanded;
            updateVisibleProducts();
        });
    }

    const categoryGrid = document.getElementById('homeCategoryGrid');
    if (categoryGrid) {
        document.querySelectorAll('[data-direction]').forEach((button) => {
            button.addEventListener('click', () => {
                const card = categoryGrid.querySelector('.home-category-card');
                const gap = Number.parseFloat(getComputedStyle(categoryGrid).gap) || 0;
                const step = card ? card.getBoundingClientRect().width + gap : categoryGrid.clientWidth;
                categoryGrid.scrollBy({
                    left: step * Number(button.dataset.direction),
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
                });
            });
        });
    }

    const wrap = document.getElementById('promoTrackWrap');
    const track = document.getElementById('promoTrack');
    if (!wrap || !track) return;

    document.querySelectorAll('[data-promo-direction]').forEach((button) => {
        button.addEventListener('click', () => {
            const card = track.querySelector('.promo-photo-card');
            const gap = Number.parseFloat(getComputedStyle(track).gap) || 0;
            const step = card ? card.getBoundingClientRect().width + gap : wrap.clientWidth;
            wrap.scrollBy({
                left: step * Number(button.dataset.promoDirection),
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
            });
        });
    });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.frontend', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\home.blade.php ENDPATH**/ ?>