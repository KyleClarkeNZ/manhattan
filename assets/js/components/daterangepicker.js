/**
 * Manhattan UI Framework - DateRangePicker Module
 *
 * Dual-calendar date-range selector.
 * Reuses calendar rendering helpers shared conceptually with DatePicker.
 *
 * Emits:
 *   m:daterangepicker:change  — { start, end } when both dates confirmed
 *   m:daterangepicker:start   — { start }       when start date selected
 *   m:daterangepicker:clear   — {}              when cleared
 */
(function(window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before daterangepicker module');
        return;
    }

    const utils = m.utils;

    // ── Month/day helpers ────────────────────────────────────────────────────

    const MONTH_NAMES = [
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    function monthName(idx) { return MONTH_NAMES[idx]; }

    function makeDate(year, month, day) {
        return new Date(year, month, day);
    }

    /** Midnight-normalised copy of d so comparisons only look at Y-m-d. */
    function dateOnly(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
    }

    function isSameDay(a, b) {
        return a && b &&
            a.getFullYear() === b.getFullYear() &&
            a.getMonth()    === b.getMonth()    &&
            a.getDate()     === b.getDate();
    }

    function isBefore(a, b) { return dateOnly(a) < dateOnly(b); }
    function isAfter(a, b)  { return dateOnly(a) > dateOnly(b); }

    // ── Default presets factory ──────────────────────────────────────────────

    function buildDefaultPresets(format) {
        var t = new Date();
        var tod = dateOnly(t);

        function fmt(d) { return utils.formatDate(d, format); }

        var startOfMonth = makeDate(tod.getFullYear(), tod.getMonth(), 1);
        var endOfMonth   = makeDate(tod.getFullYear(), tod.getMonth() + 1, 0);

        var lastMonthStart = makeDate(tod.getFullYear(), tod.getMonth() - 1, 1);
        var lastMonthEnd   = makeDate(tod.getFullYear(), tod.getMonth(), 0);

        var startOfYear    = makeDate(tod.getFullYear(), 0, 1);
        var endOfYear      = makeDate(tod.getFullYear(), 11, 31);

        var yesterday = new Date(tod); yesterday.setDate(tod.getDate() - 1);
        var last7     = new Date(tod); last7.setDate(tod.getDate() - 6);
        var last30    = new Date(tod); last30.setDate(tod.getDate() - 29);
        var last90    = new Date(tod); last90.setDate(tod.getDate() - 89);

        return [
            { label: 'Today',        start: fmt(tod),           end: fmt(tod)         },
            { label: 'Yesterday',    start: fmt(yesterday),     end: fmt(yesterday)   },
            { label: 'Last 7 days',  start: fmt(last7),         end: fmt(tod)         },
            { label: 'Last 30 days', start: fmt(last30),        end: fmt(tod)         },
            { label: 'Last 90 days', start: fmt(last90),        end: fmt(tod)         },
            { label: 'This month',   start: fmt(startOfMonth),  end: fmt(endOfMonth)  },
            { label: 'Last month',   start: fmt(lastMonthStart),end: fmt(lastMonthEnd)},
            { label: 'This year',    start: fmt(startOfYear),   end: fmt(endOfYear)   },
        ];
    }

    // ── Main factory ─────────────────────────────────────────────────────────

    m.daterangepicker = function(id, options) {
        var wrapper = utils.getElement(id);
        if (!wrapper) {
            console.warn('Manhattan: DateRangePicker element not found:', id);
            return null;
        }

        // Read config from data attributes
        var format          = wrapper.getAttribute('data-format')        || 'Y-m-d';
        var minAttr         = wrapper.getAttribute('data-min')           || null;
        var maxAttr         = wrapper.getAttribute('data-max')           || null;
        var startPh         = wrapper.getAttribute('data-start-ph')      || 'Start date';
        var endPh           = wrapper.getAttribute('data-end-ph')        || 'End date';
        var combinedPh      = wrapper.getAttribute('data-placeholder')   || null;
        var highlightToday  = wrapper.getAttribute('data-highlight-today') !== 'false';
        var showPresets     = wrapper.getAttribute('data-show-presets')  === 'true';
        var weekStart       = parseInt(wrapper.getAttribute('data-week-start') || '0', 10);
        var singleMonth     = wrapper.getAttribute('data-single-month')  === 'true';
        var autoApply       = wrapper.getAttribute('data-auto-apply')    === 'true';
        var isDisabled      = wrapper.getAttribute('data-disabled')      === 'true';

        var customPresetsAttr = wrapper.getAttribute('data-presets');
        var customPresets = null;
        if (customPresetsAttr) {
            try { customPresets = JSON.parse(customPresetsAttr); } catch(e) {}
        }

        options = utils.extend({
            format:         format,
            min:            minAttr,
            max:            maxAttr,
            startPh:        startPh,
            endPh:          endPh,
            combinedPh:     combinedPh,
            highlightToday: highlightToday,
            showPresets:    showPresets,
            weekStart:      weekStart,
            singleMonth:    singleMonth,
            autoApply:      autoApply,
        }, options || {});

        // ── Hidden inputs ─────────────────────────────────────────────────
        var startInput = wrapper.querySelector('.m-drp-start');
        var endInput   = wrapper.querySelector('.m-drp-end');

        // ── State ─────────────────────────────────────────────────────────
        var startDate      = startInput && startInput.value ? utils.parseDate(startInput.value, options.format) : null;
        var endDate        = endInput   && endInput.value   ? utils.parseDate(endInput.value,   options.format) : null;
        var confirmedStart = startDate; // saved when panel opens; restored on cancel
        var confirmedEnd   = endDate;
        var hoverDate      = null;   // date user is hovering (range preview)
        var selecting      = false;  // true after start clicked, waiting for end
        var isOpen         = false;

        // ── Left calendar month (right = leftYear/leftMonth + 1) ─────────
        var today        = new Date();
        var leftMonth    = startDate ? startDate.getMonth()    : today.getMonth();
        var leftYear     = startDate ? startDate.getFullYear() : today.getFullYear();

        // Ensure left < right when pre-populated
        if (endDate && !singleMonth) {
            var rightM = leftMonth + 1;
            var rightY = leftYear;
            if (rightM > 11) { rightM = 0; rightY++; }
            // If both dates are in the same month advance left back one
            if (endDate.getFullYear() === leftYear && endDate.getMonth() === leftMonth) {
                leftMonth--;
                if (leftMonth < 0) { leftMonth = 11; leftYear--; }
            }
        }

        // ── DOM construction ──────────────────────────────────────────────
        wrapper.classList.add('m-daterangepicker-wrapper');

        // Trigger button
        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.id   = wrapper.id + '_trigger';
        trigger.className = 'm-drp-trigger' + (isDisabled ? ' m-disabled' : '');
        if (isDisabled) { trigger.disabled = true; }

        wrapper.insertBefore(trigger, wrapper.firstChild);

        // Dropdown panel
        var panel = document.createElement('div');
        panel.className = 'm-drp-panel';
        panel.style.display = 'none';
        wrapper.appendChild(panel);

        // ── Trigger rendering ─────────────────────────────────────────────

        function renderTrigger() {
            var sVal = startInput ? startInput.value : '';
            var eVal = endInput   ? endInput.value   : '';
            var labelHtml;

            if (sVal && eVal) {
                if (sVal === eVal) {
                    labelHtml = '<span class="m-drp-label m-drp-has-value">' +
                        '<span class="m-drp-segment">' + escapeHtml(sVal) + '</span>' +
                    '</span>';
                } else {
                    labelHtml = '<span class="m-drp-label m-drp-has-value">' +
                        '<span class="m-drp-segment">' + escapeHtml(sVal) + '</span>' +
                        '<span class="m-drp-arrow"><i class="fas fa-arrow-right" aria-hidden="true"></i></span>' +
                        '<span class="m-drp-segment">' + escapeHtml(eVal) + '</span>' +
                    '</span>';
                }
            } else if (sVal) {
                labelHtml = '<span class="m-drp-label m-drp-has-value">' +
                    '<span class="m-drp-segment">' + escapeHtml(sVal) + '</span>' +
                    '<span class="m-drp-arrow"><i class="fas fa-arrow-right" aria-hidden="true"></i></span>' +
                    '<span class="m-drp-segment m-drp-ph">' + escapeHtml(options.endPh) + '</span>' +
                '</span>';
            } else if (options.combinedPh) {
                labelHtml = '<span class="m-drp-label">' + escapeHtml(options.combinedPh) + '</span>';
            } else {
                labelHtml = '<span class="m-drp-label">' +
                    '<span class="m-drp-segment m-drp-ph">' + escapeHtml(options.startPh) + '</span>' +
                    '<span class="m-drp-arrow"><i class="fas fa-arrow-right" aria-hidden="true"></i></span>' +
                    '<span class="m-drp-segment m-drp-ph">' + escapeHtml(options.endPh) + '</span>' +
                '</span>';
            }

            // Icon is absolutely positioned on the right (mirrors .m-datepicker-icon)
            trigger.innerHTML = labelHtml + '<i class="fas fa-calendar-week m-drp-icon" aria-hidden="true"></i>';
        }

        // ── Panel rendering ───────────────────────────────────────────────

        function renderPanel() {
            var html = '<div class="m-drp-panel-inner"><div class="m-drp-panel-row">';

            // Presets sidebar
            if (options.showPresets) {
                var presets = customPresets || buildDefaultPresets(options.format);
                html += '<div class="m-drp-presets">';
                for (var i = 0; i < presets.length; i++) {
                    var p = presets[i];
                    var active = startInput && endInput &&
                                 startInput.value === p.start && endInput.value === p.end;
                    html += '<button type="button" class="m-drp-preset' + (active ? ' m-drp-preset-active' : '') + '"' +
                            ' data-start="' + escapeAttr(p.start) + '" data-end="' + escapeAttr(p.end) + '">' +
                            escapeHtml(p.label) + '</button>';
                }
                html += '</div>';
            }

            // Calendars area
            html += '<div class="m-drp-calendars' + (options.singleMonth ? ' m-drp-single' : '') + '">';

            // Left calendar
            html += renderCalendarHtml(leftYear, leftMonth);

            // Right calendar (unless singleMonth)
            if (!options.singleMonth) {
                var rMonth = leftMonth + 1;
                var rYear  = leftYear;
                if (rMonth > 11) { rMonth = 0; rYear++; }
                html += renderCalendarHtml(rYear, rMonth);
            }

            html += '</div>'; // .m-drp-calendars

            html += '</div>'; // .m-drp-panel-row

            // Footer
            html += '<div class="m-drp-footer">';
            // Clear button (always)
            html += '<button type="button" class="m-drp-clear"><i class="fas fa-times" aria-hidden="true"></i> Clear</button>';

            if (!options.autoApply) {
                html += '<div class="m-drp-footer-right">';
                html += '<button type="button" class="m-drp-cancel">Cancel</button>';
                html += '<button type="button" class="m-drp-apply m-button m-button-primary">' +
                        '<i class="fas fa-check" aria-hidden="true"></i> Apply</button>';
                html += '</div>';
            }
            html += '</div>'; // .m-drp-footer

            html += '</div>'; // .m-drp-panel-inner
            panel.innerHTML = html;

            bindPanelEvents();
        }

        // ── Calendar HTML for one month ───────────────────────────────────

        function renderCalendarHtml(year, month) {
            var isPrevDisabled = false;
            var isNextDisabled = false;

            // Determine if this is the left or right calendar by checking
            // if it equals the right calendar month (disallow nav that would
            // make left >= right).
            var isLeftCal = (year === leftYear && month === leftMonth);
            if (!options.singleMonth) {
                var rM = leftMonth + 1, rY = leftYear;
                if (rM > 11) { rM = 0; rY++; }
                var isRightCal = (year === rY && month === rM);

                if (isLeftCal) {
                    // Left: can't advance past month before right
                    var nextM = month + 1 > 11 ? 0 : month + 1;
                    var nextY = month + 1 > 11 ? year + 1 : year;
                    isPrevDisabled = false;
                    isNextDisabled = (nextY === rY && nextM === rM);
                }
                if (isRightCal) {
                    // Right: can't go back past month after left
                    var prevM = month - 1 < 0 ? 11 : month - 1;
                    var prevY = month - 1 < 0 ? year - 1 : year;
                    isPrevDisabled = (prevY === leftYear && prevM === leftMonth);
                    isNextDisabled = false;
                }
            }

            var side = isLeftCal ? 'left' : 'right';

            // In dual-calendar mode the inner nav buttons are always disabled
            // (months are locked one apart). Hide them with a spacer so the
            // title stays centred, but don't render a useless disabled button.
            var hideNext = !options.singleMonth && isLeftCal;
            var hidePrev = !options.singleMonth && !isLeftCal;

            var html  = '<div class="m-drp-calendar" data-side="' + side + '">';
            html += '<div class="m-calendar-header">';

            if (hidePrev) {
                html += '<span class="m-cal-btn-spacer" aria-hidden="true"></span>';
            } else {
                html += '<button type="button" class="m-cal-btn m-cal-prev" data-side="' + side + '" data-year="' + year + '" data-month="' + month + '"' +
                        (isPrevDisabled ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';
            }

            html += '<span class="m-cal-title">' + monthName(month) + ' ' + year + '</span>';

            if (hideNext) {
                html += '<span class="m-cal-btn-spacer" aria-hidden="true"></span>';
            } else {
                html += '<button type="button" class="m-cal-btn m-cal-next" data-side="' + side + '" data-year="' + year + '" data-month="' + month + '"' +
                        (isNextDisabled ? ' disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';
            }

            html += '</div>';

            html += '<div class="m-calendar-body">';
            html += '<div class="m-cal-weekdays">';
            var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            for (var d = 0; d < 7; d++) {
                html += '<div class="m-cal-weekday">' + days[(d + options.weekStart) % 7] + '</div>';
            }
            html += '</div>';

            html += '<div class="m-cal-days">';

            var firstDayRaw = new Date(year, month, 1).getDay();
            var firstDay    = (firstDayRaw - options.weekStart + 7) % 7;
            var daysInMonth = new Date(year, month + 1, 0).getDate();
            var prevMonthDays = new Date(year, month, 0).getDate();

            // Previous-month filler days
            for (var f = firstDay - 1; f >= 0; f--) {
                html += '<div class="m-cal-day m-cal-other-month">' + (prevMonthDays - f) + '</div>';
            }

            // Current month
            for (var day = 1; day <= daysInMonth; day++) {
                var date    = makeDate(year, month, day);
                var dateStr = utils.formatDate(date, options.format);
                var classes = ['m-cal-day'];

                var isStart = startDate && isSameDay(date, startDate);
                var isEnd   = endDate   && isSameDay(date, endDate);

                // In-range highlight (confirmed or hover preview)
                var rangeEnd = endDate || (selecting && hoverDate ? hoverDate : null);
                var inRange  = false;
                if (startDate && rangeEnd) {
                    var rangeMin = isBefore(startDate, rangeEnd) ? startDate : rangeEnd;
                    var rangeMax = isBefore(startDate, rangeEnd) ? rangeEnd  : startDate;
                    inRange = isAfter(date, rangeMin) && isBefore(date, rangeMax);
                }

                if (isStart && isEnd)                              { classes.push('m-cal-range-start', 'm-cal-range-end', 'm-cal-selected'); }
                else if (isStart)                                  { classes.push('m-cal-range-start', 'm-cal-selected'); }
                else if (isEnd)                                    { classes.push('m-cal-range-end', 'm-cal-selected'); }
                else if (inRange)                                  { classes.push('m-cal-in-range'); }

                if (options.highlightToday && isSameDay(date, today)) { classes.push('m-cal-today'); }

                var minDate = options.min ? utils.parseDate(options.min, options.format) : null;
                var maxDate = options.max ? utils.parseDate(options.max, options.format) : null;
                if ((minDate && isBefore(date, minDate)) || (maxDate && isAfter(date, maxDate))) {
                    classes.push('m-cal-disabled');
                }

                html += '<div class="' + classes.join(' ') + '" data-date="' + dateStr + '">' + day + '</div>';
            }

            // Next-month filler
            var totalCells = firstDay + daysInMonth;
            var rem = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
            for (var n = 1; n <= rem; n++) {
                html += '<div class="m-cal-day m-cal-other-month">' + n + '</div>';
            }

            html += '</div></div>'; // .m-cal-days .m-calendar-body
            html += '</div>'; // .m-drp-calendar
            return html;
        }

        // ── In-place range class update (no DOM rebuild) ─────────────────
        // Called on hover/selection changes to avoid detaching e.target
        // which would break the document-level outside-click handler.

        function updateRangeClasses() {
            var days    = panel.querySelectorAll('.m-cal-day[data-date]');
            var rangeEnd = endDate || (selecting && hoverDate ? hoverDate : null);
            var rangeMin = null, rangeMax = null;
            if (startDate && rangeEnd) {
                rangeMin = isBefore(startDate, rangeEnd) ? startDate : rangeEnd;
                rangeMax = isBefore(startDate, rangeEnd) ? rangeEnd  : startDate;
            }
            for (var i = 0; i < days.length; i++) {
                var el = days[i];
                if (el.classList.contains('m-cal-disabled')) { continue; }
                var dateStr = el.getAttribute('data-date');
                var date    = utils.parseDate(dateStr, options.format);

                el.classList.remove('m-cal-range-start', 'm-cal-range-end', 'm-cal-in-range', 'm-cal-selected');

                var isStart = startDate && isSameDay(date, startDate);
                var isEnd   = rangeEnd  && isSameDay(date, rangeEnd);
                var inRange = rangeMin  && rangeMax && isAfter(date, rangeMin) && isBefore(date, rangeMax);

                if      (isStart && isEnd) { el.classList.add('m-cal-range-start', 'm-cal-range-end', 'm-cal-selected'); }
                else if (isStart)          { el.classList.add('m-cal-range-start', 'm-cal-selected'); }
                else if (isEnd)            { el.classList.add('m-cal-range-end',   'm-cal-selected'); }
                else if (inRange)          { el.classList.add('m-cal-in-range'); }
            }
        }

        // ── Panel event binding ───────────────────────────────────────────

        function bindPanelEvents() {

            // CRITICAL: stop all panel clicks from bubbling to the document-level
            // close handler. Without this, renderPanel() detaches e.target which
            // makes wrapper.contains(e.target) return false, closing the panel
            // immediately after every day click.
            panel.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // ── Preset clicks ─────────────────────────────────────────
            var presetBtns = panel.querySelectorAll('.m-drp-preset');
            for (var i = 0; i < presetBtns.length; i++) {
                presetBtns[i].addEventListener('click', function() {
                    var s = this.getAttribute('data-start');
                    var e = this.getAttribute('data-end');
                    applyRange(s, e);
                    hoverDate = null;
                    selecting = false;
                    if (options.autoApply) {
                        utils.trigger(wrapper, 'm:daterangepicker:change', { start: s, end: e });
                        closePanel(true);
                    } else {
                        renderPanel(); // re-render to highlight active preset
                    }
                });
            }

            // ── Nav buttons ───────────────────────────────────────────
            var navBtns = panel.querySelectorAll('.m-cal-prev, .m-cal-next');
            for (var j = 0; j < navBtns.length; j++) {
                navBtns[j].addEventListener('click', function(evt) {
                    evt.preventDefault();
                    if (this.disabled) { return; }

                    var side  = this.getAttribute('data-side');
                    var year  = parseInt(this.getAttribute('data-year'),  10);
                    var month = parseInt(this.getAttribute('data-month'), 10);
                    var dir   = this.classList.contains('m-cal-prev') ? -1 : 1;

                    if (side === 'left' || options.singleMonth) {
                        leftMonth += dir;
                        if (leftMonth > 11) { leftMonth = 0;  leftYear++;  }
                        if (leftMonth < 0)  { leftMonth = 11; leftYear--;  }
                    } else {
                        var newRightM = month + dir;
                        var newRightY = year;
                        if (newRightM > 11) { newRightM = 0;  newRightY++; }
                        if (newRightM < 0)  { newRightM = 11; newRightY--; }
                        leftMonth = newRightM - 1;
                        leftYear  = newRightY;
                        if (leftMonth < 0) { leftMonth = 11; leftYear--; }
                    }
                    renderPanel();
                });
            }

            // ── Day interaction via event delegation ──────────────────
            // Using delegation on .m-drp-calendars avoids attaching/removing
            // dozens of individual listeners and survives re-renders.

            var calendarsEl = panel.querySelector('.m-drp-calendars');
            var isDragging    = false;
            var dragStartEl   = null; // { str, date }

            function dayFromTarget(target) {
                var el = target;
                while (el && el !== calendarsEl) {
                    if (el.classList && el.classList.contains('m-cal-day')) {
                        if (!el.classList.contains('m-cal-disabled') &&
                            !el.classList.contains('m-cal-other-month') &&
                            el.hasAttribute('data-date')) {
                            return el;
                        }
                        return null; // hit a disabled/other-month day
                    }
                    el = el.parentElement;
                }
                return null;
            }

            // mousedown — record drag start, prevent text selection
            calendarsEl.addEventListener('mousedown', function(e) {
                var day = dayFromTarget(e.target);
                if (!day) { return; }
                isDragging  = false;
                dragStartEl = { str: day.getAttribute('data-date'), date: utils.parseDate(day.getAttribute('data-date'), options.format) };
                e.preventDefault();
            });

            // mousemove — activate drag once mouse actually moves to a different day
            calendarsEl.addEventListener('mousemove', function(e) {
                if (!dragStartEl) { return; }
                var day = dayFromTarget(e.target);
                if (!day) { return; }
                var dStr  = day.getAttribute('data-date');
                var dDate = utils.parseDate(dStr, options.format);
                if (!isDragging && isSameDay(dDate, dragStartEl.date)) { return; } // still on start day
                isDragging = true;

                // Show live drag preview
                if (isBefore(dDate, dragStartEl.date)) {
                    startDate = dDate;
                    hoverDate = dragStartEl.date;
                } else {
                    startDate = dragStartEl.date;
                    hoverDate = dDate;
                }
                endDate   = null;
                selecting = true;
                updateRangeClasses();
            });

            // mouseup — finalise a drag
            calendarsEl.addEventListener('mouseup', function(e) {
                if (!dragStartEl || !isDragging) {
                    dragStartEl = null;
                    isDragging  = false;
                    return; // single click — let the click handler deal with it
                }
                var dragStart = dragStartEl;
                dragStartEl   = null;
                isDragging    = false;

                var day = dayFromTarget(e.target);
                if (!day) {
                    hoverDate = null;
                    updateRangeClasses();
                    return;
                }

                var dDate = utils.parseDate(day.getAttribute('data-date'), options.format);
                if (isBefore(dDate, dragStart.date)) {
                    startDate = dDate;
                    endDate   = dragStart.date;
                } else {
                    startDate = dragStart.date;
                    endDate   = dDate;
                }
                selecting = false;
                hoverDate = null;
                var sStr  = utils.formatDate(startDate, options.format);
                var eStr  = utils.formatDate(endDate,   options.format);
                setHiddenInputs(sStr, eStr);

                if (options.autoApply) {
                    utils.trigger(wrapper, 'm:daterangepicker:change', { start: sStr, end: eStr });
                    closePanel(true);
                } else {
                    updateRangeClasses();
                }
            });

            // click — two-click selection (only when no drag occurred)
            calendarsEl.addEventListener('click', function(e) {
                if (isDragging) { return; } // drag finalised by mouseup
                var day = dayFromTarget(e.target);
                if (!day) { return; }
                var dateStr = day.getAttribute('data-date');
                var clicked = utils.parseDate(dateStr, options.format);

                if (!selecting || !startDate) {
                    // First click — set start, wait for end
                    startDate = clicked;
                    endDate   = null;
                    hoverDate = null;
                    selecting = true;
                    setHiddenInputs(dateStr, '');
                    utils.trigger(wrapper, 'm:daterangepicker:start', { start: dateStr });
                    updateRangeClasses();
                } else {
                    // Second click — set end (or restart if clicked before start)
                    if (isBefore(clicked, startDate)) {
                        startDate = clicked;
                        endDate   = null;
                        hoverDate = null;
                        selecting = true;
                        setHiddenInputs(dateStr, '');
                        utils.trigger(wrapper, 'm:daterangepicker:start', { start: dateStr });
                        updateRangeClasses();
                    } else {
                        endDate   = clicked;
                        selecting = false;
                        hoverDate = null;
                        var sStr  = utils.formatDate(startDate, options.format);
                        var eStr  = utils.formatDate(endDate,   options.format);
                        setHiddenInputs(sStr, eStr);

                        if (options.autoApply) {
                            utils.trigger(wrapper, 'm:daterangepicker:change', { start: sStr, end: eStr });
                            closePanel(true);
                            return;
                        }
                        updateRangeClasses();
                    }
                }
            });

            // mouseover — range preview during two-click selection (in-place, no re-render)
            calendarsEl.addEventListener('mouseover', function(e) {
                if (!selecting || !startDate) { return; }
                var day = dayFromTarget(e.target);
                if (!day) { return; }
                var newHover = utils.parseDate(day.getAttribute('data-date'), options.format);
                if (hoverDate && isSameDay(newHover, hoverDate)) { return; } // unchanged
                hoverDate = newHover;
                updateRangeClasses();
            });

            calendarsEl.addEventListener('mouseleave', function() {
                if (!selecting) { return; }
                hoverDate = null;
                updateRangeClasses();
            });

            // ── Footer buttons ────────────────────────────────────────
            var clearBtn = panel.querySelector('.m-drp-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    clearRange();
                    confirmedStart = null;
                    confirmedEnd   = null;
                    closePanel(true); // committed=true so the cleared state is kept
                });
            }

            var cancelBtn = panel.querySelector('.m-drp-cancel');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    closePanel(false);
                });
            }

            var applyBtn = panel.querySelector('.m-drp-apply');
            if (applyBtn) {
                applyBtn.addEventListener('click', function() {
                    var sStr = startInput ? startInput.value : '';
                    var eStr = endInput   ? endInput.value   : '';
                    utils.trigger(wrapper, 'm:daterangepicker:change', { start: sStr, end: eStr });
                    closePanel(true);
                });
            }
        }

        // ── Open / close ──────────────────────────────────────────────────

        function openPanel() {
            if (isOpen) { return; }
            // Save confirmed state so cancel can revert cleanly
            confirmedStart = startDate;
            confirmedEnd   = endDate;
            isOpen    = true;
            selecting = false;
            hoverDate = null;
            renderPanel();
            panel.style.display = '';
            wrapper.classList.add('m-drp-open');
            positionPanel();
        }

        function closePanel(committed) {
            if (!isOpen) { return; }
            isOpen    = false;
            selecting = false;
            hoverDate = null;
            panel.style.display = 'none';
            wrapper.classList.remove('m-drp-open');
            if (!committed) {
                // Revert to the state that existed when the panel was opened
                startDate = confirmedStart;
                endDate   = confirmedEnd;
                setHiddenInputs(
                    startDate ? utils.formatDate(startDate, options.format) : '',
                    endDate   ? utils.formatDate(endDate,   options.format) : ''
                );
            } else {
                // Committed — update confirmed state
                confirmedStart = startDate;
                confirmedEnd   = endDate;
            }
            renderTrigger();
        }

        function positionPanel() {
            wrapper.classList.remove('m-drp-open-up', 'm-drp-align-right');
            var triggerRect = trigger.getBoundingClientRect();
            var panelRect   = panel.getBoundingClientRect();
            var vh = window.innerHeight || document.documentElement.clientHeight;
            var vw = window.innerWidth  || document.documentElement.clientWidth;

            var spaceBelow = vh - triggerRect.bottom;
            var spaceAbove = triggerRect.top;
            if (spaceBelow < panelRect.height && spaceAbove > spaceBelow) {
                wrapper.classList.add('m-drp-open-up');
            }

            var spaceRight = vw - triggerRect.left;
            if (spaceRight < panelRect.width) {
                wrapper.classList.add('m-drp-align-right');
            }
        }

        // ── Helpers ───────────────────────────────────────────────────────

        function setHiddenInputs(startVal, endVal) {
            if (startInput) { startInput.value = startVal; }
            if (endInput)   { endInput.value   = endVal;   }
        }

        function applyRange(startStr, endStr) {
            setHiddenInputs(startStr, endStr);
            startDate = startStr ? utils.parseDate(startStr, options.format) : null;
            endDate   = endStr   ? utils.parseDate(endStr,   options.format) : null;
            selecting = false;
        }

        function clearRange() {
            setHiddenInputs('', '');
            startDate = null;
            endDate   = null;
            selecting = false;
            hoverDate = null;
            utils.trigger(wrapper, 'm:daterangepicker:clear', {});
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeAttr(str) {
            return String(str).replace(/"/g, '&quot;');
        }

        // ── Wire trigger ──────────────────────────────────────────────────

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            if (trigger.disabled) { return; }
            if (isOpen) {
                closePanel(false);
            } else {
                openPanel();
            }
        });

        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (isOpen) { closePanel(false); } else { openPanel(); }
            } else if (e.key === 'Escape') {
                closePanel(false);
            }
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (isOpen && !wrapper.contains(e.target)) {
                closePanel(false);
            }
        });

        // ── Initial render ────────────────────────────────────────────────
        renderTrigger();

        // ── Public API ────────────────────────────────────────────────────
        return {
            /**
             * Get or set the selected range.
             * Getter: returns { start: string, end: string }
             * Setter: accepts { start: string, end: string }
             */
            value: function(val) {
                if (val === undefined) {
                    return {
                        start: startInput ? startInput.value : '',
                        end:   endInput   ? endInput.value   : '',
                    };
                }
                var s = (val && val.start) ? String(val.start) : '';
                var e = (val && val.end)   ? String(val.end)   : '';
                applyRange(s, e);
                renderTrigger();
                return this;
            },

            /** Get or set the start date string only. */
            start: function(val) {
                if (val === undefined) { return startInput ? startInput.value : ''; }
                var e = endInput ? endInput.value : '';
                applyRange(String(val), e);
                renderTrigger();
                return this;
            },

            /** Get or set the end date string only. */
            end: function(val) {
                if (val === undefined) { return endInput ? endInput.value : ''; }
                var s = startInput ? startInput.value : '';
                applyRange(s, String(val));
                renderTrigger();
                return this;
            },

            /** Clear both dates. */
            clear: function() {
                clearRange();
                renderTrigger();
                return this;
            },

            /** Update the minimum date constraint. */
            min: function(val) {
                options.min = val;
                return this;
            },

            /** Update the maximum date constraint. */
            max: function(val) {
                options.max = val;
                return this;
            },

            enable: function() {
                trigger.disabled = false;
                trigger.classList.remove('m-disabled');
                wrapper.classList.remove('m-disabled');
                isDisabled = false;
                return this;
            },

            disable: function() {
                trigger.disabled = true;
                trigger.classList.add('m-disabled');
                wrapper.classList.add('m-disabled');
                if (isOpen) { closePanel(false); }
                isDisabled = true;
                return this;
            },

            open:  function() { openPanel();  return this; },
            close: function() { closePanel(false); return this; },
        };
    };

    // ── Auto-initialize ───────────────────────────────────────────────────────
    utils.ready(function() {
        var wrappers = document.querySelectorAll('.m-daterangepicker-wrapper');
        for (var i = 0; i < wrappers.length; i++) {
            var el = wrappers[i];
            if (el.id && !el._mDrp) {
                el._mDrp = m.daterangepicker(el.id);
            }
        }
    });

})(window);
