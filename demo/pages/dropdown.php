<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<?php
$priorities = [
    ['value' => '1', 'text' => 'Low Priority'],
    ['value' => '2', 'text' => 'Medium Priority'],
    ['value' => '3', 'text' => 'High Priority'],
    ['value' => '4', 'text' => 'Critical'],
];

$categories = [
    ['id' => 1, 'name' => 'Work'],
    ['id' => 2, 'name' => 'Personal'],
    ['id' => 3, 'name' => 'Shopping'],
    ['id' => 4, 'name' => 'Health'],
    ['id' => 5, 'name' => 'Other'],
];

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

<div class="m-demo-section">
    <h2><?= $m->icon('fa-chevron-circle-down') ?> Dropdown</h2>
    <p class="m-demo-desc">Custom select dropdown with keyboard navigation, grouped options, remote data loading, and search filtering.</p>

    <h3>Basic</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Priority:</label>
            <?= $m->dropdown('dropdown-priority')->dataSource($priorities)->value('2')->placeholder('Select priority...')->name('priority') ?>
        </div>
        <div class="m-demo-field">
            <label>Category (custom fields):</label>
            <?= $m->dropdown('dropdown-category', ['textField' => 'name', 'valueField' => 'id', 'placeholder' => 'Select category...', 'name' => 'category'])->dataSource($categories) ?>
        </div>
    </div>

    <h3>Grouped Options</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Category:</label>
            <?= $m->dropdown('dropdown-grouped')
                ->groupedDataSource($groupedCategories)
                ->placeholder('Select category...')
                ->name('grouped_category') ?>
        </div>
    </div>

    <h3>Dynamic AJAX Data</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Remote Options:</label>
            <?= $m->dropdown('dropdown-ajax')
                ->placeholder('Select...')
                ->remoteUrl('/getDropdownData')
                ->loaderText('Loading options...') ?>
        </div>
        <div class="m-demo-field">
            <?= $m->button('btn-load-data', 'Reload Data')->icon('fa-sync-alt') ?>
        </div>
    </div>

    <div class="m-demo-output" id="dropdown-output">Select an option to see output...</div>

    <?= demoCodeTabs(
        '// Basic dropdown
<?= $m->dropdown(\'priority\')
    ->dataSource($priorities)
    ->value(\'2\')
    ->placeholder(\'Select...\')
    ->name(\'priority\') ?>

// Custom text/value fields
<?= $m->dropdown(\'category\', [
    \'textField\'  => \'name\',
    \'valueField\' => \'id\',
])->dataSource($items) ?>

// Grouped options
<?= $m->dropdown(\'grouped\')
    ->groupedDataSource([
        [\'group\' => \'Work\', \'items\' => [
            [\'value\' => \'1\', \'text\' => \'Meetings\'],
        ]],
    ])
    ->placeholder(\'Select category...\') ?>

// Remote AJAX data
<?= $m->dropdown(\'remote\')
    ->remoteUrl(\'/api/options\')
    ->loaderText(\'Loading...\') ?>',
        '// Listen for changes (addEventListener on the element)
document.getElementById(\'priority\').addEventListener(\'m:dropdown:change\', function(e) {
    console.log(e.detail.value, e.detail.text);
});

// Legacy callback (options.events.change) — still supported
m.dropdown(\'priority\', {
    events: {
        change: function(data) {
            console.log(data.value, data.text);
        }
    }
});

// Get/set value
var dd = m.dropdown(\'priority\');
dd.value();           // get
dd.value(\'3\');        // set

// Get text
dd.text();

// Reload remote data
dd.reload();

// Enable/disable
dd.enable();
dd.disable();

// Clear selection
dd.clear();'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->dropdown($id)', 'string', 'Create a dropdown component.'],
    ['->dataSource($data)', 'array', 'Set local data: <code>[[\'value\' => ..., \'text\' => ...], ...]</code>.'],
    ['->textField($field)', 'string', 'Property name to display (default: <code>"text"</code>).'],
    ['->valueField($field)', 'string', 'Property name for the value (default: <code>"value"</code>).'],
    ['->groupedDataSource($groups)', 'array', 'Grouped data: <code>[[\'group\' => \'Label\', \'items\' => [...]]]</code>.'],
    ['->placeholder($text)', 'string', 'Placeholder text when nothing is selected.'],
    ['->value($value)', '?string', 'Set the initially selected value.'],
    ['->name($name)', 'string', 'Form field name attribute.'],
    ['->disabled()', '', 'Disable the dropdown.'],
    ['->remoteUrl($url)', 'string', 'Set the AJAX endpoint for remote data loading.'],
    ['->autoLoadRemote($auto)', 'bool', 'Auto-fetch remote data on initialisation (default: <code>true</code>).'],
    ['->useLoader($use)', 'bool', 'Show a loading spinner while fetching (default: <code>true</code>).'],
    ['->loaderText($text)', 'string', 'Custom loading text.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.dropdown(id, options)', 'string, ?object', 'Initialise or get a dropdown instance.'],
    ['value(val?)', '?string', 'Get or set the selected value.'],
    ['text()', '', 'Get the display text of the selected item.'],
    ['dataSource(data?)', '?array', 'Get or set the data source array.'],
    ['reload()', '', 'Re-fetch data from the remote URL. Returns a Promise.'],
    ['enable()', '', 'Enable the dropdown.'],
    ['disable()', '', 'Disable and close the dropdown.'],
    ['clear()', '', 'Clear the current selection.'],
    ['configure(options)', 'object', 'Update configuration at runtime.'],
]) ?>

<?= eventsTable([
    ['m:dropdown:change', '{value, text}', 'Fired on the dropdown element when the selection changes. Listen with <code>el.addEventListener(\'m:dropdown:change\', fn)</code>. The underlying <code>&lt;select&gt;</code> also receives a native <code>change</code> event.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!window.m) return;

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
    if (btnLoadData && dropdownAjax) {
        btnLoadData.addEventListener('click', function() {
            var el = this;
            el.disabled = true;
            if (dropdownAjax && typeof dropdownAjax.reload === 'function') {
                dropdownAjax.reload().then(function() {
                    el.disabled = false;
                    setOutput('dropdown-output', '<strong>Data reloaded</strong>');
                });
            } else {
                el.disabled = false;
            }
        });
    }
});
</script>
