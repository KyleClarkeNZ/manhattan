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

    m.toaster = (function() {
        const instances = {};

        function resolveContainer(id) {
            const containerId = id || 'mToaster';
            let el = document.getElementById(containerId);

            if (!el) {
                el = utils.createElement('div', 'm-toaster m-toaster-top-right');
                el.id = containerId;
                el.setAttribute('role', 'region');
                el.setAttribute('aria-live', 'polite');
                el.setAttribute('aria-atomic', 'true');
                document.body.appendChild(el);
            }

            if (!el.classList.contains('m-toaster')) {
                el.classList.add('m-toaster');
            }

            // Position from dataset if present (server-rendered Toaster component)
            const position = (el.dataset && el.dataset.position) ? el.dataset.position : null;
            if (position) {
                el.classList.remove(
                    'm-toaster-top-right',
                    'm-toaster-top-left',
                    'm-toaster-bottom-right',
                    'm-toaster-bottom-left',
                    'm-toaster-banner'
                );
                el.classList.add('m-toaster-' + position);
            }

            return el;
        }

        function iconForType(type) {
            switch (type) {
                case 'success':
                    return 'fa-check-circle';
                case 'error':
                    return 'fa-exclamation-circle';
                case 'warning':
                    return 'fa-exclamation-triangle';
                default:
                    return 'fa-info-circle';
            }
        }

        function alertClassForType(type) {
            switch (type) {
                case 'success':
                    return 'alert-success';
                case 'warning':
                    return 'alert-warning';
                case 'error':
                    return 'alert-error';
                default:
                    return 'alert-info';
            }
        }

        return function(id, options) {
            const containerId = (typeof id === 'string') ? id : 'mToaster';
            const opts = (typeof id === 'object' && id !== null) ? id : (options || {});

            if (instances[containerId]) {
                return instances[containerId];
            }

            const container = resolveContainer(containerId);
            const containerPosition = (container.dataset && container.dataset.position) ? container.dataset.position : null;
            const isBanner = containerPosition === 'banner' || container.classList.contains('m-toaster-banner');
            const defaultDuration = typeof opts.duration === 'number' ? opts.duration : (isBanner ? 0 : 5000);

            function show(message, type, toastOptions) {
                const tType = (typeof type === 'string' && type !== '') ? type : 'info';
                const tOpts = (typeof type === 'object' && type !== null) ? type : (toastOptions || {});
                const duration = typeof tOpts.duration === 'number' ? tOpts.duration : defaultDuration;

                const currentPosition = (container.dataset && container.dataset.position) ? container.dataset.position : null;
                const bannerMode = currentPosition === 'banner' || container.classList.contains('m-toaster-banner');

                if (bannerMode) {
                    const replace = (typeof tOpts.replace === 'boolean') ? tOpts.replace : true;
                    if (replace) {
                        while (container.firstChild) {
                            container.removeChild(container.firstChild);
                        }
                    }

                    const alertEl = utils.createElement('div', 'alert ' + alertClassForType(tType));
                    const icon = iconForType(tType);

                    const iconEl = utils.createElement('i', 'fas ' + icon);
                    iconEl.setAttribute('aria-hidden', 'true');

                    const messageEl = utils.createElement('div', 'alert-message');
                    messageEl.textContent = String(message || '');

                    alertEl.appendChild(iconEl);
                    alertEl.appendChild(messageEl);

                    function removeBanner() {
                        if (alertEl && alertEl.parentNode) {
                            alertEl.parentNode.removeChild(alertEl);
                        }
                    }

                    container.appendChild(alertEl);

                    if (duration > 0) {
                        setTimeout(removeBanner, duration);
                    }

                    return alertEl;
                }

                const toast = utils.createElement('div', 'm-toast m-toast-' + tType);
                const icon = iconForType(tType);
                toast.innerHTML = `
                    <div class="m-toast-icon"><i class="fas ${icon}" aria-hidden="true"></i></div>
                    <div class="m-toast-message"></div>
                    <button type="button" class="m-toast-close" aria-label="Close">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                `;

                const msgEl = toast.querySelector('.m-toast-message');
                if (msgEl) {
                    msgEl.textContent = String(message || '');
                }

                const closeBtn = toast.querySelector('.m-toast-close');
                function removeToast() {
                    toast.classList.remove('m-toast-show');
                    toast.classList.add('m-toast-hide');
                    setTimeout(() => {
                        if (toast.parentNode) toast.parentNode.removeChild(toast);
                    }, 200);
                }

                if (closeBtn) {
                    closeBtn.addEventListener('click', removeToast);
                }

                container.appendChild(toast);
                requestAnimationFrame(() => toast.classList.add('m-toast-show'));

                if (duration > 0) {
                    setTimeout(removeToast, duration);
                }

                return toast;
            }

            function clearAll() {
                while (container.firstChild) {
                    container.removeChild(container.firstChild);
                }
            }

            function hide(toastEl) {
                if (!toastEl || !toastEl.parentNode) return;
                if (toastEl.classList.contains('m-toast')) {
                    toastEl.classList.remove('m-toast-show');
                    toastEl.classList.add('m-toast-hide');
                    setTimeout(function() {
                        if (toastEl.parentNode) toastEl.parentNode.removeChild(toastEl);
                    }, 200);
                } else {
                    toastEl.parentNode.removeChild(toastEl);
                }
            }

            instances[containerId] = {
                element: container,
                show: show,
                clearAll: clearAll,
                hide: hide
            };

            return instances[containerId];
        };
    })();


})(window);
