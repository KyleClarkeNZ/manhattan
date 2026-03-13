<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-tachometer-alt') ?> StatCard</h2>
    <p class="m-demo-desc">Compact metric cards ideal for key numbers, KPIs, and summary statistics.</p>

    <h3>Variants</h3>
    <div class="m-demo-row" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
        <?= $m->statCard('sc-tasks')->icon('fa-clipboard-list')->value('24')->label('Tasks Today')->primary() ?>
        <?= $m->statCard('sc-done')->icon('fa-check-circle')->value('18')->label('Completed')->success()->delta('+3 today')->deltaUp() ?>
        <?= $m->statCard('sc-pending')->icon('fa-clock')->value('6')->label('Overdue')->warning()->delta('-1 this week')->deltaDown() ?>
        <?= $m->statCard('sc-streak')->icon('fa-fire')->value('12')->label('Day Streak')->purple() ?>
    </div>

    <h3>Without Delta</h3>
    <div class="m-demo-row" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
        <?= $m->statCard('sc-users')->icon('fa-users')->value('1,204')->label('Total Users')->primary() ?>
        <?= $m->statCard('sc-revenue')->icon('fa-dollar-sign')->value('$42.5k')->label('Revenue')->success() ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->statCard(\'sc-done\')
    ->icon(\'fa-check-circle\')
    ->value(\'18\')
    ->label(\'Completed\')
    ->success()
    ->delta(\'+3 today\')
    ->deltaUp() ?>

// Simple card without delta
<?= $m->statCard(\'sc-users\')
    ->icon(\'fa-users\')
    ->value(\'1,204\')
    ->label(\'Total Users\')
    ->primary() ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->statCard($id)', 'string', 'Create a stat card component.'],
    ['->value($value)', 'string', 'Set the main metric value (e.g. <code>"42"</code>, <code>"$1.2k"</code>).'],
    ['->label($label)', 'string', 'Set the descriptive label below the value.'],
    ['->icon($faIcon)', 'string', 'Set the Font Awesome icon.'],
    ['->variant($variant)', 'string', 'Set colour variant: <code>primary</code>, <code>success</code>, <code>warning</code>, <code>danger</code>, <code>purple</code>, <code>secondary</code>.'],
    ['->primary()', '', 'Apply primary colour.'],
    ['->success()', '', 'Apply success colour.'],
    ['->warning()', '', 'Apply warning colour.'],
    ['->danger()', '', 'Apply danger colour.'],
    ['->purple()', '', 'Apply purple colour.'],
    ['->delta($text)', 'string', 'Set delta text (e.g. <code>"+5 today"</code>, <code>"-2%"</code>).'],
    ['->deltaUp()', '', 'Show green up-arrow indicator.'],
    ['->deltaDown()', '', 'Show red down-arrow indicator.'],
]) ?>
