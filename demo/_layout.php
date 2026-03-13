<?php
/** @var \Manhattan\HtmlHelper $m */
/** @var string $mDemoTheme */
/** @var bool $mDemoIsDark */
/** @var string $toggleUrl */
/** @var string $cssBase */
/** @var string $jsBase */
/** @var array $demoNav */
/** @var string $page */
/** @var string $pageFile */

// Group nav items by group name
$navGroups = [];
foreach ($demoNav as $slug => $info) {
    $group = $info[2];
    if (!isset($navGroups[$group])) {
        $navGroups[$group] = [];
    }
    $navGroups[$group][$slug] = $info;
}

$pageTitle = 'Manhattan UI';
if ($page !== 'overview' && isset($demoNav[$page])) {
    $pageTitle = $demoNav[$page][0] . ' — Manhattan UI';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <?= $m->renderStyles() ?>
    <?php if ($mDemoIsDark): ?>
    <?= $m->renderDarkStyles() ?>
    <?php endif; ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, sans-serif; background: #f4f6f8; color: #333; }
        body.m-dark { background: #1a1d21; color: #e0e0e0; }

        /* Top nav */
        .m-demo-topnav {
            background: #2c3e50;
            color: #fff;
            padding: 0 24px;
            height: 48px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .m-demo-topnav a { color: #aac; text-decoration: none; font-size: 13px; }
        .m-demo-topnav a:hover { color: #fff; }
        .m-demo-topnav strong { font-size: 17px; letter-spacing: .5px; }

        /* Mobile menu toggle */
        .m-demo-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            padding: 4px 8px;
        }

        /* Layout */
        .m-demo-wrapper {
            display: flex;
            margin-top: 48px;
            min-height: calc(100vh - 48px);
        }

        /* Sidebar */
        .m-demo-sidebar {
            width: 240px;
            min-width: 240px;
            background: #fff;
            border-right: 1px solid #e0e0e0;
            position: fixed;
            top: 48px;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            padding: 12px 0;
            z-index: 900;
        }
        body.m-dark .m-demo-sidebar {
            background: #1e242b;
            border-color: rgba(255,255,255,0.1);
        }

        .m-demo-sidebar-group { margin-bottom: 8px; }
        .m-demo-sidebar-group-label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9aa0a9;
            padding: 8px 16px 4px;
        }

        .m-demo-sidebar a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: 500;
            color: #555;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .m-demo-sidebar a i { width: 16px; text-align: center; font-size: 12px; color: #999; }
        .m-demo-sidebar a:hover { background: #f0f4ff; color: #2196F3; }
        .m-demo-sidebar a:hover i { color: #2196F3; }
        .m-demo-sidebar a.active { background: #e8f0fe; color: #1a73e8; font-weight: 600; }
        .m-demo-sidebar a.active i { color: #1a73e8; }

        body.m-dark .m-demo-sidebar a { color: #a7b0bb; }
        body.m-dark .m-demo-sidebar a i { color: #5a6470; }
        body.m-dark .m-demo-sidebar a:hover { background: rgba(255,255,255,0.05); color: #64b5f6; }
        body.m-dark .m-demo-sidebar a:hover i { color: #64b5f6; }
        body.m-dark .m-demo-sidebar a.active { background: rgba(33,150,243,0.12); color: #90caf9; }
        body.m-dark .m-demo-sidebar a.active i { color: #90caf9; }
        body.m-dark .m-demo-sidebar-group-label { color: #5a6470; }

        /* Accordion navigation styling */
        .m-demo-sidebar .m-accordion {
            margin: 0;
        }
        .m-demo-sidebar .m-accordion-header {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9aa0a9;
            padding: 8px 16px 4px;
            background: transparent;
            border: none;
        }
        .m-demo-sidebar .m-accordion-header:hover {
            background: rgba(0,0,0,0.02);
        }
        body.m-dark .m-demo-sidebar .m-accordion-header {
            color: #5a6470;
        }
        body.m-dark .m-demo-sidebar .m-accordion-header:hover {
            background: rgba(255,255,255,0.03);
        }
        .m-demo-sidebar .m-accordion-content {
            padding: 0;
        }
        .m-demo-sidebar .m-accordion-caret {
            color: #9aa0a9;
            font-size: 10px;
        }
        body.m-dark .m-demo-sidebar .m-accordion-caret {
            color: #5a6470;
        }

        /* Main content */
        .m-demo-main {
            flex: 1;
            margin-left: 240px;
            padding: 24px;
            max-width: 960px;
        }

        /* Page sections */
        .m-demo-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            padding: 20px 24px;
            margin-bottom: 20px;
        }
        body.m-dark .m-demo-section {
            background: #1e242b;
            box-shadow: 0 1px 4px rgba(0,0,0,0.3);
        }

        .m-demo-section h2 {
            font-size: 20px;
            color: #2c3e50;
            margin: 0 0 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        body.m-dark .m-demo-section h2 { color: #eaecef; }

        .m-demo-section h3 {
            font-size: 14px;
            font-weight: 600;
            color: #555;
            margin: 20px 0 10px;
        }
        body.m-dark .m-demo-section h3 { color: #a7b0bb; }

        .m-demo-desc {
            font-size: 13.5px;
            color: #7f8c8d;
            margin: 0 0 16px;
            line-height: 1.5;
        }
        body.m-dark .m-demo-desc { color: #a7b0bb; }

        .m-demo-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .m-demo-field {
            flex: 1;
            min-width: 220px;
        }
        .m-demo-field label {
            display: block;
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
            font-weight: 600;
        }
        body.m-dark .m-demo-field label { color: #a7b0bb; }

        .m-demo-output {
            margin-top: 12px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 13px;
            color: #333;
        }
        body.m-dark .m-demo-output {
            background: #161b22;
            border-color: rgba(255,255,255,0.1);
            color: #e6e6e6;
        }

        .m-demo-pills { display: flex; flex-wrap: wrap; gap: 10px; }
        .m-demo-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border: 1px solid #e0e0e0;
            border-radius: 999px;
            background: #fafafa;
            font-size: 13px;
        }
        body.m-dark .m-demo-pill {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.14);
            color: #e6e6e6;
        }

        .m-textbox-wrapper { width: 100%; }

        /* Tabs for Code/API */
        .m-demo-code-tabs {
            margin-top: 16px;
        }

        /* API reference table */
        .m-api-section { margin-top: 24px; }
        .m-api-section h3 {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
        }
        body.m-dark .m-api-section h3 { border-color: rgba(255,255,255,0.12); }

        .m-api-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .m-api-table th {
            text-align: left;
            padding: 8px 10px;
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #666;
        }
        .m-api-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        .m-api-table code {
            background: #f0f4f8;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 12px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        body.m-dark .m-api-table th {
            background: #161b22;
            border-color: rgba(255,255,255,0.12);
            color: #a7b0bb;
        }
        body.m-dark .m-api-table td {
            border-color: rgba(255,255,255,0.06);
        }
        body.m-dark .m-api-table code {
            background: rgba(255,255,255,0.08);
            color: #90caf9;
        }

        .m-api-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .m-api-badge-php { background: #7b86c6; color: #fff; }
        .m-api-badge-js { background: #f0db4f; color: #333; }
        .m-api-badge-event { background: #4CAF50; color: #fff; }

        /* Theme toggle */
        .m-demo-theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.2);
            background: transparent;
            color: #aac;
            font-size: 12px;
            cursor: pointer;
            transition: all .2s;
        }
        .m-demo-theme-toggle:hover { border-color: #fff; color: #fff; }

        /* Responsive */
        @media (max-width: 768px) {
            .m-demo-menu-toggle { display: block; }
            .m-demo-sidebar {
                transform: translateX(-100%);
                transition: transform .25s ease;
            }
            .m-demo-sidebar.open { transform: translateX(0); }
            .m-demo-main { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body<?php if ($mDemoIsDark): ?> class="m-dark"<?php endif; ?>>

<!-- Top Navigation -->
<nav class="m-demo-topnav">
    <button type="button" class="m-demo-menu-toggle" id="mDemoMenuToggle">
        <i class="fas fa-bars"></i>
    </button>
    <a href="/demo/" style="color:#fff;text-decoration:none"><strong>Manhattan UI</strong></a>
    <span style="flex:1"></span>
    <button type="button" class="m-demo-theme-toggle" id="mDemoThemeToggle">
        <?php if ($mDemoIsDark): ?>
            <i class="fas fa-sun"></i> Light
        <?php else: ?>
            <i class="fas fa-moon"></i> Dark
        <?php endif; ?>
    </button>
    <a href="https://github.com/KyleClarkeNZ/manhattan" target="_blank" rel="noopener">
        <i class="fab fa-github"></i> GitHub
    </a>
</nav>

<div class="m-demo-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="m-demo-sidebar" id="mDemoSidebar">
        <div class="m-demo-sidebar-group">
            <a href="/demo/"<?php if ($page === 'overview'): ?> class="active"<?php endif; ?>>
                <i class="fas fa-home"></i> Overview
            </a>
        </div>
        <?php
        // Build accordion panels for navigation groups
        $navAccordion = $m->accordion('navAccordion')
            ->allowMultiple()
            ->animated()
            ->addClass('m-accordion--borderless m-accordion--compact');
        
        $groupIndex = 0;
        $defaultOpenIndex = null;
        
        foreach ($navGroups as $groupName => $items):
            // Find if any item in this group is currently active
            $groupHasActive = false;
            foreach ($items as $slug => $info) {
                if ($page === $slug) {
                    $groupHasActive = true;
                    $defaultOpenIndex = $groupIndex;
                    break;
                }
            }
            
            // Build the content HTML for this group
            $groupHtml = '';
            foreach ($items as $slug => $info) {
                $activeClass = ($page === $slug) ? ' class="active"' : '';
                $groupHtml .= '<a href="/demo/' . $slug . '"' . $activeClass . '>';
                $groupHtml .= '<i class="fas ' . htmlspecialchars($info[1], ENT_QUOTES, 'UTF-8') . '"></i> ';
                $groupHtml .= htmlspecialchars($info[0], ENT_QUOTES, 'UTF-8');
                $groupHtml .= '</a>';
            }
            
            $navAccordion->panel($groupName, $groupHtml);
            $groupIndex++;
        endforeach;
        
        // Set default open to the group containing the active page, or first group if overview
        if ($defaultOpenIndex !== null) {
            $navAccordion->defaultOpen($defaultOpenIndex);
        } elseif ($page === 'overview') {
            $navAccordion->defaultOpen(0);
        }
        
        echo $navAccordion;
        ?>
    </aside>

    <!-- Main Content -->
    <main class="m-demo-main">
        <?php include $pageFile; ?>
    </main>
</div>

<?= $m->toaster('demoToaster')->position('top-right') ?>
<?= $m->toaster('demoBannerToaster')->position('banner') ?>

<?= $m->renderScripts() ?>

<script>
(function() {
    // Theme toggle
    var btn = document.getElementById('mDemoThemeToggle');
    if (btn) {
        btn.addEventListener('click', function() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', <?= json_encode($toggleUrl) ?>, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() { if (xhr.status === 200) window.location.reload(); };
            xhr.send('{}');
        });
    }

    // Mobile menu toggle
    var menuBtn = document.getElementById('mDemoMenuToggle');
    var sidebar = document.getElementById('mDemoSidebar');
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
        // Close sidebar when clicking a link on mobile
        sidebar.querySelectorAll('a').forEach(function(a) {
            a.addEventListener('click', function() { sidebar.classList.remove('open'); });
        });
    }
})();

function setOutput(id, html) {
    var el = document.getElementById(id);
    if (el) el.innerHTML = html;
}
</script>
</body>
</html>
