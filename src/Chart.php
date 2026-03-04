<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Chart Component (SVG)
 *
 * Supports:
 * - Bar chart (single series)
 * - Line/time-series chart (single series)
 *
 * This is intentionally dependency-free (no external JS libraries).
 */
final class Chart extends Component
{
    private string $type = 'bar';

    /** @var string[] */
    private array $labels = [];

    /** @var array<int, array{name: string, values: float[], color: string}> */
    private array $series = [];

    private int $width = 640;
    private int $height = 220;

    private ?float $yMax = null;

    /** @var array{value: float, color: string, label: string}|null */
    private ?array $goalLine = null;

    public function type(string $type): self
    {
        $type = strtolower(trim($type));
        if (!in_array($type, ['bar', 'line'], true)) {
            $type = 'bar';
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @param string[] $labels
     */
    public function labels(array $labels): self
    {
        $this->labels = array_map(static fn($v) => (string)$v, $labels);
        return $this;
    }

    /**
     * Add a single series. For now, only the first series is rendered.
     *
     * @param array<int, float|int|string|null> $values
     */
    public function series(string $name, array $values, ?string $color = null): self
    {
        $vals = [];
        foreach ($values as $v) {
            if ($v === null || $v === '') {
                $vals[] = 0.0;
                continue;
            }
            $vals[] = is_numeric($v) ? (float)$v : 0.0;
        }

        $this->series[] = [
            'name' => $name,
            'values' => $vals,
            'color' => $color ?: '#2196F3',
        ];
        return $this;
    }

    public function width(int $width): self
    {
        $this->width = max(240, $width);
        return $this;
    }

    public function height(int $height): self
    {
        $this->height = max(140, $height);
        return $this;
    }

    public function yMax(?float $yMax): self
    {
        $this->yMax = $yMax;
        return $this;
    }

    /**
     * Draw a horizontal dashed goal line at the given value.
     *
     * @param float  $value The Y-axis value at which to draw the line
     * @param string $color Line colour (default: #e74c3c)
     * @param string $label Label shown at the right end of the line
     */
    public function goal(float $value, string $color = '#e74c3c', string $label = 'Goal'): self
    {
        $this->goalLine = ['value' => $value, 'color' => $color, 'label' => $label];
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'chart';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-chart', 'm-chart-' . $this->type], $this->getExtraClasses());
        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');
        $idAttr = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);
        $eventAttrs = $this->renderEventAttributes();

        $labels = $this->labels;
        $series0 = $this->series[0] ?? ['name' => '', 'values' => [], 'color' => '#2196F3'];
        $seriesNameRaw = (string)($series0['name'] ?? '');
        $values = $series0['values'];

        $n = max(count($labels), count($values));
        if ($n <= 0) {
            $empty = '<div class="m-chart-empty">No data</div>';
            return "<div id=\"{$idAttr}\" class=\"{$classAttr}\"{$eventAttrs}{$extraAttrs}>{$empty}</div>";
        }

        // Normalize lengths
        for ($i = 0; $i < $n; $i++) {
            if (!isset($labels[$i])) {
                $labels[$i] = (string)($i + 1);
            }
            if (!isset($values[$i])) {
                $values[$i] = 0.0;
            }
        }

        $maxVal = 0.0;
        foreach ($values as $v) {
            $maxVal = max($maxVal, (float)$v);
        }
        $yMax = $this->yMax !== null ? max(0.0, $this->yMax) : $maxVal;
        if ($yMax <= 0.0) {
            $yMax = 1.0;
        }

        $w = $this->width;
        $h = $this->height;

        $padL = 42;
        $padR = 14;
        $padT = 14;
        $padB = 34;

        $plotW = max(1, $w - $padL - $padR);
        $plotH = max(1, $h - $padT - $padB);

        $axisX1 = $padL;
        $axisY = $padT + $plotH;
        $axisX2 = $padL + $plotW;

        $color = htmlspecialchars((string)$series0['color'], ENT_QUOTES, 'UTF-8');

        $formatValue = static function (float $v): string {
            $rounded = round($v);
            if (abs($v - $rounded) < 0.0001) {
                return (string)(int)$rounded;
            }
            return number_format($v, 2, '.', '');
        };

        $grid = '';
        $ticks = 3;
        for ($t = 0; $t <= $ticks; $t++) {
            $y = $padT + ($plotH * (1 - ($t / $ticks)));
            $val = ($yMax * ($t / $ticks));
            $grid .= '<line class="m-chart-grid" x1="' . $axisX1 . '" y1="' . $y . '" x2="' . $axisX2 . '" y2="' . $y . '" />';
            $grid .= '<text class="m-chart-axis" x="' . ($padL - 8) . '" y="' . ($y + 4) . '" text-anchor="end">' . htmlspecialchars((string)round($val), ENT_QUOTES, 'UTF-8') . '</text>';
        }

        $seriesSvg = '';

        if ($this->type === 'bar') {
            $step = $plotW / $n;
            $barW = max(2.0, $step * 0.62);

            for ($i = 0; $i < $n; $i++) {
                $v = max(0.0, (float)$values[$i]);
                $barH = ($v / $yMax) * $plotH;
                $x = $padL + ($step * $i) + (($step - $barW) / 2);
                $y = $axisY - $barH;

                $labRaw = (string)$labels[$i];
                $tip = $labRaw;
                if ($seriesNameRaw !== '') {
                    $tip .= ' — ' . $seriesNameRaw;
                }
                $tip .= ': ' . $formatValue($v);
                $tipEsc = htmlspecialchars($tip, ENT_QUOTES, 'UTF-8');

                $seriesSvg .= '<rect class="m-chart-bar" x="' . $x . '" y="' . $y . '" width="' . $barW . '" height="' . $barH . '" fill="' . $color . '" rx="4" data-m-tooltip="' . $tipEsc . '" data-m-tooltip-position="top" />';

                // X labels (sparse if lots)
                if ($n <= 10 || ($i % 2) === 0) {
                    $lx = $padL + ($step * $i) + ($step / 2);
                    $lab = htmlspecialchars((string)$labels[$i], ENT_QUOTES, 'UTF-8');
                    $seriesSvg .= '<text class="m-chart-x" x="' . $lx . '" y="' . ($h - 10) . '" text-anchor="middle">' . $lab . '</text>';
                }
            }
        } else {
            $step = $plotW / max(1, $n - 1);
            $points = [];
            for ($i = 0; $i < $n; $i++) {
                $v = max(0.0, (float)$values[$i]);
                $x = $padL + ($step * $i);
                $y = $axisY - (($v / $yMax) * $plotH);
                $points[] = $x . ',' . $y;

                if ($n <= 10 || ($i % 2) === 0) {
                    $lab = htmlspecialchars((string)$labels[$i], ENT_QUOTES, 'UTF-8');
                    $seriesSvg .= '<text class="m-chart-x" x="' . $x . '" y="' . ($h - 10) . '" text-anchor="middle">' . $lab . '</text>';
                }
            }

            $seriesSvg .= '<polyline class="m-chart-line" fill="none" stroke="' . $color . '" stroke-width="3" points="' . htmlspecialchars(implode(' ', $points), ENT_QUOTES, 'UTF-8') . '" />';
            foreach ($points as $idx => $p) {
                [$cx, $cy] = explode(',', $p);

                $v = max(0.0, (float)$values[$idx]);
                $labRaw = (string)$labels[$idx];
                $tip = $labRaw;
                if ($seriesNameRaw !== '') {
                    $tip .= ' — ' . $seriesNameRaw;
                }
                $tip .= ': ' . $formatValue($v);
                $tipEsc = htmlspecialchars($tip, ENT_QUOTES, 'UTF-8');

                $seriesSvg .= '<circle class="m-chart-point" cx="' . $cx . '" cy="' . $cy . '" r="3.5" fill="' . $color . '" data-m-tooltip="' . $tipEsc . '" data-m-tooltip-position="top" />';
            }
        }

        $title = htmlspecialchars((string)$series0['name'], ENT_QUOTES, 'UTF-8');

        $goalSvg = '';
        if ($this->goalLine !== null) {
            $goalVal = $this->goalLine['value'];
            $goalColor = htmlspecialchars($this->goalLine['color'], ENT_QUOTES, 'UTF-8');
            $goalLabel = htmlspecialchars($this->goalLine['label'], ENT_QUOTES, 'UTF-8');
            $goalY = $axisY - (min($goalVal, $yMax) / $yMax) * $plotH;
            $goalY = round($goalY, 2);
            $goalSvg = '<line class="m-chart-goal" x1="' . $axisX1 . '" y1="' . $goalY . '" x2="' . $axisX2 . '" y2="' . $goalY . '" stroke="' . $goalColor . '" stroke-dasharray="6 3" stroke-width="2" />';
            $goalSvg .= '<text class="m-chart-goal-label" x="' . ($axisX2 + 2) . '" y="' . ($goalY + 4) . '" fill="' . $goalColor . '" font-size="10" text-anchor="start">' . $goalLabel . '</text>';
        }

        $svg = <<<SVG
<svg class="m-chart-svg" viewBox="0 0 {$w} {$h}" role="img" aria-label="{$title}">
    {$grid}
    <line class="m-chart-axis-line" x1="{$axisX1}" y1="{$axisY}" x2="{$axisX2}" y2="{$axisY}" />
    <line class="m-chart-axis-line" x1="{$axisX1}" y1="{$padT}" x2="{$axisX1}" y2="{$axisY}" />
    {$seriesSvg}
    {$goalSvg}
</svg>
SVG;

        $legend = '';
        if ($title !== '') {
            $legend = '<div class="m-chart-legend"><span class="m-chart-swatch" style="background:' . $color . '"></span>' . $title . '</div>';
        }

        return "<div id=\"{$idAttr}\" class=\"{$classAttr}\"{$eventAttrs}{$extraAttrs}>{$legend}{$svg}</div>";
    }
}
