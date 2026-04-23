/**
 * Manhattan UI Framework — TimePicker component module
 *
 * Custom time selection control. Wraps a hidden text input in a styled
 * trigger + dropdown panel with scrollable hour and minute columns.
 *
 * PHP renders:
 *   <input type="text" id="{id}" class="m-timepicker" name="..." value="..."
 *          data-step="15" [data-show-now="true"] [data-12h="true"]
 *          [data-format="H:i"] [disabled]>
 *
 * Format tokens (data-format, PHP date-style):
 *   H  24-hour hours with leading zero (00-23)
 *   G  24-hour hours without leading zero (0-23)
 *   h  12-hour hours with leading zero (01-12)
 *   g  12-hour hours without leading zero (1-12)
 *   i  minutes with leading zero (00-59)
 *   A  uppercase AM/PM
 *   a  lowercase am/pm
 *   Default: 'H:i'
 *
 * JS API:
 *   var tp = m.timepicker('myId');
 *   tp.value();          // getter — returns value in the configured format, or ''
 *   tp.value('14:30');   // setter — accepts HH:MM or a formatted string
 *   tp.enable(); tp.disable();
 *   tp.clear();
 *
 * Events:
 *   input element fires 'm:timepicker:change' with { detail: { value: '...' } }
 *   value is in the configured output format (default 'H:i' = HH:MM).
 */

