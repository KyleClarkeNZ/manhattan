/**
 * Manhattan ToggleSwitch Component
 * Enhanced toggle switches with labels and state management
 */
(function() {
    'use strict';

    function initToggleSwitches() {
        const switches = document.querySelectorAll('.m-toggle-switch');
        
        switches.forEach(switchEl => {
            if (switchEl.dataset.initialized) return;
            switchEl.dataset.initialized = 'true';
            
            const input = switchEl.querySelector('input[type="checkbox"]');
            const slider = switchEl.querySelector('.m-switch-slider');
            const stateLabel = switchEl.querySelector('.m-switch-state-label');
            
            if (!input) return;
            
            // Update state label on change
            if (stateLabel) {
                updateStateLabel(switchEl, input.checked);
                
                input.addEventListener('change', function() {
                    updateStateLabel(switchEl, this.checked);
                });
            }
            
            // Keyboard accessibility
            switchEl.addEventListener('keydown', function(e) {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    input.click();
                }
            });
        });
    }
    
    function updateStateLabel(switchEl, isChecked) {
        const stateLabel = switchEl.querySelector('.m-switch-state-label');
        if (!stateLabel) return;
        
        const onLabel = switchEl.dataset.onLabel || 'On';
        const offLabel = switchEl.dataset.offLabel || 'Off';
        
        stateLabel.textContent = isChecked ? onLabel : offLabel;
        
        if (isChecked) {
            stateLabel.classList.add('m-switch-on');
            stateLabel.classList.remove('m-switch-off');
        } else {
            stateLabel.classList.add('m-switch-off');
            stateLabel.classList.remove('m-switch-on');
        }
    }
    
    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initToggleSwitches);
    } else {
        initToggleSwitches();
    }
    
    // Expose API
    window.Manhattan = window.Manhattan || {};
    window.Manhattan.ToggleSwitch = {
        init: initToggleSwitches
    };
})();
