<?php
declare(strict_types=1);

namespace Manhattan;

abstract class Component
{
    protected string $id;
    protected array $options;

    /** @var array<string, string> */
    private array $attributes = [];

    /** @var array<string, string> */
    private array $events = [];

    /** @var string[] */
    private array $extraClasses = [];

    public function __construct(string $id, array $options = [])
    {
        $this->id = $id;
        $this->options = $options;

        if (isset($options['class']) && is_string($options['class'])) {
            $this->addClass($options['class']);
        }

        if (isset($options['attrs']) && is_array($options['attrs'])) {
            foreach ($options['attrs'] as $key => $val) {
                if (is_string($key) && (is_string($val) || is_numeric($val))) {
                    $this->attr($key, (string)$val);
                }
            }
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function addClass(string $class): self
    {
        $parts = preg_split('/\s+/', trim($class)) ?: [];
        foreach ($parts as $part) {
            if ($part !== '') {
                $this->extraClasses[] = $part;
            }
        }
        return $this;
    }

    public function attr(string $name, ?string $value): self
    {
        if ($value === null) {
            unset($this->attributes[$name]);
            return $this;
        }

        $this->attributes[$name] = $value;
        return $this;
    }

    public function data(string $name, ?string $value): self
    {
        $name = trim($name);
        if ($name === '') {
            return $this;
        }
        return $this->attr('data-' . $name, $value);
    }

    public function on(string $event, string $handler): self
    {
        $event = strtolower(trim($event));
        if ($event === '') {
            return $this;
        }
        $this->events[$event] = $handler;
        return $this;
    }

    public function render(): string
    {
        return $this->renderHtml();
    }

    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** @return string[] */
    protected function getExtraClasses(): array
    {
        return $this->extraClasses;
    }

    /**
     * @param string[] $exclude
     */
    protected function renderAdditionalAttributes(array $exclude = []): string
    {
        $excludeMap = array_fill_keys($exclude, true);
        $out = '';

        foreach ($this->attributes as $key => $value) {
            if (isset($excludeMap[$key])) {
                continue;
            }
            $out .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return $out;
    }

    protected function renderEventAttributes(): string
    {
        $out = '';

        foreach ($this->events as $event => $handler) {
            $attrName = 'on' . $event;
            $js = $this->normalizeJsHandler($handler);
            $out .= ' ' . htmlspecialchars($attrName, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($js, ENT_QUOTES, 'UTF-8') . '"';
        }

        return $out;
    }

    private function normalizeJsHandler(string $handler): string
    {
        $handler = trim($handler);
        if ($handler === '') {
            return '';
        }

        if (preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*(\.[A-Za-z_$][A-Za-z0-9_$]*)*$/', $handler) === 1) {
            return $handler . '(event)';
        }

        return $handler;
    }

    abstract protected function getComponentType(): string;

    abstract protected function renderHtml(): string;
}
