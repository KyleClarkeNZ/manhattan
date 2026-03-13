<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-map-marker-alt') ?> Address</h2>
    <p class="m-demo-desc">NZ Post-powered address autocomplete with overseas manual-entry fallback. Proxies API keys server-side for security.</p>

    <h3>NZ Address (Autocomplete)</h3>
    <p>Start typing an NZ address below. The component queries a server-side proxy that forwards requests to the NZ Post API (or returns mock data when no API key is configured).</p>
    <div class="m-demo-row">
        <?= $m->address('demo-address-nz')
            ->suggestUrl('/nzpostSuggest')
            ->mode('nz') ?>
    </div>

    <h3>Overseas Address (Manual)</h3>
    <div class="m-demo-row">
        <?= $m->address('demo-address-overseas')
            ->mode('overseas') ?>
    </div>

    <div class="m-demo-output" id="address-output">Interact with an address field to see output...</div>

    <?= demoCodeTabs(
        '// NZ address with autocomplete
<?= $m->address(\'deliveryAddress\')
    ->suggestUrl(\'/nzpostSuggest\')
    ->mode(\'nz\') ?>

// Overseas manual input
<?= $m->address(\'intlAddress\')
    ->mode(\'overseas\') ?>

// Custom name prefix for form fields
<?= $m->address(\'billing\')
    ->namePrefix(\'billing_address\')
    ->suggestUrl(\'/nzpostSuggest\')
    ->mode(\'nz\') ?>',
        '// Get address instance
var addr = m.address(\'deliveryAddress\');

// Switch mode
addr.setMode(\'overseas\');
addr.setMode(\'nz\');

// Clear all fields
addr.clear();

// Listen for address selection
document.getElementById(\'deliveryAddress\')
    .addEventListener(\'m:address:select\', function(e) {
        console.log(\'Selected:\', e.detail);
    });

// Listen for mode changes
document.getElementById(\'deliveryAddress\')
    .addEventListener(\'m:address:mode\', function(e) {
        console.log(\'Mode:\', e.detail.mode);
    });'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->address($id)', 'string', 'Create an address component.'],
    ['->suggestUrl($url)', 'string', 'Server-side proxy URL for NZ Post suggestions.'],
    ['->mode($mode)', 'string', 'Initial mode: <code>nz</code> (autocomplete) or <code>overseas</code> (manual).'],
    ['->namePrefix($prefix)', 'string', 'Prefix for hidden field names (default: component ID).'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.address(id, opts)', 'string, ?object', 'Get or create address instance.'],
    ['setMode(mode)', 'string', 'Switch between <code>nz</code> and <code>overseas</code>.'],
    ['clear()', '', 'Clear all address fields.'],
]) ?>

<?= apiTable('JS Options', 'js', [
    ['minChars', 'int', 'Minimum characters before triggering autocomplete (default: 3).'],
    ['debounceMs', 'int', 'Debounce delay in milliseconds (default: 250).'],
    ['suggestUrl', 'string', 'Override the proxy URL for NZ Post suggestions.'],
    ['onChange', 'function(data)', 'Called when an address is selected or mode changes.'],
]) ?>

<?= eventsTable([
    ['m:address:mode', '{mode}', 'Fired when the address mode changes between NZ and overseas.'],
    ['m:address:select', '{suggestion, ...fields}', 'Fired when an NZ address suggestion is selected.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var nzAddr = document.getElementById('demo-address-nz');
    if (nzAddr) {
        nzAddr.addEventListener('m:address:mode', function(e) {
            setOutput('address-output', '<strong>Mode:</strong> ' + (e.detail.mode === 'nz' ? 'NZ Address' : 'Overseas Address'));
        });
        nzAddr.addEventListener('m:address:select', function() {
            setOutput('address-output', '<strong>Selected:</strong> NZ address suggestion');
        });
    }
});
</script>
