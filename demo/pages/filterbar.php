<?php
/** @var \Manhattan\HtmlHelper $m */

// ── Demo data ──────────────────────────────────────────────────────────────

// Movies spanning several years for the group-aware pagination demo
$movies = [
    ['title' => 'The Grand Illusion',    'year' => 1937, 'genre' => 'Drama'],
    ['title' => 'Citizen Kane',          'year' => 1941, 'genre' => 'Drama'],
    ['title' => 'Sunset Boulevard',      'year' => 1950, 'genre' => 'Drama'],
    ['title' => 'Rear Window',           'year' => 1954, 'genre' => 'Thriller'],
    ['title' => 'Vertigo',               'year' => 1958, 'genre' => 'Thriller'],
    ['title' => 'Breathless',            'year' => 1960, 'genre' => 'Drama'],
    ['title' => 'Lawrence of Arabia',    'year' => 1962, 'genre' => 'Adventure'],
    ['title' => 'Dr. Strangelove',       'year' => 1964, 'genre' => 'Comedy'],
    ['title' => 'The Good, the Bad and the Ugly', 'year' => 1966, 'genre' => 'Western'],
    ['title' => '2001: A Space Odyssey', 'year' => 1968, 'genre' => 'Sci-Fi'],
    ['title' => 'Chinatown',             'year' => 1974, 'genre' => 'Thriller'],
    ['title' => 'Apocalypse Now',        'year' => 1979, 'genre' => 'Drama'],
    ['title' => 'Raging Bull',           'year' => 1980, 'genre' => 'Drama'],
    ['title' => 'Blade Runner',          'year' => 1982, 'genre' => 'Sci-Fi'],
    ['title' => 'Fanny and Alexander',   'year' => 1982, 'genre' => 'Drama'],
    ['title' => 'Paris, Texas',          'year' => 1984, 'genre' => 'Drama'],
    ['title' => 'Blue Velvet',           'year' => 1986, 'genre' => 'Thriller'],
    ['title' => 'Wings of Desire',       'year' => 1987, 'genre' => 'Drama'],
    ['title' => 'Do the Right Thing',    'year' => 1989, 'genre' => 'Drama'],
    ['title' => 'GoodFellas',            'year' => 1990, 'genre' => 'Drama'],
    ['title' => 'Barton Fink',           'year' => 1991, 'genre' => 'Drama'],
    ['title' => 'Unforgiven',            'year' => 1992, 'genre' => 'Western'],
    ['title' => 'Schindler\'s List',     'year' => 1993, 'genre' => 'Drama'],
    ['title' => 'Pulp Fiction',          'year' => 1994, 'genre' => 'Thriller'],
    ['title' => 'Chungking Express',     'year' => 1994, 'genre' => 'Drama'],
    ['title' => 'Three Colors: Red',     'year' => 1994, 'genre' => 'Drama'],
    ['title' => 'Secrets & Lies',        'year' => 1996, 'genre' => 'Drama'],
    ['title' => 'LA Confidential',       'year' => 1997, 'genre' => 'Thriller'],
    ['title' => 'There Will Be Blood',   'year' => 2007, 'genre' => 'Drama'],
    ['title' => 'No Country for Old Men','year' => 2007, 'genre' => 'Thriller'],
    ['title' => 'The Dark Knight',       'year' => 2008, 'genre' => 'Action'],
    ['title' => 'A Prophet',             'year' => 2009, 'genre' => 'Drama'],
    ['title' => 'The Tree of Life',      'year' => 2011, 'genre' => 'Drama'],
    ['title' => 'The Artist',            'year' => 2011, 'genre' => 'Comedy'],
    ['title' => 'The Master',            'year' => 2012, 'genre' => 'Drama'],
    ['title' => 'Her',                   'year' => 2013, 'genre' => 'Sci-Fi'],
    ['title' => 'Boyhood',               'year' => 2014, 'genre' => 'Drama'],
    ['title' => 'Mad Max: Fury Road',    'year' => 2015, 'genre' => 'Action'],
    ['title' => 'Moonlight',             'year' => 2016, 'genre' => 'Drama'],
    ['title' => 'Get Out',               'year' => 2017, 'genre' => 'Thriller'],
    ['title' => 'Parasite',              'year' => 2019, 'genre' => 'Thriller'],
    ['title' => 'First Cow',             'year' => 2019, 'genre' => 'Drama'],
    ['title' => 'Nomadland',             'year' => 2020, 'genre' => 'Drama'],
    ['title' => 'The Power of the Dog',  'year' => 2021, 'genre' => 'Drama'],
    ['title' => 'Drive My Car',          'year' => 2021, 'genre' => 'Drama'],
    ['title' => 'The Northman',          'year' => 2022, 'genre' => 'Action'],
    ['title' => 'Aftersun',              'year' => 2022, 'genre' => 'Drama'],
    ['title' => 'Past Lives',            'year' => 2023, 'genre' => 'Drama'],
];

