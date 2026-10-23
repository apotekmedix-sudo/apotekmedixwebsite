@php
    $unitPriceValues = [
        'tablet' => (int) round((float) $medicine->price_tablet),
        'strip' => (int) round((float) $medicine->price_strip),
        'box' => (int) round((float) $medicine->price_box),
    ];
    $unitFactors = [
        'tablet' => 1,
        'strip' => (int) $medicine->tablets_per_strip,
        'box' => $medicine->units_per_box,
    ];
@endphp
@if($medicine->unit_packaging_enabled)
    <label for="{{ $unitSelectId }}" style="display:block;margin:0.5rem 0 0.25rem;font-size:.78rem;font-weight:700;color:#475569;">Beli per satuan</label>
    <select id="{{ $unitSelectId }}" onchange="updateCardUnitPrice('{{ $unitSelectId }}', '{{ $priceTargetId }}')"
            data-unit-prices="{{ json_encode($unitPriceValues) }}"
            data-unit-factors="{{ json_encode($unitFactors) }}"
            data-stock-units="{{ (int) $medicine->stock_base_units }}"
            style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.5rem;background:#fff;color:#1e293b;">
        <option value="tablet" data-price="{{ $unitPriceValues['tablet'] }}">Tablet · Rp {{ number_format($unitPriceValues['tablet'], 0, ',', '.') }}</option>
        <option value="strip" data-price="{{ $unitPriceValues['strip'] }}">Strip · Rp {{ number_format($unitPriceValues['strip'], 0, ',', '.') }}</option>
        <option value="box" data-price="{{ $unitPriceValues['box'] }}" selected>Box · Rp {{ number_format($unitPriceValues['box'], 0, ',', '.') }}</option>
    </select>
@endif
