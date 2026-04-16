/**
 * Manhattan Wizard Component
 *
 * Multi-step form wizard with step-progress indicator, per-step validation,
 * skippable steps, remote data binding, and structured AJAX / form submission.
 *
 * Auto-initialised for any .m-wizard element on DOMContentLoaded (see
 * manhattan.js).  Use m.wizard(id) to get the programmatic API.
 *
 * JavaScript API:
 *   var wiz = m.wizard('myWizard');
 *   wiz.next()            – advance to next step (validates first)
 *   wiz.prev()            – go to previous step
 *   wiz.skip()            – skip current step (only if skippable)
 *   wiz.goTo(index)       – jump to a specific step index
 *   wiz.submit()          – trigger final submission
 *   wiz.reset()           – return to step 0 and clear collected data
 *   wiz.getCurrentStep()  – { index, key, title, … }
 *   wiz.getData()         – returns the full structured submission payload
 *   wiz.setFieldValue(fieldId, value) – set a field's value programmatically
 *
 * Events (fired on the root .m-wizard element):
 *   m:wizard:stepchange  – before navigation;  detail: { from, to, direction }
 *                          return false from handler to cancel
 *   m:wizard:stepchanged – after navigation;   detail: { from, to, direction }
 *   m:wizard:validate    – validation failed;  detail: { step, fields }
 *   m:wizard:submit      – about to submit;    detail: { data }
 *   m:wizard:complete    – submission success; detail: { response }
 *   m:wizard:error       – submission error;   detail: { error }
 */

