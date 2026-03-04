<?php
declare(strict_types=1);

namespace Manhattan;

final class CodeArea extends Component
{
    private string $language = 'js';
    private ?string $name = null;
    private string $value = '';
    private bool $readOnly = true;
    private int $rows = 8;
    private bool $wrap = true;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['language'])) {
            $this->language((string)$options['language']);
        }
        if (isset($options['name'])) {
            $this->name((string)$options['name']);
        }
        if (isset($options['value'])) {
            $this->value((string)$options['value']);
        }
        if (isset($options['readOnly'])) {
            $this->readOnly((bool)$options['readOnly']);
        }
        if (isset($options['rows'])) {
            $this->rows((int)$options['rows']);
        }
        if (isset($options['wrap'])) {
            $this->wrap((bool)$options['wrap']);
        }
    }

    /** Allowed: js|css|sql|php (plus tolerant aliases) */
    public function language(string $language): self
    {
        $lang = strtolower(trim($language));
        if ($lang === 'javascript') $lang = 'js';
        if ($lang === 'stylesheet') $lang = 'css';
        if (!in_array($lang, ['js', 'css', 'sql', 'php'], true)) {
            $lang = 'js';
        }
        $this->language = $lang;
        return $this;
    }

    public function name(?string $name): self
    {
        $this->name = $name !== null ? trim($name) : null;
        return $this;
    }

    public function value(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function readOnly(bool $readOnly = true): self
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    public function rows(int $rows): self
    {
        $this->rows = max(2, $rows);
        return $this;
    }

    /**
     * Controls long-line wrapping in the editor + highlight view.
     * Default: on.
     */
    public function wrap(bool $wrap = true): self
    {
        $this->wrap = $wrap;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'codearea';
    }

    protected function renderHtml(): string
    {
        $wrapperClasses = array_merge(
            ['m-codearea-wrapper', 'm-codearea-lang-' . $this->language, $this->wrap ? 'm-codearea-wrap' : 'm-codearea-nowrap'],
            $this->getExtraClasses()
        );
        $wrapperClassAttr = htmlspecialchars(implode(' ', array_filter($wrapperClasses)), ENT_QUOTES, 'UTF-8');

        $idAttr = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $langLabel = htmlspecialchars(strtoupper($this->language), ENT_QUOTES, 'UTF-8');

        $this->data('component', 'codearea');
        $this->data('language', $this->language);
        $this->data('wrap', $this->wrap ? '1' : '0');

        $attrs = $this->renderAdditionalAttributes();
        $events = $this->renderEventAttributes();

        $textareaAttrs = '';
        $textareaAttrs .= ' id="' . $idAttr . '"';
        $textareaAttrs .= ' class="m-codearea"';
        $textareaAttrs .= ' rows="' . (int)$this->rows . '"';
        $textareaAttrs .= ' spellcheck="false"';
        $textareaAttrs .= ' autocapitalize="off"';
        $textareaAttrs .= ' autocomplete="off"';
        $textareaAttrs .= ' autocorrect="off"';

        if ($this->name !== null && $this->name !== '') {
            $textareaAttrs .= ' name="' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->readOnly) {
            $textareaAttrs .= ' readonly';
        }

        $valueEscaped = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');

        $copyLabel = 'Copy ' . $langLabel;

        $copyIconHtml = Icon::html('fa-copy', ['ariaHidden' => true]);

        return <<<HTML
<div class="{$wrapperClassAttr}"{$attrs}{$events}>
    <div class="m-codearea-toolbar">
        <div class="m-codearea-language">{$langLabel}</div>
        <button type="button" class="m-codearea-copy" aria-label="{$copyLabel}" data-m-tooltip="{$copyLabel}" data-m-tooltip-position="bottom">
            {$copyIconHtml}
        </button>
    </div>
    <div class="m-codearea-editor">
        <pre class="m-codearea-highlight" aria-hidden="true"><code class="m-codearea-code"></code></pre>
        <textarea{$textareaAttrs}>{$valueEscaped}</textarea>
    </div>
</div>
HTML;
    }
}
