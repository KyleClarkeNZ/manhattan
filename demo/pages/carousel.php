<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-film') ?> Carousel</h2>
    <p class="m-demo-desc">
        A horizontal tile carousel with scroll-snap, prev/next buttons and optional dots.
        Tiles are rendered server-side or loaded from a JSON endpoint. With a single tile, buttons and dots are hidden.
    </p>

    <h3>Default</h3>
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
        echo $m->carousel('demoCarousel1')->tiles($tiles1);
        ?>
    </div>

    <?= demoCodeTabs(
        '$tiles = [
    [\'title\' => \'Ocean Sunset\', \'href\' => \'/photos/1\', \'imageUrl\' => \'/img/ocean.jpg\', \'caption\' => \'Photography\'],
    [\'title\' => \'City Lights\',  \'href\' => \'/photos/2\', \'imageUrl\' => \'/img/city.jpg\',  \'caption\' => \'Urban\'],
];
echo $m->carousel(\'myCarousel\')->tiles($tiles);

// Or one at a time: tile($title, $href, $imageUrl, $caption, $chip, $chipVariant)
echo $m->carousel(\'one\')->tile(\'Only Item\', \'/link\', \'/img.jpg\', \'Subtitle\');'
    ) ?>

    <h3>Dots, Width &amp; Placeholders</h3>
    <p class="m-demo-desc">
        <code>->dots()</code> takes <code>'below'</code> (default), <code>'above'</code> or <code>'none'</code>.
        Tiles without an <code>imageUrl</code> show a placeholder icon.
    </p>
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
            ->dots('none');
        ?>
    </div>

    <?= demoCodeTabs(
        'echo $m->carousel(\'topDots\')->tiles($tiles)->tileWidth(\'200px\')->dots(\'above\');
echo $m->carousel(\'noDots\')->tiles($tiles)->tileWidth(\'140px\')->tileGap(8)->dots(\'none\');'
    ) ?>

    <h3>Chips</h3>
    <p class="m-demo-desc">
        Add <code>chip</code> and optional <code>chipVariant</code> to a tile for a corner label
        (<code>primary</code>, <code>success</code>, <code>warning</code>, <code>danger</code>, <code>purple</code>, <code>info</code>, <code>secondary</code> default).
    </p>
    <div class="m-demo-row">
        <?php
        $tilesChip = [
            ['title' => 'Ocean Sunset',  'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/ch1/320/240', 'caption' => 'Photography', 'chip' => 'Featured',  'chipVariant' => 'primary'],
            ['title' => 'Draft Article', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/ch2/320/240', 'caption' => 'Writing',     'chip' => 'Draft',     'chipVariant' => 'warning'],
            ['title' => 'Forest Path',   'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/ch3/320/240', 'caption' => 'Nature',      'chip' => 'Published', 'chipVariant' => 'success'],
            ['title' => 'Sold Out Item', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/ch4/320/240', 'caption' => 'Product',     'chip' => 'Sold Out',  'chipVariant' => 'danger'],
            ['title' => 'No Chip Tile',  'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/ch5/320/240', 'caption' => 'Standard'],
            ['title' => 'New Arrival',   'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/ch6/320/240', 'caption' => 'Product',     'chip' => 'New'],
        ];
        echo $m->carousel('demoCarouselChips')
            ->tiles($tilesChip)
            ->tileWidth('180px');
        ?>
    </div>

    <?= demoCodeTabs(
        '$tiles = [
    [\'title\' => \'Featured\', \'href\' => \'/1\', \'imageUrl\' => \'/a.jpg\', \'chip\' => \'Featured\', \'chipVariant\' => \'primary\'],
    [\'title\' => \'Normal\',   \'href\' => \'/2\', \'imageUrl\' => \'/b.jpg\'],
];
echo $m->carousel(\'chipDemo\')->tiles($tiles);'
    ) ?>

    <h3>Remote Data</h3>
    <p class="m-demo-desc">
        <code>->remoteUrl()</code> loads tiles in the browser. The endpoint returns <code>{ "tiles": [ … ] }</code>
        using the same keys as <code>->tiles()</code>; <code>->perPage()</code> appends <code>?perPage=N</code>.
    </p>
    <div class="m-demo-row">
        <?= $m->carousel('demoCarousel6')->remoteUrl('/demo/carouselData')->perPage(8) ?>
    </div>
    <div class="m-demo-output" id="demoCarouselRemoteOut">Loading tiles from <code>/demo/carouselData</code>…</div>

    <?= demoCodeTabs(
        'echo $m->carousel(\'ajaxCarousel\')
    ->remoteUrl(\'/api/carousel-tiles\')
    ->perPage(8);'
    ) ?>

    <h3>JS API</h3>
    <div class="m-demo-row">
        <?php
        $tiles7 = [
            ['title' => 'Slide 1', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js1/320/240'],
            ['title' => 'Slide 2', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js2/320/240'],
            ['title' => 'Slide 3', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js3/320/240'],
            ['title' => 'Slide 4', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js4/320/240'],
            ['title' => 'Slide 5', 'href' => '#', 'imageUrl' => 'https://picsum.photos/seed/js5/320/240'],
        ];
        echo $m->carousel('demoCarousel7')->tiles($tiles7);
        ?>
    </div>
    <div class="m-demo-row" style="gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem;">
        <?= $m->button('cJsPrev', 'Prev')->icon('fa-chevron-left') ?>
        <?= $m->button('cJsNext', 'Next')->icon('fa-chevron-right') ?>
        <?= $m->button('cJsGo2', 'Go to #3')->primary()->icon('fa-crosshairs') ?>
    </div>
    <div class="m-demo-output" id="cJsOut">Current tile: —</div>

    <?= demoCodeTabs(
        null,
        'var c = m.carousel(\'myCarousel\');
c.next();              // next page of tiles; prev() goes back
c.goTo(2);             // tile index (0-based)
c.current();           // current tile index
c.reload(\'/api/other-tiles\', 10);

document.getElementById(\'myCarousel\').addEventListener(\'m:carousel:change\', function (e) {
    console.log(\'Active tile:\', e.detail.index);
});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->carousel($id)', 'string', 'Create a Carousel component.'],
    ['->tile($title, $href, $imageUrl, $caption, $chip, $chipVariant)', 'string, string, ?string, ?string, ?string, string', 'Add a single tile. <code>imageUrl</code>, <code>caption</code>, and <code>chip</code> are optional. <code>chipVariant</code> defaults to <code>\'secondary\'</code>.'],
    ['->tiles($tiles)', 'array', 'Add multiple tiles at once. Each element: <code>{title, href, imageUrl?, caption?, chip?, chipVariant?}</code>.'],
    ['->remoteUrl($url)', 'string', 'Client-side remote datasource URL. Response: <code>{"tiles":[…]}</code>. Each tile object may include <code>chip</code> and <code>chipVariant</code>.'],
    ['->perPage($n)', 'int', 'Tiles per remote fetch (appended as <code>?perPage=N</code>). Default: <code>0</code> (load all).'],
    ['->dots($placement)', 'string', 'Dot indicator placement. <code>\'below\'</code> (default), <code>\'above\'</code>, or <code>\'none\'</code>.'],
    ['->tileWidth($css)', 'string', 'CSS width of each tile. Default: <code>\'160px\'</code>. Accepts any CSS length.'],
    ['->tileGap($px)', 'int', 'Gap between tiles in pixels. Default: <code>12</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.carousel(id)', 'string|Element', 'Get the Carousel API for the given element ID or DOM element.'],
    ['c.goTo(idx)', 'number', 'Navigate to tile at zero-based <code>idx</code>.'],
    ['c.next() / c.prev()', '', 'Scroll one page (a viewport of tiles) forward or back.'],
    ['c.goToPage(idx)', 'number', 'Navigate to page at zero-based <code>idx</code>.'],
    ['c.current()', '', 'Returns the current tile index (0-based).'],
    ['c.count()', '', 'Returns the total number of tiles.'],
    ['c.currentPage() / c.pageCount()', '', 'Current page index (0-based) and total page count.'],
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
