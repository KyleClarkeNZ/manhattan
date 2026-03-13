<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-certificate') ?> Badge</h2>
    <p class="m-demo-desc">Small status indicators with gradient backgrounds. Use for important status labels, counts, and highlights.</p>

    <h3>Variants</h3>
    <div class="m-demo-pills">
        <?= $m->badge('b-primary', 'Primary')->primary()->icon('fa-star') ?>
        <?= $m->badge('b-success', 'Success')->success()->icon('fa-check') ?>
        <?= $m->badge('b-warning', 'Warning')->warning()->icon('fa-exclamation') ?>
        <?= $m->badge('b-danger', 'Danger')->danger()->icon('fa-times') ?>
        <?= $m->badge('b-purple', 'Purple')->purple()->icon('fa-crown') ?>
        <?= $m->badge('b-secondary', 'Secondary')->secondary()->icon('fa-info') ?>
        <?= $m->badge('b-info', 'Info')->info()->icon('fa-info-circle') ?>
    </div>

    <h3>Without Icons</h3>
    <div class="m-demo-pills">
        <?= $m->badge('b-plain-1', '42')->primary() ?>
        <?= $m->badge('b-plain-2', 'New')->success() ?>
        <?= $m->badge('b-plain-3', 'Draft')->secondary() ?>
    </div>

    <?= demoCodeTabs(
        '// Using the component helper
<?= $m->badge(\'id\', \'Primary\')->primary()->icon(\'fa-star\') ?>
<?= $m->badge(\'id\', \'Success\')->success()->icon(\'fa-check\') ?>
<?= $m->badge(\'id\', \'42\')->primary() ?>

// Using raw CSS classes
<span class="m-badge m-badge-primary">
    <i class="fas fa-star"></i> Primary
</span>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->badge($id, $text)', 'string, string', 'Create a badge component.'],
    ['->text($text)', 'string', 'Set badge text.'],
    ['->icon($faIcon)', 'string', 'Add a Font Awesome icon.'],
    ['->variant($variant)', 'string', 'Set the colour variant.'],
    ['->primary()', '', 'Apply primary variant.'],
    ['->success()', '', 'Apply success (green) variant.'],
    ['->warning()', '', 'Apply warning (orange) variant.'],
    ['->danger()', '', 'Apply danger (red) variant.'],
    ['->purple()', '', 'Apply purple variant.'],
    ['->secondary()', '', 'Apply secondary (grey) variant.'],
    ['->info()', '', 'Apply info (blue) variant.'],
]) ?>

<?= apiTable('CSS Classes', 'php', [
    ['.m-badge', '', 'Base badge styling.'],
    ['.m-badge-primary', '', 'Blue gradient.'],
    ['.m-badge-success', '', 'Green gradient.'],
    ['.m-badge-warning', '', 'Orange gradient.'],
    ['.m-badge-danger', '', 'Red gradient.'],
    ['.m-badge-purple', '', 'Purple gradient.'],
    ['.m-badge-secondary', '', 'Grey gradient.'],
    ['.m-badge-info', '', 'Light blue gradient.'],
]) ?>
