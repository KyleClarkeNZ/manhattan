<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-window-maximize') ?> Window</h2>
    <p class="m-demo-desc">Non-modal windows and modal dialogs. Non-modal windows (default) are draggable and stack with z-index management. Modal windows show an overlay and block page interaction.</p>

    <h3>Non-Modal Window (Default)</h3>
    <p class="m-demo-desc">Non-modal windows are draggable by default, have no overlay, and re-order to front when clicked.</p>
    <div class="m-demo-row">
        <?= $m->button('demo-open-window1', 'Open Window 1')->primary()->icon('fa-window-restore') ?>
        <?= $m->button('demo-open-window2', 'Open Window 2')->secondary()->icon('fa-window-restore') ?>
    </div>

    <?= $m->window('demo-window-1', 'Non-Modal Window 1')
        ->content('<p style="padding:1rem;">This is a non-modal window (default behavior).</p><p style="padding:0 1rem 1rem;">You can drag it, click other windows to bring them to front, and interact with the page beneath.</p>')
        ->width('400px') ?>

    <?= $m->window('demo-window-2', 'Non-Modal Window 2')
        ->content('<p style="padding:1rem;">This is another non-modal window.</p><p style="padding:0 1rem 1rem;">Open both, drag them around, and click each one to see z-index stacking in action.</p>')
        ->width('400px') ?>

    <h3>Modal Window</h3>
    <p class="m-demo-desc">Modal windows show an overlay, block interaction with the page, and are not draggable.</p>
    <div class="m-demo-row">
        <?= $m->button('demo-open-modal', 'Open Modal')->icon('fa-window-maximize') ?>
    </div>

    <?= $m->window('demo-window-modal', 'Modal Window')
        ->content('<p style="padding:1rem;">This is a modal window. Click the X or press Escape to close.</p><p style="padding:0 1rem 1rem;">The overlay prevents interaction with the page beneath, and modal windows cannot be dragged.</p>')
        ->modal()
        ->width('500px')
        ->addButton('OK', 'close', 'primary')
        ->addButton('Cancel', 'close', 'secondary') ?>

    <h3>Draggable Modal (Override)</h3>
    <p class="m-demo-desc">You can explicitly make a modal draggable with <code>->draggable()</code>.</p>
    <div class="m-demo-row">
        <?= $m->button('demo-open-drag', 'Open Draggable Modal')->icon('fa-arrows-alt') ?>
    </div>

    <?= $m->window('demo-window-drag', 'Draggable Modal')
        ->content('<p style="padding:1rem;">This modal has been made draggable via <code>->modal()->draggable()</code>.</p><p style="padding:0 1rem 1rem;">Drag the title bar to move this window around.</p>')
        ->modal()
        ->draggable()
        ->width('400px') ?>

    <h3>Content via JavaScript</h3>
    <div class="m-demo-row">
        <?= $m->button('demo-open-dynamic', 'Open with Dynamic Content')->icon('fa-sync') ?>
    </div>

    <?= $m->window('demo-window-dynamic', 'Dynamic Content')
        ->content('<p style="padding:1rem;">Loading…</p>')
        ->width('500px') ?>

    <div class="m-demo-output" id="window-output">Open a window to see output...</div>

    <?= demoCodeTabs(
        '// PHP: Non-modal window (default - draggable, no overlay)
<?= $m->window(\'myWin\', \'Window Title\')
    ->content(\'<p>Content here</p>\')
    ->width(\'500px\') ?>

// Modal window (overlay, blocks interaction, not draggable)
<?= $m->window(\'myModal\', \'Modal Title\')
    ->content(\'<p>Modal content</p>\')
    ->modal()
    ->width(\'500px\')
    ->addButton(\'OK\', \'close\', \'primary\')
    ->addButton(\'Cancel\', \'close\', \'secondary\') ?>

// Draggable modal (override default)
<?= $m->window(\'dragModal\', \'Draggable Modal\')
    ->content(\'<p>Drag me!</p>\')
    ->modal()
    ->draggable()
    ->width(\'400px\') ?>

// Visible on load
<?= $m->window(\'visible\', \'Welcome\')
    ->content(\'<p>Hello!</p>\')
    ->visible() ?>

// Non-draggable non-modal window
<?= $m->window(\'static\', \'Static Window\')
    ->content(\'<p>Cannot drag</p>\')
    ->draggable(false) ?>',
        '// Open/close a window
var win = m.window(\'myWin\');
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
document.getElementById(\'myWin\')
    .addEventListener(\'m:window:open\', function() {
        console.log(\'Window opened\');
    });
document.getElementById(\'myWin\')
    .addEventListener(\'m:window:close\', function() {
        console.log(\'Window closed\');
    });
document.getElementById(\'myWin\')
    .addEventListener(\'m:window:action\', function(e) {
        console.log(\'Button action:\', e.detail);
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->window($id, $title)', 'string, string', 'Create a window component (non-modal by default).'],
    ['->title($title)', 'string', 'Set the window title.'],
    ['->content($html)', 'string', 'Set body HTML content.'],
    ['->modal($isModal)', 'bool', 'Make window modal with overlay (default: <code>false</code>). Modals block interaction and are not draggable unless <code>->draggable()</code> is called.'],
    ['->draggable($drag)', 'bool', 'Allow dragging by title bar. Default: <code>true</code> for non-modal windows, <code>false</code> for modals.'],
    ['->resizable($resize)', 'bool', 'Allow window resizing.'],
    ['->scrollable($scroll)', 'bool', 'Enable content area scrolling (default: <code>true</code>).'],
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
    // Non-modal window 1
    var openWin1 = document.getElementById('demo-open-window1');
    if (openWin1) {
        openWin1.addEventListener('click', function() {
            m.window('demo-window-1').open();
            setOutput('window-output', '<strong>Non-modal window 1 opened</strong> — draggable, no overlay');
        });
    }

    // Non-modal window 2
    var openWin2 = document.getElementById('demo-open-window2');
    if (openWin2) {
        openWin2.addEventListener('click', function() {
            m.window('demo-window-2').open();
            setOutput('window-output', '<strong>Non-modal window 2 opened</strong> — click windows to bring to front');
        });
    }

    // Modal
    var openModal = document.getElementById('demo-open-modal');
    if (openModal) {
        openModal.addEventListener('click', function() {
            m.window('demo-window-modal').open();
            setOutput('window-output', '<strong>Modal window opened</strong> — overlay blocks page interaction');
        });
    }

    // Draggable modal
    var openDrag = document.getElementById('demo-open-drag');
    if (openDrag) {
        openDrag.addEventListener('click', function() {
            m.window('demo-window-drag').open();
            setOutput('window-output', '<strong>Draggable modal opened</strong> — modal with dragging enabled');
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
