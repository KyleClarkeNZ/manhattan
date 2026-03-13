<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-tag') ?> Label</h2>
    <p class="m-demo-desc">Subtle text indicators for status tags, categories, and metadata. Lighter-weight than badges.</p>

    <h3>Variants</h3>
    <div class="m-demo-pills">
        <?= $m->label('l-primary', 'Primary')->primary()->icon('fa-info-circle') ?>
        <?= $m->label('l-success', 'Success')->success()->icon('fa-check') ?>
        <?= $m->label('l-warning', 'Warning')->warning()->icon('fa-exclamation-triangle') ?>
        <?= $m->label('l-danger', 'Danger')->danger()->icon('fa-times-circle') ?>
        <?= $m->label('l-purple', 'Purple')->purple()->icon('fa-star') ?>
        <?= $m->label('l-secondary', 'Secondary')->secondary()->icon('fa-tag') ?>
    </div>

    <h3>Plain Labels</h3>
    <div class="m-demo-pills">
        <?= $m->label('l-plain-1', 'Active')->success() ?>
        <?= $m->label('l-plain-2', 'Archived')->secondary() ?>
        <?= $m->label('l-plain-3', 'Urgent')->danger() ?>
    </div>

    <?= demoCodeTabs(
        '// Using the component helper
<?= $m->label(\'id\', \'Primary\')->primary()->icon(\'fa-info-circle\') ?>
<?= $m->label(\'id\', \'Active\')->success() ?>

// Using raw CSS classes
<span class="m-label m-label-primary">
    <i class="fas fa-info-circle"></i> Primary
</span>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->label($id, $text)', 'string, string', 'Create a label component.'],
    ['->text($text)', 'string', 'Set label text.'],
    ['->icon($faIcon)', 'string', 'Add a Font Awesome icon.'],
    ['->variant($variant)', 'string', 'Set the colour variant.'],
    ['->primary()', '', 'Apply primary styling.'],
    ['->success()', '', 'Apply success styling.'],
    ['->warning()', '', 'Apply warning styling.'],
    ['->danger()', '', 'Apply danger styling.'],
    ['->purple()', '', 'Apply purple styling.'],
    ['->secondary()', '', 'Apply secondary styling.'],
]) ?>

<?= apiTable('CSS Classes', 'php', [
    ['.m-label', '', 'Base label styling.'],
    ['.m-label-primary', '', 'Primary colour.'],
    ['.m-label-success', '', 'Green colour.'],
    ['.m-label-warning', '', 'Orange colour.'],
    ['.m-label-danger', '', 'Red colour.'],
    ['.m-label-purple', '', 'Purple colour.'],
    ['.m-label-secondary', '', 'Grey colour.'],
    ['.m-label-default', '', 'Default (neutral) colour.'],
]) ?>
