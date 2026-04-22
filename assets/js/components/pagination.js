/**
 * Manhattan UI Framework — Pagination Component
 *
 * Drives page-based navigation in three modes:
 *
 *  client  (default) — all items are in the DOM, JS shows/hides slices.
 *  server             — PHP rendered <a> links, JS fires events for hybrid use.
 *  ajax               — JS fires events and optionally auto-fetches a URL,
 *                       injecting the response HTML into a target container.
 *
 * JS API:
 *   var p = m.pagination('my-pager');
 *   p.goTo(3);
 *   p.next();  p.prev();  p.first();  p.last();
 *   p.setTotal(200);          // update total (usually after AJAX response)
 *   p.setPerPage(25);         // change per-page size
 *   p.getState();             // { page, perPage, total, totalPages, offset, limit }
 *   p.refresh();              // re-scan DOM for target items (client mode)
 *
 * Events (fired on the pagination element):
 *   m:pagination:change  — { page, perPage, total, totalPages, offset, limit }
 *   m:pagination:loaded  — { page, perPage, url }   (ajax auto-fetch completed)
 *
 * Per-trigger elements:
 *   Any element with data-m-pagination="pagerId" and data-page="N" becomes a
 *   trigger that calls goTo(N) when clicked.  Useful for custom prev/next buttons
 *   outside the component.
 *
 * Client-mode item discovery (in order of preference):
 *   1. Direct children of target with [data-pagination-item] attribute.
 *   2. All direct children of target.
 *   Items are determined once on init; call p.refresh() after DOM mutations.
 */

