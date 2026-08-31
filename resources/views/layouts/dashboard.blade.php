<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="robots" content="noindex, nofollow, noarchive"><title>{{ $title ?? 'Dashboard' }} — Wavexa</title>
    @fonts @vite(['resources/css/app.css', 'resources/js/app.js']) @livewireStyles
</head>
<body data-dashboard class="min-h-screen bg-stone-100 font-sans text-slate-950 antialiased" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <a href="#dashboard-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100]">Skip to dashboard content</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/45 lg:hidden" @click="sidebarOpen = false"></div>
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-[280px] flex-col bg-slate-950 text-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="flex h-20 items-center justify-between border-b border-white/10 px-5"><a wire:navigate href="{{ route('admin.dashboard') }}" class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl bg-orange-600"><svg viewBox="0 0 24 24" class="size-6"><path d="M3 12h2l2-6 3 12 3-9 2 6 2-3h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4"/></svg></span><span><strong class="font-display block text-xl">wavexa</strong><small class="text-[8px] font-bold uppercase tracking-[.22em] text-slate-400">Operations</small></span></a><button @click="sidebarOpen = false" class="grid size-10 place-items-center rounded-xl bg-white/10 text-xl lg:hidden" aria-label="Close menu">×</button></div>
            <nav class="flex-1 overflow-y-auto p-4" aria-label="Dashboard navigation">
                @foreach(config('menu.admin', []) as $section)
                    <section class="mb-6"><p class="px-3 pb-2 text-[9px] font-extrabold uppercase tracking-[.22em] text-slate-500">{{ $section['label'] }}</p><div class="space-y-1">
                        @foreach($section['items'] as $item)
                            @php($itemActive = isset($item['active']) && request()->routeIs($item['active']))
                            @php($childActive = $itemActive || collect($item['children'] ?? [])->contains(fn ($child) => isset($child['active']) && request()->routeIs($child['active'])))
                            @if(isset($item['route']))
                                <a wire:navigate href="{{ route($item['route']) }}" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ $itemActive ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"><span class="grid size-7 place-items-center rounded-lg {{ $itemActive ? 'bg-orange-100 text-orange-700' : 'bg-white/10' }}">◫</span><strong class="text-sm">{{ $item['label'] }}</strong></a>
                            @else
                                <details class="group" @if($childActive) open @endif><summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-slate-300 hover:bg-white/10 hover:text-white"><span class="grid size-7 place-items-center rounded-lg bg-white/10">{{ $item['icon'] === 'signal' ? '⌁' : '◫' }}</span><strong class="flex-1 text-sm">{{ $item['label'] }}</strong><span class="text-xs text-slate-500 transition group-open:rotate-90">›</span></summary><div class="ml-6 mt-1 space-y-1 border-l border-white/10 pl-4">@foreach($item['children'] as $child)@if(isset($child['route']))<a wire:navigate href="{{ route($child['route']) }}" @click="sidebarOpen = false" class="flex items-center justify-between rounded-lg px-3 py-2 text-xs font-bold {{ request()->routeIs($child['active']) ? 'bg-white text-slate-950' : 'text-slate-400 hover:bg-white/10 hover:text-white' }}">{{ $child['label'] }}</a>@else<span class="flex items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold text-slate-500"><span>{{ $child['label'] }}</span><small class="text-[8px] font-extrabold uppercase tracking-wider text-slate-600">Planned</small></span>@endif @endforeach</div></details>
                            @endif
                        @endforeach
                    </div></section>
                @endforeach
            </nav>
            <div class="border-t border-white/10 p-4"><div class="rounded-2xl bg-white/5 p-3"><p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p><div class="mt-3 flex gap-2"><a wire:navigate href="{{ route('home') }}" class="flex-1 rounded-xl bg-white/10 px-3 py-2 text-center text-xs font-bold">Public site</a><form method="POST" action="{{ route('logout') }}" class="flex-1">@csrf<button class="w-full rounded-xl bg-rose-500/15 px-3 py-2 text-xs font-bold text-rose-200">Sign out</button></form></div></div></div>
        </aside>
        <div class="min-w-0"><header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-stone-200 bg-white/95 px-4 backdrop-blur sm:px-7"><div class="flex items-center gap-3"><button @click="sidebarOpen = true" class="grid size-11 place-items-center rounded-xl border border-stone-200 lg:hidden" aria-label="Open menu">☰</button><div><p class="text-[9px] font-extrabold uppercase tracking-[.2em] text-orange-600">Wavexa operations</p><h1 class="font-display text-xl font-extrabold">{{ $title ?? 'Dashboard' }}</h1></div></div><span class="hidden rounded-full bg-emerald-100 px-3 py-2 text-xs font-extrabold text-emerald-800 sm:inline-flex">Admin access</span></header><main id="dashboard-content" class="p-4 sm:p-7 lg:p-10">{{ $slot }}</main></div>
    </div>
    @persist('wavexa-radio-player')
    <div data-radio-dock class="fixed inset-x-3 bottom-3 z-[60] hidden rounded-[22px] bg-slate-950 p-3 text-white shadow-2xl md:inset-x-auto md:bottom-5 md:right-5 md:w-[420px]">
        <div class="flex items-center gap-3"><span data-player-art class="grid size-12 shrink-0 place-items-center rounded-2xl bg-orange-500 text-lg font-black">W</span><span class="min-w-0 flex-1"><small class="block text-[10px] font-bold uppercase tracking-wider text-emerald-300">Playing live</small><strong data-player-title class="block truncate text-sm">Wavexa Radio</strong><span data-player-status class="block truncate text-xs text-slate-400">Connecting…</span></span><button data-player-report type="button" class="text-[10px] font-bold text-slate-400 underline">Report</button><button data-player-toggle type="button" class="grid size-11 shrink-0 place-items-center rounded-full bg-white text-slate-950" aria-label="Pause live radio"><svg data-pause-icon viewBox="0 0 24 24" class="size-5" fill="currentColor"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg><svg data-play-icon viewBox="0 0 24 24" class="ml-0.5 hidden size-5" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg></button></div><audio data-radio-audio preload="none"></audio>
    </div>
    @endpersist
    @persist('wavexa-tv-player')<x-tv-player-dock />@endpersist
    @livewireScripts
</body>
</html>
