@extends('layouts.public')

@section('title', 'Live Radio Around the World — Wavexa')
@section('description', 'Browse and listen to directory-listed live radio stations by country, genre, and language on Wavexa.')

@section('content')
    <section class="border-b border-orange-200 bg-orange-50">
        <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 sm:py-16 lg:px-8">
            <div class="grid min-w-0 gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
                <div class="min-w-0">
                    <span class="inline-flex max-w-full items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[10px] font-bold text-emerald-700 shadow-sm ring-1 ring-stone-200 sm:text-xs"><span class="size-2 shrink-0 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span><span class="truncate">DIRECTORY-LISTED LIVE STREAMS</span></span>
                    <h1 class="mt-5 max-w-3xl text-4xl font-extrabold leading-none tracking-[-0.05em] sm:text-6xl">Radio without borders.</h1>
                    <p class="mt-4 max-w-2xl leading-7 text-slate-600">Find voices, music, and conversations by place. Wavexa plays each stream directly from its provider and keeps its source visible.</p>
                </div>
                <div class="flex items-center gap-3 rounded-2xl bg-slate-950 px-5 py-4 text-white lg:block"><span class="block text-3xl font-extrabold">{{ $stations->total() }}</span><span class="text-xs text-slate-400">stations match this view</span></div>
            </div>

            <button type="button" data-filter-toggle aria-expanded="false" class="mt-7 flex w-full items-center justify-between rounded-2xl bg-white px-4 py-4 text-sm font-extrabold shadow-sm sm:hidden">Search and filters <span>＋</span></button>
            <form data-filter-panel method="GET" action="{{ route('radio.index') }}" class="mt-3 hidden min-w-0 max-w-full gap-4 rounded-[24px] border border-orange-200 bg-white p-4 shadow-lg shadow-orange-900/5 sm:mt-7 sm:grid sm:grid-cols-2 lg:grid-cols-4" role="search">
                <label class="min-w-0 sm:col-span-2"><span class="mb-1.5 block text-xs font-bold text-slate-600">Search</span><span class="flex min-h-13 min-w-0 items-center gap-3 rounded-2xl bg-stone-50 px-4"><svg viewBox="0 0 24 24" class="size-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Station or location" class="min-w-0 w-full border-0 bg-transparent text-base outline-none placeholder:text-slate-400"></span></label>
                <label class="min-w-0"><span class="mb-1.5 block text-xs font-bold text-slate-600">Country</span><select name="country" class="min-h-13 w-full min-w-0 rounded-2xl border-0 bg-stone-50 px-4 text-base font-semibold text-slate-900 outline-none focus:ring-2 focus:ring-orange-400"><option value="">Every country</option>@foreach ($countries as $country)<option value="{{ $country->iso_alpha_2 }}" @selected(($filters['country'] ?? '') === $country->iso_alpha_2)>{{ $country->name }}</option>@endforeach</select></label>
                <label class="min-w-0"><span class="mb-1.5 block text-xs font-bold text-slate-600">Sort by</span><select name="sort" class="min-h-13 w-full min-w-0 rounded-2xl border-0 bg-stone-50 px-4 text-base font-semibold text-slate-900 outline-none focus:ring-2 focus:ring-orange-400"><option value="popular" @selected($sort === 'popular')>Most popular</option><option value="location" @selected($sort === 'location')>Location A–Z</option><option value="name_asc" @selected($sort === 'name_asc')>Name A–Z</option><option value="name_desc" @selected($sort === 'name_desc')>Name Z–A</option><option value="bitrate" @selected($sort === 'bitrate')>Highest bitrate</option><option value="recent" @selected($sort === 'recent')>Recently checked</option></select></label>

                <div class="grid min-w-0 gap-4 sm:col-span-2 sm:grid-cols-2 lg:col-span-4 lg:grid-cols-5">
                    <label class="min-w-0"><span class="mb-1.5 block text-xs font-bold text-slate-600">State or region</span><select name="state" class="min-h-13 w-full min-w-0 rounded-2xl border border-stone-200 bg-white px-4 text-base font-semibold text-slate-900 outline-none focus:ring-2 focus:ring-orange-400"><option value="">All states</option>@foreach ($states as $state)<option value="{{ $state }}" @selected(($filters['state'] ?? '') === $state)>{{ $state }}</option>@endforeach</select></label>
                    <label class="min-w-0"><span class="mb-1.5 block text-xs font-bold text-slate-600">Popular genre</span><select name="genre" class="min-h-13 w-full min-w-0 rounded-2xl border border-stone-200 bg-white px-4 text-base font-semibold text-slate-900 outline-none focus:ring-2 focus:ring-orange-400"><option value="">All genres</option>@foreach ($genres as $genre)<option value="{{ $genre->slug }}" @selected(($filters['genre'] ?? '') === $genre->slug)>{{ $genre->name }} ({{ $genre->radio_count }})</option>@endforeach</select></label>
                    <label class="min-w-0"><span class="mb-1.5 block text-xs font-bold text-slate-600">Language</span><select name="language" class="min-h-13 w-full min-w-0 rounded-2xl border border-stone-200 bg-white px-4 text-base font-semibold text-slate-900 outline-none focus:ring-2 focus:ring-orange-400"><option value="">All languages</option>@foreach ($languages as $language)<option value="{{ $language->id }}" @selected((string) ($filters['language'] ?? '') === (string) $language->id)>{{ $language->name }}</option>@endforeach</select></label>
                    <label class="min-w-0"><span class="mb-1.5 block text-xs font-bold text-slate-600">Audio format</span><select name="codec" class="min-h-13 w-full min-w-0 rounded-2xl border border-stone-200 bg-white px-4 text-base font-semibold text-slate-900 outline-none focus:ring-2 focus:ring-orange-400"><option value="">All formats</option>@foreach ($codecs as $codec)<option value="{{ $codec }}" @selected(($filters['codec'] ?? '') === $codec)>{{ $codec }}</option>@endforeach</select></label>
                    <button type="submit" class="min-h-13 rounded-2xl bg-orange-500 px-5 py-3 text-base font-extrabold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 sm:self-end">Show stations</button>
                </div>
            </form>

            @if ($countries->isNotEmpty())
                <div class="mt-5"><div class="flex items-center justify-between"><p class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Explore by country</p><span class="text-xs text-slate-400">Swipe to browse</span></div><div class="wavexa-rail -mx-4 mt-3 flex snap-x gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">@foreach ($countries as $country)<a href="{{ route('countries.show', $country->iso_alpha_2) }}" class="flex shrink-0 snap-start items-center gap-2 rounded-full border px-3 py-2 text-xs font-bold {{ ($filters['country'] ?? '') === $country->iso_alpha_2 ? 'border-orange-500 bg-orange-500 text-white' : 'border-orange-200 bg-white text-slate-700' }}"><span>{{ $country->name }}</span><span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[10px]">{{ $country->radio_count }}</span></a>@endforeach</div></div>
            @endif
        </div>
    </section>

    @if (!request()->hasAny(['q','country','state','genre','language','codec']))
    <section class="border-b border-stone-200 bg-white py-9"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="grid gap-8 lg:grid-cols-2"><div><p class="text-xs font-extrabold uppercase tracking-wider text-rose-600">Trending now</p><div class="mt-4 grid gap-2 sm:grid-cols-2">@foreach($trendingStations as $item)<a href="{{ route('radio.show',$item->slug) }}" class="rounded-2xl bg-rose-50 p-3 font-bold">{{ $item->name }}<span class="block text-xs font-normal text-slate-500">{{ $item->country?->name ?? 'Global' }}</span></a>@endforeach</div></div><div><p class="text-xs font-extrabold uppercase tracking-wider text-violet-600">Recently added</p><div class="mt-4 grid gap-2 sm:grid-cols-2">@foreach($recentStations as $item)<a href="{{ route('radio.show',$item->slug) }}" class="rounded-2xl bg-violet-50 p-3 font-bold">{{ $item->name }}<span class="block text-xs font-normal text-slate-500">{{ $item->country?->name ?? 'Global' }}</span></a>@endforeach</div></div></div></div></section>
    @endif

    <section class="py-10 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center justify-between gap-4"><div class="min-w-0"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-600">On the dial</p><h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Choose a live station</h2></div>@if (request()->hasAny(['q', 'country', 'state', 'genre', 'language', 'codec', 'sort']))<a href="{{ route('radio.index') }}" class="shrink-0 text-xs font-bold text-slate-500 sm:text-sm">Clear all</a>@endif</div>

            @if ($stations->isEmpty())
                <div class="mt-8 rounded-[28px] border border-dashed border-stone-300 bg-white p-10 text-center"><span class="mx-auto grid size-14 place-items-center rounded-2xl bg-orange-100 text-orange-700"><svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="m7 7 10-4M8 13h.01M12 13h5"/></svg></span><h3 class="mt-5 text-xl font-extrabold">No stations match yet</h3><p class="mt-2 text-slate-500">Try another name or country, or import more Radio Browser stations.</p></div>
            @else
                <div class="mt-8 grid min-w-0 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($stations as $station)
                        @php
                            $stream = $station->primaryStream;
                            $source = $station->sources->first(fn ($item) => $item->sourceProvider?->slug === 'radio-browser');
                            $metadata = $source?->metadata ?? [];
                            $artwork = $station->artworks->firstWhere('is_primary', true)?->url;
                            $streamUrl = $stream?->resolved_url ?: $stream?->url;
                            $playableStreams = $station->streamSources->where('status', '!=', \App\Enums\StreamStatus::Offline)->map(fn ($item) => ['id' => $item->id, 'url' => $item->resolved_url ?: $item->url, 'format' => $item->format])->values();
                            $initial = mb_strtoupper(mb_substr(ltrim($station->name, '#'), 0, 1));
                        @endphp
                        <article class="group min-w-0 max-w-full overflow-hidden rounded-[26px] border border-stone-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex min-w-0 gap-3 sm:gap-4">
                                <div class="relative grid size-20 shrink-0 place-items-center overflow-hidden rounded-[22px] bg-orange-100 text-2xl font-black text-orange-700">{{ $initial }}@if ($artwork)<img src="{{ $artwork }}" alt="" class="absolute inset-0 size-full object-contain" loading="lazy" referrerpolicy="no-referrer" onerror="this.remove()">@endif</div>
                                <div class="min-w-0 flex-1"><div class="flex min-w-0 flex-wrap items-center gap-2"><span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700"><span class="size-1.5 rounded-full bg-emerald-500"></span> Online</span>@if ($stream?->codec)<span class="rounded-full bg-stone-100 px-2 py-1 text-[10px] font-bold text-slate-500">{{ $stream->codec }}</span>@endif</div><h3 class="mt-2 truncate text-base font-extrabold sm:text-lg"><a href="{{ route('radio.show', $station->slug) }}">{{ $station->name }}</a></h3><p class="mt-1 truncate text-xs text-slate-500 sm:text-sm">{{ $station->country?->name ?? ($metadata['country'] ?? 'Global') }}@if ($station->radioStation?->source_state) · {{ $station->radioStation->source_state }}@endif</p><p class="mt-1 truncate text-[11px] text-slate-400">{{ number_format($station->radioStation?->source_vote_count ?? 0) }} votes @if ($stream?->bitrate_kbps) · {{ $stream->bitrate_kbps }} kbps @endif</p></div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-1.5">@forelse ($station->genres->take(3) as $genre)<a href="{{ route('radio.index', ['genre' => $genre->slug]) }}" class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">{{ $genre->name }}</a>@empty<span class="text-xs text-slate-400">Live broadcast</span>@endforelse</div>
                            <div class="mt-5 grid grid-cols-[1fr_auto] gap-2">
                                <button type="button" data-play-station data-stream="{{ $streamUrl }}" data-streams="{{ $playableStreams->toJson() }}" data-title="{{ $station->name }}" data-slug="{{ $station->slug }}" data-art="{{ $initial }}" class="flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-extrabold text-white"><svg viewBox="0 0 24 24" class="size-4" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg>Play live</button>
                                <a href="{{ route('radio.show', $station->slug) }}" class="grid size-11 place-items-center rounded-2xl border border-stone-200 text-slate-600" aria-label="View {{ $station->name }} details"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-10 flex items-center justify-center gap-3">@if($stations->previousPageUrl())<a href="{{ $stations->previousPageUrl() }}" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-bold">Previous</a>@endif @if($stations->nextPageUrl())<a href="{{ $stations->nextPageUrl() }}" class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-extrabold text-white">Load more stations</a>@endif</div>
            @endif
        </div>
    </section>

    <section class="border-y border-stone-200 bg-white py-10"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="grid gap-5 rounded-[28px] bg-amber-100 p-6 sm:grid-cols-[auto_1fr] sm:items-center"><span class="grid size-12 place-items-center rounded-2xl bg-amber-300 text-amber-950"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg></span><div><h2 class="font-extrabold">Source and rights transparency</h2><p class="mt-1 text-sm leading-6 text-amber-950/70">Radio Browser lists publicly discoverable streams and reports technical health. Wavexa plays provider URLs directly; directory inclusion does not by itself prove commercial redistribution rights.</p></div></div></div></section>
@endsection
