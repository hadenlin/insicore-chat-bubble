(function () {
    'use strict';

    window.addEventListener('message', function (event) {
        if (!event.data || event.data.type !== 'pcb_update') return;
        const data = event.data.payload;
        const container = document.getElementById('pcb-container');
        if (!container) return;

        const s  = data.settings      || {};
        const sm = data.settingsMobile || {};

        /* ── Responsive CSS via <style> tag ─────────────────── */
        let styleEl = document.getElementById('pcb-live-styles');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'pcb-live-styles';
            document.head.appendChild(styleEl);
        }

        const br_d    = s.border_radius  ? s.border_radius  + 'px' : '50%';
        const br_m_v  = sm.border_radius !== undefined && sm.border_radius !== '' ? sm.border_radius : s.border_radius;
        const br_m    = br_m_v ? br_m_v + 'px' : '50%';
        const bot_m   = sm.offset_bottom  !== '' && sm.offset_bottom  !== undefined ? sm.offset_bottom  : (s.offset_bottom  || 30);
        const side_m  = sm.offset_side    !== '' && sm.offset_side    !== undefined ? sm.offset_side    : (s.offset_side    || 30);
        const size_m  = sm.button_size    !== '' && sm.button_size    !== undefined ? sm.button_size    : (s.button_size    || 60);
        const corner_m = sm.corner || s.corner || 'right';
        const mobilePos = corner_m === 'left'
            ? 'left: var(--pcb-side) !important; right: auto !important;'
            : 'right: var(--pcb-side) !important; left: auto !important;';

        const hoverColor = s.bubble_hover_color || s.bubble_color || '#1a202c';
        const themeCSS   = pcbThemeCss(s.theme_preset || '');

        styleEl.textContent = `
            :root {
                --pcb-primary-color: ${s.bubble_color || '#1a202c'};
                --pcb-hover-color:   ${hoverColor};
            }
            #pcb-container {
                --pcb-bottom: ${s.offset_bottom || 30}px;
                --pcb-side:   ${s.offset_side   || 30}px;
                --pcb-radius: ${br_d};
                --pcb-size:   ${s.button_size   || 60}px;
            }
            @media (max-width: 768px) {
                #pcb-container {
                    --pcb-bottom: ${bot_m}px;
                    --pcb-side:   ${side_m}px;
                    --pcb-radius: ${br_m};
                    --pcb-size:   ${size_m}px;
                    ${mobilePos}
                }
            }
            ${themeCSS}
            ${s.custom_css || ''}
        `;

        /* ── Position class (desktop corner) ─────────────────── */
        container.className = 'pcb-container pcb-pos-' + (s.corner || 'right');

        /* ── Main wrap (animation target) ────────────────────── */
        const mainWrap = document.getElementById('pcb-main-wrap');
        if (mainWrap) {
            mainWrap.className = 'pcb-main-wrap';
            if (s.animation_type && s.animation_type !== 'none') {
                mainWrap.classList.add('pcb-anim-' + s.animation_type);
            }
        }

        /* ── Main button icon + badge ────────────────────────── */
        const mainBtn = document.getElementById('pcb-main-bubble');
        if (mainBtn) {
            const defaultSvg = `<svg class="pcb-chat-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
            const badgeHtml = s.badge_enabled
                ? `<span class="pcb-badge" style="background:${s.badge_bg_color || '#ef4444'};color:${s.badge_text_color || '#fff'}">${esc(s.badge_text || '1')}</span>`
                : '';
            mainBtn.innerHTML = (
                s.custom_svg
                    ? `<img src="${s.custom_svg}" class="pcb-chat-icon" alt="">`
                    : defaultSvg
            ) + badgeHtml;
            mainBtn.className = 'pcb-main-bubble';
        }

        /* ── Tooltip merged into the greeting/message bubble ─── */
        container.classList.add('pcb-no-tip');

        /* ── Message bubble preview ───────────────────────────── */
        let mb = container.querySelector('#pcb-msgbubble');
        if (s.msgbubble_enabled && s.msgbubble_text) {
            if (!mb) {
                mb = document.createElement('div');
                mb.id = 'pcb-msgbubble';
                mb.className = 'pcb-msgbubble pcb-mb-visible';
                mb.innerHTML = `<button class="pcb-msgbubble-close">×</button><div class="pcb-msgbubble-text"></div>`;
                container.appendChild(mb);
            }
            mb.style.setProperty('--pcb-mb-bg', s.msgbubble_bg_color || '#fff');
            mb.style.setProperty('--pcb-mb-color', s.msgbubble_text_color || '#1a202c');
            mb.classList.add('pcb-mb-visible');
            mb.querySelector('.pcb-msgbubble-text').textContent = s.msgbubble_text;
        } else if (mb) {
            mb.remove();
        }

        /* ── Channels ─────────────────────────────────────────── */
        const menu = document.getElementById('pcb-menu');
        if (menu) {
            menu.innerHTML = '';
            (data.channels || []).forEach((ch, i) => {
                const a = document.createElement('a');
                a.href      = '#';
                a.className = 'pcb-menu-item' + (s.show_labels ? ' pcb-has-label' : '');
                a.style.cssText = `--pcb-index:${i};--pcb-ch-color:${ch.color};background:${ch.color};--pcb-ch-hover:${ch.hover};`;
                a.innerHTML = `<span class="pcb-icon">${ch.icon}</span>` +
                    (s.show_labels && ch.label ? `<span class="pcb-ch-label">${esc(ch.label)}</span>` : '');
                menu.appendChild(a);
            });
        }

        /* ── Force expand ─────────────────────────────────────── */
        if (data.forceExpand) {
            container.classList.add('active', 'force-active');
        } else {
            container.classList.remove('active', 'force-active');
        }
    });

    function esc(s) {
        return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Keep in sync with includes/admin/themes.php definitions.
    function pcbThemeCss(slug) {
        const map = {
            modern: `.pcb-main-bubble { box-shadow: 0 20px 40px -10px rgba(0,0,0,0.35), 0 8px 16px -8px rgba(0,0,0,0.25); } .pcb-menu-item { box-shadow: 0 12px 28px -8px rgba(0,0,0,0.3); }`,
            glass:  `.pcb-main-bubble { backdrop-filter: blur(14px); background: rgba(255,255,255,0.18) !important; border: 1px solid rgba(255,255,255,0.4); color: #fff !important; } .pcb-menu-item { backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.35); }`,
            dark:   `.pcb-main-bubble { background: #111827 !important; box-shadow: 0 10px 30px rgba(0,0,0,0.6); } .pcb-tooltip { background: #1f2937 !important; color: #f9fafb !important; border: none; }`,
            soft:   `.pcb-main-bubble { box-shadow: 0 16px 30px -10px rgba(99,102,241,0.4); } .pcb-menu-item { box-shadow: 0 10px 20px -8px rgba(99,102,241,0.35); } .pcb-tooltip { background: #fef3c7 !important; color: #78350f !important; border: none; }`,
            neon:   `.pcb-main-bubble { box-shadow: 0 0 18px var(--pcb-primary-color), 0 0 32px var(--pcb-primary-color); } .pcb-menu-item { box-shadow: 0 0 14px var(--pcb-ch-color, var(--pcb-primary-color)); }`,
            squared:`.pcb-main-bubble, .pcb-menu-item, .pcb-menu-item.pcb-has-label { border-radius: 10px !important; } .pcb-tooltip { border-radius: 6px !important; }`,
            gradient:`.pcb-main-bubble { background: linear-gradient(135deg, var(--pcb-primary-color), var(--pcb-hover-color, #9333ea)) !important; }`,
            flat:   `.pcb-main-bubble, .pcb-menu-item { box-shadow: none !important; } .pcb-tooltip { box-shadow: none !important; border: 1px solid rgba(0,0,0,0.1); }`,
        };
        return map[slug] || '';
    }
})();
