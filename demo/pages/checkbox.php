<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-check-square') ?> Checkbox</h2>
    <p class="m-demo-desc">Standard checkbox with label text, custom indicator styling, and keyboard support.</p>

    <h3>Basic Checkboxes</h3>
    <div class="m-demo-row">
        <?= $m->checkbox('demo-cb-1')->name('options[]')->value('email')->label('Email notifications') ?>
        <?= $m->checkbox('demo-cb-2')->name('options[]')->value('sms')->label('SMS notifications')->checked() ?>
        <?= $m->checkbox('demo-cb-3')->name('options[]')->value('push')->label('Push notifications') ?>
    </div>

    <h3>Required</h3>
    <div class="m-demo-row">
        <?= $m->checkbox('demo-cb-terms')->name('terms')->value('1')->label('I agree to the Terms & Conditions')->required() ?>
    </div>

    <h3>Disabled</h3>
    <div class="m-demo-row">
        <?= $m->checkbox('demo-cb-dis')->label('Disabled unchecked')->disabled() ?>
        <?= $m->checkbox('demo-cb-dis2')->label('Disabled checked')->checked()->disabled() ?>
    </div>

    <div class="m-demo-output" id="checkbox-output">Check a box to see output...</div>

    <?= demoCodeTabs(
        '// Basic checkbox
<?= $m->checkbox(\'agree\')
    ->name(\'agree\')
    ->value(\'1\')
    ->label(\'I agree\') ?>

// Pre-checked
<?= $m->checkbox(\'newsletter\')
    ->name(\'newsletter\')
    ->value(\'yes\')
    ->label(\'Subscribe to newsletter\')
    ->checked() ?>

// Required
<?= $m->checkbox(\'terms\')
    ->name(\'terms\')
    ->value(\'1\')
    ->label(\'Accept terms\')
    ->required() ?>

// Disabled
<?= $m->checkbox(\'locked\')
    ->label(\'Not editable\')
    ->disabled() ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->checkbox($id)', 'string', 'Create a checkbox component.'],
    ['->name($name)', 'string', 'Form field name.'],
    ['->value($value)', 'string', 'Value submitted when checked.'],
    ['->checked($checked)', 'bool', 'Set checked state (default: true).'],
    ['->disabled($disabled)', 'bool', 'Disable the checkbox.'],
    ['->required($req)', 'bool', 'Mark as required.'],
    ['->label($label)', 'string', 'Label text beside the checkbox.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ['demo-cb-1', 'demo-cb-2', 'demo-cb-3', 'demo-cb-terms'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                setOutput('checkbox-output', '<strong>' + id + ':</strong> ' + (this.checked ? 'checked' : 'unchecked'));
            });
        }
    });
});
</script>
