<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-pen-to-square') ?> RichTextEditor</h2>
    <p class="m-demo-desc">
        A contenteditable editor with a customisable toolbar that outputs clean, semantic HTML.
        Shortcuts use <kbd>Ctrl</kbd> or <kbd>Cmd</kbd>; toolbar dropdowns are keyboard navigable
        (<kbd>Enter</kbd>/<kbd>↓</kbd> to open, arrows to move, <kbd>Esc</kbd> to close).
    </p>

    <h3>Default Toolbar</h3>
    <?= $m->richTextEditor('rteDefault')
        ->name('content_default')
        ->placeholder('Start writing…')
        ->minHeight(180) ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'bioEditor\')
    ->name(\'bio\')
    ->placeholder(\'Start writing…\')
    ->minHeight(180) ?>',
        'var rte = m.richTextEditor(\'bioEditor\');
var html = rte.getValue();
rte.setValue(\'<p>New content.</p>\');
rte.focus();'
    ) ?>

    <h3>Custom Toolbar &amp; Lists</h3>
    <p class="m-demo-desc">
        Pass tool names to <code>->toolbar()</code> (see <em>Toolbar Tools</em> below); <code>'separator'</code> adds a divider.
        In a list, <kbd>Tab</kbd> nests an item and <kbd>Shift+Tab</kbd> promotes it; outside a list, <kbd>Tab</kbd> indents.
        <code>'link'</code> opens a styled dialog with an "Open in new tab" option.
    </p>

    <?= $m->richTextEditor('rteListIndent')
        ->name('content_list_indent')
        ->toolbar(['bold', 'italic', 'underline', 'separator', 'bulletList', 'orderedList', 'separator', 'link'])
        ->value('<ol><li>First item</li><li>Second item</li><li>Third item</li></ol><ul><li>Apples</li><li>Bananas</li><li>Cherries</li></ul>')
        ->minHeight(160) ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'descEditor\')
    ->name(\'description\')
    ->toolbar([\'bold\', \'italic\', \'underline\', \'separator\',
               \'bulletList\', \'orderedList\', \'separator\', \'link\']) ?>',
        null
    ) ?>

    <h3>Character Limits</h3>
    <p class="m-demo-desc">
        <code>->showCharCount()</code> adds a counter; <code>->minChars()</code>/<code>->maxChars()</code> enable it automatically.
        It turns orange at 90% of the max and red, with an inline error, when a limit is broken.
    </p>

    <?= $m->richTextEditor('rteCharLimits')
        ->name('content_char_limits')
        ->placeholder('Must be between 20 and 200 characters…')
        ->minChars(20)
        ->maxChars(200)
        ->toolbar(['bold', 'italic', 'separator', 'bulletList'])
        ->minHeight(100) ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'bioEditor\')
    ->name(\'bio\')
    ->minChars(20)
    ->maxChars(200) ?>',
        null
    ) ?>

    <h3>Saved Content &amp; Read-only</h3>
    <p class="m-demo-desc">
        Load stored HTML with <code>->value()</code>. To display it outside the editor with the same
        typography (including embeds), wrap it in <code>.m-richtext</code>. <code>->readOnly()</code> disables editing.
    </p>

    <?= $m->richTextEditor('rtePrepopulated')
        ->name('content_prepopulated')
        ->value('<h2>Welcome to Manhattan</h2><p>This editor outputs <strong>clean semantic HTML</strong> that looks great everywhere.</p><ul><li>Supports <em>headings</em> and lists</li><li>Works with custom <span style="color:#3B82F6">text colours</span></li></ul>')
        ->minHeight(160) ?>

    <?= $m->richTextEditor('rteReadOnly')
        ->value('<p>This content is <strong>read-only</strong> and cannot be edited.</p>')
        ->readOnly()
        ->minHeight(80) ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'postEditor\')
    ->name(\'body\')
    ->value($post[\'body\']) ?>

<?= $m->richTextEditor(\'preview\')->value($html)->readOnly() ?>

// Displaying saved HTML elsewhere
<div class="m-richtext"><?= $post[\'body\'] ?></div>',
        null
    ) ?>

    <h3>Events</h3>
    <?= $m->richTextEditor('rteEvents')
        ->name('content_events')
        ->placeholder('Start typing to see events fire…')
        ->showCharCount()
        ->minHeight(100) ?>
    <div class="m-demo-output" id="rte-events-output">Events will appear here…</div>

    <?= demoCodeTabs(
        null,
        'var el = document.getElementById(\'rteEvents\');
el.addEventListener(\'m:rte:change\', function (e) { console.log(e.detail.value); });
el.addEventListener(\'m:rte:focus\',  function () { /* ... */ });
el.addEventListener(\'m:rte:blur\',   function () { /* ... */ });'
    ) ?>

    <h3>Images</h3>
    <p class="m-demo-desc">
        The <code>'image'</code> tool inserts by URL with no setup. Add <code>->uploader($url, $stem)</code> to allow file upload:
        the endpoint receives a multipart POST with an <code>image</code> file (and optional <code>stem</code>) and returns
        <code>{ "url": "…" }</code>. <code>->allowPasteImages()</code> uploads pasted screenshots too.
        Clicking an image shows left / centre / right alignment; <code>->allowImageResize()</code> adds drag handles.
    </p>

    <?= $m->richTextEditor('rteImageResize')
        ->name('content_image_resize')
        ->value('<p>Click the image to align or resize it.</p><p><img src="https://picsum.photos/seed/manhattan/300/180" alt="Sample image" style="width:300px;"> Text wraps beside a left- or right-aligned image; centre puts it on its own line.</p>')
        ->toolbar(['bold', 'italic', 'separator', 'align', 'separator', 'image'])
        ->uploader('/demo/image-upload', 'demo_image')
        ->allowPasteImages()
        ->allowImageResize()
        ->minHeight(200) ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'articleEditor\')
    ->name(\'content\')
    ->toolbar([\'bold\', \'italic\', \'separator\', \'image\'])
    ->uploader(\'/articles/upload-image\', \'article_img\')
    ->allowPasteImages()
    ->allowImageResize() ?>',
        'var el = document.getElementById(\'articleEditor\');
el.addEventListener(\'m:rte:upload:end\', function (e) {
    if (!e.detail.success) console.error(e.detail.error);
});
el.addEventListener(\'m:rte:error\', function (e) { console.error(e.detail.message); });

m.richTextEditor(\'articleEditor\').insertImage(\'/uploads/photo.jpg\', \'A scenic photo\');'
    ) ?>

    <h3>YouTube Embed</h3>
    <p class="m-demo-desc">
        The <code>'youtube'</code> tool accepts any YouTube URL or video ID and inserts a centred, resizable 16:9
        embed via <code>youtube-nocookie.com</code>, followed by an automatic channel credit line.
    </p>

    <?= $m->richTextEditor('rteYoutube')
        ->name('content_youtube')
        ->placeholder('Click the YouTube button to embed a video…')
        ->toolbar(['bold', 'italic', 'separator', 'youtube', 'separator', 'link'])
        ->minHeight(160) ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'blogEditor\')
    ->name(\'content\')
    ->toolbar([\'bold\', \'italic\', \'separator\', \'image\', \'youtube\']) ?>',
        null
    ) ?>

    <h3>Scrollable</h3>
    <p class="m-demo-desc">
        By default the editor grows with its content. <code>->maxHeight()</code> with <code>->scrollable()</code>
        caps it and scrolls inside with a thin overlay scrollbar; the toolbar and footer stay visible.
    </p>
    <?= $m->richTextEditor('rteScrollable')
        ->name('scroll_content')
        ->minHeight(150)
        ->maxHeight(280)
        ->scrollable()
        ->value('<p>This editor is capped at 280 px and scrolls internally once the content grows past that limit.</p>') ?>

    <?= demoCodeTabs(
        '<?= $m->richTextEditor(\'body\')
    ->name(\'body\')
    ->maxHeight(280)
    ->scrollable() ?>',
        null
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
    ['->maxHeight($px)', 'int', 'Maximum height of the editing area in pixels. When exceeded, the body scrolls. Default: none.'],
    ['->scrollable()', '', 'Enable Apple-style thin overlay scrollbars on the editing area. Best combined with <code>->maxHeight()</code> to constrain the editor height; the scrollbar fades in on hover and disappears when idle. Default: <code>false</code>.'],
    ['->readOnly()', '', 'Disable editing and dim the toolbar. Default: <code>false</code>.'],
    ['->uploader($url, $stem)', 'string, string?', 'Configure the image upload endpoint. The POST endpoint must return <code>{ "url": "…" }</code>. Optional <code>$stem</code> is sent as a <code>stem</code> field to suggest a filename prefix.'],
    ['->allowPasteImages()', '', 'Allow pasted raw images (screenshots etc.) to be auto-uploaded via the uploader. Requires <code>->uploader()</code>. Default: <code>false</code>.'],
    ['->refetchExternalImages()', '', 'Also re-host external images found in pasted HTML. The uploader must accept a <code>fetch_url</code> field and download server-side. Default: <code>false</code>.'],
    ['->spellcheck($enabled, $lang)', 'bool, string', 'Toggle browser spell-checking; sets <code>lang</code> (default <code>en-NZ</code>).'],
    ['->allowImageResize()', '', 'Show 8-point drag handles when an image is selected, allowing the user to resize it. The image\'s original natural dimensions are stored in <code>data-original-width</code> / <code>data-original-height</code> attributes. Default: <code>false</code>.'],
]) ?>

<?= apiTable('Toolbar Tools', 'php', [
    ['\'bold\'', '', 'Bold (<kbd>Ctrl/Cmd+B</kbd>).'],
    ['\'italic\'', '', 'Italic (<kbd>Ctrl/Cmd+I</kbd>).'],
    ['\'underline\'', '', 'Underline (<kbd>Ctrl/Cmd+U</kbd>).'],
    ['\'strikethrough\'', '', 'Strikethrough.'],
    ['\'align\'', '', 'Alignment group: left, centre, right, justify.'],
    ['\'orderedList\'', '', 'Numbered list. Use <kbd>Tab</kbd>/<kbd>Shift+Tab</kbd> to indent/promote items.'],
    ['\'bulletList\'', '', 'Bullet list. Use <kbd>Tab</kbd>/<kbd>Shift+Tab</kbd> to indent/promote items.'],
    ['\'heading\'', '', 'Block format dropdown: Normal / H1–H4.'],
    ['\'fontSize\'', '', 'Font size dropdown: Tiny → Huge.'],
    ['\'foreColor\'', '', 'Text colour picker (presets + custom).'],
    ['\'link\'', '', 'Insert / edit a hyperlink.'],
    ['\'image\'', '', 'Insert an image. Opens a dialog for URL entry and (if uploader configured) file upload.'],
    ['\'youtube\'', '', 'Embed a YouTube video. Opens a dialog where you paste any YouTube URL or video ID — a responsive 16:9 iframe is inserted.'],
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
    ['rte.insertImage(url, alt)', 'string, string?', 'Insert an image at the current cursor position.'],
]) ?>

<?= eventsTable([
    ['m:rte:change',       '{ value: string }',                               'Fired on the container whenever content changes. <code>detail.value</code> is the current HTML.'],
    ['m:rte:focus',        '{}',                                              'Fired when the editing area receives focus.'],
    ['m:rte:blur',         '{}',                                              'Fired when the editing area loses focus.'],
    ['m:rte:error',        '{ message: string }',                             'Fired when an error occurs (e.g. paste attempted without uploader configured).'],
    ['m:rte:upload:start', '{}',                                              'Fired when an image upload begins.'],
    ['m:rte:upload:end',   '{ success: bool, url: string|null, error: string|null }', 'Fired when an upload completes. <code>detail.url</code> is the image URL on success.'],
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
