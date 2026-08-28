@if ($paginator->hasPages())
    <nav class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-stone-200 pt-6 sm:flex-row" aria-label="Pagination">
        <p class="text-xs font-semibold text-slate-500">Showing <span class="text-slate-950">{{ number_format($paginator->firstItem()) }}–{{ number_format($paginator->lastItem()) }}</span> of <span class="text-slate-950">{{ number_format($paginator->total()) }}</span></p>
        <div class="flex max-w-full items-center gap-1 overflow-x-auto rounded-2xl border border-stone-200 bg-white p-1.5 shadow-sm">
            @if ($paginator->onFirstPage())
                <span class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-300" aria-disabled="true">←</span>
            @else
                <a wire:navigate href="{{ $paginator->previousPageUrl() }}" class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-600 hover:bg-stone-100" rel="prev" aria-label="Previous page">←</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))<span class="grid size-10 shrink-0 place-items-center text-xs text-slate-400">{{ $element }}</span>@endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-950 text-xs font-extrabold text-white" aria-current="page">{{ $page }}</span>
                        @else
                            <a wire:navigate href="{{ $url }}" class="grid size-10 shrink-0 place-items-center rounded-xl text-xs font-bold text-slate-600 hover:bg-stone-100" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a wire:navigate href="{{ $paginator->nextPageUrl() }}" class="grid size-10 shrink-0 place-items-center rounded-xl bg-orange-50 font-bold text-orange-700 hover:bg-orange-100" rel="next" aria-label="Next page">→</a>
            @else
                <span class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-300" aria-disabled="true">→</span>
            @endif
        </div>
    </nav>
@endif
