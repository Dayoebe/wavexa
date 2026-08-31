@if ($paginator->hasPages())
    <nav class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-stone-200 pt-6 sm:flex-row" aria-label="Pagination">
        <p class="text-xs font-semibold text-slate-500">Showing <span class="text-slate-950">{{ number_format($paginator->firstItem()) }}–{{ number_format($paginator->lastItem()) }}</span> of <span class="text-slate-950">{{ number_format($paginator->total()) }}</span></p>
        <div class="flex max-w-full items-center gap-1 overflow-x-auto rounded-2xl border border-stone-200 bg-white p-1.5 shadow-sm">
            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="document.querySelector('{{ $scrollTo ?? '#channels' }}')?.scrollIntoView({ behavior: 'smooth' })" @disabled($paginator->onFirstPage()) class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-600 hover:bg-stone-100 disabled:text-slate-300" aria-label="Previous page">←</button>
            @foreach ($elements as $element)
                @if (is_string($element))<span class="grid size-10 shrink-0 place-items-center text-xs text-slate-400">{{ $element }}</span>@endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-950 text-xs font-extrabold text-white" aria-current="page">{{ $page }}</span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="document.querySelector('{{ $scrollTo ?? '#channels' }}')?.scrollIntoView({ behavior: 'smooth' })" class="grid size-10 shrink-0 place-items-center rounded-xl text-xs font-bold text-slate-600 hover:bg-stone-100" aria-label="Go to page {{ $page }}">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach
            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="document.querySelector('{{ $scrollTo ?? '#channels' }}')?.scrollIntoView({ behavior: 'smooth' })" @disabled(! $paginator->hasMorePages()) class="grid size-10 shrink-0 place-items-center rounded-xl bg-cyan-50 font-bold text-cyan-800 hover:bg-cyan-100 disabled:bg-transparent disabled:text-slate-300" aria-label="Next page">→</button>
        </div>
    </nav>
@endif
