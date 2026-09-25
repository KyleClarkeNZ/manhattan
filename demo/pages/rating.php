<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-star') ?> Rating</h2>
    <p class="m-demo-desc">Star-based rating component. Supports read-only display, interactive editing, half-star increments, and keyboard navigation.</p>

    <h3>Read-Only</h3>
    <div class="m-demo-row" style="gap:2rem;">
        <?= $m->rating('demo-rating-ro')->value(3.5)->max(5)->halfStars()->readonly()->label('Product') ?>
        <?= $m->rating('demo-rating-ro2')->value(4)->max(5)->readonly()->label('Service') ?>
    </div>

    <h3>Interactive</h3>
    <div class="m-demo-row" style="gap:2rem;">
        <?= $m->rating('demo-rating-int')->value(0)->max(5)->label('Rate this') ?>
    </div>

    <h3>Sizes</h3>
    <div class="m-demo-row" style="gap:2rem; align-items:center;">
        <?= $m->rating('demo-rating-sm')->value(3)->max(5)->readonly()->sm()->label('Small') ?>
        <?= $m->rating('demo-rating-md')->value(3)->max(5)->readonly()->label('Medium') ?>
        <?= $m->rating('demo-rating-lg')->value(3)->max(5)->readonly()->lg()->label('Large') ?>
    </div>

    <h3>Custom Colour</h3>
    <div class="m-demo-row" style="gap:2rem;">
        <?= $m->rating('demo-rating-danger')->value(2)->max(5)->readonly()->color('danger')->label('Danger') ?>
        <?= $m->rating('demo-rating-success')->value(4)->max(5)->readonly()->color('success')->label('Success') ?>
    </div>

    <h3>Aggregate (Community Average)</h3>
    <p class="m-demo-desc">Shows the average until the user hovers, then lets them set their own rating. Update the label yourself.</p>
    <div class="m-demo-row" style="gap:1rem; align-items:center;">
        <?= $m->rating('demo-rating-agg')->aggregate(4.3, 128)->max(5) ?>
        <span style="font-size:13px; color:#888;">4.3 (128 ratings)</span>
    </div>

    <div class="m-demo-output" id="rating-output">Click a star to rate...</div>

    <?= demoCodeTabs(
        '// Read-only with half stars
<?= $m->rating(\'productRating\')
    ->value(3.5)->max(5)
    ->halfStars()->readonly()
    ->label(\'Product\') ?>

// Interactive
<?= $m->rating(\'myRating\')
    ->value(0)->max(5)
    ->label(\'Rate this\')
    ->onChange(\'handleRating\') ?>

// Sizes
<?= $m->rating(\'sm\')->value(3)->max(5)->sm() ?>
<?= $m->rating(\'lg\')->value(3)->max(5)->lg() ?>

// Community average; hover to rate
<?= $m->rating(\'avgRating\')->aggregate(4.3, 128) ?>',
        '// Get rating instance  
var r = m.rating(\'myRating\');

// Get/set value
var val = r.getValue();
r.setValue(4);

// Aggregate mode: the user\'s own pick, and a refreshed average
r.getUserValue();
r.setAggregate(4.4, 129);

// Listen for changes
document.getElementById(\'myRating\')
    .addEventListener(\'m-rating-change\', function(e) {
        console.log(\'New rating:\', e.detail.value);
    });

// onChange callback (via PHP)
function handleRating(value, element) {
    console.log(\'Rated:\', value);
}'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->rating($id)', 'string', 'Create a rating component.'],
    ['->value($v)', 'float', 'Initial rating value.'],
    ['->max($m)', 'int', 'Number of stars (default: 5).'],
    ['->readonly($ro)', 'bool', 'Make display-only (default: false).'],
    ['->halfStars($half)', 'bool', 'Enable half-star rendering (default: false).'],
    ['->label($text)', 'string', 'Label text beside the stars.'],
    ['->size($s)', 'string', 'Size: <code>sm</code>, <code>md</code>, <code>lg</code>.'],
    ['->sm()', '', 'Small size shorthand.'],
    ['->lg()', '', 'Large size shorthand.'],
    ['->color($c)', 'string', 'Colour: <code>primary</code>, <code>warning</code>, <code>danger</code>, <code>success</code>, <code>purple</code>.'],
    ['->aggregate($avg, $count)', 'float, int', 'Display a community average; hovering lets the user set their own rating. Value text is suppressed.'],
    ['->onChange($callback)', 'string', 'JS function name or expression called with <code>(value, element)</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.rating(id, overrides)', 'string, ?object', 'Get or create rating instance.'],
    ['getValue()', '', 'Returns the current rating value.'],
    ['setValue(value)', 'number', 'Set the rating value.'],
    ['getUserValue()', '', 'Aggregate mode: the user\'s own rating (0 if none).'],
    ['setAggregate(avg, count)', 'number, int', 'Aggregate mode: update the average after a submission.'],
    ['destroy()', '', 'Clean up event listeners.'],
]) ?>

<?= eventsTable([
    ['m-rating-change', '{value}', 'Fired when the rating value changes (interactive mode).'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('demo-rating-int');
    if (el) {
        el.addEventListener('m-rating-change', function(e) {
            setOutput('rating-output', '<strong>Rating:</strong> ' + e.detail.value + ' / 5');
        });
    }
});
</script>
