<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * TimePicker component — custom time selection control.
 *
 * Renders a hidden text input which is replaced at runtime by a styled
 * trigger button + scrollable hour/minute dropdown panel. The internal
 * value is always stored and returned in 24-hour HH:MM format.
 *
 * Usage:
 *   echo $m->timepicker('meetingTime')
 *       ->name('meeting_time')
 *       ->value('14:30')
 *       ->step(15)
 *       ->showNowButton()
 *       ->label('Time')
 *       ->labelHint('Optional');
 */
class TimePicker extends Component
{
    private ?string $value = null;
    private ?string $name = null;
    private ?string $placeholder = null;
    private int $step = 15;
    private bool $disabled = false;
    private bool $showNowButton = false;
    private bool $use24Hour = true;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['value'])) {
            $this->value = (string)$options['value'];
        }
        if (isset($options['name'])) {
            $this->name = (string)$options['name'];
        }
        if (isset($options['placeholder'])) {
            $this->placeholder = (string)$options['placeholder'];
        }
        if (isset($options['step'])) {
            $this->step = (int)$options['step'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }
        if (isset($options['showNowButton'])) {
            $this->showNowButton = (bool)$options['showNowButton'];
        }
        if (isset($options['use24Hour'])) {
            $this->use24Hour = (bool)$options['use24Hour'];
        }
    }

    /**
     * Set the initial value in 24-hour HH:MM format (e.g. '14:30').
     * Pass null or empty string to clear.
     */
    public function value(?string $value): self
    {
        $this->value = ($value !== '' ? $value : null);
        return $this;
    }

    /**
     * Set the form field name attribute.
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the placeholder text shown when no time is selected.
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Set the minute step interval (e.g. 5, 10, 15, 30). Default: 15.
     */
    public function step(int $minutes): self
    {
        $this->step = max(1, $minutes);
        return $this;
    }

    /**
     * Disable the time picker.
     */
    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Show a "Now" button in the dropdown footer to select the current time.
     */
    public function showNowButton(bool $show = true): self
    {
        $this->showNowButton = $show;
        return $this;
    }

    /**
     * Display and store times in 24-hour format (default).
     * Call ->use24Hour(false) to switch to 12-hour AM/PM display.
     */
    public function use24Hour(bool $use = true): self
    {
        $this->use24Hour = $use;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'timepicker';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-timepicker'], $this->getExtraClasses());

        $attrs = [
            'id'           => htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            'type'         => 'text',
            'class'        => implode(' ', $classes),
            'autocomplete' => 'off',
            'data-step'    => (string)$this->step,
        ];

        if ($this->name !== null) {
            $attrs['name'] = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        }
        if ($this->placeholder !== null) {
            $attrs['placeholder'] = htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8');
        }
        if ($this->value !== null && $this->value !== '') {
            $attrs['value'] = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');
        }
        if ($this->disabled) {
            $attrs['disabled'] = 'disabled';
        }
        if ($this->showNowButton) {
            $attrs['data-show-now'] = 'true';
        }
        if (!$this->use24Hour) {
            $attrs['data-12h'] = 'true';
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
