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

// Searchable demo — a long list of countries / regions
$countries = [
    ['value' => 'AU', 'text' => 'Australia'],
    ['value' => 'AT', 'text' => 'Austria'],
    ['value' => 'BE', 'text' => 'Belgium'],
    ['value' => 'BR', 'text' => 'Brazil'],
    ['value' => 'CA', 'text' => 'Canada'],
    ['value' => 'CN', 'text' => 'China'],
    ['value' => 'DK', 'text' => 'Denmark'],
    ['value' => 'FI', 'text' => 'Finland'],
    ['value' => 'FR', 'text' => 'France'],
    ['value' => 'DE', 'text' => 'Germany'],
    ['value' => 'GR', 'text' => 'Greece'],
    ['value' => 'IN', 'text' => 'India'],
    ['value' => 'IE', 'text' => 'Ireland'],
    ['value' => 'IL', 'text' => 'Israel'],
    ['value' => 'IT', 'text' => 'Italy'],
    ['value' => 'JP', 'text' => 'Japan'],
    ['value' => 'MX', 'text' => 'Mexico'],
    ['value' => 'NL', 'text' => 'Netherlands'],
    ['value' => 'NZ', 'text' => 'New Zealand'],
    ['value' => 'NO', 'text' => 'Norway'],
    ['value' => 'PL', 'text' => 'Poland'],
    ['value' => 'PT', 'text' => 'Portugal'],
    ['value' => 'SG', 'text' => 'Singapore'],
    ['value' => 'ZA', 'text' => 'South Africa'],
    ['value' => 'ES', 'text' => 'Spain'],
    ['value' => 'SE', 'text' => 'Sweden'],
    ['value' => 'CH', 'text' => 'Switzerland'],
    ['value' => 'GB', 'text' => 'United Kingdom'],
    ['value' => 'US', 'text' => 'United States'],
];

