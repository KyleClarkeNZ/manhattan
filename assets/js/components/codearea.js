/**
 * Manhattan UI Framework - CodeArea Component
 * Lightweight syntax highlighting + copy-to-clipboard.
 */

(function(window) {
    'use strict';

    const m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before module');
        return;
    }

    const utils = m.utils;

    m.codearea = function(id, options) {
        const textarea = utils.getElement(id);
        if (!textarea) {
            console.warn('Manhattan: CodeArea element not found:', id);
            return null;
        }

        // Idempotent
        if (textarea._manhattanCodeAreaInstance) {
            return textarea._manhattanCodeAreaInstance;
        }

        const wrapper = textarea.closest('.m-codearea-wrapper');
        const codeEl = wrapper ? wrapper.querySelector('.m-codearea-code') : null;
        const copyBtn = wrapper ? wrapper.querySelector('.m-codearea-copy') : null;

        const dataset = textarea.dataset || {};
        options = utils.extend({
            language: (dataset.language || (wrapper && wrapper.dataset ? wrapper.dataset.language : null) || 'js').toLowerCase(),
            readOnly: textarea.hasAttribute('readonly'),
            wrap: dataset.wrap !== '0'
        }, options || {});

        if (wrapper) {
            wrapper.classList.toggle('m-codearea-wrap', !!options.wrap);
            wrapper.classList.toggle('m-codearea-nowrap', !options.wrap);
        }

        function render() {
            if (!codeEl) return;
            const lang = (options.language || 'js').toLowerCase();
            codeEl.innerHTML = highlight(textarea.value || '', lang);
            syncScroll();
        }

        function syncScroll() {
            if (!wrapper) return;
            const pre = wrapper.querySelector('.m-codearea-highlight');
            if (!pre) return;
            pre.scrollTop = textarea.scrollTop;
            pre.scrollLeft = textarea.scrollLeft;
        }

        function copyToClipboard() {
            const text = textarea.value || '';

            const done = function(ok) {
                if (!copyBtn) return;
                const original = copyBtn.innerHTML;
                copyBtn.innerHTML = ok ? m.icon('fa-check') : m.icon('fa-exclamation-triangle');
                setTimeout(function() {
                    copyBtn.innerHTML = original;
                }, 900);
            };

            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                navigator.clipboard.writeText(text).then(function() {
                    done(true);
                }).catch(function() {
                    done(false);
                });
                return;
            }

            // Fallback
            try {
                textarea.focus();
                textarea.select();
                const ok = document.execCommand('copy');
                textarea.setSelectionRange(text.length, text.length);
                done(!!ok);
            } catch (e) {
                done(false);
            }
        }

        textarea.addEventListener('input', render);
        textarea.addEventListener('scroll', syncScroll);

        if (copyBtn) {
            copyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                copyToClipboard();
            });
        }

        const api = {
            element: textarea,
            render: render,
            value: function(v) {
                if (v === undefined) return textarea.value;
                textarea.value = String(v);
                render();
                return this;
            },
            wrap: function(enabled) {
                if (enabled === undefined) return !!options.wrap;
                options.wrap = !!enabled;
                if (wrapper) {
                    wrapper.classList.toggle('m-codearea-wrap', !!options.wrap);
                    wrapper.classList.toggle('m-codearea-nowrap', !options.wrap);
                }
                render();
                return this;
            }
        };

        textarea._manhattanCodeAreaInstance = api;
        render();
        return api;
    };

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function protectTokens(input, patterns) {
        const tokens = [];
        let out = input;

        patterns.forEach(function(re) {
            out = out.replace(re, function(match) {
                const idx = tokens.length;
                tokens.push(match);
                return '___M_TOKEN_' + idx + '___';
            });
        });

        return { out: out, tokens: tokens };
    }

    function restoreTokens(input, tokens, wrapper) {
        let out = input;
        tokens.forEach(function(tok, idx) {
            out = out.replace('___M_TOKEN_' + idx + '___', wrapper(tok));
        });
        return out;
    }

    function highlight(code, lang) {
        const raw = String(code || '');

        // Identify comment/string tokens first so we don't keyword-highlight inside them.
        let patterns = [];
        if (lang === 'css') {
            patterns = [/\/\*[\s\S]*?\*\//g, /"(?:\\.|[^"\\])*"/g, /'(?:\\.|[^'\\])*'/g];
        } else if (lang === 'sql') {
            patterns = [/--.*$/gm, /\/\*[\s\S]*?\*\//g, /'(?:''|[^'])*'/g];
        } else if (lang === 'php') {
            // php
            patterns = [/\/\*[\s\S]*?\*\//g, /\/\/.*$/gm, /#.*$/gm, /"(?:\\.|[^"\\])*"/g, /'(?:\\.|[^'\\])*'/g];
        } else {
            // js/php
            patterns = [/\/\*[\s\S]*?\*\//g, /\/\/.*$/gm, /`(?:\\.|[^`\\])*`/g, /"(?:\\.|[^"\\])*"/g, /'(?:\\.|[^'\\])*'/g];
        }

        const protectedRes = protectTokens(raw, patterns);
        let out = escapeHtml(protectedRes.out);

        // Numbers
        out = out.replace(/\b\d+(?:\.\d+)?\b/g, '<span class="m-codearea-token-number">$&</span>');

        // Keywords
        if (lang === 'sql') {
            const kw = ['SELECT','FROM','WHERE','INSERT','INTO','VALUES','UPDATE','SET','DELETE','JOIN','LEFT','RIGHT','INNER','OUTER','ON','GROUP','BY','ORDER','LIMIT','OFFSET','CREATE','TABLE','ALTER','DROP','AND','OR','NOT','NULL','AS','DISTINCT'];
            const re = new RegExp('\\b(' + kw.join('|') + ')\\b', 'gi');
            out = out.replace(re, '<span class="m-codearea-token-keyword">$1</span>');
        } else if (lang === 'php') {
            const kw = [
                'declare','strict_types','namespace','use','class','interface','trait','extends','implements',
                'public','protected','private','static','final','readonly','abstract','function','return',
                'if','elseif','else','endif','switch','case','default','break','continue',
                'for','foreach','as','while','do','try','catch','finally','throw','new',
                'match','fn','yield','true','false','null'
            ];
            const re = new RegExp('\\b(' + kw.join('|') + ')\\b', 'gi');
            out = out.replace(re, '<span class="m-codearea-token-keyword">$1</span>');

            // Variables: $foo, $_bar
            out = out.replace(/\$[A-Za-z_][A-Za-z0-9_]*/g, '<span class="m-codearea-token-variable">$&</span>');
        } else if (lang === 'css') {
            // property names
            out = out.replace(/(^|[\s{;])([a-z-]+)(\s*:)/gmi, '$1<span class="m-codearea-token-keyword">$2</span>$3');
            // hex colors
            out = out.replace(/#[0-9a-f]{3,8}\b/gi, '<span class="m-codearea-token-constant">$&</span>');
        } else {
            const kw = ['const','let','var','function','return','if','else','for','while','switch','case','break','continue','try','catch','finally','throw','new','class','extends','import','from','export','default','async','await','true','false','null','undefined'];
            const re = new RegExp('\\b(' + kw.join('|') + ')\\b', 'g');
            out = out.replace(re, '<span class="m-codearea-token-keyword">$1</span>');
        }

        // Restore tokens as strings/comments
        out = restoreTokens(out, protectedRes.tokens.map(escapeHtml), function(tokEscaped) {
            // crude classification: comment starts with /* or // or --
            if (/^(\/\*|\/\/|--)/.test(tokEscaped)) {
                return '<span class="m-codearea-token-comment">' + tokEscaped + '</span>';
            }
            return '<span class="m-codearea-token-string">' + tokEscaped + '</span>';
        });

        // Preserve newlines
        return out.replace(/\n/g, '\n');
    }

    // Auto-init
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.m-codearea').forEach(function(el) {
            m.codearea(el.id || el, {});
        });
    });

})(window);
