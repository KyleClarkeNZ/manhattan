<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-hand-pointer') ?> Button</h2>
    <p class="m-demo-desc">Buttons with variants, icons, loading states, confirmation dialogs, and ripple effects.</p>

    <h3>Variants</h3>
    <div class="m-demo-row">
        <?= $m->button('btn-primary', 'Primary')->primary()->icon('fa-rocket')->on('click', 'handlePrimaryClick') ?>
        <?= $m->button('btn-secondary', 'Secondary')->icon('fa-save') ?>
        <?= $m->button('btn-danger', 'Danger')->danger()->icon('fa-trash') ?>
        <?= $m->button('btn-success', 'Success')->success()->icon('fa-check') ?>
        <?= $m->button('btn-disabled', 'Disabled')->icon('fa-ban')->attr('disabled', 'disabled') ?>
    </div>

    <h3>JavaScript Initialisation</h3>
    <div class="m-demo-row">
        <?= $m->button('btn-client', 'Client Button') ?>
        <?= $m->button('btn-loading', 'Loading Demo')->icon('fa-spinner') ?>
    </div>
    <div class="m-demo-output" id="button-output">Click a button to see output...</div>

    <?= demoCodeTabs(
        '// Primary action with icon
<?= $m->button(\'saveBtn\', \'Save Changes\')
    ->primary()
    ->icon(\'fa-save\')
    ->type(\'submit\') ?>

// Danger action
<?= $m->button(\'deleteBtn\', \'Delete\')
    ->danger()
    ->icon(\'fa-trash\')
    ->on(\'click\', \'confirmDelete\') ?>

// With confirmation dialog
<?= $m->button(\'resetBtn\', \'Reset\')
    ->confirm(\'Are you sure you want to reset?\') ?>

// Full-width block button
<?= $m->button(\'loginBtn\', \'Sign In\')
    ->primary()
    ->block() ?>',
        '// Initialise with click handler
m.button(\'btn-client\', {
    events: {
        click: function() {
            console.log(\'Clicked!\');
        }
    }
});

// Programmatic control
var btn = m.button(\'saveBtn\');
btn.disable();
btn.enable();
btn.setText(\'Saving...\');
btn.setLoading(true);

// Dynamic icon
btn.icon(\'fa-check\', \'left\');'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->button($id, $text)', 'string, string', 'Create a button component.'],
    ['->primary()', '', 'Apply primary (blue) styling.'],
    ['->secondary()', '', 'Apply secondary styling.'],
    ['->danger()', '', 'Apply danger (red) styling.'],
    ['->success()', '', 'Apply success (green) styling.'],
    ['->block()', '', 'Make the button full-width.'],
    ['->loading()', '', 'Show a loading spinner.'],
    ['->icon($icon)', 'string', 'Set a Font Awesome icon.'],
    ['->type($type)', 'string', 'Set the button type: <code>button</code>, <code>submit</code>, <code>reset</code>.'],
    ['->name($name)', 'string', 'Set the <code>name</code> attribute.'],
    ['->confirm($message)', 'string', 'Show a browser confirm dialog before the click fires.'],
    ['->on($event, $handler)', 'string, string', 'Attach a JS event handler.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.button(id, options)', 'string, ?object', 'Initialise or get a button instance.'],
    ['enable()', '', 'Remove the disabled state.'],
    ['disable()', '', 'Add the disabled state.'],
    ['setText(text)', 'string', 'Update button text (preserves icon).'],
    ['setLoading(loading)', 'boolean', 'Toggle loading spinner and disable state.'],
    ['icon(faName, position)', 'string, string', 'Set or replace the icon. Position: <code>"left"</code> or <code>"right"</code>.'],
]) ?>

<?= eventsTable([
    ['click', '', 'Standard DOM click event. Includes ripple animation.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!window.m) return;

    window.handlePrimaryClick = function() {
        setOutput('button-output', '<strong>Primary clicked</strong><br>Timestamp: ' + new Date().toLocaleString());
        m.ajax('/handleButtonClick', { method: 'POST', data: { action: 'primary_click' } });
    };

    var btnSecondary = document.getElementById('btn-secondary');
    if (btnSecondary) {
        btnSecondary.addEventListener('click', function() {
            setOutput('button-output', '<strong>Secondary clicked</strong>');
        });
    }

    var btnDanger = document.getElementById('btn-danger');
    if (btnDanger) {
        btnDanger.addEventListener('click', function() {
            m.dialog.confirm('Delete this item?', 'Confirm', 'fa-trash').then(function(ok) {
                setOutput('button-output', '<strong>Confirm result:</strong> ' + (ok ? 'Deleted' : 'Cancelled'));
            });
        });
    }

    var btnSuccess = document.getElementById('btn-success');
    if (btnSuccess) {
        btnSuccess.addEventListener('click', function() {
            setOutput('button-output', '<strong>Success clicked</strong>');
        });
    }

    m.button('btn-client', {
        events: {
            click: function() {
                setOutput('button-output', '<strong>Client-side button clicked</strong><br>Initialised via JS');
            }
        }
    });

    var btnLoading = document.getElementById('btn-loading');
    if (btnLoading) {
        btnLoading.addEventListener('click', function() {
            var b = m.button('btn-loading');
            b.setLoading(true);
            setOutput('button-output', '<strong>Loading...</strong>');
            setTimeout(function() {
                b.setLoading(false);
                setOutput('button-output', '<strong>Loading complete!</strong>');
            }, 2000);
        });
    }
});
</script>
