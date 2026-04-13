/**
 * Manhattan ButtonGroup Component
 *
 * Mutually exclusive toggle button group (radio behaviour).
 * Supports icon-only buttons or icon + label buttons.
 * Auto-initialises all [data-component="button-group"] elements on DOMContentLoaded.
 *
 * When any button in the PHP definition includes a 'label' key, the group
 * receives the `m-button-group--labeled` CSS modifier which switches to
 * auto-sized pill buttons.
 *
 * Events:
 *   m:buttongroup:change — fired on the group element when the active button changes.
 *   detail: { value: string }
 *
 * API:
 *   var g = m.buttonGroup('myGroup');
 *   g.getActive()      // string|null — current active value
 *   g.setActive('az')  // programmatically activate by value
 */
(function (window) {
    'use strict';

    var m     = window.m;
    var utils = m.utils;

    /**
     * Create (or retrieve) a ButtonGroup instance.
     * @param {string|HTMLElement} id
     * @returns {{ getActive: function(): string|null, setActive: function(string): void }|null}
     */
    m.buttonGroup = function (id) {
        var el = utils.getElement(id);
        if (!el) { return null; }

        // Prevent double-init
        if (el._mButtonGroup) { return el._mButtonGroup; }

        function getActive() {
            var active = el.querySelector('.m-button-group-btn.m-button-group-active');
            return active ? active.getAttribute('data-value') : null;
        }

        function setActive(value) {
            var changed = false;
            var btns = el.querySelectorAll('.m-button-group-btn');
            btns.forEach(function (btn) {
                var isTarget = btn.getAttribute('data-value') === value;
                var wasActive = btn.classList.contains('m-button-group-active');
                if (isTarget) {
                    btn.classList.add('m-button-group-active');
                    if (!wasActive) { changed = true; }
                } else {
                    btn.classList.remove('m-button-group-active');
                }
            });
            if (changed) {
                utils.trigger(el, 'm:buttongroup:change', { value: value });
            }
        }

        // Click handler — delegate to button within group
        el.addEventListener('click', function (e) {
            var btn = e.target.closest('.m-button-group-btn');
            if (!btn || btn.closest('[data-component="button-group"]') !== el) { return; }
            var value = btn.getAttribute('data-value');
            if (value) { setActive(value); }
        });

        var api = { getActive: getActive, setActive: setActive };
        el._mButtonGroup = api;
        return api;
    };

    // Auto-init on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-component="button-group"]').forEach(function (el) {
            if (el.id) { m.buttonGroup(el.id); }
        });
    });

}(window));
