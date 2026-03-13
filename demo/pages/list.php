<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-list-ul') ?> List</h2>
    <p class="m-demo-desc">Interactive list with drag-to-reorder, dynamic item management, and optional server-side persistence.</p>

    <h3>Basic List</h3>
    <div class="m-demo-row">
        <?= $m->list('demo-list-basic')
            ->items([
                ['key' => '1', 'html' => '<i class="fas fa-inbox" style="margin-right:.5rem;color:var(--m-primary,#2196F3)"></i> Review pull requests'],
                ['key' => '2', 'html' => '<i class="fas fa-code" style="margin-right:.5rem;color:var(--m-primary,#2196F3)"></i> Deploy staging build'],
                ['key' => '3', 'html' => '<i class="fas fa-bug" style="margin-right:.5rem;color:var(--m-primary,#2196F3)"></i> Fix login redirect bug'],
                ['key' => '4', 'html' => '<i class="fas fa-file-alt" style="margin-right:.5rem;color:var(--m-primary,#2196F3)"></i> Update documentation'],
            ]) ?>
    </div>

    <h3>Reorderable List</h3>
    <p>Drag items to reorder. The new order is logged to the output panel.</p>
    <div class="m-demo-row">
        <?= $m->list('demo-list-reorder')
            ->reorderable()
            ->emptyMessage('No items — add some!')
            ->items([
                ['key' => 'a', 'html' => '<strong>1.</strong> First task'],
                ['key' => 'b', 'html' => '<strong>2.</strong> Second task'],
                ['key' => 'c', 'html' => '<strong>3.</strong> Third task'],
                ['key' => 'd', 'html' => '<strong>4.</strong> Fourth task'],
            ]) ?>
    </div>

    <div class="m-demo-output" id="list-output">Reorder items to see output...</div>

    <?= demoCodeTabs(
        '// Basic list
<?= $m->list(\'myList\')
    ->items([
        [\'key\' => \'1\', \'html\' => \'<i class="fas fa-inbox"></i> Item one\'],
        [\'key\' => \'2\', \'html\' => \'<i class="fas fa-code"></i> Item two\'],
    ]) ?>

// Reorderable with server sync
<?= $m->list(\'sortable\')
    ->reorderable()
    ->updateModelOnReorder()
    ->updateUrl(\'/api/tasks/reorder\')
    ->emptyMessage(\'No items yet\')
    ->items($items) ?>',
        '// Get list instance
var list = m.list(\'myList\');

// Get current order
var order = list.getOrder(); // [\'1\', \'2\', \'3\']

// Add / update / remove items
list.addItem(\'5\', \'<strong>New</strong> item\');
list.upsertItem(\'2\', \'<em>Updated</em> item\');
list.removeItem(\'1\');
list.clear();

// Get count
var count = list.count();

// Refresh from URL
list.refresh(\'/api/items\').then(function() {
    console.log(\'Refreshed\');
});

// Toggle reorderable
list.setReorderable(true);

// Listen for reorder
document.getElementById(\'myList\')
    .addEventListener(\'m:list:reorder\', function(e) {
        console.log(\'New order:\', e.detail);
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->list($id)', 'string', 'Create a list component.'],
    ['->items($items)', 'array', 'Set list items. Each: <code>{key, html, id?, class?, attrs?}</code>.'],
    ['->reorderable($enabled)', 'bool', 'Enable drag-to-reorder (default: true).'],
    ['->updateModelOnReorder($enabled)', 'bool', 'Auto-POST new order to server.'],
    ['->updateUrl($url)', '?string', 'URL to POST the reordered keys to.'],
    ['->emptyMessage($msg)', '?string', 'Message shown when the list has no items.'],
    ['->useLoader($enabled)', 'bool', 'Show loading overlay during reorder save (default: true).'],
    ['->loaderText($text)', 'string', 'Loading indicator text (default: "Saving…").'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.list(id, opts)', 'string, ?object', 'Get or create list instance.'],
    ['getOrder()', '', 'Returns array of item keys in current order.'],
    ['addItem(key, html, opts)', 'string, string, ?object', 'Append a new item.'],
    ['upsertItem(key, html, opts)', 'string, string, ?object', 'Update existing item or insert if not found.'],
    ['removeItem(key)', 'string', 'Remove an item by key.'],
    ['clear()', '', 'Remove all items.'],
    ['count()', '', 'Returns number of items.'],
    ['getItems()', '', 'Returns array of item DOM elements.'],
    ['setReorderable(bool)', 'bool', 'Enable/disable drag reorder.'],
    ['refresh(url, opts)', 'string, ?object', 'Fetch item HTML from URL and replace list contents (returns Promise).'],
]) ?>

<?= eventsTable([
    ['m:list:reorder', '{keys}', 'Fired after items are drag-reordered.'],
    ['m:list:reorder:saved', '{response}', 'Fired after server confirms the new order.'],
    ['m:list:reorder:error', '{error}', 'Fired if the reorder POST fails.'],
    ['m:list:refresh', '', 'Fired after content is refreshed from a URL.'],
    ['m:list:refresh:error', '{error}', 'Fired if content refresh fails.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var listEl = document.getElementById('demo-list-reorder');
    if (listEl) {
        listEl.addEventListener('m:list:reorder', function(e) {
            setOutput('list-output', '<strong>New order:</strong> ' + JSON.stringify(e.detail));
        });
    }
});
</script>
