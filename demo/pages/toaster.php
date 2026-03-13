<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-bell') ?> Toaster</h2>
    <p class="m-demo-desc">Toast notification system with auto-dismiss, multiple positions, and a full-width banner mode. The global <code>appToaster</code> is typically rendered in the shared layout.</p>

    <h3>Toast Types</h3>
    <div class="m-demo-row" style="gap:.5rem; flex-wrap:wrap;">
        <?= $m->button('demo-toast-success', 'Success')->success()->icon('fa-check-circle') ?>
        <?= $m->button('demo-toast-error', 'Error')->danger()->icon('fa-exclamation-circle') ?>
        <?= $m->button('demo-toast-warning', 'Warning')->icon('fa-exclamation-triangle') ?>
        <?= $m->button('demo-toast-info', 'Info')->primary()->icon('fa-info-circle') ?>
    </div>

    <h3>Persistent Toast</h3>
    <div class="m-demo-row">
        <?= $m->button('demo-toast-persist', 'Persistent Toast')->secondary()->icon('fa-thumbtack') ?>
        <?= $m->button('demo-toast-clear', 'Clear All')->icon('fa-times') ?>
    </div>

    <?php // Render a demo toaster (in a real app, one is in the shared layout) ?>
    <?= $m->toaster('demoToaster')->position('top-right') ?>

    <?= demoCodeTabs(
        '// PHP: Render the toaster container (typically once in layout)
<?= $m->toaster(\'appToaster\')->position(\'top-right\') ?>

// Positions: top-right, top-left, bottom-right, bottom-left, banner

// Banner mode with initial message
<?= $m->toaster(\'bannerToaster\')
    ->position(\'banner\')
    ->initial(\'Deployment complete!\', \'success\') ?>',
        '// Show toasts (from anywhere)
m.toaster(\'appToaster\').show(\'Task saved!\', \'success\');
m.toaster(\'appToaster\').show(\'Something went wrong.\', \'error\');
m.toaster(\'appToaster\').show(\'Check your settings.\', \'warning\');
m.toaster(\'appToaster\').show(\'New update available.\', \'info\');

// Persistent toast (duration: 0)
m.toaster(\'appToaster\').show(\'Please review.\', \'info\', {duration: 0});

// Clear all toasts
m.toaster(\'appToaster\').clearAll();

// Custom duration (ms)
m.toaster(\'appToaster\').show(\'Quick!\', \'success\', {duration: 2000});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->toaster($id)', 'string', 'Create a toaster container.'],
    ['->position($pos)', 'string', 'Position: <code>top-right</code>, <code>top-left</code>, <code>bottom-right</code>, <code>bottom-left</code>, <code>banner</code>.'],
    ['->initial($message, $type)', 'string, string', 'Add a server-rendered initial message (banner mode). Type: <code>success</code>, <code>error</code>, <code>warning</code>, <code>info</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.toaster(id, opts)', 'string, ?object', 'Get or create toaster instance.'],
    ['show(message, type, opts)', 'string, string, ?object', 'Show a toast. Type: <code>success</code>, <code>error</code>, <code>warning</code>, <code>info</code>. Options: <code>{duration: 5000}</code>.'],
    ['clearAll()', '', 'Remove all active toasts.'],
    ['hide(toastElement)', 'HTMLElement', 'Hide a specific toast.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toasterId = 'demoToaster';
    document.getElementById('demo-toast-success').addEventListener('click', function() {
        m.toaster(toasterId).show('Operation completed successfully!', 'success');
    });
    document.getElementById('demo-toast-error').addEventListener('click', function() {
        m.toaster(toasterId).show('An error occurred. Please try again.', 'error');
    });
    document.getElementById('demo-toast-warning').addEventListener('click', function() {
        m.toaster(toasterId).show('Careful — this action cannot be undone.', 'warning');
    });
    document.getElementById('demo-toast-info').addEventListener('click', function() {
        m.toaster(toasterId).show('A new version is available.', 'info');
    });
    document.getElementById('demo-toast-persist').addEventListener('click', function() {
        m.toaster(toasterId).show('This toast stays until dismissed.', 'info', {duration: 0});
    });
    document.getElementById('demo-toast-clear').addEventListener('click', function() {
        m.toaster(toasterId).clearAll();
    });
});
</script>
