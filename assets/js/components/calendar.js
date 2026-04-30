/**
 * Manhattan UI — Calendar Component
 *
 * Renders a month or week view calendar with event chips, selectable dates,
 * event-detail popovers, week numbers, and full JS navigation API.
 *
 * Auto-initialises every .m-cal-widget element on DOMContentLoaded.
 * Manual init: m.calendar('myId')
 *
 * PHP fluent API:
 *   $m->calendar('id')
 *     ->events([...])           — event data array
 *     ->view('month'|'week')    — initial view (default: 'month')
 *     ->initialDate('YYYY-MM-DD')
 *     ->selectable()            — click dates to select
 *     ->highlightToday()        — filled circle on today (default: on)
 *     ->weekStartsMonday()
 *     ->showWeekNumbers()
 *     ->withPopover()           — event-detail popover on chip click
 *     ->minDate('YYYY-MM-DD')
 *     ->maxDate('YYYY-MM-DD')
 *     ->height('480px')
 *
 * JS API  (returned by m.calendar(id)):
 *   .view('month'|'week')       — switch view
 *   .goTo('YYYY-MM-DD')         — navigate to a date
 *   .today()                    — jump to today
 *   .prev() / .next()           — navigate one period
 *   .setEvents(events)          — replace all events and re-render
 *   .addEvent(event)            — append one event and re-render
 *   .clearEvents()              — remove all events and re-render
 *   .selected()                 — get currently selected date string
 *
 * JS Events (dispatched on the root element):
 *   m:calendar:dateclick  — detail: { date, events }
 *   m:calendar:eventclick — detail: { event, date }
 *   m:calendar:navigate   — detail: { year, month, view }
 */

