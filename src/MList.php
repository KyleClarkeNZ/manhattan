<?php
declare(strict_types=1);

namespace Manhattan;

final class MList extends Component
{
    /** @var array<int, array<string, mixed>> */
    private array $items = [];

    private ?string $emptyMessage = null;

    public function emptyMessage(?string $message): self
    {
        $this->emptyMessage = $message;
        return $this;
    }

    /**
     * @param array<int, array{key?: string|int, id?: string, class?: string, html: string, attrs?: array<string, string|int|float>}> $items
     */
    public function items(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'list';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-list'], $this->getExtraClasses());
        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');
        $id = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $this->data('component', 'list');
        if ($this->emptyMessage !== null) {
            $this->data('empty-message', $this->emptyMessage);
        }

        $attrs = $this->renderAdditionalAttributes();
        $events = $this->renderEventAttributes();

        $html = "<div id=\"{$id}\" class=\"{$classAttr}\" role=\"list\"{$attrs}{$events}>";

        foreach ($this->items as $item) {
            $itemId = isset($item['id']) ? (string)$item['id'] : '';
            $key = $item['key'] ?? $itemId;
            $itemClass = 'm-list-item';
            if (!empty($item['class']) && is_string($item['class'])) {
                $itemClass .= ' ' . trim($item['class']);
            }

            $itemAttrs = '';
            if ($itemId !== '') {
                $itemAttrs .= ' id="' . htmlspecialchars($itemId, ENT_QUOTES, 'UTF-8') . '"';
            }
            if ($key !== null && $key !== '') {
                $itemAttrs .= ' data-key="' . htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') . '"';
            }
            if (!empty($item['attrs']) && is_array($item['attrs'])) {
                foreach ($item['attrs'] as $attrName => $attrVal) {
                    if (!is_string($attrName)) {
                        continue;
                    }
                    $itemAttrs .= ' ' . htmlspecialchars($attrName, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string)$attrVal, ENT_QUOTES, 'UTF-8') . '"';
                }
            }

            $itemHtml = (string)$item['html'];
            $html .= '<div class="' . htmlspecialchars($itemClass, ENT_QUOTES, 'UTF-8') . '" role="listitem"' . $itemAttrs . '>' . $itemHtml . '</div>';
        }

        if ($this->emptyMessage !== null && empty($this->items)) {
            $html .= '<div class="m-list-empty">' . htmlspecialchars($this->emptyMessage, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
