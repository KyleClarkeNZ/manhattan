<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-pen-to-square') ?> RichTextEditor</h2>
    <p class="m-demo-desc">
        A contenteditable-based rich text editor with a fully customisable toolbar.
        Always outputs clean, semantic HTML — including <code>&lt;p&gt;</code> blocks, headings, and lists.
        Toolbar tools are grouped automatically using Manhattan <strong>ButtonGroup</strong> styling.
        Keyboard shortcuts work on both Windows/Linux (<kbd>Ctrl</kbd>) and macOS (<kbd>Cmd</kbd>).
    </p>
    <p class="m-demo-desc">
        <strong>Tab key behaviour:</strong> Pressing <kbd>Tab</kbd> inside a list item creates a sub-list
        (numbered → lettered, bullets → open circles). <kbd>Shift+Tab</kbd> promotes a sub-item back up.
        Outside of a list, <kbd>Tab</kbd> inserts a visual indent (4 non-breaking spaces).
    </p>
    <p class="m-demo-desc">
        <strong>Toolbar dropdown keyboard navigation:</strong> Toolbar dropdowns (e.g. Text Format, Font Size)
        can be opened with <kbd>Enter</kbd> or <kbd>Space</kbd> when the trigger is focused, or
        <kbd>↓</kbd> opens and jumps straight to the first item. Use <kbd>↑</kbd>/<kbd>↓</kbd> to move
        between options, <kbd>Enter</kbd>/<kbd>Space</kbd> to select, and <kbd>Esc</kbd> to close without
        selecting. The <strong>Text Format</strong> dropdown shows a live style preview of each heading level.
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
    <h3>List Indentation (Tab / Shift+Tab)</h3>
    <p class="m-demo-desc">
        Inside a list item, pressing <kbd>Tab</kbd> creates a nested sub-list.
        <kbd>Shift+Tab</kbd> promotes an item back to the parent level.<br>
        Sub-list styling is automatic: <strong>ordered lists</strong> use lettered sub-items
        (a, b, c…), and <strong>unordered lists</strong> use open-circle bullets.
        Outside a list, <kbd>Tab</kbd> inserts a visual indent.
    </p>

    <?= $m->richTextEditor('rteListIndent')
        ->name('content_list_indent')
        ->toolbar(['bulletList', 'orderedList', 'separator', 'bold', 'italic'])
        ->value('<ol><li>First item</li><li>Second item</li><li>Third item</li></ol><ul><li>Apples</li><li>Bananas</li><li>Cherries</li></ul>')
        ->minHeight(160) ?>

    <?= demoCodeTabs(
        '// Tab / Shift+Tab work automatically — no configuration needed
<?= $m->richTextEditor(\'listEditor\')
    ->name(\'content\')
    ->toolbar([\'bulletList\', \'orderedList\', \'separator\', \'bold\', \'italic\'])
    ->minHeight(160) ?>',
        '// Tab key behaviour is built in:
// – Inside a list item:  Tab   → indent (create sub-list)
//                        Shift+Tab → outdent (promote back up)
// – Outside a list:      Tab   → insert 4 non-breaking spaces
// – Sub-list types cycle automatically via CSS:
//     ol > ol  → lower-alpha (a, b, c…)
//     ul > ul  → circle bullets
//     ol > ol > ol → lower-roman (i, ii, iii…)'
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

    <!-- ============================================================ -->
    <h3>Image Insertion — URL</h3>
    <p class="m-demo-desc">
        Adding <code>'image'</code> to the toolbar renders an <strong>Insert Image</strong> button.
        Clicking it opens a dialog where you can enter any image URL and optional alt text.
        No uploader configuration is required for URL-based insertion.
    </p>

    <?= $m->richTextEditor('rteImageUrl')
        ->name('content_image_url')
        ->placeholder('Click the image button in the toolbar to insert an image by URL…')
        ->toolbar(['bold', 'italic', 'separator', 'image', 'separator', 'link'])
        ->minHeight(140) ?>

    <?= demoCodeTabs(
        '// Add \'image\' to the toolbar — URL insertion always available
<?= $m->richTextEditor(\'bodyEditor\')
    ->name(\'body\')
    ->toolbar([\'bold\', \'italic\', \'separator\', \'image\', \'separator\', \'link\'])
    ->minHeight(200) ?>',
        null
    ) ?>

    <!-- ============================================================ -->
    <h3>Image Insertion — File Upload &amp; Paste</h3>
    <p class="m-demo-desc">
        To enable <strong>file upload</strong> (via the Insert Image dialog) and/or
        <strong>paste-to-upload</strong>, configure an uploader endpoint using
        <code>->uploader($url, $stem)</code>.
        The endpoint receives a <code>multipart/form-data</code> POST with an <code>image</code>
        file field (and an optional <code>stem</code> text field) and must return
        <code>{ "url": "/path/to/saved/image.ext" }</code>.
        Use <code>->allowPasteImages()</code> to also allow users to paste raw image data
        (e.g. screenshots) directly into the editor.
        If pasting is attempted and no uploader is configured a toaster error is shown automatically.
    </p>

    <?= $m->richTextEditor('rteImageUpload')
        ->name('content_image_upload')
        ->placeholder('Paste an image or use the toolbar button to upload one…')
        ->toolbar(['bold', 'italic', 'separator', 'image'])
        ->uploader('/demo/image-upload', 'demo_image')
        ->allowPasteImages()
        ->minHeight(140) ?>

    <?= demoCodeTabs(
        '// With file-upload support in the dialog
<?= $m->richTextEditor(\'postEditor\')
    ->name(\'body\')
    ->toolbar([\'bold\', \'italic\', \'separator\', \'image\'])
    ->uploader(\'/posts/upload-image\', \'post_img\') ?>

// Also enable paste-to-upload (screenshots etc.)
<?= $m->richTextEditor(\'articleEditor\')
    ->name(\'content\')
    ->toolbar([\'bold\', \'italic\', \'separator\', \'image\'])
    ->uploader(\'/articles/upload-image\', \'article_img\')
    ->allowPasteImages() ?>',
        '// Listen for upload lifecycle events
document.getElementById(\'articleEditor\')
    .addEventListener(\'m:rte:upload:start\', function () {
        console.log(\'Upload started\');
    });

document.getElementById(\'articleEditor\')
    .addEventListener(\'m:rte:upload:end\', function (e) {
        if (e.detail.success) {
            console.log(\'Uploaded to:\', e.detail.url);
        } else {
            console.error(\'Upload failed:\', e.detail.error);
        }
    });

// Listen for errors (uploader not configured etc.)
document.getElementById(\'articleEditor\')
    .addEventListener(\'m:rte:error\', function (e) {
        console.error(\'RTE error:\', e.detail.message);
    });

// Insert programmatically
var rte = m.richTextEditor(\'articleEditor\');
rte.insertImage(\'/uploads/photo.jpg\', \'A scenic photo\');'
    ) ?>

    <!-- ============================================================ -->
    <h3>Image Alignment &amp; Resize</h3>
    <p class="m-demo-desc">
        Clicking any image in the editor reveals a small <strong>alignment toolbar</strong>
        above it with three modes:
    </p>
    <ul style="margin:0 0 1em 1.5em;line-height:1.8">
        <li><strong>Left</strong> — <code>float: left</code>. Text in the same paragraph wraps to the right of the image.</li>
        <li><strong>Centre</strong> — <code>display: block; margin: auto</code> (+ <code>text-align: center</code> on the parent paragraph as a fallback). The image sits on its own centred line with no text wrapping.</li>
        <li><strong>Right</strong> — <code>float: right</code>. Text in the same paragraph wraps to the left of the image.</li>
    </ul>
    <p class="m-demo-desc">
        Alignment is always available when the <code>'image'</code> tool is in the toolbar; no extra option is needed.
        Paragraphs containing floated images are automatically cleared (via CSS <code>overflow: hidden</code> in
        <code>.m-richtext</code> and a <code>::after</code> clearfix in the editor) so the float never bleeds
        into the next paragraph.
        <br><br>
        Enable <code>->allowImageResize()</code> to also show <strong>8-point drag handles</strong>
        around the selected image. Corner handles resize proportionally; edge handles scale
        on a single axis. The image's original natural dimensions are preserved in
        <code>data-original-width</code> / <code>data-original-height</code> attributes.
    </p>

    <?= $m->richTextEditor('rteImageResize')
        ->name('content_image_resize')
        ->value('<p>Click the image below to select it, then try each alignment button. Float left/right lets text wrap around the image; centre isolates it on its own line.</p><p><img src="https://picsum.photos/seed/manhattan/300/180" alt="Sample image" style="width:300px;"> Text that wraps to the right when the image is float-left. Try clicking Right align to make this text move to the other side instead.</p>')
        ->toolbar(['bold', 'italic', 'separator', 'align', 'separator', 'image'])
        ->allowImageResize()
        ->minHeight(200) ?>

    <?= demoCodeTabs(
        '// Image resize enabled — click any image to see handles and alignment bar
<?= $m->richTextEditor(\'contentEditor\')
    ->name(\'content\')
    ->toolbar([\'bold\', \'italic\', \'separator\', \'image\'])
    ->allowImageResize()
    ->minHeight(300) ?>

// Combined: upload + paste + resize + YouTube
<?= $m->richTextEditor(\'articleEditor\')
    ->name(\'content\')
    ->toolbar([\'bold\', \'italic\', \'separator\', \'image\', \'youtube\'])
    ->uploader(\'/articles/upload-image\', \'article_img\')
    ->allowPasteImages()
    ->allowImageResize()
    ->minHeight(300) ?>',
        '// Image alignment: clicking an image shows the mini-toolbar automatically.
// Three modes:
//   Left   — float:left  (text wraps to the right)
//   Centre — display:block + margin:auto (centred, no wrap)
//   Right  — float:right (text wraps to the left)

// Saved HTML examples:
// float left:   <img style="float:left;margin-right:1em;margin-bottom:0.5em" src="...">
// centre:       <p style="text-align:center"><img style="display:block;margin-left:auto;margin-right:auto" src="..."></p>
// float right:  <img style="float:right;margin-left:1em;margin-bottom:0.5em" src="...">

// Listen for changes after resize / alignment:
document.getElementById(\'contentEditor\')
    .addEventListener(\'m:rte:change\', function (e) {
        console.log(\'Updated HTML:\', e.detail.value);
    });'
    ) ?>

    <!-- ============================================================ -->
    <h3>YouTube Video Embed</h3>
    <p class="m-demo-desc">
        Adding <code>'youtube'</code> to the toolbar renders an <strong>Embed YouTube Video</strong> button
        (<i class="fab fa-youtube" style="color:#ff0000"></i>).
        Clicking it opens a dialog where you can paste any YouTube URL — watch links, shortened
        <code>youtu.be</code> links, embed URLs, or even bare 11-character video IDs.
        A responsive 16:9 iframe wrapper (<code>.m-rte-youtube-wrapper</code>) is inserted at the cursor.
        Videos are <strong>always block-centred</strong> — they cannot be floated. Width is adjustable
        via drag handles; the embed stays centred regardless of width.
        Videos are served via <code>youtube-nocookie.com</code> for privacy.
        A <strong>Video Credit</strong> line is automatically fetched from the YouTube oEmbed API and
        appended below each embed, linking to the video's channel by name. The credit is part of the
        saved HTML output and is styled via <code>.m-rte-youtube-credit</code>.
    </p>

    <?= $m->richTextEditor('rteYoutube')
        ->name('content_youtube')
        ->placeholder('Click the YouTube button to embed a video…')
        ->toolbar(['bold', 'italic', 'separator', 'image', 'youtube', 'separator', 'link'])
        ->minHeight(200) ?>

    <?= demoCodeTabs(
        '// Add \'youtube\' to the toolbar to enable the embed dialog
<?= $m->richTextEditor(\'blogEditor\')
    ->name(\'content\')
    ->toolbar([\'bold\', \'italic\', \'separator\', \'image\', \'youtube\', \'separator\', \'link\'])
    ->minHeight(300) ?>',
        '// YouTube embeds are inserted as a responsive .m-rte-youtube-wrapper div.
// A "Video Credit" line is automatically fetched from the YouTube oEmbed API
// and appended inside the wrapper, linking to the channel by name:
//   <p class="m-rte-youtube-credit">
//     <i class="fab fa-youtube"></i>
//     Video Credit: <a href="..." target="_blank" rel="noopener noreferrer">Channel Name</a>
//   </p>

// You can retrieve the full HTML (including embed + credit) via getValue():
var rte = m.richTextEditor(\'blogEditor\');
console.log(rte.getValue());

// The wrapper has contenteditable="false" so it is treated as an atom
// by the browser — select and delete it like any block element.

// When rendering stored content, apply .m-richtext to your wrapper
// so the responsive embed styles are automatically applied:
// <div class="m-richtext"><?= $savedHtml ?></div>'
    ) ?>

    <h3>Scrollable Input Area</h3>
    <p class="m-demo-desc">
        By default the editor auto-extends its height to fit all content, which can push the rest of the
        page down. Calling <code>->scrollable()</code> (optionally combined with <code>->maxHeight()</code>)
        constrains the editing area and scrolls the content <em>within</em> it using Apple-style thin overlay
        scrollbars — the same style used by the image viewer thumbstrip. The editor still auto-grows up to the
        specified max-height and then scrolls; the toolbar and footer stay visible at all times.
    </p>
    <?= $m->richTextEditor('rteScrollable')
        ->name('scroll_content')
        ->minHeight(150)
        ->maxHeight(280)
        ->scrollable()
        ->value('<p>This editor is capped at 280 px and scrolls internally once the content grows past that limit. Try typing several paragraphs to see the thin overlay scrollbar appear on hover.</p><p>The toolbar and character counter (if enabled) remain fixed outside the scrollable region, so they are always accessible.</p>') ?>

    <?= demoCodeTabs(
        '// Constrain the editor to 280 px and enable Apple-style thin scrollbar
<?= $m->richTextEditor(\'body\')
    ->name(\'body\')
    ->minHeight(150)
    ->maxHeight(280)
    ->scrollable() ?>

// scrollable() can also be used without maxHeight() — the thin scrollbar
// is applied regardless and will appear whenever the parent clips the editor:
<?= $m->richTextEditor(\'body\')
    ->name(\'body\')
    ->scrollable() ?>',
        '// No extra JS is required — scrollable() is purely a CSS enhancement.
// The scrollbar fades in on hover and disappears when idle.

// You can still read/set the value normally:
var rte = m.richTextEditor(\'body\');
console.log(rte.getValue());'
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
