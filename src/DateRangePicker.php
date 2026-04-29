<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * DateRangePicker — a dual-calendar date-range selector.
 *
 * Renders two hidden inputs (start + end) and a styled trigger.
 * JavaScript enhances the trigger into a dropdown panel with two
 * side-by-side month calendars, range highlighting, optional
 * preset shortcuts, and keyboard navigation.
 *
 * Basic usage:
 *   echo $m->daterangepicker('bookingDates')
 *       ->startName('start')
 *       ->endName('end')
 *       ->placeholder('Select date range…')
 *       ->showPresets();
 */
class DateRangePicker extends Component
{
    private ?string $startValue       = null;
    private ?string $endValue         = null;
    private ?string $startName        = null;
    private ?string $endName          = null;
    private string  $placeholder      = '';
    private string  $startPlaceholder = 'Start date';
    private string  $endPlaceholder   = 'End date';
    private ?string $min              = null;
    private ?string $max              = null;
    private string  $format           = 'Y-m-d';
    private bool    $disabled         = false;
    private bool    $highlightToday   = true;
    private bool    $showPresets      = false;
    private bool    $weekStartsMonday = false;
    private bool    $singleMonth      = false;
    private bool    $required         = false;
    private bool    $autoApply        = false;

    /**
     * Custom presets array.
     * Each entry: ['label' => string, 'start' => 'Y-m-d', 'end' => 'Y-m-d']
     * When null the default presets are used.
     *
     * @var array<int, array{label: string, start: string, end: string}>|null
     */
    private ?array $presets = null;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['startValue']))       { $this->startValue       = (string)$options['startValue'];  }
        if (isset($options['endValue']))         { $this->endValue         = (string)$options['endValue'];    }
        if (isset($options['startName']))        { $this->startName        = (string)$options['startName'];   }
        if (isset($options['endName']))          { $this->endName          = (string)$options['endName'];     }
        if (isset($options['placeholder']))      { $this->placeholder      = (string)$options['placeholder']; }
        if (isset($options['startPlaceholder'])) { $this->startPlaceholder = (string)$options['startPlaceholder']; }
        if (isset($options['endPlaceholder']))   { $this->endPlaceholder   = (string)$options['endPlaceholder'];   }
        if (isset($options['min']))              { $this->min              = (string)$options['min'];  }
        if (isset($options['max']))              { $this->max              = (string)$options['max'];  }
        if (isset($options['format']))           { $this->format           = (string)$options['format']; }
        if (isset($options['disabled']))         { $this->disabled         = (bool)$options['disabled']; }
        if (isset($options['highlightToday']))   { $this->highlightToday   = (bool)$options['highlightToday']; }
        if (isset($options['showPresets']))      { $this->showPresets      = (bool)$options['showPresets']; }
        if (isset($options['weekStartsMonday'])) { $this->weekStartsMonday = (bool)$options['weekStartsMonday']; }
        if (isset($options['singleMonth']))      { $this->singleMonth      = (bool)$options['singleMonth']; }
        if (isset($options['required']))         { $this->required         = (bool)$options['required']; }
        if (isset($options['autoApply']))        { $this->autoApply        = (bool)$options['autoApply']; }
        if (isset($options['presets']) && is_array($options['presets'])) {
            $this->presets = $options['presets'];
        }
    }

    // ── Fluent setters ──────────────────────────────────────────────────────

    /** Set the start date value (format matching ->format(), default Y-m-d). */
    public function startValue(?string $value): self
    {
        $this->startValue = $value;
        return $this;
    }

    /** Set the end date value (format matching ->format(), default Y-m-d). */
    public function endValue(?string $value): self
    {
        $this->endValue = $value;
        return $this;
    }

    /** Set both start and end values at once. */
    public function values(?string $start, ?string $end): self
    {
        $this->startValue = $start;
        $this->endValue   = $end;
        return $this;
    }

    /** Form field name for the start date hidden input. */
    public function startName(string $name): self
    {
        $this->startName = $name;
        return $this;
    }

    /** Form field name for the end date hidden input. */
    public function endName(string $name): self
    {
        $this->endName = $name;
        return $this;
    }

    /**
     * Combined placeholder shown in the trigger when no range is selected.
     * If omitted, startPlaceholder + arrow + endPlaceholder are shown.
     */
    public function placeholder(string $text): self
    {
        $this->placeholder = $text;
        return $this;
    }

    /** Placeholder for the start segment of the trigger. Default: "Start date". */
    public function startPlaceholder(string $text): self
    {
        $this->startPlaceholder = $text;
        return $this;
    }

    /** Placeholder for the end segment of the trigger. Default: "End date". */
    public function endPlaceholder(string $text): self
    {
        $this->endPlaceholder = $text;
        return $this;
    }

    /** Minimum selectable date. */
    public function min(string $date): self
    {
        $this->min = $date;
        return $this;
    }

    /** Maximum selectable date. */
    public function max(string $date): self
    {
        $this->max = $date;
        return $this;
    }

    /**
     * PHP date() format string used for both display and values.
     * Default: 'Y-m-d'. Only Y, m, d tokens are supported by the JS layer.
     */
    public function format(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    /** Disable the picker. */
    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /** Highlight today's date in the calendars. Default: true. */
    public function highlightToday(bool $highlight = true): self
    {
        $this->highlightToday = $highlight;
        return $this;
    }

    /**
     * Show a preset shortcuts panel (Today, Yesterday, Last 7 days, etc.).
     * Default: false.
     */
    public function showPresets(bool $show = true): self
    {
        $this->showPresets = $show;
        return $this;
    }

    /**
     * Provide custom preset entries.
     * Each entry must have: ['label' => string, 'start' => 'Y-m-d', 'end' => 'Y-m-d'].
     * Calling this automatically enables the preset panel.
     *
     * @param array<int, array{label: string, start: string, end: string}> $presets
     */
    public function presets(array $presets): self
    {
        $this->presets   = $presets;
        $this->showPresets = true;
        return $this;
    }

    /** Start week on Monday instead of Sunday. Default: false (Sunday). */
    public function weekStartsMonday(bool $monday = true): self
    {
        $this->weekStartsMonday = $monday;
        return $this;
    }

    /**
     * Show a single calendar month instead of two side-by-side.
     * Two calendars are shown by default; on small screens the picker
     * automatically collapses to a single calendar regardless of this setting.
     */
    public function singleMonth(bool $single = true): self
    {
        $this->singleMonth = $single;
        return $this;
    }

    /**
     * Auto-apply the selection when both dates are picked, without needing
     * the Apply button. Default: false.
     */
    public function autoApply(bool $auto = true): self
    {
        $this->autoApply = $auto;
        return $this;
    }

    /** Mark the field as required (adds required attribute). */
    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    // ── Component contract ──────────────────────────────────────────────────

    protected function getComponentType(): string
    {
        return 'daterangepicker';
    }

    /**
     * Override render() so the label's `for` points to the JS-created trigger
     * button ({id}_trigger) rather than the outer wrapper div.
     */
    public function render(): string
    {
        if ($this->getLabelText() === '') {
            return $this->renderHtml();
        }

        $labelComp = new Label($this->id . '_label', $this->getLabelText());
        $labelComp->for($this->id . '_trigger');
        if ($this->getLabelRequired()) {
            $labelComp->required();
        }
        if ($this->getLabelHint() !== '') {
            $labelComp->hint($this->getLabelHint());
        }
        if ($this->getLabelIcon() !== '') {
            $labelComp->icon($this->getLabelIcon());
        }
        return (string)$labelComp . $this->renderHtml();
    }

    protected function renderHtml(): string
    {
        $id = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        // ── Wrapper data attributes ─────────────────────────────────────────
        $wrapperClasses   = array_merge(['m-daterangepicker-wrapper'], $this->getExtraClasses());
        $wrapperClassAttr = implode(' ', $wrapperClasses);

        $data = [
            'data-format'       => htmlspecialchars($this->format, ENT_QUOTES, 'UTF-8'),
            'data-start-ph'     => htmlspecialchars($this->startPlaceholder, ENT_QUOTES, 'UTF-8'),
            'data-end-ph'       => htmlspecialchars($this->endPlaceholder,   ENT_QUOTES, 'UTF-8'),
        ];

        if ($this->placeholder !== '') {
            $data['data-placeholder'] = htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8');
        }
        if ($this->min !== null) {
            $data['data-min'] = htmlspecialchars($this->min, ENT_QUOTES, 'UTF-8');
        }
        if ($this->max !== null) {
            $data['data-max'] = htmlspecialchars($this->max, ENT_QUOTES, 'UTF-8');
        }
        if ($this->highlightToday) {
            $data['data-highlight-today'] = 'true';
        }
        if ($this->showPresets) {
            $data['data-show-presets'] = 'true';
        }
        if ($this->weekStartsMonday) {
            $data['data-week-start'] = '1';
        }
        if ($this->singleMonth) {
            $data['data-single-month'] = 'true';
        }
        if ($this->autoApply) {
            $data['data-auto-apply'] = 'true';
        }
        if ($this->disabled) {
            $data['data-disabled'] = 'true';
        }
        if ($this->presets !== null) {
            // Encode custom presets as JSON for JS consumption.
            $data['data-presets'] = htmlspecialchars(json_encode($this->presets) ?: '[]', ENT_QUOTES, 'UTF-8');
        }

        $dataAttrs = '';
        foreach ($data as $key => $val) {
            $dataAttrs .= " {$key}=\"{$val}\"";
        }

        $extraAttrs = $this->renderAdditionalAttributes(array_keys($data));
        $eventAttrs = $this->renderEventAttributes();

        // ── Hidden inputs ───────────────────────────────────────────────────
        $startInputAttrs = ' id="' . $id . '_start" type="hidden" class="m-drp-start"';
        if ($this->startName !== null) {
            $startInputAttrs .= ' name="' . htmlspecialchars($this->startName, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->startValue !== null) {
            $startInputAttrs .= ' value="' . htmlspecialchars($this->startValue, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->required) {
            $startInputAttrs .= ' required="required"';
        }

        $endInputAttrs = ' id="' . $id . '_end" type="hidden" class="m-drp-end"';
        if ($this->endName !== null) {
            $endInputAttrs .= ' name="' . htmlspecialchars($this->endName, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->endValue !== null) {
            $endInputAttrs .= ' value="' . htmlspecialchars($this->endValue, ENT_QUOTES, 'UTF-8') . '"';
        }

        $html  = '<div id="' . $id . '" class="' . $wrapperClassAttr . '"' . $dataAttrs . $extraAttrs . $eventAttrs . '>';
        $html .= '<input' . $startInputAttrs . '>';
        $html .= '<input' . $endInputAttrs . '>';
        $html .= '</div>';

        return $html;
    }
}
