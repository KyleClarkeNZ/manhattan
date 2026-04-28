/**
 * Manhattan UI Framework - Address Component
 * - NZ mode: typeahead suggestions via backend proxy (NZPost)
 * - Overseas mode: manual address inputs
 */

(function(window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before address module');
        return;
    }

    const utils = m.utils;

    function debounce(fn, delayMs) {
        let timer = null;
        return function() {
            const ctx = this;
            const args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() {
                fn.apply(ctx, args);
            }, delayMs);
        };
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Shared result cache (module-scoped, keyed by 'url|query') ─────────────
    // Avoids repeat API calls for the same query; expires after 5 minutes.
    const CACHE_TTL_MS = 5 * 60 * 1000;
    const suggestCache = {};

    function cacheKey(url, query) { return url + '|' + query.toLowerCase(); }

    function getCached(url, query) {
        const key = cacheKey(url, query);
        const entry = suggestCache[key];
        if (!entry) { return null; }
        if (Date.now() - entry.ts > CACHE_TTL_MS) { delete suggestCache[key]; return null; }
        return entry.data;
    }

    function setCached(url, query, data) {
        suggestCache[cacheKey(url, query)] = { ts: Date.now(), data: data };
    }

    function getDataAttr(el, name, fallback) {
        const v = el.getAttribute('data-' + name);
        return (v === null || v === undefined || v === '') ? fallback : v;
    }

    function normalizeSuggestions(payload) {
        if (!payload) return [];

        // Our proxy returns {success, suggestions: []}
        if (Array.isArray(payload.suggestions)) {
            return payload.suggestions;
        }

        // Sometimes API returns {addresses: []} / {results: []}
        if (Array.isArray(payload.addresses)) {
            return payload.addresses;
        }
        if (Array.isArray(payload.results)) {
            return payload.results;
        }

        if (Array.isArray(payload)) {
            return payload;
        }

        return [];
    }

    function suggestionLabel(s) {
        if (!s) return '';
        if (typeof s === 'string') return s;
        return s.label || s.text || s.fullAddress || s.FullAddress || s.address || s.Address || s.description || s.Description || '';
    }

    function suggestionValue(s) {
        if (!s) return '';
        if (typeof s === 'string') return s;
        return s.value || s.id || s.Id || s.addressId || s.address_id || s.dpid || s.DPID || suggestionLabel(s);
    }

    function setHidden(root, field, value) {
        const el = root.querySelector('.m-address-nz-hidden[data-field="' + field + '"]');
        if (el) el.value = value == null ? '' : String(value);
    }

    function setHelp(root, message, type) {
        const help = root.querySelector('[data-role="help"]');
        if (!help) return;

        if (!message) {
            help.hidden = true;
            help.textContent = '';
            help.classList.remove('m-address-help-error');
            help.classList.remove('m-address-help-info');
            return;
        }

        help.hidden = false;
        help.textContent = String(message);
        help.classList.toggle('m-address-help-error', type === 'error');
        help.classList.toggle('m-address-help-info', type !== 'error');
    }

    function showResults(resultsEl) {
        resultsEl.hidden = false;
        resultsEl.classList.add('m-address-results-open');
    }

    function hideResults(resultsEl) {
        resultsEl.hidden = true;
        resultsEl.classList.remove('m-address-results-open');
        resultsEl.innerHTML = '';
        resultsEl._activeIndex = -1;
    }

    function renderResults(resultsEl, suggestions) {
        const items = (suggestions || []).slice(0, 10);
        if (!items.length) {
            hideResults(resultsEl);
            return;
        }

        const html = items.map(function(s, idx) {
            const label = suggestionLabel(s);
            const value = suggestionValue(s);
            return (
                '<div class="m-address-result" role="option" tabindex="-1" data-index="' + idx + '" data-value="' + escapeHtml(value) + '">' +
                '<span class="m-address-result-icon" aria-hidden="true">' + m.icon('fa-map-marker-alt') + '</span>' +
                '<span class="m-address-result-text">' + escapeHtml(label) + '</span>' +
                '</div>'
            );
        }).join('');

        resultsEl.innerHTML = html;
        resultsEl._items = items;
        resultsEl._activeIndex = -1;
        showResults(resultsEl);
    }

    function setActiveResult(resultsEl, index) {
        const els = Array.from(resultsEl.querySelectorAll('.m-address-result'));
        els.forEach(function(el) { el.classList.remove('m-address-result-active'); });

        if (!els.length) {
            resultsEl._activeIndex = -1;
            return;
        }

        const next = Math.max(0, Math.min(index, els.length - 1));
        resultsEl._activeIndex = next;
        els[next].classList.add('m-address-result-active');
        els[next].scrollIntoView({ block: 'nearest' });
    }

    m.address = function(id, options) {
        const root = utils.getElement(id);
        if (!root) {
            console.warn('Manhattan: Address element not found:', id);
            return null;
        }

        if (root._manhattanAddress) {
            // Already initialized; allow caller to attach extra change handler by returning the API.
            return root._manhattanAddress;
        }

        options = utils.extend({
            minChars: 3,
            debounceMs: 250,
            suggestUrl: null,
            onChange: null
        }, options || {});

        const suggestUrl = options.suggestUrl || getDataAttr(root, 'suggest-url', '');

        const nzPanel = root.querySelector('.m-address-panel-nz');
        const overseasPanel = root.querySelector('.m-address-panel-overseas');

        const search = root.querySelector('.m-address-search');
        const results = root.querySelector('.m-address-results');

        const typeRadios = Array.from(root.querySelectorAll('input[type="radio"][name$="[type]"]'));

        // Inject a small loader between the search box and the results dropdown.
        // Hidden by default; shown while an API request is in flight.
        const searchWrapper = nzPanel ? nzPanel.querySelector('.m-address-search-wrapper') : null;
        const affixIcon = searchWrapper ? searchWrapper.querySelector('.m-address-search-affix i') : null;
        var loader = null;
        if (searchWrapper && results) {
            loader = document.createElement('div');
            loader.className = 'm-loader m-loader-sm m-address-loader m-hidden';
            loader.innerHTML = '<div class="m-loader-spinner"></div><span class="m-loader-text">Searching…</span>';
            results.parentNode.insertBefore(loader, results);
        }

        function currentMode() {
            const checked = typeRadios.find(function(r) { return r.checked; });
            return checked ? checked.value : 'nz';
        }

        function setMode(mode) {
            const isNz = mode === 'nz';
            if (nzPanel) nzPanel.classList.toggle('m-hidden', !isNz);
            if (overseasPanel) overseasPanel.classList.toggle('m-hidden', isNz);

            // Disable unused inputs to avoid accidental form submits
            if (nzPanel) {
                nzPanel.querySelectorAll('input, select, textarea').forEach(function(el) {
                    if (el.type === 'hidden') return; // keep hidden values enabled
                    el.disabled = !isNz;
                });
            }
            if (overseasPanel) {
                overseasPanel.querySelectorAll('input, select, textarea').forEach(function(el) {
                    el.disabled = isNz;
                });
            }

            if (results) hideResults(results);
            setHelp(root, '', 'info');

            utils.trigger(root, 'm:address:mode', { mode: mode });
            if (typeof options.onChange === 'function') {
                options.onChange({ mode: mode, source: 'mode' });
            }
        }

        function clearSelection() {
            setHidden(root, 'line1', '');
            setHidden(root, 'line2', '');
            setHidden(root, 'suburb', '');
            setHidden(root, 'city', '');
            setHidden(root, 'postcode', '');
            setHidden(root, 'lat', '');
            setHidden(root, 'lng', '');
            setHidden(root, 'raw', '');
        }

        function applySelection(suggestion) {
            const label = suggestionLabel(suggestion);

            if (search) {
                search.value = label;
            }

            // Best-effort mapping. If the API returns structured fields, we preserve them.
            const data = (suggestion && suggestion.data) ? suggestion.data : suggestion;

            setHidden(root, 'line1', data.line1 || data.Line1 || data.address1 || data.Address1 || label);
            setHidden(root, 'line2', data.line2 || data.Line2 || data.address2 || data.Address2 || '');
            setHidden(root, 'suburb', data.suburb || data.Suburb || '');
            setHidden(root, 'city', data.city || data.City || data.town || data.Town || '');
            setHidden(root, 'postcode', data.postcode || data.Postcode || data.PostCode || data.zip || data.Zip || '');
            setHidden(root, 'lat', data.lat || data.Lat || data.latitude || data.Latitude || '');
            setHidden(root, 'lng', data.lng || data.Lng || data.longitude || data.Longitude || data.lon || data.Lon || '');

            try {
                setHidden(root, 'raw', JSON.stringify(data));
            } catch (e) {
                setHidden(root, 'raw', '');
            }

            if (results) hideResults(results);
            setHelp(root, '', 'info');
            if (loader) { loader.classList.add('m-hidden'); }
            // Show a confirmed-address checkmark in the search affix icon
            if (affixIcon) {
                affixIcon.classList.remove('fa-search');
                affixIcon.classList.add('fa-check-circle');
            }
            root.classList.add('m-address-confirmed');

            utils.trigger(root, 'm:address:select', { suggestion: suggestion, label: label, value: suggestionValue(suggestion) });
            if (typeof options.onChange === 'function') {
                options.onChange({ mode: 'nz', source: 'select', suggestion: suggestion });
            }
        }

        // Generation counter — incremented on every new request. Response handlers
        // compare their captured generation against this value; if it has moved on,
        // the response is stale and is silently discarded.  This prevents a slow
        // response for "te a" from overwriting the fresher results already shown
        // for "te ata" when the user typed quickly.
        var requestGen = 0;

        const doSuggest = debounce(function() {
            if (!search || !results) return;
            if (currentMode() !== 'nz') return;

            const q = String(search.value || '').trim();
            clearSelection();

            if (q.length < options.minChars) {
                requestGen++; // discard any in-flight request
                hideResults(results);
                setHelp(root, '', 'info');
                if (loader) { loader.classList.add('m-hidden'); }
                return;
            }

            if (!suggestUrl) {
                setHelp(root, 'NZPost autocomplete is not configured (missing suggest URL).', 'error');
                hideResults(results);
                return;
            }

            // Cache hit — instant results, no request needed.
            const cached = getCached(suggestUrl, q);
            if (cached !== null) {
                if (!cached.length) {
                    setHelp(root, 'No matches found.', 'info');
                    hideResults(results);
                } else {
                    setHelp(root, '', 'info');
                    renderResults(results, cached);
                }
                return;
            }

            // New request — bump generation so any in-flight response for an older
            // query is discarded when it arrives.
            requestGen++;
            var myGen = requestGen;

            setHelp(root, '', 'info');
            if (loader) { loader.classList.remove('m-hidden'); }

            m.ajax(suggestUrl + (suggestUrl.indexOf('?') === -1 ? '?' : '&') + 'q=' + encodeURIComponent(q), {
                method: 'GET',
                contentType: null
            }).then(function(payload) {
                if (myGen !== requestGen) { return; } // stale response — discard
                if (loader) { loader.classList.add('m-hidden'); }

                const suggestions = normalizeSuggestions(payload);

                if (!suggestions.length) {
                    setCached(suggestUrl, q, suggestions);
                    setHelp(root, 'No matches found.', 'info');
                    hideResults(results);
                    return;
                }

                setCached(suggestUrl, q, suggestions);
                setHelp(root, '', 'info');
                renderResults(results, suggestions);
            }).catch(function() {
                if (myGen !== requestGen) { return; } // stale — discard
                if (loader) { loader.classList.add('m-hidden'); }
                setHelp(root, 'Address lookup failed. Please type the address manually.', 'error');
                hideResults(results);
            });
        }, options.debounceMs);

        // Mode toggle
        typeRadios.forEach(function(r) {
            r.addEventListener('change', function() {
                setMode(currentMode());
            });
        });

        // Suggest on input
        if (search && results) {
            search.addEventListener('input', function() {
                // Clear confirmed state when the user starts typing again
                if (affixIcon) {
                    affixIcon.classList.remove('fa-check-circle');
                    affixIcon.classList.add('fa-search');
                }
                root.classList.remove('m-address-confirmed');
                doSuggest();
            });

            search.addEventListener('keydown', function(e) {
                if (results.hidden) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActiveResult(results, (results._activeIndex || 0) + 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActiveResult(results, (results._activeIndex || 0) - 1);
                } else if (e.key === 'Enter') {
                    const idx = results._activeIndex;
                    if (typeof idx === 'number' && idx >= 0 && results._items && results._items[idx]) {
                        e.preventDefault();
                        applySelection(results._items[idx]);
                    }
                } else if (e.key === 'Escape') {
                    hideResults(results);
                }
            });

            results.addEventListener('mousedown', function(e) {
                const target = e.target.closest('.m-address-result');
                if (!target) return;
                const idx = parseInt(target.getAttribute('data-index') || '-1', 10);
                if (!isNaN(idx) && results._items && results._items[idx]) {
                    applySelection(results._items[idx]);
                }
            });

            document.addEventListener('click', function(e) {
                if (!root.contains(e.target)) {
                    hideResults(results);
                }
            });
        }

        // Initial mode
        setMode(currentMode());

        const api = {
            element: root,
            setMode: function(mode) {
                typeRadios.forEach(function(r) {
                    r.checked = (r.value === mode);
                });
                setMode(mode);
                return this;
            },
            clear: function() {
                if (search) search.value = '';
                clearSelection();
                if (results) hideResults(results);
                if (loader) { loader.classList.add('m-hidden'); }
                if (affixIcon) {
                    affixIcon.classList.remove('fa-check-circle');
                    affixIcon.classList.add('fa-search');
                }
                root.classList.remove('m-address-confirmed');
                return this;
            },
            /**
             * Pre-populate the search box display text without triggering a search.
             * Use this when restoring a saved address from the database on edit pages.
             * Automatically shows the confirmed (green checkmark) state when a non-empty
             * value is provided.
             */
            setValue: function(text) {
                if (search) { search.value = text; }
                if (text && String(text).trim()) {
                    if (affixIcon) {
                        affixIcon.classList.remove('fa-search');
                        affixIcon.classList.add('fa-check-circle');
                    }
                    root.classList.add('m-address-confirmed');
                }
                return this;
            },
            getCoordinates: function() {
                var latEl = root.querySelector('.m-address-nz-hidden[data-field="lat"]');
                var lngEl = root.querySelector('.m-address-nz-hidden[data-field="lng"]');
                if (!latEl || !lngEl || latEl.value === '' || lngEl.value === '') {
                    return null;
                }
                var lat = parseFloat(latEl.value);
                var lng = parseFloat(lngEl.value);
                if (isNaN(lat) || isNaN(lng)) {
                    return null;
                }
                return { lat: lat, lng: lng };
            }
        };

        root._manhattanAddress = api;
        return api;
    };

})(window);
