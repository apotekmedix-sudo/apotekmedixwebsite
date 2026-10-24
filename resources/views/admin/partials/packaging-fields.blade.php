@php
    $packageUnit = old('satuan', $medicine?->satuan ?? old('sediaan', $medicine?->sediaan));
    $form = old('sediaan', $medicine?->sediaan);
    $canConfigureTabletPackaging = \App\Models\Medicine::supportsTabletPackaging((string) $packageUnit, (string) $form);
    $stripsPerBox = old('strips_per_box', $medicine?->usesTabletPackaging() ? $medicine->strips_per_box : null);
    $tabletsPerStrip = old('tablets_per_strip', $medicine?->usesTabletPackaging() ? $medicine->tablets_per_strip : null);
    $tabletsPerBox = old('tablets_per_box', $medicine?->usesTabletPackaging() ? $medicine->tablets_per_strip * $medicine->strips_per_box : null);
    $priceStrip = old('price_strip', $medicine?->usesTabletPackaging() ? $medicine->price_strip : null);
    $priceTablet = old('price_tablet', $medicine?->usesTabletPackaging() ? $medicine->price_tablet : null);
@endphp
<div id="packaging-unit-fields" style="grid-column:1 / -1;" @unless($canConfigureTabletPackaging) hidden @endunless>
<div class="form-grid">
<div class="form-group">
    <label class="form-label">Isi per Kemasan (Strip)</label>
    <input type="number" id="packaging-strips-per-box" name="strips_per_box" class="form-input {{ $errors->has('strips_per_box') ? 'is-invalid' : '' }}" min="0" step="1" placeholder="Contoh: 10 (1 box = 10 strip)" value="{{ $stripsPerBox }}">
    <small>Contoh: 1 <span data-stock-unit-label>{{ strtolower($medicine?->package_unit_label ?? 'box') }}</span> berisi 10 strip. Kosongkan atau isi 0 untuk produk non-tablet.</small>
    @error('strips_per_box')<div class="form-error">{{ $message }}</div>@enderror
</div>
<div class="form-group">
    <label class="form-label">Isi per Strip (Tablet)</label>
    <input type="number" id="packaging-tablets-per-strip" name="tablets_per_strip" class="form-input {{ $errors->has('tablets_per_strip') ? 'is-invalid' : '' }}" min="0" step="1" placeholder="Contoh: 10 (1 strip = 10 tablet)" value="{{ $tabletsPerStrip }}">
    <small>Contoh: setiap 1 strip berisi 10 tablet.</small>
    @error('tablets_per_strip')<div class="form-error">{{ $message }}</div>@enderror
</div>
<div class="form-group">
    <label class="form-label">Isi per Kemasan (Total Tablet)</label>
    <input type="number" id="packaging-tablets-per-box" name="tablets_per_box" class="form-input {{ $errors->has('tablets_per_box') ? 'is-invalid' : '' }}" min="0" step="1" placeholder="Otomatis: 100 (10 strip × 10 tablet)" value="{{ $tabletsPerBox }}">
    <small>Otomatis terhitung dari (jumlah strip × tablet per strip), misalnya 10 × 10 = 100 tablet per <span data-stock-unit-label>{{ strtolower($medicine?->package_unit_label ?? 'box') }}</span>.</small>
    @error('tablets_per_box')<div class="form-error">{{ $message }}</div>@enderror
</div>
<div class="form-group">
    <label class="form-label">Harga Jual per Strip (Rp)</label>
    <input type="number" name="price_strip" class="form-input {{ $errors->has('price_strip') ? 'is-invalid' : '' }}" min="0" step="1" placeholder="Contoh: 15000" value="{{ $priceStrip }}">
    <small>Harga B2C untuk pembelian per 1 strip (opsional).</small>
    @error('price_strip')<div class="form-error">{{ $message }}</div>@enderror
</div>
<div class="form-group">
    <label class="form-label">Harga Jual per Tablet (Rp)</label>
    <input type="number" name="price_tablet" class="form-input {{ $errors->has('price_tablet') ? 'is-invalid' : '' }}" min="0" step="1" placeholder="Contoh: 1500" value="{{ $priceTablet }}">
    <small>Harga B2C untuk pembelian per 1 tablet (opsional).</small>
    @error('price_tablet')<div class="form-error">{{ $message }}</div>@enderror
