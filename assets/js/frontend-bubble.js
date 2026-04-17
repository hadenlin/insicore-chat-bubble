(function () {
    'use strict';

    const cfg = window.pcbFrontendConfig || {};
    const ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
    const i18n = cfg.i18n || {};

    function device() {
        return window.innerWidth <= 768 ? 'mobile' : 'desktop';
    }

    // ── Analytics: lightweight click tracker ─────────────────
    function track(event_type, extra) {
        const a = cfg.analytics || {};
        if (!a.enabled) return;
        const data = new FormData();
        data.append('action', 'pcb_track_event');
        data.append('nonce', a.nonce || '');
        data.append('event_type', event_type);
        data.append('page_url', location.href);
        data.append('device', device());
        Object.entries(extra || {}).forEach(([k, v]) => data.append(k, v));
        // Use sendBeacon when available so it fires even on outbound navigation.
        if (navigator.sendBeacon) {
            navigator.sendBeacon(ajaxUrl, data);
        } else {
            fetch(ajaxUrl, { method: 'POST', body: data, keepalive: true });
        }
    }

    // ── Bubble open/close behaviour ──────────────────────────
    function initBubble(container, bubble) {
        if (!bubble || !container) return;
        bubble.addEventListener('click', function (e) {
            e.preventDefault();
            const opened = container.classList.toggle('active');
            bubble.classList.add('pcb-clicked');
            setTimeout(() => bubble.classList.remove('pcb-clicked'), 200);
            // Once opened, hide the one-off message bubble / badge.
            if (opened) {
                container.classList.add('pcb-has-opened');
                track('open');
            }
        });
    }

    // ── Channel click analytics ──────────────────────────────
    function bindChannelTracking(container) {
        container.querySelectorAll('.pcb-menu-item').forEach(a => {
            a.addEventListener('click', () => {
                track('click', {
                    channel_type:  a.dataset.pcbChannel || '',
                    channel_label: a.dataset.pcbLabel   || '',
                });
            });
        });
    }

    // ── Message bubble popup (one-off) ───────────────────────
    function initMsgBubble(container) {
        const mb = container.querySelector('#pcb-msgbubble');
        const mbCfg = (cfg.msgBubble || {});
        if (!mb || !mbCfg.enabled) return;
        const closeBtn = mb.querySelector('.pcb-msgbubble-close');
        const show = () => {
            if (container.classList.contains('active')) return;
            mb.classList.add('pcb-mb-visible');
            if (mbCfg.autohide > 0) {
                setTimeout(() => mb.classList.remove('pcb-mb-visible'), mbCfg.autohide * 1000);
            }
        };
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                mb.classList.remove('pcb-mb-visible');
                mb.classList.add('pcb-mb-dismissed');
            });
        }
        // Clicking the message bubble opens the menu.
        mb.addEventListener('click', () => {
            const btn = document.getElementById('pcb-main-bubble');
            if (btn) btn.click();
        });
        setTimeout(show, (mbCfg.delay || 0) * 1000);
    }

    // ── Triggers: reveal bubble once any trigger fires ───────
    function initTriggers(container) {
        const t = cfg.triggers || {};
        if (!container.classList.contains('pcb-hidden-init')) return;

        const reveal = () => {
            container.classList.remove('pcb-hidden-init');
            container.classList.add('pcb-triggered');
        };

        if (t.scroll && t.scroll.enabled) {
            const onScroll = () => {
                const doc = document.documentElement;
                const scrolled = (doc.scrollTop + window.innerHeight) / doc.scrollHeight * 100;
                if (scrolled >= (t.scroll.percent || 30)) {
                    reveal();
                    window.removeEventListener('scroll', onScroll);
                }
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        if (t.time && t.time.enabled) {
            setTimeout(reveal, (t.time.seconds || 10) * 1000);
        }

        if (t.exit && t.exit.enabled) {
            const onExit = (e) => {
                if (e.clientY <= 10) {
                    reveal();
                    document.removeEventListener('mouseleave', onExit);
                }
            };
            document.addEventListener('mouseleave', onExit);
        }

        // If no trigger fires within a safety window, show after 30s anyway
        // so the bubble never stays hidden forever when configs are broken.
        setTimeout(reveal, 30000);
    }

    // ── Multi-agent round-robin (localStorage-based) ─────────
    function initAgentRotation(container) {
        container.querySelectorAll('.pcb-menu-item[data-agents]').forEach(link => {
            let agents;
            try { agents = JSON.parse(link.dataset.agents); } catch (e) { return; }
            if (!Array.isArray(agents) || agents.length < 2) return;

            const storageKey = 'pcb_rr_' + (link.dataset.pcbChannel || '') + '_' + (link.dataset.agentGroup || '0');

            link.addEventListener('click', function (e) {
                let idx = parseInt(localStorage.getItem(storageKey) || '0', 10);
                if (isNaN(idx) || idx >= agents.length) idx = 0;
                const agent = agents[idx];
                localStorage.setItem(storageKey, String((idx + 1) % agents.length));
                if (agent && agent.url && agent.url !== '#') {
                    e.preventDefault();
                    window.open(agent.url, '_blank', 'noopener,noreferrer');
                }
            });
        });
    }

    // ── Built-in contact form popup ──────────────────────────
    function initForms(container) {
        container.querySelectorAll('.pcb-form-trigger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const idx    = btn.dataset.formIdx;
                const modal  = document.getElementById('pcb-form-modal-' + idx);
                if (modal) {
                    modal.classList.add('pcb-show');
                    modal.setAttribute('aria-hidden', 'false');
                }
                track('click', {
                    channel_type:  'form',
                    channel_label: btn.dataset.pcbLabel || 'Contact Form',
                });
            });
        });

        document.querySelectorAll('.pcb-form-modal').forEach(modal => {
            const close = () => {
                modal.classList.remove('pcb-show');
                modal.setAttribute('aria-hidden', 'true');
            };
            modal.querySelector('.pcb-form-close')?.addEventListener('click', close);
            modal.querySelector('.pcb-form-overlay')?.addEventListener('click', close);

            const form   = modal.querySelector('.pcb-contact-form');
            const status = modal.querySelector('.pcb-form-status');
            if (!form) return;
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                status.className = 'pcb-form-status';
                status.textContent = '';
                const fd = new FormData(form);
                try {
                    const res = await fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
                    const json = await res.json();
                    if (json.success) {
                        status.className = 'pcb-form-status pcb-ok';
                        status.textContent = json.data.message || i18n.thanks || 'Thanks!';
                        form.reset();
                        track('form_submit', { channel_type: 'form' });
                        setTimeout(close, 2500);
                    } else {
                        status.className = 'pcb-form-status pcb-err';
                        status.textContent = (json.data && json.data.message) || i18n.error || 'Something went wrong.';
                    }
                } catch (err) {
                    status.className = 'pcb-form-status pcb-err';
                    status.textContent = i18n.networkError || 'Network error. Please try again.';
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('pcb-container');
        const bubble    = document.getElementById('pcb-main-bubble');
        if (!container) return;

        initBubble(container, bubble);
        bindChannelTracking(container);
        initMsgBubble(container);
        initTriggers(container);
        initForms(container);
        initAgentRotation(container);

        // Click outside to close the fly-out menu.
        document.addEventListener('click', (e) => {
            document.querySelectorAll('.pcb-container.active:not(.force-active)').forEach(c => {
                if (!c.contains(e.target)) c.classList.remove('active');
            });
        });

        // Fire an impression event once per page load.
        track('impression');
    });
})();
