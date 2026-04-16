/**
 * Manhattan UI Framework - List Component
 * Dynamic display list with item management and optional remote refresh.
 */

(function(window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before list module');
        return;
    }

    const utils = m.utils;

    m.list = function(id, options) {
        const element = utils.getElement(id);
        if (!element) {
            console.warn('Manhattan: List element not found:', id);
            return null;
        }

        const dataset = element.dataset || {};
        options = utils.extend({
            emptyMessage: dataset.emptyMessage || null,
            dataUrl: dataset.dataUrl || null
        }, options || {});

        element._manhattan = {
            type: 'list',
            options: options
        };

        function getItems() {
            return Array.from(element.querySelectorAll('.m-list-item'));
        }

        function getKey(itemEl) {
            return itemEl.getAttribute('data-key') || itemEl.id || '';
        }

        function ensureEmptyState() {
            const existing = element.querySelector('.m-list-empty');
            const hasItems = getItems().length > 0;
            if (hasItems) {
                if (existing) existing.remove();
                return;
            }
            if (!options.emptyMessage) return;
            if (existing) return;
            const emptyEl = utils.createElement('div', 'm-list-empty', options.emptyMessage);
            element.appendChild(emptyEl);
        }

        function upsertItem(key, html, opts) {
            const stringKey = String(key);
            let item = element.querySelector('.m-list-item[data-key="' + CSS.escape(stringKey) + '"]');
            if (!item && opts && opts.id) {
                item = document.getElementById(opts.id);
            }
            if (!item) {
                item = utils.createElement('div', 'm-list-item');
                item.setAttribute('data-key', stringKey);
                element.appendChild(item);
            }
            if (opts && opts.className) {
                item.className = 'm-list-item ' + opts.className;
            }
            if (opts && opts.id) {
                item.id = opts.id;
            }
            item.innerHTML = html;
            ensureEmptyState();
            return item;
        }

        function removeItem(key) {
            const stringKey = String(key);
            const item = element.querySelector('.m-list-item[data-key="' + CSS.escape(stringKey) + '"]') ||
                document.getElementById('task_' + stringKey);
            if (item) item.remove();
            ensureEmptyState();
        }

        function clear() {
            getItems().forEach(function(item) { item.remove(); });
            ensureEmptyState();
        }

        function count() {
            return getItems().length;
        }

        function addItem(key, html, opts) {
            return upsertItem(key, html, opts);
        }

        function refresh(url, opts) {
            var fetchUrl = url || options.dataUrl || null;
            if (!fetchUrl) {
                console.warn('Manhattan List: No URL provided for refresh');
                return Promise.resolve([]);
            }
            return m.ajax(fetchUrl, utils.extend({
                method: 'GET',
                dataType: 'json'
            }, opts || {})).then(function(resp) {
                var items = Array.isArray(resp) ? resp : (resp && resp.items ? resp.items : []);
                clear();
                items.forEach(function(item) {
                    upsertItem(
                        item.key || item.id || '',
                        item.html || item.content || '',
                        { id: item.id || null, className: item.className || item['class'] || '' }
                    );
                });
                utils.trigger(element, 'm:list:refresh', { id: id, items: items });
                return items;
            }).catch(function(err) {
                utils.trigger(element, 'm:list:refresh:error', { id: id, error: err });
                throw err;
            });
        }

        ensureEmptyState();

        return {
            element: element,
            options: options,
            getOrder: function() { return getItems().map(getKey).filter(Boolean); },
            upsertItem: upsertItem,
            removeItem: removeItem,
            addItem: addItem,
            clear: clear,
            count: count,
            getItems: function() { return getItems(); },
            refresh: refresh
        };
    };

})(window);
