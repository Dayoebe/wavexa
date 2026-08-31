<div>
    <section class="border-b border-cyan-200 bg-cyan-50">
        <div class="mx-auto max-w-7xl px-4 pb-8 pt-7 sm:px-6 sm:py-14 lg:px-8">
            <div class="flex items-center justify-between"><span class="rounded-full border border-cyan-200 bg-white px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-cyan-800">Free-TV · Live directory</span><span class="text-xs font-bold text-slate-400">Direct provider streams</span></div>
            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px] lg:items-end"><div><h1 class="max-w-3xl text-[42px] font-extrabold leading-[.95] tracking-[-.055em] sm:text-6xl">Live television, beyond borders.</h1><p class="mt-4 max-w-xl leading-7 text-slate-600">News, culture, entertainment, and public channels from around the world—organized beautifully in one place.</p></div><div class="grid grid-cols-2 gap-3"><div class="rounded-[22px] bg-slate-950 p-4 text-white"><strong class="block text-3xl">{{ number_format($channels->total()) }}</strong><span class="text-[11px] text-slate-400">matching channels</span></div><div class="rounded-[22px] border border-cyan-200 bg-white p-4"><strong class="block text-3xl">{{ $countries->count() }}</strong><span class="text-[11px] text-slate-500">countries</span></div></div></div>

            <div x-data="{ open: false }">
                <button type="button" @click="open = !open" :aria-expanded="open" class="mt-6 flex w-full items-center justify-between rounded-[20px] bg-white px-4 py-4 text-sm font-extrabold shadow-sm ring-1 ring-cyan-200 sm:hidden"><span>Search and filters</span><span x-text="open ? '−' : '+'"></span></button>
                <form wire:submit="applyFilters" :class="open ? 'grid' : 'hidden sm:grid'" class="mt-3 gap-3 rounded-[26px] border border-cyan-200 bg-white p-3 shadow-xl shadow-cyan-950/5 sm:mt-7 sm:grid-cols-[2fr_1fr_1fr_auto]">
                    <label class="relative"><span class="sr-only">Search TV channels</span><input wire:model.live.debounce.300ms="q" autocomplete="off" placeholder="Search a TV channel" class="min-h-14 w-full rounded-[18px] border-0 bg-stone-100 px-4 pr-11 text-base outline-none"><span wire:loading wire:target="q" class="absolute right-4 top-1/2 size-4 -translate-y-1/2 animate-spin rounded-full border-2 border-cyan-600 border-r-transparent" aria-label="Searching"></span></label>
                    <select wire:model.live="country" class="min-h-14 rounded-[18px] border-0 bg-stone-100 px-4 font-bold"><option value="">Every country</option>@foreach($countries as $item)<option value="{{ $item->iso_alpha_2 }}">{{ $item->name }}</option>@endforeach</select>
                    <select wire:model.live="sort" class="min-h-14 rounded-[18px] border-0 bg-stone-100 px-4 font-bold"><option value="name_asc">Name A–Z</option><option value="name_desc">Name Z–A</option><option value="country">Country A–Z</option></select>
                    <button class="rounded-[18px] bg-cyan-600 px-6 py-3 font-extrabold text-white" wire:loading.attr="disabled">Apply</button>
                </form>
            </div>

            <div class="mt-6"><div class="flex justify-between"><p class="text-[11px] font-extrabold uppercase tracking-[.18em] text-slate-500">Popular destinations</p><span class="text-[11px] text-slate-400">Swipe</span></div><div class="wavexa-rail -mx-4 mt-3 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:px-0">@foreach($countries->sortByDesc('tv_count')->take(20) as $item)<button type="button" wire:click="$set('country', '{{ $item->iso_alpha_2 }}')" class="flex shrink-0 items-center gap-2 rounded-full border border-cyan-200 bg-white py-2 pl-3 pr-2 text-xs font-bold"><span>{{ $item->name }}</span><span class="rounded-full bg-cyan-100 px-2 py-1 text-[10px] text-cyan-800">{{ $item->tv_count }}</span></button>@endforeach</div></div>
        </div>
    </section>

    @if($q === '' && $country === '' && $channels->currentPage() === 1)
        <section class="border-b border-stone-200 bg-white py-8"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><p class="text-[11px] font-extrabold uppercase tracking-[.2em] text-violet-600">Fresh on Wavexa</p><h2 class="mt-1 text-2xl font-extrabold">Recently added channels</h2><div class="wavexa-rail -mx-4 mt-5 flex gap-3 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0">@foreach($recentChannels as $item)<a wire:navigate href="{{ route('tv.show', $item->slug) }}" class="w-[245px] shrink-0 rounded-[24px] bg-violet-50 p-5"><span class="grid size-10 place-items-center rounded-2xl bg-violet-600 font-black text-white">{{ mb_strtoupper(mb_substr($item->name, 0, 1)) }}</span><h3 class="mt-6 truncate font-extrabold">{{ $item->name }}</h3><p class="mt-1 text-xs text-slate-500">{{ $item->country?->name ?? 'Global' }}</p></a>@endforeach</div></div></section>
    @endif

    <section id="channels" class="py-10 sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" wire:loading.class="opacity-60">
            <div class="flex items-end justify-between"><div><p class="text-[11px] font-extrabold uppercase tracking-[.2em] text-cyan-700">On air</p><h2 class="mt-1 text-2xl font-extrabold sm:text-3xl">{{ $q !== '' ? 'Results for “'.$q.'”' : 'Choose a channel' }}</h2><p wire:loading wire:target="q" class="mt-1 text-xs font-semibold text-cyan-700">Searching channels…</p></div>@if($q !== '' || $country !== '' || $sort !== 'name_asc')<button wire:click="clearFilters" class="rounded-full bg-stone-200 px-3 py-2 text-xs font-bold">Clear filters</button>@endif</div>
            @if($channels->isEmpty())
                <div class="mt-8 rounded-[28px] border border-dashed border-stone-300 bg-white p-10 text-center"><h3 class="text-xl font-extrabold">No channels found</h3><p class="mt-2 text-sm text-slate-500">Try another country or a broader channel name.</p></div>
            @else
                <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($channels as $channel)
                        @php($logo = $channel->artworks->firstWhere('is_primary', true)?->url) @php($meta = $channel->sources->first()?->metadata ?? [])
                        <article wire:key="channel-{{ $channel->id }}" class="overflow-hidden rounded-[26px] border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl"><a wire:navigate href="{{ route('tv.show', $channel->slug) }}" class="block"><div class="relative aspect-video bg-slate-950"><span class="absolute inset-0 grid place-items-center text-5xl font-black text-cyan-300">{{ mb_strtoupper(mb_substr($channel->name, 0, 1)) }}</span>@if($logo)<img src="{{ $logo }}" alt="" class="absolute inset-0 size-full object-contain p-8" loading="lazy" onerror="this.remove()">@endif<div class="absolute inset-x-3 bottom-3 flex items-center justify-between"><span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-extrabold">LIVE</span>@if($meta['is_geoblocked'] ?? false)<span class="rounded-full bg-amber-300 px-2.5 py-1 text-[10px] font-bold">Location limited</span>@endif</div></div><div class="flex items-center justify-between gap-3 p-4"><div class="min-w-0"><h3 class="truncate text-lg font-extrabold">{{ $channel->name }}</h3><p class="mt-1 truncate text-xs text-slate-500">{{ $channel->country?->name ?? ($meta['group'] ?? 'Global') }}</p></div><span class="grid size-12 shrink-0 place-items-center rounded-[17px] bg-cyan-100 text-cyan-800">▶</span></div></a></article>
                    @endforeach
                </div>
                {{ $channels->onEachSide(1)->links('livewire.components.pagination', data: ['scrollTo' => '#channels']) }}
            @endif
        </div>
    </section>
</div>
