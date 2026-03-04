/**
 * Manhattan Tabs Component
 * Handles tab switching, keyboard navigation, and events.
 */
(function() {
    'use strict';

    function initTabs() {
        var tabContainers = document.querySelectorAll('.m-tabs');

        for (var i = 0; i < tabContainers.length; i++) {
            initSingleTabs(tabContainers[i]);
        }
    }

    function initSingleTabs(container) {
        if (container.dataset.mInitialized) return;
        container.dataset.mInitialized = 'true';

        var tabs = container.querySelectorAll('.m-tabs-tab');
        var panels = container.querySelectorAll('.m-tabs-panel');

        // Click handler
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].addEventListener('click', function(e) {
                var tab = e.currentTarget;
                if (tab.classList.contains('m-disabled')) return;
                activateTab(container, tab, tabs, panels);
            });
        }

        // Keyboard navigation (Arrow Left/Right, Home, End)
        var strip = container.querySelector('.m-tabs-strip');
        if (strip) {
            strip.addEventListener('keydown', function(e) {
                var current = document.activeElement;
                if (!current || !current.classList.contains('m-tabs-tab')) return;

                var enabledTabs = getEnabledTabs(tabs);
                if (enabledTabs.length === 0) return;

                var idx = -1;
                for (var j = 0; j < enabledTabs.length; j++) {
                    if (enabledTabs[j] === current) { idx = j; break; }
                }

                var newIdx = -1;

                if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    newIdx = (idx + 1) % enabledTabs.length;
                } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    newIdx = (idx - 1 + enabledTabs.length) % enabledTabs.length;
                } else if (e.key === 'Home') {
                    e.preventDefault();
                    newIdx = 0;
                } else if (e.key === 'End') {
                    e.preventDefault();
                    newIdx = enabledTabs.length - 1;
                }

                if (newIdx >= 0 && enabledTabs[newIdx]) {
                    enabledTabs[newIdx].focus();
                    activateTab(container, enabledTabs[newIdx], tabs, panels);
                }
            });
        }
    }

    function activateTab(container, tab, allTabs, allPanels) {
        var key = tab.getAttribute('data-tab-key');
        if (!key) return;

        // Deactivate all tabs
        for (var i = 0; i < allTabs.length; i++) {
            allTabs[i].classList.remove('m-active');
            allTabs[i].setAttribute('aria-selected', 'false');
        }

        // Hide all panels
        for (var j = 0; j < allPanels.length; j++) {
            allPanels[j].setAttribute('hidden', '');
        }

        // Activate selected
        tab.classList.add('m-active');
        tab.setAttribute('aria-selected', 'true');

        var panel = container.querySelector('.m-tabs-panel[data-tab-key="' + key + '"]');
        if (panel) {
            panel.removeAttribute('hidden');
        }

        // Fire custom event
        if (window.m && window.m.utils && window.m.utils.trigger) {
            window.m.utils.trigger(container, 'm-tab-change', { key: key, tab: tab, panel: panel });
        }
    }

    function getEnabledTabs(tabs) {
        var result = [];
        for (var i = 0; i < tabs.length; i++) {
            if (!tabs[i].classList.contains('m-disabled')) {
                result.push(tabs[i]);
            }
        }
        return result;
    }

    // Register on m namespace
    if (window.m) {
        window.m.tabs = function(idOrEl, options) {
            var el = (typeof idOrEl === 'string')
                ? document.getElementById(idOrEl)
                : idOrEl;
            if (!el) return null;

            initSingleTabs(el);

            var api = {
                element: el,

                /** Programmatically select a tab by key */
                selectTab: function(key) {
                    var tabs = el.querySelectorAll('.m-tabs-tab');
                    var panels = el.querySelectorAll('.m-tabs-panel');
                    var tab = el.querySelector('.m-tabs-tab[data-tab-key="' + key + '"]');
                    if (tab && !tab.classList.contains('m-disabled')) {
                        activateTab(el, tab, tabs, panels);
                    }
                    return api;
                },

                /** Replace content of a tab panel by key */
                setContent: function(key, html) {
                    var panel = el.querySelector('.m-tabs-panel[data-tab-key="' + key + '"]');
                    if (panel) {
                        panel.innerHTML = html;
                    }
                    return api;
                },

                /** Load tab content from a URL via AJAX */
                refreshContent: function(key, url, opts) {
                    var panel = el.querySelector('.m-tabs-panel[data-tab-key="' + key + '"]');
                    if (!panel) return Promise.resolve(null);

                    if (window.m && window.m.ajax) {
                        return window.m.ajax(url, window.m.utils.extend({
                            method: 'GET'
                        }, opts || {})).then(function(resp) {
                            var html = (typeof resp === 'string') ? resp : (resp && resp.html ? resp.html : '');
                            panel.innerHTML = html;
                            if (window.m.utils && window.m.utils.trigger) {
                                window.m.utils.trigger(el, 'm-tab-content-refresh', { key: key, panel: panel });
                            }
                            return resp;
                        });
                    }

                    return fetch(url).then(function(r) { return r.text(); }).then(function(html) {
                        panel.innerHTML = html;
                        return html;
                    });
                },

                /** Get the currently active tab key */
                getActiveTab: function() {
                    var active = el.querySelector('.m-tabs-tab.m-active');
                    return active ? active.getAttribute('data-tab-key') : null;
                }
            };

            // Store API on element for easy access
            el.manhattanTabs = api;

            return api;
        };
    }

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTabs);
    } else {
        initTabs();
    }
})();
