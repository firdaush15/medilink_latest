/**
 * MEDILINK — SHARED SIDEBAR JS
 * Handles: dropdowns, mobile toggle, notification sound, badge updates
 *
 * Include once, before </body>, on every page that uses the sidebar.
 * Works with the shared sidebar.css and any role's Blade partial.
 */

(function () {
    'use strict';

    /* ── Config ─────────────────────────────────────────────────── */
    const SOUND_PREF_KEY = 'medilink_notification_sound';
    const POLL_INTERVAL = 30_000; // ms

    /* ── State ───────────────────────────────────────────────────── */
    let soundEnabled = localStorage.getItem(SOUND_PREF_KEY) !== 'false';
    const prevCounts = {};   // { elementId: lastCount }

    /* ────────────────────────────────────────────────────────────── *
     *  1. DROPDOWN MENUS
     * ────────────────────────────────────────────────────────────── */
    function initDropdowns() {
        document.querySelectorAll('.sb-dropdown').forEach(menu => {
            const trigger = menu.querySelector('.sb-drop-trigger');
            const content = menu.querySelector('.sb-drop-content');
            if (!trigger || !content) return;

            trigger.addEventListener('click', e => {
                e.preventDefault();
                const isOpen = trigger.classList.contains('open');

                // Close all other dropdowns
                document.querySelectorAll('.sb-drop-trigger.open').forEach(t => {
                    if (t !== trigger) {
                        t.classList.remove('open', 'active');
                        t.nextElementSibling?.classList.remove('open');
                    }
                });

                trigger.classList.toggle('open', !isOpen);
                trigger.classList.toggle('active', !isOpen);
                content.classList.toggle('open', !isOpen);
            });
        });

        // Close dropdowns when clicking outside the sidebar
        document.addEventListener('click', e => {
            if (!e.target.closest('.sidebar')) {
                document.querySelectorAll('.sb-drop-trigger.open').forEach(t => {
                    t.classList.remove('open', 'active');
                    t.nextElementSibling?.classList.remove('open');
                });
            }
        });
    }

    /* ────────────────────────────────────────────────────────────── *
     *  2. MOBILE SIDEBAR TOGGLE
     * ────────────────────────────────────────────────────────────── */
    function initMobileToggle() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sb-overlay');
        const toggles = document.querySelectorAll('.sb-toggle');

        function open() { sidebar?.classList.add('sidebar--open'); overlay?.classList.add('active'); }
        function close() { sidebar?.classList.remove('sidebar--open'); overlay?.classList.remove('active'); }

        toggles.forEach(btn => btn.addEventListener('click', () => {
            sidebar?.classList.contains('sidebar--open') ? close() : open();
        }));
        overlay?.addEventListener('click', close);
    }

    /* ────────────────────────────────────────────────────────────── *
     *  3. NOTIFICATION SOUND  (Web Audio API — no file needed)
     * ────────────────────────────────────────────────────────────── */
    let audioCtx = null;

    function getAudioContext() {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        return audioCtx;
    }

    /**
     * Play a soft two-tone chime.
     * Frequency and duration can be overridden for different alert severities.
     *   severity: 'normal' | 'warning' | 'critical'
     */
    function playNotificationSound(severity = 'normal') {
        if (!soundEnabled) return;
        try {
            const ctx = getAudioContext();
            const now = ctx.currentTime;
            const cfg = {
                normal: { f1: 880, f2: 1100, gain: 0.18, dur: 0.28 },
                warning: { f1: 660, f2: 990, gain: 0.22, dur: 0.35 },
                critical: { f1: 440, f2: 660, gain: 0.28, dur: 0.50 },
            }[severity] || { f1: 880, f2: 1100, gain: 0.18, dur: 0.28 };

            [cfg.f1, cfg.f2].forEach((freq, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, now + i * 0.12);
                gain.gain.setValueAtTime(cfg.gain, now + i * 0.12);
                gain.gain.exponentialRampToValueAtTime(0.001, now + i * 0.12 + cfg.dur);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now + i * 0.12);
                osc.stop(now + i * 0.12 + cfg.dur + 0.05);
            });
        } catch (err) {
            /* AudioContext blocked (e.g. no user gesture yet) — silently ignore */
        }
    }

    /* Unlock AudioContext on first user interaction (browser autoplay policy) */
    function unlockAudio() {
        document.addEventListener('click', () => {
            try { getAudioContext().resume(); } catch (_) { }
        }, { once: true });
    }

    /* ────────────────────────────────────────────────────────────── *
     *  4. BADGE UPDATES  — call this from your polling logic
     *
     *  Usage:
     *    MediLink.updateBadge('alertsNavBadge', 5, 'critical');
     *    MediLink.updateBadge('queueNavBadge',  3, 'normal');
     * ────────────────────────────────────────────────────────────── */
    function updateBadge(elementId, count, severity = 'normal') {
        const el = document.getElementById(elementId);
        if (!el) return;

        const stored = parseInt(sessionStorage.getItem('sb_prev_' + elementId) ?? '0');
        const prev = prevCounts[elementId] ?? stored;
        prevCounts[elementId] = count;
        sessionStorage.setItem('sb_prev_' + elementId, count);

        if (count > 0) {
            el.textContent = count;
            el.style.display = 'inline-flex';
            el.classList.toggle('sb-badge--pulse', severity === 'critical');
            // Only play sound when count genuinely increases
            if (count > prev) playNotificationSound(severity);
        } else {
            el.style.display = 'none';
        }
    }

    /* ────────────────────────────────────────────────────────────── *
     *  5. SOUND TOGGLE BUTTON  (optional — add to your settings page)
     *
     *  <button id="sb-sound-toggle" class="..." onclick="MediLink.toggleSound()">
     *      Toggle notification sounds
     *  </button>
     * ────────────────────────────────────────────────────────────── */
    function toggleSound() {
        soundEnabled = !soundEnabled;
        localStorage.setItem(SOUND_PREF_KEY, soundEnabled ? 'true' : 'false');
        document.querySelectorAll('[data-sb-sound-toggle]').forEach(el => {
            el.textContent = soundEnabled ? '🔔 Sounds on' : '🔕 Sounds off';
            el.setAttribute('aria-pressed', String(soundEnabled));
        });
        if (soundEnabled) playNotificationSound('normal');
        return soundEnabled;
    }

    /* ────────────────────────────────────────────────────────────── *
     *  6. GENERIC BADGE POLLER
     *
     *  Registers an endpoint to poll every POLL_INTERVAL ms.
     *  The endpoint must return JSON like:
     *    { count: 5, severity: 'critical' }
     *
     *  Usage:
     *    MediLink.registerPoll('alertsNavBadge', '/nurse/alerts/unread-count');
     *    MediLink.registerPoll('queueNavBadge',  '/nurse/queue/count', 'warning');
     * ────────────────────────────────────────────────────────────── */
    const pollers = [];

    function registerPoll(elementId, url, defaultSeverity = 'normal') {
        pollers.push({ elementId, url, defaultSeverity });
    }

    async function runPollers() {
        await Promise.allSettled(pollers.map(async ({ elementId, url, defaultSeverity }) => {
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const data = await res.json();
                // Support { count } or { count, severity }
                updateBadge(elementId, data.count ?? 0, data.severity ?? defaultSeverity);
            } catch (_) { /* network error — keep last badge */ }
        }));
    }

    function startPolling() {
        if (pollers.length === 0) return;
        runPollers();
        setInterval(runPollers, POLL_INTERVAL);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) runPollers();
        });
    }

    /* ────────────────────────────────────────────────────────────── *
     *  INIT
     * ────────────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        initDropdowns();
        initMobileToggle();
        unlockAudio();
        startPolling();
    });

    /* ── Public API ─────────────────────────────────────────────── */
    window.MediLink = {
        updateBadge,
        toggleSound,
        playSound: playNotificationSound,
        registerPoll,
        get soundEnabled() { return soundEnabled; },
    };

})();