<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-icons') ?> Icon</h2>
    <p class="m-demo-desc">Font Awesome icons rendered via the PHP helper or raw <code>&lt;i&gt;</code> tags. Manhattan ships Font Awesome 6 and provides a convenient wrapper.</p>

    <h3>Common Icons</h3>
    <div class="m-demo-pills">
        <span class="m-demo-pill"><?= $m->icon('fa-check') ?> fa-check</span>
        <span class="m-demo-pill"><?= $m->icon('fa-info-circle') ?> fa-info-circle</span>
        <span class="m-demo-pill"><?= $m->icon('fa-exclamation-triangle') ?> fa-exclamation-triangle</span>
        <span class="m-demo-pill"><?= $m->icon('far fa-circle') ?> far fa-circle</span>
        <span class="m-demo-pill"><?= $m->icon('fas fa-spinner fa-spin') ?> fa-spinner fa-spin</span>
        <span class="m-demo-pill"><?= $m->icon('fa-home') ?> fa-home</span>
        <span class="m-demo-pill"><?= $m->icon('fa-user') ?> fa-user</span>
        <span class="m-demo-pill"><?= $m->icon('fa-cog') ?> fa-cog</span>
        <span class="m-demo-pill"><?= $m->icon('fa-trash') ?> fa-trash</span>
        <span class="m-demo-pill"><?= $m->icon('fa-edit') ?> fa-edit</span>
    </div>

    <h3>Style Prefixes</h3>
    <div class="m-demo-pills">
        <span class="m-demo-pill"><?= $m->icon('fas fa-star') ?> fas (solid)</span>
        <span class="m-demo-pill"><?= $m->icon('far fa-star') ?> far (regular)</span>
        <span class="m-demo-pill"><?= $m->icon('fab fa-github') ?> fab (brands)</span>
    </div>

    <?= demoCodeTabs(
        '// PHP helper (preferred)
<?= $m->icon(\'fa-check\') ?>
<?= $m->icon(\'far fa-circle\') ?>
<?= $m->icon(\'fas fa-spinner fa-spin\') ?>

// Raw HTML (also valid)
<i class="fas fa-check"></i>',
        '// JS helper — returns HTML string
var html = m.icon(\'fa-check\');
var html2 = m.icon(\'fa-star\', { style: \'far\' });'
    ) ?>
</div>

<?= apiTable('PHP Methods', 'php', [
    ['$m->icon($faName)', 'string $faName', 'Render an icon. Accepts <code>fa-name</code>, <code>fas fa-name</code>, <code>far fa-name</code>, <code>fab fa-name</code>.'],
    ['->addClass($class)', 'string $class', 'Add extra CSS classes to the icon element.'],
    ['->attr($name, $value)', 'string, ?string', 'Set an HTML attribute on the icon.'],
    ['->data($name, $value)', 'string, ?string', 'Set a <code>data-*</code> attribute.'],
]) ?>

<?= apiTable('JS API', 'js', [
    ['m.icon(faName, options)', 'string, ?object', 'Returns an HTML string for a Font Awesome icon.'],
]) ?>

<?= apiTable('Constructor Options (PHP)', 'php', [
    ['style', 'string', 'Icon style prefix: <code>fas</code> (default), <code>far</code>, <code>fab</code>, <code>fal</code>, <code>fad</code>.'],
    ['ariaHidden', 'bool', 'Set to <code>false</code> to make icon visible to screen readers (default: <code>true</code>).'],
    ['ariaLabel', 'string', 'Adds <code>aria-label</code> and <code>role="img"</code> to the icon.'],
]) ?>
