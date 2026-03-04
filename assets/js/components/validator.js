/**
 * Manhattan Validator Component
 * Client-side form validation without HTML5 native validation
 * Prevents layout shifts with inline error messages
 */

(function(window) {
    'use strict';
    
    const Validator = function(config) {
        this.formId = config.formId;
        this.fields = config.fields || {};
        this.onSubmitCallback = config.onSubmit;
        this.validateOnBlur = config.validateOnBlur !== false;
        this.validateOnInput = config.validateOnInput === true;
        
        this.form = document.getElementById(this.formId);
        if (!this.form) {
            console.error(`Manhattan Validator: Form with ID "${this.formId}" not found`);
            return;
        }
        
        console.log(`Manhattan Validator initialized for form: ${this.formId}`);
        this.init();
    };
    
    Validator.prototype.init = function() {
        // Disable HTML5 validation
        this.form.setAttribute('novalidate', 'novalidate');
        
        // Setup field validation
        for (const fieldName in this.fields) {
            this.setupFieldValidation(fieldName);
        }
        
        // Setup form submit handler
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
    };
    
    Validator.prototype.setupFieldValidation = function(fieldName) {
        // Try to find field by ID first, then by name attribute
        let field = document.getElementById(fieldName);
        if (!field) {
            field = this.form.querySelector(`[name="${fieldName}"]`);
        }
        
        if (!field) {
            console.warn(`Manhattan Validator: Field "${fieldName}" not found in form`);
            return;
        }
        
        // For Manhattan components, check if field is a wrapper or the actual input
        let targetInput = field;
        
        // If it's a wrapper, find the input inside
        if (field.classList.contains('m-textbox-wrapper') || 
            field.classList.contains('m-dropdown-wrapper') || 
            field.classList.contains('m-datepicker-wrapper')) {
            const input = field.querySelector('input, select, textarea');
            if (input) {
                targetInput = input;
            }
        }
        // If it's already an input element, use it directly
        else if (field.tagName === 'INPUT' || field.tagName === 'SELECT' || field.tagName === 'TEXTAREA') {
            targetInput = field;
        }
        
        // Add event listeners to the actual input
        if (this.validateOnBlur) {
            targetInput.addEventListener('blur', () => this.validateField(fieldName));
        }
        
        if (this.validateOnInput) {
            targetInput.addEventListener('input', () => this.validateField(fieldName));
        }
    };
    
    Validator.prototype.validateField = function(fieldName) {
        // Try to find field by ID first, then by name attribute
        let field = document.getElementById(fieldName);
        if (!field) {
            field = this.form.querySelector(`[name="${fieldName}"]`);
        }
        
        if (!field) {
            return true;
        }
        
        const config = this.fields[fieldName];
        
        // For Manhattan components, check if field is a wrapper or the actual input
        let targetInput = field;
        
        // If it's a wrapper, find the input inside
        if (field.classList.contains('m-textbox-wrapper') || 
            field.classList.contains('m-dropdown-wrapper') || 
            field.classList.contains('m-datepicker-wrapper')) {
            const input = field.querySelector('input, select, textarea');
            if (input) {
                targetInput = input;
            }
        }
        // If it's already an input element, use it directly
        else if (field.tagName === 'INPUT' || field.tagName === 'SELECT' || field.tagName === 'TEXTAREA') {
            targetInput = field;
        }
        
        const value = targetInput.value.trim();
        const rules = config.rules;
        
        // Clear previous error
        this.clearError(field, targetInput);
        
        // Check required
        if (rules.includes('required') && !value) {
            this.showError(field, targetInput, config.message);
            return false;
        }
        
        // If field is empty and not required, consider valid
        if (!value && !rules.includes('required')) {
            this.clearError(field, targetInput);
            return true;
        }
        
        // Check email
        if (rules.includes('email')) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showError(field, targetInput, config.message);
                return false;
            }
        }
        
        // Check minLength
        const minLength = rules.find(r => typeof r === 'object' && r.minLength !== undefined);
        if (minLength && value.length < minLength.minLength) {
            this.showError(field, targetInput, config.message);
            return false;
        }
        
        // Check maxLength
        const maxLength = rules.find(r => typeof r === 'object' && r.maxLength !== undefined);
        if (maxLength && value.length > maxLength.maxLength) {
            this.showError(field, targetInput, config.message);
            return false;
        }
        
        // Check number validations
        const numericValue = parseFloat(value);
        const isNumericField = targetInput.type === 'number';
        
        // Check min (for numbers)
        const min = rules.find(r => typeof r === 'object' && r.min !== undefined);
        if (min !== undefined && isNumericField && !isNaN(numericValue)) {
            if (numericValue < min.min) {
                this.showError(field, targetInput, config.message);
                return false;
            }
        }
        
        // Check max (for numbers)
        const max = rules.find(r => typeof r === 'object' && r.max !== undefined);
        if (max !== undefined && isNumericField && !isNaN(numericValue)) {
            if (numericValue > max.max) {
                this.showError(field, targetInput, config.message);
                return false;
            }
        }
        
        // Check integer validation
        if (rules.includes('integer') && isNumericField) {
            if (!Number.isInteger(numericValue)) {
                this.showError(field, targetInput, config.message);
                return false;
            }
        }
        
        // Check positive number
        if (rules.includes('positive') && isNumericField) {
            if (numericValue <= 0) {
                this.showError(field, targetInput, config.message);
                return false;
            }
        }
        
        // Check pattern
        const pattern = rules.find(r => typeof r === 'object' && r.pattern !== undefined);
        if (pattern) {
            const regex = new RegExp(pattern.pattern);
            if (!regex.test(value)) {
                this.showError(field, targetInput, config.message);
                return false;
            }
        }
        
        // Check custom validator
        const custom = rules.find(r => typeof r === 'object' && r.custom !== undefined);
        if (custom && typeof custom.custom === 'function') {
            if (!custom.custom(value, targetInput)) {
                this.showError(field, targetInput, config.message);
                return false;
            }
        }
        
        // All validations passed
        this.clearError(field, targetInput);
        return true;
    };
    
    Validator.prototype.showError = function(fieldElement, inputElement, message) {
        // Add invalid class to the actual input
        inputElement.classList.add('m-validator-invalid');
        
        // Determine where to insert the error message
        // If fieldElement is a wrapper, insert after it. Otherwise, insert after the input's parent container
        let insertAfter = fieldElement;
        
        // If the field is the input itself, find the parent form-group
        if (fieldElement === inputElement) {
            const formGroup = inputElement.closest('.form-group');
            if (formGroup) {
                insertAfter = formGroup.querySelector('input, select, textarea') || inputElement;
            }
        }
        
        // Find or create error message element
        let errorMsg = insertAfter.parentNode.querySelector('.m-validator-error');
        
        // Only create if it doesn't exist
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'm-validator-error';
            
            // Insert after the input element
            if (insertAfter.nextSibling) {
                insertAfter.parentNode.insertBefore(errorMsg, insertAfter.nextSibling);
            } else {
                insertAfter.parentNode.appendChild(errorMsg);
            }
        }
        
        errorMsg.textContent = message;
        errorMsg.style.display = 'flex';
    };
    
    Validator.prototype.clearError = function(fieldElement, inputElement) {
        // Remove invalid class
        inputElement.classList.remove('m-validator-invalid');
        
        // Find the parent form-group or use the field's parent
        let searchParent = fieldElement.parentNode;
        if (fieldElement === inputElement) {
            const formGroup = inputElement.closest('.form-group');
            if (formGroup) {
                searchParent = formGroup;
            }
        }
        
        // Find and hide error message
        const errorMsg = searchParent.querySelector('.m-validator-error');
        
        if (errorMsg) {
            errorMsg.style.display = 'none';
            errorMsg.textContent = '';
        }
    };
    
    Validator.prototype.handleSubmit = function(event) {
        // If another handler (e.g. jQuery AJAX) has already taken over this submit event,
        // we still run validation for UI feedback but must NOT call form.submit() afterwards —
        // that would trigger a native page navigation that would race against (or cancel) the
        // pending AJAX request.
        var alreadyHandled = event.defaultPrevented;

        event.preventDefault();
        
        let isValid = true;
        
        // Validate all fields
        for (const fieldName in this.fields) {
            if (!this.validateField(fieldName)) {
                isValid = false;
            }
        }
        
        if (isValid) {
            // Execute callback if provided
            if (this.onSubmitCallback) {
                if (typeof this.onSubmitCallback === 'function') {
                    this.onSubmitCallback(event);
                } else if (typeof this.onSubmitCallback === 'string') {
                    // Execute as JavaScript code
                    try {
                        const func = new Function('event', this.onSubmitCallback);
                        func.call(this.form, event);
                    } catch (error) {
                        console.error('Manhattan Validator: Error executing onSubmit callback', error);
                    }
                }
            } else if (!alreadyHandled) {
                // Default submit behavior — only when no other handler has already taken over.
                // IMPORTANT: Preserve the submit button's name/value when programmatically submitting
                // because form.submit() doesn't include button data
                const submitButton = event.submitter || this.form.querySelector('button[type="submit"]');
                if (submitButton && submitButton.name && submitButton.name !== '') {
                    // Add hidden input to preserve button name
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = submitButton.name;
                    hiddenInput.value = submitButton.value || '';
                    this.form.appendChild(hiddenInput);
                }
                
                this.form.submit();
            }
            // else: alreadyHandled — the AJAX/custom handler takes care of the actual submission;
            // we only ran validation here for field-level UI feedback.
        } else {
            // Focus first invalid field
            const firstInvalid = this.form.querySelector('.m-validator-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    };
    
    Validator.prototype.reset = function() {
        for (const fieldName in this.fields) {
            // Try to find field by ID first, then by name attribute
            let field = document.getElementById(fieldName);
            if (!field) {
                field = this.form.querySelector(`[name="${fieldName}"]`);
            }
            
            if (field) {
                // For Manhattan components, check if field is a wrapper or the actual input
                let targetInput = field;
                
                // If it's a wrapper, find the input inside
                if (field.classList.contains('m-textbox-wrapper') || 
                    field.classList.contains('m-dropdown-wrapper') || 
                    field.classList.contains('m-datepicker-wrapper')) {
                    const input = field.querySelector('input, select, textarea');
                    if (input) {
                        targetInput = input;
                    }
                }
                // If it's already an input element, use it directly
                else if (field.tagName === 'INPUT' || field.tagName === 'SELECT' || field.tagName === 'TEXTAREA') {
                    targetInput = field;
                }
                
                this.clearError(field, targetInput);
            }
        }
    };
    
    // Export to Manhattan namespace
    if (!window.m) {
        window.m = {};
    }
    
    window.m.validator = function(config) {
        return new Validator(config);
    };
    
})(window);