$moviesJson = json_encode($movies);

$phpCodeBasic = <<<'PHP'
// Search only
echo $m->filterBar('myFilter')
    ->search('Search…');

// Sort buttons only
echo $m->filterBar('myFilter')
    ->sort([
        ['value' => 'desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Newest first', 'active' => true],
        ['value' => 'asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Oldest first'],
    ]);

// Full FilterBar with search, sort, group, and linked pager
echo $m->filterBar('myFilter')
    ->search('Search titles…')
    ->sort([
        ['value' => 'desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Descending', 'active' => true],
        ['value' => 'asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Ascending'],
    ])
    ->group([
        ['value' => 'year',  'icon' => 'fa-calendar',    'tooltip' => 'Group by year',  'active' => true],
        ['value' => 'genre', 'icon' => 'fa-film',        'tooltip' => 'Group by genre'],
        ['value' => 'none',  'icon' => 'fa-list',        'tooltip' => 'No grouping'],
    ])
    ->pager('myPager');
PHP;

$jsCodeBasic = <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    var fb = m.filterBar('myFilter');

    document.getElementById('myFilter').addEventListener('m:filterbar:change', function (e) {
        console.log(e.detail); // { search: '...', sort: '...', group: '...' }
        // Re-render your list using e.detail
    });
});
JS;

$phpCodeGroupPaging = <<<'PHP'
echo $m->filterBar('moviesFilter')
    ->search('Search titles…')
    ->sort([
        ['value' => 'desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Descending', 'active' => true],
        ['value' => 'asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Ascending'],
    ])
    ->group([
        ['value' => 'year',  'icon' => 'fa-calendar', 'tooltip' => 'Group by year',  'active' => true],
        ['value' => 'genre', 'icon' => 'fa-film',     'tooltip' => 'Group by genre'],
        ['value' => 'none',  'icon' => 'fa-list',     'tooltip' => 'No grouping'],
    ])
    ->pager('moviesPager');

echo $m->pagination('moviesPager')
    ->showInfo(true)
    ->align('center');
PHP;

$jsCodeGroupPaging = <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    var allMovies = /* PHP JSON */ [];
    var pageIndex = 0;
    var perPage   = 6;

    var fb    = m.filterBar('moviesFilter');
    var pager = m.pagination('moviesPager');

    document.getElementById('moviesFilter').addEventListener('m:filterbar:change', function () {
        pageIndex = 0; // reset on filter change (pager already reset to page 1 by FilterBar)
        render();
    });

    document.getElementById('moviesPager').addEventListener('m:pagination:change', function (e) {
        pageIndex = e.detail.page - 1;
        render();
    });

    function render() {
        var state    = fb.getState();
        var filtered = allMovies.filter(function (m) {
            return m.title.toLowerCase().indexOf(state.search.toLowerCase()) >= 0;
        });

        // Sort filtered items first
        filtered.sort(function (a, b) {
            return state.sort === 'asc' ? a.year - b.year : b.year - a.year;
        });

        var result;

        if (state.group === 'none') {
            // Ungrouped: use standard slice, then display as flat list
            var total = filtered.length;
            var slice = filtered.slice(pageIndex * perPage, (pageIndex + 1) * perPage);
            pager.setTotal(total);
            // displayFlat(slice); ...
        } else {
            // Grouped: use groupSlice — never splits a group across pages
            var keyFn  = state.group === 'genre'
                ? function (m) { return m.genre; }
                : function (m) { return String(m.year); };
            var sortFn = function (keys) {
                return keys.sort(state.sort === 'asc' ? undefined
                    : function (a, b) { return b.localeCompare(a); });
            };

            result = fb.groupSlice(filtered, keyFn, sortFn, perPage, pageIndex);
            pager.setTotal(filtered.length);
            pager.setTotalPages(result.totalPages);   // <-- correct page count for groups
            // displayGroups(result.keys, result.groups); ...
        }
    }

    render();
});
JS;

