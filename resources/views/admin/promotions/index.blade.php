@extends('layouts.admin')

@section('title', 'Promo Diskon Produk - Admin Apotek Medix')
@section('page-title', 'Promo Diskon Produk')

@section('content')
<div class="card">
    <div class="card-title">Atur Diskon per Kanal</div>
    <p style="margin:0 0 1rem;color:#64748b;">Atur persentase promo secara terpisah untuk harga Internal, B2B, dan B2C. Isi 0 untuk menonaktifkan promo pada kanal tersebut.</p>

    @if(session('success'))
        <div role="status" style="margin-bottom:1rem;padding:.8rem 1rem;border-radius:10px;background:#ecfdf5;color:#047857;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" style="margin-bottom:1rem;padding:.8rem 1rem;border-radius:10px;background:#fef2f2;color:#b91c1c;">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="{{ route('admin.promotions.index') }}" style="display:flex;gap:.6rem;margin-bottom:1rem;">
        <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama produk atau SKU" style="flex:1;min-width:0;padding:.65rem .8rem;border:1px solid #cbd5e1;border-radius:8px;">
        <button class="btn btn-primary" type="submit">Cari</button>
        @if($search)
            <a class="btn btn-secondary" href="{{ route('admin.promotions.index') }}">Reset</a>
        @endif
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga Internal<br><small>Diskon %</small></th>
                    <th>Harga B2B<br><small>Diskon %</small></th>
                    <th>Harga B2C<br><small>Diskon %</small></th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicines as $medicine)
                    @php
                        $channelPrices = [
                            'internal' => [
                                'field' => 'promo_internal_percent',
                                'percent' => $medicine->promo_internal_percent,
                                'base' => $medicine->getChannelBasePrice('internal'),
                            ],
                            'b2b' => [
                                'field' => 'promo_b2b_percent',
                                'percent' => $medicine->promo_b2b_percent,
                                'base' => $medicine->getChannelBasePrice('b2b'),
                            ],
                            'b2c' => [
                                'field' => 'promo_b2c_percent',
                                'percent' => $medicine->promo_b2c_percent,
                                'base' => $medicine->getChannelBasePrice('b2c'),
                            ],
                        ];
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $medicine->nama_obat }}</strong>
                            <div style="font-size:.78rem;color:#64748b;">{{ $medicine->sku ?: $medicine->kategori }} · {{ $medicine->kelompok === 'PBF' ? 'Produk B2B' : 'Produk Internal/B2C' }}</div>
                            <form id="promotion-{{ $medicine->id }}" method="POST" action="{{ route('admin.promotions.update', $medicine) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="search" value="{{ $search }}">
                            </form>
                        </td>
                        @foreach($channelPrices as $channel => $price)
                            <td>
                                <div class="promo-channel-price" data-base-price="{{ $price['base'] }}" style="min-width:155px;">
                                    <div style="margin-bottom:.35rem;color:#64748b;font-size:.76rem;">Harga asli: <span>Rp {{ number_format($price['base'], 0, ',', '.') }}</span></div>
                                    <label style="display:flex;align-items:center;gap:.35rem;margin-bottom:.4rem;">
                                        <input class="promo-percent-input" form="promotion-{{ $medicine->id }}" type="number" name="{{ $price['field'] }}" value="{{ $price['percent'] }}" min="0" max="100" step="1" required aria-label="Diskon {{ $channel }} untuk {{ $medicine->nama_obat }}" style="width:72px;padding:.45rem;border:1px solid #cbd5e1;border-radius:7px;">
                                        <span>% diskon</span>
                                    </label>
                                    <strong class="promo-calculated-price" aria-live="polite" style="display:block;color:#047857;font-size:.8rem;">Harga promo: Rp {{ number_format($medicine->getChannelPromoPrice($channel), 0, ',', '.') }}</strong>
                                </div>
                            </td>
                        @endforeach
                        <td><button form="promotion-{{ $medicine->id }}" class="btn btn-primary btn-sm" type="submit">Simpan</button></td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:#64748b;">Produk tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem;">{{ $medicines->links() }}</div>
</div>
<script>
document.querySelectorAll('.promo-channel-price').forEach((priceBox) => {
    const input = priceBox.querySelector('.promo-percent-input');
    const output = priceBox.querySelector('.promo-calculated-price');
    const basePrice = Number(priceBox.dataset.basePrice || 0);

    const updateCalculatedPrice = () => {
        const percentage = Math.min(100, Math.max(0, Number(input.value) || 0));
        const discountedPrice = Math.round(basePrice * (100 - percentage)) / 100;
        output.textContent = 'Harga promo: ' + new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(discountedPrice);
    };

    input.addEventListener('input', updateCalculatedPrice);
    input.addEventListener('change', updateCalculatedPrice);
});
</script>
@endsection