(function (window) {
    'use strict';

    var m     = window.m;
    var utils = m.utils;

    // =========================================================================
    // Constructor
    // =========================================================================

    /**
     * @param {string|HTMLElement} id
     * @param {object} [options]  Override any key from data-config
     */
    m.wizard = function (id, options) {
        var el = utils.getElement(id);
        if (!el) return null;

        // Avoid double-init
        if (el._mWizardInstance) {
            return el._mWizardInstance;
        }

        // ── Read config from PHP-rendered data attribute ───────────────────
        var rawConfig = {};
        try {
            rawConfig = JSON.parse(el.getAttribute('data-config') || '{}');
        } catch (e) {
            console.error('Manhattan Wizard: invalid data-config JSON', e);
        }

        var cfg = utils.extend({
            steps:           [],
            submitUrl:       null,
            submitMethod:    'POST',
            dataUrl:         null,
            ajaxSubmit:      true,
            onComplete:      null,
            onStepChange:    null,
            showStepCounter: true,
            nextText:        'Next',
            prevText:        'Back',
            skipText:        'Skip',
            submitText:      'Submit'
        }, rawConfig, options || {});

        // ── DOM references ─────────────────────────────────────────────────
        var steps      = Array.prototype.slice.call(el.querySelectorAll('.m-wizard-step'));
        var panels     = Array.prototype.slice.call(el.querySelectorAll('.m-wizard-panel'));
        var prevBtn    = el.querySelector('.m-wizard-btn-prev');
        var nextBtn    = el.querySelector('.m-wizard-btn-next');
        var skipBtn    = el.querySelector('.m-wizard-btn-skip');
        var submitBtn  = el.querySelector('.m-wizard-btn-submit');
        var counter    = el.querySelector('.m-wizard-step-counter');
        var errorBanner = el.querySelector('.m-wizard-error-banner');
        var errorText   = errorBanner ? errorBanner.querySelector('.m-wizard-error-text') : null;
        var loadingEl   = el.querySelector('.m-wizard-loading');
        var connectors  = Array.prototype.slice.call(el.querySelectorAll('.m-wizard-connector'));

        // ── State ──────────────────────────────────────────────────────────
        var currentIndex   = 0;
        var totalSteps     = cfg.steps.length;
        var skippedSteps   = [];   // array of step keys
        var completedSteps = [];   // array of step keys
        /** Captures field values keyed by step key → { fieldName: value } */
        var stepData       = {};

        (function initStepData() {
            cfg.steps.forEach(function (s) { stepData[s.key] = {}; });
        }());

        // =========================================================================
        // Navigation helpers
        // =========================================================================

        function getStepConfig(index) {
            return cfg.steps[index] || null;
        }

        /** Return all field elements inside a panel (unwrap Manhattan wrappers). */
        function getFieldsInPanel(panelEl) {
            return Array.prototype.slice.call(
                panelEl.querySelectorAll('input, select, textarea')
            ).filter(function (f) {
                // Exclude hidden wizard-internal inputs, but keep RTE's hidden input
                return f.type !== 'hidden'
                    || f.classList.contains('m-wizard-field')
                    || f.classList.contains('m-rte-hidden-input');
            });
        }

        /**
         * Resolve a field by id or name attribute, unwrapping Manhattan
         * component wrappers (.m-textbox-wrapper, .m-dropdown-wrapper, etc.).
         */
        function resolveField(panelEl, identifier) {
            // Direct id lookup first
            var direct = document.getElementById(identifier);
            if (direct) return { el: direct, inputEl: unwrapToInput(direct) };

            // name attribute lookup within the panel
            var byName = panelEl.querySelector('[name="' + identifier + '"]');
            if (byName) return { el: byName, inputEl: unwrapToInput(byName) };

            return null;
        }

        function unwrapToInput(el) {
            if (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') {
                return el;
            }
            var inner = el.querySelector('input, select, textarea');
            return inner || el;
        }

        function getFieldValue(inputEl) {
            if (!inputEl) return '';
            if (inputEl.type === 'checkbox') return inputEl.checked ? inputEl.value || 'on' : '';
            if (inputEl.type === 'radio') {
                // find the checked radio in the same group
                var group = document.querySelectorAll('input[name="' + inputEl.name + '"]:checked');
                return group.length ? group[0].value : '';
            }
            return inputEl.value;
        }

        function setFieldValue(inputEl, value) {
            if (!inputEl) return;
            if (inputEl.type === 'checkbox') {
                inputEl.checked = !!value;
            } else if (inputEl.type === 'radio') {
                var radios = document.querySelectorAll('input[name="' + inputEl.name + '"]');
                radios.forEach(function (r) { r.checked = (r.value === String(value)); });
            } else {
                inputEl.value = String(value == null ? '' : value);
                // Trigger change so Manhattan components (dropdown etc.) can react
                inputEl.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // =========================================================================
        // Data capture
        // =========================================================================

        /** Harvest all field values from the given panel into stepData. */
        function captureStepData(panelEl, stepKey) {
            if (!panelEl) return;
            var fields = getFieldsInPanel(panelEl);
            fields.forEach(function (f) {
                var name = f.id || f.name || f.getAttribute('data-name');
                if (name) {
                    stepData[stepKey] = stepData[stepKey] || {};
                    stepData[stepKey][name] = getFieldValue(f);
                }
            });
        }

        /** Collect a flat map of all field values across every step. */
        function collectAllData() {
            // Re-capture the current panel before building the payload
            var currentPanel = panels[currentIndex];
            var currentStepCfg = getStepConfig(currentIndex);
            if (currentPanel && currentStepCfg) {
                captureStepData(currentPanel, currentStepCfg.key);
            }

            var flat = {};
            cfg.steps.forEach(function (s) {
                var data = stepData[s.key];
                if (data) {
                    Object.keys(data).forEach(function (k) { flat[k] = data[k]; });
                }
            });
            return flat;
        }

        /** Build the complete submission payload including wizard metadata. */
        function buildPayload() {
            var flat = collectAllData();
            var currentStepCfg = getStepConfig(currentIndex);

            flat['_wizard'] = {
                id:               el.id,
                currentStep:      currentStepCfg ? currentStepCfg.key : '',
                currentStepIndex: currentIndex,
                completedSteps:   completedSteps.slice(),
                skippedSteps:     skippedSteps.slice(),
                totalSteps:       totalSteps,
                stepData:         stepData
            };
            return flat;
        }

        // =========================================================================
        // Validation
        // =========================================================================

        function clearErrors() {
            if (errorBanner) errorBanner.style.display = 'none';
            el.querySelectorAll('.m-wizard-field-error').forEach(function (e) {
                e.classList.remove('m-wizard-field-error');
            });
            el.querySelectorAll('.m-wizard-inline-error').forEach(function (e) {
                e.parentNode && e.parentNode.removeChild(e);
            });
            // If the current step uses a Form Validator, reset its inline errors
            var stepCfg = getStepConfig(currentIndex);
            if (stepCfg && stepCfg.validatorFormId) {
                var vForm = document.getElementById(stepCfg.validatorFormId);
                if (vForm && vForm._mValidatorInstance) {
                    vForm._mValidatorInstance.reset();
                }
            }
        }

        function showError(message) {
            if (!errorBanner) return;
            if (errorText) errorText.textContent = message;
            errorBanner.style.display = '';
        }

        function highlightField(inputEl) {
            if (!inputEl) return;
            inputEl.classList.add('m-wizard-field-error');
            // Also mark the parent wrapper if it exists
            var wrapper = inputEl.closest(
                '.m-textbox-wrapper, .m-dropdown-wrapper, .m-datepicker-wrapper, .m-textarea-wrapper'
            );
            if (wrapper) wrapper.classList.add('m-wizard-field-error');
        }

        /**
         * Validate the current step.
         * Returns true if valid, false otherwise (also shows errors inline).
         *
         * If the step has a validatorFormId, the Manhattan Validator attached
         * to that <form> is used for field-level inline errors.  Otherwise
         * the bespoke validateFields check is used as a fallback.
         */
        function validateStep(index) {
            var stepCfg = getStepConfig(index);
            if (!stepCfg) return true;

            // ── Path 1: delegate to a Manhattan Validator ──────────────────
            if (stepCfg.validatorFormId) {
                var vForm = document.getElementById(stepCfg.validatorFormId);
                if (vForm && vForm._mValidatorInstance) {
                    clearErrors();
                    var isValid = vForm._mValidatorInstance.validateAll();
                    if (!isValid) {
                        var bannerMsg = stepCfg.validationMessage ||
                            'Please fill in all required fields before continuing.';
                        showError(bannerMsg);
                        utils.trigger(el, 'm:wizard:validate', {
                            step:   stepCfg,
                            fields: []
                        });
                    }
                    return isValid;
                }
            }

            // ── Path 2: bespoke validateFields check ───────────────────────
            if (!stepCfg.validateFields || stepCfg.validateFields.length === 0) {
                return true;
            }

            var panel = panels[index];
            if (!panel) return true;

            clearErrors();

            var invalid = [];
            stepCfg.validateFields.forEach(function (fieldId) {
                var resolved = resolveField(panel, fieldId);
                if (!resolved) return;

                var value = getFieldValue(resolved.inputEl);
                if (value === '' || value === null || value === undefined) {
                    invalid.push(resolved.inputEl);
                    highlightField(resolved.inputEl);
                }
            });

            if (invalid.length > 0) {
                var msg = stepCfg.validationMessage ||
                    'Please fill in all required fields before continuing.';
                showError(msg);

                utils.trigger(el, 'm:wizard:validate', {
                    step:   stepCfg,
                    fields: invalid
                });

                return false;
            }

            return true;
        }

        // =========================================================================
        // UI state updates
        // =========================================================================

        function updateStepStrip(fromIndex, toIndex) {
            // Mark previous step as completed (or skipped indicator is already set)
            if (fromIndex >= 0 && fromIndex < steps.length) {
                var fromEl = steps[fromIndex];
                fromEl.classList.remove('m-wizard-step-active');
                fromEl.setAttribute('aria-selected', 'false');
                fromEl.setAttribute('tabindex', '-1');
            }

            // Mark new step as active; also clear any done/skipped marker
            // so that navigating back to a completed step shows it as active
            if (toIndex >= 0 && toIndex < steps.length) {
                var toEl = steps[toIndex];
                toEl.classList.remove('m-wizard-step-done', 'm-wizard-step-skipped');
                toEl.classList.add('m-wizard-step-active');
                toEl.setAttribute('aria-selected', 'true');
                toEl.setAttribute('tabindex', '0');
            }

            // Mark connectors up to the new step as filled
            connectors.forEach(function (connector, i) {
                // connector[i] sits between step[i] and step[i+1]
                if (i < toIndex) {
                    connector.classList.add('m-wizard-connector-done');
                } else {
                    connector.classList.remove('m-wizard-connector-done');
                }
            });
        }

        /** Mark a step indicator circle as done (tick). */
        function markStepDone(index) {
            if (index < 0 || index >= steps.length) return;
            steps[index].classList.add('m-wizard-step-done');
        }

        /** Mark a step indicator circle as skipped. */
        function markStepSkipped(index) {
            if (index < 0 || index >= steps.length) return;
            steps[index].classList.add('m-wizard-step-skipped');
        }

        function showPanel(index) {
            panels.forEach(function (p, i) {
                p.classList.toggle('m-wizard-panel-active', i === index);
            });
        }

        function updateNavButtons(index) {
            var stepCfg = getStepConfig(index);
            var isFirst = (index === 0);
            var isLast  = (index === totalSteps - 1);

            // Back button — also disabled when the closest step behind us is noReturn.
            if (prevBtn) {
                var prevBlocked = isFirst;
                if (!prevBlocked) {
                    var prevStepCfg = getStepConfig(index - 1);
                    if (prevStepCfg && prevStepCfg.noReturn) {
                        prevBlocked = true;
                    }
                }
                prevBtn.disabled = prevBlocked;
                prevBtn.classList.toggle('m-wizard-btn-hidden', false);
            }

            // Skip button (only if current step is skippable)
            if (skipBtn) {
                var canSkip = stepCfg && stepCfg.skippable && !isLast;
                skipBtn.style.display = canSkip ? '' : 'none';
            }

            // Next vs Submit
            if (nextBtn)   nextBtn.style.display   = isLast ? 'none' : '';
            if (submitBtn) submitBtn.style.display  = isLast ? ''     : 'none';

            // Counter
            if (counter) {
                counter.textContent = 'Step ' + (index + 1) + ' of ' + totalSteps;
            }
        }

        // =========================================================================
        // Navigation
        // =========================================================================

        /**
         * Navigate to the given step index.
         *
         * @param {number} toIndex
         * @param {string} direction  'next' | 'prev' | 'skip' | 'goto'
         * @returns {boolean}
         */
        function goTo(toIndex, direction) {
            direction = direction || 'goto';

            if (toIndex < 0 || toIndex >= totalSteps) return false;
            if (toIndex === currentIndex) return false;

            var fromIndex = currentIndex;
            var fromStepCfg = getStepConfig(fromIndex);

            // ── Fire before-change event ────────────────────────────────────
            var eventDetail = { from: fromIndex, to: toIndex, direction: direction, wizard: api };
            var evt = new CustomEvent('m:wizard:stepchange', {
                detail: eventDetail,
                bubbles: true,
                cancelable: true
            });
            var notCancelled = el.dispatchEvent(evt);
            if (!notCancelled) return false;

            // ── External onStepChange callback ──────────────────────────────
            if (cfg.onStepChange && typeof window[cfg.onStepChange] === 'function') {
                var cbResult = window[cfg.onStepChange](eventDetail);
                if (cbResult === false) return false;
            }

            // ── Capture data from the step we're leaving ────────────────────
            if (fromStepCfg) {
                captureStepData(panels[fromIndex], fromStepCfg.key);
            }

            // ── Mark leaving step state ─────────────────────────────────────
            if (direction === 'skip') {
                if (fromStepCfg && skippedSteps.indexOf(fromStepCfg.key) === -1) {
                    skippedSteps.push(fromStepCfg.key);
                }
                markStepSkipped(fromIndex);
            } else if (direction === 'next' || direction === 'goto') {
                if (fromStepCfg && completedSteps.indexOf(fromStepCfg.key) === -1) {
                    completedSteps.push(fromStepCfg.key);
                }
                markStepDone(fromIndex);
            }

            // ── Transition ──────────────────────────────────────────────────
            clearErrors();
            currentIndex = toIndex;
            showPanel(toIndex);
            updateStepStrip(fromIndex, toIndex);
            updateNavButtons(toIndex);

            // Focus first input in new panel for accessibility
            var newPanel = panels[toIndex];
            if (newPanel) {
                var firstInput = newPanel.querySelector('input:not([type=hidden]), select, textarea');
                if (firstInput) {
                    setTimeout(function () { firstInput.focus(); }, 80);
                }
            }

            // ── Fire after-change event ─────────────────────────────────────
            utils.trigger(el, 'm:wizard:stepchanged', eventDetail);

            return true;
        }

        function next() {
            if (!validateStep(currentIndex)) return false;
            return goTo(currentIndex + 1, 'next');
        }

        function prev() {
            clearErrors();
            // Walk backward, skipping over noReturn steps, to the first
            // step we are actually allowed to return to.
            var target = currentIndex - 1;
            while (target >= 0) {
                var tCfg = getStepConfig(target);
                if (!tCfg || !tCfg.noReturn) break;
                target--;
            }
            if (target < 0) return false;
            return goTo(target, 'prev');
        }

        function skip() {
            var stepCfg = getStepConfig(currentIndex);
            if (!stepCfg || !stepCfg.skippable) return false;
            return goTo(currentIndex + 1, 'skip');
        }

        // =========================================================================
        // Submission
        // =========================================================================

        function setSubmitting(busy) {
            if (submitBtn) {
                submitBtn.disabled = busy;
                var icon = submitBtn.querySelector('i.fas');
                if (icon) {
                    icon.className = busy
                        ? 'fas fa-spinner fa-spin'
                        : 'fas fa-check';
                }
            }
            if (nextBtn) nextBtn.disabled = busy;
            if (prevBtn) prevBtn.disabled = busy;
        }

        function submit() {
            // Final step validation
            if (!validateStep(currentIndex)) return;

            var payload = buildPayload();

            utils.trigger(el, 'm:wizard:submit', { data: payload });

            if (!cfg.submitUrl) {
                // No URL provided – just fire completion with the payload
                utils.trigger(el, 'm:wizard:complete', { response: payload });
                if (cfg.onComplete && typeof window[cfg.onComplete] === 'function') {
                    window[cfg.onComplete](payload);
                }
                return;
            }

            if (!cfg.ajaxSubmit) {
                // Standard form POST
                var form = document.createElement('form');
                form.method = cfg.submitMethod;
                form.action = cfg.submitUrl;
                form.style.display = 'none';

                function addHidden(name, value) {
                    var input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = name;
                    input.value = typeof value === 'object' ? JSON.stringify(value) : String(value);
                    form.appendChild(input);
                }

                Object.keys(payload).forEach(function (k) { addHidden(k, payload[k]); });
                document.body.appendChild(form);
                form.submit();
                return;
            }

            // AJAX submission
            setSubmitting(true);
            clearErrors();

            var xhr = new XMLHttpRequest();
            xhr.open(cfg.submitMethod, cfg.submitUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');

            // Include CSRF token if available
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                xhr.setRequestHeader('X-CSRF-Token', csrfMeta.getAttribute('content') || '');
            }

            xhr.onload = function () {
                setSubmitting(false);
                var response = null;
                try { response = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }

                if (xhr.status >= 200 && xhr.status < 300 && response && response.success !== false) {
                    utils.trigger(el, 'm:wizard:complete', { response: response });
                    if (cfg.onComplete && typeof window[cfg.onComplete] === 'function') {
                        window[cfg.onComplete](response);
                    }
                } else {
                    var errMsg = (response && response.message) ||
                        'Submission failed. Please try again.';
                    showError(errMsg);
                    utils.trigger(el, 'm:wizard:error', { error: errMsg, response: response });
                }
            };

            xhr.onerror = function () {
                setSubmitting(false);
                var msg = 'A network error occurred. Please check your connection and try again.';
                showError(msg);
                utils.trigger(el, 'm:wizard:error', { error: msg });
            };

            xhr.send(JSON.stringify(payload));
        }

        // =========================================================================
        // Remote data source
        // =========================================================================

        function loadRemoteData() {
            if (!cfg.dataUrl) return;

            if (loadingEl) loadingEl.style.display = '';

            var xhr = new XMLHttpRequest();
            xhr.open('GET', cfg.dataUrl, true);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.onload = function () {
                if (loadingEl) loadingEl.style.display = 'none';
                if (xhr.status < 200 || xhr.status >= 300) return;

                var response = null;
                try { response = JSON.parse(xhr.responseText); } catch (e) { return; }

                if (!response || !response.data) return;

                // Populate all fields across all panels
                var data = response.data;
                panels.forEach(function (panel) {
                    Object.keys(data).forEach(function (key) {
                        // Try by id, then by name
                        var field = document.getElementById(key)
                            || panel.querySelector('[name="' + key + '"]');
                        if (field) {
                            setFieldValue(unwrapToInput(field), data[key]);
                        }
                    });
                });
            };

            xhr.onerror = function () {
                if (loadingEl) loadingEl.style.display = 'none';
            };

            xhr.send(null);
        }

        // =========================================================================
        // Button wiring
        // =========================================================================

        if (prevBtn)   prevBtn.addEventListener('click',   function () { prev(); });
        if (nextBtn)   nextBtn.addEventListener('click',   function () { next(); });
        if (skipBtn)   skipBtn.addEventListener('click',   function () { skip(); });
        if (submitBtn) submitBtn.addEventListener('click', function () { submit(); });

        // Wire step indicator clicks for backward navigation.
        // Clicking a completed or skipped step navigates back to it,
        // unless it is marked noReturn.
        steps.forEach(function (stepEl, i) {
            stepEl.addEventListener('click', function () {
                if (i < currentIndex) {
                    var targetCfg = getStepConfig(i);
                    if (targetCfg && targetCfg.noReturn) return;
                    clearErrors();
                    goTo(i, 'prev');
                }
            });
            stepEl.addEventListener('keydown', function (e) {
                if ((e.key === 'Enter' || e.key === ' ') && i < currentIndex) {
                    var targetCfg2 = getStepConfig(i);
                    if (targetCfg2 && targetCfg2.noReturn) return;
                    e.preventDefault();
                    clearErrors();
                    goTo(i, 'prev');
                }
            });
        });

        // =========================================================================
        // Init
        // =========================================================================

        // ── Compressed step strip ──────────────────────────────────────────
        // When there is not enough horizontal space to show all steps at their
        // natural size, the step strip switches to a compact "overlap" mode.
        // Steps before and after the active one bunch up with negative margins;
        // only the active step remains fully visible with its label shown.

        var stepsEl = el.querySelector('.m-wizard-steps');

        /**
         * Each step at minimum needs its circle (44px) and a connector (16px)
         * after it (except the last).  We estimate needed width as:
         *   steps * 80px  +  (steps - 1) * 16px
         * where 80px is the min-width of a step item.
         */
        var MIN_STEP_WIDTH  = 80;
        var MIN_CONN_WIDTH  = 16;
        var STEP_MIN_NEEDED = totalSteps * MIN_STEP_WIDTH + (totalSteps - 1) * MIN_CONN_WIDTH;

        /**
         * Apply or remove m-wizard-step-before-active / m-wizard-step-after-active
         * classes to step and connector elements based on the current active index.
         */
        function updateCompressedClasses(activeIdx) {
            steps.forEach(function (stepEl, i) {
                stepEl.classList.remove('m-wizard-step-before-active', 'm-wizard-step-after-active');
                if (i < activeIdx) {
                    stepEl.classList.add('m-wizard-step-before-active');
                } else if (i > activeIdx) {
                    stepEl.classList.add('m-wizard-step-after-active');
                }
            });
            // connector[i] sits between step[i] and step[i+1]
            connectors.forEach(function (connector, i) {
                connector.classList.remove('m-wizard-connector-before-active', 'm-wizard-connector-after-active');
                // connector just before the active step (between step[activeIdx-1] and step[activeIdx])
                if (i === activeIdx - 1) {
                    connector.classList.add('m-wizard-connector-before-active');
                // connector just after the active step (between step[activeIdx] and step[activeIdx+1])
                } else if (i === activeIdx) {
                    connector.classList.add('m-wizard-connector-after-active');
                }
            });
        }

        function checkCompressed() {
            if (!stepsEl) return;
            var available = stepsEl.offsetWidth;
            if (available > 0 && available < STEP_MIN_NEEDED) {
                if (!stepsEl.classList.contains('m-wizard-compressed')) {
                    stepsEl.classList.add('m-wizard-compressed');
                }
                updateCompressedClasses(currentIndex);
            } else {
                stepsEl.classList.remove('m-wizard-compressed');
                steps.forEach(function (s) {
                    s.classList.remove('m-wizard-step-before-active', 'm-wizard-step-after-active');
                });
                connectors.forEach(function (c) {
                    c.classList.remove('m-wizard-connector-before-active', 'm-wizard-connector-after-active');
                });
            }
        }

        if (stepsEl) {
            if (typeof ResizeObserver !== 'undefined') {
                var _ro = new ResizeObserver(function () { checkCompressed(); });
                _ro.observe(stepsEl);
            } else {
                // Fallback for older browsers
                window.addEventListener('resize', function () { checkCompressed(); });
            }
        }

        // Keep compressed classes in sync whenever the active step changes.
        // We patch goTo to also call updateCompressedClasses after navigation.
        var _origGoTo = goTo;
        goTo = function (toIndex, direction) {
            var result = _origGoTo(toIndex, direction);
            if (result && stepsEl && stepsEl.classList.contains('m-wizard-compressed')) {
                updateCompressedClasses(currentIndex);
            }
            return result;
        };

        // Set initial UI state
        updateNavButtons(0);
        updateStepStrip(-1, 0);

        // Load remote data if configured
        loadRemoteData();

        // =========================================================================
        // Public API
        // =========================================================================

        var api = {
            next:       next,
            prev:       prev,
            skip:       skip,
            goTo:       function (i) { return goTo(i, 'goto'); },
            submit:     submit,
            reset:      function () {
                skippedSteps   = [];
                completedSteps = [];
                cfg.steps.forEach(function (s) { stepData[s.key] = {}; });
                steps.forEach(function (s) {
                    s.classList.remove('m-wizard-step-done', 'm-wizard-step-skipped');
                });
                // Reset all Validator instances across all steps
                cfg.steps.forEach(function (stepCfg) {
                    if (stepCfg.validatorFormId) {
                        var vf = document.getElementById(stepCfg.validatorFormId);
                        if (vf && vf._mValidatorInstance) {
                            vf._mValidatorInstance.reset();
                        }
                    }
                });
                currentIndex = 0;
                clearErrors();
                showPanel(0);
                updateStepStrip(-1, 0);
                updateNavButtons(0);
            },
            getCurrentStep: function () { return getStepConfig(currentIndex); },
            getData:        function () { return buildPayload(); },
            setFieldValue:  function (fieldId, value) {
                var f = document.getElementById(fieldId)
                    || el.querySelector('[name="' + fieldId + '"]');
                if (f) setFieldValue(unwrapToInput(f), value);
            },
            getEl:     function () { return el; },
            getConfig: function () { return cfg; }
        };

        el._mWizardInstance = api;
        return api;
    };

})(window);