?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-filter') ?> FilterBar</h2>
    <p class="m-demo-desc">
        A unified search+sort+group toolbar. Any combination of controls can be included.
        Fires a single <code>m:filterbar:change</code> event with the full filter state
        whenever any control changes. When linked to a Pagination instance via <code>->pager()</code>,
        the pager is automatically reset to page 1 on each change.
        The JS instance provides <code>groupSlice()</code> — a <strong>group-aware pagination</strong>
        utility that packs complete groups into pages so no group is ever split across a page boundary.
    </p>

    <!-- ================================================================
         1. Search only
         ================================================================ -->
    <h3>Search Only</h3>
    <p class="m-demo-desc">The simplest configuration — a search box that fires <code>m:filterbar:change</code> on every keystroke (debounced 200 ms).</p>

    <div class="m-demo-row">
        <?= $m->filterBar('demo-search-only')->search('Search anything…') ?>
    </div>
    <div class="m-demo-output" id="demo-search-output">Type to see state…</div>

    <!-- ================================================================
         2. Sort buttons only
         ================================================================ -->
    <h3>Sort Buttons Only</h3>
    <p class="m-demo-desc">Button groups for sort or view-mode switching. Buttons are mutually exclusive within a group.</p>

    <div class="m-demo-row">
        <?= $m->filterBar('demo-sort-only')->sort([
            ['value' => 'desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Newest first', 'active' => true],
            ['value' => 'asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Oldest first'],
        ]) ?>
    </div>
    <div class="m-demo-output" id="demo-sort-output">Click a sort button…</div>

    <!-- ================================================================
         3. Full toolbar with labels
         ================================================================ -->
    <h3>Sort + Group with Labels</h3>
    <p class="m-demo-desc">Mix icons and labels in the same button group. Useful when the distinction between options needs to be clear.</p>

    <div class="m-demo-row">
        <?= $m->filterBar('demo-labels')->group([
            ['value' => 'all',    'label' => 'All',    'active' => true],
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'closed', 'label' => 'Closed'],
        ]) ?>
    </div>
    <div class="m-demo-output" id="demo-labels-output">Click a group button…</div>

    <!-- ================================================================
         4. Group-aware pagination demo
         ================================================================ -->
    <h3>Group-Aware Pagination</h3>
    <p class="m-demo-desc">
        The classic pagination bug: slicing items <em>before</em> grouping splits groups across page
        boundaries. <code>groupSlice(items, keyFn, sortFn, perPage, pageIndex)</code> solves this by
        packing complete groups into pages greedily — a group is never split.
        Changing any control below updates the page count and resets to page 1 automatically.
    </p>
    <p class="m-demo-desc" style="color: var(--m-text-muted); font-size: 0.85rem;">
        <?= $m->icon('fa-circle-info') ?>
        <strong><?= count($movies) ?> films</strong> across multiple years and genres.
        Set per-page to a small number (e.g. 6) to observe that changing the group dimension
        never leaves a partial group at the end of a page.
    </p>

    <?= $m->filterBar('demo-gp-filter')
        ->search('Search titles…')
        ->sort([
            ['value' => 'desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Descending', 'active' => true],
            ['value' => 'asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Ascending'],
        ])
        ->group([
            ['value' => 'year',  'icon' => 'fa-calendar', 'tooltip' => 'Group by year',  'active' => true],
            ['value' => 'genre', 'icon' => 'fa-film',     'tooltip' => 'Group by genre'],
            ['value' => 'none',  'icon' => 'fa-list',     'tooltip' => 'No grouping'],
        ])
        ->pager('demo-gp-pager') ?>

    <div id="demo-gp-list" style="margin-top:14px;"></div>

    <?= $m->pagination('demo-gp-pager')
        ->showInfo(true)
        ->showSizeSelector([3, 6, 10, 15])
        ->align('center') ?>

    <?php $phpCodeGroupPagingEcho = $phpCodeGroupPaging; ?>
    <?= demoCodeTabs($phpCodeGroupPagingEcho, $jsCodeGroupPaging) ?>

    <!-- ================================================================
         Basic usage code tabs
         ================================================================ -->
    <h3>Basic Usage</h3>
    <?= demoCodeTabs($phpCodeBasic, $jsCodeBasic) ?>

