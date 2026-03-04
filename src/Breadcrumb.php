<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Breadcrumb Component
 * Displays hierarchical navigation path showing where the user is within the site structure.
 */
final class Breadcrumb extends Component
{
    /** @var array<int, array{url?: string, text: string, icon?: string, current?: bool}> */
    private array $items = [];

    /**
     * Add an item to the breadcrumb trail
     * 
     * @param string $text The text to display for this breadcrumb
     * @param string|null $url The URL this item links to (null for current page)
     * @param string|null $icon Optional Font Awesome icon class
     * @return self
     */
    public function item(string $text, ?string $url = null, ?string $icon = null): self
    {
        $item = ['text' => $text];
        
        if ($url !== null) {
            $item['url'] = $url;
        }
        
        if ($icon !== null) {
            $item['icon'] = $icon;
        }
        
        // If no URL provided, mark as current
        if ($url === null) {
            $item['current'] = true;
        }
        
        $this->items[] = $item;
        return $this;
    }

    /**
     * Add a home breadcrumb item
     * 
     * @param string $url The URL for the home page (default: '/')
     * @param string $text The text to display (default: 'Dashboard')
     * @return self
     */
    public function home(string $url = '/', string $text = 'Dashboard'): self
    {
        return $this->item($text, $url, 'fa-home');
    }

    /**
     * Mark the last item as the current page
     * 
     * @return self
     */
    public function current(): self
    {
        if (!empty($this->items)) {
            $lastIndex = count($this->items) - 1;
            $this->items[$lastIndex]['current'] = true;
            unset($this->items[$lastIndex]['url']);
        }
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'breadcrumb';
    }

    protected function renderHtml(): string
    {
        if (empty($this->items)) {
            return '';
        }

        $classes = ['m-breadcrumb'];
        $classes = array_merge($classes, $this->getExtraClasses());
        $classStr = implode(' ', $classes);

        $attrs = $this->renderAdditionalAttributes(['class']);
        $ariaLabel = 'aria-label="Breadcrumb"';

        $html = "<nav id=\"{$this->id}\" class=\"{$classStr}\" {$ariaLabel}{$attrs}>";

        $itemCount = count($this->items);
        foreach ($this->items as $index => $item) {
            $isLast = ($index === $itemCount - 1);
            $isCurrent = $item['current'] ?? false;
            
            // Add separator before each item except the first
            if ($index > 0) {
                $html .= '<span class="m-breadcrumb-sep">' . (new Icon('', 'fa-chevron-right'))->render() . '</span>';
            }

            // Render the item
            if ($isCurrent || $isLast) {
                // Current page - no link
                $html .= '<span class="m-breadcrumb-current">';
                if (isset($item['icon'])) {
                    $html .= (new Icon('', $item['icon']))->render() . ' ';
                }
                $html .= htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8');
                $html .= '</span>';
            } else {
                // Link to another page
                $url = htmlspecialchars($item['url'] ?? '#', ENT_QUOTES, 'UTF-8');
                $html .= "<a href=\"{$url}\" class=\"m-breadcrumb-link\">";
                if (isset($item['icon'])) {
                    $html .= (new Icon('', $item['icon']))->render() . ' ';
                }
                $html .= htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8');
                $html .= '</a>';
            }
        }

        $html .= '</nav>';
        return $html;
    }
}
