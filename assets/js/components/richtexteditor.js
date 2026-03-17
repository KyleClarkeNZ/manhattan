/**
 * Manhattan RichTextEditor Component
 *
 * contenteditable-based rich text editor with a customisable toolbar.
 * Outputs clean semantic HTML with <p> blocks.
 *
 * Auto-initialises all [data-component="richtexteditor"] elements on DOMContentLoaded.
 *
 * Keyboard shortcuts:
 *   Ctrl/Cmd + B  — Bold
 *   Ctrl/Cmd + I  — Italic
 *   Ctrl/Cmd + U  — Underline
 *   Ctrl/Cmd + Z  — Undo
 *   Ctrl/Cmd + Shift + Z  — Redo
 *   Ctrl + Y (Windows/Linux)  — Redo
 *
 * Events:
 *   m:rte:change  — fired on the container when content changes
 *                   detail: { value: string (html) }
 *   m:rte:focus   — fired when the editor gains focus
 *   m:rte:blur    — fired when the editor loses focus
 *
 * JS API:
 *   var rte = m.richTextEditor('myEditor');
 *   rte.getValue()           — current HTML string
 *   rte.setValue(html)       — set content programmatically
 *   rte.focus()              — focus the editing area
 *   rte.execCommand(cmd, v)  — execute a toolbar command
 */
