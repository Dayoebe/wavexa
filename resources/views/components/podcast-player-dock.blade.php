<div data-podcast-dock class="fixed inset-x-3 bottom-24 z-[55] hidden overflow-hidden rounded-[22px] border border-white/10 bg-slate-950 text-white shadow-2xl shadow-slate-950/35 md:inset-x-auto md:bottom-5 md:right-5 md:w-[440px]" aria-label="Persistent podcast player">
    <div data-podcast-video-wrap class="hidden bg-black">
        <video data-podcast-video controls playsinline preload="metadata" class="aspect-video w-full bg-black object-contain"></video>
    </div>
    <div class="flex items-center gap-3 p-3">
        <span data-podcast-art class="relative grid size-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-amber-400 text-lg font-black text-slate-950">P</span>
        <div class="min-w-0 flex-1"><small data-podcast-kind class="block text-[9px] font-extrabold uppercase tracking-[.18em] text-amber-300">Podcast</small><strong data-podcast-title class="block truncate text-sm">Wavexa Podcasts</strong><span data-podcast-show class="block truncate text-xs text-slate-400"></span></div>
        <button data-podcast-toggle type="button" class="grid size-11 shrink-0 place-items-center rounded-full bg-white text-slate-950" aria-label="Pause podcast"><svg data-podcast-pause viewBox="0 0 24 24" class="size-5" fill="currentColor"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg><svg data-podcast-play viewBox="0 0 24 24" class="ml-0.5 hidden size-5" fill="currentColor"><path d="m8 5 11 7-11 7Z"/></svg></button>
        <button data-podcast-close type="button" class="grid size-9 shrink-0 place-items-center rounded-full bg-white/10 text-lg" aria-label="Close podcast">×</button>
    </div>
    <audio data-podcast-audio preload="metadata"></audio>
</div>
