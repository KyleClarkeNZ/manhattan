<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-inbox') ?> EmptyState</h2>
    <p class="m-demo-desc">Zero-data placeholders shown when a list or view has no content yet. Supports link actions, JS click handlers, FAB triggers, and a compact variant for inline panels.</p>

    <h3>Default Variant</h3>
    <div class="m-demo-row">
        <?= $m->emptyState('demo-empty-tasks')
            ->icon('fa-clipboard-list')
            ->title('No tasks yet')
            ->message('Get started by adding your first task. Your tasks will appear here.')
            ->action('Add your first task', '/tasks/create', 'fa-plus') ?>
    </div>

    <h3>JS Click Handler</h3>
    <div class="m-demo-row">
        <?= $m->emptyState('demo-empty-js')
            ->icon('fa-sync')
            ->title('No data loaded')
            ->message('Click below to fetch data from the server.')
            ->actionJs('Load Data', 'alert("Loading data...")', 'fa-download') ?>
    </div>

    <h3>Compact Variant (for Panels)</h3>
    <div class="m-demo-row" style="gap:1rem;align-items:flex-start;">
        <div style="flex:1;min-width:220px;max-width:300px;">
            <?= $m->emptyState('demo-empty-compact-a')
                ->compact()
                ->bordered()
                ->icon('fa-inbox')
                ->title('No items scheduled')
                ->actionFab('Add Task', 'task', 'fa-plus') ?>
        </div>
        <div style="flex:1;min-width:220px;max-width:300px;">
            <?= $m->emptyState('demo-empty-compact-b')
                ->compact()
                ->bordered()
                ->icon('fa-running')
                ->title('No activity tracked')
                ->actionFab('Track', 'activity', 'fa-plus') ?>
        </div>
    </div>

    <?= demoCodeTabs(
        '// Default with link action
<?= $m->emptyState(\'id\')
    ->icon(\'fa-clipboard-list\')
    ->title(\'No tasks yet\')
    ->message(\'Add your first task to get started.\')
    ->action(\'Add Task\', \'/tasks/create\', \'fa-plus\') ?>

// JS click handler
<?= $m->emptyState(\'id2\')
    ->icon(\'fa-sync\')
    ->title(\'No data loaded\')
    ->actionJs(\'Load Data\', \'fetchData()\', \'fa-download\') ?>

// Compact with border
<?= $m->emptyState(\'id3\')
    ->compact()
    ->bordered()
    ->icon(\'fa-inbox\')
    ->title(\'No items scheduled\')
    ->actionFab(\'Add Task\', \'task\', \'fa-plus\') ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->emptyState($id)', 'string', 'Create an empty state component.'],
    ['->icon($faIcon)', 'string', 'Set the large icon displayed above the title.'],
    ['->title($title)', 'string', 'Set the heading text.'],
    ['->message($message)', 'string', 'Set the descriptive message text.'],
    ['->action($label, $url, $icon)', 'string, string, ?string', 'Add a link-style call-to-action button.'],
    ['->actionJs($label, $onClick, $icon)', 'string, string, ?string', 'Add a button with an <code>onclick</code> JS handler.'],
    ['->actionFab($label, $fabAction, $icon)', 'string, string, ?string', 'Add a FAB-trigger button.'],
    ['->compact()', '', 'Use reduced padding for inline/panel usage.'],
    ['->bordered()', '', 'Add a dashed border and background tint.'],
]) ?>
