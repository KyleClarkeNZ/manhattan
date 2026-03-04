<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * TextArea Component
 * Renders a multi-line text input with resize options and auto-resize support
 */
class TextArea extends Component
{
    private ?string $value = null;
    private ?string $placeholder = null;
    private ?string $name = null;
    private bool $required = false;
    private ?int $maxLength = null;
    private ?int $rows = 4;
    private ?int $cols = null;
    private bool $disabled = false;
    private string $resize = 'vertical'; // 'none', 'vertical', 'horizontal', 'both', 'auto'
    private ?int $charCountMax = null;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);
        
        if (isset($options['value'])) {
            $this->value = $options['value'];
        }
        if (isset($options['placeholder'])) {
            $this->placeholder = $options['placeholder'];
        }
        if (isset($options['name'])) {
            $this->name = $options['name'];
        }
        if (isset($options['required'])) {
            $this->required = (bool)$options['required'];
        }
        if (isset($options['maxLength'])) {
            $this->maxLength = (int)$options['maxLength'];
        }
        if (isset($options['rows'])) {
            $this->rows = (int)$options['rows'];
        }
        if (isset($options['cols'])) {
            $this->cols = (int)$options['cols'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }
        if (isset($options['resize'])) {
            $this->resize = $options['resize'];
        }
    }

    /**
     * Set the textarea value
     */
    public function value(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set placeholder text
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Set the name attribute (for form submission)
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Mark the field as required
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Set maximum length
     */
    public function maxLength(int $maxLength): self
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * Set number of visible rows
     */
    public function rows(int $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * Set number of visible columns
     */
    public function cols(int $cols): self
    {
        $this->cols = $cols;
        return $this;
    }

    /**
     * Set disabled state
     */
    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Enable character counter displayed below the textarea.
     */
    public function characterCount(int $max): self
    {
        $this->charCountMax = $max;
        return $this;
    }

    /**
     * Set resize behavior: 'none', 'vertical', 'horizontal', 'both', 'auto'
     */
    public function resize(string $resize): self
    {
        $this->resize = $resize;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'textarea';
    }

    protected function renderHtml(): string
    {
        $classes = ['m-textarea'];
        $classes[] = 'm-textarea-resize-' . $this->resize;
        $classes = array_merge($classes, $this->getExtraClasses());
        
        $attrs = [
            'id' => $this->id,
            'class' => implode(' ', $classes),
        ];

        if ($this->name) {
            $attrs['name'] = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        }

        if ($this->placeholder) {
            $attrs['placeholder'] = htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8');
        }

        if ($this->required) {
            $attrs['required'] = 'required';
        }

        if ($this->maxLength !== null) {
            $attrs['maxlength'] = (string)$this->maxLength;
        }

        if ($this->rows !== null) {
            $attrs['rows'] = (string)$this->rows;
        }

        if ($this->cols !== null) {
            $attrs['cols'] = (string)$this->cols;
        }

        if ($this->disabled) {
            $attrs['disabled'] = 'disabled';
        }

        $attrString = '';
        foreach ($attrs as $key => $val) {
            $attrString .= " {$key}=\"{$val}\"";
        }

        $eventAttrs = $this->renderEventAttributes();
        $extraAttrs = $this->renderAdditionalAttributes(array_keys($attrs));

        $value = $this->value ? htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8') : '';

        $counterHtml = '';
        if ($this->charCountMax !== null) {
            $currentLen = strlen($this->value ?? '');
            $counterHtml = '<span class="m-char-count" data-max="' . $this->charCountMax . '">' . $currentLen . '/' . $this->charCountMax . '</span>';
        }

        return <<<HTML
<div class="m-textarea-wrapper">
    <textarea{$attrString}{$eventAttrs}{$extraAttrs}>{$value}</textarea>
    {$counterHtml}
</div>
HTML;
    }
}
