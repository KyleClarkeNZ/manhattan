<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-comment-alt') ?> Popover</h2>
    <p class="m-demo-desc">
        A floating panel anchored to a trigger element. Supports static HTML content or remote AJAX
        content, hover (default) or click triggers, and smart viewport-aware placement.
        The popover is rendered to <code>&lt;body&gt;</code> and uses <code>position: absolute</code>
        so it scrolls naturally with the document — no JavaScript scroll tracking needed.
        When the popover is clamped near a viewport edge the arrow always adjusts to keep pointing
        at the trigger element. Popovers are ideal for contextual info cards, user profile previews,
        and rich tooltips.
    </p>

    <!-- ================================================================
         1. Basic Hover Popover
         ================================================================ -->
    <h3>Basic Hover Popover</h3>
    <p class="m-demo-desc">
        Default behaviour: shows on hover with a short delay, hides when the mouse leaves the
        trigger or the popover itself. Placement defaults to <code>auto</code> (prefers bottom).
    </p>
    <div class="m-demo-row">
        <?= $m->button('demo-pop-trigger-1', 'Hover over me')->icon('fa-info-circle') ?>
    </div>

    <?= $m->popover('demo-pop-1')
        ->trigger('demo-pop-trigger-1')
        ->title('Quick Info')
        ->content('<p>Popovers are great for contextual<br>information without cluttering the UI.</p>') ?>

    <!-- ================================================================
         2. Click Popover
         ================================================================ -->
    <h3>Click Trigger</h3>
    <p class="m-demo-desc">
        Use <code>->triggerOn('click')</code> for click-to-toggle behaviour.
        Clicking outside the popover or pressing Escape dismisses it.
    </p>
    <div class="m-demo-row">
        <?= $m->button('demo-pop-trigger-2', 'Click to toggle')->secondary()->icon('fa-mouse-pointer') ?>
    </div>

    <?= $m->popover('demo-pop-2')
        ->trigger('demo-pop-trigger-2')
        ->title('Click Popover')
        ->triggerOn('click')
        ->placement('bottom')
        ->content('<p>Click the button again or click elsewhere to close.</p>') ?>

    <!-- ================================================================
         3. Placement variants
         ================================================================ -->
    <h3>Placement</h3>
    <p class="m-demo-desc">
        Explicit <code>top</code>, <code>bottom</code>, <code>left</code>, and <code>right</code>
        placements, plus <code>auto</code> (default — flips to top when near the bottom edge).
    </p>
    <div class="m-demo-row" style="gap:0.75rem; flex-wrap:wrap; justify-content:center; padding:2rem 0;">
        <?= $m->button('demo-pop-top',    'Top')->icon('fa-arrow-up') ?>
        <?= $m->button('demo-pop-bottom', 'Bottom')->icon('fa-arrow-down') ?>
        <?= $m->button('demo-pop-left',   'Left')->icon('fa-arrow-left') ?>
        <?= $m->button('demo-pop-right',  'Right')->icon('fa-arrow-right') ?>
    </div>

    <?= $m->popover('demo-pop-placement-top')
        ->trigger('demo-pop-top')
        ->placement('top')
        ->content('<p>Placed <strong>above</strong> the trigger.</p>') ?>

    <?= $m->popover('demo-pop-placement-bottom')
        ->trigger('demo-pop-bottom')
        ->placement('bottom')
        ->content('<p>Placed <strong>below</strong> the trigger.</p>') ?>

    <?= $m->popover('demo-pop-placement-left')
        ->trigger('demo-pop-left')
        ->placement('left')
        ->content('<p>Placed to the <strong>left</strong>.</p>') ?>

    <?= $m->popover('demo-pop-placement-right')
        ->trigger('demo-pop-right')
        ->placement('right')
        ->content('<p>Placed to the <strong>right</strong>.</p>') ?>

    <!-- ================================================================
         4. Remote / AJAX content
         ================================================================ -->
    <h3>Remote Content (AJAX)</h3>
    <p class="m-demo-desc">
        Use <code>->remote(url)</code> to load the popover body from an endpoint on first show.
        Responses are cached per URL by default — set <code>->cache(false)</code> to always
        re-fetch. A spinner is shown while loading.
    </p>
    <div class="m-demo-row">
        <?= $m->button('demo-pop-trigger-remote', 'Hover for remote content')->primary()->icon('fa-cloud-download-alt') ?>
    </div>

    <?= $m->popover('demo-pop-remote')
        ->trigger('demo-pop-trigger-remote')
        ->title('Remote Data')
        ->remote('/demo/popoverContent')
        ->width('280px') ?>

    <!-- ================================================================
         5. Shared popover — multiple triggers via CSS selector
         ================================================================ -->
    <h3>Shared Popover — User Profile Cards</h3>
    <p class="m-demo-desc">
        A single popover instance can serve many triggers via <code>->triggerSelector()</code>.
        Each trigger passes per-trigger overrides using <code>data-popover-title</code> and
        <code>data-popover-content</code> (or <code>data-popover-url</code> for AJAX overrides).
        This is the recommended pattern for <em>user profile cards</em> on a username list.
    </p>
