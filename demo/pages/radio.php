<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-dot-circle') ?> Radio</h2>
    <p class="m-demo-desc">Radio button for single-choice selection within a named group.</p>

    <h3>Radio Group</h3>
    <div class="m-demo-row">
        <?= $m->radio('demo-radio-low')->name('priority')->value('low')->label('Low') ?>
        <?= $m->radio('demo-radio-med')->name('priority')->value('medium')->label('Medium')->checked() ?>
        <?= $m->radio('demo-radio-high')->name('priority')->value('high')->label('High') ?>
    </div>

    <h3>Disabled</h3>
    <div class="m-demo-row">
        <?= $m->radio('demo-radio-dis1')->name('locked')->value('a')->label('Option A')->checked()->disabled() ?>
        <?= $m->radio('demo-radio-dis2')->name('locked')->value('b')->label('Option B')->disabled() ?>
    </div>

    <div class="m-demo-output" id="radio-output">Select an option to see output...</div>

    <?= demoCodeTabs(
        '// Radio group
<?= $m->radio(\'low\')->name(\'priority\')->value(\'low\')->label(\'Low\') ?>
<?= $m->radio(\'med\')->name(\'priority\')->value(\'medium\')->label(\'Medium\')->checked() ?>
<?= $m->radio(\'high\')->name(\'priority\')->value(\'high\')->label(\'High\') ?>

// Disabled
<?= $m->radio(\'dis\')
    ->name(\'locked\')
    ->value(\'a\')
    ->label(\'Locked\')
    ->checked()
    ->disabled() ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->radio($id)', 'string', 'Create a radio button component.'],
    ['->name($name)', 'string', 'Form group name (all radios in a group share the same name).'],
    ['->value($value)', 'string', 'Value submitted when selected.'],
    ['->checked($checked)', 'bool', 'Set selected state (default: true).'],
    ['->disabled($disabled)', 'bool', 'Disable the radio button.'],
    ['->required($req)', 'bool', 'Mark as required.'],
    ['->label($label)', 'string', 'Label text beside the radio button.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ['demo-radio-low', 'demo-radio-med', 'demo-radio-high'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                setOutput('radio-output', '<strong>Priority:</strong> ' + this.value);
            });
        }
    });
});
</script>
