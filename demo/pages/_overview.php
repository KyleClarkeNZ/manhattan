<?php
/** @var \Manhattan\HtmlHelper $m */
/** @var array $demoNav */

// Group nav items
$groups = [];
foreach ($demoNav as $slug => $info) {
    $g = $info[2];
    if (!isset($groups[$g])) {
        $groups[$g] = [];
    }
    $groups[$g][$slug] = $info;
}
?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-cubes') ?> Manhattan UI Components</h2>
    <p class="m-demo-desc">
        A server-rendered PHP + vanilla-JS UI library with zero build dependencies.
        Browse the components in the sidebar, or pick one below.
    </p>
</div>

<?php foreach ($groups as $groupName => $items): ?>
<div class="m-demo-section">
    <h3><?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?></h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-top:10px;">
        <?php foreach ($items as $slug => $info): ?>
        <a href="/demo/<?= $slug ?>" style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;border:1px solid #e0e0e0;text-decoration:none;color:#333;font-size:13px;font-weight:600;transition:border-color .15s,color .15s;">
            <i class="fas <?= htmlspecialchars($info[1], ENT_QUOTES, 'UTF-8') ?>" style="color:#999;width:16px;text-align:center;"></i>
            <?= htmlspecialchars($info[0], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-plug') ?> Setup</h2>
    <p class="m-demo-desc">Configure asset URLs once, then output styles in <code>&lt;head&gt;</code> and scripts before <code>&lt;/body&gt;</code>. jQuery must be loaded by your app.</p>

    <?= demoCodeTabs(
        '<?php
use Manhattan\HtmlHelper;

HtmlHelper::configure(\'/assets/css\', \'/assets/js\', \'/vendor/components/font-awesome\');
$m = HtmlHelper::getInstance();
?>
<head>
    <?= $m->renderStyles() ?>
    <?php if ($dark): ?><?= $m->renderDarkStyles() ?><?php endif; ?>
</head>
<body>
    <?= $m->button(\'save\', \'Save\')->primary() ?>
    <?= $m->renderScripts() ?>
</body>',
        '// Components auto-initialise on page load. Get an instance by id:
m.button(\'save\').setLoading(true);

// Fetch wrapper: JSON in/out, sends X-CSRF-Token from <meta name="csrf-token">
m.ajax(\'/api/save\', { method: \'POST\', data: { id: 1 } })
    .then(function (json) { /* null on error */ });'
    ) ?>
</div>

<?= apiTable('Shared PHP Methods (every component)', 'php', [
    ['->addClass($class)', 'string', 'Add CSS classes to the root element.'],
    ['->attr($name, $value)', 'string, ?string', 'Set an HTML attribute (<code>null</code> removes it).'],
    ['->data($name, $value)', 'string, ?string', 'Set a <code>data-*</code> attribute.'],
    ['->on($event, $handler)', 'string, string', 'Bind a JS handler by function name.'],
    ['->label($text)', 'string', 'Render a <a href="/demo/label">Label</a> above the component. Pair with <code>->labelRequired()</code>, <code>->labelHint()</code>, <code>->labelIcon()</code>.'],
]) ?>

<?= apiTable('Core JS', 'js', [
    ['m.ajax(url, options)', 'string, {method, data, headers, contentType, signal, beforeSend, success, error, complete}', '<code>fetch</code> wrapper. Sends <code>data</code> as JSON, parses JSON responses, adds CSRF and <code>X-Requested-With</code> headers. Returns a Promise resolving to the parsed body, or <code>null</code> on error.'],
    ['m.utils.ready(fn)', 'function', 'Run once the DOM is ready.'],
    ['m.overlays.closeAll()', '', 'Close every open popup surface (dropdowns, pickers, popovers).'],
]) ?>
