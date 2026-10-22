<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-table') ?> DataGrid</h2>
    <p class="m-demo-desc">Feature-rich data table with sorting, pagination, column resize/reorder, grouping, filtering, row selection, toolbar, and remote data support.</p>

    <h3>Local Data (Client-side)</h3>
    <?php
    $localData = [
        ['id' => 1, 'name' => 'Alice Johnson', 'role' => 'Developer', 'status' => 'Active', 'progress' => 85],
        ['id' => 2, 'name' => 'Bob Smith',     'role' => 'Designer',  'status' => 'Active', 'progress' => 60],
        ['id' => 3, 'name' => 'Carol White',   'role' => 'Manager',   'status' => 'Away',   'progress' => 95],
        ['id' => 4, 'name' => 'Dan Brown',     'role' => 'Developer', 'status' => 'Active', 'progress' => 45],
        ['id' => 5, 'name' => 'Eve Davis',     'role' => 'QA',        'status' => 'Offline','progress' => 70],
        ['id' => 6, 'name' => 'Frank Lee',     'role' => 'Developer', 'status' => 'Active', 'progress' => 30],
        ['id' => 7, 'name' => 'Grace Kim',     'role' => 'Designer',  'status' => 'Active', 'progress' => 90],
        ['id' => 8, 'name' => 'Hank Miller',   'role' => 'QA',        'status' => 'Away',   'progress' => 55],
    ];
    ?>
    <div class="m-demo-row">
        <?= $m->dataGrid('demo-grid-local')
            ->columns([
                ['field' => 'id',       'title' => '#',        'width' => 50,  'sortable' => true],
                ['field' => 'name',     'title' => 'Name',     'sortable' => true],
                ['field' => 'role',     'title' => 'Role',     'sortable' => true],
                ['field' => 'status',   'title' => 'Status',   'component' => ['type' => 'badge', 'textBind' => 'status']],
                ['field' => 'progress', 'title' => 'Progress', 'width' => 160, 'component' => ['type' => 'progressBar', 'valueBind' => 'progress', 'max' => 100, 'showPercent' => true]],
            ])
            ->dataSource($localData)
            ->sortable()
            ->resizable()
            ->pageable(5)
            ->selectable()
            ->filterable()
            ->striped() ?>
    </div>

    <h3>Remote Data (Server-side Pagination)</h3>
    <div class="m-demo-row">
        <?= $m->dataGrid('demo-grid-remote')
            ->columns([
                ['field' => 'id',       'title' => '#',        'width' => 60,  'sortable' => true],
                ['field' => 'title',    'title' => 'Task',     'sortable' => true],
                ['field' => 'assignee', 'title' => 'Assignee', 'sortable' => true],
                ['field' => 'status',   'title' => 'Status',   'sortable' => true, 'component' => ['type' => 'badge', 'textBind' => 'status']],
                ['field' => 'due_date', 'title' => 'Due Date', 'format' => 'date', 'sortable' => true],
            ])
            ->remoteUrl('/getGridData')
            ->pageable(10, 'remote')
            ->sortable()
            ->resizable()
            ->toolbar([
                ['text' => 'Add Task', 'icon' => 'fa-plus', 'click' => 'demoAddTask()'],
                ['text' => 'Refresh', 'icon' => 'fa-sync', 'click' => 'demoRefreshGrid()'],
            ])
            ->emptyState('No tasks found', 'Create a new task using the toolbar above.') ?>
    </div>

    <h3>With Grouping</h3>
    <div class="m-demo-row">
        <?= $m->dataGrid('demo-grid-group')
            ->columns([
                ['field' => 'id',   'title' => '#',    'width' => 50],
                ['field' => 'name', 'title' => 'Name', 'sortable' => true],
                ['field' => 'role', 'title' => 'Role', 'groupable' => true],
                ['field' => 'status', 'title' => 'Status'],
            ])
            ->dataSource($localData)
            ->groupable()
            ->sortable() ?>
    </div>

    <h3>Fixed Groups with a Header Template</h3>
    <p class="m-demo-desc"><code>->groupBy('order_id')</code> starts the grid grouped by a field that is not
        a visible column, with no drop zone. <code>->groupTemplate()</code> renders each header from the group's
        first row — <code>{field}</code> values are escaped, <code>{_count}</code> is the row count — so a header
        can carry its own links and buttons. Groups start expanded; the chevron collapses them.</p>
    <div class="m-demo-row">
        <?php
        $orderLines = [
            ['order_id' => 1042, 'customer' => 'Aroha Ngata',  'placed' => '22 Oct 2026 09:14', 'order_total' => '$96.00',  'item' => 'Kōwhai Tūī',      'size' => 'A3', 'qty' => 1, 'line_total' => '$45.00'],
            ['order_id' => 1042, 'customer' => 'Aroha Ngata',  'placed' => '22 Oct 2026 09:14', 'order_total' => '$96.00',  'item' => 'Greeting card set', 'size' => '',   'qty' => 3, 'line_total' => '$51.00'],
            ['order_id' => 1041, 'customer' => 'Sam O\'Brien', 'placed' => '21 Oct 2026 17:52', 'order_total' => '$180.00', 'item' => 'Snail Splash',     'size' => 'A2', 'qty' => 2, 'line_total' => '$180.00'],
            ['order_id' => 1040, 'customer' => 'Mei Chen',     'placed' => '20 Oct 2026 11:03', 'order_total' => '$32.00',  'item' => 'Coaster',          'size' => '',   'qty' => 4, 'line_total' => '$32.00'],
        ];
        ?>
        <?= $m->dataGrid('demo-grid-orders')
            ->columns([
                ['field' => 'item',       'title' => 'Item'],
                ['field' => 'size',       'title' => 'Size',  'width' => 70],
                ['field' => 'qty',        'title' => 'Qty',   'width' => 60, 'align' => 'right'],
                ['field' => 'line_total', 'title' => 'Total', 'width' => 90, 'align' => 'right'],
            ])
            ->dataSource($orderLines)
            ->groupBy('order_id')
            ->groupTemplate(
                '<strong>#{order_id}</strong> {customer}'
                . '<span class="m-datagrid-group-count">{placed} · {_count} items</span>'
                . '<span style="margin-left:auto">{order_total}</span>'
                . '<a class="m-button m-button-outline m-button-sm" href="#order-{order_id}">View</a>'
            )
            ->striped() ?>
    </div>

    <div class="m-demo-output" id="datagrid-output">Interact with a grid to see output...</div>

    <?= demoCodeTabs(
        '// Local data grid with features
<?= $m->dataGrid(\'myGrid\')
    ->columns([
        [\'field\' => \'id\',     \'title\' => \'#\',    \'width\' => 50, \'sortable\' => true],
        [\'field\' => \'name\',   \'title\' => \'Name\', \'sortable\' => true],
        [\'field\' => \'status\', \'title\' => \'Status\',
         \'component\' => [\'type\' => \'badge\', \'textBind\' => \'status\']],
        [\'field\' => \'progress\', \'title\' => \'Progress\', \'width\' => 160,
         \'component\' => [\'type\' => \'progressBar\',
                         \'valueBind\' => \'progress\',
                         \'max\' => 100, \'showPercent\' => true]],
    ])
    ->dataSource($rows)
    ->sortable()
    ->resizable()
    ->pageable(20)
    ->selectable()
    ->filterable()
    ->striped() ?>

// Remote data grid
<?= $m->dataGrid(\'remoteGrid\')
    ->columns([...])
    ->remoteUrl(\'/api/tasks\')
    ->pageable(25, \'remote\')
    ->sortable()
    ->toolbar([
        [\'text\' => \'Add\', \'icon\' => \'fa-plus\', \'click\' => \'addRow()\'],
    ]) ?>

// Groupable grid
<?= $m->dataGrid(\'grouped\')
    ->columns([...])->dataSource($data)->groupable() ?>

// Fixed groups (one per order) with a custom header
<?= $m->dataGrid(\'orders\')
    ->columns([...])            // the line-item columns
    ->dataSource($orderLines)   // one row per line, each carrying its order\'s fields
    ->groupBy(\'order_id\')
    ->groupTemplate(\'<strong>#{order_id}</strong> {customer} \'
        . \'<span style="margin-left:auto">{order_total}</span>\') ?>',
        '// Get grid instance
var grid = m.dataGrid(\'myGrid\');

// Refresh remote data
grid.refresh();

// Replace local data
grid.setData(newArray);

// Pagination
grid.goToPage(2);

// Sorting
grid.sort(\'name\', \'asc\');
grid.clearSort();

// Filtering
grid.clearFilters();

// Grouping
grid.groupBy(\'role\');
grid.clearGroup();

// Extra params for remote
grid.setExtraParams({filter: \'active\'});

// Get data
var data = grid.getData();        // current page
var total = grid.getTotal();      // total records
var selected = grid.getSelectedData();

// Clean up
grid.destroy();'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->dataGrid($id)', 'string', 'Create a DataGrid component.'],
    ['->columns($cols)', 'array', 'Define columns. Each column: <code>field</code>, <code>title</code>, <code>width</code>, <code>sortable</code>, <code>format</code>, <code>template</code>, <code>component</code>, <code>align</code>, <code>frozen</code>, <code>hidden</code>, <code>wrap</code>.'],
    ['->dataSource($data)', 'array', 'Provide local data (array of assoc arrays).'],
    ['->remoteUrl($url, $method)', 'string, string', 'Fetch data from a remote URL. Expected response: <code>{data: [], total: N}</code>.'],
    ['->remoteHeaders($headers)', 'array', 'Extra HTTP headers for remote requests.'],
    ['->pageable($pageSize, $mode)', 'int, string', 'Enable pagination. Mode: <code>local</code> or <code>remote</code>.'],
    ['->scrollOnPage($scroll)', 'bool', 'Scroll the grid into view on page change (default: <code>true</code>).'],
    ['->sortable()', '', 'Enable column sorting.'],
    ['->resizable()', '', 'Enable column resize.'],
    ['->reorderable()', '', 'Enable column drag-reorder.'],
    ['->groupable()', '', 'Enable row grouping by column: shows a drop zone to drag a column header onto.'],
    ['->groupBy($field)', 'string', 'Start grouped by <code>$field</code> (default: ungrouped). The field need not be a visible column. Without <code>->groupable()</code> there is no drop zone, so the grouping is fixed. Note that <code>->pageable()</code> pages by row, not by group.'],
    ['->groupTemplate($html)', 'string', 'Group header HTML, replacing the default <em>value (count)</em> label. <code>{field}</code> comes from the group\'s first row (escaped); <code>{_count}</code> is the row count. The expand/collapse toggle is kept.'],
    ['->selectable()', '', 'Enable single-row selection.'],
    ['->filterable()', '', 'Add column filter inputs.'],
    ['->extraParams($params)', 'array', 'Extra params sent with every remote request.'],
    ['->height($height)', 'string', 'Set fixed height with scrollable body (e.g. <code>400px</code>).'],
    ['->striped()', '', 'Enable striped rows.'],
    ['->borderless()', '', 'Remove outer border.'],
    ['->toolbar($buttons)', 'array', 'Add toolbar buttons: <code>[text, icon, click, class]</code>.'],
    ['->emptyState($title, $msg)', 'string, string', 'Empty-data message.'],
    ['->onDataBound($callback)', 'string', 'JS callback after data renders.'],
    ['->onRowClick($callback)', 'string', 'JS callback on row click.'],
    ['->onRowSelect($callback)', 'string', 'JS callback on row selection.'],
    ['->onRowExpand($callback)', 'string', 'JS callback on group expand/collapse.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.dataGrid(id, opts)', 'string, ?object', 'Get or create DataGrid instance.'],
    ['refresh()', '', 'Re-fetch remote data (returns Promise for remote grids).'],
    ['setData(array)', 'array', 'Replace the local dataset.'],
    ['goToPage(num)', 'int', 'Navigate to a specific page.'],
    ['sort(field, dir)', 'string, string', 'Sort by field. Dir: <code>asc</code> or <code>desc</code>.'],
    ['clearSort()', '', 'Clear current sort.'],
    ['clearFilters()', '', 'Clear all column filters.'],
    ['groupBy(field)', 'string', 'Group rows by the given field.'],
    ['clearGroup()', '', 'Remove grouping.'],
    ['setExtraParams(obj, merge)', 'object, ?bool', 'Set extra remote request params. Pass <code>true</code> to merge.'],
    ['getExtraParams()', '', 'Get current extra params object.'],
    ['getData()', '', 'Get the data array for the current page.'],
    ['getTotal()', '', 'Get total record count.'],
    ['getSelectedData()', '', 'Get the selected row data.'],
    ['destroy()', '', 'Clean up event listeners and DOM.'],
]) ?>

<?= apiTable('Column Component Types', 'js', [
    ['badge', 'textBind, text, variant, variantBind, icon, iconBind', 'Render a badge in the cell.'],
    ['label', 'textBind, text, variant, variantBind, icon, iconBind', 'Render a styled label.'],
    ['icon', 'iconBind, icon', 'Render a Font Awesome icon.'],
    ['checkbox', 'valueBind, readonly', 'Render a checkbox (read-only by default).'],
    ['rating', 'valueBind, max, halfStars', 'Render a star rating.'],
    ['progressBar', 'valueBind, max, variant, showPercent, striped, animated', 'Render a progress bar.'],
]) ?>

<script>
function demoAddTask() {
    m.dialog.alert('This is a demo — "Add Task" would open a form in your app.', 'Demo', 'fa-plus');
}
function demoRefreshGrid() {
    var grid = m.dataGrid('demo-grid-remote');
    if (grid && grid.refresh) {
        grid.refresh();
        setOutput('datagrid-output', '<strong>Remote grid refreshed</strong>');
    }
}
</script>
