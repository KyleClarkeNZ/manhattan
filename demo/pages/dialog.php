<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-comment-alt') ?> Dialog</h2>
    <p class="m-demo-desc">JavaScript-only dialog system for alerts, confirmations, and prompts. Returns Promises for easy async flow.</p>

    <h3>Alert</h3>
    <div class="m-demo-row">
        <?= $m->button('demo-dialog-alert', 'Show Alert')->primary()->icon('fa-info-circle') ?>
    </div>

    <h3>Confirm</h3>
    <div class="m-demo-row">
        <?= $m->button('demo-dialog-confirm', 'Show Confirm')->secondary()->icon('fa-question-circle') ?>
    </div>

    <h3>Prompt</h3>
    <div class="m-demo-row">
        <?= $m->button('demo-dialog-prompt', 'Show Prompt')->icon('fa-edit') ?>
    </div>

    <div class="m-demo-output" id="dialog-output">Click a button to see a dialog...</div>

    <?= demoCodeTabs(
        '// Dialog is JS-only — no PHP rendering needed.
// The JS module is loaded automatically via $m->renderScripts().',
        '// Alert
m.dialog.alert(\'File saved successfully.\', \'Success\', \'fa-check-circle\')
    .then(function() {
        console.log(\'Alert dismissed\');
    });

// Confirm
m.dialog.confirm(\'Delete this item?\', \'Confirm Delete\', \'fa-trash\')
    .then(function(confirmed) {
        if (confirmed) {
            console.log(\'User confirmed\');
        } else {
            console.log(\'User cancelled\');
        }
    });

// Prompt
m.dialog.prompt(\'Enter your name:\', \'World\', \'Your Name\', \'fa-user\')
    .then(function(value) {
        if (value !== null) {
            console.log(\'User entered:\', value);
        } else {
            console.log(\'User cancelled\');
        }
    });'
    ) ?>
</div>

<?= apiTable('JS Methods (Static)', 'js', [
    ['m.dialog.alert(message, title, icon)', 'string, ?string, ?string', 'Show an alert dialog. Returns <code>Promise&lt;void&gt;</code>. Default title: "Alert", icon: <code>fa-info-circle</code>.'],
    ['m.dialog.confirm(message, title, icon)', 'string, ?string, ?string', 'Show a confirm dialog. Returns <code>Promise&lt;boolean&gt;</code>. Resolves <code>true</code> (OK) or <code>false</code> (Cancel).'],
    ['m.dialog.prompt(message, defaultValue, title, icon)', 'string, ?string, ?string, ?string', 'Show a prompt with text input. Returns <code>Promise&lt;string|null&gt;</code>. Resolves the input value or <code>null</code> if cancelled.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('demo-dialog-alert').addEventListener('click', function() {
        m.dialog.alert('This is an informational alert.', 'Notice', 'fa-info-circle').then(function() {
            setOutput('dialog-output', '<strong>Alert dismissed</strong>');
        });
    });
    document.getElementById('demo-dialog-confirm').addEventListener('click', function() {
        m.dialog.confirm('Are you sure you want to proceed?', 'Confirm Action', 'fa-question-circle').then(function(confirmed) {
            setOutput('dialog-output', '<strong>Confirm result:</strong> ' + (confirmed ? 'Yes' : 'No'));
        });
    });
    document.getElementById('demo-dialog-prompt').addEventListener('click', function() {
        m.dialog.prompt('Enter your name:', 'World', 'Your Name', 'fa-user').then(function(value) {
            if (value !== null) {
                setOutput('dialog-output', '<strong>Hello,</strong> ' + value + '!');
            } else {
                setOutput('dialog-output', '<strong>Prompt cancelled</strong>');
            }
        });
    });
});
</script>
