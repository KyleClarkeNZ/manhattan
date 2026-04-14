<?php /** @var \Manhattan\HtmlHelper $m */ ?>

<div class="m-demo-section">
    <h2><?= $m->icon('fa-tag') ?> Label</h2>
    <p class="m-demo-desc">
        A semantic <code>&lt;label&gt;</code> element for associating text with a form input.
        Use <code>->for($inputId)</code> to wire it to the correct field — this enlarges the
        clickable/focusable area and is essential for accessibility.
        Use <code>->required()</code> to show a red asterisk for mandatory fields, and
        <code>->hint()</code> to add optional sub-text.
    </p>

    <h3>Basic Form Label</h3>
    <p class="m-demo-desc">A label linked to an input via <code>for</code>.</p>
    <div class="m-demo-row" style="flex-direction:column; align-items:flex-start; gap:0.25rem;">
        <?= $m->label('demo-lbl-name', 'Full Name')->for('demo-input-name') ?>
        <?= $m->textbox('demo-input-name')->placeholder('Enter your name')->name('full_name') ?>
    </div>

    <h3>Required Field</h3>
    <p class="m-demo-desc"><code>->required()</code> appends a red asterisk.</p>
    <div class="m-demo-row" style="flex-direction:column; align-items:flex-start; gap:0.25rem;">
        <?= $m->label('demo-lbl-email', 'Email Address')->for('demo-input-email')->required() ?>
        <?= $m->textbox('demo-input-email')->placeholder('you@example.com')->name('email') ?>
    </div>

    <h3>With Hint Text</h3>
    <p class="m-demo-desc"><code>->hint()</code> adds subdued helper text beside the label.</p>
    <div class="m-demo-row" style="flex-direction:column; align-items:flex-start; gap:0.25rem;">
        <?= $m->label('demo-lbl-bio', 'Bio')->for('demo-input-bio')->hint('Optional') ?>
        <?= $m->textarea('demo-input-bio')->placeholder('Tell us about yourself…')->name('bio') ?>
    </div>

    <h3>With Icon</h3>
    <p class="m-demo-desc"><code>->icon()</code> prepends a Font Awesome icon to the label text. Accepts the same icon strings as <code>$m->icon()</code>.</p>
    <div class="m-demo-row" style="flex-direction:column; align-items:flex-start; gap:0.25rem;">
        <?= $m->label('demo-lbl-email2', 'Email Address')->for('demo-input-email2')->icon('fa-envelope') ?>
        <?= $m->textbox('demo-input-email2')->placeholder('you@example.com')->name('email2') ?>
    </div>
    <div class="m-demo-row" style="flex-direction:column; align-items:flex-start; gap:0.25rem;">
        <?= $m->label('demo-lbl-phone', 'Phone')->for('demo-input-phone')->icon('fa-phone')->required() ?>
        <?= $m->textbox('demo-input-phone')->placeholder('+1 555 000 0000')->name('phone') ?>
    </div>

    <h3>Component <code>->label()</code> &amp; <code>->labelIcon()</code></h3>
    <p class="m-demo-desc">Any Manhattan component supports <code>->label()</code> to render a label above itself, and <code>->labelIcon()</code> to include an icon in that label.</p>
    <div class="m-demo-row" style="flex-direction:column; align-items:flex-start; gap:0.25rem;">
        <?= $m->dropdown('demo-dd-notif')
            ->label('Email Notifications')
            ->labelIcon('fa-envelope')
            ->dataSource([
                ['value' => 'disabled',  'text' => 'Disabled'],
                ['value' => 'immediate', 'text' => 'Immediately'],
                ['value' => 'daily',     'text' => 'Daily digest'],
            ])
            ->placeholder('Select…') ?>
    </div>

    <?= demoCodeTabs(
'// Basic label linked to an input
<?= $m->label(\'name-lbl\', \'Full Name\')->for(\'nameInput\') ?>
<?= $m->textbox(\'nameInput\')->name(\'full_name\') ?>

// Required field (red asterisk)
<?= $m->label(\'email-lbl\', \'Email Address\')->for(\'emailInput\')->required() ?>
<?= $m->textbox(\'emailInput\')->name(\'email\') ?>

// With hint text
<?= $m->label(\'bio-lbl\', \'Bio\')->for(\'bioArea\')->hint(\'Optional\') ?>
<?= $m->textarea(\'bioArea\')->name(\'bio\') ?>

// With icon
<?= $m->label(\'email-lbl\', \'Email Address\')->for(\'emailInput\')->icon(\'fa-envelope\') ?>
<?= $m->textbox(\'emailInput\')->name(\'email\') ?>

// Inline label via any component (no separate label call needed)
<?= $m->dropdown(\'notifDd\')
    ->label(\'Email Notifications\')
    ->labelIcon(\'fa-envelope\')
    ->dataSource($options) ?>'
    ) ?>
</div>

<?= apiTable('PHP Methods (Fluent)', 'php', [
    ['$m->label($id, $text)', 'Label', 'Create a form label component.'],
    ['->text($text)', 'self', 'Set the label text.'],
    ['->for($inputId)', 'self', 'Set the <code>for</code> attribute — ID of the associated input.'],
    ['->required()', 'self', 'Show a red asterisk to indicate a required field.'],
    ['->hint($text)', 'self', 'Add subdued helper text beside the label.'],
    ['->icon($icon)', 'self', 'Prepend a Font Awesome icon. Same format as <code>$m->icon()</code>.'],
]) ?>

<?= apiTable('Component Fluent Methods (on any component)', 'php', [
    ['->label($text)', 'self', 'Render a label above this component.'],
    ['->labelIcon($icon)', 'self', 'Add an icon to the inline label. Has no effect without <code>->label()</code>.'],
    ['->labelRequired()', 'self', 'Mark the inline label as required. Has no effect without <code>->label()</code>.'],
    ['->labelHint($text)', 'self', 'Add hint text to the inline label. Has no effect without <code>->label()</code>.'],
]) ?>

<?= apiTable('CSS Classes', 'php', [
    ['.m-label', '', 'Base form label — block, bold, small text.'],
    ['.m-label-required', '', 'Red asterisk span inside a required label.'],
    ['.m-label-hint', '', 'Muted helper/hint text span inside a label.'],
]) ?>
