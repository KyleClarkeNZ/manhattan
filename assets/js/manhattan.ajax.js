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

        // FormData, URLSearchParams and Blob bodies are sent as-is so file uploads
        // work; the browser sets their Content-Type (including the multipart boundary).
        const rawBody = hasBody && isRawBody(options.data);

        const headers = utils.extend({
            'X-Requested-With': 'XMLHttpRequest'
        }, options.headers || {});

        // CSRF support (expects <meta name="csrf-token" content="...">)
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta && csrfMeta.getAttribute('content')) {
            headers['X-CSRF-Token'] = csrfMeta.getAttribute('content');
        }

        if (hasBody && !rawBody && options.contentType) {
            headers['Content-Type'] = options.contentType;
        }

        if (typeof options.beforeSend === 'function') {
            try { options.beforeSend(); } catch (e) { /* noop */ }
        }

        let lastResponse = null;

        return fetch(url, {
            method: method,
            headers: headers,
            body: hasBody ? (rawBody ? options.data : JSON.stringify(options.data)) : null,
            signal: options.signal || undefined
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
                // Prefer the server's own message, e.g. {"success": false, "message": "..."}
                const serverMessage = parsed && typeof parsed === 'object' && typeof parsed.message === 'string'
                    ? parsed.message : '';
                const err = new Error(serverMessage || 'Request failed');
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
            // AbortError is expected (e.g. typeahead cancelling stale requests) — don't log
            const isAbort = error && (error.name === 'AbortError' || error.code === 20);
            if (typeof options.error === 'function' && !isAbort) {
                try { options.error(error, lastResponse); } catch (e) { /* noop */ }
            }
            if (!isAbort) {
                console.error('Manhattan Ajax Error:', error);
            }
            return null;
        })
        .finally(() => {
            if (typeof options.complete === 'function') {
                try { options.complete(); } catch (e) { /* noop */ }
            }
        });
    };

    function isRawBody(data) {
        return (typeof FormData !== 'undefined' && data instanceof FormData)
            || (typeof URLSearchParams !== 'undefined' && data instanceof URLSearchParams)
            || (typeof Blob !== 'undefined' && data instanceof Blob);
    }

})(window);
