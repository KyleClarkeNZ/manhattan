<?php
/** @var \Manhattan\HtmlHelper $m */

$phpIcon = <<<'PHP'
<?= $m->icon('fa-icons') ?>
PHP;
$jsIcon = <<<'JS'
m.icon('fa-icons');
JS;

$phpButton = <<<'PHP'
// Save form with primary button
<?= $m->button('saveBtn', 'Save Changes')
    ->primary()
    ->icon('fa-save')
    ->type('submit') ?>

// Delete with confirmation
<?= $m->button('deleteBtn', 'Delete')
    ->danger()
    ->icon('fa-trash')
    ->on('click', 'confirmDelete') ?>
PHP;
$jsButton = <<<'JS'
// Handle button click
function confirmDelete() {
    m.dialog.confirm('Delete this item?', 'Confirm', 'fa-trash')
        .then(function(ok) {
            if (ok) deleteItem();
        });
}
JS;

$phpBreadcrumb = <<<'PHP'
<?= $m->breadcrumb('main-nav')
    ->home('/')
    ->item('Recipes', '/recipes')
    ->item('Baking', '/recipes/baking')
    ->item('Sourdough Bread')
    ->current() ?>
PHP;
$jsBreadcrumb = <<<'JS'
// Breadcrumb is server-side rendered only - no JS API needed
JS;

$phpTextBox = <<<'PHP'
// Form field with validation
<label for="userEmail">Email Address</label>
<?= $m->textbox('userEmail')
    ->name('email')
    ->email()
    ->placeholder('you@example.com')
    ->required(true) ?>
PHP;
$jsTextBox = <<<'JS'
// Real-time validation
m.textbox('userEmail', {
    onInput: function(data) {
        validateEmail(data.value);
    }
});
JS;

$phpTextArea = <<<'PHP'
<?= $m->textarea('notes', ['rows' => 3, 'resize' => 'auto']) ?>
PHP;
$jsTextArea = <<<'JS'
m.textarea('notes', { autoResize: true });
JS;

$phpToggle = <<<'PHP'
<?= $m->toggleSwitch('notify', ['checked' => true, 'label' => 'On']) ?>
PHP;
$jsToggle = <<<'JS'
document.getElementById('notify').addEventListener('change', function () { /* ... */ });
JS;

$phpCheckbox = <<<'PHP'
<?= $m->checkbox('agree')->name('agree')->value('1')->label('I agree')->checked(true) ?>
PHP;
$jsCheckbox = <<<'JS'
document.getElementById('agree').addEventListener('change', function () { /* ... */ });
JS;

$phpRadio = <<<'PHP'
<?= $m->radio('plan_basic')->name('plan')->value('basic')->label('Basic')->checked(true) ?>
<?= $m->radio('plan_pro')->name('plan')->value('pro')->label('Pro') ?>
PHP;
$jsRadio = <<<'JS'
document.querySelectorAll('input[name="plan"]').forEach(function (el) {
    el.addEventListener('change', function () { /* ... */ });
});
JS;

$phpAddress = <<<'PHP'
<?= $m->address('demo-address', ['suggestUrl' => '/manhattan/nzpostSuggest']) ?>
PHP;
$jsAddress = <<<'JS'
m.address('demo-address', { onChange: function (data) { console.log(data); } });
JS;

$phpTabs = <<<'PHP'
<?= $m->tabs('myTabs')
    ->tab('general', 'General')->icon('fa-cog')->content('<p>General settings here.</p>')->active()
    ->tab('advanced', 'Advanced')->icon('fa-sliders-h')->content('<p>Advanced options.</p>')
    ->tab('disabled', 'Disabled')->disabled() ?>
PHP;
$jsTabs = <<<'JS'
// Listen for tab changes:
document.getElementById('myTabs').addEventListener('m-tab-change', function (e) {
    console.log('Tab changed to:', e.detail.key);
});
JS;

$phpDatePicker = <<<'PHP'
// Date range selection
<label>Start Date</label>
<?= $m->datepicker('startDate')
    ->name('start_date')
    ->value(date('Y-m-d'))
    ->placeholder('Select start date...') ?>

<label>End Date</label>
<?= $m->datepicker('endDate')
    ->name('end_date')
    ->min(date('Y-m-d'))
    ->placeholder('Select end date...') ?>
PHP;
$jsDatePicker = <<<'JS'
// Update min date when  start changes
document.getElementById('startDate')
    .addEventListener('change', function() {
        document.getElementById('endDate')
            .setAttribute('min', this.value);
    });
JS;

$phpDropdown = <<<'PHP'
// Dropdown with grouped options
<?= $m->dropdown('category')
    ->groupedDataSource([
        ['group' => 'Work', 'items' => [
            ['value' => '1', 'text' => 'Meetings'],
            ['value' => '2', 'text' => 'Reports']
        ]],
        ['group' => 'Personal', 'items' => [
            ['value' => '3', 'text' => 'Errands']
        ]]
    ])
    ->placeholder('Select category...')
    ->name('task_category') ?>
PHP;
$jsDropdown = <<<'JS'
// Listen for selection changes
m.dropdown('category', {
    events: {
        change: function(data) {
            loadTasksForCategory(data.value);
        }
    }
});
JS;

$phpList = <<<'PHP'
// Task list with drag-and-drop reordering
$taskItems = [
    ['key' => 't1', 'html' => '<i class="fas fa-clipboard-list"></i> Review pull requests'],
    ['key' => 't2', 'html' => '<i class="fas fa-clipboard-list"></i> Update documentation'],
    ['key' => 't3', 'html' => '<i class="fas fa-clipboard-list"></i> Deploy to staging']
];
echo $m->list('priorityList')
    ->items($taskItems)
    ->reorderable(true)
    ->emptyMessage('No tasks to display');
PHP;
$jsList = <<<'JS'
// Listen for reorder events
document.getElementById('priorityList')
    .addEventListener('m-list-reorder', function(e) {
        console.log('New order:', e.detail.order);
        savePriority(e.detail.order);
    });

// Add new item programmatically
var list = m.list('priorityList');
list.addItem({key: 't4', html: 'New task'});
JS;

$phpWindow = <<<'PHP'
// Define window structure with buttons
<?= $m->window('editTaskWindow', 'Edit Task')
    ->modal(true)
    ->width('600px')
    ->content($formHtml)
    ->addButton('Cancel', 'cancel', 'secondary')
    ->addButton('Save', 'save_task', 'primary') ?>
PHP;
$jsWindow = <<<'JS'
// Open window
m.window('editTaskWindow').open();

// Close window
m.window('editTaskWindow').close();

// Listen for button events
document.getElementById('editTaskWindow')
    .addEventListener('m-window-button', function(e) {
        if (e.detail.action === 'save_task') {
            saveTaskForm();
        }
    });
JS;

$phpDialog = <<<'PHP'
// Dialog is JS-only (no server-rendered HTML)
PHP;
$jsDialog = <<<'JS'
// Confirm before delete
m.dialog.confirm(
    'Delete this task? This cannot be undone.',
    'Confirm Delete',
    'fa-trash'
).then(function(confirmed) {
    if (confirmed) deleteTask(taskId);
});

// Prompt for input
m.dialog.prompt(
    'Enter task name:',
    'Create Task',
    'fa-plus'
).then(function(value) {
    if (value) createTask(value);
});

// Simple alert
m.dialog.alert('Task saved successfully!', 'Success', 'fa-check');
JS;

$phpToaster = <<<'PHP'
// Define toaster containers
<?= $m->toaster('banner')->position('banner') ?>
<?= $m->toaster('appToaster')->position('top-right') ?>
PHP;
$jsToaster = <<<'JS'
// Success notification
m.toaster('appToaster').show('Task saved successfully!', 'success');

// Error notification
m.toaster('appToaster').show('Failed to save task', 'error');

// Info with longer display
m.toaster('appToaster').show('Processing...', 'info', 5000);

// Banner-style notification (full-width top)
m.toaster('banner').show('New update available', 'warning');
JS;

// DataGrid demo data
$dgSampleData = [];
$statuses   = ['Pending', 'In Progress', 'Completed', 'On Hold', 'Cancelled'];
$priorities = ['Low', 'Medium', 'High', 'Critical'];
$owners     = ['Alice', 'Bob', 'Carol', 'Dave', 'Eve'];
$statusColors = ['Pending' => 'secondary', 'In Progress' => 'primary', 'Completed' => 'success', 'On Hold' => 'warning', 'Cancelled' => 'danger'];
for ($i = 1; $i <= 30; $i++) {
    $status = $statuses[($i - 1) % 5];
    $dgSampleData[] = [
        'id'          => $i,
        'task'        => 'Task ' . $i . ': ' . ['Refactor auth module', 'Write unit tests', 'Deploy to staging', 'Update docs', 'Fix bug #' . (100 + $i), 'Code review', 'Performance audit', 'Security scan', 'Database migration', 'UI polish'][($i - 1) % 10],
        'owner'       => $owners[($i - 1) % 5],
        'priority'    => $priorities[($i - 1) % 4],
        'status'      => $status,
        'statusColor' => $statusColors[$status],
        'due_date'    => date('Y-m-d', strtotime('+' . ($i * 3) . ' days')),
        'progress'    => ($i * 7) % 101,
        'done'        => ($i % 3 === 0),
        'rating'      => round((($i * 13) % 11) / 2, 1), // 0.0 – 5.0 in 0.5 steps
    ];
}

$phpDataGrid = <<<'PHP'
// Full-featured local grid
$columns = [
    ['field' => 'id',       'title' => '#',      'width' => 60,  'frozen' => true],
    ['field' => 'task',     'title' => 'Task',   'width' => 280, 'sortable' => true, 'resizable' => true, 'frozen' => true],
    ['field' => 'owner',    'title' => 'Owner',  'width' => 100, 'sortable' => true, 'groupable' => true],
    ['field' => 'status',   'title' => 'Status', 'width' => 120, 'sortable' => true, 'groupable' => true],
    ['field' => 'due_date', 'title' => 'Due',    'width' => 110, 'sortable' => true, 'format' => 'date'],
];

echo $m->dataGrid('tasksGrid')
    ->columns($columns)
    ->dataSource($tasks)
    ->pageable(20, 'local')
    ->sortable()
    ->resizable()
    ->reorderable()
    ->groupable()
    ->selectable()
    ->filterable()
    ->height('500px')
    ->toolbar([
        ['text' => 'Add Task', 'icon' => 'fa-plus', 'click' => 'addTask()'],
        ['text' => 'Refresh', 'icon' => 'fa-sync', 'click' => 'refreshGrid()']
    ])
    ->emptyState('No tasks found', 'Create your first task to get started');

