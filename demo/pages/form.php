<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-edit') ?> Form</h2>
    <p class="m-demo-desc">
        A server-side form wrapper that handles <code>form-group</code> layout, labels with
        required-field indicators, hint text, automatic CSRF tokens, and an auto-generated
        client-side Validator &mdash; all from a single fluent builder.
        The Form component is purely PHP; fields with <code>required</code> in their rules
        automatically display a red asterisk.
    </p>
    <p class="m-demo-desc">
        <strong>Usage pattern:</strong> build the form chain, call <code>->submit()</code> to
        configure the submit button (returns a <code>Button</code> for fluent chaining), then
        <code>echo $form</code> to render the complete HTML.
    </p>

    <!-- ── Section 1: Auto-validating form ─────────────────────────────── -->
    <h3>Auto-validating Form</h3>
    <p class="m-demo-desc">
        Pass rules as the third argument to <code>->field()</code>. The Form generates a
        <code>Validator</code> automatically, deriving error messages from the label text.
        Override with the optional fourth argument. CSRF token injected by default on POST.
    </p>

    <?php
    $contactForm = $m->form('demoContactForm')
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
            'Message must be at least 10 characters.',
            'Be as specific as you can.'
        );
    $contactForm->submit('Send Message', 'fa-paper-plane')->primary();
    echo $contactForm;
    ?>

    <div class="m-demo-output" id="form-output">Fill in the form and click Send Message...</div>

    <?= demoCodeTabs(
        '// Build the form, configure the submit button (returns Button),
// then echo the Form variable to render the full HTML.
<?php
$form = $m->form(\'contactForm\')
    ->action(\'/contact/submit\')
    ->field(
        $m->textbox(\'cf-name\')->name(\'name\'),
        \'Full Name\',
        [\'required\']
    )
    ->field(
        $m->textbox(\'cf-email\')->name(\'email\'),
        \'Email Address\',
        [\'required\', \'email\']
    )
    ->field(
        $m->textarea(\'cf-message\')->name(\'message\'),
        \'Message\',
        [\'required\', [\'minLength\' => 10]],
        \'Message must be at least 10 characters.\',
        \'Be as specific as you can.\'
    );
$form->submit(\'Send Message\', \'fa-paper-plane\')->primary();
echo $form;'
    ) ?>
</div>

<!-- ── Section 2: Cancel & Reset buttons ─────────────────────────────── -->
<div class="m-demo-section">
    <h3>Cancel &amp; Reset Buttons</h3>
    <p class="m-demo-desc">
        <code>->cancel($text, $href, $icon)</code> adds a secondary action button rendered as an
        anchor link (when <code>$href</code> is given) or a <code>history.back()</code> button
        when no href is supplied. <code>->reset($text, $icon)</code> adds a
        <code>type="reset"</code> button that clears all fields to their initial DOM values.
        Both methods return the Form for chaining. Action buttons render in the order:
        <em>submit &rarr; cancel &rarr; reset</em>.
    </p>

    <?php
    $cancelForm = $m->form('demoCancelForm')
        ->noCsrf()
        ->formAttr('onsubmit', 'return false')
        ->field(
            $m->textbox('cc-title')->name('title')->placeholder('Post title…'),
            'Title',
            ['required']
        )
        ->field(
            $m->textarea('cc-body')->name('body')->placeholder('Write your post…')->rows(3),
            'Body',
            ['required']
        )
        ->cancel('Cancel', '/discuss');
    $cancelForm->submit('Publish Post', 'fa-paper-plane')->primary();
    echo $cancelForm;
    ?>

    <?= demoCodeTabs(
        '// ->cancel() returns the Form for chaining.
// Supply a $href to render as an anchor link; omit it for history.back().
<?php
$form = $m->form(\'newPostForm\')
    ->action(\'/posts/create\')
    ->field($m->textbox(\'title\')->name(\'title\'), \'Title\', [\'required\'])
    ->field($m->textarea(\'body\' )->name(\'body\'),  \'Body\',  [\'required\'])
    ->cancel(\'Cancel\', \'/posts\');     // href → renders as <a href="/posts">
$form->submit(\'Publish Post\', \'fa-paper-plane\')->primary();
echo $form;

// Without href — renders as onclick="window.history.back()"
$form = $m->form(\'editForm\')
    ->action(\'/posts/update\')
    ->field(...)
    ->cancel();                        // defaults: "Cancel", history.back(), fa-times
$form->submit(\'Save Changes\', \'fa-save\')->primary();
echo $form;'
    ) ?>

    <h3>Reset Button</h3>
    <p class="m-demo-desc">
        Use <code>->reset()</code> on filter or data-entry forms where users may want to clear all
        inputs. No JavaScript needed — the browser handles it via <code>type="reset"</code>.
    </p>

    <?php
    $filterForm = $m->form('demoFilterForm')
        ->noCsrf()
        ->noValidation()
        ->formAttr('onsubmit', 'return false')
        ->layout('inline')
        ->field(
            $m->textbox('ff-name')->name('name')->placeholder('Name…'),
            'Name'
        )
        ->field(
            $m->dropdown('ff-status')->name('status')
                ->dataSource([
                    ['value' => '',        'text' => 'Any status'],
                    ['value' => 'active',  'text' => 'Active'],
                    ['value' => 'pending', 'text' => 'Pending'],
                    ['value' => 'closed',  'text' => 'Closed'],
                ])
                ->value(''),
            'Status'
        )
        ->reset('Clear');
    $filterForm->submit('Filter', 'fa-filter');
    echo $filterForm;
    ?>

    <?= demoCodeTabs(
        '// ->reset() adds a <button type="reset"> — the browser clears all fields.
<?php
$form = $m->form(\'filterForm\')
    ->action(\'/items\')
    ->layout(\'inline\')
    ->field($m->textbox(\'name\'  )->name(\'name\'),   \'Name\')
    ->field($m->dropdown(\'status\')->name(\'status\'), \'Status\')
    ->reset(\'Clear\');       // optional icon: ->reset(\'Reset\', \'fa-times\')
$form->submit(\'Filter\', \'fa-filter\');
echo $form;'
    ) ?>

    <h3>All Three Action Buttons</h3>
    <p class="m-demo-desc">
        Submit, cancel, and reset can all be combined. They render in one
        <code>form-actions</code> div in the order: <em>submit &rarr; cancel &rarr; reset</em>.
    </p>

    <?php
    $fullActionsForm = $m->form('demoFullActionsForm')
        ->noCsrf()
        ->formAttr('onsubmit', 'return false')
        ->field(
            $m->textbox('fa-fn')->name('first_name')->placeholder('First name…'),
            'First Name',
            ['required']
        )
        ->field(
            $m->textbox('fa-ln')->name('last_name')->placeholder('Last name…'),
            'Last Name'
        )
        ->cancel('Discard', '/profile')
        ->reset('Clear All', 'fa-eraser');
    $fullActionsForm->submit('Save Profile', 'fa-save')->primary();
    echo $fullActionsForm;
    ?>

    <?= demoCodeTabs(
        '// All three action buttons — order: submit → cancel → reset.
<?php
$form = $m->form(\'profileForm\')
    ->action(\'/profile/save\')
    ->field($m->textbox(\'first_name\')->name(\'first_name\'), \'First Name\', [\'required\'])
    ->field($m->textbox(\'last_name\' )->name(\'last_name\'),  \'Last Name\')
    ->cancel(\'Discard\', \'/profile\')     // anchor back to profile
    ->reset(\'Clear All\', \'fa-eraser\');  // type="reset"
$form->submit(\'Save Profile\', \'fa-save\')->primary();
echo $form;'
    ) ?>
</div>

<!-- ── Section 3: Model binding ─────────────────────────────────────── -->
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
    $editForm = $m->form('demoEditForm')
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
        ->cancel('Cancel', '/profile');
    $editForm->submit('Save Changes', 'fa-save')->primary();
    echo $editForm;
    ?>

    <div class="m-demo-output" id="edit-output">Edit the fields above and click Save Changes...</div>

    <?= demoCodeTabs(
        '// Array keys map to each component\'s name() attribute.
$user = [\'display_name\' => \'Jane Smith\', \'email\' => \'jane@example.com\', \'bio\' => \'...\'];

<?php
$form = $m->form(\'editForm\')
    ->action(\'/profile/save\')
    ->model($user)
    ->field($m->textbox(\'ef-name\' )->name(\'display_name\'), \'Display Name\', [\'required\'])
    ->field($m->textbox(\'ef-email\')->name(\'email\'),         \'Email Address\', [\'required\', \'email\'])
    ->field($m->textarea(\'ef-bio\' )->name(\'bio\'),           \'Bio\',           [[\'maxLength\' => 200]], \'\', \'Max 200 chars.\')
    ->cancel(\'Cancel\', \'/profile\');
$form->submit(\'Save Changes\', \'fa-save\')->primary();
echo $form;'
    ) ?>
</div>

<!-- ── Section 4: Layout options ─────────────────────────────────────── -->
<div class="m-demo-section">
    <h3>Layouts</h3>
    <p class="m-demo-desc">
        <code>->layout('horizontal')</code> places labels and fields side-by-side.
        <code>->layout('inline')</code> flows all fields into one row — useful for filter bars.
        Default is <code>vertical</code>.
    </p>

    <h4 style="margin:0 0 0.75rem">Horizontal</h4>
    <?php
    $horizForm = $m->form('demoHorizForm')
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
        );
    $horizForm->submit('Update', 'fa-check')->primary();
    echo $horizForm;
    ?>

    <?= demoCodeTabs(
        '// layout() options: vertical (default) | horizontal | inline
<?php
$form = $m->form(\'settingsForm\')
    ->action(\'/settings/save\')
    ->layout(\'horizontal\')
    ->field($m->textbox(\'s-first\')->name(\'first_name\'), \'First Name\')
    ->field($m->textbox(\'s-last\' )->name(\'last_name\'),  \'Last Name\')
    ->field(
        $m->dropdown(\'s-role\')->name(\'role\')->dataSource($roles)->placeholder(\'Select…\'),
        \'Role\'
    );
$form->submit(\'Update\', \'fa-check\')->primary();
echo $form;'
    ) ?>
</div>

<!-- ── Section 5: No-validation + standalone Validator ───────────────── -->
<div class="m-demo-section">
    <h3>No Auto-Validation</h3>
    <p class="m-demo-desc">
        <code>->noValidation()</code> suppresses the auto-generated Validator. Use this when the
        Form is inside a Wizard (which manages validation per-step), or when you want to manage a
        separate <code>$m->validator()</code> yourself.
    </p>

    <?= demoCodeTabs(
        '// Suppress auto-Validator — e.g. inside a Wizard step
<?php
$stepForm = $m->form(\'step1-form\')
    ->noCsrf()
    ->noValidation()
    ->field($m->textbox(\'s1-name\')->name(\'name\'),  \'Full Name\')
    ->field($m->textbox(\'s1-email\')->name(\'email\'), \'Email\');
$stepForm->submit(\'Next\', \'fa-arrow-right\')->primary();

// Attach a separate Validator that the Wizard step references
$stepValidator = $m->validator(\'step1-form\')
    ->field(\'s1-name\',  \'Name is required.\',              [\'required\'])
    ->field(\'s1-email\', \'Valid email required.\',           [\'required\', \'email\']);

$step = $m->wizardStep(\'step1\', \'Your Details\')
    ->content((string)$stepForm . (string)$stepValidator)
    ->useValidator(\'step1-form\');'
    ) ?>
</div>

<!-- ── Section 6: Hidden fields and raw HTML ─────────────────────────── -->
<div class="m-demo-section">
    <h3>Hidden Fields &amp; Raw HTML</h3>
    <p class="m-demo-desc">
        <code>->hidden()</code> injects a <code>&lt;input type="hidden"&gt;</code>.
        <code>->html()</code> inserts arbitrary markup — useful for captcha images, card wrappers,
        or any content that doesn't fit the standard field pattern. Complex multi-section forms
        can capture their body via <code>ob_start()</code>/<code>ob_get_clean()</code> and pass
        the result to <code>->html()</code>, letting the Form manage the outer tag and CSRF.
    </p>

    <?= demoCodeTabs(
        '// hidden() injects a hidden input
<?php
$form = $m->form(\'editRecord\')
    ->action(\'/records/save\')
    ->hidden(\'record_id\', (string)$record[\'id\'])
    ->field($m->textbox(\'r-title\')->name(\'title\'), \'Title\', [\'required\']);
$form->submit(\'Save\', \'fa-save\')->primary();
echo $form;

// html() — useful for captcha images or card-wrapped sections
<?php
ob_start();
echo $m->card(\'sectionCard\')->sectionHeader(\'Details\')->content($detailsHtml);
$bodyHtml = ob_get_clean();

$form = $m->form(\'complexForm\')
    ->action(\'/save\')
    ->html($bodyHtml)
    ->cancel(\'Cancel\', \'/back\');
$form->submit(\'Save\', \'fa-save\')->primary();
echo $form;'
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
    ['->submit($text, $icon)', 'string, string', 'Add a submit button. Returns a <code>Button</code> instance for further fluent configuration (e.g. <code>->primary()</code>). Echo the <em>Form</em> variable to render full HTML — not the Button return value.'],
    ['->cancel($text, $href, $icon)', 'string, string, string', 'Add a cancel action. With <code>$href</code>: renders as <code>&lt;a href="…"&gt;</code>. Without: <code>onclick="history.back()"</code>. Defaults: text <em>Cancel</em>, icon <code>fa-times</code>. Returns <code>self</code>.'],
    ['->reset($text, $icon)', 'string, string', 'Add a <code>type="reset"</code> button. Defaults: text <em>Reset</em>, icon <code>fa-undo</code>. Returns <code>self</code>.'],
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
