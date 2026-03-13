<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Wizard Component
 *
 * A multi-step form wizard with:
 *  - Visual step-progress strip
 *  - Per-step validation (mandatory fields must be filled before Next)
 *  - Skippable steps
 *  - Remote data-source binding (populates fields from a GET endpoint)
 *  - AJAX or standard-form submission; the payload includes all field
 *    values plus a '_wizard' meta-block with step information
 *
 * Usage:
 *   echo $m->wizard('orderWizard')
 *       ->step('account', 'Account Details')
 *           ->icon('fa-user')
 *           ->content('<p>Your form fields go here</p>')
 *           ->validateFields(['username', 'email'])
 *       ->step('shipping', 'Shipping')
 *           ->icon('fa-truck')
 *           ->content('<p>Shipping fields</p>')
 *           ->skippable()
 *       ->step('confirm', 'Review & Submit')
 *           ->icon('fa-check-circle')
 *           ->content('<p>Confirmation panel</p>')
 *       ->submitUrl('/orders/create')
 *       ->dataUrl('/orders/formData')
 *       ->onComplete('handleOrderComplete');
 *
 * Submission payload structure:
 *   {
 *     "username": "john",
 *     "email": "john@example.com",
 *     ...all other field values...,
 *     "_wizard": {
 *       "id": "orderWizard",
 *       "currentStep": "confirm",
 *       "currentStepIndex": 2,
 *       "completedSteps": ["account", "confirm"],
 *       "skippedSteps": ["shipping"],
 *       "totalSteps": 3,
 *       "stepData": {
 *         "account":  { "username": "john", "email": "john@example.com" },
 *         "shipping": null,
 *         "confirm":  {}
 *       }
 *     }
 *   }
 */
class Wizard extends Component
{
    /** @var WizardStep[] Ordered list of steps added via ->step() */
    private array $steps = [];

    /** Step currently being configured by the fluent chain */
    private ?WizardStep $currentStep = null;

    /** @var string|null URL to POST to on final submission */
    private ?string $submitUrl = null;

    /** HTTP method for the submit request */
    private string $submitMethod = 'POST';

    /** @var string|null URL to GET initial field values from */
    private ?string $dataUrl = null;

    /** Whether to submit via fetch() / AJAX rather than a full page POST */
    private bool $ajaxSubmit = true;

    /** @var string|null JS callback name invoked on successful completion */
    private ?string $onCompleteCallback = null;

    /** @var string|null JS callback name invoked before/after each step change */
    private ?string $onStepChangeCallback = null;

    /** Text labels for navigation buttons */
    private string $nextText   = 'Next';
    private string $prevText   = 'Back';
    private string $skipText   = 'Skip Step';
    private string $submitText = 'Submit';

