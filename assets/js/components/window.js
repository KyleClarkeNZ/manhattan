/**
 * Manhattan UI Framework - Module
 */

(function(window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before module');
        return;
    }

    const utils = m.utils;

    m.window = function(id, options) {
        const windowEl = utils.getElement(id);
        if (!windowEl) {
            console.warn('Manhattan Window: Element not found:', id);
            return null;
        }

        // Return the cached instance if already initialised.
        // This prevents duplicate event listeners when m.window() is called
        // more than once on the same element (e.g. auto-init + manual call).
        if (windowEl._mWindowInstance) {
            return windowEl._mWindowInstance;
        }

        // Read configuration from data attributes (set by PHP)
        const isModal = windowEl.getAttribute('data-modal') === 'true';
        const isDraggable = windowEl.getAttribute('data-draggable') === 'true';
        
        const defaults = {
            title: '',
            content: '',
            modal: isModal,   // Respect data attribute (default non-modal)
            draggable: isDraggable,  // Respect data attribute
            width: '600px',
            height: 'auto',
            buttons: [],
            onClose: null,
            onOpen: null
        };

        options = utils.extend({}, defaults, options);
        
        const overlay = windowEl.querySelector('.m-window-overlay');
        const wrapper = windowEl.querySelector('.m-window-wrapper');
        const closeBtn = windowEl.querySelector('.m-window-close');
        const titleBar = windowEl.querySelector('.m-window-titlebar');

        let isDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;
        let windowStartX = 0;
        let windowStartY = 0;
        
        // Z-index management for non-modal windows
        let baseZIndex = 10000;
        let maxZIndex = baseZIndex;

        // Open window
        const open = function() {
            windowEl.classList.add('m-visible');
            
            // Only prevent scroll for modal windows
            if (options.modal) {
                document.body.style.overflow = 'hidden';
            } else {
                // Non-modal: bring to front
                bringToFront();
            }
            
            if (options.onOpen) {
                options.onOpen();
            }
            
            utils.trigger(windowEl, 'm:window:open', { id });
        };

        // Close window — fires a cancellable m:window:beforeclose event first.
        // Listeners can call e.preventDefault() to abort the close.
        const close = function() {
            var beforeEvent = utils.trigger(windowEl, 'm:window:beforeclose', { id });
            if (beforeEvent && beforeEvent.defaultPrevented) {
                return; // listener cancelled the close
            }

            windowEl.classList.remove('m-visible');
            
            // Restore scroll (only matters for modals)
            if (options.modal) {
                document.body.style.overflow = '';
            }
            
            if (options.onClose) {
                options.onClose();
            }
            
            utils.trigger(windowEl, 'm:window:close', { id });
        };
        
        // Bring window to front (non-modal windows only)
        const bringToFront = function() {
            if (!options.modal) {
                maxZIndex++;
                windowEl.style.zIndex = maxZIndex;
            }
        };

        // Close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', close);
        }

        // Overlay click (close modal only)
        if (overlay && options.modal) {
            overlay.addEventListener('click', close);
        }
        
        // Click window to bring to front (non-modal only)
        if (!options.modal) {
            windowEl.addEventListener('mousedown', function() {
                if (windowEl.classList.contains('m-visible')) {
                    bringToFront();
                }
            });
        }

        // Escape key to close (can be disabled via data-close-on-esc="false")
        var closeOnEsc = windowEl.getAttribute('data-close-on-esc') !== 'false';
        document.addEventListener('keydown', function(e) {
            if (closeOnEsc && e.key === 'Escape' && windowEl.classList.contains('m-visible')) {
                close();
            }
        });

        // Draggable functionality
        if (options.draggable && titleBar && wrapper) {
            titleBar.addEventListener('mousedown', function(e) {
                if (e.target === closeBtn || closeBtn.contains(e.target)) {
                    return; // Don't drag when clicking close button
                }
                
                isDragging = true;
                dragStartX = e.clientX;
                dragStartY = e.clientY;
                
                const rect = wrapper.getBoundingClientRect();
                windowStartX = rect.left;
                windowStartY = rect.top;
                
                wrapper.style.position = 'fixed';
                wrapper.style.left = windowStartX + 'px';
                wrapper.style.top = windowStartY + 'px';
                wrapper.style.margin = '0';
                
                // Bring to front when starting drag
                if (!options.modal) {
                    bringToFront();
                }
                
                e.preventDefault();
            });
            
            document.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                
                const deltaX = e.clientX - dragStartX;
                const deltaY = e.clientY - dragStartY;
                
                wrapper.style.left = (windowStartX + deltaX) + 'px';
                wrapper.style.top = (windowStartY + deltaY) + 'px';
            });
            
            document.addEventListener('mouseup', function() {
                isDragging = false;
            });
        }

        // Handle action buttons
        windowEl.querySelectorAll('.m-window-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                
                if (action === 'close') {
                    close();
                } else {
                    utils.trigger(windowEl, 'm:window:action', { action, id });
                }
            });
        });

        /**
         * Inject HTML into an element, executing any embedded <script> tags.
         * Unlike innerHTML assignment, createContextualFragment preserves script execution.
         */
        function injectHtml(el, html) {
            el.innerHTML = '';
            try {
                var frag = document.createRange().createContextualFragment(html);
                el.appendChild(frag);
            } catch (e) {
                el.innerHTML = html;
            }
        }

        var fallbackErrorHtml = '<div class="partial-error">'
            + '<div class="partial-error__icon"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></div>'
            + '<div class="partial-error__body"><p class="partial-error__message">Failed to load content.</p></div>'
            + '</div>';

        const api = {
            open,
            close,
            element: windowEl,
            toggle: function() {
                if (windowEl.classList.contains('m-visible')) {
                    close();
                } else {
                    open();
                }
            },
            setTitle: function(html) {
                var titleEl = windowEl.querySelector('.m-window-title');
                if (titleEl) titleEl.innerHTML = html;
            },
            setContent: function(html) {
                var bodyEl = windowEl.querySelector('.m-window-content');
                if (bodyEl) injectHtml(bodyEl, html);
            },
            loadContent: function(url, fetchOpts) {
                var bodyEl = windowEl.querySelector('.m-window-content');
                if (!bodyEl) return Promise.resolve(null);

                // Show spinner immediately before the fetch starts
                injectHtml(bodyEl, '<div class="m-tabs-loader"><span class="m-loader-spinner" aria-hidden="true"></span></div>');

                if (m.ajax) {
                    return m.ajax(url, utils.extend({ method: 'GET' }, fetchOpts || {}))
                        .then(function(resp) {
                            var html = (typeof resp === 'string') ? resp : (resp && resp.html ? resp.html : '');
                            injectHtml(bodyEl, html);
                            utils.trigger(windowEl, 'm:window:content-loaded', { id: id });
                            return resp;
                        })
                        ['catch'](function(error) {
                            var html = (error && error.data && typeof error.data === 'string')
                                ? error.data
                                : fallbackErrorHtml;
                            injectHtml(bodyEl, html);
                        });
                }
                // Fallback: plain fetch
                return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) {
                        return r.text().then(function(html) {
                            if (!r.ok) {
                                injectHtml(bodyEl, html || fallbackErrorHtml);
                            } else {
                                injectHtml(bodyEl, html);
                            }
                            return html;
                        });
                    })
                    ['catch'](function() {
                        injectHtml(bodyEl, fallbackErrorHtml);
                    });
            }
        };

        // Cache instance on the element to prevent duplicate initialisation
        windowEl._mWindowInstance = api;
        return api;
    };

    /**
     * Dialog Component - Alert, Confirm, Prompt
     * Pure-JS overlays using the existing .m-dialog-* CSS classes.
     */

    function escDialogText(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str == null ? '' : String(str)));
        return div.innerHTML;
    }

    function openDialogOverlay() {
        var overlay = document.createElement('div');
        overlay.className = 'm-dialog-overlay';
        var dialog = document.createElement('div');
        dialog.className = 'm-dialog';
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
        // Double rAF to trigger CSS transition
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                overlay.classList.add('m-dialog-active');
            });
        });
        return { overlay: overlay, dialog: dialog };
    }

    function closeDialogOverlay(overlay) {
        overlay.classList.remove('m-dialog-active');
        setTimeout(function() {
            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 300);
    }

    function dialogHeader(title, iconClass) {
        var iconHtml = iconClass
            ? '<i class="fas ' + escDialogText(iconClass) + ' m-dialog-icon" aria-hidden="true"></i>'
            : '';
        return '<div class="m-dialog-header">' + iconHtml + '<h3>' + escDialogText(title) + '</h3></div>';
    }

    m.dialog = {
        confirm: function(message, title, iconClass) {
            return new Promise(function(resolve) {
                var parts   = openDialogOverlay();
                var overlay = parts.overlay;
                var dialog  = parts.dialog;

                dialog.innerHTML = dialogHeader(title || 'Confirm', iconClass || 'fa-question-circle')
                    + '<div class="m-dialog-body"><p>' + escDialogText(message) + '</p></div>'
                    + '<div class="m-dialog-footer">'
                    + '<button class="m-button m-dlg-cancel-btn">Cancel</button>'
                    + '<button class="m-button m-button-primary m-dlg-confirm-btn">Confirm</button>'
                    + '</div>';

                var done = false;
                function finish(result) {
                    if (done) return;
                    done = true;
                    closeDialogOverlay(overlay);
                    resolve(result);
                }

                dialog.querySelector('.m-dlg-confirm-btn').addEventListener('click', function() { finish(true); });
                dialog.querySelector('.m-dlg-cancel-btn').addEventListener('click', function() { finish(false); });
                overlay.addEventListener('click', function(e) { if (e.target === overlay) finish(false); });
                document.addEventListener('keydown', function onKey(e) {
                    if (!done && e.key === 'Escape') { document.removeEventListener('keydown', onKey); finish(false); }
                });
            });
        },

        alert: function(message, title, iconClass) {
            return new Promise(function(resolve) {
                var parts   = openDialogOverlay();
                var overlay = parts.overlay;
                var dialog  = parts.dialog;

                dialog.innerHTML = dialogHeader(title || 'Alert', iconClass || 'fa-info-circle')
                    + '<div class="m-dialog-body"><p>' + escDialogText(message) + '</p></div>'
                    + '<div class="m-dialog-footer">'
                    + '<button class="m-button m-button-primary m-dlg-ok-btn">OK</button>'
                    + '</div>';

                var done = false;
                function finish() {
                    if (done) return;
                    done = true;
                    closeDialogOverlay(overlay);
                    resolve();
                }

                dialog.querySelector('.m-dlg-ok-btn').addEventListener('click', finish);
                overlay.addEventListener('click', function(e) { if (e.target === overlay) finish(); });
                document.addEventListener('keydown', function onKey(e) {
                    if (!done && e.key === 'Escape') { document.removeEventListener('keydown', onKey); finish(); }
                });
            });
        }
    };

})(window);
