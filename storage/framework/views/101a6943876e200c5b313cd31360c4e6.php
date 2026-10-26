<!-- CATEGORY SELECTION SECTION - GOAPOTIK STYLE -->
<style>
/* ===== CATEGORY PILIHAN ===== */
.category-selection-section {
    background: linear-gradient(135deg, #0D47A1 0%, #1565C0 50%, #1E88E5 100%);
    padding: 2rem 0;
    margin: center;
    border-radius: 0;
    box-shadow: 0 8px 32px rgba(13, 71, 161, 0.25);
    position: relative;
    overflow: visible;
    width: 100%;
    margin: 0 auto;
}

.category-selection-section::before {
    display: none;
}

.category-selection-section::after {
    display: none;
}

.category-selection-inner {
    position: relative;
    z-index: 1;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 2rem;
}

.category-title {
    display: none;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 180px));
    gap: 1.25rem;
    margin: 0 auto;
    padding: 0;
    justify-content: center;
    width: auto;
}

.category-dropdown-container {
    width: auto;
    max-width: 180px;
}

.category-icon-btn {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1.25rem 1rem;
    background: transparent;
    border-radius: 20px;
    text-decoration: none;
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    border: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: none;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.category-icon-btn:hover {
    transform: scale(1.08);
}

.category-icon {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    position: relative;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2), inset 0 2px 4px rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.category-icon-btn:hover .category-icon {
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3), inset 0 2px 4px rgba(255, 255, 255, 0.2);
    transform: translateY(-4px);
}

