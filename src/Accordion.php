<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Accordion Component
 * 
 * Collapsible content panels where only one can be open at a time.
 * Supports animated carets, customizable behavior, and keyboard navigation.
 * 
 * @package Manhattan
 * @since 1.5.0
 */
class Accordion extends Component
{
    protected array $panels = [];
    protected bool $animated = false;
    protected bool $allowMultiple = false;
    protected ?int $defaultOpen = null;
    
    public function __construct(string $id)
    {
        parent::__construct($id);
    }
    
    protected function getComponentType(): string
    {
        return 'accordion';
    }
    
    /**
     * Add a panel to the accordion
     * 
     * @param string $title Panel header text
     * @param string $content Panel content (HTML)
     * @param string|null $icon Optional Font Awesome icon for header
     * @return self
     */
    public function panel(string $title, string $content, ?string $icon = null): self
    {
        $this->panels[] = [
            'title' => $title,
            'content' => $content,
            'icon' => $icon,
        ];
        return $this;
    }
    
    /**
     * Enable/disable animation on expand/collapse
     */
    public function animated(bool $enabled = true): self
    {
        $this->animated = $enabled;
        return $this;
    }
    
    /**
     * Allow multiple panels to be open simultaneously
     */
    public function allowMultiple(bool $enabled = true): self
    {
        $this->allowMultiple = $enabled;
        return $this;
    }
    
    /**
     * Set which panel should be open by default (0-based index)
     */
    public function defaultOpen(?int $index): self
    {
        $this->defaultOpen = $index;
        return $this;
    }
    
    protected function renderHtml(): string
    {
        $html = [];
        
        // Build class list
        $classes = ['m-accordion'];
        $classes = array_merge($classes, $this->getExtraClasses());
        
        // Build accordion container attributes
        $attrs = [
            'id' => $this->id,
            'class' => implode(' ', $classes),
        ];
        
        if ($this->animated) {
            $attrs['data-m-animated'] = 'true';
        }
        
        if ($this->allowMultiple) {
            $attrs['data-m-multiple'] = 'true';
        }
        
        // Add custom attributes
        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);
        
        $html[] = '<div' . $this->renderAttributes($attrs) . $extraAttrs . '>';
        
        // Render panels
        foreach ($this->panels as $index => $panel) {
            $panelId = $this->id . '_panel_' . $index;
            $headerId = $this->id . '_header_' . $index;
            $contentId = $this->id . '_content_' . $index;
            $isOpen = ($this->defaultOpen === $index);
            
            $panelClass = 'm-accordion-panel';
            if ($isOpen) {
                $panelClass .= ' m-accordion-panel--open';
            }
            
            $html[] = '<div class="' . $panelClass . '" id="' . htmlspecialchars($panelId, ENT_QUOTES, 'UTF-8') . '">';
            
            // Header
            $html[] = '<button class="m-accordion-header" id="' . htmlspecialchars($headerId, ENT_QUOTES, 'UTF-8') . '"'
                    . ' aria-expanded="' . ($isOpen ? 'true' : 'false') . '"'
                    . ' aria-controls="' . htmlspecialchars($contentId, ENT_QUOTES, 'UTF-8') . '">';
            
            if (!empty($panel['icon'])) {
                $iconHelper = new Icon('', $panel['icon']);
                $html[] = '<span class="m-accordion-icon">' . $iconHelper . '</span>';
            }
            
            $html[] = '<span class="m-accordion-title">' . htmlspecialchars($panel['title'], ENT_QUOTES, 'UTF-8') . '</span>';
            $html[] = '<i class="m-accordion-caret fas fa-chevron-down" aria-hidden="true"></i>';
            $html[] = '</button>';
            
            // Content
            $html[] = '<div class="m-accordion-content" id="' . htmlspecialchars($contentId, ENT_QUOTES, 'UTF-8') . '"'
                    . ' role="region"'
                    . ' aria-labelledby="' . htmlspecialchars($headerId, ENT_QUOTES, 'UTF-8') . '"'
                    . ($isOpen ? '' : ' style="display: none;"') . '>';
            $html[] = '<div class="m-accordion-body">';
            $html[] = $panel['content'];
            $html[] = '</div>';
            $html[] = '</div>';
            
            $html[] = '</div>'; // .m-accordion-panel
        }
        
        $html[] = '</div>'; // .m-accordion
        
        return implode("\n", $html);
    }
    
    private function renderAttributes(array $attrs): string
    {
        $result = '';
        foreach ($attrs as $key => $value) {
            $result .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') 
                    . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return $result;
    }
}
