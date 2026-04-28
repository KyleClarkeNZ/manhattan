<?php
declare(strict_types=1);

/**
 * Manhattan Demo — standalone bootstrap & router
 *
 * Run from the package root:
 *   php -S localhost:8080
 * Then visit: http://localhost:8080/demo/
 */

// ---------------------------------------------------------------------------
// Autoloading
// ---------------------------------------------------------------------------
$autoloaderPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../autoload.php',
];

$autoloaderLoaded = false;
foreach ($autoloaderPaths as $path) {
    if (is_file($path)) {
        require_once $path;
        $autoloaderLoaded = true;
        break;
    }
}

if (!$autoloaderLoaded) {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'Manhattan\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $file = __DIR__ . '/../src/' . substr($class, strlen($prefix)) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

// ---------------------------------------------------------------------------
// Session
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ---------------------------------------------------------------------------
// API endpoints (JSON)
// ---------------------------------------------------------------------------
if ($uri === '/demo/toggleTheme' || (strpos($uri, '/toggleTheme') !== false && $method === 'POST')) {
    header('Content-Type: application/json');
    $current = isset($_SESSION['manhattan_theme']) ? (string)$_SESSION['manhattan_theme'] : 'light';
    $_SESSION['manhattan_theme'] = ($current === 'dark') ? 'light' : 'dark';
    echo json_encode(['success' => true, 'theme' => $_SESSION['manhattan_theme']]);
    exit;
}

if (strpos($uri, '/nzpostSuggest') !== false) {
    header('Content-Type: application/json');
    $query = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $key   = getenv('LINZ_API_KEY') ?: '';
    if ($query === '' || strlen($query) < 3) {
        echo json_encode(['success' => true, 'suggestions' => []]);
        exit;
    }
    if ($key === '') {
        // Return mock NZ address data for the demo (no LINZ_API_KEY set)
        $mockAddresses = [
            ['text' => '1 Queen Street, Auckland Central, Auckland 1010', 'id' => 'mock-1'],
            ['text' => '2 Lambton Quay, Wellington Central, Wellington 6011', 'id' => 'mock-2'],
            ['text' => '100 Colombo Street, Christchurch Central, Christchurch 8011', 'id' => 'mock-3'],
            ['text' => '45 George Street, Dunedin Central, Dunedin 9016', 'id' => 'mock-4'],
            ['text' => '10 Cameron Road, Tauranga 3110', 'id' => 'mock-5'],
        ];
        $filtered = array_values(array_filter($mockAddresses, static function (array $a) use ($query): bool {
            return stripos($a['text'], $query) !== false;
        }));
        if (empty($filtered)) {
            $filtered = array_slice($mockAddresses, 0, 3);
        }
        echo json_encode(['success' => true, 'suggestions' => $filtered]);
        exit;
    }
    // Strip control chars; escape for CQL ILIKE interpolation.
    $sanitized = preg_replace('/[\x00-\x1F\x7F]/', '', $query);
    $escaped   = str_replace("'", "''", (string)$sanitized);
    // Normalize for full_address_ascii: strip macrons then collapse double-vowels.
    $macronMap   = ['ā'=>'a','ē'=>'e','ī'=>'i','ō'=>'o','ū'=>'u','Ā'=>'A','Ē'=>'E','Ī'=>'I','Ō'=>'O','Ū'=>'U'];
    $normalized  = strtr((string)$sanitized, $macronMap);
    $normalized  = (string)preg_replace('/([aeiouAEIOU])\1+/u', '$1', $normalized);
    $escapedNorm = str_replace("'", "''", $normalized);
    $cql = "full_address ILIKE '%" . $escaped . "%'"
         . " OR full_address_ascii ILIKE '%" . $escapedNorm . "%'";

    // ── Parallel: LINZ + Nominatim ──────────────────────────────────────
    $mh = curl_multi_init();

    $linzUrl = 'https://data.linz.govt.nz/services;key=' . rawurlencode($key) . '/wfs'
        . '?service=WFS&version=2.0.0&request=GetFeature'
        . '&typeNames=layer-123113&outputFormat=application%2Fjson'
        . '&count=10&srsName=CRS%3A84'
        . '&CQL_FILTER=' . rawurlencode($cql);
    $chLinz = curl_init($linzUrl);
    curl_setopt_array($chLinz, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    curl_multi_add_handle($mh, $chLinz);

    $nominatimUrl = 'https://nominatim.openstreetmap.org/search'
        . '?q=' . rawurlencode($sanitized)
        . '&format=jsonv2&countrycodes=nz&limit=5&addressdetails=1';
    $chNominatim = curl_init($nominatimUrl);
    curl_setopt_array($chNominatim, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'User-Agent: ManhattanDemo/1.0 (https://github.com/KyleClarkeNZ/manhattan)',
        ],
    ]);
    curl_multi_add_handle($mh, $chNominatim);

    $running = null;
    do { curl_multi_exec($mh, $running); curl_multi_select($mh, 0.1); } while ($running > 0);

    $linzBody        = curl_multi_getcontent($chLinz);
    $linzStatus      = (int)curl_getinfo($chLinz, CURLINFO_HTTP_CODE);
    $nominatimBody   = curl_multi_getcontent($chNominatim);
    $nominatimStatus = (int)curl_getinfo($chNominatim, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $chLinz);    curl_close($chLinz);
    curl_multi_remove_handle($mh, $chNominatim); curl_close($chNominatim);
    curl_multi_close($mh);

    // ── Parse LINZ ──────────────────────────────────────────────────────
    $linzSuggestions = [];
    if ($linzBody !== false && $linzStatus === 200) {
        $data = json_decode((string)$linzBody, true);
        if (is_array($data) && isset($data['features'])) {
            foreach ($data['features'] as $feature) {
                if (!is_array($feature)) { continue; }
                $props = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
                $geom  = is_array($feature['geometry']   ?? null) ? $feature['geometry']   : null;
                $addrNum = trim((string)($props['address_number'] ?? ''));
                $road    = trim((string)($props['road_name']      ?? ''));
                $line1   = $addrNum !== '' && $road !== '' ? $addrNum . ' ' . $road : trim($addrNum . $road);
                $lng = $lat = '';
                if ($geom !== null && isset($geom['coordinates'][0], $geom['coordinates'][1])
                    && is_numeric($geom['coordinates'][0]) && is_numeric($geom['coordinates'][1])
                ) {
                    $lng = (string)$geom['coordinates'][0];
                    $lat = (string)$geom['coordinates'][1];
                }
                $linzSuggestions[] = [
                    'text'     => (string)($props['full_address'] ?? $line1),
                    'id'       => (string)($props['id'] ?? ''),
                    'line1'    => $line1,
                    'suburb'   => (string)($props['suburb_locality'] ?? ''),
                    'city'     => (string)($props['town_city']       ?? ''),
                    'postcode' => (string)($props['postcode']         ?? ''),
                    'lat'      => $lat,
                    'lng'      => $lng,
                ];
            }
        }
    }

    // ── Parse Nominatim ─────────────────────────────────────────────────
    $nominatimSuggestions = [];
    if ($nominatimBody !== false && $nominatimStatus === 200) {
        $nominatimData = json_decode((string)$nominatimBody, true);
        if (is_array($nominatimData)) {
            foreach ($nominatimData as $item) {
                if (!is_array($item)) { continue; }
                $addr     = is_array($item['address'] ?? null) ? $item['address'] : [];
                $poiName  = trim((string)(
                    $addr['amenity']  ?? $addr['building'] ?? $addr['tourism'] ??
                    $addr['leisure'] ?? $addr['shop']     ?? $addr['office']   ?? ''
                ));
                $houseNo  = trim((string)($addr['house_number'] ?? ''));
                $road     = trim((string)($addr['road']         ?? ''));
                $suburb   = trim((string)($addr['suburb']       ?? $addr['quarter'] ?? $addr['neighbourhood'] ?? ''));
                $city     = trim((string)($addr['city']         ?? $addr['town']    ?? $addr['county']        ?? ''));
                $postcode = trim((string)($addr['postcode']     ?? ''));
                $line1    = $houseNo !== '' && $road !== '' ? $houseNo . ' ' . $road : ($houseNo !== '' ? $houseNo : $road);
                $parts    = array_filter([$poiName, $line1, $suburb, trim($city . ($postcode !== '' ? ' ' . $postcode : ''))]);
                $text     = implode(', ', $parts);
                if ($text === '' || ($poiName === '' && $line1 === '')) { continue; }
                $nominatimSuggestions[] = [
                    'text'     => $text,
                    'id'       => 'osm-' . ($item['osm_id'] ?? ''),
                    'line1'    => $line1,
                    'suburb'   => $suburb,
                    'city'     => $city,
                    'postcode' => $postcode,
                    'lat'      => (string)($item['lat'] ?? ''),
                    'lng'      => (string)($item['lon'] ?? ''),
                ];
            }
        }
    }

    // ── Merge, deduplicate by proximity, sort ───────────────────────────
    $merged = [];
    foreach (array_merge($nominatimSuggestions, $linzSuggestions) as $item) {
        $iLat = (float)$item['lat']; $iLng = (float)$item['lng']; $isDup = false;
        if ($iLat !== 0.0 || $iLng !== 0.0) {
            foreach ($merged as $ex) {
                if (abs($iLat - (float)$ex['lat']) < 0.001 && abs($iLng - (float)$ex['lng']) < 0.001) {
                    $isDup = true; break;
                }
            }
        }
        if (!$isDup) { $merged[] = $item; }
    }
    $queryLower = mb_strtolower($query);
    usort($merged, static function (array $a, array $b) use ($queryLower): int {
        $aStarts = mb_strtolower(mb_substr($a['text'], 0, mb_strlen($queryLower))) === $queryLower ? 0 : 1;
        $bStarts = mb_strtolower(mb_substr($b['text'], 0, mb_strlen($queryLower))) === $queryLower ? 0 : 1;
        return $aStarts - $bStarts;
    });
    echo json_encode(['success' => true, 'suggestions' => array_slice($merged, 0, 10)]);
    exit;
}

