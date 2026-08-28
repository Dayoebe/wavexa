<div data-tv-dock class="fixed inset-x-3 bottom-24 z-[45] hidden overflow-hidden rounded-[24px] border border-white/10 bg-slate-950 text-white shadow-2xl shadow-slate-950/30 md:inset-x-auto md:bottom-5 md:right-5 md:w-[440px]">
    <div class="relative aspect-video bg-black">
        <video data-tv-player class="size-full bg-black object-contain" controls playsinline preload="metadata"></video>
        <div data-tv-message class="pointer-events-none absolute inset-0 grid place-items-center bg-slate-950/55 p-6 text-center text-sm font-bold">
            Connecting to the live channel…
        </div>
        <div class="absolute right-2 top-2 flex gap-2">
            <button data-tv-pip type="button" class="rounded-full bg-black/75 px-3 py-2 text-[11px] font-bold backdrop-blur" aria-label="Open picture in picture">Pop out</button>
            <button data-tv-close type="button" class="grid size-9 place-items-center rounded-full bg-black/75 text-lg font-bold backdrop-blur" aria-label="Close live TV">×</button>
        </div>
        <button data-tv-toggle type="button" class="absolute inset-0 m-auto grid size-14 place-items-center rounded-full bg-white text-slate-950 shadow-xl" aria-label="Resume live TV">
            <svg data-tv-play-icon viewBox="0 0 24 24" class="ml-1 size-6" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg>
            <svg data-tv-pause-icon viewBox="0 0 24 24" class="hidden size-6" fill="currentColor"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>
        </button>
    </div>
    <div class="flex items-center gap-3 px-4 py-3">
        <span class="size-2 shrink-0 rounded-full bg-cyan-400"></span>
        <div class="min-w-0 flex-1"><small class="block text-[9px] font-extrabold uppercase tracking-[0.18em] text-cyan-300">Live television</small><strong data-tv-title class="block truncate text-sm">Wavexa TV</strong></div>
        <button data-tv-expand type="button" class="rounded-xl bg-white/10 px-3 py-2 text-[11px] font-bold" aria-label="Expand player">Expand</button>
    </div>
</div>
