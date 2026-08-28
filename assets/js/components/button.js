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

        // Internal helper: set text content while preserving the icon element.
        function _setButtonText(text) {
            var icon = element.querySelector('.m-button-icon');
            var iconPosition = element.getAttribute('data-icon-position') || 'left';
            element.textContent = text;
            if (icon) {
                if (iconPosition === 'right') {
                    element.appendChild(icon);
                } else {
                    element.insertBefore(icon, element.firstChild);
                }
            }
        }

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
                _setButtonText(text);
                return this;
            },

            setLoading: function(loading) {
                var loadingText = element.getAttribute('data-loading-text');
                if (loading) {
                    // Swap in loading text (and save original) only on the first setLoading(true) call.
                    if (loadingText && element._mOriginalText === undefined) {
                        // Capture current text nodes (exclude the icon element).
                        var textParts = [];
                        for (var i = 0; i < element.childNodes.length; i++) {
                            var node = element.childNodes[i];
                            if (node.nodeType === 3) { // TEXT_NODE
                                var t = node.textContent.trim();
                                if (t) { textParts.push(t); }
                            }
                        }
                        element._mOriginalText = textParts.join(' ');
                        _setButtonText(loadingText);
                    }
                    element.classList.add('m-button-loading');
                    element.disabled = true;
                } else {
                    element.classList.remove('m-button-loading');
                    element.disabled = false;
                    // Restore original text if it was swapped.
                    if (loadingText && element._mOriginalText !== undefined) {
                        _setButtonText(element._mOriginalText);
                        delete element._mOriginalText;
                    }
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
     * Delegated handler for Button->confirm() / ['confirm' => ...].
     *
     * Button.php renders the message as data-m-confirm, but nothing read the
     * attribute, so the confirmation silently never happened and the action ran
     * regardless.
     *
     * Bound at the document level in the CAPTURE phase so it runs before any
     * listener the page attached to the button itself — cancelling must stop
     * those too, not just the default action. On confirm the event is left
     * completely alone rather than re-dispatched, so a submit button still
     * submits, an onclick still fires, and a programmatic .click() still works
     * (HTMLElement.click() refuses to re-enter on the same element, which a
     * re-dispatch would trip over).
     *
     * Delegation also means buttons added to the DOM after load are covered
     * without re-initialising anything.
     */
    document.addEventListener('click', function(e) {
        const target = e.target.closest ? e.target.closest('[data-m-confirm]') : null;
        if (!target) return;

        const message = target.getAttribute('data-m-confirm');
        if (!message) return;

        if (!window.confirm(message)) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

})(window);