(function(window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before timepicker module');
        return;
    }

    var utils = m.utils;

    m.timepicker = function(id, options) {
        var input = utils.getElement(id);
        if (!input) {
            console.warn('Manhattan TimePicker: element not found:', id);
            return null;
        }

        // Return cached instance so multiple callers share the same closure
        if (input._mTimepicker) {
            return input._mTimepicker;
        }

        options = options || {};

        var step        = parseInt(input.getAttribute('data-step'), 10) || 15;
        var showNow     = input.getAttribute('data-show-now') === 'true';
        var use12h      = input.getAttribute('data-12h') === 'true';        var format      = input.getAttribute('data-format') || 'H:i';        var placeholder = input.getAttribute('placeholder') || 'Select time\u2026';

        // ---------------------------------------------------------------
        // DOM construction
        // ---------------------------------------------------------------

        // Wrapper
        var wrapper = document.createElement('div');
        wrapper.className = 'm-timepicker-wrapper';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        // Hide original input
        input.style.position = 'absolute';
        input.style.opacity  = '0';
        input.style.width    = '1px';
        input.style.height   = '1px';
        input.style.pointerEvents = 'none';

        // Trigger button
        var trigger = document.createElement('div');
        trigger.className = 'm-timepicker-input';
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('role', 'button');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.innerHTML =
            '<span class="m-timepicker-value"></span>' +
            '<i class="fas fa-clock m-timepicker-icon" aria-hidden="true"></i>';
        wrapper.insertBefore(trigger, input);

        // Dropdown panel (built on demand)
        var panel = document.createElement('div');
        panel.className = 'm-timepicker-panel';
        panel.style.display = 'none';
        wrapper.appendChild(panel);

        var valueSpan = trigger.querySelector('.m-timepicker-value');

        if (input.disabled) {
            trigger.classList.add('m-disabled');
        }

        // ---------------------------------------------------------------
        // Helpers
        // ---------------------------------------------------------------

        function pad(n) {
            return n < 10 ? '0' + n : String(n);
        }

        /**
         * Apply the configured format tokens to a resolved 24-hour h/m pair.
         * Tokens: H G h g i A a  (PHP date-style)
         */
        function applyFormat(h24, min) {
            var h12  = h24 % 12 || 12;
            var ampm = h24 < 12 ? 'AM' : 'PM';
            return format.replace(/H|G|h|g|i|A|a/g, function(token) {
                switch (token) {
                    case 'H': return pad(h24);
                    case 'G': return String(h24);
                    case 'h': return pad(h12);
                    case 'g': return String(h12);
                    case 'i': return pad(min);
                    case 'A': return ampm;
                    case 'a': return ampm.toLowerCase();
                }
                return token;
            });
        }

        function parseTime(str) {
            if (!str) { return null; }
            str = str.trim();

            // HH:MM or HH:MM:SS (24-hour, from DB or default format)
            var m24 = str.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
            if (m24) {
                var h   = parseInt(m24[1], 10);
                var min = parseInt(m24[2], 10);
                if (h >= 0 && h <= 23 && min >= 0 && min <= 59) {
                    return { h: h, m: min };
                }
                return null;
            }

            // 12-hour with AM/PM: e.g. '2:30 PM', '02:30 pm', '2:30PM'
            var m12 = str.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
            if (m12) {
                var h12  = parseInt(m12[1], 10);
                var min2 = parseInt(m12[2], 10);
                var period = m12[3].toUpperCase();
                if (h12 >= 1 && h12 <= 12 && min2 >= 0 && min2 <= 59) {
                    var h24;
                    if (period === 'AM') {
                        h24 = (h12 === 12) ? 0 : h12;
                    } else {
                        h24 = (h12 === 12) ? 12 : h12 + 12;
                    }
                    return { h: h24, m: min2 };
                }
                return null;
            }

            return null;
        }

        function formatDisplay(h, m) {
            if (use12h) {
                var ampm = h < 12 ? 'AM' : 'PM';
                var h12  = h % 12 || 12;
                return h12 + ':' + pad(m) + '\u00a0' + ampm;
            }
            return pad(h) + ':' + pad(m);
        }

        /** Snap a raw minute value to the nearest step boundary. */
        function snapMinute(min) {
            return Math.round(min / step) * step % 60;
        }

        function updateDisplay() {
            var val = input.value;
            var t   = parseTime(val);
            if (t) {
                valueSpan.textContent = formatDisplay(t.h, t.m);
                valueSpan.classList.add('m-has-value');
            } else {
                valueSpan.textContent = placeholder;
                valueSpan.classList.remove('m-has-value');
            }
        }

        // ---------------------------------------------------------------
        // Panel building
        // ---------------------------------------------------------------

        function buildPanel() {
            var t    = parseTime(input.value);
            var selH = t ? t.h : -1;
            var selM = t ? t.m : -1;

            // Hour list
            var hourCount = use12h ? 12 : 24;
            var hourStart = use12h ? 1 : 0;

            var hoursHtml = '<div class="m-tp-col m-tp-hours" role="listbox" aria-label="Hour">';
            for (var i = 0; i < hourCount; i++) {
                var hVal = hourStart + i;
                var isHourSel = false;
                if (selH >= 0) {
                    if (use12h) {
                        var h12 = selH % 12 || 12;
                        isHourSel = h12 === hVal;
                    } else {
                        isHourSel = selH === hVal;
                    }
                }
                var hLabel = use12h ? String(hVal) : pad(hVal);
                hoursHtml += '<div class="m-tp-item' + (isHourSel ? ' m-tp-selected' : '') +
                    '" data-val="' + hVal + '" role="option" aria-selected="' +
                    (isHourSel ? 'true' : 'false') + '">' + hLabel + '</div>';
            }
            hoursHtml += '</div>';

            // Minute list
            var minsHtml = '<div class="m-tp-col m-tp-minutes" role="listbox" aria-label="Minute">';
            for (var mVal = 0; mVal < 60; mVal += step) {
                var snapped   = selM >= 0 ? snapMinute(selM) : -1;
                var isMinSel  = snapped === mVal && selM >= 0;
                minsHtml += '<div class="m-tp-item' + (isMinSel ? ' m-tp-selected' : '') +
                    '" data-val="' + mVal + '" role="option" aria-selected="' +
                    (isMinSel ? 'true' : 'false') + '">' + pad(mVal) + '</div>';
            }
            minsHtml += '</div>';

            // AM/PM (12h only)
            var ampmHtml = '';
            if (use12h) {
                var amSel = selH >= 0 && selH < 12;
                var pmSel = selH >= 12;
                ampmHtml =
                    '<div class="m-tp-col m-tp-ampm">' +
                    '<div class="m-tp-item m-tp-ampm-item' + (amSel ? ' m-tp-selected' : '') +
                        '" data-val="am" role="option" aria-selected="' + (amSel ? 'true' : 'false') + '">AM</div>' +
                    '<div class="m-tp-item m-tp-ampm-item' + (pmSel ? ' m-tp-selected' : '') +
                        '" data-val="pm" role="option" aria-selected="' + (pmSel ? 'true' : 'false') + '">PM</div>' +
                    '</div>';
            }

            // Footer
            var footerHtml = '<div class="m-tp-footer">';
            if (showNow) {
                footerHtml += '<button type="button" class="m-tp-now-btn">' +
                    '<i class="fas fa-clock" aria-hidden="true"></i> Now</button>';
            }
            footerHtml += '<button type="button" class="m-tp-clear-btn">' +
                '<i class="fas fa-times" aria-hidden="true"></i> Clear</button>';
            footerHtml += '</div>';

            panel.innerHTML =
                '<div class="m-tp-columns">' +
                    hoursHtml +
                    '<div class="m-tp-sep" aria-hidden="true">:</div>' +
                    minsHtml +
                    ampmHtml +
                '</div>' +
                footerHtml;

            bindPanelEvents();
            scrollToSelected(panel.querySelector('.m-tp-hours'));
            scrollToSelected(panel.querySelector('.m-tp-minutes'));
        }

        function scrollToSelected(col) {
            if (!col) { return; }
            var sel = col.querySelector('.m-tp-selected');
            if (sel) {
                col.scrollTop = sel.offsetTop - (col.clientHeight / 2) + (sel.clientHeight / 2);
            }
        }

        function getSelectedHour() {
            var item = panel.querySelector('.m-tp-hours .m-tp-selected');
            return item ? parseInt(item.getAttribute('data-val'), 10) : -1;
        }

        function getSelectedMinute() {
            var item = panel.querySelector('.m-tp-minutes .m-tp-selected');
            return item ? parseInt(item.getAttribute('data-val'), 10) : -1;
        }

        function getSelectedAmPm() {
            var item = panel.querySelector('.m-tp-ampm .m-tp-selected');
            return item ? item.getAttribute('data-val') : null;
        }

        function commitSelection() {
            var h   = getSelectedHour();
            var min = getSelectedMinute();
            if (h < 0 || min < 0) { return; }

            var h24 = h;
            if (use12h) {
                var ampm = getSelectedAmPm();
                if (ampm === 'am') {
                    h24 = (h === 12) ? 0 : h;
                } else {
                    h24 = (h === 12) ? 12 : h + 12;
                }
            }

            setValue(applyFormat(h24, min));
        }

        function bindPanelEvents() {
            // Column item clicks
            var items = panel.querySelectorAll('.m-tp-item');
            for (var i = 0; i < items.length; i++) {
                (function(item) {
                    item.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var col = item.parentElement;
                        // Deselect siblings
                        var siblings = col.querySelectorAll('.m-tp-item');
                        for (var j = 0; j < siblings.length; j++) {
                            siblings[j].classList.remove('m-tp-selected');
                            siblings[j].setAttribute('aria-selected', 'false');
                        }
                        item.classList.add('m-tp-selected');
                        item.setAttribute('aria-selected', 'true');
                        commitSelection();
                    });
                })(items[i]);
            }

            // Now button
            var nowBtn = panel.querySelector('.m-tp-now-btn');
            if (nowBtn) {
                nowBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var now     = new Date();
                    var h       = now.getHours();
                    var snapped = snapMinute(now.getMinutes());
                    setValue(applyFormat(h, snapped));
                    closePanel();
                });
            }

            // Clear button
            var clearBtn = panel.querySelector('.m-tp-clear-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    setValue('');
                    closePanel();
                });
            }
        }

        // ---------------------------------------------------------------
        // Value management
        // ---------------------------------------------------------------

        function setValue(val) {
            input.value = val;
            updateDisplay();
            utils.trigger(input, 'change', { value: val });
            utils.trigger(input, 'm:timepicker:change', { value: val });
        }

        // ---------------------------------------------------------------
        // Panel open / close
        // ---------------------------------------------------------------

        function openPanel() {
            if (trigger.classList.contains('m-disabled')) { return; }

            buildPanel();
            panel.style.display = 'block';
            trigger.setAttribute('aria-expanded', 'true');

            // Viewport positioning — identical logic to datepicker
            wrapper.classList.remove('m-open-up', 'm-align-right');
            var trigRect  = trigger.getBoundingClientRect();
            var panelRect = panel.getBoundingClientRect();
            var vw = window.innerWidth  || document.documentElement.clientWidth;
            var vh = window.innerHeight || document.documentElement.clientHeight;

            if ((vh - trigRect.bottom) < panelRect.height && trigRect.top > (vh - trigRect.bottom)) {
                wrapper.classList.add('m-open-up');
            }
            if ((trigRect.left + panelRect.width) > vw) {
                wrapper.classList.add('m-align-right');
            }
        }

        function closePanel() {
            if (panel.style.display === 'none') { return; }
            panel.style.display = 'none';
            trigger.setAttribute('aria-expanded', 'false');
        }

        function isOpen() {
            return panel.style.display !== 'none';
        }

        // ---------------------------------------------------------------
        // Event wiring
        // ---------------------------------------------------------------

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isOpen()) { closePanel(); } else { openPanel(); }
        });

        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (isOpen()) { closePanel(); } else { openPanel(); }
            } else if (e.key === 'Escape') {
                closePanel();
                trigger.focus();
            }
        });

        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                closePanel();
            }
        });

        // ---------------------------------------------------------------
        // Initialise
        // ---------------------------------------------------------------

        updateDisplay();

        // ---------------------------------------------------------------
        // Public API
        // ---------------------------------------------------------------

        var api = {
            /**
             * Get or set the value.
             * @param {string} [val] - Value string in any supported format, or '' to clear.
             * @returns {string|api} Current formatted value (getter) or api (setter, chainable).
             */
            value: function(val) {
                if (val === undefined) {
                    return input.value || '';
                }
                setValue(String(val));
                return this;
            },

            /** Enable the control. */
            enable: function() {
                input.removeAttribute('disabled');
                trigger.classList.remove('m-disabled');
                return this;
            },

            /** Disable the control and close the panel. */
            disable: function() {
                input.setAttribute('disabled', 'disabled');
                trigger.classList.add('m-disabled');
                closePanel();
                return this;
            },

            /** Clear the selected time. */
            clear: function() {
                setValue('');
                return this;
            }
        };

        input._mTimepicker = api;
        return api;
    };

})(window);