if (strpos($uri, '/handleButtonClick') !== false && $method === 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Button click received']);
    exit;
}

if (strpos($uri, '/getDropdownData') !== false) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            ['value' => 'opt1', 'text' => 'Dynamic Option 1'],
            ['value' => 'opt2', 'text' => 'Dynamic Option 2'],
            ['value' => 'opt3', 'text' => 'Dynamic Option 3'],
            ['value' => 'opt4', 'text' => 'Dynamic Option 4'],
        ]
    ]);
    exit;
}

if (strpos($uri, '/getGridData') !== false) {
    header('Content-Type: application/json');
    require __DIR__ . '/data/grid_data.php';
    exit;
}

if (strpos($uri, '/wizardSubmit') !== false && $method === 'POST') {
    header('Content-Type: application/json');
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true) ?: [];
    $wizardMeta = isset($data['_wizard']) && is_array($data['_wizard']) ? $data['_wizard'] : [];
    $step  = isset($wizardMeta['currentStep']) ? (string)$wizardMeta['currentStep'] : 'unknown';
    $total = isset($wizardMeta['totalSteps'])  ? (int)$wizardMeta['totalSteps']  : 0;
    echo json_encode([
        'success' => true,
        'message' => 'Wizard submitted at step "' . $step . '" (' . $total . ' steps total).',
        'ref'     => 'DEMO-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
        'received' => $data,
    ]);
    exit;
}

