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

        // Return cached instance — ensures all callers share the same closure
        // (same isDirty state, same event listeners). Without this, calling
        // m.form(id) a second time (e.g. in a save-success callback) creates a
        // fresh instance with isDirty=false, leaving the original auto-init
        // instance's isDirty=true and the dirty warning still firing on navigation.
        if (form._mForm) { return form._mForm; }

        const isAjax = form.getAttribute('data-m-ajax') === 'true';
        const hasDirtyProtection = form.getAttribute('data-m-dirty-protection') === 'true';
        let isSubmitting = false;
        let isDirty = false;
        let dirtyBaseline = null;
        let beforeUnloadHandler = null;
        
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

        // ── Dirty Form Protection ───────────────────────────────────────────────
        /**
         * Get a snapshot of all current field values keyed by name/id.
         * Handles text inputs, textareas, selects, checkboxes, and radio buttons.
         */
        function snapshotFields() {
            var snapshot = {};
            var inputs = form.querySelectorAll('input, select, textarea');
            for (var i = 0; i < inputs.length; i++) {
                var el = inputs[i];
                var key = el.name || el.id;
                if (!key) continue;
                if (el.type === 'checkbox' || el.type === 'radio') {
                    snapshot[key + '::' + el.value] = el.checked;
                } else {
                    snapshot[key] = el.value;
                }
            }
            // Also snapshot Manhattan rich text editors (data-m-rte)
            var rtes = form.querySelectorAll('[data-component="richtexteditor"]');
            for (var j = 0; j < rtes.length; j++) {
                var rte = rtes[j];
                var rteKey = rte.id || rte.getAttribute('name');
                if (rteKey && window.m && m.richTextEditor) {
                    var rteApi = m.richTextEditor(rteKey);
                    if (rteApi && typeof rteApi.getValue === 'function') {
                        snapshot['__rte__' + rteKey] = rteApi.getValue();
                    }
                }
            }
            return snapshot;
        }

        /**
         * Compare current snapshot against baseline.
         * Returns true if any value has changed.
         */
        function checkDirty() {
            if (!dirtyBaseline) return false;
            var current = snapshotFields();
            var baseKeys = Object.keys(dirtyBaseline);
            var currKeys = Object.keys(current);
            if (baseKeys.length !== currKeys.length) return true;
            for (var i = 0; i < baseKeys.length; i++) {
                var k = baseKeys[i];
                if (current[k] !== dirtyBaseline[k]) return true;
            }
            return false;
        }

        /**
         * Mark the form as not dirty and update the baseline to current values.
         */
        function clearDirty() {
            isDirty = false;
            dirtyBaseline = snapshotFields();
        }

        /**
         * Show a Manhattan confirmation dialog, then proceed if user confirms.
         * @param {function} proceedFn - Called if user chooses to leave.
         */
        function promptDirty(proceedFn) {
            m.dialog.confirm(
                'You have unsaved changes. If you leave now, your changes will be lost.',
                'Unsaved Changes',
                'fa-exclamation-triangle'
            ).then(function(confirmed) {
                if (confirmed) {
                    clearDirty();
                    proceedFn();
                }
            });
        }

        /**
         * Remove all dirty protection event listeners and clear dirty state.
         * Called on form submit or after a confirmed navigation.
         */
        function deactivateDirtyProtection() {
            clearDirty();
            if (docClickHandler) {
                document.removeEventListener('click', docClickHandler, true);
                docClickHandler = null;
            }
            if (beforeUnloadHandler) {
                window.removeEventListener('beforeunload', beforeUnloadHandler);
                beforeUnloadHandler = null;
            }
        }

        var docClickHandler = null;

        if (hasDirtyProtection) {
            // Take baseline snapshot after initial render (next microtask so pre-populated
            // values from Manhattan components have been applied to the DOM).
            setTimeout(function() {
                dirtyBaseline = snapshotFields();
            }, 0);

            // Mark dirty on any user input
            form.addEventListener('input', function() {
                if (checkDirty()) {
                    isDirty = true;
                }
            });
            form.addEventListener('change', function() {
                if (checkDirty()) {
                    isDirty = true;
                }
            });

            // Reset clears dirty state
            form.addEventListener('reset', function() {
                // After native reset the field values are back to defaults —
                // re-snapshot so we don't falsely flag as dirty again.
                setTimeout(function() {
                    clearDirty();
                }, 0);
            });

            // Submit deactivates protection so beforeunload doesn't fire during navigation
            form.addEventListener('submit', function() {
                deactivateDirtyProtection();
            });

            // Native beforeunload: fallback only for true browser-level navigation
            // (tab close, URL bar input, browser back/forward button) where clicking
            // cannot be intercepted. After clearDirty() isDirty is false, so this
            // won't double-fire for Manhattan-intercepted navigations.
            beforeUnloadHandler = function(e) {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            };
            window.addEventListener('beforeunload', beforeUnloadHandler);

            // Intercept ALL navigating link and cancel-button clicks on the page using
            // capture phase. e.preventDefault() stops the navigation, which also prevents
            // beforeunload from firing — so the Manhattan dialog is the only prompt shown.
            docClickHandler = function(e) {
                if (!isDirty) return;

                var target = e.target;
                while (target && target.tagName) {
                    var tag = target.tagName;

                    // <a href> links that navigate to a real URL (not anchors or javascript:)
                    if (tag === 'A') {
                        var href = target.getAttribute('href');
                        if (href && href !== '#' && href.charAt(0) !== '#' && href.indexOf('javascript:') !== 0) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            (function(absHref) {
                                promptDirty(function() {
                                    window.location.href = absHref;
                                });
                            })(target.href);
                        }
                        return; // found <a>, stop ascending regardless
                    }

                    // <button type="button"> with history.back() onclick (cancel buttons)
                    if (tag === 'BUTTON') {
                        var btnType = (target.type || 'button').toLowerCase();
                        if (btnType !== 'submit' && btnType !== 'reset') {
                            var onclick = target.getAttribute('onclick') || '';
                            if (onclick.indexOf('history.back') !== -1) {
                                e.preventDefault();
                                e.stopImmediatePropagation();
                                promptDirty(function() {
                                    window.history.back();
                                });
                            }
                        }
                        return; // found <button>, stop ascending
                    }

                    target = target.parentElement;
                }
            };
            document.addEventListener('click', docClickHandler, true);
        }

        // Public API
        const api = {
            serialize: serialize,
            populate: populate,
            reset: reset,
            setLoading: setLoading,
            submit: submit,
            onSubmit: onSubmit,
            validate: validate,
            isDirty: function() { return isDirty; },
            clearDirty: clearDirty
        };
        form._mForm = api;
        return api;
    };

    // Auto-initialize all AJAX forms and dirty-protection forms
    utils.ready(function() {
        var forms = document.querySelectorAll('[data-m-ajax="true"], [data-m-dirty-protection="true"]');
        var seen = {};
        for (var i = 0; i < forms.length; i++) {
            var form = forms[i];
            if (form.id && !seen[form.id]) {
                seen[form.id] = true;
                m.form(form.id);
            }
        }
    });

})(window);
