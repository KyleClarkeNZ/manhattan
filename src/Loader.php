<?php
declare(strict_types=1);

namespace Manhattan;

final class Loader extends Component
{
    private string $text = '';
    private bool $hidden = false;
    private bool $overlay = false;
    private string $size = 'md';
    private bool $animateDots = false;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['text'])) {
            $this->text((string)$options['text']);
        }
        if (isset($options['hidden'])) {
            $this->hidden((bool)$options['hidden']);
        }
        if (isset($options['overlay'])) {
            $this->overlay((bool)$options['overlay']);
        }
        if (isset($options['animateDots'])) {
            $this->animateDots((bool)$options['animateDots']);
        }
        if (isset($options['size'])) {
            $this->size((string)$options['size']);
        }
    }

    public function text(?string $text): self
    {
        $this->text = trim((string)$text);
        return $this;
    }

    /**
     * Animate a three-dot ellipsis after the text.
     * Has no effect if no text is set.
     */
    public function animateDots(bool $animate = true): self
    {
        $this->animateDots = $animate;
        return $this;
    }

    public function hidden(bool $hidden = true): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function overlay(bool $overlay = true): self
    {
        $this->overlay = $overlay;
        return $this;
    }

    /**
     * Allowed: sm|md|lg
     */
    public function size(string $size): self
    {
        $size = strtolower(trim($size));
        if (!in_array($size, ['sm', 'md', 'lg'], true)) {
            $size = 'md';
        }
        $this->size = $size;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'loader';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(
            ['m-loader', $this->overlay ? 'm-loader-overlay' : 'm-loader-inline', 'm-loader-' . $this->size],
            $this->getExtraClasses()
        );
        if ($this->hidden) {
            $classes[] = 'm-hidden';
        }

        // Accessibility: this container announces progress politely.
        $this->attr('role', 'status');
        $this->attr('aria-live', 'polite');
        $this->attr('aria-busy', 'true');

        $attrs = [
            'id' => htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            'class' => htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8'),
        ];

        $attrString = '';
        foreach ($attrs as $key => $val) {
            $attrString .= " {$key}=\"{$val}\"";
        }

        $eventAttrs = $this->renderEventAttributes();
        $extraAttrs = $this->renderAdditionalAttributes(array_keys($attrs));

        $textHtml = '';
        if ($this->text !== '') {
            $dots = '';
            if ($this->animateDots) {
                $dots = '<span class="m-loader-dots" aria-hidden="true">'
                      . '<span>.</span><span>.</span><span>.</span>'
                      . '</span>';
            }
            $textHtml = '<span class="m-loader-text">'
                      . htmlspecialchars($this->text, ENT_QUOTES, 'UTF-8')
                      . $dots
                      . '</span>';
        }

        return "<div{$attrString}{$eventAttrs}{$extraAttrs}><span class=\"m-loader-spinner\" aria-hidden=\"true\"></span>{$textHtml}</div>";
    }
}
