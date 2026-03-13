<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-clipboard-check') ?> Validator</h2>
    <p class="m-demo-desc">Client-side form validation with inline error messages, blur/input triggers, and built-in rules. Prevents layout shifts by using Manhattan styling instead of native HTML5 validation.</p>

    <h3>Registration Form Example</h3>
    <form id="demoValidatorForm" novalidate>
        <div class="m-demo-row" style="flex-direction:column; gap:1rem; max-width:400px;">
            <div class="m-demo-field">
                <label for="val-username">Username</label>
                <div class="m-textbox-wrapper">
                    <?= $m->textbox('val-username')->name('username')->placeholder('Enter username…') ?>
                </div>
            </div>
            <div class="m-demo-field">
                <label for="val-email">Email</label>
                <div class="m-textbox-wrapper">
                    <?= $m->textbox('val-email')->name('email')->placeholder('you@example.com') ?>
                </div>
            </div>
            <div class="m-demo-field">
                <label for="val-password">Password</label>
                <div class="m-textbox-wrapper">
                    <?= $m->textbox('val-password')->name('password')->type('password')->placeholder('Min 8 characters') ?>
                </div>
            </div>
            <div>
                <?= $m->button('val-submit', 'Register')->primary()->icon('fa-user-plus')->attr('type', 'submit') ?>
            </div>
        </div>
    </form>

    <?= $m->validator('demoValidatorForm')
        ->field('username', 'Username is required (letters, numbers, underscores only)', ['required', 'pattern' => '/^[a-zA-Z0-9_]+$/'])
        ->field('email', 'A valid email is required', ['required', 'email'])
        ->field('password', 'Password must be at least 8 characters', ['required', 'minLength' => 8])
        ->onSubmit('handleDemoFormSubmit(event)')
        ->validateOnBlur()
        ->validateOnInput() ?>

    <div class="m-demo-output" id="validator-output">Fill out the form and submit to see validation...</div>

    <?= demoCodeTabs(
        '// Attach validator to a form
<?= $m->validator(\'myForm\')
    ->field(\'username\', \'Username is required\', [\'required\'])
    ->field(\'email\', \'Valid email required\', [\'required\', \'email\'])
    ->field(\'password\', \'Min 8 chars\', [\'required\', \'minLength\' => 8])
    ->onSubmit(\'handleSubmit(event)\')
    ->validateOnBlur()
    ->validateOnInput() ?>',
        '// The onSubmit callback fires only when all fields are valid
function handleSubmit(event) {
    event.preventDefault();
    var form = event.target;
    var data = new FormData(form);
    console.log(\'Valid form submitted\');
}

// Available validation rules:
// \'required\'
// \'email\'
// {minLength: 5}
// {maxLength: 20}
// {min: 0}  (numbers)
// {max: 100}
// \'integer\'
// \'positive\'
// {pattern: \'/^[a-z]+$/i\'}
// {custom: function(value, input) { return value === \'ok\'; }}'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->validator($formId)', 'string', 'Create a validator targeting the form with the given ID.'],
    ['->field($name, $message, $rules)', 'string, string, array', 'Add a field to validate. Rules can be strings (<code>required</code>, <code>email</code>) or key-value pairs (<code>minLength => 8</code>).'],
    ['->onSubmit($callback)', 'string', 'JS code to execute when the form passes all validation.'],
    ['->validateOnBlur($enabled)', 'bool', 'Validate fields when they lose focus (default: true).'],
    ['->validateOnInput($enabled)', 'bool', 'Validate fields in real-time on each keystroke (default: false).'],
]) ?>

<?= apiTable('JS Validation Rules', 'js', [
    ['required', '', 'Field must have a non-empty value.'],
    ['email', '', 'Value must be a valid email address.'],
    ['minLength', 'int', 'Minimum string length.'],
    ['maxLength', 'int', 'Maximum string length.'],
    ['min', 'number', 'Minimum numeric value.'],
    ['max', 'number', 'Maximum numeric value.'],
    ['integer', '', 'Value must be a whole number.'],
    ['positive', '', 'Value must be a positive number.'],
    ['pattern', 'string (regex)', 'Value must match the given regex pattern.'],
    ['custom', 'function(value, input)', 'Custom validation function returning true/false.'],
]) ?>

<script>
function handleDemoFormSubmit(event) {
    event.preventDefault();
    setOutput('validator-output', '<strong style="color:var(--m-success, #4CAF50)">Form is valid!</strong> Username: ' + event.target.username.value + ', Email: ' + event.target.email.value);
}
</script>
