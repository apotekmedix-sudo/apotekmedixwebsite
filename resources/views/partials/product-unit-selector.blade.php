@php
    $priceChannel = $priceChannel ?? 'b2c';
    $showUnitChoices = ($showUnitChoices ?? true)
        && !in_array($priceChannel, ['internal', 'b2b'], true);
    $redLogoAccess = $redLogoAccess
        ?? (($b2cAccess ?? \App\Models\ShoppingAccessCode::hasActiveB2cSession())
            || \App\Models\ShoppingAccessCode::hasActiveInternalSession());
    $isRedLogoLocked = in_array($priceChannel, ['b2c', 'internal'], true)
        && $medicine->isRedLogo()
        && !$redLogoAccess;
    $unitKeys = $showUnitChoices ? ['box', 'strip', 'tablet'] : ['box'];
    $unitPriceValues = [
        'box' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'box')),
    ];
    $unitBasePriceValues = [
        'box' => $medicine->getChannelBasePrice($priceChannel, 'box'),
    ];
    $unitFactors = [
        'box' => $medicine->units_per_box,
    ];
    $unitLabels = [
        'box' => $medicine->package_unit_label,
    ];
    if ($showUnitChoices) {
        $unitPriceValues = [
            'tablet' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'tablet')),
            'strip' => (int) round($medicine->getChannelPromoPrice($priceChannel, 'strip')),
        ] + $unitPriceValues;
        $unitBasePriceValues = [
            'tablet' => $medicine->getChannelBasePrice($priceChannel, 'tablet'),
            'strip' => $medicine->getChannelBasePrice($priceChannel, 'strip'),
        ] + $unitBasePriceValues;
        $unitFactors = [
            'tablet' => 1,
            'strip' => (int) $medicine->tablets_per_strip,
        ] + $unitFactors;
        $unitLabels = [
            'tablet' => 'Tablet',
            'strip' => 'Strip',
        ] + $unitLabels;
    }
    $availableUnits = $medicine->usesTabletPackaging()
        ? array_filter(
            $unitKeys,
            fn ($unit) => $unitBasePriceValues[$unit] > 0 && $unitFactors[$unit] > 0
        )
        : ($unitBasePriceValues['box'] > 0 ? ['box'] : []);
    $stockUnits = max(0, (int) ($medicine->available_stock_units ?? $medicine->stock_base_units ?? $medicine->stok));
    $promoPercent = $medicine->getPromoPercent($priceChannel);
@endphp

<select id="{{ $unitSelectId }}" class="product-unit-data" data-stock-product-id="{{ $medicine->id }}" aria-hidden="true" tabindex="-1"
        data-unit-prices="{{ json_encode($unitPriceValues) }}"
        data-unit-factors="{{ json_encode($unitFactors) }}"
        data-unit-labels="{{ json_encode($unitLabels) }}"
        data-price-channel="{{ $priceChannel }}"
        data-sales-channel="{{ in_array($priceChannel, ['b2c', 'internal'], true) ? 'b2c' : $priceChannel }}"
        data-red-logo="{{ $medicine->isRedLogo() ? '1' : '0' }}"
        data-red-logo-locked="{{ $isRedLogoLocked ? '1' : '0' }}"
        data-stock-units="{{ $stockUnits }}">
    @foreach($availableUnits as $unit)
        <option value="{{ $unit }}" data-price="{{ $unitPriceValues[$unit] }}" @selected($unit === 'box')>{{ $unitLabels[$unit] }} · Rp {{ number_format($unitPriceValues[$unit], 0, ',', '.') }}</option>
    @endforeach
</select>

@if($availableUnits)
    <div class="product-unit-section {{ $isRedLogoLocked ? 'is-red-logo-locked' : '' }}">
        <span class="product-unit-heading">{{ $showUnitChoices ? 'Beli per satuan & jumlah' : 'Harga per kemasan & jumlah' }}</span>
        <div class="product-unit-grid product-unit-grid-{{ count($availableUnits) }}">
            @foreach($availableUnits as $unit)
                @php
                    $availableQuantity = intdiv($stockUnits, max(1, (int) $unitFactors[$unit]));
                    $unitBasePrice = $unitBasePriceValues[$unit];
                @endphp
                <div class="product-unit-card {{ $isRedLogoLocked ? 'is-red-logo-locked' : '' }}" data-cart-unit="{{ $unit }}">
                    <button type="button" class="product-unit-choice{{ $isRedLogoLocked ? ' is-red-logo-locked' : '' }}" aria-pressed="false"
                            data-unit-price="{{ $unitPriceValues[$unit] }}"
                            data-price-target="{{ $priceTargetId ?? '' }}"
                            data-red-logo-locked="{{ $isRedLogoLocked ? '1' : '0' }}"
                            data-product-name="{{ $medicine->nama_obat }}"
                            onclick="selectProductCardUnit(this)">
                        <span class="product-unit-name">{{ $unitLabels[$unit] }}</span>
                        @if($promoPercent > 0 && $unitBasePrice > $unitPriceValues[$unit])
                            <small class="product-unit-old-price">Rp {{ number_format($unitBasePrice, 0, ',', '.') }}</small>
                        @endif
                        <strong class="product-unit-price">Rp {{ number_format($unitPriceValues[$unit], 0, ',', '.') }}</strong>
                        <small class="product-unit-stock" data-unit-stock-badge="{{ $unit }}">Stok: {{ number_format($availableQuantity, 0, ',', '.') }}</small>
                    </button>
                    <div class="product-unit-quantity">
                        <button type="button" aria-label="Kurangi {{ $unitLabels[$unit] }}"
                                onclick="adjustProductCardQty(this, -1)" disabled>−</button>
                        <input type="number" min="0" max="{{ $availableQuantity }}" step="1" value="0"
                               data-cart-quantity data-stock-unit="{{ $unit }}"
                               aria-label="Jumlah {{ $unitLabels[$unit] }}"
                               oninput="updateProductCardQty(this)" disabled>
                        <button type="button" aria-label="Tambah {{ $unitLabels[$unit] }}"
                                onclick="adjustProductCardQty(this, 1)" disabled>+</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <small class="product-unit-unavailable">Harga satuan produk belum tersedia.</small>
@endif
