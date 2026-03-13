<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-toggle-on') ?> Toggle Switch</h2>
    <p class="m-demo-desc">On/off toggle with optional labels, state text, and keyboard accessibility.</p>

    <h3>Basic Toggle</h3>
    <div class="m-demo-row">
        <?= $m->toggleSwitch('demo-toggle-basic')->name('notifications')->label('Enable notifications') ?>
    </div>

    <h3>Pre-checked</h3>
    <div class="m-demo-row">
        <?= $m->toggleSwitch('demo-toggle-checked')->name('darkMode')->label('Dark mode')->checked() ?>
    </div>

    <h3>With On/Off Labels</h3>
    <div class="m-demo-row">
        <?= $m->toggleSwitch('demo-toggle-labels')->name('status')->label('Status')->onLabel('Active')->offLabel('Inactive') ?>
    </div>

    <h3>Disabled</h3>
    <div class="m-demo-row">
        <?= $m->toggleSwitch('demo-toggle-disabled')->label('Locked setting')->checked()->disabled() ?>
    </div>

    <div class="m-demo-output" id="toggle-output">Toggle a switch to see output...</div>

    <?= demoCodeTabs(
        '// Basic toggle
<?= $m->toggleSwitch(\'notifications\')
    ->name(\'notifications\')
    ->label(\'Enable notifications\') ?>

// Pre-checked
<?= $m->toggleSwitch(\'darkMode\')
    ->name(\'dark_mode\')
    ->label(\'Dark mode\')
    ->checked() ?>

// With state labels
<?= $m->toggleSwitch(\'status\')
    ->name(\'status\')
    ->label(\'Status\')
    ->onLabel(\'Active\')
    ->offLabel(\'Inactive\') ?>

// Disabled
<?= $m->toggleSwitch(\'locked\')
    ->label(\'Locked\')
    ->checked()
    ->disabled() ?>',
        '// Listen to native change event
document.getElementById(\'notifications\')
    .addEventListener(\'change\', function() {
        console.log(\'Toggled:\', this.checked);
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->toggleSwitch($id)', 'string', 'Create a toggle switch component.'],
    ['->name($name)', 'string', 'Form field name.'],
    ['->value($value)', 'string', 'Hidden input value when checked.'],
    ['->checked($checked)', 'bool', 'Set checked state (default: true).'],
    ['->disabled($disabled)', 'bool', 'Disable the toggle.'],
    ['->label($label)', 'string', 'Label text shown beside the switch.'],
    ['->onLabel($text)', 'string', 'Text shown when switch is ON.'],
    ['->offLabel($text)', 'string', 'Text shown when switch is OFF.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ['demo-toggle-basic', 'demo-toggle-checked', 'demo-toggle-labels'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                setOutput('toggle-output', '<strong>' + id + ':</strong> ' + (this.checked ? 'ON' : 'OFF'));
            });
        }
    });
});
</script>
