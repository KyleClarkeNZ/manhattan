<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<?php
// Shared Unsplash images used across demos
$ivImages = [
    ['src' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200', 'thumb' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=200', 'caption' => 'Mountain peaks at sunset'],
    ['src' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1200', 'thumb' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=200', 'caption' => 'Forest mist at dawn'],
    ['src' => 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=1200', 'thumb' => 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=200', 'caption' => 'Waterfall in tropical forest'],
    ['src' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200', 'thumb' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=200', 'caption' => 'Tropical beach shoreline'],
    ['src' => 'https://images.unsplash.com/photo-1476231682828-37e571bc172f?w=1200', 'thumb' => 'https://images.unsplash.com/photo-1476231682828-37e571bc172f?w=200', 'caption' => 'Desert sand dunes'],
    ['src' => 'https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=1200', 'thumb' => 'https://images.unsplash.com/photo-1504701954957-2010ec3bcec1?w=200', 'caption' => 'Northern lights over snow'],
];
?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-images') ?> ImageViewer</h2>
    <p class="m-demo-desc">
        A media gallery with a main display stage and a navigable thumbnail strip.
        Supports images, direct video files, and YouTube links.
        Two layouts are available: <strong>side</strong> (default — vertical thumbnail strip on the left)
        and <strong>below</strong> (horizontal strip beneath the main image).
        Optional auto-advance, forward/back arrows, and lightbox integration are included.
    </p>

    <!-- ─────────────────────────────────────────────────────────── -->
    <h3>Side Layout (Default)</h3>
    <p class="m-demo-desc">
        Thumbnails appear in a scrollable vertical strip on the left. This is the default layout —
        no <code>->layout()</code> call is needed. Clicking the main image opens it in a lightbox.
    </p>

    <?php
    $ivSide = $m->imageViewer('demo-iv-side')
        ->lightbox();

    foreach ($ivImages as $img) {
        $ivSide->addImage($img['src'], $img['thumb'], $img['caption']);
    }
    // Add a YouTube video as the last item
    $ivSide->addVideo('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Rick Astley – Never Gonna Give You Up');

    echo $ivSide;
    ?>

    <div class="m-demo-output" id="iv-side-output">Navigate to see output...</div>

    <?= demoCodeTabs('// Side layout is default — thumbnails on the left
<?= $m->imageViewer(\'gallery\')
    ->addImage(\'https://example.com/img1.jpg\', \'https://example.com/thumb1.jpg\', \'Sunset\')
    ->addImage(\'https://example.com/img2.jpg\', \'https://example.com/thumb2.jpg\', \'Forest\')
    ->addVideo(\'https://youtu.be/dQw4w9WgXcQ\', \'YouTube video\')
    ->lightbox() ?>',
'// JS API
var iv = m.imageviewer(\'gallery\');
iv.goTo(2);       // jump to index 2
iv.prev();        // previous
iv.next();        // next
iv.currentIndex(); // get current index') ?>

    <!-- ─────────────────────────────────────────────────────────── -->
    <h3>Below Layout</h3>
    <p class="m-demo-desc">
        Thumbnails appear in a scrollable horizontal strip below the main image.
        Use <code>->layout(\'below\')</code> to enable this mode.
    </p>

    <?php
    $ivBelow = $m->imageViewer('demo-iv-below')
        ->layout('below');

    foreach ($ivImages as $img) {
        $ivBelow->addImage($img['src'], $img['thumb'], $img['caption']);
    }
    $ivBelow->addVideo('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Rick Astley – Never Gonna Give You Up');

    echo $ivBelow;
    ?>

    <?= demoCodeTabs('// Below layout — thumbnails below the main image
<?= $m->imageViewer(\'galleryBelow\')
    ->layout(\'below\')
    ->addImage(\'https://example.com/img1.jpg\', null, \'Mountain\')
    ->addImage(\'https://example.com/img2.jpg\', null, \'Ocean\') ?>',
'var iv = m.imageviewer(\'galleryBelow\');
iv.goTo(0);') ?>

    <!-- ─────────────────────────────────────────────────────────── -->
    <h3>Auto-Advance</h3>
    <p class="m-demo-desc">
        Enable auto-advance with <code>->autoAdvance()</code> (default: off).
        The interval defaults to 4 000 ms and can be changed with <code>->interval($ms)</code>.
        Clicking a thumbnail or navigation arrow resets the timer.
    </p>

    <div class="m-demo-row">
        <?= $m->button('iv-auto-start', 'Start Auto-Advance')->primary()->icon('fa-play') ?>
        <?= $m->button('iv-auto-stop',  'Stop Auto-Advance')->secondary()->icon('fa-pause') ?>
    </div>

    <?php
    $ivAuto = $m->imageViewer('demo-iv-auto')
        ->interval(2500);

    foreach ($ivImages as $img) {
        $ivAuto->addImage($img['src'], $img['thumb'], $img['caption']);
    }
    echo $ivAuto;
    ?>

    <?= demoCodeTabs('// Auto-advance every 3 seconds (default: 4 000 ms)
<?= $m->imageViewer(\'autoGallery\')
    ->autoAdvance()
    ->interval(3000)
    ->addImage(\'https://example.com/img1.jpg\')
    ->addImage(\'https://example.com/img2.jpg\')
    ->addImage(\'https://example.com/img3.jpg\') ?>',
'var iv = m.imageviewer(\'autoGallery\');
iv.stopAuto();   // pause
iv.startAuto();  // resume') ?>

    <!-- ─────────────────────────────────────────────────────────── -->
    <h3>Custom Thumbnail Size</h3>
    <p class="m-demo-desc">
        Use <code>->thumbSize($width, $height)</code> to control thumbnail dimensions (any CSS length).
        Default is <code>80px × 60px</code>.
    </p>

    <?php
    $ivLargeThumbs = $m->imageViewer('demo-iv-large-thumbs')
        ->thumbSize('110px', '80px');

    foreach (array_slice($ivImages, 0, 4) as $img) {
        $ivLargeThumbs->addImage($img['src'], $img['thumb'], $img['caption']);
    }
    echo $ivLargeThumbs;
    ?>

    <?= demoCodeTabs('// Larger thumbnails
<?= $m->imageViewer(\'bigThumbGallery\')
    ->thumbSize(\'110px\', \'80px\')
    ->addImage(\'https://example.com/img1.jpg\', null, \'Photo 1\')
    ->addImage(\'https://example.com/img2.jpg\', null, \'Photo 2\') ?>',
null) ?>

    <!-- ─────────────────────────────────────────────────────────── -->
    <h3>With Direct Video</h3>
    <p class="m-demo-desc">
        Pass a direct video URL (MP4, WebM, etc.) to <code>->addVideo()</code> to embed a
        native HTML video player as one of the gallery items. A custom thumbnail image can
        be provided as the third parameter.
    </p>

    <?php
    $ivVideo = $m->imageViewer('demo-iv-video');
    foreach (array_slice($ivImages, 0, 3) as $img) {
        $ivVideo->addImage($img['src'], $img['thumb'], $img['caption']);
    }
    // Big Buck Bunny CC video (public domain)
    $ivVideo->addVideo(
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
        'Big Buck Bunny (CC)',
        'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=200'
    );
    echo $ivVideo;
    ?>

    <?= demoCodeTabs('// Direct video file — shows a native <video> player
<?= $m->imageViewer(\'videoGallery\')
    ->addImage(\'https://example.com/cover.jpg\', null, \'Cover photo\')
    ->addVideo(
        \'https://example.com/clip.mp4\',
        \'Product demo clip\',
        \'https://example.com/clip-thumb.jpg\'  // optional custom thumbnail
    ) ?>',
null) ?>

    <!-- ─────────────────────────────────────────────────────────── -->

    <?= apiTable('PHP Methods (Fluent)', 'php', [
        ['$m->imageViewer($id)', 'ImageViewer', 'Create an ImageViewer component.'],
        ['->layout($layout)', 'self', 'Set thumbnail strip position: <code>\'side\'</code> (default) or <code>\'below\'</code>.'],
        ['->addImage($src, $thumb, $caption)', 'self', 'Add an image. <code>$thumb</code> defaults to <code>$src</code>; <code>$caption</code> is optional.'],
        ['->addVideo($src, $caption, $thumb)', 'self', 'Add a video. Accepts a direct URL or YouTube share/watch link. Auto-derives YouTube thumbnail when <code>$thumb</code> is omitted.'],
        ['->lightbox($enabled, $lightboxId)', 'self', 'Enable lightbox on main-image click. Default: <code>false</code>. Omit <code>$lightboxId</code> to auto-generate a sibling Lightbox.'],
        ['->autoAdvance($enabled)', 'self', 'Enable auto-advance slideshow on init. Default: <code>false</code>.'],
        ['->interval($ms)', 'self', 'Auto-advance interval in milliseconds. Default: <code>4000</code>.'],
        ['->height($height)', 'self', 'Stage height (any CSS length, e.g. <code>\'400px\'</code>, <code>\'50vh\'</code>). Default: <code>380px</code>.'],
        ['->thumbSize($width, $height)', 'self', 'Thumbnail dimensions (any CSS length). Default: <code>80px, 60px</code>.'],
    ]) ?>

    <?= apiTable('JS Methods', 'js', [
        ['m.imageviewer(id)', 'object', 'Initialise (or retrieve) ImageViewer instance.'],
        ['iv.goTo(index)', '', 'Navigate to item at <code>index</code>.'],
        ['iv.prev()', '', 'Go to previous item.'],
        ['iv.next()', '', 'Go to next item.'],
        ['iv.currentIndex()', 'number', 'Return the active item index.'],
        ['iv.startAuto()', '', 'Start auto-advance.'],
        ['iv.stopAuto()', '', 'Stop auto-advance.'],
    ]) ?>

    <?= eventsTable([
        ['m:imageviewer:change', '{ index }', 'Fired after the active item changes.'],
    ]) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ivSide = m.imageviewer('demo-iv-side');
    var ivAuto = m.imageviewer('demo-iv-auto');

    // Side viewer output
    var sideOut = document.getElementById('iv-side-output');
    document.getElementById('demo-iv-side').addEventListener('m:imageviewer:change', function(e) {
        if (sideOut) sideOut.textContent = 'Active item: ' + (e.detail.index + 1) + ' / 7';
    });

    // Auto-advance controls
    document.getElementById('iv-auto-start').addEventListener('click', function() {
        ivAuto.startAuto();
    });
    document.getElementById('iv-auto-stop').addEventListener('click', function() {
        ivAuto.stopAuto();
    });
});
</script>
