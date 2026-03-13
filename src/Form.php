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
    protected bool $ajax = false;
    protected string $layout = 'vertical';
    protected bool $autoValidate = true;
    protected bool $autoCsrf = true;
    protected ?string $errorBannerId = null;
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
     * Add a field to the form with optional label and validation rules
     * 
     * @param Component $component Manhattan component (textbox, dropdown, etc.)
     * @param string $label Field label text (empty = no label)
     * @param array $validationRules Validation rules for Manhattan Validator
     * @param string $hint Optional help text displayed below field
     * @param string $wrapperClass Optional CSS class(es) for the form-group wrapper
     * @return self
     */
    public function field($component, string $label = '', array $validationRules = [], string $hint = '', string $wrapperClass = ''): self
    {
        // Extract field name from component if it has one
        $fieldName = $this->extractFieldName($component);
        
        $this->fields[] = [
            'type' => 'field',
            'component' => $component,
            'label' => $label,
            'rules' => $validationRules,
            'hint' => $hint,
            'name' => $fieldName,
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
     * Enable automatic error banner display using Manhattan Toaster
     * 
     * @param string $toasterId ID for the toaster component
     */
    public function errorBanner(string $toasterId = 'formErrorBanner'): self
    {
        $this->errorBannerId = $toasterId;
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
            'class' => implode(' ', $this->classes),
        ];
        
        // Add AJAX data attribute
        if ($this->ajax) {
            $formAttrs['data-m-ajax'] = 'true';
        }
        
        // Merge custom form attributes
        $formAttrs = array_merge($formAttrs, $this->formAttributes);
        
        // Add any custom attributes from parent Component
        foreach ($this->attributes as $key => $value) {
            if (!isset($formAttrs[$key])) {
                $formAttrs[$key] = $value;
            }
        }
        
        $formTag = '<form';
        foreach ($formAttrs as $key => $value) {
            $formTag .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') 
                     . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
        }
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
                $labelFor = $component->getId();
                $html[] = '<label for="' . htmlspecialchars($labelFor, ENT_QUOTES, 'UTF-8') . '">' 
                        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>';
            }
            
            // Render component
            $html[] = (string)$component;
            
            // Render hint if provided
            if (!empty($hint)) {
                $html[] = '<div class="field-hint">' . $hint . '</div>';
            }
            
            $html[] = '</div>';
        }
        
        // Render submit button if provided
        if ($this->submitButton !== null) {
            $html[] = '<div class="form-actions">';
            $html[] = (string)$this->submitButton;
            $html[] = '</div>';
        }
        
        $html[] = '</form>';
        
        // Add validator if enabled
        if ($this->autoValidate && !empty($this->fields)) {
            $validator = new Validator($this->id);
            
            foreach ($this->fields as $field) {
                if (empty($field['rules']) || $field['name'] === null) {
                    continue;
                }
                
                $fieldId = $field['component']->getId();
                $validator->field($fieldId, $field['rules']);
            }
            
            $html[] = (string)$validator;
        }
        
        return implode("\n", $html);
    }
}