(function (window) {
    'use strict';

    var m = window.m;
    var utils = m.utils;

    m.richTextEditor = function (id) {
        var container = utils.getElement(id);
        if (!container) { return null; }

        // Prevent double-init
        if (container._mRte) { return container._mRte; }

        var toolbar     = container.querySelector('.m-rte-toolbar');
        var content     = container.querySelector('.m-rte-content');
        var hiddenInput = container.querySelector('.m-rte-hidden-input');
        var charCountEl = container.querySelector('.m-rte-char-count');

        if (!content) { return null; }

        var isReadOnly    = container.getAttribute('data-read-only') === 'true';
        var placeholder   = content.getAttribute('data-placeholder') || '';
        var minChars      = parseInt(container.getAttribute('data-min-chars'), 10) || 0;
        var maxChars      = parseInt(container.getAttribute('data-max-chars'), 10) || 0;

        // --- Paragraph separator ---
        try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) { /* ignore */ }

        // Initialise: ensure there is at least one paragraph
        if (content.innerHTML.trim() === '' || content.innerHTML === '<br>') {
            content.innerHTML = '<p><br></p>';
        }

        // =========================================================
        // Core helpers
        // =========================================================

        function getValue() {
            return content.innerHTML;
        }

        function setValue(html) {
            content.innerHTML = html && html.trim() ? html : '<p><br></p>';
            syncHidden();
            updateCharCount();
            updateToolbarState();
        }

        function syncHidden() {
            if (hiddenInput) {
                hiddenInput.value = getValue();
            }
        }

        function updateCharCount() {
            if (!charCountEl) { return; }
            var text  = content.innerText || content.textContent || '';
            // Trim trailing newline that browsers add for the trailing <br>
            text = text.replace(/\n$/, '');
            var count = text.length;

            // Format display text
            var charText;
            if (maxChars > 0) {
                charText = count + ' / ' + maxChars;
            } else {
                charText = count + (count === 1 ? ' character' : ' characters');
            }
            charCountEl.textContent = charText;

            // Colour states
            charCountEl.classList.remove('m-rte-char-warn', 'm-rte-char-error');

            var isValid   = true;
            var errorMsg  = '';

            if (maxChars > 0 && count > maxChars) {
                isValid  = false;
                errorMsg = 'Maximum ' + maxChars + ' characters exceeded (' + count + ' / ' + maxChars + ')';
                charCountEl.classList.add('m-rte-char-error');
            } else if (maxChars > 0 && count >= Math.round(maxChars * 0.9)) {
                charCountEl.classList.add('m-rte-char-warn');
            }

            if (minChars > 0 && count < minChars) {
                isValid  = false;
                var remaining = minChars - count;
                errorMsg = remaining + ' more character' + (remaining === 1 ? '' : 's') + ' needed (minimum ' + minChars + ')';
                charCountEl.classList.add('m-rte-char-error');
            }

            if (isValid) {
                clearCharError();
            } else {
                showCharError(errorMsg);
            }
        }

        function showCharError(message) {
            var err = container._mRteCharError;
            if (!err) {
                err = document.createElement('div');
                err.className = 'm-validator-error m-rte-char-error';
                container._mRteCharError = err;
                container.parentNode.insertBefore(err, container.nextSibling);
            }
            err.textContent = message;
            err.style.display = 'flex';
            if (hiddenInput) { hiddenInput.classList.add('m-validator-invalid'); }
            container.classList.add('m-richtexteditor-invalid');
        }

        function clearCharError() {
            var err = container._mRteCharError;
            if (err) { err.style.display = 'none'; }
            if (hiddenInput) { hiddenInput.classList.remove('m-validator-invalid'); }
            container.classList.remove('m-richtexteditor-invalid');
        }

        // =========================================================
        // Toolbar state (reflect selection)
        // =========================================================

        function updateToolbarState() {
            if (!toolbar) { return; }

            var toggleMap = {
                'bold':          'bold',
                'italic':        'italic',
                'underline':     'underline',
                'strikethrough': 'strikeThrough',
            };

            Object.keys(toggleMap).forEach(function (cmd) {
                var btn = toolbar.querySelector('[data-rte-command="' + cmd + '"]');
                if (!btn) { return; }
                try {
                    var active = document.queryCommandState(toggleMap[cmd]);
                    btn.classList.toggle('m-button-group-active', active);
                } catch (e) { /* ignore */ }
            });

            var alignMap = {
                'alignLeft':   'justifyLeft',
                'alignCenter': 'justifyCenter',
                'alignRight':  'justifyRight',
                'alignFull':   'justifyFull',
            };

            Object.keys(alignMap).forEach(function (cmd) {
                var btn = toolbar.querySelector('[data-rte-command="' + cmd + '"]');
                if (!btn) { return; }
                try {
                    var active = document.queryCommandState(alignMap[cmd]);
                    btn.classList.toggle('m-button-group-active', active);
                } catch (e) { /* ignore */ }
            });

            // Heading dropdown: reflect current block format in trigger label
            var headingDropdown = toolbar.querySelector('[data-rte-dropdown="heading"]');
            if (headingDropdown) {
                try {
                    var fmt = document.queryCommandValue('formatBlock').toLowerCase();
                    // Normalise — some browsers prefix with '<'
                    fmt = fmt.replace(/[<>]/g, '');
                    if (!fmt || fmt === 'div' || fmt === 'normal') { fmt = 'p'; }
                    var headingLabelMap = { p: 'Normal', h1: 'Heading 1', h2: 'Heading 2', h3: 'Heading 3', h4: 'Heading 4' };
                    var labelEl = headingDropdown.querySelector('.m-rte-dropdown-label');
                    if (labelEl) { labelEl.textContent = headingLabelMap[fmt] || 'Normal'; }
                    // Mark active item
                    headingDropdown.querySelectorAll('.m-rte-dropdown-item').forEach(function (item) {
                        item.classList.toggle('m-rte-dropdown-item-active', item.getAttribute('data-rte-value') === fmt);
                    });
                } catch (e) { /* ignore */ }
            }
        }

        // =========================================================
        // Font-size helper — avoids <font> tags
        // =========================================================

        var FONT_SIZE_MAP = {
            '1': '10px',
            '2': '13px',
            '3': '16px',
            '4': '18px',
            '5': '24px',
            '6': '32px',
            '7': '48px',
        };

        function applyFontSize(sizeValue) {
            var px = FONT_SIZE_MAP[sizeValue] || '16px';
            var sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) { return; }
            var range = sel.getRangeAt(0);
            if (range.collapsed) { return; }

            var span = document.createElement('span');
            span.style.fontSize = px;

            try {
                range.surroundContents(span);
            } catch (e) {
                // Fallback for partial cross-element selections: use a
                // marker-based approach via execCommand, then replace the <font>
                document.execCommand('fontSize', false, '7');
                var fontEls = content.querySelectorAll('font[size="7"]');
                fontEls.forEach(function (font) {
                    var s = document.createElement('span');
                    s.style.fontSize = px;
                    while (font.firstChild) { s.appendChild(font.firstChild); }
                    if (font.parentNode) { font.parentNode.replaceChild(s, font); }
                });
            }
        }

        // =========================================================
        // Execute a toolbar command
        // =========================================================

        function execCmd(command, value) {
            if (isReadOnly) { return; }
            content.focus();

            switch (command) {
                case 'bold':
                    document.execCommand('bold', false, null);
                    break;
                case 'italic':
                    document.execCommand('italic', false, null);
                    break;
                case 'underline':
                    document.execCommand('underline', false, null);
                    break;
                case 'strikethrough':
                    document.execCommand('strikeThrough', false, null);
                    break;

                case 'alignLeft':
                    document.execCommand('justifyLeft', false, null);
                    break;
                case 'alignCenter':
                    document.execCommand('justifyCenter', false, null);
                    break;
                case 'alignRight':
                    document.execCommand('justifyRight', false, null);
                    break;
                case 'alignFull':
                    document.execCommand('justifyFull', false, null);
                    break;

                case 'orderedList':
                    document.execCommand('insertOrderedList', false, null);
                    break;
                case 'bulletList':
                    document.execCommand('insertUnorderedList', false, null);
                    break;

                case 'heading':
                    if (value) {
                        document.execCommand('formatBlock', false, value === 'p' ? 'p' : value);
                    }
                    break;

                case 'fontSize':
                    if (value) { applyFontSize(value); }
                    break;

                case 'foreColor':
                    if (value) { document.execCommand('foreColor', false, value); }
                    break;

                case 'link': {
                    openLinkDialog();
                    break;
                }

                case 'undo':
                    document.execCommand('undo', false, null);
                    break;
                case 'redo':
                    document.execCommand('redo', false, null);
                    break;

                case 'clearFormat':
                    document.execCommand('removeFormat', false, null);
                    // Also strip heading level
                    document.execCommand('formatBlock', false, 'p');
                    break;
            }

            syncHidden();
            updateCharCount();
            updateToolbarState();
            utils.trigger(container, 'm:rte:change', { value: getValue() });
        }

        // =========================================================
        // Keyboard shortcuts (cross-platform)
        // =========================================================

        content.addEventListener('keydown', function (e) {
            var isMac = /Mac|iPod|iPhone|iPad/.test(navigator.platform);
            var mod   = isMac ? e.metaKey : e.ctrlKey;
            if (!mod) { return; }

            switch (e.key.toLowerCase()) {
                case 'b':
                    e.preventDefault();
                    execCmd('bold');
                    break;
                case 'i':
                    e.preventDefault();
                    execCmd('italic');
                    break;
                case 'u':
                    e.preventDefault();
                    execCmd('underline');
                    break;
                case 'z':
                    if (e.shiftKey) {
                        e.preventDefault();
                        execCmd('redo');
                    }
                    // Ctrl/Cmd+Z without shift: let the browser's native undo
                    // fire (it integrates with execCommand history).
                    break;
                case 'y':
                    if (!isMac) {
                        e.preventDefault();
                        execCmd('redo');
                    }
                    break;
            }
        });

        // =========================================================
        // Toolbar interactions
        // =========================================================

        if (toolbar) {
            // Prevent toolbar clicks from blurring the editor
            toolbar.addEventListener('mousedown', function (e) {
                // Allow color input to receive normal pointer events
                if (e.target.tagName === 'INPUT' && e.target.type === 'color') { return; }
                e.preventDefault();
            });

            // Button clicks + dropdown routing
            toolbar.addEventListener('click', function (e) {
                // Dropdown trigger — toggle the panel
                var trigger = e.target.closest('.m-rte-dropdown-trigger');
                if (trigger) {
                    e.preventDefault();
                    var dd    = trigger.closest('.m-rte-dropdown');
                    var panel = dd && dd.querySelector('.m-rte-dropdown-panel');
                    if (panel) {
                        var wasOpen = !panel.hidden;
                        closeAllDropdowns();
                        if (!wasOpen) { openDropdown(dd); }
                    }
                    return;
                }

                // Dropdown item — execute command and close
                var item = e.target.closest('.m-rte-dropdown-item');
                if (item) {
                    var iCmd = item.getAttribute('data-rte-command');
                    var iVal = item.getAttribute('data-rte-value') || null;
                    if (iCmd) { execCmd(iCmd, iVal); }
                    closeAllDropdowns();
                    return;
                }

                // Regular tool button
                var btn = e.target.closest('[data-rte-command]');
                if (!btn) { return; }
                // Ignore the color-custom input (handled separately)
                if (btn.tagName === 'INPUT') { return; }
                var command = btn.getAttribute('data-rte-command');
                var value   = btn.getAttribute('data-rte-value') || null;
                if (command) { execCmd(command, value); }
            });

            // Color picker — toggle panel
            var colorBtn   = toolbar.querySelector('.m-rte-color-btn');
            var colorPanel = toolbar.querySelector('.m-rte-color-panel');
            var colorSwatch = toolbar.querySelector('.m-rte-color-btn .m-rte-color-swatch');
            var colorCustom = toolbar.querySelector('.m-rte-color-custom');

            if (colorBtn && colorPanel) {
                colorBtn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    colorPanel.hidden = !colorPanel.hidden;
                });

                // After applying a preset, update the swatch indicator and close
                colorPanel.addEventListener('click', function (e) {
                    var swatchBtn = e.target.closest('.m-rte-color-swatch-btn');
                    if (!swatchBtn) { return; }
                    var color = swatchBtn.getAttribute('data-rte-value');
                    if (color) {
                        if (colorSwatch) { colorSwatch.style.background = color; }
                        colorPanel.hidden = true;
                    }
                });

                // Custom color input
                if (colorCustom) {
                    colorCustom.addEventListener('mousedown', function (e) {
                        e.stopPropagation(); // Don't close the panel
                    });
                    colorCustom.addEventListener('change', function () {
                        var color = colorCustom.value;
                        execCmd('foreColor', color);
                        if (colorSwatch) { colorSwatch.style.background = color; }
                        colorPanel.hidden = true;
                    });
                }

                // Close when clicking outside
                document.addEventListener('click', function (e) {
                    if (colorPanel && !colorPanel.hidden && !container.contains(e.target)) {
                        colorPanel.hidden = true;
                    }
                    if (toolbar && !container.contains(e.target)) {
                        closeAllDropdowns();
                    }
                });
            }

            // =========================================================
            // Toolbar dropdown helpers
            // =========================================================

            function openDropdown(dropdown) {
                var panel   = dropdown.querySelector('.m-rte-dropdown-panel');
                var triggerEl = dropdown.querySelector('.m-rte-dropdown-trigger');
                if (panel)   { panel.hidden = false; }
                if (triggerEl) { triggerEl.setAttribute('aria-expanded', 'true'); }
            }

            function closeAllDropdowns() {
                if (!toolbar) { return; }
                toolbar.querySelectorAll('.m-rte-dropdown').forEach(function (dd) {
                    var panel   = dd.querySelector('.m-rte-dropdown-panel');
                    var triggerEl = dd.querySelector('.m-rte-dropdown-trigger');
                    if (panel)   { panel.hidden = true; }
                    if (triggerEl) { triggerEl.setAttribute('aria-expanded', 'false'); }
                });
            }
        }

        // =========================================================
        // Link dialog
        // =========================================================

        var linkDialog    = container.querySelector('.m-rte-link-dialog');
        var linkUrlInput  = linkDialog && linkDialog.querySelector('.m-rte-link-url');
        var linkNewTabChk = linkDialog && linkDialog.querySelector('.m-rte-link-newtab-check');
        var linkInsertBtn = linkDialog && linkDialog.querySelector('.m-rte-link-insert');
        var linkCancelBtn = linkDialog && linkDialog.querySelector('.m-rte-link-cancel');
        var linkCloseBtn  = linkDialog && linkDialog.querySelector('.m-rte-link-close');
        var linkBackdrop  = linkDialog && linkDialog.querySelector('.m-rte-link-backdrop');

        var savedRange = null;

        function openLinkDialog() {
            if (!linkDialog) { return; }

            // Save the current selection so we can restore it before inserting
            var sel = window.getSelection();
            savedRange = (sel && sel.rangeCount > 0) ? sel.getRangeAt(0).cloneRange() : null;

            // Pre-fill if the selection is already inside an <a>
            var existingUrl = '';
            if (savedRange) {
                var node = savedRange.commonAncestorContainer;
                var anchor = (node.nodeType === 3 ? node.parentNode : node).closest('a');
                if (anchor) { existingUrl = anchor.getAttribute('href') || ''; }
            }

            if (linkUrlInput) {
                linkUrlInput.value = existingUrl || 'https://';
            }

            linkDialog.hidden = false;
            setTimeout(function () {
                if (linkUrlInput) { linkUrlInput.focus(); linkUrlInput.select(); }
            }, 50);
        }

        function closeLinkDialog(restoreSelection) {
            if (!linkDialog) { return; }
            linkDialog.hidden = true;
            if (restoreSelection && savedRange) {
                content.focus();
                var sel = window.getSelection();
                if (sel) {
                    sel.removeAllRanges();
                    sel.addRange(savedRange);
                }
            }
        }

        function insertLink() {
            if (!linkUrlInput) { return; }
            var url = linkUrlInput.value.trim();
            if (!url || url === 'https://') { closeLinkDialog(false); return; }

            closeLinkDialog(true);

            document.execCommand('createLink', false, url);

            var isNewTab = linkNewTabChk && linkNewTabChk.checked;
            content.querySelectorAll('a[href="' + url + '"]').forEach(function (a) {
                if (isNewTab) {
                    a.setAttribute('target', '_blank');
                    a.setAttribute('rel', 'noopener noreferrer');
                }
            });

            syncHidden();
            updateCharCount();
            updateToolbarState();
            utils.trigger(container, 'm:rte:change', { value: getValue() });
        }

        if (linkDialog) {
            if (linkInsertBtn) { linkInsertBtn.addEventListener('click', insertLink); }
            if (linkCancelBtn) { linkCancelBtn.addEventListener('click', function () { closeLinkDialog(false); }); }
            if (linkCloseBtn)  { linkCloseBtn.addEventListener('click',  function () { closeLinkDialog(false); }); }
            if (linkBackdrop)  { linkBackdrop.addEventListener('click',  function () { closeLinkDialog(false); }); }
            if (linkUrlInput) {
                linkUrlInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') { e.preventDefault(); closeLinkDialog(false); }
                    if (e.key === 'Enter')  { e.preventDefault(); insertLink(); }
                });
            }
        }

        // =========================================================
        // Content area events
        // =========================================================

        content.addEventListener('input', function () {
            // Guard against completely empty editor
            var inner = content.innerHTML;
            if (inner === '' || inner === '<br>' || inner === '<br/>') {
                content.innerHTML = '<p><br></p>';
                // Restore cursor
                var p   = content.querySelector('p');
                var sel = window.getSelection();
                if (p && sel) {
                    var range = document.createRange();
                    range.setStart(p, 0);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
            syncHidden();
            updateCharCount();
            utils.trigger(container, 'm:rte:change', { value: getValue() });
        });

        content.addEventListener('focus', function () {
            container.classList.add('m-richtexteditor-focused');
            utils.trigger(container, 'm:rte:focus', {});
        });

        content.addEventListener('blur', function () {
            container.classList.remove('m-richtexteditor-focused');
            utils.trigger(container, 'm:rte:blur', {});
        });

        // Update toolbar active state whenever the selection moves
        document.addEventListener('selectionchange', function () {
            var sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) { return; }
            var range = sel.getRangeAt(0);
            if (content.contains(range.commonAncestorContainer)) {
                updateToolbarState();
            }
        });

        // =========================================================
        // Paste handler — strip unsafe / undesirable HTML
        // =========================================================

        content.addEventListener('paste', function (e) {
            e.preventDefault();
            var clipData = e.clipboardData || window.clipboardData;
            var html  = clipData ? clipData.getData('text/html') : '';
            var text  = clipData ? clipData.getData('text/plain') : '';

            if (html) {
                html = cleanPastedHtml(html);
                document.execCommand('insertHTML', false, html);
            } else if (text) {
                // Convert plain text — each line becomes a <p>
                var lines     = text.split(/\r?\n/);
                var pasteHtml = lines.map(function (line) {
                    return '<p>' + escapeHtml(line || '\u200B') + '</p>';
                }).join('');
                document.execCommand('insertHTML', false, pasteHtml);
            }

            syncHidden();
            updateCharCount();
            utils.trigger(container, 'm:rte:change', { value: getValue() });
        });

        // =========================================================
        // Paste helpers
        // =========================================================

        function cleanPastedHtml(html) {
            var tmp = document.createElement('div');
            tmp.innerHTML = html;

            // Remove unsafe or noisy elements
            tmp.querySelectorAll(
                'script, style, meta, link, head, ' +
                '[class*="Mso"], [style*="mso-"], [lang], ' +
                'o\\:p'
            ).forEach(function (el) {
                var parent = el.parentNode;
                if (parent) { parent.removeChild(el); }
            });

            // Strip class/id attributes; sanitise inline styles
            tmp.querySelectorAll('*').forEach(function (el) {
                el.removeAttribute('class');
                el.removeAttribute('id');
                el.removeAttribute('lang');
                el.removeAttribute('dir');

                var style = el.getAttribute('style');
                if (style) {
                    var cleaned = sanitiseStyle(style);
                    if (cleaned) {
                        el.setAttribute('style', cleaned);
                    } else {
                        el.removeAttribute('style');
                    }
                }
            });

            return tmp.innerHTML;
        }

        var ALLOWED_STYLE_PROPS = [
            'font-weight', 'font-style', 'text-decoration', 'color',
            'font-size', 'text-align', 'background-color',
        ];

        function sanitiseStyle(style) {
            var kept = [];
            style.split(';').forEach(function (part) {
                var idx = part.indexOf(':');
                if (idx === -1) { return; }
                var prop = part.substring(0, idx).trim().toLowerCase();
                if (ALLOWED_STYLE_PROPS.indexOf(prop) !== -1) {
                    kept.push(part.trim());
                }
            });
            return kept.join('; ');
        }

        function escapeHtml(str) {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // =========================================================
        // Initial state
        // =========================================================

        syncHidden();
        updateCharCount();

        // =========================================================
        // Public API
        // =========================================================

        var api = {
            getValue:    getValue,
            setValue:    setValue,
            focus:       function () { content.focus(); },
            execCommand: execCmd,
        };

        container._mRte = api;
        return api;
    };

    // Auto-init on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-component="richtexteditor"]').forEach(function (el) {
            if (el.id) { m.richTextEditor(el.id); }
        });
    });

}(window));