    /** Whether to render the "Step X of Y" counter in the footer */
    private bool $showStepCounter = true;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);
    }

    protected function getComponentType(): string
    {
        return 'wizard';
    }

    // =========================================================================
    // Step builder – delegate to current step
    // =========================================================================

    /**
     * Add a new step and begin its fluent configuration.
     *
     * All subsequent step-scoped calls (->icon(), ->content(), etc.) apply
     * to this step until the next ->step() call or until rendering begins.
     *
     * @param string $key   Machine-readable key used as the step namespace in
     *                      the submitted data structure.
     * @param string $title Human-readable label shown in the progress strip.
     */
    public function step(string $key, string $title): self
    {
        $this->sealCurrentStep();
        $this->currentStep = new WizardStep($key, $title);
        return $this;
    }

    /**
     * Set the Font Awesome icon for the current step's circle indicator.
     *
     * Accepts either a bare icon name ('fa-user') or a full FA class string
     * ('fas fa-user').  When omitted the step number is shown instead.
     */
    public function icon(string $icon): self
    {
        if ($this->currentStep !== null) {
            $this->currentStep->icon = $icon;
        }
        return $this;
    }

    /**
     * Set the HTML content rendered inside the current step's panel.
     */
    public function content(string $html): self
    {
        if ($this->currentStep !== null) {
            $this->currentStep->content = $html;
        }
        return $this;
    }

    /**
     * Mark the current step as skippable.
     * A "Skip Step" button is shown in the footer when the active step is
     * skippable; skipped steps are recorded in the submission payload.
     */
    public function skippable(bool $skip = true): self
    {
        if ($this->currentStep !== null) {
            $this->currentStep->skippable = $skip;
        }
        return $this;
    }

    /**
     * Specify field IDs or name-attribute values that MUST be non-empty
     * before the user can advance past the current step.
     *
     * The JS layer inspects each field found in the current step panel.
     * Any Manhattan component wrapper is unwrapped to its underlying input.
     *
     * @param string[] $fieldIds HTML element IDs or input name attributes.
     */
    public function validateFields(array $fieldIds): self
    {
        if ($this->currentStep !== null) {
            $this->currentStep->validateFields = array_values($fieldIds);
        }
        return $this;
    }

    /**
     * Override the validation error message shown when required fields are
     * empty.  Defaults to a generic message when not set.
     */
    public function validationMessage(string $message): self
    {
        if ($this->currentStep !== null) {
            $this->currentStep->validationMessage = $message;
        }
        return $this;
    }

    /**
     * Specify the ID of a <form> element (rendered by Manhattan's Form
     * component) that has a Validator attached inside this step's panel.
     *
     * When set, the wizard's JS defers validation for this step to
     * m.validator via the form's _mValidatorInstance — field-level inline
     * errors are shown by the Validator and the wizard only shows its
     * summary banner.  Falls back to validateFields if no validator is found.
     *
     * @param string $formId The HTML id of the <form> element.
     */
    public function useValidator(string $formId): self
    {
        if ($this->currentStep !== null) {
            $this->currentStep->validatorFormId = $formId;
        }
        return $this;
    }

    // =========================================================================
    // Wizard-level options
    // =========================================================================

    /**
     * URL to POST the collected wizard data to on final submission.
     *
     * Expected server response: { "success": true, "message": "..." }
     * On success the onComplete callback (if configured) is invoked.
     */
    public function submitUrl(string $url): self
    {
        $this->sealCurrentStep();
        $this->submitUrl = $url;
        return $this;
    }

    /**
     * HTTP method for the submit request.  Default: 'POST'.
     */
    public function submitMethod(string $method): self
    {
        $this->submitMethod = strtoupper(trim($method));
        return $this;
    }

    /**
     * URL to GET initial field data from.  Fetched once on component init.
     *
     * Expected JSON: { "success": true, "data": { "fieldId": "value", … } }
     * Each key is matched against field IDs and name attributes across all
     * step panels; matching fields are populated with the returned value.
     */
    public function dataUrl(string $url): self
    {
        $this->sealCurrentStep();
        $this->dataUrl = $url;
        return $this;
    }

    /**
     * Whether to submit via fetch() AJAX (default: true).
     * Set to false to perform a standard form POST instead.
     */
    public function ajaxSubmit(bool $ajax = true): self
    {
        $this->ajaxSubmit = $ajax;
        return $this;
    }

    /**
     * Name of a global JS function to call when the wizard is successfully
     * submitted.  The function receives the parsed server response object.
     *
     * Example:  ->onComplete('handleOrderComplete')
     *   function handleOrderComplete(response) { ... }
     */
    public function onComplete(string $callback): self
    {
        $this->sealCurrentStep();
        $this->onCompleteCallback = $callback;
        return $this;
    }

    /**
     * Name of a global JS function to call when the active step changes.
     * The function receives { from, to, direction, wizard } where from/to
     * are zero-based step indices and direction is 'next'|'prev'|'skip'.
     *
     * Return false from the callback to cancel the navigation.
     *
     * Example:  ->onStepChange('handleStepChange')
     *   function handleStepChange(event) { return true; }
     */
    public function onStepChange(string $callback): self
    {
        $this->sealCurrentStep();
        $this->onStepChangeCallback = $callback;
        return $this;
    }

    /**
     * Text label for the "Next" button.  Default: 'Next'.
     */
    public function nextText(string $text): self
    {
        $this->nextText = $text;
        return $this;
    }

    /**
     * Text label for the "Back" button.  Default: 'Back'.
     */
    public function prevText(string $text): self
    {
        $this->prevText = $text;
        return $this;
    }

    /**
     * Text label for the "Skip Step" button.  Default: 'Skip Step'.
     */
    public function skipText(string $text): self
    {
        $this->skipText = $text;
        return $this;
    }

    /**
     * Text label for the final "Submit" button.  Default: 'Submit'.
     */
    public function submitText(string $text): self
    {
        $this->submitText = $text;
        return $this;
    }

    /**
     * Show or hide the "Step X of Y" counter in the footer.  Default: true.
     */
    public function showStepCounter(bool $show = true): self
    {
        $this->showStepCounter = $show;
        return $this;
    }

    // =========================================================================
    // Internal helpers
    // =========================================================================

    /** Seal the open step (if any) into the steps array. */
    private function sealCurrentStep(): void
    {
        if ($this->currentStep !== null) {
            $this->steps[] = $this->currentStep;
            $this->currentStep = null;
        }
    }

    /**
     * Normalise a FA icon token into a full class string.
     *
     * 'fa-user'      → 'fas fa-user'
     * 'far fa-user'  → 'far fa-user'  (no change)
     */
    private function normaliseIcon(string $icon): string
    {
        $icon = trim($icon);
        if ($icon === '') {
            return '';
        }
        if (strpos($icon, ' ') !== false) {
            return $icon;
        }
        if (strpos($icon, 'fa-') === 0) {
            return 'fas ' . $icon;
        }
        return 'fas fa-' . $icon;
    }

    // =========================================================================
    // Rendering
    // =========================================================================

    protected function renderHtml(): string
    {
        $this->sealCurrentStep();

        if (empty($this->steps)) {
            return '<div id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
                . '" class="m-wizard m-wizard-empty"></div>';
        }

        $totalSteps = count($this->steps);
        $eid = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        // ── Build JS config ──────────────────────────────────────────────────
        $stepsConfig = [];
        foreach ($this->steps as $i => $step) {
            $stepsConfig[] = [
                'index'             => $i,
                'key'               => $step->key,
                'title'             => $step->title,
                'skippable'         => $step->skippable,
                'validateFields'    => $step->validateFields,
                'validationMessage' => $step->validationMessage,
                'validatorFormId'   => $step->validatorFormId,
            ];
        }

        $jsConfig = [
            'steps'           => $stepsConfig,
            'submitUrl'       => $this->submitUrl,
            'submitMethod'    => $this->submitMethod,
            'dataUrl'         => $this->dataUrl,
            'ajaxSubmit'      => $this->ajaxSubmit,
            'onComplete'      => $this->onCompleteCallback,
            'onStepChange'    => $this->onStepChangeCallback,
            'showStepCounter' => $this->showStepCounter,
            'nextText'        => $this->nextText,
            'prevText'        => $this->prevText,
            'skipText'        => $this->skipText,
            'submitText'      => $this->submitText,
        ];

        $configJson = htmlspecialchars(
            (string)(json_encode($jsConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}'),
            ENT_QUOTES,
            'UTF-8'
        );

        // ── Build extra class / attribute strings ────────────────────────────
        $extraClasses = implode(' ', $this->getExtraClasses());
        $classAttr    = 'm-wizard' . ($extraClasses !== '' ? ' ' . $extraClasses : '');
        $extraAttrs   = $this->renderAdditionalAttributes(['class', 'id', 'data-config']);

        $html = '<div id="' . $eid . '" class="' . $classAttr . '" data-config="' . $configJson . '"' . $extraAttrs . '>';

        // ── Step progress strip ──────────────────────────────────────────────
        $html .= '<div class="m-wizard-header">';
        $html .= '<div class="m-wizard-steps" role="tablist" aria-label="Wizard steps">';

        foreach ($this->steps as $i => $step) {
            $stepKey  = htmlspecialchars($step->key,   ENT_QUOTES, 'UTF-8');
            $stepTitle = htmlspecialchars($step->title, ENT_QUOTES, 'UTF-8');
            $isActive  = ($i === 0);

            // Connector line between steps
            if ($i > 0) {
                $html .= '<div class="m-wizard-connector" aria-hidden="true">'
                       . '<div class="m-wizard-connector-fill"></div>'
                       . '</div>';
            }

            $html .= '<div class="m-wizard-step'
                   . ($isActive ? ' m-wizard-step-active' : '')
                   . '" data-step-index="' . $i . '" data-step-key="' . $stepKey . '"'
                   . ' role="tab" aria-selected="' . ($isActive ? 'true' : 'false') . '"'
                   . ' tabindex="' . ($isActive ? '0' : '-1') . '">';

            $html .= '<div class="m-wizard-step-indicator">';
            $html .= '<div class="m-wizard-step-circle">';

            // Icon or step number (defaulting to number)
            if ($step->icon !== '') {
                $iconClass = htmlspecialchars($this->normaliseIcon($step->icon), ENT_QUOTES, 'UTF-8');
                $html .= '<i class="' . $iconClass . ' m-wizard-step-default-icon" aria-hidden="true"></i>';
            } else {
                $html .= '<span class="m-wizard-step-num" aria-hidden="true">' . ($i + 1) . '</span>';
            }

            // Check icon shown when step is done
            $html .= '<i class="fas fa-check m-wizard-step-check-icon" aria-hidden="true"></i>';

            $html .= '</div>'; // .m-wizard-step-circle
            $html .= '</div>'; // .m-wizard-step-indicator

            $html .= '<div class="m-wizard-step-label">' . $stepTitle . '</div>';
            $html .= '</div>'; // .m-wizard-step
        }

        $html .= '</div>'; // .m-wizard-steps
        $html .= '</div>'; // .m-wizard-header

        // ── Step content panels ──────────────────────────────────────────────
        $html .= '<div class="m-wizard-body" role="tabpanel">';

        // Loading overlay (shown while dataUrl is being fetched)
        $html .= '<div class="m-wizard-loading" aria-live="polite" style="display:none">'
               . '<div class="m-wizard-loading-inner">'
               . '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i>'
               . ' <span>Loading&hellip;</span>'
               . '</div></div>';

        foreach ($this->steps as $i => $step) {
            $html .= '<div class="m-wizard-panel'
                   . ($i === 0 ? ' m-wizard-panel-active' : '')
                   . '" data-panel="' . $i . '" data-step-key="'
                   . htmlspecialchars($step->key, ENT_QUOTES, 'UTF-8') . '">';
            $html .= $step->content;
            $html .= '</div>'; // .m-wizard-panel
        }

        $html .= '</div>'; // .m-wizard-body

        // ── Inline validation error banner ───────────────────────────────────
        $html .= '<div class="m-wizard-error-banner" id="' . $eid . '-error-banner"'
               . ' role="alert" aria-live="assertive" style="display:none">'
               . '<i class="fas fa-exclamation-circle" aria-hidden="true"></i>'
               . ' <span class="m-wizard-error-text"></span>'
               . '</div>';

        // ── Navigation footer ────────────────────────────────────────────────
        $nextText   = htmlspecialchars($this->nextText,   ENT_QUOTES, 'UTF-8');
        $prevText   = htmlspecialchars($this->prevText,   ENT_QUOTES, 'UTF-8');
        $skipText   = htmlspecialchars($this->skipText,   ENT_QUOTES, 'UTF-8');
        $submitText = htmlspecialchars($this->submitText, ENT_QUOTES, 'UTF-8');

        $html .= '<div class="m-wizard-footer">';

        // Left: Back
        $html .= '<div class="m-wizard-footer-left">'
               . '<button type="button" class="m-button m-wizard-btn-prev"'
               . ' id="' . $eid . '-prev" disabled'
               . ' aria-label="Go to previous step">'
               . '<i class="fas fa-chevron-left" aria-hidden="true"></i>'
               . ' ' . $prevText
               . '</button>'
               . '</div>';

        // Center: step counter
        if ($this->showStepCounter) {
            $html .= '<div class="m-wizard-footer-center">'
                   . '<span class="m-wizard-step-counter" aria-live="polite">'
                   . 'Step 1 of ' . $totalSteps
                   . '</span>'
                   . '</div>';
        }

        // Right: Skip, Next, Submit
        $html .= '<div class="m-wizard-footer-right">';

        $html .= '<button type="button" class="m-button m-wizard-btn-skip"'
               . ' id="' . $eid . '-skip" style="display:none"'
               . ' aria-label="Skip this step">'
               . $skipText
               . ' <i class="fas fa-forward" aria-hidden="true"></i>'
               . '</button>';

        $html .= '<button type="button" class="m-button m-button-primary m-wizard-btn-next"'
               . ' id="' . $eid . '-next"'
               . ' aria-label="Go to next step">'
               . $nextText
               . ' <i class="fas fa-chevron-right" aria-hidden="true"></i>'
               . '</button>';

        $html .= '<button type="button" class="m-button m-button-primary m-wizard-btn-submit"'
               . ' id="' . $eid . '-submit" style="display:none"'
               . ' aria-label="Submit wizard">'
               . '<i class="fas fa-check" aria-hidden="true"></i>'
               . ' ' . $submitText
               . '</button>';

        $html .= '</div>'; // .m-wizard-footer-right
        $html .= '</div>'; // .m-wizard-footer

        $html .= '</div>'; // .m-wizard

        return $html;
    }
}
