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

    m.list = function(id, options) {
        const element = utils.getElement(id);
        if (!element) {
            console.warn('Manhattan: List element not found:', id);
            return null;
        }

        const dataset = element.dataset || {};
        options = utils.extend({
            reorderable: dataset.reorderable === '1',
            updateModelOnReorder: dataset.updateModelOnReorder === '1',
            updateUrl: dataset.updateUrl || null,
            emptyMessage: dataset.emptyMessage || null,
            keyAttribute: 'data-key',
            useLoader: dataset.useLoader !== '0',
            loaderText: dataset.loaderText || 'Saving...'
        }, options || {});

        element._manhattan = {
            type: 'list',
            options
        };

        function getItems() {
            return Array.from(element.querySelectorAll('.m-list-item'));
        }

        function getKey(itemEl) {
            return itemEl.getAttribute('data-key') || itemEl.id || '';
        }

        function getOrder() {
            return getItems().map(getKey).filter(Boolean);
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

        function getLoaderElement() {
            return element.querySelector('.m-list-loader');
        }

        function setLoading(isLoading) {
            if (!options.useLoader) return;
            const loader = getLoaderElement();
            if (!loader) return;
            loader.classList.toggle('m-hidden', !isLoading);
            element.classList.toggle('m-is-loading', !!isLoading);

            const textEl = loader.querySelector('.m-loader-text');
            if (textEl && options.loaderText) {
                textEl.textContent = options.loaderText;
            }
        }

        function setReorderable(enabled) {
            options.reorderable = !!enabled;
            element.classList.toggle('m-list-reorderable', options.reorderable);
            element.setAttribute('data-reorderable', options.reorderable ? '1' : '0');

            getItems().forEach(item => {
                item.draggable = options.reorderable;
            });
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
            item.draggable = options.reorderable;
            ensureEmptyState();
            return item;
        }

        function removeItem(key) {
            const stringKey = String(key);
            const item = element.querySelector('.m-list-item[data-key="' + CSS.escape(stringKey) + '"]') || document.getElementById('task_' + stringKey);
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
            setLoading(true);
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
                setLoading(false);
                utils.trigger(element, 'm:list:refresh', { id: id, items: items });
                return items;
            }).catch(function(err) {
                setLoading(false);
                utils.trigger(element, 'm:list:refresh:error', { id: id, error: err });
                throw err;
            });
        }

        // --- Drag/drop reordering ---
        let dragEl = null;

        function onDragStart(e) {
            if (!options.reorderable) return;
            const target = e.target.closest('.m-list-item');
            if (!target) return;
            dragEl = target;
            target.classList.add('m-list-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', getKey(target));
        }

        function onDragOver(e) {
            if (!options.reorderable) return;
            const over = e.target.closest('.m-list-item');
            if (!over || over === dragEl) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            over.classList.add('m-list-drop-target');
        }

        function clearDropTargets() {
            getItems().forEach(i => i.classList.remove('m-list-drop-target'));
        }

        function onDragLeave(e) {
            const over = e.target.closest('.m-list-item');
            if (!over) return;
            over.classList.remove('m-list-drop-target');
        }

        function onDrop(e) {
            if (!options.reorderable) return;
            const dropTarget = e.target.closest('.m-list-item');
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
            if (dragEl) dragEl.classList.remove('m-list-dragging');
            dragEl = null;
            clearDropTargets();
        }

        function emitReorder() {
            const order = getOrder();
            utils.trigger(element, 'm:list:reorder', { id, order });

            if (options.updateModelOnReorder && options.updateUrl) {
                m.ajax(options.updateUrl, {
                    method: 'POST',
                    data: { order },
                    beforeSend: function() {
                        setLoading(true);
                    },
                    success: function(resp) {
                        utils.trigger(element, 'm:list:reorder:saved', { id, order, response: resp });
                    },
                    error: function(err) {
                        utils.trigger(element, 'm:list:reorder:error', { id, order, error: err });
                    },
                    complete: function() {
                        setLoading(false);
                    }
                });
            }
        }

        // Bind listeners once
        element.addEventListener('dragstart', onDragStart);
        element.addEventListener('dragover', onDragOver);
        element.addEventListener('dragleave', onDragLeave);
        element.addEventListener('drop', onDrop);
        element.addEventListener('dragend', onDragEnd);

        // Initial state
        setReorderable(options.reorderable);
        ensureEmptyState();

        return {
            element,
            options,
            getOrder,
            setReorderable,
            upsertItem,
            removeItem,
            addItem,
            clear,
            count,
            getItems: function() { return getItems(); },
            refresh
        };
    };

    /**
     * Window/Modal Component
     * Creates modal dialogs with dragging and animation
     */

})(window);
