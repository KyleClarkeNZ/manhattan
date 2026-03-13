/**
 * Manhattan UI Framework - ProgressBar Component
 */

(function(window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before progressbar module');
        return;
    }

    const utils = m.utils;

    /**
     * ProgressBar component
     * Provides JS API for reading and updating progress values
     * 
     * @param {string} id - The progress bar element ID
     * @returns {Object} ProgressBar API
     */
    m.progressBar = function(id) {
        const el = utils.getElement(id);
        if (!el) {
            console.warn('Manhattan ProgressBar: Element not found:', id);
            return null;
        }

        const track = el.querySelector('.m-progress-track');
        const fill = el.querySelector('.m-progress-fill');
        const labelEl = el.querySelector('.m-progress-label');
        const pctEl = el.querySelector('.m-progress-pct');

        /**
         * Get current value from data attribute
         */
        const getValue = function() {
            return parseFloat(el.getAttribute('data-value') || '0');
        };

        /**
         * Get max value from data attribute
         */
        const getMax = function() {
            return parseFloat(el.getAttribute('data-max') || '100');
        };

        /**
         * Calculate percentage
         */
        const getPercent = function() {
            const value = getValue();
            const max = getMax();
            return max > 0 ? Math.min(100, (value / max) * 100) : 0;
        };

        /**
         * Update the progress bar value
         * @param {number} newValue - New progress value
         * @param {boolean} animate - Whether to animate the change (default: true)
         */
        const setValue = function(newValue, animate) {
            animate = animate !== false; // default true
            
            const oldValue = getValue();
            const max = getMax();
            
            // Clamp value
            newValue = Math.max(0, Math.min(max, newValue));
            
            // Update data attribute
            el.setAttribute('data-value', newValue.toString());
            
            // Calculate percentage
            const pct = max > 0 ? Math.min(100, (newValue / max) * 100) : 0;
            const pctRounded = Math.round(pct * 10) / 10;
            
            // Update fill width
            if (fill) {
                if (!animate) {
                    // Temporarily disable transition
                    const originalTransition = fill.style.transition;
                    fill.style.transition = 'none';
                    fill.style.width = pctRounded + '%';
                    // Force reflow
                    fill.offsetHeight;
                    fill.style.transition = originalTransition;
                } else {
                    fill.style.width = pctRounded + '%';
                }
                
                // Update ARIA
                if (track) {
                    track.setAttribute('aria-valuenow', newValue.toString());
                }
            }
            
            // Update percentage display if present
            if (pctEl) {
                pctEl.textContent = pctRounded + '%';
            }
            
            // Fire event
            utils.trigger(el, 'm:progressbar:change', {
                id: id,
                oldValue: oldValue,
                newValue: newValue,
                percent: pctRounded,
                max: max
            });
            
            return api;
        };

        /**
         * Set the max value
         * @param {number} newMax - New maximum value
         */
        const setMax = function(newMax) {
            newMax = Math.max(1, newMax);
            el.setAttribute('data-max', newMax.toString());
            
            if (track) {
                track.setAttribute('aria-valuemax', newMax.toString());
            }
            
            // Recalculate with current value
            setValue(getValue(), false);
            
            return api;
        };

        /**
         * Increment the value
         * @param {number} amount - Amount to increment (default: 1)
         */
        const increment = function(amount) {
            amount = amount || 1;
            setValue(getValue() + amount);
            return api;
        };

        /**
         * Decrement the value
         * @param {number} amount - Amount to decrement (default: 1)
         */
        const decrement = function(amount) {
            amount = amount || 1;
            setValue(getValue() - amount);
            return api;
        };

        /**
         * Set to 100% (complete)
         */
        const complete = function() {
            setValue(getMax());
            utils.trigger(el, 'm:progressbar:complete', {
                id: id,
                value: getMax()
            });
            return api;
        };

        /**
         * Reset to 0
         */
        const reset = function() {
            setValue(0);
            utils.trigger(el, 'm:progressbar:reset', {
                id: id
            });
            return api;
        };

        const api = {
            element: el,
            getValue: getValue,
            getMax: getMax,
            getPercent: getPercent,
            setValue: setValue,
            setMax: setMax,
            increment: increment,
            decrement: decrement,
            complete: complete,
            reset: reset
        };

        return api;
    };

})(window);
