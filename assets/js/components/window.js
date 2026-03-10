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
        const defaults = {
            title: '',
            content: '',
            modal: true,
            draggable: false,
            width: '600px',
            height: 'auto',
            buttons: [],
            onClose: null,
            onOpen: null
        };

        options = utils.extend({}, defaults, options);
        
        const windowEl = utils.getElement(id);
        if (!windowEl) {
            console.warn('Manhattan Window: Element not found:', id);
            return null;
        }

        const overlay = windowEl.querySelector('.m-window-overlay');
        const wrapper = windowEl.querySelector('.m-window-wrapper');
        const closeBtn = windowEl.querySelector('.m-window-close');
        const titleBar = windowEl.querySelector('.m-window-titlebar');

        let isDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;
        let windowStartX = 0;
        let windowStartY = 0;

        // Open window
        const open = function() {
            windowEl.classList.add('m-visible');
            document.body.style.overflow = 'hidden'; // Prevent scroll
            
            if (options.onOpen) {
                options.onOpen();
            }
            
            utils.trigger(windowEl, 'm:window:open', { id });
        };

        // Close window
        const close = function() {
            windowEl.classList.remove('m-visible');
            document.body.style.overflow = ''; // Restore scroll
            
            if (options.onClose) {
                options.onClose();
            }
            
            utils.trigger(windowEl, 'm:window:close', { id });
        };

        // Close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', close);
        }

        // Overlay click (close modal)
        if (overlay) {
            overlay.addEventListener('click', close);
        }

        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && windowEl.classList.contains('m-visible')) {
                close();
            }
        });

        // Draggable functionality
        if (options.draggable && titleBar && wrapper) {
            titleBar.style.cursor = 'move';
            
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

        return {
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
                var bodyEl = windowEl.querySelector('.m-window-body');
                if (bodyEl) injectHtml(bodyEl, html);
            },
            loadContent: function(url, fetchOpts) {
                var bodyEl = windowEl.querySelector('.m-window-body');
                if (!bodyEl) return Promise.resolve(null);
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
    };

    /**
     * Dialog Component - Alert, Confirm, Prompt
     */

})(window);
