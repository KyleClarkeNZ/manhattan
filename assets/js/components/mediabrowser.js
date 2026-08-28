/**
 * Manhattan UI — MediaBrowser Component
 *
 * A modal image picker: lists the files in a server-side folder, uploads new
 * ones, and hands the chosen file back to a form field or a callback.
 *
 * PHP usage:
 *   echo $m->mediaBrowser('coverBrowser')
 *       ->endpoint('/media.php')
 *       ->folder('blog')
 *       ->trigger('cover_browse')
 *       ->target('cover_image');
 *
 * JS API:
 *   var mb = m.mediaBrowser('coverBrowser');
 *   mb.open(function (file) { ... });  // callback overrides the ->target() write
 *   mb.close();
 *   mb.refresh();                      // re-fetch the file list
 *   mb.getSelected();                  // → file object or null
 *
 * Events (fired on the container element):
 *   m:mediabrowser:open     — { id }
 *   m:mediabrowser:select   — { id, file }
 *   m:mediabrowser:upload   — { id, file }
 *   m:mediabrowser:close    — { id }
 *
 * Server contract — see src/MediaBrowser.php. In short:
 *   GET  <endpoint>?action=list&folder=<key>   → {"files": [ ... ]}
 *   POST <endpoint>  (action=upload, folder, file) → {"file": { ... }}
 *   errors: 4xx/5xx with {"message": "..."}
 */
