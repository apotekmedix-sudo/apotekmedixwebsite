@php
    $priceChannel = $priceChannel ?? 'b2c';
    $unitPriceValues = [
        'tablet' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'tablet')),
        'strip' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'strip')),
        'box' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'box')),
    ];
    $unitBasePriceValues = [
        'tablet' => $medicine->getChannelBasePrice($priceChannel, 'tablet'),
        'strip' => $medicine->getChannelBasePrice($priceChannel, 'strip'),
        'box' => $medicine->getChannelBasePrice($priceChannel, 'box'),
    ];
    $unitFactors = [
        'tablet' => 1,
        'strip' => (int) $medicine->tablets_per_strip,
        'box' => $medicine->units_per_box,
    ];
    $unitLabels = [
        'tablet' => 'Tablet',
        'strip' => 'Strip',
        'box' => $medicine->package_unit_label,
    ];
    $availableUnits = array_filter(
        ['tablet', 'strip', 'box'],
        fn ($unit) => $unitBasePriceValues[$unit] > 0 && $unitFactors[$unit] > 0
    );
@endphp
@if($medicine->unit_packaging_enabled)
    @if($availableUnits)
    <label for="{{ $unitSelectId }}" style="display:block;margin:0.5rem 0 0.25rem;font-size:.78rem;font-weight:700;color:#475569;">Beli per satuan</label>
    <select id="{{ $unitSelectId }}" onchange="updateCardUnitPrice('{{ $unitSelectId }}', '{{ $priceTargetId }}')"
            data-unit-prices="{{ json_encode($unitPriceValues) }}"
            data-unit-factors="{{ json_encode($unitFactors) }}"
            data-unit-labels="{{ json_encode($unitLabels) }}"
            data-stock-units="{{ (int) $medicine->stock_base_units }}"
            style="width:100%;padding:.5rem;border:1px solid #cbd5e1;border-radius:.5rem;background:#fff;color:#1e293b;">
        @foreach($availableUnits as $unit)
            <option value="{{ $unit }}" data-price="{{ $unitPriceValues[$unit] }}" @selected($unit === 'box')>{{ $unitLabels[$unit] }} · Rp {{ number_format($unitPriceValues[$unit], 0, ',', '.') }}</option>
        @endforeach
    </select>
    @else
        <small style="display:block;margin-top:.5rem;color:#b45309;">Harga satuan produk belum tersedia.</small>
    @endif
@endif
