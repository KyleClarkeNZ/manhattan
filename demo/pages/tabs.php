<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-folder') ?> Tabs</h2>
    <p class="m-demo-desc">Tabbed panels for organising content into switchable sections. Supports default, pills, and underline styles. Fully keyboard-navigable with ARIA roles.</p>

    <h3>Default Style</h3>
    <div class="m-demo-row">
        <?= $m->tabs('demoTabsDefault')
            ->tab('general', 'General')->icon('fa-cog')->content('<p style="padding:1rem">General settings content goes here.</p>')->active()
            ->tab('advanced', 'Advanced')->icon('fa-sliders-h')->content('<p style="padding:1rem">Advanced options content goes here.</p>')
            ->tab('disabled', 'Disabled')->disabled() ?>
    </div>

    <h3>With Badge Counts</h3>
    <div class="m-demo-row">
        <?= $m->tabs('demoTabsBadge')
            ->tab('all', 'All Tasks')->icon('fa-list')->content('<p style="padding:1rem">Showing all tasks.</p>')
            ->tab('pending', 'Pending')->icon('fa-clock')->content('<p style="padding:1rem">Pending tasks.</p>')->active()
            ->tab('overdue', 'Overdue')->icon('fa-exclamation-circle')->content('<p style="padding:1rem">Overdue tasks.</p>')
            ->badge('pending', 6)
            ->badge('overdue', 2) ?>
    </div>

    <h3>Pills Style</h3>
    <div class="m-demo-row">
        <?= $m->tabs('demoTabsPills')->tabStyle('pills')
            ->tab('inbox', 'Inbox')->icon('fa-inbox')->content('<p style="padding:1rem">Your inbox messages.</p>')->active()
            ->tab('sent', 'Sent')->icon('fa-paper-plane')->content('<p style="padding:1rem">Sent messages.</p>')
            ->tab('drafts', 'Drafts')->icon('fa-file-alt')->content('<p style="padding:1rem">Draft messages.</p>') ?>
    </div>

    <h3>Underline Style</h3>
    <div class="m-demo-row">
        <?= $m->tabs('demoTabsUnderline')->tabStyle('underline')
            ->tab('overview', 'Overview')->content('<p style="padding:1rem">Overview panel.</p>')->active()
            ->tab('details', 'Details')->content('<p style="padding:1rem">Detailed information.</p>')
            ->tab('history', 'History')->content('<p style="padding:1rem">History log.</p>') ?>
    </div>

    <?= demoCodeTabs(
        '// Default tabs with icons and content
<?= $m->tabs(\'myTabs\')
    ->tab(\'general\', \'General\')
        ->icon(\'fa-cog\')
        ->content(\'<p>General settings here.</p>\')
        ->active()
    ->tab(\'advanced\', \'Advanced\')
        ->icon(\'fa-sliders-h\')
        ->content(\'<p>Advanced options.</p>\')
    ->tab(\'disabled\', \'Disabled\')
        ->disabled() ?>

// Pills style
<?= $m->tabs(\'pillTabs\')->tabStyle(\'pills\')
    ->tab(\'inbox\', \'Inbox\')->active()
    ->tab(\'sent\', \'Sent\') ?>

// Underline style
<?= $m->tabs(\'lineTabs\')->tabStyle(\'underline\')
    ->tab(\'overview\', \'Overview\')->active()
    ->tab(\'details\', \'Details\') ?>

// Badge counts
<?= $m->tabs(\'t\')
    ->tab(\'pending\', \'Pending\')->active()
    ->tab(\'overdue\', \'Overdue\')
    ->badge(\'pending\', 6)
    ->badge(\'overdue\', 2) ?>',
        '// Listen for tab changes
document.getElementById(\'myTabs\')
    .addEventListener(\'m-tab-change\', function(e) {
        console.log(\'Tab changed to:\', e.detail.key);
    });

// Programmatic tab selection
var tabs = m.tabs(\'myTabs\');
tabs.selectTab(\'advanced\');

// Update tab content
tabs.setContent(\'general\', \'<p>Updated content</p>\');

// Load content via AJAX
tabs.refreshContent(\'advanced\', \'/api/settings\');

// Get active tab key
var key = tabs.getActiveTab();'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->tabs($id)', 'string', 'Create a tabs component.'],
    ['->tabStyle($style)', 'string', 'Set tab style: <code>default</code>, <code>pills</code>, or <code>underline</code>.'],
    ['->tab($key, $label)', 'string, string', 'Add a new tab panel. Returns self for chaining panel methods.'],
    ['->icon($icon)', 'string', 'Set icon on the current tab (chain after <code>->tab()</code>).'],
    ['->content($html)', 'string', 'Set HTML content for the current tab panel.'],
    ['->active()', '', 'Mark the current tab as the active/default tab.'],
    ['->disabled()', '', 'Disable the current tab.'],
    ['->remoteUrl($url)', 'string', 'Load tab content via AJAX on first activation.'],
    ['->badge($key, $count)', 'string, int', 'Add a badge count to a specific tab by key.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.tabs(id)', 'string', 'Get the tabs API for the given element ID.'],
    ['selectTab(key)', 'string', 'Activate a tab by its key.'],
    ['setContent(key, html)', 'string, string', 'Replace a tab panel\'s HTML content.'],
    ['refreshContent(key, url, opts)', 'string, string, ?object', 'Fetch content from a URL and inject into the tab panel.'],
    ['getActiveTab()', '', 'Returns the key of the currently active tab.'],
]) ?>

<?= eventsTable([
    ['m-tab-change', '{key, tab, panel}', 'Fired when the active tab changes.'],
    ['m-tab-content-loaded', '{key, panel}', 'Fired when remote content finishes loading for the first time.'],
    ['m-tab-content-refresh', '{key, panel}', 'Fired when content is refreshed via <code>refreshContent()</code>.'],
]) ?>
