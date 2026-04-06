<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-expand') ?> Lightbox</h2>
    <p class="m-demo-desc">
        A full-screen overlay image viewer. Supports keyboard navigation
        (<kbd>←</kbd> / <kbd>→</kbd> to navigate, <kbd>Esc</kbd> to close),
        click-backdrop-to-close, and can be opened programmatically.
        Can be pre-populated from PHP or supplied at open-time via JavaScript.
    </p>

    <!-- ─────────────────────────────────────────────────────────── -->
    <h3>Standalone Lightbox</h3>
    <p class="m-demo-desc">
        A lightbox pre-loaded with six Unsplash images. Click any thumbnail
        below to open it at that image.
    </p>

    <?php
    $demoImages = [
        ['src' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1600', 'caption' => 'Mountain peaks at sunset'],
        ['src' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1600', 'caption' => 'Forest mist at dawn'],
        ['src' => 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=1600', 'caption' => 'Waterfall in tropical forest'],
        ['src' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1600', 'caption' => 'Tropical beach shoreline'],
        ['src' => 'https://images.unsplash.com/photo-1476231682828-37e571bc172f?w=1600', 'caption' => 'Desert sand dunes'],
        ['src' => 'https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=1600', 'caption' => 'Northern lights over snow'],
    ];
    $thumbImages = [
        'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=200',
        'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=200',
        'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=200',
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=200',
        'https://images.unsplash.com/photo-1476231682828-37e571bc172f?w=200',
        'https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=200',
    ];
    ?>

    <div class="m-demo-row" id="lb-thumb-grid" style="gap:8px;flex-wrap:wrap;">
        <?php foreach ($demoImages as $i => $img): ?>
        <img src="<?= htmlspecialchars($thumbImages[$i]) ?>"
             alt="<?= htmlspecialchars($img['caption']) ?>"
             data-lb-index="<?= $i ?>"
             style="width:100px;height:70px;object-fit:cover;border-radius:4px;cursor:zoom-in;border:2px solid transparent;transition:border-color 0.15s;"
             onmouseover="this.style.borderColor='#118AB2'" onmouseout="this.style.borderColor='transparent'">
        <?php endforeach; ?>
    </div>

    <?php
    $lb = $m->lightbox('demo-lb');
    foreach ($demoImages as $img) {
        $lb->addImage($img['src'], $img['caption']);
    }
    echo $lb;
    ?>

    <div class="m-demo-output" id="lb-output">Click a thumbnail to open the lightbox...</div>

    <?= demoCodeTabs('// PHP: Pre-load images into the lightbox
$lb = $m->lightbox(\'photoLightbox\');
$lb->addImage(\'https://example.com/photo1.jpg\', \'Mountain sunset\')
   ->addImage(\'https://example.com/photo2.jpg\', \'Ocean view\')
   ->addImage(\'https://example.com/photo3.jpg\', \'Forest trail\');
echo $lb;',
'// Open at a specific index
var lb = m.lightbox(\'photoLightbox\');
lb.show(0);   // first image

// Or supply images dynamically at open time
lb.show(2, [
    { src: \'/img/a.jpg\', caption: \'Image A\' },
    { src: \'/img/b.jpg\', caption: \'Image B\' },
    { src: \'/img/c.jpg\', caption: \'Image C\' },
]);

// Navigate
lb.prev();
lb.next();
lb.hide();

// Listen to events
document.getElementById(\'photoLightbox\').addEventListener(\'m:lightbox:change\', function(e) {
    console.log(\'Now at index\', e.detail.index);
});') ?>

    <!-- ─────────────────────────────────────────────────────────── -->
    <h3>Single Image Lightbox</h3>
    <p class="m-demo-desc">
        A lightbox can hold a single image — navigation arrows are hidden automatically.
        Useful for enlarging a single photo or diagram.
    </p>

    <div class="m-demo-row">
        <?= $m->button('lb-single-open', 'View Full Size')->secondary()->icon('fa-expand-alt') ?>
    </div>

    <?php
    $lbSingle = $m->lightbox('demo-lb-single')
        ->addImage('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1600', 'Mountain peaks at sunset');
    echo $lbSingle;
    ?>

    <?= demoCodeTabs('// Single image — no navigation arrows shown
echo $m->lightbox(\'singleLb\')
    ->addImage(\'https://example.com/hero.jpg\', \'Hero image\');',
'var lb = m.lightbox(\'singleLb\');
lb.show(0);') ?>

    <!-- ─────────────────────────────────────────────────────────── -->

    <?= apiTable('PHP Methods (Fluent)', 'php', [
        ['$m->lightbox($id)', 'Lightbox', 'Create a Lightbox component.'],
        ['->addImage($src, $caption, $thumb)', 'self', 'Pre-populate with an image. <code>$caption</code> and <code>$thumb</code> are optional.'],
    ]) ?>

    <?= apiTable('JS Methods', 'js', [
        ['m.lightbox(id)', 'object', 'Initialise (or retrieve) lightbox instance.'],
        ['lb.show(index, images)', '', 'Open at <code>index</code>. <code>images</code> is an optional <code>[{src, caption}]</code> array that overrides pre-loaded images.'],
        ['lb.hide()', '', 'Close the lightbox.'],
        ['lb.prev()', '', 'Go to previous image.'],
        ['lb.next()', '', 'Go to next image.'],
        ['lb.getIndex()', 'number', 'Return the current image index.'],
    ]) ?>

    <?= eventsTable([
        ['m:lightbox:open',   '{ index }', 'Fired when the lightbox opens.'],
        ['m:lightbox:close',  '{}',        'Fired when the lightbox closes.'],
        ['m:lightbox:change', '{ index }', 'Fired when the active image changes.'],
    ]) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var lb = m.lightbox('demo-lb');

    // Open gallery lightbox on thumbnail click
    var grid = document.getElementById('lb-thumb-grid');
    if (grid) {
        grid.querySelectorAll('img[data-lb-index]').forEach(function(img) {
            img.addEventListener('click', function() {
                lb.show(parseInt(img.getAttribute('data-lb-index'), 10));
            });
        });
    }

    // Update output on change
    var lbOutput = document.getElementById('lb-output');
    document.getElementById('demo-lb').addEventListener('m:lightbox:open', function(e) {
        if (lbOutput) lbOutput.textContent = 'Lightbox opened at image ' + (e.detail.index + 1) + ' of 6.';
    });
    document.getElementById('demo-lb').addEventListener('m:lightbox:close', function() {
        if (lbOutput) lbOutput.textContent = 'Lightbox closed.';
    });
    document.getElementById('demo-lb').addEventListener('m:lightbox:change', function(e) {
        if (lbOutput) lbOutput.textContent = 'Navigated to image ' + (e.detail.index + 1) + ' of 6.';
    });

    // Single image demo
    var lbSingle = m.lightbox('demo-lb-single');
    var openSingle = document.getElementById('lb-single-open');
    if (openSingle) {
        openSingle.addEventListener('click', function() {
            lbSingle.show(0);
        });
    }
});
</script>
