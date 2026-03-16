<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-table-columns') ?> ButtonGroup</h2>
    <p class="m-demo-desc">
        A compact set of mutually exclusive icon-only toggle buttons. Exactly one button is active
        at a time (radio behaviour). Ideal for toolbars, sort controls, and view-mode switchers.
        Each button shows a tooltip on hover. Fires <code>m:buttongroup:change</code> when the
        selection changes.
    </p>

    <!-- ── Sort Controls ──────────────────────────────────────────── -->
    <h3>Sort Controls</h3>
    <p class="m-demo-desc">Date and alphabetical sort — one active at a time.</p>
    <div class="m-demo-row">
        <?= $m->buttonGroup('demo-sort')
            ->buttons([
                ['value' => 'date-desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Newest first',  'active' => true],
                ['value' => 'date-asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Oldest first'],
                ['value' => 'az',        'icon' => 'fa-arrow-down-a-z',         'tooltip' => 'A → Z'],
                ['value' => 'za',        'icon' => 'fa-arrow-up-z-a',           'tooltip' => 'Z → A'],
            ]) ?>
    </div>
    <div class="m-demo-output" id="sort-output">Click a sort button…</div>

    <!-- ── View Mode ──────────────────────────────────────────────── -->
    <h3>View Mode</h3>
    <p class="m-demo-desc">Switch between list, grouped, and grid layouts.</p>
    <div class="m-demo-row">
        <?= $m->buttonGroup('demo-view')
            ->buttons([
                ['value' => 'list',  'icon' => 'fa-list-ul',     'tooltip' => 'List view',    'active' => true],
                ['value' => 'group', 'icon' => 'fa-layer-group', 'tooltip' => 'Group by type'],
                ['value' => 'grid',  'icon' => 'fa-grip',        'tooltip' => 'Grid view'],
            ]) ?>
    </div>
    <div class="m-demo-output" id="view-output">Click a view button…</div>

    <!-- ── Combined Toolbar ───────────────────────────────────────── -->
    <h3>Combined Toolbar</h3>
    <p class="m-demo-desc">Multiple groups with a visual separator, wrapped in an <code>.m-toolbar</code> container.</p>
    <div class="m-demo-row">
        <div class="m-toolbar">
            <?= $m->buttonGroup('demo-toolbar-sort')
                ->buttons([
                    ['value' => 'date-desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Newest first', 'active' => true],
                    ['value' => 'date-asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Oldest first'],
                    ['value' => 'az',        'icon' => 'fa-arrow-down-a-z',         'tooltip' => 'A → Z'],
                    ['value' => 'za',        'icon' => 'fa-arrow-up-z-a',           'tooltip' => 'Z → A'],
                ]) ?>
            <div class="m-button-group-sep"></div>
            <?= $m->buttonGroup('demo-toolbar-view')
                ->buttons([
                    ['value' => 'list',  'icon' => 'fa-list-ul',     'tooltip' => 'List view',     'active' => true],
                    ['value' => 'group', 'icon' => 'fa-layer-group', 'tooltip' => 'Grouped view'],
                    ['value' => 'grid',  'icon' => 'fa-grip',        'tooltip' => 'Grid view'],
                ]) ?>
        </div>
    </div>
    <div class="m-demo-output" id="toolbar-output">Interact with the toolbar…</div>

    <!-- ── Programmatic ───────────────────────────────────────────── -->
    <h3>Programmatic Control</h3>
    <div class="m-demo-row">
        <?= $m->buttonGroup('demo-prog')
            ->buttons([
                ['value' => 'a', 'icon' => 'fa-1', 'tooltip' => 'One'],
                ['value' => 'b', 'icon' => 'fa-2', 'tooltip' => 'Two',   'active' => true],
                ['value' => 'c', 'icon' => 'fa-3', 'tooltip' => 'Three'],
            ]) ?>
        &nbsp;
        <?= $m->button('btnProgA', 'Set A')->attr('onclick', "m.buttonGroup('demo-prog').setActive('a')") ?>
        <?= $m->button('btnProgB', 'Set B')->attr('onclick', "m.buttonGroup('demo-prog').setActive('b')") ?>
        <?= $m->button('btnProgC', 'Set C')->attr('onclick', "m.buttonGroup('demo-prog').setActive('c')") ?>
    </div>
    <div class="m-demo-output" id="prog-output">Active value will appear here…</div>

    <?= demoCodeTabs(
        '// Sort toolbar
echo $m->buttonGroup(\'sortGroup\')
    ->buttons([
        [\'value\' => \'date-desc\', \'icon\' => \'fa-arrow-down-wide-short\', \'tooltip\' => \'Newest first\', \'active\' => true],
        [\'value\' => \'date-asc\',  \'icon\' => \'fa-arrow-up-short-wide\',   \'tooltip\' => \'Oldest first\'],
        [\'value\' => \'az\',        \'icon\' => \'fa-arrow-down-a-z\',         \'tooltip\' => \'A → Z\'],
        [\'value\' => \'za\',        \'icon\' => \'fa-arrow-up-z-a\',           \'tooltip\' => \'Z → A\'],
    ]);

// View toolbar
echo $m->buttonGroup(\'viewGroup\')
    ->buttons([
        [\'value\' => \'list\',  \'icon\' => \'fa-list-ul\',     \'tooltip\' => \'List view\',  \'active\' => true],
        [\'value\' => \'group\', \'icon\' => \'fa-layer-group\', \'tooltip\' => \'Group by type\'],
    ]);

// Combined toolbar with separator
echo \'<div class="m-toolbar">\';
echo $m->buttonGroup(\'sortGroup\')->buttons([...]);
echo \'<div class="m-button-group-sep"></div>\';
echo $m->buttonGroup(\'viewGroup\')->buttons([...]);
echo \'</div>\';',
        '// Get current active value
var sort = m.buttonGroup(\'sortGroup\');
console.log(sort.getActive()); // "date-desc"

// Set programmatically
sort.setActive(\'az\');

// Listen for changes
document.getElementById(\'sortGroup\')
    .addEventListener(\'m:buttongroup:change\', function(e) {
        console.log(\'Sort changed to:\', e.detail.value);
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->buttonGroup($id)', '',                          'Create a ButtonGroup instance.'],
    ['->buttons($arr)',      'array',                     'Define the buttons. Each item: <code>{value, icon, tooltip?, active?}</code>.'],
    ['->addClass($class)',   'string',                    'Add extra CSS classes to the group element.'],
    ['->attr($name, $val)',  'string, string',            'Set an arbitrary HTML attribute on the group element.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.buttonGroup(id)',  'string|HTMLElement', 'Get (or initialise) a ButtonGroup instance.'],
    ['.getActive()',       '',                   'Return the currently active value, or <code>null</code> if none.'],
    ['.setActive(value)',  'string',             'Programmatically activate a button by value; fires the change event if the value differs.'],
]) ?>

<?= eventsTable([
    ['m:buttongroup:change', '{ value: string }', 'Fired on the group element whenever the active button changes.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    ['demo-sort', 'demo-view'].forEach(function (groupId) {
        var el = document.getElementById(groupId);
        if (!el) { return; }
        var outputId = groupId === 'demo-sort' ? 'sort-output' : 'view-output';
        el.addEventListener('m:buttongroup:change', function (e) {
            document.getElementById(outputId).textContent = 'Active: ' + e.detail.value;
        });
    });

    ['demo-toolbar-sort', 'demo-toolbar-view'].forEach(function (groupId) {
        var el = document.getElementById(groupId);
        if (!el) { return; }
        el.addEventListener('m:buttongroup:change', function (e) {
            var grp = groupId.replace('demo-toolbar-', '');
            document.getElementById('toolbar-output').textContent = grp + ' → ' + e.detail.value;
        });
    });

    var progEl = document.getElementById('demo-prog');
    if (progEl) {
        progEl.addEventListener('m:buttongroup:change', function (e) {
            document.getElementById('prog-output').textContent = 'Active: ' + e.detail.value;
        });
    }
});
</script>
