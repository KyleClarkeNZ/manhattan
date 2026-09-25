<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-calendar-week') ?> DateRangePicker</h2>
    <p class="m-demo-desc">Two-month range selector with presets and keyboard support. Start and end are submitted as two hidden inputs.</p>

    <h3>Basic Range Picker</h3>
    <p class="m-demo-desc">Click a start date, then an end date (the same day twice selects one day).</p>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:420px">
            <?= $m->daterangepicker('demo-drp-basic')
                ->startName('start_date')
                ->endName('end_date')
                ->placeholder('Select date range…') ?>
        </div>
    </div>
    <div class="m-demo-output" id="drp-basic-output">Pick a range to see output…</div>

    <h3>With Preset Shortcuts</h3>
    <p class="m-demo-desc">Enables the preset panel with common shortcuts: Today, Last 7 days, This month, etc.</p>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:420px">
            <?= $m->daterangepicker('demo-drp-presets')
                ->startName('from')
                ->endName('to')
                ->showPresets()
                ->startPlaceholder('From')
                ->endPlaceholder('To') ?>
        </div>
    </div>
    <div class="m-demo-output" id="drp-presets-output">Pick a range to see output…</div>

    <h3>Auto-Apply</h3>
    <p class="m-demo-desc">The selection closes immediately when the second date is clicked — no Apply button needed.</p>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:420px">
            <?= $m->daterangepicker('demo-drp-auto')
                ->startName('check_in')
                ->endName('check_out')
                ->autoApply()
                ->showPresets()
                ->startPlaceholder('Check-in')
                ->endPlaceholder('Check-out') ?>
        </div>
    </div>
    <div class="m-demo-output" id="drp-auto-output">Pick a range to see output…</div>

    <h3>Pre-populated with Min/Max Constraints</h3>
    <p class="m-demo-desc">Days outside the allowed range are greyed out and unselectable.</p>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:420px">
            <?= $m->daterangepicker('demo-drp-constrained')
                ->startName('period_start')
                ->endName('period_end')
                ->startValue(date('Y-m-01'))
                ->endValue(date('Y-m-d'))
                ->min(date('Y') . '-01-01')
                ->max(date('Y') . '-12-31') ?>
        </div>
    </div>

    <h3>Single Month</h3>
    <p class="m-demo-desc">Show a single calendar month instead of two side-by-side. Useful in narrow layouts.</p>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:280px">
            <?= $m->daterangepicker('demo-drp-single')
                ->startName('single_start')
                ->endName('single_end')
                ->singleMonth()
                ->autoApply()
                ->placeholder('Pick a range…') ?>
        </div>
    </div>

    <h3>Custom Presets</h3>
    <p class="m-demo-desc">Supply your own preset labels and date pairs — useful for domain-specific ranges like financial quarters.</p>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:420px">
            <?= $m->daterangepicker('demo-drp-custom-presets')
                ->startName('q_start')
                ->endName('q_end')
                ->presets([
                    ['label' => 'Q1 ' . date('Y'), 'start' => date('Y') . '-01-01', 'end' => date('Y') . '-03-31'],
                    ['label' => 'Q2 ' . date('Y'), 'start' => date('Y') . '-04-01', 'end' => date('Y') . '-06-30'],
                    ['label' => 'Q3 ' . date('Y'), 'start' => date('Y') . '-07-01', 'end' => date('Y') . '-09-30'],
                    ['label' => 'Q4 ' . date('Y'), 'start' => date('Y') . '-10-01', 'end' => date('Y') . '-12-31'],
                ])
                ->placeholder('Select a quarter…') ?>
        </div>
    </div>

    <h3>Week Starting Monday + With Label</h3>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:420px">
            <?= $m->daterangepicker('demo-drp-monday')
                ->label('Campaign Period')
                ->labelRequired()
                ->labelIcon('fa-calendar-week')
                ->startName('camp_start')
                ->endName('camp_end')
                ->weekStartsMonday()
                ->showPresets() ?>
        </div>
    </div>

    <h3>Disabled</h3>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:420px">
            <?= $m->daterangepicker('demo-drp-disabled')
                ->startValue('2025-03-01')
                ->endValue('2025-03-31')
                ->disabled() ?>
        </div>
    </div>

    <?= demoCodeTabs(
        '<?= $m->daterangepicker(\'reportDates\')
    ->startName(\'from\')
    ->endName(\'to\')
    ->startValue(date(\'Y-m-01\'))
    ->endValue(date(\'Y-m-d\'))
    ->min(date(\'Y\') . \'-01-01\')
    ->showPresets()        // or ->presets([[\'label\' => \'Q1\', \'start\' => \'2025-01-01\', \'end\' => \'2025-03-31\']])
    ->autoApply()          // close on second click
    ->singleMonth()
    ->weekStartsMonday() ?>',
        'var drp = m.daterangepicker(\'reportDates\');
drp.value();                                        // { start, end }
drp.value({ start: \'2025-06-01\', end: \'2025-06-30\' });
drp.start(\'2025-07-01\');                           // or end(); both also get
drp.min(\'2025-01-01\');                             // max()
drp.clear();                                        // also open(), close(), enable(), disable()

document.getElementById(\'reportDates\').addEventListener(\'m:daterangepicker:change\', function (e) {
    console.log(e.detail.start, e.detail.end);
});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->daterangepicker($id)', 'string', 'Create a DateRangePicker component.'],
    ['->startValue($date)', '?string', 'Set the initial start date (Y-m-d by default).'],
    ['->endValue($date)',   '?string', 'Set the initial end date.'],
    ['->values($start, $end)', '?string, ?string', 'Set both start and end values in one call.'],
    ['->startName($name)', 'string', 'Form field name for the start hidden input.'],
    ['->endName($name)',   'string', 'Form field name for the end hidden input.'],
    ['->placeholder($text)', 'string', 'Combined placeholder shown when no range is selected.'],
    ['->startPlaceholder($text)', 'string', 'Placeholder for the start segment. Default: <code>Start date</code>.'],
    ['->endPlaceholder($text)',   'string', 'Placeholder for the end segment. Default: <code>End date</code>.'],
    ['->min($date)', 'string', 'Earliest selectable date (Y-m-d).'],
    ['->max($date)', 'string', 'Latest selectable date (Y-m-d).'],
    ['->format($fmt)', 'string', 'PHP date() format for values. Default: <code>Y-m-d</code>.'],
    ['->disabled($dis)', 'bool', 'Disable the picker. Default: <code>false</code>.'],
    ['->highlightToday($hl)', 'bool', 'Highlight today in the calendar. Default: <code>true</code>.'],
    ['->showPresets($show)', 'bool', 'Show the built-in preset shortcuts sidebar. Default: <code>false</code>.'],
    ['->presets($arr)', 'array', 'Custom preset array (each entry: <code>[label, start, end]</code>). Automatically enables the preset panel.'],
    ['->weekStartsMonday($mon)', 'bool', 'Start week columns on Monday. Default: <code>false</code> (Sunday).'],
    ['->singleMonth($single)', 'bool', 'Show one calendar instead of two side-by-side. Default: <code>false</code>.'],
    ['->autoApply($auto)', 'bool', 'Close and apply immediately when the end date is clicked, skipping the Apply button. Default: <code>false</code>.'],
    ['->required($req)', 'bool', 'Mark the start input as required. Default: <code>false</code>.'],
    ['->label($text)', 'string', 'Render a label above the trigger. Inherited from Component.'],
    ['->labelRequired()', '', 'Mark the label with a required asterisk.'],
    ['->labelHint($text)', 'string', 'Add hint text to the label.'],
    ['->labelIcon($fa)', 'string', 'Prepend a Font Awesome icon to the label.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.daterangepicker(id)', 'string', 'Get DateRangePicker instance (auto-initialized on DOMContentLoaded).'],
    ['value()', '', 'Get range as <code>{ start, end }</code> strings.'],
    ['value({ start, end })', 'object', 'Set both dates programmatically.'],
    ['start()', '', 'Get start date string.'],
    ['start(val)', 'string', 'Set start date (keeps existing end).'],
    ['end()', '', 'Get end date string.'],
    ['end(val)', 'string', 'Set end date (keeps existing start).'],
    ['clear()', '', 'Clear both dates and emit <code>m:daterangepicker:clear</code>.'],
    ['min(val)', 'string', 'Update minimum date constraint.'],
    ['max(val)', 'string', 'Update maximum date constraint.'],
    ['open()', '', 'Open the picker panel programmatically.'],
    ['close()', '', 'Close the picker panel.'],
    ['enable()', '', 'Enable the picker.'],
    ['disable()', '', 'Disable the picker.'],
]) ?>

<?= eventsTable([
    ['m:daterangepicker:change', '{ start: string, end: string }', 'Fired when the Apply button is clicked (or auto-apply fires) with a complete range.'],
    ['m:daterangepicker:start',  '{ start: string }',             'Fired immediately when the start date is clicked (end not yet selected).'],
    ['m:daterangepicker:clear',  '{}',                            'Fired when the Clear button is clicked.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function fmtRange(s, e) {
        if (!s && !e) return 'Cleared';
        if (s && !e)  return 'Start: ' + s + ' — end not yet selected';
        if (s === e)  return 'Single day: ' + s;
        return 'Start: ' + s + '  →  End: ' + e;
    }

    // Basic
    document.getElementById('demo-drp-basic').addEventListener('m:daterangepicker:change', function(e) {
        document.getElementById('drp-basic-output').textContent = fmtRange(e.detail.start, e.detail.end);
    });
    document.getElementById('demo-drp-basic').addEventListener('m:daterangepicker:clear', function() {
        document.getElementById('drp-basic-output').textContent = 'Cleared';
    });

    // Presets
    document.getElementById('demo-drp-presets').addEventListener('m:daterangepicker:change', function(e) {
        document.getElementById('drp-presets-output').textContent = fmtRange(e.detail.start, e.detail.end);
    });
    document.getElementById('demo-drp-presets').addEventListener('m:daterangepicker:clear', function() {
        document.getElementById('drp-presets-output').textContent = 'Cleared';
    });

    // Auto
    document.getElementById('demo-drp-auto').addEventListener('m:daterangepicker:change', function(e) {
        document.getElementById('drp-auto-output').textContent = fmtRange(e.detail.start, e.detail.end);
    });
    document.getElementById('demo-drp-auto').addEventListener('m:daterangepicker:clear', function() {
        document.getElementById('drp-auto-output').textContent = 'Cleared';
    });
});
</script>
