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

    m.datepicker = function(id, options) {
        let input = utils.getElement(id);
        
        if (!input) {
            console.warn('Manhattan: DatePicker element not found:', id);
            return null;
        }

        options = utils.extend({
            format: input.getAttribute('data-format') || 'Y-m-d',
            min: input.getAttribute('data-min') || null,
            max: input.getAttribute('data-max') || null,
            placeholder: input.getAttribute('placeholder') || 'Select date...',
            showTodayButton: input.getAttribute('data-show-today') === 'true',
            highlightToday: input.getAttribute('data-highlight-today') !== 'false'
        }, options || {});

        // Replace native input with custom component
        const wrapper = input.closest('.m-datepicker-wrapper') || createDatePickerWrapper(input);
        const customInput = wrapper.querySelector('.m-datepicker-input') || createCustomInput(input, wrapper);
        const calendar = createCalendar(customInput, wrapper, options);

        // Store component data
        customInput._manhattan = {
            type: 'datepicker',
            options: options,
            calendar: calendar,
            originalInput: input
        };

        // Setup interactions
        setupDatePickerEvents(customInput, calendar, wrapper, options);

        return {
            element: customInput,
            
            value: function(val) {
                if (val === undefined) {
                    return customInput.getAttribute('data-value') || '';
                }
                setDateValue(customInput, calendar, val, options);
                return this;
            },
            
            min: function(val) {
                options.min = val;
                customInput.setAttribute('data-min', val);
                return this;
            },
            
            max: function(val) {
                options.max = val;
                customInput.setAttribute('data-max', val);
                return this;
            },
            
            enable: function() {
                customInput.classList.remove('m-disabled');
                wrapper.classList.remove('m-disabled');
                return this;
            },
            
            disable: function() {
                customInput.classList.add('m-disabled');
                wrapper.classList.add('m-disabled');
                calendar.style.display = 'none';
                return this;
            }
        };
    };

    function createDatePickerWrapper(input) {
        const wrapper = utils.createElement('div', 'm-datepicker-wrapper');
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        return wrapper;
    }

    function createCustomInput(originalInput, wrapper) {
        const customInput = utils.createElement('div', 'm-datepicker-input');
        customInput.setAttribute('tabindex', '0');
        customInput.setAttribute('data-value', originalInput.value || '');
        customInput.innerHTML = `
            <span class="m-datepicker-value">${originalInput.value || originalInput.getAttribute('placeholder') || 'Select date...'}</span>
            <i class="fas fa-calendar-alt m-datepicker-icon"></i>
        `;

        // Apply value class if pre-populated (e.g. via PHP ->value())
        if (originalInput.value) {
            customInput.querySelector('.m-datepicker-value').classList.add('m-has-value');
        }

        // Hide original input
        originalInput.style.display = 'none';
        wrapper.insertBefore(customInput, originalInput);
        
        return customInput;
    }

    function createCalendar(input, wrapper, options) {
        const calendar = utils.createElement('div', 'm-calendar');
        calendar.style.display = 'none';
        wrapper.appendChild(calendar);
        return calendar;
    }

    function setupDatePickerEvents(input, calendar, wrapper, options) {
        const originalInput = input._manhattan?.originalInput;
        
        // Listen for programmatic changes to the original input (e.g., from edit forms)
        if (originalInput) {
            originalInput.addEventListener('change', function() {
                const newValue = this.value;
                // Update custom datepicker display without triggering another change event
                input.setAttribute('data-value', newValue);
                
                const valueSpan = input.querySelector('.m-datepicker-value');
                if (valueSpan) {
                    valueSpan.textContent = newValue || options.placeholder || 'Select date...';
                    if (newValue) {
                        valueSpan.classList.add('m-has-value');
                    } else {
                        valueSpan.classList.remove('m-has-value');
                    }
                }
            });
        }
        
        // Toggle calendar
        input.addEventListener('click', function(e) {
            e.stopPropagation();
            if (calendar.style.display === 'none') {
                showCalendar(input, calendar, options);
            } else {
                hideCalendar(calendar);
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                hideCalendar(calendar);
            }
        });

        // Keyboard support
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                showCalendar(input, calendar, options);
            } else if (e.key === 'Escape') {
                hideCalendar(calendar);
            }
        });
    }

    function showCalendar(input, calendar, options) {
        const currentValue = input.getAttribute('data-value');
        const displayDate = currentValue ? utils.parseDate(currentValue, options.format) : new Date();
        renderCalendar(input, calendar, displayDate, options);
        calendar.style.display = 'block';

        // Choose direction (down vs up) within nearest scroll container / viewport
        const wrapper = calendar.parentElement;
        if (wrapper) {
            wrapper.classList.remove('m-open-up');
            wrapper.classList.remove('m-align-right');

            const triggerRect = input.getBoundingClientRect();
            const calRect = calendar.getBoundingClientRect();
            const boundary = getBoundaryRect(wrapper);

            // Vertical positioning (up vs down)
            const spaceBelow = boundary.bottom - triggerRect.bottom;
            const spaceAbove = triggerRect.top - boundary.top;
            const needed = calRect.height;

            if (spaceBelow < needed && spaceAbove > spaceBelow) {
                wrapper.classList.add('m-open-up');
            }

            // Horizontal positioning (check right edge)
            const spaceRight = boundary.right - triggerRect.left;
            if (spaceRight < calRect.width) {
                wrapper.classList.add('m-align-right');
            }
        }
    }

    function hideCalendar(calendar) {
        calendar.style.display = 'none';
        const wrapper = calendar.parentElement;
        if (wrapper) {
            wrapper.classList.remove('m-open-up');
        }
    }

    function getBoundaryRect(el) {
        const vh = window.innerHeight || document.documentElement.clientHeight;
        const vw = window.innerWidth || document.documentElement.clientWidth;

        let p = el.parentElement;
        while (p && p !== document.body) {
            const style = window.getComputedStyle(p);
            const overflowY = style.overflowY;
            const overflowX = style.overflowX;
            const overflow = (overflowY || '') + ' ' + (overflowX || '');

            if (/(auto|scroll|hidden)/.test(overflow)) {
                return p.getBoundingClientRect();
            }
            p = p.parentElement;
        }

        return { top: 0, left: 0, right: vw, bottom: vh };
    }

    function renderCalendar(input, calendar, displayDate, options) {
        const year = displayDate.getFullYear();
        const month = displayDate.getMonth();
        const today = new Date();
        const currentValue = input.getAttribute('data-value');
        const selectedDate = currentValue ? utils.parseDate(currentValue, options.format) : null;

        let html = '<div class="m-calendar-header">';
        html += `<button type="button" class="m-cal-btn m-cal-prev" data-action="prev"><i class="fas fa-chevron-left"></i></button>`;
        html += `<span class="m-cal-title">${getMonthName(month)} ${year}</span>`;
        html += `<button type="button" class="m-cal-btn m-cal-next" data-action="next"><i class="fas fa-chevron-right"></i></button>`;
        html += '</div>';
        
        html += '<div class="m-calendar-body">';
        html += '<div class="m-cal-weekdays">';
        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
            html += `<div class="m-cal-weekday">${day}</div>`;
        });
        html += '</div>';
        
        html += '<div class="m-cal-days">';
        
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const prevMonthDays = new Date(year, month, 0).getDate();
        
        // Previous month days
        for (let i = firstDay - 1; i >= 0; i--) {
            const day = prevMonthDays - i;
            html += `<div class="m-cal-day m-cal-other-month">${day}</div>`;
        }
        
        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateStr = utils.formatDate(date, options.format);
            let classes = 'm-cal-day';
            
            if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
                classes += ' m-cal-selected';
            }
            
            if (options.highlightToday && date.toDateString() === today.toDateString()) {
                classes += ' m-cal-today';
            }
            
            if (options.min && date < utils.parseDate(options.min, options.format)) {
                classes += ' m-cal-disabled';
            }
            if (options.max && date > utils.parseDate(options.max, options.format)) {
                classes += ' m-cal-disabled';
            }
            
            html += `<div class="${classes}" data-date="${dateStr}" data-year="${year}" data-month="${month}">${day}</div>`;
        }
        
        // Next month days
        const totalCells = firstDay + daysInMonth;
        const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        for (let day = 1; day <= remainingCells; day++) {
            html += `<div class="m-cal-day m-cal-other-month">${day}</div>`;
        }
        
        html += '</div></div>';
        
        // Add today button if enabled
        if (options.showTodayButton) {
            html += '<div class="m-calendar-footer">';
            html += '<button type="button" class="m-cal-today-btn"><i class="fas fa-calendar-day"></i> Today</button>';
            html += '</div>';
        }
        
        calendar.innerHTML = html;
        
        // Bind events
        calendar.querySelectorAll('.m-cal-day:not(.m-cal-disabled):not(.m-cal-other-month)').forEach(dayEl => {
            dayEl.addEventListener('click', function() {
                const dateStr = this.getAttribute('data-date');
                setDateValue(input, calendar, dateStr, options);
                hideCalendar(calendar);
            });
        });
        
        calendar.querySelector('.m-cal-prev').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const newDate = new Date(year, month - 1, 1);
            renderCalendar(input, calendar, newDate, options);
        });
        
        calendar.querySelector('.m-cal-next').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const newDate = new Date(year, month + 1, 1);
            renderCalendar(input, calendar, newDate, options);
        });
        
        // Today button handler
        const todayBtn = calendar.querySelector('.m-cal-today-btn');
        if (todayBtn) {
            todayBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const today = new Date();
                const todayStr = utils.formatDate(today, options.format);
                setDateValue(input, calendar, todayStr, options);
                hideCalendar(calendar);
            });
        }
    }

    function setDateValue(input, calendar, value, options) {
        input.setAttribute('data-value', value);
        const valueSpan = input.querySelector('.m-datepicker-value');
        if (valueSpan) {
            valueSpan.textContent = value || options.placeholder || 'Select date...';
            if (value) {
                valueSpan.classList.add('m-has-value');
            } else {
                valueSpan.classList.remove('m-has-value');
            }
        }
        
        // Update original hidden input
        const originalInput = input._manhattan?.originalInput;
        if (originalInput) {
            originalInput.value = value;
            utils.trigger(originalInput, 'change', { value: value });
        }
    }

    function getMonthName(month) {
        const months = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
        return months[month];
    }

    /**
     * Custom Dropdown Component with fully custom rendering
     */

})(window);
