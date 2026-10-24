@php
    $medicine = $medicine ?? null;
    $packagingFormId = 'packaging-fields-' . ($medicine?->id ?? 'new');
@endphp
<div class="form-group" style="padding:1rem;border:1px solid #bae6fd;border-radius:.65rem;background:#f0f9ff;">
    <strong style="display:block;color:#075985;">Isi Kemasan dan Harga Satuan</strong>
    <small style="display:block;margin:.4rem 0 .85rem;color:#475569;">Isi PER STRIP dan PER TABLET jika produk dapat dijual per strip/tablet. Kosongkan atau isi 0 jika tidak berlaku.</small>
    <div id="{{ $packagingFormId }}" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:.75rem;">
        <label>PER STRIP (strip per kemasan)
            <input type="number" name="strips_per_box" min="0" step="1" value="{{ old('strips_per_box', $medicine?->unit_packaging_enabled ? $medicine->strips_per_box : 0) }}">
        </label>
        <label>PER TABLET (tablet per kemasan)
            <input type="number" name="tablets_per_box" min="0" step="1" value="{{ old('tablets_per_box', $medicine?->unit_packaging_enabled ? $medicine->units_per_box : 0) }}">
        </label>
        <label>Harga jual per tablet (Rp)
            <input type="number" name="price_tablet" min="0" step="1" value="{{ old('price_tablet', $medicine?->price_tablet) }}" data-packaging-input>
        </label>
        <label>Harga jual per strip (Rp)
            <input type="number" name="price_strip" min="0" step="1" value="{{ old('price_strip', $medicine?->price_strip) }}" data-packaging-input>
        </label>
    </div>
</div>
<style>
    #{{ $packagingFormId }} label { display:flex;flex-direction:column;gap:.35rem;font-size:.8rem;font-weight:700;color:#334155; }
    #{{ $packagingFormId }} input { width:100%;min-width:0;padding:.55rem .65rem;border:1px solid #cbd5e1;border-radius:.45rem;background:#fff;color:#1e293b; }
</style>
