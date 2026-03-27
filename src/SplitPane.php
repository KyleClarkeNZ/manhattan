<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * SplitPane Component
 *
 * A two-panel layout with a draggable divider between them.
 * The first (primary) pane can be resized between optional min/max constraints.
 * On mobile the panes stack vertically and the divider is hidden.
 *
 * Usage:
 *   echo $m->splitPane('myPane')
 *       ->first($leftHtml)
 *       ->second($rightHtml)
 *       ->initialSize(320)
 *       ->minSize(200)
 *       ->maxSize(600)
 *       ->direction('horizontal'); // 'horizontal' (default) or 'vertical'
 */
final class SplitPane extends Component
{
    private string $firstHtml  = '';
    private string $secondHtml = '';
    private int $initialSize   = 300;
    private ?int $minSize      = null;
    private ?int $maxSize      = null;
    private string $direction  = 'horizontal';

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['initialSize'])) {
            $this->initialSize = (int)$options['initialSize'];
        }
        if (isset($options['minSize'])) {
            $this->minSize = (int)$options['minSize'];
        }
        if (isset($options['maxSize'])) {
            $this->maxSize = (int)$options['maxSize'];
        }
        if (isset($options['direction'])) {
            $this->direction = (string)$options['direction'];
        }
    }

    /** Set the HTML content of the first (primary / left) pane. */
    public function first(string $html): self
    {
        $this->firstHtml = $html;
        return $this;
    }

    /** Set the HTML content of the second (secondary / right) pane. */
    public function second(string $html): self
    {
        $this->secondHtml = $html;
        return $this;
    }

    /**
     * Set the initial pixel size of the first pane.
     * For horizontal splits this is the width; for vertical splits the height.
     */
    public function initialSize(int $px): self
    {
        $this->initialSize = $px;
        return $this;
    }

    /** Minimum pixel size the first pane can be dragged to. */
    public function minSize(int $px): self
    {
        $this->minSize = $px;
        return $this;
    }

    /** Maximum pixel size the first pane can be dragged to. */
    public function maxSize(int $px): self
    {
        $this->maxSize = $px;
        return $this;
    }

    /**
     * Split direction: 'horizontal' (left | right, default) or 'vertical' (top | bottom).
     */
    public function direction(string $direction): self
    {
        $this->direction = $direction;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'splitpane';
    }

    protected function renderHtml(): string
    {
        $id        = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $dir       = $this->direction === 'vertical' ? 'vertical' : 'horizontal';
        $size      = $this->initialSize;
        $min       = $this->minSize  !== null ? $this->minSize  : 0;
        $max       = $this->maxSize  !== null ? $this->maxSize  : 9999;

        $extraClasses = implode(' ', $this->getExtraClasses());
        $classAttr    = trim('m-split-pane m-split-pane--' . $dir . ' ' . $extraClasses);

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class',
            'data-direction', 'data-initial-size', 'data-min-size', 'data-max-size']);

        return <<<HTML
<div id="{$id}" class="{$classAttr}"
     data-component="splitpane"
     data-direction="{$dir}"
     data-initial-size="{$size}"
     data-min-size="{$min}"
     data-max-size="{$max}"{$extraAttrs}>
    <div class="m-split-pane__first">{$this->firstHtml}</div>
    <div class="m-split-pane__divider" role="separator" aria-orientation="{$dir}" tabindex="0"
         aria-label="Resize pane" aria-valuenow="{$size}"
         aria-valuemin="{$min}" aria-valuemax="{$max}">
        <div class="m-split-pane__divider-handle"></div>
    </div>
    <div class="m-split-pane__second">{$this->secondHtml}</div>
</div>
HTML;
    }
}
