/**
 * Manhattan Rating Component
 *
 * Renders a star-based rating widget. Supports:
 *   • Read-only display with half-star rendering
 *   • Interactive editing with hover, click and keyboard (arrow keys)
 *   • onChange callback + DOM event 'm-rating-change'
 *   • Sizes: sm / md (default) / lg
 *
 * Auto-initialised on any .m-rating[data-rating-config] element.
 * Manual init: m.rating('myRatingId')  or  m.rating(element, { value: 3 })
 */
(function (window) {
    'use strict';

    var m = window.m;
    if (!m) {
        console.warn('Manhattan Rating: core not loaded');
        return;
    }

    // ─── Constructor ──────────────────────────────────────────────────────────

    function Rating(element, config) {
        this.element  = element;
        this.config   = config;
        this.value    = parseFloat(config.value) || 0;
        this.max      = parseInt(config.max, 10)  || 5;
        this._hover   = null;
        this._starsEl = null;
        this._textEl  = null;
    }

    // ─── Initialise ───────────────────────────────────────────────────────────

    Rating.prototype.init = function () {
        this._starsEl = this.element.querySelector('.m-rating-stars');
        this._textEl  = this.element.querySelector('.m-rating-value-text');
        this._render(this.value);
        if (!this.config.readonly) {
            this._bindInteractive();
        }
    };

    // ─── Rendering ───────────────────────────────────────────────────────────

    Rating.prototype._render = function (activeValue) {
        var html = '';
        var half = this.config.halfStars;

        for (var i = 1; i <= this.max; i++) {
            var filled = i <= Math.floor(activeValue);
            var isHalf = !filled && half && i <= Math.ceil(activeValue) && (activeValue % 1) >= 0.5;
            var icon   = isHalf ? 'fa-star-half-alt' : 'fa-star';
            var style  = (filled || isHalf) ? 'fas' : 'far';
            var cls    = 'm-rating-star' + ((filled || isHalf) ? ' m-rating-star-filled' : '') +
                         (this.config.readonly ? '' : ' m-rating-star-interactive');
            html += '<i class="' + style + ' ' + icon + ' ' + cls + '"'
                 +  ' data-value="' + i + '"'
                 +  ' aria-hidden="true"></i>';
        }

        if (this._starsEl) {
            this._starsEl.innerHTML = html;
        }

        if (this._textEl) {
            if (activeValue > 0) {
                this._textEl.textContent = activeValue + '\u00a0/\u00a0' + this.max;
            } else {
                this._textEl.textContent = '';
            }
        }
    };

    // ─── Interactive bindings ─────────────────────────────────────────────────

    Rating.prototype._bindInteractive = function () {
        var self  = this;
        var stars = this._starsEl;
        if (!stars) return;

        // Hover preview
        stars.addEventListener('mouseover', function (e) {
            var star = e.target.closest ? e.target.closest('[data-value]') : _closestDataValue(e.target);
            if (!star) return;
            self._hover = parseInt(star.getAttribute('data-value'), 10);
            self._render(self._hover);
        });

        stars.addEventListener('mouseleave', function () {
            self._hover = null;
            self._render(self.value);
        });

        // Select on click; click same value again to clear
        stars.addEventListener('click', function (e) {
            var star = e.target.closest ? e.target.closest('[data-value]') : _closestDataValue(e.target);
            if (!star) return;
            var clicked = parseInt(star.getAttribute('data-value'), 10);
            self.value  = (clicked === self.value) ? 0 : clicked;
            self._render(self.value);
            self._fireChange();
        });

        // Keyboard: arrow R/U = increase, arrow L/D = decrease
        this.element.setAttribute('tabindex', '0');
        this.element.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
                e.preventDefault();
                self.value = Math.min(self.max, self.value + 1);
                self._render(self.value);
                self._fireChange();
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
                e.preventDefault();
                self.value = Math.max(0, self.value - 1);
                self._render(self.value);
                self._fireChange();
            }
        });
    };

    // Fallback closest() for older browsers
    function _closestDataValue(el) {
        while (el) {
            if (el.hasAttribute && el.hasAttribute('data-value')) return el;
            el = el.parentElement;
        }
        return null;
    }

    // ─── Callbacks ────────────────────────────────────────────────────────────

    Rating.prototype._fireChange = function () {
        var fn = this.config.onChange;
        if (fn) {
            try {
                var resolved = (typeof fn === 'function') ? fn : _resolvePath(fn);
                if (typeof resolved === 'function') {
                    resolved(this.value, this.element);
                }
            } catch (e) {
                console.error('Manhattan Rating onChange error:', e);
            }
        }
        this.element.dispatchEvent(new CustomEvent('m-rating-change', {
            detail:  { value: this.value },
            bubbles: true,
        }));
    };

    function _resolvePath(path) {
        return path.split('.').reduce(function (o, k) { return o && o[k]; }, window);
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    /** Get current value. */
    Rating.prototype.getValue = function () {
        return this.value;
    };

    /** Set value programmatically. */
    Rating.prototype.setValue = function (v) {
        this.value = Math.max(0, Math.min(this.max, parseFloat(v) || 0));
        this._render(this.value);
    };

    /** Destroy instance. */
    Rating.prototype.destroy = function () {
        this.element._manhattanRating = null;
    };

    // ─── Factory / registration ───────────────────────────────────────────────

    /**
     * m.rating(idOrElement, overrides?)
     * Returns a Rating API instance.
     */
    m.rating = function (idOrEl, overrides) {
        var element = (typeof idOrEl === 'string')
            ? document.getElementById(idOrEl)
            : idOrEl;

        if (!element) {
            console.warn('Manhattan Rating: element not found:', idOrEl);
            return null;
        }

        if (element._manhattanRating) {
            return element._manhattanRating;
        }

        var config = {};
        try {
            config = JSON.parse(element.getAttribute('data-rating-config') || '{}');
        } catch (e) { config = {}; }

        if (overrides) {
            Object.keys(overrides).forEach(function (k) { config[k] = overrides[k]; });
        }

        var rating = new Rating(element, config);
        rating.init();
        element._manhattanRating = rating;
        return rating;
    };

    // Auto-init any .m-rating elements on DOM ready
    function autoInit() {
        document.querySelectorAll('.m-rating[data-rating-config]').forEach(function (el) {
            if (!el._manhattanRating) {
                m.rating(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }

})(window);
