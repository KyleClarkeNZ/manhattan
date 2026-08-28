<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-photo-film') ?> MediaBrowser</h2>
    <p class="m-demo-desc">
        A modal image picker: browse the files already in a server-side folder, upload new
        ones, and hand the chosen file's URL back to a form field. Deliberately minimal —
        one flat folder, pick or upload, no rename, move or delete. It exists to replace
        heavyweight legacy file managers for applications that only ever used those two
        features.
    </p>
    <p class="m-demo-desc">
        Manhattan ships the interface only. <strong>The host application supplies the
        endpoint</strong>, because only it knows who is allowed to browse and upload, and
        where the files actually live. This demo talks to <code>/demo/mediaLibrary</code>,
        a working reference implementation in <code>demo/index.php</code> that writes to
        <code>demo/data/media/</code>.
    </p>

    <h3>Declarative — button opens it, input receives the URL</h3>
    <p class="m-demo-desc">
        With <code>trigger()</code> and <code>target()</code> set, no JavaScript is needed:
        the browser binds the button, writes the chosen URL into the input and fires a
        bubbling <code>change</code> event so existing form logic and previews react.
    </p>

    <div class="m-demo-row">
        <div class="m-demo-field" style="flex:1 1 320px">
            <?= $m->textbox('mb-cover')
                ->name('cover_image')
                ->label('Cover image')
                ->placeholder('/demo/media/example.jpg') ?>
        </div>
        <div class="m-demo-field" style="align-self:flex-end">
            <?= $m->button('mb-cover-browse', 'Browse')
                ->icon('fa-folder-open')
                ->outline()
                ->type('button') ?>
        </div>
    </div>

    <?= $m->mediaBrowser('mb-declarative')
        ->endpoint('/demo/mediaLibrary')
        ->folder('demo')
        ->title('Demo library')
        ->trigger('mb-cover-browse')
        ->target('mb-cover')
        ->maxBytes(8 * 1024 * 1024) ?>

<?= demoCodeTabs(
    <<<'PHP'
echo $m->textbox('cover_image')->name('cover_image')->label('Cover image');
echo $m->button('cover_browse', 'Browse')->icon('fa-folder-open')->type('button');

echo $m->mediaBrowser('coverBrowser')
    ->endpoint('/media.php')     // your endpoint — see the contract below
    ->folder('blog')             // a KEY your endpoint resolves, not a path
    ->title('Blog images')
    ->trigger('cover_browse')    // element id that opens the browser
    ->target('cover_image')      // input id that receives the chosen URL
    ->maxBytes(8 * 1024 * 1024);
PHP
) ?>
</div>

<div class="m-demo-section">
    <h3>Programmatic — open with a callback</h3>
    <p class="m-demo-desc">
        Call <code>open(fn)</code> to handle the choice yourself. The callback receives the
        whole file object, so you get dimensions and size as well as the URL, and it
        replaces the <code>target()</code> write for that opening only.
    </p>

    <div class="m-demo-row">
        <?= $m->button('mb-api-open', 'Pick an image')->icon('fa-images')->type('button') ?>
    </div>
    <div class="m-demo-output" id="mb-api-output">Nothing picked yet.</div>

    <?= $m->mediaBrowser('mb-programmatic')
        ->endpoint('/demo/mediaLibrary')
        ->folder('demo')
        ->title('Pick an image')
        ->selectLabel('Use this one') ?>

<?= demoCodeTabs(
    <<<'PHP'
echo $m->mediaBrowser('picker')
    ->endpoint('/media.php')
    ->folder('blog')
    ->selectLabel('Use this one');
PHP,
    <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    var picker = m.mediaBrowser('picker');

    document.getElementById('open-btn').addEventListener('click', function () {
        picker.open(function (file) {
            console.log(file.url, file.name, file.width, file.height);
        });
    });
});

// Or listen for the event instead of passing a callback:
document.getElementById('picker').addEventListener('m:mediabrowser:select', function (e) {
    console.log(e.detail.file.url);
});
JS
) ?>
</div>

<div class="m-demo-section">
    <h3>Picker only — no uploads</h3>
    <p class="m-demo-desc">
        <code>allowUpload(false)</code> hides the upload control and the drop zone, leaving a
        read-only picker over whatever the endpoint lists. The endpoint must still refuse
        uploads on its own — hiding the button is not a permission.
    </p>

    <div class="m-demo-row">
        <?= $m->button('mb-readonly-open', 'Browse library')->icon('fa-folder-open')->outline()->type('button') ?>
    </div>

    <?= $m->mediaBrowser('mb-readonly')
        ->endpoint('/demo/mediaLibrary')
        ->folder('demo')
        ->title('Existing images')
        ->trigger('mb-readonly-open')
        ->allowUpload(false)
        ->emptyMessage('This folder is empty.') ?>

<?= demoCodeTabs(
    <<<'PHP'
echo $m->mediaBrowser('libraryOnly')
    ->endpoint('/media.php')
    ->folder('blog')
    ->trigger('browse_btn')
    ->allowUpload(false)
    ->emptyMessage('This folder is empty.');
PHP
) ?>
</div>

<div class="m-demo-section">
    <h3>The server contract</h3>
    <p class="m-demo-desc">
        Two requests, both answered with JSON. Errors are any 4xx/5xx carrying
        <code>{"message": "…"}</code> — the message is shown to the user verbatim, so write
        it for them.
    </p>

