/**
 * Manhattan UI Framework — Popover Component
 *
 * A floating panel anchored to a trigger element.
 * Supports static content, AJAX remote content, hover/click triggers,
 * and smart viewport-aware placement (top/bottom/left/right/auto).
 *
 * PHP usage:
 *   <?= $m->popover('myPop')->trigger('myBtn')->content('<p>Hello</p>') ?>
 *
 * JS usage:
 *   var pop = m.popover('myPop');
 *   pop.show(triggerEl);
 *   pop.hide();
 *   pop.setContent('<p>Updated</p>');
 *   pop.loadContent('/api/data');
 *
 * Per-trigger data attributes (on any trigger element):
 *   data-m-popover="popoverId"   — links the element to a popover
 *   data-popover-url="..."       — override remote URL for this trigger
 *   data-popover-title="..."     — override title text for this trigger
 *   data-popover-content="..."   — override static content for this trigger
 *
 * Events (fired on the popover element):
 *   m:popover:show            — { id, trigger }
 *   m:popover:hide            — { id }
 *   m:popover:content-loaded  — { id, url }
 */

(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before popover module');
        return;
    }

    var utils = m.utils;

    /**
     * Inject HTML into an element including executing any embedded <script> tags.
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

    /**
     * Resolve the effective placement ('top'|'bottom'|'left'|'right') for a given
     * trigger rect, accounting for viewport space when placement is 'auto'.
     */
    function resolvePlacement(preferred, triggerRect) {
        if (preferred !== 'auto') return preferred;
        var spaceBelow = window.innerHeight - triggerRect.bottom;
        var spaceAbove = triggerRect.top;
        // Prefer bottom; only flip to top when above has more room and below is tight
        return (spaceAbove > spaceBelow && spaceBelow < 160) ? 'top' : 'bottom';
    }

    /**
     * Position the popover near triggerEl using page-absolute coordinates.
     * The popover must be a direct child of <body> (ensured during init) so that
     * `position: absolute` resolves against the document origin.
     *
     * After clamping to the viewport the arrow is shifted to always point at the
     * horizontal (top/bottom placements) or vertical (left/right placements)
     * centre of the trigger element.
     */
    function positionPopover(popEl, triggerEl, offset) {
        var rect = triggerEl.getBoundingClientRect();
        var preferred = popEl.getAttribute('data-placement') || 'auto';
        var placement = resolvePlacement(preferred, rect);
        var align     = popEl.getAttribute('data-align') || 'center';

        popEl.classList.remove('m-popover-top', 'm-popover-bottom', 'm-popover-left', 'm-popover-right');
        popEl.classList.add('m-popover-' + placement);

        // popover is never display:none — offsetWidth/Height are always valid
        var popW = popEl.offsetWidth;
        var popH = popEl.offsetHeight;

        var vw = document.documentElement.clientWidth;
        var vh = document.documentElement.clientHeight;
        // Page scroll offsets — convert viewport-relative rect to page-absolute coords
        var sx = window.scrollX !== undefined ? window.scrollX : window.pageXOffset;
        var sy = window.scrollY !== undefined ? window.scrollY : window.pageYOffset;
        var MARGIN = 8;

        var left, top;
        if (placement === 'bottom') {
            if (align === 'start') {
                left = sx + rect.left;
            } else if (align === 'end') {
                left = sx + rect.right - popW;
            } else {
                left = sx + rect.left + rect.width  / 2 - popW / 2;
            }
            top  = sy + rect.bottom + offset;
        } else if (placement === 'top') {
            if (align === 'start') {
                left = sx + rect.left;
            } else if (align === 'end') {
                left = sx + rect.right - popW;
            } else {
                left = sx + rect.left + rect.width  / 2 - popW / 2;
            }
            top  = sy + rect.top - popH - offset;
        } else if (placement === 'left') {
            left = sx + rect.left - popW - offset;
            top  = sy + rect.top + rect.height / 2 - popH / 2;
        } else { // right
            left = sx + rect.right + offset;
            top  = sy + rect.top + rect.height / 2 - popH / 2;
        }

        // Clamp to stay within the current viewport
        var clampedLeft = Math.max(sx + MARGIN, Math.min(left, sx + vw - popW - MARGIN));
        var clampedTop  = Math.max(sy + MARGIN, Math.min(top,  sy + vh - popH - MARGIN));

        popEl.style.left = clampedLeft + 'px';
        popEl.style.top  = clampedTop  + 'px';

        // Shift the arrow so it always points at the trigger's centre,
        // even when the popover box has been clamped away from ideal position.
        var arrowEl = popEl.querySelector('.m-popover-arrow');
        if (arrowEl) {
            arrowEl.style.left = '';
            arrowEl.style.top  = '';
            if (placement === 'bottom' || placement === 'top') {
                var triggerCX = sx + rect.left + rect.width  / 2;
                var arrowLeft = triggerCX - clampedLeft;
                // Keep arrow within the rounded corners of the popover
                arrowLeft = Math.max(16, Math.min(arrowLeft, popW - 16));
                arrowEl.style.left = arrowLeft + 'px';
            } else {
                var triggerCY = sy + rect.top  + rect.height / 2;
                var arrowTop  = triggerCY - clampedTop;
                arrowTop = Math.max(16, Math.min(arrowTop, popH - 16));
                arrowEl.style.top = arrowTop + 'px';
            }
        }
    }

    /**
     * popover(id) — create or retrieve a popover instance.
     */
    m.popover = function (id) {
        var popEl = utils.getElement(id);
        if (!popEl) {
            console.warn('Manhattan Popover: Element not found:', id);
            return null;
        }

        var triggerOn  = popEl.getAttribute('data-trigger-on') || 'hover';
        var delayShow  = parseInt(popEl.getAttribute('data-delay-show') || '200', 10);
        var delayHide  = parseInt(popEl.getAttribute('data-delay-hide') || '300', 10);
        var offset     = parseInt(popEl.getAttribute('data-offset')     || '8',   10);
        var defaultUrl = popEl.getAttribute('data-remote') || '';
        var useCache   = popEl.getAttribute('data-cache') !== 'false';

        // Reparent to <body> so `position: absolute` resolves against the document
        // origin regardless of where the PHP rendered the popover element.
        if (popEl.parentNode !== document.body) {
            document.body.appendChild(popEl);
        }

        var bodyEl  = popEl.querySelector('.m-popover-body');
        var titleEl = popEl.querySelector('.m-popover-title');

        var showTimer    = null;
        var hideTimer    = null;
        var activeTrigger = null;
        var cachedUrls   = {};

        var fallbackHtml = '<div class="m-popover-error">'
            + '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i> '
            + 'Failed to load content.</div>';

        function cancelTimers() {
            if (showTimer) { clearTimeout(showTimer); showTimer = null; }
            if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
        }

        /**
         * Fetch remote content and inject it into the body element.
         * @param {string} url
         * @param {boolean} forceRefresh  skip cache check when true
         */
        function loadRemote(url, forceRefresh) {
            if (!bodyEl) return;
            if (useCache && !forceRefresh && cachedUrls[url]) return;

            injectHtml(bodyEl,
                '<div class="m-popover-loading">'
                + '<span class="m-loader-spinner" aria-hidden="true"></span>'
                + '</div>'
            );

            var done = function (html) {
                injectHtml(bodyEl, html);
                if (useCache) cachedUrls[url] = true;
                utils.trigger(popEl, 'm:popover:content-loaded', { id: id, url: url });
            };

            var fail = function (err) {
                var html = (err && err.data && typeof err.data === 'string')
                    ? err.data
                    : fallbackHtml;
                injectHtml(bodyEl, html);
            };

            if (m.ajax) {
                m.ajax(url, { method: 'GET' })
                    .then(function (resp) {
                        var html = typeof resp === 'string'
                            ? resp
                            : (resp && resp.html ? resp.html : '');
                        done(html);
                    })
                    ['catch'](fail);
            } else {
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.text(); })
                    .then(done)
                    ['catch'](fail);
            }
        }

        /**
         * Show the popover anchored to triggerEl.
         */
        function show(triggerEl) {
            cancelTimers();
            activeTrigger = triggerEl;

            // Apply per-trigger overrides
            var overrideTitle   = triggerEl.getAttribute('data-popover-title')   || '';
            var overrideUrl     = triggerEl.getAttribute('data-popover-url')     || '';
            var overrideContent = triggerEl.getAttribute('data-popover-content') || '';

            if (overrideTitle && titleEl) {
                titleEl.textContent = overrideTitle;
            }

            if (overrideContent && bodyEl) {
                injectHtml(bodyEl, overrideContent);
            } else if (overrideUrl) {
                loadRemote(overrideUrl, true);
            } else if (defaultUrl) {
                loadRemote(defaultUrl, false);
            }

            positionPopover(popEl, triggerEl, offset);
            popEl.setAttribute('aria-hidden', 'false');
            popEl.classList.add('m-popover-visible');
            utils.trigger(popEl, 'm:popover:show', { id: id, trigger: triggerEl });
        }

        /**
         * Hide the popover.
         */
        function hide() {
            cancelTimers();
            activeTrigger = null;
            popEl.setAttribute('aria-hidden', 'true');
            popEl.classList.remove('m-popover-visible');
            utils.trigger(popEl, 'm:popover:hide', { id: id });
        }

        function scheduledShow(triggerEl) {
            cancelTimers();
            if (delayShow <= 0) {
                show(triggerEl);
            } else {
                showTimer = setTimeout(function () { show(triggerEl); }, delayShow);
            }
        }

        function scheduledHide() {
            cancelTimers();
            if (delayHide <= 0) {
                hide();
            } else {
                hideTimer = setTimeout(hide, delayHide);
            }
        }

        /**
         * Bind hover or click events to a trigger element.
         * Safe to call multiple times — guards against double-binding via data attribute.
         */
        function bindTrigger(el) {
            if (el._mPopoverBound) return;
            el._mPopoverBound = true;

            if (triggerOn === 'click') {
                el.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (popEl.classList.contains('m-popover-visible') && activeTrigger === el) {
                        hide();
                    } else {
                        show(el);
                    }
                });
            } else {
                // hover + keyboard focus
                el.addEventListener('mouseenter', function () { scheduledShow(el); });
                el.addEventListener('mouseleave', function () { scheduledHide(); });
                el.addEventListener('focus',      function () { scheduledShow(el); });
                el.addEventListener('blur',       function () { scheduledHide(); });
            }
        }

        // Keep popover open when the user moves the mouse onto it (hover mode)
        if (triggerOn === 'hover') {
            popEl.addEventListener('mouseenter', function () { cancelTimers(); });
            popEl.addEventListener('mouseleave', function () { scheduledHide(); });
        }

        // Click-outside to dismiss (click mode)
        if (triggerOn === 'click') {
            document.addEventListener('click', function (e) {
                if (popEl.classList.contains('m-popover-visible') && !popEl.contains(e.target)) {
                    hide();
                }
            });
        }

        // Escape key to close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && popEl.classList.contains('m-popover-visible')) {
                hide();
            }
        });

        // Reposition on viewport resize (absolute coords may need updating)
        window.addEventListener('resize', function () {
            if (popEl.classList.contains('m-popover-visible') && activeTrigger) {
                positionPopover(popEl, activeTrigger, offset);
            }
        });

        // --- Auto-bind configured triggers ---

        // Single element ID via data-trigger
        var singleId = popEl.getAttribute('data-trigger') || '';
        if (singleId) {
            var singleEl = document.getElementById(singleId);
            if (singleEl) bindTrigger(singleEl);
        }

        // CSS selector via data-trigger-selector
        var selector = popEl.getAttribute('data-trigger-selector') || '';
        if (selector) {
            var matched = document.querySelectorAll(selector);
            for (var i = 0; i < matched.length; i++) {
                bindTrigger(matched[i]);
            }
        }

        // Any element with data-m-popover="id" anywhere in the DOM
        var byAttr = document.querySelectorAll('[data-m-popover="' + id + '"]');
        for (var j = 0; j < byAttr.length; j++) {
            bindTrigger(byAttr[j]);
        }

        // --- Public API ---
        return {
            /** Show the popover anchored to a given trigger element. */
            show: show,

            /** Hide the popover. */
            hide: hide,

            /** Toggle visibility for a given trigger element. */
            toggle: function (triggerEl) {
                if (popEl.classList.contains('m-popover-visible') && activeTrigger === triggerEl) {
                    hide();
                } else {
                    show(triggerEl);
                }
            },

            /** Replace the popover body with arbitrary HTML. */
            setContent: function (html) {
                if (bodyEl) injectHtml(bodyEl, html);
            },

            /** Update the popover title text. */
            setTitle: function (text) {
                if (titleEl) titleEl.textContent = text;
            },

            /**
             * Fetch content from a URL and inject it into the body.
             * Returns the AJAX promise (or undefined when using plain fetch).
             */
            loadContent: function (url, forceRefresh) {
                return loadRemote(url, forceRefresh === true);
            },

            /**
             * Manually bind an element as a trigger for this popover.
             * Useful when triggers are added to the DOM dynamically.
             */
            bindTrigger: bindTrigger,

            /**
             * Re-scan the DOM for trigger elements matching the configured
             * selector or data-m-popover attribute. Call after dynamic DOM changes.
             */
            refresh: function () {
                if (selector) {
                    var els = document.querySelectorAll(selector);
                    for (var k = 0; k < els.length; k++) {
                        bindTrigger(els[k]);
                    }
                }
                var newByAttr = document.querySelectorAll('[data-m-popover="' + id + '"]');
                for (var l = 0; l < newByAttr.length; l++) {
                    bindTrigger(newByAttr[l]);
                }
            },

            /** The underlying popover DOM element. */
            element: popEl
        };
    };

    // Auto-initialise every .m-popover element on the page
    document.addEventListener('DOMContentLoaded', function () {
        var popovers = document.querySelectorAll('.m-popover[id]');
        for (var i = 0; i < popovers.length; i++) {
            m.popover(popovers[i].id);
        }
    });

})(window);
