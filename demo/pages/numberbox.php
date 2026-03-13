<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-hashtag') ?> NumberBox</h2>
    <p class="m-demo-desc">Numeric input with integer/decimal presets, min/max range, and step control.</p>

    <h3>Examples</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Integer (0-100):</label>
            <div class="m-textbox-wrapper">
                <?= $m->numberbox('demo-integer')->integer()->range(0, 100)->placeholder('Enter a whole number') ?>
            </div>
        </div>
        <div class="m-demo-field">
            <label>Decimal (2 places):</label>
            <div class="m-textbox-wrapper">
                <?= $m->numberbox('demo-decimal')->decimal(2)->min(0)->placeholder('e.g. 12.34') ?>
            </div>
        </div>
    </div>

    <h3>With Step</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Quantity (step 5):</label>
            <div class="m-textbox-wrapper">
                <?= $m->numberbox('demo-step')->integer()->step(5)->range(0, 100)->value('25')->name('quantity') ?>
            </div>
        </div>
    </div>

    <div class="m-demo-output" id="numberbox-output">Enter numbers to see output...</div>

    <?= demoCodeTabs(
        '// Integer with range
<?= $m->numberbox(\'quantity\')
    ->integer()
    ->range(0, 100)
    ->placeholder(\'Enter a number\') ?>

// Decimal with precision
<?= $m->numberbox(\'price\')
    ->decimal(2)
    ->min(0)
    ->placeholder(\'e.g. 12.34\') ?>

// Custom step
<?= $m->numberbox(\'step\')
    ->integer()
    ->step(5)
    ->range(0, 100) ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->numberbox($id)', 'string', 'Create a number input component.'],
    ['->value($value)', '?string', 'Set the initial value.'],
    ['->placeholder($text)', 'string', 'Set placeholder text.'],
    ['->name($name)', 'string', 'Form field name.'],
    ['->required($req)', 'bool', 'Mark as required.'],
    ['->min($min)', 'float', 'Minimum allowed value.'],
    ['->max($max)', 'float', 'Maximum allowed value.'],
    ['->step($step)', 'float', 'Step increment.'],
    ['->disabled($dis)', 'bool', 'Disable the input.'],
    ['->integer()', '', 'Preset for integers: step=1.'],
    ['->decimal($precision)', 'int', 'Preset for decimals with given decimal places (default: 2).'],
    ['->range($min, $max)', 'float, float', 'Set both min and max in one call.'],
    ['->allowDecimals($allow)', 'bool', 'Allow decimal input (auto-sets step=0.01).'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var nbInt = document.getElementById('demo-integer');
    var nbDec = document.getElementById('demo-decimal');
    [nbInt, nbDec].forEach(function(el) {
        if (el) el.addEventListener('input', function() {
            setOutput('numberbox-output', '<strong>' + el.id + ':</strong> ' + el.value);
        });
    });
});
</script>
