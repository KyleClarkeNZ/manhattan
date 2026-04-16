<?php
declare(strict_types=1);

namespace Manhattan;

final class Reorderable extends Component
{
    /** @var array<int, array<string, mixed>> */
    private array $items = [];

    private bool $updateModelOnReorder = false;
    private ?string $updateUrl = null;
    private ?string $emptyMessage = null;
    private string $loaderText = 'Saving...';
    private bool $showHandles = true;

    public function updateModelOnReorder(bool $enabled = true): self
    {
        $this->updateModelOnReorder = $enabled;
        return $this;
    }

    public function updateUrl(?string $url): self
    {
        $this->updateUrl = $url;
        return $this;
    }

    public function emptyMessage(?string $message): self
    {
        $this->emptyMessage = $message;
        return $this;
    }

    public function loaderText(string $text): self
    {
        $this->loaderText = $text;
        return $this;
    }

    public function handles(bool $show = true): self
    {
        $this->showHandles = $show;
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
        return 'reorderable';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-reorderable'], $this->getExtraClasses());
        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');
        $id = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $this->data('component', 'reorderable');
        $this->data('update-model-on-reorder', $this->updateModelOnReorder ? '1' : '0');
        $this->data('handles', $this->showHandles ? '1' : '0');
        $this->data('loader-text', $this->loaderText);
        if ($this->updateUrl !== null) {
            $this->data('update-url', $this->updateUrl);
        }
        if ($this->emptyMessage !== null) {
            $this->data('empty-message', $this->emptyMessage);
        }

        $attrs = $this->renderAdditionalAttributes();
        $events = $this->renderEventAttributes();

        $html = "<div id=\"{$id}\" class=\"{$classAttr}\"{$attrs}{$events}>";

        foreach ($this->items as $item) {
            $itemId = isset($item['id']) ? (string)$item['id'] : '';
            $key = $item['key'] ?? $itemId;
            $itemClass = 'm-reorderable-item';
            if (!empty($item['class']) && is_string($item['class'])) {
                $itemClass .= ' ' . trim($item['class']);
            }

            $itemAttrs = ' draggable="true"';
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
            $handleHtml = $this->showHandles
                ? '<span class="m-reorderable-handle" aria-hidden="true"><i class="fas fa-grip-vertical"></i></span>'
                : '';
            $html .= '<div class="' . htmlspecialchars($itemClass, ENT_QUOTES, 'UTF-8') . '"' . $itemAttrs . '>' . $handleHtml . $itemHtml . '</div>';
        }

        if ($this->emptyMessage !== null && empty($this->items)) {
            $html .= '<div class="m-reorderable-empty">' . htmlspecialchars($this->emptyMessage, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $html .= (new Loader($this->id . '__loader'))
            ->overlay(true)
            ->hidden(true)
            ->size('sm')
            ->text($this->loaderText)
            ->addClass('m-reorderable-loader')
            ->render();

        $html .= '</div>';

        return $html;
    }
}
