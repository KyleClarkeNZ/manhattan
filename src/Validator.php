<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Validator Component
 * 
 * Provides client-side validation without HTML5 native validation to prevent layout shifts.
 * Displays inline error messages with consistent Manhattan styling.
 * 
 * @example
 * $m->validator('registration-form')
 *    ->field('email', 'Email is required', ['required', 'email'])
 *    ->field('password', 'Password must be at least 8 characters', ['required', 'minLength' => 8])
 *    ->field('username', 'Username is required', ['required', 'pattern' => '/^[a-zA-Z0-9_]+$/'])
 *    ->onSubmit('return handleFormSubmit(event);')
 */
class Validator extends Component {
    
    private string $formId;
    private array $fields = [];
    private ?string $onSubmit = null;
    private bool $validateOnBlur = true;
    private bool $validateOnInput = false;
    
    public function __construct(string $id) {
        parent::__construct($id);
        $this->formId = $id;
    }
    
    protected function getComponentType(): string {
        return 'validator';
    }
    
    /**
     * Add a field to validate
     * 
     * @param string $fieldName The name attribute of the input field
     * @param string $errorMessage The error message to display
     * @param array $rules Validation rules (required, email, minLength, maxLength, pattern, custom)
     * @return self
     */
    public function field(string $fieldName, string $errorMessage, array $rules): self {
        $this->fields[$fieldName] = [
            'message' => $errorMessage,
            'rules' => $rules
        ];
        return $this;
    }
    
    /**
     * Set callback function for form submission
     * 
     * @param string $callback JavaScript code to execute on valid form submit
     * @return self
     */
    public function onSubmit(string $callback): self {
        $this->onSubmit = $callback;
        return $this;
    }
    
    /**
     * Enable/disable validation on blur
     * 
     * @param bool $enabled
     * @return self
     */
    public function validateOnBlur(bool $enabled = true): self {
        $this->validateOnBlur = $enabled;
        return $this;
    }
    
    /**
     * Enable/disable validation on input (real-time)
     * 
     * @param bool $enabled
     * @return self
     */
    public function validateOnInput(bool $enabled = true): self {
        $this->validateOnInput = $enabled;
        return $this;
    }
    
    public function renderHtml(): string {
        $config = [
            'formId' => $this->formId,
            'fields' => $this->fields,
            'onSubmit' => $this->onSubmit,
            'validateOnBlur' => $this->validateOnBlur,
            'validateOnInput' => $this->validateOnInput
        ];
        
        // Encode as JSON without HTML encoding (it's in a script tag, so it's safe)
        $configJson = json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        
        return <<<HTML
<script>
(function() {
    function initValidator() {
        if (typeof window.m !== 'undefined' && typeof window.m.validator === 'function') {
            const config = {$configJson};
            window.m.validator(config);
        } else {
            console.warn('Manhattan Validator component not loaded');
        }
    }
    
    // Wait for DOM and all scripts to load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initValidator, 100);
        });
    } else {
        setTimeout(initValidator, 100);
    }
})();
</script>
HTML;
    }
}
