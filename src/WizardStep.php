<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * WizardStep — configuration object for a single step inside a Wizard.
 *
 * Not constructed directly; instances are created and configured via
 * the fluent Wizard builder API:
 *
 *   $m->wizard('id')
 *       ->step('account', 'Account Details')
 *           ->icon('fa-user')
 *           ->content('<p>…</p>')
 *           ->validateFields(['username', 'email'])
 *       ->step('confirm', 'Confirm')
 *           ->icon('fa-check-circle')
 *           ->content('<p>…</p>')
 *       ->submitUrl('/submit');
 */
class WizardStep
{
    /** @var string Unique key used as field namespace in submitted data */
    public string $key;

    /** @var string Display label shown in the step progress strip */
    public string $title;

    /** @var string Font Awesome icon name, e.g. 'fa-user' or 'fas fa-user' */
    public string $icon = '';

    /** @var string HTML content rendered inside this step's panel */
    public string $content = '';

    /** @var bool Whether the step may be skipped by the user */
    public bool $skippable = false;

    /**
     * When true, the user cannot navigate back to this step once it has been
     * passed.  Useful for steps that should be treated as already complete at
     * wizard start (e.g. a step pre-completed before the wizard opens).
     */
    public bool $noReturn = false;

    /**
     * List of HTML element IDs (or name attributes) whose values must be
     * non-empty before the user can advance past this step.
     *
     * @var string[]
     */
    public array $validateFields = [];

    /**
     * Optional override for the inline validation error message shown when
     * required fields are empty.  When null the JS uses a generic message.
     */
    public ?string $validationMessage = null;

    /**
     * ID of a <form> element inside this step's panel that has a Manhattan
     * Validator attached.  When set, the wizard defers validation to that
     * Validator instance (m.validator) instead of using the bespoke
     * validateFields check.
     */
    public ?string $validatorFormId = null;

    public function __construct(string $key, string $title)
    {
        $this->key   = $key;
        $this->title = $title;
    }
}
