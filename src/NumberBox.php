<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * NumberBox Component
 * Input field for numeric values (integers or decimals)
 */
class NumberBox extends Component
{
    private ?string $value = null;
    private ?string $placeholder = null;
    private ?string $name = null;
    private bool $required = false;
    private ?float $min = null;
    private ?float $max = null;
    private ?float $step = null;
    private bool $disabled = false;
    private bool $allowDecimals = false;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['value'])) {
            $this->value = (string)$options['value'];
        }
        if (isset($options['placeholder'])) {
            $this->placeholder = (string)$options['placeholder'];
        }
        if (isset($options['name'])) {
            $this->name = (string)$options['name'];
        }
        if (isset($options['required'])) {
            $this->required = (bool)$options['required'];
        }
        if (isset($options['min'])) {
            $this->min = (float)$options['min'];
        }
        if (isset($options['max'])) {
            $this->max = (float)$options['max'];
        }
        if (isset($options['step'])) {
            $this->step = (float)$options['step'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }
        if (isset($options['allowDecimals'])) {
            $this->allowDecimals = (bool)$options['allowDecimals'];
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

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    public function min(float $min): self
    {
        $this->min = $min;
        return $this;
    }

    public function max(float $max): self
    {
        $this->max = $max;
        return $this;
    }

    public function step(float $step): self
    {
        $this->step = $step;
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Allow decimal values (defaults to integers only)
     */
    public function allowDecimals(bool $allow = true): self
    {
        $this->allowDecimals = $allow;
        
        // Auto-set step if not already set
        if ($allow && $this->step === null) {
            $this->step = 0.01;
        } elseif (!$allow && $this->step === null) {
            $this->step = 1.0;
        }
        
        return $this;
    }

    /**
     * Convenience preset for integer fields
     */
    public function integer(): self
    {
        $this->allowDecimals = false;
        if ($this->step === null) {
            $this->step = 1.0;
        }
        return $this;
    }

    /**
     * Convenience preset for decimal fields
     */
    public function decimal(int $precision = 2): self
    {
        $this->allowDecimals = true;
        $this->step = 1 / pow(10, $precision);
        return $this;
    }

    /**
     * Set a range (min and max together)
     */
    public function range(float $min, float $max): self
    {
        $this->min = $min;
        $this->max = $max;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'numberbox';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(
            ['m-textbox'],
            $this->getExtraClasses()
        );

        $attrs = [];
        $attrs['type'] = 'number';
        $attrs['id'] = $this->id;
        $attrs['class'] = implode(' ', $classes);

        if ($this->name !== null) {
            $attrs['name'] = $this->name;
        }

        if ($this->value !== null) {
            $attrs['value'] = $this->value;
        }

        if ($this->placeholder !== null) {
            $attrs['placeholder'] = $this->placeholder;
        }

        if ($this->required) {
            $attrs['required'] = 'required';
        }

        if ($this->disabled) {
            $attrs['disabled'] = 'disabled';
        }

        if ($this->min !== null) {
            $attrs['min'] = (string)$this->min;
        }

        if ($this->max !== null) {
            $attrs['max'] = (string)$this->max;
        }

        if ($this->step !== null) {
            $attrs['step'] = (string)$this->step;
        } else {
            // Default step based on decimal allowance
            $attrs['step'] = $this->allowDecimals ? '0.01' : '1';
        }

        // Build attribute string
        $attrString = '';
        foreach ($attrs as $key => $val) {
            if ($key === 'required' || $key === 'disabled') {
                $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            } else {
                $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        // Add additional attributes and events
        $attrString .= $this->renderAdditionalAttributes(['id', 'class', 'name', 'value', 'placeholder', 'required', 'disabled', 'min', 'max', 'step', 'type']);
        $attrString .= $this->renderEventAttributes();

        return '<input' . $attrString . '>';
    }
}