(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before pagination module');
        return;
    }

    var utils = m.utils;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    function injectHtml(el, html) {
        el.innerHTML = '';
        try {
            var frag = document.createRange().createContextualFragment(html);
            el.appendChild(frag);
        } catch (e) {
            el.innerHTML = html;
        }
    }

    /**
     * Build the list of pages to display.
     * Returns an array of integers + '...' strings.
     * Mirror of the PHP Pagination::buildPageList() algorithm.
     */
    function buildPageList(current, total, max) {
        if (total <= max) {
            var all = [];
            for (var i = 1; i <= total; i++) all.push(i);
            return all;
        }

        var centerCount = Math.max(1, max - 4);
        var half        = Math.floor(centerCount / 2);
        var start       = Math.max(2, current - half);
        var end         = start + centerCount - 1;

        if (end > total - 1) {
            end   = total - 1;
            start = Math.max(2, end - centerCount + 1);
        }

        var pages = [1];
        if (start > 2)        pages.push('...');
        for (var p = start; p <= end; p++) pages.push(p);
        if (end < total - 1)  pages.push('...');
        pages.push(total);

        return pages;
    }

    /** Build a URL from a template, replacing {page} and {perPage} tokens. */
    function buildUrl(template, page, perPage) {
        return template
            .replace('{page}',    String(page))
            .replace('{perPage}', String(perPage));
    }

    // -----------------------------------------------------------------------
    // Factory
    // -----------------------------------------------------------------------

    m.pagination = function (id) {
        var navEl = utils.getElement(id);
        if (!navEl) {
            console.warn('Manhattan Pagination: Element not found:', id);
            return null;
        }

        // --- Read configuration from data attributes ---
        var mode          = navEl.getAttribute('data-mode')         || 'client';
        var urlTemplate   = navEl.getAttribute('data-url')          || '';
        var targetId      = navEl.getAttribute('data-target')       || '';
        var maxButtons    = parseInt(navEl.getAttribute('data-max-buttons')    || '7',  10);
        var showFirstLast = navEl.getAttribute('data-show-first-last') === 'true';
        var autoLoad      = navEl.getAttribute('data-auto-load')    === 'true';
        var scrollOnPage  = navEl.getAttribute('data-scroll-on-page') !== 'false'; // default true
        var hideIfSingle  = navEl.getAttribute('data-hide-if-single')  === 'true';
        var pageSizes     = (navEl.getAttribute('data-page-sizes')  || '')
                                .split(',').map(Number).filter(Boolean);

        var state = {
            page:       parseInt(navEl.getAttribute('data-current-page') || '1',  10),
            perPage:    parseInt(navEl.getAttribute('data-per-page')      || '10', 10),
            total:      parseInt(navEl.getAttribute('data-total')         || '0',  10),
            totalPages: parseInt(navEl.getAttribute('data-total-pages')   || '1',  10)
        };

        var controlsEl = navEl.querySelector('.m-pagination-controls');
        var infoEl     = navEl.querySelector('.m-pagination-info');
        var sizeEl     = navEl.querySelector('.m-pagination-size-select');
        var targetEl   = targetId ? document.getElementById(targetId) : null;

        // Items discovered in client mode (populated in discoverItems)
        var clientItems = [];

        // -----------------------------------------------------------------------
        // Client mode — item discovery & paging
        // -----------------------------------------------------------------------

        function discoverItems() {
            if (!targetEl) return;
            var explicit = targetEl.querySelectorAll('[data-pagination-item]');
            if (explicit.length > 0) {
                clientItems = Array.prototype.slice.call(explicit);
            } else {
                // All direct children
                clientItems = Array.prototype.slice.call(targetEl.children);
            }
            // If total was 0 (auto), set it from item count
            if (state.total === 0 && clientItems.length > 0) {
                state.total      = clientItems.length;
                state.totalPages = Math.ceil(state.total / state.perPage) || 1;
                navEl.setAttribute('data-total', String(state.total));
                navEl.setAttribute('data-total-pages', String(state.totalPages));
            }
        }

        function applyClientPaging() {
            if (mode !== 'client' || clientItems.length === 0) return;
            var offset = (state.page - 1) * state.perPage;
            for (var i = 0; i < clientItems.length; i++) {
                var show = (i >= offset && i < offset + state.perPage);
                clientItems[i].style.display = show ? '' : 'none';
            }
        }

        // -----------------------------------------------------------------------
        // AJAX mode — fetch & inject
        // -----------------------------------------------------------------------

        function fetchPage(page) {
            if (!urlTemplate || !targetEl) return;

            var url = buildUrl(urlTemplate, page, state.perPage);

            // Loading state
            targetEl.classList.add('m-pagination-target-loading');

            var done = function (html, newTotal) {
                injectHtml(targetEl, html);
                targetEl.classList.remove('m-pagination-target-loading');
                if (typeof newTotal === 'number' && newTotal > 0) {
                    state.total      = newTotal;
                    state.totalPages = Math.ceil(state.total / state.perPage) || 1;
                    navEl.setAttribute('data-total',       String(state.total));
                    navEl.setAttribute('data-total-pages', String(state.totalPages));
                    renderControls();
                    renderInfo();
                }
                utils.trigger(navEl, 'm:pagination:loaded', { page: page, perPage: state.perPage, url: url });
            };

            var fail = function () {
                targetEl.classList.remove('m-pagination-target-loading');
            };

            if (m.ajax) {
                m.ajax(url, { method: 'GET' })
                    .then(function (resp) {
                        if (resp === null || resp === undefined) { fail(); return; }
                        if (typeof resp === 'string') {
                            done(resp, 0);
                        } else if (resp && typeof resp.html === 'string') {
                            done(resp.html, resp.total || 0);
                        } else {
                            fail();
                        }
                    })
                    ['catch'](fail);
            } else {
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.text(); })
                    .then(function (html) { done(html, 0); })
                    ['catch'](fail);
            }
        }

        // -----------------------------------------------------------------------
        // Controls rendering (client + ajax modes re-render on every page change)
        // -----------------------------------------------------------------------

        function renderControls() {
            if (!controlsEl) return;

            // server mode is static — only update aria-current and disabled states
            if (mode === 'server') {
                updateServerControls();
                return;
            }

            var html = '';
            var page  = state.page;
            var total = state.totalPages;
            var prev  = Math.max(1, page - 1);
            var next  = Math.min(total, page + 1);

            // « First
            if (showFirstLast) {
                html += navBtn(1, page === 1, '&laquo;', 'First page');
            }

            // ‹ Prev
            html += navBtn(prev, page === 1, '&lsaquo;', 'Previous page');

            // Numbered buttons + ellipsis
            var pageList = buildPageList(page, total, maxButtons);
            for (var i = 0; i < pageList.length; i++) {
                var entry = pageList[i];
                if (entry === '...') {
                    html += '<span class="m-pagination-ellipsis" aria-hidden="true">&hellip;</span>';
                } else {
                    var active = (entry === page);
                    html += pageBtn(entry, active);
                }
            }

            // › Next
            html += navBtn(next, page === total, '&rsaquo;', 'Next page');

            // » Last
            if (showFirstLast) {
                html += navBtn(total, page === total, '&raquo;', 'Last page');
            }

            controlsEl.innerHTML = html;
            bindControlEvents();
        }

        function navBtn(targetPage, disabled, symbol, label) {
            var disClass = disabled ? ' m-pagination-disabled' : '';
            return '<button type="button"'
                + ' class="m-pagination-btn m-pagination-nav' + disClass + '"'
                + ' data-page="' + targetPage + '"'
                + (disabled ? ' disabled aria-disabled="true"' : '')
                + ' aria-label="' + label + '">'
                + symbol + '</button>';
        }

        function pageBtn(page, active) {
            var activeClass  = active ? ' m-pagination-active' : '';
            var ariaCurrent  = active ? ' aria-current="page"' : '';
            return '<button type="button"'
                + ' class="m-pagination-btn' + activeClass + '"'
                + ' data-page="' + page + '"'
                + ariaCurrent + '>'
                + page + '</button>';
        }

        /** In server mode: update active/disabled classes without changing links. */
        function updateServerControls() {
            if (!controlsEl) return;
            var btns = controlsEl.querySelectorAll('.m-pagination-btn');
            for (var i = 0; i < btns.length; i++) {
                var btn  = btns[i];
                var pg   = parseInt(btn.getAttribute('data-page') || btn.textContent, 10);
                btn.classList.toggle('m-pagination-active', pg === state.page);
                if (btn.hasAttribute('aria-current')) {
                    btn.setAttribute('aria-current', pg === state.page ? 'page' : 'false');
                }
            }
        }

        // -----------------------------------------------------------------------
        // Info text rendering
        // -----------------------------------------------------------------------

        function renderInfo() {
            if (!infoEl) return;
            // Only update if the element is meant to be visible
            if (infoEl.getAttribute('aria-hidden') === 'true') return;
            if (state.total > 0) {
                var from = (state.page - 1) * state.perPage + 1;
                var to   = Math.min(state.page * state.perPage, state.total);
                infoEl.textContent = 'Showing ' + from + '\u2013' + to + ' of ' + state.total;
            } else {
                infoEl.textContent = '';
            }
        }

        // -----------------------------------------------------------------------
        // Event binding for controls
        // -----------------------------------------------------------------------

        function bindControlEvents() {
            if (!controlsEl) return;
            var btns = controlsEl.querySelectorAll('button[data-page]');
            for (var i = 0; i < btns.length; i++) {
                (function (btn) {
                    btn.addEventListener('click', function () {
                        var p = parseInt(btn.getAttribute('data-page'), 10);
                        if (!isNaN(p)) navigate(p);
                    });
                })(btns[i]);
            }
        }

        // -----------------------------------------------------------------------
        // Navigation
        // -----------------------------------------------------------------------

        function navigate(page) {
            page = Math.max(1, Math.min(state.totalPages, page));
            if (page === state.page) return;

            state.page = page;
            navEl.setAttribute('data-current-page', String(page));

            var detail = {
                page:       state.page,
                perPage:    state.perPage,
                total:      state.total,
                totalPages: state.totalPages,
                offset:     (state.page - 1) * state.perPage,
                limit:      state.perPage
            };

            utils.trigger(navEl, 'm:pagination:change', detail);
            renderControls();
            renderInfo();

            if (scrollOnPage) {
                navEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            if (mode === 'client') {
                applyClientPaging();
            } else if (mode === 'ajax' && urlTemplate) {
                fetchPage(page);
            }
        }

        // -----------------------------------------------------------------------
        // Size selector
        // -----------------------------------------------------------------------

        if (sizeEl) {
            // Shared handler — called from both native-change and m:dropdown:change paths.
            function applyPerPageChange(newSize) {
                if (sizeEl._pgUpdating) return;
                if (isNaN(newSize) || newSize < 1) return;

                if (mode === 'server' && urlTemplate) {
                    // Server mode: redirect to page 1 with new perPage
                    window.location.href = buildUrl(urlTemplate, 1, newSize);
                    return;
                }

                state.perPage    = newSize;
                state.totalPages = Math.ceil(state.total / state.perPage) || 1;
                state.page       = 1;
                navEl.setAttribute('data-per-page',    String(state.perPage));
                navEl.setAttribute('data-total-pages', String(state.totalPages));
                navEl.setAttribute('data-current-page', '1');

                utils.trigger(navEl, 'm:pagination:change', {
                    page:       1,
                    perPage:    state.perPage,
                    total:      state.total,
                    totalPages: state.totalPages,
                    offset:     0,
                    limit:      state.perPage
                });

                renderControls();
                renderInfo();

                if (scrollOnPage) {
                    navEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }

                if (mode === 'client') {
                    applyClientPaging();
                } else if (mode === 'ajax' && urlTemplate) {
                    fetchPage(1);
                }
            }

            // Native <select> change — works for plain selects and bubbles up from
            // the inner <select> when the size selector is a Manhattan Dropdown.
            // Use e.target.value (the actual <select>) rather than sizeEl.value
            // (which is undefined on a Dropdown wrapper <div>).
            sizeEl.addEventListener('change', function (e) {
                var val = (e && e.target && e.target.tagName === 'SELECT')
                    ? e.target.value
                    : sizeEl.value;
                applyPerPageChange(parseInt(val, 10));
            });

            // Manhattan Dropdown fires m:dropdown:change with e.detail.value.
            sizeEl.addEventListener('m:dropdown:change', function (e) {
                applyPerPageChange(parseInt(e.detail && e.detail.value, 10));
            });
        }

        // -----------------------------------------------------------------------
        // External trigger elements
        // -----------------------------------------------------------------------

        function bindExternalTrigger(el) {
            if (el._mPaginationBound) return;
            el._mPaginationBound = true;
            el.addEventListener('click', function (e) {
                e.preventDefault();
                var pg = parseInt(el.getAttribute('data-page'), 10);
                if (!isNaN(pg)) navigate(pg);
            });
        }

        function scanExternalTriggers() {
            var triggers = document.querySelectorAll('[data-m-pagination="' + id + '"]');
            for (var i = 0; i < triggers.length; i++) {
                bindExternalTrigger(triggers[i]);
            }
        }

        // -----------------------------------------------------------------------
        // Init
        // -----------------------------------------------------------------------

        if (mode === 'client') {
            discoverItems();
            applyClientPaging();
        }

        // Render controls — in client mode discoverItems() may have updated totalPages,
        // so we must re-render the button set here rather than relying on PHP-rendered buttons.
        if (mode === 'server') {
            updateServerControls();
        } else {
            renderControls(); // also calls bindControlEvents() internally
        }

        renderInfo();
        scanExternalTriggers();

        // Hide the pagination element when there is only 1 page (after item discovery).
        if (hideIfSingle && state.totalPages <= 1) {
            navEl.style.display = 'none';
        }

        if (mode === 'ajax' && autoLoad && urlTemplate) {
            fetchPage(state.page);
        }

        // -----------------------------------------------------------------------
        // Public API
        // -----------------------------------------------------------------------

        return {
            /** Navigate to a specific page number. */
            goTo: function (page) { navigate(page); },

            /** Go to the next page. */
            next: function () { navigate(state.page + 1); },

            /** Go to the previous page. */
            prev: function () { navigate(state.page - 1); },

            /** Jump to the first page. */
            first: function () { navigate(1); },

            /** Jump to the last page. */
            last:  function () { navigate(state.totalPages); },

            /**
             * Update the total item count and re-render controls.
             * Useful after an AJAX response that returns a new total.
             * Navigates back to page 1 if the current page is now out of range.
             */
            setTotal: function (total) {
                state.total      = Math.max(0, total);
                state.totalPages = Math.ceil(state.total / state.perPage) || 1;
                navEl.setAttribute('data-total',       String(state.total));
                navEl.setAttribute('data-total-pages', String(state.totalPages));
                if (state.page > state.totalPages) {
                    state.page = state.totalPages;
                    navEl.setAttribute('data-current-page', String(state.page));
                }
                renderControls();
                renderInfo();
            },

            /**
             * Override the total page count independently of the item total.
             *
             * Use this with group-aware pagination where the number of pages does
             * not equal ceil(total/perPage) — e.g. when using FilterBar.groupSlice().
             * Call this AFTER setTotal() so it is not recalculated from the item count.
             *
             * @param {number} totalPages  The exact number of pages to show
             */
            setTotalPages: function (totalPages) {
                state.totalPages = Math.max(1, totalPages);
                navEl.setAttribute('data-total-pages', String(state.totalPages));
                if (state.page > state.totalPages) {
                    state.page = state.totalPages;
                    navEl.setAttribute('data-current-page', String(state.page));
                }
                renderControls();
                renderInfo();
            },

            /**
             * Atomically update both the total item count and the total page count,
             * then re-render controls once.
             *
             * Use this instead of calling setTotal() + setTotalPages() separately when
             * using group-aware pagination — it avoids a flash of incorrectly-calculated
             * page buttons between the two calls.
             *
             * @param {number} total       Total filtered item count (used for info text)
             * @param {number} totalPages  Exact page count (group-aware or otherwise)
             */
            setTotalAndPages: function (total, totalPages) {
                state.total      = Math.max(0, total);
                state.totalPages = Math.max(1, totalPages);
                navEl.setAttribute('data-total',       String(state.total));
                navEl.setAttribute('data-total-pages', String(state.totalPages));
                if (state.page > state.totalPages) {
                    state.page = state.totalPages;
                    navEl.setAttribute('data-current-page', String(state.page));
                }
                renderControls();
                renderInfo();
            },

            /**
             * Atomically update both the total item count and the total page count,
             * then re-render controls once.
             *
             * Use this instead of calling setTotal() + setTotalPages() separately when
             * using group-aware pagination — it avoids a flash of incorrectly-calculated
             * page buttons between the two calls.
             *
             * @param {number} total       Total filtered item count (used for info text)
             * @param {number} totalPages  Exact page count (group-aware or otherwise)
             */
            setTotalAndPages: function (total, totalPages) {
                state.total      = Math.max(0, total);
                state.totalPages = Math.max(1, totalPages);
                navEl.setAttribute('data-total',       String(state.total));
                navEl.setAttribute('data-total-pages', String(state.totalPages));
                if (state.page > state.totalPages) {
                    state.page = state.totalPages;
                    navEl.setAttribute('data-current-page', String(state.page));
                }
                renderControls();
                renderInfo();
            },

            /** Update the per-page size and re-render. */
            setPerPage: function (perPage) {
                state.perPage    = Math.max(1, perPage);
                state.totalPages = Math.ceil(state.total / state.perPage) || 1;
                state.page       = 1;
                navEl.setAttribute('data-per-page',    String(state.perPage));
                navEl.setAttribute('data-total-pages', String(state.totalPages));
                navEl.setAttribute('data-current-page', '1');
                if (sizeEl) {
                    // Use Manhattan dropdown API if initialized, else fall back to raw select
                    if (sizeEl._manhattanDropdownInstance) {
                        sizeEl._pgUpdating = true;
                        sizeEl._manhattanDropdownInstance.value(String(state.perPage));
                        sizeEl._pgUpdating = false;
                    } else {
                        sizeEl.value = String(state.perPage);
                    }
                }
                utils.trigger(navEl, 'm:pagination:change', {
                    page:       1,
                    perPage:    state.perPage,
                    total:      state.total,
                    totalPages: state.totalPages,
                    offset:     0,
                    limit:      state.perPage
                });
                renderControls();
                renderInfo();
                if (mode === 'client') applyClientPaging();
            },

            /**
             * Returns the current pagination state.
             * @returns {{ page, perPage, total, totalPages, offset, limit }}
             */
            getState: function () {
                return {
                    page:       state.page,
                    perPage:    state.perPage,
                    total:      state.total,
                    totalPages: state.totalPages,
                    offset:     (state.page - 1) * state.perPage,
                    limit:      state.perPage
                };
            },

            /**
             * Re-discover items in the target container and re-apply paging.
             * Call after dynamically adding/removing items in client mode.
             */
            refresh: function () {
                if (mode === 'client') {
                    discoverItems();
                    applyClientPaging();
                }
                scanExternalTriggers();
                renderInfo();
            },

            /** The underlying <nav> DOM element. */
            element: navEl
        };
    };

    // Auto-initialise every .m-pagination element on the page
    document.addEventListener('DOMContentLoaded', function () {
        var pagers = document.querySelectorAll('.m-pagination[id]');
        for (var i = 0; i < pagers.length; i++) {
            m.pagination(pagers[i].id);
        }
    });

})(window);
