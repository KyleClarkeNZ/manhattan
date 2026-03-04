<?php
declare(strict_types=1);

namespace Manhattan;

final class Loader extends Component
{
    private string $text = 'Loading...';
    private bool $hidden = false;
    private bool $overlay = false;
    private string $size = 'md';

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
        if (isset($options['size'])) {
            $this->size((string)$options['size']);
        }
    }

    public function text(?string $text): self
    {
        $this->text = trim((string)$text);
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
            $textHtml = '<span class="m-loader-text">' . htmlspecialchars($this->text, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        return "<div{$attrString}{$eventAttrs}{$extraAttrs}><span class=\"m-loader-spinner\" aria-hidden=\"true\"></span>{$textHtml}</div>";
    }
}
