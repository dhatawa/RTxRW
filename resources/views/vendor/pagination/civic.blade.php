@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4" aria-label="Halaman">
        <p class="text-sm text-ink-muted">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm" aria-disabled="true"><x-svg-icon name="chevron-left" class="size-4" /><span class="hidden sm:inline">Sebelumnya</span></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm"><x-svg-icon name="chevron-left" class="size-4" /><span class="hidden sm:inline">Sebelumnya</span></a>
            @endif

            <span class="hidden px-3 text-sm text-ink-muted sm:inline">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm"><span class="hidden sm:inline">Berikutnya</span><x-svg-icon name="chevron-right" class="size-4" /></a>
            @else
                <span class="btn btn-secondary btn-sm" aria-disabled="true"><span class="hidden sm:inline">Berikutnya</span><x-svg-icon name="chevron-right" class="size-4" /></span>
            @endif
        </div>
    </nav>
@endif