// Remote grid with server-side pagination
echo $m->dataGrid('remoteGrid')
    ->columns($columns)
    ->remoteUrl('/api/tasks', 'POST')
    ->remoteHeaders(['X-CSRF-Token' => $csrfToken])
    ->extraParams(['project_id' => $projectId, 'status' => 'active'])
    ->pageable(25, 'remote')
    ->sortable()
    ->filterable()
    ->onDataBound('handleDataBound')
    ->onRowClick('handleRowClick');
PHP;
$jsDataGrid = <<<'JS'
// Get grid instance
var grid = m.dataGrid('tasksGrid');

// Refresh data
grid.refresh();

// Navigate pages
grid.goToPage(2);

// Sort programmatically
grid.sort('due_date', 'asc');

// Group by field
grid.groupBy('status');
grid.clearGroup();

// Get selected rows
var selected = grid.getSelectedData();

// Update data
grid.setData(newTasksArray);

// Event handlers
function handleDataBound() {
    console.log('Grid loaded');
}

function handleRowClick(row) {
    editTask(row.id);
}
JS;

$phpRating = <<<'PHP'
// Read-only display with half-star precision
<?= $m->rating('productRating')
    ->value(3.5)->max(5)
    ->halfStars()->readonly()
    ->label('Quality') ?>

// Interactive – fires onChange callback + m-rating-change event
<?= $m->rating('editRating')
    ->value(4)->max(5)
    ->label('Your rating')
    ->onChange('handleRating') ?>

// Sizes and colour variants
<?= $m->rating('r1')->value(3)->sm() ?>
<?= $m->rating('r2')->value(4)->lg()->color('success') ?>
PHP;
$jsRating = <<<'JS'
// Get/set value programmatically
var r = m.rating('editRating');
r.getValue();     // → 4
r.setValue(2.5);

// Listen via DOM event
document.getElementById('editRating')
    .addEventListener('m-rating-change', function (e) {
        console.log('New rating:', e.detail.value);
    });
JS;

function codeBlock(string $lang, string $code): string
{
    $m = \Manhattan\HtmlHelper::getInstance();

    static $i = 0;
    $i += 1;

    $id = 'code_' . preg_replace('/[^a-z0-9_]+/i', '_', strtolower($lang)) . '_' . $i;

    return (string)$m->codeArea($id)
        ->language($lang)
        ->value($code)
        ->readOnly(true)
        ->rows(8);
}
?>

<?php
    // Variables are already set by demo/index.php but re-set here so the view
    // also works when included directly from MyDay (backward compatible).
    if (!isset($mDemoTheme)) {
        $mDemoTheme = isset($_SESSION['manhattan_theme']) ? (string)$_SESSION['manhattan_theme'] : 'light';
        $mDemoIsDark = ($mDemoTheme === 'dark');
    }
    // Allow caller to pass in $cssBase / $jsBase; fall back to MyDay-compatible paths.
    if (!isset($cssBase)) { $cssBase = '/Manhattan/CSS'; }
    if (!isset($jsBase))  { $jsBase  = '/Manhattan/JS';  }
?>
<?php if ($mDemoIsDark): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($cssBase, ENT_QUOTES, 'UTF-8') ?>/manhattan-dark.css">
<?php endif; ?>

