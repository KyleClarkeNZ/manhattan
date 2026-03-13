<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-tasks') ?> ProgressBar</h2>
    <p class="m-demo-desc">Linear progress indicator with label, percentage display, colour variants, stripes, and animation.</p>

    <h3>Basic Progress</h3>
    <div class="m-demo-row" style="flex-direction:column; gap:1rem; max-width:500px;">
        <?= $m->progressBar('demo-pb-basic')->value(65)->max(100)->label('Upload progress')->showPercent() ?>
    </div>

    <h3>Colour Variants</h3>
    <div class="m-demo-row" style="flex-direction:column; gap:1rem; max-width:500px;">
        <?= $m->progressBar('demo-pb-primary')->value(80)->label('Primary')->primary()->showPercent() ?>
        <?= $m->progressBar('demo-pb-success')->value(100)->label('Success')->success()->showPercent() ?>
        <?= $m->progressBar('demo-pb-warning')->value(55)->label('Warning')->warning()->showPercent() ?>
        <?= $m->progressBar('demo-pb-danger')->value(30)->label('Danger')->danger()->showPercent() ?>
    </div>

    <h3>Striped &amp; Animated</h3>
    <div class="m-demo-row" style="flex-direction:column; gap:1rem; max-width:500px;">
        <?= $m->progressBar('demo-pb-striped')->value(70)->label('Striped')->striped()->showPercent() ?>
        <?= $m->progressBar('demo-pb-animated')->value(50)->label('Animated')->striped()->animated()->showPercent()->success() ?>
    </div>

    <h3>Custom Value/Max</h3>
    <div class="m-demo-row" style="flex-direction:column; gap:1rem; max-width:500px;">
        <?= $m->progressBar('demo-pb-custom')->value(3)->max(5)->label('Tasks completed (3 of 5)')->showPercent()->success() ?>
    </div>

    <?= demoCodeTabs(
        '// Basic with percentage
<?= $m->progressBar(\'upload\')
    ->value(65)->max(100)
    ->label(\'Upload progress\')
    ->showPercent() ?>

// Colour variants
<?= $m->progressBar(\'ok\')->value(100)->success()->label(\'Done\') ?>
<?= $m->progressBar(\'warn\')->value(55)->warning()->label(\'Caution\') ?>
<?= $m->progressBar(\'err\')->value(30)->danger()->label(\'Low\') ?>

// Striped and animated
<?= $m->progressBar(\'busy\')
    ->value(50)->success()
    ->striped()->animated()
    ->label(\'Processing…\')->showPercent() ?>

// Custom value/max
<?= $m->progressBar(\'tasks\')
    ->value(3)->max(5)
    ->label(\'Tasks: 3 of 5\')
    ->showPercent() ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->progressBar($id)', 'string', 'Create a ProgressBar component.'],
    ['->value($v)', 'float', 'Current progress value (min: 0).'],
    ['->max($m)', 'float', 'Maximum value (default: 100, min: 1).'],
    ['->label($text)', 'string', 'Text label shown above the bar.'],
    ['->showPercent($show)', 'bool', 'Show percentage next to the label (default: false).'],
    ['->variant($v)', 'string', 'Colour: <code>primary</code>, <code>success</code>, <code>warning</code>, <code>danger</code>, <code>purple</code>.'],
    ['->primary()', '', 'Shorthand for <code>->variant(\'primary\')</code>.'],
    ['->success()', '', 'Shorthand for <code>->variant(\'success\')</code>.'],
    ['->warning()', '', 'Shorthand for <code>->variant(\'warning\')</code>.'],
    ['->danger()', '', 'Shorthand for <code>->variant(\'danger\')</code>.'],
    ['->striped($s)', 'bool', 'Add diagonal stripe pattern.'],
    ['->animated($a)', 'bool', 'Animate stripes (requires <code>->striped()</code>).'],
]) ?>
