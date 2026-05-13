<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-images') ?> CarouselBanner</h2>
    <p class="m-demo-desc">
        A full-width animated banner slideshow — ideal for hero sections, promotional banners, and featured
        content areas. Supports <strong>slide</strong> and <strong>fade</strong> animation types, auto-play
        with a configurable interval, swipe gestures on mobile, keyboard navigation, lazy-loaded images,
        a thumbnail navigation strip, and rich overlay content (title, subtitle, CTA button).
    </p>
    <p class="m-demo-desc">
        <strong>Defaults:</strong> animation <code>'slide'</code>, no auto-play, dots <code>'inside'</code>,
        arrows <code>true</code>, loop <code>true</code>, lazy-load <code>true</code>, aspect ratio <code>16/9</code>.
    </p>

    <?php
    // ── Shared slide data ──────────────────────────────────────────────────────
    $demoSlides = [
        [
            'imageUrl'  => 'https://picsum.photos/seed/b1/1200/675',
            'title'     => 'Discover New Horizons',
            'subtitle'  => 'Beautiful landscapes from around the world.',
            'ctaUrl'    => '#',
            'ctaLabel'  => 'Explore',
            'altText'   => 'Mountain landscape at dawn',
            'caption'   => 'Photo: Unsplash / Mountains Collection',
        ],
        [
            'imageUrl'  => 'https://picsum.photos/seed/b2/1200/675',
            'title'     => 'Urban Stories',
            'subtitle'  => 'The pulse of the city, captured in every frame.',
            'ctaUrl'    => '#',
            'ctaLabel'  => 'View Gallery',
            'ctaVariant'=> 'light',
            'altText'   => 'City skyline at night',
        ],
        [
            'imageUrl'  => 'https://picsum.photos/seed/b3/1200/675',
            'title'     => 'Into the Wild',
            'subtitle'  => 'Nature up close — untamed and breathtaking.',
            'ctaUrl'    => '#',
            'ctaLabel'  => 'Learn More',
            'ctaVariant'=> 'secondary',
            'altText'   => 'Dense forest canopy',
            'caption'   => 'Photographed in Fiordland National Park, NZ',
        ],
        [
            'imageUrl'  => 'https://picsum.photos/seed/b4/1200/675',
            'title'     => 'Ocean Dreams',
            'altText'   => 'Turquoise ocean waves',
        ],
    ];
    ?>

    <!-- ── Example 1: Default (slide animation, inside dots, arrows) ─────── -->
    <h3>Default — slide animation</h3>
    <p class="m-demo-desc">
        Four slides with slide animation, inside dots, and arrows.
        No auto-play by default — press the arrows or swipe on mobile.
    </p>
    <div class="m-demo-row">
        <?php
        echo $m->carouselBanner('demoBanner1')
            ->slides($demoSlides)
            ->animation('slide')
            ->dots('inside')
            ->arrows(true)
            ->loop(true);
        ?>
    </div>

    <?= demoCodeTabs(
        '$slides = [
    [
        \'imageUrl\' => \'/assets/banner1.jpg\',
        \'title\'    => \'Discover New Horizons\',
        \'subtitle\' => \'Beautiful landscapes from around the world.\',
        \'ctaUrl\'   => \'/explore\',
        \'ctaLabel\' => \'Explore\',
        \'altText\'  => \'Mountain landscape at dawn\',
    ],
    [
        \'imageUrl\'   => \'/assets/banner2.jpg\',
        \'title\'      => \'Urban Stories\',
        \'ctaVariant\' => \'light\',
        \'ctaUrl\'     => \'/gallery\',
        \'ctaLabel\'   => \'View Gallery\',
    ],
    // ...
];
echo $m->carouselBanner(\'heroBanner\')
    ->slides($slides)
    ->animation(\'slide\')
    ->dots(\'inside\')
    ->arrows(true)
    ->loop(true);',
        'document.addEventListener(\'DOMContentLoaded\', function() {
    var banner = m.carouselBanner(\'heroBanner\');

    // Navigate programmatically
    banner.next();
    banner.prev();
    banner.goTo(2);

    // Current index
    console.log(banner.currentIndex()); // 0-based

    // Total slides
    console.log(banner.count());

    // Listen for slide change
    document.getElementById(\'heroBanner\')
        .addEventListener(\'m:cb:change\', function(e) {
            console.log(\'Slide changed to:\', e.detail.index);
        });
});'
    ) ?>

    <!-- ── Example 2: Fade + auto-play + progress bar ────────────────────── -->
    <h3>Fade animation with auto-play</h3>
    <p class="m-demo-desc">
        <code>->animation('fade')</code> crossfades between slides.
        <code>->autoPlay(4000)</code> advances automatically every 4 seconds.
        A progress bar shows remaining time. Auto-play pauses on hover.
    </p>
    <div class="m-demo-row">
        <?php
        echo $m->carouselBanner('demoBanner2')
            ->slides($demoSlides)
            ->animation('fade')
            ->animationSpeed(700)
            ->autoPlay(4000)
            ->dots('inside')
            ->arrows(true)
            ->pauseOnHover(true)
            ->loop(true);
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carouselBanner(\'fadeBanner\')
    ->slides($slides)
    ->animation(\'fade\')
    ->animationSpeed(700)    // 700 ms crossfade
    ->autoPlay(4000)         // advance every 4 s
    ->pauseOnHover(true)     // pause on hover (default)
    ->dots(\'inside\')
    ->arrows(true)
    ->loop(true);',
        'document.addEventListener(\'DOMContentLoaded\', function() {
    var banner = m.carouselBanner(\'fadeBanner\');

    // Control auto-play manually
    banner.pause();   // pause
    banner.play();    // resume (uses original interval)
    banner.stop();    // stop permanently

    // Auto-play events
    document.getElementById(\'fadeBanner\')
        .addEventListener(\'m:cb:play\',  function(e) { console.log(\'playing, interval:\', e.detail.interval); });
    document.getElementById(\'fadeBanner\')
        .addEventListener(\'m:cb:pause\', function() { console.log(\'paused\'); });
});'
    ) ?>

    <!-- ── Example 3: Dots below, no arrows ──────────────────────────────── -->
    <h3>Dots below, no arrows</h3>
    <p class="m-demo-desc">
        <code>->dots('below')</code> places the dot indicators below the banner frame.
        <code>->arrows(false)</code> hides the prev/next buttons — navigation is dot and swipe only.
    </p>
    <div class="m-demo-row">
        <?php
        echo $m->carouselBanner('demoBanner3')
            ->slides(array_slice($demoSlides, 0, 3))
            ->animation('slide')
            ->dots('below')
            ->arrows(false)
            ->loop(true)
            ->aspectRatio('21/9');
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carouselBanner(\'wideBanner\')
    ->slides($slides)
    ->animation(\'slide\')
    ->dots(\'below\')
    ->arrows(false)
    ->aspectRatio(\'21/9\');  // ultra-wide cinematic ratio'
    ) ?>

    <!-- ── Example 4: Thumbnail strip ────────────────────────────────────── -->
    <h3>Thumbnail navigation strip</h3>
    <p class="m-demo-desc">
        <code>->thumbs(true)</code> renders a scrollable thumbnail row below the banner.
        Clicking a thumbnail navigates to that slide. The active thumbnail is highlighted.
    </p>
    <div class="m-demo-row">
        <?php
        echo $m->carouselBanner('demoBanner4')
            ->slides($demoSlides)
            ->animation('slide')
            ->animationSpeed(400)
            ->dots('none')
            ->arrows(true)
            ->thumbs(true)
            ->loop(true);
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carouselBanner(\'thumbBanner\')
    ->slides($slides)
    ->thumbs(true)       // show thumbnail strip
    ->dots(\'none\')       // hide dot indicators
    ->arrows(true)
    ->animationSpeed(400);'
    ) ?>

    <!-- ── Example 5: Centre overlay, max height ─────────────────────────── -->
    <h3>Centred overlay, max-height, no loop</h3>
    <p class="m-demo-desc">
        Individual slides can have their <code>overlayPosition</code> set to
        <code>'center'</code> for a centred card-style overlay.
        <code>->maxHeight('400px')</code> caps the banner height regardless of aspect ratio.
        <code>->loop(false)</code> disables wrap-around — arrows dim at the first/last slide.
    </p>
    <div class="m-demo-row">
        <?php
        $centreSlides = [
            [
                'imageUrl'        => 'https://picsum.photos/seed/b5/1200/675',
                'title'           => 'Centered Overlay',
                'subtitle'        => 'Text and CTA floated to the centre of the slide.',
                'ctaUrl'          => '#',
                'ctaLabel'        => 'Get Started',
                'altText'         => 'Abstract geometric pattern',
                'overlayPosition' => 'center',
            ],
            [
                'imageUrl'        => 'https://picsum.photos/seed/b6/1200/675',
                'title'           => 'Top Left Overlay',
                'subtitle'        => 'Overlay positioned at the top.',
                'ctaUrl'          => '#',
                'ctaLabel'        => 'Discover',
                'altText'         => 'Aerial view of coastline',
                'overlayPosition' => 'top-left',
                'ctaVariant'      => 'light',
            ],
            [
                'imageUrl'        => 'https://picsum.photos/seed/b7/1200/675',
                'title'           => 'Image Only',
                'altText'         => 'Rolling green hills',
            ],
        ];
        echo $m->carouselBanner('demoBanner5')
            ->slides($centreSlides)
            ->animation('fade')
            ->animationSpeed(450)
            ->maxHeight('360px')
            ->dots('inside')
            ->arrows(true)
            ->loop(false);
        ?>
    </div>

    <?= demoCodeTabs(
        '$slides = [
    [
        \'imageUrl\'        => \'/assets/banner.jpg\',
        \'title\'           => \'Centred Overlay\',
        \'subtitle\'        => \'Text floated to the middle of the slide.\',
        \'ctaUrl\'          => \'/start\',
        \'ctaLabel\'        => \'Get Started\',
        \'overlayPosition\' => \'center\',  // bottom-left | bottom-center | center | top-left
    ],
    // ...
];
echo $m->carouselBanner(\'heroBanner\')
    ->slides($slides)
    ->animation(\'fade\')
    ->maxHeight(\'360px\')   // cap height (overrides aspect ratio)
    ->loop(false);          // no wrap-around'
    ) ?>

    <!-- ── Example 6: Auto-play, below dots, lazy load off ───────────────── -->
    <h3>Auto-play, below dots</h3>
    <p class="m-demo-desc">
        <code>->autoPlay(3500)</code> auto-advances every 3.5 s. Dots are placed below
        (<code>'below'</code>). <code>->lazyLoad(false)</code> pre-loads all images eagerly.
    </p>
    <div class="m-demo-row">
        <?php
        echo $m->carouselBanner('demoBanner6')
            ->slides($demoSlides)
            ->animation('slide')
            ->autoPlay(3500)
            ->dots('below')
            ->arrows(true)
            ->lazyLoad(false)
            ->loop(true);
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carouselBanner(\'promoBanner\')
    ->slides($slides)
    ->autoPlay(3500)
    ->dots(\'below\')
    ->lazyLoad(false)   // eager-load all images
    ->loop(true);'
    ) ?>

    <!-- ── API Tables ──────────────────────────────────────────────────────── -->

    <?= apiTable('PHP Methods (Fluent)', 'php', [
        ['$m->carouselBanner($id)',                       'string',              'Create a new CarouselBanner instance.'],
        ['->slide($imageUrl, $title, $subtitle, $ctaUrl, $ctaLabel, $altText, $ctaVariant, $thumbUrl, $overlayPosition, $color, $caption)', 'mixed', 'Add a single slide. Only <code>$imageUrl</code> is required.'],
        ['->slides($array)',                              'array',               'Add multiple slides from an array (keys: imageUrl, title, subtitle, ctaUrl, ctaLabel, altText, ctaVariant, thumbUrl, overlayPosition, color, <strong>caption</strong>).'],
        ['->animation($type)',                            "'slide'|'fade'",     "Animation type. Default: <code>'slide'</code>."],
        ['->animationSpeed($ms)',                         'int',                 'Transition duration in milliseconds. Default: <code>500</code>.'],
        ['->autoPlay($ms)',                               'int',                 'Auto-advance interval in ms. <code>0</code> = disabled (default).'],
        ['->pauseOnHover($bool)',                         'bool',                'Pause auto-play on hover/focus. Default: <code>true</code>.'],
        ['->dots($position)',                             "'inside'|'below'|'none'", "Dot indicator position. Default: <code>'inside'</code>."],
        ['->arrows($bool)',                               'bool',                'Show prev/next arrow buttons. Default: <code>true</code>.'],
        ['->loop($bool)',                                 'bool',                'Wrap from last slide to first. Default: <code>true</code>.'],
        ['->lazyLoad($bool)',                             'bool',                'Lazy-load non-visible slides. Default: <code>true</code>.'],
        ['->thumbs($bool)',                               'bool',                'Show thumbnail navigation strip. Default: <code>false</code>.'],
        ['->aspectRatio($ratio)',                         'string',              "CSS aspect-ratio of the banner. Default: <code>'16/9'</code>. E.g. <code>'21/9'</code>, <code>'4/1'</code>."],
        ['->maxHeight($css)',                             'string',              'Cap the banner height (CSS value, e.g. <code>\'500px\'</code>, <code>\'60vh\'</code>). Overrides aspect ratio.'],
        ['->startIndex($n)',                              'int',                 'Initial active slide index (0-based). Default: <code>0</code>.'],
    ]) ?>

    <?= apiTable('Per-slide array keys', 'php', [
        ['imageUrl',        'string',       '<strong>Required.</strong> URL of the slide image.'],
        ['title',           'string|null',  'Overlay heading text.'],
        ['subtitle',        'string|null',  'Overlay secondary text, shown below the title.'],
        ['caption',         'string|null',  'Small italic caption shown below the CTA — ideal for photo credits or source notes.'],
        ['ctaUrl',          'string|null',  'CTA button href. Button not rendered when omitted.'],
        ['ctaLabel',        'string|null',  'CTA button text. Default: <code>\'Learn More\'</code>.'],
        ['ctaVariant',      'string',       "CTA style: <code>'primary'</code> (default), <code>'secondary'</code>, <code>'light'</code>."],
        ['altText',         'string|null',  'Image alt text. Falls back to <code>title</code> when omitted.'],
        ['thumbUrl',        'string|null',  'Thumbnail image URL for the thumb strip. Falls back to <code>imageUrl</code>.'],
        ['overlayPosition', 'string',       "Text overlay position: <code>'bottom-left'</code> (default), <code>'bottom-center'</code>, <code>'center'</code>, <code>'top-left'</code>."],
        ['color',           'string|null',  'CSS colour applied to all overlay text (e.g. <code>\'#fff\'</code>). Optional.'],
    ]) ?>

    <?= apiTable('JS Methods', 'js', [
        ['m.carouselBanner(id)',    '',    'Get the API for an existing CarouselBanner.'],
        ['banner.goTo(index)',      'int', 'Navigate to a slide by 0-based index.'],
        ['banner.prev()',           '',    'Go to the previous slide.'],
        ['banner.next()',           '',    'Go to the next slide.'],
        ['banner.currentIndex()',   'int', 'Returns the current slide index (0-based).'],
        ['banner.count()',          'int', 'Returns the total number of slides.'],
        ['banner.play()',           '',    'Start (or restart) auto-play.'],
        ['banner.pause()',          '',    'Pause auto-play.'],
        ['banner.stop()',           '',    'Stop auto-play permanently.'],
    ]) ?>

    <?= eventsTable([
        ['m:cb:change', "{ index: n, total: n }",  'Fired on the banner element when the active slide changes.'],
        ['m:cb:play',   "{ interval: n }",          'Fired when auto-play starts or restarts.'],
        ['m:cb:pause',  '{}',                        'Fired when auto-play pauses.'],
    ]) ?>

</div>
