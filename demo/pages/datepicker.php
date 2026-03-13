<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-calendar-alt') ?> DatePicker</h2>
    <p class="m-demo-desc">Calendar-based date selector with min/max constraints, Today button, and keyboard navigation.</p>

    <h3>Basic DatePicker</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Start Date:</label>
            <div class="m-textbox-wrapper">
                <?= $m->datepicker('demo-dp-basic')
                    ->name('start_date')
                    ->placeholder('Select a date…') ?>
            </div>
        </div>
    </div>

    <h3>With Min/Max Range</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Due Date (this year only):</label>
            <div class="m-textbox-wrapper">
                <?= $m->datepicker('demo-dp-range')
                    ->name('due_date')
                    ->min(date('Y') . '-01-01')
                    ->max(date('Y') . '-12-31')
                    ->placeholder('Pick a date in ' . date('Y')) ?>
            </div>
        </div>
    </div>

    <h3>Pre-populated with Today Button</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Event Date:</label>
            <div class="m-textbox-wrapper">
                <?= $m->datepicker('demo-dp-today')
                    ->name('event_date')
                    ->value(date('Y-m-d'))
                    ->showTodayButton() ?>
            </div>
        </div>
    </div>

    <h3>Disabled</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Locked Date:</label>
            <div class="m-textbox-wrapper">
                <?= $m->datepicker('demo-dp-disabled')
                    ->value('2025-01-01')
                    ->disabled() ?>
            </div>
        </div>
    </div>

    <div class="m-demo-output" id="datepicker-output">Pick a date to see output...</div>

    <?= demoCodeTabs(
        '// Basic date picker
<?= $m->datepicker(\'startDate\')
    ->name(\'start_date\')
    ->placeholder(\'Select a date…\') ?>

// With min/max range
<?= $m->datepicker(\'dueDate\')
    ->name(\'due_date\')
    ->min(\'2025-01-01\')
    ->max(\'2025-12-31\') ?>

// Pre-populated with Today button
<?= $m->datepicker(\'eventDate\')
    ->name(\'event_date\')
    ->value(date(\'Y-m-d\'))
    ->showTodayButton() ?>

// Disabled
<?= $m->datepicker(\'locked\')
    ->value(\'2025-01-01\')
    ->disabled() ?>',
        '// Get datepicker instance
var dp = m.datepicker(\'startDate\');

// Get/set value
var date = dp.value();
dp.value(\'2025-06-15\');

// Update constraints
dp.min(\'2025-01-01\');
dp.max(\'2025-12-31\');

// Enable/disable
dp.enable();
dp.disable();'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->datepicker($id)', 'string', 'Create a DatePicker component.'],
    ['->value($value)', '?string', 'Set the initial date value (Y-m-d format).'],
    ['->placeholder($text)', 'string', 'Set placeholder text.'],
    ['->name($name)', 'string', 'Form field name.'],
    ['->min($date)', 'string', 'Earliest selectable date (Y-m-d).'],
    ['->max($date)', 'string', 'Latest selectable date (Y-m-d).'],
    ['->format($format)', 'string', 'Date format string (default: <code>Y-m-d</code>).'],
    ['->disabled($dis)', 'bool', 'Disable the datepicker.'],
    ['->showTodayButton($show)', 'bool', 'Show a "Today" shortcut button in the calendar (default: true).'],
    ['->highlightToday($hl)', 'bool', 'Highlight today\'s date in the calendar (default: true).'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.datepicker(id, opts)', 'string, ?object', 'Get or create DatePicker instance.'],
    ['value(val)', '?string', 'Get or set the current date. Omit parameter to get.'],
    ['min(val)', 'string', 'Set minimum date constraint.'],
    ['max(val)', 'string', 'Set maximum date constraint.'],
    ['enable()', '', 'Enable the datepicker.'],
    ['disable()', '', 'Disable the datepicker.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ['demo-dp-basic', 'demo-dp-range', 'demo-dp-today'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                setOutput('datepicker-output', '<strong>' + id + ':</strong> ' + (this.value || '(empty)'));
            });
        }
    });
});
</script>
