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

    m.dialog = {
        /**
         * Show an alert dialog
         * @param {string} message - The message to display
         * @param {string} title - The dialog title (default: "Alert")
         * @param {string} icon - Optional Font Awesome icon class
         * @returns {Promise<void>}
         */
        alert: function(message, title = 'Alert', icon = 'fa-info-circle') {
            return new Promise((resolve) => {
                const dialog = this._createDialog({
                    title: title,
                    message: message,
                    icon: icon,
                    buttons: [
                        { text: 'OK', type: 'primary', action: () => resolve() }
                    ]
                });
                
                this._showDialog(dialog);
            });
        },

        /**
         * Show a confirm dialog
         * @param {string} message - The message to display
         * @param {string} title - The dialog title (default: "Confirm")
         * @param {string} icon - Optional Font Awesome icon class
         * @returns {Promise<boolean>}
         */
        confirm: function(message, title = 'Confirm', icon = 'fa-question-circle') {
            return new Promise((resolve) => {
                const dialog = this._createDialog({
                    title: title,
                    message: message,
                    icon: icon,
                    buttons: [
                        { text: 'Cancel', type: 'secondary', action: () => resolve(false) },
                        { text: 'OK', type: 'primary', action: () => resolve(true) }
                    ]
                });
                
                this._showDialog(dialog);
            });
        },

        /**
         * Show a prompt dialog
         * @param {string} message - The message to display
         * @param {string} defaultValue - Default input value
         * @param {string} title - The dialog title (default: "Prompt")
         * @param {string} icon - Optional Font Awesome icon class
         * @returns {Promise<string|null>}
         */
        prompt: function(message, defaultValue = '', title = 'Prompt', icon = 'fa-edit') {
            return new Promise((resolve) => {
                const inputId = 'm-dialog-input-' + Date.now();
                const dialog = this._createDialog({
                    title: title,
                    message: message,
                    icon: icon,
                    input: { id: inputId, value: defaultValue },
                    buttons: [
                        { text: 'Cancel', type: 'secondary', action: () => resolve(null) },
                        { text: 'OK', type: 'primary', action: () => {
                            const input = document.getElementById(inputId);
                            resolve(input ? input.value : null);
                        }}
                    ]
                });
                
                this._showDialog(dialog);
                
                // Focus input after showing
                setTimeout(() => {
                    const input = document.getElementById(inputId);
                    if (input) {
                        input.focus();
                        input.select();
                    }
                }, 100);
            });
        },

        /**
         * Create dialog HTML
         * @private
         */
        _createDialog: function(config) {
            const overlay = document.createElement('div');
            overlay.className = 'm-dialog-overlay';
            
            const dialog = document.createElement('div');
            dialog.className = 'm-dialog';
            
            // Header
            const header = document.createElement('div');
            header.className = 'm-dialog-header';
            
            if (config.icon) {
                const iconEl = document.createElement('i');
                iconEl.className = 'fas ' + config.icon + ' m-dialog-icon';
                header.appendChild(iconEl);
            }
            
            const titleEl = document.createElement('h3');
            titleEl.textContent = config.title;
            header.appendChild(titleEl);
            
            // Body
            const body = document.createElement('div');
            body.className = 'm-dialog-body';
            
            const messageEl = document.createElement('p');
            messageEl.textContent = config.message;
            body.appendChild(messageEl);
            
            // Input for prompt
            if (config.input) {
                const input = document.createElement('input');
                input.type = 'text';
                input.id = config.input.id;
                input.className = 'm-dialog-input';
                input.value = config.input.value || '';
                body.appendChild(input);
                
                // Handle Enter key
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        const okButton = config.buttons.find(b => b.type === 'primary');
                        if (okButton) {
                            okButton.action();
                            this._closeDialog(overlay);
                        }
                    }
                });
            }
            
            // Footer
            const footer = document.createElement('div');
            footer.className = 'm-dialog-footer';
            
            config.buttons.forEach(btnConfig => {
                const button = document.createElement('button');
                button.className = 'm-button m-button-' + btnConfig.type;
                button.textContent = btnConfig.text;
                button.addEventListener('click', () => {
                    btnConfig.action();
                    this._closeDialog(overlay);
                });
                footer.appendChild(button);
            });
            
            dialog.appendChild(header);
            dialog.appendChild(body);
            dialog.appendChild(footer);
            overlay.appendChild(dialog);
            
            // Close on ESC or overlay click
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    const cancelButton = config.buttons.find(b => b.type === 'secondary');
                    if (cancelButton) {
                        cancelButton.action();
                        this._closeDialog(overlay);
                    }
                }
            });
            
            return overlay;
        },

        /**
         * Show the dialog
         * @private
         */
        _showDialog: function(overlay) {
            document.body.appendChild(overlay);
            
            // Trigger reflow to enable animation
            overlay.offsetHeight;
            overlay.classList.add('m-dialog-active');
            
            // ESC key handler
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    const cancelButton = overlay.querySelector('.m-button-secondary');
                    if (cancelButton) {
                        cancelButton.click();
                    }
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
        },

        /**
         * Close the dialog
         * @private
         */
        _closeDialog: function(overlay) {
            overlay.classList.remove('m-dialog-active');
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 300);
        }
    };

    /**
     * Toaster Component
     * Usage:
     *   const t = m.toaster('appToaster');
     *   t.show('Saved!', 'success');
     */

})(window);
