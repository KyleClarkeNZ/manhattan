<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Form Component
 * 
 * Standardizes form creation with automatic CSRF protection, validation, 
 * model binding, and integration with other Manhattan components.
 * 
 * @package Manhattan
 * @since 1.5.0
 */
class Form extends Component
{
    protected string $action = '';
    protected string $method = 'post';
    protected string $name;
    protected ?array $model = null;
    protected array $fields = [];
    protected ?Button $submitButton = null;
    protected ?array $cancelButton = null;
    protected ?Button $resetButton = null;
    protected bool $ajax = false;
    protected string $layout = 'vertical';
    protected bool $autoValidate = true;
    protected bool $autoCsrf = true;
    protected bool $dirtyFormProtection = false;
    protected array $formAttributes = [];
    
    public function __construct(string $id)
    {
        parent::__construct($id);
        $this->name = $id;
        $this->addClass('m-form');
    }
    
    protected function getComponentType(): string
    {
        return 'form';
    }
    
    /**
     * Set form action URL
     */
    public function action(string $url): self
    {
        $this->action = $url;
        return $this;
    }
    
    /**
     * Set form method (post, get, put, delete)
     */
    public function method(string $method): self
    {
        $this->method = strtolower($method);
        return $this;
    }
    
    /**
     * Set form name attribute (defaults to form ID)
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Bind a model (array or object) for auto-populating field values
     * 
     * @param array|object $data
     */
    public function model($data): self
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        $this->model = $data;
        return $this;
    }
    
    /**
     * Add a field to the form.
     *
     * @param Component $component     Manhattan component (textbox, dropdown, etc.)
     * @param string    $label         Label text (empty = no label rendered).
     * @param array     $validationRules Validation rules passed to the auto-generated
     *                                 Validator (e.g. <code>['required', 'email']</code>,
     *                                 <code>['required', ['minLength' => 8]]</code>).
     *                                 When supplied the label text is used to generate
     *                                 the inline error message automatically.
     * @param string    $errorMessage  Override the auto-generated validation error
     *                                 message. Ignored when $validationRules is empty.
     * @param string    $hint          Optional help text shown below the field.
     * @param string    $wrapperClass  Optional extra CSS classes on the form-group div.
     * @return self
     */
    public function field($component, string $label = '', array $validationRules = [], string $errorMessage = '', string $hint = '', string $wrapperClass = ''): self
    {
        $fieldName = $this->extractFieldName($component);

        $this->fields[] = [
            'type'         => 'field',
            'component'    => $component,
            'label'        => $label,
            'rules'        => $validationRules,
            'errorMessage' => $errorMessage,
            'hint'         => $hint,
            'name'         => $fieldName,
            'wrapperClass' => $wrapperClass,
        ];
        
        return $this;
    }
    
    /**
     * Add a hidden input field
     * 
     * @param string $name Field name
     * @param string $value Field value
     * @return self
     */
    public function hidden(string $name, string $value): self
    {
        $this->fields[] = [
            'type' => 'hidden',
            'name' => $name,
            'value' => $value,
        ];
        
        return $this;
    }
    
    /**
     * Add raw HTML content to the form
     * Use for custom layouts, captcha images, or other special content
     * 
     * @param string $html Raw HTML to insert
     * @return self
     */
    public function html(string $html): self
    {
        $this->fields[] = [
            'type' => 'html',
            'html' => $html,
        ];
        
        return $this;
    }
    
    /**
     * Add a submit button to the form
     * Returns a Button instance for further fluent configuration
     * 
     * @param string $text Button text
     * @param string $icon Font Awesome icon class (e.g., 'fa-save')
     * @return Button
     */
    public function submit(string $text = 'Submit', string $icon = ''): Button
    {
        $submitId = $this->id . '_submit';
        $this->submitButton = new Button($submitId, $text);
        $this->submitButton->type('submit');
        
        if (!empty($icon)) {
            $this->submitButton->icon($icon);
        }
        
        return $this->submitButton;
    }
    
    /**
     * Add a cancel button to the form.
     *
     * If $href is provided the button renders as an anchor link and navigates
     * to that URL. Without $href it renders as a plain button that calls
     * window.history.back() — useful when the cancel target isn't known at
     * render time.
     *
     * @param string $text  Button label. Default: 'Cancel'.
     * @param string $href  URL to navigate to on click. Empty = history.back().
     * @param string $icon  Font Awesome icon class. Default: 'fa-times'.
     * @return self
     */
    public function cancel(string $text = 'Cancel', string $href = '', string $icon = 'fa-times'): self
    {
        $this->cancelButton = [
            'text' => $text,
            'href' => $href,
            'icon' => $icon,
        ];
        return $this;
    }

    /**
     * Add a reset button to the form.
     *
     * Renders a <button type="reset"> that clears all fields back to their
     * initial values — useful for long filter or data-entry forms.
     *
     * @param string $text Button label. Default: 'Reset'.
     * @param string $icon Font Awesome icon class. Default: 'fa-undo'.
     * @return self
     */
    public function reset(string $text = 'Reset', string $icon = 'fa-undo'): self
    {
        $resetId = $this->id . '_reset';
        $this->resetButton = new Button($resetId, $text);
        $this->resetButton->type('reset');
        if (!empty($icon)) {
            $this->resetButton->icon($icon);
        }
        return $this;
    }

    /**
     * Enable AJAX form submission
     */
    public function ajax(bool $enabled = true): self
    {
        $this->ajax = $enabled;
        return $this;
    }
    
    /**
     * Set form layout: vertical (default), horizontal, or inline
     */
    public function layout(string $layout): self
    {
        $validLayouts = ['vertical', 'horizontal', 'inline'];
        if (in_array($layout, $validLayouts, true)) {
            $this->layout = $layout;
            $this->addClass('m-form--' . $layout);
        }
        return $this;
    }
    
    /**
     * Disable automatic validator generation
     */
    public function noValidation(): self
    {
        $this->autoValidate = false;
        return $this;
    }
    
    /**
     * Disable automatic CSRF token injection
     */
    public function noCsrf(): self
    {
        $this->autoCsrf = false;
        return $this;
    }

    /**
     * Enable dirty form protection.
     *
     * When enabled, the user will be prompted with a Manhattan confirmation
     * dialog if they try to leave or cancel the form with unsaved changes.
     * Pre-populated forms are only considered dirty after a human edit.
     *
     * @param bool $enabled Whether to enable dirty form protection. Default: true.
     * @return self
     */
    public function dirtyFormProtection(bool $enabled = true): self
    {
        $this->dirtyFormProtection = $enabled;
        return $this;
    }
    
    /**
     * Add custom attributes to the form element
     */
    public function formAttr(string $name, string $value): self
    {
        $this->formAttributes[$name] = $value;
        return $this;
    }
    
    /**
     * Extract field name from a component
     */
    protected function extractFieldName($component): ?string
    {
        if (!is_object($component)) {
            return null;
        }
        
        // Try to get name from component attributes or properties
        $reflection = new \ReflectionClass($component);
        
        // Check for name in attributes array
        if ($reflection->hasProperty('attributes')) {
            $attrProp = $reflection->getProperty('attributes');
            $attrProp->setAccessible(true);
            $attrs = $attrProp->getValue($component);
            if (isset($attrs['name'])) {
                return $attrs['name'];
            }
        }
        
        // Check for name property
        if ($reflection->hasProperty('name')) {
            $nameProp = $reflection->getProperty('name');
            $nameProp->setAccessible(true);
            $name = $nameProp->getValue($component);
            if (!empty($name)) {
                return $name;
            }
        }
        
        return null;
    }
    
    /**
     * Bind model data to a component by setting its value
     */
    protected function bindModelData($component, string $fieldName): void
    {
        if ($this->model === null || !isset($this->model[$fieldName])) {
            return;
        }
        
        $value = $this->model[$fieldName];
        
        // Try to set value on component if it has a value() method
        if (method_exists($component, 'value')) {
            $component->value((string)$value);
        }
        
        // Handle checkboxes - use checked() instead of value()
        if (method_exists($component, 'checked') && $value) {
            $component->checked(true);
        }
    }
    
    protected function renderHtml(): string
    {
        $html = [];
        
        // Build form opening tag
        $formAttrs = [
            'id' => $this->id,
            'name' => $this->name,
            'method' => $this->method === 'get' ? 'get' : 'post',
            'action' => $this->action,
            'class' => implode(' ', $this->getExtraClasses()),
        ];
        
        // Add AJAX data attribute
        if ($this->ajax) {
            $formAttrs['data-m-ajax'] = 'true';
        }

        // Add dirty form protection data attribute
        if ($this->dirtyFormProtection) {
            $formAttrs['data-m-dirty-protection'] = 'true';
        }
        
        // Merge custom form attributes
        $formAttrs = array_merge($formAttrs, $this->formAttributes);

        $formTag = '<form';
        foreach ($formAttrs as $key => $value) {
            $formTag .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8')
                     . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
        }
        // Append any extra attributes set via ->attr() on the Component
        $formTag .= $this->renderAdditionalAttributes(array_keys($formAttrs));
        $formTag .= '>';
        
        $html[] = $formTag;
        
        // Add CSRF token for POST/PUT/DELETE
        if ($this->autoCsrf && $this->method !== 'get') {
            $csrfToken = $_SESSION['csrf_token'] ?? '';
            $html[] = '<input type="hidden" name="csrf_token" value="' 
                    . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '">';
        }
        
        // Add method spoofing for PUT/DELETE
        if (in_array($this->method, ['put', 'delete'], true)) {
            $html[] = '<input type="hidden" name="_method" value="' 
                    . htmlspecialchars(strtoupper($this->method), ENT_QUOTES, 'UTF-8') . '">';
        }
        
        // Render fields
        foreach ($this->fields as $field) {
            // Handle different field types
            $fieldType = $field['type'] ?? 'field';
            
            if ($fieldType === 'hidden') {
                // Hidden field
                $html[] = '<input type="hidden" name="' 
                        . htmlspecialchars($field['name'], ENT_QUOTES, 'UTF-8') 
                        . '" value="' 
                        . htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8') 
                        . '">';
                continue;
            }
            
            if ($fieldType === 'html') {
                // Raw HTML content
                $html[] = $field['html'];
                continue;
            }
            
            // Regular field
            $component = $field['component'];
            $label = $field['label'];
            $hint = $field['hint'];
            $fieldName = $field['name'];
            $wrapperClass = $field['wrapperClass'] ?? '';
            
            // Bind model data if available
            if ($fieldName !== null && $this->model !== null) {
                $this->bindModelData($component, $fieldName);
            }
            
            // Wrap field in form-group
            $groupClasses = 'form-group';
            if (!empty($wrapperClass)) {
                $groupClasses .= ' ' . $wrapperClass;
            }
            $html[] = '<div class="' . htmlspecialchars($groupClasses, ENT_QUOTES, 'UTF-8') . '">';
            
            // Render label if provided
            if (!empty($label)) {
                $isRequired  = !empty($field['rules']) && in_array('required', $field['rules'], true);
                $labelComp   = new Label($component->getId() . '_label', $label);
                $labelComp->for($component->getId());
                if ($isRequired) {
                    $labelComp->required();
                }
                $html[] = (string)$labelComp;
            }
            
            // Render component
            $html[] = (string)$component;
            
            // Render hint if provided
            if (!empty($hint)) {
                $html[] = '<div class="field-hint">' . $hint . '</div>';
            }
            
            $html[] = '</div>';
        }
        
        // Render action buttons if at least one is configured
        if ($this->submitButton !== null || $this->cancelButton !== null || $this->resetButton !== null) {
            $html[] = '<div class="form-actions">';

            if ($this->submitButton !== null) {
                $html[] = (string)$this->submitButton;
            }

            if ($this->cancelButton !== null) {
                $safeText = htmlspecialchars($this->cancelButton['text'], ENT_QUOTES, 'UTF-8');
                $iconHtml = '';
                if (!empty($this->cancelButton['icon'])) {
                    $iconHtml = '<i class="fas ' . htmlspecialchars($this->cancelButton['icon'], ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i> ';
                }
                if (!empty($this->cancelButton['href'])) {
                    $safeHref = htmlspecialchars($this->cancelButton['href'], ENT_QUOTES, 'UTF-8');
                    $html[] = '<a href="' . $safeHref . '" class="m-button">' . $iconHtml . $safeText . '</a>';
                } else {
                    $html[] = '<button type="button" class="m-button" onclick="window.history.back()">' . $iconHtml . $safeText . '</button>';
                }
            }

            if ($this->resetButton !== null) {
                $html[] = (string)$this->resetButton;
            }

            $html[] = '</div>';
        }
        
        $html[] = '</form>';
        
        // Add auto-generated validator if enabled
        if ($this->autoValidate) {
            $validator   = new Validator($this->id);
            $hasRules    = false;

            foreach ($this->fields as $field) {
                if ($field['type'] !== 'field' || empty($field['rules'])) {
                    continue;
                }

                $fieldId = $field['component']->getId();

                // Derive error message: explicit override > label-based > generic
                $msg = $field['errorMessage'] ?? '';
                if ($msg === '') {
                    $label = $field['label'] ?? '';
                    $msg   = $label !== ''
                        ? 'Please fill in the ' . strtolower($label) . ' field.'
                        : 'This field is required.';
                }

                $validator->field($fieldId, $msg, $field['rules']);
                $hasRules = true;
            }

            if ($hasRules) {
                $html[] = (string)$validator;
            }
        }
        
        return implode("\n", $html);
    }
}
