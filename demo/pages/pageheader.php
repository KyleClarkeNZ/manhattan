<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-heading') ?> PageHeader</h2>
    <p class="m-demo-desc">Standardised page title area combining an optional breadcrumb, icon, heading, and subtitle into a single reusable component.</p>

    <h3>Full Example</h3>
    <?php
    $bc = $m->breadcrumb('demo-ph-bc')->home('/', 'Home')->item('Components', '/demo')->item('Library')->current();
    ?>
    <div class="m-demo-row">
        <?= $m->pageHeader('demo-ph')
            ->breadcrumb($bc)
            ->icon('fa-book')
            ->title('Component Library')
            ->subtitle('Browse all available Manhattan UI components.') ?>
    </div>

    <h3>Minimal (Title Only)</h3>
    <div class="m-demo-row">
        <?= $m->pageHeader('demo-ph-min')
            ->title('Dashboard') ?>
    </div>

    <h3>With Icon and Subtitle</h3>
    <div class="m-demo-row">
        <?= $m->pageHeader('demo-ph-icon')
            ->icon('fa-cog')
            ->title('Settings')
            ->subtitle('Manage your account preferences.') ?>
    </div>

    <?= demoCodeTabs(
        '<?php
$bc = $m->breadcrumb(\'nav\')
    ->home(\'/\', \'Home\')
    ->item(\'Library\')
    ->current();
?>
<?= $m->pageHeader(\'ph\')
    ->breadcrumb($bc)
    ->icon(\'fa-book\')
    ->title(\'Page Title\')
    ->subtitle(\'Optional subtitle below the heading.\') ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->pageHeader($id)', 'string', 'Create a page header component.'],
    ['->title($title)', 'string', 'Set the main heading text.'],
    ['->subtitle($subtitle)', 'string', 'Set the subtitle text below the heading.'],
    ['->icon($faIcon)', 'string', 'Set a Font Awesome icon next to the title.'],
    ['->breadcrumb($breadcrumb)', 'Breadcrumb', 'Embed a Breadcrumb component above the title.'],
]) ?>
