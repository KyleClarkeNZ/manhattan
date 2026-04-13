/**
 * Manhattan ImageViewer Component
 *
 * Gallery/media viewer with side or below thumbnail strip layout,
 * keyboard navigation, auto-advance, and optional lightbox integration.
 *
 * Auto-initialised on any .m-imageviewer element at DOMContentLoaded.
 * Manual init: var iv = m.imageviewer('myId');
 *
 * JS API:
 *   iv.goTo(index)      – navigate to item at index
 *   iv.prev()           – previous item
 *   iv.next()           – next item
 *   iv.currentIndex()   – returns the active item index
 *   iv.startAuto()      – start auto-advance
 *   iv.stopAuto()       – stop auto-advance
 *
 * Events (dispatched on the root element):
 *   m:imageviewer:change – { index }
 */
(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan ImageViewer: core not loaded');
        return;
    }

    var utils = m.utils;

    m.imageviewer = function (id) {
        var el = utils.getElement(id);
        if (!el) return null;

        var items      = el.querySelectorAll('.m-imageviewer-item');
        var thumbs     = el.querySelectorAll('.m-imageviewer-thumb');
        var prevBtn    = el.querySelector('.m-imageviewer-nav--prev');
        var nextBtn    = el.querySelector('.m-imageviewer-nav--next');
        var captionEl  = el.querySelector('.m-imageviewer-caption');
        var counterEl  = el.querySelector('.m-imageviewer-counter');
        var thumbstrip = el.querySelector('.m-imageviewer-thumbstrip');

        var lightboxId  = el.getAttribute('data-m-lightbox-id') || '';
        var autoEnabled = el.getAttribute('data-m-autoadvance') === 'true';
        var interval    = parseInt(el.getAttribute('data-m-interval') || '4000', 10);

        var currentIdx = 0;
        var autoTimer  = null;
        var lbApi      = null;

        var count = items.length;

        // Cache for YouTube oEmbed data — keyed by video ID
        var ytOEmbedCache = {};

        // ── YouTube credit helpers ────────────────────────────────────────

        /** Allow only YouTube channel / user / handle URLs as the credit href. */
        function sanitizeYtChannelUrl(url) {
            if (!url) { return ''; }
            if (/^https:\/\/(www\.)?youtube\.com\/(channel\/|user\/|c\/|@)[A-Za-z0-9_\-@.%]+/.test(url)) {
                return url;
            }
            return '';
        }

        /**
         * Rebuild captionEl with the video credit appended.
         * Uses DOM construction (no innerHTML) to prevent XSS.
         */
        function renderYtCredit(captionEl, captionText, info) {
            captionEl.innerHTML = '';
            if (captionText) {
                captionEl.appendChild(document.createTextNode(captionText));
            }
            var safeUrl = info && info.url ? sanitizeYtChannelUrl(info.url) : '';
            if (info && info.name && safeUrl) {
                if (captionText) {
                    captionEl.appendChild(document.createTextNode(' \u2014 '));
                }
                var creditSpan = document.createElement('span');
                creditSpan.className = 'm-imageviewer-credit';
                creditSpan.appendChild(document.createTextNode('Video Credit: '));
                var link = document.createElement('a');
                link.href   = safeUrl;
                link.target = '_blank';
                link.rel    = 'noopener noreferrer';
                link.textContent = info.name;
                creditSpan.appendChild(link);
                captionEl.appendChild(creditSpan);
            }
            if (captionEl.childNodes.length > 0) {
                captionEl.removeAttribute('hidden');
            } else {
                captionEl.setAttribute('hidden', '');
            }
        }

        /**
         * Fetch oEmbed data for videoId and update captionEl.
         * Uses cached data when available.
         */
        function fetchYtCredit(captionEl, captionText, videoId) {
            if (ytOEmbedCache[videoId]) {
                renderYtCredit(captionEl, captionText, ytOEmbedCache[videoId]);
                return;
            }
            var oEmbedUrl = 'https://www.youtube.com/oembed?url='
                + encodeURIComponent('https://www.youtube.com/watch?v=' + videoId)
                + '&format=json';
            var xhr = new XMLHttpRequest();
            xhr.open('GET', oEmbedUrl, true);
            xhr.timeout = 6000;
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        var info = { name: data.author_name || null, url: data.author_url || null };
                        ytOEmbedCache[videoId] = info;
                        // Only update if this item is still active
                        renderYtCredit(captionEl, captionText, info);
                    } catch (e) { /* ignore */ }
                }
            };
            xhr.onerror   = function () { /* no-op */ };
            xhr.ontimeout = function () { /* no-op */ };
            xhr.send();
        }

        // ── Lightbox integration ──────────────────────────────────────────

        function initLightbox() {
            if (!lightboxId || !m.lightbox) return;
            lbApi = m.lightbox(lightboxId);
        }

        function buildImageList() {
            var list = [];
            for (var i = 0; i < items.length; i++) {
                if (items[i].getAttribute('data-type') === 'image') {
                    var img = items[i].querySelector('img');
                    if (img) {
                        // Prefer data-lightbox-src (e.g. a full-resolution URL) over
                        // the stage src (which is typically a smaller preview).
                        var src = img.getAttribute('data-lightbox-src') || img.src;
                        list.push({ src: src, caption: img.alt || '' });
                    }
                }
            }
            return list;
        }

        // Index into the lightbox image list that corresponds to item index i
        function imageLbIndex(itemIdx) {
            var lbIdx = 0;
            for (var i = 0; i < itemIdx; i++) {
                if (items[i] && items[i].getAttribute('data-type') === 'image') {
                    lbIdx++;
                }
            }
            return lbIdx;
        }

        // ── Navigation ────────────────────────────────────────────────────

        function goTo(index) {
            if (count === 0) return;
            index = ((index % count) + count) % count; // safe modulo

            // Deactivate current item — pause any embedded media
            var prevItem = items[currentIdx];
            if (prevItem) {
                prevItem.classList.remove('m-active');
                var vid = prevItem.querySelector('video');
                if (vid) vid.pause();
                // Pause YouTube iframe by re-setting src
                var iframe = prevItem.querySelector('iframe');
                if (iframe && prevItem.getAttribute('data-type') === 'youtube') {
                    var src = iframe.src;
                    iframe.src = '';
                    iframe.src = src;
                }
            }

            // Activate new item
            items[index].classList.add('m-active');

            // Sync thumbnails
            for (var i = 0; i < thumbs.length; i++) {
                var isActive = (i === index);
                thumbs[i].classList.toggle('m-active', isActive);
                thumbs[i].setAttribute('aria-selected', String(isActive));
                thumbs[i].setAttribute('tabindex', isActive ? '0' : '-1');
            }

            // Update caption
            var activeItem = items[index];
            if (captionEl) {
                var caption = '';
                var mediaEl = activeItem.querySelector('img, video, iframe');
                if (mediaEl) {
                    caption = mediaEl.getAttribute('alt') || mediaEl.getAttribute('title') || '';
                }

                if (activeItem.getAttribute('data-type') === 'youtube' && mediaEl) {
                    // Show plain caption immediately, then fetch/cache channel credit
                    captionEl.textContent = caption;
                    if (caption) {
                        captionEl.removeAttribute('hidden');
                    } else {
                        captionEl.setAttribute('hidden', '');
                    }
                    var ytSrc     = mediaEl.getAttribute('src') || '';
                    var ytIdMatch = ytSrc.match(/\/embed\/([A-Za-z0-9_\-]{11})/);
                    if (ytIdMatch) {
                        fetchYtCredit(captionEl, caption, ytIdMatch[1]);
                    }
                } else {
                    captionEl.textContent = caption;
                    if (caption) {
                        captionEl.removeAttribute('hidden');
                    } else {
                        captionEl.setAttribute('hidden', '');
                    }
                }
            }

            // Update counter
            if (counterEl) {
                counterEl.textContent = (index + 1) + ' / ' + count;
            }

            currentIdx = index;

            // Scroll the active thumb into view within the strip only — do NOT
            // use scrollIntoView() which would scroll the whole page.
            var activeThumb = thumbs[index];
            if (activeThumb && thumbstrip) {
                var layout = el.getAttribute('data-m-layout') || 'side';
                if (layout === 'side') {
                    // Vertical strip: adjust scrollTop
                    var tTop  = activeThumb.offsetTop - thumbstrip.offsetTop;
                    var tH    = activeThumb.offsetHeight;
                    var sTop  = thumbstrip.scrollTop;
                    var sH    = thumbstrip.clientHeight;
                    if (tTop < sTop) {
                        thumbstrip.scrollTop = tTop;
                    } else if (tTop + tH > sTop + sH) {
                        thumbstrip.scrollTop = tTop + tH - sH;
                    }
                } else {
                    // Horizontal strip: adjust scrollLeft
                    var tLeft = activeThumb.offsetLeft - thumbstrip.offsetLeft;
                    var tW    = activeThumb.offsetWidth;
                    var sLeft = thumbstrip.scrollLeft;
                    var sW    = thumbstrip.clientWidth;
                    if (tLeft < sLeft) {
                        thumbstrip.scrollLeft = tLeft;
                    } else if (tLeft + tW > sLeft + sW) {
                        thumbstrip.scrollLeft = tLeft + tW - sW;
                    }
                }
            }

            utils.trigger(el, 'm:imageviewer:change', { index: currentIdx });
        }

        function prev() {
            resetAutoTimer();
            goTo(currentIdx - 1);
        }

        function next() {
            resetAutoTimer();
            goTo(currentIdx + 1);
        }

        // ── Auto-advance ──────────────────────────────────────────────────

        function startAuto() {
            stopAuto();
            autoTimer = setInterval(function () { goTo(currentIdx + 1); }, interval);
        }

        function stopAuto() {
            if (autoTimer !== null) {
                clearInterval(autoTimer);
                autoTimer = null;
            }
        }

        function resetAutoTimer() {
            if (!autoEnabled) return;
            stopAuto();
            startAuto();
        }

        // ── Event Wiring ──────────────────────────────────────────────────

        if (prevBtn) prevBtn.addEventListener('click', prev);
        if (nextBtn) nextBtn.addEventListener('click', next);

        for (var t = 0; t < thumbs.length; t++) {
            (function (idx) {
                thumbs[idx].addEventListener('click', function () {
                    resetAutoTimer();
                    goTo(idx);
                });
                thumbs[idx].addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        resetAutoTimer();
                        goTo(idx);
                    }
                });
            })(t);
        }

        // Keyboard navigation on the viewer itself
        el.setAttribute('tabindex', '0');
        el.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft')  { e.preventDefault(); prev(); }
            if (e.key === 'ArrowRight') { e.preventDefault(); next(); }
        });

        // Touch swipe navigation
        var touchStartX = 0;
        var touchStartY = 0;
        el.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].clientX;
            touchStartY = e.changedTouches[0].clientY;
        }, { passive: true });
        el.addEventListener('touchend', function (e) {
            var dx = e.changedTouches[0].clientX - touchStartX;
            var dy = e.changedTouches[0].clientY - touchStartY;
            // Only trigger if horizontal movement dominates and exceeds threshold
            if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
                if (dx < 0) { next(); } else { prev(); }
            }
        }, { passive: true });

        // Lightbox on image click
        el.addEventListener('click', function (e) {
            if (!lbApi) return;
            var imgEl = e.target;
            if (!imgEl || !imgEl.classList.contains('m-imageviewer-img')) return;

            var imageList = buildImageList();
            if (!imageList.length) return;

            var lbIdx = imageLbIndex(currentIdx);
            lbApi.show(lbIdx, imageList);
        });

        // ── Initialise ────────────────────────────────────────────────────

        initLightbox();

        // Add zoom cursor to images when lightbox is configured
        if (lightboxId) {
            var stageImgs = el.querySelectorAll('.m-imageviewer-img');
            for (var k = 0; k < stageImgs.length; k++) {
                stageImgs[k].style.cursor = 'zoom-in';
            }
        }

        if (autoEnabled) startAuto();

        // ── Public API ────────────────────────────────────────────────────

        return {
            goTo:         goTo,
            prev:         prev,
            next:         next,
            currentIndex: function () { return currentIdx; },
            startAuto:    startAuto,
            stopAuto:     stopAuto
        };
    };

    // ── Auto-initialise ───────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        // lightbox.js initialises all .m-lightbox elements first (it is included
        // before imageviewer.js in renderScripts). Viewers are initialised here;
        // they look up their lightbox via the instance cache in m.lightbox.
        var viewers = document.querySelectorAll('.m-imageviewer');
        for (var j = 0; j < viewers.length; j++) {
            m.imageviewer(viewers[j]);
        }
    });

})(window);
