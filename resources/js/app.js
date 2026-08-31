const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const parseStreams = (element) => {
    try {
        const streams = JSON.parse(element?.dataset.streams || '[]');
        return streams.length ? streams : element?.dataset.stream ? [{ url: element.dataset.stream }] : [];
    } catch {
        return element?.dataset.stream ? [{ url: element.dataset.stream }] : [];
    }
};

const attachStream = async (media, stream, onFatal) => {
    if (window.location.protocol === 'https:' && stream.url.startsWith('http:')) throw new Error('mixed-content');
    const isHls = stream.format === 'hls' || stream.url.toLowerCase().includes('.m3u8');
    if (!isHls || media.canPlayType('application/vnd.apple.mpegurl')) { media.src = stream.url; return null; }
    const { default: Hls } = await import('hls.js');
    if (!Hls.isSupported()) throw new Error('unsupported');
    const hls = new Hls({ enableWorker: true, lowLatencyMode: true });
    hls.loadSource(stream.url); hls.attachMedia(media);
    hls.on(Hls.Events.ERROR, (_event, data) => data.fatal && onFatal());
    return hls;
};

const radioDock = document.querySelector('[data-radio-dock]');
const radioAudio = document.querySelector('[data-radio-audio]');
if (radioDock && radioAudio) {
    const title = radioDock.querySelector('[data-player-title]');
    const status = radioDock.querySelector('[data-player-status]');
    const artwork = radioDock.querySelector('[data-player-art]');
    const toggle = radioDock.querySelector('[data-player-toggle]');
    const report = radioDock.querySelector('[data-player-report]');
    const pauseIcon = radioDock.querySelector('[data-pause-icon]');
    const playIcon = radioDock.querySelector('[data-play-icon]');
    let state = null; let hls = null; let streamIndex = 0;
    const setPlaying = (playing) => { pauseIcon?.classList.toggle('hidden', !playing); playIcon?.classList.toggle('hidden', playing); toggle?.setAttribute('aria-label', playing ? 'Pause live radio' : 'Resume live radio'); };
    const showState = () => { radioDock.classList.remove('hidden'); title.textContent = state.title; artwork.textContent = state.art || 'W'; };
    const loadCurrent = async (autoplay = true) => {
        const stream = state?.streams?.[streamIndex];
        if (!stream) { status.textContent = 'Every available source is currently unavailable.'; setPlaying(false); return; }
        hls?.destroy(); radioAudio.removeAttribute('src');
        status.textContent = streamIndex ? 'Trying an alternative source…' : 'Connecting to the provider…';
        try { hls = await attachStream(radioAudio, stream, () => { streamIndex++; loadCurrent(); }); if (autoplay) await radioAudio.play(); }
        catch (error) { status.textContent = error.message === 'mixed-content' ? 'HTTP stream blocked. Trying another source…' : 'Trying another source…'; streamIndex++; loadCurrent(autoplay); }
    };
    const bindRadioButtons = () => document.querySelectorAll('[data-play-station]:not([data-player-bound])').forEach((button) => {
        button.dataset.playerBound = 'true';
        button.addEventListener('click', async () => {
            document.querySelector('[data-tv-close]')?.click();
            state = { title: button.dataset.title || 'Live radio', slug: button.dataset.slug, art: button.dataset.art, streams: parseStreams(button) };
            streamIndex = 0; showState(); await loadCurrent();
            fetch(`/radio/${encodeURIComponent(state.slug)}/play`, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken } }).catch(() => {});
        });
    });
    bindRadioButtons();
    document.addEventListener('livewire:navigated', bindRadioButtons);
    toggle?.addEventListener('click', () => radioAudio.paused ? loadCurrent() : radioAudio.pause());
    report?.addEventListener('click', async () => {
        const stream = state?.streams?.[streamIndex]; if (!stream?.id) return;
        const response = await fetch(`/streams/${stream.id}/report`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ reason: 'not_playing' }) });
        if (response.ok) report.textContent = 'Reported';
    });
    radioAudio.addEventListener('playing', () => { status.textContent = 'Live from the station provider'; setPlaying(true); });
    radioAudio.addEventListener('pause', () => setPlaying(false));
    radioAudio.addEventListener('waiting', () => { status.textContent = 'Buffering live audio…'; });
    radioAudio.addEventListener('error', () => { streamIndex++; loadCurrent(); });
    localStorage.removeItem('wavexa-player');
}

