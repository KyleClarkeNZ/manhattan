<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * StatCard Component
 *
 * A compact metric display card showing a single value with a label.
 * Optionally shows an icon, a colour-variant accent, and a delta change indicator.
 *
 * Usage:
 *   $m->statCard('activeCount')->value(12)->label('Active Tasks')->primary()
 *   $m->statCard('overdueCount')->value(3)->label('Overdue')->danger()->icon('fa-exclamation-circle')
 *   $m->statCard('steps')->value('8,432')->label('Steps Today')->icon('fa-walking')->delta('+12%')->deltaUp()
 */
final class StatCard extends Component
{
    private string $value = '—';
    private string $label = '';
    private ?string $icon = null;
    private string $variant = 'primary';
    private ?string $delta = null;
    private ?bool $deltaUp = null; // true=up(green), false=down(red), null=neutral

    public function value(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function icon(string $faIcon): self
    {
        $this->icon = $faIcon;
        return $this;
    }

    /** @param string $variant primary|success|warning|danger|purple|secondary */
    public function variant(string $variant): self
    {
        $this->variant = $variant;
        return $this;
    }

    public function primary(): self    { return $this->variant('primary'); }
    public function success(): self    { return $this->variant('success'); }
    public function warning(): self    { return $this->variant('warning'); }
    public function danger(): self     { return $this->variant('danger'); }
    public function purple(): self     { return $this->variant('purple'); }
    public function secondary(): self  { return $this->variant('secondary'); }

    /**
     * Optional small delta indicator (e.g. "+12%" or "3 new")
     */
    public function delta(string $delta): self
    {
        $this->delta = $delta;
        return $this;
    }

    /** Mark delta as positive (green arrow up) */
    public function deltaUp(): self
    {
        $this->deltaUp = true;
        return $this;
    }

    /** Mark delta as negative (red arrow down) */
    public function deltaDown(): self
    {
        $this->deltaUp = false;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'stat-card';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(
            ['m-stat-card', 'm-stat-card-' . htmlspecialchars($this->variant, ENT_QUOTES, 'UTF-8')],
            $this->getExtraClasses()
        );
        $classAttr = implode(' ', $classes);
        $idAttr = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);
        $eventAttrs = $this->renderEventAttributes();

        $iconHtml = '';
        if ($this->icon !== null) {
            $iconHtml = '<div class="m-stat-card-icon">' . (new Icon('', $this->icon))->render() . '</div>';
        }

        $valueHtml = '<div class="m-stat-card-value">' . htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8') . '</div>';
        $labelHtml = '<div class="m-stat-card-label">' . htmlspecialchars($this->label, ENT_QUOTES, 'UTF-8') . '</div>';

        $deltaHtml = '';
        if ($this->delta !== null) {
            $deltaClass = 'm-stat-card-delta';
            $deltaIcon = '';
            if ($this->deltaUp === true) {
                $deltaClass .= ' m-stat-card-delta-up';
                $deltaIcon = (new Icon('', 'fa-arrow-up'))->render() . ' ';
            } elseif ($this->deltaUp === false) {
                $deltaClass .= ' m-stat-card-delta-down';
                $deltaIcon = (new Icon('', 'fa-arrow-down'))->render() . ' ';
            }
            $deltaHtml = '<div class="' . $deltaClass . '">' . $deltaIcon . htmlspecialchars($this->delta, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        return <<<HTML
<div id="{$idAttr}" class="{$classAttr}"{$eventAttrs}{$extraAttrs}>
    {$iconHtml}
    <div class="m-stat-card-body">
        {$valueHtml}
        {$labelHtml}
        {$deltaHtml}
    </div>
</div>
HTML;
    }
}