if (strpos($uri, '/wizardData') !== false && $method === 'GET') {
    header('Content-Type: application/json');
    // Simulate a server returning pre-populated field values
    echo json_encode([
        'success' => true,
        'data' => [
            'ord-customer'       => 'Acme Corporation',
            'ord-customer-email' => 'acme@example.com',
        ],
    ]);
    exit;
}

if (strpos($uri, '/paginationPage') !== false && $method === 'GET') {
    $page    = max(1, (int)($_GET['page']    ?? 1));
    $perPage = max(1, (int)($_GET['perPage'] ?? 5));
    $total   = 47;
    $offset  = ($page - 1) * $perPage;
    $items   = [];
    for ($n = $offset + 1; $n <= min($offset + $perPage, $total); $n++) {
        $items[] = '<div class="m-list-item" data-pagination-item>'
            . '<div style="display:flex;align-items:center;gap:10px;padding:2px 0">'
            . '<span style="width:28px;height:28px;border-radius:50%;background:#118AB2;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">' . $n . '</span>'
            . '<div><strong style="font-size:13px">AJAX Item #' . $n . '</strong><br>'
            . '<span style="font-size:12px;color:#888">Loaded from server — page ' . $page . '</span></div>'
            . '</div></div>';
    }
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (strpos($accept, 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['html' => implode('', $items), 'total' => $total]);
    } else {
        echo implode('', $items);
    }
    exit;
}