</div>
</div>
</div>
<div class="form-group" style="grid-column: 1 / -1;">
    <div id="packaging-conversion-preview" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:0.65rem;padding:0.9rem 1.1rem;display:flex;flex-direction:column;gap:0.45rem;">
        <div style="font-size:0.8rem;font-weight:700;color:#0369a1;display:flex;align-items:center;gap:0.4rem;">
            <i class="fa-solid fa-Calculator"></i> Konversi Stok Otomatis (Box / Strip / Tablet)
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0.65rem;">
            <div style="background:#fff;border:1px solid #e0f2fe;border-radius:0.5rem;padding:0.55rem 0.75rem;">
                <div style="font-size:0.72rem;color:#64748b;font-weight:600;">Stok Satuan (<span data-stock-unit-label>{{ $medicine?->package_unit_label ?? 'Box' }}</span>)</div>
                <div id="preview-stock-box" style="font-size:1rem;font-weight:800;color:#0f172a;">0</div>
            </div>
            <div data-conversion-unit-preview @unless($canConfigureTabletPackaging) hidden @endunless style="background:#fff;border:1px solid #e0f2fe;border-radius:0.5rem;padding:0.55rem 0.75rem;">
                <div style="font-size:0.72rem;color:#64748b;font-weight:600;">Stok Strip Terkonversi</div>
                <div id="preview-stock-strip" style="font-size:1rem;font-weight:800;color:#0284c7;">0 Strip</div>
            </div>
            <div data-conversion-unit-preview @unless($canConfigureTabletPackaging) hidden @endunless style="background:#fff;border:1px solid #e0f2fe;border-radius:0.5rem;padding:0.55rem 0.75rem;">
                <div style="font-size:0.72rem;color:#64748b;font-weight:600;">Stok Tablet Terkonversi</div>
                <div id="preview-stock-tablet" style="font-size:1rem;font-weight:800;color:#059669;">0 Tablet</div>
            </div>
        </div>
        <small id="preview-stock-formula" style="font-size:0.76rem;color:#475569;">
            Jika ada pembelian 1 Box, 1 Strip, atau 1 Tablet, maka ketiga stok di atas akan otomatis berkurang secara proporsional.
        </small>
    </div>
