<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-chart-bar') ?> Chart</h2>
    <p class="m-demo-desc">Dependency-free SVG chart component supporting bar, line, and stacked-bar types with tooltips, axis labels, and optional goal lines.</p>

    <h3>Bar Chart</h3>
    <div class="m-demo-row">
        <?= $m->chart('demo-chart-bar')
            ->type('bar')
            ->labels(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])
            ->series('Steps', [6500, 8200, 5100, 9300, 7400, 4200, 8800], '#2196F3')
            ->goal(8000, '#e74c3c', 'Goal')
            ->width(640)
            ->height(220) ?>
    </div>

    <h3>Line Chart</h3>
    <div class="m-demo-row">
        <?= $m->chart('demo-chart-line')
            ->type('line')
            ->labels(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'])
            ->series('Revenue', [12000, 18500, 15200, 22100, 19800, 25400], '#4CAF50')
            ->width(640)
            ->height(220) ?>
    </div>

    <h3>Stacked Bar Chart</h3>
    <p class="m-demo-desc">Multiple series are stacked vertically. The Y-axis maximum is the largest cumulative total across all positions. Each series requires its own <code>->series()</code> call.</p>
    <div class="m-demo-row">
        <?= $m->chart('demo-chart-stacked')
            ->type('stacked-bar')
            ->labels(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])
            ->series('Film',    [3, 5, 2, 4, 6, 1, 3], '#118AB2')
            ->series('TV',      [2, 1, 4, 3, 2, 5, 2], '#FF9800')
            ->series('Theatre', [1, 2, 1, 2, 1, 3, 1], '#9C27B0')
            ->width(640)
            ->height(220) ?>
    </div>

    <?= demoCodeTabs(
        '// Bar chart with goal line
<?= $m->chart(\'stepsChart\')
    ->type(\'bar\')
    ->labels([\'Mon\', \'Tue\', \'Wed\', \'Thu\', \'Fri\'])
    ->series(\'Steps\', [6500, 8200, 5100, 9300, 7400], \'#2196F3\')
    ->goal(8000, \'#e74c3c\', \'Goal\')
    ->width(640)
    ->height(220) ?>

// Line chart
<?= $m->chart(\'revenueChart\')
    ->type(\'line\')
    ->labels([\'Jan\', \'Feb\', \'Mar\', \'Apr\', \'May\'])
    ->series(\'Revenue\', [12000, 18500, 15200, 22100, 19800], \'#4CAF50\')
    ->width(640)
    ->height(220) ?>

// Stacked bar chart (multiple series)
<?= $m->chart(\'categoryChart\')
    ->type(\'stacked-bar\')
    ->labels([\'Mon\', \'Tue\', \'Wed\', \'Thu\', \'Fri\'])
    ->series(\'Film\',    [3, 5, 2, 4, 6], \'#118AB2\')
    ->series(\'TV\',      [2, 1, 4, 3, 2], \'#FF9800\')
    ->series(\'Theatre\', [1, 2, 1, 2, 1], \'#9C27B0\')
    ->width(640)
    ->height(220) ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->chart($id)', 'string', 'Create a chart component.'],
    ['->type($type)', 'string', 'Chart type: <code>bar</code>, <code>line</code>, or <code>stacked-bar</code>. Default: <code>bar</code>.'],
    ['->labels($labels)', 'array', 'X-axis category labels.'],
    ['->series($name, $values, $color)', 'string, array, ?string', 'Add a data series. For <code>stacked-bar</code>, call once per series. <code>$color</code> defaults to <code>#2196F3</code>.'],
    ['->width($w)', 'int', 'SVG viewBox width in px (default: 640, min: 240).'],
    ['->height($h)', 'int', 'SVG viewBox height in px (default: 220, min: 140).'],
    ['->yMax($max)', '?float', 'Override the automatic Y-axis maximum.'],
    ['->goal($value, $color, $label)', 'float, ?string, ?string', 'Draw a horizontal goal/target line (bar and line types only).'],
]) ?>