if (strpos($uri, '/popoverContent') !== false && $method === 'GET') {
    // Return demo HTML for the remote popover example
    echo '<div style="display:flex;align-items:center;gap:10px;padding:2px 0">'
       . '<div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#118AB2,#06457a);color:#fff;'
       .      'display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0">DM</div>'
       . '<div style="display:flex;flex-direction:column;gap:2px">'
       .   '<strong style="font-size:13px">Demo User</strong>'
       .   '<span style="font-size:12px;color:#888">Loaded via AJAX</span>'
       .   '<span style="font-size:11px;color:#aaa"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Remote, URL</span>'
       . '</div></div>';
    exit;
}

if (strpos($uri, '/carouselData') !== false && $method === 'GET') {
    header('Content-Type: application/json');
    $seeds    = ['cs1','cs2','cs3','cs4','cs5','cs6','cs7','cs8','cs9','cs10'];
    $captions = ['Landscape','Portrait','Abstract','Architecture','Nature','Street','Documentary','Aerial','Underwater','Astro'];
    $titles   = ['Golden Hour','Blue Yonder','Urban Grid','Stone & Glass','Deep Forest','Night Market','Field Report','Above the Clouds','Coral Garden','Milky Way'];
    $tiles = [];
    foreach ($seeds as $i => $seed) {
        $tiles[] = [
            'title'    => $titles[$i],
            'href'     => '#demo-' . ($i + 1),
            'imageUrl' => 'https://picsum.photos/seed/' . $seed . '/320/240',
            'caption'  => $captions[$i],
        ];
    }
    echo json_encode(['tiles' => $tiles]);
    exit;
}

// ---------------------------------------------------------------------------
// Manhattan setup
// ---------------------------------------------------------------------------
use Manhattan\HtmlHelper;

HtmlHelper::configure('/assets/css', '/assets/js', '/vendor/components/font-awesome');

$m = HtmlHelper::getInstance();

$mDemoTheme  = isset($_SESSION['manhattan_theme']) ? (string)$_SESSION['manhattan_theme'] : 'light';
$mDemoIsDark = ($mDemoTheme === 'dark');
$toggleUrl   = '/demo/toggleTheme';
$cssBase     = '/assets/css';
$jsBase      = '/assets/js';

// ---------------------------------------------------------------------------
// Routing — determine which page to show
// ---------------------------------------------------------------------------
require __DIR__ . '/_helpers.php';

