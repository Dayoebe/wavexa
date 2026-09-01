const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
let playbackMessages = {};
try { playbackMessages = JSON.parse(document.querySelector('#wavexa-playback-messages')?.textContent || '{}'); } catch { playbackMessages = {}; }
const playbackMessage = (key, fallback) => playbackMessages[key] || fallback;

const parseStreams = (element) => {
    try {
        const streams = JSON.parse(element?.dataset.streams || '[]');
        return streams.length ? streams : element?.dataset.stream ? [{ url: element.dataset.stream }] : [];
    } catch {
        return element?.dataset.stream ? [{ url: element.dataset.stream }] : [];
    }
};

const attachStream = async (media, stream, onFatal) => {
    if (stream.id) {
        const response = await fetch(`/api/v1/streams/${stream.id}/playback-policy`, { headers: { Accept: 'application/json' } });
        if (response.ok) {
            const policy = await response.json();
            if (!policy.allowed) { const error = new Error(policy.reason || 'policy'); error.policyMessage = policy.message; throw error; }
        }
    }
    if (window.location.protocol === 'https:' && stream.url.startsWith('http:')) throw new Error('mixed-content');
    const isHls = stream.format === 'hls' || stream.url.toLowerCase().includes('.m3u8');
    if (!isHls || media.canPlayType('application/vnd.apple.mpegurl')) { media.src = stream.url; return null; }
    const { default: Hls } = await import('hls.js');
    if (!Hls.isSupported()) throw new Error('unsupported');
    const hls = new Hls({
        enableWorker: true,
        lowLatencyMode: false,
        capLevelToPlayerSize: true,
        startFragPrefetch: true,
        liveSyncDurationCount: 5,
        liveMaxLatencyDurationCount: 12,
        maxBufferLength: 60,
        maxMaxBufferLength: 120,
        backBufferLength: 30,
        manifestLoadingTimeOut: 15000,
        fragLoadingTimeOut: 25000,
    });
    let networkRecoveries = 0;
    let mediaRecoveries = 0;
    hls.loadSource(stream.url); hls.attachMedia(media);
    hls.on(Hls.Events.ERROR, (_event, data) => {
        if (!data.fatal) return;
        if (data.type === Hls.ErrorTypes.NETWORK_ERROR && networkRecoveries < 2) {
            networkRecoveries++;
            setTimeout(() => hls.startLoad(), 750 * networkRecoveries);
            return;
        }
        if (data.type === Hls.ErrorTypes.MEDIA_ERROR && mediaRecoveries < 2) {
            mediaRecoveries++;
            hls.recoverMediaError();
            return;
        }
        onFatal();
    });
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
        if (!stream) { status.textContent = playbackMessage('offline', 'Every available source is currently unavailable.'); setPlaying(false); return; }
        hls?.destroy(); radioAudio.removeAttribute('src');
        status.textContent = streamIndex ? 'Trying an alternative source…' : playbackMessage('connecting', 'Connecting to the provider…');
        try { hls = await attachStream(radioAudio, stream, () => { streamIndex++; loadCurrent(); }); if (autoplay) await radioAudio.play(); }
        catch (error) {
            if (error.name === 'NotAllowedError') { status.textContent = 'Ready to play. Press play to start listening.'; setPlaying(false); return; }
            status.textContent = error.policyMessage || (error.message === 'mixed-content' ? playbackMessage('mixed_content', 'HTTP stream blocked. Trying another source…') : 'Trying another source…'); streamIndex++; loadCurrent(autoplay);
        }
    };
    const bindRadioButtons = () => document.querySelectorAll('[data-play-station]:not([data-player-bound])').forEach((button) => {
        button.dataset.playerBound = 'true';
        button.addEventListener('click', async () => {
            if (state?.slug === button.dataset.slug && !radioAudio.paused) { showState(); return; }
            document.querySelector('[data-tv-close]')?.click();
            state = { title: button.dataset.title || 'Live radio', slug: button.dataset.slug, art: button.dataset.art, streams: parseStreams(button) };
            streamIndex = 0; showState(); await loadCurrent();
            fetch(`/radio/${encodeURIComponent(state.slug)}/play`, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken } }).catch(() => {});
        });
        if (button.hasAttribute('data-autoplay') && !button.hasAttribute('data-autoplay-started')) {
            button.setAttribute('data-autoplay-started', '');
            queueMicrotask(() => button.click());
        }
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
    radioAudio.addEventListener('waiting', () => { status.textContent = playbackMessage('buffering', 'Buffering live audio…'); });
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
    let state = null; let streamIndex = 0; let hls = null; let closed = true; let playbackTimer = null; let stallTimer = null;

    const clearPlaybackTimers = () => {
        clearTimeout(playbackTimer);
        clearTimeout(stallTimer);
        playbackTimer = null;
        stallTimer = null;
    };

    const watchPlaybackStart = () => {
        clearTimeout(playbackTimer);
        playbackTimer = setTimeout(() => {
            if (closed || !state || !tvPlayer.paused && tvPlayer.readyState >= HTMLMediaElement.HAVE_FUTURE_DATA) return;
            if (state.streams[streamIndex + 1]) {
                streamIndex++;
                message.textContent = 'The first source is too slow. Trying an alternative…';
                loadCurrent();
                return;
            }
            tvPlayer.pause();
            message.classList.remove('hidden');
            message.textContent = 'This provider is responding too slowly. Press play to retry.';
            setPlaying(false);
        }, 25000);
    };

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
        clearPlaybackTimers();
        const stream = state?.streams?.[streamIndex];
        if (!stream) { message.classList.remove('hidden'); message.textContent = playbackMessage('offline', 'Every available source is offline or unavailable in your location.'); setPlaying(false); return; }
        hls?.destroy(); tvPlayer.removeAttribute('src');
        message.classList.remove('hidden');
        message.textContent = streamIndex ? 'Trying an alternative source…' : playbackMessage('connecting', 'Connecting to the channel provider…');
        try {
            hls = await attachStream(tvPlayer, stream, () => { streamIndex++; loadCurrent(); });
            if (autoplay) { watchPlaybackStart(); await tvPlayer.play(); }
        } catch (error) {
            if (error.name === 'NotAllowedError') { clearPlaybackTimers(); message.textContent = 'Ready to play. Press play to start watching.'; setPlaying(false); return; }
            message.textContent = error.policyMessage || (error.message === 'mixed-content' ? playbackMessage('mixed_content', 'This HTTP source is blocked on HTTPS. Trying another…') : 'Trying another source…');
            streamIndex++; loadCurrent(autoplay);
        }
    };
    const bindTvButtons = () => document.querySelectorAll('[data-play-tv]:not([data-tv-bound])').forEach((button) => {
        button.dataset.tvBound = 'true';
        button.addEventListener('click', async () => {
            if (state?.slug === button.dataset.slug && !tvPlayer.paused) { showDock(); return; }
            document.querySelector('[data-radio-audio]')?.pause();
            document.querySelector('[data-radio-dock]')?.classList.add('hidden');
            state = { slug: button.dataset.slug, title: button.dataset.title || 'Live television', streams: parseStreams(button) };
            streamIndex = 0; showDock(); await loadCurrent();
        });
        if (button.hasAttribute('data-autoplay') && !button.hasAttribute('data-autoplay-started')) {
            button.setAttribute('data-autoplay-started', '');
            queueMicrotask(() => button.click());
        }
    });
    bindTvButtons();
    document.addEventListener('livewire:navigating', () => tvDock.removeAttribute('data-inline'));
    document.addEventListener('livewire:navigated', () => { bindTvButtons(); requestAnimationFrame(syncTvPlacement); });
    window.addEventListener('scroll', syncTvPlacement, { passive: true });
    window.addEventListener('resize', syncTvPlacement, { passive: true });
    toggle?.addEventListener('click', () => {
        if (tvPlayer.paused) { watchPlaybackStart(); tvPlayer.play().catch(() => loadCurrent()); } else tvPlayer.pause();
    });
    tvDock.querySelector('[data-tv-close]')?.addEventListener('click', async () => {
        closed = true; clearPlaybackTimers(); hls?.destroy(); tvPlayer.pause(); tvPlayer.removeAttribute('src'); tvPlayer.load();
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
    tvPlayer.addEventListener('playing', () => { clearPlaybackTimers(); message.classList.add('hidden'); setPlaying(true); });
    tvPlayer.addEventListener('pause', () => { clearPlaybackTimers(); if (!closed) setPlaying(false); });
    tvPlayer.addEventListener('waiting', () => {
        message.classList.remove('hidden'); message.textContent = playbackMessage('buffering', 'Stabilizing the live stream…');
        clearTimeout(stallTimer);
        stallTimer = setTimeout(() => {
            if (closed || !state || tvPlayer.readyState >= HTMLMediaElement.HAVE_FUTURE_DATA) return;
            hls?.startLoad();
            tvPlayer.play().catch(() => {});
            message.textContent = 'Recovering the provider connection…';
        }, 15000);
    });
    tvPlayer.addEventListener('error', () => { if (!closed && state) { streamIndex++; loadCurrent(); } });
    localStorage.removeItem('wavexa-tv-player');
}

const podcastDock = document.querySelector('[data-podcast-dock]');
const podcastAudio = podcastDock?.querySelector('[data-podcast-audio]');
const podcastVideo = podcastDock?.querySelector('[data-podcast-video]');
if (podcastDock && podcastAudio && podcastVideo) {
    const videoWrap = podcastDock.querySelector('[data-podcast-video-wrap]');
    const title = podcastDock.querySelector('[data-podcast-title]');
    const show = podcastDock.querySelector('[data-podcast-show]');
    const kind = podcastDock.querySelector('[data-podcast-kind]');
    const artwork = podcastDock.querySelector('[data-podcast-art]');
    const toggle = podcastDock.querySelector('[data-podcast-toggle]');
    const playIcon = podcastDock.querySelector('[data-podcast-play]');
    const pauseIcon = podcastDock.querySelector('[data-podcast-pause]');
    let currentMedia = podcastAudio;
    let state = null;

    const setPlaying = (playing) => {
        playIcon?.classList.toggle('hidden', playing);
        pauseIcon?.classList.toggle('hidden', !playing);
        toggle?.setAttribute('aria-label', playing ? 'Pause podcast' : 'Resume podcast');
    };
    const stopOtherPlayers = () => {
        radioAudio?.pause();
        radioDock?.classList.add('hidden');
        tvDock?.querySelector('[data-tv-close]')?.click();
    };
    const loadPodcast = async () => {
        const isVideo = ['mp4', 'webm'].includes(state.format);
        podcastAudio.pause(); podcastVideo.pause();
        podcastAudio.removeAttribute('src'); podcastVideo.removeAttribute('src');
        currentMedia = isVideo ? podcastVideo : podcastAudio;
        videoWrap?.classList.toggle('hidden', !isVideo);
        currentMedia.src = state.url;
        title.textContent = state.title;
        show.textContent = state.show;
        kind.textContent = isVideo ? 'Video podcast' : 'Audio podcast';
        artwork.textContent = state.art ? '' : (state.show?.charAt(0) || 'P').toUpperCase();
        artwork.style.backgroundImage = state.art ? `url("${state.art.replaceAll('"', '%22')}")` : '';
        artwork.style.backgroundSize = 'cover'; artwork.style.backgroundPosition = 'center';
        podcastDock.classList.remove('hidden');
        try { await currentMedia.play(); } catch { setPlaying(false); }
    };
    const bindPodcastButtons = () => document.querySelectorAll('[data-play-podcast]:not([data-podcast-bound])').forEach((button) => {
        button.dataset.podcastBound = 'true';
        button.addEventListener('click', async () => {
            stopOtherPlayers();
            if (state?.url === button.dataset.url) {
                podcastDock.classList.remove('hidden');
                if (currentMedia.paused) await currentMedia.play().catch(() => {});
                return;
            }
            state = { url: button.dataset.url, format: button.dataset.format, title: button.dataset.title, show: button.dataset.show, art: button.dataset.art };
            await loadPodcast();
        });
    });
    bindPodcastButtons();
    document.addEventListener('livewire:navigated', bindPodcastButtons);
    toggle?.addEventListener('click', () => currentMedia.paused ? currentMedia.play().catch(() => {}) : currentMedia.pause());
    podcastDock.querySelector('[data-podcast-close]')?.addEventListener('click', () => {
        podcastAudio.pause(); podcastVideo.pause();
        podcastAudio.removeAttribute('src'); podcastVideo.removeAttribute('src');
        podcastAudio.load(); podcastVideo.load();
        podcastDock.classList.add('hidden'); state = null; setPlaying(false);
    });
    [podcastAudio, podcastVideo].forEach((media) => {
        media.addEventListener('playing', () => { if (media === currentMedia) setPlaying(true); });
        media.addEventListener('pause', () => { if (media === currentMedia) setPlaying(false); });
        media.addEventListener('ended', () => { if (media === currentMedia) setPlaying(false); });
    });
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
