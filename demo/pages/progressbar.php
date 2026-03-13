<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-tasks') ?> ProgressBar</h2>
    <p class="m-demo-desc">Linear progress indicator with label, percentage display, colour variants, stripes, animation, and segmented display.</p>

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

    <h3>Segmented Progress</h3>
    <p class="m-demo-desc">Display multiple segments in a single progress bar, each with its own color.</p>
    <div class="m-demo-row" style="flex-direction:column; gap:1rem; max-width:500px;">
        <?= $m->progressBar('demo-pb-segments')->max(100)->label('Project Status')->showPercent()
            ->segments([
                ['value' => 45, 'variant' => 'success', 'label' => 'Completed'],
                ['value' => 25, 'variant' => 'warning', 'label' => 'In Progress'],
                ['value' => 10, 'variant' => 'danger', 'label' => 'Blocked']
            ]) ?>
        
        <?= $m->progressBar('demo-pb-storage')->max(1000)->label('Storage Usage (800 GB of 1 TB)')
            ->segments([
                ['value' => 400, 'variant' => 'primary', 'label' => 'Documents'],
                ['value' => 250, 'variant' => 'purple', 'label' => 'Media'],
                ['value' => 150, 'variant' => 'success', 'label' => 'Backups']
            ]) ?>
    </div>

    <h3>JavaScript API</h3>
    <p class="m-demo-desc">Read and update progress values dynamically with JavaScript.</p>
    <div class="m-demo-row" style="gap:0.5rem;">
        <?= $m->button('demo-pb-js-inc', '+10')->primary()->icon('fa-plus') ?>
        <?= $m->button('demo-pb-js-dec', '-10')->secondary()->icon('fa-minus') ?>
        <?= $m->button('demo-pb-js-complete', 'Complete')->success()->icon('fa-check') ?>
        <?= $m->button('demo-pb-js-reset', 'Reset')->icon('fa-redo') ?>
    </div>
    <div style="max-width:500px; margin-top:1rem;">
        <?= $m->progressBar('demo-pb-js')->value(40)->max(100)->label('Controlled progress')->showPercent() ?>
    </div>

    <div class="m-demo-output" id="progressbar-output">Use the buttons above to control the progress bar...</div>

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
    ->showPercent() ?>