<?= demoCodeTabs(
    <<<'PHP'
// LIST   GET <endpoint>?action=list&folder=<key>
{"files": [
    {"name":     "cover_1.jpg",
     "url":      "/images/blog/cover_1.jpg",
     "size":     84213,        // bytes,   optional
     "modified": 1756339200,   // unix ts, optional — used for sorting
     "width":    800,          // pixels,  optional
     "height":   533}          // pixels,  optional
]}

// UPLOAD POST <endpoint>   multipart/form-data
//        fields: action=upload, folder=<key>, file=<binary>
{"file": { ...same shape as a list entry... }}

// ERROR  any 4xx/5xx
{"message": "That image is larger than 8 MB."}
PHP
) ?>

    <h3>Writing the endpoint safely</h3>
    <p class="m-demo-desc">
        <code>folder</code> is a <strong>key</strong>, not a path. It arrives from the
        browser, so it must never be concatenated into a filesystem path — resolve it
        against a fixed whitelist and reject anything else. The endpoint is also the only
        place authorisation can live: Manhattan has no notion of a session or a user, so
        gate it exactly as you gate the screen that renders the component.
    </p>

<?= demoCodeTabs(
    <<<'PHP'
<?php
// media.php — host application endpoint

require_once 'vendor/autoload.php';
require_once 'auth.php';

// 1. Gate it. Manhattan cannot do this for you.
if (!current_user_is_admin()) {
    http_response_code(403);
    exit(json_encode(['message' => 'Not authorised.']));
}

// 2. Resolve the folder KEY against a whitelist — never build a path from input.
$folders = [
    'blog' => ['dir' => __DIR__ . '/images/blog/', 'url' => '/images/blog/'],
];
$key = $_REQUEST['folder'] ?? '';
if (!isset($folders[$key])) {
    http_response_code(400);
    exit(json_encode(['message' => 'Unknown folder.']));
}

// 3. On upload, trust the bytes — not the filename or the declared MIME type.
$info = @getimagesize($_FILES['file']['tmp_name']);
$types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png',
          IMAGETYPE_GIF  => 'gif', IMAGETYPE_WEBP => 'webp'];
if ($info === false || !isset($types[$info[2]])) {
    http_response_code(422);
    exit(json_encode(['message' => 'Only JPEG, PNG, GIF and WebP images can be uploaded.']));
}

// 4. Name the file yourself, so a crafted filename cannot escape the folder.
$dest = $folders[$key]['dir'] . 'img_' . bin2hex(random_bytes(6)) . '.' . $types[$info[2]];
move_uploaded_file($_FILES['file']['tmp_name'], $dest);
PHP
) ?>
</div>

<div class="m-demo-section">
    <h2>API Reference</h2>

<?= apiTable('PHP Methods', 'php', [
    ['endpoint',     'string $url',       'URL of the host endpoint. <strong>Required.</strong>'],
    ['folder',       'string $key',       'Folder key sent to the endpoint as <code>folder</code>. A logical name resolved server-side against a whitelist — never a filesystem path.'],
    ['title',        'string $title',     'Heading shown in the modal. Default <code>Media library</code>.'],
    ['trigger',      'string $elementId', 'Id of an element whose click opens the browser. Bound with a delegated listener, so the trigger may be rendered later or injected dynamically.'],
    ['target',       'string $inputId',   'Id of the input that receives the chosen URL. A bubbling <code>change</code> is fired on it.'],
    ['accept',       'string $accept',    '<code>accept</code> attribute for the upload file input. Default images only.'],
    ['allowUpload',  'bool $allow',       'Hide the upload control and drop zone, leaving a read-only picker.'],
    ['showFilter',   'bool $show',        'Hide the filename filter box.'],
    ['maxBytes',     'int $bytes',        'Client-side size ceiling; oversized files are rejected before upload. The endpoint must enforce its own limit regardless.'],
    ['emptyMessage', 'string $message',   'Message shown when the folder is empty.'],
    ['selectLabel',  'string $label',     'Label on the confirm button. Default <code>Select</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.mediaBrowser', 'id',              'Get (or initialise) the browser instance for a container id.'],
    ['open',           'fn? callback',    'Open the modal. An optional callback receives the chosen file object and replaces the <code>target()</code> write for that opening.'],
    ['close',          '—',               'Close the modal without selecting.'],
    ['refresh',        '—',               'Discard the cached listing and re-fetch from the server.'],
    ['getSelected',    '—',               'The currently highlighted file object, or <code>null</code>.'],
    ['setFolder',      'string key',      'Change the folder key and discard the cached listing.'],
    ['element',        '—',               'The container element.'],
]) ?>

<?= eventsTable([
    ['m:mediabrowser:open',   '{ id }',          'Fired when the modal opens.'],
    ['m:mediabrowser:select', '{ id, file }',    'Fired when a file is confirmed, before the target input is written.'],
    ['m:mediabrowser:upload', '{ id, file }',    'Fired after a successful upload, with the file the endpoint returned.'],
    ['m:mediabrowser:close',  '{ id }',          'Fired when the modal closes.'],
]) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var picker = m.mediaBrowser('mb-programmatic');
    var output = document.getElementById('mb-api-output');

    document.getElementById('mb-api-open').addEventListener('click', function () {
        picker.open(function (file) {
            output.textContent = file.name + ' — ' + file.url
                + (file.width ? ' (' + file.width + '×' + file.height + ')' : '');
        });
    });
});
</script>