</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->filterBar($id)', 'FilterBar', 'Create a FilterBar instance.'],
    ['->search($placeholder)', 'self', 'Add a debounced search input. Default: omitted (no search shown).'],
    ['->sort($options)', 'self', 'Add a mutually-exclusive sort button group. Each option: <code>[\'value\', \'icon\', \'label\', \'tooltip\', \'active\']</code>. Default: omitted.'],
    ['->group($options)', 'self', 'Add a mutually-exclusive group/view button group. Same option shape as sort. Default: omitted.'],
    ['->pager($pagerId)', 'self', 'Link to a Pagination element (by id). The pager resets to page 1 on every filter change. Default: omitted (no linked pager).'],
    ['->addClass($class)', 'self', 'Append extra CSS class(es) to the root element.'],
    ['->attr($name, $val)', 'self', 'Add an arbitrary HTML attribute to the root element.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.filterBar(id)', 'API object', 'Get the FilterBar instance for the given element id.'],
    ['getState()', '{ search, sort, group }', 'Return the current filter state object.'],
    ['setState(partial)', 'void', 'Programmatically update one or more state keys. Does NOT fire the change event or reset the linked pager.'],
    ['groupSlice(items, keyFn, sortFn, perPage, pageIndex)', '{ keys, groups, totalPages }', 'Return the groups and keys for one page using group-aware pagination (no group is ever split). <code>pageIndex</code> is 0-based (= pager.getState().page − 1).'],
    ['computeGroupPages(items, keyFn, sortFn, perPage)', '{ pages, allGroups, sortedKeys, totalPages }', 'Pre-compute all page buckets. Useful when you need <code>totalPages</code> before rendering.'],
]) ?>

<?= eventsTable([
    ['m:filterbar:change', '{ search: string, sort: string, group: string }', 'Fired on the FilterBar element whenever any search/sort/group control changes. The linked pager (if set) resets to page 1 before this fires.'],
]) ?>

