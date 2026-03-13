/**
 * Manhattan Accordion Component
 */
(function(window) {
    'use strict';
    const m = window.m;
    const utils = m.utils;

    m.accordion = function(id, options) {
        const el = utils.getElement(id);
        if (!el) return null;

        options = options || {};

        const animated = el.getAttribute('data-m-animated') === 'true';
        const allowMultiple = el.getAttribute('data-m-multiple') === 'true';

        const panels = el.querySelectorAll('.m-accordion-panel');
        const headers = el.querySelectorAll('.m-accordion-header');

        /**
         * Open a panel
         */
        function open(index) {
            if (index < 0 || index >= panels.length) return;

            const panel = panels[index];
            const header = headers[index];
            const content = panel.querySelector('.m-accordion-content');

            if (!content) return;

            // Close other panels if not allowing multiple
            if (!allowMultiple) {
                for (let i = 0; i < panels.length; i++) {
                    if (i !== index) {
                        close(i);
                    }
                }
            }

            panel.classList.add('m-accordion-panel--open');
            header.setAttribute('aria-expanded', 'true');

            if (animated) {
                // Smooth animation
                content.style.display = 'block';
                const targetHeight = content.scrollHeight;
                content.style.height = '0';
                
                // Force reflow
                content.offsetHeight;
                
                content.style.transition = 'height 0.3s ease';
                content.style.height = targetHeight + 'px';
                
                setTimeout(function() {
                    content.style.height = '';
                    content.style.transition = '';
                }, 300);
            } else {
                content.style.display = 'block';
            }

            utils.trigger(el, 'm:accordion:opened', { index: index, panel: panel });
        }

        /**
         * Close a panel
         */
        function close(index) {
            if (index < 0 || index >= panels.length) return;

            const panel = panels[index];
            const header = headers[index];
            const content = panel.querySelector('.m-accordion-content');

            if (!content) return;

            panel.classList.remove('m-accordion-panel--open');
            header.setAttribute('aria-expanded', 'false');

            if (animated) {
                const currentHeight = content.scrollHeight;
                content.style.height = currentHeight + 'px';
                
                // Force reflow
                content.offsetHeight;
                
                content.style.transition = 'height 0.3s ease';
                content.style.height = '0';
                
                setTimeout(function() {
                    content.style.display = 'none';
                    content.style.height = '';
                    content.style.transition = '';
                }, 300);
            } else {
                content.style.display = 'none';
            }

            utils.trigger(el, 'm:accordion:closed', { index: index, panel: panel });
        }

        /**
         * Toggle a panel
         */
        function toggle(index) {
            if (index < 0 || index >= panels.length) return;

            const panel = panels[index];
            if (panel.classList.contains('m-accordion-panel--open')) {
                close(index);
            } else {
                open(index);
            }
        }

        /**
         * Get currently open panel indices
         */
        function getOpen() {
            const openIndices = [];
            for (let i = 0; i < panels.length; i++) {
                if (panels[i].classList.contains('m-accordion-panel--open')) {
                    openIndices.push(i);
                }
            }
            return openIndices;
        }

        // Initialize event handlers
        for (let i = 0; i < headers.length; i++) {
            (function(index) {
                const header = headers[index];
                
                header.addEventListener('click', function() {
                    toggle(index);
                });

                // Keyboard navigation
                header.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggle(index);
                    } else if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const nextIndex = (index + 1) % headers.length;
                        headers[nextIndex].focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        const prevIndex = (index - 1 + headers.length) % headers.length;
                        headers[prevIndex].focus();
                    } else if (e.key === 'Home') {
                        e.preventDefault();
                        headers[0].focus();
                    } else if (e.key === 'End') {
                        e.preventDefault();
                        headers[headers.length - 1].focus();
                    }
                });
            })(i);
        }

        return {
            open: open,
            close: close,
            toggle: toggle,
            getOpen: getOpen
        };
    };

    // Auto-initialize accordions
    utils.ready(function() {
        const accordions = document.querySelectorAll('.m-accordion');
        for (let i = 0; i < accordions.length; i++) {
            const accordion = accordions[i];
            if (accordion.id) {
                m.accordion(accordion.id);
            }
        }
    });

})(window);