/* Color untuk masing-masing kategori */
.category-icon.obat {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.category-icon.alkes {
    background: linear-gradient(135deg, #f093fb, #f5576c);
}

.category-icon.kecantikan {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.category-icon.nutrisi {
    background: linear-gradient(135deg, #43e97b, #38f9d7);
}

.category-icon.apotik {
    background: linear-gradient(135deg, #ffb74d, #ff8a65);
}

.category-icon.pbf {
    background: linear-gradient(135deg, #66bb6a, #43a047);
}

.category-icon.jasa {
    background: linear-gradient(135deg, #fa709a, #fee140);
}

.category-icon i {
    color: #fff;
}

/* ===== DROPDOWN SUBMENU ===== */
.category-dropdown-container {
    position: relative;
    display: inline-block;
    width: 100%;
}

.dropdown-menu {
    display: none;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
    padding: 1rem 0;
    z-index: 1000;
    width: min(320px, calc(100vw - 2rem));
    max-width: 100%;
    border: 1px solid rgba(0, 0, 0, 0.05);
    top: calc(100% + 10px);
}

.dropdown-menu.show {
    display: block;
    animation: slideDownSmooth 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideDownSmooth {
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.9rem 1.5rem;
    color: #374151;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 4px solid transparent;
}

.dropdown-item i {
    font-size: 1.2rem;
    color: #1E88E5;
    flex-shrink: 0;
    display: inline-block;
    width: 1.5rem;
    text-align: center;
}

.dropdown-item:hover {
    background: #f0f9ff;
    border-left-color: #1E88E5;
    padding-left: 1.75rem;
    color: #1E88E5;
    font-weight: 600;
}

.dropdown-item:hover i {
    color: #1E88E5;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .category-grid {
        grid-template-columns: repeat(3, minmax(0, 120px));
        gap: 0.8rem;
        justify-items: center;
        justify-content: center;
        align-items: center;
        max-width: 420px;
        margin: 0 auto;
    }

    .category-dropdown-container {
        width: auto;
        max-width: 140px;
    }

    .category-selection-section {
        padding: 1.5rem 0;
        margin: 0;
    }

    .category-icon-btn {
        padding: 0.85rem 0.5rem;
        gap: 0.5rem;
        font-size: 0.75rem;
    }

    .category-icon {
        width: 52px;
        height: 52px;
        font-size: 1.8rem;
    }

    .dropdown-menu {
        min-width: 280px;
        padding: 0.75rem 0;
    }

    .dropdown-item {
        padding: 0.75rem 1.25rem;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .category-grid {
        grid-template-columns: repeat(3, minmax(0, 100px));
        gap: 0.6rem;
        justify-items: center;
        justify-content: center;
        align-items: center;
        max-width: 330px;
        margin: 0 auto;
    }

    .category-dropdown-container {
        width: auto;
        max-width: 110px;
    }

    .category-selection-section {
        padding: 1.25rem 0;
        margin: 0;
    }

    .category-icon-btn {
        padding: 0.6rem 0.4rem;
        gap: 0.35rem;
        font-size: 0.65rem;
    }

    .category-icon {
        width: 44px;
        height: 44px;
        font-size: 1.5rem;
        border-radius: 14px;
    }

    .dropdown-menu {
        position: fixed;
        left: 0.75rem;
        right: 0.75rem;
        top: auto;
        transform: none;
        width: auto;
        min-width: unset;
        max-width: calc(100vw - 1.5rem);
        padding: 0.5rem 0;
        font-size: 0.8rem;
    }

    .dropdown-item {
        padding: 0.6rem 1rem;
    }

    .dropdown-item i {
        font-size: 1rem;
    }
}
</style>

<style>
.social-follow-section {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 68%, #164E63 100%);
    padding: 2.5rem 0;
    width: 100%;
    overflow: hidden;
    border-top: 2px solid #06B6D4;
}

.social-follow-inner {
    display: grid;
    grid-template-columns: 40px minmax(0, 460px) 40px;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
}

.social-follow-title {
    grid-column: 1 / -1;
    color: #fff;
    font-size: 1.15rem;
    font-weight: 800;
    margin: 0;
    text-align: center;
}

.social-follow-links {
    position: relative;
    height: 118px;
    min-width: 0;
    perspective: 900px;
    touch-action: pan-y;
    cursor: grab;
}

.social-follow-links.is-dragging { cursor: grabbing; }

.social-follow-link {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 64px;
    height: 64px;
    margin: -32px 0 0 -32px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.7rem;
    text-decoration: none;
    pointer-events: auto;
    cursor: pointer;
    transform-style: preserve-3d;
    transition: transform 0.6s ease, opacity 0.6s ease, filter 0.6s ease;
    will-change: transform;
}

.social-follow-link img {
    width: 70%;
    height: 70%;
    object-fit: contain;
    border-radius: 8px;
}

.social-follow-link:hover {
    color: #fff;
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
}

.social-follow-control {
    width: 36px;
    height: 36px;
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 50%;
    background: rgba(255,255,255,0.14);
    color: #fff;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
}

#socialFollowPrev { grid-column: 1; grid-row: 2; justify-self: end; }
#socialFollowLinks { grid-column: 2; grid-row: 2; }
#socialFollowNext { grid-column: 3; grid-row: 2; justify-self: start; }

.social-follow-control:hover { background: rgba(255,255,255,0.28); transform: scale(1.08); }

@media (max-width: 576px) {
    .social-follow-section { padding: 1.5rem 0.75rem; }
    .social-follow-inner { grid-template-columns: 34px minmax(0, 270px) 34px; gap: 0.25rem; }
    .social-follow-title { grid-column: 1 / -1; text-align: center; }
    .social-follow-links { grid-column: 2; height: 92px; }
    .social-follow-link { width: 52px; height: 52px; margin: -26px 0 0 -26px; font-size: 1.35rem; border-radius: 12px; }
}
</style>

<div class="social-follow-section">
    <div class="container social-follow-inner">
        <h2 class="social-follow-title"><i class="fa-solid fa-share-nodes"></i> Ikuti Kami</h2>
        <button type="button" class="social-follow-control" id="socialFollowPrev" title="Sebelumnya" aria-label="Sosial sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="social-follow-links" id="socialFollowLinks">
            <a href="https://www.instagram.com/apotekmedix" target="_blank" rel="noopener" class="social-follow-link" style="background:linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);" title="Instagram" aria-label="Instagram">
                <i class="fa-brands fa-instagram"></i>
            </a>
            <a href="https://wa.me/6285882717844" target="_blank" rel="noopener" class="social-follow-link" style="background:#25D366;" title="WhatsApp" aria-label="WhatsApp">
                <i class="fa-brands fa-whatsapp"></i>
            </a>
            <a href="https://facebook.com/apotekmedix" target="_blank" rel="noopener" class="social-follow-link" style="background:#1877F2;" title="Facebook" aria-label="Facebook">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
            <a href="https://pin.it/69pbGoR2f" target="_blank" rel="noopener" class="social-follow-link" style="background:#E60023;" title="Pinterest" aria-label="Pinterest">
                <i class="fa-brands fa-pinterest-p"></i>
            </a>
            <a href="https://www.youtube.com/@apotekmedix" target="_blank" rel="noopener" class="social-follow-link" style="background:#FF0000;" title="YouTube" aria-label="YouTube">
                <i class="fa-brands fa-youtube"></i>
            </a>
            <a href="https://www.tiktok.com/@apotekmedix" target="_blank" rel="noopener" class="social-follow-link" style="background:#111;" title="TikTok" aria-label="TikTok">
                <i class="fa-brands fa-tiktok"></i>
            </a>
        </div>
        <button type="button" class="social-follow-control" id="socialFollowNext" title="Berikutnya" aria-label="Sosial berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
</div>

<script>
(function () {
    const orbit = document.getElementById('socialFollowLinks');
    if (!orbit) return;

    const links = Array.from(orbit.querySelectorAll('.social-follow-link'));
    const previous = document.getElementById('socialFollowPrev');
    const next = document.getElementById('socialFollowNext');
    let offset = 0;
    let timer;
    let dragStartX = null;
    let dragged = false;

    function render() {
        const count = links.length;
        const radius = window.innerWidth <= 576 ? 112 : 170;
        links.forEach((link, index) => {
            const angle = ((index + offset) / count) * Math.PI * 2 - Math.PI / 2;
            const depth = Math.sin(angle);
            const x = Math.cos(angle) * radius;
            const z = depth * 90;
            const scale = 0.82 + (depth + 1) * 0.12;
            link.style.transform = `translate3d(${x}px, ${depth * 12}px, ${z}px) scale(${scale})`;
            link.style.opacity = String(0.58 + (depth + 1) * 0.21);
            link.style.filter = `brightness(${0.82 + (depth + 1) * 0.09})`;
            link.style.zIndex = String(Math.round(100 + z));
        });
    }

    function move(step) {
        offset += step;
        render();
    }

    function startAutoOrbit() {
        clearInterval(timer);
        timer = setInterval(() => move(-1), 1900);
    }

    previous.addEventListener('click', () => { move(-1); startAutoOrbit(); });
    next.addEventListener('click', () => { move(1); startAutoOrbit(); });
    orbit.addEventListener('pointerdown', event => {
        dragStartX = event.clientX;
        dragged = false;
        orbit.classList.add('is-dragging');
    });
    orbit.addEventListener('pointerup', event => {
        if (dragStartX !== null && Math.abs(event.clientX - dragStartX) > 24) {
            dragged = true;
            move(event.clientX > dragStartX ? 1 : -1);
        }
        dragStartX = null;
        orbit.classList.remove('is-dragging');
        startAutoOrbit();
    });
    links.forEach(link => link.addEventListener('click', event => {
        if (dragged) event.preventDefault();
        dragged = false;
    }));
    orbit.addEventListener('pointercancel', () => {
        dragStartX = null;
        orbit.classList.remove('is-dragging');
        startAutoOrbit();
    });
    window.addEventListener('resize', render);
    render();
    startAutoOrbit();
})();
</script>

<?php if(false): ?>
<div class="category-selection-section">
    <div class="container">
        <div class="category-selection-inner">
            <div class="category-grid">
                <!-- APOTIK -->
                <div class="category-dropdown-container">
                    <button class="category-icon-btn" onclick="toggleDropdown(event)">
                        <div class="category-icon apotik">
                            <i class="fa-solid fa-prescription-bottle-medical"></i>
                        </div>
                        <span>APOTIK</span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo e(route('products.apotek')); ?>" class="dropdown-item">
                            <i class="fa-solid fa-th-large"></i>
                            <span>Semua Produk Apotek</span>
                        </a>
                        <a href="<?php echo e(route('products.apotek')); ?>?kategori_produk=OBAT" class="dropdown-item">
                            <i class="fa-solid fa-pills"></i>
                            <span>Obat Oral</span>
                        </a>
                        <a href="<?php echo e(route('products.apotek')); ?>?kategori_produk=OBAT" class="dropdown-item">
                            <i class="fa-solid fa-syringe"></i>
                            <span>Obat Injeksi</span>
                        </a>
                        <a href="<?php echo e(route('products.apotek')); ?>?kategori_produk=OBAT" class="dropdown-item">
                            <i class="fa-solid fa-bottle-droplet"></i>
                            <span>Obat Luar</span>
                        </a>
                        <a href="<?php echo e(route('products.apotek')); ?>?kategori_produk=OBAT" class="dropdown-item">
                            <i class="fa-solid fa-tablets"></i>
                            <span>Obat OTC</span>
                        </a>
                        <a href="<?php echo e(route('products.apotek')); ?>?kategori_produk=OBAT" class="dropdown-item">
                            <i class="fa-solid fa-bottle-droplet"></i>
                            <span>Susu</span>
                        </a>
                        <a href="<?php echo e(route('products.apotek')); ?>?kategori_produk=OBAT" class="dropdown-item">
                            <i class="fa-solid fa-heart"></i>
                            <span>Suplemen</span>
                        </a>
                        <a href="<?php echo e(route('products.apotek')); ?>?kategori_produk=OBAT" class="dropdown-item">
                            <i class="fa-solid fa-leaf"></i>
                            <span>Herbal</span>
                        </a>
                    </div>
                </div>

                <!-- KECANTIKAN -->
                <div class="category-dropdown-container">
                    <button class="category-icon-btn" onclick="toggleDropdown(event)">
                        <div class="category-icon kecantikan">
                            <i class="fa-solid fa-spa"></i>
                        </div>
                        <span>KECANTIKAN</span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo e(route('products.index')); ?>?kategori_produk=SKINCARE+%26+KOSMETIK" class="dropdown-item">
                            <i class="fa-solid fa-th-large"></i>
                            <span>Skincare</span>
                        </a>
                        <a href="<?php echo e(route('products.index')); ?>?kategori_produk=SKINCARE+%26+KOSMETIK" class="dropdown-item">
                            <i class="fa-solid fa-th-large"></i>
                            <span>Kosmetik</span>
                        </a>
                        <a href="<?php echo e(route('products.index')); ?>?kategori_produk=SKINCARE+%26+KOSMETIK" class="dropdown-item">
                            <i class="fa-solid fa-th-large"></i>
                            <span>Material Klinik</span>
                        </a>
                    </div>
                </div>

                <!-- ALAT KESEHATAN -->
                <div class="category-dropdown-container">
                    <button class="category-icon-btn" onclick="toggleDropdown(event)">
                        <div class="category-icon alkes">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <span>ALKES</span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo e(route('products.index')); ?>?kategori_produk=ALAT+KESEHATAN" class="dropdown-item">
                            <i class="fa-solid fa-th-large"></i>
                            <span>Semua ALKES</span>
                        </a>
                        <a href="<?php echo e(route('products.apotek')); ?>?kategori_produk=ALAT+KESEHATAN" class="dropdown-item">
                            <i class="fa-solid fa-store"></i>
                            <span>Alkes Apotek</span>
                        </a>
                        <a href="<?php echo e(route('products.pbf')); ?>?kategori_produk=ALAT+KESEHATAN" class="dropdown-item">
                            <i class="fa-solid fa-warehouse"></i>
                            <span>Alkes PBF</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDropdown(e) {
    e.preventDefault();
    const button = e.currentTarget;
    const dropdown = button.nextElementSibling;
    
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu !== dropdown) {
            menu.classList.remove('show');
        }
    });

    // Toggle current dropdown
    if (dropdown && dropdown.classList.contains('dropdown-menu')) {
        dropdown.classList.toggle('show');
        if (dropdown.classList.contains('show')) {
            positionDropdown(dropdown, button);
        }
    }
}

function positionDropdown(dropdown, button) {
    if (window.innerWidth <= 768) {
        const rect = button.getBoundingClientRect();
        dropdown.style.top = `${rect.bottom + 10}px`;
        dropdown.style.left = '0.75rem';
        dropdown.style.right = '0.75rem';
        dropdown.style.transform = 'none';
    } else {
        dropdown.style.top = '';
        dropdown.style.left = '50%';
        dropdown.style.right = '';
        dropdown.style.transform = 'translateX(-50%)';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.category-dropdown-container')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Close dropdown when clicking on a link
document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function() {
        this.closest('.dropdown-menu').classList.remove('show');
    });
});

// Handle window scroll to close dropdowns while scrolling
window.addEventListener('scroll', function() {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        menu.classList.remove('show');
    });
});
</script>
<?php endif; ?>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/components/category-selection.blade.php ENDPATH**/ ?>