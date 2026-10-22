<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-chart-bar') ?> Chart</h2>
    <p class="m-demo-desc">Dependency-free SVG chart component supporting bar, stacked-bar, horizontal-bar and multi-series line charts, with tooltips, clean axis ticks, value formatting and optional goal lines. Hover a bar or point to see its value.</p>

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
            ->valueFormat('$')
            ->width(640)
            ->height(220) ?>
    </div>

    <h3>Multi-series Line Chart</h3>
    <p class="m-demo-desc">Every <code>->series()</code> is drawn. <code>->smooth()</code> curves the lines without overshooting the data, and a <code>null</code> value leaves a gap — here the months of this year that haven't happened yet.</p>
    <div class="m-demo-row">
        <?= $m->chart('demo-chart-multiline')
            ->type('line')
            ->title('Visitors — this year vs last year')
            ->labels(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'])
            ->series('This year', [4200, 4650, 5100, 4980, 5600, 6120, 6400, 6010, null, null, null, null], '#008ED3')
            ->series('Last year', [3900, 4100, 4300, 4250, 4800, 5200, 5500, 5300, 5050, 5400, 6100, 6900], '#AAAAAA')
            ->smooth()
            ->legend('bottom')
            ->width(640)
            ->height(240) ?>
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

    <h3>Horizontal Bar Chart</h3>
    <p class="m-demo-desc">For ranked lists and long category labels. Each value is labelled at the end of its bar, and the chart grows taller to fit its rows. Labels too long for the space are shortened, with the full text on hover.</p>
    <div class="m-demo-row">
        <?= $m->chart('demo-chart-hbar')
            ->type('horizontal-bar')
            ->labels(['Customer support', 'Billing enquiries', 'Technical issues', 'Feature requests', 'Account access'])
            ->series('Tickets', [128, 94, 77, 41, 23], '#118AB2')
            ->width(640) ?>
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

// Multi-series line chart: smooth curves, null = gap, $ values
<?= $m->chart(\'visitorsChart\')
    ->type(\'line\')
    ->title(\'Visitors — this year vs last year\')
    ->labels([\'Jan\', \'Feb\', \'Mar\', \'Apr\', \'May\', \'Jun\'])
    ->series(\'This year\', [4200, 4650, 5100, null, null, null], \'#008ED3\')
    ->series(\'Last year\', [3900, 4100, 4300, 4250, 4800, 5200], \'#AAAAAA\')
    ->smooth()
    ->legend(\'bottom\') ?>

// Stacked bar chart (multiple series)
<?= $m->chart(\'categoryChart\')
    ->type(\'stacked-bar\')
    ->labels([\'Mon\', \'Tue\', \'Wed\', \'Thu\', \'Fri\'])
    ->series(\'Film\',    [3, 5, 2, 4, 6], \'#118AB2\')
    ->series(\'TV\',      [2, 1, 4, 3, 2], \'#FF9800\')
    ->series(\'Theatre\', [1, 2, 1, 2, 1], \'#9C27B0\') ?>

// Horizontal bar chart
<?= $m->chart(\'ticketsChart\')
    ->type(\'horizontal-bar\')
    ->labels([\'Customer support\', \'Billing enquiries\', \'Technical issues\'])
    ->series(\'Tickets\', [128, 94, 77], \'#118AB2\') ?>'
    ) ?>
</div>

<?php
// Sample data for the dashboard example (NZ tax year runs April–March)
$dashMonths  = ['Oct 25', 'Nov 25', 'Dec 25', 'Jan 26', 'Feb 26', 'Mar 26', 'Apr 26', 'May 26', 'Jun 26', 'Jul 26', 'Aug 26', 'Sep 26', 'Oct 26'];
$taxMonths   = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];
$wtdDays     = ['Mon 19', 'Tue 20', 'Wed 21', 'Thu 22'];
?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-chart-line') ?> Example: Sales Dashboard</h2>
    <p class="m-demo-desc">A store's sales reporting built from Chart alone: stacked monthly revenue, year-to-date versus the previous year, products added per month, and week-to-date sales in Tabs.</p>

    <div class="m-demo-row">
        <?= $m->chart('demo-dash-revenue')
            ->type('stacked-bar')
            ->title('Monthly revenue — online vs markets (last 13 months)')
            ->labels($dashMonths)
            ->series('Online',  [1240, 1580, 2860, 980, 760, 1120, 940, 1310, 870, 1020, 1190, 1460, 1350], '#008ED3')
            ->series('Markets', [2100, 2650, 3900, 1450, 1800, 1650, 1200, 900, 760, 980, 1340, 1720, 1610], '#D97706')
            ->valueFormat('$')
            ->legend('bottom')
            ->width(700)
            ->height(300) ?>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;margin-top:24px">
        <?= $m->chart('demo-dash-online-ytd')
            ->type('line')
            ->title('Online sales — 2026/27 YTD vs 2025/26')
            ->labels($taxMonths)
            ->series('2026/27 (YTD)',       [940, 1310, 870, 1020, 1190, 1460, 1350, null, null, null, null, null], '#008ED3')
            ->series('2025/26 (full year)', [820, 1050, 960, 890, 1100, 1240, 1180, 1580, 2860, 980, 760, 1120], '#AAAAAA')
            ->valueFormat('$')
            ->smooth()
            ->legend('bottom')
            ->width(420)
            ->height(260) ?>

        <?= $m->chart('demo-dash-market-ytd')
            ->type('line')
            ->title('Market sales — 2026/27 YTD vs 2025/26')
            ->labels($taxMonths)
            ->series('2026/27 (YTD)',       [1200, 900, 760, 980, 1340, 1720, 1610, null, null, null, null, null], '#D97706')
            ->series('2025/26 (full year)', [1350, 1020, 690, 820, 1150, 1500, 2100, 2650, 3900, 1450, 1800, 1650], '#AAAAAA')
            ->valueFormat('$')
            ->smooth()
            ->legend('bottom')
            ->width(420)
            ->height(260) ?>
    </div>

    <div class="m-demo-row" style="margin-top:24px">
        <?= $m->chart('demo-dash-uploads')
            ->type('stacked-bar')
            ->title('New products per month (last 13 months)')
            ->labels($dashMonths)
            ->series('Originals', [4, 2, 6, 1, 3, 5, 2, 4, 3, 6, 2, 5, 3], '#D97706')
            ->series('Prints',    [8, 5, 12, 3, 6, 9, 4, 7, 5, 10, 6, 8, 7], '#008ED3')
            ->series('Products',  [2, 4, 6, 1, 2, 3, 5, 2, 1, 4, 3, 6, 2], '#9C27B0')
            ->legend('bottom')
            ->width(700)
            ->height(260) ?>
    </div>

    <h3>Week to date</h3>
    <div class="m-demo-row">
        <?= $m->tabs('demoDashWtd')->tabStyle('pills')
            ->tab('sales', 'Sales WTD')->content(
                (string)$m->chart('demo-dash-sales-wtd')
                    ->type('line')
                    ->title('Sales per day (WTD)')
                    ->labels($wtdDays)
                    ->series('Sales', [6, 9, 4, 11], '#008ED3')
                    ->width(640)
                    ->height(240)
            )->active()
            ->tab('dollars', 'Dollars WTD')->content(
                (string)$m->chart('demo-dash-dollars-wtd')
                    ->type('line')
                    ->title('Dollars per day (WTD)')
                    ->labels($wtdDays)
                    ->series('Dollars', [312, 486, 198, 645], '#008ED3')
                    ->valueFormat('$')
                    ->width(640)
                    ->height(240)
            )
            ->tab('top', 'Top Products WTD')->content(
                (string)$m->chart('demo-dash-top-products')
                    ->type('horizontal-bar')
                    ->title('Top 10 products (WTD)')
                    ->labels([
                        'Kōwhai Morning (A4 print)',
                        'Harbour Lights (A3 print)',
                        'Tūī Song greeting card',
                        'Pōhutukawa Coast (original)',
                        'Kererū in Flight (A4 print)',
                        'Driftwood Bay tea towel',
                        'Southern Alps (A3 print)',
                        'Fantail greeting card',
                        'Rangitoto at Dusk (A4 print)',
                        'Manuka Blossom coaster set',
                    ])
                    ->series('Qty sold', [14, 11, 9, 8, 7, 6, 5, 5, 4, 3], '#008ED3')
                    ->width(640)
            ) ?>
    </div>

    <?= demoCodeTabs(
        '// Stacked monthly revenue, $ axis, legend under the chart
<?= $m->chart(\'revenueChart\')
    ->type(\'stacked-bar\')
    ->title(\'Monthly revenue — online vs markets (last 13 months)\')
    ->labels($monthLabels)
    ->series(\'Online\',  $onlineByMonth, \'#008ED3\')
    ->series(\'Markets\', $marketsByMonth, \'#D97706\')
    ->valueFormat(\'$\')
    ->legend(\'bottom\')
    ->height(300) ?>

// Year to date vs last year: pass null for months still to come
<?= $m->chart(\'onlineYtdChart\')
    ->type(\'line\')
    ->title(\'Online sales — 2026/27 YTD vs 2025/26\')
    ->labels([\'Apr\', \'May\', \'Jun\', \'Jul\', \'Aug\', \'Sep\', \'Oct\', \'Nov\', \'Dec\', \'Jan\', \'Feb\', \'Mar\'])
    ->series(\'2026/27 (YTD)\',       $thisYear, \'#008ED3\')  // [940, 1310, ..., null, null]
    ->series(\'2025/26 (full year)\', $lastYear, \'#AAAAAA\')
    ->valueFormat(\'$\')
    ->smooth()
    ->legend(\'bottom\') ?>

// Top sellers
<?= $m->chart(\'topProductsChart\')
    ->type(\'horizontal-bar\')
    ->title(\'Top 10 products (WTD)\')
    ->labels($productTitles)
    ->series(\'Qty sold\', $quantities, \'#008ED3\') ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->chart($id)', 'string', 'Create a chart component.'],
    ['->type($type)', 'string', 'Chart type: <code>bar</code>, <code>line</code>, <code>stacked-bar</code> or <code>horizontal-bar</code> (alias <code>hbar</code>). Default: <code>bar</code>.'],
    ['->labels($labels)', 'array', 'Category labels (X axis; rows for <code>horizontal-bar</code>). Labels are thinned automatically so they never overlap.'],
    ['->series($name, $values, $color, $striped)', 'string, array, ?string, bool', 'Add a data series. <code>line</code> and <code>stacked-bar</code> draw every series; <code>bar</code> and <code>horizontal-bar</code> draw the first. <code>null</code> means no data (a gap in a line). <code>$color</code> defaults to <code>#2196F3</code>.'],
    ['->title($title)', 'string', 'Heading above the chart. A single-series chart with a title omits its legend.'],
    ['->valueFormat($prefix, $suffix)', 'string, string', 'Wrap values in axis ticks, tooltips and labels, e.g. <code>(\'$\')</code> or <code>(\'\', \'%\')</code>. Ticks are compact (<code>$1.5K</code>); tooltips are full (<code>$1,500</code>).'],
    ['->smooth($smooth)', 'bool', 'Draw <code>line</code> series as smooth curves (default when called: <code>true</code>).'],
    ['->legend($position)', 'string', '<code>top</code> (default), <code>bottom</code> or <code>none</code>.'],
    ['->width($w)', 'int', 'SVG viewBox width in px (default: 640, min: 240). The chart scales to its container.'],
    ['->height($h)', 'int', 'SVG viewBox height in px (default: 220, min: 140). <code>horizontal-bar</code> grows to fit its rows.'],
    ['->yMax($max)', '?float', 'Override the automatic axis maximum (otherwise rounded up to a clean number).'],
    ['->goal($value, $color, $label)', 'float, ?string, ?string', 'Draw a horizontal goal/target line (<code>bar</code>, <code>stacked-bar</code> and <code>line</code>).'],
]) ?>
