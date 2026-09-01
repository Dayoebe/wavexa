<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <section class="overflow-hidden rounded-[32px] border border-stone-200 bg-amber-50 p-6 sm:p-10">
        <p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-amber-700">Publisher-powered listening</p>
        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_.7fr] lg:items-end">
            <div><h1 class="max-w-3xl text-5xl font-extrabold leading-[.9] tracking-[-.055em] sm:text-7xl">Stories worth hearing.</h1><p class="mt-5 max-w-2xl leading-7 text-slate-600">Explore independent voices, news, culture, technology, and conversations. Episodes play directly from each publisher's public RSS feed.</p></div>
            <div><label class="block"><span class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Find a podcast</span><input wire:model.live.debounce.300ms="q" type="search" class="mt-2 min-h-14 w-full rounded-2xl border border-stone-300 bg-white px-5 text-base outline-none focus:border-amber-500" placeholder="Search title, topic, or creator"></label><nav class="mt-3 grid grid-cols-3 gap-2" aria-label="Podcast formats"><a wire:navigate href="{{ route('podcasts.index') }}" class="grid min-h-11 place-items-center rounded-xl px-3 text-xs font-extrabold {{ $format==='all'?'bg-slate-950 text-white':'border border-stone-300 bg-white text-slate-600' }}">All shows</a><a wire:navigate href="{{ route('podcasts.audio') }}" class="grid min-h-11 place-items-center rounded-xl px-3 text-xs font-extrabold {{ $format==='audio'?'bg-slate-950 text-white':'border border-stone-300 bg-white text-slate-600' }}">Audio</a><a wire:navigate href="{{ route('podcasts.video') }}" class="grid min-h-11 place-items-center rounded-xl px-3 text-xs font-extrabold {{ $format==='video'?'bg-slate-950 text-white':'border border-stone-300 bg-white text-slate-600' }}">Video</a></nav></div>
        </div>
    </section>

    <div class="mt-8 flex items-end justify-between"><div><p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-slate-400">Podcast catalogue</p><h2 class="mt-2 text-3xl font-extrabold">{{ number_format($podcasts->total()) }} shows</h2></div></div>
    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($podcasts as $item) @php($art=$item->artworks->firstWhere('is_primary',true)?->url)
            <a wire:navigate href="{{ route('podcasts.show',$item->slug) }}" class="group grid grid-cols-[88px_1fr] gap-4 rounded-3xl border border-stone-200 bg-white p-4 transition hover:-translate-y-1 hover:border-amber-400 hover:shadow-xl">
                <span class="relative grid size-[88px] place-items-center overflow-hidden rounded-2xl bg-amber-100 text-2xl font-extrabold text-amber-800">{{ mb_strtoupper(mb_substr($item->name,0,1)) }}@if($art)<img src="{{ $art }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy" onerror="this.remove()">@endif</span>
                <span class="min-w-0"><strong class="line-clamp-2 text-lg leading-5">{{ $item->name }}</strong><small class="mt-2 block truncate font-semibold text-slate-500">{{ $item->podcast?->author ?: 'Independent publisher' }}</small><span class="mt-4 flex flex-wrap gap-2"><span class="inline-flex rounded-full bg-stone-100 px-3 py-1 text-[10px] font-extrabold text-slate-600">{{ number_format($item->episode_count) }} episodes</span>@if($item->has_video)<span class="inline-flex rounded-full bg-violet-100 px-3 py-1 text-[10px] font-extrabold text-violet-800">Video</span>@endif</span></span>
            </a>
        @empty <div class="col-span-full rounded-3xl border border-dashed border-stone-300 p-12 text-center"><h3 class="text-2xl font-extrabold">No podcasts found</h3><p class="mt-2 text-slate-500">Try another title, topic, or creator.</p></div> @endforelse
    </div>
    <div class="mt-8">{{ $podcasts->onEachSide(1)->links('livewire.components.pagination',data:['scrollTo'=>'main']) }}</div>
</div>