(function(window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before calendar module');
        return;
    }

    var utils = m.utils;

    // ── Utility helpers ───────────────────────────────────────────────────────

    function padZ(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    function dateToStr(d) {
        return d.getFullYear() + '-' + padZ(d.getMonth() + 1) + '-' + padZ(d.getDate());
    }

    function parseDate(str) {
        if (!str) { return null; }
        var parts = str.split('-');
        if (parts.length !== 3) { return null; }
        var y = parseInt(parts[0], 10);
        var mo = parseInt(parts[1], 10) - 1;
        var d  = parseInt(parts[2], 10);
        if (isNaN(y) || isNaN(mo) || isNaN(d)) { return null; }
        return new Date(y, mo, d);
    }

    function todayDate() {
        var now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), now.getDate());
    }

    /** ISO 8601 week number */
    function isoWeek(d) {
        var tmp = new Date(d.getTime());
        // Move to nearest Thursday (ISO week date rule)
        tmp.setDate(tmp.getDate() - ((tmp.getDay() + 6) % 7) + 3);
        var jan4 = new Date(tmp.getFullYear(), 0, 4);
        return 1 + Math.round((tmp - jan4) / 604800000);
    }

    /** Build a { 'YYYY-MM-DD': [event, ...] } lookup */
    function buildEventMap(events) {
        var map = {};
        for (var i = 0; i < events.length; i++) {
            var ev = events[i];
            if (!ev.date) { continue; }
            if (!map[ev.date]) { map[ev.date] = []; }
            map[ev.date].push(ev);
        }
        return map;
    }

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function friendlyDate(str) {
        var d = parseDate(str);
        if (!d) { return str; }
        var months = ['January','February','March','April','May','June',
                      'July','August','September','October','November','December'];
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
    }

    var MONTH_NAMES = ['January','February','March','April','May','June',
                       'July','August','September','October','November','December'];

    // ── Factory ───────────────────────────────────────────────────────────────

    m.calendar = function(id, overrides) {
        var el = utils.getElement(id);
        if (!el) {
            console.warn('Manhattan Calendar: element not found:', id);
            return null;
        }

        // Read config from PHP-rendered data attributes
        var cfg = {
            view:             el.getAttribute('data-view')               || 'month',
            initialDate:      el.getAttribute('data-initial-date')       || null,
            minDate:          el.getAttribute('data-min-date')           || null,
            maxDate:          el.getAttribute('data-max-date')           || null,
            selectable:       el.getAttribute('data-selectable')         === 'true',
            highlightToday:   el.getAttribute('data-highlight-today')    !== 'false',
            weekStartsMonday: el.getAttribute('data-week-starts-monday') === 'true',
            showWeekNumbers:  el.getAttribute('data-show-week-numbers')  === 'true',
            withPopover:      el.getAttribute('data-popover')            === 'true'
        };

        // Allow JS-side overrides at init time
        if (overrides && typeof overrides === 'object') {
            for (var k in overrides) {
                if (Object.prototype.hasOwnProperty.call(overrides, k)) {
                    cfg[k] = overrides[k];
                }
            }
        }

        // ── State ─────────────────────────────────────────────────────────────

        var events   = (window.ManhattanCalendarData && window.ManhattanCalendarData[id]) || [];
        var eventMap = buildEventMap(events);

        // currentDate tracks the first day of the visible period
        var initD      = cfg.initialDate ? parseDate(cfg.initialDate) : todayDate();
        var currentDate = new Date(initD.getFullYear(), initD.getMonth(), 1);
        var currentView = cfg.view;
        var selectedDate = null;

        // ── Popover element (lazy-created on first use) ───────────────────────

        var popoverEl = null;

        function ensurePopover() {
            if (popoverEl) { return; }
            popoverEl = document.createElement('div');
            popoverEl.className = 'm-cal-popover';
            popoverEl.style.display = 'none';
            document.body.appendChild(popoverEl);

            document.addEventListener('click', function(e) {
                if (!popoverEl) { return; }
                var inside = popoverEl.contains(e.target)
                    || (e.target.closest && e.target.closest('.m-cal-event'));
                if (!inside) { hidePopover(); }
            });
        }

        function showPopover(triggerEl, evData) {
            if (!cfg.withPopover) { return; }
            ensurePopover();

            var color   = evData.color || 'var(--m-primary, #118AB2)';
            var title   = evData.title       || 'Event';
            var desc    = evData.description || '';
            var evType  = evData.type        || '';
            var url     = evData.url         || '';
            var dateStr = evData.date         || '';

            var html = '<div class="m-cal-pop-header" style="border-left-color:' + escHtml(color) + '">'
                + '<div class="m-cal-pop-title">' + escHtml(title) + '</div>'
                + (dateStr ? '<div class="m-cal-pop-date"><i class="fas fa-calendar-alt" aria-hidden="true"></i> ' + escHtml(friendlyDate(dateStr)) + '</div>' : '')
                + '</div>';

            if (desc) {
                html += '<div class="m-cal-pop-body">' + escHtml(desc) + '</div>';
            }

            if (evType) {
                html += '<div class="m-cal-pop-meta">'
                    + '<span class="m-cal-pop-type" style="background:' + escHtml(color) + '22;color:' + escHtml(color) + '">'
                    + escHtml(evType) + '</span></div>';
            }

            if (url) {
                html += '<div class="m-cal-pop-footer">'
                    + '<a href="' + escHtml(url) + '" class="m-cal-pop-link">View details '
                    + '<i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>';
            }

            html += '<button class="m-cal-pop-close" type="button" aria-label="Close">'
                + '<i class="fas fa-times" aria-hidden="true"></i></button>';

            popoverEl.innerHTML = html;
            popoverEl.style.display = 'block';

            positionPopover(triggerEl);

            var closeBtn = popoverEl.querySelector('.m-cal-pop-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    hidePopover();
                });
            }
        }

        function positionPopover(trigger) {
            if (!popoverEl) { return; }
            var rect  = trigger.getBoundingClientRect();
            var popW  = 230;
            var scrollY = window.pageYOffset || document.documentElement.scrollTop;
            var scrollX = window.pageXOffset || document.documentElement.scrollLeft;
            var top  = rect.bottom + scrollY + 6;
            var left = rect.left + scrollX - (popW / 2) + (rect.width / 2);
            var vpW  = document.documentElement.clientWidth;
            if (left + popW > vpW - 8) { left = vpW - popW - 8; }
            if (left < 8) { left = 8; }
            popoverEl.style.top  = top + 'px';
            popoverEl.style.left = left + 'px';
        }

        function hidePopover() {
            if (popoverEl) { popoverEl.style.display = 'none'; }
        }

        // ── Navigation ────────────────────────────────────────────────────────

        function navigate(dir) {
            if (currentView === 'week') {
                // Advance by 7 days
                currentDate = new Date(currentDate.getTime());
                currentDate.setDate(currentDate.getDate() + dir * 7);
            } else {
                // Advance by one month
                var y = currentDate.getFullYear();
                var mo = currentDate.getMonth() + dir;
                if (mo > 11) { mo = 0; y++; }
                if (mo < 0)  { mo = 11; y--; }
                currentDate = new Date(y, mo, 1);
            }
            render();
            utils.trigger(el, 'm:calendar:navigate', {
                year: currentDate.getFullYear(),
                month: currentDate.getMonth() + 1,
                view: currentView
            });
        }

        // ── Header ────────────────────────────────────────────────────────────

        function buildHeader() {
            var header = document.createElement('div');
            header.className = 'm-cal-header';

            // Top row: month/year title (full width, centred)
            var title = document.createElement('div');
            title.className = 'm-cal-title';
            if (currentView === 'week') {
                var ws = getWeekStart(currentDate);
                var we = new Date(ws.getTime());
                we.setDate(we.getDate() + 6);
                var titleText;
                if (ws.getMonth() === we.getMonth()) {
                    titleText = MONTH_NAMES[ws.getMonth()] + ' ' + ws.getDate()
                        + '–' + we.getDate() + ', ' + ws.getFullYear();
                } else {
                    titleText = MONTH_NAMES[ws.getMonth()] + ' ' + ws.getDate()
                        + ' – ' + MONTH_NAMES[we.getMonth()] + ' ' + we.getDate()
                        + ', ' + we.getFullYear();
                }
                title.textContent = titleText;
            } else {
                title.textContent = MONTH_NAMES[currentDate.getMonth()] + ' ' + currentDate.getFullYear();
            }

            // Bottom row: prev/today/next on the left, view switcher on the right
            var navRow = document.createElement('div');
            navRow.className = 'm-cal-nav-row';

            var nav = document.createElement('div');
            nav.className = 'm-cal-nav';

            var btnPrev = document.createElement('button');
            btnPrev.type = 'button';
            btnPrev.className = 'm-cal-nav-btn';
            btnPrev.setAttribute('aria-label', 'Previous');
            btnPrev.innerHTML = '<i class="fas fa-chevron-left" aria-hidden="true"></i>';
            btnPrev.addEventListener('click', function() { navigate(-1); });

            var btnToday = document.createElement('button');
            btnToday.type = 'button';
            btnToday.className = 'm-cal-nav-btn m-cal-today-btn';
            btnToday.textContent = 'Today';
            btnToday.addEventListener('click', function() {
                var t = todayDate();
                currentDate = new Date(t.getFullYear(), t.getMonth(), 1);
                render();
                utils.trigger(el, 'm:calendar:navigate', {
                    year: currentDate.getFullYear(),
                    month: currentDate.getMonth() + 1,
                    view: currentView
                });
            });

            var btnNext = document.createElement('button');
            btnNext.type = 'button';
            btnNext.className = 'm-cal-nav-btn';
            btnNext.setAttribute('aria-label', 'Next');
            btnNext.innerHTML = '<i class="fas fa-chevron-right" aria-hidden="true"></i>';
            btnNext.addEventListener('click', function() { navigate(1); });

            nav.appendChild(btnPrev);
            nav.appendChild(btnToday);
            nav.appendChild(btnNext);

            // View switcher
            var switcher = document.createElement('div');
            switcher.className = 'm-cal-view-switcher';

            ['month', 'week'].forEach(function(v) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'm-cal-view-btn' + (v === currentView ? ' m-active' : '');
                btn.textContent = v.charAt(0).toUpperCase() + v.slice(1);
                btn.addEventListener('click', function() {
                    if (currentView === v) { return; }
                    currentView = v;
                    render();
                });
                switcher.appendChild(btn);
            });

            navRow.appendChild(nav);
            navRow.appendChild(switcher);

            header.appendChild(title);
            header.appendChild(navRow);
            return header;
        }

        // ── Day-name header row ───────────────────────────────────────────────

        function buildDayHeaderRow() {
            var dayNames = cfg.weekStartsMonday
                ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            var row = document.createElement('div');
            row.className = 'm-cal-day-headers' + (cfg.showWeekNumbers ? ' m-cal-has-week-nums' : '');

            if (cfg.showWeekNumbers) {
                var wkHdr = document.createElement('div');
                wkHdr.className = 'm-cal-week-num-hdr';
                wkHdr.textContent = 'Wk';
                row.appendChild(wkHdr);
            }

            for (var i = 0; i < dayNames.length; i++) {
                var dh = document.createElement('div');
                dh.className = 'm-cal-day-hdr';
                dh.textContent = dayNames[i];
                row.appendChild(dh);
            }

            return row;
        }

        // ── Week-start helper ─────────────────────────────────────────────────

        function getWeekStart(fromDate) {
            var dow = fromDate.getDay(); // 0=Sun
            if (cfg.weekStartsMonday) { dow = (dow + 6) % 7; } // 0=Mon
            var ws = new Date(fromDate.getTime());
            ws.setDate(ws.getDate() - dow);
            return ws;
        }

        // ── Month grid ────────────────────────────────────────────────────────

        function buildMonthGrid() {
            var grid = document.createElement('div');
            grid.className = 'm-cal-grid m-cal-grid--month';
            grid.appendChild(buildDayHeaderRow());

            var body = document.createElement('div');
            body.className = 'm-cal-body';

            var firstOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            var todayStr = dateToStr(todayDate());

            // Start from the Monday/Sunday before (or on) the 1st
            var cellDate = getWeekStart(firstOfMonth);
            var activeMonth = currentDate.getMonth();

            for (var row = 0; row < 6; row++) {
                var tr = document.createElement('div');
                tr.className = 'm-cal-row' + (cfg.showWeekNumbers ? ' m-cal-has-week-nums' : '');

                if (cfg.showWeekNumbers) {
                    var wn = document.createElement('div');
                    wn.className = 'm-cal-week-num';
                    wn.textContent = isoWeek(cellDate);
                    tr.appendChild(wn);
                }

                for (var col = 0; col < 7; col++) {
                    tr.appendChild(buildCell(cellDate, todayStr, activeMonth));
                    cellDate = new Date(cellDate.getFullYear(), cellDate.getMonth(), cellDate.getDate() + 1);
                }

                body.appendChild(tr);
            }

            grid.appendChild(body);
            return grid;
        }

        // ── Week grid ─────────────────────────────────────────────────────────

        function buildWeekGrid() {
            var grid = document.createElement('div');
            grid.className = 'm-cal-grid m-cal-grid--week';

            // Day headers include the actual date numbers for week view
            var dayBases = cfg.weekStartsMonday
                ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            var ws = getWeekStart(currentDate);
            var todayStr = dateToStr(todayDate());
            var headerRow = document.createElement('div');
            headerRow.className = 'm-cal-day-headers' + (cfg.showWeekNumbers ? ' m-cal-has-week-nums' : '');

            if (cfg.showWeekNumbers) {
                var wkHdr = document.createElement('div');
                wkHdr.className = 'm-cal-week-num-hdr';
                wkHdr.textContent = 'Wk';
                headerRow.appendChild(wkHdr);
            }

            for (var i = 0; i < 7; i++) {
                var d = new Date(ws.getFullYear(), ws.getMonth(), ws.getDate() + i);
                var isToday = dateToStr(d) === todayStr;
                var dh = document.createElement('div');
                dh.className = 'm-cal-day-hdr' + (isToday ? ' m-cal-day-hdr--today' : '');
                dh.innerHTML = '<span class="m-cal-day-hdr-name">' + escHtml(dayBases[i]) + '</span>'
                    + '<span class="m-cal-day-hdr-num">' + d.getDate() + '</span>';
                headerRow.appendChild(dh);
            }
            grid.appendChild(headerRow);

            var body = document.createElement('div');
            body.className = 'm-cal-body';

            var row = document.createElement('div');
            row.className = 'm-cal-row m-cal-row--week' + (cfg.showWeekNumbers ? ' m-cal-has-week-nums' : '');

            if (cfg.showWeekNumbers) {
                var wn = document.createElement('div');
                wn.className = 'm-cal-week-num';
                wn.textContent = isoWeek(ws);
                row.appendChild(wn);
            }

            var todayStr2 = dateToStr(todayDate());
            for (var j = 0; j < 7; j++) {
                var wd = new Date(ws.getFullYear(), ws.getMonth(), ws.getDate() + j);
                row.appendChild(buildCell(wd, todayStr2, wd.getMonth()));
            }

            body.appendChild(row);
            grid.appendChild(body);
            return grid;
        }

        // ── Day cell ──────────────────────────────────────────────────────────

        function buildCell(date, todayStr, activeMonth) {
            var dateStr  = dateToStr(date);
            var isOther  = date.getMonth() !== activeMonth;
            var isToday  = cfg.highlightToday && dateStr === todayStr;
            var isSelected = selectedDate === dateStr;

            var disabled = false;
            if (cfg.minDate && dateStr < cfg.minDate) { disabled = true; }
            if (cfg.maxDate && dateStr > cfg.maxDate) { disabled = true; }

            var classes = ['m-cal-cell'];
            if (isOther)    { classes.push('m-cal-cell--other-month'); }
            if (isToday)    { classes.push('m-cal-cell--today'); }
            if (isSelected) { classes.push('m-cal-cell--selected'); }
            if (disabled)   { classes.push('m-cal-cell--disabled'); }
            else if (cfg.selectable) { classes.push('m-cal-cell--selectable'); }

            var cell = document.createElement('div');
            cell.className = classes.join(' ');
            cell.setAttribute('data-date', dateStr);

            // Day number badge
            var num = document.createElement('span');
            num.className = 'm-cal-day-num';
            num.textContent = date.getDate();
            cell.appendChild(num);

            // Events
            var dayEvs = eventMap[dateStr] || [];
            if (dayEvs.length > 0) {
                var maxShow = 3;
                var evWrap = document.createElement('div');
                evWrap.className = 'm-cal-events';

                for (var i = 0; i < Math.min(dayEvs.length, maxShow); i++) {
                    evWrap.appendChild(buildEventChip(dayEvs[i], i, dateStr));
                }

                if (dayEvs.length > maxShow) {
                    var more = document.createElement('div');
                    more.className = 'm-cal-event-more';
                    more.textContent = '+' + (dayEvs.length - maxShow) + ' more';
                    evWrap.appendChild(more);
                }

                cell.appendChild(evWrap);
            }

            // Date click handler (selectable dates only)
            if (cfg.selectable && !disabled) {
                cell.addEventListener('click', function(e) {
                    // Ignore clicks on event chips — they have their own handler
                    if (e.target.closest && e.target.closest('.m-cal-event')) { return; }
                    if (e.target.closest && e.target.closest('.m-cal-event-more')) { return; }

                    var prev = el.querySelector('.m-cal-cell--selected');
                    if (prev) { prev.classList.remove('m-cal-cell--selected'); }
                    cell.classList.add('m-cal-cell--selected');
                    selectedDate = dateStr;

                    utils.trigger(el, 'm:calendar:dateclick', {
                        date: dateStr,
                        events: eventMap[dateStr] || []
                    });
                });
            }

            return cell;
        }

        function buildEventChip(ev, idx, dateStr) {
            var chip = document.createElement('div');
            chip.className = 'm-cal-event';
            if (ev.type) { chip.setAttribute('data-event-type', ev.type); }
            if (ev.color) {
                chip.style.setProperty('--m-cal-event-color', ev.color);
            }
            chip.textContent = ev.title || '';
            chip.setAttribute('title', ev.title || '');

            chip.addEventListener('click', function(e) {
                e.stopPropagation();
                utils.trigger(el, 'm:calendar:eventclick', { event: ev, date: dateStr });
                if (cfg.withPopover) {
                    showPopover(chip, ev);
                }
            });

            return chip;
        }

        // ── Full render ───────────────────────────────────────────────────────

        function render() {
            hidePopover();
            el.innerHTML = '';

            el.appendChild(buildHeader());

            if (currentView === 'week') {
                el.appendChild(buildWeekGrid());
            } else {
                el.appendChild(buildMonthGrid());
            }
        }

        // Initial render
        render();
        el.classList.add('m-cal-ready');

        // ── Public API ────────────────────────────────────────────────────────

        return {
            element: el,

            /** Switch to 'month' or 'week' view */
            view: function(v) {
                currentView = v;
                render();
                return this;
            },

            /** Navigate to the period containing the given date (YYYY-MM-DD) */
            goTo: function(dateStr) {
                var d = parseDate(dateStr);
                if (d) {
                    currentDate = new Date(d.getFullYear(), d.getMonth(), 1);
                    render();
                }
                return this;
            },

            /** Jump to today */
            today: function() {
                var t = todayDate();
                currentDate = new Date(t.getFullYear(), t.getMonth(), 1);
                render();
                return this;
            },

            /** Navigate backward one month (or one week in week view) */
            prev: function() {
                navigate(-1);
                return this;
            },

            /** Navigate forward one month (or one week in week view) */
            next: function() {
                navigate(1);
                return this;
            },

            /**
             * Replace all events and re-render.
             * @param {Array} newEvents
             */
            setEvents: function(newEvents) {
                events   = newEvents;
                eventMap = buildEventMap(events);
                render();
                return this;
            },

            /**
             * Append one event and re-render.
             * @param {Object} ev
             */
            addEvent: function(ev) {
                events.push(ev);
                eventMap = buildEventMap(events);
                render();
                return this;
            },

            /** Remove all events and re-render */
            clearEvents: function() {
                events   = [];
                eventMap = {};
                render();
                return this;
            },

            /** Get the currently selected date string (YYYY-MM-DD), or null */
            selected: function() {
                return selectedDate;
            }
        };
    };

    // ── Auto-init ─────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function() {
        var cals = document.querySelectorAll('.m-cal-widget[id]');
        for (var i = 0; i < cals.length; i++) {
            m.calendar(cals[i].id);
        }
    });

})(window);
