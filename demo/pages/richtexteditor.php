<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-pen-to-square') ?> RichTextEditor</h2>
    <p class="m-demo-desc">
        A contenteditable-based rich text editor with a fully customisable toolbar.
        Always outputs clean, semantic HTML — including <code>&lt;p&gt;</code> blocks, headings, and lists.
        Toolbar tools are grouped automatically using Manhattan <strong>ButtonGroup</strong> styling.
        Keyboard shortcuts work on both Windows/Linux (<kbd>Ctrl</kbd>) and macOS (<kbd>Cmd</kbd>).
    </p>

    <!-- ============================================================ -->
    <h3>Default Toolbar</h3>
    <p class="m-demo-desc">
        The default toolbar includes bold, italic, underline, alignment, lists, heading level,
        font size, and text colour.
    </p>

    <?= $m->richTextEditor('rteDefault')
        ->name('content_default')
        ->placeholder('Start writing…')
        ->minHeight(180) ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'bioEditor\')
    ->name(\'bio\')
    ->placeholder(\'Start writing…\')
    ->minHeight(180) ?>',
        null
    ) ?>

    <!-- ============================================================ -->
    <h3>Character Count with Limits</h3>
    <p class="m-demo-desc">
        Enable a live character counter with <code>->showCharCount()</code>.
        Set <code>->minChars()</code> and/or <code>->maxChars()</code> to enforce limits —
        the counter is automatically shown when limits are set.
        The count turns <strong style="color:#F57C00">orange</strong> as you approach 90% of the maximum,
        and <strong style="color:#e74c3c">red</strong> when a limit is violated.
        An error message appears below the editor (using the same style as Validator errors),
        and the editor border turns red.
    </p>

    <?= $m->richTextEditor('rteCharCount')
        ->name('content_charcount')
        ->placeholder('Type something to see the character count…')
        ->showCharCount()
        ->minHeight(120) ?>

    <?= $m->richTextEditor('rteCharLimits')
        ->name('content_char_limits')
        ->placeholder('Must be between 20 and 200 characters…')
        ->minChars(20)
        ->maxChars(200)
        ->toolbar(['bold', 'italic', 'separator', 'bulletList'])
        ->minHeight(100) ?>

    <?= demoCodeTabs(
        '// Basic counter
<?= $m->richTextEditor(\'tweetBox\')
    ->name(\'tweet\')
    ->placeholder(\'What\\\'s happening?\')
    ->showCharCount()
    ->minHeight(120) ?>

// Enforce min and max (counter shown automatically)
<?= $m->richTextEditor(\'bioEditor\')
    ->name(\'bio\')
    ->placeholder(\'Must be between 20 and 200 characters…\')
    ->minChars(20)
    ->maxChars(200)
    ->minHeight(100) ?>',
        '// Listen for changes and inspect the character count
document.getElementById(\'tweetBox\')
    .addEventListener(\'m:rte:change\', function (e) {
        var html = e.detail.value;
        console.log(\'HTML:\', html);
    });'
    ) ?>

    <!-- ============================================================ -->
    <h3>Custom Toolbar &amp; Link Insertion</h3>
    <p class="m-demo-desc">
        Pass an array of tool names to <code>->toolbar()</code> to show exactly the tools you need.
        Use <code>'separator'</code> to add a visual divider between groups.
        The <code>'link'</code> tool opens a <strong>Manhattan-styled dialog</strong> (not the browser prompt)
        with a URL field and an "Open in new tab" checkbox.
    </p>

    <?= $m->richTextEditor('rteMinimal')
        ->name('content_minimal')
        ->placeholder('Simple bold / italic editor…')
        ->toolbar(['bold', 'italic', 'underline', 'separator', 'bulletList', 'orderedList', 'separator', 'link'])
        ->minHeight(120) ?>

    <?= demoCodeTabs(
        '// Minimal toolbar — just the essentials
<?= $m->richTextEditor(\'descEditor\')
    ->name(\'description\')
    ->toolbar([\'bold\', \'italic\', \'underline\',
               \'separator\',
               \'bulletList\', \'orderedList\',
               \'separator\',
               \'link\'])
    ->minHeight(120) ?>

// Full toolbar (default)
<?= $m->richTextEditor(\'fullEditor\')
    ->name(\'content\')
    ->toolbar([\'bold\', \'italic\', \'underline\', \'strikethrough\',
               \'separator\',
               \'undo\', \'redo\', \'clearFormat\',
               \'separator\',
               \'align\',
               \'separator\',
               \'bulletList\', \'orderedList\',
               \'separator\',
               \'heading\', \'fontSize\',
               \'separator\',
               \'foreColor\',
               \'separator\',
               \'link\']) ?>',
        null
    ) ?>

    <!-- ============================================================ -->
    <h3>Pre-populated Content</h3>
    <p class="m-demo-desc">
        Pass existing HTML to <code>->value()</code> to pre-load the editor.
        The same HTML can be displayed outside the editor using the
        <code>m-richtext</code> CSS class for consistent typography.
    </p>

    <?= $m->richTextEditor('rtePrepopulated')
        ->name('content_prepopulated')
        ->value('<h2>Welcome to Manhattan</h2><p>This editor outputs <strong>clean semantic HTML</strong> that looks great everywhere.</p><ul><li>Supports <em>headings</em> and lists</li><li>Works with custom <span style="color:#3B82F6">text colours</span></li><li>Keyboard shortcuts on all platforms</li></ul>')
        ->minHeight(180) ?>

    <?= demoCodeTabs(
        '// Load saved HTML into the editor
$savedHtml = $post[\'body\'];  // HTML from your database

<?= $m->richTextEditor(\'postEditor\')
    ->name(\'body\')
    ->value($savedHtml)
    ->minHeight(300) ?>',
        '// Get and set content programmatically
var rte = m.richTextEditor(\'postEditor\');

// Read current HTML
var html = rte.getValue();

// Replace content
rte.setValue(\'<p>New content.</p>\');

// Focus the editor
rte.focus();'
    ) ?>

    <!-- ============================================================ -->
    <h3>Displaying Saved Content</h3>
    <p class="m-demo-desc">
        Wrap stored rich-text HTML in <code>&lt;div class="m-richtext"&gt;</code> to apply
        the same consistent typography that appears inside the editor.
    </p>

    <div class="m-demo-row" style="display:block;">
        <div class="m-richtext" style="padding: 1rem; border: 1px solid var(--m-border, #dde3ec); border-radius: 8px;">
            <h2>Article Title</h2>
            <p>This is a paragraph with <strong>bold text</strong>, <em>italic text</em>, and a <a href="#">link</a>.</p>
            <h3>A Section Heading</h3>
            <p>More content follows, demonstrating how the <code>m-richtext</code> class applies
               consistent typography to any stored HTML when rendered outside of the editor.</p>
            <ul>
                <li>First bullet point</li>
                <li>Second bullet point with <span style="color:#EF4444">coloured text</span></li>
                <li>Third point</li>
            </ul>
            <p>And an ordered list:</p>
            <ol>
                <li>Step one</li>
                <li>Step two</li>
                <li>Step three</li>
            </ol>
        </div>
    </div>

    <?= demoCodeTabs(
        '// Display saved HTML with consistent m-richtext typography
<div class="m-richtext">
    <?= $post[\'body\'] ?>
</div>',
        null
    ) ?>

    <!-- ============================================================ -->
    <h3>Read-only Mode</h3>
    <p class="m-demo-desc">
        <code>->readOnly()</code> disables editing and dims the toolbar.
        Useful for preview panels or displaying content that should not be changed.
    </p>

    <?= $m->richTextEditor('rteReadOnly')
        ->value('<p>This content is <strong>read-only</strong> and cannot be edited.</p>')
        ->readOnly()
        ->minHeight(80) ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'preview\')
    ->value($html)
    ->readOnly()
    ->minHeight(80) ?>',
        null
    ) ?>

    <!-- ============================================================ -->
    <h3>JavaScript Events</h3>
    <p class="m-demo-desc">Listen for editor events to react to content changes or focus state.</p>

    <?= $m->richTextEditor('rteEvents')
        ->name('content_events')
        ->placeholder('Start typing to see events fire…')
        ->showCharCount()
        ->minHeight(100) ?>
    <div class="m-demo-output" id="rte-events-output">Events will appear here…</div>

    <?= demoCodeTabs(
        null,
        '// Content change
document.getElementById(\'rteEvents\')
    .addEventListener(\'m:rte:change\', function (e) {
        console.log(\'Changed:\', e.detail.value);
    });

// Focus / blur
document.getElementById(\'rteEvents\')
    .addEventListener(\'m:rte:focus\', function () {
        console.log(\'Editor focused\');
    });

document.getElementById(\'rteEvents\')
    .addEventListener(\'m:rte:blur\', function () {
        console.log(\'Editor blurred\');
    });'
    ) ?>

</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->richTextEditor($id)', 'string', 'Create a RichTextEditor component.'],
    ['->name($name)', 'string', 'Set the hidden input\'s <code>name</code> attribute for form submission.'],
    ['->value($html)', 'string', 'Pre-load the editor with HTML content.'],
    ['->placeholder($text)', 'string', 'Placeholder text shown when the editor is empty.'],
    ['->showCharCount()', '', 'Show a live character count in the footer. Default: <code>false</code>.'],
    ['->minChars($n)', 'int', 'Minimum character count required. Enables char counter automatically.'],
    ['->maxChars($n)', 'int', 'Maximum character count allowed. Enables char counter automatically. Counter turns orange at 90%, red when exceeded.'],
    ['->customColor($show)', 'bool', 'Show the custom colour input in the colour picker. Default: <code>true</code>.'],
    ['->toolbar($tools)', 'string[]', 'Define which tools appear in the toolbar (see available tools below). Default: full toolbar.'],
    ['->minHeight($px)', 'int', 'Minimum height of the editing area in pixels. Default: <code>200</code>.'],
    ['->maxHeight($px)', 'int', 'Maximum height (enables scroll). Default: none.'],
    ['->readOnly()', '', 'Disable editing and dim the toolbar. Default: <code>false</code>.'],
]) ?>

<?= apiTable('Toolbar Tools', 'php', [
    ['\'bold\'', '', 'Bold (Ctrl/Cmd+B).'],
    ['\'italic\'', '', 'Italic (Ctrl/Cmd+I).'],
    ['\'underline\'', '', 'Underline (Ctrl/Cmd+U).'],
    ['\'strikethrough\'', '', 'Strikethrough.'],
    ['\'align\'', '', 'Alignment group: left, centre, right, justify.'],
    ['\'orderedList\'', '', 'Numbered list.'],
    ['\'bulletList\'', '', 'Bullet list.'],
    ['\'heading\'', '', 'Block format dropdown: Normal / H1–H4.'],
    ['\'fontSize\'', '', 'Font size dropdown: Tiny → Huge.'],
    ['\'foreColor\'', '', 'Text colour picker (presets + custom).'],
    ['\'link\'', '', 'Insert / edit a hyperlink.'],
    ['\'undo\'', '', 'Undo.'],
    ['\'redo\'', '', 'Redo.'],
    ['\'clearFormat\'', '', 'Remove all inline formatting.'],
    ['\'separator\'', '', 'A visual divider between tool groups.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.richTextEditor(id)', 'string', 'Get (or create) an editor instance.'],
    ['rte.getValue()', 'string', 'Return the current HTML content.'],
    ['rte.setValue(html)', 'string', 'Replace the editor content programmatically.'],
    ['rte.focus()', '', 'Focus the editing area.'],
    ['rte.execCommand(cmd, val)', 'string, string?', 'Execute a toolbar command programmatically.'],
]) ?>

<?= eventsTable([
    ['m:rte:change', '{ value: string }', 'Fired on the container whenever content changes. <code>detail.value</code> is the current HTML.'],
    ['m:rte:focus',  '{}',                'Fired when the editing area receives focus.'],
    ['m:rte:blur',   '{}',                'Fired when the editing area loses focus.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var outputEl = document.getElementById('rte-events-output');
    var rteEl    = document.getElementById('rteEvents');
    if (!rteEl || !outputEl) { return; }

    var lastLines = [];

    function log(msg) {
        lastLines.push(msg);
        if (lastLines.length > 6) { lastLines.shift(); }
        outputEl.innerHTML = lastLines.map(function (l) {
            return '<div>' + l + '</div>';
        }).join('');
    }

    rteEl.addEventListener('m:rte:change', function (e) {
        var text = (e.detail.value || '').replace(/<[^>]+>/g, '');
        log('<strong>change</strong> — ' + text.substring(0, 60) + (text.length > 60 ? '…' : ''));
    });

    rteEl.addEventListener('m:rte:focus', function () {
        log('<strong>focus</strong>');
    });

    rteEl.addEventListener('m:rte:blur', function () {
        log('<strong>blur</strong>');
    });
});
</script>
