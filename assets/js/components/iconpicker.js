/**
 * Manhattan UI — IconPicker Component
 *
 * A dropdown-style picker showing a configurable grid of Font Awesome icons.
 * Selecting an icon updates the trigger label, the hidden form input, and fires
 * a change event.
 *
 * PHP usage:
 *   echo $m->iconPicker('myPicker')
 *       ->name('icon_field')
 *       ->value('fa-star')
 *       ->icons([
 *           ['value' => 'fa-star',     'text' => 'Featured'],
 *           ['value' => 'fa-comments', 'text' => 'Comments'],
 *       ]);
 *
 * JS API:
 *   var picker = m.iconPicker('myPicker');
 *   picker.getValue()         // → 'fa-star'
 *   picker.setValue('fa-bug') // programmatically select an icon
 *   picker.open()             // open the panel
 *   picker.close()            // close the panel
 *
 * Events (fired on the container element):
 *   m:iconpicker:change   — { id, value, label }
 */
(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before iconpicker module');
        return;
    }

    var utils = m.utils;

    m.iconPicker = function (id) {
        var container = utils.getElement(id);
        if (!container) { return null; }

        // Prevent double-init
        if (container._mIconPicker) { return container._mIconPicker; }

        var trigger      = container.querySelector('.m-iconpicker-trigger');
        var panel        = container.querySelector('.m-iconpicker-panel');
        var grid         = container.querySelector('.m-iconpicker-grid');
        var hiddenInput  = container.querySelector('.m-iconpicker-input');
        var triggerIcon  = container.querySelector('.m-iconpicker-trigger-icon');
        var triggerLabel = container.querySelector('.m-iconpicker-trigger-label');
        var placeholder  = container.getAttribute('data-placeholder') || 'Select an icon…';

        if (!trigger || !panel) { return null; }

        // ------------------------------------------------------------------
        // Open / close
        // ------------------------------------------------------------------

        function open() {
            panel.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
        }

        function close() {
            panel.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        }

        function isOpen() {
            return !panel.hidden;
        }

        // ------------------------------------------------------------------
        // Selection
        // ------------------------------------------------------------------

        function updateTriggerDisplay(value, label) {
            if (triggerIcon) {
                if (value) {
                    triggerIcon.className = 'fas ' + value + ' m-iconpicker-trigger-icon';
                } else {
                    triggerIcon.className = 'fas fa-icons m-iconpicker-trigger-icon m-iconpicker-placeholder-icon';
                }
            }
            if (triggerLabel) {
                triggerLabel.textContent = value ? label : placeholder;
            }
        }

        function selectIcon(value, label) {
            if (hiddenInput) { hiddenInput.value = value; }
            container.setAttribute('data-value', value);

            updateTriggerDisplay(value, label);

            // Reflect selection in the grid
            if (grid) {
                grid.querySelectorAll('.m-iconpicker-btn').forEach(function (btn) {
                    var sel = btn.getAttribute('data-value') === value;
                    btn.classList.toggle('m-iconpicker-selected', sel);
                    btn.setAttribute('aria-selected', sel ? 'true' : 'false');
                });
            }

            close();
            utils.trigger(container, 'm:iconpicker:change', { id: id, value: value, label: label });
        }

        // ------------------------------------------------------------------
        // Event listeners
        // ------------------------------------------------------------------

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            if (isOpen()) { close(); } else { open(); }
        });

        if (grid) {
            grid.addEventListener('click', function (e) {
                var btn = e.target.closest('.m-iconpicker-btn');
                if (!btn) { return; }
                var val   = btn.getAttribute('data-value') || '';
                var label = btn.getAttribute('data-label') || val;
                selectIcon(val, label);
            });
        }

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (isOpen() && !container.contains(e.target)) {
                close();
            }
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen()) { close(); }
        });

        // ------------------------------------------------------------------
        // Public API
        // ------------------------------------------------------------------

        var api = {
            /** Return the currently selected icon value (FA class string). */
            getValue: function () {
                return hiddenInput ? hiddenInput.value : '';
            },

            /** Programmatically select an icon by its FA class string. */
            setValue: function (value) {
                var label = value;
                if (grid) {
                    var btn = grid.querySelector('[data-value="' + value + '"]');
                    if (btn) { label = btn.getAttribute('data-label') || value; }
                }
                selectIcon(value, label);
            },

            open:    open,
            close:   close,
            element: container
        };

        container._mIconPicker = api;
        return api;
    };

    // Auto-initialise all iconpicker containers on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        var pickers = document.querySelectorAll('[data-component="iconpicker"]');
        for (var i = 0; i < pickers.length; i++) {
            if (pickers[i].id) {
                m.iconPicker(pickers[i].id);
            }
        }
    });

})(window);
