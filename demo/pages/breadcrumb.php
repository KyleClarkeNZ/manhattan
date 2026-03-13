<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-stream') ?> Breadcrumb</h2>
    <p class="m-demo-desc">Hierarchical navigation trail showing where the user is within the application. Server-side rendered, accessible, and dark-mode aware.</p>

    <h3>Multi-level Navigation</h3>
    <div class="m-demo-row">
        <?= $m->breadcrumb('demo-recipe')
            ->home('/', 'Home')
            ->item('Recipes', '/recipes')
            ->item('Baking', '/recipes/baking')
            ->item('Sourdough Bread')
            ->current() ?>
    </div>

    <h3>With Custom Icons</h3>
    <div class="m-demo-row">
        <?= $m->breadcrumb('demo-admin')
            ->item('Admin', '/admin', 'fa-tachometer-alt')
            ->item('Users', '/admin/users', 'fa-users')
            ->item('Edit User')
            ->current() ?>
    </div>

    <h3>Simple Two-Level</h3>
    <div class="m-demo-row">
        <?= $m->breadcrumb('demo-simple')
            ->home('/')
            ->item('Settings')
            ->current() ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->breadcrumb(\'main-nav\')
    ->home(\'/\', \'Home\')
    ->item(\'Recipes\', \'/recipes\')
    ->item(\'Baking\', \'/recipes/baking\')
    ->item(\'Sourdough Bread\')
    ->current() ?>

// With custom icons
<?= $m->breadcrumb(\'admin-nav\')
    ->item(\'Admin\', \'/admin\', \'fa-tachometer-alt\')
    ->item(\'Users\', \'/admin/users\', \'fa-users\')
    ->item(\'Edit User\')
    ->current() ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->breadcrumb($id)', 'string', 'Create a breadcrumb component.'],
    ['->home($url, $text)', 'string, string', 'Add a home item with <code>fa-home</code> icon. Default text: <code>"Dashboard"</code>.'],
    ['->item($text, $url, $icon)', 'string, ?string, ?string', 'Add a breadcrumb item. If <code>$url</code> is null, item is rendered as plain text (current page).'],
    ['->current()', '', 'Explicitly mark the last item as the current page (adds <code>aria-current="page"</code>).'],
]) ?>
