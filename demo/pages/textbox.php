<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-i-cursor') ?> TextBox</h2>
    <p class="m-demo-desc">Single-line text input with validation, character counting, presets for email and password fields, and JS event hooks.</p>

    <h3>Basic Inputs</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Email (HTML5 type):</label>
            <div class="m-textbox-wrapper">
                <?= $m->textbox('demo-email')->email()->placeholder('name@example.com')->required(true) ?>
            </div>
        </div>
        <div class="m-demo-field">
            <label>Password:</label>
            <div class="m-textbox-wrapper">
                <?= $m->textbox('demo-pass')->password('new-password')->placeholder('Enter a password')->minLength(8) ?>
            </div>
        </div>
    </div>

    <h3>Character Count</h3>
    <div class="m-demo-row">
        <div class="m-demo-field">
            <label>Username (max 20 chars):</label>
            <div class="m-textbox-wrapper">
                <?= $m->textbox('demo-username')->placeholder('Enter username')->characterCount(20)->name('username') ?>
            </div>
        </div>
    </div>

    <div class="m-demo-output" id="textbox-output">Type to see output...</div>

    <?= demoCodeTabs(
        '// Email preset
<?= $m->textbox(\'email\')
    ->email()
    ->placeholder(\'you@example.com\')
    ->required(true) ?>

// Password preset
<?= $m->textbox(\'password\')
    ->password(\'new-password\')
    ->placeholder(\'Min 8 characters\')
    ->minLength(8) ?>

// With character count
<?= $m->textbox(\'username\')
    ->placeholder(\'Enter username\')
    ->characterCount(20)
    ->name(\'username\') ?>

// Pattern validation
<?= $m->textbox(\'code\')
    ->pattern(\'[A-Z]{3}-[0-9]{4}\')
    ->placeholder(\'ABC-1234\') ?>',
        '// Initialise with event hooks
m.textbox(\'email\', {
    onInput: function(data) {
        console.log(data.value);
    },
    onChange: function(data) {
        console.log(\'Final:\', data.value);
    }
});

// Programmatic API
var tb = m.textbox(\'email\');
tb.getValue();
tb.setValue(\'new@example.com\');
tb.clear();
tb.focus();
tb.enable();
tb.disable();

// Validation
tb.validate();      // check HTML5 rules
tb.setError(\'Invalid email\');
tb.clearError();'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->textbox($id)', 'string', 'Create a text input component.'],
    ['->value($value)', '?string', 'Set the initial value.'],
    ['->placeholder($text)', 'string', 'Set placeholder text.'],
    ['->name($name)', 'string', 'Form field name.'],
    ['->required($req)', 'bool', 'Mark as required (default: <code>true</code>).'],
    ['->minLength($len)', 'int', 'Minimum character length.'],
    ['->maxLength($len)', 'int', 'Maximum character length.'],
    ['->disabled($dis)', 'bool', 'Disable the input.'],
    ['->type($type)', 'string', 'HTML input type (<code>text</code>, <code>email</code>, <code>password</code>, etc.).'],
    ['->email()', '', 'Preset for email: sets type, autocomplete, inputmode.'],
    ['->password($autocomplete)', 'string', 'Preset for password field. Default: <code>"current-password"</code>.'],
    ['->autocomplete($value)', 'string', 'Set the <code>autocomplete</code> attribute.'],
    ['->pattern($pattern)', 'string', 'Regex validation pattern.'],
    ['->characterCount($max)', 'int', 'Show a live <code>X/max</code> character counter.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.textbox(id, options)', 'string, ?object', 'Initialise or get a textbox instance.'],
    ['getValue()', '', 'Get the current input value.'],
    ['setValue(value)', 'string', 'Set the value and fire a <code>change</code> event.'],
    ['clear()', '', 'Clear the input value.'],
    ['focus()', '', 'Focus the input element.'],
    ['enable()', '', 'Remove the disabled state.'],
    ['disable()', '', 'Add the disabled state.'],
    ['validate()', '', 'Run HTML5 <code>checkValidity()</code>.'],
    ['setError(message)', 'string', 'Show an error message below the input.'],
    ['clearError()', '', 'Clear any error state.'],
]) ?>

<?= apiTable('JS Options', 'js', [
    ['onChange', 'function|string', 'Callback fired on the <code>change</code> event. Receives <code>{value}</code>.'],
    ['onInput', 'function|string', 'Callback fired on every <code>input</code> event. Receives <code>{value}</code>.'],
    ['onFocus', 'function|string', 'Callback fired on <code>focus</code>.'],
    ['onBlur', 'function|string', 'Callback fired on <code>blur</code>.'],
    ['validateOnBlur', 'boolean', 'Auto-validate on blur (default: <code>true</code>).'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!window.m) return;

    m.textbox('demo-email', {
        onInput: function(data) {
            setOutput('textbox-output', '<strong>Email:</strong> ' + (data.value || '(empty)'));
        }
    });
    m.textbox('demo-pass', {
        onInput: function(data) {
            setOutput('textbox-output', '<strong>Password length:</strong> ' + String((data.value || '').length));
        }
    });
});
</script>
