<?php
declare(strict_types=1);

namespace Manhattan;

class DatePicker extends Component
{
    private ?string $value = null;
    private ?string $placeholder = null;
    private ?string $min = null;
    private ?string $max = null;
    private ?string $name = null;
    private string $format = 'Y-m-d';
    private bool $disabled = false;
    private bool $showTodayButton = true;
    private bool $highlightToday = true;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['value'])) {
            $this->value = (string)$options['value'];
        }
        if (isset($options['placeholder'])) {
            $this->placeholder = (string)$options['placeholder'];
        }
        if (isset($options['min'])) {
            $this->min = (string)$options['min'];
        }
        if (isset($options['max'])) {
            $this->max = (string)$options['max'];
        }
        if (isset($options['name'])) {
            $this->name = (string)$options['name'];
        }
        if (isset($options['format'])) {
            $this->format = (string)$options['format'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }
        if (isset($options['showTodayButton'])) {
            $this->showTodayButton = (bool)$options['showTodayButton'];
        }
        if (isset($options['highlightToday'])) {
            $this->highlightToday = (bool)$options['highlightToday'];
        }
    }

    public function value(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function min(string $min): self
    {
        $this->min = $min;
        return $this;
    }

    public function max(string $max): self
    {
        $this->max = $max;
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function format(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function showTodayButton(bool $show = true): self
    {
        $this->showTodayButton = $show;
        return $this;
    }

    public function highlightToday(bool $highlight = true): self
    {
        $this->highlightToday = $highlight;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'datepicker';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-datepicker'], $this->getExtraClasses());

        $attrs = [
            'id' => htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            'type' => 'text',
            'class' => implode(' ', $classes),
            'autocomplete' => 'off',
            'data-format' => htmlspecialchars($this->format, ENT_QUOTES, 'UTF-8'),
        ];

        if ($this->name) {
            $attrs['name'] = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        }
        if ($this->placeholder) {
            $attrs['placeholder'] = htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8');
        }
        if ($this->value !== null) {
            $attrs['value'] = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');
        }
        if ($this->min) {
            $attrs['data-min'] = htmlspecialchars($this->min, ENT_QUOTES, 'UTF-8');
        }
        if ($this->max) {
            $attrs['data-max'] = htmlspecialchars($this->max, ENT_QUOTES, 'UTF-8');
        }
        if ($this->disabled) {
            $attrs['disabled'] = 'disabled';
        }
        if ($this->showTodayButton) {
            $attrs['data-show-today'] = 'true';
        }
        if (!$this->highlightToday) {
            $attrs['data-highlight-today'] = 'false';
        }

        $attrString = '';
        foreach ($attrs as $key => $val) {
            $attrString .= " {$key}=\"{$val}\"";
        }

        $eventAttrs = $this->renderEventAttributes();
        $extraAttrs = $this->renderAdditionalAttributes(array_keys($attrs));

        return "<input{$attrString}{$eventAttrs}{$extraAttrs}>";
    }
}
