/**
 * Manhattan UI Framework
 * Namespace: m
 *
 * Core bootstrap + shared utilities.
 * Components are defined in separate module files under /Manhattan/JS/components.
 */

(function(window) {
    'use strict';

    // Main Manhattan namespace (global)
    const m = window.m || {};

    // Utilities shared across modules
    const utils = {
        getElement: function(selector) {
            if (typeof selector === 'string') {
                return selector.startsWith('#') ?
                    document.querySelector(selector) :
                    document.getElementById(selector);
            }
            return selector;
        },

        extend: function(target, ...sources) {
            sources.forEach(source => {
                if (source) {
                    Object.keys(source).forEach(key => {
                        target[key] = source[key];
                    });
                }
            });
            return target;
        },

        trigger: function(element, eventName, data) {
            const event = new CustomEvent(eventName, {
                detail: data,
                bubbles: true,
                cancelable: true
            });
            element.dispatchEvent(event);
            return event;
        },

        formatDate: function(date, format) {
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');

            return format
                .replace('Y', year)
                .replace('m', month)
                .replace('d', day);
        },

        parseDate: function(dateString, format) {
            if (!dateString) return null;
            const parts = dateString.split('-');
            if (parts.length === 3) {
                return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            }
            return new Date(dateString);
        },

        createElement: function(tag, className, content) {
            const el = document.createElement(tag);
            if (className) el.className = className;
            if (content) el.innerHTML = content;
            return el;
        },

        normalizeFaClasses: function(faName, defaultStyle) {
            defaultStyle = (defaultStyle || 'fas').trim() || 'fas';
            faName = (faName || '').trim();
            if (!faName) return '';

            // Full class list provided
            if (faName.indexOf(' ') !== -1) {
                if (/(fas|far|fab|fal|fad)/.test(faName)) {
                    return faName;
                }
                return (defaultStyle + ' ' + faName).trim();
            }

            // Single token: treat fa-... as icon name
            if (faName.startsWith('fa-')) {
                return (defaultStyle + ' ' + faName).trim();
            }

            // Be forgiving: allow "edit" instead of "fa-edit"
            return (defaultStyle + ' fa-' + faName).trim();
        },

        createIconElement: function(faName, options) {
            options = options || {};

            const classes = utils.normalizeFaClasses(faName, options.style || 'fas');
            if (!classes) return null;

            const el = document.createElement('i');
            el.className = (classes + (options.className ? (' ' + options.className) : '')).trim();

            const ariaHidden = options.ariaHidden !== undefined ? !!options.ariaHidden : true;
            if (options.ariaLabel) {
                el.setAttribute('role', 'img');
                el.setAttribute('aria-label', String(options.ariaLabel));
            } else if (ariaHidden) {
                el.setAttribute('aria-hidden', 'true');
            }

            if (options.title) {
                el.setAttribute('title', String(options.title));
            }

            return el;
        },

        /**
         * Nearest ancestor that would clip an absolutely-positioned panel —
         * a scroll container, a modal body, or any card with overflow:hidden.
         * Returns null when nothing between `el` and the body clips.
         */
        getClippingParent: function(el) {
            const vw = window.innerWidth  || document.documentElement.clientWidth;
            const vh = window.innerHeight || document.documentElement.clientHeight;
            let p = el.parentElement;

            while (p && p !== document.body && p !== document.documentElement) {
                const style    = window.getComputedStyle(p);
                const overflow = (style.overflow || '') + ' ' + (style.overflowY || '') + ' ' + (style.overflowX || '');

                if (/(auto|scroll|hidden|clip)/.test(overflow)) {
                    const rect = p.getBoundingClientRect();
                    // Only clipping if it is actually smaller than the viewport
                    // on at least one axis.
                    if (rect.height < vh || rect.width < vw) {
                        return p;
                    }
                }
                p = p.parentElement;
            }
            return null;
        },

        /**
         * The rect a popup should be positioned within — the nearest
         * overflow-constrained ancestor, else the viewport.
         */
        getBoundaryRect: function(el) {
            const vh = window.innerHeight || document.documentElement.clientHeight;
            const vw = window.innerWidth  || document.documentElement.clientWidth;

            let p = el.parentElement;
            while (p && p !== document.body) {
                const style    = window.getComputedStyle(p);
                const overflow = (style.overflowY || '') + ' ' + (style.overflowX || '');

                if (/(auto|scroll|hidden|clip)/.test(overflow)) {
                    return p.getBoundingClientRect();
                }
                p = p.parentElement;
            }

            return { top: 0, left: 0, right: vw, bottom: vh };
        },

        /**
         * Escape an overflow-clipping ancestor by switching `panel` to fixed
         * positioning derived from `trigger`'s bounding rect.
         *
         * Without this, a calendar or time panel opened inside a card
         * (.m-card-default is overflow: hidden, to clip content to its rounded
         * corners) or inside a scrollable modal body is cut off at the
         * container edge. Consumers used to have to set overflow: visible on
         * the container, which gave up the rounded-corner clipping.
         *
         * No-op — returns false — when nothing clips, so the normal
         * absolutely-positioned path (and its CSS animations) is kept.
         *
         * @param {Element}  panel
         * @param {Element}  trigger
         * @param {Object}   [options] openUp, alignRight, matchWidth, onScroll
         * @returns {boolean} whether the panel was pinned
         */
        pinToTrigger: function(panel, trigger, options) {
            options = options || {};

            const clipper = utils.getClippingParent(trigger);
            if (!clipper) return false;

            const rect = trigger.getBoundingClientRect();
            const vw = window.innerWidth  || document.documentElement.clientWidth;
            const vh = window.innerHeight || document.documentElement.clientHeight;

            panel.style.position = 'fixed';
            panel.style.zIndex   = '99999';
            if (options.matchWidth) {
                panel.style.width = rect.width + 'px';
            }

            if (options.openUp) {
                panel.style.top    = 'auto';
                panel.style.bottom = (vh - rect.top) + 'px';
            } else {
                panel.style.top    = rect.bottom + 'px';
                panel.style.bottom = 'auto';
            }

            if (options.alignRight) {
                panel.style.left  = 'auto';
                panel.style.right = (vw - rect.right) + 'px';
            } else {
                panel.style.left  = rect.left + 'px';
                panel.style.right = 'auto';
            }

            // Fixed coordinates go stale the moment the container scrolls.
            if (typeof options.onScroll === 'function' && !panel._mPinScroll) {
                panel._mPinScroll   = options.onScroll;
                panel._mPinScroller = clipper;
                clipper.addEventListener('scroll', panel._mPinScroll, { passive: true });
            }

            return true;
        },

        /** Undo pinToTrigger, restoring the panel's stylesheet positioning. */
        unpin: function(panel) {
            panel.style.position = '';
            panel.style.zIndex   = '';
            panel.style.width    = '';
            panel.style.top      = '';
            panel.style.bottom   = '';
            panel.style.left     = '';
            panel.style.right    = '';

            if (panel._mPinScroll && panel._mPinScroller) {
                panel._mPinScroller.removeEventListener('scroll', panel._mPinScroll);
                panel._mPinScroll   = null;
                panel._mPinScroller = null;
            }
        },

        ready: function(callback) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', callback);
            } else {
                callback();
            }
        }
    };

    m.utils = utils;

    /**
     * Overlay registry — "only one popup surface open at a time".
     *
     * Every component that opens a floating panel (dropdown list, calendar,
     * time panel, icon grid, address suggestions, rich-text toolbar menu…)
     * registers its root element together with a close callback, then calls
     * `closeOthers(root)` immediately before it shows its panel.
     *
     * Why this has to be central: each of those components calls
     * `e.stopPropagation()` on its own trigger so that its own
     * "close on outside click" document listener does not immediately undo the
     * click that opened it. That stopPropagation also prevents EVERY OTHER
     * component's document listener from ever seeing the event, so an already
     * open panel elsewhere on the page stays open. Components cannot solve this
     * for themselves — dropdown.js used to scan for other `.m-dropdown-custom`
     * elements, which fixed dropdown-versus-dropdown but left a dropdown open
     * behind an opening datepicker, and so on for every other pairing.
     *
     * Nesting is respected: opening a panel that lives inside another open
     * overlay (a dropdown inside a dialog, a datepicker inside a popover) does
     * not close its host.
     */
    const overlays = {
        _entries: [],

        /**
         * Register a closable overlay.
         * Re-registering the same root replaces the previous close callback,
         * so component re-initialisation does not stack duplicates.
         *
         * @param {Element}  root  Element containing both trigger and panel.
         * @param {Function} close Called to close this overlay.
         */
        register: function(root, close) {
            if (!root || typeof close !== 'function') return null;

            for (let i = 0; i < overlays._entries.length; i++) {
                if (overlays._entries[i].root === root) {
                    overlays._entries[i].close = close;
                    return overlays._entries[i];
                }
            }

            const entry = { root: root, close: close };
            overlays._entries.push(entry);
            return entry;
        },

        /** Forget an overlay (e.g. its component was destroyed). */
        unregister: function(root) {
            overlays._entries = overlays._entries.filter(entry => entry.root !== root);
        },

        /**
         * Close every registered overlay except `root` and its ancestors /
         * descendants. Pass nothing to close all of them.
         */
        closeOthers: function(root) {
            // Drop entries whose element has been removed from the document.
            // Components that build rows dynamically (add/remove a repeating
            // form row) would otherwise leak a registration per row.
            overlays._entries = overlays._entries.filter(
                entry => entry.root === root || document.contains(entry.root)
            );

            overlays._entries.forEach(function(entry) {
                if (root && (entry.root === root
                    || entry.root.contains(root)
                    || root.contains(entry.root))) {
                    return;
                }
                try {
                    entry.close();
                } catch (err) {
                    console.warn('Manhattan: overlay close failed', err);
                }
            });
        },

        /** Close every registered overlay. */
        closeAll: function() {
            overlays.closeOthers(null);
        }
    };

    m.overlays  = overlays;
    utils.overlays = overlays;

    /**
     * Icon helper
     * Returns an HTML string (for templates) for a Font Awesome icon.
     */
    m.icon = function(faName, options) {
        const el = utils.createIconElement(faName, options);
        return el ? el.outerHTML : '';
    };

    // Expose Manhattan globally
    window.m = m;

    // Auto-initialize components (modules register methods on m)
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof m.button === 'function') {
            document.querySelectorAll('[data-m-button]').forEach(el => {
                m.button(el.id || el, {});
            });
        }

        if (typeof m.datepicker === 'function') {
            document.querySelectorAll('.m-datepicker').forEach(el => {
                m.datepicker(el.id || el, {});
            });
        }

        if (typeof m.timepicker === 'function') {
            document.querySelectorAll('.m-timepicker').forEach(el => {
                if (el.id) m.timepicker(el.id, {});
            });
        }

        if (typeof m.dropdown === 'function') {
            document.querySelectorAll('.m-dropdown').forEach(el => {
                m.dropdown(el.id || el, {});
            });
        }

        if (typeof m.textbox === 'function') {
            document.querySelectorAll('.m-textbox').forEach(el => {
                m.textbox(el.id || el, {});
            });
        }

        if (typeof m.address === 'function') {
            document.querySelectorAll('.m-address').forEach(el => {
                m.address(el.id || el, {});
            });
        }

        if (typeof m.textarea === 'function') {
            document.querySelectorAll('.m-textarea').forEach(el => {
                const autoResize = el.classList.contains('m-textarea-resize-auto');
                m.textarea(el.id || el, { autoResize: autoResize });
            });
        }

        if (typeof m.window === 'function') {
            document.querySelectorAll('.m-window').forEach(el => {
                m.window(el.id || el, {});
            });
        }

        if (typeof m.tabs === 'function') {
            document.querySelectorAll('.m-tabs').forEach(el => {
                m.tabs(el.id || el, {});
            });
        }

        if (typeof m.wizard === 'function') {
            document.querySelectorAll('.m-wizard').forEach(el => {
                if (el.id) m.wizard(el.id, {});
            });
        }

        if (typeof m.richTextEditor === 'function') {
            document.querySelectorAll('[data-component="richtexteditor"]').forEach(el => {
                if (el.id) m.richTextEditor(el.id);
            });
        }

        if (typeof m.filterBar === 'function') {
            document.querySelectorAll('.m-filter-bar').forEach(el => {
                if (el.id) m.filterBar(el.id);
            });
        }

        if (typeof m.reorderable === 'function') {
            document.querySelectorAll('.m-reorderable').forEach(el => {
                if (el.id) m.reorderable(el.id);
            });
        }
    });

})(window);
