<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Button Component
 * Renders an interactive button with optional icon and event handling
 */
class Button extends Component
{
    private string $text = 'Button';
    private ?string $icon = null;
    private string $type = 'button';
    private ?string $name = null;
    private bool $primary = false;
    private bool $secondary = false;
    private bool $danger = false;
    private bool $success = false;
    private bool $block = false;
    private bool $loading = false;
    private bool $disabled = false;
    private ?string $confirmMessage = null;

    public function __construct(string $id, string $text, array $options = [])
    {
        parent::__construct($id, $options);
        $this->text = $text;
        
        if (isset($options['icon'])) {
            $this->icon = $options['icon'];
        }
        if (isset($options['type'])) {
            $this->type = $options['type'];
        }
        if (isset($options['name'])) {
            $this->name = $options['name'];
        }
        if (isset($options['primary'])) {
            $this->primary = (bool)$options['primary'];
        }
        if (isset($options['secondary'])) {
            $this->secondary = (bool)$options['secondary'];
        }
        if (isset($options['danger'])) {
            $this->danger = (bool)$options['danger'];
        }
        if (isset($options['success'])) {
            $this->success = (bool)$options['success'];
        }
        if (isset($options['block'])) {
            $this->block = (bool)$options['block'];
        }
        if (isset($options['loading'])) {
            $this->loading = (bool)$options['loading'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }
        if (isset($options['confirm'])) {
            $this->confirmMessage = (string)$options['confirm'];
        }
    }

    /**
     * Set button as primary styled
     */
    public function primary(bool $primary = true): self
    {
        $this->primary = $primary;
        return $this;
    }

    /**
     * Set button as secondary styled
     */
    public function secondary(bool $secondary = true): self
    {
        $this->secondary = $secondary;
        return $this;
    }

    /**
     * Set button as danger styled
     */
    public function danger(bool $danger = true): self
    {
        $this->danger = $danger;
        return $this;
    }

    /**
     * Set button as success styled
     */
    public function success(bool $success = true): self
    {
        $this->success = $success;
        return $this;
    }

    /**
     * Set button as block (full width)
     */
    public function block(bool $block = true): self
    {
        $this->block = $block;
        return $this;
    }

    /**
     * Show a spinner and disable the button (use with JS m-button-loading class toggling)
     */
    public function loading(bool $loading = true): self
    {
        $this->loading = $loading;
        return $this;
    }

    /**
     * Disable the button.
     */
    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Show a browser confirm() dialog before the button action fires.
     */
    public function confirm(string $message): self
    {
        $this->confirmMessage = $message;
        return $this;
    }

    /**
     * Set button icon
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set button type (button, submit, reset)
     */
    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set button name attribute
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'button';
    }

    protected function renderHtml(): string
    {
        $classes = ['m-button'];
        $classes = array_merge($classes, $this->getExtraClasses());
        if ($this->primary) {
            $classes[] = 'm-button-primary';
        }
        if ($this->secondary) {
            $classes[] = 'm-button-secondary';
        }
        if ($this->danger) {
            $classes[] = 'm-button-danger';
        }
        if ($this->success) {
            $classes[] = 'm-button-success';
        }
        if ($this->block) {
            $classes[] = 'm-button-block';
        }
        if ($this->loading) {
            $classes[] = 'm-button-loading';
        }
        
        $classAttr = implode(' ', $classes);
        $disabledAttr = $this->disabled ? ' disabled' : '';
        $iconHtml = '';

        if ($this->icon) {
            // Accept either a full FA class list (e.g. "far fa-circle") or a single fa-name (e.g. "fa-edit").
            $iconHtml = Icon::html($this->icon, ['ariaHidden' => true]) . ' ';
        }

        $escapedText = htmlspecialchars($this->text, ENT_QUOTES, 'UTF-8');
        $nameAttr = $this->name ? ' name="' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8') . '"' : '';

        $eventAttrs = $this->renderEventAttributes();
        $extraAttrs = $this->renderAdditionalAttributes(['id', 'type', 'name', 'class']);

        $confirmAttr = $this->confirmMessage !== null
            ? ' data-m-confirm="' . htmlspecialchars($this->confirmMessage, ENT_QUOTES, 'UTF-8') . '"'
            : '';

        return <<<HTML
<button id="{$this->id}" type="{$this->type}"{$nameAttr} class="{$classAttr}"{$disabledAttr}{$confirmAttr}{$eventAttrs}{$extraAttrs}>
    {$iconHtml}{$escapedText}
</button>
HTML;
    }
}
