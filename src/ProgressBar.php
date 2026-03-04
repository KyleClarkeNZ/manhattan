<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * ProgressBar Component
 *
 * A linear progress bar with optional label, percentage display, and colour variant.
 *
 * Usage:
 *   $m->progressBar('stepsProgress')->value(6500)->max(10000)->label('Steps')->showPercent()
 *   $m->progressBar('goalBar')->value(3)->max(5)->label('Tasks done')->success()
 */
final class ProgressBar extends Component
{
    private float $value = 0;
    private float $max = 100;
    private ?string $label = null;
    private bool $showPercent = false;
    private string $variant = 'primary';
    private bool $striped = false;
    private bool $animated = false;

    public function value(float $value): self
    {
        $this->value = max(0.0, $value);
        return $this;
    }

    public function max(float $max): self
    {
        $this->max = max(1.0, $max);
        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /** Show the percentage value inside/beside the bar */
    public function showPercent(bool $show = true): self
    {
        $this->showPercent = $show;
        return $this;
    }

    /** @param string $variant primary|success|warning|danger|purple */
    public function variant(string $variant): self
    {
        $this->variant = $variant;
        return $this;
    }

    public function primary(): self   { return $this->variant('primary'); }
    public function success(): self   { return $this->variant('success'); }
    public function warning(): self   { return $this->variant('warning'); }
    public function danger(): self    { return $this->variant('danger'); }
    public function purple(): self    { return $this->variant('purple'); }

    /** Add diagonal stripe pattern */
    public function striped(bool $striped = true): self
    {
        $this->striped = $striped;
        return $this;
    }

    /** Animate the stripes (requires striped) */
    public function animated(bool $animated = true): self
    {
        $this->animated = $animated;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'progress-bar';
    }

    protected function renderHtml(): string
    {
        $pct = $this->max > 0 ? min(100.0, ($this->value / $this->max) * 100) : 0.0;
        $pctRounded = round($pct, 1);

        $classes = array_merge(['m-progress'], $this->getExtraClasses());
        $classAttr = implode(' ', $classes);
        $idAttr = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);

        $fillClasses = ['m-progress-fill', 'm-progress-fill-' . htmlspecialchars($this->variant, ENT_QUOTES, 'UTF-8')];
        if ($this->striped) {
            $fillClasses[] = 'm-progress-striped';
        }
        if ($this->animated) {
            $fillClasses[] = 'm-progress-animated';
        }
        $fillClassAttr = implode(' ', $fillClasses);

        $labelHtml = '';
        if ($this->label !== null) {
            $labelHtml = '<div class="m-progress-label">'
                . htmlspecialchars($this->label, ENT_QUOTES, 'UTF-8')
                . ($this->showPercent ? '<span class="m-progress-pct">' . $pctRounded . '%</span>' : '')
                . '</div>';
        }

        $ariaValue = htmlspecialchars((string)$this->value, ENT_QUOTES, 'UTF-8');
        $ariaMax   = htmlspecialchars((string)$this->max, ENT_QUOTES, 'UTF-8');
        $ariaLabel = htmlspecialchars($this->label ?? 'Progress', ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div id="{$idAttr}" class="{$classAttr}"{$extraAttrs}>
    {$labelHtml}
    <div class="m-progress-track" role="progressbar" aria-valuenow="{$ariaValue}" aria-valuemax="{$ariaMax}" aria-label="{$ariaLabel}">
        <div class="{$fillClassAttr}" style="width:{$pctRounded}%"></div>
    </div>
</div>
HTML;
    }
}
