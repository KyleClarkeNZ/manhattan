/**
 * Manhattan FilterBar Component
 *
 * Manages filter state (search, sort, group) for a linked data view.
 * Fires a unified `m:filterbar:change` event whenever any control changes.
 * When linked to a Pagination instance via `data-pager`, the pager is
 * automatically reset to page 1 on every filter change.
 *
 * Also provides `groupSlice()` — a group-aware pagination utility that
 * packs grouped items into page buckets without ever splitting a group
 * across page boundaries.
 *
 * JS API:
 *   var fb = m.filterBar('myFilter');
 *   fb.getState();                              // { search, sort, group }
 *   fb.setState({ sort: 'asc' });               // update state programmatically
 *   fb.groupSlice(items, keyFn, sortFn, perPage, pageIndex)
 *                                               // { keys, groups, totalPages }
 *   fb.computeGroupPages(items, keyFn, sortFn, perPage)
 *                                               // { pages, allGroups, sortedKeys, totalPages }
 *
 * Events (fired on the filter-bar element):
 *   m:filterbar:change  — detail: { search, sort, group }
 *
 * Options (data attributes on the root element):
 *   data-pager          — id of linked Pagination element
 *   data-default-sort   — initial sort value (echoed from PHP)
 *   data-default-group  — initial group value (echoed from PHP)
 */

