<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-spinner') ?> Loader</h2>
    <p class="m-demo-desc">
        Inline and overlay spinner for indicating asynchronous activity.
        Text and an <strong>animated ellipsis</strong> can be added optionally.
        Default: no text, <code>md</code> size, inline mode.
    </p>

    <!-- Basic -->
    <h3>Basic (spinner only)</h3>
    <p class="m-demo-desc">By default no text is shown — just the spinner.</p>
    <div class="m-demo-row">
        <?= $m->loader('demo-loader-basic') ?>
    </div>
    <?= demoCodeTabs(
        '$m->loader(\'demo-loader-basic\')',
        '// No JS API needed for a static loader'
    ) ?>

    <!-- With text -->
    <h3>With text</h3>
    <p class="m-demo-desc">Use <code>->text()</code> to show a label beside the spinner.</p>
    <div class="m-demo-row">
        <?= $m->loader('demo-loader-text')->text('Loading') ?>
        <?= $m->loader('demo-loader-text-sm')->text('Fetching data')->size('sm') ?>
        <?= $m->loader('demo-loader-text-lg')->text('Please wait')->size('lg') ?>
    </div>
    <?= demoCodeTabs(
        '$m->loader(\'my-loader\')->text(\'Loading\')' . "\n" .
        '$m->loader(\'my-loader-sm\')->text(\'Fetching data\')->size(\'sm\')' . "\n" .
        '$m->loader(\'my-loader-lg\')->text(\'Please wait\')->size(\'lg\')',
        '// No JS API needed for a static loader'
    ) ?>

    <!-- Animated dots -->
    <h3>Animated ellipsis</h3>
    <p class="m-demo-desc">
        Call <code>->animateDots()</code> to append a staggered three-dot animation after the text.
        Requires <code>->text()</code> to be set.
    </p>
    <div class="m-demo-row">
        <?= $m->loader('demo-loader-dots')->text('Loading')->animateDots() ?>
        <?= $m->loader('demo-loader-decrypt')->text('Decrypting Messages')->animateDots() ?>
        <?= $m->loader('demo-loader-dots-lg')->text('Uploading')->animateDots()->size('lg') ?>
    </div>
    <?= demoCodeTabs(
        '$m->loader(\'my-loader\')->text(\'Loading\')->animateDots()' . "\n" .
        '$m->loader(\'decrypt-loader\')->text(\'Decrypting Messages\')->animateDots()' . "\n" .
        '$m->loader(\'upload-loader\')->text(\'Uploading\')->animateDots()->size(\'lg\')',
        '// No JS API needed for a static loader'
    ) ?>

    <!-- Overlay -->
    <h3>Overlay mode</h3>
    <p class="m-demo-desc">
        Use <code>->overlay()</code> on a <code>position:relative</code> parent to show the loader
        centred over a content area. The parent must have <code>position: relative</code>.
        Click the button to toggle.
    </p>
    <div class="m-demo-row">
        <?= $m->button('demo-loader-overlay-btn', 'Toggle overlay loader')->primary()->icon('fa-eye') ?>
    </div>
    <div id="demo-loader-overlay-wrap" style="position:relative; min-height:120px; background:#f5f5f5; border-radius:8px; display:flex; align-items:center; justify-content:center; padding:2rem; margin-bottom:1rem;">
        <p style="margin:0; color:#888;">Content area — the overlay covers this.</p>
        <?= $m->loader('demo-loader-overlay')->text('Loading')->animateDots()->overlay()->hidden() ?>
    </div>
    <?= demoCodeTabs(
        '// Parent must be position:relative' . "\n" .
        '$m->loader(\'my-overlay\')->text(\'Loading\')->animateDots()->overlay()->hidden()',
        'var loader = document.getElementById(\'my-overlay\');' . "\n" .
        '// Show' . "\n" .
        'loader.classList.remove(\'m-hidden\');' . "\n" .
        '// Hide' . "\n" .
        'loader.classList.add(\'m-hidden\');'
    ) ?>

    <!-- JS show/hide -->
    <h3>Show / hide via JavaScript</h3>
    <p class="m-demo-desc">Toggle the <code>m-hidden</code> class to show or hide a loader at any time.</p>
    <div class="m-demo-row">
        <?= $m->button('demo-loader-show-btn', 'Show loader')->primary()->icon('fa-eye') ?>
        <?= $m->button('demo-loader-hide-btn', 'Hide loader')->icon('fa-eye-slash') ?>
    </div>
    <?= $m->loader('demo-loader-controlled')->text('Working')->animateDots()->hidden() ?>

    <div class="m-demo-output" id="loader-output" style="margin-top:1rem;">Loader is hidden.</div>

    <?= demoCodeTabs(
        '$m->loader(\'my-loader\')->text(\'Working\')->animateDots()->hidden()',
        'var loader = document.getElementById(\'my-loader\');' . "\n\n" .
        '// Show' . "\n" .
        'loader.classList.remove(\'m-hidden\');' . "\n\n" .
        '// Hide' . "\n" .
        'loader.classList.add(\'m-hidden\');'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->loader($id)',          'string', 'Create a loader instance. No text or animation by default.'],
    ['->text($str)',             'string', 'Label displayed beside the spinner. Default: <code>\'\'</code> (none).'],
    ['->animateDots($bool)',     'bool',   'Append an animated three-dot ellipsis after the text. Default: <code>false</code>.'],
    ['->size($size)',            'string', 'Spinner size: <code>sm</code>, <code>md</code> (default), <code>lg</code>.'],
    ['->overlay($bool)',        'bool',   'Cover a <code>position:relative</code> parent. Default: <code>false</code> (inline).'],
    ['->hidden($bool)',         'bool',   'Start hidden (<code>m-hidden</code> class). Default: <code>false</code>.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Overlay demo
    var overlayBtn  = document.getElementById('demo-loader-overlay-btn');
    var overlayEl   = document.getElementById('demo-loader-overlay');
    if (overlayBtn && overlayEl) {
        overlayBtn.addEventListener('click', function () {
            overlayEl.classList.toggle('m-hidden');
        });
    }

    // Show/hide demo
    var showBtn     = document.getElementById('demo-loader-show-btn');
    var hideBtn     = document.getElementById('demo-loader-hide-btn');
    var ctrlLoader  = document.getElementById('demo-loader-controlled');
    var loaderOut   = document.getElementById('loader-output');

    if (showBtn && ctrlLoader) {
        showBtn.addEventListener('click', function () {
            ctrlLoader.classList.remove('m-hidden');
            if (loaderOut) loaderOut.textContent = 'Loader is visible.';
        });
    }
    if (hideBtn && ctrlLoader) {
        hideBtn.addEventListener('click', function () {
            ctrlLoader.classList.add('m-hidden');
            if (loaderOut) loaderOut.textContent = 'Loader is hidden.';
        });
    }
});
</script>
