/**
 * Manhattan Lightbox Component
 *
 * Full-screen image overlay viewer with keyboard and pointer navigation.
 *
 * Auto-initialised on any .m-lightbox element at DOMContentLoaded.
 * Manual init: var lb = m.lightbox('myId');
 *
 * JS API:
 *   lb.show(index, images)  – open at index; images is optional array of
 *                              {src, caption} objects (overrides pre-loaded)
 *   lb.hide()               – close
 *   lb.prev()               – go to previous image
 *   lb.next()               – go to next image
 *   lb.getIndex()           – return current index
 *
 * Events (dispatched on the lightbox element):
 *   m:lightbox:open   – { index }
 *   m:lightbox:close  – {}
 *   m:lightbox:change – { index }
 */
(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan Lightbox: core not loaded');
        return;
    }

    var utils = m.utils;

    // Instance cache — keyed by element.id so imageviewer can look up the API
    var instances = {};

    /**
     * Initialise (or re-retrieve) a lightbox.
     *
     * @param {string|Element} id
     * @param {Array}          [images]  Optional pre-loaded image list override
     * @returns {object|null}
     */
    m.lightbox = function (id, images) {
        var el = utils.getElement(id);
        if (!el) return null;

        // Return cached instance (allows imageviewer to call m.lightbox(id) safely)
        var elId = el.id || el;
        if (instances[elId]) {
            return instances[elId];
        }

        var backdropEl  = el.querySelector('.m-lightbox-backdrop');
        var closeBtn    = el.querySelector('.m-lightbox-close');
        var prevBtn     = el.querySelector('.m-lightbox-nav--prev');
        var nextBtn     = el.querySelector('.m-lightbox-nav--next');
        var imgEl       = el.querySelector('.m-lightbox-img');
        var captionEl   = el.querySelector('.m-lightbox-caption');
        var counterEl   = el.querySelector('.m-lightbox-counter');

        // Image list — can be loaded from data attribute or provided at show() time
        var imageList   = [];
        var currentIdx  = 0;

        // Load optional pre-populated images from PHP data attribute
        var dataImages = el.getAttribute('data-m-images');
        if (dataImages) {
            try {
                var parsed = JSON.parse(dataImages);
                if (Array.isArray(parsed)) {
                    imageList = parsed;
                }
            } catch (e) { /* ignore malformed JSON */ }
        }

        // Override with caller-supplied images
        if (images && Array.isArray(images) && images.length) {
            imageList = images;
        }

        // ── Helpers ────────────────────────────────────────────────────────

        function updateView() {
            var item = imageList[currentIdx];
            if (!item) return;

            if (imgEl) {
                imgEl.src = item.src  || '';
                imgEl.alt = item.caption || '';
            }
            if (captionEl) {
                captionEl.textContent = item.caption || '';
                captionEl.style.display = item.caption ? '' : 'none';
            }
            if (counterEl) {
                counterEl.textContent = (currentIdx + 1) + ' / ' + imageList.length;
            }

            var hasMult = imageList.length > 1;
            if (prevBtn) prevBtn.style.display = hasMult ? '' : 'none';
            if (nextBtn) nextBtn.style.display = hasMult ? '' : 'none';
        }

        function show(index, items) {
            if (items && Array.isArray(items) && items.length) {
                imageList = items;
            }
            currentIdx = (typeof index === 'number') ? index : 0;
            // Clamp
            if (currentIdx < 0) currentIdx = 0;
            if (currentIdx >= imageList.length) currentIdx = imageList.length - 1;

            updateView();
            el.removeAttribute('hidden');
            document.body.style.overflow = 'hidden';
            el.setAttribute('tabindex', '-1');
            el.focus();
            utils.trigger(el, 'm:lightbox:open', { index: currentIdx });
        }

        function hide() {
            el.setAttribute('hidden', '');
            document.body.style.overflow = '';
            utils.trigger(el, 'm:lightbox:close', {});
        }

        function prev() {
            if (imageList.length <= 1) return;
            currentIdx = (currentIdx - 1 + imageList.length) % imageList.length;
            updateView();
            utils.trigger(el, 'm:lightbox:change', { index: currentIdx });
        }

        function next() {
            if (imageList.length <= 1) return;
            currentIdx = (currentIdx + 1) % imageList.length;
            updateView();
            utils.trigger(el, 'm:lightbox:change', { index: currentIdx });
        }

        function getIndex() {
            return currentIdx;
        }

        // ── Event Wiring ──────────────────────────────────────────────────

        if (closeBtn)   closeBtn.addEventListener('click',   hide);
        if (backdropEl) backdropEl.addEventListener('click', hide);
        if (prevBtn)    prevBtn.addEventListener('click',    prev);
        if (nextBtn)    nextBtn.addEventListener('click',    next);

        el.addEventListener('keydown', function (e) {
            if (e.key === 'Escape')     { e.preventDefault(); hide(); }
            if (e.key === 'ArrowLeft')  { e.preventDefault(); prev(); }
            if (e.key === 'ArrowRight') { e.preventDefault(); next(); }
        });

        // ── Public API ────────────────────────────────────────────────────

        var api = { show: show, hide: hide, prev: prev, next: next, getIndex: getIndex };

        if (elId) instances[elId] = api;
        return api;
    };

    // ── Auto-initialise ───────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        var els = document.querySelectorAll('.m-lightbox');
        for (var i = 0; i < els.length; i++) {
            m.lightbox(els[i]);
        }
    });

})(window);
