@extends('layouts.app')
@section('title', $country->name.' live radio and TV — Wavexa')
@section('description', 'Discover live radio stations and television channels from '.$country->name.' on Wavexa.')
@section('canonical', route('countries.show', $country->iso_alpha_2))
@section('structured_data', \App\Support\Seo::schema($country->name.' live radio and TV — Wavexa', 'Discover live radio stations and television channels from '.$country->name.' on Wavexa.', route('countries.show', $country->iso_alpha_2), [['name' => 'Home', 'url' => route('home')], ['name' => $country->name, 'url' => route('countries.show', $country->iso_alpha_2)]]))

@section('content')
<section class="relative overflow-hidden border-b border-stone-200 bg-[#f6f3ed]">
    <div class="absolute -right-20 -top-28 size-80 rounded-full border-[60px] border-violet-200" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-16 lg:px-8">
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-bold text-slate-500"><a wire:navigate href="{{ route('home') }}">Discover</a><span>/</span><span class="text-slate-950">{{ $country->name }}</span></nav>
        <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <div><span class="inline-flex rounded-full border border-stone-300 bg-white px-3 py-2 text-[9px] font-extrabold uppercase tracking-[.2em]">{{ $country->iso_alpha_2 }} · Country signal</span><h1 class="mt-5 max-w-4xl text-5xl font-extrabold leading-[.9] tracking-[-.06em] sm:text-7xl">Live from<br><span class="text-violet-700">{{ $country->name }}.</span></h1><p class="mt-5 max-w-xl leading-7 text-slate-600">Local voices, television, news, music, and culture—organized into one live destination.</p></div>
            <div class="grid grid-cols-2 gap-3"><div class="min-w-36 rounded-[22px] bg-slate-950 p-5 text-white"><strong class="text-3xl">{{ number_format($radioCount) }}</strong><span class="mt-1 block text-[10px] uppercase tracking-wider text-slate-400">Radio stations</span></div><div class="min-w-36 rounded-[22px] border border-stone-300 bg-white p-5"><strong class="text-3xl">{{ number_format($tvCount) }}</strong><span class="mt-1 block text-[10px] uppercase tracking-wider text-slate-500">TV channels</span></div></div>
        </div>
    </div>
</section>

<section class="bg-white py-12 sm:py-16"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-3"><div><p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-orange-600">Listen locally</p><h2 class="mt-2 text-3xl font-extrabold">Radio from {{ $country->name }}</h2></div><a wire:navigate href="{{ route('radio.index', ['country' => $country->iso_alpha_2]) }}" class="rounded-full border border-stone-300 px-4 py-2 text-xs font-bold">View all →</a></div>
    <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@forelse($radioStations as $item)<a wire:navigate href="{{ route('radio.show', $item->slug) }}" class="group flex items-center gap-4 rounded-[22px] border border-stone-200 bg-[#f8f6f1] p-4 transition hover:-translate-y-1 hover:border-orange-300 hover:shadow-lg"><span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-orange-100 text-xl font-black text-orange-700">{{ mb_strtoupper(mb_substr(ltrim($item->name, '#'), 0, 1)) }}</span><span class="min-w-0 flex-1"><strong class="block truncate">{{ $item->name }}</strong><small class="mt-1 block text-slate-500">Live radio station</small></span><span class="grid size-9 place-items-center rounded-full bg-white">↗</span></a>@empty<p class="col-span-full rounded-2xl border border-dashed border-stone-300 p-8 text-center text-slate-500">No radio is available yet.</p>@endforelse</div>
    {{ $radioStations->onEachSide(1)->links('components.pagination-links') }}
</div></section>

<section class="border-t border-stone-200 bg-slate-950 py-12 text-white sm:py-16"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-3"><div><p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-cyan-300">Watch locally</p><h2 class="mt-2 text-3xl font-extrabold">Television from {{ $country->name }}</h2></div><a wire:navigate href="{{ route('tv.index', ['country' => $country->iso_alpha_2]) }}" class="rounded-full border border-white/20 px-4 py-2 text-xs font-bold">View all →</a></div>
    <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@forelse($tvChannels as $item)<a wire:navigate href="{{ route('tv.show', $item->slug) }}" class="group flex items-center gap-4 rounded-[22px] border border-white/10 bg-white/[.06] p-4 transition hover:-translate-y-1 hover:border-cyan-400/60"><span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-cyan-400 text-xl font-black text-slate-950">{{ mb_strtoupper(mb_substr($item->name, 0, 1)) }}</span><span class="min-w-0 flex-1"><strong class="block truncate">{{ $item->name }}</strong><small class="mt-1 block text-slate-400">Supported live stream</small></span><span class="grid size-9 place-items-center rounded-full bg-white/10">↗</span></a>@empty<p class="col-span-full rounded-2xl border border-dashed border-white/20 p-8 text-center text-slate-400">No television is available yet.</p>@endforelse</div>
    {{ $tvChannels->onEachSide(1)->links('components.pagination-links') }}
</div></section>
@endsection
