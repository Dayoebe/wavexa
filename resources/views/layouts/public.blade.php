<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#fafaf9">
        <meta name="description" content="@yield('description', 'Discover live media from around the world with Wavexa.')">
        <title>@yield('title', 'Wavexa')</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-50 font-sans text-slate-950 antialiased">
        <a href="#main-content" class="sr-only z-[100] rounded-xl bg-slate-950 px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

        <header class="sticky top-0 z-40 border-b border-stone-200 bg-stone-50/95 backdrop-blur-xl">
            <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="Wavexa home">
                    <span class="grid size-10 place-items-center rounded-[14px] bg-orange-500 text-white shadow-sm"><svg viewBox="0 0 24 24" class="size-6" aria-hidden="true"><path d="M3 12h2l2-6 3 12 3-9 2 6 2-3h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4"/></svg></span>
                    <span><span class="block text-xl font-extrabold leading-none tracking-[-0.04em]">wavexa</span><span class="mt-1 hidden text-[9px] font-bold uppercase tracking-[0.25em] text-slate-400 sm:block">The world is live</span></span>
                </a>
                <nav class="hidden items-center gap-1 rounded-full border border-stone-200 bg-white p-1 text-sm font-semibold shadow-sm md:flex" aria-label="Primary navigation">
                    <a href="{{ route('home') }}" class="rounded-full px-5 py-2 text-slate-600 hover:bg-stone-100">Discover</a>
                    <a href="{{ route('radio.index') }}" class="rounded-full px-5 py-2 {{ request()->routeIs('radio.*') ? 'bg-orange-500 text-white' : 'text-slate-600 hover:bg-orange-50' }}">Radio</a>
                    <a href="{{ route('tv.index') }}" class="rounded-full px-5 py-2 {{ request()->routeIs('tv.*') ? 'bg-cyan-600 text-white' : 'text-slate-600 hover:bg-cyan-50' }}">TV</a><span class="rounded-full px-5 py-2 text-slate-400">Podcasts</span>
                </nav>
                <span class="inline-flex items-center gap-2 rounded-full {{ request()->routeIs('tv.*') ? 'bg-cyan-100 text-cyan-800' : 'bg-emerald-100 text-emerald-800' }} px-3 py-2 text-xs font-bold"><span class="size-2 rounded-full {{ request()->routeIs('tv.*') ? 'bg-cyan-500' : 'bg-emerald-500' }}"></span><span class="hidden sm:inline">{{ request()->routeIs('tv.*') ? 'TV live' : 'Radio live' }}</span></span>
            </div>
        </header>

        <main id="main-content" class="pb-28 md:pb-0">@yield('content')</main>
        <footer class="border-t border-stone-200 bg-white"><div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 pb-28 pt-8 text-sm text-slate-500 sm:flex-row sm:justify-between sm:px-6 md:py-8 lg:px-8"><p>© {{ now()->year }} Wavexa. The world is live.</p><p>Streams play directly from their providers.</p></div></footer>

        <nav class="fixed inset-x-3 bottom-3 z-50 grid grid-cols-4 rounded-[24px] border border-stone-200 bg-white/95 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 shadow-2xl shadow-slate-900/20 backdrop-blur-xl md:hidden" aria-label="Mobile navigation">
            <a href="{{ route('home') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold text-slate-500"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-2 4-4 2 2-4Z"/></svg>Discover</a>
            <a href="{{ route('radio.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold {{ request()->routeIs('radio.*') ? 'bg-orange-100 text-orange-700' : 'text-slate-500' }}"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="m7 7 10-4M8 13h.01M12 13h5"/></svg>Radio</a>
            <a href="{{ route('tv.index') }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold {{ request()->routeIs('tv.*') ? 'bg-cyan-100 text-cyan-800' : 'text-slate-500' }}"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m9 10 5 3-5 3Z"/></svg>TV</a>
            <span class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold text-slate-400"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>Profile</span>
        </nav>

        <div data-radio-dock class="fixed inset-x-3 bottom-24 z-40 hidden rounded-[22px] bg-slate-950 p-3 text-white shadow-2xl md:inset-x-auto md:bottom-5 md:right-5 md:w-[420px]">
            <div class="flex items-center gap-3"><span data-player-art class="grid size-12 shrink-0 place-items-center rounded-2xl bg-orange-500 text-lg font-black">W</span><span class="min-w-0 flex-1"><small class="block text-[10px] font-bold uppercase tracking-wider text-emerald-300">Playing live</small><strong data-player-title class="block truncate text-sm">Wavexa Radio</strong><span data-player-status class="block truncate text-xs text-slate-400">Connecting…</span></span><button data-player-report type="button" class="text-[10px] font-bold text-slate-400 underline">Report</button><button data-player-toggle type="button" class="grid size-11 shrink-0 place-items-center rounded-full bg-white text-slate-950" aria-label="Pause live radio"><svg data-pause-icon viewBox="0 0 24 24" class="size-5" fill="currentColor"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg><svg data-play-icon viewBox="0 0 24 24" class="ml-0.5 hidden size-5" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg></button></div>
            <audio data-radio-audio preload="none"></audio>
        </div>
    </body>
</html>