<?php
    // Build profile card HTML in PHP variables so inner HTML can be safely
    // htmlspecialchars()'d into data attributes without breaking attribute parsing.
    // Using single-quoted class attributes on the <i> tag avoids conflicts with
    // the outer double-quoted data attribute.
    $mapIcon = "<i class='fas fa-map-marker-alt'></i>";
    $demoProfiles = [
        ['initials' => 'AJ', 'title' => 'Alice Johnson', 'role' => 'Senior Developer',  'location' => 'Wellington, NZ'],
        ['initials' => 'BT', 'title' => 'Bob Tane',      'role' => 'Product Manager',   'location' => 'Auckland, NZ'],
        ['initials' => 'CR', 'title' => 'Carmen Reyes',  'role' => 'UX Designer',        'location' => 'Christchurch, NZ'],
    ];
    $demoHandles = ['alice', 'bob', 'carmen'];
    foreach ($demoProfiles as $pi => $prof) {
        $prof['content'] = "<div class='demo-profile-card'>"
            . "<div class='demo-avatar'>" . htmlspecialchars($prof['initials'], ENT_QUOTES, 'UTF-8') . "</div>"
            . "<div class='demo-profile-info'>"
            . "<strong>" . htmlspecialchars($prof['title'],    ENT_QUOTES, 'UTF-8') . "</strong>"
            . "<span>"   . htmlspecialchars($prof['role'],     ENT_QUOTES, 'UTF-8') . "</span>"
            . "<span class='demo-profile-meta'>{$mapIcon} " . htmlspecialchars($prof['location'], ENT_QUOTES, 'UTF-8') . "</span>"
            . "</div></div>";
        $demoProfiles[$pi] = $prof;
    }
