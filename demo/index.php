<?php
declare(strict_types=1);

/**
 * Manhattan Demo — standalone bootstrap
 *
 * Run from the package root:
 *   php -S localhost:8080
 * Then visit: http://localhost:8080/demo/
 */

// ---------------------------------------------------------------------------
// Autoloading — supports both "installed as a Composer package" and
// "cloned standalone and composer-installed locally"
// ---------------------------------------------------------------------------
$autoloaderPaths = [
    __DIR__ . '/../vendor/autoload.php',  // standalone clone
    __DIR__ . '/../../autoload.php',       // installed as a Composer dependency
];

$autoloiserLoaded = false;
foreach ($autoloaderPaths as $path) {
    if (is_file($path)) {
        require_once $path;
        $autoloiserLoaded = true;
        break;
    }
}

if (!$autoloiserLoaded) {
    // Fallback: manual PSR-4 registration if Composer hasn't been run yet
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
// Session (used only for demo dark/light theme toggle)
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Resolve asset base URL — works whether served from /demo/ or /
$scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/demo/index.php'));
$assetBase  = rtrim($scriptDir, '/') . '/../assets';

// Simple theme toggle endpoint consumed by the demo JS
if (($_SERVER['REQUEST_URI'] ?? '') === '/demo/toggleTheme' ||
    strpos($_SERVER['REQUEST_URI'] ?? '', '/toggleTheme') !== false &&
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
) {
    header('Content-Type: application/json');
    $current = isset($_SESSION['manhattan_theme']) ? (string)$_SESSION['manhattan_theme'] : 'light';
    $_SESSION['manhattan_theme'] = ($current === 'dark') ? 'light' : 'dark';
    echo json_encode(['success' => true, 'theme' => $_SESSION['manhattan_theme']]);
    exit;
}

// NZPost address proxy (optional — only works when NZPOST_SUBSCRIPTION_KEY is set)
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/nzpostSuggest') !== false) {
    header('Content-Type: application/json');
    $query = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $key   = getenv('NZPOST_SUBSCRIPTION_KEY') ?: '';
    if ($query === '' || strlen($query) < 3) {
        echo json_encode(['success' => true, 'suggestions' => []]);
        exit;
    }
    if ($key === '') {
        echo json_encode(['success' => false, 'message' => 'NZPOST_SUBSCRIPTION_KEY not set.', 'suggestions' => []]);
        exit;
    }
    $url = 'https://api.nzpost.co.nz/addresschecker/1.0/suggest?q=' . rawurlencode($query);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_HTTPHEADER     => ['Ocp-Apim-Subscription-Key: ' . $key],
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    echo $body ?: json_encode(['success' => false, 'suggestions' => []]);
    exit;
}

// ---------------------------------------------------------------------------
// Demo data
// ---------------------------------------------------------------------------
use Manhattan\HtmlHelper;

// Configure Manhattan to serve assets from /assets/ (standalone demo path).
// Font Awesome is served directly from vendor/ when running the demo locally.
HtmlHelper::configure('/assets/css', '/assets/js', '/vendor/components/font-awesome');

$m = HtmlHelper::getInstance();

$mDemoTheme = isset($_SESSION['manhattan_theme']) ? (string)$_SESSION['manhattan_theme'] : 'light';
$mDemoIsDark = ($mDemoTheme === 'dark');

$priorities = [
    ['value' => '1', 'text' => 'Low Priority'],
    ['value' => '2', 'text' => 'Medium Priority'],
    ['value' => '3', 'text' => 'High Priority'],
    ['value' => '4', 'text' => 'Critical'],
];

$categories = [
    ['id' => 1, 'name' => 'Work'],
    ['id' => 2, 'name' => 'Personal'],
    ['id' => 3, 'name' => 'Shopping'],
    ['id' => 4, 'name' => 'Health'],
    ['id' => 5, 'name' => 'Other'],
];

$currentDate = date('Y-m-d');
$dueDate     = date('Y-m-d', strtotime('+7 days'));

// These URL paths are relative to the server root when served via `php -S localhost:8080`
// from the manhattan/ package root directory.
$cssBase = '/assets/css';
$jsBase  = '/assets/js';

// ---------------------------------------------------------------------------
// Render
// ---------------------------------------------------------------------------
$toggleUrl = '/demo/toggleTheme';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manhattan UI — Component Demo</title>
    <?= $m->renderStyles() ?>
    <?php if ($mDemoIsDark): ?>
    <?= $m->renderDarkStyles() ?>
    <?php endif; ?>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; background: #f4f6f8; }
        body.m-dark { background: #1a1d21; color: #e0e0e0; }
    </style>
</head>
<body>
<nav style="background:#2c3e50;color:#fff;padding:12px 24px;display:flex;align-items:center;gap:16px;">
    <strong style="font-size:18px;letter-spacing:.5px;">Manhattan UI</strong>
    <span style="flex:1"></span>
    <a href="https://github.com/KyleClarkeNZ/manhattan" target="_blank"
       style="color:#aac;text-decoration:none;font-size:13px;"><i class="fab fa-github"></i> GitHub</a>
</nav>
<?php
// Pass the toggle URL to the demo view so the theme toggle works in standalone mode
$cssBase = '/assets/css';
$jsBase  = '/assets/js';
?>
<script>var manhattanToggleUrl = '<?= htmlspecialchars($toggleUrl, ENT_QUOTES, 'UTF-8') ?>';</script>
<?php include __DIR__ . '/_view.php'; ?>
<?= $m->renderScripts() ?>
</body>
</html>
