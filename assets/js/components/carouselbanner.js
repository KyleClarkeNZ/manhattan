/**
 * Manhattan CarouselBanner Component
 *
 * Full-width animated banner slideshow with:
 *   - Slide and fade animation types
 *   - Auto-play with configurable interval and progress indicator
 *   - Pause on hover / focus
 *   - Touch swipe gestures (mobile)
 *   - Keyboard navigation (arrow keys)
 *   - Dot indicators (inside or below)
 *   - Prev/next arrow buttons
 *   - Thumbnail navigation strip
 *   - Lazy loading support
 *   - Loop or non-loop mode
 *   - ARIA live region for screen readers
 *
 * Auto-initialised on .m-carousel-banner[data-cb-config] elements.
 * Manual API: m.carouselBanner('myId')
 *
 * Events fired on the banner element:
 *   m:cb:change  — { detail: { index: n, total: n } }  on slide change
 *   m:cb:play    — { detail: { interval: n } }         when auto-play starts
 *   m:cb:pause   — {}                                  when auto-play pauses
 */
(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan CarouselBanner: core not loaded');
        return;
    }

    var utils = m.utils;

    // ─── Public factory ───────────────────────────────────────────────────────

    m.carouselBanner = function (id) {
        var element = utils.getElement(id);
        if (!element) {
            console.warn('Manhattan CarouselBanner: element not found:', id);
            return null;
        }
        return element._mCarouselBanner || initBanner(element);
    };

    // ─── Auto-init ────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        var banners = document.querySelectorAll('.m-carousel-banner[data-cb-config]');
        for (var i = 0; i < banners.length; i++) {
            initBanner(banners[i]);
        }
    });

    // ─── Core init ────────────────────────────────────────────────────────────

    function initBanner(container) {
        if (container._mCarouselBanner) { return container._mCarouselBanner; }

        // Parse config
        var config = {};
        try {
            config = JSON.parse(container.getAttribute('data-cb-config') || '{}');
        } catch (e) {}

        var animation      = config.animation      || 'slide';
        var animSpeed      = config.animationSpeed || 500;
        var autoPlayMs     = config.autoPlay        || 0;
        var loop           = config.loop            !== false;
        var pauseOnHover   = config.pauseOnHover    !== false;
        var lazyLoad       = config.lazyLoad        !== false;
        var hasArrows      = config.arrows          !== false;
        var hasThumbs      = config.thumbs          === true;
        var startIndex     = config.startIndex      || 0;

        var track        = container.querySelector('.m-cb-track');
        var prevBtn      = container.querySelector('.m-cb-prev');
        var nextBtn      = container.querySelector('.m-cb-next');
        var dotsEl       = container.querySelector('.m-cb-dots');
        var belowDotsEl  = container.querySelector('.m-cb-dots--below');
        var thumbsEl     = container.querySelector('.m-cb-thumbs');
        var progressBar  = container.querySelector('.m-cb-progress-bar');

        if (!track) { return null; }

        var slides      = track.querySelectorAll('.m-cb-slide');
        var total       = slides.length;
        var current     = startIndex;
        var isAnimating = false;
        var autoTimer   = null;
        var isPaused    = false;
        var progressTimer = null;
        var progressStart = null;
        var progressRemaining = 0;

        if (total === 0) { return null; }

        // ─── Slide helpers ────────────────────────────────────────────────────

        function goTo(index, dir) {
            if (isAnimating) { return; }
            if (!loop && (index < 0 || index >= total)) { return; }

            // Clamp index
            var next = ((index % total) + total) % total;
            if (next === current) { return; }

            isAnimating = true;

            var fromSlide = slides[current];
            var toSlide   = slides[next];

            // Determine direction for slide animation
            var forward = dir !== undefined ? dir : (next > current || (current === total - 1 && next === 0));

            if (animation === 'fade') {
                // Fade: crossfade between slides
                toSlide.classList.add('m-cb-slide--entering');
                toSlide.classList.add('m-cb-slide--fade-in');

                // Preload image if lazy
                if (lazyLoad) { preloadSlide(toSlide); }

                reflow(toSlide);

                fromSlide.classList.add('m-cb-slide--fade-out');
                fromSlide.classList.remove('m-active');

                setTimeout(function () {
                    fromSlide.classList.remove('m-cb-slide--fade-out', 'm-cb-slide--entering');
                    toSlide.classList.remove('m-cb-slide--entering', 'm-cb-slide--fade-in');
                    toSlide.classList.add('m-active');
                    current = next;
                    isAnimating = false;
                    afterChange();
                }, animSpeed);

            } else {
                // Slide: translate X
                var enterClass = forward ? 'm-cb-slide--enter-right' : 'm-cb-slide--enter-left';
                var exitClass  = forward ? 'm-cb-slide--exit-left'  : 'm-cb-slide--exit-right';

                toSlide.classList.add(enterClass);
                if (lazyLoad) { preloadSlide(toSlide); }

                reflow(toSlide);

                fromSlide.classList.add(exitClass);
                fromSlide.classList.remove('m-active');
                toSlide.classList.remove(enterClass);
                toSlide.classList.add('m-active', 'm-cb-slide--moving');

                setTimeout(function () {
                    fromSlide.classList.remove(exitClass);
                    toSlide.classList.remove('m-cb-slide--moving');
                    current = next;
                    isAnimating = false;
                    afterChange();
                }, animSpeed);
            }

            updateUI(next);
        }

        function prev() {
            goTo(current - 1, false);
        }

        function next() {
            goTo(current + 1, true);
        }

        function afterChange() {
            utils.trigger(container, 'm:cb:change', { index: current, total: total });
            // Restart auto-play timer after manual navigation
            if (autoPlayMs > 0 && !isPaused) {
                resetAutoPlay();
            }
        }

        // ─── UI updates ───────────────────────────────────────────────────────

        function updateUI(index) {
            // Dots
            var allDots = container.querySelectorAll('.m-cb-dot');
            for (var i = 0; i < allDots.length; i++) {
                var active = (i === index);
                allDots[i].classList.toggle('m-active', active);
                allDots[i].setAttribute('aria-selected', active ? 'true' : 'false');
            }

            // Thumbnails
            if (hasThumbs && thumbsEl) {
                var thumbBtns = thumbsEl.querySelectorAll('.m-cb-thumb');
                for (var j = 0; j < thumbBtns.length; j++) {
                    var thumbActive = (j === index);
                    thumbBtns[j].classList.toggle('m-active', thumbActive);
                    thumbBtns[j].setAttribute('aria-selected', thumbActive ? 'true' : 'false');
                }
            }

            // Arrows
            if (hasArrows) {
                if (prevBtn) {
                    prevBtn.disabled = !loop && index === 0;
                }
                if (nextBtn) {
                    nextBtn.disabled = !loop && index === total - 1;
                }
            }

            // Preload adjacent slides
            if (lazyLoad) {
                var prevIdx = ((index - 1) % total + total) % total;
                var nextIdx = (index + 1) % total;
                preloadSlide(slides[prevIdx]);
                preloadSlide(slides[nextIdx]);
            }
        }

        // ─── Lazy loading ─────────────────────────────────────────────────────

        function preloadSlide(slide) {
            if (!slide) { return; }
            var img = slide.querySelector('img.m-cb-slide-img');
            if (!img) { return; }
            // Remove lazy loading attribute to trigger native load
            if (img.getAttribute('loading') === 'lazy') {
                img.removeAttribute('loading');
            }
        }

        // ─── Auto-play ────────────────────────────────────────────────────────

        function startAutoPlay() {
            if (autoPlayMs <= 0 || total <= 1) { return; }
            stopAutoPlay();
            progressRemaining = autoPlayMs;
            runAutoPlay();
            utils.trigger(container, 'm:cb:play', { interval: autoPlayMs });
        }

        function runAutoPlay() {
            if (autoPlayMs <= 0 || total <= 1 || isPaused) { return; }
            var stepMs = progressRemaining || autoPlayMs;
            progressStart = Date.now();
            if (progressBar) { animateProgress(stepMs); }
            autoTimer = setTimeout(function () {
                next();
                progressRemaining = autoPlayMs;
                if (!isPaused) { runAutoPlay(); }
            }, stepMs);
        }

        function stopAutoPlay() {
            if (autoTimer) {
                clearTimeout(autoTimer);
                autoTimer = null;
            }
            if (progressTimer) {
                cancelAnimationFrame(progressTimer);
                progressTimer = null;
            }
        }

        function resetAutoPlay() {
            stopAutoPlay();
            progressRemaining = autoPlayMs;
            runAutoPlay();
        }

        function pauseAutoPlay() {
            if (!autoPlayMs || isPaused) { return; }
            isPaused = true;
            // Record remaining time
            var elapsed = progressStart !== null ? Date.now() - progressStart : 0;
            progressRemaining = Math.max(0, (progressRemaining || autoPlayMs) - elapsed);
            stopAutoPlay();
            if (progressBar) { progressBar.style.transition = 'none'; }
            utils.trigger(container, 'm:cb:pause', {});
        }

        function resumeAutoPlay() {
            if (!autoPlayMs || !isPaused) { return; }
            isPaused = false;
            runAutoPlay();
        }

        function animateProgress(duration) {
            if (!progressBar) { return; }
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
            reflow(progressBar);
            progressBar.style.transition = 'width ' + duration + 'ms linear';
            progressBar.style.width = '100%';
        }

        // ─── Swipe gestures ───────────────────────────────────────────────────

        var swipeStartX   = 0;
        var swipeStartY   = 0;
        var swipeDeltaX   = 0;
        var swipeDeltaY   = 0;
        var isSwiping     = false;
        var SWIPE_THRESHOLD = 50;   // px
        var SWIPE_ANGLE_MAX = 40;   // degrees — reject mostly-vertical swipes

        function onTouchStart(e) {
            swipeStartX = e.touches[0].clientX;
            swipeStartY = e.touches[0].clientY;
            swipeDeltaX = 0;
            swipeDeltaY = 0;
            isSwiping   = false;
            if (autoPlayMs) { pauseAutoPlay(); }
        }

        function onTouchMove(e) {
            swipeDeltaX = e.touches[0].clientX - swipeStartX;
            swipeDeltaY = e.touches[0].clientY - swipeStartY;

            // Prevent page scroll when clearly swiping horizontally
            var angle = Math.abs(Math.atan2(swipeDeltaY, swipeDeltaX) * 180 / Math.PI);
            var isHorizontal = angle < SWIPE_ANGLE_MAX || angle > (180 - SWIPE_ANGLE_MAX);
            if (isHorizontal && Math.abs(swipeDeltaX) > 10) {
                isSwiping = true;
                e.preventDefault();
            }
        }

        function onTouchEnd() {
            if (isSwiping && Math.abs(swipeDeltaX) >= SWIPE_THRESHOLD) {
                if (swipeDeltaX < 0) {
                    next();
                } else {
                    prev();
                }
            }
            isSwiping = false;
            if (autoPlayMs && !isPaused) { resumeAutoPlay(); }
        }

        // ─── Keyboard navigation ──────────────────────────────────────────────

        function onKeyDown(e) {
            if (e.key === 'ArrowLeft')  { prev(); if (autoPlayMs) { resetAutoPlay(); } }
            if (e.key === 'ArrowRight') { next(); if (autoPlayMs) { resetAutoPlay(); } }
        }

        // ─── Event bindings ───────────────────────────────────────────────────

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                prev();
                if (autoPlayMs) { resetAutoPlay(); }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                next();
                if (autoPlayMs) { resetAutoPlay(); }
            });
        }

        // Dot clicks (inside or below)
        container.addEventListener('click', function (e) {
            var dot = e.target.closest('.m-cb-dot');
            if (dot) {
                var idx = parseInt(dot.getAttribute('data-cb-index') || '0', 10);
                goTo(idx);
                if (autoPlayMs) { resetAutoPlay(); }
            }
        });

        // Thumbnail clicks
        if (hasThumbs && thumbsEl) {
            thumbsEl.addEventListener('click', function (e) {
                var thumb = e.target.closest('.m-cb-thumb');
                if (thumb) {
                    var idx = parseInt(thumb.getAttribute('data-cb-index') || '0', 10);
                    goTo(idx);
                    if (autoPlayMs) { resetAutoPlay(); }
                }
            });
        }

        // Touch / swipe
        var stage = container.querySelector('.m-cb-stage');
        if (stage) {
            stage.addEventListener('touchstart', onTouchStart, { passive: true });
            stage.addEventListener('touchmove',  onTouchMove,  { passive: false });
            stage.addEventListener('touchend',   onTouchEnd,   { passive: true });
        }

        // Keyboard
        container.setAttribute('tabindex', '0');
        container.addEventListener('keydown', onKeyDown);

        // Hover pause
        if (pauseOnHover && autoPlayMs) {
            container.addEventListener('mouseenter', pauseAutoPlay);
            container.addEventListener('mouseleave', resumeAutoPlay);
            container.addEventListener('focusin',   pauseAutoPlay);
            container.addEventListener('focusout',  function (e) {
                if (!container.contains(e.relatedTarget)) { resumeAutoPlay(); }
            });
        }

        // IntersectionObserver: pause when out of view, resume when visible
        if (autoPlayMs && typeof IntersectionObserver !== 'undefined') {
            var visibilityObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        if (!isPaused) { resetAutoPlay(); }
                    } else {
                        stopAutoPlay();
                    }
                });
            }, { threshold: 0.2 });
            visibilityObserver.observe(container);
        }

        // ─── Initial state ────────────────────────────────────────────────────

        // Ensure correct starting slide
        for (var i = 0; i < slides.length; i++) {
            slides[i].classList.toggle('m-active', i === current);
        }
        updateUI(current);

        // Preload first + adjacent slides eagerly
        preloadSlide(slides[current]);
        if (lazyLoad) {
            preloadSlide(slides[((current - 1) % total + total) % total]);
            preloadSlide(slides[(current + 1) % total]);
        }

        // Start auto-play
        if (autoPlayMs > 0 && total > 1) {
            startAutoPlay();
        }

        // ─── Public API ───────────────────────────────────────────────────────

        var api = {
            /** Navigate to a specific slide by 0-based index. */
            goTo: function (index) { goTo(index); return api; },

            /** Go to previous slide. */
            prev: function () { prev(); return api; },

            /** Go to next slide. */
            next: function () { next(); return api; },

            /** Get the current slide index (0-based). */
            currentIndex: function () { return current; },

            /** Total number of slides. */
            count: function () { return total; },

            /** Start (or restart) auto-play. */
            play: function () { isPaused = false; startAutoPlay(); return api; },

            /** Pause auto-play. */
            pause: function () { pauseAutoPlay(); return api; },

            /** Stop auto-play permanently (interval set to 0). */
            stop: function () { autoPlayMs = 0; stopAutoPlay(); return api; }
        };

        container._mCarouselBanner = api;
        return api;
    }

    // ─── Utility ──────────────────────────────────────────────────────────────

    /** Force a reflow to ensure CSS transitions start correctly. */
    function reflow(el) {
        /* jshint ignore:start */
        void el.offsetWidth;
        /* jshint ignore:end */
    }

})(window);
