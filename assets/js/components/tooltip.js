/**
 * Manhattan UI Framework - Tooltip Component
 *
 * Usage (standalone):
 * - Add `data-m-tooltip="Your text"` (or `data-tooltip="..."`) to any element.
 * - Optionally set `data-m-tooltip-position="top|right|bottom|left"`.
 *
 * Auto-upgrade:
 * - Manhattan will convert certain `title="..."` attributes into custom tooltips.
 */

(function (window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before tooltip module');
        return;
    }

    const utils = m.utils;

    const DEFAULT_POSITION = 'top';
    const TOOLTIP_ID_PREFIX = 'm-tooltip-';

    let tooltipEl = null;
    let activeTarget = null;
    let lastId = 0;
    let rafMove = 0;

    function isElement(node) {
        return node && node.nodeType === 1;
    }

    function getTooltipText(el) {
        if (!el) return '';
        const ds = el.dataset || {};
        const fromData = ds.mTooltip || ds.tooltip;
        if (typeof fromData === 'string' && fromData.trim() !== '') {
            return fromData.trim();
        }
        const title = el.getAttribute('title');
        if (typeof title === 'string' && title.trim() !== '') {
            return title.trim();
        }
        return '';
    }

    function isTooltipDisabled(el) {
        if (!el) return false;
        const ds = el.dataset || {};
        return ds.mTooltipDisabled === '1' || ds.tooltipDisabled === '1';
    }

    function isSafeToConvertTitle(el) {
        if (!el || !el.tagName) return false;
        const tag = String(el.tagName).toUpperCase();
        // Preserve iframe titles (commonly used for accessibility)
        if (tag === 'IFRAME') return false;
        return true;
    }

    function convertTitleToTooltip(el) {
        if (!el || !el.getAttribute || !el.removeAttribute) return;
        if (isTooltipDisabled(el)) return;

        const title = el.getAttribute('title');
        if (!title || title.trim() === '') return;
        if (!isSafeToConvertTitle(el)) return;

        const ds = el.dataset || {};
        if (!ds.mTooltip && !ds.tooltip) {
            el.dataset.mTooltip = title.trim();
        }
        // Always remove native title to avoid browser tooltip delay overlays
        el.removeAttribute('title');
    }

    function getTooltipPosition(el) {
        const ds = el.dataset || {};
        const pos = (ds.mTooltipPosition || ds.tooltipPosition || '').toLowerCase();
        if (pos === 'top' || pos === 'right' || pos === 'bottom' || pos === 'left') {
            return pos;
        }
        return DEFAULT_POSITION;
    }

    function ensureTooltipEl() {
        if (tooltipEl) return tooltipEl;
        tooltipEl = document.createElement('div');
        tooltipEl.className = 'm-tooltip';
        tooltipEl.setAttribute('role', 'tooltip');
        tooltipEl.setAttribute('aria-hidden', 'true');
        document.body.appendChild(tooltipEl);
        return tooltipEl;
    }

    function nextTooltipId() {
        lastId += 1;
        return TOOLTIP_ID_PREFIX + String(lastId);
    }

    function setAriaDescribedby(target, id) {
        if (!target || !id) return;
        const existing = (target.getAttribute('aria-describedby') || '').trim();
        if (!existing) {
            target.setAttribute('aria-describedby', id);
            return;
        }
        const parts = existing.split(/\s+/).filter(Boolean);
        if (!parts.includes(id)) {
            parts.push(id);
            target.setAttribute('aria-describedby', parts.join(' '));
        }
    }

    function removeAriaDescribedby(target, id) {
        if (!target || !id) return;
        const existing = (target.getAttribute('aria-describedby') || '').trim();
        if (!existing) return;
        const parts = existing.split(/\s+/).filter(Boolean).filter(p => p !== id);
        if (parts.length === 0) {
            target.removeAttribute('aria-describedby');
        } else {
            target.setAttribute('aria-describedby', parts.join(' '));
        }
    }

    function clamp(v, min, max) {
        return Math.max(min, Math.min(max, v));
    }

    function positionTooltip(target) {
        if (!tooltipEl || !target) return;

        const rect = target.getBoundingClientRect();
        const tipRect = tooltipEl.getBoundingClientRect();

        const pos = getTooltipPosition(target);
        const gap = 10;

        let top = 0;
        let left = 0;

        if (pos === 'bottom') {
            top = rect.bottom + gap;
            left = rect.left + (rect.width / 2) - (tipRect.width / 2);
        } else if (pos === 'left') {
            top = rect.top + (rect.height / 2) - (tipRect.height / 2);
            left = rect.left - gap - tipRect.width;
        } else if (pos === 'right') {
            top = rect.top + (rect.height / 2) - (tipRect.height / 2);
            left = rect.right + gap;
        } else {
            // top
            top = rect.top - gap - tipRect.height;
            left = rect.left + (rect.width / 2) - (tipRect.width / 2);
        }

        // Keep within viewport
        const vw = document.documentElement.clientWidth;
        const vh = document.documentElement.clientHeight;

        left = clamp(left, 8, vw - tipRect.width - 8);
        top = clamp(top, 8, vh - tipRect.height - 8);

        tooltipEl.style.left = Math.round(left) + 'px';
        tooltipEl.style.top = Math.round(top) + 'px';
        tooltipEl.setAttribute('data-position', pos);
    }

    function showTooltip(target) {
        if (!isElement(target)) return;

        if (isTooltipDisabled(target)) return;

        // Always migrate/remove native title before showing (prevents double tooltips)
        if (target.hasAttribute('title')) {
            convertTitleToTooltip(target);
        }

        const text = getTooltipText(target);
        if (!text) return;

        const tip = ensureTooltipEl();

        tip.textContent = text;
        tip.setAttribute('aria-hidden', 'false');

        if (!tip.id) {
            tip.id = nextTooltipId();
        }

        activeTarget = target;
        setAriaDescribedby(activeTarget, tip.id);

        // Position after text is set (so size is correct)
        positionTooltip(activeTarget);
        tip.classList.add('m-tooltip-visible');
    }

    function hideTooltip() {
        if (!tooltipEl) return;

        tooltipEl.classList.remove('m-tooltip-visible');
        tooltipEl.setAttribute('aria-hidden', 'true');

        if (activeTarget && tooltipEl.id) {
            removeAriaDescribedby(activeTarget, tooltipEl.id);
        }

        activeTarget = null;
    }

    function findTooltipTargetFromEvent(e) {
        const t = e && e.target;
        if (!isElement(t)) return null;
        return t.closest('[data-m-tooltip], [data-tooltip], [title]');
    }

    function onMouseOver(e) {
        const target = findTooltipTargetFromEvent(e);
        if (!target) return;
        showTooltip(target);
    }

    function onMouseOut(e) {
        if (!activeTarget) return;
        const related = e.relatedTarget;
        if (isElement(related) && activeTarget.contains(related)) {
            return;
        }
        hideTooltip();
    }

    function onFocusIn(e) {
        const target = findTooltipTargetFromEvent(e);
        if (!target) return;
        showTooltip(target);
    }

    function onFocusOut() {
        hideTooltip();
    }

    function onKeyDown(e) {
        if (e && e.key === 'Escape') {
            hideTooltip();
        }
    }

    function onMouseMove() {
        if (!activeTarget || !tooltipEl) return;
        if (rafMove) return;
        rafMove = window.requestAnimationFrame(function () {
            rafMove = 0;
            positionTooltip(activeTarget);
        });
    }

    function refresh(root) {
        const scope = root && root.querySelectorAll ? root : document;

        // 1) If an element already has a Manhattan tooltip, remove native title if present.
        scope.querySelectorAll('[data-m-tooltip][title], [data-tooltip][title]').forEach(function (el) {
            if (isTooltipDisabled(el)) return;
            if (!isSafeToConvertTitle(el)) return;
            el.removeAttribute('title');
        });

        // 2) Convert remaining title attributes into Manhattan tooltips (prevents double tooltips).
        scope.querySelectorAll('[title]').forEach(function (el) {
            convertTitleToTooltip(el);
        });
    }

    // Public API
    m.tooltip = {
        refresh: refresh,
        show: showTooltip,
        hide: hideTooltip
    };

    // Bind once
    document.addEventListener('mouseover', onMouseOver, true);
    document.addEventListener('mouseout', onMouseOut, true);
    document.addEventListener('focusin', onFocusIn, true);
    document.addEventListener('focusout', onFocusOut, true);
    document.addEventListener('keydown', onKeyDown, true);
    document.addEventListener('mousemove', onMouseMove, true);

    window.addEventListener('scroll', function () {
        if (activeTarget) positionTooltip(activeTarget);
    }, true);

    window.addEventListener('resize', function () {
        if (activeTarget) positionTooltip(activeTarget);
    });

    // Initial auto-upgrade
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            refresh(document);
        });
    } else {
        refresh(document);
    }

})(window);
