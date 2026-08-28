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
            state = { title: button.dataset.title || 'Live radio', slug: button.dataset.slug, art: button.dataset.art, streams: parseStreams(button) };
            streamIndex = 0; showState(); localStorage.setItem('wavexa-player', JSON.stringify(state)); await loadCurrent();
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
    try { state = JSON.parse(localStorage.getItem('wavexa-player')); if (state?.streams?.length) { showState(); status.textContent = 'Ready to resume'; setPlaying(false); } } catch { localStorage.removeItem('wavexa-player'); }
}

const initializeTvPlayer = async () => {
    const player = document.querySelector('[data-tv-player]:not([data-player-bound])'); if (!player) return;
    player.dataset.playerBound = 'true';
    const message = document.querySelector('[data-tv-message]'); const streams = parseStreams(player);
    let index = 0; let hls = null;
    const load = async () => {
        if (!streams[index]) { message.classList.remove('hidden'); message.textContent = 'Every available source is offline or unavailable in your location.'; return; }
        hls?.destroy();
        try { hls = await attachStream(player, streams[index], () => { index++; load(); }); }
        catch (error) { message.textContent = error.message === 'mixed-content' ? 'This source is blocked on HTTPS. Trying another…' : 'Trying another source…'; index++; load(); }
    };
    await load();
    player.addEventListener('playing', () => message?.classList.add('hidden'));
    player.addEventListener('waiting', () => { message.classList.remove('hidden'); message.textContent = 'Buffering live television…'; });
};
initializeTvPlayer();

const bindPageControls = () => {
    initializeTvPlayer();
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
