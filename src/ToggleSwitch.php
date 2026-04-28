<?php
declare(strict_types=1);

namespace Manhattan;

class ToggleSwitch extends Component
{
    private ?string $name = null;
    private ?string $value = null;
    private bool $checked = false;
    private bool $disabled = false;
    private ?string $label = null;
    private ?string $onLabel = null;
    private ?string $offLabel = null;

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

    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function onLabel(string $onLabel): self
    {
        $this->onLabel = $onLabel;
        return $this;
    }

    public function offLabel(string $offLabel): self
    {
        $this->offLabel = $offLabel;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'toggleswitch';
    }

    protected function renderHtml(): string
    {
        $wrapperClasses = array_merge(['m-switch-wrapper'], $this->getExtraClasses());
        $wrapperClassAttr = implode(' ', array_filter($wrapperClasses));

        $ariaChecked = $this->checked ? 'true' : 'false';

        $inputAttrs = [
            'id' => htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            'type' => 'checkbox',
            'class' => 'm-switch-input',
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

        $inputAttrString = '';
        foreach ($inputAttrs as $key => $val) {
            $inputAttrString .= " {$key}=\"{$val}\"";
        }

        $eventAttrs = $this->renderEventAttributes();
        $extraAttrs = $this->renderAdditionalAttributes(array_keys($inputAttrs));

        $labelText = $this->label !== null
            ? '<span class="m-switch-label">' . htmlspecialchars($this->label, ENT_QUOTES, 'UTF-8') . '</span>'
            : '';

        $stateLabels = '';
        if ($this->onLabel !== null && $this->offLabel !== null) {
            $stateLabels = '<span class="m-switch-state-label m-switch-state-on" aria-hidden="true">' 
                . htmlspecialchars($this->onLabel, ENT_QUOTES, 'UTF-8') 
                . '</span>'
                . '<span class="m-switch-state-label m-switch-state-off" aria-hidden="true">' 
                . htmlspecialchars($this->offLabel, ENT_QUOTES, 'UTF-8') 
                . '</span>';
        }

        return <<<HTML
<div class="{$wrapperClassAttr}">
    <label class="m-switch" for="{$this->id}">
        <input{$inputAttrString}{$eventAttrs}{$extraAttrs} role="switch" aria-checked="{$ariaChecked}">
        <span class="m-switch-slider" aria-hidden="true"></span>
        {$labelText}
        {$stateLabels}
    </label>
</div>
HTML;
    }
}
