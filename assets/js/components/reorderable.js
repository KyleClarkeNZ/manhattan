/**
 * Manhattan UI Framework - Reorderable Component
 * Drag-to-reorder sortable list with optional server-side persistence.
 */

(function(window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before reorderable module');
        return;
    }

    const utils = m.utils;

    m.reorderable = function(id, options) {
        const element = utils.getElement(id);
        if (!element) {
            console.warn('Manhattan: Reorderable element not found:', id);
            return null;
        }

        const dataset = element.dataset || {};
        options = utils.extend({
            updateModelOnReorder: dataset.updateModelOnReorder === '1',
            updateUrl: dataset.updateUrl || null,
            emptyMessage: dataset.emptyMessage || null,
            loaderText: dataset.loaderText || 'Saving...'
        }, options || {});

        element._manhattan = {
            type: 'reorderable',
            options: options
        };

        function getItems() {
            return Array.from(element.querySelectorAll('.m-reorderable-item'));
        }

        function getKey(itemEl) {
            return itemEl.getAttribute('data-key') || itemEl.id || '';
        }

        function getOrder() {
            return getItems().map(getKey).filter(Boolean);
        }

        function ensureEmptyState() {
            const existing = element.querySelector('.m-reorderable-empty');
            const hasItems = getItems().length > 0;
            if (hasItems) {
                if (existing) existing.remove();
                return;
            }
            if (!options.emptyMessage) return;
            if (existing) return;
            const emptyEl = utils.createElement('div', 'm-reorderable-empty', options.emptyMessage);
            element.appendChild(emptyEl);
        }

        function getLoaderElement() {
            return element.querySelector('.m-reorderable-loader');
        }

        function setLoading(isLoading) {
            const loader = getLoaderElement();
            if (!loader) return;
            loader.classList.toggle('m-hidden', !isLoading);
            element.classList.toggle('m-is-loading', !!isLoading);
        }

        function upsertItem(key, html, opts) {
            const stringKey = String(key);
            let item = element.querySelector('.m-reorderable-item[data-key="' + CSS.escape(stringKey) + '"]');
            if (!item && opts && opts.id) {
                item = document.getElementById(opts.id);
            }
            if (!item) {
                item = utils.createElement('div', 'm-reorderable-item');
                item.setAttribute('data-key', stringKey);
                item.draggable = true;
                element.appendChild(item);
            }
            if (opts && opts.className) {
                item.className = 'm-reorderable-item ' + opts.className;
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
            const item = element.querySelector('.m-reorderable-item[data-key="' + CSS.escape(stringKey) + '"]');
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

        // --- Drag/drop reordering ---
        var dragEl = null;

        function onDragStart(e) {
            const target = e.target.closest('.m-reorderable-item');
            if (!target) return;
            dragEl = target;
            target.classList.add('m-reorderable-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', getKey(target));
        }

        function onDragOver(e) {
            const over = e.target.closest('.m-reorderable-item');
            if (!over || over === dragEl) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            over.classList.add('m-reorderable-drop-target');
        }

        function clearDropTargets() {
            getItems().forEach(function(i) { i.classList.remove('m-reorderable-drop-target'); });
        }

        function onDragLeave(e) {
            const over = e.target.closest('.m-reorderable-item');
            if (!over) return;
            over.classList.remove('m-reorderable-drop-target');
        }

        function onDrop(e) {
            const dropTarget = e.target.closest('.m-reorderable-item');
            if (!dropTarget || !dragEl || dropTarget === dragEl) return;
            e.preventDefault();
            const rect = dropTarget.getBoundingClientRect();
            const after = (e.clientY - rect.top) > (rect.height / 2);
            if (after) {
                dropTarget.after(dragEl);
            } else {
                dropTarget.before(dragEl);
            }
            clearDropTargets();
            emitReorder();
        }

        function onDragEnd() {
            if (dragEl) dragEl.classList.remove('m-reorderable-dragging');
            dragEl = null;
            clearDropTargets();
        }

        function emitReorder() {
            const order = getOrder();
            utils.trigger(element, 'm:reorderable:reorder', { id: id, order: order });

            if (options.updateModelOnReorder && options.updateUrl) {
                m.ajax(options.updateUrl, {
                    method: 'POST',
                    data: { order: order },
                    beforeSend: function() { setLoading(true); },
                    success: function(resp) {
                        utils.trigger(element, 'm:reorderable:saved', { id: id, order: order, response: resp });
                    },
                    error: function(err) {
                        utils.trigger(element, 'm:reorderable:error', { id: id, order: order, error: err });
                    },
                    complete: function() { setLoading(false); }
                });
            }
        }

        // Bind drag listeners
        element.addEventListener('dragstart', onDragStart);
        element.addEventListener('dragover', onDragOver);
        element.addEventListener('dragleave', onDragLeave);
        element.addEventListener('drop', onDrop);
        element.addEventListener('dragend', onDragEnd);

        // Ensure all server-rendered items are draggable
        getItems().forEach(function(item) { item.draggable = true; });

        ensureEmptyState();

        return {
            element: element,
            options: options,
            getOrder: getOrder,
            upsertItem: upsertItem,
            removeItem: removeItem,
            addItem: addItem,
            clear: clear,
            count: count,
            getItems: function() { return getItems(); }
        };
    };

})(window);
