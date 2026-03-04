<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Window Component
 * 
 * Creates modal dialogs and windows with optional draggable functionality.
 * 
 * @example
 * <?= $m->window('myModal', 'Modal Title')
 *       ->content('<p>Modal content here</p>')
 *       ->modal()
 *       ->width('500px') ?>
 */
class Window extends Component
{
    protected string $title = '';
    protected string $content = '';
    protected bool $isModal = false;
    protected bool $isDraggable = false;
    protected bool $isResizable = false;
    protected bool $scrollable = true;
    protected ?string $width = null;
    protected ?string $height = null;
    protected ?string $minWidth = null;
    protected ?string $minHeight = null;
    protected array $buttons = [];
    protected bool $visible = false;
    
    /**
     * Set the window title
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Set the window content (HTML)
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Make this window a modal (blocks interaction with page)
     */
    public function modal(bool $isModal = true): self
    {
        $this->isModal = $isModal;
        return $this;
    }
    
    /**
     * Make window draggable
     */
    public function draggable(bool $isDraggable = true): self
    {
        $this->isDraggable = $isDraggable;
        return $this;
    }
    
    /**
     * Make window resizable
     */
    public function resizable(bool $isResizable = true): self
    {
        $this->isResizable = $isResizable;
        return $this;
    }

    /**
     * Enable/disable content scrolling
     */
    public function scrollable(bool $scrollable = true): self
    {
        $this->scrollable = $scrollable;
        return $this;
    }

    /**
     * Set window width
     */
    public function width(string $width): self
    {
        $this->width = $width;
        return $this;
    }
    
    /**
     * Set window height
     */
    public function height(string $height): self
    {
        $this->height = $height;
        return $this;
    }
    
    /**
     * Set minimum width
     */
    public function minWidth(string $minWidth): self
    {
        $this->minWidth = $minWidth;
        return $this;
    }
    
    /**
     * Set minimum height
     */
    public function minHeight(string $minHeight): self
    {
        $this->minHeight = $minHeight;
        return $this;
    }
    
    /**
     * Add a button to the window footer
     */
    public function addButton(string $text, string $action, string $style = 'primary'): self
    {
        $this->buttons[] = [
            'text' => $text,
            'action' => $action,
            'style' => $style
        ];
        return $this;
    }
    
    /**
     * Set window visibility on load
     */
    public function visible(bool $visible = true): self
    {
        $this->visible = $visible;
        return $this;
    }
    
    /**
     * Get the component type name
     */
    protected function getComponentType(): string
    {
        return 'window';
    }

    /**
     * Render the server-side HTML
     */
    protected function renderHtml(): string
    {
        return $this->renderWindowHtml();
    }
    
    /**
     * Render the window component HTML
     */
    private function renderWindowHtml(): string
    {
        $classes = ['m-window'];
        
        if ($this->isModal) {
            $classes[] = 'm-modal';
        }
        
        if ($this->isDraggable) {
            $classes[] = 'm-draggable';
        }
        
        if ($this->isResizable) {
            $classes[] = 'm-resizable';
        }
        
        if ($this->visible) {
            $classes[] = 'm-visible';
        }
        
        $styles = [];
        if ($this->width) {
            $styles[] = "width: {$this->width}";
        }
        if ($this->height) {
            $styles[] = "height: {$this->height}";
        }
        if ($this->minWidth) {
            $styles[] = "min-width: {$this->minWidth}";
        }
        if ($this->minHeight) {
            $styles[] = "min-height: {$this->minHeight}";
        }
        
        $styleAttr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';
        
        $html = '<div id="' . htmlspecialchars($this->id) . '" class="' . implode(' ', $classes) . '">';
        
        // Modal overlay
        if ($this->isModal) {
            $html .= '<div class="m-window-overlay"></div>';
        }
        
        // Window content wrapper
        $html .= '<div class="m-window-wrapper"' . $styleAttr . '>';
        
        // Title bar
        if ($this->title) {
            $html .= '<div class="m-window-titlebar">';
            $html .= '<span class="m-window-title">' . $this->title . '</span>';
            $html .= '<button class="m-window-close" type="button">' . Icon::html('fa-times', ['ariaHidden' => true]) . '</button>';
            $html .= '</div>';
        }
        
        // Content area
        $html .= '<div class="m-window-content">';
        $html .= $this->content;
        $html .= '</div>';
        
        // Buttons footer
        if (!empty($this->buttons)) {
            $html .= '<div class="m-window-actions">';
            foreach ($this->buttons as $btnIdx => $button) {
                $style = $button['style'] ?? 'primary';
                $btnComponent = (new Button($this->id . '_btn_' . $btnIdx, $button['text']))
                    ->addClass('m-window-btn');
                if ($style === 'primary') {
                    $btnComponent->primary();
                } elseif ($style === 'secondary') {
                    $btnComponent->secondary();
                } elseif ($style === 'danger') {
                    $btnComponent->danger();
                }
                $btnComponent->attr('data-action', $button['action']);
                $html .= (string)$btnComponent;
            }
            $html .= '</div>';
        }
        
        $html .= '</div>'; // .m-window-wrapper
        $html .= '</div>'; // .m-window
        
        return $html;
    }
    
    /**
     * Render the window component (override parent to skip script rendering)
     */
    public function render(): string
    {
        return $this->renderWindowHtml();
    }
}
