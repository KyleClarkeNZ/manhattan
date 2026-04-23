<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * TimePicker component — custom time selection control.
 *
 * Renders a hidden text input which is replaced at runtime by a styled
 * trigger button + scrollable hour/minute dropdown panel.
 *
 * By default the picker stores and submits times as 24-hour HH:MM.  Use
 * ->format() to change the submitted value format and ->ampm() (or
 * ->use24Hour(false)) to switch the dropdown UI to 12-hour AM/PM mode.
 *
 * Format tokens (PHP date-style):
 *   H  24-hour hours with leading zero (00–23)
 *   G  24-hour hours without leading zero (0–23)
 *   h  12-hour hours with leading zero (01–12)
 *   g  12-hour hours without leading zero (1–12)
 *   i  minutes with leading zero (00–59)
 *   A  uppercase AM/PM
 *   a  lowercase am/pm
 *
 * Usage:
 *   echo $m->timepicker('meetingTime')
 *       ->name('meeting_time')
 *       ->value('14:30')
 *       ->step(15)
 *       ->ampm()              // 12-hour UI
 *       ->format('H:i')      // submit as 24-hour HH:MM (MySQL TIME)
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
    private ?string $outputFormat = null;

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
        if (isset($options['outputFormat'])) {
            $this->outputFormat = (string)$options['outputFormat'];
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

    /**
     * Switch the picker UI to 12-hour AM/PM mode.
     * Equivalent to ->use24Hour(false).
     * Pass false to revert to 24-hour mode.
     */
    public function ampm(bool $use = true): self
    {
        $this->use24Hour = !$use;
        return $this;
    }

    /**
     * Set the format of the value written to the input (and submitted to
     * the server).  Uses PHP date-style tokens:
     *
     *   H  24-hour hours with leading zero (00–23)
     *   G  24-hour hours without leading zero (0–23)
     *   h  12-hour hours with leading zero (01–12)
     *   g  12-hour hours without leading zero (1–12)
     *   i  minutes with leading zero (00–59)
     *   A  uppercase AM/PM
     *   a  lowercase am/pm
     *
     * Default: 'H:i'  (24-hour HH:MM — compatible with MySQL TIME).
     *
     * Example: ->format('g:i A')  stores '2:30 PM'
     *          ->format('H:i')    stores '14:30'
     */
    public function format(string $fmt): self
    {
        $this->outputFormat = $fmt;
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
        if ($this->outputFormat !== null) {
            $attrs['data-format'] = htmlspecialchars($this->outputFormat, ENT_QUOTES, 'UTF-8');
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
