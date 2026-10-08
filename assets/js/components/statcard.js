/**
 * Manhattan UI Framework - StatCard Component
 *
 * JS API for updating a server-rendered StatCard in place:
 *
 *   m.statCard('activeCount').value();        // 12 (number; 0 when not numeric)
 *   m.statCard('activeCount').value(13);      // numbers are locale-formatted ("8,432")
 *   m.statCard('activeCount').value('—');     // strings are shown as-is
 *   m.statCard('activeCount').increment();    // +1
 *   m.statCard('activeCount').increment(-1);  // -1
 *   m.statCard('activeCount').label('Open');
 *
 * Events (fired on the card element):
 *   m:statcard:change — { value } — after value() / increment() changes the value
 */

(function(window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before statcard module');
        return;
    }

    const utils = m.utils;

    m.statCard = function(id) {
        const el = utils.getElement(id);
        if (!el) {
            console.warn('Manhattan StatCard: Element not found:', id);
            return null;
        }
        if (el._mStatCardInstance) {
            return el._mStatCardInstance;
        }

        const valueEl = el.querySelector('.m-stat-card-value');
        const labelEl = el.querySelector('.m-stat-card-label');

        function readNumber() {
            const n = parseFloat(String(valueEl ? valueEl.textContent : '').replace(/[^0-9.\-]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function writeValue(val) {
            if (!valueEl) return;
            valueEl.textContent = typeof val === 'number' ? val.toLocaleString() : String(val);
            utils.trigger(el, 'm:statcard:change', { value: val });
        }

        const api = {
            element: el,

            value: function(val) {
                if (val === undefined) {
                    return readNumber();
                }
                writeValue(val);
                return api;
            },

            increment: function(by) {
                writeValue(readNumber() + (by === undefined ? 1 : Number(by)));
                return api;
            },

            label: function(text) {
                if (text === undefined) {
                    return labelEl ? labelEl.textContent : '';
                }
                if (labelEl) labelEl.textContent = String(text);
                return api;
            }
        };

        el._mStatCardInstance = api;
        return api;
    };

})(window);
