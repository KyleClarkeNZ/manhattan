<?php
declare(strict_types=1);

namespace Manhattan;

class TextBox extends Component
{
    private ?string $value = null;
    private ?string $placeholder = null;
    private ?string $name = null;
    private bool $required = false;
    private ?int $minLength = null;
    private ?int $maxLength = null;
    private bool $disabled = false;
    private string $type = 'text';
    private ?int $charCountMax = null;

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
        if (isset($options['minLength'])) {
            $this->minLength = (int)$options['minLength'];
        }
        if (isset($options['maxLength'])) {
            $this->maxLength = (int)$options['maxLength'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }
        if (isset($options['type'])) {
            $this->type = (string)$options['type'];
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

    public function minLength(int $minLength): self
    {
        $this->minLength = $minLength;
        return $this;
    }

    public function maxLength(int $maxLength): self
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Enable character counter. Wraps the input in a div and shows X/max below.
     */
    public function characterCount(int $max): self
    {
        $this->charCountMax = $max;
        return $this;
    }

    /**
     * Convenience preset for email fields.
     */
    public function email(): self
    {
        return $this
            ->type('email')
            ->autocomplete('email')
            ->attr('inputmode', 'email')
            ->attr('spellcheck', 'false')
            ->attr('autocapitalize', 'none');
    }

    /**
     * Convenience preset for password fields.
     */
    public function password(string $autocomplete = 'current-password'): self
    {
        return $this
            ->type('password')
            ->autocomplete($autocomplete);
    }

    /**
     * Set the HTML autocomplete attribute (e.g. username, current-password, off)
     */
    public function autocomplete(string $value): self
    {
        return $this->attr('autocomplete', $value);
    }

    /**
     * Set the HTML pattern attribute (regular expression)
     */
    public function pattern(string $pattern): self
    {
        return $this->attr('pattern', $pattern);
    }

    protected function getComponentType(): string
    {
        return 'textbox';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-textbox'], $this->getExtraClasses());

        $attrs = [
            'id' => htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            'type' => htmlspecialchars($this->type, ENT_QUOTES, 'UTF-8'),
            'class' => implode(' ', $classes),
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
        if ($this->required) {
            $attrs['required'] = 'required';
        }
        if ($this->minLength !== null) {
            $attrs['minlength'] = (string)$this->minLength;
        }
        if ($this->maxLength !== null) {
            $attrs['maxlength'] = (string)$this->maxLength;
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

        if ($this->charCountMax !== null) {
            $currentLen = strlen($this->value ?? '');
            $max = $this->charCountMax;
            $countId = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8') . '-char-count';
            return '<div class="m-textbox-wrapper">' .
                "<input{$attrString} data-char-count=\"{$max}\" aria-describedby=\"{$countId}\"{$eventAttrs}{$extraAttrs}>" .
                '<span id="' . $countId . '" class="m-char-count" data-max="' . $max . '" aria-live="polite" aria-atomic="true">' . $currentLen . '/' . $max . '</span>' .
                '</div>';
        }

        return "<input{$attrString}{$eventAttrs}{$extraAttrs}>";
    }
}
