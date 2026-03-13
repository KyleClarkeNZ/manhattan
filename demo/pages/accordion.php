<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-bars-staggered') ?> Accordion</h2>
    <p class="m-demo-desc">Collapsible panels for organizing content into expandable sections. By default, only one panel can be open at a time (true accordion behavior). Supports animated carets, multiple open panels, and compact/borderless variants. Fully keyboard-navigable with ARIA roles.</p>

    <h3>Basic Accordion (Single Open)</h3>
    <p class="m-demo-desc">By default, opening a panel automatically closes any other open panels. Default: first panel open.</p>
    <div class="m-demo-row">
        <?= $m->accordion('demoAccordionBasic')
            ->panel('General Settings', '<p>Configure general application settings here.</p>', 'fa-cog')
            ->panel('Privacy & Security', '<p>Manage privacy settings and security options.</p>', 'fa-shield-alt')
            ->panel('Notifications', '<p>Control email and in-app notifications.</p>', 'fa-bell')
            ->defaultOpen(0)
            ->animated() ?>
    </div>

    <?= demoCodeTabs(
        '// Default behavior: only one panel open at a time
<?= $m->accordion(\'settings\')
    ->panel(\'General Settings\', \'<p>General content</p>\', \'fa-cog\')
    ->panel(\'Privacy & Security\', \'<p>Privacy content</p>\', \'fa-shield-alt\')
    ->panel(\'Notifications\', \'<p>Notifications content</p>\', \'fa-bell\')
    ->defaultOpen(0)  // First panel open by default
    ->animated() ?>',
        '// Listen for panel open/close events
document.getElementById(\'settings\')
    .addEventListener(\'m:accordion:opened\', function(e) {
        console.log(\'Opened panel:\', e.detail.index);
    });

document.getElementById(\'settings\')
    .addEventListener(\'m:accordion:closed\', function(e) {
        console.log(\'Closed panel:\', e.detail.index);
    });'
    ) ?>

    <h3>Allow Multiple Panels Open</h3>
    <p class="m-demo-desc">Use <code>->allowMultiple()</code> to allow multiple panels to be open simultaneously.</p>
    <div class="m-demo-row">
        <?= $m->accordion('demoAccordionMultiple')
            ->panel('Database', '<p>Connection: MySQL 8.0<br>Status: Connected<br>Uptime: 42 days</p>', 'fa-database')
            ->panel('Cache', '<p>Redis 6.2<br>Memory Usage: 45%<br>Hit Rate: 98.7%</p>', 'fa-memory')
            ->panel('Storage', '<p>Disk Usage: 234 GB / 1 TB<br>Backup: Daily at 2 AM</p>', 'fa-hdd')
            ->allowMultiple()
            ->animated() ?>
    </div>

    <?= demoCodeTabs(
        '// Allow multiple panels to be open at once
<?= $m->accordion(\'system\')
    ->panel(\'Database\', \'<p>DB info</p>\', \'fa-database\')
    ->panel(\'Cache\', \'<p>Cache stats</p>\', \'fa-memory\')
    ->panel(\'Storage\', \'<p>Storage info</p>\', \'fa-hdd\')
    ->allowMultiple()
    ->animated() ?>',
        '// Programmatically control panels
var acc = m.accordion(\'system\');

// Open specific panels
acc.open(0);  // Open first panel
acc.open(2);  // Open third panel (both can be open with allowMultiple)

// Close a panel
acc.close(0);

// Toggle a panel
acc.toggle(1);

// Get currently open panels
var openPanels = acc.getOpen();  // Returns array of indices
console.log(openPanels);  // e.g., [0, 2]'
    ) ?>

    <h3>Non-Animated</h3>
    <p class="m-demo-desc">Omit <code>->animated()</code> for instant expand/collapse with a static caret icon. Default: non-animated.</p>
    <div class="m-demo-row">
        <?= $m->accordion('demoAccordionNoAnim')
            ->panel('Quick Facts', '<ul><li>Founded: 2024</li><li>Headquarters: Wellington</li><li>Employees: 50+</li></ul>', 'fa-info-circle')
            ->panel('Products', '<ul><li>Manhattan UI</li><li>CallSheet</li><li>Other Projects</li></ul>', 'fa-box')
            ->panel('Contact', '<p>Email: hello@example.com<br>Phone: 04 123 4567</p>', 'fa-envelope')
            ->defaultOpen(0) ?>
    </div>

    <?= demoCodeTabs(
        '// Non-animated (default behavior if ->animated() not called)
<?= $m->accordion(\'info\')
    ->panel(\'Quick Facts\', \'<ul>...</ul>\', \'fa-info-circle\')
    ->panel(\'Products\', \'<ul>...</ul>\', \'fa-box\')
    ->panel(\'Contact\', \'<p>Contact info</p>\', \'fa-envelope\')
    ->defaultOpen(0) ?>',
        '// Keyboard navigation available automatically:
// - Arrow Down: Next panel
// - Arrow Up: Previous panel
// - Home: First panel
// - End: Last panel
// - Space/Enter: Toggle focused panel'
    ) ?>

    <h3>Compact Style</h3>
    <p class="m-demo-desc">Use <code>->addClass('m-accordion--compact')</code> for smaller padding on headers and content.</p>
    <div class="m-demo-row">
        <?= $m->accordion('demoAccordionCompact')
            ->panel('Option 1', '<p>This is a compact accordion panel.</p>')
            ->panel('Option 2', '<p>Reduced padding for tighter layouts.</p>')
            ->panel('Option 3', '<p>Great for sidebars or narrow spaces.</p>')
            ->addClass('m-accordion--compact')
            ->animated() ?>
    </div>

    <h3>Borderless Style</h3>
    <p class="m-demo-desc">Use <code>->addClass('m-accordion--borderless')</code> to remove borders between panels.</p>
    <div class="m-demo-row">
        <?= $m->accordion('demoAccordionBorderless')
            ->panel('Feature A', '<p>No borders between panels for a cleaner look.</p>', 'fa-star')
            ->panel('Feature B', '<p>Works well with card-based layouts.</p>', 'fa-star')
            ->panel('Feature C', '<p>Subtle divider between panels.</p>', 'fa-star')
            ->addClass('m-accordion--borderless')
            ->animated() ?>
    </div>

    <?= demoCodeTabs(
        '// Compact accordion
<?= $m->accordion(\'compactAcc\')
    ->panel(\'Option 1\', \'<p>Content</p>\')
    ->panel(\'Option 2\', \'<p>Content</p>\')
    ->addClass(\'m-accordion--compact\')
    ->animated() ?>

// Borderless accordion
<?= $m->accordion(\'borderlessAcc\')
    ->panel(\'Feature A\', \'<p>Content</p>\', \'fa-star\')
    ->panel(\'Feature B\', \'<p>Content</p>\', \'fa-star\')
    ->addClass(\'m-accordion--borderless\')
    ->animated() ?>

// Combine variants
<?= $m->accordion(\'combined\')
    ->panel(\'Item\', \'<p>Content</p>\')
    ->addClass(\'m-accordion--compact m-accordion--borderless\')
    ->animated() ?>',
        '// No additional JavaScript required for variants.
// Styling is handled entirely via CSS classes.'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->accordion($id)', 'string', 'Create an accordion component.'],
    ['->panel($title, $content, ?$icon)', 'string, string, ?string', 'Add a panel with title, HTML content, and optional icon. Returns self for chaining.'],
    ['->animated()', '', 'Enable animated caret rotation and smooth height transitions. Default: <code>false</code>.'],
    ['->allowMultiple()', '', 'Allow multiple panels to be open simultaneously. Default: <code>false</code> (only one panel open).'],
    ['->defaultOpen($index)', 'int', 'Set which panel is open by default (0-based index). Default: none open.'],
    ['->addClass($class)', 'string', 'Add CSS classes. Use <code>m-accordion--compact</code> or <code>m-accordion--borderless</code> for variants.'],
    ['->attr($name, $value)', 'string, string', 'Add custom HTML attributes to the accordion container.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.accordion(id)', 'string', 'Get the accordion API for the given element ID.'],
    ['open(index)', 'int', 'Open a panel by index (0-based). If <code>allowMultiple</code> is false, closes other panels.'],
    ['close(index)', 'int', 'Close a panel by index (0-based).'],
    ['toggle(index)', 'int', 'Toggle a panel\'s open/closed state.'],
    ['getOpen()', '', 'Returns an array of currently open panel indices.'],
]) ?>

<?= eventsTable([
    ['m:accordion:opened', '{index, panel, header, content}', 'Fired when a panel is opened. <code>index</code> is the 0-based panel index.'],
    ['m:accordion:closed', '{index, panel, header, content}', 'Fired when a panel is closed. <code>index</code> is the 0-based panel index.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for demo output (optional)
    var demos = ['demoAccordionBasic', 'demoAccordionMultiple', 'demoAccordionNoAnim', 'demoAccordionCompact', 'demoAccordionBorderless'];
    
    demos.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('m:accordion:opened', function(e) {
                console.log('[' + id + '] Opened panel:', e.detail.index);
            });
            el.addEventListener('m:accordion:closed', function(e) {
                console.log('[' + id + '] Closed panel:', e.detail.index);
            });
        }
    });
});
</script>
