<?php
declare(strict_types=1);

namespace Manhattan;

class Checkbox extends Component
{
    private ?string $name = null;
    private ?string $value = null;
    private bool $checked = false;
    private bool $disabled = false;
    private bool $required = false;
    private ?string $label = null;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['name'])) {
            $this->name = (string)$options['name'];
        }
        if (isset($options['value'])) {
            $this->value = (string)$options['value'];
        }
        if (isset($options['checked'])) {
            $this->checked = (bool)$options['checked'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }
        if (isset($options['required'])) {
            $this->required = (bool)$options['required'];
        }
        if (isset($options['label'])) {
            $this->label = (string)$options['label'];
        }
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function value(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function checked(bool $checked = true): self
    {
        $this->checked = $checked;
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'checkbox';
    }

    protected function renderHtml(): string
    {
        $labelClasses = array_merge(['m-choice', 'm-checkbox'], $this->getExtraClasses());
        $labelClassAttr = implode(' ', array_filter($labelClasses));

        $inputAttrs = [
            'id' => htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            'type' => 'checkbox',
            'class' => 'm-choice-input m-checkbox-input',
        ];

        if ($this->name) {
            $inputAttrs['name'] = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        }
        if ($this->value !== null) {
            $inputAttrs['value'] = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');
        }
        if ($this->checked) {
            $inputAttrs['checked'] = 'checked';
        }
        if ($this->disabled) {
            $inputAttrs['disabled'] = 'disabled';
        }
        if ($this->required) {
            $inputAttrs['required'] = 'required';
        }

        $inputAttrString = '';
        foreach ($inputAttrs as $key => $val) {
            $inputAttrString .= " {$key}=\"{$val}\"";
        }

        $eventAttrs = $this->renderEventAttributes();
        $extraAttrs = $this->renderAdditionalAttributes(array_keys($inputAttrs));

        $labelText = $this->label !== null
            ? '<span class="m-choice-label">' . htmlspecialchars($this->label, ENT_QUOTES, 'UTF-8') . '</span>'
            : '';

        return <<<HTML
<div class="m-choice-wrapper">
    <label class="{$labelClassAttr}" for="{$this->id}">
        <input{$inputAttrString}{$eventAttrs}{$extraAttrs}>
        <span class="m-choice-indicator" aria-hidden="true"></span>
        {$labelText}
    </label>
</div>
HTML;
    }
}
