@php
    $rawLogo = mb_strtolower(trim((string) $medicine->logo));
    $logoColor = match ($rawLogo) {
        'hijau', 'green', 'logo hijau' => 'green',
        'merah', 'red', 'logo merah', 'k' => 'red',
        'biru', 'blue', 'logo biru' => 'blue',
        default => ($medicine->isRedLogo() ? 'red' : null),
    };
@endphp
@if($logoColor)
    <span class="product-logo-marker product-logo-marker--{{ $logoColor }}"
          role="img" aria-label="Logo {{ ['green' => 'hijau', 'red' => 'merah', 'blue' => 'biru'][$logoColor] }}"></span>
@endif
