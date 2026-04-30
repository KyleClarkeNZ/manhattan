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
    });

})(window);