<style>
.demo-gp-group { margin-bottom: 1.25rem; }
.demo-gp-group-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--m-text-muted, #8896a9);
    margin-bottom: 6px;
    padding-bottom: 4px;
    border-bottom: 1px solid var(--m-border, #e0e4ed);
}
.demo-gp-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 7px 0;
    border-bottom: 1px solid var(--m-border-light, #f0f3f7);
    font-size: 0.875rem;
}
.demo-gp-row:last-child { border-bottom: none; }
.demo-gp-year {
    display: inline-block;
    min-width: 46px;
    padding: 2px 8px;
    border-radius: 100px;
    background: var(--m-primary-faint, rgba(17,138,178,0.1));
    color: var(--m-primary, #118AB2);
    font-size: 0.75rem;
    font-weight: 600;
    text-align: center;
}
.demo-gp-genre-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 100px;
    background: var(--m-border, #e0e4ed);
    color: var(--m-text-muted);
    font-size: 0.75rem;
}
.demo-gp-empty {
    text-align: center;
    padding: 2rem;
    color: var(--m-text-muted);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Demo 1: search only ───────────────────────────────────────────────
    document.getElementById('demo-search-only').addEventListener('m:filterbar:change', function (e) {
        document.getElementById('demo-search-output').textContent =
            'state: ' + JSON.stringify(e.detail);
    });

    // ── Demo 2: sort only ─────────────────────────────────────────────────
    document.getElementById('demo-sort-only').addEventListener('m:filterbar:change', function (e) {
        document.getElementById('demo-sort-output').textContent =
            'state: ' + JSON.stringify(e.detail);
    });

    // ── Demo 3: labels ────────────────────────────────────────────────────
    document.getElementById('demo-labels').addEventListener('m:filterbar:change', function (e) {
        document.getElementById('demo-labels-output').textContent =
            'state: ' + JSON.stringify(e.detail);
    });

    // ── Demo 4: group-aware pagination ────────────────────────────────────
    var allMovies = <?= $moviesJson ?>;
    var pageIndex = 0;

    var fb    = m.filterBar('demo-gp-filter');
    var pager = m.pagination('demo-gp-pager');

    function renderGp() {
        var state    = fb.getState();
        var search   = state.search.toLowerCase();
        var filtered = allMovies.filter(function (item) {
            return item.title.toLowerCase().indexOf(search) >= 0;
        });

        // Sort filtered items by year (the primary dimension for both group modes)
        filtered.sort(function (a, b) {
            return state.sort === 'asc' ? a.year - b.year : b.year - a.year;
        });

        var listEl = document.getElementById('demo-gp-list');
        var perPage = pager.getState().perPage;

        if (filtered.length === 0) {
            listEl.innerHTML = '<div class="demo-gp-empty">' +
                '<i class="fas fa-search" style="font-size:1.5rem;margin-bottom:8px;display:block"></i>' +
                'No films match your search.</div>';
            pager.setTotal(0);
            pager.setTotalPages(1);
            return;
        }

        if (state.group === 'none') {
            // Ungrouped — plain slice
            var slice = filtered.slice(pageIndex * perPage, (pageIndex + 1) * perPage);
            var totalPages = Math.ceil(filtered.length / perPage) || 1;
            pager.setTotal(filtered.length);
            pager.setTotalPages(totalPages);

            var html = '';
            for (var i = 0; i < slice.length; i++) {
                html += '<div class="demo-gp-row">' +
                    '<span class="demo-gp-year">' + slice[i].year + '</span>' +
                    '<span>' + escHtml(slice[i].title) + '</span>' +
                    '<span class="demo-gp-genre-badge">' + slice[i].genre + '</span>' +
                    '</div>';
            }
            listEl.innerHTML = html;
        } else {
            var keyFn = state.group === 'genre'
                ? function (item) { return item.genre; }
                : function (item) { return String(item.year); };

            var sortFn = function (keys) {
                return keys.slice().sort(state.sort === 'asc'
                    ? function (a, b) { return a.localeCompare(b); }
                    : function (a, b) { return b.localeCompare(a); });
            };

            var result = fb.groupSlice(filtered, keyFn, sortFn, perPage, pageIndex);
            pager.setTotal(filtered.length);
            pager.setTotalPages(result.totalPages);

            var html = '';
            for (var gi = 0; gi < result.keys.length; gi++) {
                var gKey   = result.keys[gi];
                var gItems = result.groups[gKey];
                html += '<div class="demo-gp-group"><div class="demo-gp-group-title">' + escHtml(gKey) + '</div>';
                for (var ki = 0; ki < gItems.length; ki++) {
                    html += '<div class="demo-gp-row">' +
                        '<span class="demo-gp-year">' + gItems[ki].year + '</span>' +
                        '<span>' + escHtml(gItems[ki].title) + '</span>' +
                        (state.group !== 'genre'
                            ? '<span class="demo-gp-genre-badge">' + gItems[ki].genre + '</span>'
                            : '') +
                        '</div>';
                }
                html += '</div>';
            }
            listEl.innerHTML = html;
        }
    }

    function escHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.getElementById('demo-gp-filter').addEventListener('m:filterbar:change', function () {
        pageIndex = 0;
        renderGp();
    });

    document.getElementById('demo-gp-pager').addEventListener('m:pagination:change', function (e) {
        pageIndex = e.detail.page - 1;
        renderGp();
    });

    renderGp();
});
</script>
