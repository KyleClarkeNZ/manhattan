<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-layer-group') ?> Wizard</h2>
    <p class="m-demo-desc">
        A multi-step form wizard with a visual step-progress strip, per-step validation,
        skippable steps, remote data binding, and structured AJAX submission.
        All field values are collected across steps and submitted as a single payload
        that includes a <code>_wizard</code> metadata block.
    </p>

    <!-- =====================================================================
         Example 1 – Registration wizard (validation + skip)
    ====================================================================== -->
    <h3>Registration Wizard (validation &amp; skip)</h3>
    <p class="m-demo-desc">
        Three-step registration flow.  The <em>Account</em> step requires a username
        and e-mail before you can continue.  The <em>Profile</em> step is optional and
        can be skipped.  The <em>Confirmation</em> step shows a review panel and
        triggers AJAX submission.
    </p>

    <?php
    /* ── Step 1: Account — Form component + separate Validator ──────────── */
    $step1Form = $m->form('reg-step1-form')
        ->noCsrf()
        ->noValidation()
        ->formAttr('onsubmit', 'return false')
        ->field($m->textbox('reg-username')->name('username')->placeholder('Choose a username…'),
            'Username')
        ->field($m->textbox('reg-email')->name('email')->placeholder('you@example.com'),
            'Email Address')
        ->field($m->textbox('reg-password')->name('password')
                ->placeholder('At least 8 characters…')->attr('type', 'password'),
            'Password');

    $step1Validator = $m->validator('reg-step1-form')
        ->field('reg-username', 'Username is required.', ['required'])
        ->field('reg-email',    'Please enter a valid email address.', ['required', 'email'])
        ->field('reg-password', 'Password must be at least 8 characters.', ['required', ['minLength' => 8]]);

    $step1 = (string)$step1Form . (string)$step1Validator;

    /* ── Step 2: Profile (optional / skippable) — Form, no validation ───── */
    $step2 = '<p class="m-demo-desc" style="margin-top:0">These fields are optional — you can skip this step.</p>'
        . (string)$m->form('reg-step2-form')
            ->noCsrf()
            ->noValidation()
            ->formAttr('onsubmit', 'return false')
            ->field($m->textbox('reg-fullname')->name('full_name')->placeholder('Your full name'),
                'Full Name')
            ->field($m->datepicker('reg-dob')->name('date_of_birth'),
                'Date of Birth')
            ->field($m->textarea('reg-bio')->name('bio')->placeholder('Tell us a little about yourself…'),
                'Short Bio');

    /* ── Step 3: Confirmation — review panel only, no form fields ────────── */
    $step3 = '<p class="m-demo-desc" style="margin-top:0">'
        . 'Review your details below, then click <strong>Create Account</strong> to complete registration.</p>'
        . '<div id="reg-review" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:1.25rem 1.5rem;">'
        . '<p style="margin:0;color:#64748b;font-style:italic">Your details will appear here when you reach this step.</p>'
        . '</div>';

    echo $m->wizard('regWizard')
        ->step('account', 'Account')
            ->icon('fa-user')
            ->content($step1)
            ->useValidator('reg-step1-form')
            ->validationMessage('Please fill in your username, email, and password before continuing.')
        ->step('profile', 'Profile')
            ->icon('fa-id-card')
            ->content($step2)
            ->skippable()
        ->step('confirm', 'Confirm')
            ->icon('fa-check-circle')
            ->content($step3)
        ->submitUrl('/demo/wizardSubmit')
        ->submitText('Create Account')
        ->onComplete('regWizardComplete')
        ->onStepChange('regWizardStepChange');
    ?>

    <div class="m-demo-output" id="wizard-output">Wizard output will appear here after submission&hellip;</div>

    <?= demoCodeTabs(
        '// Build step content using Manhattan Form + Validator
// The Form component handles layout; Validator handles inline field errors.

$step1Form = $m->form(\'reg-step1-form\')
    ->noCsrf()           // Wizard handles CSRF via X-CSRF-Token header
    ->noValidation()     // Validator is attached manually below
    ->formAttr(\'onsubmit\', \'return false\')  // Prevent Enter-key submission
    ->field($m->textbox(\'reg-username\')->name(\'username\'), \'Username\')
    ->field($m->textbox(\'reg-email\'   )->name(\'email\'),    \'Email Address\')
    ->field($m->textbox(\'reg-password\')->name(\'password\')->attr(\'type\', \'password\'), \'Password\');

$step1Validator = $m->validator(\'reg-step1-form\')
    ->field(\'reg-username\', \'Username is required.\',                    [\'required\'])
    ->field(\'reg-email\',    \'Please enter a valid email address.\',      [\'required\', \'email\'])
    ->field(\'reg-password\', \'Password must be at least 8 characters.\',  [\'required\', [\'minLength\' => 8]]);

$step1 = (string)$step1Form . (string)$step1Validator;

echo $m->wizard(\'regWizard\')
    ->step(\'account\', \'Account\')
        ->icon(\'fa-user\')
        ->content($step1)
        ->useValidator(\'reg-step1-form\')   // delegate validation to Validator
        ->validationMessage(\'Please fill in all required fields.\')
    ->step(\'profile\', \'Profile\')
        ->icon(\'fa-id-card\')
        ->content($profileContent)
        ->skippable()
    ->step(\'confirm\', \'Confirm\')
        ->icon(\'fa-check-circle\')
        ->content($confirmContent)
    ->submitUrl(\'/orders/create\')
    ->submitText(\'Create Account\')
    ->onComplete(\'handleComplete\')
    ->onStepChange(\'handleStepChange\');',

        'function regWizardComplete(response) {
    m.toaster(\'appToaster\').show(response.message || \'Done!\', \'success\');
}

function regWizardStepChange(event) {
    // event = { from, to, direction, wizard }
    // return false to cancel navigation
    return true;
}

// Programmatic control
var wiz = m.wizard(\'regWizard\');
wiz.next();              // validate then advance
wiz.prev();              // go back
wiz.skip();              // skip current step (if skippable)
wiz.goTo(2);             // jump to step index 2 (click completed step circle to go back)
wiz.submit();            // trigger final submission
wiz.reset();             // back to step 0, clear data + reset all validators
wiz.getCurrentStep();    // → { index, key, title, skippable, validateFields, validatorFormId, … }
wiz.getData();           // → full payload including _wizard meta-block'
    ) ?>
</div>

<!-- =====================================================================
     Example 2 – Order wizard (remote data source)
====================================================================== -->
<div class="m-demo-section">
    <h3>Order Wizard (remote data source)</h3>
    <p class="m-demo-desc">
        Demonstrates <code>->dataUrl()</code>: on initialisation the wizard fetches
        data from the server and pre-populates fields.  The submit payload mirrors
        the structured <code>_wizard</code> metadata so the server knows exactly which
        steps were completed, skipped, and what data was entered at each step.
    </p>

    <?php
    /* ── Order step 1: Customer — Form + Validator ───────────────────────── */
    $orderStep1Form = $m->form('ord-step1-form')
        ->noCsrf()
        ->noValidation()
        ->formAttr('onsubmit', 'return false')
        ->field($m->textbox('ord-customer')->name('customer_name')->placeholder('Customer name…'),
            'Customer Name')
        ->field($m->textbox('ord-customer-email')->name('customer_email')->placeholder('customer@example.com'),
            'Customer Email');

    $orderStep1Validator = $m->validator('ord-step1-form')
        ->field('ord-customer',       'Customer name is required.', ['required'])
        ->field('ord-customer-email', 'Please enter a valid email address.', ['required', 'email']);

    $orderStep1 = (string)$orderStep1Form . (string)$orderStep1Validator;

    /* ── Order step 2: Items — Form + Validator ──────────────────────────── */
    $orderStep2Form = $m->form('ord-step2-form')
        ->noCsrf()
        ->noValidation()
        ->formAttr('onsubmit', 'return false')
        ->field($m->dropdown('ord-product')
                ->name('product')
                ->dataSource([
                    ['value' => 'widget-a', 'text' => 'Widget A — $49.99'],
                    ['value' => 'widget-b', 'text' => 'Widget B — $89.99'],
                    ['value' => 'gadget',   'text' => 'Gadget Pro — $129.99'],
                ])
                ->placeholder('Select a product…'),
            'Product')
        ->field($m->numberbox('ord-qty')->name('quantity')->min(1)->max(99)->value(1),
            'Quantity');

    $orderStep2Validator = $m->validator('ord-step2-form')
        ->field('ord-product', 'Please select a product.', ['required']);

    $orderStep2 = (string)$orderStep2Form . (string)$orderStep2Validator;

    /* ── Order step 3: Delivery — Form + Validator ───────────────────────── */
    $orderStep3Form = $m->form('ord-step3-form')
        ->noCsrf()
        ->noValidation()
        ->formAttr('onsubmit', 'return false')
        ->field($m->datepicker('ord-delivery-date')->name('delivery_date')->min(date('Y-m-d')),
            'Preferred Delivery Date')
        ->field($m->textarea('ord-notes')->name('delivery_notes')->placeholder('Any special instructions…'),
            'Delivery Notes')
        ->field($m->checkbox('ord-gift')->name('is_gift')->label('This is a gift'),
            '');

    $orderStep3Validator = $m->validator('ord-step3-form')
        ->field('ord-delivery-date', 'Please select a delivery date.', ['required']);

    $orderStep3 = (string)$orderStep3Form . (string)$orderStep3Validator;

    /* ── Order step 4: Review — summary panel only, no form fields ───────── */
    $orderStep4 = '<p class="m-demo-desc" style="margin-top:0">Review your order — then click <strong>Place Order</strong>.</p>'
        . '<div id="order-review" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:1.25rem 1.5rem;">'
        . '<p style="margin:0;color:#64748b;font-style:italic">Order summary will appear here.</p>'
        . '</div>';

    echo $m->wizard('orderWizard')
        ->step('customer', 'Customer')
            ->icon('fa-user-tie')
            ->content($orderStep1)
            ->useValidator('ord-step1-form')
        ->step('items', 'Items')
            ->icon('fa-box')
            ->content($orderStep2)
            ->useValidator('ord-step2-form')
        ->step('delivery', 'Delivery')
            ->icon('fa-truck')
            ->content($orderStep3)
            ->useValidator('ord-step3-form')
        ->step('review', 'Review')
            ->icon('fa-clipboard-check')
            ->content($orderStep4)
        ->submitUrl('/demo/wizardSubmit')
        ->dataUrl('/demo/wizardData')
        ->submitText('Place Order')
        ->onComplete('orderWizardComplete');
    ?>

    <div class="m-demo-output" id="order-wizard-output">Order wizard output will appear here&hellip;</div>

    <?= demoCodeTabs(
        '// Order wizard with 4 steps and remote data source
echo $m->wizard(\'orderWizard\')
    ->step(\'customer\', \'Customer\')
        ->icon(\'fa-user-tie\')
        ->content($customerContent)
        ->validateFields([\'ord-customer\', \'ord-customer-email\'])
    ->step(\'items\', \'Items\')
        ->icon(\'fa-box\')
        ->content($itemsContent)
        ->validateFields([\'ord-product\'])
    ->step(\'delivery\', \'Delivery\')
        ->icon(\'fa-truck\')
        ->content($deliveryContent)
        ->validateFields([\'ord-delivery-date\'])
    ->step(\'review\', \'Review\')
        ->icon(\'fa-clipboard-check\')
        ->content($reviewContent)
    ->submitUrl(\'/orders/create\')
    ->dataUrl(\'/orders/formData\')   // GET – pre-populate fields
    ->submitText(\'Place Order\')
    ->onComplete(\'orderWizardComplete\');',

        '// Server response from dataUrl:
// { "success": true, "data": { "ord-customer": "Acme Corp", ... } }
// Fields are populated by matching keys to element IDs / name attributes.

// Submission payload sent to submitUrl (JSON):
// {
//   "customer_name": "Acme Corp",
//   "customer_email": "acme@example.com",
//   "product": "widget-a",
//   "quantity": "2",
//   "delivery_date": "2026-04-01",
//   "_wizard": {
//     "id": "orderWizard",
//     "currentStep": "review",
//     "currentStepIndex": 3,
//     "completedSteps": ["customer", "items", "delivery", "review"],
//     "skippedSteps": [],
//     "totalSteps": 4,
//     "stepData": {
//       "customer": { "customer_name": "Acme Corp", ... },
//       "items":    { "product": "widget-a", ... },
//       "delivery": { ... },
//       "review":   {}
//     }
//   }
// }

function orderWizardComplete(response) {
    document.getElementById(\'order-wizard-output\').innerHTML =
        \'<strong>Order placed!</strong> Reference: \' + (response.ref || \'N/A\');
}'
    ) ?>
</div>

<!-- =====================================================================
     API Reference
====================================================================== -->
<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->wizard($id)', 'string', 'Create a wizard instance.'],
    ['->step($key, $title)', 'string, string', 'Add a new step. All following step-scoped calls apply to this step until the next <code>->step()</code>.'],
    ['->icon($icon)', 'string', 'Font Awesome icon for the step circle, e.g. <code>fa-user</code> or <code>fas fa-user</code>. When omitted the step number is shown.'],
    ['->content($html)', 'string', 'HTML content rendered inside this step\'s panel. Place Manhattan form components here.'],
    ['->skippable()', 'bool?', 'Mark the step as skippable. A "Skip Step" button appears in the footer. Default: <code>false</code>.'],
    ['->validateFields($ids)', 'string[]', 'Bespoke validation: array of element IDs or <code>name</code> attributes that must be non-empty before advancing. Used when <em>not</em> using a Form + Validator.'],
    ['->useValidator($formId)', 'string', 'Delegate this step\'s validation to a Manhattan Validator attached to the <code>&lt;form&gt;</code> with the given ID. Takes precedence over <code>->validateFields()</code>. The Form component\'s inline field errors are used; the wizard banner shows the summary message.'],
    ['->validationMessage($msg)', 'string', 'Override the inline error message shown when required fields are empty.'],
    ['->submitUrl($url)', 'string', 'URL to POST the wizard payload to on final submission.'],
    ['->dataUrl($url)', 'string', 'URL to GET initial field values from. Response: <code>{"success":true,"data":{…}}</code>.'],
    ['->submitMethod($method)', 'string', 'HTTP method for submission. Default: <code>POST</code>.'],
    ['->ajaxSubmit()', 'bool?', 'Submit via fetch() AJAX (default: <code>true</code>). Pass <code>false</code> for a standard form POST.'],
    ['->onComplete($fn)', 'string', 'Name of a JS function invoked on successful submission. Receives the parsed server response.'],
    ['->onStepChange($fn)', 'string', 'Name of a JS function called before each step change. Return <code>false</code> to cancel navigation.'],
    ['->nextText($text)', 'string', 'Label for the Next button. Default: <code>Next</code>.'],
    ['->prevText($text)', 'string', 'Label for the Back button. Default: <code>Back</code>.'],
    ['->skipText($text)', 'string', 'Label for the Skip button. Default: <code>Skip Step</code>.'],
    ['->submitText($text)', 'string', 'Label for the Submit button. Default: <code>Submit</code>.'],
    ['->showStepCounter()', 'bool?', 'Show/hide the "Step X of Y" counter. Default: <code>true</code>.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.wizard(id, options?)', 'string, object?', 'Get (or init) the wizard instance for the given element ID.'],
    ['next()', '', 'Validate the current step then advance to the next. Returns <code>false</code> if validation fails.'],
    ['prev()', '', 'Go to the previous step (no validation).'],
    ['skip()', '', 'Skip the current step (only if it is marked skippable). Returns <code>false</code> otherwise.'],
    ['goTo(index)', 'number', 'Jump to a specific step by zero-based index. Does NOT validate. Completed-step circles are also clickable to navigate back.'],
    ['submit()', '', 'Validate the current step then submit the collected payload.'],
    ['reset()', '', 'Return to step 0 and clear all collected data.'],
    ['getCurrentStep()', '', 'Returns the config object for the active step: <code>{index, key, title, skippable, validateFields}</code>.'],
    ['getData()', '', 'Returns the full payload that would be sent on submission, including the <code>_wizard</code> meta-block.'],
    ['setFieldValue(fieldId, value)', 'string, any', 'Programmatically set a field value (supports Manhattan component wrappers).'],
]) ?>

<?= eventsTable([
    ['m:wizard:stepchange',  '{ from, to, direction, wizard }', 'Fired on the wizard element before navigation. Call <code>event.preventDefault()</code> or return <code>false</code> from the <code>onStepChange</code> callback to cancel.'],
    ['m:wizard:stepchanged', '{ from, to, direction, wizard }', 'Fired after a successful step transition.'],
    ['m:wizard:validate',    '{ step, fields }',                'Fired when step validation fails. <code>fields</code> is an array of invalid input elements.'],
    ['m:wizard:submit',      '{ data }',                        'Fired immediately before the AJAX/form submission.  <code>data</code> is the full payload.'],
    ['m:wizard:complete',    '{ response }',                    'Fired on a successful submission response.'],
    ['m:wizard:error',       '{ error, response }',             'Fired when the submission fails (network error or server error).'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.m) return;

    /* ── Registration wizard callbacks ─────────────────────────────────── */
    window.regWizardComplete = function (response) {
        var out = document.getElementById('wizard-output');
        if (out) {
            out.innerHTML = '<strong>'
                + (response.message || 'Registration successful!')
                + '</strong>'
                + '<pre style="margin:.75rem 0 0;font-size:.8125rem;text-align:left;overflow:auto;">'
                + JSON.stringify(response, null, 2)
                + '</pre>';
        }
        if (window.m && m.toaster) {
            m.toaster('appToaster').show(response.message || 'Registration complete!', 'success');
        }
    };

    window.regWizardStepChange = function (event) {
        // Build a review panel when reaching the confirm step
        if (event.to === 2) {
            setTimeout(function () {
                var wiz  = m.wizard('regWizard');
                var data = wiz.getData();
                var html = '<table style="width:100%;border-collapse:collapse;font-size:.875rem;">';
                ['username', 'email', 'full_name', 'date_of_birth', 'bio'].forEach(function (k) {
                    var v = data[k];
                    if (v) {
                        html += '<tr><td style="padding:.4rem .75rem;border-bottom:1px solid #e2e8f0;color:#64748b;width:40%">'
                            + k.replace(/_/g, ' ')
                            + '</td><td style="padding:.4rem .75rem;border-bottom:1px solid #e2e8f0;">'
                            + String(v)
                            + '</td></tr>';
                    }
                });
                html += '</table>';
                var panel = document.getElementById('reg-review');
                if (panel) panel.innerHTML = html;
            }, 50);
        }
        return true;
    };

    /* ── Order wizard callbacks ─────────────────────────────────────────── */
    window.orderWizardComplete = function (response) {
        var out = document.getElementById('order-wizard-output');
        if (out) {
            out.innerHTML = '<strong>'
                + (response.message || 'Order submitted!')
                + '</strong>';
        }
        if (window.m && m.toaster) {
            m.toaster('appToaster').show(response.message || 'Order placed!', 'success');
        }
    };

    // Build order review when the review step becomes active
    var orderWiz = m.wizard('orderWizard');
    if (orderWiz) {
        document.getElementById('orderWizard').addEventListener('m:wizard:stepchanged', function (e) {
            if (e.detail.to === 3) {
                setTimeout(function () {
                    var data = orderWiz.getData();
                    var html = '<table style="width:100%;border-collapse:collapse;font-size:.875rem;">';
                    ['customer_name', 'customer_email', 'product', 'quantity', 'delivery_date', 'is_gift'].forEach(function (k) {
                        var v = data[k];
                        if (v !== undefined && v !== '') {
                            html += '<tr><td style="padding:.4rem .75rem;border-bottom:1px solid #e2e8f0;color:#64748b;width:40%">'
                                + k.replace(/_/g, ' ')
                                + '</td><td style="padding:.4rem .75rem;border-bottom:1px solid #e2e8f0;">'
                                + String(v)
                                + '</td></tr>';
                        }
                    });
                    html += '</table>';
                    var panel = document.getElementById('order-review');
                    if (panel) panel.innerHTML = html;
                }, 50);
            }
        });
    }
});
</script>
