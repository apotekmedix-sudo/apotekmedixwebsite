@php
    $gallery = $medicine->image_gallery;
    $hasSecondaryImage = count($gallery) > 1;
    $galleryLinkClass = $hasSecondaryImage
        ? 'product-gallery-link product-gallery-link--has-secondary'
        : 'product-gallery-link';
@endphp
<a class="{{ $galleryLinkClass }}" href="{{ route('medicines.show', $medicine->id) }}" aria-label="Lihat detail {{ $medicine->nama_obat }}">
    @if($gallery)
        <img class="product-gallery-photo product-gallery-primary" src="{{ $medicine->image_url }}" alt="{{ $medicine->nama_obat }}" loading="lazy">
        @if(count($gallery) > 1)
            <img class="product-gallery-photo product-gallery-secondary" src="{{ url('storage/' . $gallery[1]) }}" alt="{{ $medicine->nama_obat }} - foto kedua" loading="lazy">
        @endif
    @else
        <img class="product-placeholder-logo" src="{{ asset('LOGO APOTEK MEDIX.PNG') }}" alt="Logo Apotek Medix" loading="lazy">
    @endif
</a>
