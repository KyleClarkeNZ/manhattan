<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-window-maximize') ?> Window</h2>
    <p class="m-demo-desc">Modal and non-modal window component with optional dragging, resizing, footer buttons, and dynamic content loading.</p>

    <h3>Modal Window</h3>
    <div class="m-demo-row">
        <?= $m->button('demo-open-modal', 'Open Modal')->primary()->icon('fa-window-maximize') ?>
    </div>

    <?= $m->window('demo-window-modal', 'Modal Window')
        ->content('<p style="padding:1rem;">This is a modal window. Click the X or press Escape to close.</p><p style="padding:0 1rem 1rem;">The overlay prevents interaction with the page beneath.</p>')
        ->modal()
        ->width('500px')
        ->addButton('OK', 'close', 'primary')
        ->addButton('Cancel', 'close', 'secondary') ?>

    <h3>Draggable Window</h3>
    <div class="m-demo-row">
        <?= $m->button('demo-open-drag', 'Open Draggable')->secondary()->icon('fa-arrows-alt') ?>
    </div>

    <?= $m->window('demo-window-drag', 'Draggable Window')
        ->content('<p style="padding:1rem;">Drag the title bar to move this window around.</p>')
        ->modal()
        ->draggable()
        ->width('400px') ?>

    <h3>Content via JavaScript</h3>
    <div class="m-demo-row">
        <?= $m->button('demo-open-dynamic', 'Open with Dynamic Content')->icon('fa-sync') ?>
    </div>

    <?= $m->window('demo-window-dynamic', 'Dynamic Content')
        ->content('<p style="padding:1rem;">Loading…</p>')
        ->modal()
        ->width('500px') ?>

    <div class="m-demo-output" id="window-output">Open a window to see output...</div>

    <?= demoCodeTabs(
        '// PHP: Define the window (hidden by default)
<?= $m->window(\'myModal\', \'Modal Title\')
    ->content(\'<p>Content here</p>\')
    ->modal()
    ->width(\'500px\')
    ->addButton(\'OK\', \'close\', \'primary\')
    ->addButton(\'Cancel\', \'close\', \'secondary\') ?>

// Draggable window
<?= $m->window(\'dragWin\', \'Draggable\')
    ->content(\'<p>Drag me!</p>\')
    ->modal()
    ->draggable()
    ->resizable()
    ->width(\'400px\') ?>

// Visible on load
<?= $m->window(\'visible\', \'Welcome\')
    ->content(\'<p>Hello!</p>\')
    ->visible() ?>',
        '// Open/close a window
var win = m.window(\'myModal\');
win.open();
win.close();
win.toggle();

// Update content
win.setTitle(\'New Title\');
win.setContent(\'<p>Updated</p>\');

// Load content from URL
win.loadContent(\'/api/details\')
    .then(function() { console.log(\'Loaded\'); });

// Listen for events
document.getElementById(\'myModal\')
    .addEventListener(\'m:window:open\', function() {
        console.log(\'Window opened\');
    });
document.getElementById(\'myModal\')
    .addEventListener(\'m:window:close\', function() {
        console.log(\'Window closed\');
    });
document.getElementById(\'myModal\')
    .addEventListener(\'m:window:action\', function(e) {
        console.log(\'Button action:\', e.detail);
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->window($id, $title)', 'string, string', 'Create a window component.'],
    ['->title($title)', 'string', 'Set the window title.'],
    ['->content($html)', 'string', 'Set body HTML content.'],
    ['->modal($isModal)', 'bool', 'Show as modal with overlay (default: true).'],
    ['->draggable($drag)', 'bool', 'Allow dragging by title bar.'],
    ['->resizable($resize)', 'bool', 'Allow window resizing.'],
    ['->scrollable($scroll)', 'bool', 'Enable content area scrolling (default: true).'],
    ['->width($w)', 'string', 'Window width (CSS value, e.g. <code>500px</code>).'],
    ['->height($h)', 'string', 'Window height.'],
    ['->minWidth($w)', 'string', 'Minimum width.'],
    ['->minHeight($h)', 'string', 'Minimum height.'],
    ['->addButton($text, $action, $style)', 'string, string, string', 'Add a footer button. Style: <code>primary</code>, <code>secondary</code>, <code>danger</code>.'],
    ['->visible($vis)', 'bool', 'Show the window on page load.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.window(id, opts)', 'string, ?object', 'Get or create Window instance.'],
    ['open()', '', 'Show the window.'],
    ['close()', '', 'Hide the window.'],
    ['toggle()', '', 'Toggle visibility.'],
    ['setTitle(html)', 'string', 'Update the title bar.'],
    ['setContent(html)', 'string', 'Replace body content.'],
    ['loadContent(url, fetchOpts)', 'string, ?object', 'Fetch HTML from a URL and set as content (returns Promise).'],
]) ?>

<?= eventsTable([
    ['m:window:open', '', 'Fired when the window opens.'],
    ['m:window:close', '', 'Fired when the window closes.'],
    ['m:window:action', '{action}', 'Fired when a footer button is clicked.'],
    ['m:window:content-loaded', '{url}', 'Fired after <code>loadContent()</code> completes.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal
    var openModal = document.getElementById('demo-open-modal');
    if (openModal) {
        openModal.addEventListener('click', function() {
            m.window('demo-window-modal').open();
            setOutput('window-output', '<strong>Modal opened</strong>');
        });
    }

    // Draggable
    var openDrag = document.getElementById('demo-open-drag');
    if (openDrag) {
        openDrag.addEventListener('click', function() {
            m.window('demo-window-drag').open();
            setOutput('window-output', '<strong>Draggable window opened</strong>');
        });
    }

    // Dynamic content
    var openDyn = document.getElementById('demo-open-dynamic');
    if (openDyn) {
        openDyn.addEventListener('click', function() {
            var win = m.window('demo-window-dynamic');
            win.setContent('<p style="padding:1rem;"><i class="fas fa-spinner fa-spin"></i> Loading content…</p>');
            win.open();
            setTimeout(function() {
                win.setContent('<p style="padding:1rem;"><i class="fas fa-check-circle" style="color:var(--m-success,#4CAF50)"></i> Content loaded dynamically via <code>setContent()</code></p>');
                setOutput('window-output', '<strong>Dynamic content loaded</strong>');
            }, 1500);
        });
    }

    // Close events
    var modalEl = document.getElementById('demo-window-modal');
    if (modalEl) {
        modalEl.addEventListener('m:window:close', function() {
            setOutput('window-output', '<strong>Modal closed</strong>');
        });
    }
});
</script>
