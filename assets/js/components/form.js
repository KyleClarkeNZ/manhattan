/**
 * Manhattan Form Component
 * Provides AJAX submission, loading states, validation, and event handling
 * 
 * @since 1.5.0
 */
(function(window) {
    'use strict';
    const m = window.m;
    const utils = m.utils;

    /**
     * Initialize a Form component
     * @param {string} id - Form element ID
     * @returns {object} API object
     */
    m.form = function(id) {
        const form = utils.getElement(id);
        if (!form) {
            console.error('[Manhattan Form] Element not found:', id);
            return null;
        }

        const isAjax = form.getAttribute('data-m-ajax') === 'true';
        let isSubmitting = false;
        
        /**
         * Get form data as an object
         * @returns {object} Form data
         */
        function serialize() {
            const formData = new FormData(form);
            const data = {};
            
            for (const [key, value] of formData.entries()) {
                // Handle multiple values (checkboxes, multi-selects)
                if (data[key]) {
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
            
            return data;
        }

        /**
         * Populate form fields from an object
         * @param {object} data - Data to populate
         */
        function populate(data) {
            if (!data || typeof data !== 'object') {
                return;
            }

            Object.keys(data).forEach(function(key) {
                const value = data[key];
                const elements = form.elements[key];
                
                if (!elements) {
                    return;
                }

                // Handle multiple elements with same name (radio buttons, checkboxes)
                if (elements.length > 1) {
                    for (let i = 0; i < elements.length; i++) {
                        const el = elements[i];
                        if (el.type === 'radio') {
                            el.checked = (el.value === value);
                        } else if (el.type === 'checkbox') {
                            if (Array.isArray(value)) {
                                el.checked = value.indexOf(el.value) !== -1;
                            } else {
                                el.checked = (el.value === value);
                            }
                        }
                    }
                } else {
                    const el = elements[0] || elements;
                    if (el.type === 'checkbox') {
                        el.checked = !!value;
                    } else if (el.type === 'radio') {
                        el.checked = (el.value === value);
                    } else {
                        el.value = value;
                    }
                }
            });

            utils.trigger(form, 'm:form:populated', { data: data });
        }

        /**
         * Reset form to initial state
         */
        function reset() {
            form.reset();
            setLoading(false);
            utils.trigger(form, 'm:form:reset');
        }

        /**
         * Set loading state (disable submit button, show spinner)
         * @param {boolean} loading - Whether form is loading
         */
        function setLoading(loading) {
            isSubmitting = loading;
            
            // Find submit button and disable/enable it
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                if (loading) {
                    submitBtn.setAttribute('disabled', 'disabled');
                    submitBtn.classList.add('m-button--loading');
                } else {
                    submitBtn.removeAttribute('disabled');
                    submitBtn.classList.remove('m-button--loading');
                }
            }

            // Disable all form inputs while loading
            const inputs = form.querySelectorAll('input, select, textarea, button');
            for (let i = 0; i < inputs.length; i++) {
                inputs[i].disabled = loading;
            }
        }

        /**
         * Submit the form programmatically
         * @param {function} callback - Optional callback after submit
         */
        function submit(callback) {
            if (isSubmitting) {
                return;
            }

            // Trigger pre-submit event (cancelable)
            const event = utils.trigger(form, 'm:form:submit', { 
                data: serialize(),
                cancelable: true 
            });
            
            if (event && event.defaultPrevented) {
                return;
            }

            if (isAjax) {
                submitAjax(callback);
            } else {
                form.submit();
                if (callback) {
                    callback();
                }
            }
        }

        /**
         * Submit form via AJAX
         * @param {function} callback - Optional callback
         */
        function submitAjax(callback) {
            setLoading(true);

            const action = form.getAttribute('action') || window.location.href;
            const method = form.getAttribute('method') || 'POST';
            const formData = new FormData(form);

            m.ajax(action, {
                method: method,
                data: formData,
                success: function(response) {
                    setLoading(false);
                    utils.trigger(form, 'm:form:success', { response: response });
                    
                    if (callback) {
                        callback(null, response);
                    }
                },
                error: function(error) {
                    setLoading(false);
                    utils.trigger(form, 'm:form:error', { error: error });
                    
                    if (callback) {
                        callback(error, null);
                    }
                }
            });
        }

        /**
         * Register custom submit handler
         * @param {function} handler - Submit handler function
         */
        function onSubmit(handler) {
            if (typeof handler !== 'function') {
                return;
            }

            form.addEventListener('submit', function(e) {
                if (isAjax) {
                    e.preventDefault();
                }
                
                handler(e, serialize());
            });
        }

        /**
         * Validate form (if validator is attached)
         * @returns {boolean} Whether form is valid
         */
        function validate() {
            // Trigger validation via validator if present
            const validator = window.manhattanValidators && window.manhattanValidators[id];
            if (validator && typeof validator.validate === 'function') {
                return validator.validate();
            }
            
            // Fallback: use HTML5 validation
            return form.checkValidity();
        }

        // Auto-handle AJAX form submission
        if (isAjax) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submit();
            });
        }

        // Public API
        return {
            serialize: serialize,
            populate: populate,
            reset: reset,
            setLoading: setLoading,
            submit: submit,
            onSubmit: onSubmit,
            validate: validate
        };
    };

    // Auto-initialize all AJAX forms
    utils.ready(function() {
        const forms = document.querySelectorAll('[data-m-ajax="true"]');
        for (let i = 0; i < forms.length; i++) {
            const form = forms[i];
            if (form.id) {
                m.form(form.id);
            }
        }
    });

})(window);