const tvDock = document.querySelector('[data-tv-dock]');
const tvPlayer = tvDock?.querySelector('[data-tv-player]');
if (tvDock && tvPlayer) {
    const message = tvDock.querySelector('[data-tv-message]');
    const title = tvDock.querySelector('[data-tv-title]');
    const toggle = tvDock.querySelector('[data-tv-toggle]');
    const playIcon = tvDock.querySelector('[data-tv-play-icon]');
    const pauseIcon = tvDock.querySelector('[data-tv-pause-icon]');
    const expand = tvDock.querySelector('[data-tv-expand]');
    let state = null; let streamIndex = 0; let hls = null; let closed = true;

    const syncTvPlacement = () => {
        const host = document.querySelector('[data-tv-inline-host]');
        const matchesChannel = host && state?.slug && host.dataset.channel === state.slug;
        if (!matchesChannel || tvDock.hasAttribute('data-expanded')) {
            tvDock.removeAttribute('data-inline');
            return;
        }

        const rect = host.getBoundingClientRect();
        tvDock.setAttribute('data-inline', '');
        tvDock.style.setProperty('--tv-inline-left', `${rect.left}px`);
        tvDock.style.setProperty('--tv-inline-top', `${rect.top}px`);
        tvDock.style.setProperty('--tv-inline-width', `${rect.width}px`);
    };

    const setPlaying = (playing) => {
        playIcon?.classList.toggle('hidden', playing);
        pauseIcon?.classList.toggle('hidden', !playing);
        toggle?.classList.toggle('hidden', playing);
        toggle?.setAttribute('aria-label', playing ? 'Pause live TV' : 'Resume live TV');
    };
    const showDock = () => {
        closed = false; tvDock.classList.remove('hidden');
        title.textContent = state?.title || 'Live television';
        tvPlayer.setAttribute('aria-label', `${state?.title || 'Live television'} live stream`);
        requestAnimationFrame(syncTvPlacement);
    };
    const loadCurrent = async (autoplay = true) => {
        const stream = state?.streams?.[streamIndex];
        if (!stream) { message.classList.remove('hidden'); message.textContent = 'Every available source is offline or unavailable in your location.'; setPlaying(false); return; }
        hls?.destroy(); tvPlayer.removeAttribute('src');
        message.classList.remove('hidden');
        message.textContent = streamIndex ? 'Trying an alternative source…' : 'Connecting to the channel provider…';
        try {
            hls = await attachStream(tvPlayer, stream, () => { streamIndex++; loadCurrent(); });
            if (autoplay) await tvPlayer.play();
        } catch (error) {
            message.textContent = error.message === 'mixed-content' ? 'This HTTP source is blocked on HTTPS. Trying another…' : 'Trying another source…';
            streamIndex++; loadCurrent(autoplay);
        }
    };
    const bindTvButtons = () => document.querySelectorAll('[data-play-tv]:not([data-tv-bound])').forEach((button) => {
        button.dataset.tvBound = 'true';
        button.addEventListener('click', async () => {
            document.querySelector('[data-radio-audio]')?.pause();
            document.querySelector('[data-radio-dock]')?.classList.add('hidden');
            state = { slug: button.dataset.slug, title: button.dataset.title || 'Live television', streams: parseStreams(button) };
            streamIndex = 0; showDock(); await loadCurrent();
        });
    });
    bindTvButtons();
    document.addEventListener('livewire:navigating', () => tvDock.removeAttribute('data-inline'));
    document.addEventListener('livewire:navigated', () => { bindTvButtons(); requestAnimationFrame(syncTvPlacement); });
    window.addEventListener('scroll', syncTvPlacement, { passive: true });
    window.addEventListener('resize', syncTvPlacement, { passive: true });
    toggle?.addEventListener('click', () => tvPlayer.paused ? tvPlayer.play().catch(() => loadCurrent()) : tvPlayer.pause());
    tvDock.querySelector('[data-tv-close]')?.addEventListener('click', async () => {
        closed = true; hls?.destroy(); tvPlayer.pause(); tvPlayer.removeAttribute('src'); tvPlayer.load();
        if (document.pictureInPictureElement === tvPlayer) await document.exitPictureInPicture().catch(() => {});
        tvDock.classList.add('hidden'); tvDock.removeAttribute('data-expanded'); tvDock.removeAttribute('data-inline'); state = null;
    });
    tvDock.querySelector('[data-tv-pip]')?.addEventListener('click', async () => {
        if (!document.pictureInPictureEnabled || tvPlayer.readyState === 0) { message.classList.remove('hidden'); message.textContent = 'Picture-in-picture is not supported by this browser.'; return; }
        if (document.pictureInPictureElement === tvPlayer) await document.exitPictureInPicture(); else await tvPlayer.requestPictureInPicture();
    });
    expand?.addEventListener('click', () => {
        const expanded = tvDock.toggleAttribute('data-expanded');
        tvDock.removeAttribute('data-inline');
        expand.textContent = expanded ? 'Minimize' : 'Expand'; expand.setAttribute('aria-label', expanded ? 'Minimize player' : 'Expand player');
        if (!expanded) requestAnimationFrame(syncTvPlacement);
    });
    tvPlayer.addEventListener('playing', () => { message.classList.add('hidden'); setPlaying(true); });
    tvPlayer.addEventListener('pause', () => { if (!closed) setPlaying(false); });
    tvPlayer.addEventListener('waiting', () => { message.classList.remove('hidden'); message.textContent = 'Buffering live television…'; });
    tvPlayer.addEventListener('error', () => { if (!closed && state) { streamIndex++; loadCurrent(); } });
    localStorage.removeItem('wavexa-tv-player');
}

