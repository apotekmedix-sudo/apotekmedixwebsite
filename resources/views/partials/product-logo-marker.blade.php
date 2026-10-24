@php
    $logoColor = match (mb_strtolower(trim((string) $medicine->logo))) {
        'hijau', 'green' => 'green',
        'merah', 'red' => 'red',
        'biru', 'blue' => 'blue',
        default => null,
    };
@endphp
@if($logoColor)
    <span class="product-logo-marker product-logo-marker--{{ $logoColor }}"
          role="img" aria-label="Logo {{ ['green' => 'hijau', 'red' => 'merah', 'blue' => 'biru'][$logoColor] }}"></span>
@endif
