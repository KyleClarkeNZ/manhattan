<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Rating Component
 *
 * A star-based rating selector. Supports read-only display and interactive
 * editing with keyboard navigation, half-star support, and an onChange callback.
 *
 * Usage — read-only display:
 *   $m->rating('productRating')->value(3.5)->max(5)->halfStars()->readonly()
 *
 * Usage — interactive:
 *   $m->rating('myRating')->value(4)->max(5)->label('Quality')->onChange('handleRating')
 *
 * Usage — in a DataGrid column:
 *   ['field' => 'score', 'title' => 'Score', 'component' => [
 *       'type'      => 'rating',
 *       'valueBind' => 'score',
 *       'max'       => 5,
 *   ]]
 */
final class Rating extends Component
{
    private float   $value     = 0;
    private int     $max       = 5;
    private bool    $readonly  = false;
    private bool    $halfStars = false;
    private ?string $label     = null;
    private string  $size      = 'md';
    private string  $color     = '';
    private ?string $onChange  = null;

    // ── Fluent builder ────────────────────────────────────────────────────────

    public function value(float $value): self
    {
        $this->value = max(0.0, $value);
        return $this;
    }

    public function max(int $max): self
    {
        $this->max = max(1, $max);
        return $this;
    }

    /** Make the rating display-only (no click/keyboard interaction). */
    public function readonly(bool $readonly = true): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    /** Enable half-star increments (visual display only in read-only mode; interactive mode rounds to whole). */
    public function halfStars(bool $half = true): self
    {
        $this->halfStars = $half;
        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string $size sm|md|lg
     */
    public function size(string $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function sm(): self { return $this->size('sm'); }
    public function lg(): self { return $this->size('lg'); }

    /**
     * Override the star colour.
     * @param string $color primary|warning|danger|success|purple
     */
    public function color(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * JS callback — function name or expression called with (value, element).
     * A DOM event 'm-rating-change' is also dispatched.
     */
    public function onChange(string $callback): self
    {
        $this->onChange = $callback;
        return $this;
    }

    // ── Component interface ───────────────────────────────────────────────────

    protected function getComponentType(): string
    {
        return 'rating';
    }

    protected function renderHtml(): string
    {
        $id        = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $classes   = array_merge(['m-rating', 'm-rating-' . $this->size], $this->getExtraClasses());
        if ($this->readonly)       { $classes[] = 'm-rating-readonly'; }
        if ($this->color !== '')   { $classes[] = 'm-rating-color-' . htmlspecialchars($this->color, ENT_QUOTES, 'UTF-8'); }
        $classAttr  = htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');
        $extra      = $this->renderAdditionalAttributes(['id', 'class', 'data-rating-config']);
        $eventAttrs = $this->renderEventAttributes();

        $config = [
            'value'     => $this->value,
            'max'       => $this->max,
            'readonly'  => $this->readonly,
            'halfStars' => $this->halfStars,
            'onChange'  => $this->onChange,
        ];
        $configJson = htmlspecialchars(
            json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP) ?: '{}',
            ENT_QUOTES,
            'UTF-8'
        );

        $labelHtml = '';
        if ($this->label !== null) {
            $labelHtml = '<span class="m-rating-label">'
                . htmlspecialchars($this->label, ENT_QUOTES, 'UTF-8')
                . '</span>';
        }

        $ariaLabel = htmlspecialchars($this->label ?? 'Rating', ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div id="{$id}" class="{$classAttr}" data-rating-config="{$configJson}" role="group" aria-label="{$ariaLabel}"{$eventAttrs}{$extra}>
    {$labelHtml}
    <span class="m-rating-stars" aria-hidden="true"></span>
    <span class="m-rating-value-text"></span>
</div>
HTML;
    }
}
