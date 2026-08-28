@extends('layouts.public')

@section('title', 'Live TV Around the World — Wavexa')
@section('description', 'Discover free television channels by country and watch supported live streams on Wavexa.')

@section('content')
    <section class="border-b border-cyan-200 bg-cyan-50">
        <div class="mx-auto max-w-7xl px-4 py-9 sm:px-6 sm:py-16 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[10px] font-extrabold text-cyan-800 shadow-sm ring-1 ring-cyan-200"><span class="size-2 rounded-full bg-cyan-500"></span>FREE-TV DIRECTORY</span>
            <div class="mt-5 grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
                <div><h1 class="max-w-3xl text-4xl font-extrabold leading-none tracking-[-0.05em] sm:text-6xl">The world, on screen.</h1><p class="mt-4 max-w-2xl leading-7 text-slate-600">Browse free-to-air and freely available television streams with visible country, source, and availability context.</p></div>
                <div class="flex items-center gap-3 rounded-2xl bg-slate-950 px-5 py-4 text-white"><strong class="text-3xl">{{ $channels->total() }}</strong><span class="text-xs text-slate-400">channels in this view</span></div>
            </div>

            <button type="button" data-filter-toggle aria-expanded="false" class="mt-7 flex w-full items-center justify-between rounded-2xl bg-white px-4 py-4 text-sm font-extrabold shadow-sm sm:hidden">Search and filters <span>＋</span></button>
            <form data-filter-panel method="GET" action="{{ route('tv.index') }}" class="mt-3 hidden gap-4 rounded-[24px] border border-cyan-200 bg-white p-4 shadow-lg shadow-cyan-900/5 sm:mt-7 sm:grid sm:grid-cols-2 lg:grid-cols-4">
                <label class="sm:col-span-2"><span class="mb-1.5 block text-xs font-bold text-slate-600">Search</span><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search a TV channel" class="min-h-13 w-full rounded-2xl border-0 bg-stone-50 px-4 text-base outline-none focus:ring-2 focus:ring-cyan-400"></label>
                <label><span class="mb-1.5 block text-xs font-bold text-slate-600">Country</span><select name="country" class="min-h-13 w-full rounded-2xl border-0 bg-stone-50 px-4 text-base font-semibold outline-none focus:ring-2 focus:ring-cyan-400"><option value="">Every country</option>@foreach ($countries as $country)<option value="{{ $country->iso_alpha_2 }}" @selected(($filters['country'] ?? '') === $country->iso_alpha_2)>{{ $country->name }}</option>@endforeach</select></label>
                <label><span class="mb-1.5 block text-xs font-bold text-slate-600">Sort by</span><select name="sort" class="min-h-13 w-full rounded-2xl border-0 bg-stone-50 px-4 text-base font-semibold outline-none focus:ring-2 focus:ring-cyan-400"><option value="name_asc" @selected($sort === 'name_asc')>Name A–Z</option><option value="name_desc" @selected($sort === 'name_desc')>Name Z–A</option><option value="country" @selected($sort === 'country')>Country A–Z</option></select></label>
                <button class="min-h-13 rounded-2xl bg-cyan-600 px-5 text-base font-extrabold text-white sm:col-span-2 lg:col-span-4">Show channels</button>
            </form>
            @if ($countries->isNotEmpty())
                <div class="mt-5"><div class="flex items-center justify-between"><p class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Explore by country</p><span class="text-xs text-slate-400">Swipe to browse</span></div><div class="wavexa-rail -mx-4 mt-3 flex snap-x gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">@foreach ($countries as $country)<a href="{{ route('countries.show', $country->iso_alpha_2) }}" class="flex shrink-0 snap-start items-center gap-2 rounded-full border px-3 py-2 text-xs font-bold {{ ($filters['country'] ?? '') === $country->iso_alpha_2 ? 'border-cyan-600 bg-cyan-600 text-white' : 'border-cyan-200 bg-white text-slate-700' }}"><span>{{ $country->name }}</span><span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[10px]">{{ $country->tv_count }}</span></a>@endforeach</div></div>
            @endif
        </div>
    </section>

    @if (!request()->hasAny(['q','country']))<section class="border-b border-cyan-200 bg-white py-9"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><p class="text-xs font-extrabold uppercase tracking-wider text-violet-600">Recently added</p><div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">@foreach($recentChannels as $item)<a href="{{ route('tv.show',$item->slug) }}" class="rounded-2xl bg-violet-50 p-3 font-bold">{{ $item->name }}<span class="block text-xs font-normal text-slate-500">{{ $item->country?->name ?? 'Global' }}</span></a>@endforeach</div></div></section>@endif

    <section class="py-10 sm:py-16"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between gap-4"><div><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-cyan-700">Live now</p><h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Choose a channel</h2></div>@if (request()->hasAny(['q', 'country', 'sort']))<a href="{{ route('tv.index') }}" class="shrink-0 text-sm font-bold text-slate-500">Clear all</a>@endif</div>
        @if ($channels->isEmpty())
            <div class="rounded-[28px] border border-dashed border-stone-300 bg-white p-10 text-center"><h2 class="text-xl font-extrabold">No TV channels match yet</h2><p class="mt-2 text-slate-500">Try another search or import more Free-TV channels.</p></div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($channels as $channel)
                    @php($logo = $channel->artworks->firstWhere('is_primary', true)?->url)
                    @php($metadata = $channel->sources->first()?->metadata ?? [])
                    <article class="overflow-hidden rounded-[26px] border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <a href="{{ route('tv.show', $channel->slug) }}" class="block">
                            <div class="relative aspect-video bg-slate-950"><div class="absolute inset-0 grid place-items-center text-4xl font-black text-cyan-300">{{ mb_strtoupper(mb_substr($channel->name, 0, 1)) }}</div>@if ($logo)<img src="{{ $logo }}" alt="" class="absolute inset-0 size-full object-contain p-7" loading="lazy" referrerpolicy="no-referrer" onerror="this.remove()">@endif<span class="absolute bottom-3 left-3 rounded-full bg-white px-2.5 py-1 text-[10px] font-extrabold text-slate-900">LIVE TV</span></div>
                            <div class="p-4"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><h2 class="truncate text-lg font-extrabold">{{ $channel->name }}</h2><p class="mt-1 truncate text-sm text-slate-500">{{ $channel->country?->name ?? ($metadata['group'] ?? 'Global') }}</p></div><span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-cyan-100 text-cyan-800"><svg viewBox="0 0 24 24" class="ml-0.5 size-5" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg></span></div>@if ($metadata['is_geoblocked'] ?? false)<p class="mt-3 text-xs font-bold text-amber-700">May be limited by location</p>@endif<p class="mt-3 text-sm font-extrabold text-cyan-700">Watch live →</p></div>
                        </a>
                    </article>
                @endforeach
            </div>
            <div class="mt-10 flex items-center justify-center gap-3">@if($channels->previousPageUrl())<a href="{{ $channels->previousPageUrl() }}" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-bold">Previous</a>@endif @if($channels->nextPageUrl())<a href="{{ $channels->nextPageUrl() }}" class="rounded-2xl bg-cyan-600 px-6 py-3 text-sm font-extrabold text-white">Load more channels</a>@endif</div>
        @endif
    </div></section>
@endsection
