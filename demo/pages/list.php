<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-list-ul') ?> List</h2>
    <p class="m-demo-desc">A lightweight display list for rendering dynamic items. Items are plain containers with no visual frame by default — add your own padding and layout inside the <code>html</code> payload. For drag-to-reorder behaviour, use the <a href="/demo/reorderable">Reorderable</a> component instead.</p>

    <h3>Basic List</h3>
    <p class="m-demo-desc">Items can be updated, added, or removed via the JS API without a page reload.</p>
    <div class="m-demo-row">
        <?= $m->list('demo-list-basic')
            ->items([
                ['key' => '1', 'html' => '<div style="padding:0.6rem 0.85rem;display:flex;align-items:center;gap:0.6rem"><i class="fas fa-inbox" style="color:var(--m-primary,#2196F3)"></i> Review pull requests</div>'],
                ['key' => '2', 'html' => '<div style="padding:0.6rem 0.85rem;display:flex;align-items:center;gap:0.6rem"><i class="fas fa-code" style="color:var(--m-primary,#2196F3)"></i> Deploy staging build</div>'],
                ['key' => '3', 'html' => '<div style="padding:0.6rem 0.85rem;display:flex;align-items:center;gap:0.6rem"><i class="fas fa-bug" style="color:var(--m-primary,#2196F3)"></i> Fix login redirect bug</div>'],
                ['key' => '4', 'html' => '<div style="padding:0.6rem 0.85rem;display:flex;align-items:center;gap:0.6rem"><i class="fas fa-file-alt" style="color:var(--m-primary,#2196F3)"></i> Update documentation</div>'],
            ]) ?>
    </div>

    <div class="m-demo-row" style="gap:0.5rem;flex-wrap:wrap;">
        <?= $m->button('listAddBtn', 'Add item')->primary()->icon('fa-plus') ?>
        <?= $m->button('listRemoveBtn', 'Remove last')->icon('fa-minus') ?>
        <?= $m->button('listClearBtn', 'Clear')->danger()->icon('fa-trash') ?>
    </div>
    <div class="m-demo-output" id="list-output">Interact to see output...</div>

    <?= demoCodeTabs(
        '// Static list — items provided server-side
<?= $m->list(\'myList\')
    ->emptyMessage(\'No items yet\')
    ->items([
        [\'key\' => \'1\', \'html\' => \'<div style="padding:.6rem .85rem"><i class="fas fa-inbox"></i> Item one</div>\'],
        [\'key\' => \'2\', \'html\' => \'<div style="padding:.6rem .85rem"><i class="fas fa-code"></i> Item two</div>\'],
    ]) ?>

// Empty list — populated via JS
<?= $m->list(\'taskList\')->emptyMessage(\'No tasks yet\') ?>',
        '// Get list instance
var list = m.list(\'myList\');

// Add / update / remove items
list.addItem(\'5\', \'<div style="padding:.6rem .85rem"><strong>New</strong> item</div>\');
list.upsertItem(\'2\', \'<div style="padding:.6rem .85rem"><em>Updated</em> item</div>\');
list.removeItem(\'1\');
list.clear();

// Count
var n = list.count();

// Refresh from URL (replaces list contents)
list.refresh(\'/api/items\').then(function() {
    console.log(\'Refreshed\');
});

// Listen for refresh events
document.getElementById(\'myList\')
    .addEventListener(\'m:list:refresh\', function(e) {
        console.log(\'Items refreshed:\', e.detail);
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->list($id)', 'string', 'Create a list component.'],
    ['->items($items)', 'array', 'Set list items. Each: <code>{key, html, id?, class?, attrs?}</code>.'],
    ['->emptyMessage($msg)', '?string', 'Message shown when the list has no items.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.list(id, opts)', 'string, ?object', 'Get or create list instance.'],
    ['addItem(key, html, opts)', 'string, string, ?object', 'Append a new item.'],
    ['upsertItem(key, html, opts)', 'string, string, ?object', 'Update existing item or insert if not found.'],
    ['removeItem(key)', 'string', 'Remove an item by key.'],
    ['clear()', '', 'Remove all items.'],
    ['count()', '', 'Returns number of items.'],
    ['getItems()', '', 'Returns array of item DOM elements.'],
    ['getOrder()', '', 'Returns array of item keys in current order.'],
    ['refresh(url, opts)', 'string, ?object', 'Fetch item HTML from URL and replace list contents (returns Promise).'],
]) ?>

<?= eventsTable([
    ['m:list:refresh', '{id, items}', 'Fired after content is refreshed from a URL.'],
    ['m:list:refresh:error', '{id, error}', 'Fired if content refresh fails.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var list = m.list('demo-list-basic');
    var counter = 10;

    document.getElementById('listAddBtn').addEventListener('click', function() {
        counter++;
        list.addItem(String(counter), '<div style="padding:0.6rem 0.85rem;display:flex;align-items:center;gap:0.6rem"><i class="fas fa-star" style="color:var(--m-primary,#2196F3)"></i> New item ' + counter + '</div>');
        setOutput('list-output', 'Added item ' + counter + '. Count: ' + list.count());
    });

    document.getElementById('listRemoveBtn').addEventListener('click', function() {
        var items = list.getItems();
        if (items.length === 0) { setOutput('list-output', 'No items to remove.'); return; }
        var last = items[items.length - 1];
        var key = last.getAttribute('data-key') || last.id || '';
        list.removeItem(key);
        setOutput('list-output', 'Removed item "' + key + '". Count: ' + list.count());
    });

    document.getElementById('listClearBtn').addEventListener('click', function() {
        list.clear();
        setOutput('list-output', 'List cleared.');
    });
});
</script>
