/**
 * Manhattan Carousel Component
 *
 * Tile-based horizontal scroll carousel with CSS scroll-snap, prev/next
 * navigation buttons, and optional dot indicators.
 *
 * Supports:
 *   - Server-rendered tiles (PHP)
 *   - Client-side remote datasource (JSON endpoint)
 *   - Smooth scroll-snapping per tile
 *   - Dot placement: below (default), above, none
 *   - ResizeObserver for responsive reflow
 *   - scrollend / debounce scroll-settled detection
 *
 * Auto-initialised on .m-carousel[data-carousel-config] elements.
 * Manual API: m.carousel('myCarouselId')
 *
 * Events fired on the carousel element:
 *   m:carousel:change  — { detail: { index: n } }  on tile change
 *   m:carousel:loaded  — { detail: { count: n } }  after remote load
 */
(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan Carousel: core not loaded');
        return;
    }

    var utils = m.utils;

    // ─── Public factory ───────────────────────────────────────────────────────

    /**
     * Get (or create) a Carousel API for the given element ID.
     *
     * @param  {string|Element} id  Element ID or DOM element.
     * @return {object|null}        Carousel API or null.
     */
    m.carousel = function (id) {
        var element = utils.getElement(id);
        if (!element) {
            console.warn('Manhattan Carousel: element not found:', id);
            return null;
        }
        return element._mCarousel || initCarousel(element);
    };

    // ─── Auto-init ────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        var carousels = document.querySelectorAll('.m-carousel[data-carousel-config]');
        for (var i = 0; i < carousels.length; i++) {
            initCarousel(carousels[i]);
        }
    });

    // ─── Core init ────────────────────────────────────────────────────────────

    function initCarousel(container) {
        if (container._mCarousel) { return container._mCarousel; }

        // Parse config from data attribute
        var config = {};
        try {
            config = JSON.parse(container.getAttribute('data-carousel-config') || '{}');
        } catch (e) {}

        var viewport = container.querySelector('.m-carousel-viewport');
        var track    = container.querySelector('.m-carousel-track');
        var prevBtn  = container.querySelector('.m-carousel-prev');
        var nextBtn  = container.querySelector('.m-carousel-next');
        var dotsEl   = container.querySelector('.m-carousel-dots');

        if (!viewport || !track) { return null; }

        var currentTile    = 0;
        var isProgrammatic = false;
        var scrollDebounce;

        // ─── Tile helpers ──────────────────────────────────────────────────────

        function getTiles() {
            return track.querySelectorAll('.m-carousel-tile');
        }

        function getTileCount() {
            return getTiles().length;
        }

        function getTileWidth() {
            var tiles = getTiles();
            if (!tiles.length) { return 0; }
            // offsetWidth includes border; use getBoundingClientRect for subpixel accuracy
            return tiles[0].getBoundingClientRect().width;
        }

        function getGap() {
            try {
                var cs = window.getComputedStyle(track);
                return parseFloat(cs.columnGap || cs.gap || '0') || 0;
            } catch (e) {
                return 0;
            }
        }

        // ─── Dot rendering ────────────────────────────────────────────────────

        function renderDots() {
            if (!dotsEl) { return; }

            var count = getTileCount();
            dotsEl.innerHTML = '';

            if (count <= 1) { return; }

            for (var i = 0; i < count; i++) {
                var dot = document.createElement('button');
                dot.type        = 'button';
                dot.className   = 'm-carousel-dot' + (i === currentTile ? ' m-active' : '');
                dot.setAttribute('aria-label', 'Item ' + (i + 1));
                dot.setAttribute('role', 'tab');
                dot.setAttribute('aria-selected', i === currentTile ? 'true' : 'false');

                (function (idx) {
                    dot.addEventListener('click', function () { goTo(idx); });
                }(i));

                dotsEl.appendChild(dot);
            }
        }

        // ─── Button / dot state update ────────────────────────────────────────

        function updateState() {
            var count  = getTileCount();
            var single = (count <= 1);

            if (prevBtn) {
                prevBtn.disabled         = (currentTile <= 0);
                prevBtn.style.visibility = single ? 'hidden' : '';
            }
            if (nextBtn) {
                nextBtn.disabled         = (currentTile >= count - 1);
                nextBtn.style.visibility = single ? 'hidden' : '';
            }

            if (dotsEl) {
                var dots = dotsEl.querySelectorAll('.m-carousel-dot');
                for (var i = 0; i < dots.length; i++) {
                    var isActive = (i === currentTile);
                    dots[i].className = 'm-carousel-dot' + (isActive ? ' m-active' : '');
                    dots[i].setAttribute('aria-selected', isActive ? 'true' : 'false');
                }
            }

            utils.trigger(container, 'm:carousel:change', { index: currentTile });
        }

        // ─── Scroll to tile ───────────────────────────────────────────────────

        function goTo(idx) {
            var count = getTileCount();
            if (count === 0) { return; }

            idx = Math.max(0, Math.min(idx, count - 1));
            currentTile = idx;

            var tw        = getTileWidth();
            var gap       = getGap();
            var target    = idx * (tw + gap);
            var maxScroll = Math.max(0, viewport.scrollWidth - viewport.clientWidth);
            target        = Math.min(target, maxScroll);

            if (Math.abs(viewport.scrollLeft - target) < 1) {
                updateState();
                return;
            }

            isProgrammatic = true;
            try {
                viewport.scrollTo({ left: target, behavior: 'smooth' });
            } catch (e) {
                viewport.scrollLeft = target;
            }

            // Reflect intended state immediately; animation plays in background
            updateState();
        }

        function scrollInstant(target) {
            try {
                viewport.scrollTo({ left: target, behavior: 'instant' });
            } catch (e) {
                viewport.scrollLeft = target;
            }
        }

        // ─── Derive tile from scrollLeft ──────────────────────────────────────

        function tileFromScrollLeft() {
            var tw  = getTileWidth();
            var gap = getGap();
            if (tw + gap <= 0) { return 0; }
            var count = getTileCount();
            var idx   = Math.round(viewport.scrollLeft / (tw + gap));
            return Math.max(0, Math.min(idx, count - 1));
        }

        // ─── Scroll end handling ──────────────────────────────────────────────

        function onScrollSettled() {
            if (isProgrammatic) {
                isProgrammatic = false;
            } else {
                currentTile = tileFromScrollLeft();
            }
            updateState();
        }

        // Use native scrollend when available (Chrome 114+, Firefox 109+, Safari 16.4+).
        // Fall back to a 550 ms debounce — safely longer than any smooth-scroll animation.
        var hasScrollEnd = ('onscrollend' in window) || ('onscrollend' in viewport);
        if (hasScrollEnd) {
            viewport.addEventListener('scrollend', onScrollSettled);
        } else {
            viewport.addEventListener('scroll', function () {
                clearTimeout(scrollDebounce);
                scrollDebounce = setTimeout(onScrollSettled, 550);
            });
        }

        // ─── Button handlers ──────────────────────────────────────────────────

        if (prevBtn) {
            prevBtn.addEventListener('click', function () { goTo(currentTile - 1); });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', function () { goTo(currentTile + 1); });
        }

        // ─── Keyboard navigation on focused viewport ──────────────────────────

        viewport.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                goTo(currentTile + 1);
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                goTo(currentTile - 1);
            } else if (e.key === 'Home') {
                e.preventDefault();
                goTo(0);
            } else if (e.key === 'End') {
                e.preventDefault();
                goTo(getTileCount() - 1);
            }
        });

        // ─── ResizeObserver ───────────────────────────────────────────────────

        if (window.ResizeObserver) {
            new ResizeObserver(function () {
                var count = getTileCount();
                if (count === 0) { return; }

                currentTile = Math.max(0, Math.min(currentTile, count - 1));

                var tw        = getTileWidth();
                var gap       = getGap();
                var target    = currentTile * (tw + gap);
                var maxScroll = Math.max(0, viewport.scrollWidth - viewport.clientWidth);
                target        = Math.min(target, maxScroll);

                if (Math.abs(viewport.scrollLeft - target) >= 1) {
                    isProgrammatic = true;
                    scrollInstant(target);
                }

                updateState();
            }).observe(viewport);
        }

        // ─── Remote datasource ────────────────────────────────────────────────

        if (config.remoteUrl) {
            loadRemote(config.remoteUrl, config.perPage || 0);
        }

        function loadRemote(url, perPage) {
            container.classList.add('m-carousel--loading');

            var fetchUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + 'perPage=' + perPage;

            fetch(fetchUrl, { credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    container.classList.remove('m-carousel--loading');
                    if (!data || !Array.isArray(data.tiles)) { return; }

                    track.innerHTML = '';
                    data.tiles.forEach(function (t) {
                        track.appendChild(buildTileEl(t));
                    });

                    currentTile = 0;
                    renderDots();
                    updateState();
                    utils.trigger(container, 'm:carousel:loaded', { count: data.tiles.length });
                })
                .catch(function () {
                    container.classList.remove('m-carousel--loading');
                });
        }

        // ─── Build a tile element from a plain object ─────────────────────────

        function buildTileEl(t) {
            var tile = document.createElement('div');
            tile.className = 'm-carousel-tile';
            tile.setAttribute('role', 'group');
            tile.setAttribute('aria-label', t.title || '');

            var imgHtml;
            if (t.imageUrl) {
                imgHtml = '<div class="m-carousel-tile-img">'
                    + '<img src="' + escAttr(t.imageUrl) + '" alt="' + escAttr(t.title || '') + '" loading="lazy">'
                    + '</div>';
            } else {
                imgHtml = '<div class="m-carousel-tile-img m-carousel-tile-img--empty">'
                    + '<i class="fas fa-image" aria-hidden="true"></i></div>';
            }

            var capHtml = '<div class="m-carousel-tile-caption">'
                + '<span class="m-carousel-tile-title">' + escHtml(t.title || '') + '</span>';

            if (t.caption) {
                capHtml += '<span class="m-carousel-tile-sub">' + escHtml(t.caption) + '</span>';
            }
            capHtml += '</div>';

            tile.innerHTML = '<a href="' + escAttr(t.href || '#') + '" class="m-carousel-tile-link" tabindex="0">'
                + imgHtml + capHtml + '</a>';

            return tile;
        }

        // ─── HTML helpers ──────────────────────────────────────────────────────

        function escHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function escAttr(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;');
        }

        // ─── Initial state ────────────────────────────────────────────────────

        requestAnimationFrame(function () {
            renderDots();
            updateState();
        });

        // ─── Public API ───────────────────────────────────────────────────────

        var api = {
            /** Navigate to a specific tile by zero-based index. */
            goTo: function (idx) { goTo(idx); },

            /** Navigate to the next tile. */
            next: function () { goTo(currentTile + 1); },

            /** Navigate to the previous tile. */
            prev: function () { goTo(currentTile - 1); },

            /** Return the current tile index (0-based). */
            current: function () { return currentTile; },

            /** Return the total number of tiles. */
            count: function () { return getTileCount(); },

            /**
             * Reload tiles from a remote URL.
             *
             * @param {string} [url]     Defaults to the configured remoteUrl.
             * @param {number} [perPage] Defaults to the configured perPage.
             */
            reload: function (url, perPage) {
                var u = url || config.remoteUrl;
                if (u) { loadRemote(u, perPage !== undefined ? perPage : (config.perPage || 0)); }
            }
        };

        container._mCarousel = api;
        return api;
    }

}(window));
