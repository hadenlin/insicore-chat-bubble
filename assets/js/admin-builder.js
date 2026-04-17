(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {

        /* ── DOM references ─────────────────────────────────── */
        const stack             = document.getElementById('pcb-active-stack');
        const hiddenDataInput   = document.getElementById('pcb_channels_data_hidden');
        const settingsForm      = document.getElementById('pcb-settings-main-form');
        const previewFrame      = document.getElementById('pcb-preview-frame');
        const forceExpandToggle = document.getElementById('pcb-force-expand');
        const saveTrigger       = document.getElementById('pcb-save-trigger');
        const rulesInput        = document.getElementById('pcb-visibility-rules-input');
        const repeater          = document.getElementById('pcb-rule-repeater');
        const addRuleBtn        = document.getElementById('pcb-add-rule-trigger');
        const toastEl           = document.getElementById('pcb-toast');

        if (!stack || !hiddenDataInput || !settingsForm) return;

        let channels = [];
        try { channels = JSON.parse(hiddenDataInput.value || '[]'); } catch (e) { channels = []; }

        /* ── Toast ──────────────────────────────────────────── */
        function showToast(msg, type = '') {
            if (!toastEl) return;
            toastEl.textContent = msg;
            toastEl.className = 'pcb-toast' + (type ? ' ' + type : '');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => toastEl.classList.add('show'));
            });
            setTimeout(() => toastEl.classList.remove('show'), 2800);
        }

        /* ── Tabs ───────────────────────────────────────────── */
        document.querySelectorAll('.pcb-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.tab;
                document.querySelectorAll('.pcb-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.pcb-tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                const panel = document.querySelector(`.pcb-tab-panel[data-panel="${tab}"]`);
                if (panel) panel.classList.add('active');
            });
        });

        /* ── Responsive sub-toggle ──────────────────────────── */
        let _deviceSyncing = false;

        document.querySelectorAll('.pcb-resp-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const section = btn.closest('.pcb-resp-section');
                if (!section) return;
                section.querySelectorAll('.pcb-resp-btn').forEach(b => b.classList.remove('active'));
                section.querySelectorAll('.pcb-resp-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                const panel = section.querySelector(`.pcb-resp-panel[data-resp-panel="${btn.dataset.resp}"]`);
                if (panel) panel.classList.add('active');
                if (!_deviceSyncing) {
                    _deviceSyncing = true;
                    const deviceBtn = document.querySelector(`.pcb-device-btn[data-device="${btn.dataset.resp}"]`);
                    if (deviceBtn) deviceBtn.click();
                    _deviceSyncing = false;
                }
            });
        });

        /* ── Device Preview Toggle ──────────────────────────── */
        document.querySelectorAll('.pcb-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.pcb-device-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (!previewFrame) return;
                if (btn.dataset.device === 'mobile') {
                    previewFrame.classList.remove('desktop-mode');
                    previewFrame.classList.add('mobile-mode');
                } else {
                    previewFrame.classList.remove('mobile-mode');
                    previewFrame.classList.add('desktop-mode');
                }
                if (!_deviceSyncing) {
                    _deviceSyncing = true;
                    document.querySelectorAll(`.pcb-resp-btn[data-resp="${btn.dataset.device}"]`).forEach(respBtn => respBtn.click());
                    _deviceSyncing = false;
                }
            });
        });
        if (previewFrame) previewFrame.classList.add('desktop-mode');

        /* ── Upload button (main icon) ──────────────────────── */
        document.querySelectorAll('.pcb-upload-button').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!window.wp || !wp.media) return;
                const targetId = btn.dataset.target;
                const targetInput = document.getElementById(targetId);
                const uploader = wp.media({ title: 'Select Icon', button: { text: 'Use this icon' }, multiple: false });
                uploader.on('select', () => {
                    const att = uploader.state().get('selection').first().toJSON();
                    if (targetInput) { targetInput.value = att.url; sync(); }
                });
                uploader.open();
            });
        });

        /* ── Color hex label updater ────────────────────────── */
        settingsForm.querySelectorAll('.pcb-color-row').forEach(row => {
            const inp = row.querySelector('input[type=color]');
            const hex = row.querySelector('.pcb-color-hex');
            if (inp && hex) {
                inp.addEventListener('input', (e) => {
                    hex.textContent = e.target.value.toUpperCase();
                });
            }
        });

        /* ── Sync State ─────────────────────────────────────── */
        function sync() {
            hiddenDataInput.value = JSON.stringify(channels);
            if (!previewFrame || !previewFrame.contentWindow) return;

            const get = (name) => {
                const el = settingsForm.querySelector(`[name="pcb_settings[${name}]"]`);
                return el ? (el.type === 'checkbox' ? el.checked : el.value) : '';
            };

            const payload = {
                type: 'pcb_update',
                payload: {
                    settings: {
                        bubble_color:       get('bubble_color'),
                        bubble_hover_color: get('bubble_hover_color'),
                        button_size:    get('button_size'),
                        corner:         get('corner'),
                        offset_bottom:  get('offset_bottom'),
                        offset_side:    get('offset_side'),
                        border_radius:  get('border_radius'),
                        custom_svg:     get('custom_svg'),
                        animation_type: get('animation_type'),
                        show_labels:    get('show_labels'),
                        badge_enabled:    get('badge_enabled'),
                        badge_text:       get('badge_text'),
                        badge_bg_color:   get('badge_bg_color'),
                        badge_text_color: get('badge_text_color'),
                        msgbubble_enabled:    get('msgbubble_enabled'),
                        msgbubble_text:       get('msgbubble_text'),
                        msgbubble_bg_color:   get('msgbubble_bg_color'),
                        msgbubble_text_color: get('msgbubble_text_color'),
                        theme_preset:  get('theme_preset'),
                        custom_css:    get('custom_css'),
                    },
                    settingsMobile: {
                        corner:        get('corner_mobile'),
                        button_size:   get('button_size_mobile'),
                        border_radius: get('border_radius_mobile'),
                        offset_bottom: get('offset_bottom_mobile'),
                        offset_side:   get('offset_side_mobile'),
                    },
                    channels: channels,
                    forceExpand: forceExpandToggle ? forceExpandToggle.checked : false
                }
            };
            previewFrame.contentWindow.postMessage(payload, '*');
        }

        /* ── Channel card: extra detail rows for new features ─ */
        function extraChannelRows(idx, ch) {
            const agentTypes  = (pcbL10n && pcbL10n.agentsTypes)    || [];
            const msgTypes    = (pcbL10n && pcbL10n.presetMsgTypes) || [];
            const isForm      = ch.type === 'form';
            const supportsMsg = msgTypes.includes(ch.type);
            const supportsAgt = agentTypes.includes(ch.type);

            let html = '';

            if (isForm) {
                const fc = ch.form_config || {};
                html += `
                <div class="pcb-detail-row">
                    <label>${escHtml(pcbL10n.formTitleLabel || 'Form Title')}</label>
                    <input type="text" class="pcb-form-title-val" value="${escAttr(fc.title || '')}" placeholder="Send us a message">
                </div>
                <div class="pcb-detail-row">
                    <label>${escHtml(pcbL10n.formSubmitLabel || 'Submit Button Text')}</label>
                    <input type="text" class="pcb-form-submit-val" value="${escAttr(fc.submit_label || '')}" placeholder="Send Message">
                </div>
                <div class="pcb-detail-row pcb-checkrow">
                    <label><input type="checkbox" class="pcb-form-phone-val" ${fc.show_phone ? 'checked' : ''}> ${escHtml('Show Phone field')}</label>
                    <label><input type="checkbox" class="pcb-form-message-val" ${(fc.show_message !== false) ? 'checked' : ''}> ${escHtml('Show Message field')}</label>
                </div>
                <p class="pcb-option-hint">${escHtml(pcbL10n.formFieldsNote || '')}</p>`;
            }

            if (supportsMsg) {
                html += `
                <div class="pcb-detail-row">
                    <label>${escHtml(pcbL10n.presetMsgLabel || 'Pre-filled Message (optional)')}</label>
                    <input type="text" class="pcb-preset-msg-val" value="${escAttr(ch.preset_message || '')}" placeholder="${escAttr(pcbL10n.presetMsgPlaceholder || '')}">
                    <span class="pcb-option-hint">${escHtml(pcbL10n.presetMsgHint || '')}</span>
                </div>`;
            }

            if (supportsAgt) {
                const agents = Array.isArray(ch.agents) ? ch.agents : [];
                html += `
                <div class="pcb-detail-row pcb-agents-row">
                    <label>
                        <input type="checkbox" class="pcb-agents-toggle" ${ch.agents_enabled ? 'checked' : ''}>
                        ${escHtml(pcbL10n.agentsToggle || 'Round-robin multiple contacts')}
                    </label>
                    <div class="pcb-agents-list" style="display:${ch.agents_enabled ? 'block' : 'none'};margin-top:6px;">
                        ${agents.map((a, i) => agentItemHtml(a, i)).join('')}
                        <button type="button" class="button button-small pcb-add-agent">${escHtml(pcbL10n.addAgent || '+ Add another contact')}</button>
                    </div>
                </div>`;
            }

            return html;
        }

        function agentItemHtml(a, i) {
            return `
            <div class="pcb-agent-item" data-agent-idx="${i}">
                <input type="text" class="pcb-agent-name"  value="${escAttr(a.name  || '')}" placeholder="${escAttr(pcbL10n.agentLabel || 'Name')}" style="flex:1;">
                <input type="text" class="pcb-agent-value" value="${escAttr(a.value || '')}" placeholder="${escAttr(pcbL10n.agentValue || 'Phone / URL / ID')}" style="flex:1.2;">
                <button type="button" class="button button-small pcb-remove-agent" title="${escAttr(pcbL10n.removeAgent || 'Remove')}">×</button>
            </div>`;
        }

        /* ── Render Channel Stack ───────────────────────────── */
        function render() {
            stack.innerHTML = '';

            if (channels.length === 0) {
                stack.innerHTML = `<div class="pcb-stack-empty">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    Click an app above to add it</div>`;
                return;
            }

            channels.forEach((ch, idx) => {
                const item = document.createElement('div');
                item.className = 'pcb-channel-item' + (ch.enabled === false ? ' pcb-disabled' : '');
                item.draggable = true;
                item.dataset.index = idx;

                const urlHint = ch.url ? ch.url.replace(/^https?:\/\//, '') : '— no URL set —';
                const isCustom = ch.type === 'custom';
                const isForm   = ch.type === 'form';
                const urlPlaceholders = (pcbL10n && pcbL10n.urlPlaceholders) ? pcbL10n.urlPlaceholders : {};
                const urlPlaceholder = urlPlaceholders[ch.type] || 'https://...';

                item.innerHTML = `
                    <span class="pcb-item-remove-abs pcb-rm" title="Remove">
                        <svg width="7" height="7" viewBox="0 0 8 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="1" y1="1" x2="7" y2="7"/><line x1="7" y1="1" x2="1" y2="7"/></svg>
                    </span>
                    <div class="pcb-row-header pcb-row-toggle">
                        <span class="pcb-item-handle">⠿</span>
                        <div class="pcb-item-type-icon" style="background:${ch.color}">${ch.icon}</div>
                        <div class="pcb-item-info">
                            ${(isCustom || isForm)
                                ? `<input type="text" class="pcb-label-val" value="${escAttr(ch.label)}" placeholder="${escAttr(pcbL10n.labelPlaceholder || 'Label')}">`
                                : `<h4>${escHtml(ch.label)}</h4>`
                            }
                            <span class="pcb-url-hint">${escHtml(urlHint)}</span>
                        </div>
                        <div class="pcb-item-end">
                            <label class="pcb-enable-switch" title="Enable / Disable">
                                <input type="checkbox" class="pcb-ch-enable" ${ch.enabled !== false ? 'checked' : ''}>
                                <span class="pcb-enable-slider"></span>
                            </label>
                            <span class="pcb-item-chevron">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2,4 6,8 10,4"/></svg>
                            </span>
                        </div>
                    </div>
                    <div class="pcb-item-details" style="display:none">
                        ${isForm ? '' : `
                        <div class="pcb-detail-row">
                            <label>${escHtml(pcbL10n.urlLabel || 'URL / Phone / ID')}</label>
                            <input type="text" class="pcb-val" value="${escAttr(ch.url || '')}" placeholder="${escAttr(urlPlaceholder)}">
                        </div>`}
                        ${isCustom ? `
                        <div class="pcb-detail-row">
                            <label>${escHtml(pcbL10n.customIconLabel || 'Custom Icon (URL or SVG)')}</label>
                            <div style="display:flex;gap:5px;">
                                <input type="text" class="pcb-icon-val" value="${escAttr(ch.icon_value || '')}" placeholder="${escAttr(pcbL10n.customIconPlaceholder || 'https://... or <svg...>')}" style="flex:1;">
                                <button type="button" class="button pcb-icon-upload-btn">Upload</button>
                            </div>
                        </div>` : ''}
                        ${extraChannelRows(idx, ch)}
                        <div class="pcb-color-grid">
                            <div>
                                <div class="pcb-color-input-wrap pcb-c-trigger">
                                    <span class="pcb-color-label">${escHtml(pcbL10n.colorLabel || 'Color')}</span>
                                    <input type="color" class="pcb-c1" value="${ch.color}">
                                    <span class="pcb-hex-val">${(ch.color || '').toUpperCase()}</span>
                                </div>
                            </div>
                            <div>
                                <div class="pcb-color-input-wrap pcb-c-trigger">
                                    <span class="pcb-color-label">${escHtml(pcbL10n.hoverLabel || 'Hover')}</span>
                                    <input type="color" class="pcb-c2" value="${ch.hover}">
                                    <span class="pcb-hex-val">${(ch.hover || '').toUpperCase()}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                /* Toggle expand */
                item.querySelector('.pcb-row-toggle').addEventListener('click', (e) => {
                    if (e.target.closest('input, button, label')) return;
                    const details = item.querySelector('.pcb-item-details');
                    const open = item.classList.toggle('pcb-item-open');
                    details.style.display = open ? 'block' : 'none';
                });
                if (!ch.url && !isForm) {
                    item.querySelector('.pcb-item-details').style.display = 'block';
                    item.classList.add('pcb-item-open');
                }

                /* Remove */
                item.querySelector('.pcb-rm').addEventListener('click', () => {
                    channels.splice(idx, 1);
                    render(); sync();
                });

                /* Enable toggle */
                item.querySelector('.pcb-ch-enable').addEventListener('change', (e) => {
                    channels[idx].enabled = e.target.checked;
                    item.classList.toggle('pcb-disabled', !e.target.checked);
                    sync();
                });

                /* URL */
                const urlEl = item.querySelector('.pcb-val');
                if (urlEl) {
                    urlEl.addEventListener('input', (e) => {
                        channels[idx].url = e.target.value;
                        item.querySelector('.pcb-url-hint').textContent =
                            e.target.value ? e.target.value.replace(/^https?:\/\//, '') : '— no URL set —';
                        sync();
                    });
                }

                /* Label */
                const labelInput = item.querySelector('.pcb-label-val');
                if (labelInput) {
                    labelInput.addEventListener('input', (e) => { channels[idx].label = e.target.value; sync(); });
                }

                /* Color pickers */
                item.querySelectorAll('.pcb-c-trigger').forEach(wrap => {
                    wrap.addEventListener('click', () => wrap.querySelector('input[type=color]').click());
                });
                item.querySelector('.pcb-c1').addEventListener('input', (e) => {
                    channels[idx].color = e.target.value;
                    item.querySelector('.pcb-item-type-icon').style.background = e.target.value;
                    item.querySelectorAll('.pcb-hex-val')[0].textContent = e.target.value.toUpperCase();
                    sync();
                });
                item.querySelector('.pcb-c2').addEventListener('input', (e) => {
                    channels[idx].hover = e.target.value;
                    item.querySelectorAll('.pcb-hex-val')[1].textContent = e.target.value.toUpperCase();
                    sync();
                });

                /* Custom icon */
                if (isCustom) {
                    const iconInput = item.querySelector('.pcb-icon-val');
                    const upBtn     = item.querySelector('.pcb-icon-upload-btn');
                    if (iconInput) {
                        iconInput.addEventListener('input', (e) => {
                            const val = e.target.value.trim();
                            channels[idx].icon_value = val;
                            if (val.startsWith('<svg')) {
                                channels[idx].icon = val;
                            } else if (val.startsWith('http')) {
                                channels[idx].icon = `<img src="${val}" alt="">`;
                            } else {
                                channels[idx].icon = '?';
                            }
                            item.querySelector('.pcb-item-type-icon').innerHTML = channels[idx].icon;
                            sync();
                        });
                    }
                    if (upBtn && window.wp && wp.media) {
                        upBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            const up = wp.media({ title: 'Select Icon', button: { text: 'Use this icon' }, multiple: false });
                            up.on('select', () => {
                                const att = up.state().get('selection').first().toJSON();
                                if (iconInput) { iconInput.value = att.url; iconInput.dispatchEvent(new Event('input')); }
                            });
                            up.open();
                        });
                    }
                }

                /* Preset message */
                const presetMsg = item.querySelector('.pcb-preset-msg-val');
                if (presetMsg) {
                    presetMsg.addEventListener('input', (e) => {
                        channels[idx].preset_message = e.target.value;
                        sync();
                    });
                }

                /* Agents (round-robin) */
                const agentsToggle = item.querySelector('.pcb-agents-toggle');
                const agentsList   = item.querySelector('.pcb-agents-list');
                if (agentsToggle) {
                    agentsToggle.addEventListener('change', (e) => {
                        channels[idx].agents_enabled = e.target.checked ? 1 : 0;
                        if (agentsList) agentsList.style.display = e.target.checked ? 'block' : 'none';
                        // Seed an empty agent on first enable for UX clarity.
                        if (e.target.checked && (!channels[idx].agents || !channels[idx].agents.length)) {
                            channels[idx].agents = [ { name: '', value: channels[idx].url || '' } ];
                            render(); sync();
                            return;
                        }
                        sync();
                    });
                }
                item.querySelectorAll('.pcb-agent-name').forEach((inp, aIdx) => {
                    inp.addEventListener('input', (e) => {
                        if (!channels[idx].agents) channels[idx].agents = [];
                        if (!channels[idx].agents[aIdx]) channels[idx].agents[aIdx] = {};
                        channels[idx].agents[aIdx].name = e.target.value;
                        sync();
                    });
                });
                item.querySelectorAll('.pcb-agent-value').forEach((inp, aIdx) => {
                    inp.addEventListener('input', (e) => {
                        if (!channels[idx].agents) channels[idx].agents = [];
                        if (!channels[idx].agents[aIdx]) channels[idx].agents[aIdx] = {};
                        channels[idx].agents[aIdx].value = e.target.value;
                        sync();
                    });
                });
                item.querySelectorAll('.pcb-remove-agent').forEach((btn, aIdx) => {
                    btn.addEventListener('click', () => {
                        if (!channels[idx].agents) return;
                        channels[idx].agents.splice(aIdx, 1);
                        render(); sync();
                    });
                });
                const addAgentBtn = item.querySelector('.pcb-add-agent');
                if (addAgentBtn) {
                    addAgentBtn.addEventListener('click', () => {
                        if (!channels[idx].agents) channels[idx].agents = [];
                        channels[idx].agents.push({ name: '', value: '' });
                        render(); sync();
                    });
                }

                /* Form config (form channel) */
                const ft = item.querySelector('.pcb-form-title-val');
                const fs = item.querySelector('.pcb-form-submit-val');
                const fp = item.querySelector('.pcb-form-phone-val');
                const fm = item.querySelector('.pcb-form-message-val');
                const ensureFormConfig = () => {
                    if (!channels[idx].form_config) channels[idx].form_config = { show_message: true };
                };
                if (ft) ft.addEventListener('input', (e) => { ensureFormConfig(); channels[idx].form_config.title = e.target.value; sync(); });
                if (fs) fs.addEventListener('input', (e) => { ensureFormConfig(); channels[idx].form_config.submit_label = e.target.value; sync(); });
                if (fp) fp.addEventListener('change', (e) => { ensureFormConfig(); channels[idx].form_config.show_phone = e.target.checked; sync(); });
                if (fm) fm.addEventListener('change', (e) => { ensureFormConfig(); channels[idx].form_config.show_message = e.target.checked; sync(); });

                /* Drag */
                item.addEventListener('dragstart', () => item.classList.add('dragging'));
                item.addEventListener('dragend',   () => item.classList.remove('dragging'));

                stack.appendChild(item);
            });
        }

        /* ── Add Preset Channel (no duplicates except custom) ─ */
        document.querySelectorAll('.pcb-app-tile[data-type]').forEach(tile => {
            tile.addEventListener('click', () => {
                const type = tile.dataset.type;
                if (type !== 'custom_link' && channels.some(ch => ch.type === type)) {
                    showToast(`${tile.dataset.label} already added`);
                    document.querySelector('.pcb-tab-btn[data-tab="channels"]')?.click();
                    return;
                }
                channels.push({
                    type:    type,
                    label:   tile.dataset.label,
                    icon:    tile.dataset.icon,
                    color:   tile.dataset.color,
                    hover:   tile.dataset.hover,
                    enabled: true,
                    url:     '',
                });
                render(); sync();
                document.querySelector('.pcb-tab-btn[data-tab="channels"]')?.click();
            });
        });

        /* ── Add Custom Channel + Form Channel ──────────────── */
        const customTile = document.querySelector('.pcb-add-custom-tile');
        if (customTile) {
            customTile.addEventListener('click', () => {
                channels.push({ type: 'custom', label: (pcbL10n && pcbL10n.customChannelLabel) ? pcbL10n.customChannelLabel : 'Custom', icon: '?', icon_value: '', color: '#4a5568', hover: '#2d3748', enabled: true, url: '' });
                render(); sync();
                document.querySelector('.pcb-tab-btn[data-tab="channels"]')?.click();
            });
        }
        const formTile = document.querySelector('.pcb-add-form-tile');
        if (formTile) {
            formTile.addEventListener('click', () => {
                const formIcon = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>';
                channels.push({
                    type: 'form', label: 'Contact Form', icon: formIcon,
                    color: '#6366f1', hover: '#4f46e5', enabled: true, url: '',
                    form_config: { title: 'Send us a message', submit_label: 'Send Message', show_phone: true, show_message: true }
                });
                render(); sync();
                document.querySelector('.pcb-tab-btn[data-tab="channels"]')?.click();
            });
        }

        /* ── Drag & Drop Reorder ────────────────────────────── */
        stack.addEventListener('dragover', (e) => {
            e.preventDefault();
            const dragging = stack.querySelector('.dragging');
            if (!dragging) return;
            const items = [...stack.querySelectorAll('.pcb-channel-item:not(.dragging)')];
            const next  = items.find(i => e.clientY <= i.getBoundingClientRect().top + i.offsetHeight / 2);
            stack.insertBefore(dragging, next || null);
        });
        stack.addEventListener('dragend', () => {
            const newOrder = [];
            stack.querySelectorAll('.pcb-channel-item').forEach(el => {
                newOrder.push(channels[parseInt(el.dataset.index)]);
            });
            channels = newOrder;
            render(); sync();
        });

        /* ── Form Sync Events ───────────────────────────────── */
        if (forceExpandToggle) forceExpandToggle.addEventListener('change', sync);
        settingsForm.addEventListener('input',  sync);
        settingsForm.addEventListener('change', sync);
        if (previewFrame) previewFrame.addEventListener('load', sync);

        /* ── Initial Render ─────────────────────────────────── */
        render();

        /* ── Visibility Rules ───────────────────────────────── */
        const template = document.getElementById('pcb-rule-template');

        const typeLabels = (pcbL10n && pcbL10n.typeLabels) ? pcbL10n.typeLabels : {
            pages: 'Pages', posts: 'Posts', categories: 'Categories',
            tags: 'Tags', taxonomies: 'Custom Taxonomies', others: 'Other Pages'
        };

        function createRuleRow(data = {}) {
            if (!template || !repeater) return;
            const clone          = template.children[0].cloneNode(true);
            const enabledInput   = clone.querySelector('input[type="checkbox"]');
            const typeSelect     = clone.querySelector('.pcb-type-select');
            const searchInput    = clone.querySelector('.pcb-search-input');
            const resultsBox     = clone.querySelector('.pcb-dropdown-results');
            const tagsContainer  = clone.querySelector('.pcb-selected-tags');
            const hiddenTargets  = clone.querySelector('input[type="hidden"]');
            const removeBtn      = clone.querySelector('.pcb-remove-rule');

            enabledInput.checked = data.enabled !== false;
            typeSelect.value     = data.type || 'pages';
            let selectedTargets  = Array.isArray(data.targets) ? data.targets.slice() : [];

            function saveTargets() { hiddenTargets.value = JSON.stringify(selectedTargets); }

            function renderTags() {
                tagsContainer.innerHTML = '';
                selectedTargets.forEach((t, i) => {
                    const tag  = document.createElement('div');
                    tag.className = 'pcb-tag';
                    const lbl  = document.createElement('span');
                    lbl.textContent = t.title;
                    const rm   = document.createElement('span');
                    rm.className = 'pcb-tag-remove';
                    rm.textContent = '×';
                    rm.addEventListener('click', () => { selectedTargets.splice(i, 1); renderTags(); });
                    tag.append(lbl, rm);
                    tagsContainer.appendChild(tag);
                });
                saveTargets();
            }

            function renderDropdown(term) {
                const type  = typeSelect.value;
                const label = typeLabels[type] || type;
                resultsBox.innerHTML = '';
                resultsBox.style.display = 'block';

                const allEl = document.createElement('div');
                allEl.className = 'pcb-dropdown-item pcb-dropdown-all';
                const star = document.createElement('span');
                star.className = 'pcb-star-icon'; star.textContent = '★';
                const allText = (pcbL10n && pcbL10n.allOf ? pcbL10n.allOf : 'All') + ' ' + label;
                allEl.append(star, document.createTextNode(' ' + allText));
                allEl.addEventListener('click', () => {
                    selectedTargets = [{ id: 'all', title: allText }];
                    searchInput.value = ''; resultsBox.style.display = 'none'; renderTags();
                });
                resultsBox.appendChild(allEl);

                let matches;
                if (type === 'others') {
                    const op = (pcbL10n && pcbL10n.otherPages) ? pcbL10n.otherPages : {};
                    matches = [
                        { id: 'home',   title: op.home   || 'Homepage'       },
                        { id: '404',    title: op['404'] || '404 Page'        },
                        { id: 'search', title: op.search || 'Search Results'  },
                    ];
                } else {
                    matches = (window.pcbAllRulesData || []).filter(p => p.type === type);
                }
                if (term) {
                    const q = term.toLowerCase();
                    matches = matches.filter(m => m.title.toLowerCase().includes(q));
                }
                if (matches.length === 0 && term) {
                    const empty = document.createElement('div');
                    empty.className = 'pcb-dropdown-empty'; empty.textContent = (pcbL10n && pcbL10n.noResults) ? pcbL10n.noResults : 'No results found';
                    resultsBox.appendChild(empty);
                    return;
                }
                matches.slice(0, 15).forEach(p => {
                    const div = document.createElement('div');
                    div.className = 'pcb-dropdown-item'; div.textContent = p.title;
                    div.addEventListener('click', () => {
                        if (selectedTargets.length === 1 && selectedTargets[0].id === 'all') selectedTargets = [];
                        if (!selectedTargets.find(t => t.id === p.id)) selectedTargets.push({ id: p.id, title: p.title });
                        searchInput.value = ''; resultsBox.style.display = 'none'; renderTags();
                    });
                    resultsBox.appendChild(div);
                });
            }

            searchInput.addEventListener('focus', () => renderDropdown(searchInput.value.trim()));
            searchInput.addEventListener('input', (e) => renderDropdown(e.target.value.trim()));
            typeSelect.addEventListener('change', () => {
                selectedTargets = []; searchInput.value = '';
                renderTags(); searchInput.focus(); renderDropdown('');
            });

            const closeDropdown = (e) => { if (!clone.contains(e.target)) resultsBox.style.display = 'none'; };
            document.addEventListener('click', closeDropdown);

            removeBtn.addEventListener('click', () => {
                document.removeEventListener('click', closeDropdown);
                clone.remove();
            });

            repeater.appendChild(clone);
            renderTags();

            if (!data.type) setTimeout(() => { searchInput.focus(); renderDropdown(''); }, 60);
        }

        if (addRuleBtn) addRuleBtn.addEventListener('click', () => createRuleRow());

        if (window.pcbSavedRules && window.pcbSavedRules.length) {
            window.pcbSavedRules.forEach(r => createRuleRow(r));
        } else if (repeater) {
            createRuleRow({ type: 'pages', enabled: true });
        }

        /* ── Serialize Rules & Submit ───────────────────────── */
        function serializeAndSubmit() {
            hiddenDataInput.value = JSON.stringify(channels);

            const rules = [];
            if (repeater) {
                repeater.querySelectorAll('.pcb-vis-rule-card').forEach(row => {
                    rules.push({
                        enabled: row.querySelector('input[type="checkbox"]').checked,
                        type:    row.querySelector('select').value,
                        targets: JSON.parse(row.querySelector('input[type="hidden"]').value || '[]')
                    });
                });
            }
            if (rulesInput) rulesInput.value = JSON.stringify(rules);

            settingsForm.submit();
        }

        /* ── Save button ─────────────────────────────────────── */
        if (saveTrigger) {
            saveTrigger.addEventListener('click', () => {
                saveTrigger.classList.add('saving');
                saveTrigger.textContent = (pcbL10n && pcbL10n.saving) ? pcbL10n.saving : 'Saving…';
                serializeAndSubmit();
            });
        }

        /* ── Ctrl + S Shortcut ───────────────────────────────── */
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                serializeAndSubmit();
            }
        });

        /* ── Show success toast if redirected back after save ── */
        if (window.location.search.includes('settings-updated=true')) {
            showToast((pcbL10n && pcbL10n.saved) ? pcbL10n.saved : '✓ Settings saved', 'success');
        }

        /* ── Helpers ────────────────────────────────────────── */
        function escHtml(str) {
            return String(str == null ? '' : str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function escAttr(str) { return escHtml(str); }

    });
})();