?>
    <div class="m-demo-row" style="gap:0.5rem; flex-wrap:wrap; align-items:center;">
        <span>Mentioned by: </span>
        <?php foreach ($demoProfiles as $pi => $prof): ?>
        <a class="demo-username-link" href="#"
           data-popover-title="<?= htmlspecialchars($prof['title'], ENT_QUOTES, 'UTF-8') ?>"
           data-popover-content="<?= htmlspecialchars($prof['content'], ENT_QUOTES, 'UTF-8') ?>"
        >@<?= htmlspecialchars($demoHandles[$pi], ENT_QUOTES, 'UTF-8') ?></a><?= $pi < count($demoProfiles) - 1 ? ',' : '' ?>
        <?php endforeach; ?>
    </div>

    <?= $m->popover('demo-pop-profile')
        ->triggerSelector('.demo-username-link')
        ->placement('bottom')
        ->delay(150, 300)
        ->width('240px') ?>

    <div class="m-demo-output" id="pop-output">Interact with the examples above to see events here…</div>

    <?= demoCodeTabs(
        '// Static content, single trigger (hover by default)
<?= $m->popover(\'info-pop\')
      ->trigger(\'info-btn\')
      ->title(\'Quick Info\')
      ->content(\'<p>Details here.</p>\')
      ->placement(\'bottom\') ?>

// Click trigger
<?= $m->popover(\'click-pop\')
      ->trigger(\'click-btn\')
      ->title(\'Click Popover\')
      ->triggerOn(\'click\')
      ->content(\'<p>Click outside to close.</p>\') ?>

// Remote / AJAX content
<?= $m->popover(\'remote-pop\')
      ->trigger(\'remote-btn\')
      ->title(\'User Profile\')
      ->remote(\'/api/user/card?id=42\')
      ->width(\'280px\') ?>

// Shared popover for multiple triggers (user profile cards)
<?= $m->popover(\'profile-pop\')
      ->triggerSelector(\'.username-link\')
      ->placement(\'bottom\')
      ->delay(150, 300)
      ->width(\'260px\') ?>

// Each trigger carries per-trigger overrides:
<a class="username-link"
   data-popover-title="Jane Smith"
   data-popover-url="/profile/card?id=5">@jane</a>

// Or inline content override:
<a class="username-link"
   data-popover-title="Jane Smith"
   data-popover-content="<p>Inline content</p>">@jane</a>

// Programmatic binding for dynamically added triggers:
document.querySelectorAll(\'.new-link\').forEach(function(el) {
    m.popover(\'profile-pop\').bindTrigger(el);
});',
        '// Get the popover API
var pop = m.popover(\'my-pop\');

// Show anchored to any element
pop.show(document.getElementById(\'my-btn\'));

// Hide
pop.hide();

// Toggle for a trigger element
pop.toggle(document.getElementById(\'my-btn\'));

// Update content on demand
pop.setContent(\'<p>New content</p>\');
pop.setTitle(\'New title\');

// Load content from a URL
pop.loadContent(\'/api/details\');

// Manually bind a new trigger (after dynamic DOM insertion)
pop.bindTrigger(someNewElement);

// Re-scan DOM for triggers added after init
pop.refresh();

// Listen for events
document.getElementById(\'my-pop\').addEventListener(\'m:popover:show\', function(e) {
    console.log(\'shown for trigger\', e.detail.trigger);
});

document.getElementById(\'my-pop\').addEventListener(\'m:popover:content-loaded\', function(e) {
    console.log(\'loaded from\', e.detail.url);
});'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->popover($id)', 'Popover', 'Create a new popover component.'],
    ['->title($text)', 'self', 'Set the header title text.'],
    ['->content($html)', 'self', 'Set static HTML for the popover body.'],
    ['->trigger($elementId)', 'self', 'Bind to a single DOM element by ID.'],
    ['->triggerSelector($css)', 'self', 'Bind to all elements matching a CSS selector.'],
    ['->placement($p)', 'self', 'Placement: <code>auto</code> (default), <code>top</code>, <code>bottom</code>, <code>left</code>, <code>right</code>.'],
    ['->align($a)', 'self', 'Horizontal alignment for top/bottom placements: <code>center</code> (default), <code>start</code> (left-edge), <code>end</code> (right-edge). No effect on left/right placements.'],
    ['->triggerOn($event)', 'self', 'Trigger event: <code>hover</code> (default) or <code>click</code>.'],
    ['->delay($show, $hide)', 'self', 'Show/hide delays in ms. Default: 200 / 300.'],
    ['->remote($url)', 'self', 'Load body content from a URL via AJAX.'],
    ['->cache($bool)', 'self', 'Cache remote responses per URL. Default: <code>true</code>.'],
    ['->width($css)', 'self', 'Force a specific CSS width, e.g. <code>\'300px\'</code>.'],
    ['->offset($px)', 'self', 'Gap between trigger and popover in pixels. Default: <code>8</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.popover(id)', 'object|null', 'Get a popover instance by ID (auto-inits on DOMContentLoaded).'],
    ['pop.show(triggerEl)', '', 'Show the popover anchored to an element.'],
    ['pop.hide()', '', 'Hide the popover.'],
    ['pop.toggle(triggerEl)', '', 'Toggle visibility for a trigger element.'],
    ['pop.setContent(html)', '', 'Replace the body with arbitrary HTML.'],
    ['pop.setTitle(text)', '', 'Update the header title text.'],
    ['pop.loadContent(url, force)', '', 'Fetch content from a URL. Pass <code>true</code> to bypass cache.'],
    ['pop.bindTrigger(el)', '', 'Bind a DOM element as a trigger (safe to call multiple times).'],
    ['pop.refresh()', '', 'Re-scan the DOM for new trigger elements matching the configured selector.'],
    ['pop.element', 'HTMLElement', 'The underlying popover DOM element.'],
]) ?>

<?= eventsTable([
    ['m:popover:show', '{ id, trigger }', 'Fired on the popover element when it becomes visible.'],
    ['m:popover:hide', '{ id }', 'Fired when the popover is hidden.'],
    ['m:popover:content-loaded', '{ id, url }', 'Fired after remote content is successfully loaded and injected.'],
]) ?>

<style>
.demo-username-link {
    color: var(--m-primary, #118AB2);
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    border-bottom: 1px dashed currentColor;
}
.demo-username-link:hover {
    text-decoration: none;
    opacity: 0.85;
}
.demo-profile-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 2px 0;
}
.demo-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #118AB2, #06457a);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
}
.demo-profile-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.demo-profile-info strong {
    font-size: 13px;
    color: inherit;
    display: block;
}
.demo-profile-info span {
    font-size: 12px;
    color: #888;
    display: block;
}
.demo-profile-meta {
    font-size: 11px !important;
    color: #aaa !important;
}
.demo-profile-meta i {
    margin-right: 2px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var output = document.getElementById('pop-output');

    // Log events from all demo popovers
    var DEMO_POP_IDS = [
        'demo-pop-1', 'demo-pop-2',
        'demo-pop-placement-top', 'demo-pop-placement-bottom',
        'demo-pop-placement-left', 'demo-pop-placement-right',
        'demo-pop-remote', 'demo-pop-profile'
    ];

    DEMO_POP_IDS.forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;

        el.addEventListener('m:popover:show', function (e) {
            output.textContent = 'popover "' + id + '" shown';
        });
        el.addEventListener('m:popover:hide', function (e) {
            output.textContent = 'popover "' + id + '" hidden';
        });
        el.addEventListener('m:popover:content-loaded', function (e) {
            output.textContent = 'popover "' + id + '" loaded content from: ' + e.detail.url;
        });
    });
});
</script>
