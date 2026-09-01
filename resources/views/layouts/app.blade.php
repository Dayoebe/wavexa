<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @php
            $seoTitle = $title ?? trim($__env->yieldContent('title', 'Wavexa — The world is live'));
            $seoDescription = \App\Support\Seo::description($description ?? trim($__env->yieldContent('description', 'Discover live radio and supported live television streams from around the world with Wavexa.')));
            $seoCanonical = $canonical ?? trim($__env->yieldContent('canonical', \App\Support\Seo::canonical()));
            $seoImage = \App\Support\Seo::image($metaImage ?? (trim($__env->yieldContent('meta_image')) ?: null));
            $seoRobots = $robots ?? (request()->query() === [] ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' : 'noindex, follow');
            $seoSchema = $structuredData ?? (trim($__env->yieldContent('structured_data')) ?: \App\Support\Seo::schema($seoTitle, $seoDescription, $seoCanonical));
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#fafaf9">
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="robots" content="{{ $seoRobots }}">
        <link rel="canonical" href="{{ $seoCanonical }}">
        <link rel="alternate" type="application/rss+xml" title="Wavexa recently added live media" href="{{ \App\Support\Seo::siteUrl('feed.xml') }}">
        <meta property="og:type" content="website"><meta property="og:site_name" content="Wavexa"><meta property="og:locale" content="en_US">
        <meta property="og:title" content="{{ $seoTitle }}"><meta property="og:description" content="{{ $seoDescription }}"><meta property="og:url" content="{{ $seoCanonical }}"><meta property="og:image" content="{{ $seoImage }}">
        <meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="{{ $seoTitle }}"><meta name="twitter:description" content="{{ $seoDescription }}"><meta name="twitter:image" content="{{ $seoImage }}">
        <title>{{ $seoTitle }}</title>
        <script type="application/ld+json">{!! $seoSchema !!}</script>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-stone-50 font-sans text-slate-950 antialiased">
        <a wire:navigate href="#main-content" class="sr-only z-[100] rounded-xl bg-slate-950 px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

        <div x-data="{ searchOpen: false }" @keydown.escape.window="searchOpen = false">
        <header class="sticky top-0 z-40 border-b border-stone-200/90 bg-[#fcfbf8]/95 backdrop-blur-xl">
            <div class="mx-auto flex h-[74px] max-w-7xl items-center justify-between px-4 sm:h-20 sm:px-6 lg:px-8">
                <a wire:navigate href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="Wavexa home">
                    <span class="grid size-11 place-items-center rounded-[15px] bg-orange-600 text-white shadow-sm"><svg viewBox="0 0 24 24" class="size-6" aria-hidden="true"><path d="M3 12h2l2-6 3 12 3-9 2 6 2-3h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4"/></svg></span>
                    <span><span class="font-display block text-[22px] font-extrabold leading-none tracking-[-0.05em]">wavexa</span><span class="mt-1 hidden text-[8px] font-bold uppercase tracking-[0.28em] text-slate-400 sm:block">The world is live</span></span>
                </a>
                <nav class="hidden items-center gap-7 text-sm font-semibold md:flex" aria-label="Primary navigation">
                    <a wire:navigate href="{{ route('home') }}" class="border-b-2 py-2 {{ request()->routeIs('home') ? 'border-slate-950 text-slate-950' : 'border-transparent text-slate-500 hover:text-slate-950' }}">Discover</a>
                    <a wire:navigate href="{{ route('radio.index') }}" class="border-b-2 py-2 {{ request()->routeIs('radio.*') ? 'border-orange-600 text-slate-950' : 'border-transparent text-slate-500 hover:text-slate-950' }}">Live radio</a>
                    <a wire:navigate href="{{ route('tv.index') }}" class="border-b-2 py-2 {{ request()->routeIs('tv.*') ? 'border-cyan-600 text-slate-950' : 'border-transparent text-slate-500 hover:text-slate-950' }}">Live TV</a><a wire:navigate href="{{ route('podcasts.index') }}" class="border-b-2 py-2 {{ request()->routeIs('podcasts.*') ? 'border-amber-600 text-slate-950' : 'border-transparent text-slate-500 hover:text-slate-950' }}">Podcasts</a>
                </nav>
                <div class="flex items-center gap-2">
                    <button type="button" @click="searchOpen = true; $nextTick(() => $refs.globalSearch.focus())" class="grid size-11 place-items-center rounded-full border border-stone-300 bg-white text-slate-700 shadow-sm" aria-label="Open search"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></button>
                    @guest
                        <a wire:navigate href="{{ route('login') }}" class="grid size-11 place-items-center rounded-full border border-stone-300 bg-white text-slate-700 sm:hidden" aria-label="Sign in"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></a>
                        <a wire:navigate href="{{ route('login') }}" class="hidden rounded-xl px-3 py-2 text-sm font-bold text-slate-600 sm:inline-flex">Sign in</a><a wire:navigate href="{{ route('register') }}" class="hidden rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-extrabold text-white sm:inline-flex">Create account</a>
                    @else
                        @if(auth()->user()->is_admin)<a wire:navigate href="{{ route('admin.dashboard') }}" class="hidden rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-extrabold text-white sm:inline-flex">Dashboard</a>@endif
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="grid size-11 place-items-center rounded-full bg-orange-100 text-sm font-extrabold text-orange-800" aria-label="Sign out {{ auth()->user()->name }}">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</button></form>
                    @endguest
                </div>
            </div>
        </header>

        <div x-cloak x-show="searchOpen" x-transition.opacity class="fixed inset-0 z-[80] bg-slate-950/45 p-4 backdrop-blur-sm" @click.self="searchOpen = false">
            <div x-show="searchOpen" x-transition class="mx-auto mt-16 max-w-2xl rounded-[28px] bg-white p-4 shadow-2xl sm:mt-28 sm:p-6">
                <div class="flex items-center justify-between"><div><p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-orange-600">Global search</p><h2 class="mt-1 text-xl font-extrabold">Find a live signal</h2></div><button @click="searchOpen = false" class="grid size-10 place-items-center rounded-full bg-stone-100 text-xl" aria-label="Close search">×</button></div>
                <form action="{{ route('radio.index') }}" class="mt-5 flex rounded-2xl border border-stone-300 bg-stone-50 p-2"><input x-ref="globalSearch" name="q" class="min-w-0 flex-1 bg-transparent px-3 outline-none" placeholder="Station, channel, city, or language"><button class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">Search radio</button></form>
                <div class="mt-4 grid grid-cols-3 gap-3"><a wire:navigate @click="searchOpen = false" href="{{ route('radio.index') }}" class="rounded-2xl bg-orange-50 p-4 text-sm font-bold text-orange-800">Radio →</a><a wire:navigate @click="searchOpen = false" href="{{ route('tv.index') }}" class="rounded-2xl bg-cyan-50 p-4 text-sm font-bold text-cyan-800">TV →</a><a wire:navigate @click="searchOpen = false" href="{{ route('podcasts.index') }}" class="rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-800">Podcasts →</a></div>
            </div>
        </div>
        </div>

        <main id="main-content" class="pb-28 md:pb-0">{{ $slot ?? '' }}@yield('content')</main>
        <footer class="border-t border-stone-200 bg-slate-950 text-white"><div class="mx-auto grid max-w-7xl gap-8 px-4 pb-32 pt-10 sm:grid-cols-2 sm:px-6 md:pb-10 lg:grid-cols-[1fr_auto] lg:px-8"><div><p class="font-display text-2xl font-extrabold">wavexa<span class="text-orange-500">.</span></p><p class="mt-3 max-w-md text-sm leading-6 text-slate-400">A global discovery layer for live voices, television, and culture. Streams connect directly to their listed providers.</p></div><div class="flex gap-3 self-end text-xs font-bold"><a wire:navigate href="{{ route('radio.index') }}" class="rounded-full border border-white/15 px-4 py-2">Radio</a><a wire:navigate href="{{ route('tv.index') }}" class="rounded-full border border-white/15 px-4 py-2">TV</a><a wire:navigate href="{{ route('podcasts.index') }}" class="rounded-full border border-white/15 px-4 py-2">Podcasts</a></div></div></footer>

        <nav class="fixed inset-x-3 bottom-3 z-50 grid grid-cols-4 rounded-[24px] border border-stone-200 bg-white/95 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 shadow-2xl shadow-slate-900/20 backdrop-blur-xl md:hidden" aria-label="Mobile navigation" x-data>
            <a wire:navigate href="{{ route('home') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold text-slate-500"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-2 4-4 2 2-4Z"/></svg>Discover</a>
            <a wire:navigate href="{{ route('radio.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold {{ request()->routeIs('radio.*') ? 'bg-orange-100 text-orange-700' : 'text-slate-500' }}"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="m7 7 10-4M8 13h.01M12 13h5"/></svg>Radio</a>
            <a wire:navigate href="{{ route('tv.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold {{ request()->routeIs('tv.*') ? 'bg-cyan-100 text-cyan-800' : 'text-slate-500' }}"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m9 10 5 3-5 3Z"/></svg>TV</a>
            <a wire:navigate href="{{ route('podcasts.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold {{ request()->routeIs('podcasts.*') ? 'bg-amber-100 text-amber-800' : 'text-slate-500' }}"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="11" r="3"/><path d="M7.5 16.5a7 7 0 1 1 9 0M9.5 14.5a4 4 0 1 1 5 0M12 14v7"/></svg>Podcasts</a>
        </nav>

        @persist('wavexa-radio-player')
        <div data-radio-dock class="fixed inset-x-3 bottom-24 z-40 hidden rounded-[22px] bg-slate-950 p-3 text-white shadow-2xl md:inset-x-auto md:bottom-5 md:right-5 md:w-[420px]">
            <div class="flex items-center gap-3"><span data-player-art class="grid size-12 shrink-0 place-items-center rounded-2xl bg-orange-500 text-lg font-black">W</span><span class="min-w-0 flex-1"><small class="block text-[10px] font-bold uppercase tracking-wider text-emerald-300">Playing live</small><strong data-player-title class="block truncate text-sm">Wavexa Radio</strong><span data-player-status class="block truncate text-xs text-slate-400">Connecting…</span></span><button data-player-report type="button" class="text-[10px] font-bold text-slate-400 underline">Report</button><button data-player-toggle type="button" class="grid size-11 shrink-0 place-items-center rounded-full bg-white text-slate-950" aria-label="Pause live radio"><svg data-pause-icon viewBox="0 0 24 24" class="size-5" fill="currentColor"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg><svg data-play-icon viewBox="0 0 24 24" class="ml-0.5 hidden size-5" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg></button></div>
            <audio data-radio-audio preload="none"></audio>
        </div>
        @endpersist
        @persist('wavexa-tv-player')
            <x-tv-player-dock />
        @endpersist
        @persist('wavexa-podcast-player')
            <x-podcast-player-dock />
        @endpersist
        @livewireScripts
    </body>
</html>
