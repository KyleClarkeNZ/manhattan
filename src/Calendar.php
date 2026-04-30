<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Calendar Component
 *
 * A full-featured calendar for plotting events and special dates.
 * Supports month and week views, event popovers, selectable dates,
 * week numbers, min/max date constraints, and dynamic JS API.
 *
 * Usage:
 *   <?= $m->calendar('eventsCal')
 *         ->events([
 *             ['date' => '2025-05-01', 'title' => 'May Day', 'type' => 'holiday', 'color' => '#4CAF50', 'description' => 'Public Holiday'],
 *             ['date' => '2025-05-15', 'title' => 'Audition Open', 'type' => 'submission', 'color' => '#118AB2', 'url' => '/events/1'],
 *         ])
 *         ->view('month')
 *         ->selectable()
 *         ->withPopover()
 *         ->weekStartsMonday()
 *         ->showWeekNumbers()
 *         ->highlightToday() ?>
 */
final class Calendar extends Component
{
    /** @var string month|week */
    private string $view = 'month';

    /** @var string|null YYYY-MM-DD — initial displayed date */
    private ?string $initialDate = null;

    /** @var bool Allow clicking on dates to select them */
    private bool $selectable = false;

    /** @var bool Highlight today's date (default: true) */
    private bool $highlightToday = true;

    /** @var bool Start the week on Monday instead of Sunday */
    private bool $weekStartsMonday = false;

    /** @var bool Show ISO week numbers down the left side */
    private bool $showWeekNumbers = false;

    /** @var bool Show an event-detail popover on event click */
    private bool $withPopover = false;

    /** @var string|null YYYY-MM-DD — earliest selectable/visible date */
    private ?string $minDate = null;

    /** @var string|null YYYY-MM-DD — latest selectable/visible date */
    private ?string $maxDate = null;

    /** @var string|null Fixed height for the grid (e.g. '520px'). Defaults to fluid. */
    private ?string $height = null;

    /** @var array<int, array<string, mixed>> */
    private array $events = [];

    // ── Fluent setters ───────────────────────────────────────────────────────

    /**
     * Set the calendar view. Default: 'month'.
     * Accepted: 'month' | 'week'
     */
    public function view(string $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Set the initial displayed date (YYYY-MM-DD). Defaults to today.
     */
    public function initialDate(string $date): self
    {
        $this->initialDate = $date;
        return $this;
    }

    /**
     * Allow clicking on dates to select them.
     * Fires the m:calendar:dateclick event.
     * Default: false
     */
    public function selectable(bool $val = true): self
    {
        $this->selectable = $val;
        return $this;
    }

    /**
     * Highlight today's date with a filled circle. Default: true
     */
    public function highlightToday(bool $val = true): self
    {
        $this->highlightToday = $val;
        return $this;
    }

    /**
     * Start the week on Monday instead of Sunday. Default: false
     */
    public function weekStartsMonday(bool $val = true): self
    {
        $this->weekStartsMonday = $val;
        return $this;
    }

    /**
     * Show ISO week numbers in the left gutter. Default: false
     */
    public function showWeekNumbers(bool $val = true): self
    {
        $this->showWeekNumbers = $val;
        return $this;
    }

    /**
     * Show an event-detail popover when an event chip is clicked. Default: false
     */
    public function withPopover(bool $val = true): self
    {
        $this->withPopover = $val;
        return $this;
    }

    /**
     * Earliest date that can be viewed/selected (YYYY-MM-DD).
     */
    public function minDate(string $date): self
    {
        $this->minDate = $date;
        return $this;
    }

    /**
     * Latest date that can be viewed/selected (YYYY-MM-DD).
     */
    public function maxDate(string $date): self
    {
        $this->maxDate = $date;
        return $this;
    }

    /**
     * Set a fixed height for the calendar grid (CSS value, e.g. '480px').
     * When omitted the grid uses natural content height.
     */
    public function height(string $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Replace all events.
     *
     * Each event is an associative array with these keys:
     *   - date        (string, YYYY-MM-DD) required
     *   - title       (string) required
     *   - type        (string) optional — displayed as a tag in the popover
     *   - color       (string) optional — CSS colour, e.g. '#4CAF50'
     *   - description (string) optional — body text in the popover
     *   - url         (string) optional — 'View details' link in the popover
     *
     * @param array<int, array<string, mixed>> $events
     */
    public function events(array $events): self
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Append a single event.
     *
     * @param array<string, mixed> $event
     */
    public function addEvent(array $event): self
    {
        $this->events[] = $event;
        return $this;
    }

    // ── Render ───────────────────────────────────────────────────────────────

    protected function getComponentType(): string
    {
        return 'calendar';
    }

    protected function renderHtml(): string
    {
        $id = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $classes = array_merge(['m-cal-widget'], $this->getExtraClasses());
        $classStr = implode(' ', array_map(static function (string $c): string {
            return htmlspecialchars($c, ENT_QUOTES, 'UTF-8');
        }, $classes));

        $style = $this->height !== null
            ? ' style="--m-calendar-height:' . htmlspecialchars($this->height, ENT_QUOTES, 'UTF-8') . '"'
            : '';

        $attrs  = ' data-view="' . htmlspecialchars($this->view, ENT_QUOTES, 'UTF-8') . '"';
        $attrs .= ' data-selectable="'        . ($this->selectable        ? 'true' : 'false') . '"';
        $attrs .= ' data-highlight-today="'   . ($this->highlightToday    ? 'true' : 'false') . '"';
        $attrs .= ' data-week-starts-monday="' . ($this->weekStartsMonday ? 'true' : 'false') . '"';
        $attrs .= ' data-show-week-numbers="' . ($this->showWeekNumbers   ? 'true' : 'false') . '"';
        $attrs .= ' data-popover="'           . ($this->withPopover       ? 'true' : 'false') . '"';

        if ($this->initialDate !== null) {
            $attrs .= ' data-initial-date="' . htmlspecialchars($this->initialDate, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->minDate !== null) {
            $attrs .= ' data-min-date="' . htmlspecialchars($this->minDate, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->maxDate !== null) {
            $attrs .= ' data-max-date="' . htmlspecialchars($this->maxDate, ENT_QUOTES, 'UTF-8') . '"';
        }

        $attrs .= $this->renderAdditionalAttributes();

        // Embed events data as a global JS variable (JSON_HEX_* prevents XSS via tag injection)
        $idJs      = json_encode($this->id);
        $eventsJs  = json_encode(
            $this->events,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
        );

        return <<<HTML
<div id="{$id}" class="{$classStr}"{$attrs}{$style}></div>
<script>
(function(){
    window.ManhattanCalendarData = window.ManhattanCalendarData || {};
    window.ManhattanCalendarData[{$idJs}] = {$eventsJs};
})();
</script>
HTML;
    }
}
