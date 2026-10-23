@php
    $medicine = $medicine ?? null;
    $packagingFormId = 'packaging-fields-' . ($medicine?->id ?? 'new');
    $packagingEnabled = old('unit_packaging_enabled', $medicine?->unit_packaging_enabled ?? false);
@endphp
<div class="form-group" style="padding:1rem;border:1px solid #bae6fd;border-radius:.65rem;background:#f0f9ff;">
    <label style="display:flex;align-items:center;gap:.55rem;font-weight:700;color:#075985;">
        <input type="hidden" name="unit_packaging_enabled" value="0">
        <input type="checkbox" name="unit_packaging_enabled" value="1" data-packaging-toggle="{{ $packagingFormId }}" @checked($packagingEnabled)>
        Izinkan penjualan per tablet, strip, dan box
    </label>
    <small style="display:block;margin:.4rem 0 .85rem;color:#475569;">Stok diisi sebagai jumlah box. Isi kemasan dan harga setiap satuan diatur untuk produk ini.</small>
    <div id="{{ $packagingFormId }}" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:.75rem;">
        <label>Tablet per strip
            <input type="number" name="tablets_per_strip" min="1" step="1" value="{{ old('tablets_per_strip', $medicine?->tablets_per_strip) }}" data-packaging-input>
        </label>
        <label>Strip per box
            <input type="number" name="strips_per_box" min="1" step="1" value="{{ old('strips_per_box', $medicine?->strips_per_box) }}" data-packaging-input>
        </label>
        <label>Harga / tablet (Rp)
            <input type="number" name="price_tablet" min="0" step="1" value="{{ old('price_tablet', $medicine?->price_tablet) }}" data-packaging-input>
        </label>
        <label>Harga / strip (Rp)
            <input type="number" name="price_strip" min="0" step="1" value="{{ old('price_strip', $medicine?->price_strip) }}" data-packaging-input>
        </label>
        <label>Harga / box (Rp)
            <input type="number" name="price_box" min="0" step="1" value="{{ old('price_box', $medicine?->price_box) }}" data-packaging-input>
        </label>
    </div>
</div>
<style>
    #{{ $packagingFormId }} label { display:flex;flex-direction:column;gap:.35rem;font-size:.8rem;font-weight:700;color:#334155; }
    #{{ $packagingFormId }} input { width:100%;min-width:0;padding:.55rem .65rem;border:1px solid #cbd5e1;border-radius:.45rem;background:#fff;color:#1e293b; }
</style>
<script>
(() => {
    const toggle = document.querySelector('[data-packaging-toggle="{{ $packagingFormId }}"]');
    const fields = document.getElementById('{{ $packagingFormId }}');
    if (!toggle || !fields) return;
    const syncPackagingFields = () => {
        fields.querySelectorAll('[data-packaging-input]').forEach(input => {
            input.disabled = !toggle.checked;
            input.required = toggle.checked;
        });
        fields.style.opacity = toggle.checked ? '1' : '.55';
    };
    toggle.addEventListener('change', syncPackagingFields);
    syncPackagingFields();
})();
</script>
