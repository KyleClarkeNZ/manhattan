/**
 * Manhattan ToggleSwitch Component
 *
 * The markup is a wrapping <label class="m-switch"> around a visually-hidden
 * native checkbox, so clicking and keyboard operation are handled by the
 * browser and the on/off state labels are swapped by CSS sibling selectors.
 * The only thing that needs script is keeping aria-checked in step with the
 * checkbox — ToggleSwitch.php renders it once, at its initial value, and it
 * would otherwise report the wrong state to assistive technology for the whole
 * life of the page.
 *
 * This used to query '.m-toggle-switch', a class ToggleSwitch.php has never
 * rendered (it renders .m-switch-wrapper / .m-switch), so none of it ever ran.
 * It also bound its own keydown handler that called input.click() — on the real
 * markup that would double-toggle, because Space on a focused checkbox already
 * toggles it natively.
 */
(function() {
    'use strict';

    var WRAPPER_SELECTOR = '.m-switch-wrapper, .m-toggle-switch';

    function initToggleSwitches(root) {
        var scope = root || document;
        var switches = scope.querySelectorAll(WRAPPER_SELECTOR);

        Array.prototype.forEach.call(switches, function(switchEl) {
            if (switchEl.dataset.mSwitchInit) return;
            switchEl.dataset.mSwitchInit = 'true';

            var input = switchEl.querySelector('input[type="checkbox"]');
            if (!input) return;

            syncState(switchEl, input);

            input.addEventListener('change', function() {
                syncState(switchEl, input);
            });
        });
    }

    function syncState(switchEl, input) {
        // Keep the ARIA state honest — PHP only stamps the initial value.
        if (input.hasAttribute('role')) {
            input.setAttribute('aria-checked', input.checked ? 'true' : 'false');
        }

        switchEl.classList.toggle('m-switch-checked', input.checked);

        // Legacy single-span markup: one .m-switch-state-label whose text is
        // swapped between data-on-label / data-off-label. The current markup
        // renders two spans (.m-switch-state-on / .m-switch-state-off) and lets
        // CSS hide the inactive one, so leave those alone.
        var stateLabels = switchEl.querySelectorAll('.m-switch-state-label');
        if (stateLabels.length !== 1) return;

        var stateLabel = stateLabels[0];
        if (stateLabel.classList.contains('m-switch-state-on')
            || stateLabel.classList.contains('m-switch-state-off')) {
            return;
        }

        stateLabel.textContent = input.checked
            ? (switchEl.dataset.onLabel || 'On')
            : (switchEl.dataset.offLabel || 'Off');

        stateLabel.classList.toggle('m-switch-on', input.checked);
        stateLabel.classList.toggle('m-switch-off', !input.checked);
    }

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { initToggleSwitches(); });
    } else {
        initToggleSwitches();
    }

    // Expose API. `init(root)` can be called again after injecting markup.
    window.Manhattan = window.Manhattan || {};
    window.Manhattan.ToggleSwitch = {
        init: initToggleSwitches
    };

    if (window.m) {
        window.m.toggleSwitch = initToggleSwitches;
    }
})();
