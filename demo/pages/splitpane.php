<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-columns') ?> SplitPane</h2>
    <p class="m-demo-desc">
        A resizable two-panel layout. Drag the divider to resize the panels.
        Keyboard accessible via arrow keys (10&thinsp;px step, Shift = 50&thinsp;px).
        The first-pane size is persisted in <code>localStorage</code> between page loads.
        On mobile (&le;&thinsp;768&thinsp;px) the divider is hidden and the panes stack vertically.
        Default: horizontal orientation.
    </p>

    <h3>Horizontal Split (default)</h3>
    <p class="m-demo-desc">Left pane has a minimum of 120&thinsp;px, maximum of 400&thinsp;px, and opens at 220&thinsp;px.</p>
    <div class="m-demo-row" style="height:220px">
        <?= $m->splitPane('demoHSplit')
            ->initialSize(220)
            ->minSize(120)
            ->maxSize(400)
            ->first('<div style="padding:1rem;height:100%;box-sizing:border-box;overflow:auto"><strong>Left pane</strong><p style="margin-top:.5rem;color:#666;font-size:.85rem">Drag the divider to resize. This pane will not shrink below 120&thinsp;px or grow past 400&thinsp;px.</p></div>')
            ->second('<div style="padding:1rem;height:100%;box-sizing:border-box;overflow:auto"><strong>Right pane</strong><p style="margin-top:.5rem;color:#666;font-size:.85rem">The right pane takes all remaining space.</p></div>') ?>
    </div>

    <h3>Vertical Split</h3>
    <p class="m-demo-desc">Top pane resizes vertically. Minimum 80&thinsp;px, maximum 200&thinsp;px, default 120&thinsp;px.</p>
    <div class="m-demo-row" style="height:300px">
        <?= $m->splitPane('demoVSplit')
            ->direction('vertical')
            ->initialSize(120)
            ->minSize(80)
            ->maxSize(200)
            ->first('<div style="padding:1rem;height:100%;box-sizing:border-box;overflow:auto"><strong>Top pane</strong><p style="margin-top:.5rem;color:#666;font-size:.85rem">Drag the horizontal bar to resize.</p></div>')
            ->second('<div style="padding:1rem;height:100%;box-sizing:border-box;overflow:auto"><strong>Bottom pane</strong><p style="margin-top:.5rem;color:#666;font-size:.85rem">The bottom pane takes all remaining space.</p></div>') ?>
    </div>

    <h3>JS API — get/set/reset</h3>
    <p class="m-demo-desc">Use <code>m.splitPane(id)</code> to programmatically read or write the first-pane size, or reset to the initial value.</p>
    <div class="m-demo-row" style="height:200px">
        <?= $m->splitPane('demoApiSplit')
            ->initialSize(200)
            ->minSize(100)
            ->maxSize(380)
            ->first('<div style="padding:1rem;height:100%;box-sizing:border-box;overflow:auto"><strong>Resizable pane</strong></div>')
            ->second('<div style="padding:1rem;height:100%;box-sizing:border-box;overflow:auto"><strong>Content pane</strong></div>') ?>
    </div>
    <div class="m-demo-row" style="gap:.5rem;flex-wrap:wrap;margin-top:.75rem">
        <?= $m->button('btnGetSize', 'Get Size')->icon('fa-ruler-horizontal') ?>
        <?= $m->button('btnSetSize', 'Set to 300 px')->icon('fa-expand-arrows-alt') ?>
        <?= $m->button('btnReset',   'Reset')->icon('fa-undo') ?>
        <span id="sizeOutput" style="align-self:center;font-size:.875rem;color:#666"></span>
    </div>

    <?= demoCodeTabs(
        '// Horizontal split (default direction)
<?= $m->splitPane(\'mySplit\')
    ->initialSize(220)
    ->minSize(120)
    ->maxSize(400)
    ->first(\'<div>Left pane content</div>\')
    ->second(\'<div>Right pane content</div>\') ?>

// Vertical split
<?= $m->splitPane(\'vertSplit\')
    ->direction(\'vertical\')
    ->initialSize(180)
    ->minSize(80)
    ->maxSize(300)
    ->first(\'<div>Top pane content</div>\')
    ->second(\'<div>Bottom pane content</div>\') ?>',
        '// Get the current first-pane size in pixels
var sp = m.splitPane(\'mySplit\');
var px = sp.getSize(); // returns current width (horizontal) or height (vertical)

// Set a specific size
sp.setSize(300);

// Reset to the initial size (data-initial-size attribute)
sp.reset();

// Listen for resize events
document.getElementById(\'mySplit\').addEventListener(\'m:splitpane:resize\', function (e) {
    console.log(\'New size:\', e.detail.size, \'px\');
});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->splitPane($id)', 'string $id', 'Create a SplitPane component.'],
    ['->first($html)', 'string $html', 'HTML content for the first (left / top) pane.'],
    ['->second($html)', 'string $html', 'HTML content for the second (right / bottom) pane.'],
    ['->direction($dir)', "string 'horizontal'|'vertical'", 'Split direction. Default: <code>\'horizontal\'</code>.'],
    ['->initialSize($px)', 'int $px', 'Starting width (horizontal) or height (vertical) of the first pane in pixels. Default: <code>300</code>.'],
    ['->minSize($px)', 'int $px', 'Minimum first-pane size in pixels. Default: none.'],
    ['->maxSize($px)', 'int $px', 'Maximum first-pane size in pixels. Default: none.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.splitPane(id)', 'string id', 'Get the SplitPane API object for the given element id.'],
    ['sp.getSize()', '', 'Returns the current first-pane size in pixels.'],
    ['sp.setSize(px)', 'number px', 'Programmatically set the first-pane size (clamped to min/max).'],
    ['sp.reset()', '', 'Reset the first pane to its <code>data-initial-size</code> value and clear localStorage.'],
]) ?>

<?= eventsTable([
    ['m:splitpane:resize', '{ size: number }', 'Fired on the SplitPane root element whenever the divider is moved or the size is changed programmatically.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sizeOutput = document.getElementById('sizeOutput');

    document.getElementById('btnGetSize').addEventListener('click', function () {
        var sp = m.splitPane('demoApiSplit');
        sizeOutput.textContent = 'Current size: ' + sp.getSize() + ' px';
    });

    document.getElementById('btnSetSize').addEventListener('click', function () {
        m.splitPane('demoApiSplit').setSize(300);
        sizeOutput.textContent = 'Set to 300 px';
    });

    document.getElementById('btnReset').addEventListener('click', function () {
        m.splitPane('demoApiSplit').reset();
        sizeOutput.textContent = 'Reset to initial size (200 px)';
    });

    document.getElementById('demoApiSplit').addEventListener('m:splitpane:resize', function (e) {
        sizeOutput.textContent = 'Resized to: ' + e.detail.size + ' px';
    });
});
</script>
