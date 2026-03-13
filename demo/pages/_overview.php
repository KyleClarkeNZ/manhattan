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
