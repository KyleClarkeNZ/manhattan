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

if (strpos($uri, '/addressSuggest') !== false) {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    $query = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $key   = getenv('LINZ_API_KEY') ?: '';

    if ($query === '' || strlen($query) < 3) {
        echo json_encode(['success' => true, 'suggestions' => []]);
        exit;
    }

    if ($key === '') {
        // Return mock NZ address data for the demo (no LINZ_API_KEY set).
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

    $proxy       = new \Manhattan\AddressProxy($key, 'ManhattanDemo/1.0 (https://github.com/KyleClarkeNZ/manhattan)');
    $suggestions = $proxy->suggest($query);
    echo json_encode(['success' => true, 'suggestions' => $suggestions]);
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

// ---------------------------------------------------------------------------
// MediaBrowser demo endpoint
//
// A working reference implementation of the contract in src/MediaBrowser.php.
// Host applications should crib the shape of this — especially the folder
// whitelist and the content-based validation — and add their own auth gate,
// which a public demo deliberately has none of.
// ---------------------------------------------------------------------------
if (strpos($uri, '/mediaLibrary') !== false) {
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');

    $respond = static function (array $payload, int $status = 200): void {
        http_response_code($status);
        echo json_encode($payload);
        exit;
    };

    // `folder` arrives from the browser, so it is a KEY resolved against this
    // whitelist — never concatenated into a path.
    $folders = [
        'demo' => ['dir' => __DIR__ . '/data/media/', 'url' => '/demo/media/'],
    ];

    $key = isset($_REQUEST['folder']) ? (string)$_REQUEST['folder'] : '';
    if (!isset($folders[$key])) {
        $respond(['message' => 'Unknown folder.'], 400);
    }
    $dir = $folders[$key]['dir'];
    $url = $folders[$key]['url'];

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $describe = static function (string $path, string $urlBase): array {
        $entry = [
            'name'     => basename($path),
            'url'      => $urlBase . rawurlencode(basename($path)),
            'size'     => (int)filesize($path),
            'modified' => (int)filemtime($path),
        ];
        $info = @getimagesize($path);
        if ($info !== false) {
            $entry['width']  = (int)$info[0];
            $entry['height'] = (int)$info[1];
        }
        return $entry;
    };

    $action = isset($_REQUEST['action']) ? (string)$_REQUEST['action'] : 'list';

    // ----- upload -----
    if ($action === 'upload' && $method === 'POST') {
        if (!is_dir($dir) || !is_writable($dir)) {
            $respond(['message' => 'The demo upload folder is not writable.'], 500);
        }

        $error = isset($_FILES['file']['error']) ? (int)$_FILES['file']['error'] : UPLOAD_ERR_NO_FILE;
        $tmp   = isset($_FILES['file']['tmp_name']) ? (string)$_FILES['file']['tmp_name'] : '';

        if ($error !== UPLOAD_ERR_OK || $tmp === '' || !is_uploaded_file($tmp)) {
            $respond(['message' => 'No file was uploaded.'], 422);
        }

        // Trust the bytes, not the filename the browser supplied.
        $info = @getimagesize($tmp);
        $types = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_WEBP => 'webp',
        ];
        if ($info === false || !isset($types[$info[2]])) {
            $respond(['message' => 'Only JPEG, PNG, GIF and WebP images can be uploaded.'], 422);
        }
        $ext = $types[$info[2]];

        // Name the file ourselves; never reuse the submitted one.
        $stem = 'demo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
        $dest = $dir . $stem . '.' . $ext;

        if (!@move_uploaded_file($tmp, $dest)) {
            $respond(['message' => 'Could not save the uploaded image.'], 500);
        }
        @chmod($dest, 0644);

        $respond(['file' => $describe($dest, $url)]);
    }

    // ----- list -----
    $files = [];
    foreach (glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [] as $path) {
        if (is_file($path)) {
            $files[] = $describe($path, $url);
        }
    }

    usort($files, static function (array $a, array $b): int {
        return $b['modified'] <=> $a['modified'];
    });

    $respond(['files' => $files]);
}

// Serve the demo media folder (the demo runs under `php -S`, which will not
// route /demo/media/* on its own once index.php is the router).
if (preg_match('#^/demo/media/([A-Za-z0-9._-]+)$#', $uri, $mediaMatch)) {
    $path = __DIR__ . '/data/media/' . $mediaMatch[1];
    if (is_file($path)) {
        $info = @getimagesize($path);
        if ($info !== false && !empty($info['mime'])) {
            header('Content-Type: ' . $info['mime']);
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
    }
    http_response_code(404);
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
    'carousel'       => ['Carousel',       'fa-film',         'Layout & Display'],
    'carouselbanner' => ['CarouselBanner', 'fa-rectangle-ad',  'Layout & Display'],
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
    'datepicker'      => ['DatePicker',      'fa-calendar-alt',       'Editors & Forms'],
    'daterangepicker' => ['DateRangePicker', 'fa-calendar-week',      'Editors & Forms'],
    'timepicker'  => ['TimePicker',  'fa-clock',              'Editors & Forms'],
    'address'     => ['Address',     'fa-map-marker-alt',     'Editors & Forms'],
    'iconpicker'  => ['IconPicker',  'fa-icons',              'Editors & Forms'],
    'mediabrowser'=> ['MediaBrowser','fa-photo-film',          'Editors & Forms'],
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
    'calendar'    => ['Calendar',    'fa-calendar-alt',       'Data & Visualisation'],
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
