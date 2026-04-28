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
        this.element    = element;
        this.config     = config;
        this.max        = parseInt(config.max, 10)  || 5;
        this._hover     = null;
        this._starsEl   = null;
        this._textEl    = null;
        // In aggregate mode, userValue tracks the user's own selection (separate
        // from the community-average display value).
        this.userValue  = parseFloat(config.value) || 0;
        this.value      = parseFloat(config.value) || 0;
    }

    // ─── Initialise ───────────────────────────────────────────────────────────

    Rating.prototype.init = function () {
        this._starsEl = this.element.querySelector('.m-rating-stars');
        this._textEl  = this.element.querySelector('.m-rating-value-text');

        // Build the star elements ONCE up front. Subsequent renders mutate the
        // existing <i> nodes' classes rather than rewriting innerHTML — this is
        // critical because rebuilding innerHTML during a hover sequence destroys
        // the very DOM nodes the browser is tracking for mouseover / mousedown,
        // which causes:
        //   • mouseleave to never fire reliably (continuous mouseover / mouseout
        //     storms as new nodes appear under the cursor),
        //   • clicks to be silently dropped (mousedown and mouseup land on
        //     different DOM nodes).
        this._buildStars();

        if (this.config.aggregate) {
            // Show user's saved rating if they have one, otherwise the community avg
            this._renderAgg(this.userValue > 0 ? this.userValue : this.config.aggregate.avg);
        } else {
            this._render(this.value);
        }
        if (!this.config.readonly) {
            this._bindInteractive();
        }
    };

    // ─── Rendering ───────────────────────────────────────────────────────────

    /**
     * Create the persistent <i> star elements. Called once from init().
     * Class state (filled / half / empty) is applied later by _render().
     */
    Rating.prototype._buildStars = function () {
        if (!this._starsEl) { return; }
        var frag = document.createDocumentFragment();
        for (var i = 1; i <= this.max; i++) {
            var iEl = document.createElement('i');
            iEl.className       = 'm-rating-star' + (this.config.readonly ? '' : ' m-rating-star-interactive');
            iEl.setAttribute('data-value',   String(i));
            iEl.setAttribute('aria-hidden',  'true');
            // Default empty appearance — _render will toggle filled state.
            iEl.classList.add('far', 'fa-star');
            frag.appendChild(iEl);
        }
        // Replace any pre-existing children in one shot (e.g. from a prior init).
        this._starsEl.innerHTML = '';
        this._starsEl.appendChild(frag);
        this._starNodes = Array.prototype.slice.call(
            this._starsEl.querySelectorAll('[data-value]')
        );
    };

    Rating.prototype._render = function (activeValue) {
        var half = this.config.halfStars;

        if (this._starNodes && this._starNodes.length) {
            for (var i = 0; i < this._starNodes.length; i++) {
                var idx    = i + 1;
                var node   = this._starNodes[i];
                var filled = idx <= Math.floor(activeValue);
                var isHalf = !filled && half && idx <= Math.ceil(activeValue) && (activeValue % 1) >= 0.5;

                // Toggle the FA style + glyph classes in place — no DOM rebuild.
                node.classList.toggle('fas',              filled || isHalf);
                node.classList.toggle('far',              !(filled || isHalf));
                node.classList.toggle('fa-star',          !isHalf);
                node.classList.toggle('fa-star-half-alt',  isHalf);
                node.classList.toggle('m-rating-star-filled', filled || isHalf);
            }
        }

        if (this._textEl) {
            // In aggregate mode the calling code manages the label — don't overwrite it
            if (!this.config.aggregate) {
                if (activeValue > 0) {
                    this._textEl.textContent = activeValue + '\u00a0/\u00a0' + this.max;
                } else {
                    this._textEl.textContent = '';
                }
            }
        }
    };

    /**
     * Render for aggregate mode: updates stars and manages the dim class that
     * signals "showing community average, not a personal selection".
     *
     * The dim is only applied when there is a meaningful community average to
     * show (avg > 0) AND the user has not set a personal rating. When there are
     * no ratings yet the widget shows fully-bright empty stars so it is clearly
     * visible and inviting as an interactive control.
     */
    Rating.prototype._renderAgg = function (displayVal) {
        this._render(displayVal);
        var hasAvg = this.config.aggregate && this.config.aggregate.avg > 0;
        if (!this.userValue && hasAvg) {
            // Dim: showing community avg, user hasn't rated yet
            this.element.classList.add('m-rating-showing-avg');
        } else {
            // Full brightness: either user has rated, or no avg exists yet
            this.element.classList.remove('m-rating-showing-avg');
        }
    };

    // ─── Interactive bindings ─────────────────────────────────────────────────

    Rating.prototype._bindInteractive = function () {
        var self  = this;
        var stars = this._starsEl;
        var agg   = self.config.aggregate;
        if (!stars) return;

        // Hover preview
        stars.addEventListener('mouseover', function (e) {
            var star = _findStarFromEvent(stars, e);
            if (!star) return;
            self._hover = parseInt(star.getAttribute('data-value'), 10);
            // Remove dim BEFORE rendering so newly-created star elements are not
            // painted with the dimmed opacity from the 'm-rating-showing-avg' rule.
            if (agg) { self.element.classList.remove('m-rating-showing-avg'); }
            self._render(self._hover);
        });

        stars.addEventListener('mouseleave', function () {
            self._hover = null;
            if (agg) {
                // Revert to user's saved value if set, otherwise the community avg
                self._renderAgg(self.userValue > 0 ? self.userValue : agg.avg);
            } else {
                self._render(self.value);
            }
        });

        // Select on click
        stars.addEventListener('click', function (e) {
            var star = _findStarFromEvent(stars, e);
            if (!star) return;
            var clicked = parseInt(star.getAttribute('data-value'), 10);
            if (agg) {
                // Aggregate mode: no toggle-to-zero; record user's selection
                self.userValue = clicked;
                self.value     = clicked;
                self._renderAgg(self.userValue);
            } else {
                // Standard mode: click same value to clear
                self.value = (clicked === self.value) ? 0 : clicked;
                self._render(self.value);
            }
            self._fireChange();
        });

        // Keyboard: arrow R/U = increase, arrow L/D = decrease
        this.element.setAttribute('tabindex', '0');
        this.element.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (agg) {
                    self.userValue = Math.min(self.max, (self.userValue || 0) + 1);
                    self.value = self.userValue;
                    self._renderAgg(self.userValue);
                } else {
                    self.value = Math.min(self.max, self.value + 1);
                    self._render(self.value);
                }
                self._fireChange();
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
                e.preventDefault();
                if (agg) {
                    self.userValue = Math.max(0, (self.userValue || 0) - 1);
                    self.value = self.userValue;
                    self._renderAgg(self.userValue > 0 ? self.userValue : agg.avg);
                } else {
                    self.value = Math.max(0, self.value - 1);
                    self._render(self.value);
                }
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

    /**
     * Locate the star [data-value] for a mouse event. First tries closest(),
     * then falls back to coordinate-based hit-testing of each star's bounding
     * box. The fallback is required because Font Awesome icons render via a
     * ::before pseudo-element whose painted area is only the glyph itself —
     * so when the mouse is over the inline <i>'s reported bounding box but
     * not the glyph, the event target resolves to the parent container and
     * closest('[data-value]') returns null.
     */
    function _findStarFromEvent(stars, e) {
        var target = e.target;
        var hit = target.closest ? target.closest('[data-value]') : _closestDataValue(target);
        if (hit) return hit;
        var children = stars.querySelectorAll('[data-value]');
        for (var i = 0; i < children.length; i++) {
            var r = children[i].getBoundingClientRect();
            if (e.clientX >= r.left && e.clientX <= r.right
             && e.clientY >= r.top  && e.clientY <= r.bottom) {
                return children[i];
            }
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

    /** Get the user's personal selection (aggregate mode only; same as getValue otherwise). */
    Rating.prototype.getUserValue = function () {
        return this.userValue;
    };

    /** Set value programmatically (also updates userValue in aggregate mode). */
    Rating.prototype.setValue = function (v) {
        this.value = Math.max(0, Math.min(this.max, parseFloat(v) || 0));
        if (this.config.aggregate) {
            this.userValue = this.value;
            var displayVal = this.userValue > 0 ? this.userValue : this.config.aggregate.avg;
            this._renderAgg(displayVal);
        } else {
            this._render(this.value);
        }
    };

    /**
     * Update the aggregate data after a user submits a rating.
     * Re-renders the stars to reflect the new community average.
     * @param {number} avg   New community average
     * @param {number} count New total count
     */
    Rating.prototype.setAggregate = function (avg, count) {
        if (!this.config.aggregate) { return; }
        this.config.aggregate.avg   = avg;
        this.config.aggregate.count = count;
        // Re-render only when not currently hovering
        if (this._hover === null) {
            var displayVal = this.userValue > 0 ? this.userValue : avg;
            this._renderAgg(displayVal);
        }
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