</div>
<script>
(() => {
    const stockInput = document.querySelector('input[name="stok"]');
    const unitInput = document.getElementById('product-package-unit');
    const formInput = document.querySelector('input[name="sediaan"]');
    const packagingFields = document.getElementById('packaging-unit-fields');
    const conversionUnitPreviews = document.querySelectorAll('[data-conversion-unit-preview]');
    const stripsInput = document.getElementById('packaging-strips-per-box');
    const tabletsPerStripInput = document.getElementById('packaging-tablets-per-strip');
    const tabletsPerBoxInput = document.getElementById('packaging-tablets-per-box');
    const boxEl = document.getElementById('preview-stock-box');
    const stripEl = document.getElementById('preview-stock-strip');
    const tabletEl = document.getElementById('preview-stock-tablet');
    const formulaEl = document.getElementById('preview-stock-formula');

    if (!stripsInput || !tabletsPerStripInput || !tabletsPerBoxInput) return;

    const fmt = (n) => Number(n || 0).toLocaleString('id-ID');

    function syncFromStripAndTabletPerStrip() {
        const strips = Math.max(0, parseInt(stripsInput.value, 10) || 0);
        const tabPerStrip = Math.max(0, parseInt(tabletsPerStripInput.value, 10) || 0);
        if (strips > 0 && tabPerStrip > 0) {
            tabletsPerBoxInput.value = String(strips * tabPerStrip);
        } else if (strips === 0 || tabPerStrip === 0) {
            if (!document.activeElement || document.activeElement !== tabletsPerBoxInput) {
                tabletsPerBoxInput.value = '';
            }
        }
        renderPreview();
    }

    function syncFromTabletsPerBox() {
        const strips = Math.max(0, parseInt(stripsInput.value, 10) || 0);
        const tabPerBox = Math.max(0, parseInt(tabletsPerBoxInput.value, 10) || 0);
        if (strips > 0 && tabPerBox > 0 && tabPerBox % strips === 0) {
            tabletsPerStripInput.value = String(Math.floor(tabPerBox / strips));
        }
        renderPreview();
    }

    function renderPreview() {
        const unitLabel = (unitInput?.value || '').trim() || 'Box';
        const form = (formInput?.value || '').trim().toLowerCase();
        const packageUnit = unitLabel.toLowerCase();
        const excludedForm = /botol|bottle|tube|tabung|sachet|flacon|\bfls\b|vial|ampul|ampoule|\bpot\b|sirup|syrup|suspensi|drops|tetes|krim|cream|salep|ointment|\bgel\b|inhaler|nebule|suppositoria|ovula|enema|\bpen\b/i;
        const allowedForm = /tablet|kaplet|caplet|kapsul|capsule|blister|\bstrip\b|\bpil\b|\bbox\b/i;
        const canConvert = packageUnit === 'box'
            && !excludedForm.test(form)
            && (!form || form === 'box' || allowedForm.test(form));
        if (packagingFields) packagingFields.hidden = !canConvert;
        packagingFields?.querySelectorAll('input').forEach((input) => {
            input.disabled = !canConvert;
        });
        conversionUnitPreviews.forEach((element) => {
            element.hidden = !canConvert;
        });
        const stockBoxes = Math.max(0, parseFloat(stockInput?.value || '0') || 0);
        const stripsPerBox = Math.max(0, parseInt(stripsInput.value, 10) || 0);
        const tabPerBox = Math.max(0, parseInt(tabletsPerBoxInput.value, 10) || 0);
        const tabPerStrip = stripsPerBox > 0 && tabPerBox > 0
            ? Math.floor(tabPerBox / stripsPerBox)
            : Math.max(0, parseInt(tabletsPerStripInput.value, 10) || 0);

        if (!canConvert) {
            boxEl.textContent = `${fmt(Math.floor(stockBoxes))} ${unitLabel}`;
            stripEl.textContent = 'Tidak berlaku';
            tabletEl.textContent = 'Tidak berlaku';
            formulaEl.textContent = 'Stok botol, tube, dan sediaan non-tablet dihitung per kemasan.';
        } else if (stripsPerBox > 0 && tabPerStrip > 0) {
            const totalTablets = Math.round(stockBoxes * stripsPerBox * tabPerStrip);
            const totalStrips = Math.floor(totalTablets / tabPerStrip);
            const wholeBoxes = Math.floor(totalTablets / (stripsPerBox * tabPerStrip));
            boxEl.textContent = `${fmt(wholeBoxes)} ${unitLabel}`;
            stripEl.textContent = `${fmt(totalStrips)} Strip`;
            tabletEl.textContent = `${fmt(totalTablets)} Tablet`;
            formulaEl.textContent = `Konversi aktif: 1 ${unitLabel} = ${fmt(stripsPerBox)} Strip, 1 Strip = ${fmt(tabPerStrip)} Tablet (1 ${unitLabel} = ${fmt(stripsPerBox * tabPerStrip)} Tablet).`;
        } else {
            const wholeBoxes = Math.floor(stockBoxes);
            boxEl.textContent = `${fmt(wholeBoxes)} ${unitLabel}`;
            stripEl.textContent = `0 Strip (Non-strip)`;
            tabletEl.textContent = `0 Tablet (Non-tablet)`;
            formulaEl.textContent = `Isi jumlah Strip per ${unitLabel} dan Tablet per Strip untuk mengaktifkan konversi 3 stok otomatis.`;
        }
    }

    stripsInput.addEventListener('input', syncFromStripAndTabletPerStrip);
    tabletsPerStripInput.addEventListener('input', syncFromStripAndTabletPerStrip);
    tabletsPerBoxInput.addEventListener('input', syncFromTabletsPerBox);
    stockInput?.addEventListener('input', renderPreview);
    unitInput?.addEventListener('input', renderPreview);
    formInput?.addEventListener('input', renderPreview);
    renderPreview();
})();
</script>
