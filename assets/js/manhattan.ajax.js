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

    m.ajax = function(url, options) {
        options = utils.extend({
            method: 'GET',
            data: null,
            beforeSend: null,
            success: null,
            error: null,
            complete: null,
            headers: null,
            contentType: 'application/json'
        }, options || {});

        const method = String(options.method || 'GET').toUpperCase();
        const hasBody = !(method === 'GET' || method === 'HEAD') && options.data !== null && options.data !== undefined;

        const headers = utils.extend({
            'X-Requested-With': 'XMLHttpRequest'
        }, options.headers || {});

        // CSRF support (expects <meta name="csrf-token" content="...">)
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta && csrfMeta.getAttribute('content')) {
            headers['X-CSRF-Token'] = csrfMeta.getAttribute('content');
        }

        if (hasBody && options.contentType) {
            headers['Content-Type'] = options.contentType;
        }

        if (typeof options.beforeSend === 'function') {
            try { options.beforeSend(); } catch (e) { /* noop */ }
        }

        let lastResponse = null;

        return fetch(url, {
            method: method,
            headers: headers,
            body: hasBody ? JSON.stringify(options.data) : null
        })
        .then(async (response) => {
            lastResponse = response;

            const text = await response.text();
            let parsed = null;
            if (text) {
                try {
                    parsed = JSON.parse(text);
                } catch (e) {
                    parsed = text;
                }
            }

            if (!response.ok) {
                const err = new Error('Request failed');
                err.status = response.status;
                err.response = response;
                err.data = parsed;
                throw err;
            }

            if (typeof options.success === 'function') {
                options.success(parsed, response);
            }

            return parsed;
        })
        .catch((error) => {
            if (typeof options.error === 'function') {
                try { options.error(error, lastResponse); } catch (e) { /* noop */ }
            }
            console.error('Manhattan Ajax Error:', error);
            return null;
        })
        .finally(() => {
            if (typeof options.complete === 'function') {
                try { options.complete(); } catch (e) { /* noop */ }
            }
        });
    };

    /**
     * List Component
     * - Optional drag/drop reordering (default off)
     * - Emits 'm:list:reorder' on the list element when order changes
     * - Optionally persists order to server when updateModelOnReorder + updateUrl are set
     */

})(window);
