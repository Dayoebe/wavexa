<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    <section class="rounded-[30px] bg-slate-950 p-6 text-white sm:p-10">
        <p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-orange-400">Private to your account</p>
        <h1 class="mt-3 text-4xl font-extrabold tracking-[-.04em] sm:text-6xl">Your library</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Return to saved stations, channels, shows, and episodes. Your listening and viewing history is visible only to you.</p>
    </section>

    @if(session('status'))<p class="mt-5 rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('status') }}</p>@endif
    <div class="mt-7 flex items-center justify-between gap-3 border-b border-stone-200">
        <nav class="flex gap-5" aria-label="Library sections"><button wire:click="showTab('favorites')" class="border-b-2 px-1 pb-3 text-sm font-extrabold {{ $tab === 'favorites' ? 'border-orange-500 text-slate-950' : 'border-transparent text-slate-500' }}">Favorites</button><button wire:click="showTab('history')" class="border-b-2 px-1 pb-3 text-sm font-extrabold {{ $tab === 'history' ? 'border-orange-500 text-slate-950' : 'border-transparent text-slate-500' }}">Recently played</button></nav>
        @if($tab === 'history' && $items->isNotEmpty())<button wire:click="clearHistory" wire:confirm="Clear your entire playback history?" class="mb-3 rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700">Clear history</button>@endif
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($items as $item)
            @php($media = $item->media) @continue(!$media)
            @php($type = $media->type->value) @php($art = $media->artworks->firstWhere('is_primary', true)?->url)
            @php($url = match($type) { 'radio' => route('radio.show', $media->slug), 'tv' => route('tv.show', $media->slug), 'podcast' => route('podcasts.show', $media->slug), default => $media->podcastEpisode?->podcast?->media ? route('podcasts.show', $media->podcastEpisode->podcast->media->slug) : route('search', ['q' => $media->name]) })
            <article wire:key="library-{{ $tab }}-{{ $item->id }}" class="flex gap-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                <span class="relative grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-orange-100 text-xl font-black text-orange-800">{{ mb_strtoupper(mb_substr($media->name, 0, 1)) }}@if($art)<img src="{{ $art }}" alt="" class="absolute inset-0 size-full object-cover">@endif</span>
                <div class="min-w-0 flex-1"><p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">{{ str($type)->replace('_', ' ')->headline() }} · {{ $media->country?->name ?? 'Global' }}</p><a wire:navigate href="{{ $url }}" class="mt-1 block truncate font-extrabold hover:text-orange-700">{{ $media->name }}</a>@if($tab === 'history')<p class="mt-2 text-xs text-slate-500">{{ $item->last_played_at->diffForHumans() }} · {{ number_format($item->play_count) }} {{ str('play')->plural($item->play_count) }}</p>@else<button wire:click="removeFavorite({{ $media->id }})" class="mt-2 text-xs font-bold text-rose-700">Remove</button>@endif
                    @if($media->primaryStream && $type === 'radio')<button type="button" data-play-station data-title="{{ $media->name }}" data-slug="{{ $media->slug }}" data-streams="{{ collect([['id'=>$media->primaryStream->id,'url'=>$media->primaryStream->resolved_url ?: $media->primaryStream->url,'format'=>$media->primaryStream->format]])->toJson() }}" class="mt-3 rounded-xl bg-slate-950 px-3 py-2 text-xs font-bold text-white">▶ Play again</button>@endif
                    @if($media->primaryStream && $type === 'tv')<button type="button" data-play-tv data-title="{{ $media->name }}" data-slug="{{ $media->slug }}" data-streams="{{ collect([['id'=>$media->primaryStream->id,'url'=>$media->primaryStream->resolved_url ?: $media->primaryStream->url,'format'=>$media->primaryStream->format]])->toJson() }}" class="mt-3 rounded-xl bg-slate-950 px-3 py-2 text-xs font-bold text-white">▶ Watch again</button>@endif
                    @if($media->primaryStream && $type === 'podcast_episode')<button type="button" data-play-podcast data-media="{{ $media->id }}" data-url="{{ $media->primaryStream->resolved_url ?: $media->primaryStream->url }}" data-format="{{ $media->primaryStream->format }}" data-title="{{ $media->name }}" data-show="{{ $media->podcastEpisode?->podcast?->media?->name }}" data-art="{{ $art }}" class="mt-3 rounded-xl bg-slate-950 px-3 py-2 text-xs font-bold text-white">▶ Play again</button>@endif
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-3xl border border-dashed border-stone-300 bg-white p-12 text-center"><h2 class="text-2xl font-extrabold">{{ $tab === 'favorites' ? 'Nothing saved yet' : 'No playback history yet' }}</h2><p class="mt-2 text-slate-500">{{ $tab === 'favorites' ? 'Use the save button on a station, channel, podcast, or episode.' : 'Media you play while signed in will appear here.' }}</p><a wire:navigate href="{{ route('home') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">Explore Wavexa</a></div>
        @endforelse
    </div>
    <div class="mt-7">{{ $items->links('livewire.components.pagination', data: ['scrollTo' => 'main']) }}</div>
</div>
