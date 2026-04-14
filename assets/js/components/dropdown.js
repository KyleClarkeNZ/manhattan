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

    m.dropdown = function(id, options) {
        let select = utils.getElement(id);
        
        if (!select) {
            console.warn('Manhattan: Dropdown element not found:', id);
            return null;
        }

        // Idempotent init: if we've already initialized this select, reuse it.
        if (select._manhattanDropdownInstance) {
            if (options && typeof select._manhattanDropdownInstance.configure === 'function') {
                select._manhattanDropdownInstance.configure(options);
            }
            return select._manhattanDropdownInstance;
        }

        const dataset = select.dataset || {};

        options = utils.extend({
            textField: 'text',
            valueField: 'value',
            dataSource: null,
            placeholder: select.querySelector('option[value=""]')?.textContent || 'Select...',

            // Remote loading
            remoteUrl: dataset.remoteUrl || null,
            remoteMethod: dataset.remoteMethod || 'GET',
            autoLoadRemote: dataset.remoteAutoload !== '0',
            useLoader: dataset.useLoader !== '0',
            loaderText: dataset.loaderText || 'Loading...'
        }, options || {});

        // Get existing options as dataSource if not provided
        if (!options.dataSource && select.options.length > 0) {
            options.dataSource = Array.from(select.options).map(opt => ({
                [options.valueField]: opt.value,
                [options.textField]: opt.textContent
            }));
        }

        // Create custom dropdown
        const wrapper = createDropdownWrapper(select);

        // If wrapper already has a custom dropdown for this select (e.g., double init), reuse it.
        const existingCustom = wrapper.querySelector('.m-dropdown-custom');
        if (existingCustom && existingCustom._manhattan && existingCustom._manhattan.originalSelect === select && existingCustom._manhattan.instance) {
            select._manhattanDropdownInstance = existingCustom._manhattan.instance;
            return existingCustom._manhattan.instance;
        }

        const customDropdown = createCustomDropdown(select, wrapper, options);
        
        // Store component data
        customDropdown._manhattan = {
            type: 'dropdown',
            options: options,
            originalSelect: select,
            isOpen: false
        };

        setupDropdownEvents(customDropdown, wrapper, options);

        if (options.remoteUrl && options.autoLoadRemote) {
            loadRemoteData(customDropdown, wrapper, options);
        }

        const api = {
            element: customDropdown,
            configure: function(nextOptions) {
                if (!nextOptions) return this;
                // Shallow-merge into the existing options object so closures keep seeing updates.
                options = utils.extend(options, nextOptions);
                customDropdown._manhattan.options = options;
                if (nextOptions.dataSource) {
                    renderDropdownOptions(customDropdown, options);
                }
                return this;
            },
            
            value: function(val) {
                if (val === undefined) {
                    return customDropdown.getAttribute('data-value') || '';
                }
                setDropdownValue(customDropdown, val, options);
                return this;
            },
            
            text: function() {
                const valueSpan = customDropdown.querySelector('.m-dropdown-value');
                return valueSpan ? valueSpan.textContent : '';
            },
            
            dataSource: function(data) {
                if (data === undefined) {
                    return options.dataSource;
                }
                options.dataSource = data;
                renderDropdownOptions(customDropdown, options);
                return this;
            },

            reload: function() {
                return loadRemoteData(customDropdown, wrapper, options);
            },
            
            enable: function() {
                customDropdown.classList.remove('m-disabled');
                return this;
            },
            
            disable: function() {
                customDropdown.classList.add('m-disabled');
                closeDropdown(customDropdown);
                return this;
            },
            
            clear: function() {
                setDropdownValue(customDropdown, '', options);
                return this;
            }
        };

        customDropdown._manhattan.instance = api;
        select._manhattanDropdownInstance = api;
        return api;
    };

    function ensureLoader(wrapper, options) {
        let loader = wrapper.querySelector('.m-dropdown-loader');
        if (loader) return loader;

        loader = utils.createElement('div', 'm-loader m-loader-overlay m-loader-sm m-hidden m-dropdown-loader');
        loader.setAttribute('role', 'status');
        loader.setAttribute('aria-live', 'polite');
        loader.setAttribute('aria-busy', 'true');
        loader.innerHTML = '<span class="m-loader-spinner" aria-hidden="true"></span>' +
            '<span class="m-loader-text"></span>';

        const textEl = loader.querySelector('.m-loader-text');
        if (textEl) textEl.textContent = options.loaderText || 'Loading...';

        wrapper.appendChild(loader);
        return loader;
    }

    function setLoading(wrapper, options, isLoading) {
        if (!options.useLoader) return;
        const loader = ensureLoader(wrapper, options);
        loader.classList.toggle('m-hidden', !isLoading);
        wrapper.classList.toggle('m-is-loading', !!isLoading);
    }

    function loadRemoteData(dropdown, wrapper, options) {
        if (!options.remoteUrl) return;
        if (!m.ajax) {
            console.warn('Manhattan: m.ajax not available; cannot load dropdown remoteUrl');
            return;
        }

        return m.ajax(options.remoteUrl, {
            method: options.remoteMethod || 'GET',
            beforeSend: function() {
                setLoading(wrapper, options, true);
            },
            success: function(data) {
                if (Array.isArray(data)) {
                    options.dataSource = data;
                    renderDropdownOptions(dropdown, options);
                    // Ensure placeholder is shown until user selects
                    if (!dropdown.getAttribute('data-value')) {
                        const valueSpan = dropdown.querySelector('.m-dropdown-value');
                        if (valueSpan) {
                            valueSpan.textContent = options.placeholder;
                            valueSpan.classList.remove('m-has-value');
                        }
                    }
                }
            },
            error: function() {
                // Keep previous state; just log via m.ajax and stop loader
            },
            complete: function() {
                setLoading(wrapper, options, false);
            }
        });
    }

    function createDropdownWrapper(select) {
        let wrapper = select.parentElement;
        if (!wrapper.classList.contains('m-dropdown-wrapper')) {
            wrapper = utils.createElement('div', 'm-dropdown-wrapper');
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
        }
        return wrapper;
    }

    function createCustomDropdown(select, wrapper, options) {
        const custom = utils.createElement('div', 'm-dropdown-custom');
        custom.setAttribute('tabindex', '0');
        custom.setAttribute('data-value', select.value || '');
        
        const selectedText = select.selectedIndex >= 0 ? 
            select.options[select.selectedIndex].textContent : 
            options.placeholder;
        
        custom.innerHTML = `
            <div class="m-dropdown-header">
                <span class="m-dropdown-value">${selectedText}</span>
                <i class="fas fa-chevron-down m-dropdown-arrow"></i>
            </div>
            <div class="m-dropdown-list"></div>
        `;
        
        // Hide original select
        select.style.display = 'none';
        wrapper.insertBefore(custom, select);
        
        renderDropdownOptions(custom, options);
        
        return custom;
    }

    function renderDropdownOptions(dropdown, options) {
        const list = dropdown.querySelector('.m-dropdown-list');
        list.innerHTML = '';
        
        if (options.dataSource && options.dataSource.length > 0) {
            options.dataSource.forEach(item => {
                const value = typeof item === 'object' ? item[options.valueField] : item;
                const text = typeof item === 'object' ? item[options.textField] : item;
                
                const option = utils.createElement('div', 'm-dropdown-item');
                option.setAttribute('data-value', value);
                option.textContent = text;
                
                const currentValue = dropdown.getAttribute('data-value');
                if (String(value) === String(currentValue)) {
                    option.classList.add('m-selected');
                }
                
                option.addEventListener('click', function(e) {
                    e.stopPropagation();
                    setDropdownValue(dropdown, value, options);
                    closeDropdown(dropdown);
                });
                
                list.appendChild(option);
            });
        }
    }

    function setupDropdownEvents(dropdown, wrapper, options) {
        const header = dropdown.querySelector('.m-dropdown-header');
        const originalSelect = dropdown._manhattan?.originalSelect;
        
        // Listen for programmatic changes to the original select
        if (originalSelect) {
            originalSelect.addEventListener('change', function() {
                const newValue = this.value;
                // Update custom dropdown display without triggering another change event
                dropdown.setAttribute('data-value', newValue);
                
                const valueSpan = dropdown.querySelector('.m-dropdown-value');
                const selectedItem = Array.from(dropdown.querySelectorAll('.m-dropdown-item'))
                    .find(item => item.getAttribute('data-value') === String(newValue));
                
                if (valueSpan && selectedItem) {
                    valueSpan.textContent = selectedItem.textContent;
                    valueSpan.classList.add('m-has-value');
                }
                
                // Update selection styling
                dropdown.querySelectorAll('.m-dropdown-item').forEach(item => {
                    item.classList.toggle('m-selected', item.getAttribute('data-value') === String(newValue));
                });
            });
        }
        
        header.addEventListener('click', function(e) {
            e.stopPropagation();
            if (dropdown.classList.contains('m-disabled')) return;
            
            if (dropdown._manhattan.isOpen) {
                closeDropdown(dropdown);
            } else {
                openDropdown(dropdown);
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                closeDropdown(dropdown);
            }
        });

        // Keyboard support
        dropdown.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (dropdown._manhattan.isOpen) {
                    closeDropdown(dropdown);
                } else {
                    openDropdown(dropdown);
                }
            } else if (e.key === 'Escape') {
                closeDropdown(dropdown);
            } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                navigateOptions(dropdown, e.key === 'ArrowDown' ? 1 : -1, options);
            }
        });
    }

    function openDropdown(dropdown) {
        // Close any other open dropdowns so only one can be open at a time.
        document.querySelectorAll('.m-dropdown-custom.m-open').forEach(other => {
            if (other !== dropdown) {
                closeDropdown(other);
            }
        });

        dropdown.classList.add('m-open');
        dropdown._manhattan.isOpen = true;
        
        // Position the dropdown list
        const list = dropdown.querySelector('.m-dropdown-list');
        if (!list) return;

        // Measure and choose direction (down vs up) within nearest scroll container / viewport
        dropdown.classList.remove('m-open-up');
        dropdown.classList.remove('m-align-right');
        list.style.display = 'block';

        const triggerRect = dropdown.getBoundingClientRect();
        const listRect = list.getBoundingClientRect();
        const boundary = getBoundaryRect(dropdown);

        // Vertical positioning (up vs down)
        const spaceBelow = boundary.bottom - triggerRect.bottom;
        const spaceAbove = triggerRect.top - boundary.top;
        const needed = listRect.height;

        if (spaceBelow < needed && spaceAbove > spaceBelow) {
            dropdown.classList.add('m-open-up');
        }

        // Horizontal positioning (check right edge)
        const spaceRight = boundary.right - triggerRect.left;
        if (spaceRight < listRect.width) {
            dropdown.classList.add('m-align-right');
        }
    }

    function closeDropdown(dropdown) {
        dropdown.classList.remove('m-open');
        dropdown.classList.remove('m-open-up');
        dropdown.classList.remove('m-align-right');
        if (dropdown._manhattan) {
            dropdown._manhattan.isOpen = false;
        }
        
        const list = dropdown.querySelector('.m-dropdown-list');
        if (list) list.style.display = 'none';
    }

    function getBoundaryRect(el) {
        const vh = window.innerHeight || document.documentElement.clientHeight;
        const vw = window.innerWidth || document.documentElement.clientWidth;

        let p = el.parentElement;
        while (p && p !== document.body) {
            const style = window.getComputedStyle(p);
            const overflowY = style.overflowY;
            const overflowX = style.overflowX;
            const overflow = (overflowY || '') + ' ' + (overflowX || '');

            if (/(auto|scroll|hidden)/.test(overflow)) {
                return p.getBoundingClientRect();
            }
            p = p.parentElement;
        }

        return { top: 0, left: 0, right: vw, bottom: vh };
    }

    function setDropdownValue(dropdown, value, options) {
        dropdown.setAttribute('data-value', value);
        
        // Update display
        const valueSpan = dropdown.querySelector('.m-dropdown-value');
        const selectedItem = Array.from(dropdown.querySelectorAll('.m-dropdown-item'))
            .find(item => item.getAttribute('data-value') === String(value));
        
        if (valueSpan) {
            if (selectedItem) {
                valueSpan.textContent = selectedItem.textContent;
                valueSpan.classList.add('m-has-value');
            } else {
                valueSpan.textContent = options.placeholder;
                valueSpan.classList.remove('m-has-value');
            }
        }
        
        // Update selection styling
        dropdown.querySelectorAll('.m-dropdown-item').forEach(item => {
            item.classList.toggle('m-selected', item.getAttribute('data-value') === String(value));
        });
        
        // Update original select
        const originalSelect = dropdown._manhattan?.originalSelect;
        if (originalSelect) {
            originalSelect.value = value;
            utils.trigger(originalSelect, 'change', {
                value: value,
                text: valueSpan ? valueSpan.textContent : ''
            });
        }

        // Fire m:dropdown:change on the custom wrapper element so callers can listen with addEventListener.
        utils.trigger(dropdown, 'm:dropdown:change', {
            value: value,
            text: valueSpan ? valueSpan.textContent : ''
        });

        // Legacy callback pattern (options.events.change)
        if (options.events && options.events.change) {
            const handler = options.events.change;
            const data = {
                value: value,
                text: valueSpan ? valueSpan.textContent : ''
            };
            if (typeof handler === 'function') {
                handler.call(dropdown, data);
            } else if (typeof window[handler] === 'function') {
                window[handler].call(dropdown, data);
            }
        }
    }

    function navigateOptions(dropdown, direction, options) {
        const currentValue = dropdown.getAttribute('data-value');
        const items = dropdown.querySelectorAll('.m-dropdown-item');
        
        if (items.length === 0) return;
        
        let currentIndex = -1;
        items.forEach((item, index) => {
            if (item.getAttribute('data-value') === currentValue) {
                currentIndex = index;
            }
        });
        
        let newIndex = currentIndex + direction;
        if (newIndex < 0) newIndex = items.length - 1;
        if (newIndex >= items.length) newIndex = 0;
        
        const newValue = items[newIndex].getAttribute('data-value');
        setDropdownValue(dropdown, newValue, options);
    }

    /**
     * Custom TextBox Component
     */

})(window);
