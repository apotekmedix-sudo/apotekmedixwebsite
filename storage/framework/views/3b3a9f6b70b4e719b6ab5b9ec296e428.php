<?php
    $valIndikasi  = $indikasi ?? request('indikasi', '');
    $valKomposisi = $komposisi ?? request('komposisi', '');
    $valSediaan   = $sediaan ?? request('sediaan', '');
    $valDosis     = $dosis ?? request('dosis', '');
    $valGolongan  = $golongan ?? request('golongan', '');

    $sediaanItems = $sediaanList ?? [
        'Tablet', 'Sirup', 'Kapsul', 'Kaplet', 'Krim', 'Salep', 'Gel',
        'Drops', 'Suspensi', 'Sachet', 'Botol', 'Box', 'Strip', 'Tube', 'Fls',
    ];
    $golonganItems = $golonganList ?? [
        'Obat Bebas', 'Bebas Terbatas', 'Obat Keras', 'Vitamin & Suplemen', 'Obat Herbal / Jamu',
    ];
?>

<div class="medix-attr-filter-grid" style="width:100%;display:grid;grid-template-columns:repeat(auto-fit,minmax(165px,1fr));gap:0.65rem;padding-top:0.75rem;margin-top:0.35rem;border-top:1px dashed #e2e8f0;">
    <div class="medix-attr-filter-item">
        <label style="display:block;font-weight:600;font-size:0.76rem;color:#475569;margin-bottom:0.3rem;">
            <i class="fa-solid fa-stethoscope" style="color:#0E7490;"></i> Indikasi / Keluhan
        </label>
        <input
            type="text"
            name="indikasi"
            value="<?php echo e($valIndikasi); ?>"
            placeholder="Cth: demam, batuk, maag..."
            style="width:100%;padding:0.52rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.84rem;color:#1e293b;background:#f8fafc;outline:none;"
        >
    </div>

    <div class="medix-attr-filter-item">
        <label style="display:block;font-weight:600;font-size:0.76rem;color:#475569;margin-bottom:0.3rem;">
            <i class="fa-solid fa-flask" style="color:#0E7490;"></i> Komposisi
        </label>
        <input
            type="text"
            name="komposisi"
            value="<?php echo e($valKomposisi); ?>"
            placeholder="Cth: paracetamol, vit c..."
            style="width:100%;padding:0.52rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.84rem;color:#1e293b;background:#f8fafc;outline:none;"
        >
    </div>

    <div class="medix-attr-filter-item">
        <label style="display:block;font-weight:600;font-size:0.76rem;color:#475569;margin-bottom:0.3rem;">
            <i class="fa-solid fa-prescription-bottle-medical" style="color:#0E7490;"></i> Sediaan (Sirup/Tablet)
        </label>
        <input
            type="text"
            name="sediaan"
            list="medixSediaanOptions"
            value="<?php echo e($valSediaan); ?>"
            placeholder="Cth: Sirup, Tablet, Kapsul..."
            style="width:100%;padding:0.52rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.84rem;color:#1e293b;background:#f8fafc;outline:none;"
        >
        <datalist id="medixSediaanOptions">
            <?php $__currentLoopData = $sediaanItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($item); ?>"></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </datalist>
    </div>

    <div class="medix-attr-filter-item">
        <label style="display:block;font-weight:600;font-size:0.76rem;color:#475569;margin-bottom:0.3rem;">
            <i class="fa-solid fa-scale-balanced" style="color:#0E7490;"></i> Dosis
        </label>
        <input
            type="text"
            name="dosis"
            value="<?php echo e($valDosis); ?>"
            placeholder="Cth: 500mg, 100mg, 3x1..."
            style="width:100%;padding:0.52rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.84rem;color:#1e293b;background:#f8fafc;outline:none;"
        >
    </div>

    <div class="medix-attr-filter-item">
        <label style="display:block;font-weight:600;font-size:0.76rem;color:#475569;margin-bottom:0.3rem;">
            <i class="fa-solid fa-shield-heart" style="color:#0E7490;"></i> Golongan Obat
        </label>
        <input
            type="text"
            name="golongan"
            list="medixGolonganOptions"
            value="<?php echo e($valGolongan); ?>"
            placeholder="Cth: Obat Bebas, Keras..."
            style="width:100%;padding:0.52rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.84rem;color:#1e293b;background:#f8fafc;outline:none;"
        >
        <datalist id="medixGolonganOptions">
            <?php $__currentLoopData = $golonganItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($item); ?>"></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </datalist>
    </div>
</div>
<?php /**PATH C:\project-library\ai-video\ApotekMedix\apotekmedixwebsite\resources\views/partials/product-attribute-filters.blade.php ENDPATH**/ ?>