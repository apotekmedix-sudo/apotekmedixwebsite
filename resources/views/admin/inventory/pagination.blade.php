@if($paginator->total() > 0)
    <div class="inventory-pagination">
        <p class="inventory-pagination-summary">
            Menampilkan {{ number_format($paginator->firstItem(), 0, ',', '.') }}–{{ number_format($paginator->lastItem(), 0, ',', '.') }}
            dari {{ number_format($paginator->total(), 0, ',', '.') }} {{ $label }}
        </p>
        @if($paginator->hasPages())
            <nav class="inventory-pagination-nav" aria-label="Halaman {{ $label }}">
                @if($paginator->onFirstPage())
                    <span class="inventory-page-link is-disabled" aria-disabled="true">‹ Sebelumnya</span>
                @else
                    <a class="inventory-page-link" href="{{ $paginator->previousPageUrl() }}">‹ Sebelumnya</a>
                @endif

                @foreach(range(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page)
                    @if($page === $paginator->currentPage())
                        <span class="inventory-page-link is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="inventory-page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($paginator->hasMorePages())
                    <a class="inventory-page-link" href="{{ $paginator->nextPageUrl() }}">Berikutnya ›</a>
                @else
                    <span class="inventory-page-link is-disabled" aria-disabled="true">Berikutnya ›</span>
                @endif
            </nav>
        @endif
    </div>
@endif
