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

    m.textarea = function(id, options) {
        let textarea = utils.getElement(id);
        
        if (!textarea) {
            console.warn('Manhattan: TextArea element not found:', id);
            return null;
        }

        options = utils.extend({
            onChange: null,
            onInput: null,
            onFocus: null,
            onBlur: null,
            autoResize: false
        }, options || {});

        // Initialize Manhattan data
        if (!textarea._manhattan) {
            textarea._manhattan = {
                options: options,
                isValid: true,
                errorMessage: ''
            };
        }

        // Auto-resize functionality
        if (options.autoResize || textarea.classList.contains('m-textarea-resize-auto')) {
            const autoResize = function() {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            };
            
            textarea.addEventListener('input', autoResize);
            // Initial resize
            setTimeout(autoResize, 0);
        }

        // Input event handler
        if (options.onInput) {
            textarea.addEventListener('input', function(e) {
                const handler = options.onInput;
                const data = { 
                    value: textarea.value,
                    element: textarea
                };
                if (typeof handler === 'function') {
                    handler.call(textarea, data);
                } else if (typeof window[handler] === 'function') {
                    window[handler].call(textarea, data);
                }
            });
        }

        // Change event handler
        if (options.onChange) {
            textarea.addEventListener('change', function(e) {
                const handler = options.onChange;
                const data = { 
                    value: textarea.value,
                    element: textarea
                };
                if (typeof handler === 'function') {
                    handler.call(textarea, data);
                } else if (typeof window[handler] === 'function') {
                    window[handler].call(textarea, data);
                }
            });
        }

        // Focus event handler
        if (options.onFocus) {
            textarea.addEventListener('focus', function(e) {
                const handler = options.onFocus;
                if (typeof handler === 'function') {
                    handler.call(textarea, e);
                } else if (typeof window[handler] === 'function') {
                    window[handler].call(textarea, e);
                }
            });
        }

        // Blur event handler
        if (options.onBlur) {
            textarea.addEventListener('blur', function(e) {
                const handler = options.onBlur;
                if (typeof handler === 'function') {
                    handler.call(textarea, e);
                } else if (typeof window[handler] === 'function') {
                    window[handler].call(textarea, e);
                }
            });
        }

        // API
        return {
            element: textarea,
            getValue: function() {
                return textarea.value;
            },
            setValue: function(value) {
                textarea.value = value || '';
                if (options.autoResize || textarea.classList.contains('m-textarea-resize-auto')) {
                    textarea.style.height = 'auto';
                    textarea.style.height = textarea.scrollHeight + 'px';
                }
                utils.trigger(textarea, 'change', { value: textarea.value });
                return this;
            },
            clear: function() {
                return this.setValue('');
            },
            focus: function() {
                textarea.focus();
                return this;
            },
            disable: function() {
                textarea.disabled = true;
                textarea.classList.add('m-disabled');
                return this;
            },
            enable: function() {
                textarea.disabled = false;
                textarea.classList.remove('m-disabled');
                return this;
            }
        };
    };

    /**
     * Ajax helper
     */

})(window);
