<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-align-left') ?> TextArea</h2>
    <p class="m-demo-desc">Multi-line text input with resize control, character counter, and auto-resize support.</p>

    <h3>Basic TextArea</h3>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:500px">
            <?= $m->textarea('demo-textarea')
                ->name('notes')
                ->placeholder('Enter your notes here…')
                ->rows(4) ?>
        </div>
    </div>

    <h3>With Character Counter</h3>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:500px">
            <?= $m->textarea('demo-textarea-count')
                ->name('bio')
                ->placeholder('Write a short bio…')
                ->rows(3)
                ->characterCount(200) ?>
        </div>
    </div>

    <h3>Auto-Resize</h3>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:500px">
            <?= $m->textarea('demo-textarea-auto')
                ->name('description')
                ->placeholder('This area grows as you type…')
                ->rows(2)
                ->resize('auto') ?>
        </div>
    </div>

    <h3>Disabled</h3>
    <div class="m-demo-row">
        <div class="m-demo-field" style="max-width:500px">
            <?= $m->textarea('demo-textarea-disabled')
                ->value('This textarea is disabled.')
                ->disabled()
                ->rows(2)
                ->resize('none') ?>
        </div>
    </div>

    <div class="m-demo-output" id="textarea-output">Type in a textarea to see output...</div>

    <?= demoCodeTabs(
        '// Basic textarea
<?= $m->textarea(\'notes\')
    ->name(\'notes\')
    ->placeholder(\'Enter your notes…\')
    ->rows(4) ?>

// Character counter
<?= $m->textarea(\'bio\')
    ->name(\'bio\')
    ->placeholder(\'Write a bio…\')
    ->rows(3)
    ->characterCount(200) ?>

// Auto-resize
<?= $m->textarea(\'desc\')
    ->name(\'description\')
    ->resize(\'auto\')
    ->rows(2) ?>

// Disabled
<?= $m->textarea(\'locked\')
    ->value(\'Read-only content\')
    ->disabled()
    ->resize(\'none\') ?>',
        '// Get textarea instance
var ta = m.textarea(\'notes\');

// Get/set value
var val = ta.getValue();
ta.setValue(\'New text\');
ta.clear();

// Focus / enable / disable
ta.focus();
ta.enable();
ta.disable();

// Event callbacks via options
m.textarea(\'notes\', {
    onChange: function(data) {
        console.log(\'Changed:\', data.value);
    },
    onInput: function(data) {
        console.log(\'Input:\', data.value);
    },
    autoResize: true
});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->textarea($id)', 'string', 'Create a textarea component.'],
    ['->value($value)', '?string', 'Set the initial value.'],
    ['->placeholder($text)', 'string', 'Set placeholder text.'],
    ['->name($name)', 'string', 'Form field name.'],
    ['->required($req)', 'bool', 'Mark as required (default: true).'],
    ['->maxLength($max)', 'int', 'Set HTML maxlength attribute.'],
    ['->rows($rows)', 'int', 'Visible row count (default: 4).'],
    ['->cols($cols)', 'int', 'Visible column count.'],
    ['->disabled($dis)', 'bool', 'Disable the textarea.'],
    ['->characterCount($max)', 'int', 'Show a character counter with the given maximum.'],
    ['->resize($mode)', 'string', 'Resize behaviour: <code>none</code>, <code>vertical</code>, <code>horizontal</code>, <code>both</code>, <code>auto</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.textarea(id, opts)', 'string, ?object', 'Get or create textarea API instance.'],
    ['getValue()', '', 'Returns the current text value.'],
    ['setValue(value)', 'string', 'Set the textarea value.'],
    ['clear()', '', 'Clear the textarea.'],
    ['focus()', '', 'Focus the textarea.'],
    ['enable()', '', 'Enable the textarea.'],
    ['disable()', '', 'Disable the textarea.'],
]) ?>

<?= apiTable('JS Options', 'js', [
    ['onChange', 'function(data)', 'Called when value changes. <code>data</code>: <code>{value, element}</code>.'],
    ['onInput', 'function(data)', 'Called on every input event.'],
    ['onFocus', 'function(event)', 'Called when textarea gains focus.'],
    ['onBlur', 'function(event)', 'Called when textarea loses focus.'],
    ['autoResize', 'bool', 'Auto-grow height to fit content (default: false).'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ta = document.getElementById('demo-textarea');
    if (ta) {
        ta.addEventListener('input', function() {
            setOutput('textarea-output', '<strong>Notes:</strong> ' + (this.value || '(empty)'));
        });
    }
});
</script>
