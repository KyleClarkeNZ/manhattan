<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-film') ?> Carousel</h2>
    <p class="m-demo-desc">
        A tile-based horizontal scroll carousel with CSS <code>scroll-snap</code>, prev/next navigation buttons,
        and optional dot indicators. Each click of Next/Prev snaps to the adjacent tile.
        Supports server-rendered tiles (PHP array) or client-side loading from a remote JSON endpoint.
    </p>
    <p class="m-demo-desc">
        <strong>Dot placement</strong> defaults to <code>'below'</code>. Use <code>->dots('above')</code>
        or <code>->dots('none')</code> to change or remove them.
    </p>

    <!-- ── Example 1: Dots below (default) ─────────────────────────────── -->
    <h3>Default — dots below</h3>
    <p class="m-demo-desc">Server-rendered tiles, dot indicators positioned below the carousel.</p>
    <div class="m-demo-row">
        <?php
        $tiles1 = [
            ['title' => 'Ocean Sunset',   'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/c1/320/240', 'caption' => 'Photography'],
            ['title' => 'City Lights',    'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/c2/320/240', 'caption' => 'Urban'],
            ['title' => 'Forest Path',    'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/c3/320/240', 'caption' => 'Nature'],
            ['title' => 'Desert Dunes',   'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/c4/320/240', 'caption' => 'Landscape'],
            ['title' => 'Mountain Peak',  'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/c5/320/240', 'caption' => 'Adventure'],
            ['title' => 'River Bend',     'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/c6/320/240', 'caption' => 'Scenic'],
            ['title' => 'Coral Reef',     'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/c7/320/240', 'caption' => 'Underwater'],
            ['title' => 'Aurora Borealis','href' => '#', 'imageUrl' => 'https://picsum.photos/seed/c8/320/240', 'caption' => 'Night Sky'],
        ];
        echo $m->carousel('demoCarousel1')
            ->tiles($tiles1)
            ->dots('below');
        ?>
    </div>

    <?= demoCodeTabs(
        '$tiles = [
    [\'title\' => \'Ocean Sunset\',  \'href\' => \'/photos/1\', \'imageUrl\' => \'/assets/ocean.jpg\',  \'caption\' => \'Photography\'],
    [\'title\' => \'City Lights\',   \'href\' => \'/photos/2\', \'imageUrl\' => \'/assets/city.jpg\',   \'caption\' => \'Urban\'],
    [\'title\' => \'Forest Path\',   \'href\' => \'/photos/3\', \'imageUrl\' => \'/assets/forest.jpg\', \'caption\' => \'Nature\'],
    // ...
];
echo $m->carousel(\'myCarousel\')
    ->tiles($tiles)
    ->dots(\'below\');   // default'
    ) ?>

    <!-- ── Example 2: Dots above ────────────────────────────────────────── -->
    <h3>Dots above</h3>
    <p class="m-demo-desc">Navigate with dots placed above the carousel. Use a wider tile width.</p>
    <div class="m-demo-row">
        <?php
        $tiles2 = [
            ['title' => 'Component Design',  'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/d1/320/240', 'caption' => 'Design'],
            ['title' => 'Code Architecture', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/d2/320/240', 'caption' => 'Engineering'],
            ['title' => 'UI Patterns',       'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/d3/320/240', 'caption' => 'UX'],
            ['title' => 'Typography',        'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/d4/320/240', 'caption' => 'Visual'],
            ['title' => 'Colour Theory',     'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/d5/320/240', 'caption' => 'Design'],
        ];
        echo $m->carousel('demoCarousel2')
            ->tiles($tiles2)
            ->tileWidth('200px')
            ->dots('above');
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carousel(\'topDotsCarousel\')
    ->tiles($tiles)
    ->tileWidth(\'200px\')
    ->dots(\'above\');'
    ) ?>

    <!-- ── Example 3: No dots ───────────────────────────────────────────── -->
    <h3>No dots</h3>
    <p class="m-demo-desc">Carousel with only prev/next buttons — no dot indicators.</p>
    <div class="m-demo-row">
        <?php
        $tiles3 = [
            ['title' => 'Prop: Helmet',  'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/p1/320/240', 'caption' => 'Props'],
            ['title' => 'Prop: Shield',  'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/p2/320/240', 'caption' => 'Props'],
            ['title' => 'Prop: Sword',   'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/p3/320/240', 'caption' => 'Props'],
            ['title' => 'Prop: Armour',  'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/p4/320/240', 'caption' => 'Props'],
            ['title' => 'Prop: Bow',     'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/p5/320/240', 'caption' => 'Props'],
        ];
        echo $m->carousel('demoCarousel3')
            ->tiles($tiles3)
            ->dots('none');
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carousel(\'noDots\')
    ->tiles($tiles)
    ->dots(\'none\');'
    ) ?>

    <!-- ── Example 4: Single tile (no duplicates) ───────────────────────── -->
    <h3>Single tile — buttons / dots hidden</h3>
    <p class="m-demo-desc">When only one tile exists, navigation buttons and dots are automatically hidden.</p>
    <div class="m-demo-row">
        <?php
        echo $m->carousel('demoCarousel4')
            ->tile('Only Item', '#', 'https://picsum.photos/seed/s1/320/240', 'Standalone')
            ->dots('below');
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carousel(\'oneTile\')
    ->tile(\'Only Item\', \'/link\', \'/assets/img.jpg\', \'Subtitle\')
    ->dots(\'below\');'
    ) ?>

    <!-- ── Example 5: No images (empty state tiles) ─────────────────────── -->
    <h3>Tiles without images</h3>
    <p class="m-demo-desc">When no <code>imageUrl</code> is provided, a placeholder icon is shown.</p>
    <div class="m-demo-row">
        <?php
        $tiles5 = [
            ['title' => 'Item Alpha',   'href' => '#', 'caption' => 'Category A'],
            ['title' => 'Item Beta',    'href' => '#', 'caption' => 'Category B'],
            ['title' => 'Item Gamma',   'href' => '#', 'caption' => 'Category C'],
            ['title' => 'Item Delta',   'href' => '#', 'caption' => 'Category D'],
            ['title' => 'Item Epsilon', 'href' => '#'],
        ];
        echo $m->carousel('demoCarousel5')
            ->tiles($tiles5)
            ->tileWidth('140px')
            ->dots('below');
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carousel(\'noImages\')
    ->tiles([
        [\'title\' => \'Item Alpha\', \'href\' => \'/item/1\', \'caption\' => \'Category A\'],
        [\'title\' => \'Item Beta\',  \'href\' => \'/item/2\', \'caption\' => \'Category B\'],
    ])
    ->tileWidth(\'140px\')
    ->dots(\'below\');'
    ) ?>

    <!-- ── Example 6: Remote / Client-side datasource ───────────────────── -->
    <h3>Remote datasource (client-side)</h3>
    <p class="m-demo-desc">
        Pass <code>->remoteUrl()</code> instead of <code>->tiles()</code> to load tiles
        client-side on page load. The endpoint should return
        <code>{ "tiles": [ {title, href, imageUrl, caption}, … ] }</code>.
        A <code>perPage</code> query param is appended when set via <code>->perPage()</code>.
    </p>
    <div class="m-demo-row">
        <?php
        // Demo: uses the local demo API endpoint below (served by demo/index.php)
        echo $m->carousel('demoCarousel6')
            ->remoteUrl('/demo/carouselData')
            ->perPage(8)
            ->dots('below');
        ?>
    </div>
    <div class="m-demo-output" id="demoCarouselRemoteOut">
        <small>Tiles load automatically from <code>/demo/carouselData</code> on page load.</small>
    </div>

    <?= demoCodeTabs(
        'echo $m->carousel(\'ajaxCarousel\')
    ->remoteUrl(\'/api/carousel-tiles\')
    ->perPage(8)
    ->dots(\'below\');',
        '// Endpoint response format:
// GET /api/carousel-tiles?perPage=8
// {
//   "tiles": [
//     { "title": "...", "href": "...", "imageUrl": "...", "caption": "..." },
//     ...
//   ]
// }

// JS API (after DOMContentLoaded):
document.addEventListener(\'DOMContentLoaded\', function () {
    var c = m.carousel(\'ajaxCarousel\');

    // Navigate programmatically
    c.next();
    c.prev();
    c.goTo(3);

    // Inspect state
    console.log(c.current()); // current tile index (0-based)
    console.log(c.count());   // total tiles

    // Reload from a different URL
    c.reload(\'/api/other-tiles\', 10);
});

// Listen for change events
document.getElementById(\'ajaxCarousel\')
    .addEventListener(\'m:carousel:change\', function (e) {
        console.log(\'Active tile:\', e.detail.index);
    });

// Listen for remote load completion
document.getElementById(\'ajaxCarousel\')
    .addEventListener(\'m:carousel:loaded\', function (e) {
        console.log(\'Loaded tiles:\', e.detail.count);
    });'
    ) ?>

    <!-- ── Example 7: JS API demo ────────────────────────────────────────── -->
    <h3>JS API</h3>
    <p class="m-demo-desc">Control the carousel programmatically using <code>m.carousel(id)</code>.</p>
    <div class="m-demo-row">
        <?php
        $tiles7 = [
            ['title' => 'Slide 1', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js1/320/240'],
            ['title' => 'Slide 2', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js2/320/240'],
            ['title' => 'Slide 3', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js3/320/240'],
            ['title' => 'Slide 4', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js4/320/240'],
            ['title' => 'Slide 5', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js5/320/240'],
        ];
        echo $m->carousel('demoCarousel7')
            ->tiles($tiles7)
            ->dots('below');
        ?>
    </div>
    <div class="m-demo-row" style="gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem;">
        <?= $m->button('cJsPrev', 'Prev')->icon('fa-chevron-left') ?>
        <?= $m->button('cJsNext', 'Next')->icon('fa-chevron-right') ?>
        <?= $m->button('cJsGo2', 'Go to #3')->primary()->icon('fa-crosshairs') ?>
    </div>
    <div class="m-demo-output" id="cJsOut">Current tile: —</div>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->carousel($id)', 'string', 'Create a Carousel component.'],
    ['->tile($title, $href, $imageUrl, $caption)', 'string, string, ?string, ?string', 'Add a single tile. <code>imageUrl</code> and <code>caption</code> are optional.'],
    ['->tiles($tiles)', 'array', 'Add multiple tiles at once. Each element: <code>{title, href, imageUrl?, caption?}</code>.'],
    ['->remoteUrl($url)', 'string', 'Client-side remote datasource URL. Response: <code>{"tiles":[…]}</code>. Use instead of <code>->tiles()</code>.'],
    ['->perPage($n)', 'int', 'Tiles per remote fetch (appended as <code>?perPage=N</code>). Default: <code>0</code> (load all).'],
    ['->dots($placement)', 'string', 'Dot indicator placement. <code>\'below\'</code> (default), <code>\'above\'</code>, or <code>\'none\'</code>.'],
    ['->tileWidth($css)', 'string', 'CSS width of each tile. Default: <code>\'160px\'</code>. Accepts any CSS length.'],
    ['->tileGap($px)', 'int', 'Gap between tiles in pixels. Default: <code>12</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.carousel(id)', 'string|Element', 'Get the Carousel API for the given element ID or DOM element.'],
    ['c.goTo(idx)', 'number', 'Navigate to tile at zero-based <code>idx</code>.'],
    ['c.next()', '', 'Navigate to the next tile.'],
    ['c.prev()', '', 'Navigate to the previous tile.'],
    ['c.current()', '', 'Returns the current tile index (0-based).'],
    ['c.count()', '', 'Returns the total number of tiles.'],
    ['c.reload(url?, perPage?)', '', 'Reload tiles from a remote URL. Defaults to configured <code>remoteUrl</code>/<code>perPage</code>.'],
]) ?>

<?= eventsTable([
    ['m:carousel:change', '{ index: number }', 'Fired on the carousel element when the active tile changes.'],
    ['m:carousel:loaded', '{ count: number }', 'Fired on the carousel element after remote tiles have loaded.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── JS API demo ──────────────────────────────────────────────────────
    var c7   = m.carousel('demoCarousel7');
    var out7 = document.getElementById('cJsOut');

    function updateOut7() {
        if (out7 && c7) {
            out7.textContent = 'Current tile: ' + (c7.current() + 1) + ' of ' + c7.count();
        }
    }

    document.getElementById('demoCarousel7')
        .addEventListener('m:carousel:change', function () { updateOut7(); });

    document.getElementById('cJsPrev')
        .addEventListener('click', function () { c7.prev(); });

    document.getElementById('cJsNext')
        .addEventListener('click', function () { c7.next(); });

    document.getElementById('cJsGo2')
        .addEventListener('click', function () { c7.goTo(2); });

    updateOut7();

    // ── Remote demo output ────────────────────────────────────────────────
    var remoteEl = document.getElementById('demoCarousel6');
    if (remoteEl) {
        remoteEl.addEventListener('m:carousel:loaded', function (e) {
            var out = document.getElementById('demoCarouselRemoteOut');
            if (out) {
                out.textContent = 'Loaded ' + e.detail.count + ' tiles from remote endpoint.';
            }
        });
    }
});
</script>
