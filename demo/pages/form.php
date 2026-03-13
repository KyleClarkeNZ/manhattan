<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-edit') ?> Form</h2>
    <p class="m-demo-desc">
        A server-side form wrapper that handles <code>form-group</code> layout, labels with
        required-field indicators, hint text, automatic CSRF tokens, and an auto-generated
        client-side Validator — all from a single fluent builder.
        The Form component is purely PHP; fields with <code>required</code> in their rules
        automatically display a red asterisk.
    </p>

    <!-- ── Section 1: Auto-validating form ─────────────────────────────── -->
    <h3>Auto-validating Form</h3>
    <p class="m-demo-desc">
        Pass rules as the third argument to <code>->field()</code>. The Form generates a
        <code>Validator</code> automatically, deriving error messages from the label text.
        Override with the optional fourth argument. CSRF token injected by default on POST.
    </p>

    <?= $m->form('demoContactForm')
        ->noCsrf()
        ->formAttr('onsubmit', 'return handleContactSubmit(event)')
        ->field(
            $m->textbox('cf-name')->name('name')->placeholder('Your full name…'),
            'Full Name',
            ['required']
        )
        ->field(
            $m->textbox('cf-email')->name('email')->placeholder('you@example.com'),
            'Email Address',
            ['required', 'email']
        )
        ->field(
            $m->textarea('cf-message')->name('message')->placeholder('Your message…')->rows(4),
            'Message',
            ['required', ['minLength' => 10]],
            'Message must be at least 10 characters.',  // custom error override
            'Be as specific as you can.'                // hint text
        )
        ->submit('Send Message', 'fa-paper-plane')->primary() ?>

    <div class="m-demo-output" id="form-output">Fill in the form and click Send Message...</div>

    <?= demoCodeTabs(
        '// ->field($component, $label, $rules, $errorMessage, $hint, $wrapperClass)
// Rules drive the auto-generated Validator; labels power the default error messages.
<?= $m->form(\'contactForm\')
    ->action(\'/contact/submit\')
    ->field(
        $m->textbox(\'cf-name\')->name(\'name\'),
        \'Full Name\',
        [\'required\']                       // error: "Please fill in the full name field."
    )
    ->field(
        $m->textbox(\'cf-email\')->name(\'email\'),
        \'Email Address\',
        [\'required\', \'email\']             // flag rules are plain strings
    )
    ->field(
        $m->textarea(\'cf-message\')->name(\'message\'),
        \'Message\',
        [\'required\', [\'minLength\' => 10]], // value rules are single-key arrays
        \'Message must be at least 10 characters.\',   // explicit error override
        \'Be as specific as you can.\'                 // hint text shown below field
    )
    ->submit(\'Send Message\', \'fa-paper-plane\')->primary() ?>'
    ) ?>
</div>

<!-- ── Section 2: Model binding ─────────────────────────────────────── -->
<div class="m-demo-section">
    <h3>Model Binding</h3>
    <p class="m-demo-desc">
        Pass an associative array or object to <code>->model()</code> to pre-populate field values.
        Keys are matched to each component's <code>name()</code> attribute via <code>value()</code>.
    </p>

    <?php
    $existingUser = [
        'display_name' => 'Jane Smith',
        'email'        => 'jane@example.com',
        'bio'          => 'Full-stack developer and coffee enthusiast.',
    ];
    ?>

    <?= $m->form('demoEditForm')
        ->noCsrf()
        ->formAttr('onsubmit', 'return handleEditSubmit(event)')
        ->model($existingUser)
        ->field(
            $m->textbox('ef-name')->name('display_name'),
            'Display Name',
            ['required']
        )
        ->field(
            $m->textbox('ef-email')->name('email'),
            'Email Address',
            ['required', 'email']
        )
        ->field(
            $m->textarea('ef-bio')->name('bio')->rows(3),
            'Bio',
            [['maxLength' => 200]],
            '',
            'Max 200 characters.'
        )
        ->submit('Save Changes', 'fa-save')->primary() ?>

    <div class="m-demo-output" id="edit-output">Edit the fields above and click Save Changes...</div>

    <?= demoCodeTabs(
        '// Array keys map to each component\'s name() attribute.
$user = [\'display_name\' => \'Jane Smith\', \'email\' => \'jane@example.com\', \'bio\' => \'...\'];

<?= $m->form(\'editForm\')
    ->action(\'/profile/save\')
    ->model($user)
    ->field($m->textbox(\'ef-name\' )->name(\'display_name\'), \'Display Name\', [\'required\'])
    ->field($m->textbox(\'ef-email\')->name(\'email\'),         \'Email Address\', [\'required\', \'email\'])
    ->field($m->textarea(\'ef-bio\' )->name(\'bio\'),           \'Bio\',           [[\'maxLength\' => 200]], \'\', \'Max 200 chars.\')
    ->submit(\'Save Changes\', \'fa-save\')->primary() ?>'
    ) ?>
</div>

<!-- ── Section 3: Layout options ─────────────────────────────────────── -->
<div class="m-demo-section">
    <h3>Layouts</h3>
    <p class="m-demo-desc">
        <code>->layout('horizontal')</code> places labels and fields side-by-side.
        <code>->layout('inline')</code> flows all fields into one row — useful for filter bars.
        Default is <code>vertical</code>.
    </p>

    <h4 style="margin:0 0 0.75rem">Horizontal</h4>
    <?= $m->form('demoHorizForm')
        ->layout('horizontal')
        ->noCsrf()
        ->noValidation()
        ->formAttr('onsubmit', 'return false')
        ->field(
            $m->textbox('hz-first')->name('first_name')->placeholder('First name…'),
            'First Name'
        )
        ->field(
            $m->textbox('hz-last')->name('last_name')->placeholder('Last name…'),
            'Last Name'
        )
        ->field(
            $m->dropdown('hz-role')->name('role')
                ->dataSource([
                    ['value' => 'admin',  'text' => 'Admin'],
                    ['value' => 'editor', 'text' => 'Editor'],
                    ['value' => 'viewer', 'text' => 'Viewer'],
                ])
                ->placeholder('Select a role…'),
            'Role'
        )
        ->submit('Update', 'fa-check')->primary() ?>

    <?= demoCodeTabs(
        '// layout() options: vertical (default) | horizontal | inline
<?= $m->form(\'settingsForm\')
    ->action(\'/settings/save\')
    ->layout(\'horizontal\')
    ->field($m->textbox(\'s-first\')->name(\'first_name\'), \'First Name\')
    ->field($m->textbox(\'s-last\' )->name(\'last_name\'),  \'Last Name\')
    ->field(
        $m->dropdown(\'s-role\')->name(\'role\')->dataSource($roles)->placeholder(\'Select…\'),
        \'Role\'
    )
    ->submit(\'Update\', \'fa-check\')->primary() ?>'
    ) ?>
</div>

<!-- ── Section 4: No-validation + standalone Validator ───────────────── -->
<div class="m-demo-section">
    <h3>No Auto-Validation</h3>
    <p class="m-demo-desc">
        <code>->noValidation()</code> suppresses the auto-generated Validator. Use this when the
        Form is inside a Wizard (which manages validation per-step), or when you want to manage a
        separate <code>$m->validator()</code> yourself.
    </p>

    <?= demoCodeTabs(
        '// Suppress auto-Validator — e.g. inside a Wizard step
$stepForm = (string)$m->form(\'step1-form\')
    ->noCsrf()
    ->noValidation()
    ->formAttr(\'onsubmit\', \'return false\')
    ->field($m->textbox(\'s1-name\')->name(\'name\'),  \'Full Name\')
    ->field($m->textbox(\'s1-email\')->name(\'email\'), \'Email\');

// Attach a separate Validator that the Wizard step references
$stepValidator = (string)$m->validator(\'step1-form\')
    ->field(\'s1-name\',  \'Name is required.\',              [\'required\'])
    ->field(\'s1-email\', \'A valid email address required.\', [\'required\', \'email\']);

// In the Wizard step: ->useValidator(\'step1-form\')
$step = $m->wizardStep(\'step1\', \'Your Details\')
    ->content($stepForm . $stepValidator)
    ->useValidator(\'step1-form\');'
    ) ?>
</div>

<!-- ── Section 5: Hidden fields and raw HTML ─────────────────────────── -->
<div class="m-demo-section">
    <h3>Hidden Fields &amp; Raw HTML</h3>
    <p class="m-demo-desc">
        <code>->hidden()</code> injects a <code>&lt;input type="hidden"&gt;</code> at the current
        position. <code>->html()</code> inserts arbitrary markup — useful for section dividers,
        captcha images, or custom alerting.
    </p>

    <?= demoCodeTabs(
        '// hidden() injects a hidden input at the current position in the field list
<?= $m->form(\'editRecord\')
    ->action(\'/records/save\')
    ->hidden(\'record_id\', (string)$record[\'id\'])
    ->field($m->textbox(\'r-title\')->name(\'title\'), \'Title\', [\'required\'])
    ->submit(\'Save\', \'fa-save\')->primary() ?>

// html() inserts raw markup between fields
<?= $m->form(\'detailedForm\')
    ->field($m->textbox(\'d-name\')->name(\'name\'),  \'Name\', [\'required\'])
    ->html(\'<hr><p class="field-hint" style="font-weight:600">Optional extras</p>\')
    ->field($m->textarea(\'d-notes\')->name(\'notes\'), \'Notes\')
    ->submit(\'Save\') ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->form($id)', 'string', 'Create a Form. The <code>$id</code> is also used as the form\'s <code>name</code> attribute.'],
    ['->action($url)', 'string', 'Form <code>action</code> URL. Defaults to empty (submits to current page).'],
    ['->method($method)', 'string', 'HTTP method: <code>post</code> (default), <code>get</code>, <code>put</code>, <code>delete</code>. PUT/DELETE emit a hidden <code>_method</code> field.'],
    ['->name($name)', 'string', 'Override the form <code>name</code> attribute. Defaults to the ID.'],
    ['->model($data)', 'array|object', 'Bind an associative array or object to pre-populate field values. Keys are matched to component <code>name()</code> attributes.'],
    ['->field($component, $label, $rules, $errorMessage, $hint, $wrapperClass)', 'Component, string, array, string, string, string', 'Add a field wrapped in a <code>form-group</code> div. <code>$rules</code> drive the auto-Validator; <code>$errorMessage</code> overrides the label-derived default; <code>$hint</code> is small text shown below the field; required fields show a red asterisk.'],
    ['->hidden($name, $value)', 'string, string', 'Inject a <code>&lt;input type="hidden"&gt;</code>.'],
    ['->html($html)', 'string', 'Insert raw HTML at the current position in the field list.'],
    ['->submit($text, $icon)', 'string, string', 'Add a submit button. Returns a <code>Button</code> instance for further fluent configuration (e.g. <code>->primary()</code>).'],
    ['->layout($layout)', 'string', 'Form layout: <code>vertical</code> (default), <code>horizontal</code>, <code>inline</code>.'],
    ['->ajax()', '', 'Set <code>data-m-ajax="true"</code> on the form element.'],
    ['->noValidation()', '', 'Suppress the auto-generated client-side Validator.'],
    ['->noCsrf()', '', 'Suppress the automatic <code>csrf_token</code> hidden input (injected by default on POST/PUT/DELETE).'],
    ['->formAttr($name, $value)', 'string, string', 'Add an arbitrary attribute to the <code>&lt;form&gt;</code> element (e.g. <code>onsubmit</code>, <code>enctype</code>, <code>autocomplete</code>).'],
]) ?>

<script>
function handleContactSubmit(event) {
    event.preventDefault();
    var data = new FormData(event.target);
    setOutput('form-output',
        '<strong style="color:var(--m-success,#4CAF50)">Form valid!</strong> ' +
        'Name: <em>' + data.get('name') + '</em>, ' +
        'Email: <em>' + data.get('email') + '</em>');
    return false;
}

function handleEditSubmit(event) {
    event.preventDefault();
    var data = new FormData(event.target);
    setOutput('edit-output',
        '<strong style="color:var(--m-success,#4CAF50)">Changes saved!</strong> ' +
        'Name: <em>' + data.get('display_name') + '</em>, ' +
        'Email: <em>' + data.get('email') + '</em>');
    return false;
}
</script>

    <p class="m-demo-desc">Simple form with fields, labels, hints, and submit button.</p>
    
    <?= $m->form('basicForm')
        ->action('/demo/form-submit')
        ->method('post')
        ->field(
            $m->textbox('username')->name('username')->placeholder('Enter username'),
            'Username',
            ['required' => true, ['pattern' => '^[a-zA-Z0-9]{3,20}$']],
            'Alphanumeric, 3-20 characters'
        )
        ->field(
            $m->textbox('email')->name('email')->type('email')->placeholder('your@email.com'),
            'Email Address',
            ['required' => true, 'email' => true],
            'We\'ll never share your email'
        )
        ->field(
            $m->textbox('password')->name('password')->type('password')->placeholder('At least 8 characters'),
            'Password',
            ['required' => true, ['minLength' => 8]]
        )
        ->submit('Create Account', 'fa-user-plus')->primary()
    ?>

    <?= demoCodeTabs(
'<?= $m->form(\'registerForm\')
    ->action(\'/user/register\')
    ->method(\'post\')
    ->field(
        $m->textbox(\'username\')->name(\'username\'),
        \'Username\',
        [\'required\' => true],  // validation rules
        \'Alphanumeric, 3-20 characters\'  // hint text
    )
    ->field(
        $m->textbox(\'email\')->name(\'email\')->type(\'email\'),
        \'Email Address\',
        [\'required\' => true, \'email\' => true]
    )
    ->submit(\'Create Account\', \'fa-user-plus\')->primary()
?>',
'// Form automatically validates on submit
// Access form API programmatically
const form = m.form(\'registerForm\');

// Listen for submit event
form.onSubmit(function(e, data) {
    console.log(\'Form submitting:\', data);
});

// Get form data
const data = form.serialize();

// Programmatic submit
form.submit();'
    ) ?>

    <h3>Model Binding</h3>
    <p class="m-demo-desc">Auto-populate form fields from a model (array or object). Perfect for edit forms.</p>
    
    <?php
    $user = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'bio' => 'Web developer and designer',
        'receive_emails' => '1'
    ];
    ?>
    
    <?= $m->form('editForm')
        ->action('/demo/form-submit')
        ->model($user)  // Bind model data
        ->field(
            $m->textbox('first_name')->name('first_name'),
            'First Name',
            ['required' => true]
        )
        ->field(
            $m->textbox('last_name')->name('last_name'),
            'Last Name',
            ['required' => true]
        )
        ->field(
            $m->textarea('bio')->name('bio')->rows(4),
            'Bio',
            [],
            'Tell us about yourself'
        )
        ->field(
            $m->checkbox('receive_emails')->name('receive_emails')->value('1')->label('Receive email notifications'),
            ''
        )
        ->submit('Save Changes', 'fa-save')->primary()
    ?>

    <?= demoCodeTabs(
'<?php
$user = [
    \'first_name\' => \'John\',
    \'last_name\' => \'Doe\',
    \'bio\' => \'Web developer\'
];
?>

<?= $m->form(\'editForm\')
    ->action(\'/profile/edit\')
    ->model($user)  // Auto-populate from model
    ->field(
        $m->textbox(\'first_name\')->name(\'first_name\'),
        \'First Name\'
    )
    ->field(
        $m->textarea(\'bio\')->name(\'bio\'),
        \'Bio\'
    )
    ->submit(\'Save Changes\', \'fa-save\')->primary()
?>',
'// Populate form dynamically from JS
const form = m.form(\'editForm\');
form.populate({
    first_name: \'Jane\',
    last_name: \'Smith\',
    bio: \'Updated bio\'
});

// Reset to initial state
form.reset();'
    ) ?>

    <h3>AJAX Form</h3>
    <p class="m-demo-desc">Enable AJAX submission with automatic loading states and event handling.</p>
    
    <?= $m->form('ajaxForm')
        ->action('/demo/ajax-submit')
        ->ajax()  // Enable AJAX mode
        ->field(
            $m->textbox('search')->name('search')->placeholder('Search...'),
            'Search Query'
        )
        ->submit('Search', 'fa-search')->primary()
    ?>
    
    <div class="m-demo-output" id="ajaxOutput">Submit the form to see AJAX response...</div>

    <?= demoCodeTabs(
'<?= $m->form(\'ajaxForm\')
    ->action(\'/api/search\')
    ->ajax()  // Enable AJAX submission
    ->field(
        $m->textbox(\'search\')->name(\'search\'),
        \'Search Query\'
    )
    ->submit(\'Search\', \'fa-search\')->primary()
?>',
'// Listen for AJAX events
const form = m.form(\'ajaxForm\');

// On successful response
document.getElementById(\'ajaxForm\').addEventListener(\'m:form:success\', function(e) {
    console.log(\'Success!\', e.detail.response);
    m.toaster(\'demoToaster\').show(\'Form submitted!\', \'success\');
});

// On error
document.getElementById(\'ajaxForm\').addEventListener(\'m:form:error\', function(e) {
    console.log(\'Error:\', e.detail.error);
    m.toaster(\'demoToaster\').show(\'Submission failed\', \'error\');
});'
    ) ?>

    <h3>Horizontal Layout</h3>
    <p class="m-demo-desc">Labels and fields side-by-side. Automatically stacks on mobile.</p>
    
    <?= $m->form('horizontalForm')
        ->action('/demo/submit')
        ->layout('horizontal')  // Horizontal layout
        ->field(
            $m->textbox('company')->name('company'),
            'Company Name'
        )
        ->field(
            $m->dropdown('industry')->name('industry')->dataSource([
                ['value' => 'tech', 'text' => 'Technology'],
                ['value' => 'finance', 'text' => 'Finance'],
                ['value' => 'healthcare', 'text' => 'Healthcare']
            ]),
            'Industry'
        )
        ->submit('Save', 'fa-save')->primary()
    ?>

    <?= demoCodeTabs(
'<?= $m->form(\'settingsForm\')
    ->action(\'/settings/save\')
    ->layout(\'horizontal\')  // Labels on left, fields on right
    ->field(
        $m->textbox(\'company\')->name(\'company\'),
        \'Company Name\'
    )
    ->field(
        $m->dropdown(\'industry\')->name(\'industry\'),
        \'Industry\'
    )
    ->submit(\'Save\', \'fa-save\')->primary()
?>',
'// Same JavaScript API regardless of layout
const form = m.form(\'settingsForm\');'
    ) ?>

    <h3>Inline Layout</h3>
    <p class="m-demo-desc">Compact form with fields in a row. Perfect for filters and search bars.</p>
    
    <?= $m->form('inlineForm')
        ->action('/demo/search')
        ->layout('inline')  // Inline layout
        ->field(
            $m->textbox('keyword')->name('keyword')->placeholder('Keyword'),
            'Keyword'
        )
        ->field(
            $m->dropdown('category')->name('category')->dataSource([
                ['value' => 'all', 'text' => 'All Categories'],
                ['value' => 'news', 'text' => 'News'],
                ['value' => 'blog', 'text' => 'Blog']
            ]),
            'Category'
        )
        ->submit('Filter', 'fa-filter')
    ?>

    <?= demoCodeTabs(
'<?= $m->form(\'filterForm\')
    ->action(\'/search\')
    ->layout(\'inline\')  // All fields in one row
    ->field(
        $m->textbox(\'keyword\')->name(\'keyword\'),
        \'Keyword\'
    )
    ->field(
        $m->dropdown(\'category\')->name(\'category\'),
        \'Category\'
    )
    ->submit(\'Filter\', \'fa-filter\')
?>',
'// Inline forms are great for toolbars
const form = m.form(\'filterForm\');

form.onSubmit(function(e, data) {
    e.preventDefault();
    updateResults(data);
});'
    ) ?>

    <h3>Without Auto-Validation</h3>
    <p class="m-demo-desc">Disable automatic validator generation for custom validation logic.</p>
    
    <?= $m->form('customForm')
        ->action('/demo/submit')
        ->noValidation()  // Disable auto-validator
        ->field(
            $m->textbox('custom')->name('custom'),
            'Custom Field'
        )
        ->submit('Submit')
    ?>

    <?= demoCodeTabs(
'<?= $m->form(\'customForm\')
    ->action(\'/submit\')
    ->noValidation()  // No automatic validator
    ->field(
        $m->textbox(\'custom\')->name(\'custom\'),
        \'Custom Field\'
    )
    ->submit(\'Submit\')
?>

<!-- Add custom validator manually -->
<?= $m->validator(\'customForm\')
    ->field(\'custom\', [\'required\' => true])
    ->customRule(function($value) {
        return strlen($value) > 5;
    })
?>',
'// Custom validation logic
const form = m.form(\'customForm\');

form.onSubmit(function(e, data) {
    if (!myCustomValidation(data)) {
        e.preventDefault();
        m.toaster(\'demoToaster\').show(\'Validation failed\', \'error\');
    }
});'
    ) ?>

    <h3>Without CSRF Token</h3>
    <p class="m-demo-desc">Disable automatic CSRF token injection (e.g., for public forms).</p>
    
    <?= $m->form('publicForm')
        ->action('/demo/submit')
        ->noCsrf()  // No CSRF token
        ->field(
            $m->textbox('subscribe_email')->name('email')->type('email'),
            'Email'
        )
        ->submit('Subscribe', 'fa-envelope')
    ?>

    <?= demoCodeTabs(
'<?= $m->form(\'newsletterForm\')
    ->action(\'/newsletter/subscribe\')
    ->noCsrf()  // Skip CSRF token (public form)
    ->field(
        $m->textbox(\'email\')->name(\'email\')->type(\'email\'),
        \'Email Address\'
    )
    ->submit(\'Subscribe\', \'fa-envelope\')
?>',
'// Still gets full form API
const form = m.form(\'newsletterForm\');'
    ) ?>

    <h3>Hidden Fields & Raw HTML</h3>
    <p class="m-demo-desc">Add hidden fields and custom HTML content (e.g., captcha images, custom messages).</p>
    
    <?= $m->form('advancedForm')
        ->action('/demo/submit')
        ->hidden('user_id', '12345')  // Hidden field
        ->hidden('token', 'abc123')
        ->html('<div class="form-group captcha-group"><img src="/captcha.php" alt="captcha" class="captcha-image" /></div>')
        ->field(
            $m->textbox('captcha')->name('captcha')->placeholder('Enter code above'),
            'Verification Code',
            ['required' => true],
            '',
            'captcha-group'  // Custom wrapper class
        )
        ->html('<p class="form-notice">Protected by captcha verification</p>')
        ->submit('Verify', 'fa-check')
    ?>

    <?= demoCodeTabs(
'<?= $m->form(\'registerForm\')
    ->action(\'/register\')
    // Add hidden fields
    ->hidden(\'user_id\', $userId)
    ->hidden(\'token\', $token)
    // Add custom HTML (e.g., captcha image)
    ->html(\'<div class="captcha-group">
        <img src="/captcha.php" alt="captcha" />
    </div>\')
    // Regular field with custom wrapper class
    ->field(
        $m->textbox(\'captcha\')->name(\'captcha\'),
        \'Verification Code\',
        [\'required\' => true],
        \'\',
        \'captcha-group\'  // 5th param: wrapper class
    )
    ->submit(\'Register\', \'fa-user-plus\')->primary()
?>',
'// Hidden fields are included in form data
const form = m.form(\'registerForm\');
const data = form.serialize();
// data.user_id === "12345"
// data.token === "abc123"'
    ) ?>
</div>

<!-- API Documentation -->
<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->form($id)', 'Form', 'Create a Form component instance.'],
    ['->action($url)', 'self', 'Set form action URL. Default: empty (submit to current page).'],
    ['->method($method)', 'self', 'Set HTTP method: post (default), get, put, delete. PUT/DELETE use method spoofing.'],
    ['->name($name)', 'self', 'Set form name attribute. Default: same as ID.'],
    ['->model($data)', 'self', 'Bind model (array or object) to auto-populate field values.'],
    ['->field($component, $label, $rules, $hint, $wrapperClass)', 'self', 'Add a field with label, validation rules, hint text, and optional wrapper class.'],
    ['->hidden($name, $value)', 'self', 'Add a hidden input field.'],
    ['->html($html)', 'self', 'Insert raw HTML content (for captcha images, custom layouts, etc.).'],
    ['->submit($text, $icon)', 'Button', 'Create submit button. Returns Button instance for chaining (e.g., <code>->primary()</code>).'],
    ['->ajax($enabled)', 'self', 'Enable AJAX form submission. Default: <code>false</code>.'],
    ['->layout($layout)', 'self', 'Set form layout: vertical (default), horizontal, inline.'],
    ['->noValidation()', 'self', 'Disable automatic Validator generation.'],
    ['->noCsrf()', 'self', 'Disable automatic CSRF token injection.'],
    ['->formAttr($name, $value)', 'self', 'Add custom HTML attribute to <code>&lt;form&gt;</code> element.'],
]) ?>

<?= apiTable('JS Methods', 'js', [
    ['m.form(id)', 'object', 'Get Form component instance.'],
    ['serialize()', 'object', 'Get form data as key-value object.'],
    ['populate(data)', '', 'Populate form fields from object.'],
    ['reset()', '', 'Reset form to initial state, clear loading.'],
    ['setLoading(bool)', '', 'Enable/disable loading state (disables inputs, shows spinner).'],
    ['submit(callback)', '', 'Submit form programmatically. Callback: <code>function(err, response)</code>.'],
    ['onSubmit(handler)', '', 'Register custom submit handler: <code>function(event, data)</code>.'],
    ['validate()', 'boolean', 'Trigger validation. Returns <code>true</code> if form is valid.'],
]) ?>

<?= eventsTable([
    ['m:form:submit', '{data, cancelable: true}', 'Fired before form submission. Call <code>event.preventDefault()</code> to cancel.'],
    ['m:form:success', '{response}', '(AJAX only) Fired after successful server response.'],
    ['m:form:error', '{error}', '(AJAX only) Fired on submission error.'],
    ['m:form:reset', '{}', 'Fired after form reset.'],
    ['m:form:populated', '{data}', 'Fired after <code>populate()</code> call.'],
]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AJAX form demo
    const ajaxForm = m.form('ajaxForm');
    const output = document.getElementById('ajaxOutput');

    document.getElementById('ajaxForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        output.textContent = 'Submitting...';
        
        // Simulate AJAX response
        setTimeout(function() {
            const data = ajaxForm.serialize();
            output.innerHTML = '<strong>Form Data:</strong> ' + JSON.stringify(data, null, 2);
            
            // Trigger success event manually for demo
            const event = new CustomEvent('m:form:success', {
                detail: { response: { success: true, data: data } }
            });
            document.getElementById('ajaxForm').dispatchEvent(event);
        }, 1000);
    });

    // Listen for success
    document.getElementById('ajaxForm').addEventListener('m:form:success', function(e) {
        console.log('AJAX Success:', e.detail.response);
    });
});
</script>
