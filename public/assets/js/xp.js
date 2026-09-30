(function () {
    'use strict';

    document.body.classList.add('js-ready');

    var TASKBAR_H = 30;
    var EXCLUDE_PATH = /^\/(yonetim|rss\.xml|assets)/;

    /* ================= Görüntü Özellikleri (ayarlar) ================= */

    var SETTINGS_KEY = 'xp-display-settings';
    var DEFAULT_SETTINGS = { scheme: 'blue', fontSize: 'normal', textColor: 'default' };
    var TEXT_COLOR_HEX = { default: '#1a1a1a', navy: '#1a2a6c', maroon: '#6c1a2a', darkgreen: '#1a5c2a', darkgray: '#3a3a3a' };

    function loadSettings() {
        try {
            var raw = localStorage.getItem(SETTINGS_KEY);
            if (!raw) return Object.assign({}, DEFAULT_SETTINGS);
            return Object.assign({}, DEFAULT_SETTINGS, JSON.parse(raw));
        } catch (err) { return Object.assign({}, DEFAULT_SETTINGS); }
    }
    function saveSettings(s) {
        try { localStorage.setItem(SETTINGS_KEY, JSON.stringify(s)); } catch (err) { /* localStorage unavailable */ }
    }
    function applySettings(s) {
        var root = document.documentElement;
        if (s.scheme && s.scheme !== 'blue') root.dataset.scheme = s.scheme; else delete root.dataset.scheme;
        if (s.fontSize && s.fontSize !== 'normal') root.dataset.fontSize = s.fontSize; else delete root.dataset.fontSize;
        if (s.textColor && s.textColor !== 'default') root.dataset.textColor = s.textColor; else delete root.dataset.textColor;
    }

    var currentSettings = loadSettings();
    applySettings(currentSettings);

    var startBtn = document.getElementById('startBtn');
    var startMenu = document.getElementById('startMenu');
    var desktopIcons = document.getElementById('desktopIcons');
    var taskButtons = document.getElementById('taskButtons');
    var trayClock = document.getElementById('trayClock');

    var windows = {}; // id -> { el, taskBtn, href, restoreRect, maximized }
    var activeId = null;
    var zCounter = 20;
    var winCounter = 1;
    var cascadeStep = 0;

    var winTemplate = document.getElementById('mainWindow');
    var winTemplateHTML = winTemplate ? winTemplate.outerHTML : null;

    function clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }

    /* ================= Window registry / lifecycle ================= */

    function registerWindow(el, taskBtn, href) {
        var id = el.dataset.winId;
        windows[id] = { el: el, taskBtn: taskBtn, href: href, restoreRect: null, maximized: false };
        wireWindowChrome(el, id);
        wireTaskButton(taskBtn, id);
        return id;
    }

    function placeCascaded(el) {
        var vw = window.innerWidth, vh = window.innerHeight;
        var w = Math.min(820, vw - 60);
        var h = Math.min(560, vh - TASKBAR_H - 80);
        var offset = (cascadeStep % 7) * 28;
        cascadeStep++;
        var left = clamp(Math.round((vw - w) / 2) - 60 + offset, 4, vw - 120);
        var top = clamp(Math.round((vh - TASKBAR_H - h) / 2) - 30 + offset, 4, vh - TASKBAR_H - 80);
        el.style.width = w + 'px';
        el.style.height = h + 'px';
        el.style.left = left + 'px';
        el.style.top = top + 'px';
    }

    function placeInitial(el) {
        var vw = window.innerWidth, vh = window.innerHeight;
        var w = Math.min(880, vw - 40);
        var h = Math.min(600, vh - TASKBAR_H - 60);
        var left = Math.round((vw - w) / 2);
        var top = Math.round((vh - TASKBAR_H - h) / 2) - 10;
        el.style.width = w + 'px';
        el.style.height = h + 'px';
        el.style.left = clamp(left, 4, vw - 40) + 'px';
        el.style.top = clamp(top, 4, vh - TASKBAR_H - 40) + 'px';
    }

    function createTaskButton(icon, color, label) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'task-btn';
        btn.innerHTML =
            '<span class="badge ' + color + '" aria-hidden="true"><span class="icon icon-' + icon + '"></span></span>' +
            '<span class="task-btn-label"></span>';
        btn.querySelector('.task-btn-label').textContent = label;
        return btn;
    }

    function openWindow(href, opts) {
        opts = opts || {};
        var path = href.split('#')[0];
        var existingId = null;
        Object.keys(windows).forEach(function (id) {
            if (windows[id].href === path) existingId = id;
        });
        if (existingId) {
            restoreWindow(existingId);
            focusWindow(existingId);
            return;
        }
        if (!winTemplateHTML) { window.location.href = href; return; }

        fetch(href, { headers: { 'X-XP-Window': '1' }, credentials: 'same-origin' })
            .then(function (res) {
                if (!res.ok) throw new Error('bad response');
                var ct = res.headers.get('Content-Type') || '';
                if (ct.indexOf('application/json') === -1) throw new Error('not json');
                return res.json();
            })
            .then(function (data) {
                var wrap = document.createElement('div');
                wrap.innerHTML = winTemplateHTML;
                var el = wrap.firstElementChild;
                el.removeAttribute('id');
                var id = 'win-' + (++winCounter);
                el.dataset.winId = id;
                el.dataset.href = path;
                el.classList.remove('is-minimized', 'is-maximized', 'is-inactive');
                el.style.left = ''; el.style.top = ''; el.style.width = ''; el.style.height = '';

                var titleText = data.windowTitle || opts.title || path;
                el.querySelector('.win-title-text').textContent = titleText;
                var body = el.querySelector('.os-window-body');
                body.innerHTML = data.html;
                var statusEls = el.querySelectorAll('.os-window-status span');
                if (statusEls[0]) statusEls[0].textContent = data.status || '';
                var addrInput = el.querySelector('.addr-input');
                if (addrInput) addrInput.value = window.location.origin + path;
                var winIcon = el.querySelector('.win-icon .icon');
                if (winIcon && data.icon) winIcon.className = 'icon icon-' + data.icon;

                document.body.appendChild(el);
                placeCascaded(el);

                var taskBtn = createTaskButton(data.icon || 'folder', data.color || 'c-blue', titleText);
                taskBtn.dataset.winId = id;
                taskButtons.appendChild(taskBtn);

                registerWindow(el, taskBtn, path);
                focusWindow(id);
                if (data.docTitle) document.title = data.docTitle;
            })
            .catch(function () {
                window.location.href = href;
            });
    }

    function reloadWindowContent(id) {
        var w = windows[id];
        if (!w) return;
        fetch(w.href, { headers: { 'X-XP-Window': '1' }, credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                w.el.querySelector('.os-window-body').innerHTML = data.html;
                var statusEls = w.el.querySelectorAll('.os-window-status span');
                if (statusEls[0]) statusEls[0].textContent = data.status || '';
            })
            .catch(function () { window.location.reload(); });
    }

    /* ================= Focus / z-order ================= */

    function focusWindow(id) {
        if (!windows[id]) return;
        activeId = id;
        Object.keys(windows).forEach(function (wid) {
            var w = windows[wid];
            var isActive = wid === id;
            w.el.classList.toggle('is-inactive', !isActive);
            w.taskBtn.classList.toggle('is-active', isActive && !w.el.classList.contains('is-minimized'));
        });
        windows[id].el.style.zIndex = String(++zCounter);
        closeStartMenu();
    }

    function topMostVisible(excludeId) {
        var best = null, bestZ = -1;
        Object.keys(windows).forEach(function (id) {
            if (id === excludeId) return;
            var w = windows[id];
            if (w.el.classList.contains('is-minimized')) return;
            var z = parseInt(w.el.style.zIndex || '0', 10);
            if (z > bestZ) { bestZ = z; best = id; }
        });
        return best;
    }

    /* ================= Minimize / maximize / close ================= */

    function minimizeWindow(id) {
        var w = windows[id];
        if (!w) return;
        w.el.classList.add('is-minimized');
        w.taskBtn.classList.remove('is-active');
        if (activeId === id) {
            var next = topMostVisible(id);
            if (next) focusWindow(next); else activeId = null;
        }
    }

    function restoreWindow(id) {
        var w = windows[id];
        if (!w) return;
        w.el.classList.remove('is-minimized');
    }

    function toggleMaximize(id) {
        var w = windows[id];
        if (!w) return;
        if (w.maximized) {
            w.maximized = false;
            w.el.classList.remove('is-maximized');
            if (w.restoreRect) {
                w.el.style.left = w.restoreRect.left;
                w.el.style.top = w.restoreRect.top;
                w.el.style.width = w.restoreRect.width;
                w.el.style.height = w.restoreRect.height;
            }
        } else {
            w.restoreRect = {
                left: w.el.style.left, top: w.el.style.top,
                width: w.el.style.width, height: w.el.style.height
            };
            w.maximized = true;
            w.el.classList.add('is-maximized');
            w.el.style.left = '0px';
            w.el.style.top = '0px';
            w.el.style.width = '100vw';
            w.el.style.height = 'calc(100vh - ' + TASKBAR_H + 'px)';
        }
        focusWindow(id);
    }

    function closeWindowFn(id) {
        var w = windows[id];
        if (!w) return;
        w.el.remove();
        w.taskBtn.remove();
        delete windows[id];
        if (activeId === id) {
            var next = topMostVisible(null);
            if (next) focusWindow(next); else activeId = null;
        }
    }

    /* ================= Per-window chrome wiring (drag/resize/buttons) ================= */

    var dragState = null;
    var resizeState = null;
    var dialogDragState = null;

    function wireWindowChrome(el, id) {
        var titleBar = el.querySelector('.os-window-title');
        var minBtn = el.querySelector('.win-min');
        var maxBtn = el.querySelector('.win-max');
        var closeBtn = el.querySelector('.win-close');
        var resizeHandle = el.querySelector('.win-resize');
        var addrInput = el.querySelector('.addr-input');
        var addrGo = el.querySelector('.addr-go');

        el.addEventListener('mousedown', function () { focusWindow(id); });

        if (titleBar) {
            titleBar.addEventListener('mousedown', function (e) {
                if (e.target.closest('.win-btn')) return;
                if (windows[id].maximized) return;
                dragState = { id: id, startX: e.clientX, startY: e.clientY, startLeft: el.offsetLeft, startTop: el.offsetTop };
                focusWindow(id);
                e.preventDefault();
            });
            titleBar.addEventListener('dblclick', function (e) {
                if (e.target.closest('.win-btn')) return;
                toggleMaximize(id);
            });
        }
        if (minBtn) minBtn.addEventListener('click', function () { minimizeWindow(id); });
        if (maxBtn) maxBtn.addEventListener('click', function () { toggleMaximize(id); });
        if (closeBtn) closeBtn.addEventListener('click', function () { closeWindowFn(id); });
        if (resizeHandle) {
            resizeHandle.addEventListener('mousedown', function (e) {
                if (windows[id].maximized) return;
                resizeState = { id: id, startX: e.clientX, startY: e.clientY, startW: el.offsetWidth, startH: el.offsetHeight };
                e.preventDefault();
                e.stopPropagation();
            });
        }
        if (addrInput) {
            addrInput.addEventListener('focus', function () { addrInput.select(); });
            addrInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') navigateAddr(addrInput);
            });
        }
        if (addrGo) {
            addrGo.addEventListener('click', function () { navigateAddr(addrInput); });
        }
    }

    function navigateAddr(input) {
        if (!input) return;
        var val = input.value.trim();
        if (!val) return;
        if (!/^https?:\/\//i.test(val)) val = 'https://' + val;
        window.location.href = val;
    }

    function wireTaskButton(btn, id) {
        btn.addEventListener('click', function () {
            var w = windows[id];
            if (!w) return;
            if (w.el.classList.contains('is-minimized')) {
                restoreWindow(id);
                focusWindow(id);
            } else if (activeId === id) {
                minimizeWindow(id);
            } else {
                focusWindow(id);
            }
        });
    }

    window.addEventListener('mousemove', function (e) {
        if (dragState) {
            var w = windows[dragState.id];
            if (!w) { dragState = null; return; }
            var dx = e.clientX - dragState.startX;
            var dy = e.clientY - dragState.startY;
            var vw = window.innerWidth, vh = window.innerHeight;
            var newLeft = clamp(dragState.startLeft + dx, -w.el.offsetWidth + 80, vw - 60);
            var newTop = clamp(dragState.startTop + dy, 0, vh - 34);
            w.el.style.left = newLeft + 'px';
            w.el.style.top = newTop + 'px';
        }
        if (resizeState) {
            var rw = windows[resizeState.id];
            if (!rw) { resizeState = null; return; }
            var dw = e.clientX - resizeState.startX;
            var dh = e.clientY - resizeState.startY;
            rw.el.style.width = clamp(resizeState.startW + dw, 320, window.innerWidth - rw.el.offsetLeft - 10) + 'px';
            rw.el.style.height = clamp(resizeState.startH + dh, 220, window.innerHeight - rw.el.offsetTop - 34) + 'px';
        }
        if (dialogDragState) {
            var d = dialogDragState;
            d.el.style.left = clamp(d.startLeft + (e.clientX - d.startX), 0, window.innerWidth - 60) + 'px';
            d.el.style.top = clamp(d.startTop + (e.clientY - d.startY), 0, window.innerHeight - 34) + 'px';
        }
    });
    window.addEventListener('mouseup', function () { dragState = null; resizeState = null; dialogDragState = null; });

    /* ================= Menu bar dropdowns (global, any window) ================= */

    function closeAllMenus() {
        document.querySelectorAll('.menu-drop.is-open').forEach(function (d) { d.classList.remove('is-open'); });
    }
    document.addEventListener('click', function (e) {
        var label = e.target.closest('.menu-label');
        if (label) {
            e.stopPropagation();
            var drop = label.closest('.menu-drop');
            var wasOpen = drop.classList.contains('is-open');
            closeAllMenus();
            if (!wasOpen) drop.classList.add('is-open');
            return;
        }
        closeAllMenus();
    });
    document.addEventListener('mouseover', function (e) {
        var label = e.target.closest('.menu-label');
        if (!label) return;
        var anyOpen = document.querySelector('.menu-drop.is-open');
        if (anyOpen) {
            closeAllMenus();
            label.closest('.menu-drop').classList.add('is-open');
        }
    });

    /* ================= Menu / toolbar actions ================= */

    document.addEventListener('click', function (e) {
        var actionEl = e.target.closest('[data-action]');
        if (!actionEl) return;
        var ownerWin = actionEl.closest('.os-window');
        var ownerId = ownerWin ? ownerWin.dataset.winId : activeId;
        var action = actionEl.dataset.action;
        switch (action) {
            case 'print':
                window.print();
                break;
            case 'reload':
                if (ownerId) reloadWindowContent(ownerId);
                break;
            case 'back':
                window.history.back();
                break;
            case 'forward':
                window.history.forward();
                break;
            case 'minimize':
                if (ownerId) minimizeWindow(ownerId);
                break;
            case 'maximize':
                if (ownerId) toggleMaximize(ownerId);
                break;
            case 'close':
                if (ownerId) closeWindowFn(ownerId);
                break;
            case 'copy-link':
                var link = window.location.origin + (ownerWin ? ownerWin.dataset.href : window.location.pathname);
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(link).then(function () {
                        actionEl.textContent = 'Kopyalandı!';
                        setTimeout(function () { actionEl.textContent = 'Bağlantıyı Kopyala'; }, 1400);
                    });
                }
                break;
        }
    });

    /* ================= Internal link interception -> windows ================= */

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented) return;
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest('a[href]');
        if (!a) return;
        if (a.closest('.desktop-icon')) return;
        if (a.target === '_blank') return;
        var href = a.getAttribute('href');
        if (!href || href.charAt(0) !== '/') return;
        if (EXCLUDE_PATH.test(href)) return;
        e.preventDefault();
        openWindow(href, { title: a.textContent.trim() });
    });

    /* ================= Start menu ================= */

    function closeStartMenu() {
        if (startMenu) startMenu.classList.remove('is-open');
        if (startBtn) startBtn.classList.remove('is-open');
    }
    function toggleStartMenu(e) {
        e.stopPropagation();
        if (!startMenu) return;
        var open = startMenu.classList.toggle('is-open');
        startBtn.classList.toggle('is-open', open);
    }
    if (startBtn) startBtn.addEventListener('click', toggleStartMenu);
    document.addEventListener('click', function (e) {
        if (startMenu && !startMenu.contains(e.target) && e.target !== startBtn && !startBtn.contains(e.target)) {
            closeStartMenu();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeStartMenu();
            closeAllMenus();
        }
    });

    /* ================= Görüntü Özellikleri iletişim kutusu ================= */

    var settingsDialogEl = null;

    function swatchesHTML(selected) {
        var colors = [
            { key: 'default', css: '#1a1a1a', label: 'Siyah' },
            { key: 'navy', css: '#1a2a6c', label: 'Lacivert' },
            { key: 'maroon', css: '#6c1a2a', label: 'Bordo' },
            { key: 'darkgreen', css: '#1a5c2a', label: 'Koyu Yeşil' },
            { key: 'darkgray', css: '#3a3a3a', label: 'Koyu Gri' }
        ];
        return colors.map(function (c) {
            return '<button type="button" class="swatch' + (c.key === selected ? ' is-selected' : '') +
                '" style="background:' + c.css + '" data-color="' + c.key + '" title="' + c.label + '" aria-label="' + c.label + '"></button>';
        }).join('');
    }

    function buildSettingsDialogHTML(s) {
        return '' +
            '<div class="xp-dialog-title">' +
            '<span class="win-icon" aria-hidden="true"><span class="icon icon-gear"></span></span>' +
            '<span class="xp-dialog-title-text">Görüntü Özellikleri</span>' +
            '<button class="win-btn win-close" type="button" title="Kapat" aria-label="Kapat"></button>' +
            '</div>' +
            '<div class="xp-dialog-body">' +
            '<fieldset>' +
            '<legend>Görünüm</legend>' +
            '<label class="field-label">Renk Şeması</label>' +
            '<select data-setting="scheme">' +
            '<option value="blue"' + (s.scheme === 'blue' ? ' selected' : '') + '>Mavi (Windows XP)</option>' +
            '<option value="olive"' + (s.scheme === 'olive' ? ' selected' : '') + '>Zeytin Yeşili</option>' +
            '<option value="silver"' + (s.scheme === 'silver' ? ' selected' : '') + '>Gümüş</option>' +
            '</select>' +
            '</fieldset>' +
            '<fieldset>' +
            '<legend>Yazı Tipi</legend>' +
            '<label class="field-label">Boyut</label>' +
            '<select data-setting="fontSize">' +
            '<option value="small"' + (s.fontSize === 'small' ? ' selected' : '') + '>Küçük</option>' +
            '<option value="normal"' + (s.fontSize === 'normal' ? ' selected' : '') + '>Normal</option>' +
            '<option value="large"' + (s.fontSize === 'large' ? ' selected' : '') + '>Büyük</option>' +
            '</select>' +
            '<label class="field-label" style="margin-top:10px;">Renk</label>' +
            '<div class="swatch-row" data-setting="textColor">' + swatchesHTML(s.textColor) + '</div>' +
            '<div class="xp-dialog-preview">Örnek Metin — Ağüşiöç 123</div>' +
            '</fieldset>' +
            '</div>' +
            '<div class="xp-dialog-actions">' +
            '<button type="button" data-act="ok" class="primary">Tamam</button>' +
            '<button type="button" data-act="cancel">İptal</button>' +
            '<button type="button" data-act="apply">Uygula</button>' +
            '</div>';
    }

    function openSettingsDialog() {
        closeContextMenu();
        if (settingsDialogEl) {
            settingsDialogEl.style.zIndex = String(++zCounter);
            return;
        }
        var working = Object.assign({}, currentSettings);

        var el = document.createElement('div');
        el.className = 'xp-dialog';
        el.innerHTML = buildSettingsDialogHTML(working);
        document.body.appendChild(el);
        settingsDialogEl = el;

        var vw = window.innerWidth, vh = window.innerHeight;
        el.style.left = clamp(Math.round((vw - 380) / 2), 4, vw - 60) + 'px';
        el.style.top = clamp(Math.round((vh - 360) / 2), 4, vh - 60) + 'px';
        el.style.zIndex = String(++zCounter);

        var preview = el.querySelector('.xp-dialog-preview');
        function updatePreview() { preview.style.color = TEXT_COLOR_HEX[working.textColor] || TEXT_COLOR_HEX.default; }
        updatePreview();

        el.querySelectorAll('select[data-setting]').forEach(function (sel) {
            sel.addEventListener('change', function () {
                working[sel.dataset.setting] = sel.value;
                applySettings(working);
            });
        });
        el.querySelectorAll('.swatch').forEach(function (btn) {
            btn.addEventListener('click', function () {
                working.textColor = btn.dataset.color;
                el.querySelectorAll('.swatch').forEach(function (s) { s.classList.remove('is-selected'); });
                btn.classList.add('is-selected');
                applySettings(working);
                updatePreview();
            });
        });

        el.addEventListener('mousedown', function () { el.style.zIndex = String(++zCounter); });
        var titleBar = el.querySelector('.xp-dialog-title');
        titleBar.addEventListener('mousedown', function (e) {
            if (e.target.closest('.win-btn')) return;
            dialogDragState = { el: el, startX: e.clientX, startY: e.clientY, startLeft: el.offsetLeft, startTop: el.offsetTop };
            el.style.zIndex = String(++zCounter);
            e.preventDefault();
        });

        function closeDialog(restore) {
            if (restore) applySettings(currentSettings);
            el.remove();
            settingsDialogEl = null;
        }
        el.querySelector('.win-close').addEventListener('click', function () { closeDialog(true); });
        el.querySelector('[data-act="cancel"]').addEventListener('click', function () { closeDialog(true); });
        el.querySelector('[data-act="apply"]').addEventListener('click', function () {
            currentSettings = Object.assign({}, working);
            saveSettings(currentSettings);
        });
        el.querySelector('[data-act="ok"]').addEventListener('click', function () {
            currentSettings = Object.assign({}, working);
            saveSettings(currentSettings);
            closeDialog(false);
        });
    }

    /* ================= Masaüstü sağ tık menüsü ================= */

    var contextMenuEl = null;

    function buildContextMenu() {
        var el = document.createElement('div');
        el.className = 'desktop-context-menu';
        el.innerHTML =
            '<button type="button" data-ctx="arrange"><span class="icon icon-tag"></span>Simgeleri Diz (Ada Göre)</button>' +
            '<button type="button" data-ctx="refresh"><span class="icon icon-gear"></span>Yenile</button>' +
            '<div class="menu-sep"></div>' +
            '<button type="button" data-ctx="properties"><span class="icon icon-folder"></span>Özellikler</button>';
        document.body.appendChild(el);
        el.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-ctx]');
            if (!btn) return;
            var action = btn.dataset.ctx;
            closeContextMenu();
            if (action === 'arrange') arrangeIcons();
            else if (action === 'refresh') window.location.reload();
            else if (action === 'properties') openSettingsDialog();
        });
        return el;
    }

    function openContextMenu(x, y) {
        if (!contextMenuEl) contextMenuEl = buildContextMenu();
        contextMenuEl.classList.add('is-open');
        var vw = window.innerWidth, vh = window.innerHeight;
        contextMenuEl.style.left = clamp(x, 4, vw - 200) + 'px';
        contextMenuEl.style.top = clamp(y, 4, vh - TASKBAR_H - 110) + 'px';
    }
    function closeContextMenu() {
        if (contextMenuEl) contextMenuEl.classList.remove('is-open');
    }

    function arrangeIcons() {
        if (!desktopIcons) return;
        var icons = Array.prototype.slice.call(desktopIcons.querySelectorAll('.desktop-icon'));
        icons.sort(function (a, b) {
            var la = (a.querySelector('.di-label') || {}).textContent || '';
            var lb = (b.querySelector('.di-label') || {}).textContent || '';
            return la.trim().localeCompare(lb.trim(), 'tr');
        });
        icons.forEach(function (icon) { desktopIcons.appendChild(icon); });
    }

    document.addEventListener('contextmenu', function (e) {
        if (e.target.closest('.os-window') || e.target.closest('.taskbar') || e.target.closest('.start-menu') ||
            e.target.closest('.xp-dialog') || e.target.closest('.desktop-icon') || e.target.closest('.desktop-context-menu')) {
            closeContextMenu();
            return;
        }
        e.preventDefault();
        openContextMenu(e.clientX, e.clientY);
    });
    document.addEventListener('click', function (e) {
        if (contextMenuEl && !contextMenuEl.contains(e.target)) closeContextMenu();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeContextMenu();
    });

    /* ================= Desktop icon select / open ================= */

    if (desktopIcons) {
        var icons = desktopIcons.querySelectorAll('.desktop-icon');
        icons.forEach(function (icon) {
            icon.addEventListener('click', function (e) {
                e.preventDefault();
                icons.forEach(function (i) { i.classList.remove('is-selected'); });
                icon.classList.add('is-selected');
            });
            icon.addEventListener('dblclick', function () {
                openWindow(icon.getAttribute('href'), { title: icon.querySelector('.di-label') ? icon.querySelector('.di-label').textContent : '' });
            });
        });
        document.addEventListener('click', function (e) {
            if (!desktopIcons.contains(e.target)) {
                icons.forEach(function (i) { i.classList.remove('is-selected'); });
            }
        });
    }

    /* ================= Tray clock ================= */

    function updateClock() {
        if (!trayClock) return;
        var now = new Date();
        var h = now.getHours();
        var m = now.getMinutes();
        trayClock.textContent = (h < 10 ? '0' + h : h) + ':' + (m < 10 ? '0' + m : m);
    }
    updateClock();
    setInterval(updateClock, 15000);

    /* ================= Bootstrap the initial SSR window ================= */

    if (winTemplate) {
        placeInitial(winTemplate);
        var initialTaskBtn = taskButtons.querySelector('.task-btn[data-win-id="win-1"]');
        registerWindow(winTemplate, initialTaskBtn, winTemplate.dataset.href || window.location.pathname);
        var initialAddr = winTemplate.querySelector('.addr-input');
        if (initialAddr) initialAddr.value = window.location.href;
        activeId = 'win-1';
        windows['win-1'].el.style.zIndex = String(++zCounter);
    }

    window.addEventListener('resize', function () {
        if (winTemplate && Object.keys(windows).length === 1 && !windows['win-1'].maximized && !winTemplate.dataset.moved) {
            placeInitial(winTemplate);
        }
    });
})();
