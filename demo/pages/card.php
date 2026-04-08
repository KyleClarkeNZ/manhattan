<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-square') ?> Card</h2>
    <p class="m-demo-desc">
        A content container with an optional header and footer. Cards automatically receive the
        <code>m-card-default</code> style class, giving them a white background, border, rounded
        corners, and a subtle shadow — no extra class needed.
    </p>

    <h3>Basic Card</h3>
    <p class="m-demo-desc">A card with just body content.</p>
    <div class="m-demo-row" style="max-width:480px;">
        <?= $m->card('demoBasicCard')
            ->content('<p>This is the card body. Any HTML can go here.</p>') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->card(\'basicCard\')
    ->content(\'<p>This is the card body.</p>\') ?>'
    ) ?>
</div>

<div class="m-demo-section">
    <h3>Card with Title</h3>
    <p class="m-demo-desc">Use <code>->title()</code> to add a styled header section.</p>
    <div class="m-demo-row" style="max-width:480px;">
        <?= $m->card('demoTitleCard')
            ->title('Card Title')
            ->content('<p>Card body with a title above it.</p>') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->card(\'titleCard\')
    ->title(\'Card Title\')
    ->content(\'<p>Card body with a title above it.</p>\') ?>'
    ) ?>
</div>

<div class="m-demo-section">
    <h3>Card with Title, Subtitle &amp; Footer</h3>
    <p class="m-demo-desc">
        Combine <code>->title()</code>, <code>->subtitle()</code>, and <code>->footer()</code>
        for a fully-structured card.
    </p>
    <div class="m-demo-row" style="max-width:480px;">
        <?= $m->card('demoFullCard')
            ->title('Full Card Example')
            ->subtitle('A supporting subtitle line')
            ->content('<p>Main content area. Add any HTML here — forms, lists, text, components.</p>')
            ->footer('<small style="color:#7f8c8d;">Last updated today</small>') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->card(\'fullCard\')
    ->title(\'Full Card Example\')
    ->subtitle(\'A supporting subtitle line\')
    ->content(\'<p>Main content area.</p>\')
    ->footer(\'<small>Last updated today</small>\') ?>'
    ) ?>
</div>

<div class="m-demo-section">
    <h3>Title with Icon</h3>
    <p class="m-demo-desc">
        <code>->title()</code> accepts trusted HTML, so you can embed FA icons directly.
    </p>
    <div class="m-demo-row" style="max-width:480px;">
        <?= $m->card('demoIconCard')
            ->title('<i class="fas fa-chart-bar" aria-hidden="true"></i> Analytics')
            ->content('<p>Body content here.</p>') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->card(\'iconCard\')
    ->title(\'<i class="fas fa-chart-bar" aria-hidden="true"></i> Analytics\')
    ->content(\'<p>Body content here.</p>\') ?>'
    ) ?>
</div>

<div class="m-demo-section">
    <h3>Section Headers</h3>
    <p class="m-demo-desc">
        Use <code>->sectionHeader()</code> to add divider rows with an optional "View all" link
        before the card body. Call multiple times to add multiple sections.
    </p>
    <div class="m-demo-row" style="max-width:480px;">
        <?= $m->card('demoSectionCard')
            ->sectionHeader('Recent Activity', '/activity', 'View all')
            ->content('<p>Section content goes here.</p>') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->card(\'sectionCard\')
    ->sectionHeader(\'Recent Activity\', \'/activity\', \'View all\')
    ->content(\'<p>Section content here.</p>\') ?>'
    ) ?>
</div>

<div class="m-demo-section">
    <h3>Custom Classes</h3>
    <p class="m-demo-desc">
        Extra classes can be added via <code>->addClass()</code>. The <code>m-card-default</code>
        style is always present; your class adds on top of it.
    </p>
    <div class="m-demo-row" style="max-width:480px;">
        <?= $m->card('demoCustomCard')
            ->title('Custom Styled Card')
            ->content('<p>Body with a custom accent class applied.</p>')
            ->addClass('my-custom-card') ?>
    </div>

    <?= demoCodeTabs(
        '<?= $m->card(\'customCard\')
    ->title(\'Custom Styled Card\')
    ->content(\'<p>Body content.\')
    ->addClass(\'my-custom-card\') ?>',
        '/* Override specific card elements */
.my-custom-card .m-card-header {
    border-top: 3px solid #118AB2;
}'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->card($id)', 'string', 'Create a card component. Automatically applies <code>m-card</code> and <code>m-card-default</code>.'],
    ['->title($html)', 'string', 'Set the card header title (trusted HTML, renders inside <code>.m-card-header</code>).'],
    ['->subtitle($html)', 'string', 'Set a subtitle shown below the title.'],
    ['->content($html)', 'string', 'Set the card body HTML.'],
    ['->footer($html)', 'string', 'Set the card footer HTML.'],
    ['->sectionHeader($title, $linkUrl, $linkText)', 'string, ?string, string', 'Add a section-header divider before the body, with an optional link. <code>$linkText</code> defaults to <code>\'View all\'</code>. Call multiple times.'],
    ['->addClass($class)', 'string', 'Append additional CSS class(es) to the card element.'],
    ['->attr($name, $value)', 'string, string', 'Set an arbitrary HTML attribute on the card element.'],
]) ?>