// Segmented progress bar
<?= $m->progressBar(\'project\')->max(100)
    ->label(\'Project Status\')->showPercent()
    ->segments([
        [\'value\' => 45, \'variant\' => \'success\', \'label\' => \'Done\'],
        [\'value\' => 25, \'variant\' => \'warning\', \'label\' => \'In Progress\'],
        [\'value\' => 10, \'variant\' => \'danger\', \'label\' => \'Blocked\']
    ]) ?>',
        '// Get progress bar instance
var pb = m.progressBar(\'myProgress\');

// Read values
var current = pb.getValue();    // Get current value
var max = pb.getMax();          // Get max value
var pct = pb.getPercent();      // Get percentage (0-100)

// Update values
pb.setValue(75);                // Set to 75
pb.setValue(50, false);         // Set without animation

// Increment/decrement
pb.increment(10);               // Add 10
pb.decrement(5);                // Subtract 5

// Complete or reset
pb.complete();                  // Set to max (100%)
pb.reset();                     // Set to 0

// Change max value
pb.setMax(200);                 // Recalculates percentage

// Listen for changes
document.getElementById(\'myProgress\')
    .addEventListener(\'m:progressbar:change\', function(e) {
        console.log(\'Changed:\', e.detail);
        // { oldValue, newValue, percent, max }
    });

// Complete event
document.getElementById(\'myProgress\')
    .addEventListener(\'m:progressbar:complete\', function(e) {
        console.log(\'Progress complete!\');
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->progressBar($id)', 'string', 'Create a ProgressBar component.'],
    ['->value($v)', 'float', 'Current progress value (min: 0).'],
    ['->max($m)', 'float', 'Maximum value (default: <code>100</code>, min: 1).'],
    ['->label($text)', 'string', 'Text label shown above the bar.'],
    ['->showPercent($show)', 'bool', 'Show percentage next to the label (default: <code>false</code>).'],
    ['->variant($v)', 'string', 'Colour: <code>primary</code>, <code>success</code>, <code>warning</code>, <code>danger</code>, <code>purple</code>.'],
    ['->primary()', '', 'Shorthand for <code>->variant(\'primary\')</code>.'],
    ['->success()', '', 'Shorthand for <code>->variant(\'success\')</code>.'],
    ['->warning()', '', 'Shorthand for <code>->variant(\'warning\')</code>.'],
    ['->danger()', '', 'Shorthand for <code>->variant(\'danger\')</code>.'],
    ['->striped($s)', 'bool', 'Add diagonal stripe pattern.'],
    ['->animated($a)', 'bool', 'Animate stripes (requires <code>->striped()</code>).'],
    ['->segments($segs)', 'array', 'Array of segments: <code>[[\'value\' => 30, \'variant\' => \'success\', \'label\' => \'Done\'], ...]</code>'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.progressBar(id)', 'string', 'Get ProgressBar instance.'],
    ['getValue()', '', 'Returns current value as float.'],
    ['getMax()', '', 'Returns max value as float.'],
    ['getPercent()', '', 'Returns percentage (0-100).'],
    ['setValue(val, animate)', 'number, ?bool', 'Set value. animate defaults to <code>true</code>.'],
    ['setMax(max)', 'number', 'Set max value and recalculate percentage.'],
    ['increment(amount)', '?number', 'Add to value (default: 1).'],
    ['decrement(amount)', '?number', 'Subtract from value (default: 1).'],
    ['complete()', '', 'Set to max (100%).'],
    ['reset()', '', 'Set to 0.'],
]) ?>

<?= eventsTable([
    ['m:progressbar:change', '{oldValue, newValue, percent, max}', 'Fired when value changes.'],
    ['m:progressbar:complete', '{value}', 'Fired when <code>complete()</code> is called.'],
    ['m:progressbar:reset', '{}', 'Fired when <code>reset()</code> is called.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var pb = m.progressBar('demo-pb-js');
    
    // +10 button
    var incBtn = document.getElementById('demo-pb-js-inc');
    if (incBtn) {
        incBtn.addEventListener('click', function() {
            pb.increment(10);
            setOutput('progressbar-output', '<strong>Incremented by 10</strong> → Value: ' + pb.getValue() + ', Percent: ' + pb.getPercent().toFixed(1) + '%');
        });
    }
    
    // -10 button
    var decBtn = document.getElementById('demo-pb-js-dec');
    if (decBtn) {
        decBtn.addEventListener('click', function() {
            pb.decrement(10);
            setOutput('progressbar-output', '<strong>Decremented by 10</strong> → Value: ' + pb.getValue() + ', Percent: ' + pb.getPercent().toFixed(1) + '%');
        });
    }
    
    // Complete button
    var completeBtn = document.getElementById('demo-pb-js-complete');
    if (completeBtn) {
        completeBtn.addEventListener('click', function() {
            pb.complete();
            setOutput('progressbar-output', '<strong>Completed!</strong> → Value: ' + pb.getValue());
        });
    }
    
    // Reset button
    var resetBtn = document.getElementById('demo-pb-js-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            pb.reset();
            setOutput('progressbar-output', '<strong>Reset</strong> → Value: 0');
        });
    }
    
    // Listen for change events
    var pbEl = document.getElementById('demo-pb-js');
    if (pbEl) {
        pbEl.addEventListener('m:progressbar:change', function(e) {
            console.log('Progress changed:', e.detail);
        });
        
        pbEl.addEventListener('m:progressbar:complete', function() {
            console.log('Progress complete!');
        });
        
        pbEl.addEventListener('m:progressbar:reset', function() {
            console.log('Progress reset');
        });
    }
});
</script>
