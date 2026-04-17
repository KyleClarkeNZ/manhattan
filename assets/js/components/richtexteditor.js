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
 *   Tab (in list)  — Indent list item (create sub-list)
 *   Shift+Tab (in list)  — Outdent list item (promote back up)
 *   Tab (outside list)  — Insert visual indent (4 non-breaking spaces)
 *
 * Events:
 *   m:rte:change       — fired on the container when content changes
 *                        detail: { value: string (html) }
 *   m:rte:focus        — fired when the editor gains focus
 *   m:rte:blur         — fired when the editor loses focus
 *   m:rte:error        — fired when an error occurs (e.g. upload failure)
 *                        detail: { message: string }
 *   m:rte:upload:start — fired when an image upload begins
 *   m:rte:upload:end   — fired when an image upload completes (success or failure)
 *                        detail: { success: bool, url: string|null, error: string|null }
 *
 * JS API:
 *   var rte = m.richTextEditor('myEditor');
 *   rte.getValue()           — current HTML string
 *   rte.setValue(html)       — set content programmatically
 *   rte.focus()              — focus the editing area
 *   rte.execCommand(cmd, v)  — execute a toolbar command
 *   rte.insertImage(url, alt) — insert an image at the current cursor position
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

        var isReadOnly       = container.getAttribute('data-read-only') === 'true';
        var placeholder      = content.getAttribute('data-placeholder') || '';
        var minChars         = parseInt(container.getAttribute('data-min-chars'), 10) || 0;
        var maxChars         = parseInt(container.getAttribute('data-max-chars'), 10) || 0;
        var uploaderUrl      = container.getAttribute('data-uploader-url') || '';
        var uploaderStem     = container.getAttribute('data-uploader-stem') || '';
        var allowPasteImages = container.getAttribute('data-allow-paste-images') === 'true';
        var allowImageResize = container.getAttribute('data-allow-image-resize') === 'true';

        // --- Paragraph separator ---
        try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) { /* ignore */ }

        // Initialise: ensure there is at least one paragraph
        if (content.innerHTML.trim() === '' || content.innerHTML === '<br>') {
            content.innerHTML = '<p><br></p>';
        }

        // =========================================================
        // Image upload & insert helpers
        // =========================================================

        function showRteError(message) {
            var toasterEl = document.querySelector('[data-component="toaster"]');
            if (toasterEl && toasterEl.id) {
                try { m.toaster(toasterEl.id).show(message, 'error'); } catch (e) { /* ignore */ }
            }
            utils.trigger(container, 'm:rte:error', { message: message });
        }

        function insertImageHtmlAtCursor(url, alt) {
            var safeAlt = (alt || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            content.focus();
            document.execCommand('insertHTML', false, '<img src="' + url + '" alt="' + safeAlt + '">');
            syncHidden();
            updateCharCount();
            utils.trigger(container, 'm:rte:change', { value: getValue() });
        }
        function uploadImage(file, callback, onProgress) {
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrf     = csrfMeta ? (csrfMeta.getAttribute('content') || '') : '';

            var formData = new FormData();
            formData.append('image', file);
            if (uploaderStem) {
                formData.append('stem', uploaderStem);
            }

            utils.trigger(container, 'm:rte:upload:start', {});

            var xhr = new XMLHttpRequest();
            xhr.open('POST', uploaderUrl, true);
            if (csrf) { xhr.setRequestHeader('X-CSRF-Token', csrf); }

            if (onProgress) {
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        onProgress(Math.round(e.loaded / e.total * 100));
                    }
                });
            }

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        if (result && result.url) {
                            callback(null, result.url);
                            utils.trigger(container, 'm:rte:upload:end', { success: true, url: result.url, error: null, response: result });
                        } else {
                            var msg = (result && result.message) ? result.message : 'Invalid response from upload server';
                            utils.trigger(container, 'm:rte:upload:end', { success: false, url: null, error: msg });
                            callback(msg);
                        }
                    } catch (e) {
                        var parseErr = 'Upload server returned an invalid response';
                        utils.trigger(container, 'm:rte:upload:end', { success: false, url: null, error: parseErr });
                        callback(parseErr);
                    }
                } else {
                    var httpErr = 'Upload failed (HTTP ' + xhr.status + ')';
                    utils.trigger(container, 'm:rte:upload:end', { success: false, url: null, error: httpErr });
                    callback(httpErr);
                }
            };

            xhr.onerror = function () {
                var netErr = 'Image upload failed — network error';
                utils.trigger(container, 'm:rte:upload:end', { success: false, url: null, error: netErr });
                callback(netErr);
            };

            xhr.send(formData);
        }

        // =========================================================
        // Image selection, alignment & resize
        // =========================================================

        var imageBody        = container.querySelector('.m-rte-body');
        var selectedImg      = null;
        var resizerEl        = null;
        var resizerBarEl     = null;
        var imgAltInput      = null;
        var uploadOverlayEl  = null;
        var uploadProgressEl = null;
        var uploadingImg     = null;

        // YouTube wrapper selection & resize
        var selectedYt   = null;
        var ytResizerEl  = null;
        var ytDragState  = null;

        // Build the resizer overlay element (created once, reused)
        (function buildResizer() {
            resizerEl = document.createElement('div');
            resizerEl.className = 'm-rte-image-resizer';
            resizerEl.setAttribute('hidden', '');

            // Alignment mini-toolbar (always present when an image is selected)
            resizerBarEl = document.createElement('div');
            resizerBarEl.className = 'm-rte-image-resizer-bar';
            resizerBarEl.innerHTML =
                '<button type="button" class="m-rte-imgbar-btn" data-img-align="left"   title="Align left">  <i class="fas fa-align-left"    aria-hidden="true"></i></button>' +
                '<button type="button" class="m-rte-imgbar-btn" data-img-align="center" title="Align center"><i class="fas fa-align-center"  aria-hidden="true"></i></button>' +
                '<button type="button" class="m-rte-imgbar-btn" data-img-align="right"  title="Align right"> <i class="fas fa-align-right"   aria-hidden="true"></i></button>' +
                '<div class="m-rte-imgbar-sep" aria-hidden="true"></div>' +
                '<input type="text" class="m-rte-imgbar-alt" placeholder="Alt text\u2026" aria-label="Image alt text" title="Alt text">' +
                '<div class="m-rte-imgbar-sep" aria-hidden="true"></div>' +
                '<button type="button" class="m-rte-imgbar-btn m-rte-imgbar-btn-remove" data-img-remove="true" title="Remove image" aria-label="Remove image"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>';
            imgAltInput = resizerBarEl.querySelector('.m-rte-imgbar-alt');
            resizerEl.appendChild(resizerBarEl);

            // Resize handles (8-point, only interactive when allowImageResize)
            if (allowImageResize) {
                var handles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
                handles.forEach(function (pos) {
                    var h = document.createElement('div');
                    h.className = 'm-rte-resize-handle';
                    h.setAttribute('data-handle', pos);
                    resizerEl.appendChild(h);
                });
            }

            if (imageBody) {
                imageBody.appendChild(resizerEl);
            }
        }());

        // Build the upload progress overlay (one instance, reused while uploading)
        (function buildUploadOverlay() {
            uploadOverlayEl = document.createElement('div');
            uploadOverlayEl.className = 'm-rte-upload-overlay';
            uploadOverlayEl.setAttribute('hidden', '');

            var track = document.createElement('div');
            track.className = 'm-rte-upload-progress-track';

            uploadProgressEl = document.createElement('div');
            uploadProgressEl.className = 'm-rte-upload-progress-bar m-rte-progress-indeterminate';

            track.appendChild(uploadProgressEl);
            uploadOverlayEl.appendChild(track);

            if (imageBody) { imageBody.appendChild(uploadOverlayEl); }
        }());

        // Build the YouTube resizer overlay (created once, reused)
        (function buildYtResizer() {
            ytResizerEl = document.createElement('div');
            ytResizerEl.className = 'm-rte-yt-resizer';
            ytResizerEl.setAttribute('hidden', '');

            ['w', 'e'].forEach(function (pos) {
                var h = document.createElement('div');
                h.className = 'm-rte-yt-resize-handle';
                h.setAttribute('data-yt-handle', pos);
                ytResizerEl.appendChild(h);
            });

            if (imageBody) { imageBody.appendChild(ytResizerEl); }
        }());

        function showUploadOverlay(img) {
            if (!uploadOverlayEl || !imageBody) { return; }
            uploadingImg = img;

            function positionOverlay() {
                var bodyRect   = imageBody.getBoundingClientRect();
                var imgRect    = img.getBoundingClientRect();
                var scrollTop  = imageBody.scrollTop  || 0;
                var scrollLeft = imageBody.scrollLeft || 0;
                uploadOverlayEl.style.top    = (imgRect.top  - bodyRect.top  + scrollTop)  + 'px';
                uploadOverlayEl.style.left   = (imgRect.left - bodyRect.left + scrollLeft) + 'px';
                uploadOverlayEl.style.width  = (imgRect.width  || 120) + 'px';
                uploadOverlayEl.style.height = (imgRect.height || 80)  + 'px';
            }

            if (img.complete && img.naturalWidth) {
                positionOverlay();
            } else {
                img.addEventListener('load', function onLoad() {
                    img.removeEventListener('load', onLoad);
                    positionOverlay();
                });
            }

            if (uploadProgressEl) {
                uploadProgressEl.classList.add('m-rte-progress-indeterminate');
                uploadProgressEl.style.width = '';
            }
            uploadOverlayEl.removeAttribute('hidden');
        }

        function updateUploadProgress(percent) {
            if (!uploadProgressEl) { return; }
            uploadProgressEl.classList.remove('m-rte-progress-indeterminate');
            uploadProgressEl.style.width = Math.min(100, percent) + '%';
        }

        function hideUploadOverlay() {
            if (uploadOverlayEl) { uploadOverlayEl.setAttribute('hidden', ''); }
            uploadingImg = null;
        }

        function positionResizer(img) {
            if (!resizerEl || !imageBody) { return; }
            // Position relative to .m-rte-body
            var bodyRect   = imageBody.getBoundingClientRect();
            var imgRect    = img.getBoundingClientRect();
            var scrollTop  = imageBody.scrollTop  || 0;
            var scrollLeft = imageBody.scrollLeft || 0;
            var top  = imgRect.top  - bodyRect.top  + scrollTop;
            var left = imgRect.left - bodyRect.left + scrollLeft;
            resizerEl.style.top    = top  + 'px';
            resizerEl.style.left   = left + 'px';
            resizerEl.style.width  = imgRect.width  + 'px';
            resizerEl.style.height = imgRect.height + 'px';

            // Reflect current alignment on mini-toolbar buttons
            var currentAlign = getImageAlign(img);
            resizerBarEl.querySelectorAll('.m-rte-imgbar-btn').forEach(function (btn) {
                btn.classList.toggle('m-rte-imgbar-btn-active', btn.getAttribute('data-img-align') === currentAlign);
            });
        }

        function getImageAlign(img) {
            if (!img) { return 'left'; }
            var fl = img.style.cssFloat || img.style.float || '';
            if (fl === 'right') { return 'right'; }
            if (fl === 'left')  { return 'left'; }
            // Check parent for text-align:center (set by applyImageAlign for belt-and-suspenders)
            var parent = img.parentNode;
            if (parent && parent.style && parent.style.textAlign === 'center') { return 'center'; }
            // Fallback: display:block + margin:auto approach
            var disp   = img.style.display     || '';
            var mLeft  = img.style.marginLeft  || '';
            var mRight = img.style.marginRight || '';
            if (disp === 'block' && mLeft === 'auto' && mRight === 'auto') { return 'center'; }
            return 'left';
        }

        function applyImageAlign(img, align) {
            if (!img) { return; }
            // Clear all alignment inline styles
            img.style.cssFloat     = '';
            img.style.float        = '';
            img.style.display      = '';
            img.style.marginLeft   = '';
            img.style.marginRight  = '';
            img.style.marginBottom = '';

            // Clear any text-align previously set on the parent block
            var parent = img.parentNode;
            if (parent && parent !== content) {
                parent.style.textAlign = '';
            }

            switch (align) {
                case 'left':
                    // Float left — text wraps to the right of the image
                    img.style.cssFloat     = 'left';
                    img.style.marginRight  = '1em';
                    img.style.marginBottom = '0.5em';
                    break;
                case 'center':
                    // Block-centred — no text wrapping.
                    // display:block + margin:auto works for images narrower than the
                    // container; text-align:center on the parent is the belt-and-suspenders
                    // fallback that covers images constrained to 100% by max-width.
                    img.style.display     = 'block';
                    img.style.marginLeft  = 'auto';
                    img.style.marginRight = 'auto';
                    if (parent && parent !== content) {
                        parent.style.textAlign = 'center';
                    }
                    break;
                case 'right':
                    // Float right — text wraps to the left of the image
                    img.style.cssFloat     = 'right';
                    img.style.marginLeft   = '1em';
                    img.style.marginBottom = '0.5em';
                    break;
                default:
                    // Inline flow — no explicit alignment
                    break;
            }
            syncHidden();
            utils.trigger(container, 'm:rte:change', { value: getValue() });
        }

        function selectImage(img) {
            if (isReadOnly) { return; }
            if (img.getAttribute('data-rte-uploading')) { return; } // still uploading
            // Set browser selection to encapsulate the image
            content.focus();
            var sel = window.getSelection();
            if (sel) {
                var range = document.createRange();
                range.selectNode(img);
                sel.removeAllRanges();
                sel.addRange(range);
            }
            selectedImg = img;

            // Store original natural dimensions the first time we touch this image
            if (!img.getAttribute('data-original-width') && img.naturalWidth) {
                img.setAttribute('data-original-width',  img.naturalWidth);
                img.setAttribute('data-original-height', img.naturalHeight);
            }
            // Always clear inline height so the browser auto-sizes from the width
            img.style.height = '';

            // Visual selection indicator
            content.querySelectorAll('img').forEach(function (i) { i.classList.remove('m-rte-img-selected'); });
            img.classList.add('m-rte-img-selected');

            // Populate alt text field
            if (imgAltInput) { imgAltInput.value = img.getAttribute('alt') || ''; }

            // Show overlay
            resizerEl.removeAttribute('hidden');
            positionResizer(img);
            updateToolbarState();
        }

        function dismissImageSelection() {
            if (selectedImg) {
                selectedImg.classList.remove('m-rte-img-selected');
                selectedImg = null;
            }
            if (resizerEl) { resizerEl.setAttribute('hidden', ''); }
        }

        // ---- YouTube wrapper helpers ----

        function addYtClickShields() {
            if (isReadOnly) { return; }
            content.querySelectorAll('.m-rte-youtube-wrapper').forEach(function (wrapper) {
                // Ensure contenteditable=false is always present — it can be
                // stripped by server-side HTML sanitizers when a post is saved
                // and re-loaded for editing.
                if (wrapper.getAttribute('contenteditable') !== 'false') {
                    wrapper.setAttribute('contenteditable', 'false');
                }
                if (!wrapper.querySelector('.m-rte-yt-click-shield')) {
                    var shield = document.createElement('div');
                    shield.className = 'm-rte-yt-click-shield';
                    wrapper.appendChild(shield);
                }
            });
        }

        function positionYtResizer(wrapper) {
            if (!ytResizerEl || !imageBody) { return; }
            var bodyRect   = imageBody.getBoundingClientRect();
            var wrapRect   = wrapper.getBoundingClientRect();
            var scrollTop  = imageBody.scrollTop  || 0;
            var scrollLeft = imageBody.scrollLeft || 0;
            ytResizerEl.style.top    = (wrapRect.top  - bodyRect.top  + scrollTop)  + 'px';
            ytResizerEl.style.left   = (wrapRect.left - bodyRect.left + scrollLeft) + 'px';
            ytResizerEl.style.width  = wrapRect.width  + 'px';
            ytResizerEl.style.height = wrapRect.height + 'px';
        }

        function selectYtWrapper(wrapper) {
            if (isReadOnly) { return; }
            content.querySelectorAll('.m-rte-youtube-wrapper').forEach(function (w) {
                w.classList.remove('m-rte-yt-selected');
            });
            selectedYt = wrapper;
            wrapper.classList.add('m-rte-yt-selected');
            ytResizerEl.removeAttribute('hidden');
            positionYtResizer(wrapper);
        }

        function dismissYtSelection() {
            if (selectedYt) {
                selectedYt.classList.remove('m-rte-yt-selected');
                selectedYt = null;
            }
            if (ytResizerEl) { ytResizerEl.setAttribute('hidden', ''); }
        }

        // Click inside the editor — select image/youtube-wrapper or dismiss
        content.addEventListener('click', function (e) {
            var img = e.target.nodeName === 'IMG' ? e.target : null;
            // The click shield sits on top of the iframe; clicking it selects the wrapper
            var isShield    = e.target.classList.contains('m-rte-yt-click-shield');
            var ytWrapper   = isShield ? e.target.parentNode : null;

            if (img && content.contains(img)) {
                e.stopPropagation();
                dismissYtSelection();
                selectImage(img);
            } else if (ytWrapper && content.contains(ytWrapper)) {
                e.stopPropagation();
                dismissImageSelection();
                selectYtWrapper(ytWrapper);
            } else {
                dismissImageSelection();
                dismissYtSelection();
            }
        });

        // Alignment mini-toolbar clicks
        resizerBarEl.addEventListener('mousedown', function (e) {
            // Allow normal interaction with the alt text input
            if (e.target.closest('.m-rte-imgbar-alt')) { return; }
            e.preventDefault(); // Keep image selection intact
            var btn = e.target.closest('.m-rte-imgbar-btn');
            if (!btn || !selectedImg) { return; }

            if (btn.getAttribute('data-img-remove')) {
                if (selectedImg.parentNode) {
                    selectedImg.parentNode.removeChild(selectedImg);
                }
                dismissImageSelection();
                syncHidden();
                updateCharCount();
                utils.trigger(container, 'm:rte:change', { value: getValue() });
                return;
            }

            var align = btn.getAttribute('data-img-align');
            if (align) {
                applyImageAlign(selectedImg, align);
                // Defer resizer repositioning until after the browser has reflowed
                // the new alignment (e.g. float removed, display:block applied).
                // Without this the resizer snaps to the pre-reflow position.
                var imgForReposition = selectedImg;
                requestAnimationFrame(function () {
                    if (imgForReposition) { positionResizer(imgForReposition); }
                });
            }
        });

        // Alt text input — update image attribute live
        if (imgAltInput) {
            imgAltInput.addEventListener('input', function () {
                if (selectedImg) {
                    selectedImg.setAttribute('alt', imgAltInput.value);
                    syncHidden();
                    utils.trigger(container, 'm:rte:change', { value: getValue() });
                }
            });
        }

        // ---- Resize drag logic ----
        if (allowImageResize) {
            var dragState = null;

            resizerEl.addEventListener('mousedown', function (e) {
                var handle = e.target.closest('.m-rte-resize-handle');
                if (!handle || !selectedImg) { return; }
                e.preventDefault();

                // Record original dimensions on first drag
                if (!selectedImg.getAttribute('data-original-width') && selectedImg.naturalWidth) {
                    selectedImg.setAttribute('data-original-width',  selectedImg.naturalWidth);
                    selectedImg.setAttribute('data-original-height', selectedImg.naturalHeight);
                }

                var startX = e.clientX;
                var startY = e.clientY;
                var startW = selectedImg.offsetWidth  || selectedImg.naturalWidth  || 100;
                var startH = selectedImg.offsetHeight || selectedImg.naturalHeight || 100;
                var pos    = handle.getAttribute('data-handle');
                var aspect = startH > 0 ? startW / startH : 1;

                dragState = { startX: startX, startY: startY, startW: startW, startH: startH, pos: pos, aspect: aspect };
            });

            document.addEventListener('mousemove', function (e) {
                if (!dragState || !selectedImg) { return; }
                var dx  = e.clientX - dragState.startX;
                var dy  = e.clientY - dragState.startY;
                var pos = dragState.pos;
                var newW = dragState.startW;
                var newH = dragState.startH;

                // Edge handles — single axis; corner handles — proportional
                if (pos === 'e' || pos === 'ne' || pos === 'se') {
                    newW = Math.max(20, dragState.startW + dx);
                } else if (pos === 'w' || pos === 'nw' || pos === 'sw') {
                    newW = Math.max(20, dragState.startW - dx);
                } else if (pos === 's') {
                    newH = Math.max(20, dragState.startH + dy);
                    newW = Math.round(newH * dragState.aspect);
                } else if (pos === 'n') {
                    newH = Math.max(20, dragState.startH - dy);
                    newW = Math.round(newH * dragState.aspect);
                }

                // Maintain aspect ratio for corner handles
                if (pos === 'nw' || pos === 'ne' || pos === 'se' || pos === 'sw') {
                    newH = Math.round(newW / dragState.aspect);
                }

                selectedImg.style.width  = newW + 'px';
                selectedImg.style.height = '';   // always auto — maintains natural ratio
                positionResizer(selectedImg);
            });

            document.addEventListener('mouseup', function () {
                if (!dragState) { return; }
                dragState = null;
                if (selectedImg) {
                    syncHidden();
                    utils.trigger(container, 'm:rte:change', { value: getValue() });
                }
            });
        }

        // YouTube resize drag
        ytResizerEl.addEventListener('mousedown', function (e) {
            var handle = e.target.closest('.m-rte-yt-resize-handle');
            if (!handle || !selectedYt) { return; }
            e.preventDefault();
            var pos         = handle.getAttribute('data-yt-handle');
            var parentEl    = selectedYt.parentNode;
            var parentWidth = parentEl ? (parentEl.offsetWidth || parentEl.clientWidth) : 0;
            ytDragState = {
                pos:        pos,
                startX:     e.clientX,
                startWidth: selectedYt.offsetWidth,
                parentWidth: parentWidth || selectedYt.offsetWidth
            };
        });

        document.addEventListener('mousemove', function (e) {
            if (!ytDragState || !selectedYt) { return; }
            var dx = e.clientX - ytDragState.startX;
            var newWidth = ytDragState.pos === 'e'
                ? ytDragState.startWidth + dx
                : ytDragState.startWidth - dx;
            var pct = Math.round(newWidth / ytDragState.parentWidth * 100);
            pct = Math.min(100, Math.max(10, pct));
            selectedYt.style.width = pct + '%';
            positionYtResizer(selectedYt);
        });

        document.addEventListener('mouseup', function () {
            if (!ytDragState) { return; }
            ytDragState = null;
            if (selectedYt) {
                syncHidden();
                utils.trigger(container, 'm:rte:change', { value: getValue() });
            }
        });

        // Dismiss when clicking outside the entire editor
        document.addEventListener('click', function (e) {
            if (!container.contains(e.target)) {
                dismissImageSelection();
                dismissYtSelection();
            }
        });

        // Re-position resizers when the body scrolls (when maxHeight is set)
        if (imageBody) {
            imageBody.addEventListener('scroll', function () {
                if (selectedImg && !resizerEl.hasAttribute('hidden')) {
                    positionResizer(selectedImg);
                }
                if (selectedYt && ytResizerEl && !ytResizerEl.hasAttribute('hidden')) {
                    positionYtResizer(selectedYt);
                }
            });
        }

        // =========================================================
        // Core helpers
        // =========================================================

        function getValue() {
            // Clone so we can strip editor-only elements without touching the live DOM
            var clone = content.cloneNode(true);
            clone.querySelectorAll('.m-rte-yt-click-shield').forEach(function (el) {
                if (el.parentNode) { el.parentNode.removeChild(el); }
            });
            // Images still uploading (data: src) are stripped from saved output to
            // avoid persisting huge base64 payloads — they'll be replaced once uploaded.
            clone.querySelectorAll('img[data-rte-uploading]').forEach(function (el) {
                if (el.parentNode) { el.parentNode.removeChild(el); }
            });
            return clone.innerHTML;
        }

        function setValue(html) {
            content.innerHTML = html && html.trim() ? html : '<p><br></p>';
            dismissImageSelection();
            dismissYtSelection();
            addYtClickShields();
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

                case 'insertImage': {
                    openImageDialog();
                    break;
                }

                case 'insertYouTube': {
                    openYouTubeDialog();
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
            // Dismiss image selection on any key that edits content
            if (selectedImg && !e.ctrlKey && !e.metaKey && !e.altKey) {
                var nav = ['ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End','Escape','Tab'];
                if (nav.indexOf(e.key) === -1) {
                    dismissImageSelection();
                }
            }
            if (e.key === 'Escape') { dismissImageSelection(); dismissYtSelection(); }

            // Delete or Backspace removes a selected YouTube wrapper
            if (selectedYt && (e.key === 'Delete' || e.key === 'Backspace')) {
                e.preventDefault();
                var ytToRemove = selectedYt;
                dismissYtSelection();
                if (ytToRemove.parentNode) {
                    ytToRemove.parentNode.removeChild(ytToRemove);
                }
                syncHidden();
                updateCharCount();
                utils.trigger(container, 'm:rte:change', { value: getValue() });
                return;
            }

            // ---- Tab / Shift+Tab ----
            // In a list item: indent (Tab) / outdent (Shift+Tab) to create/remove sub-lists.
            // Outside a list: Tab inserts a visual indent; Shift+Tab does nothing.
            if (e.key === 'Tab') {
                e.preventDefault();
                var sel = window.getSelection();
                var liNode = null;
                if (sel && sel.rangeCount > 0) {
                    var anchorNode = sel.anchorNode;
                    var cur = anchorNode && anchorNode.nodeType === 3 ? anchorNode.parentNode : anchorNode;
                    while (cur && cur !== content) {
                        if (cur.nodeName === 'LI') { liNode = cur; break; }
                        cur = cur.parentNode;
                    }
                }
                if (liNode) {
                    // Inside a list item: indent or outdent the list
                    if (e.shiftKey) {
                        document.execCommand('outdent', false, null);
                    } else {
                        document.execCommand('indent', false, null);
                    }
                } else if (!e.shiftKey) {
                    // Outside a list, Tab only: insert a visual indent (4 non-breaking spaces)
                    document.execCommand('insertHTML', false, '\u00a0\u00a0\u00a0\u00a0');
                }
                syncHidden();
                updateCharCount();
                utils.trigger(container, 'm:rte:change', { value: getValue() });
                return;
            }

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
        // Image dialog
        // =========================================================

        var imageDialog    = container.querySelector('.m-rte-image-dialog');
        var imageUrlInput  = imageDialog && imageDialog.querySelector('.m-rte-image-url');
        var imageAltInput  = imageDialog && imageDialog.querySelector('.m-rte-image-alt');
        var imageInsertBtn = imageDialog && imageDialog.querySelector('.m-rte-image-insert');
        var imageCancelBtn = imageDialog && imageDialog.querySelector('.m-rte-image-cancel');
        var imageCloseBtn  = imageDialog && imageDialog.querySelector('.m-rte-image-close');
        var imageBackdrop  = imageDialog && imageDialog.querySelector('.m-rte-image-backdrop');
        var imageFileInput = imageDialog && imageDialog.querySelector('.m-rte-image-file-input');
        var imageFileName  = imageDialog && imageDialog.querySelector('.m-rte-image-file-name');

        var savedImageRange  = null;
        var pendingImageFile = null;

        function setImageInsertBusy(busy) {
            if (!imageInsertBtn) { return; }
            imageInsertBtn.disabled = busy;
            imageInsertBtn.classList.toggle('m-button-disabled', busy);
            imageInsertBtn.innerHTML = busy
                ? '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Uploading…'
                : '<i class="fas fa-image" aria-hidden="true"></i> Insert';
        }

        function openImageDialog() {
            if (!imageDialog) { return; }
            var sel = window.getSelection();
            savedImageRange = (sel && sel.rangeCount > 0) ? sel.getRangeAt(0).cloneRange() : null;
            if (imageUrlInput) { imageUrlInput.value = ''; }
            if (imageAltInput) { imageAltInput.value = ''; }
            if (imageFileInput) { imageFileInput.value = ''; }
            if (imageFileName)  { imageFileName.textContent = 'No file chosen'; }
            pendingImageFile = null;
            setImageInsertBusy(false);
            imageDialog.hidden = false;
            setTimeout(function () {
                if (imageUrlInput) { imageUrlInput.focus(); }
            }, 50);
        }

        function closeImageDialog(restoreSelection) {
            if (!imageDialog) { return; }
            imageDialog.hidden = true;
            if (restoreSelection) {
                content.focus();
                var sel = window.getSelection();
                if (sel) {
                    if (savedImageRange) {
                        sel.removeAllRanges();
                        sel.addRange(savedImageRange);
                    } else if (sel.rangeCount === 0) {
                        // No saved range — place cursor at end of editor as fallback
                        var r = document.createRange();
                        r.selectNodeContents(content);
                        r.collapse(false);
                        sel.removeAllRanges();
                        sel.addRange(r);
                    }
                }
            }
        }

        function doInsertImage() {
            var url = imageUrlInput ? imageUrlInput.value.trim() : '';
            var alt = imageAltInput ? imageAltInput.value.trim() : '';

            if (url) {
                closeImageDialog(true);
                insertImageHtmlAtCursor(url, alt);
                return;
            }

            if (pendingImageFile) {
                if (!uploaderUrl) {
                    showRteError('Image uploader is not configured for this editor.');
                    return;
                }
                // Close the dialog and insert a blob placeholder immediately so the
                // user sees the image in the editor while it uploads in the background.
                var fileToUpload = pendingImageFile;
                var altText      = alt;
                var blobUrl      = URL.createObjectURL(fileToUpload);
                closeImageDialog(true);
                insertImageHtmlAtCursor(blobUrl, altText);
                var placeholderImg = content.querySelector('img[src="' + blobUrl + '"]');
                if (placeholderImg) {
                    placeholderImg.setAttribute('data-rte-uploading', 'true');
                    showUploadOverlay(placeholderImg);
                }
                uploadImage(fileToUpload, function (err, uploadedUrl) {
                    if (placeholderImg && placeholderImg.parentNode) {
                        if (err) {
                            placeholderImg.parentNode.removeChild(placeholderImg);
                        } else {
                            placeholderImg.src = uploadedUrl;
                            placeholderImg.removeAttribute('data-rte-uploading');
                        }
                    }
                    URL.revokeObjectURL(blobUrl);
                    hideUploadOverlay();
                    if (err) {
                        showRteError('Image upload failed: ' + err);
                    } else {
                        syncHidden();
                        updateCharCount();
                        utils.trigger(container, 'm:rte:change', { value: getValue() });
                    }
                }, function (percent) {
                    updateUploadProgress(percent);
                });
                return;
            }

            // Neither URL nor file provided — give the user feedback
            showRteError('Please enter an image URL or select a file to upload.');
            if (imageUrlInput) { imageUrlInput.focus(); }
        }

        if (imageDialog) {
            if (imageInsertBtn) { imageInsertBtn.addEventListener('click', doInsertImage); }
            if (imageCancelBtn) { imageCancelBtn.addEventListener('click', function () { closeImageDialog(false); }); }
            if (imageCloseBtn)  { imageCloseBtn.addEventListener('click',  function () { closeImageDialog(false); }); }
            if (imageBackdrop)  { imageBackdrop.addEventListener('click',  function () { closeImageDialog(false); }); }

            if (imageFileInput) {
                imageFileInput.addEventListener('change', function () {
                    if (imageFileInput.files && imageFileInput.files.length > 0) {
                        pendingImageFile = imageFileInput.files[0];
                        if (imageFileName) { imageFileName.textContent = pendingImageFile.name; }
                        // Clear URL field when a file is chosen
                        if (imageUrlInput) { imageUrlInput.value = ''; }
                    }
                });
            }

            if (imageUrlInput) {
                imageUrlInput.addEventListener('input', function () {
                    // If user types a URL, clear the pending file selection
                    if (imageUrlInput.value.trim()) {
                        pendingImageFile = null;
                        if (imageFileInput) { imageFileInput.value = ''; }
                        if (imageFileName)  { imageFileName.textContent = 'No file chosen'; }
                    }
                });
                imageUrlInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') { e.preventDefault(); closeImageDialog(false); }
                    if (e.key === 'Enter')  { e.preventDefault(); doInsertImage(); }
                });
            }

            if (imageAltInput) {
                imageAltInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') { e.preventDefault(); closeImageDialog(false); }
                    if (e.key === 'Enter')  { e.preventDefault(); doInsertImage(); }
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
        // YouTube embed dialog
        // =========================================================

        var ytDialog     = container.querySelector('.m-rte-youtube-dialog');
        var ytUrlInput   = ytDialog && ytDialog.querySelector('.m-rte-youtube-url');
        var ytInsertBtn  = ytDialog && ytDialog.querySelector('.m-rte-youtube-insert');
        var ytCancelBtn = ytDialog && ytDialog.querySelector('.m-rte-youtube-cancel');
        var ytCloseBtn  = ytDialog && ytDialog.querySelector('.m-rte-youtube-close');
        var ytBackdrop  = ytDialog && ytDialog.querySelector('.m-rte-youtube-backdrop');

        var savedYtRange = null;

        /**
         * Extract an 11-character YouTube video ID from any flavour of YouTube URL,
         * or return the string itself if it already looks like a bare video ID.
         *
         * Supported inputs:
         *   https://www.youtube.com/watch?v=dQw4w9WgXcQ
         *   https://youtu.be/dQw4w9WgXcQ
         *   https://www.youtube.com/embed/dQw4w9WgXcQ
         *   https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ
         *   dQw4w9WgXcQ  (bare ID)
         */
        function parseYouTubeId(input) {
            var str = (input || '').trim();
            if (!str) { return null; }

            // youtu.be/ID
            var m1 = str.match(/youtu\.be\/([A-Za-z0-9_\-]{11})/);
            if (m1) { return m1[1]; }

            // /embed/ID or /v/ID
            var m2 = str.match(/\/(?:embed|v)\/([A-Za-z0-9_\-]{11})/);
            if (m2) { return m2[1]; }

            // ?v=ID or &v=ID
            var m3 = str.match(/[?&]v=([A-Za-z0-9_\-]{11})/);
            if (m3) { return m3[1]; }

            // Bare 11-char ID
            if (/^[A-Za-z0-9_\-]{11}$/.test(str)) { return str; }

            return null;
        }

        function openYouTubeDialog() {
            if (!ytDialog) { return; }
            var sel = window.getSelection();
            savedYtRange = (sel && sel.rangeCount > 0) ? sel.getRangeAt(0).cloneRange() : null;
            if (ytUrlInput) { ytUrlInput.value = ''; }
            ytDialog.hidden = false;
            setTimeout(function () {
                if (ytUrlInput) { ytUrlInput.focus(); }
            }, 50);
        }

        function closeYouTubeDialog(restoreSelection) {
            if (!ytDialog) { return; }
            ytDialog.hidden = true;
            if (restoreSelection) {
                content.focus();
                var sel = window.getSelection();
                if (sel) {
                    if (savedYtRange) {
                        sel.removeAllRanges();
                        sel.addRange(savedYtRange);
                    } else if (sel.rangeCount === 0) {
                        var r = document.createRange();
                        r.selectNodeContents(content);
                        r.collapse(false);
                        sel.removeAllRanges();
                        sel.addRange(r);
                    }
                }
            }
        }

        /**
         * Fetch YouTube oEmbed data for a video ID.
         * Calls callback(channelName, channelUrl) — both may be null on failure.
         */
        function fetchYouTubeOEmbed(videoId, callback) {
            var oEmbedUrl = 'https://www.youtube.com/oembed?url='
                + encodeURIComponent('https://www.youtube.com/watch?v=' + videoId)
                + '&format=json';
            var xhr = new XMLHttpRequest();
            xhr.open('GET', oEmbedUrl, true);
            xhr.timeout = 6000;
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        callback(data.author_name || null, data.author_url || null);
                        return;
                    } catch (e) { /* fall through */ }
                }
                callback(null, null);
            };
            xhr.onerror   = function () { callback(null, null); };
            xhr.ontimeout = function () { callback(null, null); };
            xhr.send();
        }

        /** Allow only YouTube channel / user / handle URLs as the credit href. */
        function sanitizeYouTubeChannelUrl(url) {
            if (!url) { return ''; }
            if (/^https:\/\/(www\.)?youtube\.com\/(channel\/|user\/|c\/|@)[A-Za-z0-9_\-@.%]+/.test(url)) {
                return url;
            }
            return '';
        }

        function doInsertYouTube() {
            if (!ytUrlInput) { return; }
            var raw = ytUrlInput.value.trim();
            var videoId = parseYouTubeId(raw);

            if (!videoId) {
                showRteError('Please enter a valid YouTube URL or video ID.');
                if (ytUrlInput) { ytUrlInput.focus(); }
                return;
            }

            // Disable the Insert button while we fetch channel info
            if (ytInsertBtn) {
                ytInsertBtn.disabled = true;
                ytInsertBtn.textContent = 'Fetching…';
            }

            fetchYouTubeOEmbed(videoId, function (channelName, channelUrl) {
                // Re-enable button regardless of outcome
                if (ytInsertBtn) {
                    ytInsertBtn.disabled = false;
                    ytInsertBtn.innerHTML = '<i class="fab fa-youtube" aria-hidden="true"></i> Embed';
                }

                closeYouTubeDialog(true);

                // Build optional credit line
                var creditHtml = '';
                if (channelName && channelUrl) {
                    var safeUrl  = sanitizeYouTubeChannelUrl(channelUrl);
                    var safeName = escapeHtml(channelName);
                    if (safeUrl) {
                        creditHtml = '<p class="m-rte-youtube-credit">'
                                   + 'Video Credit: <a href="' + safeUrl + '" target="_blank" rel="noopener noreferrer">'
                                   + safeName + '</a>'
                                   + '</p>';
                    }
                }

                // Responsive 16:9 wrapper + privacy-enhanced nocookie embed
                var embedWidth = 80;
                var embedUrl = 'https://www.youtube-nocookie.com/embed/' + videoId;
                var html = '<div class="m-rte-youtube-wrapper" contenteditable="false" style="width:' + embedWidth + '%;margin-left:auto;margin-right:auto">'
                         + '<iframe src="' + embedUrl + '"'
                         + ' width="560" height="315"'
                         + ' title="YouTube video"'
                         + ' frameborder="0"'
                         + ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"'
                         + ' allowfullscreen'
                         + ' loading="lazy"></iframe>'
                         + creditHtml
                         + '</div><p><br></p>';

                document.execCommand('insertHTML', false, html);
                addYtClickShields();
                syncHidden();
                updateCharCount();
                utils.trigger(container, 'm:rte:change', { value: getValue() });
            });
        }

        if (ytDialog) {
            if (ytInsertBtn) { ytInsertBtn.addEventListener('click', doInsertYouTube); }
            if (ytCancelBtn) { ytCancelBtn.addEventListener('click', function () { closeYouTubeDialog(false); }); }
            if (ytCloseBtn)  { ytCloseBtn.addEventListener('click',  function () { closeYouTubeDialog(false); }); }
            if (ytBackdrop)  { ytBackdrop.addEventListener('click',  function () { closeYouTubeDialog(false); }); }
            if (ytUrlInput) {
                ytUrlInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') { e.preventDefault(); closeYouTubeDialog(false); }
                    if (e.key === 'Enter')  { e.preventDefault(); doInsertYouTube(); }
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
                // If selection moved away from the selected image, dismiss it
                // (range.intersectsNode correctly handles range.selectNode(img) where
                // commonAncestorContainer is the image's parent, not the image itself)
                if (selectedImg && !range.intersectsNode(selectedImg)) {
                    dismissImageSelection();
                }
                updateToolbarState();
            }
        });

        // =========================================================
        // Drop handler — intercept image file drops
        // =========================================================
        //
        // Without this, the browser's default drop behaviour inserts the
        // dropped image as a huge inline base64 data URI into the
        // contenteditable area.  We intercept image files and route them
        // through the same uploadImage() path used by the paste handler.

        content.addEventListener('dragover', function (e) {
            // Allow drop so that the 'drop' event fires
            e.preventDefault();
        });

        content.addEventListener('drop', function (e) {
            var files = e.dataTransfer && e.dataTransfer.files;
            if (!files || !files.length) { return; } // let browser handle text/url drops

            // Check if any dropped file is an image
            var imageFile = null;
            for (var i = 0; i < files.length; i++) {
                if (files[i].type.indexOf('image') === 0) {
                    imageFile = files[i];
                    break;
                }
            }

            if (!imageFile) { return; } // non-image drop — let browser handle it

            // We have an image file — take over entirely
            e.preventDefault();

            if (!uploaderUrl) {
                showRteError('Image uploader is not configured. Drag-and-drop images are not supported.');
                return;
            }

            // Position cursor at the drop point before inserting the placeholder
            if (document.caretRangeFromPoint) {
                var range = document.caretRangeFromPoint(e.clientX, e.clientY);
                if (range) {
                    var selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            }

            // Insert a placeholder so the user sees visual feedback immediately
            var blobUrl = URL.createObjectURL(imageFile);
            insertImageHtmlAtCursor(blobUrl, '');
            var placeholderImg = content.querySelector('img[src="' + blobUrl + '"]');
            if (placeholderImg) {
                placeholderImg.setAttribute('data-rte-uploading', 'true');
                showUploadOverlay(placeholderImg);
            }

            uploadImage(imageFile, function (err, url) {
                if (placeholderImg && placeholderImg.parentNode) {
                    if (err) {
                        placeholderImg.parentNode.removeChild(placeholderImg);
                    } else {
                        placeholderImg.src = url;
                        placeholderImg.removeAttribute('data-rte-uploading');
                    }
                }
                URL.revokeObjectURL(blobUrl);
                hideUploadOverlay();
                if (err) {
                    showRteError('Image upload failed: ' + err);
                } else {
                    syncHidden();
                    updateCharCount();
                    utils.trigger(container, 'm:rte:change', { value: getValue() });
                }
            }, function (percent) {
                updateUploadProgress(percent);
            });
        });

        // =========================================================
        // Paste handler — strip unsafe / undesirable HTML
        // =========================================================

        content.addEventListener('paste', function (e) {
            e.preventDefault();
            var clipData = e.clipboardData || window.clipboardData;

            var html = clipData ? clipData.getData('text/html') : '';
            var text = clipData ? clipData.getData('text/plain') : '';

            // --- Check for a raw image file item (e.g. screenshot) ---
            // Only use the raw-file path when there is no richer HTML alongside it.
            // When HTML is present (e.g. copying an image on a webpage), the HTML
            // path handles it and avoids uploading a redundant copy.
            var imageFile = null;
            if (!html && clipData && clipData.items) {
                for (var idx = 0; idx < clipData.items.length; idx++) {
                    var item = clipData.items[idx];
                    if (item.kind === 'file' && item.type.indexOf('image') === 0) {
                        imageFile = item.getAsFile();
                        break;
                    }
                }
            }

            if (imageFile) {
                // Pure raw image (screenshot / file copy) — no text content alongside it
                if (!allowPasteImages) { return; }
                if (!uploaderUrl) {
                    showRteError('Image uploader is not configured for this editor.');
                    return;
                }
                var blobUrl = URL.createObjectURL(imageFile);
                insertImageHtmlAtCursor(blobUrl, '');
                var placeholderImg = content.querySelector('img[src="' + blobUrl + '"]');
                if (placeholderImg) {
                    placeholderImg.setAttribute('data-rte-uploading', 'true');
                    showUploadOverlay(placeholderImg);
                }
                uploadImage(imageFile, function (err, url) {
                    if (placeholderImg && placeholderImg.parentNode) {
                        if (err) {
                            placeholderImg.parentNode.removeChild(placeholderImg);
                        } else {
                            placeholderImg.src = url;
                            placeholderImg.removeAttribute('data-rte-uploading');
                        }
                    }
                    URL.revokeObjectURL(blobUrl);
                    hideUploadOverlay();
                    if (err) {
                        showRteError('Image upload failed: ' + err);
                    } else {
                        syncHidden();
                        updateCharCount();
                        utils.trigger(container, 'm:rte:change', { value: getValue() });
                    }
                }, function (percent) {
                    updateUploadProgress(percent);
                });
                return;
            }

            // --- HTML paste (may include inline images alongside text) ---
            if (html) {
                html = cleanPastedHtml(html);
                document.execCommand('insertHTML', false, html);

                // Upload any base64 data: images that cleanPastedHtml kept
                if (allowPasteImages && uploaderUrl) {
                    uploadDataImages();
                }
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

            // Sanitise images:
            //   http(s) src  — safe external URL; keep src + alt only
            //   data: src    — embedded base64; keep for async upload if uploader configured, else strip
            //   everything else (blob:, file:, etc.) — strip
            tmp.querySelectorAll('img').forEach(function (el) {
                var src = el.getAttribute('src') || '';
                if (/^https?:\/\//i.test(src)) {
                    // Keep external image — preserve only src and alt
                    var keepAlt = el.getAttribute('alt') || '';
                    while (el.attributes.length > 0) { el.removeAttribute(el.attributes[0].name); }
                    el.setAttribute('src', src);
                    if (keepAlt) { el.setAttribute('alt', keepAlt); }
                } else if (/^data:image\//i.test(src) && allowPasteImages && uploaderUrl) {
                    // Embedded base64 image — mark for async upload after insert
                    while (el.attributes.length > 0) { el.removeAttribute(el.attributes[0].name); }
                    el.setAttribute('src', src);
                    el.setAttribute('data-rte-uploading', 'true');
                } else {
                    // Unsafe or unsupported src — remove entirely
                    var parent = el.parentNode;
                    if (parent) { parent.removeChild(el); }
                }
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

        /**
         * After inserting pasted HTML, find any img[data-rte-uploading] elements
         * whose src is a data: URL and upload each one, swapping the src on completion.
         */
        function uploadDataImages() {
            var imgs = content.querySelectorAll('img[data-rte-uploading]');
            imgs.forEach(function (img) {
                var src = img.getAttribute('src') || '';
                if (src.indexOf('data:') !== 0) { return; }

                var blob = dataUrlToBlob(src);
                if (!blob) {
                    img.removeAttribute('data-rte-uploading');
                    return;
                }

                showUploadOverlay(img);

                // Capture reference in closure
                (function (targetImg) {
                    uploadImage(blob, function (err, url) {
                        if (targetImg.parentNode) {
                            if (err) {
                                targetImg.parentNode.removeChild(targetImg);
                            } else {
                                targetImg.src = url;
                                targetImg.removeAttribute('data-rte-uploading');
                            }
                        }
                        hideUploadOverlay();
                        if (err) {
                            showRteError('Image upload failed: ' + err);
                        } else {
                            syncHidden();
                            updateCharCount();
                            utils.trigger(container, 'm:rte:change', { value: getValue() });
                        }
                    }, null);
                }(img));
            });
        }

        /** Convert a base64 data URL to a Blob suitable for upload. */
        function dataUrlToBlob(dataUrl) {
            try {
                var parts = dataUrl.split(',');
                if (parts.length < 2) { return null; }
                var mimeMatch = parts[0].match(/:([^;]+);/);
                if (!mimeMatch) { return null; }
                var mime = mimeMatch[1];
                var bstr = atob(parts[1]);
                var n = bstr.length;
                var u8arr = new Uint8Array(n);
                for (var i = 0; i < n; i++) { u8arr[i] = bstr.charCodeAt(i); }
                return new Blob([u8arr], { type: mime });
            } catch (ex) {
                return null;
            }
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

        addYtClickShields();
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
            insertImage: insertImageHtmlAtCursor,
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