(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan FilterBar: core not loaded before filterbar module');
        return;
    }

    var utils = m.utils;

    // ──────────────────────────────────────────────────────────────────────────
    // Group-aware pagination helpers (standalone, no component instance needed)
    // ──────────────────────────────────────────────────────────────────────────

    /** Sentinel suffix appended to a group key when that group is continued from the previous page. */
    var CONTINUED_SUFFIX = '\u0000continued';

    /**
     * Pack groups into pages, splitting groups that exceed `perPage` across
     * multiple pages.  When a group is continued on the next page its key is
     * suffixed with CONTINUED_SUFFIX so callers can render a "– continued" label.
     *
     * The returned `pageGroups` object maps every page's virtual keys to the
     * slice of items for that key on that page.
     *
     * @param {Object}   allGroups   { groupKey: items[] }
     * @param {string[]} sortedKeys  Group keys in display order
     * @param {number}   perPage     Target items per page
     * @returns {Array<{keys: string[], pageGroups: Object}>}
     */
    function packGroupPages(allGroups, sortedKeys, perPage) {
        var pages = [];

        // Build a flat list of virtual segments: each segment is one group, or
        // a perPage-sized slice of a group when the group is larger than perPage.
        // Each segment: { key: string, items: [], continued: bool }
        var segments = [];
        for (var i = 0; i < sortedKeys.length; i++) {
            var origKey = sortedKeys[i];
            var items   = allGroups[origKey] || [];
            var offset  = 0;
            var first   = true;
            while (offset < items.length) {
                var chunk = items.slice(offset, offset + perPage);
                segments.push({ key: origKey, items: chunk, continued: !first });
                offset += perPage;
                first   = false;
            }
        }

        // Greedily fill pages from segments
        var curKeys       = [];
        var curPageGroups = {};
        var curCount      = 0;

        for (var s = 0; s < segments.length; s++) {
            var seg      = segments[s];
            var segSize  = seg.items.length;
            var virtKey  = seg.continued ? seg.key + CONTINUED_SUFFIX : seg.key;

            // Start a new page when adding this segment would overflow (and page has content)
            if (curCount > 0 && curCount + segSize > perPage) {
                pages.push({ keys: curKeys, pageGroups: curPageGroups });
                curKeys       = [];
                curPageGroups = {};
                curCount      = 0;
                // If this segment is a continuation that was pushed to the next page,
                // mark it as continued even if it was originally not continued
                virtKey = seg.key + CONTINUED_SUFFIX;
            }

            curKeys.push(virtKey);
            curPageGroups[virtKey] = seg.items;
            curCount += segSize;
        }
        if (curKeys.length > 0) {
            pages.push({ keys: curKeys, pageGroups: curPageGroups });
        }
        return pages;
    }

    /**
     * Given a virtual key (possibly suffixed with CONTINUED_SUFFIX), return
     * the display key and whether it is a continuation.
     */
    function parseVirtKey(virtKey) {
        var idx = virtKey.indexOf(CONTINUED_SUFFIX);
        if (idx === -1) {
            return { displayKey: virtKey, continued: false };
        }
        return { displayKey: virtKey.slice(0, idx), continued: true };
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Factory
    // ──────────────────────────────────────────────────────────────────────────

    m.filterBar = function (id) {
        var el = utils.getElement(id);
        if (!el) {
            console.warn('Manhattan FilterBar: element not found:', id);
            return null;
        }

        var pagerId = el.getAttribute('data-pager') || '';

        var state = {
            search: '',
            sort:   el.getAttribute('data-default-sort')  || '',
            group:  el.getAttribute('data-default-group') || ''
        };

        // ── Helpers ───────────────────────────────────────────────────────────

        function getPager() {
            return (pagerId && typeof m.pagination === 'function') ? m.pagination(pagerId) : null;
        }

        function fireChange() {
            utils.trigger(el, 'm:filterbar:change', {
                search: state.search,
                sort:   state.sort,
                group:  state.group
            });
            // Linked pager resets to page 1 on every filter change
            var pager = getPager();
            if (pager) {
                pager.goTo(1);
            }
        }

        // ── Search input ──────────────────────────────────────────────────────

        var searchInput = el.querySelector('.m-filter-bar-search');
        var searchTimer = null;

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                var val = searchInput.value;
                searchTimer = setTimeout(function () {
                    state.search = val;
                    fireChange();
                }, 200);
            });
        }

        // ── Sort / group button groups ────────────────────────────────────────

        var groups = el.querySelectorAll('.m-button-group[data-filter-type]');

        for (var gi = 0; gi < groups.length; gi++) {
            (function (group) {
                var type = group.getAttribute('data-filter-type'); // 'sort' | 'group'
                var btns = group.querySelectorAll('button[data-value]');

                for (var bi = 0; bi < btns.length; bi++) {
                    (function (btn) {
                        btn.addEventListener('click', function () {
                            // Already active — no-op
                            if (btn.classList.contains('m-button-group-active')) { return; }

                            // Deactivate siblings
                            for (var k = 0; k < btns.length; k++) {
                                btns[k].classList.remove('m-button-group-active');
                                btns[k].setAttribute('aria-pressed', 'false');
                            }
                            // Activate clicked
                            btn.classList.add('m-button-group-active');
                            btn.setAttribute('aria-pressed', 'true');

                            if (type === 'sort') {
                                state.sort = btn.getAttribute('data-value') || '';
                            } else if (type === 'group') {
                                state.group = btn.getAttribute('data-value') || '';
                            }
                            fireChange();
                        });
                    })(btns[bi]);
                }
            })(groups[gi]);
        }

        // ── Public API ────────────────────────────────────────────────────────

        return {
            /** Returns the current filter state: { search, sort, group }. */
            getState: function () {
                return { search: state.search, sort: state.sort, group: state.group };
            },

            /**
             * Programmatically update one or more filter state keys.
             * Updating via setState does NOT fire m:filterbar:change or reset the pager.
             *
             * @param {{ search?: string, sort?: string, group?: string }} partial
             */
            setState: function (partial) {
                if (partial.search !== undefined) {
                    state.search = partial.search;
                    if (searchInput) { searchInput.value = state.search; }
                }
                if (partial.sort !== undefined) {
                    state.sort = partial.sort;
                    var sortGroup = el.querySelector('.m-button-group[data-filter-type="sort"]');
                    if (sortGroup) {
                        var sortBtns = sortGroup.querySelectorAll('button');
                        for (var i = 0; i < sortBtns.length; i++) {
                            var active = sortBtns[i].getAttribute('data-value') === state.sort;
                            sortBtns[i].classList.toggle('m-button-group-active', active);
                            sortBtns[i].setAttribute('aria-pressed', active ? 'true' : 'false');
                        }
                    }
                }
                if (partial.group !== undefined) {
                    state.group = partial.group;
                    var groupGroup = el.querySelector('.m-button-group[data-filter-type="group"]');
                    if (groupGroup) {
                        var groupBtns = groupGroup.querySelectorAll('button');
                        for (var j = 0; j < groupBtns.length; j++) {
                            var gActive = groupBtns[j].getAttribute('data-value') === state.group;
                            groupBtns[j].classList.toggle('m-button-group-active', gActive);
                            groupBtns[j].setAttribute('aria-pressed', gActive ? 'true' : 'false');
                        }
                    }
                }
            },

            /**
             * Compute group-aware pages for an array of items.
             *
             * Groups items using `keyFn`, sorts the group keys using `sortFn`,
             * then packs them into pages.  Groups larger than `perPage` are split
             * across pages; continuation pages have their key suffixed with a
             * sentinel so callers can detect and label them.
             *
             * @param {Array}    items     Filtered and pre-sorted items
             * @param {Function} keyFn     item → string group key
             * @param {Function} sortFn    (keys: string[]) → string[] sorted keys
             * @param {number}   perPage   Target items per page
             * @returns {{ pages: Array, allGroups: Object, sortedKeys: string[], totalPages: number }}
             */
            computeGroupPages: function (items, keyFn, sortFn, perPage) {
                var allGroups = {};
                for (var i = 0; i < items.length; i++) {
                    var k = keyFn(items[i]);
                    if (!allGroups[k]) { allGroups[k] = []; }
                    allGroups[k].push(items[i]);
                }
                var sortedKeys = sortFn(Object.keys(allGroups));
                var pages      = packGroupPages(allGroups, sortedKeys, perPage);
                return {
                    pages:      pages,
                    allGroups:  allGroups,
                    sortedKeys: sortedKeys,
                    totalPages: pages.length
                };
            },

            /**
             * Return the groups to display for one specific page (0-based index).
             *
             * Each entry in `result.keys` is a virtual key.  Pass it through
             * `result.parseKey(virtKey)` to get `{ displayKey, continued }` for
             * rendering.  The `continued` flag indicates the group was split and
             * this is a continuation page (render "– continued" in the header).
             *
             * @param {Array}    items      Filtered and pre-sorted items
             * @param {Function} keyFn      item → string group key
             * @param {Function} sortFn     (keys: string[]) → string[] sorted keys
             * @param {number}   perPage    Target items per page (should match pager perPage)
             * @param {number}   pageIndex  0-based page index (= pager.getState().page - 1)
             * @returns {{ keys: string[], groups: Object, totalPages: number, parseKey: Function }}
             */
            groupSlice: function (items, keyFn, sortFn, perPage, pageIndex) {
                var allGroups = {};
                for (var i = 0; i < items.length; i++) {
                    var k = keyFn(items[i]);
                    if (!allGroups[k]) { allGroups[k] = []; }
                    allGroups[k].push(items[i]);
                }
                var sortedKeys = sortFn(Object.keys(allGroups));
                var pages      = packGroupPages(allGroups, sortedKeys, perPage);
                var pg         = pages[pageIndex] || pages[0] || { keys: [], pageGroups: {} };
                return {
                    keys:       pg.keys,
                    groups:     pg.pageGroups,
                    totalPages: pages.length,
                    parseKey:   parseVirtKey
                };
            }
        };
    };

})(window);
