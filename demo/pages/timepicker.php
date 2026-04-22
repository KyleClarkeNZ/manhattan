<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-clock') ?> TimePicker</h2>
    <p class="m-demo-desc">Custom time selection control with scrollable hour and minute columns. Stores values in 24-hour HH:MM format and supports 12-hour AM/PM display mode.</p>

    <h3>Basic TimePicker</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Start Time:</label>
            <?= $m->timepicker('demo-tp-basic')
                ->name('start_time')
                ->placeholder('Select a time\u2026') ?>
        </div>
    </div>

    <h3>With Pre-populated Value &amp; Now Button</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Meeting Time:</label>
            <?= $m->timepicker('demo-tp-value')
                ->name('meeting_time')
                ->value('09:30')
                ->step(15)
                ->showNowButton() ?>
        </div>
    </div>

    <h3>30-Minute Step Intervals</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Appointment Slot:</label>
            <?= $m->timepicker('demo-tp-step30')
                ->name('appointment_time')
                ->placeholder('Select slot\u2026')
                ->step(30)
                ->showNowButton() ?>
        </div>
    </div>

    <h3>12-Hour AM/PM Mode</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Event Time:</label>
            <?= $m->timepicker('demo-tp-12h')
                ->name('event_time')
                ->value('14:00')
                ->use24Hour(false)
                ->step(15) ?>
        </div>
    </div>

    <h3>Disabled</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Locked Time:</label>
            <?= $m->timepicker('demo-tp-disabled')
                ->value('10:00')
                ->disabled() ?>
        </div>
    </div>

    <div class="m-demo-output" id="timepicker-output">Select a time to see the value...</div>

    <?= demoCodeTabs(
        '// Basic time picker
<?= $m->timepicker(\'startTime\')
    ->name(\'start_time\')
    ->placeholder(\'Select a time\u2026\') ?>

// Pre-populated with Now button (15-minute steps)
<?= $m->timepicker(\'meetingTime\')
    ->name(\'meeting_time\')
    ->value(\'09:30\')
    ->step(15)
    ->showNowButton() ?>

// 30-minute slots
<?= $m->timepicker(\'appointmentTime\')
    ->name(\'appointment_time\')
    ->step(30) ?>

// 12-hour AM/PM display
<?= $m->timepicker(\'eventTime\')
    ->name(\'event_time\')
    ->value(\'14:00\')
    ->use24Hour(false) ?>

// Disabled
<?= $m->timepicker(\'locked\')
    ->value(\'10:00\')
    ->disabled() ?>',
        'var tp = m.timepicker(\'startTime\');

// Getter — returns \'HH:MM\' or \'\'
var time = tp.value();

// Setter
tp.value(\'14:30\');

// Clear
tp.clear();

// Enable / disable
tp.enable();
tp.disable();

// Listen for changes
document.getElementById(\'startTime\').addEventListener(\'m:timepicker:change\', function(e) {
    console.log(\'New time:\', e.detail.value);
});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->timepicker($id)', 'string', 'Create a TimePicker component.'],
    ['->value($value)', '?string', 'Set the initial value in 24-hour HH:MM format (e.g. <code>\'14:30\'</code>). Pass <code>null</code> or empty string to clear.'],
    ['->name($name)', 'string', 'Form field name attribute.'],
    ['->placeholder($text)', 'string', 'Placeholder text shown when no time is selected. Default: <code>\'Select time\u2026\'</code>.'],
    ['->step($minutes)', 'int', 'Minute step interval — must be a divisor of 60 (e.g. 5, 10, 15, 30). Default: <code>15</code>.'],
    ['->showNowButton($show)', 'bool', 'Show a "Now" footer button that snaps to the current time. Default: <code>false</code>.'],
    ['->use24Hour($use)', 'bool', 'Display times in 24-hour format. Default: <code>true</code>. Pass <code>false</code> for 12-hour AM/PM mode.'],
    ['->disabled($dis)', 'bool', 'Disable the timepicker. Default: <code>false</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.timepicker(id)', 'string', 'Get (or create) TimePicker instance. Returns cached instance on subsequent calls.'],
    ['value()', '', 'Get the current value as a <code>\'HH:MM\'</code> string, or <code>\'\'</code> if empty.'],
    ['value(val)', 'string', 'Set the value. Pass a 24-hour <code>\'HH:MM\'</code> string, or <code>\'\'</code> to clear.'],
    ['clear()', '', 'Clear the selected time.'],
    ['enable()', '', 'Enable the timepicker.'],
    ['disable()', '', 'Disable the timepicker and close the panel.'],
]) ?>

<?= eventsTable([
    ['m:timepicker:change', '{ value: string }', 'Fired on the hidden input element whenever the selected time changes (including when cleared). <code>detail.value</code> is the new HH:MM string or empty string.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ['demo-tp-basic', 'demo-tp-value', 'demo-tp-step30', 'demo-tp-12h'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('m:timepicker:change', function(e) {
                document.getElementById('timepicker-output').innerHTML =
                    '<strong>' + id + ':</strong> ' + (e.detail.value || '(cleared)');
            });
        }
    });
});
</script>
