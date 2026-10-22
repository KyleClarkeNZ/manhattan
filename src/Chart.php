<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Chart Component (SVG)
 *
 * Supports:
 * - Bar chart (single series, vertical columns)
 * - Stacked bar chart (multiple series, vertical columns)
 * - Horizontal bar chart (single series, suits long category labels)
 * - Line/time-series chart (multiple series, optional smoothing, null = gap)
 *
 * This is intentionally dependency-free (no external JS libraries).
 */
final class Chart extends Component
{
    private const TYPES = ['bar', 'line', 'stacked-bar', 'horizontal-bar'];

    /** Approximate glyph width (px) of the 11px axis font, used for layout. */
    private const CHAR_W = 6.4;

    /** Surface gap (px) between stacked segments. */
    private const GAP = 2.0;

    private string $type = 'bar';

    /** @var string[] */
    private array $labels = [];

    /** @var array<int, array{name: string, values: array<int, float|null>, color: string, striped: bool}> */
    private array $series = [];

    private int $width = 640;
    private int $height = 220;

    private ?float $yMax = null;

    /** @var array{value: float, color: string, label: string}|null */
    private ?array $goalLine = null;

    private string $title = '';
    private string $valuePrefix = '';
    private string $valueSuffix = '';
    private bool $smooth = false;
    private string $legendPosition = 'top';