const bindPageControls = () => {
    document.querySelectorAll('[data-report-stream]:not([data-report-bound])').forEach((button) => {
        button.dataset.reportBound = 'true';
        button.addEventListener('click', async () => {
            const response = await fetch(`/streams/${button.dataset.reportStream}/report`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ reason: 'not_playing' }) });
            const message = document.querySelector('[data-report-message]');
            if (response.ok && message) { message.textContent = 'Thank you. This stream has been queued for review.'; message.classList.remove('hidden'); button.disabled = true; }
        });
    });
    document.querySelectorAll('[data-filter-toggle]:not([data-filter-bound])').forEach((button) => {
        button.dataset.filterBound = 'true';
        button.addEventListener('click', () => {
            const panel = button.parentElement.querySelector('[data-filter-panel]');
            const opening = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !opening); panel.classList.toggle('grid', opening);
            button.setAttribute('aria-expanded', String(opening));
            button.lastElementChild.textContent = opening ? '−' : '＋';
        });
    });
    document.querySelectorAll('form[data-filter-panel]:not([data-navigate-bound])').forEach((form) => {
        form.dataset.navigateBound = 'true';
        form.addEventListener('submit', (event) => {
            if (!window.Livewire?.navigate) return;
            event.preventDefault();
            const query = new URLSearchParams(new FormData(form)).toString();
            window.Livewire.navigate(`${form.action || window.location.pathname}?${query}`);
        });
    });
};
bindPageControls();
document.addEventListener('livewire:navigated', bindPageControls);