$groupedRegions = [
    ['group' => 'Oceania', 'items' => [
        ['value' => 'auckland',    'text' => 'Auckland'],
        ['value' => 'wellington',  'text' => 'Wellington'],
        ['value' => 'christchurch','text' => 'Christchurch'],
        ['value' => 'hamilton',    'text' => 'Hamilton'],
        ['value' => 'tauranga',    'text' => 'Tauranga'],
        ['value' => 'dunedin',     'text' => 'Dunedin'],
    ]],
    ['group' => 'North America', 'items' => [
        ['value' => 'new-york',    'text' => 'New York'],
        ['value' => 'los-angeles', 'text' => 'Los Angeles'],
        ['value' => 'chicago',     'text' => 'Chicago'],
        ['value' => 'toronto',     'text' => 'Toronto'],
        ['value' => 'vancouver',   'text' => 'Vancouver'],
    ]],
    ['group' => 'Europe', 'items' => [
        ['value' => 'london',  'text' => 'London'],
        ['value' => 'paris',   'text' => 'Paris'],
        ['value' => 'berlin',  'text' => 'Berlin'],
        ['value' => 'madrid',  'text' => 'Madrid'],
        ['value' => 'amsterdam','text' => 'Amsterdam'],
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

    <h3>Searchable</h3>
    <p class="m-demo-desc">Add <code>->searchable()</code> to any dropdown with a long list. A live-filter input pins above the options; typing narrows results instantly. Grouped options are also supported — group headings hide automatically when all items in the group are filtered out.</p>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Country (flat, searchable):</label>
            <?= $m->dropdown('dropdown-search-flat')
                ->dataSource($countries)
                ->placeholder('Select country...')
                ->searchable()
                ->name('country') ?>
        </div>
        <div class="m-demo-field">
            <label>City (grouped, searchable):</label>
            <?= $m->dropdown('dropdown-search-grouped')
                ->groupedDataSource($groupedRegions)
                ->placeholder('Select city...')
                ->searchable()
                ->searchPlaceholder('Search cities...')
                ->name('city') ?>
        </div>
    </div>

    <div class="m-demo-output" id="dropdown-search-output">Select a country or city...</div>

    <?= demoCodeTabs(
        '// Searchable flat list
<?= $m->dropdown(\'country\')
    ->dataSource($countries)
    ->placeholder(\'Select country...\')
    ->searchable() ?>

// Searchable with grouped options + custom placeholder
<?= $m->dropdown(\'city\')
    ->groupedDataSource($groupedRegions)
    ->placeholder(\'Select city...\')
    ->searchable()
    ->searchPlaceholder(\'Search cities...\') ?>',
        '// No extra JS needed — searchable is fully automatic.
// The change event works identically to a standard dropdown.
document.getElementById(\'country\').addEventListener(\'m:dropdown:change\', function(e) {
    console.log(e.detail.value, e.detail.text);
});

// You can also enable searchable at runtime via configure():
m.dropdown(\'myDd\').configure({ searchable: true });'
    ) ?>

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

<div class="m-demo-section">
    <h3>Select by Ordinal or Value</h3>
    <p class="m-demo-desc">
        <code>select()</code> pre-selects an option by 0-based ordinal position (flat data source)
        or by value string. Useful as a clean default when the first option should be active.
        Note: ordinal selection applies to the flat <code>dataSource</code> only, not grouped data.
    </p>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>First option (ordinal 0):</label>
            <?= $m->dropdown('dropdown-sel-ordinal')
                ->name('priority_ordinal')
                ->dataSource($priorities)
                ->select(0) ?>
        </div>
        <div class="m-demo-field">
            <label>By value string:</label>
            <?= $m->dropdown('dropdown-sel-value')
                ->name('priority_value')
                ->dataSource($priorities)
                ->select('3') ?>
        </div>
    </div>

<?= demoCodeTabs(
    <<<'PHP'
// Select first option — no hardcoded value needed
echo $m->dropdown('priority')->dataSource($options)->select(0);

// Select by value string (equivalent to ->value())
echo $m->dropdown('priority')->dataSource($options)->select('medium');

// Nullable $old fallback — 0 selects first when no prior value:
echo $m->dropdown('priority')->dataSource($options)->select($old['priority'] ?? 0);
PHP
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
    ['->select($posOrVal)', 'int|string', 'Pre-select by 0-based ordinal (flat data source) or value string. Default: <code>null</code>.'],
    ['->name($name)', 'string', 'Form field name attribute.'],
    ['->disabled()', '', 'Disable the dropdown.'],
    ['->remoteUrl($url)', 'string', 'Set the AJAX endpoint for remote data loading.'],
    ['->autoLoadRemote($auto)', 'bool', 'Auto-fetch remote data on initialisation (default: <code>true</code>).'],
    ['->useLoader($use)', 'bool', 'Show a loading spinner while fetching (default: <code>true</code>).'],
    ['->loaderText($text)', 'string', 'Custom loading text.'],
    ['->searchable($enabled)', 'bool', 'Enable a live-filter search input inside the dropdown list. Default: <code>false</code>.'],
    ['->searchPlaceholder($text)', 'string', 'Placeholder for the search input. Only applies when <code>->searchable()</code> is set. Default: <code>"Search..."</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.dropdown(id, options)', 'string, ?object', 'Initialise or get a dropdown instance.'],
    ['value(val?)', '?string', 'Get or set the selected value.'],
    ['text()', '', 'Get the display text of the selected item.'],
    ['dataSource(data?)', '?array', 'Get or set the flat data source array. Clears any grouped data source.'],
    ['groupedDataSource(data?)', '?array', 'Get or set the grouped data source: <code>[{group, items}, ...]</code>.'],
    ['reload()', '', 'Re-fetch data from the remote URL. Returns a Promise.'],
    ['enable()', '', 'Enable the dropdown.'],
    ['disable()', '', 'Disable and close the dropdown.'],
    ['clear()', '', 'Clear the current selection.'],
    ['configure(options)', 'object', 'Update configuration at runtime. Supports all init options, including <code>searchable</code>.'],
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
    m.dropdown('dropdown-grouped', {
        events: {
            change: function(data) {
                setOutput('dropdown-output', '<strong>Grouped:</strong> ' + data.text + ' (' + data.value + ')');
            }
        }
    });

    // Searchable demos
    var flatEl = document.getElementById('dropdown-search-flat');
    if (flatEl) {
        flatEl.addEventListener('m:dropdown:change', function(e) {
            setOutput('dropdown-search-output', '<strong>Country:</strong> ' + e.detail.text + ' (' + e.detail.value + ')');
        });
    }
    var groupedEl = document.getElementById('dropdown-search-grouped');
    if (groupedEl) {
        groupedEl.addEventListener('m:dropdown:change', function(e) {
            setOutput('dropdown-search-output', '<strong>City:</strong> ' + e.detail.text + ' (' + e.detail.value + ')');
        });
    }

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
