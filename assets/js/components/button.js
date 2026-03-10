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

    m.button = function(id, options) {
        const element = utils.getElement(id);
        if (!element) {
            console.warn('Manhattan: Button element not found:', id);
            return null;
        }

        options = options || {};
        
        element._manhattan = {
            type: 'button',
            options: options
        };

        // Bind click event
        if (options.events && options.events.click) {
            element.addEventListener('click', function(e) {
                if (typeof options.events.click === 'function') {
                    options.events.click.call(element, e);
                } else if (typeof window[options.events.click] === 'function') {
                    window[options.events.click].call(element, e);
                }
            });
        }

        // Add ripple effect
        element.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.className = 'm-ripple';
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });

        return {
            element: element,
            
            enable: function() {
                element.disabled = false;
                element.classList.remove('m-disabled');
                return this;
            },
            
            disable: function() {
                element.disabled = true;
                element.classList.add('m-disabled');
                return this;
            },
            
            setText: function(text) {
                const icon = element.querySelector('.m-button-icon');
                const iconPosition = element.getAttribute('data-icon-position') || 'left';
                element.textContent = text;
                if (icon) {
                    if (iconPosition === 'right') {
                        element.appendChild(icon);
                    } else {
                        element.insertBefore(icon, element.firstChild);
                    }
                }
                return this;
            },

            setLoading: function(loading) {
                if (loading) {
                    element.classList.add('m-button-loading');
                    element.disabled = true;
                } else {
                    element.classList.remove('m-button-loading');
                    element.disabled = false;
                }
                return this;
            },

            icon: function(faName, positionOrOptions, maybeOptions) {
                let position = 'left';
                let iconOptions = {};

                if (typeof positionOrOptions === 'string') {
                    position = positionOrOptions;
                    iconOptions = maybeOptions || {};
                } else {
                    iconOptions = positionOrOptions || {};
                }

                const existing = element.querySelector('.m-button-icon');
                if (existing) {
                    existing.remove();
                }

                const iconEl = utils.createIconElement(faName, utils.extend({
                    ariaHidden: true,
                    className: 'm-button-icon'
                }, iconOptions));

                if (!iconEl) {
                    element.removeAttribute('data-icon-position');
                    return this;
                }

                position = (position || 'left').toLowerCase();
                element.setAttribute('data-icon-position', position);

                if (position === 'right') {
                    iconEl.classList.add('m-icon-right');
                    element.appendChild(iconEl);
                } else {
                    element.insertBefore(iconEl, element.firstChild);
                }

                return this;
            }
        };
    };

    /**
     * Custom DatePicker Component with fully custom calendar
     */

})(window);
