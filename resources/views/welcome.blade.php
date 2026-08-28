<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @php
            $seoTitle = 'Wavexa — Discover live radio and TV worldwide';
            $seoDescription = 'Explore live radio stations and supported live television streams by country on Wavexa.';
            $seoCanonical = \App\Support\Seo::siteUrl();
            $seoImage = \App\Support\Seo::image();
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="{{ $seoCanonical }}">
        <link rel="alternate" type="application/rss+xml" title="Wavexa recently added live media" href="{{ \App\Support\Seo::siteUrl('feed.xml') }}">
        <meta property="og:type" content="website"><meta property="og:site_name" content="Wavexa"><meta property="og:locale" content="en_US">
        <meta property="og:title" content="{{ $seoTitle }}"><meta property="og:description" content="{{ $seoDescription }}"><meta property="og:url" content="{{ $seoCanonical }}"><meta property="og:image" content="{{ $seoImage }}">
        <meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="{{ $seoTitle }}"><meta name="twitter:description" content="{{ $seoDescription }}"><meta name="twitter:image" content="{{ $seoImage }}">
        <meta name="theme-color" content="#fff7ed">
        <title>{{ $seoTitle }}</title>
        <script type="application/ld+json">{!! \App\Support\Seo::schema($seoTitle, $seoDescription, $seoCanonical) !!}</script>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-50 font-sans text-slate-950 antialiased">
        <a wire:navigate href="#main-content" class="sr-only z-[100] rounded-xl bg-slate-950 px-4 py-3 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

        <header class="sticky top-0 z-40 border-b border-stone-200 bg-stone-50/95 backdrop-blur-xl">
            <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a wire:navigate href="{{ url('/') }}" class="flex items-center gap-2.5" aria-label="Wavexa home">
                    <span class="grid size-10 place-items-center rounded-[14px] bg-orange-500 text-white shadow-sm">
                        <svg viewBox="0 0 24 24" class="size-6" aria-hidden="true">
                            <path d="M3 12h2l2-6 3 12 3-9 2 6 2-3h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4"/>
                        </svg>
                    </span>
                    <span>
                        <span class="block text-xl font-extrabold leading-none tracking-[-0.04em]">wavexa</span>
                        <span class="mt-1 hidden text-[9px] font-bold uppercase tracking-[0.25em] text-slate-400 sm:block">The world is live</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 rounded-full border border-stone-200 bg-white p-1 text-sm font-semibold shadow-sm md:flex" aria-label="Primary navigation">
                    <a wire:navigate href="#discover" class="rounded-full bg-slate-950 px-5 py-2 text-white">Discover</a>
                    <a wire:navigate href="{{ route('radio.index') }}" class="rounded-full px-5 py-2 text-slate-600 hover:bg-orange-50 hover:text-orange-700">Radio</a>
                    <a wire:navigate href="{{ route('tv.index') }}" class="rounded-full px-5 py-2 text-slate-600 hover:bg-cyan-50 hover:text-cyan-700">TV</a>
                    <a wire:navigate href="#podcasts" class="rounded-full px-5 py-2 text-slate-600 hover:bg-violet-50 hover:text-violet-700">Podcasts</a>
                </nav>

                <button type="button" class="grid size-10 place-items-center rounded-full border border-stone-200 bg-white text-slate-700 shadow-sm" aria-label="Open profile preview">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                </button>
            </div>
        </header>

        <main id="main-content" class="overflow-hidden pb-24 md:pb-0">
            <section id="discover" class="relative border-b border-stone-200 bg-orange-50">
                <div class="absolute -right-14 top-14 size-36 rounded-full bg-amber-300/60" aria-hidden="true"></div>
                <div class="absolute right-16 top-36 size-12 rounded-full bg-rose-300" aria-hidden="true"></div>
                <div class="absolute -left-10 bottom-10 size-28 rounded-full bg-cyan-200/70" aria-hidden="true"></div>

                <div class="relative mx-auto grid max-w-7xl gap-10 px-4 pb-14 pt-8 sm:px-6 sm:pb-20 sm:pt-14 lg:grid-cols-[1fr_0.82fr] lg:items-center lg:px-8 lg:py-20">
                    <div>
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 shadow-sm ring-1 ring-stone-200">
                                <span class="size-2 rounded-full bg-emerald-500"></span>
                                Good afternoon
                            </span>
                            <span class="hidden sm:inline">Ready to explore?</span>
                        </div>

                        <h1 class="mt-6 max-w-3xl text-[2.8rem] font-extrabold leading-[0.98] tracking-[-0.055em] sm:text-6xl lg:text-7xl">
                            Find your next
                            <span class="text-orange-600">frequency.</span>
                        </h1>
                        <p class="mt-5 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Travel through live voices, screens, and stories—organized by country, city, language, and the mood you are in.</p>

                        <div class="mt-7 max-w-2xl rounded-[22px] border border-stone-200 bg-white p-2 shadow-xl shadow-orange-900/5">
                            <div class="flex items-center">
                                <span class="ml-3 text-slate-400" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                                </span>
                                <span class="min-w-0 flex-1 truncate px-3 py-3 text-sm text-slate-400">Search a place, language, station or show</span>
                                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-slate-950 text-white" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                                </span>
                            </div>
                        </div>

                        <div class="mt-7 grid grid-cols-3 gap-2.5 sm:max-w-xl sm:gap-3" aria-label="Media types">
                            <a wire:navigate href="{{ route('radio.index') }}" class="min-w-0 rounded-2xl bg-orange-500 p-3.5 text-white shadow-sm transition hover:-translate-y-0.5 sm:p-4">
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10v4M8 7v10M12 4v16M16 8v8M20 10v4"/></svg>
                                <strong class="mt-3 block text-sm">Radio</strong>
                                <span class="hidden text-xs text-orange-100 sm:block">Listen live</span>
                            </a>
                            <a wire:navigate href="{{ route('tv.index') }}" class="min-w-0 rounded-2xl bg-cyan-500 p-3.5 text-white shadow-sm transition hover:-translate-y-0.5 sm:p-4">
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="15" rx="2"/><path d="m9 9 6 3-6 3Z"/></svg>
                                <strong class="mt-3 block text-sm">TV</strong>
                                <span class="hidden text-xs text-cyan-100 sm:block">Watch live</span>
                            </a>
                            <a wire:navigate href="#podcasts" class="min-w-0 rounded-2xl bg-violet-600 p-3.5 text-white shadow-sm transition hover:-translate-y-0.5 sm:p-4">
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="11" r="3"/><path d="M7.8 15.2a6 6 0 1 1 8.4 0M5 18a9 9 0 1 1 14 0M10 17h4l-1 5h-2Z"/></svg>
                                <strong class="mt-3 block text-sm">Podcasts</strong>
                                <span class="hidden text-xs text-violet-100 sm:block">Find stories</span>
                            </a>
                        </div>
                    </div>

                    <div class="relative hidden min-h-[520px] lg:block" aria-label="Wavexa discovery preview">
                        <div class="absolute inset-8 rounded-[44px] border border-orange-200 bg-white p-5 shadow-2xl shadow-orange-900/10">
                            <div class="flex items-center justify-between">
                                <div><span class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Now exploring</span><strong class="mt-1 block text-xl">West Africa</strong></div>
                                <span class="grid size-11 place-items-center rounded-full bg-emerald-100 text-emerald-700"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5 7-12a7 7 0 1 0-14 0c0 7 7 12 7 12Z"/><circle cx="12" cy="9" r="2"/></svg></span>
                            </div>
                            <div class="relative mt-5 h-56 overflow-hidden rounded-[28px] bg-sky-100">
                                <span class="absolute -left-8 top-9 size-44 rounded-full bg-emerald-300"></span>
                                <span class="absolute left-24 top-20 size-32 rounded-full bg-lime-300"></span>
                                <span class="absolute right-0 top-2 size-40 rounded-full bg-amber-200"></span>
                                <span class="absolute bottom-4 right-20 size-5 rounded-full border-4 border-white bg-rose-500 shadow-lg"></span>
                                <span class="absolute bottom-16 left-24 size-4 rounded-full border-4 border-white bg-violet-600 shadow-lg"></span>
                                <span class="absolute right-8 top-8 rounded-full bg-white px-3 py-1.5 text-xs font-bold shadow-sm">Drag to explore</span>
                            </div>
                            <div class="mt-5 flex items-center gap-3 rounded-2xl bg-slate-950 p-3 text-white">
                                <span class="grid size-12 place-items-center rounded-xl bg-orange-500"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10v4M8 7v10M12 4v16M16 8v8M20 10v4"/></svg></span>
                                <span class="min-w-0 flex-1"><span class="block text-xs text-slate-400">Discovery preview</span><strong class="block truncate">Voices from the region</strong></span>
                                <span class="grid size-10 place-items-center rounded-full bg-white text-slate-950"><svg viewBox="0 0 24 24" class="ml-0.5 size-4" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg></span>
                            </div>
                        </div>
                        <span class="absolute right-0 top-20 grid size-20 place-items-center rounded-[26px] bg-amber-300 text-3xl shadow-xl">🌍</span>
                        <span class="absolute bottom-5 left-0 rounded-2xl bg-rose-500 px-4 py-3 text-sm font-bold text-white shadow-xl">Culture travels here</span>
                    </div>
                </div>
            </section>

            <section class="py-12 sm:py-18" aria-labelledby="places-heading">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-orange-600">Start with a place</p>
                            <h2 id="places-heading" class="mt-2 text-2xl font-extrabold tracking-tight sm:text-4xl">Where do you want to go?</h2>
                        </div>
                        <span class="hidden text-sm font-semibold text-slate-500 sm:block">Explore all countries →</span>
                    </div>

                    <div class="wavexa-rail -mx-4 mt-7 flex snap-x gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-4">
                        @foreach ([
                            ['Lagos', 'Nigeria', 'bg-orange-200', 'bg-orange-500', 'NG'],
                            ['Accra', 'Ghana', 'bg-amber-200', 'bg-emerald-600', 'GH'],
                            ['London', 'United Kingdom', 'bg-sky-200', 'bg-blue-600', 'GB'],
                            ['Johannesburg', 'South Africa', 'bg-lime-200', 'bg-violet-600', 'ZA'],
                        ] as [$city, $country, $canvas, $accent, $code])
                            <article class="group min-w-[78vw] snap-start overflow-hidden rounded-[28px] border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg sm:min-w-0">
                                <div @class(['relative h-40 overflow-hidden', $canvas])>
                                    <span @class(['absolute -bottom-16 -right-8 size-40 rounded-full', $accent])></span>
                                    <span class="absolute left-5 top-5 rounded-full bg-white/90 px-3 py-1.5 text-xs font-black shadow-sm">{{ $code }}</span>
                                    <span class="absolute bottom-5 left-5 grid size-10 place-items-center rounded-full bg-white text-slate-950 shadow-md"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10v4M8 7v10M12 4v16M16 8v8M20 10v4"/></svg></span>
                                </div>
                                <div class="p-5"><h3 class="text-xl font-extrabold">{{ $city }}</h3><p class="mt-1 text-sm text-slate-500">{{ $country }} · Explore local media</p></div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="radio" class="border-y border-stone-200 bg-white py-12 sm:py-20" aria-labelledby="radio-heading">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-[0.7fr_1.3fr] lg:items-end">
                        <div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-orange-100 px-3 py-1.5 text-xs font-bold text-orange-700"><span class="size-2 rounded-full bg-red-500 motion-safe:animate-pulse"></span> LIVE RADIO</span>
                            <h2 id="radio-heading" class="mt-4 text-3xl font-extrabold tracking-tight sm:text-5xl">Browse the dial,<br class="hidden lg:block"> not a directory.</h2>
                            <p class="mt-4 max-w-md leading-7 text-slate-600">Move naturally between location, language, genre, and mood. Every path should reveal something new.</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ([
                                ['Morning energy', 'Upbeat music and breakfast shows', 'bg-amber-300', 'text-amber-950', '☀'],
                                ['News around you', 'Local reporting by place and language', 'bg-blue-600', 'text-white', '◎'],
                                ['Home sounds', 'Music rooted in culture and community', 'bg-emerald-500', 'text-white', '♫'],
                                ['Late-night voices', 'Conversation for slower hours', 'bg-violet-600', 'text-white', '◐'],
                            ] as [$title, $copy, $background, $text, $symbol])
                                <article @class(['rounded-3xl p-5', $background, $text])>
                                    <div class="flex items-start justify-between gap-4"><span class="text-2xl" aria-hidden="true">{{ $symbol }}</span><span class="rounded-full bg-white/20 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider">Collection</span></div>
                                    <h3 class="mt-10 text-xl font-extrabold">{{ $title }}</h3><p class="mt-2 text-sm leading-6 opacity-80">{{ $copy }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-10 flex flex-wrap gap-2" aria-label="Browse by genre">
                        @foreach (['Afrobeats', 'Talk', 'Gospel', 'Jazz', 'Sports', 'News', 'Pop', 'Culture'] as $genre)
                            <span class="rounded-full border border-stone-300 bg-stone-50 px-4 py-2 text-sm font-semibold text-slate-700">{{ $genre }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="personal" class="py-12 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid overflow-hidden rounded-[32px] border border-cyan-200 bg-cyan-100 lg:grid-cols-2">
                        <div id="television" class="p-6 sm:p-10 lg:p-14">
                            <span class="text-xs font-extrabold uppercase tracking-[0.2em] text-cyan-800">Watch across borders</span>
                            <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-5xl">A window into what is happening now.</h2>
                            <p class="mt-5 max-w-lg leading-7 text-slate-700">Supported television streams will sit beside clear origin, language, source, and rights context—not inside an anonymous channel list.</p>
                            <span class="mt-7 inline-flex rounded-full bg-slate-950 px-5 py-3 text-sm font-bold text-white">TV discovery planned</span>
                        </div>
                        <div class="relative min-h-72 overflow-hidden bg-cyan-500 p-6 sm:min-h-96">
                            <div class="absolute -right-16 -top-20 size-64 rounded-full bg-blue-600"></div>
                            <div class="absolute -bottom-20 -left-16 size-60 rounded-full bg-amber-300"></div>
                            <div class="relative mx-auto mt-6 aspect-video max-w-md rounded-[28px] border-[10px] border-slate-950 bg-white p-3 shadow-2xl">
                                <div class="relative grid h-full place-items-center overflow-hidden rounded-xl bg-sky-100">
                                    <span class="absolute bottom-0 left-0 h-1/2 w-full bg-emerald-300"></span>
                                    <span class="relative grid size-16 place-items-center rounded-full bg-white text-slate-950 shadow-lg"><svg viewBox="0 0 24 24" class="ml-1 size-7" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="podcasts" class="bg-violet-50 py-12 sm:py-20" aria-labelledby="podcasts-heading">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-2xl">
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-700">Stories for every journey</p>
                        <h2 id="podcasts-heading" class="mt-3 text-3xl font-extrabold tracking-tight sm:text-5xl">Podcasts with a sense of place.</h2>
                        <p class="mt-4 leading-7 text-slate-600">Follow the subject that interests you, then understand where each perspective comes from.</p>
                    </div>

                    <div class="wavexa-rail -mx-4 mt-8 flex snap-x gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-3 sm:overflow-visible sm:px-0">
                        @foreach ([
                            ['City stories', 'People, streets and the ideas shaping urban life.', 'bg-rose-400', 'text-rose-950'],
                            ['The culture desk', 'Music, film, food and creative movements.', 'bg-amber-300', 'text-amber-950'],
                            ['Beyond headlines', 'Context-rich conversations about a changing world.', 'bg-indigo-600', 'text-white'],
                        ] as [$title, $copy, $background, $text])
                            <article @class(['min-w-[76vw] snap-start rounded-[28px] p-6 sm:min-w-0', $background, $text])>
                                <div class="flex items-center justify-between"><span class="grid size-11 place-items-center rounded-full bg-white/80 text-slate-950"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l10-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="16" cy="16" r="3"/></svg></span><span class="text-xs font-bold opacity-70">SERIES</span></div>
                                <h3 class="mt-16 text-2xl font-extrabold">{{ $title }}</h3><p class="mt-3 text-sm leading-6 opacity-80">{{ $copy }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="py-12 sm:py-20">
                <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                    <article class="rounded-[30px] border border-stone-200 bg-white p-6 lg:col-span-2">
                        <span class="grid size-12 place-items-center rounded-2xl bg-lime-300 text-lime-950"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3 2.2 4.5 5 .7-3.6 3.5.9 5-4.5-2.4-4.5 2.4.9-5-3.6-3.5 5-.7Z"/></svg></span>
                        <h2 class="mt-8 text-3xl font-extrabold tracking-tight">Your Wavexa grows with you.</h2>
                        <p class="mt-3 max-w-xl leading-7 text-slate-600">Favorites, history, and thoughtful recommendations will turn discovery into a personal map—while keeping you in control.</p>
                        <div class="mt-7 flex flex-wrap gap-2"><span class="rounded-full bg-lime-100 px-3 py-2 text-xs font-bold text-lime-800">Favorites</span><span class="rounded-full bg-sky-100 px-3 py-2 text-xs font-bold text-sky-800">History</span><span class="rounded-full bg-violet-100 px-3 py-2 text-xs font-bold text-violet-800">Recommendations</span></div>
                    </article>
                    <article class="rounded-[30px] bg-slate-950 p-6 text-white">
                        <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">Foundation ready</span>
                        <h2 class="mt-5 text-2xl font-extrabold">A catalog built for global context.</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-300">Countries, regions, cities, languages, genres, artwork, providers, and stream metadata now have a structured home.</p>
                        <div class="mt-8 flex items-center gap-2 text-sm font-bold text-emerald-300"><span class="size-2 rounded-full bg-emerald-400"></span> API operational</div>
                    </article>
                </div>
            </section>
        </main>

        <footer class="border-t border-stone-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 pb-28 pt-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 md:py-8 lg:px-8">
                <p>© {{ now()->year }} Wavexa. The world is live.</p>
                <p>Designed for discovery across web and mobile.</p>
            </div>
        </footer>

        <nav class="fixed inset-x-3 bottom-3 z-50 grid grid-cols-4 rounded-[24px] border border-stone-200 bg-white/95 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 shadow-2xl shadow-slate-900/20 backdrop-blur-xl md:hidden" aria-label="Mobile navigation">
            @foreach ([
                ['Discover', '#discover', 'compass'],
                ['Radio', route('radio.index'), 'radio'],
                ['Saved', '#personal', 'heart'],
                ['Profile', '#personal', 'profile'],
            ] as [$label, $href, $icon])
                <a wire:navigate href="{{ $href }}" @class(['flex min-h-12 flex-col items-center justify-center gap-1 rounded-2xl text-[10px] font-bold', 'bg-orange-100 text-orange-700' => $label === 'Discover', 'text-slate-500' => $label !== 'Discover'])>
                    @if ($icon === 'compass')
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-2 4-4 2 2-4Z"/></svg>
                    @elseif ($icon === 'radio')
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="m7 7 10-4M8 13h.01M12 13h5"/></svg>
                    @elseif ($icon === 'heart')
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 5.8a5.5 5.5 0 0 0-7.8 0L12 6.8l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 22l7.8-7.4 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    @endif
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </body>
</html>
