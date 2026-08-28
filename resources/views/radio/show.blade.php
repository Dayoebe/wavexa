@extends('layouts.public')

@section('title', $station->name.' — Live Radio on Wavexa')
@section('description', 'Listen to '.$station->name.' live and view its available station, source, language, genre, and stream details.')
@section('canonical', route('radio.show', $station->slug))
@section('meta_image', $station->artworks->firstWhere('is_primary', true)?->url ?? '')
@section('structured_data', \App\Support\Seo::schema($station->name.' — Live Radio on Wavexa', 'Listen to '.$station->name.' live and view its station, source, language, genre, and stream details.', route('radio.show', $station->slug), [['name' => 'Home', 'url' => route('home')], ['name' => 'Radio', 'url' => route('radio.index')], ['name' => $station->name, 'url' => route('radio.show', $station->slug)]]))

@section('content')
    @php
        $stream = $station->primaryStream;
        $source = $station->sources->first(fn ($item) => $item->sourceProvider?->slug === 'radio-browser');
        $metadata = $source?->metadata ?? [];
        $artwork = $station->artworks->firstWhere('is_primary', true)?->url;
        $streamUrl = $stream?->resolved_url ?: $stream?->url;
        $initial = mb_strtoupper(mb_substr(ltrim($station->name, '#'), 0, 1));
    @endphp

    <section class="border-b border-orange-200 bg-orange-50">
        <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-16 lg:px-8">
            <nav aria-label="Breadcrumb"><ol class="flex flex-wrap items-center gap-2 text-sm font-bold text-slate-600"><li><a wire:navigate href="{{ route('home') }}">Home</a></li><li aria-hidden="true">/</li><li><a wire:navigate href="{{ route('radio.index') }}">Radio</a></li><li aria-hidden="true">/</li><li aria-current="page" class="truncate">{{ $station->name }}</li></ol></nav>
            <div class="mt-8 grid gap-7 sm:grid-cols-[180px_1fr] sm:items-center">
                <div class="relative grid aspect-square w-36 place-items-center overflow-hidden rounded-[34px] bg-orange-200 text-5xl font-black text-orange-700 shadow-lg sm:w-full">{{ $initial }}@if ($artwork)<img src="{{ $artwork }}" alt="{{ $station->name }} logo" class="absolute inset-0 size-full object-contain" referrerpolicy="no-referrer" onerror="this.remove()">@endif</div>
                <div><div class="flex flex-wrap items-center gap-2"><span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-extrabold text-emerald-700"><span class="size-2 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span> Stream online</span><span class="rounded-full bg-white px-3 py-1.5 text-xs font-bold text-slate-600">Rights review pending</span></div><h1 class="mt-4 text-4xl font-extrabold tracking-[-0.04em] sm:text-6xl">{{ $station->name }}</h1><p class="mt-3 text-lg text-slate-600">{{ $station->country?->name ?? ($metadata['country'] ?? 'Location not supplied') }}@if ($metadata['state'] ?? null) · {{ $metadata['state'] }}@endif</p><button type="button" data-play-station data-stream="{{ $streamUrl }}" data-title="{{ $station->name }}" data-slug="{{ $station->slug }}" data-art="{{ $initial }}" class="mt-7 inline-flex items-center gap-3 rounded-2xl bg-orange-500 px-6 py-4 font-extrabold text-white shadow-lg"><span class="grid size-8 place-items-center rounded-full bg-white text-orange-600"><svg viewBox="0 0 24 24" class="ml-0.5 size-4" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg></span>Listen live</button></div>
            </div>
        </div>
    </section>

    <section class="py-10 sm:py-16"><div class="mx-auto grid max-w-5xl gap-5 px-4 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
        <article class="rounded-[28px] border border-stone-200 bg-white p-6"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-orange-600">Station profile</p><h2 class="mt-2 text-2xl font-extrabold">About this broadcast</h2><dl class="mt-6 grid gap-5 sm:grid-cols-2">
            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Country</dt><dd class="mt-1 font-semibold">{{ $station->country?->name ?? ($metadata['country'] ?? 'Not supplied') }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Language</dt><dd class="mt-1 font-semibold">{{ $station->languages->pluck('name')->join(', ') ?: ($metadata['language'] ?? 'Not supplied') }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Genres</dt><dd class="mt-1 font-semibold">{{ $station->genres->pluck('name')->join(', ') ?: 'Not supplied' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Directory votes</dt><dd class="mt-1 font-semibold">{{ number_format((int) ($metadata['votes'] ?? 0)) }}</dd></div>
        </dl>@if ($station->website_url)<a wire:navigate href="{{ $station->website_url }}" target="_blank" rel="noopener noreferrer" class="mt-7 inline-flex items-center gap-2 text-sm font-extrabold text-orange-700">Visit official website ↗</a>@endif</article>

        <article class="rounded-[28px] bg-slate-950 p-6 text-white"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-emerald-300">Stream details</p><dl class="mt-6 space-y-4 text-sm"><div class="flex justify-between gap-4"><dt class="text-slate-400">Format</dt><dd class="font-bold uppercase">{{ $stream?->format ?? 'Unknown' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-400">Codec</dt><dd class="font-bold">{{ $stream?->codec ?? 'Unknown' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-400">Bitrate</dt><dd class="font-bold">{{ $stream?->bitrate_kbps ? $stream->bitrate_kbps.' kbps' : 'Not supplied' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-400">Protocol</dt><dd class="font-bold uppercase">{{ $stream?->protocol }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-400">Last checked</dt><dd class="text-right font-bold">{{ $stream?->last_checked_at?->diffForHumans() ?? 'Not supplied' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-400">Provider</dt><dd class="font-bold">{{ $source?->sourceProvider?->name ?? 'Unknown' }}</dd></div></dl></article>
    </div></section>
@endsection
