@extends('layouts.public')

@section('title', $channel->name.' Live — Wavexa')
@section('description', 'Watch '.$channel->name.' live and view its source and availability details on Wavexa.')

@section('content')
    @php($stream = $channel->primaryStream)
    @php($source = $channel->sources->first())
    @php($metadata = $source?->metadata ?? [])
    <section class="bg-slate-950 text-white"><div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
        <a wire:navigate href="{{ route('tv.index') }}" class="text-sm font-bold text-cyan-300">← Back to live TV</a>
        <div class="mt-5 overflow-hidden rounded-[24px] border border-white/10 bg-black shadow-2xl">
            <div class="relative aspect-video"><video data-tv-player data-stream="{{ $stream->resolved_url ?: $stream->url }}" data-streams="{{ $channel->streamSources->where('status', '!=', \App\Enums\StreamStatus::Offline)->map(fn ($item) => ['id' => $item->id, 'url' => $item->resolved_url ?: $item->url, 'format' => $item->format])->values()->toJson() }}" class="size-full bg-black" controls playsinline preload="metadata"></video><div data-tv-message class="pointer-events-none absolute inset-0 grid place-items-center p-6 text-center"><span class="rounded-2xl bg-slate-950/90 px-5 py-3 text-sm font-bold">Press play to start the live channel</span></div></div>
        </div>
        <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-cyan-300">{{ $channel->country?->name ?? ($metadata['group'] ?? 'Global') }}</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-5xl">{{ $channel->name }}</h1></div><span class="self-start rounded-full bg-amber-100 px-3 py-2 text-xs font-bold text-amber-900">Rights review pending</span></div>
    </div></section>
    <section class="py-10"><div class="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 lg:grid-cols-[1fr_320px] lg:px-8"><div class="rounded-[26px] border border-stone-200 bg-white p-6"><h2 class="text-xl font-extrabold">About this channel</h2><p class="mt-3 leading-7 text-slate-600">{{ $channel->description }}</p>@if ($metadata['is_geoblocked'] ?? false)<div class="mt-5 rounded-2xl bg-amber-100 p-4 text-sm font-semibold text-amber-950">This source is marked as geographically restricted and may not play in your current location.</div>@endif<button type="button" data-report-stream="{{ $stream->id }}" class="mt-5 text-sm font-bold text-rose-700 underline">Report broken stream</button><p data-report-message class="mt-2 hidden text-sm text-emerald-700"></p></div><aside class="rounded-[26px] border border-cyan-200 bg-cyan-50 p-6"><h2 class="font-extrabold">Source transparency</h2><dl class="mt-4 space-y-3 text-sm"><div><dt class="text-slate-500">Directory</dt><dd class="font-bold">Free-TV</dd></div><div><dt class="text-slate-500">Format</dt><dd class="font-bold uppercase">{{ $stream->format }}</dd></div><div><dt class="text-slate-500">Channel ID</dt><dd class="break-all font-bold">{{ $channel->tvChannel?->call_sign }}</dd></div></dl></aside></div></section>
@endsection
