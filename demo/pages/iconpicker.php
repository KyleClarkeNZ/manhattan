<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<?php
$icons = [
    ['value' => 'fa-comments',       'text' => 'Comments'],
    ['value' => 'fa-star',           'text' => 'Featured'],
    ['value' => 'fa-question-circle','text' => 'Question'],
    ['value' => 'fa-lightbulb',      'text' => 'Idea'],
    ['value' => 'fa-bug',            'text' => 'Bug'],
    ['value' => 'fa-book',           'text' => 'Guide'],
    ['value' => 'fa-bell',           'text' => 'Announcement'],
    ['value' => 'fa-tag',            'text' => 'Tagged'],
    ['value' => 'fa-heart',          'text' => 'Favourite'],
    ['value' => 'fa-lock',           'text' => 'Locked'],
    ['value' => 'fa-wrench',         'text' => 'Technical'],
    ['value' => 'fa-info-circle',    'text' => 'Info'],
];
?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-icons') ?> IconPicker</h2>
    <p class="m-demo-desc">
        A dropdown-style picker that shows a configurable grid of Font Awesome icons.
        Selecting an icon updates the trigger label and a hidden form input for submission.
        Default: no icon selected (shows placeholder).
    </p>

    <h3>Basic</h3>
    <p class="m-demo-desc">A picker with a predefined icon list and no initial selection.</p>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Choose an icon:</label>
            <?= $m->iconPicker('iconpicker-basic')
                ->name('icon_basic')
                ->icons($icons)
                ->placeholder('Select an icon…') ?>
        </div>
    </div>
    <div class="m-demo-output" id="iconpicker-basic-output">No icon selected.</div>

<?= demoCodeTabs(
    <<<'PHP'
echo $m->iconPicker('iconpicker-basic')
    ->name('icon_basic')
    ->icons([
        ['value' => 'fa-comments', 'text' => 'Comments'],
        ['value' => 'fa-star',     'text' => 'Featured'],
        // ...
    ])
    ->placeholder('Select an icon…');
PHP,
    <<<'JS'
document.getElementById('iconpicker-basic').addEventListener('m:iconpicker:change', function(e) {
    console.log(e.detail.value, e.detail.label);
});

// Or via the JS API:
var picker = m.iconPicker('iconpicker-basic');
picker.getValue(); // → 'fa-comments' (or '' if none selected)
JS
) ?>
</div>

<div class="m-demo-section">
    <h3>Pre-selected Value</h3>
    <p class="m-demo-desc">Pass a <code>value()</code> to pre-select an icon on render.</p>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Topic icon:</label>
            <?= $m->iconPicker('iconpicker-presel')
                ->name('icon_presel')
                ->icons($icons)
                ->value('fa-star')
                ->placeholder('Select an icon…') ?>
        </div>
    </div>

<?= demoCodeTabs(
    <<<'PHP'
echo $m->iconPicker('iconpicker-presel')
    ->name('icon_presel')
    ->icons($icons)
    ->value('fa-star')         // pre-select "Featured"
    ->placeholder('Select an icon…');
PHP
) ?>
</div>

<div class="m-demo-section">
    <h3>Disabled</h3>
    <p class="m-demo-desc">The picker can be disabled to prevent interaction.</p>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Icon (disabled):</label>
            <?= $m->iconPicker('iconpicker-disabled')
                ->name('icon_disabled')
                ->icons($icons)
                ->value('fa-lock')
                ->disabled() ?>
        </div>
    </div>

<?= demoCodeTabs(
    <<<'PHP'
echo $m->iconPicker('iconpicker-disabled')
    ->name('icon_disabled')
    ->icons($icons)
    ->value('fa-lock')
    ->disabled();
PHP
) ?>
</div>

<div class="m-demo-section">
    <h3>JS API — setValue</h3>
    <p class="m-demo-desc">Use the JS API to set or get the selected icon programmatically.</p>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Controlled picker:</label>
            <?= $m->iconPicker('iconpicker-api')
                ->name('icon_api')
                ->icons($icons)
                ->placeholder('Select an icon…') ?>
        </div>
        <div class="m-demo-field" style="align-self:flex-end">
            <?= $m->button('apiSetBtn', 'Set fa-bug')->secondary()->icon('fa-bug') ?>
            <?= $m->button('apiClearBtn', 'Clear')->icon('fa-times') ?>
        </div>
    </div>
    <div class="m-demo-output" id="iconpicker-api-output">No icon selected.</div>

<?= demoCodeTabs(null,
    <<<'JS'
var picker = m.iconPicker('iconpicker-api');

document.getElementById('apiSetBtn').addEventListener('click', function() {
    picker.setValue('fa-bug');
});

document.getElementById('apiClearBtn').addEventListener('click', function() {
    picker.setValue('');
});

document.getElementById('iconpicker-api').addEventListener('m:iconpicker:change', function(e) {
    document.getElementById('iconpicker-api-output').textContent =
        'Selected: ' + e.detail.value + ' (' + e.detail.label + ')';
});
JS
) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->iconPicker($id)',       'IconPicker', 'Create an IconPicker instance.'],
    ['->icons(array $icons)',     'self',        'Set the icon list. Each entry: <code>[\'value\' => \'fa-star\', \'text\' => \'Label\']</code>.'],
    ['->value(?string $value)',   'self',        'Pre-select an icon by its FA class (e.g. <code>\'fa-star\'</code>). Default: <code>null</code> (none selected).'],
    ['->name(string $name)',      'self',        'Form field name for the hidden input.'],
    ['->placeholder(string $p)',  'self',        'Label shown when no icon is selected. Default: <code>\'Select an icon…\'</code>.'],
    ['->disabled(bool $d)',       'self',        'Disable the picker. Default: <code>false</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.iconPicker(id)',          'object|null', 'Get the initialized IconPicker instance for the given element ID.'],
    ['picker.getValue()',         'string',      'Return the currently selected icon value (FA class), or <code>\'\'</code> if none.'],
    ['picker.setValue(value)',    'void',        'Programmatically select an icon by its FA class. Pass <code>\'\'</code> to clear.'],
    ['picker.open()',             'void',        'Open the icon grid panel.'],
    ['picker.close()',            'void',        'Close the icon grid panel.'],
]) ?>

<?= eventsTable([
    ['m:iconpicker:change', '{ id, value, label }', 'Fired on the container element when the selected icon changes.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Basic — update output
    var basicEl = document.getElementById('iconpicker-basic');
    if (basicEl) {
        basicEl.addEventListener('m:iconpicker:change', function (e) {
            document.getElementById('iconpicker-basic-output').textContent =
                'Selected: ' + e.detail.value + ' (' + e.detail.label + ')';
        });
    }

    // API demo
    var apiPicker = m.iconPicker('iconpicker-api');
    var apiEl     = document.getElementById('iconpicker-api');
    var apiOut    = document.getElementById('iconpicker-api-output');

    if (apiEl) {
        apiEl.addEventListener('m:iconpicker:change', function (e) {
            apiOut.textContent = e.detail.value
                ? 'Selected: ' + e.detail.value + ' (' + e.detail.label + ')'
                : 'No icon selected.';
        });
    }

    var setBtn   = document.getElementById('apiSetBtn');
    var clearBtn = document.getElementById('apiClearBtn');

    if (setBtn && apiPicker) {
        setBtn.addEventListener('click', function () { apiPicker.setValue('fa-bug'); });
    }
    if (clearBtn && apiPicker) {
        clearBtn.addEventListener('click', function () { apiPicker.setValue(''); });
    }
});
</script>
