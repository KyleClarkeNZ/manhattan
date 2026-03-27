/**
 * Manhattan SplitPane Component
 * Draggable divider between two panes with min/max constraints.
 * Persists size to localStorage. Keyboard-accessible (Arrow keys on the divider).
 */
(function () {
    'use strict';

    var STORAGE_PREFIX = 'm-split-';

    function initAll() {
        var containers = document.querySelectorAll('.m-split-pane[data-component="splitpane"]');
        for (var i = 0; i < containers.length; i++) {
            initOne(containers[i]);
        }
    }

    function initOne(container) {
        if (container.dataset.mInitialized) return;
        container.dataset.mInitialized = 'true';

        var id          = container.id;
        var dir         = container.dataset.direction || 'horizontal';
        var isHoriz     = dir !== 'vertical';
        var initialSize = parseInt(container.dataset.initialSize, 10) || 300;
        var minSize     = parseInt(container.dataset.minSize,     10) || 0;
        var maxSize     = parseInt(container.dataset.maxSize,     10) || 9999;

        var firstPane   = container.querySelector('.m-split-pane__first');
        var divider     = container.querySelector('.m-split-pane__divider');
        var secondPane  = container.querySelector('.m-split-pane__second');

        if (!firstPane || !divider || !secondPane) return;

        // ── Restore persisted size ─────────────────────────────────────────────
        var storedSize = id ? parseInt(localStorage.getItem(STORAGE_PREFIX + id), 10) : NaN;
        var currentSize = (!isNaN(storedSize) && storedSize >= minSize && storedSize <= maxSize)
            ? storedSize
            : initialSize;

        applySize(currentSize);

        // ── Drag handling ──────────────────────────────────────────────────────
        var dragging    = false;
        var startPos    = 0;
        var startSize   = 0;

        divider.addEventListener('mousedown', onMouseDown);
        divider.addEventListener('touchstart', onTouchStart, { passive: true });

        function onMouseDown(e) {
            if (e.button !== 0) return;
            e.preventDefault();
            startDrag(isHoriz ? e.clientX : e.clientY);
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup',   onMouseUp);
        }
        function onMouseMove(e) {
            if (!dragging) return;
            updateDrag(isHoriz ? e.clientX : e.clientY);
        }
        function onMouseUp() {
            endDrag();
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup',   onMouseUp);
        }

        function onTouchStart(e) {
            var t = e.touches[0];
            startDrag(isHoriz ? t.clientX : t.clientY);
            document.addEventListener('touchmove', onTouchMove, { passive: false });
            document.addEventListener('touchend',  onTouchEnd);
        }
        function onTouchMove(e) {
            if (!dragging) return;
            e.preventDefault();
            var t = e.touches[0];
            updateDrag(isHoriz ? t.clientX : t.clientY);
        }
        function onTouchEnd() {
            endDrag();
            document.removeEventListener('touchmove', onTouchMove);
            document.removeEventListener('touchend',  onTouchEnd);
        }

        function startDrag(pos) {
            dragging   = true;
            startPos   = pos;
            startSize  = currentSize;
            container.classList.add('m-split-pane--dragging');
        }

        function updateDrag(pos) {
            var delta   = pos - startPos;
            var newSize = clamp(startSize + delta, minSize, maxSize);
            applySize(newSize);
        }

        function endDrag() {
            if (!dragging) return;
            dragging = false;
            container.classList.remove('m-split-pane--dragging');
            if (id) localStorage.setItem(STORAGE_PREFIX + id, String(currentSize));
            divider.setAttribute('aria-valuenow', String(currentSize));
            triggerResize();
        }

        // ── Keyboard resize (Arrow keys on divider) ────────────────────────────
        divider.addEventListener('keydown', function (e) {
            var step  = e.shiftKey ? 50 : 10;
            var delta = 0;
            if      (e.key === 'ArrowRight' || e.key === 'ArrowDown')  delta = +step;
            else if (e.key === 'ArrowLeft'  || e.key === 'ArrowUp')    delta = -step;
            else if (e.key === 'Home') { applySize(minSize); e.preventDefault(); saveAndNotify(); return; }
            else if (e.key === 'End')  { applySize(maxSize === 9999 ? currentSize : maxSize); e.preventDefault(); saveAndNotify(); return; }
            else return;

            e.preventDefault();
            applySize(clamp(currentSize + delta, minSize, maxSize));
            saveAndNotify();
        });

        // ── Helpers ───────────────────────────────────────────────────────────
        function applySize(size) {
            size = clamp(size, minSize, maxSize);
            currentSize = size;
            if (isHoriz) {
                firstPane.style.width  = size + 'px';
                firstPane.style.height = '';
            } else {
                firstPane.style.height = size + 'px';
                firstPane.style.width  = '';
            }
            divider.setAttribute('aria-valuenow', String(size));
        }

        function saveAndNotify() {
            if (id) localStorage.setItem(STORAGE_PREFIX + id, String(currentSize));
            triggerResize();
        }

        function triggerResize() {
            // Dispatch a custom event so consumers (e.g. charts, editors) can reflow
            try {
                container.dispatchEvent(new CustomEvent('m:splitpane:resize', {
                    bubbles: true,
                    detail: { id: id, size: currentSize, direction: dir }
                }));
                // Also fire window resize so third-party libs reflow
                window.dispatchEvent(new Event('resize'));
            } catch (ex) { /* IE fallback – ignore */ }
        }

        function clamp(val, lo, hi) {
            return Math.min(Math.max(val, lo), hi);
        }

        // ── Public API ─────────────────────────────────────────────────────────
        container._mSplitPane = {
            getSize:  function () { return currentSize; },
            setSize:  function (px) { applySize(px); saveAndNotify(); },
            reset:    function () { applySize(initialSize); saveAndNotify(); }
        };
    }

    // ── Register on m namespace ──────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        if (window.m) {
            window.m.splitPane = function (id) {
                var el = typeof id === 'string' ? document.getElementById(id) : id;
                return el ? el._mSplitPane || null : null;
            };
        }
        initAll();
    });

})();
