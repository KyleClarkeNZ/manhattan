<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-code') ?> CodeArea</h2>
    <p class="m-demo-desc">Syntax-highlighted code editor/viewer with copy-to-clipboard, multiple language support, and optional editing.</p>

    <h3>Read-Only (PHP)</h3>
    <div class="m-demo-row">
        <?= $m->codeArea('demo-code-php')
            ->language('php')
            ->readOnly()
            ->rows(6)
            ->value('<?php
declare(strict_types=1);

$users = $db->fetchAll("SELECT * FROM users WHERE active = ?", [1]);
foreach ($users as $user) {
    echo htmlspecialchars($user[\'name\']);
}') ?>
    </div>

    <h3>Read-Only (JavaScript)</h3>
    <div class="m-demo-row">
        <?= $m->codeArea('demo-code-js')
            ->language('js')
            ->readOnly()
            ->rows(6)
            ->value('document.addEventListener(\'DOMContentLoaded\', function() {
    const items = document.querySelectorAll(\'.item\');
    items.forEach(item => {
        item.addEventListener(\'click\', () => console.log(item.id));
    });
});') ?>
    </div>

    <h3>Read-Only (SQL)</h3>
    <div class="m-demo-row">
        <?= $m->codeArea('demo-code-sql')
            ->language('sql')
            ->readOnly()
            ->rows(5)
            ->value('SELECT u.name, COUNT(t.id) AS task_count
FROM users u
LEFT JOIN tasks t ON t.user_id = u.id
WHERE u.active = 1
GROUP BY u.id
ORDER BY task_count DESC;') ?>
    </div>

    <h3>Editable</h3>
    <div class="m-demo-row">
        <?= $m->codeArea('demo-code-edit')
            ->language('css')
            ->readOnly(false)
            ->rows(6)
            ->value('.m-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    background: var(--m-surface);
}') ?>
    </div>

    <?= demoCodeTabs(
        '// Read-only PHP viewer
<?= $m->codeArea(\'phpCode\')
    ->language(\'php\')
    ->readOnly()
    ->rows(8)
    ->value($phpSource) ?>

// Editable JS editor
<?= $m->codeArea(\'jsEditor\')
    ->language(\'js\')
    ->readOnly(false)
    ->rows(10)
    ->name(\'code\') ?>

// SQL viewer without wrapping
<?= $m->codeArea(\'sqlView\')
    ->language(\'sql\')
    ->readOnly()
    ->wrap(false)
    ->rows(6) ?>',
        '// Get codearea instance
var ca = m.codearea(\'jsEditor\');

// Get/set value
var code = ca.value();
ca.value(\'console.log("hello");\');

// Re-highlight
ca.render();

// Toggle wrapping
ca.wrap(true);
ca.wrap(false);'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->codeArea($id)', 'string', 'Create a CodeArea component.'],
    ['->language($lang)', 'string', 'Syntax language: <code>js</code>, <code>css</code>, <code>sql</code>, <code>php</code>.'],
    ['->name($name)', '?string', 'Form field name for the underlying textarea.'],
    ['->value($code)', 'string', 'Set the initial code content.'],
    ['->readOnly($ro)', 'bool', 'Read-only mode (default: true).'],
    ['->rows($rows)', 'int', 'Visible row count (default: 8, min: 2).'],
    ['->wrap($wrap)', 'bool', 'Enable long-line wrapping (default: true).'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.codearea(id, opts)', 'string, ?object', 'Get or create CodeArea instance.'],
    ['value(v)', '?string', 'Get or set the code text.'],
    ['render()', '', 'Re-run syntax highlighting.'],
    ['wrap(bool)', 'bool', 'Toggle text wrapping.'],
]) ?>

<?= apiTable('Syntax Token CSS Classes', 'js', [
    ['.m-codearea-token-keyword', '', 'Language keywords (function, var, SELECT, etc.).'],
    ['.m-codearea-token-string', '', 'String literals.'],
    ['.m-codearea-token-comment', '', 'Comments.'],
    ['.m-codearea-token-number', '', 'Numeric literals.'],
    ['.m-codearea-token-variable', '', 'Variables (e.g. PHP <code>$var</code>).'],
    ['.m-codearea-token-constant', '', 'Constants.'],
]) ?>
