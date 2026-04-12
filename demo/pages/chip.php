<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-circle-dot') ?> Chip</h2>
    <p class="m-demo-desc">
        Soft inline status tags, category indicators, and metadata labels.
        Lighter-weight than <a href="/demo/badge">Badge</a> — uses a soft tinted background
        with coloured text instead of a solid filled pill. Ideal for tags, filters, and
        inline status labels that should blend into the content rather than stand out.
    </p>

    <!-- ============================================================ -->
    <h3>Variants</h3>
    <p class="m-demo-desc">Eight colour variants plus a neutral default.</p>
    <div class="m-demo-pills">
        <?= $m->chip('ch-default',   'Default') ?>
        <?= $m->chip('ch-primary',   'Primary')->primary() ?>
        <?= $m->chip('ch-success',   'Success')->success() ?>
        <?= $m->chip('ch-warning',   'Warning')->warning() ?>
        <?= $m->chip('ch-danger',    'Danger')->danger() ?>
        <?= $m->chip('ch-purple',    'Purple')->purple() ?>
        <?= $m->chip('ch-secondary', 'Secondary')->secondary() ?>
        <?= $m->chip('ch-info',      'Info')->info() ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->chip(\'id\', \'Default\') ?>
<?= $m->chip(\'id\', \'Primary\')->primary() ?>
<?= $m->chip(\'id\', \'Success\')->success() ?>
<?= $m->chip(\'id\', \'Warning\')->warning() ?>
<?= $m->chip(\'id\', \'Danger\')->danger() ?>
<?= $m->chip(\'id\', \'Purple\')->purple() ?>
<?= $m->chip(\'id\', \'Secondary\')->secondary() ?>
<?= $m->chip(\'id\', \'Info\')->info() ?>',
        null
    ) ?>

    <!-- ============================================================ -->
    <h3>With Icons</h3>
    <p class="m-demo-desc">Add a Font Awesome icon with <code>->icon()</code>.</p>
    <div class="m-demo-pills">
        <?= $m->chip('ch-ico-active',   'Active')->success()->icon('fa-circle-check') ?>
        <?= $m->chip('ch-ico-draft',    'Draft')->secondary()->icon('fa-pencil') ?>
        <?= $m->chip('ch-ico-pending',  'Pending')->warning()->icon('fa-clock') ?>
        <?= $m->chip('ch-ico-rejected', 'Rejected')->danger()->icon('fa-circle-xmark') ?>
        <?= $m->chip('ch-ico-featured', 'Featured')->primary()->icon('fa-star') ?>
        <?= $m->chip('ch-ico-php',      'PHP')->purple()->icon('fa-code') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->chip(\'statusActive\',  \'Active\')->success()->icon(\'fa-circle-check\') ?>
<?= $m->chip(\'statusDraft\',   \'Draft\')->secondary()->icon(\'fa-pencil\') ?>
<?= $m->chip(\'statusPending\', \'Pending\')->warning()->icon(\'fa-clock\') ?>

// Raw HTML (no PHP helper)
<span class="m-chip m-chip-success">
    <i class="fas fa-circle-check"></i> Active
</span>',
        null
    ) ?>

    <!-- ============================================================ -->
    <h3>Inline in Content</h3>
    <p class="m-demo-desc">
        Chips sit naturally inline with text, making them ideal for tagging items inside
        tables, cards, and list rows.
    </p>
    <div style="display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--m-surface,#fff);border:1px solid var(--m-border,#e0e0e0);border-radius:8px;">
            <strong style="flex:1;font-size:14px;">Homepage redesign</strong>
            <?= $m->chip('ch-ctx-design',   'Design')->primary()->icon('fa-palette') ?>
            <?= $m->chip('ch-ctx-frontend', 'Frontend')->info()->icon('fa-code') ?>
            <?= $m->chip('ch-ctx-inprog',   'In Progress')->warning()->icon('fa-clock') ?>
        </div>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--m-surface,#fff);border:1px solid var(--m-border,#e0e0e0);border-radius:8px;">
            <strong style="flex:1;font-size:14px;">API authentication</strong>
            <?= $m->chip('ch-ctx-backend', 'Backend')->purple()->icon('fa-server') ?>
            <?= $m->chip('ch-ctx-done',    'Done')->success()->icon('fa-circle-check') ?>
        </div>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--m-surface,#fff);border:1px solid var(--m-border,#e0e0e0);border-radius:8px;">
            <strong style="flex:1;font-size:14px;">Legacy payment gateway</strong>
            <?= $m->chip('ch-ctx-depr',    'Deprecated')->danger()->icon('fa-triangle-exclamation') ?>
            <?= $m->chip('ch-ctx-review',  'Needs Review')->secondary()->icon('fa-magnifying-glass') ?>
        </div>
    </div>

    <?= demoCodeTabs(
        '// Inside a table row or list item
<td>
    Homepage redesign
    <?= $m->chip(\'c1\', \'Design\')->primary()->icon(\'fa-palette\') ?>
    <?= $m->chip(\'c2\', \'In Progress\')->warning()->icon(\'fa-clock\') ?>
</td>',
        null
    ) ?>

    <!-- ============================================================ -->
    <h3>Badge vs Chip</h3>
    <p class="m-demo-desc">
        <strong>Badge</strong> uses a solid gradient fill and is eye-catching — good for counts,
        alerts, and prominent status calls. <strong>Chip</strong> uses a soft tinted background
        and is subtler — good for tags, categories, and inline metadata that should complement
        the content rather than dominate it.
    </p>
    <div class="m-demo-pills" style="align-items:center;">
        <?= $m->badge('cmp-badge-1', 'Badge')->success()->icon('fa-check') ?>
        <?= $m->chip('cmp-chip-1',  'Chip')->success()->icon('fa-check') ?>
        &nbsp;&nbsp;
        <?= $m->badge('cmp-badge-2', 'Badge')->warning()->icon('fa-clock') ?>
        <?= $m->chip('cmp-chip-2',  'Chip')->warning()->icon('fa-clock') ?>
        &nbsp;&nbsp;
        <?= $m->badge('cmp-badge-3', 'Badge')->danger()->icon('fa-times') ?>
        <?= $m->chip('cmp-chip-3',  'Chip')->danger()->icon('fa-times') ?>
    </div>

</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->chip($id, $text)', 'string, string', 'Create a Chip component.'],
    ['->text($text)', 'string', 'Set chip text.'],
    ['->icon($faIcon)', 'string', 'Add a Font Awesome icon (e.g. <code>\'fa-check\'</code>).'],
    ['->variant($variant)', 'string', 'Set colour variant directly: <code>default|primary|success|warning|danger|purple|secondary|info</code>.'],
    ['->primary()', '', 'Blue tint.'],
    ['->success()', '', 'Green tint.'],
    ['->warning()', '', 'Orange tint.'],
    ['->danger()', '', 'Red tint.'],
    ['->purple()', '', 'Purple tint.'],
    ['->secondary()', '', 'Grey tint.'],
    ['->info()', '', 'Cyan tint.'],
]) ?>

<?= apiTable('CSS Classes', 'php', [
    ['.m-chip', '', 'Base chip styling (display:inline-flex, rounded corners, small padding).'],
    ['.m-chip-default', '', 'Neutral grey tint — used when no variant is specified.'],
    ['.m-chip-primary', '', 'Blue tint.'],
    ['.m-chip-success', '', 'Green tint.'],
    ['.m-chip-warning', '', 'Orange tint.'],
    ['.m-chip-danger', '', 'Red tint.'],
    ['.m-chip-purple', '', 'Purple tint.'],
    ['.m-chip-secondary', '', 'Cool grey tint.'],
    ['.m-chip-info', '', 'Cyan tint.'],
]) ?>
