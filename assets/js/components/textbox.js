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

    m.textbox = function(id, options) {
        let input = utils.getElement(id);
        
        if (!input) {
            console.warn('Manhattan: TextBox element not found:', id);
            return null;
        }

        options = utils.extend({
            onChange: null,
            onInput: null,
            onFocus: null,
            onBlur: null,
            validateOnBlur: true
        }, options || {});

        // Initialize Manhattan data
        if (!input._manhattan) {
            input._manhattan = {
                options: options,
                isValid: true,
                errorMessage: ''
            };
        }

        // Input event handler
        if (options.onInput) {
            input.addEventListener('input', function(e) {
                const handler = options.onInput;
                const data = { 
                    value: input.value,
                    element: input
                };
                if (typeof handler === 'function') {
                    handler.call(input, data);
                } else if (typeof window[handler] === 'function') {
                    window[handler].call(input, data);
                }
            });
        }

        // Change event handler
        if (options.onChange) {
            input.addEventListener('change', function(e) {
                const handler = options.onChange;
                const data = { 
                    value: input.value,
                    element: input
                };
                if (typeof handler === 'function') {
                    handler.call(input, data);
                } else if (typeof window[handler] === 'function') {
                    window[handler].call(input, data);
                }
            });
        }

        // Focus event handler
        if (options.onFocus) {
            input.addEventListener('focus', function(e) {
                const handler = options.onFocus;
                if (typeof handler === 'function') {
                    handler.call(input, e);
                } else if (typeof window[handler] === 'function') {
                    window[handler].call(input, e);
                }
            });
        }

        // Blur event handler
        if (options.onBlur || options.validateOnBlur) {
            input.addEventListener('blur', function(e) {
                if (options.validateOnBlur) {
                    validateTextBox(input);
                }
                
                if (options.onBlur) {
                    const handler = options.onBlur;
                    if (typeof handler === 'function') {
                        handler.call(input, e);
                    } else if (typeof window[handler] === 'function') {
                        window[handler].call(input, e);
                    }
                }
            });
        }

        // API
        return {
            element: input,
            getValue: function() {
                return input.value;
            },
            setValue: function(value) {
                input.value = value || '';
                utils.trigger(input, 'change', { value: input.value });
                return this;
            },
            clear: function() {
                return this.setValue('');
            },
            focus: function() {
                input.focus();
                return this;
            },
            disable: function() {
                input.disabled = true;
                input.classList.add('m-disabled');
                return this;
            },
            enable: function() {
                input.disabled = false;
                input.classList.remove('m-disabled');
                return this;
            },
            validate: function() {
                return validateTextBox(input);
            },
            setError: function(message) {
                input._manhattan.isValid = false;
                input._manhattan.errorMessage = message;
                input.classList.add('m-error');
                showTextBoxError(input, message);
                return this;
            },
            clearError: function() {
                input._manhattan.isValid = true;
                input._manhattan.errorMessage = '';
                input.classList.remove('m-error');
                hideTextBoxError(input);
                return this;
            }
        };
    };

    function validateTextBox(input) {
        const wrapper = input.closest('.m-textbox-wrapper');
        
        // Clear previous errors
        input.classList.remove('m-error');
        hideTextBoxError(input);
        
        // Check HTML5 validity
        if (!input.checkValidity()) {
            input.classList.add('m-error');
            const message = input.validationMessage || 'Invalid input';
            showTextBoxError(input, message);
            input._manhattan.isValid = false;
            input._manhattan.errorMessage = message;
            return false;
        }
        
        input._manhattan.isValid = true;
        input._manhattan.errorMessage = '';
        return true;
    }

    function showTextBoxError(input, message) {
        const wrapper = input.closest('.m-textbox-wrapper');
        if (!wrapper) return;
        
        let errorEl = wrapper.querySelector('.m-textbox-error');
        if (!errorEl) {
            errorEl = utils.createElement('div', 'm-textbox-error');
            wrapper.appendChild(errorEl);
        }
        
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    function hideTextBoxError(input) {
        const wrapper = input.closest('.m-textbox-wrapper');
        if (!wrapper) return;
        
        const errorEl = wrapper.querySelector('.m-textbox-error');
        if (errorEl) {
            errorEl.style.display = 'none';
        }
    }

    /**
     * Custom TextArea Component with auto-resize
     */

})(window);
