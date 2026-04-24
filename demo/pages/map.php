<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-map-marked-alt') ?> Map</h2>
    <p class="m-demo-desc">
        Embeds a <a href="https://developers.google.com/maps/documentation/javascript" target="_blank" rel="noopener">Google Maps</a>
        instance for displaying GPS coordinates as pins. Accepts coordinates directly from the Address component's
        <code>getCoordinates()</code> API or any other source. The two components are intentionally independent —
        wiring them together is done in application-level JS.
    </p>

    <div class="m-demo-callout" style="background:var(--m-warning-bg,#fff8e1);border-left:4px solid #f9a825;padding:0.75rem 1rem;border-radius:4px;margin-bottom:1rem;font-size:0.88rem;">
        <?= $m->icon('fa-key') ?>
        <strong>API Key required.</strong> A Google Maps JavaScript API key must be provided via <code>->apiKey()</code>.
        The demos below will show a placeholder until a valid key is configured.
        <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" rel="noopener">Get an API key →</a>
    </div>

    <h3>Static Map with Marker</h3>
    <p class="m-demo-desc">Pre-set centre and a marker via PHP fluent API. Renders immediately on page load.</p>
    <div class="m-demo-row" style="display:block;">
        <?= $m->map('demo-map-static')
            ->center(-36.8485, 174.7633)
            ->zoom(13)
            ->marker(-36.8485, 174.7633, 'Auckland CBD')
            ->height('350px') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->map(\'venueMap\')
    ->apiKey($_ENV[\'GOOGLE_MAPS_KEY\'])
    ->center(-36.8485, 174.7633)
    ->zoom(13)
    ->marker(-36.8485, 174.7633, \'Auckland CBD\')
    ->height(\'350px\') ?>',
        '// Map is auto-initialised. Access via:
var map = m.map(\'venueMap\');

// Add a marker programmatically
map.addMarker(-36.8485, 174.7633, \'Auckland CBD\');

// Move the centre
map.setCenter(-36.8485, 174.7633);

// Listen for map ready
document.getElementById(\'venueMap\')
    .addEventListener(\'m:map:ready\', function(e) {
        console.log(\'Map ready:\', e.detail.map);
    });'
    ) ?>
</div>

<div class="m-demo-section">
    <h3>Multiple Markers</h3>
    <p class="m-demo-desc">Pass an array of markers to <code>->markers()</code>. Each must have <code>lat</code>, <code>lng</code>, and optional <code>title</code>.</p>
    <div class="m-demo-row" style="display:block;">
        <?= $m->map('demo-map-multi')
            ->center(-41.2865, 174.7762)
            ->zoom(5)
            ->markers([
                ['lat' => -36.8485, 'lng' => 174.7633, 'title' => 'Auckland'],
                ['lat' => -41.2865, 'lng' => 174.7762, 'title' => 'Wellington'],
                ['lat' => -43.5321, 'lng' => 172.6362, 'title' => 'Christchurch'],
                ['lat' => -45.8788, 'lng' => 170.5028, 'title' => 'Dunedin'],
            ])
            ->height('350px') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->map(\'nzCitiesMap\')
    ->apiKey($_ENV[\'GOOGLE_MAPS_KEY\'])
    ->center(-41.2865, 174.7762)
    ->zoom(5)
    ->markers([
        [\'lat\' => -36.8485, \'lng\' => 174.7633, \'title\' => \'Auckland\'],
        [\'lat\' => -41.2865, \'lng\' => 174.7762, \'title\' => \'Wellington\'],
        [\'lat\' => -43.5321, \'lng\' => 172.6362, \'title\' => \'Christchurch\'],
        [\'lat\' => -45.8788, \'lng\' => 170.5028, \'title\' => \'Dunedin\'],
    ])
    ->height(\'350px\') ?>',
        '// Access and manage markers via JS
var map = m.map(\'nzCitiesMap\');

// Add another marker later
map.addMarker(-37.6878, 176.1651, \'Tauranga\');

// Fit all markers in view
map.fitMarkers();

// Remove all markers
map.clearMarkers();

// Get current markers array
var pins = map.getMarkers(); // [{lat, lng, title, marker}, ...]'
    ) ?>
</div>

<div class="m-demo-section">
    <h3>Dynamic Marker Management</h3>
    <p class="m-demo-desc">Use the JS API to add, clear, and re-centre the map at runtime.</p>
    <div class="m-demo-row" style="flex-direction:column;gap:0.75rem;align-items:flex-start;">
        <?= $m->button('demo-map-add-auckland', 'Add Auckland')->icon('fa-plus') ?>
        <?= $m->button('demo-map-add-wellington', 'Add Wellington')->icon('fa-plus') ?>
        <?= $m->button('demo-map-fit', 'Fit All Markers')->icon('fa-compress-arrows-alt') ?>
        <?= $m->button('demo-map-clear', 'Clear Markers')->danger()->icon('fa-trash') ?>
    </div>
    <div style="margin-top:0.75rem;">
        <?= $m->map('demo-map-dynamic')
            ->center(-41.2865, 174.7762)
            ->zoom(5)
            ->height('350px') ?>
    </div>
    <div class="m-demo-output" id="map-dynamic-output">Click a button to interact with the map...</div>

    <?= demoCodeTabs(
        '<?= $m->map(\'myMap\')
    ->apiKey($_ENV[\'GOOGLE_MAPS_KEY\'])
    ->center(-41.2865, 174.7762)
    ->zoom(5)
    ->height(\'350px\') ?>',
        'document.addEventListener(\'DOMContentLoaded\', function() {
    var map = m.map(\'myMap\');

    document.getElementById(\'addBtn\').addEventListener(\'click\', function() {
        map.addMarker(-36.8485, 174.7633, \'Auckland\');
        map.setCenter(-36.8485, 174.7633);
    });

    document.getElementById(\'clearBtn\').addEventListener(\'click\', function() {
        map.clearMarkers();
    });

    document.getElementById(\'fitBtn\').addEventListener(\'click\', function() {
        map.fitMarkers();
    });
});'
    ) ?>
</div>

<div class="m-demo-section">
    <h3>Address Picker + Map Workflow</h3>
    <p class="m-demo-desc">
        The Address component now exposes <code>getCoordinates()</code> — returns <code>{lat, lng}</code> if the
        selected NZPost suggestion includes GPS data, or <code>null</code> otherwise. This example shows how to wire
        an address picker to a map in application JS.
    </p>

    <?= demoCodeTabs(
        '// In your view:
<?= $m->address(\'deliveryAddress\')
    ->suggestUrl(\'/manhattan/nzpostSuggest\')
    ->mode(\'nz\') ?>

<?= $m->map(\'deliveryMap\')
    ->apiKey($_ENV[\'GOOGLE_MAPS_KEY\'])
    ->height(\'350px\') ?>',
        'document.addEventListener(\'DOMContentLoaded\', function() {
    var addr = m.address(\'deliveryAddress\');
    var map  = m.map(\'deliveryMap\');

    document.getElementById(\'deliveryAddress\')
        .addEventListener(\'m:address:select\', function(e) {
            var coords = addr.getCoordinates();
            if (coords) {
                map.clearMarkers();
                map.setCenter(coords.lat, coords.lng);
                map.setZoom(15);
                map.addMarker(coords.lat, coords.lng, e.detail.label);
            }
        });
});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->map($id)', 'string', 'Create a map component.'],
    ['->apiKey($key)', 'string', 'Google Maps JavaScript API key. Required to render.'],
    ['->center($lat, $lng)', 'float, float', 'Default map centre coordinates. Defaults to Wellington, NZ if not set.'],
    ['->zoom($zoom)', 'int', 'Initial zoom level (1–21). Default: <code>14</code>.'],
    ['->height($css)', 'string', 'Container height as a CSS value. Default: <code>\'400px\'</code>.'],
    ['->marker($lat, $lng, $title)', 'float, float, string', 'Add a single initial marker. <code>$title</code> optional; shown in info window on click.'],
    ['->markers($array)', 'array', 'Set all initial markers at once. Each element: <code>[\'lat\' => …, \'lng\' => …, \'title\' => …]</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.map(id)', 'string', 'Get the map API instance (auto-initialised on DOMContentLoaded).'],
    ['setCenter(lat, lng)', 'float, float', 'Pan the map to the given coordinates.'],
    ['setZoom(zoom)', 'int', 'Set the zoom level.'],
    ['addMarker(lat, lng, title)', 'float, float, ?string', 'Add a pin at the given coordinates. Returns the Google Maps <code>Marker</code> instance.'],
    ['clearMarkers()', '', 'Remove all pins from the map.'],
    ['getMarkers()', '', 'Returns an array of <code>{lat, lng, title, marker}</code> objects.'],
    ['fitMarkers()', '', 'Adjust the map bounds to fit all current markers.'],
]) ?>

<?= apiTable('Address Component Extension', 'js', [
    ['m.address(id).getCoordinates()', '', 'Returns <code>{lat, lng}</code> if the selected suggestion includes GPS coordinates, or <code>null</code>.'],
]) ?>

<?= eventsTable([
    ['m:map:ready', '{map}', 'Fired once Google Maps has initialised. <code>detail.map</code> is the raw <code>google.maps.Map</code> instance.'],
    ['m:map:markeradded', '{lat, lng, title}', 'Fired after each <code>addMarker()</code> call.'],
    ['m:map:markerscleared', '{}', 'Fired after <code>clearMarkers()</code>.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dynamicMap = m.map('demo-map-dynamic');

    document.getElementById('demo-map-add-auckland').addEventListener('click', function() {
        if (!dynamicMap) return;
        dynamicMap.addMarker(-36.8485, 174.7633, 'Auckland');
        dynamicMap.setCenter(-36.8485, 174.7633);
        setOutput('map-dynamic-output', 'Added marker: Auckland (-36.8485, 174.7633)');
    });

    document.getElementById('demo-map-add-wellington').addEventListener('click', function() {
        if (!dynamicMap) return;
        dynamicMap.addMarker(-41.2865, 174.7762, 'Wellington');
        dynamicMap.setCenter(-41.2865, 174.7762);
        setOutput('map-dynamic-output', 'Added marker: Wellington (-41.2865, 174.7762)');
    });

    document.getElementById('demo-map-fit').addEventListener('click', function() {
        if (!dynamicMap) return;
        var markers = dynamicMap.getMarkers();
        if (markers.length === 0) {
            setOutput('map-dynamic-output', 'No markers to fit.');
            return;
        }
        dynamicMap.fitMarkers();
        setOutput('map-dynamic-output', 'Fitted ' + markers.length + ' marker(s) in view.');
    });

    document.getElementById('demo-map-clear').addEventListener('click', function() {
        if (!dynamicMap) return;
        dynamicMap.clearMarkers();
        setOutput('map-dynamic-output', 'All markers cleared.');
    });

    document.getElementById('demo-map-dynamic').addEventListener('m:map:ready', function() {
        setOutput('map-dynamic-output', 'Map ready. Use the buttons above to add markers.');
    });
});
</script>
