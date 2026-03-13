<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-comment-dots') ?> Tooltip</h2>
    <p class="m-demo-desc">Auto-initialising tooltips via data attributes. Converts native <code>title</code> attributes, supports positioning, and refreshes dynamically added elements.</p>

    <h3>Data Attribute Tooltips</h3>
    <div class="m-demo-row" style="gap:1rem; flex-wrap:wrap;">
        <?= $m->button('demo-tt-top', 'Top tooltip')->attr('data-m-tooltip', 'This appears on top')->attr('data-m-tooltip-position', 'top') ?>
        <?= $m->button('demo-tt-right', 'Right tooltip')->attr('data-m-tooltip', 'Right side tooltip')->attr('data-m-tooltip-position', 'right') ?>
        <?= $m->button('demo-tt-bottom', 'Bottom tooltip')->attr('data-m-tooltip', 'Shown below')->attr('data-m-tooltip-position', 'bottom') ?>
        <?= $m->button('demo-tt-left', 'Left tooltip')->attr('data-m-tooltip', 'Left side tooltip')->attr('data-m-tooltip-position', 'left') ?>
    </div>

    <h3>On Icons &amp; Badges</h3>
    <div class="m-demo-row" style="gap:1rem; align-items:center;">
        <span data-m-tooltip="Save your work" data-m-tooltip-position="top"><?= $m->icon('fa-save') ?></span>
        <span data-m-tooltip="Delete item" data-m-tooltip-position="top"><?= $m->icon('fa-trash') ?></span>
        <span data-m-tooltip="3 pending items"><?= $m->badge('demo-tt-badge', 'Pending') ?></span>
    </div>

    <h3>Native Title Conversion</h3>
    <p>Elements with a <code>title</code> attribute are automatically converted to Manhattan tooltips.</p>
    <div class="m-demo-row" style="gap:1rem;">
        <button class="m-btn" title="This was a native title attribute">Hover me (native title)</button>
    </div>

    <?= demoCodeTabs(
        '// Add tooltips via data attributes (no PHP helper needed)
<button data-m-tooltip="Tooltip text" data-m-tooltip-position="top">
    Hover me
</button>

// Alternative syntax
<span data-tooltip="Also works">Alt syntax</span>

// On any element
<i class="fas fa-info-circle" data-m-tooltip="More info"></i>

// Disable tooltip on an element
<button data-m-tooltip="Hidden" data-m-tooltip-disabled="1">
    No tooltip
</button>

// Native title conversion (automatic)
<button title="Hello">Auto-converted</button>',
        '// Refresh tooltips (after dynamic DOM changes)
m.tooltip.refresh();

// Refresh within a specific container
m.tooltip.refresh(document.getElementById(\'myContainer\'));

// Programmatically show/hide
m.tooltip.show(element);
m.tooltip.hide();'
    ) ?>
</div>

<?= apiTable('Data Attributes', 'js', [
    ['data-m-tooltip', 'string', 'Tooltip text to display.'],
    ['data-tooltip', 'string', 'Alternative attribute name (same behaviour).'],
    ['data-m-tooltip-position', 'string', 'Position: <code>top</code> (default), <code>right</code>, <code>bottom</code>, <code>left</code>.'],
    ['data-m-tooltip-disabled', '"1"', 'Disable the tooltip for this element.'],
    ['title', 'string', 'Automatically converted to a Manhattan tooltip (native title removed).'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.tooltip.refresh(root)', '?HTMLElement', 'Re-scan for tooltip elements. Call after adding new DOM nodes. Defaults to <code>document</code>.'],
    ['m.tooltip.show(element)', 'HTMLElement', 'Show the tooltip for the given element.'],
    ['m.tooltip.hide()', '', 'Hide the currently visible tooltip.'],
]) ?>
