<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-grip-vertical') ?> Reorderable</h2>
    <p class="m-demo-desc">Drag-to-sort list with a grip-dot handle on each item. Supports optional server-side persistence — when <code>->updateModelOnReorder()</code> and <code>->updateUrl()</code> are set, the new order is automatically POSTed after each drag.</p>

    <h3>Basic Reorderable</h3>
    <p class="m-demo-desc">Drag any item to a new position — the output below reflects the updated key order.</p>
    <div class="m-demo-row">
        <?= $m->reorderable('demo-reorder-basic')
            ->emptyMessage('No items — add some!')
            ->items([
                ['key' => 'a', 'html' => '<div style="padding:0.6rem 0.85rem"><strong>First task</strong><div style="font-size:0.8rem;color:#888;margin-top:2px">Drag to reorder</div></div>'],
                ['key' => 'b', 'html' => '<div style="padding:0.6rem 0.85rem"><strong>Second task</strong><div style="font-size:0.8rem;color:#888;margin-top:2px">Drag to reorder</div></div>'],
                ['key' => 'c', 'html' => '<div style="padding:0.6rem 0.85rem"><strong>Third task</strong><div style="font-size:0.8rem;color:#888;margin-top:2px">Drag to reorder</div></div>'],
                ['key' => 'd', 'html' => '<div style="padding:0.6rem 0.85rem"><strong>Fourth task</strong><div style="font-size:0.8rem;color:#888;margin-top:2px">Drag to reorder</div></div>'],
            ]) ?>
    </div>

    <div class="m-demo-output" id="reorder-output">Reorder items to see output...</div>

    <?= demoCodeTabs(
        '// Reorderable — grip handle appears on left of each item
<?= $m->reorderable(\'sortable\')
    ->emptyMessage(\'No items yet\')
    ->items([
        [\'key\' => \'a\', \'html\' => \'<div style="padding:.6rem .85rem">First item</div>\'],
        [\'key\' => \'b\', \'html\' => \'<div style="padding:.6rem .85rem">Second item</div>\'],
    ]) ?>

// With server-side persistence
<?= $m->reorderable(\'adminList\')
    ->updateModelOnReorder()
    ->updateUrl(\'/api/items/reorder\')
    ->loaderText(\'Saving order...\')
    ->items($items) ?>',
        '// Get instance
var r = m.reorderable(\'sortable\');

// Get current order as array of keys
var order = r.getOrder(); // [\'a\', \'b\', \'c\']

// Add / update / remove items
r.addItem(\'e\', \'<div style="padding:.6rem .85rem">New item</div>\');
r.upsertItem(\'b\', \'<div style="padding:.6rem .85rem"><em>Updated</em></div>\');
r.removeItem(\'a\');
r.clear();

// Count items
var n = r.count();

// Listen for reorder events
document.getElementById(\'sortable\')
    .addEventListener(\'m:reorderable:reorder\', function(e) {
        console.log(\'New order:\', e.detail.order);
    });

document.getElementById(\'sortable\')
    .addEventListener(\'m:reorderable:saved\', function(e) {
        console.log(\'Server confirmed:\', e.detail);
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->reorderable($id)', 'string', 'Create a reorderable component.'],
    ['->items($items)', 'array', 'Set items. Each: <code>{key, html, id?, class?, attrs?}</code>.'],
    ['->updateModelOnReorder($enabled)', 'bool', 'Auto-POST new order to server after each drag. Default: <code>false</code>.'],
    ['->updateUrl($url)', '?string', 'URL to POST the reordered keys to.'],
    ['->emptyMessage($msg)', '?string', 'Message shown when there are no items.'],
    ['->loaderText($text)', 'string', 'Loading indicator text (default: <code>"Saving..."</code>).'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.reorderable(id, opts)', 'string, ?object', 'Get or create reorderable instance.'],
    ['getOrder()', '', 'Returns array of item keys in current visual order.'],
    ['addItem(key, html, opts)', 'string, string, ?object', 'Append a new item.'],
    ['upsertItem(key, html, opts)', 'string, string, ?object', 'Update existing item or insert if not found.'],
    ['removeItem(key)', 'string', 'Remove an item by key.'],
    ['clear()', '', 'Remove all items.'],
    ['count()', '', 'Returns number of items.'],
    ['getItems()', '', 'Returns array of item DOM elements.'],
]) ?>

<?= eventsTable([
    ['m:reorderable:reorder', '{id, order}', 'Fired after items are drag-reordered (before server call).'],
    ['m:reorderable:saved', '{id, order, response}', 'Fired after server confirms the new order.'],
    ['m:reorderable:error', '{id, order, error}', 'Fired if the reorder POST fails.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('demo-reorder-basic');
    if (el) {
        el.addEventListener('m:reorderable:reorder', function(e) {
            setOutput('reorder-output', '<strong>New order:</strong> ' + JSON.stringify(e.detail.order));
        });
    }
});
</script>
