<?php
    $accessReturnTo = $accessReturnTo ?? 'products.apotek';
?>
<style>
    .catalog-access-controls { display:inline-flex; align-items:center; gap:.3rem; vertical-align:middle; }
    .catalog-access-trigger,.catalog-access-exit { display:inline-grid; place-items:center; width:24px !important; min-width:24px !important; height:24px !important; min-height:24px !important; padding:0 !important; border:1px solid rgba(255,255,255,.55); border-radius:50%; color:#fff; cursor:pointer; box-shadow:0 2px 5px rgba(15,23,42,.2); transition:transform .18s,filter .18s; }
    .catalog-access-trigger:hover,.catalog-access-exit:hover { transform:translateY(-1px); filter:brightness(1.08); }
    .catalog-access-trigger--internal { background:#dc2626; font-size:.72rem; font-weight:900; }
    .catalog-access-trigger--b2c { background:#f59e0b; font-size:.65rem; }
    .catalog-access-exit { border:0; background:#334155; font-size:.6rem; }
    .catalog-access-state { display:inline-flex; align-items:center; gap:.25rem; color:#fff; font-size:.65rem; font-weight:700; }
    .catalog-access-state form { display:inline-flex; margin:0; }
    .catalog-access-dialog { width:min(420px,calc(100% - 2rem)); max-width:420px; padding:0; border:1px solid #dbeafe; border-radius:16px; color:#0f172a; box-shadow:0 24px 70px rgba(15,23,42,.35); }
    .catalog-access-dialog::backdrop { background:rgba(15,23,42,.68); backdrop-filter:blur(3px); }
    .catalog-access-dialog form { padding:1.25rem; }
    .catalog-access-dialog h2 { margin:0 0 .35rem; font-size:1.15rem; color:#0f172a; }
    .catalog-access-dialog p { margin:0 0 .9rem; color:#475569; font-size:.86rem; line-height:1.5; }
    .catalog-access-dialog label { display:block; margin-bottom:.35rem; color:#334155; font-size:.82rem; font-weight:700; }
    .catalog-access-dialog input { width:100%; padding:.7rem .8rem; border:1px solid #cbd5e1; border-radius:8px; color:#0f172a; font:inherit; letter-spacing:.16em; }
    .catalog-access-dialog-actions { display:flex; justify-content:flex-end; gap:.55rem; margin-top:1rem; }
    .catalog-access-dialog-actions button { padding:.6rem .9rem; border:0; border-radius:8px; font:inherit; font-weight:700; cursor:pointer; }
    .catalog-access-dialog-submit { background:#0052cc; color:#fff; }
    .catalog-access-dialog-cancel { background:#e2e8f0; color:#334155; }
    .catalog-access-error { margin:.65rem 0 0!important; color:#b91c1c!important; font-weight:700; }
</style>
<div class="catalog-access-controls" aria-label="Akses katalog">
    <button type="button" class="catalog-access-trigger catalog-access-trigger--b2c" aria-label="Masukkan kode akses harga internal" aria-haspopup="dialog" onclick="document.getElementById('internalAccessDialog').showModal()">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
    </button>
    <?php if($internalAccess ?? false): ?>
        <div class="catalog-access-state"><span>Internal aktif</span>
            <form action="<?php echo e(route('products.apotek.access.logout', 'internal')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="<?php echo e($accessReturnTo); ?>">
                <button type="submit" class="catalog-access-exit" aria-label="Keluar dari akses harga internal" title="Keluar dari akses harga internal"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></button>
            </form>
        </div>
    <?php endif; ?>

    <button type="button" class="catalog-access-trigger catalog-access-trigger--internal" aria-label="Masukkan kode B2C untuk membuka produk berlogo merah" aria-haspopup="dialog" onclick="document.getElementById('b2cAccessDialog').showModal()">K</button>
    <?php if($b2cAccess ?? false): ?>
        <div class="catalog-access-state"><span>B2C aktif</span>
            <form action="<?php echo e(route('products.apotek.access.logout', 'b2c')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="<?php echo e($accessReturnTo); ?>">
                <button type="submit" class="catalog-access-exit" aria-label="Keluar dari akses B2C" title="Keluar dari akses B2C"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></button>
            </form>
        </div>
    <?php endif; ?>
</div>

<dialog id="b2cAccessDialog" class="catalog-access-dialog" aria-labelledby="b2cAccessDialogTitle">
    <form action="<?php echo e(route('products.apotek.activate')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="redirect_to" value="<?php echo e($accessReturnTo); ?>">
        <input type="hidden" name="access_type" value="b2c">
        <h2 id="b2cAccessDialogTitle">B2C FULL AKSES</h2>
        <label for="b2cAccessCode">Kode akses B2C</label>
        <input id="b2cAccessCode" name="kode" type="password" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" autocomplete="one-time-code" required>
        <?php if($errors->has('kode') && old('redirect_to') === $accessReturnTo && old('access_type') === 'b2c'): ?>
            <p class="catalog-access-error" role="alert"><?php echo e($errors->first('kode')); ?></p>
        <?php endif; ?>
        <div class="catalog-access-dialog-actions">
            <button type="button" class="catalog-access-dialog-cancel" onclick="this.closest('dialog').close()">Tutup</button>
            <button type="submit" class="catalog-access-dialog-submit">Aktifkan akses</button>
        </div>
    </form>
</dialog>

<dialog id="internalAccessDialog" class="catalog-access-dialog" aria-labelledby="internalAccessDialogTitle">
    <form action="<?php echo e(route('products.apotek.internal.activate')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="redirect_to" value="<?php echo e($accessReturnTo); ?>">
        <input type="hidden" name="access_type" value="internal">
        <h2 id="internalAccessDialogTitle">TRANSAKSI INTERNAL</h2>
        <label for="internalAccessCode">Kode akses Internal</label>
        <input id="internalAccessCode" name="kode" type="password" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" autocomplete="one-time-code" required>
        <?php if($errors->has('kode') && old('redirect_to') === $accessReturnTo && old('access_type') === 'internal'): ?>
            <p class="catalog-access-error" role="alert"><?php echo e($errors->first('kode')); ?></p>
        <?php endif; ?>
        <div class="catalog-access-dialog-actions">
            <button type="button" class="catalog-access-dialog-cancel" onclick="this.closest('dialog').close()">Tutup</button>
            <button type="submit" class="catalog-access-dialog-submit">Aktifkan Internal</button>
        </div>
    </form>
</dialog>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = <?php echo json_encode(old('access_type'), 15, 512) ?>;
    if (dialog === 'b2c' || dialog === 'internal') {
        document.getElementById(dialog === 'b2c' ? 'b2cAccessDialog' : 'internalAccessDialog')?.showModal();
    }
});
</script>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\partials\catalog-access-controls.blade.php ENDPATH**/ ?>