<style>
            .m-demo-container {
                display: flex;
                justify-content: center;
            }

            .m-demo-card {
                padding: 16px;
                width: 100%;
                max-width: 1100px;
            }

            .m-demo-header {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                padding: 24px;
            }

            .m-demo-card h1 {
                margin: 0 0 10px 0;
                color: #333;
                font-size: 28px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .m-demo-subtitle {
                color: #7f8c8d;
                margin-bottom: 18px;
                font-size: 14px;
            }

            .m-demo-nav {
                display: flex;
                flex-direction: column;
                gap: 14px;
                margin: 14px 0 0 0;
            }

            .m-demo-nav-group {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .m-demo-nav-group-label {
                font-size: 10px;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #9aa0a9;
                padding: 0 2px;
            }

            .m-demo-nav-links {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }

            .m-demo-nav a {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 5px 10px;
                border-radius: 999px;
                background: #f8f9fa;
                border: 1px solid #e0e0e0;
                color: #2c3e50;
                text-decoration: none;
                font-size: 12.5px;
                font-weight: 600;
                transition: border-color 0.15s, color 0.15s;
            }

            .m-demo-nav a:hover { border-color: #2196F3; color: #2196F3; }

            .m-demo-section {
                margin-top: 16px;
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                padding: 18px 20px;
            }

            .m-demo-section h2 {
                font-size: 16px;
                color: #2c3e50;
                margin-bottom: 12px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .m-demo-section h3 {
                font-size: 14px;
                color: #555;
                margin: 14px 0 8px 0;
            }

            .m-demo-desc {
                font-size: 13px;
                color: #7f8c8d;
                margin-bottom: 14px;
            }

            .m-demo-row {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-items: flex-end;
            }

            .m-demo-field {
                flex: 1;
                min-width: 240px;
            }

            .m-demo-field label {
                display: block;
                font-size: 13px;
                color: #666;
                margin-bottom: 6px;
                font-weight: 600;
            }

            .m-demo-output {
                margin-top: 12px;
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 12px;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
                font-size: 13px;
                color: #333;
            }

            details.m-demo-code {
                margin-top: 12px;
                border: 1px solid #e0e0e0;
                border-radius: 10px;
                background: #fff;
                overflow: hidden;
            }

            details.m-demo-code > summary {
                cursor: pointer;
                list-style: none;
                padding: 10px 12px;
                font-weight: 700;
                color: #2c3e50;
                background: #f8f9fa;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            details.m-demo-code > summary::-webkit-details-marker { display: none; }

            .m-code-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 12px;
            }

            @media (min-width: 900px) {
                .m-code-grid { grid-template-columns: 1fr 1fr; }
            }

            .m-code-block { border: 1px solid #e9ecef; border-radius: 10px; overflow: hidden; }
            .m-code-label { background: #282c34; color: #fff; padding: 6px 10px; font-size: 12px; font-weight: 800; }
            .m-code-block pre { margin: 0; padding: 10px; background: #1f232a; color: #c9d1d9; overflow: auto; }
            .m-code-block code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size: 12px; }

            .m-demo-pills { display: flex; flex-wrap: wrap; gap: 10px; }
            .m-demo-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 10px; border: 1px solid #e0e0e0; border-radius: 999px; background: #fafafa; font-size: 13px; }
            .m-textbox-wrapper { width: 100%; }

            /* Theme toggle button */
            .m-demo-theme-toggle {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 14px;
                border-radius: 8px;
                border: 1px solid #e0e0e0;
                background: #f8f9fa;
                color: #2c3e50;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                float: right;
                margin-top: -4px;
            }
            .m-demo-theme-toggle:hover { border-color: #2196F3; color: #2196F3; }

            /* === Demo page dark overrides === */
            body.m-dark .m-demo-header,
            body.m-dark .m-demo-section,
            body.m-dark details.m-demo-code {
                background: #1e242b;
                border-color: rgba(255,255,255,0.12);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }

            body.m-dark .m-demo-card h1,
            body.m-dark .m-demo-section h2 {
                color: #eaecef;
            }

            body.m-dark .m-demo-section h3 {
                color: #a7b0bb;
            }

            body.m-dark .m-demo-subtitle,
            body.m-dark .m-demo-desc {
                color: #a7b0bb;
            }

            body.m-dark .m-demo-nav a {
                background: rgba(255,255,255,0.06);
                border-color: rgba(255,255,255,0.14);
                color: #a7b0bb;
            }
            body.m-dark .m-demo-nav a:hover { border-color: #64b5f6; color: #64b5f6; }
            body.m-dark .m-demo-nav-group-label { color: #5a6470; }

            body.m-dark .m-demo-output {
                background: #161b22;
                border-color: rgba(255,255,255,0.12);
                color: #e6e6e6;
            }

            body.m-dark details.m-demo-code > summary {
                background: #161b22;
                border-color: rgba(255,255,255,0.10);
                color: #a7b0bb;
            }

            body.m-dark .m-demo-pill {
                background: rgba(255,255,255,0.06);
                border-color: rgba(255,255,255,0.14);
                color: #e6e6e6;
            }

            body.m-dark .m-demo-field label {
                color: #a7b0bb;
            }

            body.m-dark .m-demo-theme-toggle {
                background: rgba(255,255,255,0.06);
                border-color: rgba(255,255,255,0.14);
                color: #a7b0bb;
            }
            body.m-dark .m-demo-theme-toggle:hover { border-color: #64b5f6; color: #64b5f6; }
        </style>

        <div class="m-demo-container">
            <?php ob_start(); ?>
                <div class="m-demo-header">
                    <button type="button" class="m-demo-theme-toggle" id="mDemoThemeToggle">
                        <?php if ($mDemoIsDark): ?>
                            <i class="fas fa-sun"></i> Light
                        <?php else: ?>
                            <i class="fas fa-moon"></i> Dark
                        <?php endif; ?>
                    </button>
                    <h1>Manhattan UI Components</h1>
                    <div class="m-demo-subtitle">End-to-end MVC-integrated component library for PHP and JQuery</div>

                    <?= $m->toaster('demoBannerToaster')->position('banner') ?>

                    <div class="m-demo-nav">

                        <div class="m-demo-nav-group">
                            <div class="m-demo-nav-group-label">Layout &amp; Display</div>
                            <div class="m-demo-nav-links">
                                <a href="#icons"><?= $m->icon('fa-icons') ?> Icons</a>
                                <a href="#badge"><?= $m->icon('fa-certificate') ?> Badge</a>
                                <a href="#breadcrumb"><?= $m->icon('fa-chevron-right') ?> Breadcrumb</a>
                                <a href="#page-header"><?= $m->icon('fa-heading') ?> PageHeader</a>
                                <a href="#label"><?= $m->icon('fa-tag') ?> Label</a>
                                <a href="#stat-card"><?= $m->icon('fa-tachometer-alt') ?> StatCard</a>
                                <a href="#empty-state"><?= $m->icon('fa-inbox') ?> EmptyState</a>
                                <a href="#tabs"><?= $m->icon('fa-folder') ?> Tabs</a>
                            </div>
                        </div>

                        <div class="m-demo-nav-group">
                            <div class="m-demo-nav-group-label">Actions &amp; Navigation</div>
                            <div class="m-demo-nav-links">
                                <a href="#buttons"><?= $m->icon('fa-hand-pointer') ?> Button</a>
                                <a href="#dropdown"><?= $m->icon('fa-chevron-circle-down') ?> Dropdown</a>
                            </div>
                        </div>

                        <div class="m-demo-nav-group">
                            <div class="m-demo-nav-group-label">Editors &amp; Forms</div>
                            <div class="m-demo-nav-links">
                                <a href="#textbox"><?= $m->icon('fa-i-cursor') ?> TextBox</a>
                                <a href="#numberbox"><?= $m->icon('fa-hashtag') ?> NumberBox</a>
                                <a href="#textarea"><?= $m->icon('fa-align-left') ?> TextArea</a>
                                <a href="#toggles"><?= $m->icon('fa-toggle-on') ?> Toggle</a>
                                <a href="#checkbox"><?= $m->icon('fa-check-square') ?> Checkbox</a>
                                <a href="#radio"><?= $m->icon('fa-dot-circle') ?> Radio</a>
                                <a href="#datepicker"><?= $m->icon('fa-calendar-alt') ?> DatePicker</a>
                                <a href="#address"><?= $m->icon('fa-map-marker-alt') ?> Address</a>
                                <a href="#validator"><?= $m->icon('fa-check-circle') ?> Validator</a>
                            </div>
                        </div>

                        <div class="m-demo-nav-group">
                            <div class="m-demo-nav-group-label">Data &amp; Visualisation</div>
                            <div class="m-demo-nav-links">
                                <a href="#datagrid"><?= $m->icon('fa-table') ?> DataGrid</a>
                                <a href="#list"><?= $m->icon('fa-list') ?> List</a>
                                <a href="#chart"><?= $m->icon('fa-chart-bar') ?> Chart</a>
                                <a href="#progress"><?= $m->icon('fa-tasks') ?> ProgressBar</a>
                                <a href="#rating"><?= $m->icon('fa-star') ?> Rating</a>
                            </div>
                        </div>

                        <div class="m-demo-nav-group">
                            <div class="m-demo-nav-group-label">Overlays &amp; Feedback</div>
                            <div class="m-demo-nav-links">
                                <a href="#window"><?= $m->icon('fa-window-maximize') ?> Window</a>
                                <a href="#dialog"><?= $m->icon('fa-comment-dots') ?> Dialog</a>
                                <a href="#toaster"><?= $m->icon('fa-bell') ?> Toaster</a>
                                <a href="#tooltip"><?= $m->icon('fa-comment') ?> Tooltip</a>
                            </div>
                        </div>

                        <div class="m-demo-nav-group">
                            <div class="m-demo-nav-group-label">Utilities</div>
                            <div class="m-demo-nav-links">
                                <a href="#codearea"><?= $m->icon('fa-code') ?> CodeArea</a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Icons -->
                <div id="icons" class="m-demo-section">
                    <h2><?= $m->icon('fa-icons') ?> Icon</h2>
                    <div class="m-demo-pills">
                        <span class="m-demo-pill"><?= $m->icon('fa-check') ?> fa-check</span>
                        <span class="m-demo-pill"><?= $m->icon('fa-info-circle') ?> fa-info-circle</span>
                        <span class="m-demo-pill"><?= $m->icon('fa-exclamation-triangle') ?> fa-exclamation-triangle</span>
                        <span class="m-demo-pill"><?= $m->icon('far fa-circle') ?> far fa-circle</span>
                        <span class="m-demo-pill"><?= $m->icon('fas fa-spinner fa-spin') ?> fa-spinner fa-spin</span>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpIcon) ?>
                            <?= codeBlock('js', $jsIcon) ?>
                        </div>
                    </details>
                </div>

                <!-- Badge -->
                <div id="badge" class="m-demo-section">
                    <h2><?= $m->icon('fa-certificate') ?> Badge</h2>
                    <div class="m-demo-pills">
                        <span class="m-badge m-badge-primary"><?= $m->icon('fa-star') ?> Primary</span>
                        <span class="m-badge m-badge-success"><?= $m->icon('fa-check') ?> Success</span>
                        <span class="m-badge m-badge-warning"><?= $m->icon('fa-exclamation') ?> Warning</span>
                        <span class="m-badge m-badge-danger"><?= $m->icon('fa-times') ?> Danger</span>
                        <span class="m-badge m-badge-purple"><?= $m->icon('fa-crown') ?> Purple</span>
                        <span class="m-badge m-badge-secondary"><?= $m->icon('fa-info') ?> Secondary</span>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('html', '<span class="m-badge m-badge-primary">' . $m->icon('fa-star') . ' Primary</span>') ?>
                            <div class="m-code-block">
                                <div class="m-code-label">CSS Classes</div>
                                <pre><code>.m-badge .m-badge-primary
.m-badge .m-badge-success
.m-badge .m-badge-warning
.m-badge .m-badge-danger
.m-badge .m-badge-purple
.m-badge .m-badge-secondary</code></pre>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Breadcrumb -->
                <div id="breadcrumb" class="m-demo-section">
                    <h2><?= $m->icon('fa-stream') ?> Breadcrumb</h2>
                    <p class="m-demo-desc">Hierarchical navigation trail showing where the user is within the application. Server-side rendered, accessible, and dark-mode aware.</p>

                    <h3 style="margin:1.25rem 0 0.75rem;font-size:0.9rem;font-weight:600;color:var(--m-text-secondary,#555);">Multi-level navigation</h3>
                    <div class="m-demo-row">
                        <?= $m->breadcrumb('demo-recipe')
                            ->home('/', 'Home')
                            ->item('Recipes', '/recipes')
                            ->item('Baking', '/recipes/baking')
                            ->item('Sourdough Bread')
                            ->current() ?>
                    </div>

                    <h3 style="margin:1.25rem 0 0.75rem;font-size:0.9rem;font-weight:600;color:var(--m-text-secondary,#555);">With custom icons</h3>
                    <div class="m-demo-row">
                        <?= $m->breadcrumb('demo-admin')
                            ->item('Admin', '/admin', 'fa-tachometer-alt')
                            ->item('Users', '/admin/users', 'fa-users')
                            ->item('Edit User')
                            ->current() ?>
                    </div>

                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpBreadcrumb) ?>
                            <div class="m-code-block">
                                <div class="m-code-label">API Reference</div>
                                <pre><code>// Shorthand home item (fa-home auto-added)
->home($url, $text = 'Home')

// Linked item
->item($text, $url)

// Linked item with custom icon
->item($text, $url, 'fa-icon-name')

// Current page — null url auto-marks current
->item($text)

// Explicitly mark last item as current
->current()</code></pre>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- PageHeader -->
                <div id="page-header" class="m-demo-section">
                    <h2><?= $m->icon('fa-heading') ?> PageHeader</h2>
                    <p class="m-demo-desc">Standardised page title area — combines an optional breadcrumb, icon, h1 and subtitle into a single reusable component.</p>
                    <?php
                    $demoPageHeaderBc = $m->breadcrumb('demo-ph-bc')->home('/components','Components')->item('Library')->current();
                    ?>
                    <div class="m-demo-row">
                        <?= $m->pageHeader('demo-ph')
                            ->breadcrumb($demoPageHeaderBc)
                            ->icon('fa-book')
                            ->title('Component Library')
                            ->subtitle('Browse all available Manhattan UI components.') ?>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <?= codeBlock('php', '<?php
$bc = $m->breadcrumb(\'nav\')->home(\'/\',\'Home\')->item(\'Library\')->current();
?>
<?= $m->pageHeader(\'ph\')
    ->breadcrumb($bc)
    ->icon(\'fa-book\')
    ->title(\'Page Title\')
    ->subtitle(\'Optional subtitle below the heading.\') ?>') ?>
                    </details>
                </div>

                <!-- Label -->
                <div id="label" class="m-demo-section">
                    <h2><?= $m->icon('fa-tag') ?> Label</h2>
                    <div class="m-demo-pills">
                        <span class="m-label m-label-primary"><?= $m->icon('fa-info-circle') ?> Primary</span>
                        <span class="m-label m-label-success"><?= $m->icon('fa-check') ?> Success</span>
                        <span class="m-label m-label-warning"><?= $m->icon('fa-exclamation-triangle') ?> Warning</span>
                        <span class="m-label m-label-danger"><?= $m->icon('fa-times-circle') ?> Danger</span>
                        <span class="m-label m-label-purple"><?= $m->icon('fa-star') ?> Purple</span>
                        <span class="m-label m-label-secondary"><?= $m->icon('fa-tag') ?> Secondary</span>
                        <span class="m-label m-label-default"><?= $m->icon('fa-tag') ?> Default</span>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('html', '<span class="m-label m-label-primary">' . $m->icon('fa-info-circle') . ' Primary</span>') ?>
                            <div class="m-code-block">
                                <div class="m-code-label">Usage</div>
                                <pre><code>Badges: Important status with gradients
Labels: Subtle text indicators
Use labels for status tags, categories</code></pre>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Button -->
                <div id="buttons" class="m-demo-section">
                    <h2><?= $m->icon('fa-hand-pointer') ?> Button</h2>
                    <div class="m-demo-row">
                        <?= $m->button('btn-primary', 'Primary')->primary()->icon('fa-rocket')->on('click', 'handlePrimaryClick') ?>
                        <?= $m->button('btn-secondary', 'Secondary')->icon('fa-save') ?>
                        <?= $m->button('btn-icon', 'Icon')->icon('fa-star') ?>
                        <?= $m->button('btn-disabled', 'Disabled')->icon('fa-ban')->attr('disabled', 'disabled') ?>
                        <?= $m->button('btn-client', 'Client Button') ?>
                    </div>
                    <div class="m-demo-output" id="button-output">Click a button to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpButton) ?>
                            <?= codeBlock('js', $jsButton) ?>
                        </div>
                    </details>
                </div>

                <!-- StatCard -->
                <div id="stat-card" class="m-demo-section">
                    <h2><?= $m->icon('fa-tachometer-alt') ?> StatCard</h2>
                    <p class="m-demo-desc">Compact metric cards ideal for key numbers, KPIs and summary statistics.</p>
                    <div class="m-demo-row" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
                        <?= $m->statCard('sc-tasks')->icon('fa-clipboard-list')->value('24')->label('Tasks Today')->primary() ?>
                        <?= $m->statCard('sc-done')->icon('fa-check-circle')->value('18')->label('Completed')->success()->delta('+3 today')->deltaUp() ?>
                        <?= $m->statCard('sc-pending')->icon('fa-clock')->value('6')->label('Overdue')->warning()->delta('-1 this week')->deltaDown() ?>
                        <?= $m->statCard('sc-streak')->icon('fa-fire')->value('12')->label('Day Streak')->purple() ?>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <?= codeBlock('php', '<?= $m->statCard(\'id\')
    ->icon(\'fa-check-circle\')
    ->value(\'42\')
    ->label(\'Tasks Done\')
    ->success()
    ->delta(\'+5 today\')
    ->deltaUp() ?>') ?>
                    </details>
                </div>

                <!-- EmptyState -->
                <div id="empty-state" class="m-demo-section">
                    <h2><?= $m->icon('fa-inbox') ?> EmptyState</h2>
                    <p class="m-demo-desc">Zero-data placeholders shown when a list or view has no content yet. Supports link actions, JS click handlers, FAB triggers, and a compact variant for inline panels.</p>

                    <h3 style="margin:1.25rem 0 0.5rem;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--m-text-muted,#888);">Default variant</h3>
                    <div class="m-demo-row">
                        <?= $m->emptyState('demo-empty-tasks')
                            ->icon('fa-clipboard-list')
                            ->title('No tasks yet')
                            ->message('Get started by adding your first task. Your tasks will appear here.')
                            ->action('Add your first task', '/tasks/create', 'fa-plus') ?>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <?= codeBlock('php', '<?= $m->emptyState(\'id\')
    ->icon(\'fa-clipboard-list\')
    ->title(\'No tasks yet\')
    ->message(\'Add your first task to get started.\')
    ->action(\'Add Task\', \'/tasks/create\', \'fa-plus\') ?>

// JS click handler instead of link
<?= $m->emptyState(\'id2\')
    ->icon(\'fa-sync\')
    ->title(\'No data loaded\')
    ->message(\'Click to fetch data.\')
    ->actionJs(\'Load Data\', \'fetchData()\', \'fa-download\') ?>

// FAB trigger (opens floating action button window)
<?= $m->emptyState(\'id3\')
    ->icon(\'fa-chart-line\')
    ->title(\'No activity tracked\')
    ->actionFab(\'Track Activity\', \'activity\', \'fa-plus\') ?>') ?>
                    </details>

                    <h3 style="margin:1.25rem 0 0.5rem;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--m-text-muted,#888);">Compact variant (for panels)</h3>
                    <div class="m-demo-row" style="gap:1rem;align-items:flex-start;">
                        <div style="flex:1;min-width:220px;max-width:300px;">
                            <?= $m->emptyState('demo-empty-compact-b')
                                ->compact()
                                ->bordered()
                                ->icon('fa-inbox')
                                ->title('No items scheduled')
                                ->actionFab('Add Task', 'task', 'fa-plus') ?>
                        </div>
                        <div style="flex:1;min-width:220px;max-width:300px;">
                            <?= $m->emptyState('demo-empty-compact-b2')
                                ->compact()
                                ->bordered()
                                ->icon('fa-running')
                                ->title('No activity tracked')
                                ->actionFab('Track', 'activity', 'fa-plus') ?>
                        </div>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <?= codeBlock('php', '<?= $m->emptyState(\'id\')
    ->compact()
    ->bordered()
    ->icon(\'fa-inbox\')
    ->title(\'No items scheduled\')
    ->actionFab(\'Add Task\', \'task\', \'fa-plus\') ?>') ?>
                    </details>
                </div>

                <!-- Rating -->
                <div id="rating" class="m-demo-section">
                    <h2><?= $m->icon('fa-star') ?> Rating</h2>
                    <p class="m-demo-desc">Star-based rating widget. Supports read-only display (with half-star precision), interactive editing, keyboard navigation, sizes, colour variants, and an <code>onChange</code> callback.</p>

                    <h3>Display &amp; Variants</h3>
                    <div class="m-demo-row" style="flex-wrap:wrap;gap:1.5rem;align-items:center">
                        <?= $m->rating('rDisplay1')->value(4)->max(5)->readonly()->label('4 stars') ?>
                        <?= $m->rating('rDisplay2')->value(3.5)->max(5)->halfStars()->readonly()->label('3.5 stars (half)') ?>
                        <?= $m->rating('rSm')->value(3)->max(5)->readonly()->sm()->label('Small size') ?>
                        <?= $m->rating('rLg')->value(3)->max(5)->readonly()->lg()->label('Large size') ?>
                        <?= $m->rating('rColPrimary')->value(4)->max(5)->readonly()->color('primary')->label('Primary color') ?>
                        <?= $m->rating('rColSuccess')->value(4)->max(5)->readonly()->color('success')->label('Success color') ?>
                    </div>

                    <h3>Interactive</h3>
                    <p class="m-demo-desc">Click a star to set the rating (click the same star again to clear). Use <kbd>←</kbd><kbd>→</kbd> arrow keys when focused.</p>
                    <div class="m-demo-row" style="flex-wrap:wrap;gap:2rem;align-items:flex-start">
                        <div>
                            <p style="margin:0 0 .4rem;font-size:.875rem;font-weight:600">Rate this component:</p>
                            <?= $m->rating('rInteractive')->value(3)->max(5)->halfStars(false)->label('Your rating')->onChange('handleDemoRating') ?>
                            <div class="m-demo-output" id="rating-output" style="margin-top:.6rem">Click a star to rate...</div>
                        </div>
                        <div>
                            <p style="margin:0 0 .4rem;font-size:.875rem;font-weight:600">Out of 10:</p>
                            <?= $m->rating('rTen')->value(7)->max(10)->label('Score') ?>
                        </div>
                    </div>

                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpRating) ?>
                            <?= codeBlock('js', $jsRating) ?>
                        </div>
                    </details>
                </div>

                <!-- ProgressBar -->
                <div id="progress" class="m-demo-section">
                    <h2><?= $m->icon('fa-tasks') ?> ProgressBar</h2>
                    <p class="m-demo-desc">Linear progress indicator with label, percentage, variants, and optional animation.</p>
                    <div style="max-width:600px;">
                        <div style="margin-bottom:1rem;">
                            <?= $m->progressBar('pb-tasks')->label('Tasks completed')->value(18)->max(24)->showPercent()->success() ?>
                        </div>
                        <div style="margin-bottom:1rem;">
                            <?= $m->progressBar('pb-steps')->label('Steps goal')->value(7200)->max(10000)->showPercent()->primary()->striped() ?>
                        </div>
                        <div style="margin-bottom:1rem;">
                            <?= $m->progressBar('pb-upload')->label('Uploading...')->value(65)->max(100)->showPercent()->warning()->striped()->animated() ?>
                        </div>
                        <div>
                            <?= $m->progressBar('pb-danger')->label('Disk usage')->value(88)->max(100)->showPercent()->danger() ?>
                        </div>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <?= codeBlock('php', '<?= $m->progressBar(\'id\')
    ->label(\'Tasks completed\')
    ->value(18)
    ->max(24)
    ->showPercent()
    ->success() ?>

// Animated striped
<?= $m->progressBar(\'id2\')
    ->label(\'Uploading...\')
    ->value(65)->max(100)
    ->warning()->striped()->animated() ?>') ?>
                    </details>
                </div>

                <!-- TextBox -->
                <div id="textbox" class="m-demo-section">
                    <h2><?= $m->icon('fa-i-cursor') ?> TextBox</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field">
                            <label>Email (HTML5 validation):</label>
                            <div class="m-textbox-wrapper">
                                <?= $m->textbox('demo-email')->email()->placeholder('name@example.com')->required(true) ?>
                            </div>
                        </div>
                        <div class="m-demo-field">
                            <label>Password:</label>
                            <div class="m-textbox-wrapper">
                                <?= $m->textbox('demo-password')->password('new-password')->placeholder('Enter a password')->minLength(8) ?>
                            </div>
                        </div>
                    </div>
                    <div class="m-demo-output" id="inputs-output">Type to see output...</div>

                <!-- Address -->
                <div id="address" class="m-demo-section">
                    <h2><?= $m->icon('fa-map-marker-alt') ?> Address</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field" style="width: 100%; max-width: 760px;">
                            <?= $m->address('demo-address', ['suggestUrl' => '/manhattan/nzpostSuggest']) ?>
                        </div>
                    </div>
                    <div class="m-demo-output" id="address-output">Choose NZ or Overseas, then enter an address...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpAddress) ?>
                            <?= codeBlock('js', $jsAddress) ?>
                        </div>
                    </details>
                </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpTextBox) ?>
                            <?= codeBlock('js', $jsTextBox) ?>
                        </div>
                    </details>
                </div>

                <!-- NumberBox -->
                <div id="numberbox" class="m-demo-section">
                    <h2><?= $m->icon('fa-hashtag') ?> NumberBox</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field">
                            <label>Integer (0-100):</label>
                            <div class="m-textbox-wrapper">
                                <?= $m->numberbox('demo-integer')->integer()->range(0, 100)->placeholder('Enter a whole number') ?>
                            </div>
                        </div>
                        <div class="m-demo-field">
                            <label>Decimal (2 places):</label>
                            <div class="m-textbox-wrapper">
                                <?= $m->numberbox('demo-decimal')->decimal(2)->min(0)->placeholder('e.g. 12.34') ?>
                            </div>
                        </div>
                    </div>
                    <div class="m-demo-output" id="numberbox-output">Enter numbers to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', '<?= $m->numberbox(\'quantity\')->integer()->range(0, 100) ?>') ?>
                            <?= codeBlock('php', '<?= $m->numberbox(\'price\')->decimal(2)->min(0) ?>') ?>
                        </div>
                    </details>
                </div>

                <!-- TextArea -->
                <div id="textarea" class="m-demo-section">
                    <h2><?= $m->icon('fa-align-left') ?> TextArea</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field">
                            <label>Notes (auto-resize):</label>
                            <?= $m->textarea('demo-notes', ['rows' => 3, 'resize' => 'auto', 'placeholder' => 'Type a few lines...']) ?>
                        </div>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpTextArea) ?>
                            <?= codeBlock('js', $jsTextArea) ?>
                        </div>
                    </details>
                </div>

                <!-- Toggle -->
                <div id="toggles" class="m-demo-section">
                    <h2><?= $m->icon('fa-toggle-on') ?> ToggleSwitch</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field">
                            <label>Enable notifications:</label>
                            <?= $m->toggleSwitch('demo-toggle-notify', ['checked' => true, 'label' => 'On']) ?>
                        </div>
                        <div class="m-demo-field">
                            <label>Disabled:</label>
                            <?= $m->toggleSwitch('demo-toggle-disabled', ['checked' => false, 'disabled' => true, 'label' => 'Disabled']) ?>
                        </div>
                    </div>
                    <div class="m-demo-output" id="toggle-output">Toggle to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpToggle) ?>
                            <?= codeBlock('js', $jsToggle) ?>
                        </div>
                    </details>
                </div>

                <!-- Checkbox -->
                <div id="checkbox" class="m-demo-section">
                    <h2><?= $m->icon('fa-check-square') ?> Checkbox</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field">
                            <label>Opt-in:</label>
                            <?= $m->checkbox('demo-checkbox-optin')->name('optin')->value('1')->label('Email notifications')->checked(true) ?>
                        </div>
                        <div class="m-demo-field">
                            <label>Disabled:</label>
                            <?= $m->checkbox('demo-checkbox-disabled')->name('disabled')->value('1')->label('Disabled')->disabled(true) ?>
                        </div>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpCheckbox) ?>
                            <?= codeBlock('js', $jsCheckbox) ?>
                        </div>
                    </details>
                </div>

                <!-- Radio -->
                <div id="radio" class="m-demo-section">
                    <h2><?= $m->icon('fa-dot-circle') ?> Radio</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field">
                            <label>Plan:</label>
                            <?= $m->radio('demo-radio-basic')->name('demo_plan')->value('basic')->label('Basic')->checked(true) ?>
                            <?= $m->radio('demo-radio-pro')->name('demo_plan')->value('pro')->label('Pro') ?>
                            <?= $m->radio('demo-radio-enterprise')->name('demo_plan')->value('enterprise')->label('Enterprise')->disabled(true) ?>
                        </div>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpRadio) ?>
                            <?= codeBlock('js', $jsRadio) ?>
                        </div>
                    </details>
                </div>

                <!-- Tabs -->
                <div id="tabs" class="m-demo-section">
                    <h2><?= $m->icon('fa-folder') ?> Tabs</h2>
                    <p class="m-demo-desc">
                        Tabbed panels for organising content into switchable sections. Supports default, pills, and underline styles. Fully keyboard-navigable with ARIA roles.
                    </p>

                    <h3>With Badge Counts</h3>
                    <div class="m-demo-row">
                        <?= $m->tabs('demoTabsBadge')
                            ->tab('all', 'All Tasks')->icon('fa-list')->content('<p style="padding:1rem">Showing all tasks.</p>')
                            ->tab('pending', 'Pending')->icon('fa-clock')->content('<p style="padding:1rem">Pending tasks.</p>')->active()
                            ->tab('overdue', 'Overdue')->icon('fa-exclamation-circle')->content('<p style="padding:1rem">Overdue tasks.</p>')
                            ->badge('pending', 6)
                            ->badge('overdue', 2) ?>
                    </div>

                    <h3>Default</h3>
                    <div class="m-demo-row">
                        <?= $m->tabs('demoTabsDefault')
                            ->tab('general', 'General')->icon('fa-cog')->content('<p style="padding:1rem">General settings content goes here.</p>')->active()
                            ->tab('advanced', 'Advanced')->icon('fa-sliders-h')->content('<p style="padding:1rem">Advanced options content goes here.</p>')
                            ->tab('disabled', 'Disabled')->disabled() ?>
                    </div>

                    <h3>Pills</h3>
                    <div class="m-demo-row">
                        <?= $m->tabs('demoTabsPills')->tabStyle('pills')
                            ->tab('inbox', 'Inbox')->icon('fa-inbox')->content('<p style="padding:1rem">Your inbox messages.</p>')->active()
                            ->tab('sent', 'Sent')->icon('fa-paper-plane')->content('<p style="padding:1rem">Sent messages.</p>')
                            ->tab('drafts', 'Drafts')->icon('fa-file-alt')->content('<p style="padding:1rem">Draft messages.</p>') ?>
                    </div>

                    <h3>Underline</h3>
                    <div class="m-demo-row">
                        <?= $m->tabs('demoTabsUnderline')->tabStyle('underline')
                            ->tab('overview', 'Overview')->content('<p style="padding:1rem">Overview panel.</p>')->active()
                            ->tab('details', 'Details')->content('<p style="padding:1rem">Detailed information.</p>')
                            ->tab('history', 'History')->content('<p style="padding:1rem">History log.</p>') ?>
                    </div>

                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpTabs) ?>
                            <?= codeBlock('js', $jsTabs) ?>
                        </div>
                    </details>
                </div>

                <!-- DatePicker -->
                <div id="datepicker" class="m-demo-section">
                    <h2><?= $m->icon('fa-calendar-alt') ?> DatePicker</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field">
                            <label>Start Date:</label>
                            <?= $m->datepicker('datepicker-start', ['value' => $currentDate, 'placeholder' => 'Select start date...']) ?>
                        </div>
                        <div class="m-demo-field">
                            <label>Due Date (min = start):</label>
                            <?= $m->datepicker('datepicker-due')->value($dueDate)->placeholder('Select due date...')->min($currentDate) ?>
                        </div>
                    </div>
                    <div class="m-demo-output" id="datepicker-output">Select dates to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpDatePicker) ?>
                            <?= codeBlock('js', $jsDatePicker) ?>
                        </div>
                    </details>
                </div>

                <!-- Dropdown -->
                <div id="dropdown" class="m-demo-section">
                    <h2><?= $m->icon('fa-chevron-circle-down') ?> Dropdown</h2>
                    <div class="m-demo-row">
                        <div class="m-demo-field">
                            <label>Priority:</label>
                            <?= $m->dropdown('dropdown-priority')->dataSource($priorities)->value('2')->placeholder('Select priority...')->name('priority') ?>
                        </div>
                        <div class="m-demo-field">
                            <label>Category:</label>
                            <?= $m->dropdown('dropdown-category', ['textField' => 'name', 'valueField' => 'id', 'placeholder' => 'Select category...', 'name' => 'category'])->dataSource($categories) ?>
                        </div>
                    </div>
                    <div class="m-demo-row" style="margin-top: 12px;">
                        <div class="m-demo-field">
                            <label>Grouped Options:</label>
                            <?php
                            $groupedCategories = [
                                ['group' => 'Work', 'items' => [
                                    ['value' => 'meetings', 'text' => 'Meetings'],
                                    ['value' => 'reports', 'text' => 'Reports'],
                                    ['value' => 'planning', 'text' => 'Planning'],
                                ]],
                                ['group' => 'Personal', 'items' => [
                                    ['value' => 'health', 'text' => 'Health & Fitness'],
                                    ['value' => 'errands', 'text' => 'Errands'],
                                    ['value' => 'learning', 'text' => 'Learning'],
                                ]],
                            ];
                            ?>
                            <?= $m->dropdown('dropdown-grouped')
                                ->groupedDataSource($groupedCategories)
                                ->placeholder('Select category...')
                                ->name('grouped_category') ?>
                        </div>
                    </div>
                    <div class="m-demo-row" style="margin-top: 12px;">
                        <div class="m-demo-field">
                            <label>Dynamic Options (Ajax):</label>
                            <?= $m->dropdown('dropdown-ajax')
                                ->placeholder('Select...')
                                ->remoteUrl('/manhattan/getDropdownData')
                                ->loaderText('Loading options...') ?>
                        </div>
                        <div class="m-demo-field">
                            <?= $m->button('btn-load-data', 'Reload Data')->icon('fa-sync-alt') ?>
                        </div>
                    </div>
                    <div class="m-demo-output" id="dropdown-output">Select an option to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpDropdown) ?>
                            <?= codeBlock('js', $jsDropdown) ?>
                        </div>
                    </details>
                </div>

                <!-- List -->
                <div id="list" class="m-demo-section">
                    <h2><?= $m->icon('fa-list') ?> List</h2>
                    <?php
                    $items = [
                        ['key' => 'a', 'html' => '<strong>' . $m->icon('fa-clipboard-list') . ' Item A</strong>'],
                        ['key' => 'b', 'html' => '<strong>' . $m->icon('fa-clipboard-list') . ' Item B</strong>'],
                        ['key' => 'c', 'html' => '<strong>' . $m->icon('fa-clipboard-list') . ' Item C</strong>'],
                    ];
                    ?>
                    <?= $m->list('demo-list')->reorderable(true)->emptyMessage('No items')->items($items) ?>
                    <div class="m-demo-row" style="margin-top: 12px;">
                        <?= $m->button('btn-list-add', 'Add Item')->icon('fa-plus') ?>
                        <?= $m->button('btn-list-toggle', 'Toggle Reorder')->icon('fa-arrows-alt') ?>
                    </div>
                    <div class="m-demo-output" id="list-output">Reorder the list to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpList) ?>
                            <?= codeBlock('js', $jsList) ?>
                        </div>
                    </details>
                </div>

                <!-- Chart -->
                <div id="chart" class="m-demo-section">
                    <h2><?= $m->icon('fa-chart-bar') ?> Chart</h2>
                    <?php
                    $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
                    $chartValues = [12, 19, 15, 23, 18];
                    ?>
                    <div class="m-demo-row">
                        <?= $m->chart('demo-bar-chart')->type('bar')->height(200)->labels($chartLabels)->series('Weekly Overview', $chartValues, '#2196F3') ?>
                    </div>
                    <div class="m-demo-row" style="margin-top: 16px;">
                        <?= $m->chart('demo-line-chart')->type('line')->height(200)->labels($chartLabels)->series('Trend Line', $chartValues, '#9C27B0') ?>
                    </div>
                    <h3>With Goal Line</h3>
                    <div class="m-demo-row">
                        <?= $m->chart('demo-goal-chart')->type('bar')->height(200)->labels($chartLabels)->series('Sales', $chartValues, '#4CAF50')->goal(20, '#e74c3c', 'Target') ?>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', '<?= $m->chart(\'myChart\')->type(\'bar\')->height(200)
    ->labels($labels)->series(\'Series\', $values, \'#2196F3\')
    ->goal(20, \'#e74c3c\', \'Target\') ?>') ?>
                            <div class="m-code-block">
                                <div class="m-code-label">Data Format</div>
                                <pre><code>$labels = ['Mon', 'Tue', 'Wed'];
$values = [12, 19, 15];</code></pre>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- CodeArea -->
                <div id="codearea" class="m-demo-section">
                    <h2><?= $m->icon('fa-code') ?> CodeArea</h2>
                    <?php
                    $sampleCode = 'function greet(name) {
    console.log("Hello, " + name);
}';
                    ?>
                    <?= $m->codeArea('demo-code')->language('javascript')->value($sampleCode)->rows(5) ?>
                    <div class="m-demo-row" style="margin-top: 12px;">
                        <?= $m->button('btn-toggle-readonly', 'Toggle Read-Only')->icon('fa-lock') ?>
                        <?= $m->button('btn-get-code', 'Get Code')->icon('fa-copy') ?>
                    </div>
                    <div class="m-demo-output" id="codearea-output">Interact with the code area...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', '<?= $m->codeArea(\'editor\')->language(\'javascript\')->value($code)->rows(10) ?>') ?>
                            <div class="m-code-block">
                                <div class="m-code-label">Features</div>
                                <pre><code>- Syntax highlighting
- Copy to clipboard button
- Line numbers
- Read-only mode
- Auto-resize</code></pre>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Tooltip -->
                <div id="tooltip" class="m-demo-section">
                    <h2><?= $m->icon('fa-comment') ?> Tooltip</h2>
                    <div class="m-demo-pills">
                        <button class="m-demo-pill" data-m-tooltip="Tooltip on top" data-m-tooltip-position="top">
                            <?= $m->icon('fa-arrow-up') ?> Top
                        </button>
                        <button class="m-demo-pill" data-m-tooltip="Tooltip on right" data-m-tooltip-position="right">
                            <?= $m->icon('fa-arrow-right') ?> Right
                        </button>
                        <button class="m-demo-pill" data-m-tooltip="Tooltip on bottom" data-m-tooltip-position="bottom">
                            <?= $m->icon('fa-arrow-down') ?> Bottom
                        </button>
                        <button class="m-demo-pill" data-m-tooltip="Tooltip on left" data-m-tooltip-position="left">
                            <?= $m->icon('fa-arrow-left') ?> Left
                        </button>
                    </div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('html', '<button data-m-tooltip="Tooltip text" data-m-tooltip-position="top">Hover me</button>') ?>
                            <div class="m-code-block">
                                <div class="m-code-label">Features</div>
                                <pre><code>- Auto-converts title attributes
- Keyboard support (Escape)
- 4 positions: top/right/bottom/left
- Viewport-aware positioning
- Opt-out via data-m-tooltip-disabled</code></pre>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Validator -->
                <div id="validator" class="m-demo-section">
                    <h2><?= $m->icon('fa-check-circle') ?> Validator</h2>
                    <p class="m-demo-subtitle">Client-side validation without HTML5 native validation to prevent layout shifts</p>
                    
                    <form id="demo-validation-form" style="max-width: 500px; margin: 20px 0;">
                        <div style="margin-bottom: 20px;">
                            <label for="demo-email" style="display: block; margin-bottom: 6px; font-weight: 600;">Email</label>
                            <?= $m->textbox('demo-email')->placeholder('Enter your email') ?>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label for="demo-password" style="display: block; margin-bottom: 6px; font-weight: 600;">Password</label>
                            <?= $m->textbox('demo-password')->type('password')->placeholder('Min 8 characters') ?>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label for="demo-username" style="display: block; margin-bottom: 6px; font-weight: 600;">Username</label>
                            <?= $m->textbox('demo-username')->placeholder('Only letters, numbers, and underscore') ?>
                        </div>
                        
                        <?= $m->button('demo-submit-btn', 'Submit Form')->type('submit')->primary(true) ?>
                    </form>
                    
                    <?= $m->validator('demo-validation-form')
                        ->field('demo-email', 'Please enter a valid email address', ['required', 'email'])
                        ->field('demo-password', 'Password must be at least 8 characters', ['required', ['minLength' => 8]])
                        ->field('demo-username', 'Username can only contain letters, numbers and underscore', ['required', ['pattern' => '^[a-zA-Z0-9_]+$']])
                        ->onSubmit('alert("Form is valid! In production, this would submit.");') ?>
                    
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', <<<'PHP'
<?= $m->validator('registration-form')
    ->field('email', 'Please enter a valid email', ['required', 'email'])
    ->field('password', 'Min 8 characters', ['required', ['minLength' => 8]])
    ->field('username', 'Letters/numbers only', ['required', ['pattern' => '^[a-zA-Z0-9_]+$']])
    ->onSubmit('handleFormSubmit(event);') ?>
PHP
                            ) ?>
                            <div class="m-code-block">
                                <div class="m-code-label">Features</div>
                                <pre><code>- Replaces HTML5 validation
- No layout shifts
- Inline error messages
- Multiple validation rules
- Real-time validation on blur
- Pattern matching support
- Custom validators
- Shake animation on error</code></pre>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Window -->
                <div id="window" class="m-demo-section">
                    <h2><?= $m->icon('fa-window-maximize') ?> Window</h2>
                    <div class="m-demo-row">
                        <?= $m->button('btn-open-window', 'Open Window')->icon('fa-external-link-alt') ?>
                        <?= $m->button('btn-open-draggable', 'Open Draggable')->icon('fa-arrows-alt') ?>
                    </div>
                    <?php
                    $windowContent = '<p>This is a Manhattan Window.</p>'
                        . '<p class="hint">Try Escape, overlay click, or close button.</p>';
                    ?>
                    <?= $m->window('demoWindow', 'Demo Window')
                        ->content($windowContent)
                        ->modal(true)
                        ->width('560px')
                        ->addButton('Close', 'close', 'secondary')
                        ->addButton('Action', 'do_action', 'primary') ?>

                    <?= $m->window('demoWindowDrag', 'Draggable Window')
                        ->content($windowContent)
                        ->modal(true)
                        ->width('560px')
                        ->addButton('Close', 'close', 'secondary')
                        ->addButton('Action', 'do_action', 'primary') ?>

                    <div class="m-demo-output" id="window-output">Open the window to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpWindow) ?>
                            <?= codeBlock('js', $jsWindow) ?>
                        </div>
                    </details>
                </div>

                <!-- Dialog -->
                <div id="dialog" class="m-demo-section">
                    <h2><?= $m->icon('fa-comment-dots') ?> Dialog</h2>
                    <div class="m-demo-row">
                        <?= $m->button('btn-dialog-alert', 'Alert')->icon('fa-info-circle') ?>
                        <?= $m->button('btn-dialog-confirm', 'Confirm')->icon('fa-question-circle') ?>
                        <?= $m->button('btn-dialog-prompt', 'Prompt')->icon('fa-edit') ?>
                    </div>
                    <div class="m-demo-output" id="dialog-output">Use the dialog buttons to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpDialog) ?>
                            <?= codeBlock('js', $jsDialog) ?>
                        </div>
                    </details>
                </div>

                <!-- Toaster -->
                <div id="toaster" class="m-demo-section">
                    <h2><?= $m->icon('fa-bell') ?> Toaster</h2>
                    <div class="m-demo-row">
                        <?= $m->button('btn-banner-success', 'Banner Success')->icon('fa-check') ?>
                        <?= $m->button('btn-banner-warning', 'Banner Warning')->icon('fa-exclamation-triangle') ?>
                        <?= $m->button('btn-banner-error', 'Banner Error')->icon('fa-exclamation-circle') ?>
                        <?= $m->button('btn-toast-success', 'Toast Success')->icon('fa-check-circle') ?>
                        <?= $m->button('btn-toast-info', 'Toast Info')->icon('fa-info-circle') ?>
                        <?= $m->button('btn-toast-error', 'Toast Error')->icon('fa-exclamation-circle') ?>
                    </div>
                    <div class="m-demo-output" id="toaster-output">Trigger toasts to see output...</div>
                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpToaster) ?>
                            <?= codeBlock('js', $jsToaster) ?>
                        </div>
                    </details>
                </div>
                <!-- DataGrid -->
                <div id="datagrid" class="m-demo-section">
                    <h2><?= $m->icon('fa-table') ?> DataGrid</h2>
                    <p class="m-demo-desc">
                        A full-featured data grid supporting local and remote data binding, pagination, sortable/resizable/reorderable columns, row grouping, row selection, toolbars, and tab compatibility.
                    </p>

                    <h3>Full-Featured Local Grid</h3>
                    <p class="m-demo-desc">Paginated, sortable, resizable &amp; reorderable columns, groupable, row selection, and a custom toolbar. Drag a column header to group, drag-and-drop columns to reorder.</p>
                    <div class="m-demo-row">
                        <?php
                        $dgColumns = [
                            ['field' => 'id',       'title' => '#',        'width' => 60,  'sortable' => true],
                            ['field' => 'task',     'title' => 'Task',      'width' => 280, 'sortable' => true, 'resizable' => true],
                            ['field' => 'owner',    'title' => 'Owner',     'width' => 100, 'sortable' => true, 'groupable' => true],
                            ['field' => 'priority', 'title' => 'Priority',  'width' => 100, 'sortable' => true, 'groupable' => true],
                            ['field' => 'status',   'title' => 'Status',    'width' => 120, 'sortable' => true, 'groupable' => true],
                            ['field' => 'due_date', 'title' => 'Due',       'width' => 110, 'sortable' => true, 'format' => 'date'],
                            ['field' => 'progress', 'title' => 'Progress%', 'width' => 90,  'sortable' => true, 'align' => 'right'],
                        ];
                        echo $m->dataGrid('demoGridLocal')
                            ->columns($dgColumns)
                            ->dataSource($dgSampleData)
                            ->pageable(8, 'local')
                            ->sortable()
                            ->resizable()
                            ->reorderable()
                            ->groupable()
                            ->selectable()
                            ->height('380px')
                            ->toolbar([
                                ['text' => 'Refresh', 'icon' => 'fa-sync',  'click' => 'demoGridRefresh()'],
                                ['text' => 'Clear Group', 'icon' => 'fa-times', 'click' => 'demoGridClearGroup()'],
                            ])
                            ->emptyState('No tasks found');
                        ?>
                    </div>
                    <div class="m-demo-output" id="datagrid-output" style="margin-top:.5rem">Click a row to see selection output...</div>

                    <h3>Grid Inside Tabs</h3>
                    <p class="m-demo-desc">DataGrids initialise correctly when revealed inside a tab panel — column widths recalculate on tab switch.</p>
                    <div class="m-demo-row">
                        <?php
                        $tabGridCols = [
                            ['field' => 'id',       'title' => '#',       'width' => 60,  'sortable' => true],
                            ['field' => 'task',     'title' => 'Task',    'width' => 240, 'sortable' => true, 'resizable' => true],
                            ['field' => 'owner',    'title' => 'Owner',   'width' => 100, 'sortable' => true],
                            ['field' => 'status',   'title' => 'Status',  'width' => 110, 'sortable' => true],
                            ['field' => 'due_date', 'title' => 'Due',     'width' => 110, 'sortable' => true, 'format' => 'date'],
                        ];
                        $gridHtml = (string)$m->dataGrid('tabGrid')
                            ->columns($tabGridCols)
                            ->dataSource(array_slice($dgSampleData, 0, 15))
                            ->pageable(5, 'local')
                            ->sortable()
                            ->height('280px');

                        // Embedded-component grid — one column per component type
                        $compGridCols = [
                            ['field' => 'id',       'title' => '#',        'width' => 50,  'frozen' => true],
                            ['field' => 'task',     'title' => 'Task',     'width' => 220, 'frozen' => true, 'sortable' => true, 'resizable' => true],
                            ['field' => 'status',   'title' => 'Status',   'width' => 120,
                                'component' => [
                                    'type'        => 'badge',
                                    'textBind'    => 'status',
                                    'variantBind' => 'statusColor',
                                ],
                            ],
                            ['field' => 'priority', 'title' => 'Priority', 'width' => 110,
                                'component' => [
                                    'type'    => 'label',
                                    'textBind' => 'priority',
                                    'variantBind' => 'priorityColor',
                                ],
                            ],
                            ['field' => 'progress', 'title' => 'Progress', 'width' => 170, 'sortable' => true,
                                'component' => [
                                    'type'        => 'progressBar',
                                    'valueBind'   => 'progress',
                                    'max'         => 100,
                                    'showPercent' => true,
                                    'variantBind' => 'progressColor',
                                ],
                            ],
                            ['field' => 'done',     'title' => 'Done',     'width' => 60,  'align' => 'center',
                                'component' => [
                                    'type'      => 'checkbox',
                                    'valueBind' => 'done',
                                ],
                            ],
                            ['field' => 'rating',   'title' => 'Rating',   'width' => 115, 'sortable' => true,
                                'component' => [
                                    'type'      => 'rating',
                                    'valueBind' => 'rating',
                                    'max'       => 5,
                                    'halfStars' => true,
                                ],
                            ],
                            ['field' => 'priority', 'title' => '',         'width' => 40,  'align' => 'center',
                                'component' => [
                                    'type'     => 'icon',
                                    'iconBind' => 'priorityIcon',
                                ],
                            ],
                        ];

                        // Enrich the data slice with computed display fields
                        $priorityColors = ['Low' => 'secondary', 'Medium' => 'primary', 'High' => 'warning', 'Critical' => 'danger'];
                        $priorityIcons  = ['Low' => 'fa-arrow-down', 'Medium' => 'fa-minus', 'High' => 'fa-arrow-up', 'Critical' => 'fa-exclamation-circle'];
                        $progressColors = static function(int $p): string {
                            if ($p >= 75) return 'success';
                            if ($p >= 40) return 'primary';
                            if ($p >= 20) return 'warning';
                            return 'danger';
                        };
                        $compData = array_map(function(array $row) use ($priorityColors, $priorityIcons, $progressColors) {
                            $row['priorityColor'] = $priorityColors[$row['priority']] ?? 'secondary';
                            $row['priorityIcon']  = $priorityIcons[$row['priority']]  ?? 'fa-minus';
                            $row['progressColor'] = $progressColors((int)$row['progress']);
                            return $row;
                        }, array_slice($dgSampleData, 0, 20));

                        $compGridHtml = (string)$m->dataGrid('compGrid')
                            ->columns($compGridCols)
                            ->dataSource($compData)
                            ->pageable(8, 'local')
                            ->sortable()
                            ->height('320px');

                        echo $m->tabs('demoGridTabs')
                            ->tab('overview', 'Overview')->icon('fa-info-circle')->content('<p style="padding:1rem">Select the <strong>Tasks</strong> tab to see a DataGrid, or <strong>Components</strong> to see embedded Manhattan components (badges, progress bars, checkboxes, ratings and icons) rendered directly inside cells.</p>')->active()
                            ->tab('tasks', 'Tasks')->icon('fa-clipboard-list')->content($gridHtml)
                            ->tab('components', 'Components')->icon('fa-puzzle-piece')->content($compGridHtml)
                            ->tab('notes', 'Notes')->icon('fa-sticky-note')->content('<p style="padding:1rem">Notes panel (no grid here).</p>');
                        ?>
                    </div>
                    <p class="m-demo-desc" style="margin-top:.5rem;font-size:.82rem;color:#7f8c8d">
                        The <strong>Components</strong> tab uses <code>frozen</code> columns (sticky left scroll), <code>component</code> cell renderers, and data-bound variants — all driven from the row data.
                    </p>

                    <h3>Remote Data Binding</h3>
                    <p class="m-demo-desc">When using <code>remoteUrl()</code> the grid fetches data server-side on every page change / sort / group. The server receives <code>page</code>, <code>pageSize</code>, <code>sortField</code>, <code>sortDir</code>, and <code>groupField</code> params and should return <code>{"data":[...],"total":&lt;int&gt;}</code>.</p>
                    <div class="m-demo-code" style="padding:.75rem 1rem">
                        <pre style="margin:0;font-size:.85rem">// Expected JSON response format:
{
    "data": [ { "id": 1, "task": "...", ... }, ... ],
    "total": 143
}</pre>
                    </div>

                    <details class="m-demo-code">
                        <summary><?= $m->icon('fa-code') ?> Code</summary>
                        <div class="m-code-grid">
                            <?= codeBlock('php', $phpDataGrid) ?>
                            <?= codeBlock('js', $jsDataGrid) ?>
                        </div>
                    </details>
                </div>

            <?php
            $demoCardContent = ob_get_clean();
            echo '<div class="m-demo-card">' . $demoCardContent . '</div>';
            ?>
        </div>

        <?= $m->toaster('demoToaster')->position('top-right') ?>

        <script>
            (function() {
                function setOutput(id, html) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.innerHTML = html;
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    if (!window.m) {
                        return;
                    }

                    window.handlePrimaryClick = function() {
                        setOutput('button-output', '<strong>Primary clicked</strong><br>Timestamp: ' + new Date().toLocaleString());
                        m.ajax('/manhattan/handleButtonClick', { method: 'POST', data: { action: 'primary_click' } });
                    };

                    m.button('btn-client', {
                        events: {
                            click: function() {
                                setOutput('button-output', '<strong>Client-side button clicked</strong><br>Initialized via JS');
                            }
                        }
                    });

                    var btnSecondary = document.getElementById('btn-secondary');
                    if (btnSecondary) {
                        btnSecondary.addEventListener('click', function() {
                            setOutput('button-output', '<strong>Secondary clicked</strong><br>Basic event handler');
                        });
                    }

                    var btnIcon = document.getElementById('btn-icon');
                    if (btnIcon) {
                        btnIcon.addEventListener('click', function() {
                            setOutput('button-output', '<strong>Icon clicked</strong><br>Buttons support icons via PHP and JS');
                        });
                    }

                    m.textbox('demo-email', {
                        onInput: function(data) {
                            setOutput('inputs-output', '<strong>Email:</strong> ' + (data.value || '(empty)'));
                        }
                    });
                    m.textbox('demo-password', {
                        onInput: function(data) {
                            setOutput('inputs-output', '<strong>Password length:</strong> ' + String((data.value || '').length));
                        }
                    });
                    m.textarea('demo-notes', {
                        autoResize: true,
                        onInput: function(data) {
                            setOutput('inputs-output', '<strong>Notes:</strong> ' + (data.value || '(empty)'));
                        }
                    });

                    var toggleNotify = document.getElementById('demo-toggle-notify');
                    if (toggleNotify) {
                        toggleNotify.addEventListener('change', function() {
                            setOutput('toggle-output', '<strong>Notifications:</strong> ' + (this.checked ? 'On' : 'Off'));
                        });
                    }

                    var addressRoot = document.getElementById('demo-address');
                    if (addressRoot && typeof m.address === 'function') {
                        m.address('demo-address', {
                            onChange: function(data) {
                                if (data.source === 'mode') {
                                    setOutput('address-output', '<strong>Mode:</strong> ' + (data.mode === 'nz' ? 'NZ Address' : 'Overseas Address'));
                                }
                                if (data.source === 'select') {
                                    setOutput('address-output', '<strong>Selected:</strong> NZ address suggestion');
                                }
                            }
                        });

                        addressRoot.addEventListener('m:address:select', function(e) {
                            var detail = (e && e.detail) ? e.detail : {};
                            var label = detail.label ? String(detail.label) : '';
                            if (label) {
                                setOutput('address-output', '<strong>Selected:</strong> ' + label);
                            }
                        });
                    }

                    m.datepicker('datepicker-start');
                    m.datepicker('datepicker-due');

                    var dpStart = document.getElementById('datepicker-start');
                    if (dpStart) {
                        dpStart.addEventListener('change', function() {
                            setOutput('datepicker-output', '<strong>Start:</strong> ' + (this.value || '(empty)'));
                        });
                    }
                    var dpDue = document.getElementById('datepicker-due');
                    if (dpDue) {
                        dpDue.addEventListener('change', function() {
                            setOutput('datepicker-output', '<strong>Due:</strong> ' + (this.value || '(empty)'));
                        });
                    }

                    m.dropdown('dropdown-priority', {
                        events: {
                            change: function(data) {
                                setOutput('dropdown-output', '<strong>Priority:</strong> ' + data.text + ' (' + data.value + ')');
                            }
                        }
                    });
                    m.dropdown('dropdown-category', {
                        events: {
                            change: function(data) {
                                setOutput('dropdown-output', '<strong>Category:</strong> ' + data.text + ' (' + data.value + ')');
                            }
                        }
                    });

                    var dropdownAjax = m.dropdown('dropdown-ajax', {
                        events: {
                            change: function(data) {
                                setOutput('dropdown-output', '<strong>Dynamic:</strong> ' + data.text + ' (' + data.value + ')');
                            }
                        }
                    });
                    var btnLoadData = document.getElementById('btn-load-data');
                    if (btnLoadData) {
                        btnLoadData.addEventListener('click', function() {
                            var el = this;
                            el.disabled = true;
                            el.innerHTML = m.icon('fa-spinner fa-spin') + ' Loading...';

                            var req = dropdownAjax && typeof dropdownAjax.reload === 'function'
                                ? dropdownAjax.reload()
                                : null;

                            if (req && typeof req.then === 'function') {
                                req.then(function(data) {
                                    if (data) {
                                        el.innerHTML = m.icon('fa-check') + ' Data Loaded';
                                    } else {
                                        el.innerHTML = m.icon('fa-exclamation-triangle') + ' Error';
                                    }
                                }).finally(function() {
                                    el.disabled = false;
                                    setTimeout(function() {
                                        el.innerHTML = m.icon('fa-sync-alt') + ' Reload Data';
                                    }, 1200);
                                });
                            } else {
                                el.disabled = false;
                                el.innerHTML = m.icon('fa-sync-alt') + ' Reload Data';
                            }
                        });
                    }

                    var demoList = m.list('demo-list');
                    var listCounter = 3;
                    if (demoList) {
                        demoList.element.addEventListener('m:list:reorder', function(e) {
                            var order = (e.detail && e.detail.order) ? e.detail.order : [];
                            setOutput('list-output', '<strong>Order:</strong> ' + order.join(', '));
                        });
                    }

                    var btnListAdd = document.getElementById('btn-list-add');
                    if (btnListAdd && demoList) {
                        btnListAdd.addEventListener('click', function() {
                            listCounter += 1;
                            var key = 'x' + listCounter;
                            demoList.upsertItem(key, '<strong>' + m.icon('fa-clipboard-list') + ' Item ' + key.toUpperCase() + '</strong>');
                            setOutput('list-output', '<strong>Added:</strong> ' + key);
                        });
                    }

                    var btnListToggle = document.getElementById('btn-list-toggle');
                    if (btnListToggle && demoList) {
                        btnListToggle.addEventListener('click', function() {
                            var enabled = !demoList.options.reorderable;
                            demoList.setReorderable(enabled);
                            setOutput('list-output', '<strong>Reorderable:</strong> ' + (enabled ? 'On' : 'Off'));
                        });
                    }

                    // CodeArea demo handlers
                    var demoCodeArea = document.getElementById('demo-code');
                    var btnToggleReadOnly = document.getElementById('btn-toggle-readonly');
                    if (btnToggleReadOnly && demoCodeArea) {
                        btnToggleReadOnly.addEventListener('click', function() {
                            var isReadOnly = demoCodeArea.hasAttribute('readonly');
                            if (isReadOnly) {
                                demoCodeArea.removeAttribute('readonly');
                                this.innerHTML = m.icon('fa-unlock') + ' Set Read-Only';
                                setOutput('codearea-output', '<strong>Mode:</strong> Editable');
                            } else {
                                demoCodeArea.setAttribute('readonly', 'readonly');
                                this.innerHTML = m.icon('fa-lock') + ' Remove Read-Only';
                                setOutput('codearea-output', '<strong>Mode:</strong> Read-Only');
                            }
                        });
                    }

                    var btnGetCode = document.getElementById('btn-get-code');
                    if (btnGetCode && demoCodeArea) {
                        btnGetCode.addEventListener('click', function() {
                            var code = demoCodeArea.value || '';
                            setOutput('codearea-output', '<strong>Code length:</strong> ' + code.length + ' characters');
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText(code).then(function() {
                                    setOutput('codearea-output', '<strong>Copied!</strong> ' + code.length + ' characters to clipboard');
                                });
                            }
                        });
                    }

                    var demoWindow = m.window('demoWindow', { draggable: false });
                    var demoWindowDrag = m.window('demoWindowDrag', { draggable: true });

                    var btnOpenWindow = document.getElementById('btn-open-window');
                    if (btnOpenWindow && demoWindow) {
                        btnOpenWindow.addEventListener('click', function() {
                            demoWindow.open();
                        });
                    }

                    var btnOpenDraggable = document.getElementById('btn-open-draggable');
                    if (btnOpenDraggable && demoWindowDrag) {
                        btnOpenDraggable.addEventListener('click', function() {
                            demoWindowDrag.open();
                        });
                    }

                    ['demoWindow', 'demoWindowDrag'].forEach(function(id) {
                        var el = document.getElementById(id);
                        if (!el) return;

                        el.addEventListener('m:window:open', function() {
                            setOutput('window-output', '<strong>' + id + ' opened</strong>');
                        });
                        el.addEventListener('m:window:close', function() {
                            setOutput('window-output', '<strong>' + id + ' closed</strong>');
                        });
                        el.addEventListener('m:window:action', function(e) {
                            var action = e.detail && e.detail.action ? e.detail.action : '';
                            if (action === 'do_action') {
                                setOutput('window-output', '<strong>' + id + ' action:</strong> do_action');
                            }
                        });
                    });

                    var btnDialogAlert = document.getElementById('btn-dialog-alert');
                    if (btnDialogAlert) {
                        btnDialogAlert.addEventListener('click', function() {
                            m.dialog.alert('This is an alert dialog.', 'Alert', 'fa-info-circle').then(function() {
                                setOutput('dialog-output', '<strong>Alert dismissed</strong>');
                            });
                        });
                    }

                    var btnDialogConfirm = document.getElementById('btn-dialog-confirm');
                    if (btnDialogConfirm) {
                        btnDialogConfirm.addEventListener('click', function() {
                            m.dialog.confirm('Confirm this action?', 'Confirm', 'fa-question-circle').then(function(ok) {
                                setOutput('dialog-output', '<strong>Confirm result:</strong> ' + (ok ? 'OK' : 'Cancel'));
                            });
                        });
                    }

                    var btnDialogPrompt = document.getElementById('btn-dialog-prompt');
                    if (btnDialogPrompt) {
                        btnDialogPrompt.addEventListener('click', function() {
                            m.dialog.prompt('Enter a value:', 'Hello', 'Prompt', 'fa-edit').then(function(val) {
                                setOutput('dialog-output', '<strong>Prompt result:</strong> ' + (val === null ? '(cancelled)' : val));
                            });
                        });
                    }

                    var banner = m.toaster('demoBannerToaster');
                    var toaster = m.toaster('demoToaster');

                    var btnBannerSuccess = document.getElementById('btn-banner-success');
                    if (btnBannerSuccess) {
                        btnBannerSuccess.addEventListener('click', function() {
                            banner.show('Saved successfully (banner).', 'success');
                            setOutput('toaster-output', '<strong>Banner:</strong> success');
                        });
                    }
                    var btnBannerWarning = document.getElementById('btn-banner-warning');
                    if (btnBannerWarning) {
                        btnBannerWarning.addEventListener('click', function() {
                            banner.show('This is a warning (banner).', 'warning');
                            setOutput('toaster-output', '<strong>Banner:</strong> warning');
                        });
                    }
                    var btnBannerError = document.getElementById('btn-banner-error');
                    if (btnBannerError) {
                        btnBannerError.addEventListener('click', function() {
                            banner.show('This is an error example (banner).', 'error');
                            setOutput('toaster-output', '<strong>Banner:</strong> error');
                        });
                    }

                    var btnToastSuccess = document.getElementById('btn-toast-success');
                    if (btnToastSuccess) {
                        btnToastSuccess.addEventListener('click', function() {
                            toaster.show('Saved successfully.', 'success');
                            setOutput('toaster-output', '<strong>Toast:</strong> success');
                        });
                    }
                    var btnToastInfo = document.getElementById('btn-toast-info');
                    if (btnToastInfo) {
                        btnToastInfo.addEventListener('click', function() {
                            toaster.show('Heads up: this is an info toast.', 'info');
                            setOutput('toaster-output', '<strong>Toast:</strong> info');
                        });
                    }
                    var btnToastError = document.getElementById('btn-toast-error');
                    if (btnToastError) {
                        btnToastError.addEventListener('click', function() {
                            toaster.show('Error toast example.', 'error');
                            setOutput('toaster-output', '<strong>Toast:</strong> error');
                        });
                    }

                    // Rating demo hook
                    window.handleDemoRating = function (value, el) {
                        setOutput('rating-output', '<strong>Rating set to:</strong> ' + value + ' / ' + (window.m && m.rating ? m.rating(el).max : 5));
                    };

                    // DataGrid demo hooks
                    window.demoGridRefresh = function() {
                        if (window.m && m.dataGrid) {
                            m.dataGrid('demoGridLocal').refresh();
                            setOutput('datagrid-output', '<strong>Grid refreshed.</strong>');
                        }
                    };
                    window.demoGridClearGroup = function() {
                        if (window.m && m.dataGrid) {
                            m.dataGrid('demoGridLocal').clearGroup();
                            setOutput('datagrid-output', '<strong>Grouping cleared.</strong>');
                        }
                    };

                    var dgEl = document.getElementById('demoGridLocal');
                    if (dgEl) {
                        dgEl.addEventListener('m-datagrid-row-select', function(e) {
                            var row = e.detail && e.detail.row;
                            if (row) {
                                setOutput('datagrid-output', '<strong>Selected:</strong> ' + JSON.stringify(row));
                            }
                        });
                        dgEl.addEventListener('m-datagrid-row-click', function(e) {
                            var row = e.detail && e.detail.row;
                            if (row && !dgEl.classList.contains('m-datagrid-selectable')) {
                                setOutput('datagrid-output', '<strong>Row clicked:</strong> ' + (row.task || row.id));
                            }
                        });
                    }
                });
            })();
        </script>

        <script>
            // Manhattan demo theme toggle (uses session, not user settings)
            (function () {
                <?php if ($mDemoIsDark): ?>
                document.body.classList.add('m-dark');
                <?php endif; ?>

                var btn = document.getElementById('mDemoThemeToggle');
                if (!btn) return;

                btn.addEventListener('click', function () {
                    var xhr = new XMLHttpRequest();
                    var toggleUrl = (typeof manhattanToggleUrl !== 'undefined') ? manhattanToggleUrl : '/manhattan/toggleTheme';
                    xhr.open('POST', toggleUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            // Reload to apply the new theme (CSS link + body class)
                            window.location.reload();
                        }
                    };
                    xhr.send('{}');
                });
            })();
        </script>