(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before mediabrowser module');
        return;
    }

    var utils = m.utils;

    /** Human-readable file size. */
    function formatSize(bytes) {
        if (typeof bytes !== 'number' || bytes <= 0) { return ''; }
        if (bytes < 1024) { return bytes + ' B'; }
        if (bytes < 1024 * 1024) { return Math.round(bytes / 1024) + ' KB'; }
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    /**
     * Pull an error message out of a response body, falling back to something
     * the user can act on rather than a status code.
     */
    function errorMessage(xhr, fallback) {
        try {
            var body = JSON.parse(xhr.responseText);
            if (body && body.message) { return String(body.message); }
        } catch (e) { /* not JSON — fall through */ }
        return fallback;
    }

    m.mediaBrowser = function (id) {
        var container = utils.getElement(id);
        if (!container) { return null; }

        // Prevent double-init (auto-init on DOMContentLoaded plus a manual call).
        if (container._mMediaBrowser) { return container._mMediaBrowser; }

        var endpoint    = container.getAttribute('data-endpoint') || '';
        var folder      = container.getAttribute('data-folder') || '';
        var triggerId   = container.getAttribute('data-trigger') || '';
        var targetId    = container.getAttribute('data-target') || '';
        var allowUpload = container.getAttribute('data-allow-upload') !== '0';
        var maxBytes    = parseInt(container.getAttribute('data-max-bytes') || '0', 10) || 0;

        var backdrop   = container.querySelector('.m-mediabrowser-backdrop');
        var panel      = container.querySelector('.m-mediabrowser-panel');
        var closeBtn   = container.querySelector('.m-mediabrowser-close');
        var uploadBtn  = container.querySelector('.m-mediabrowser-upload-btn');
        var fileInput  = container.querySelector('.m-mediabrowser-file');
        var filterBox  = container.querySelector('.m-mediabrowser-filter');
        var body       = container.querySelector('.m-mediabrowser-body');
        var grid       = container.querySelector('.m-mediabrowser-grid');
        var loadingEl  = container.querySelector('.m-mediabrowser-loading');
        var emptyEl    = container.querySelector('.m-mediabrowser-empty');
        var errorEl    = container.querySelector('.m-mediabrowser-error');
        var dropzone   = container.querySelector('.m-mediabrowser-dropzone');
        var selectedEl = container.querySelector('.m-mediabrowser-selected');
        var cancelBtn  = container.querySelector('.m-mediabrowser-cancel');
        var selectBtn  = container.querySelector('.m-mediabrowser-select');

        if (!panel) { return null; }

        // The modal is fixed-position, but a clipping ancestor (a card with
        // `overflow: hidden`, a stacking context) would still trap it. Reparent
        // to <body> once, which also lifts it out of any surrounding <form> so
        // the file input can never take part in a submit.
        if (container.parentNode !== document.body) {
            document.body.appendChild(container);
        }

        var files       = [];   // last list from the server
        var selected    = null; // currently highlighted file object
        var onPick      = null; // per-open callback, set by open(fn)
        var loaded      = false;
        var lastFocused = null;

        // ------------------------------------------------------------------
        // States
        // ------------------------------------------------------------------

        function showState(which, message) {
            if (loadingEl) { loadingEl.hidden = which !== 'loading'; }
            if (emptyEl)   { emptyEl.hidden   = which !== 'empty'; }
            if (errorEl) {
                errorEl.hidden = which !== 'error';
                if (which === 'error') { errorEl.textContent = message || 'Something went wrong.'; }
            }
            if (grid) { grid.hidden = which !== 'grid'; }
        }

        // ------------------------------------------------------------------
        // Rendering
        // ------------------------------------------------------------------

        function matchesFilter(file, needle) {
            if (!needle) { return true; }
            return String(file.name || '').toLowerCase().indexOf(needle) !== -1;
        }

        function renderGrid() {
            if (!grid) { return; }

            var needle = filterBox ? filterBox.value.trim().toLowerCase() : '';
            var shown  = files.filter(function (file) { return matchesFilter(file, needle); });

            grid.innerHTML = '';

            if (!files.length) {
                showState('empty');
                return;
            }

            if (!shown.length) {
                showState('error', 'No images match “' + needle + '”.');
                return;
            }

            shown.forEach(function (file) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'm-mediabrowser-item';
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', selected && selected.url === file.url ? 'true' : 'false');
                if (selected && selected.url === file.url) {
                    item.classList.add('m-mediabrowser-item-selected');
                }
                item.setAttribute('data-url', file.url);

                var dims = (file.width && file.height) ? file.width + '×' + file.height : '';
                var size = formatSize(file.size);
                var meta = [dims, size].filter(Boolean).join(' · ');

                var thumb = document.createElement('span');
                thumb.className = 'm-mediabrowser-thumb';
                var img = document.createElement('img');
                img.src = file.url;
                img.alt = '';
                img.loading = 'lazy';
                thumb.appendChild(img);

                var name = document.createElement('span');
                name.className = 'm-mediabrowser-name';
                name.textContent = file.name || file.url;
                name.title = file.name || file.url;

                item.appendChild(thumb);
                item.appendChild(name);

                if (meta) {
                    var metaEl = document.createElement('span');
                    metaEl.className = 'm-mediabrowser-meta';
                    metaEl.textContent = meta;
                    item.appendChild(metaEl);
                }

                item._mFile = file;
                grid.appendChild(item);
            });

            showState('grid');
        }

        function setSelected(file) {
            selected = file || null;

            if (grid) {
                var items = grid.querySelectorAll('.m-mediabrowser-item');
                for (var i = 0; i < items.length; i++) {
                    var isSel = !!(selected && items[i]._mFile && items[i]._mFile.url === selected.url);
                    items[i].classList.toggle('m-mediabrowser-item-selected', isSel);
                    items[i].setAttribute('aria-selected', isSel ? 'true' : 'false');
                }
            }

            if (selectedEl) {
                selectedEl.textContent = selected ? (selected.name || selected.url) : '';
            }
            if (selectBtn) {
                selectBtn.disabled = !selected;
            }
        }

        // ------------------------------------------------------------------
        // Loading
        // ------------------------------------------------------------------

        function load() {
            if (!endpoint) {
                showState('error', 'This media browser has no endpoint configured.');
                return;
            }

            showState('loading');

            var url = endpoint
                + (endpoint.indexOf('?') === -1 ? '?' : '&')
                + 'action=list&folder=' + encodeURIComponent(folder);

            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function () {
                if (xhr.status < 200 || xhr.status >= 300) {
                    showState('error', errorMessage(xhr, 'Could not load the media library.'));
                    return;
                }

                var data;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (e) {
                    showState('error', 'The media endpoint returned an unreadable response.');
                    return;
                }

                files = (data && Array.isArray(data.files)) ? data.files : [];
                loaded = true;
                setSelected(null);
                renderGrid();
            };

            xhr.onerror = function () {
                showState('error', 'Could not reach the media endpoint.');
            };

            xhr.send();
        }

        // ------------------------------------------------------------------
        // Uploading
        // ------------------------------------------------------------------

        function upload(file) {
            if (!file) { return; }

            if (!endpoint) {
                showState('error', 'This media browser has no endpoint configured.');
                return;
            }

            if (maxBytes && file.size > maxBytes) {
                showState('error', 'That file is larger than ' + formatSize(maxBytes) + '.');
                return;
            }

            showState('loading');
            if (uploadBtn) { uploadBtn.disabled = true; }

            var formData = new FormData();
            formData.append('action', 'upload');
            formData.append('folder', folder);
            formData.append('file', file);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', endpoint, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function () {
                if (uploadBtn) { uploadBtn.disabled = false; }

                if (xhr.status < 200 || xhr.status >= 300) {
                    showState('error', errorMessage(xhr, 'Upload failed.'));
                    return;
                }

                var data;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (e) {
                    showState('error', 'The media endpoint returned an unreadable response.');
                    return;
                }

                var uploaded = data && data.file;
                if (!uploaded || !uploaded.url) {
                    showState('error', 'The upload succeeded but no file was returned.');
                    return;
                }

                // Show it immediately at the top rather than re-listing, so a
                // slow folder listing does not hide what was just uploaded.
                files.unshift(uploaded);
                if (filterBox) { filterBox.value = ''; }
                renderGrid();
                setSelected(uploaded);

                utils.trigger(container, 'm:mediabrowser:upload', { id: id, file: uploaded });
            };

            xhr.onerror = function () {
                if (uploadBtn) { uploadBtn.disabled = false; }
                showState('error', 'Could not reach the media endpoint.');
            };

            xhr.send(formData);
        }

        // ------------------------------------------------------------------
        // Open / close
        // ------------------------------------------------------------------

        function open(callback) {
            onPick = (typeof callback === 'function') ? callback : null;
            lastFocused = document.activeElement;

            // Only one popup surface open at a time across the page.
            utils.overlays.closeOthers(container);

            container.hidden = false;
            // Next frame, so the transition runs from the hidden state.
            window.requestAnimationFrame(function () {
                container.classList.add('m-mediabrowser-active');
            });

            document.body.classList.add('m-mediabrowser-open');

            if (!loaded) {
                load();
            } else {
                renderGrid();
            }

            if (filterBox) { filterBox.value = ''; }
            setSelected(null);

            if (body) { body.focus(); }

            utils.trigger(container, 'm:mediabrowser:open', { id: id });
        }

        function close() {
            if (container.hidden) { return; }

            container.classList.remove('m-mediabrowser-active');
            container.hidden = true;
            document.body.classList.remove('m-mediabrowser-open');

            if (lastFocused && typeof lastFocused.focus === 'function') {
                lastFocused.focus();
            }
            lastFocused = null;
            onPick = null;

            utils.trigger(container, 'm:mediabrowser:close', { id: id });
        }

        function confirmSelection() {
            if (!selected) { return; }

            var file = selected;
            var callback = onPick;

            utils.trigger(container, 'm:mediabrowser:select', { id: id, file: file });

            if (callback) {
                callback(file);
            } else if (targetId) {
                var target = document.getElementById(targetId);
                if (target) {
                    target.value = file.url;
                    // A single bubbling native `change`, so host previews and
                    // form logic react exactly as they would to a typed value.
                    target.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            close();
        }

        // ------------------------------------------------------------------
        // Event listeners
        // ------------------------------------------------------------------

        if (closeBtn)  { closeBtn.addEventListener('click', close); }
        if (cancelBtn) { cancelBtn.addEventListener('click', close); }
        if (backdrop)  { backdrop.addEventListener('click', close); }
        if (selectBtn) { selectBtn.addEventListener('click', confirmSelection); }

        if (grid) {
            grid.addEventListener('click', function (e) {
                var item = e.target.closest('.m-mediabrowser-item');
                if (!item || !item._mFile) { return; }
                setSelected(item._mFile);
            });

            // Double-click picks and closes in one gesture.
            grid.addEventListener('dblclick', function (e) {
                var item = e.target.closest('.m-mediabrowser-item');
                if (!item || !item._mFile) { return; }
                setSelected(item._mFile);
                confirmSelection();
            });
        }

        if (filterBox) {
            filterBox.addEventListener('input', renderGrid);
        }

        if (allowUpload && uploadBtn && fileInput) {
            uploadBtn.addEventListener('click', function () {
                fileInput.click();
            });

            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) {
                    upload(fileInput.files[0]);
                }
                // Reset so re-picking the same file fires `change` again.
                fileInput.value = '';
            });
        }

        // Drag-and-drop upload onto the body.
        if (allowUpload && body && dropzone) {
            var dragDepth = 0;

            body.addEventListener('dragenter', function (e) {
                e.preventDefault();
                dragDepth++;
                dropzone.hidden = false;
            });

            body.addEventListener('dragover', function (e) {
                e.preventDefault();
            });

            body.addEventListener('dragleave', function () {
                dragDepth = Math.max(0, dragDepth - 1);
                if (dragDepth === 0) { dropzone.hidden = true; }
            });

            body.addEventListener('drop', function (e) {
                e.preventDefault();
                dragDepth = 0;
                dropzone.hidden = true;
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                    upload(e.dataTransfer.files[0]);
                }
            });
        }

        // Escape closes. Registered once on the document; the guard on
        // container.hidden keeps inactive browsers out of the way.
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !container.hidden) {
                e.stopPropagation();
                close();
            }
        });

        // Delegated, so the trigger may be rendered after this component or
        // injected into the DOM later.
        if (triggerId) {
            document.addEventListener('click', function (e) {
                if (!e.target || !e.target.closest) { return; }
                var selector = (window.CSS && CSS.escape)
                    ? '#' + CSS.escape(triggerId)
                    : '#' + triggerId;
                if (!e.target.closest(selector)) { return; }
                e.preventDefault();
                open();
            });
        }

        // Let any other overlay close this one when it opens.
        utils.overlays.register(container, close);

        // ------------------------------------------------------------------
        // Public API
        // ------------------------------------------------------------------

        var api = {
            open: open,
            close: close,

            /** Re-fetch the file list from the server. */
            refresh: function () {
                loaded = false;
                load();
                return api;
            },

            /** Currently highlighted file object, or null. */
            getSelected: function () {
                return selected;
            },

            /** Change the folder key, discarding the cached listing. */
            setFolder: function (key) {
                folder = String(key || '');
                container.setAttribute('data-folder', folder);
                loaded = false;
                return api;
            },

            element: container
        };

        container._mMediaBrowser = api;
        return api;
    };

    // Auto-initialise every media browser on the page.
    document.addEventListener('DOMContentLoaded', function () {
        var browsers = document.querySelectorAll('[data-component="mediabrowser"]');
        for (var i = 0; i < browsers.length; i++) {
            if (browsers[i].id) {
                m.mediaBrowser(browsers[i].id);
            }
        }
    });

})(window);
