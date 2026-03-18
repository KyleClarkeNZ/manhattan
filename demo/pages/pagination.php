<?php
/** @var \Manhattan\HtmlHelper $m */

// --- Build demo item lists ---

// 47 items for the client-side demo
$clientItems = [];
for ($i = 1; $i <= 47; $i++) {
    $clientItems[] = [
        'key'  => 'item-' . $i,
        'html' => '<div class="demo-pag-item">'
            . '<span class="demo-pag-num">' . $i . '</span>'
            . '<div><strong>List Item #' . $i . '</strong><br>'
            . '<span style="font-size:12px;color:#888">Client-side pagination — all items are in the DOM</span></div>'
            . '</div>',
        'attrs' => ['data-pagination-item' => ''],
    ];
}

// Fewer items for the compact demo
$smallItems = [];
for ($j = 1; $j <= 18; $j++) {
    $smallItems[] = [
        'key'  => 'small-' . $j,
        'html' => '<div class="demo-pag-item">'
            . '<span class="demo-pag-num">' . $j . '</span>'
            . '<div><strong>Record #' . $j . '</strong></div>'
            . '</div>',
        'attrs' => ['data-pagination-item' => ''],
    ];
}
?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-ellipsis-h') ?> Pagination</h2>
    <p class="m-demo-desc">
        A versatile pagination component supporting three modes: <strong>client</strong>
        (items already in the DOM — JS shows/hides them), <strong>server</strong>
        (renders real <code>&lt;a&gt;</code> links for full-page navigation), and
        <strong>ajax</strong> (JS fetches each page and injects the HTML response into a target container).
    </p>

    <!-- ================================================================
         1. Client-side pagination
         ================================================================ -->
    <h3>Client-Side Pagination</h3>
    <p class="m-demo-desc">
        All 47 items are rendered server-side. The pagination reads the item count automatically
        from the target container and shows/hides slices — no database query needed per page.
        Items must be direct children of the target, or carry <code>data-pagination-item</code>.
    </p>

    <div id="demo-client-list" class="demo-pag-list">
        <?php foreach ($clientItems as $item): ?>
            <div class="m-list-item" data-pagination-item><?= $item['html'] ?></div>
        <?php endforeach; ?>
    </div>

    <?= $m->pagination('demo-pager-client')
        ->target('demo-client-list')
        ->perPage(8)
        ->showInfo(true)
        ->showSizeSelector([5, 8, 15, 25])
        ->align('center') ?>

    <!-- ================================================================
         2. Server-Side Pagination
         ================================================================ -->
    <h3>Server-Side Pagination</h3>
    <p class="m-demo-desc">
        PHP renders <code>&lt;a href="…"&gt;</code> links for each page.
        The controller passes <code>total</code>, <code>perPage</code>, and <code>currentPage</code>
        from the database query, and the component builds the correct button set.
        Full-page reload on navigation (standard web behaviour).
    </p>

    <?= $m->pagination('demo-pager-server')
        ->total(243)
        ->perPage(15)
        ->currentPage(6)
        ->mode('server')
        ->url('/items?page={page}&perPage={perPage}')
        ->showInfo(true)
        ->showFirstLast(true) ?>

    <!-- ================================================================
         3. AJAX Pagination
         ================================================================ -->
    <h3>AJAX Pagination</h3>
    <p class="m-demo-desc">
        On each page change, JS calls the configured URL (with <code>{page}</code> and
        <code>{perPage}</code> tokens), then injects the HTML response into the target container.
        The endpoint may return plain HTML or a JSON object <code>{ html, total }</code>
        to also update the total count. A loading state is applied to the target during fetch.
    </p>

    <div id="demo-ajax-list" class="demo-pag-list">
        <div class="demo-pag-loading-hint">
            <span class="m-loader-spinner" aria-hidden="true"></span>
            Loading first page…
        </div>
    </div>

    <?= $m->pagination('demo-pager-ajax')
        ->mode('ajax')
        ->url('/demo/paginationPage?page={page}&perPage={perPage}')
        ->target('demo-ajax-list')
        ->total(47)
        ->perPage(5)
        ->showInfo(true)
        ->autoLoad(true) ?>

    <!-- ================================================================
         4. Sizes, Alignment, Variants
         ================================================================ -->
    <h3>Variants — Compact, Large, Alignment</h3>

    <p class="m-demo-desc"><strong>Compact</strong></p>
    <div id="demo-small-list" class="demo-pag-list">
        <?php foreach ($smallItems as $item): ?>
            <div class="m-list-item" data-pagination-item><?= $item['html'] ?></div>
        <?php endforeach; ?>
    </div>
    <?= $m->pagination('demo-pager-compact')
        ->target('demo-small-list')
        ->perPage(6)
        ->compact()
        ->showInfo(true) ?>

    <p class="m-demo-desc" style="margin-top:1.5rem"><strong>Large, left-aligned, with first/last jump buttons</strong></p>
    <?= $m->pagination('demo-pager-large')
        ->total(350)
        ->perPage(25)
        ->currentPage(8)
        ->mode('server')
        ->url('/data?page={page}')
        ->large()
        ->align('left')
        ->showFirstLast(true)
        ->showInfo(true) ?>

    <div class="m-demo-output" id="pag-output">Interact with the client or AJAX pager above to see events here…</div>

    <?= demoCodeTabs(
        '// --- CLIENT MODE (simplest) ---
// All items in DOM. Total auto-detected from child count.
<?= $m->list(\'taskList\')->items($listItems) ?>
<?= $m->pagination(\'taskPager\')
      ->target(\'taskList\')
      ->perPage(10)
      ->showInfo(true)
      ->showSizeSelector([5, 10, 25]) ?>

// Items need data-pagination-item, or be direct children of target.
// Add it via attrs when building items:
[\'html\' => \'...\', \'attrs\' => [\'data-pagination-item\' => \'\']]

// --- SERVER MODE ---
// Controller passes total/page from DB query:
$total = $model->count();
$items = $model->paginate($page, $perPage);
// View:
<?= $m->pagination(\'pager\')
      ->total($total)
      ->perPage($perPage)
      ->currentPage($page)
      ->mode(\'server\')
      ->url(\'/tasks?page={page}&perPage={perPage}\')
      ->showInfo(true)
      ->showFirstLast(true) ?>

// --- AJAX MODE ---
// Fetches URL on each page change, injects response into target.
// Response: plain HTML  OR  JSON { html: string, total: int }
<?= $m->pagination(\'pager\')
      ->mode(\'ajax\')
      ->url(\'/api/tasks?page={page}&perPage={perPage}\')
      ->target(\'taskList\')
      ->total($initialTotal)
      ->perPage(10)
      ->showInfo(true)
      ->autoLoad(true) ?>   // auto-fetch page 1 on init

// Variants:
->compact()
->large()
->align(\'left\')   // \'center\' (default) | \'left\' | \'right\'
->showFirstLast(true)
->maxButtons(9)',
        '// Get pagination instance (auto-inited on DOMContentLoaded)
var pager = m.pagination(\'my-pager\');

// Navigate programmatically
pager.goTo(3);
pager.next();
pager.prev();
pager.first();
pager.last();

// Update total (after AJAX fetch returns a count)
pager.setTotal(200);

// Change per-page size
pager.setPerPage(25);

// Read current state
var s = pager.getState();
// { page, perPage, total, totalPages, offset, limit }
console.log(\'Fetching:\', s.offset, \'+\', s.limit);

// Refresh client-mode items after DOM mutation
pager.refresh();

// External trigger elements (e.g. custom Next button elsewhere in page)
// Just add data attributes — no JS needed:
// <button data-m-pagination="my-pager" data-page="5">Go to 5</button>

// Listen to page changes
document.getElementById(\'my-pager\').addEventListener(\'m:pagination:change\', function(e) {
    var d = e.detail;
    console.log(\'Page\', d.page, \'of\', d.totalPages,
                \'| offset:\', d.offset, \'limit:\', d.limit);
    // Example: re-fetch a custom component
    m.list(\'taskList\').refresh(\'/tasks?offset=\' + d.offset + \'&limit=\' + d.limit);
});

// Ajax-loaded event
document.getElementById(\'my-pager\').addEventListener(\'m:pagination:loaded\', function(e) {
    console.log(\'Loaded page\', e.detail.page, \'from\', e.detail.url);
});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->pagination($id)', 'Pagination', 'Create a new pagination component.'],
    ['->total($n)', 'self', 'Total number of items. In client mode, leave 0 for auto-count.'],
    ['->perPage($n)', 'self', 'Items per page. Default: <code>10</code>.'],
    ['->currentPage($n)', 'self', 'Current page (1-based). Default: <code>1</code>.'],
    ['->target($id)', 'self', 'ID of the container whose children are paginated (client/ajax).'],
    ['->mode($m)', 'self', '<code>\'client\'</code> (default), <code>\'server\'</code>, or <code>\'ajax\'</code>.'],
    ['->url($template)', 'self', 'URL template. Tokens: <code>{page}</code>, <code>{perPage}</code>. Required for server mode; optional for ajax auto-fetch.'],
    ['->showInfo()', 'self', 'Show "Showing X–Y of Z" info text. Default: false.'],
    ['->showSizeSelector($sizes)', 'self', 'Show a per-page size selector. E.g. <code>[10, 25, 50]</code>.'],
    ['->align($a)', 'self', 'Alignment: <code>\'center\'</code> (default), <code>\'left\'</code>, <code>\'right\'</code>.'],
    ['->maxButtons($n)', 'self', 'Max page buttons including ellipsis. Default: <code>7</code>.'],
    ['->showFirstLast()', 'self', 'Show « (first) and » (last) jump buttons. Default: false.'],
    ['->compact()', 'self', 'Use smaller buttons.'],
    ['->large()', 'self', 'Use larger buttons.'],
    ['->autoLoad()', 'self', 'Ajax mode: fetch page 1 automatically on init. Default: false.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.pagination(id)', 'object|null', 'Get or create a pagination instance.'],
    ['p.goTo(n)', '', 'Navigate to page n.'],
    ['p.next() / p.prev() / p.first() / p.last()', '', 'Navigate relative pages.'],
    ['p.setTotal(n)', '', 'Update total item count and re-render controls.'],
    ['p.setPerPage(n)', '', 'Change per-page size and navigate to page 1.'],
    ['p.getState()', 'object', 'Returns <code>{ page, perPage, total, totalPages, offset, limit }</code>.'],
    ['p.refresh()', '', 'Re-discover items in target (client mode) and re-scan external triggers.'],
    ['p.element', 'HTMLElement', 'The underlying <code>&lt;nav&gt;</code> DOM element.'],
]) ?>

<?= eventsTable([
    ['m:pagination:change', '{ page, perPage, total, totalPages, offset, limit }', 'Fired on every page or per-page change. Use <code>offset</code> and <code>limit</code> in AJAX calls for SQL-style slicing.'],
    ['m:pagination:loaded',  '{ page, perPage, url }', 'Fired after ajax auto-fetch completes and HTML has been injected.'],
]) ?>

<style>
.demo-pag-list {
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 1rem;
    min-height: 40px;
}
.demo-pag-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
}
.m-list-item + .m-list-item {
    border-top: 1px solid rgba(0,0,0,0.05);
}
.demo-pag-num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #118AB2;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}
.demo-pag-loading-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px;
    color: #888;
    font-size: 13px;
}
body.m-dark .demo-pag-list,
body.theme-dark .demo-pag-list {
    border-color: rgba(255,255,255,0.08);
}
body.m-dark .m-list-item + .m-list-item,
body.theme-dark .m-list-item + .m-list-item {
    border-top-color: rgba(255,255,255,0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var output = document.getElementById('pag-output');

    ['demo-pager-client', 'demo-pager-ajax', 'demo-pager-compact'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('m:pagination:change', function (e) {
            var d = e.detail;
            output.textContent = '"' + id + '" → page ' + d.page + ' of ' + d.totalPages
                + ' (offset ' + d.offset + ', limit ' + d.limit + ')';
        });
        el.addEventListener('m:pagination:loaded', function (e) {
            output.textContent = '"' + id + '" loaded page ' + e.detail.page + ' from: ' + e.detail.url;
        });
    });
});
</script>