    public function type(string $type): self
    {
        $type = strtolower(trim($type));
        if ($type === 'hbar') {
            $type = 'horizontal-bar';
        }
        if (!in_array($type, self::TYPES, true)) {
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
     * Add a data series. Line and stacked-bar charts render every series;
     * bar and horizontal-bar render the first.
     *
     * A null value is "no data": a gap in a line, no bar in a bar chart.
     *
     * @param array<int, float|int|string|null> $values
     * @param bool $striped When true, renders the series bars with a diagonal stripe pattern instead of a solid fill.
     */
    public function series(string $name, array $values, ?string $color = null, bool $striped = false): self
    {
        $vals = [];
        foreach (array_values($values) as $v) {
            $vals[] = is_numeric($v) ? (float)$v : null;
        }

        $this->series[] = [
            'name'    => $name,
            'values'  => $vals,
            'color'   => $color ?: '#2196F3',
            'striped' => $striped,
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

    /**
     * Heading shown above the chart. With a title, a single-series chart drops
     * its one-swatch legend (the title already names what is plotted).
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Prefix/suffix for axis ticks, tooltips and value labels, e.g. ('$') or ('', '%').
     */
    public function valueFormat(string $prefix = '', string $suffix = ''): self
    {
        $this->valuePrefix = $prefix;
        $this->valueSuffix = $suffix;
        return $this;
    }

    /**
     * Draw line series as smooth curves (monotone, so they never overshoot the data).
     */
    public function smooth(bool $smooth = true): self
    {
        $this->smooth = $smooth;
        return $this;
    }

    /**
     * Legend placement: top (default), bottom or none.
     */
    public function legend(string $position): self
    {
        $position = strtolower(trim($position));
        $this->legendPosition = in_array($position, ['top', 'bottom', 'none'], true) ? $position : 'top';
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'chart';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-chart', 'm-chart-' . $this->type], $this->getExtraClasses());
        $classAttr = self::e(implode(' ', array_filter($classes)));
        $idAttr = self::e($this->id);

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);
        $eventAttrs = $this->renderEventAttributes();

        $titleHtml = $this->title !== '' ? '<div class="m-chart-title">' . self::e($this->title) . '</div>' : '';

        $n = count($this->labels);
        foreach ($this->series as $ser) {
            $n = max($n, count($ser['values']));
        }
        if ($n <= 0) {
            $empty = '<div class="m-chart-empty">No data</div>';
            return "<div id=\"{$idAttr}\" class=\"{$classAttr}\"{$eventAttrs}{$extraAttrs}>{$titleHtml}{$empty}</div>";
        }

        $labels = $this->labels;
        for ($i = 0; $i < $n; $i++) {
            if (!isset($labels[$i])) {
                $labels[$i] = (string)($i + 1);
            }
        }

        if ($this->type === 'horizontal-bar') {
            [$svg, $w, $h] = $this->renderHorizontalBar($labels, $n);
        } else {
            [$svg, $w, $h] = $this->renderVertical($labels, $n);
        }

        $ariaLabel = $this->title !== ''
            ? $this->title
            : implode(', ', array_filter(array_map(static fn($s) => (string)$s['name'], $this->series)));
        $ariaLabel = self::e($ariaLabel);

        $svg = "<svg class=\"m-chart-svg\" viewBox=\"0 0 {$w} {$h}\" role=\"img\" aria-label=\"{$ariaLabel}\">{$svg}</svg>";

        $legend = $this->legendPosition === 'none' ? '' : $this->renderLegend();
        $body = $this->legendPosition === 'bottom' ? $svg . $legend : $legend . $svg;

        return "<div id=\"{$idAttr}\" class=\"{$classAttr}\"{$eventAttrs}{$extraAttrs}>{$titleHtml}{$body}</div>";
    }

    /**
     * Bar, stacked-bar and line: categories along X, values up the Y axis.
     *
     * @param string[] $labels
     * @return array{0: string, 1: int, 2: int}
     */
    private function renderVertical(array $labels, int $n): array
    {
        $isLine = $this->type === 'line';
        $isStacked = $this->type === 'stacked-bar';
        $drawn = ($isLine || $isStacked) ? $this->series : array_slice($this->series, 0, 1);

        $maxVal = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $sum = 0.0;
            foreach ($drawn as $ser) {
                $v = max(0.0, (float)($ser['values'][$i] ?? 0.0));
                $sum = $isStacked ? $sum + $v : max($sum, $v);
            }
            $maxVal = max($maxVal, $sum);
        }
        if ($this->goalLine !== null) {
            $maxVal = max($maxVal, $this->goalLine['value']);
        }

        if ($this->yMax !== null && $this->yMax > 0.0) {
            $yMax = $this->yMax;
            $tickStep = $yMax / 4;
        } else {
            [$yMax, $tickStep] = self::niceScale($maxVal);
        }
        $tickCount = max(1, (int)round($yMax / $tickStep));

        $tickLabels = [];
        $longestTick = 0;
        for ($t = 0; $t <= $tickCount; $t++) {
            $tickLabels[$t] = $this->formatTick($tickStep * $t);
            $longestTick = max($longestTick, self::len($tickLabels[$t]));
        }

        $w = $this->width;
        $h = $this->height;

        $padL = (int)max(42, ceil($longestTick * self::CHAR_W + 14));
        $padR = $isLine ? 20 : 14;
        $padT = 14;
        $padB = 34;

        $plotW = max(1, $w - $padL - $padR);
        $plotH = max(1, $h - $padT - $padB);
        $axisX1 = $padL;
        $axisX2 = $padL + $plotW;
        $axisY = $padT + $plotH;

        $yFor = static fn(float $v): float => $axisY - (min(max(0.0, $v), $yMax) / $yMax) * $plotH;

        $svg = '';

        // Grid and Y ticks
        for ($t = 0; $t <= $tickCount; $t++) {
            $y = self::r($yFor($tickStep * $t));
            $svg .= '<line class="m-chart-grid" x1="' . $axisX1 . '" y1="' . $y . '" x2="' . $axisX2 . '" y2="' . $y . '" />';
            $svg .= '<text class="m-chart-axis" x="' . ($padL - 8) . '" y="' . ($y + 4) . '" text-anchor="end">' . self::e($tickLabels[$t]) . '</text>';
        }

        // X labels, thinned so they never collide
        $slotW = $isLine ? $plotW / max(1, $n - 1) : $plotW / $n;
        $longestLabel = 0;
        foreach ($labels as $lab) {
            $longestLabel = max($longestLabel, self::len($lab));
        }
        $every = max(1, (int)ceil(($longestLabel * self::CHAR_W + 10) / max(1.0, $slotW)));
        $xFor = static function (int $i) use ($isLine, $padL, $slotW): float {
            return $isLine ? $padL + $slotW * $i : $padL + $slotW * $i + $slotW / 2;
        };
        for ($i = 0; $i < $n; $i += $every) {
            $svg .= '<text class="m-chart-x" x="' . self::r($xFor($i)) . '" y="' . ($h - 10) . '" text-anchor="middle">' . self::e($labels[$i]) . '</text>';
        }

        $svg .= '<line class="m-chart-axis-line" x1="' . $axisX1 . '" y1="' . $axisY . '" x2="' . $axisX2 . '" y2="' . $axisY . '" />';
        $svg .= '<line class="m-chart-axis-line" x1="' . $axisX1 . '" y1="' . $padT . '" x2="' . $axisX1 . '" y2="' . $axisY . '" />';

        if ($isLine) {
            $svg .= $this->renderLines($labels, $n, $xFor, $yFor);
        } else {
            $svg .= $this->renderColumns($labels, $n, $drawn, $slotW, $xFor, $yFor, $axisY);
        }

        if ($this->goalLine !== null) {
            $goalVal = $this->goalLine['value'];
            $goalColor = self::e($this->goalLine['color']);
            $goalLabel = self::e($this->goalLine['label']);
            $goalY = self::r($yFor($goalVal));
            $goalTip = self::e($this->goalLine['label'] . ': ' . $this->formatValue($goalVal));

            $svg .= '<line class="m-chart-goal" x1="' . $axisX1 . '" y1="' . $goalY . '" x2="' . $axisX2 . '" y2="' . $goalY . '" stroke="' . $goalColor . '" stroke-dasharray="6 3" stroke-width="2" data-m-tooltip="' . $goalTip . '" data-m-tooltip-position="top" />';
            $svg .= '<text class="m-chart-goal-label" x="' . ($axisX2 - 6) . '" y="' . ($goalY - 6) . '" fill="' . $goalColor . '" font-size="11" font-weight="600" text-anchor="end" data-m-tooltip="' . $goalTip . '" data-m-tooltip-position="top">' . $goalLabel . '</text>';
        }

        return [$this->renderDefs() . $svg, $w, $h];
    }

    /**
     * @param string[] $labels
     * @param array<int, array{name: string, values: array<int, float|null>, color: string, striped: bool}> $drawn
     */
    private function renderColumns(array $labels, int $n, array $drawn, float $slotW, callable $xFor, callable $yFor, float $axisY): string
    {
        $barW = max(2.0, $slotW * 0.62);
        $svg = '';

        for ($i = 0; $i < $n; $i++) {
            $x = $xFor($i) - $barW / 2;

            // Visible segments bottom-up; only the top one gets rounded corners
            $segs = [];
            foreach ($drawn as $s => $ser) {
                $v = max(0.0, (float)($ser['values'][$i] ?? 0.0));
                if ($v > 0.0) {
                    $segs[] = [$s, $v];
                }
            }

            $cum = 0.0;
            $last = count($segs) - 1;
            foreach ($segs as $k => [$s, $v]) {
                $ser = $drawn[$s];
                $yBottom = $yFor($cum);
                $cum += $v;
                $yTop = $yFor($cum);
                if ($k > 0) {
                    // Surface gap separates the segment from the one beneath
                    $yBottom = max($yTop + 0.5, $yBottom - self::GAP);
                }

                $fill = $ser['striped'] ? 'url(#' . $this->patternId($s) . ')' : self::e($ser['color']);

                $tip = $labels[$i];
                if ($ser['name'] !== '') {
                    $tip .= ' — ' . $ser['name'];
                }
                $tip = self::e($tip . ': ' . $this->formatValue($v));

                if ($k === $last) {
                    $d = self::roundedRectPath($x, $yTop, $barW, $yBottom - $yTop, 'top');
                    $svg .= '<path class="m-chart-bar" d="' . $d . '" fill="' . $fill . '" data-m-tooltip="' . $tip . '" data-m-tooltip-position="top" />';
                } else {
                    $svg .= '<rect class="m-chart-bar" x="' . self::r($x) . '" y="' . self::r($yTop) . '" width="' . self::r($barW) . '" height="' . self::r($yBottom - $yTop) . '" fill="' . $fill . '" data-m-tooltip="' . $tip . '" data-m-tooltip-position="top" />';
                }
            }
        }

        return $svg;
    }

    /**
     * @param string[] $labels
     */
    private function renderLines(array $labels, int $n, callable $xFor, callable $yFor): string
    {
        $paths = '';
        $points = '';
        $hits = '';

        foreach ($this->series as $ser) {
            $color = self::e($ser['color']);

            // Split into runs of consecutive non-null values; nulls leave a gap
            $runs = [];
            $run = [];
            for ($i = 0; $i < $n; $i++) {
                $v = $ser['values'][$i] ?? null;
                if ($v === null) {
                    if ($run) {
                        $runs[] = $run;
                    }
                    $run = [];
                    continue;
                }
                $pt = [$xFor($i), $yFor($v)];
                $run[] = $pt;

                $tip = $labels[$i];
                if ($ser['name'] !== '') {
                    $tip .= ' — ' . $ser['name'];
                }
                $tip = self::e($tip . ': ' . $this->formatValue($v));

                $cx = self::r($pt[0]);
                $cy = self::r($pt[1]);
                $points .= '<circle class="m-chart-point" cx="' . $cx . '" cy="' . $cy . '" r="4" fill="' . $color . '" />';
                $hits .= '<circle class="m-chart-hit" cx="' . $cx . '" cy="' . $cy . '" r="10" data-m-tooltip="' . $tip . '" data-m-tooltip-position="top" />';
            }
            if ($run) {
                $runs[] = $run;
            }

            foreach ($runs as $r) {
                if (count($r) < 2) {
                    continue;
                }
                $d = $this->smooth ? self::smoothPath($r) : self::linearPath($r);
                $paths .= '<path class="m-chart-line" d="' . $d . '" fill="none" stroke="' . $color . '" />';
            }
        }

        return $paths . $points . $hits;
    }

    /**
     * Horizontal bars: one row per category, value labelled at the bar tip.
     *
     * @param string[] $labels
     * @return array{0: string, 1: int, 2: int}
     */
    private function renderHorizontalBar(array $labels, int $n): array
    {
        $ser = $this->series[0] ?? ['name' => '', 'values' => [], 'color' => '#2196F3', 'striped' => false];

        $maxVal = 0.0;
        $longestValue = 0;
        for ($i = 0; $i < $n; $i++) {
            $v = max(0.0, (float)($ser['values'][$i] ?? 0.0));
            $maxVal = max($maxVal, $v);
            $longestValue = max($longestValue, self::len($this->formatValue($v)));
        }
        $xMax = ($this->yMax !== null && $this->yMax > 0.0) ? $this->yMax : $maxVal;
        if ($xMax <= 0.0) {
            $xMax = 1.0;
        }

        $w = $this->width;
        $padT = 6;
        $padB = 6;
        // Grow to fit the rows rather than squashing them
        $h = max($this->height, $n * 28 + $padT + $padB);

        $longestLabel = 0;
        foreach ($labels as $lab) {
            $longestLabel = max($longestLabel, self::len($lab));
        }
        $padL = (int)min($w * 0.45, ceil($longestLabel * self::CHAR_W + 16));
        $padR = (int)ceil($longestValue * self::CHAR_W + 14);
        $maxChars = max(4, (int)floor(($padL - 16) / self::CHAR_W));

        $plotW = max(1, $w - $padL - $padR);
        $plotH = max(1, $h - $padT - $padB);
        $step = $plotH / $n;
        $barH = min(24.0, $step * 0.62);

        $fill = $ser['striped'] ? 'url(#' . $this->patternId(0) . ')' : self::e($ser['color']);

        $svg = '';
        for ($i = 0; $i < $n; $i++) {
            $cy = $padT + $step * $i + $step / 2;
            $raw = $ser['values'][$i] ?? null;
            $v = max(0.0, (float)($raw ?? 0.0));
            $len = (min($v, $xMax) / $xMax) * $plotW;

            $labRaw = $labels[$i];
            $labShown = self::len($labRaw) > $maxChars ? self::cut($labRaw, $maxChars - 1) . '…' : $labRaw;
            $labTip = $labShown !== $labRaw ? ' data-m-tooltip="' . self::e($labRaw) . '" data-m-tooltip-position="top"' : '';
            $svg .= '<text class="m-chart-x" x="' . ($padL - 8) . '" y="' . self::r($cy + 4) . '" text-anchor="end"' . $labTip . '>' . self::e($labShown) . '</text>';

            if ($raw === null) {
                continue;
            }

            $valueText = $this->formatValue($v);
            $tip = $labRaw;
            if ($ser['name'] !== '') {
                $tip .= ' — ' . $ser['name'];
            }
            $tip = self::e($tip . ': ' . $valueText);

            if ($len > 0.0) {
                $d = self::roundedRectPath((float)$padL, $cy - $barH / 2, $len, $barH, 'right');
                $svg .= '<path class="m-chart-bar" d="' . $d . '" fill="' . $fill . '" data-m-tooltip="' . $tip . '" data-m-tooltip-position="top" />';
            }
            $svg .= '<text class="m-chart-value" x="' . self::r($padL + $len + 6) . '" y="' . self::r($cy + 4) . '">' . self::e($valueText) . '</text>';
        }

        $svg .= '<line class="m-chart-axis-line" x1="' . $padL . '" y1="' . $padT . '" x2="' . $padL . '" y2="' . ($padT + $plotH) . '" />';

        return [$this->renderDefs() . $svg, $w, $h];
    }

    private function renderLegend(): string
    {
        $multi = $this->type === 'line' || $this->type === 'stacked-bar';
        $shown = $multi ? $this->series : array_slice($this->series, 0, 1);

        // A lone series is named by the title (horizontal-bar: by its row labels)
        if (count($shown) < 2 && ($this->title !== '' || $this->type === 'horizontal-bar')) {
            return '';
        }

        $items = '';
        foreach ($shown as $ser) {
            if ($ser['name'] === '') {
                continue;
            }
            $color = self::e($ser['color']);
            $swatchClass = 'm-chart-swatch' . ($this->type === 'line' ? ' m-chart-swatch-line' : '');
            if ($ser['striped']) {
                $swatchStyle = 'background: repeating-linear-gradient(45deg, ' . $color . ', ' . $color . ' 3px, transparent 3px, transparent 7px); border: 1px solid ' . $color . ';';
            } else {
                $swatchStyle = 'background:' . $color;
            }
            $items .= '<span class="m-chart-legend-item"><span class="' . $swatchClass . '" style="' . $swatchStyle . '"></span>' . self::e($ser['name']) . '</span>';
        }

        return $items !== '' ? '<div class="m-chart-legend">' . $items . '</div>' : '';
    }

    private function renderDefs(): string
    {
        $defs = '';
        foreach ($this->series as $s => $ser) {
            if ($ser['striped']) {
                $color = self::e($ser['color']);
                $defs .= '<pattern id="' . $this->patternId($s) . '" patternUnits="userSpaceOnUse" width="8" height="8" patternTransform="rotate(45 0 0)">'
                    . '<rect width="8" height="8" fill="' . $color . '" fill-opacity="0.15"/>'
                    . '<rect x="0" y="0" width="4" height="8" fill="' . $color . '"/>'
                    . '</pattern>';
            }
        }
        return $defs !== '' ? '<defs>' . $defs . '</defs>' : '';
    }

    private function patternId(int $seriesIndex): string
    {
        return 'm-chart-stripe-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $this->id) . '-' . $seriesIndex;
    }

    /**
     * Full value for tooltips and labels: prefix, thousands separators, suffix.
     */
    private function formatValue(float $v): string
    {
        $decimals = abs($v - round($v)) < 0.0001 ? 0 : 2;
        return $this->valuePrefix . number_format($v, $decimals, '.', ',') . $this->valueSuffix;
    }

    /**
     * Compact value for axis ticks: 0 / 500 / 1K / 1.5K / 2M.
     */
    private function formatTick(float $v): string
    {
        $abs = abs($v);
        if ($abs >= 1000000) {
            $num = self::trimNumber($v / 1000000) . 'M';
        } elseif ($abs >= 1000) {
            $num = self::trimNumber($v / 1000) . 'K';
        } else {
            $num = self::trimNumber($v);
        }
        return $this->valuePrefix . $num . $this->valueSuffix;
    }

    private static function trimNumber(float $v): string
    {
        $s = number_format($v, 2, '.', '');
        return rtrim(rtrim($s, '0'), '.');
    }

    /**
     * Round an axis maximum up to a clean number with a clean tick step.
     *
     * @return array{0: float, 1: float} [max, step]
     */
    private static function niceScale(float $max, int $targetTicks = 4): array
    {
        if ($max <= 0.0) {
            return [1.0, 0.25];
        }
        $rough = $max / $targetTicks;
        $mag = 10 ** floor(log10($rough));
        $norm = $rough / $mag;
        if ($norm <= 1) {
            $nice = 1;
        } elseif ($norm <= 2) {
            $nice = 2;
        } elseif ($norm <= 2.5) {
            $nice = 2.5;
        } elseif ($norm <= 5) {
            $nice = 5;
        } else {
            $nice = 10;
        }
        $step = $nice * $mag;
        return [ceil($max / $step - 1e-9) * $step, $step];
    }

    /**
     * Rect path with 4px rounded corners on one end only ('top' or 'right'),
     * square where the bar meets its baseline.
     */
    private static function roundedRectPath(float $x, float $y, float $w, float $h, string $end): string
    {
        $x1 = self::r($x);
        $y1 = self::r($y);
        $x2 = self::r($x + $w);
        $y2 = self::r($y + $h);

        if ($end === 'right') {
            $r = self::r(min(4.0, $w / 2, $h / 2));
            return "M {$x1},{$y1} L " . self::r($x2 - $r) . ",{$y1}"
                . " A {$r},{$r} 0 0 1 {$x2}," . self::r($y1 + $r)
                . " L {$x2}," . self::r($y2 - $r)
                . " A {$r},{$r} 0 0 1 " . self::r($x2 - $r) . ",{$y2}"
                . " L {$x1},{$y2} Z";
        }

        $r = self::r(min(4.0, $w / 2, $h / 2));
        return "M {$x1},{$y2} L {$x1}," . self::r($y1 + $r)
            . " A {$r},{$r} 0 0 1 " . self::r($x1 + $r) . ",{$y1}"
            . " L " . self::r($x2 - $r) . ",{$y1}"
            . " A {$r},{$r} 0 0 1 {$x2}," . self::r($y1 + $r)
            . " L {$x2},{$y2} Z";
    }

    /**
     * @param array<int, array{0: float, 1: float}> $pts
     */
    private static function linearPath(array $pts): string
    {
        $d = '';
        foreach ($pts as $i => [$x, $y]) {
            $d .= ($i === 0 ? 'M ' : ' L ') . self::r($x) . ',' . self::r($y);
        }
        return $d;
    }

    /**
     * Monotone cubic (Fritsch–Carlson) curve through the points: smooth, but
     * never bulges above a peak or below a trough the data doesn't have.
     *
     * @param array<int, array{0: float, 1: float}> $pts
     */
    private static function smoothPath(array $pts): string
    {
        $n = count($pts);
        if ($n < 3) {
            return self::linearPath($pts);
        }

        $dx = [];
        $m = [];
        for ($i = 0; $i < $n - 1; $i++) {
            $dx[$i] = $pts[$i + 1][0] - $pts[$i][0];
            $m[$i] = $dx[$i] != 0.0 ? ($pts[$i + 1][1] - $pts[$i][1]) / $dx[$i] : 0.0;
        }

        $t = [$m[0]];
        for ($i = 1; $i < $n - 1; $i++) {
            $t[$i] = ($m[$i - 1] * $m[$i] <= 0.0) ? 0.0 : ($m[$i - 1] + $m[$i]) / 2;
        }
        $t[$n - 1] = $m[$n - 2];

        for ($i = 0; $i < $n - 1; $i++) {
            if ($m[$i] == 0.0) {
                $t[$i] = 0.0;
                $t[$i + 1] = 0.0;
                continue;
            }
            $a = $t[$i] / $m[$i];
            $b = $t[$i + 1] / $m[$i];
            $s = $a * $a + $b * $b;
            if ($s > 9.0) {
                $tau = 3.0 / sqrt($s);
                $t[$i] = $tau * $a * $m[$i];
                $t[$i + 1] = $tau * $b * $m[$i];
            }
        }

        $d = 'M ' . self::r($pts[0][0]) . ',' . self::r($pts[0][1]);
        for ($i = 0; $i < $n - 1; $i++) {
            $h3 = $dx[$i] / 3;
            $d .= ' C ' . self::r($pts[$i][0] + $h3) . ',' . self::r($pts[$i][1] + $t[$i] * $h3)
                . ' ' . self::r($pts[$i + 1][0] - $h3) . ',' . self::r($pts[$i + 1][1] - $t[$i + 1] * $h3)
                . ' ' . self::r($pts[$i + 1][0]) . ',' . self::r($pts[$i + 1][1]);
        }
        return $d;
    }

    private static function r(float $v): float
    {
        return round($v, 2);
    }

    private static function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    private static function len(string $s): int
    {
        return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    }

    private static function cut(string $s, int $chars): string
    {
        return function_exists('mb_substr') ? mb_substr($s, 0, $chars, 'UTF-8') : substr($s, 0, $chars);
    }
}
