<div>
<section class="relative overflow-hidden border-b border-stone-200 bg-[#f6f3ed]">
    <div class="pointer-events-none absolute -right-24 top-16 size-72 rounded-full border-[54px] border-amber-300/70" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -left-20 bottom-8 size-44 rounded-full border-[38px] border-cyan-200" aria-hidden="true"></div>
    <div class="relative mx-auto grid max-w-7xl gap-12 px-4 pb-14 pt-10 sm:px-6 sm:pb-20 sm:pt-16 lg:grid-cols-[1.08fr_.92fr] lg:items-center lg:px-8 lg:py-24">
        <div>
            <div class="inline-flex items-center gap-3 rounded-full border border-stone-300 bg-white px-3 py-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-600 shadow-sm"><span class="grid size-6 place-items-center rounded-full bg-emerald-100"><span class="size-2 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span></span>Live signals · Worldwide</div>
            <h1 class="mt-7 max-w-3xl text-[3.45rem] font-extrabold leading-[.88] tracking-[-.07em] text-slate-950 sm:text-7xl lg:text-[6.3rem]">The world,<br><span class="text-orange-600">on air.</span></h1>
            <p class="mt-7 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Move through countries, cultures, and conversations. Wavexa brings live radio and supported television into one beautifully organized signal map.</p>
            <form action="{{ route('search') }}" method="GET" class="mt-8 flex max-w-xl items-center rounded-[22px] border border-stone-300 bg-white p-2 shadow-xl shadow-slate-900/5">
                <svg viewBox="0 0 24 24" class="ml-3 size-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <label class="sr-only" for="home-search">Search Wavexa</label><input id="home-search" name="q" class="min-w-0 flex-1 bg-transparent px-3 py-3 text-sm outline-none sm:text-base" placeholder="Search radio, TV, podcasts, or places">
                <button class="grid size-12 shrink-0 place-items-center rounded-[16px] bg-slate-950 text-white" aria-label="Search"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.3"><path d="m9 18 6-6-6-6"/></svg></button>
            </form>
            <div class="mt-8 grid max-w-xl grid-cols-3 divide-x divide-stone-300 border-y border-stone-300 py-4">
                <div class="pr-4"><strong class="block text-2xl font-extrabold tracking-tight sm:text-3xl">{{ number_format($radioCount) }}</strong><span class="mt-1 block text-[9px] font-bold uppercase tracking-wider text-slate-500 sm:text-[10px]">Radio signals</span></div>
                <div class="px-4"><strong class="block text-2xl font-extrabold tracking-tight sm:text-3xl">{{ number_format($tvCount) }}</strong><span class="mt-1 block text-[9px] font-bold uppercase tracking-wider text-slate-500 sm:text-[10px]">TV channels</span></div>
                <div class="pl-4"><strong class="block text-2xl font-extrabold tracking-tight sm:text-3xl">{{ number_format($countryCount) }}</strong><span class="mt-1 block text-[9px] font-bold uppercase tracking-wider text-slate-500 sm:text-[10px]">Countries</span></div>
            </div>
        </div>

        <div class="relative mx-auto w-full max-w-xl lg:max-w-none">
            @php
                $globePlaces = $countries->filter(fn ($country) => $country->latitude !== null && $country->longitude !== null)->map(fn ($country) => [
                    'name' => $country->name,
                    'code' => $country->iso_alpha_2,
                    'latitude' => $country->latitude,
                    'longitude' => $country->longitude,
                    'sources' => $country->radio_count + $country->tv_count,
                    'url' => route('countries.show', $country->iso_alpha_2),
                ])->values();
            @endphp
            <div class="rounded-[34px] border border-stone-300 bg-white p-3 shadow-2xl shadow-slate-900/10 sm:p-5">
                <div class="flex items-center justify-between px-2 py-2"><div><p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-slate-400">3D signal atlas</p><p class="mt-1 font-extrabold">Explore the live world</p></div><span class="rounded-full bg-emerald-100 px-3 py-1.5 text-[9px] font-extrabold uppercase tracking-wider text-emerald-800">Satellite</span></div>
                <div class="relative mt-3 min-h-[360px] overflow-hidden rounded-[26px] bg-[#071522] sm:min-h-[440px]">
                    <div wire:ignore data-signal-globe @if(config('services.maptiler.api_key')) data-globe-style="https://api.maptiler.com/maps/satellite/style.json?key={{ rawurlencode(config('services.maptiler.api_key')) }}" @endif class="absolute inset-0" role="region" aria-label="Interactive three-dimensional satellite map"></div>
                    <script type="application/json" data-globe-places>{!! json_encode($globePlaces, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                    <div data-globe-fallback class="absolute inset-0 hidden place-items-center bg-slate-950 p-8 text-center text-sm leading-6 text-slate-300">Your browser cannot display the interactive globe. Use the country links further down the page to explore Wavexa.</div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-slate-950/80 px-5 py-4 text-white backdrop-blur-sm">
                        <p data-globe-status class="text-xs font-semibold text-slate-200" aria-live="polite">Loading the interactive globe…</p>
                        <p class="mt-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Location data from <a class="pointer-events-auto underline" href="https://www.whosonfirst.org/docs/licenses/" target="_blank" rel="noreferrer">Who's On First · License</a></p>
                    </div>
                </div>
            </div>
            <div class="absolute -bottom-5 -left-2 rounded-2xl bg-slate-950 px-4 py-3 text-white shadow-xl sm:-left-7"><span class="block text-[9px] font-bold uppercase tracking-widest text-cyan-300">Direct streams</span><strong class="text-sm">From original providers</strong></div>
        </div>
    </div>
</section>

@if($featuredMedia->isNotEmpty())
<section class="border-b border-stone-200 bg-amber-50 py-14 sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="flex items-end justify-between gap-5"><div><p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-fuchsia-700">Selected by Wavexa</p><h2 class="mt-3 text-3xl font-extrabold tracking-[-.04em] sm:text-5xl">Worth discovering now.</h2></div><a wire:navigate href="{{ route('search') }}" class="hidden rounded-full border border-stone-300 bg-white px-5 py-3 text-sm font-bold sm:inline-flex">Explore everything →</a></div>
<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach($featuredMedia as $item) @php($logo = $item->artworks->firstWhere('is_primary', true)?->url) @php($url = match($item->type->value) {'radio' => route('radio.show', $item->slug), 'tv' => route('tv.show', $item->slug), default => route('podcasts.show', $item->slug)})<a wire:navigate href="{{ $url }}" class="group flex items-center gap-4 rounded-[24px] border border-amber-200 bg-white p-4 transition hover:-translate-y-1 hover:shadow-xl"><span class="relative grid size-20 shrink-0 place-items-center overflow-hidden rounded-2xl bg-fuchsia-50 text-2xl font-black text-fuchsia-700">{{ mb_strtoupper(mb_substr($item->name, 0, 1)) }}@if($logo)<img src="{{ $logo }}" alt="" class="absolute inset-0 size-full object-contain p-3" loading="lazy">@endif</span><span class="min-w-0 flex-1"><small class="font-extrabold uppercase tracking-widest text-fuchsia-700">{{ str($item->type->value)->headline() }}</small><strong class="mt-1 block truncate text-lg">{{ $item->name }}</strong><span class="mt-2 block text-xs text-slate-500">{{ $item->country?->name ?? 'Worldwide' }} · Open ↗</span></span></a>@endforeach</div></div></section>
@endif

<section class="bg-white py-14 sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-5"><div><p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-orange-600">Tune in instantly</p><h2 class="mt-3 text-3xl font-extrabold tracking-[-.04em] sm:text-5xl">Fresh frequencies.</h2></div><a wire:navigate href="{{ route('radio.index') }}" class="hidden rounded-full border border-stone-300 px-5 py-3 text-sm font-bold sm:inline-flex">Explore all radio →</a></div>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($radioStations as $station)
            @php($stream = $station->primaryStream) @php($art = $station->artworks->firstWhere('is_primary', true)?->url) @php($initial = mb_strtoupper(mb_substr(ltrim($station->name, '#'), 0, 1)))
            <article class="group flex min-h-72 flex-col rounded-[26px] border border-stone-200 bg-[#f8f6f1] p-4 transition duration-300 hover:-translate-y-1 hover:border-orange-300 hover:shadow-xl">
                <div class="relative grid aspect-[4/3] place-items-center overflow-hidden rounded-[20px] bg-orange-100 text-5xl font-black text-orange-700">{{ $initial }}@if($art)<img src="{{ $art }}" alt="" class="absolute inset-0 size-full object-contain p-5" loading="lazy" referrerpolicy="no-referrer">@endif<span class="absolute left-3 top-3 rounded-full bg-white px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-wider text-emerald-700 shadow-sm">● Live</span></div>
                <div class="mt-4 min-w-0"><p class="truncate text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $station->country?->name ?? 'Worldwide' }}</p><h3 class="mt-1 truncate text-lg font-extrabold">{{ $station->name }}</h3></div>
                <div class="mt-auto flex gap-2 pt-4"><button data-play-station data-stream="{{ $stream?->resolved_url ?: $stream?->url }}" data-streams="{{ $station->streamSources->map(fn($item) => ['id'=>$item->id,'url'=>$item->resolved_url ?: $item->url,'format'=>$item->format])->values()->toJson() }}" data-title="{{ $station->name }}" data-slug="{{ $station->slug }}" data-art="{{ $initial }}" class="flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-slate-950 text-xs font-extrabold text-white">▶ Play live</button><a wire:navigate href="{{ route('radio.show', $station->slug) }}" class="grid size-11 place-items-center rounded-xl border border-stone-300" aria-label="Open {{ $station->name }}">↗</a></div>
            </article>
        @empty<p class="col-span-full rounded-3xl border border-dashed border-stone-300 p-10 text-center text-slate-500">Radio stations will appear here after import.</p>@endforelse
    </div>
    <a wire:navigate href="{{ route('radio.index') }}" class="mt-6 flex min-h-12 items-center justify-center rounded-2xl border border-stone-300 text-sm font-bold sm:hidden">Explore all radio →</a>
</div></section>

<section class="border-y border-stone-200 bg-slate-950 py-14 text-white sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="grid gap-10 lg:grid-cols-[.75fr_1.25fr] lg:items-end">
    <div><p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-cyan-300">Live television</p><h2 class="mt-3 text-4xl font-extrabold leading-none tracking-[-.05em] sm:text-6xl">Watch beyond borders.</h2><p class="mt-5 max-w-md leading-7 text-slate-400">Public, news, cultural, and entertainment channels—played directly from listed sources.</p><a wire:navigate href="{{ route('tv.index') }}" class="mt-7 inline-flex rounded-full bg-cyan-400 px-5 py-3 text-sm font-extrabold text-slate-950">Browse live TV →</a></div>
    <div class="grid gap-3 sm:grid-cols-2">@forelse($tvChannels as $channel) @php($logo = $channel->artworks->firstWhere('is_primary', true)?->url)<a wire:navigate href="{{ route('tv.show', $channel->slug) }}" class="group flex items-center gap-4 rounded-[22px] border border-white/10 bg-white/[.06] p-3 transition hover:border-cyan-400/60 hover:bg-white/10"><span class="relative grid aspect-video w-28 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white/10 text-2xl font-black text-cyan-300">{{ mb_strtoupper(mb_substr($channel->name, 0, 1)) }}@if($logo)<img src="{{ $logo }}" alt="" class="absolute inset-0 size-full object-contain p-4" loading="lazy">@endif</span><span class="min-w-0 flex-1"><small class="text-[9px] font-bold uppercase tracking-wider text-cyan-300">{{ $channel->country?->name ?? 'Worldwide' }}</small><strong class="mt-1 block truncate">{{ $channel->name }}</strong><span class="mt-2 block text-xs text-slate-400">Open channel ↗</span></span></a>@empty<p class="text-slate-400">TV channels will appear here after import.</p>@endforelse</div>
</div></div></section>

<section class="bg-[#f6f3ed] py-14 sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-violet-700">Explore geographically</p><h2 class="mt-3 max-w-3xl text-3xl font-extrabold tracking-[-.04em] sm:text-5xl">Choose a place. Hear its pulse.</h2>
    <div class="mt-8 grid grid-cols-2 gap-3 md:grid-cols-4">@foreach($countries as $country)<a wire:navigate href="{{ route('countries.show', $country->iso_alpha_2) }}" class="group relative min-h-44 overflow-hidden rounded-[24px] border border-stone-300 bg-white p-5 transition hover:-translate-y-1 hover:shadow-xl"><span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">{{ $country->iso_alpha_2 }}</span><h3 class="mt-8 text-xl font-extrabold sm:text-2xl">{{ $country->name }}</h3><p class="mt-2 text-xs text-slate-500">{{ number_format($country->radio_count) }} radio · {{ number_format($country->tv_count) }} TV</p><span class="absolute bottom-4 right-4 grid size-10 place-items-center rounded-full bg-stone-100 transition group-hover:bg-violet-600 group-hover:text-white">↗</span></a>@endforeach</div>
</div></section>

<section class="bg-white py-14 sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="grid gap-8 rounded-[30px] border border-stone-200 bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-10 lg:grid-cols-[1fr_1.1fr] lg:items-center"><div><span class="text-[10px] font-extrabold uppercase tracking-[.22em] text-emerald-700">Built on transparency</span><h2 class="mt-3 text-3xl font-extrabold tracking-[-.04em]">Discovery without hiding the source.</h2></div><div class="grid gap-3 sm:grid-cols-3"><div class="rounded-2xl bg-orange-50 p-4"><strong class="text-sm">Find</strong><p class="mt-2 text-xs leading-5 text-slate-600">Browse by country, language, and genre.</p></div><div class="rounded-2xl bg-cyan-50 p-4"><strong class="text-sm">Play</strong><p class="mt-2 text-xs leading-5 text-slate-600">Connect directly to listed providers.</p></div><div class="rounded-2xl bg-emerald-50 p-4"><strong class="text-sm">Verify</strong><p class="mt-2 text-xs leading-5 text-slate-600">See source and stream health details.</p></div></div></div></div></section>
</div>
