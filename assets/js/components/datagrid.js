/**
 * Manhattan DataGrid Component
 *
 * A full-featured data grid supporting:
 *   • Local and remote (AJAX) data binding
 *   • Client-side and server-side pagination
 *   • Sortable, resizable, reorderable columns
 *   • Row grouping with expand/collapse
 *   • Row selection
 *   • Configurable toolbar
 *   • refresh() API
 *   • Tab-compatible (re-calculates layout when parent tab activates)
 */
(function (window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan DataGrid: core not loaded');
        return;
    }

    var utils = m.utils;

    // ─── Small helpers ────────────────────────────────────────────────────────

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function applyTemplate(template, row) {
        return template.replace(/\{(\w+)\}/g, function (_, key) {
            return escHtml(row[key]);
        });
    }

    function formatValue(value, format) {
        if (value === null || value === undefined || value === '') return '';
        switch (format) {
            case 'number':
                return Number(value).toLocaleString();
            case 'currency':
                return Number(value).toLocaleString(undefined, { style: 'currency', currency: 'USD' });
            case 'date': {
                var d = new Date(value);
                return isNaN(d.getTime()) ? escHtml(value) : d.toLocaleDateString();
            }
            case 'datetime': {
                var dt = new Date(value);
                return isNaN(dt.getTime()) ? escHtml(value) : dt.toLocaleString();
            }
            default:
                return escHtml(value);
        }
    }

    function renderCellValue(col, row) {
        // Embedded Manhattan component takes priority
        if (col.component) {
            var renderer = CellRenderers[col.component.type];
            if (renderer) { return renderer(col.component, row); }
        }
        if (col.template) {
            return applyTemplate(col.template, row);
        }
        return formatValue(row[col.field], col.format);
    }

    function debounce(fn, ms) {
        var t;
        return function () {
            clearTimeout(t);
            var args = arguments;
            var ctx = this;
            t = setTimeout(function () { fn.apply(ctx, args); }, ms);
        };
    }

    // ─── Inline cell component renderers ─────────────────────────────────────
    //
    // Each renderer receives (compCfg, row) where:
    //   compCfg  — the column's `component` object
    //   row      — the current data row
    //
    // Binding convention:
    //   Any prop ending in 'Bind' is treated as a row-field reference.
    //   E.g. { variantBind: 'statusColor' } → variant = row['statusColor']
    //   Literal props provide fallback/fixed values.
    // ─────────────────────────────────────────────────────────────────────────

    function _bind(comp, prop, row, fallback) {
        var bindKey = prop + 'Bind';
        if (comp[bindKey] !== undefined && comp[bindKey] !== null) {
            var v = row[comp[bindKey]];
            return (v !== undefined && v !== null) ? v : (fallback !== undefined ? fallback : '');
        }
        return (comp[prop] !== undefined && comp[prop] !== null) ? comp[prop] : (fallback !== undefined ? fallback : '');
    }

    var CellRenderers = {

        progressBar: function (comp, row) {
            var value   = parseFloat(_bind(comp, 'value',   row, 0));
            var max     = parseFloat(_bind(comp, 'max',     row, 100));
            var variant = String(_bind(comp, 'variant',  row, 'primary'));
            var label   = String(_bind(comp, 'label',    row, ''));
            if (max <= 0) { max = 100; }
            var pct  = Math.min(100, Math.max(0, (value / max) * 100));
            var pctR = Math.round(pct * 10) / 10;
            var showPct  = !!comp.showPercent;
            var striped  = comp.striped  ? ' m-progress-striped'  : '';
            var animated = comp.animated ? ' m-progress-animated' : '';
            var pctHtml  = showPct ? '<span class="m-progress-pct">' + pctR + '%</span>' : '';
            var labelHtml = '';
            if (label) {
                labelHtml = '<div class="m-progress-label">' + escHtml(label) + pctHtml + '</div>';
            } else if (showPct) {
                labelHtml = '<div class="m-progress-label">' + pctHtml + '</div>';
            }
            return '<div class="m-progress m-datagrid-cell-progress">'
                + labelHtml
                + '<div class="m-progress-track" role="progressbar" aria-valuenow="' + value + '" aria-valuemax="' + max + '">'
                + '<div class="m-progress-fill m-progress-fill-' + escHtml(variant) + striped + animated + '" style="width:' + pctR + '%"></div>'
                + '</div></div>';
        },

        badge: function (comp, row) {
            var text    = escHtml(String(_bind(comp, 'text',    row, row[comp.field] !== undefined ? row[comp.field] : '')));
            var variant = escHtml(String(_bind(comp, 'variant', row, 'primary')));
            var icon    = String(_bind(comp, 'icon', row, ''));
            var iconHtml = icon ? (m.icon(icon) + ' ') : '';
            return '<span class="m-badge m-badge-' + variant + '">' + iconHtml + text + '</span>';
        },

        label: function (comp, row) {
            var text    = escHtml(String(_bind(comp, 'text',    row, row[comp.field] !== undefined ? row[comp.field] : '')));
            var variant = escHtml(String(_bind(comp, 'variant', row, 'default')));
            var icon    = String(_bind(comp, 'icon', row, ''));
            var iconHtml = icon ? (m.icon(icon) + ' ') : '';
            return '<span class="m-label m-label-' + variant + '">' + iconHtml + text + '</span>';
        },

        icon: function (comp, row) {
            var name = String(_bind(comp, 'icon', row, ''));
            return name ? m.icon(name) : '';
        },

        checkbox: function (comp, row) {
            var field   = comp.valueBind || comp.field || '';
            var checked = field ? !!row[field] : !!comp.value;
            var rdonly  = (comp.readonly !== false); // default true — read-only in grid
            var disAttr = rdonly ? ' disabled' : '';
            var extraCls = rdonly ? '' : ' m-datagrid-cell-checkbox-interactive';
            return '<label class="m-choice m-checkbox m-datagrid-cell-checkbox' + extraCls + '">'
                + '<input type="checkbox" class="m-choice-input m-checkbox-input"' + (checked ? ' checked' : '') + disAttr + '>'
                + '<span class="m-choice-indicator" aria-hidden="true"></span>'
                + '</label>';
        },

        rating: function (comp, row) {
            var field = comp.valueBind || comp.field || '';
            var value = parseFloat(field ? (row[field] !== undefined ? row[field] : 0) : (comp.value || 0));
            var max   = parseInt(comp.max, 10) || 5;
            var half  = !!comp.halfStars;
            var html  = '';
            for (var i = 1; i <= max; i++) {
                var filled = i <= Math.floor(value);
                var isHalf = !filled && half && i <= Math.ceil(value) && (value % 1) >= 0.5;
                var icon   = isHalf ? 'fa-star-half-alt' : 'fa-star';
                var style  = (filled || isHalf) ? 'fas' : 'far';
                var cls    = 'm-rating-star' + ((filled || isHalf) ? ' m-rating-star-filled' : '');
                html += '<i class="' + style + ' ' + icon + ' ' + cls + '" aria-hidden="true"></i>';
            }
            return '<span class="m-rating m-rating-sm m-rating-readonly m-datagrid-cell-rating">'
                + '<span class="m-rating-stars">' + html + '</span>'
                + '</span>';
        }
    };

    // ─── DataGrid constructor ─────────────────────────────────────────────────

    function DataGrid(element, config, localData) {
        this.element  = element;
        this.config   = config;
        this.id       = element.id;

        // Column state (order, widths) — may be mutated by drag
        this.columns  = (config.columns || []).filter(function (c) { return !c.hidden; });

        // Data state
        this._allData    = localData || [];   // full local dataset
        this._data       = [];                // current page / group slice
        this._total      = 0;                 // total record count (for pager)
        this._page       = 1;
        this._sortField  = null;
        this._sortDir    = 'asc';             // 'asc' | 'desc'
        this._groupField = null;
        this._groupOpen  = {};               // groupValue -> bool
        this._loading    = false;
        this._selectedRow = null;

        // DOM refs (assigned during build)
        this._dom = {};
    }

    // ─── Initialise DOM ───────────────────────────────────────────────────────

    DataGrid.prototype.init = function () {
        this._build();
        this._bindTabEvents();
        // Initial data load
        if (this.config.remote) {
            this.refresh();
        } else {
            this._processLocal();
            this._renderBody();
            this._renderPager();
        }
    };

    DataGrid.prototype._build = function () {
        var cfg = this.config;
        var el  = this.element;
        el.innerHTML = '';

        // Toolbar
        if (cfg.toolbar && cfg.toolbar.length) {
            el.appendChild(this._buildToolbar(cfg.toolbar));
        }

        // Group drop zone
        if (cfg.groupable) {
            el.appendChild(this._buildGroupZone());
        }

        // Wrapper (handles fixed-height + scroll)
        var wrapper = document.createElement('div');
        wrapper.className = 'm-datagrid-wrapper';
        el.appendChild(wrapper);
        this._dom.wrapper = wrapper;

        // Table
        var table = document.createElement('table');
        table.className = 'm-datagrid-table';
        table.setAttribute('role', 'grid');
        wrapper.appendChild(table);
        this._dom.table = table;

        // Thead
        var thead = document.createElement('thead');
        table.appendChild(thead);
        this._dom.thead = thead;
        this._renderHeader();

        // Tbody
        var tbody = document.createElement('tbody');
        table.appendChild(tbody);
        this._dom.tbody = tbody;

        // Loading overlay
        var overlay = document.createElement('div');
        overlay.className = 'm-datagrid-loading';
        overlay.innerHTML = '<span class="m-datagrid-loading-spinner"></span>';
        el.appendChild(overlay);
        this._dom.overlay = overlay;

        // Pager container
        if (cfg.pageable) {
            var pager = document.createElement('div');
            pager.className = 'm-datagrid-pager';
            el.appendChild(pager);
            this._dom.pager = pager;
        }
    };

    // ─── Toolbar ─────────────────────────────────────────────────────────────

    DataGrid.prototype._buildToolbar = function (buttons) {
        var bar = document.createElement('div');
        bar.className = 'm-datagrid-toolbar';

        buttons.forEach(function (btn) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'm-button m-button-primary m-datagrid-toolbar-btn' + (btn['class'] ? ' ' + btn['class'] : '');
            var inner = '';
            if (btn.icon) {
                inner += m.icon(btn.icon) + ' ';
            }
            inner += escHtml(btn.text || '');
            b.innerHTML = inner;
            if (btn.click) {
                b.setAttribute('data-datagrid-click', btn.click);
                b.addEventListener('click', function () {
                    try { (new Function(btn.click))(); } catch (e) { console.error(e); }
                });
            }
            bar.appendChild(b);
        });

        return bar;
    };

    // ─── Group drop zone ──────────────────────────────────────────────────────

    DataGrid.prototype._buildGroupZone = function () {
        var self = this;
        var zone = document.createElement('div');
        zone.className = 'm-datagrid-group-zone';

        var label = document.createElement('span');
        label.className = 'm-datagrid-group-zone-label';
        label.textContent = 'Drag a column here to group by it';
        zone.appendChild(label);
        this._dom.groupZone = zone;
        this._dom.groupZoneLabel = label;

        // Drag-over / drop targets
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('m-drag-over');
        });
        zone.addEventListener('dragleave', function () {
            zone.classList.remove('m-drag-over');
        });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('m-drag-over');
            var field = e.dataTransfer.getData('text/plain');
            if (field) { self.groupBy(field); }
        });

        return zone;
    };

    // ─── Frozen column offsets ────────────────────────────────────────────────
    //
    // Returns { offsets: {field: leftPx}, lastField: string|null }
    // Used by both _renderHeader and _buildRow to apply sticky positioning.
    //
    DataGrid.prototype._computeFrozenOffsets = function () {
        var offsets   = {};
        var lastField = null;
        var left      = 0;
        this.columns.forEach(function (col) {
            if (col.frozen) {
                offsets[col.field] = left;
                left      += (col.width || 120);
                lastField  = col.field;
            }
        });
        return { offsets: offsets, lastField: lastField };
    };

    // ─── Header ───────────────────────────────────────────────────────────────

    DataGrid.prototype._renderHeader = function () {
        var self   = this;
        var cfg    = this.config;
        var thead  = this._dom.thead;
        thead.innerHTML = '';

        var tr = document.createElement('tr');
        tr.className = 'm-datagrid-header-row';

        var frozenInfo = this._computeFrozenOffsets();

        this.columns.forEach(function (col, colIdx) {
            var th = document.createElement('th');
            th.className = 'm-datagrid-th';
            if (col.align !== 'left') { th.style.textAlign = col.align; }

            // Width
            if (col.width) {
                th.style.width = col.width + 'px';
                th.style.minWidth = col.width + 'px';
            }

            // Frozen (sticky) column
            if (col.frozen) {
                th.classList.add('m-datagrid-th-frozen');
                th.style.setProperty('--m-frozen-left', frozenInfo.offsets[col.field] + 'px');
                if (col.field === frozenInfo.lastField) {
                    th.classList.add('m-datagrid-th-frozen-last');
                }
            }

            // Inner wrapper for text + sort icon
            var inner = document.createElement('div');
            inner.className = 'm-datagrid-th-inner';

            var titleEl = document.createElement('span');
            titleEl.className = 'm-datagrid-col-title';
            titleEl.textContent = col.title;
            inner.appendChild(titleEl);

            // Sort icon — empty by default, only shows direction when this column is sorted
            if (col.sortable) {
                var sortIcon = document.createElement('span');
                sortIcon.className = 'm-datagrid-sort-icon';
                sortIcon.innerHTML = '';  // hidden until column is actively sorted
                inner.appendChild(sortIcon);
                th.setAttribute('data-sort-field', col.field);
                th.classList.add('m-datagrid-th-sortable');
                th.addEventListener('click', function (e) {
                    if (e.target.classList.contains('m-datagrid-resize-handle')) return;
                    self._onSort(col.field, th);
                });
                self._dom['sort_' + col.field] = sortIcon;
            }

            th.appendChild(inner);

            // Resize handle
            if (col.resizable) {
                var handle = document.createElement('div');
                handle.className = 'm-datagrid-resize-handle';
                handle.setAttribute('draggable', 'false');
                self._bindResizeHandle(handle, th, colIdx);
                th.appendChild(handle);
            }

            // Column reorder (drag the header itself)
            if (cfg.reorderable) {
                th.setAttribute('draggable', 'true');
                th.setAttribute('data-col-field', col.field);
                th.classList.add('m-datagrid-th-draggable');
                self._bindColDrag(th, colIdx);
            }

            // Group drop affordance
            if (cfg.groupable && col.groupable) {
                th.setAttribute('data-groupable-field', col.field);
            }

            tr.appendChild(th);
        });

        thead.appendChild(tr);
        this._dom.headerRow = tr;
    };

    // ─── Sort ─────────────────────────────────────────────────────────────────

    DataGrid.prototype._onSort = function (field, th) {
        if (this._sortField === field) {
            if (this._sortDir === 'desc') {
                // 3rd click: clear sort and restore original data order
                this._sortField = null;
                this._sortDir   = 'asc';
            } else {
                // 2nd click: flip asc → desc
                this._sortDir = 'desc';
            }
        } else {
            // 1st click on this column: sort ascending
            this._sortField = field;
            this._sortDir   = 'asc';
        }
        this._page = 1;
        this._updateSortIcons();
        if (this.config.remote) {
            this.refresh();
        } else {
            this._processLocal();
            this._renderBody();
            this._renderPager();
        }
    };

    DataGrid.prototype._updateSortIcons = function () {
        var self = this;
        this.columns.forEach(function (col) {
            var el = self._dom['sort_' + col.field];
            if (!el) return;
            var th = el.closest ? el.closest('th') : null;
            if (col.field === self._sortField) {
                // Show the active sort direction only
                var icon = self._sortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                el.innerHTML = m.icon(icon);
                if (th) { th.classList.add('m-datagrid-th-sorted'); }
            } else {
                // No icon on unsorted columns
                el.innerHTML = '';
                if (th) { th.classList.remove('m-datagrid-th-sorted'); }
            }
        });
    };

    // ─── Column resize ────────────────────────────────────────────────────────

    DataGrid.prototype._bindResizeHandle = function (handle, th, colIdx) {
        var self = this;
        var startX, startW;

        function onMouseMove(e) {
            var dx   = e.clientX - startX;
            var newW = Math.max(40, startW + dx);
            th.style.width    = newW + 'px';
            th.style.minWidth = newW + 'px';
            self.columns[colIdx].width = newW;
            // Update matching td cells
            var tds = self._dom.tbody.querySelectorAll('tr td:nth-child(' + (colIdx + 1) + ')');
            tds.forEach(function (td) {
                td.style.maxWidth = newW + 'px';
            });
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            handle.classList.remove('m-dragging');
        }

        handle.addEventListener('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation();
            startX = e.clientX;
            startW = th.offsetWidth;
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            handle.classList.add('m-dragging');
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    };

    // ─── Column reorder ───────────────────────────────────────────────────────

    DataGrid.prototype._bindColDrag = function (th, colIdx) {
        var self = this;

        th.addEventListener('dragstart', function (e) {
            if (e.target.classList.contains('m-datagrid-resize-handle')) {
                e.preventDefault();
                return;
            }
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', self.columns[colIdx].field);
            e.dataTransfer.setData('application/x-col-index', String(colIdx));
            th.classList.add('m-col-dragging');
        });

        th.addEventListener('dragend', function () {
            th.classList.remove('m-col-dragging');
            document.querySelectorAll('.m-datagrid-th').forEach(function (t) {
                t.classList.remove('m-col-drop-target');
            });
        });

        th.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            th.classList.add('m-col-drop-target');
        });

        th.addEventListener('dragleave', function () {
            th.classList.remove('m-col-drop-target');
        });

        th.addEventListener('drop', function (e) {
            e.preventDefault();
            th.classList.remove('m-col-drop-target');
            var fromIdx = parseInt(e.dataTransfer.getData('application/x-col-index'), 10);
            var toIdx   = colIdx;
            if (isNaN(fromIdx) || fromIdx === toIdx) return;
            // Reorder columns array
            var moved = self.columns.splice(fromIdx, 1)[0];
            self.columns.splice(toIdx, 0, moved);
            // Rebuild header + body
            self._renderHeader();
            if (!self.config.remote) {
                self._processLocal();
            }
            self._renderBody();
        });
    };

    // ─── Local data processing ────────────────────────────────────────────────

    DataGrid.prototype._processLocal = function () {
        var self = this;
        var data = this._allData.slice();

        // Sort
        if (this._sortField) {
            var field = this._sortField;
            var dir   = this._sortDir === 'asc' ? 1 : -1;
            data.sort(function (a, b) {
                var av = a[field], bv = b[field];
                if (av === undefined || av === null) av = '';
                if (bv === undefined || bv === null) bv = '';
                // Numeric?
                var an = parseFloat(av), bn = parseFloat(bv);
                if (!isNaN(an) && !isNaN(bn)) return (an - bn) * dir;
                return String(av).localeCompare(String(bv)) * dir;
            });
        }

        this._total = data.length;

        // Paginate
        var pageable = this.config.pageable;
        if (pageable) {
            var ps    = pageable.pageSize;
            var start = (this._page - 1) * ps;
            data      = data.slice(start, start + ps);
        }

        this._data = data;
    };

    // ─── Remote fetch ─────────────────────────────────────────────────────────

    DataGrid.prototype._fetchRemote = function () {
        var self    = this;
        var remote  = this.config.remote;
        var pageable = this.config.pageable;

        var params = {};
        if (pageable) {
            params.page     = this._page;
            params.pageSize = pageable.pageSize;
        }
        if (this._sortField) {
            params.sortField = this._sortField;
            params.sortDir   = this._sortDir;
        }
        if (this._groupField) {
            params.groupField = this._groupField;
        }

        var qs = Object.keys(params).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        }).join('&');

        var url    = remote.url + (qs ? '?' + qs : '');
        var method = remote.method || 'GET';

        this._setLoading(true);

        var headers = utils.extend({ 'X-Requested-With': 'XMLHttpRequest' }, remote.headers || {});
        // Include meta CSRF if present
        var metaCsrf = document.querySelector('meta[name="csrf-token"]');
        if (metaCsrf) {
            headers['X-CSRF-Token'] = metaCsrf.getAttribute('content') || '';
        }

        var fetchOptions = {
            method:  method,
            headers: headers,
            credentials: 'same-origin',
        };

        if (method === 'POST') {
            fetchOptions.headers['Content-Type'] = 'application/json';
            fetchOptions.body = JSON.stringify(params);
        }

        return fetch(url, fetchOptions)
            .then(function (r) {
                if (!r.ok) { throw new Error('DataGrid remote fetch failed: ' + r.status); }
                return r.json();
            })
            .then(function (resp) {
                self._data  = Array.isArray(resp.data)  ? resp.data  : (Array.isArray(resp) ? resp : []);
                self._total = typeof resp.total === 'number' ? resp.total : self._data.length;
                self._setLoading(false);
                self._renderBody();
                self._renderPager();
                self._fireCallback('onDataBound', [self._data]);
            })
            .catch(function (err) {
                self._setLoading(false);
                console.error('Manhattan DataGrid:', err);
                self._renderError('Failed to load data. Please try again.');
            });
    };

    // ─── Body ─────────────────────────────────────────────────────────────────

    DataGrid.prototype._renderBody = function () {
        var self   = this;
        var tbody  = this._dom.tbody;
        var cfg    = this.config;
        tbody.innerHTML = '';

        if (!this._data || this._data.length === 0) {
            this._renderEmpty();
            this._fireCallback('onDataBound', [[]]);
            return;
        }

        // Grouping
        if (this._groupField) {
            this._renderGrouped(tbody);
        } else {
            this._data.forEach(function (row, idx) {
                tbody.appendChild(self._buildRow(row, idx));
            });
        }

        this._fireCallback('onDataBound', [this._data]);
    };

    DataGrid.prototype._buildRow = function (row, idx) {
        var self = this;
        var cfg  = this.config;
        var tr   = document.createElement('tr');
        tr.className = 'm-datagrid-row' + (idx % 2 === 1 ? ' m-datagrid-row-alt' : '');

        if (cfg.selectable) {
            tr.classList.add('m-datagrid-row-selectable');
            tr.addEventListener('click', function () {
                self._onRowClick(tr, row);
            });
        } else if (cfg.callbacks && cfg.callbacks.onRowClick) {
            tr.addEventListener('click', function () {
                self._fireCallback('onRowClick', [tr, row]);
            });
        }

        var frozenInfo = self._computeFrozenOffsets();

        this.columns.forEach(function (col) {
            var td = document.createElement('td');
            td.className = 'm-datagrid-td' + (col.class ? ' ' + col.class : '');
            if (col.align !== 'left') { td.style.textAlign = col.align; }

            // Width + overflow (only when not wrapping)
            if (col.width && !col.wrap) {
                td.style.maxWidth = col.width + 'px';
                td.style.overflow = 'hidden';
                td.style.textOverflow = 'ellipsis';
            }

            // Allow text wrapping
            if (col.wrap) {
                td.classList.add('m-datagrid-td-wrap');
            }

            // Frozen (sticky) column
            if (col.frozen) {
                td.classList.add('m-datagrid-td-frozen');
                td.style.setProperty('--m-frozen-left', frozenInfo.offsets[col.field] + 'px');
                if (col.field === frozenInfo.lastField) {
                    td.classList.add('m-datagrid-td-frozen-last');
                }
            }

            td.innerHTML = renderCellValue(col, row);
            tr.appendChild(td);
        });

        return tr;
    };

    // ─── Grouping ─────────────────────────────────────────────────────────────

    DataGrid.prototype._renderGrouped = function (tbody) {
        var self  = this;
        var field = this._groupField;
        var groups = {};
        var order = [];

        this._data.forEach(function (row) {
            var key = String(row[field] !== undefined && row[field] !== null ? row[field] : '');
            if (!groups[key]) { groups[key] = []; order.push(key); }
            groups[key].push(row);
        });

        order.forEach(function (groupKey) {
            var isOpen = self._groupOpen[groupKey] !== false; // default open

            // Group header row
            var groupTr = document.createElement('tr');
            groupTr.className = 'm-datagrid-group-header';
            var groupTd = document.createElement('td');
            groupTd.colSpan = self.columns.length;
            var arrow = isOpen ? 'fa-chevron-down' : 'fa-chevron-right';
            groupTd.innerHTML =
                '<button type="button" class="m-datagrid-group-toggle">' +
                m.icon(arrow) +
                '</button>' +
                '<span class="m-datagrid-group-label">' + escHtml(groupKey) + '</span>' +
                ' <span class="m-datagrid-group-count">(' + groups[groupKey].length + ')</span>';
            groupTr.appendChild(groupTd);
            tbody.appendChild(groupTr);

            // Toggle expand/collapse
            groupTd.querySelector('.m-datagrid-group-toggle').addEventListener('click', function () {
                self._groupOpen[groupKey] = !self._groupOpen[groupKey];
                var btn = this;
                var nowOpen = self._groupOpen[groupKey];
                btn.innerHTML = m.icon(nowOpen ? 'fa-chevron-down' : 'fa-chevron-right');
                var sibs = [];
                var next = groupTr.nextSibling;
                while (next && !next.classList.contains('m-datagrid-group-header')) {
                    sibs.push(next);
                    next = next.nextSibling;
                }
                sibs.forEach(function (s) { s.style.display = nowOpen ? '' : 'none'; });
                self._fireCallback('onRowExpand', [groupKey, nowOpen]);
            });

            // Data rows
            groups[groupKey].forEach(function (row, idx) {
                var tr = self._buildRow(row, idx);
                if (!isOpen) { tr.style.display = 'none'; }
                tbody.appendChild(tr);
            });
        });
    };

    // ─── Group by field ───────────────────────────────────────────────────────

    DataGrid.prototype.groupBy = function (field) {
        if (field) {
            this._groupField = field;
            this._page       = 1;
            // Update group zone label
            if (this._dom.groupZoneLabel) {
                var col = this.columns.find(function (c) { return c.field === field; });
                this._dom.groupZoneLabel.innerHTML =
                    '<span class="m-datagrid-group-chip">' +
                    escHtml(col ? col.title : field) +
                    '<button type="button" class="m-datagrid-group-chip-remove" title="Remove grouping">' +
                    m.icon('fa-times') + '</button>' +
                    '</span> Drop another column to change grouping';
                var self = this;
                this._dom.groupZoneLabel.querySelector('.m-datagrid-group-chip-remove')
                    .addEventListener('click', function () { self.clearGroup(); });
            }
        }
        if (this.config.remote) {
            this.refresh();
        } else {
            this._processLocal();
            this._renderBody();
            this._renderPager();
        }
    };

    DataGrid.prototype.clearGroup = function () {
        this._groupField = null;
        if (this._dom.groupZoneLabel) {
            this._dom.groupZoneLabel.textContent = 'Drag a column here to group by it';
        }
        if (this.config.remote) {
            this.refresh();
        } else {
            this._processLocal();
            this._renderBody();
            this._renderPager();
        }
    };

    // ─── Row selection ────────────────────────────────────────────────────────

    DataGrid.prototype._onRowClick = function (tr, row) {
        if (this._selectedRow) {
            this._selectedRow.classList.remove('m-selected');
        }
        if (this._selectedRow === tr) {
            this._selectedRow = null;
        } else {
            tr.classList.add('m-selected');
            this._selectedRow = tr;
        }
        this._fireCallback('onRowClick',  [tr, row]);
        this._fireCallback('onRowSelect', [tr, row]);
    };

    DataGrid.prototype.getSelectedData = function () {
        return this._selectedRow ? this._selectedRow.__mData : null;
    };

    // ─── Empty state / error ──────────────────────────────────────────────────

    DataGrid.prototype._renderEmpty = function () {
        var es   = (this.config.emptyState || {});
        var cols = this.columns.length;
        var tr   = document.createElement('tr');
        var td   = document.createElement('td');
        td.colSpan = cols;
        td.className = 'm-datagrid-empty';
        td.innerHTML =
            '<div class="m-datagrid-empty-icon">' + m.icon('fa-inbox') + '</div>' +
            '<div class="m-datagrid-empty-title">' + escHtml(es.title || 'No data available') + '</div>' +
            (es.message ? '<div class="m-datagrid-empty-message">' + escHtml(es.message) + '</div>' : '');
        tr.appendChild(td);
        this._dom.tbody.appendChild(tr);
    };

    DataGrid.prototype._renderError = function (msg) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = this.columns.length;
        td.className = 'm-datagrid-empty';
        td.innerHTML = '<div class="m-datagrid-empty-title" style="color:#e74c3c">' + escHtml(msg) + '</div>';
        tr.appendChild(td);
        this._dom.tbody.innerHTML = '';
        this._dom.tbody.appendChild(tr);
    };

    // ─── Loading state ────────────────────────────────────────────────────────

    DataGrid.prototype._setLoading = function (on) {
        this._loading = on;
        if (this._dom.overlay) {
            this._dom.overlay.style.display = on ? 'flex' : 'none';
        }
    };

    // ─── Pagination ───────────────────────────────────────────────────────────

    DataGrid.prototype._renderPager = function () {
        var self    = this;
        var pager   = this._dom.pager;
        var cfg     = this.config;
        if (!pager || !cfg.pageable) return;
        pager.innerHTML = '';

        var ps      = cfg.pageable.pageSize;
        var total   = this._total;
        var pages   = Math.max(1, Math.ceil(total / ps));
        var current = this._page;

        // Info
        var info = document.createElement('span');
        info.className = 'm-datagrid-pager-info';
        var from = Math.min(total, (current - 1) * ps + 1);
        var to   = Math.min(total, current * ps);
        info.textContent = total > 0
            ? (from + '\u2013' + to + ' of ' + total + ' items')
            : '0 items';
        pager.appendChild(info);

        // Buttons
        var btnGroup = document.createElement('div');
        btnGroup.className = 'm-datagrid-pager-buttons';

        function makeBtn(label, page, icon, disabled) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'm-datagrid-pager-btn' +
                (page === current ? ' m-active' : '') +
                (disabled ? ' m-disabled' : '');
            b.disabled = !!disabled;
            if (icon) {
                b.innerHTML = m.icon(icon);
                b.setAttribute('aria-label', label);
            } else {
                b.textContent = label;
            }
            if (!disabled && page !== current) {
                b.addEventListener('click', function () {
                    self._page = page;
                    if (self.config.remote) {
                        self.refresh();
                    } else {
                        self._processLocal();
                        self._renderBody();
                        self._renderPager();
                    }
                });
            }
            return b;
        }

        btnGroup.appendChild(makeBtn('First', 1, 'fa-angle-double-left', current <= 1));
        btnGroup.appendChild(makeBtn('Previous', current - 1, 'fa-angle-left', current <= 1));

        // Page number window
        var window_size = 5;
        var half     = Math.floor(window_size / 2);
        var winStart = Math.max(1, current - half);
        var winEnd   = Math.min(pages, winStart + window_size - 1);
        winStart     = Math.max(1, winEnd - window_size + 1);

        if (winStart > 1) {
            btnGroup.appendChild(makeBtn('1', 1, null, false));
            if (winStart > 2) {
                var ellipsis1 = document.createElement('span');
                ellipsis1.className = 'm-datagrid-pager-ellipsis';
                ellipsis1.textContent = '\u2026';
                btnGroup.appendChild(ellipsis1);
            }
        }

        for (var p = winStart; p <= winEnd; p++) {
            btnGroup.appendChild(makeBtn(String(p), p, null, false));
        }

        if (winEnd < pages) {
            if (winEnd < pages - 1) {
                var ellipsis2 = document.createElement('span');
                ellipsis2.className = 'm-datagrid-pager-ellipsis';
                ellipsis2.textContent = '\u2026';
                btnGroup.appendChild(ellipsis2);
            }
            btnGroup.appendChild(makeBtn(String(pages), pages, null, false));
        }

        btnGroup.appendChild(makeBtn('Next',  current + 1, 'fa-angle-right', current >= pages));
        btnGroup.appendChild(makeBtn('Last',  pages,       'fa-angle-double-right', current >= pages));

        pager.appendChild(btnGroup);

        // Page size picker — uses Manhattan Dropdown
        var sizeWrap = document.createElement('div');
        sizeWrap.className = 'm-datagrid-pager-size';
        var sizeLabel = document.createElement('span');
        sizeLabel.textContent = 'Rows:';
        sizeWrap.appendChild(sizeLabel);

        var sizeSizes = [10, 20, 50, 100];
        var sizeSelect = document.createElement('select');
        // Add m-dropdown class so Manhattan Dropdown styles apply
        sizeSelect.className = 'm-dropdown m-datagrid-pager-size-select';
        sizeSelect.id = self.id + '_pager_size';
        sizeSizes.forEach(function (n) {
            var opt = document.createElement('option');
            opt.value = String(n);
            opt.textContent = String(n);
            if (n === ps) opt.selected = true;
            sizeSelect.appendChild(opt);
        });
        sizeSelect.addEventListener('change', function () {
            cfg.pageable.pageSize = parseInt(this.value, 10);
            self._page = 1;
            if (self.config.remote) {
                self.refresh();
            } else {
                self._processLocal();
                self._renderBody();
                self._renderPager();
            }
        });
        sizeWrap.appendChild(sizeSelect);
        pager.appendChild(sizeWrap);

        // Upgrade to Manhattan Dropdown after it is in the DOM
        if (typeof m.dropdown === 'function') {
            m.dropdown(sizeSelect, {
                dataSource: sizeSizes.map(function (n) { return { value: String(n), text: String(n) }; })
            });
        }
    };

    // ─── Callbacks ────────────────────────────────────────────────────────────

    DataGrid.prototype._fireCallback = function (name, args) {
        var cbs = this.config.callbacks || {};
        var fn  = cbs[name];
        if (!fn) return;
        try {
            if (typeof fn === 'function') {
                fn.apply(null, args);
            } else if (typeof fn === 'string') {
                var resolved = fn.split('.').reduce(function (o, k) {
                    return o && o[k];
                }, window);
                if (typeof resolved === 'function') {
                    resolved.apply(null, args);
                }
            }
        } catch (e) {
            console.error('Manhattan DataGrid callback error:', e);
        }
    };

    // ─── Tab compatibility ────────────────────────────────────────────────────

    DataGrid.prototype._bindTabEvents = function () {
        var self = this;
        document.addEventListener('m-tab-change', function (e) {
            var panel = e.detail && e.detail.panel;
            if (panel && panel.contains(self.element)) {
                // Column widths may need recalculating after reveal
                setTimeout(function () { self._recalcColumnWidths(); }, 50);
            }
        });
    };

    DataGrid.prototype._recalcColumnWidths = function () {
        // If table was hidden when columns were set, offsetWidth would be 0
        // Trigger a layout pass by reading offsetWidth
        if (this._dom.table) {
            var ths = this._dom.table.querySelectorAll('th.m-datagrid-th');
            ths.forEach(function (th) {
                // Force browser reflow; widths set via style are already correct
                void th.offsetWidth;
            });
        }
    };

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Reload data (remote) or re-apply sort/filter (local).
     * Returns a Promise for remote grids, undefined for local.
     */
    DataGrid.prototype.refresh = function () {
        if (this.config.remote) {
            return this._fetchRemote();
        }
        this._processLocal();
        this._renderBody();
        this._renderPager();
    };

    /**
     * Replace the local dataset entirely.
     * @param {Array} data
     */
    DataGrid.prototype.setData = function (data) {
        if (!Array.isArray(data)) return;
        this._allData = data;
        this._page    = 1;
        this._processLocal();
        this._renderBody();
        this._renderPager();
    };

    /**
     * Go to a specific page (1-based).
     * @param {number} page
     */
    DataGrid.prototype.goToPage = function (page) {
        this._page = Math.max(1, page);
        this.refresh();
    };

    /**
     * Sort programmatically.
     * @param {string} field
     * @param {'asc'|'desc'} dir
     */
    DataGrid.prototype.sort = function (field, dir) {
        this._sortField = field;
        this._sortDir   = (dir === 'desc') ? 'desc' : 'asc';
        this._page      = 1;
        this._updateSortIcons();
        this.refresh();
    };

    /**
     * Clear sort.
     */
    DataGrid.prototype.clearSort = function () {
        this._sortField = null;
        this._updateSortIcons();
        this.refresh();
    };

    /**
     * Return raw data for the current view (current page / group).
     * @return {Array}
     */
    DataGrid.prototype.getData = function () {
        return this._data;
    };

    /**
     * Return total record count (respects server-reported total for remote grids).
     * @return {number}
     */
    DataGrid.prototype.getTotal = function () {
        return this._total;
    };

    /**
     * Destroy the grid — clears DOM and removes event listeners cleanly.
     */
    DataGrid.prototype.destroy = function () {
        this.element.innerHTML = '';
        this.element._manhattanDataGrid = null;
    };

    // ─── Factory / registration ───────────────────────────────────────────────

    /**
     * m.dataGrid(idOrElement, options?)
     *
     * Returns the DataGrid API for the given element.
     * Passing options overrides any config baked into data-datagrid-config.
     */
    m.dataGrid = function (idOrEl, overrides) {
        var element = (typeof idOrEl === 'string')
            ? document.getElementById(idOrEl)
            : idOrEl;

        if (!element) {
            console.warn('Manhattan DataGrid: element not found:', idOrEl);
            return null;
        }

        // Return existing instance if already initialised
        if (element._manhattanDataGrid) {
            return element._manhattanDataGrid;
        }

        // Parse config from data attribute
        var configAttr = element.getAttribute('data-datagrid-config') || '{}';
        var config;
        try { config = JSON.parse(configAttr); } catch (e) { config = {}; }

        // Parse local data from data attribute (may be absent for remote grids)
        var dataAttr = element.getAttribute('data-datagrid-data') || 'null';
        var localData;
        try { localData = JSON.parse(dataAttr); } catch (e) { localData = null; }

        // Merge any JS-side overrides
        if (overrides) {
            config = utils.extend({}, config, overrides);
        }

        var grid = new DataGrid(element, config, Array.isArray(localData) ? localData : []);
        grid.init();
        element._manhattanDataGrid = grid;
        return grid;
    };

    // Auto-init any .m-datagrid elements on DOM ready
    function autoInit() {
        document.querySelectorAll('.m-datagrid[data-datagrid-config]').forEach(function (el) {
            if (!el._manhattanDataGrid) {
                m.dataGrid(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }

})(window);
