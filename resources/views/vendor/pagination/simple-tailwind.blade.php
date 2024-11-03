@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="justify-center flex gap-6">
        {{-- Previous Page Link --}}
        @if (!$paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">
                ← Previous Page
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">
                Next Page →
            </a>
        @endif
    </nav>
@endif