// Navigation structure: slug => [label, icon, group]
$demoNav = [
    // Layout & Display
    'icon'        => ['Icon',        'fa-icons',              'Layout & Display'],
    'badge'       => ['Badge',       'fa-certificate',        'Layout & Display'],
    'breadcrumb'  => ['Breadcrumb',  'fa-chevron-right',      'Layout & Display'],
    'pageheader'  => ['PageHeader',  'fa-heading',            'Layout & Display'],
    'label'       => ['Label',       'fa-tag',                'Layout & Display'],
    'chip'        => ['Chip',        'fa-circle-dot',         'Layout & Display'],
    'splitpane'   => ['SplitPane',   'fa-columns',            'Layout & Display'],
    'statcard'    => ['StatCard',    'fa-tachometer-alt',     'Layout & Display'],
    'card'        => ['Card',        'fa-square',             'Layout & Display'],
    'emptystate'  => ['EmptyState',  'fa-inbox',              'Layout & Display'],
    'tabs'        => ['Tabs',        'fa-folder',             'Layout & Display'],
    'accordion'   => ['Accordion',   'fa-bars-staggered',     'Layout & Display'],
    'imageviewer' => ['ImageViewer', 'fa-images',             'Layout & Display'],
    'carousel'    => ['Carousel',    'fa-film',               'Layout & Display'],
    // Actions & Navigation
    'button'      => ['Button',      'fa-hand-pointer',       'Actions & Navigation'],
    'buttongroup' => ['ButtonGroup', 'fa-table-columns',      'Actions & Navigation'],
    'dropdown'    => ['Dropdown',    'fa-chevron-circle-down', 'Actions & Navigation'],
    // Editors & Forms
    'textbox'     => ['TextBox',     'fa-i-cursor',           'Editors & Forms'],
    'numberbox'   => ['NumberBox',   'fa-hashtag',            'Editors & Forms'],
    'textarea'    => ['TextArea',    'fa-align-left',         'Editors & Forms'],
    'toggleswitch'=> ['ToggleSwitch','fa-toggle-on',          'Editors & Forms'],
    'checkbox'    => ['Checkbox',    'fa-check-square',       'Editors & Forms'],
    'radio'       => ['Radio',       'fa-dot-circle',         'Editors & Forms'],
    'datepicker'  => ['DatePicker',  'fa-calendar-alt',       'Editors & Forms'],
    'timepicker'  => ['TimePicker',  'fa-clock',              'Editors & Forms'],
    'address'     => ['Address',     'fa-map-marker-alt',     'Editors & Forms'],
    'iconpicker'  => ['IconPicker',  'fa-icons',              'Editors & Forms'],
    'richtexteditor' => ['RichTextEditor', 'fa-pen-to-square',  'Editors & Forms'],
    'codearea'    => ['CodeArea',    'fa-code',               'Editors & Forms'],
    'form'        => ['Form',        'fa-edit',               'Editors & Forms'],
    'validator'   => ['Validator',   'fa-check-circle',       'Editors & Forms'],
    'wizard'      => ['Wizard',      'fa-layer-group',        'Editors & Forms'],
    // Data & Visualisation
    'datagrid'    => ['DataGrid',    'fa-table',              'Data & Visualisation'],
    'list'        => ['List',        'fa-list',               'Data & Visualisation'],
    'reorderable' => ['Reorderable', 'fa-grip-vertical',      'Data & Visualisation'],
    'chart'       => ['Chart',       'fa-chart-bar',          'Data & Visualisation'],
    'progressbar' => ['ProgressBar', 'fa-tasks',              'Data & Visualisation'],
    'rating'      => ['Rating',      'fa-star',               'Data & Visualisation'],
    'pagination'  => ['Pagination',  'fa-ellipsis-h',         'Data & Visualisation'],
    'filterbar'   => ['FilterBar',   'fa-filter',             'Data & Visualisation'],
    'map'         => ['Map',         'fa-map-marked-alt',     'Data & Visualisation'],
    // Overlays & Feedback
    'window'      => ['Window',      'fa-window-maximize',    'Overlays & Feedback'],
    'dialog'      => ['Dialog',      'fa-comment-dots',       'Overlays & Feedback'],
    'toaster'     => ['Toaster',     'fa-bell',               'Overlays & Feedback'],
    'loader'      => ['Loader',      'fa-spinner',            'Overlays & Feedback'],
    'tooltip'     => ['Tooltip',     'fa-comment',            'Overlays & Feedback'],
    'popover'     => ['Popover',     'fa-comment-alt',        'Overlays & Feedback'],
    'lightbox'    => ['Lightbox',    'fa-expand',             'Overlays & Feedback'],

];

// Parse page slug from URI: /demo/button → button, /demo/ → overview
$page = 'overview';
if (preg_match('#/demo/([a-z]+)/?$#i', $uri, $match)) {
    $slug = strtolower($match[1]);
    if (isset($demoNav[$slug])) {
        $page = $slug;
    }
}

// Resolve page file
if ($page === 'overview') {
    $pageFile = __DIR__ . '/pages/_overview.php';
} else {
    $pageFile = __DIR__ . '/pages/' . $page . '.php';
}

if (!is_file($pageFile)) {
    $pageFile = __DIR__ . '/pages/_overview.php';
    $page = 'overview';
}

// ---------------------------------------------------------------------------
// Render
// ---------------------------------------------------------------------------
include __DIR__ . '/_layout.php';
