<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Popover Component
 *
 * A floating panel anchored to a trigger element. Content can be static HTML or
 * loaded dynamically via AJAX. Triggers via hover (default) or click.
 * Supports auto/top/bottom/left/right placement with viewport-aware repositioning.
 *
 * Usage — static content, single trigger:
 *   <?= $m->popover('info-pop')
 *         ->trigger('info-btn')
 *         ->title('More Info')
 *         ->content('<p>Details here.</p>')
 *         ->placement('bottom') ?>
 *
 * Usage — remote content, multiple triggers via CSS selector:
 *   <?= $m->popover('profile-pop')
 *         ->triggerSelector('.username-link')
 *         ->title('User Profile')
 *         ->remote('/profile/card')
 *         ->placement('bottom')
 *         ->delay(200, 300) ?>
 *
 *   Trigger elements can carry per-trigger overrides:
 *     <a class="username-link"
 *        data-m-popover="profile-pop"
 *        data-popover-url="/profile/card?id=5"
 *        data-popover-title="Jane Smith">Jane</a>
 */
final class Popover extends Component
{
    private string $title = '';
    private string $content = '';
    private ?string $trigger = null;
    private ?string $triggerSelector = null;
    private string $placement = 'auto';
    private string $triggerOn = 'hover';
    private int $delayShow = 200;
    private int $delayHide = 300;
    private ?string $remote = null;
    private bool $useCache = true;
    private ?string $width = null;
    private int $offset = 8;

    /**
     * Set the popover header title.
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set static HTML content for the popover body.
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Bind this popover to a single DOM element by ID.
     */
    public function trigger(string $elementId): self
    {
        $this->trigger = $elementId;
        return $this;
    }

    /**
     * Bind this popover to all elements matching a CSS selector.
     * Each trigger may carry data-popover-url and data-popover-title to override defaults.
     */
    public function triggerSelector(string $selector): self
    {
        $this->triggerSelector = $selector;
        return $this;
    }

    /**
     * Set preferred placement: auto|top|bottom|left|right
     * 'auto' prefers bottom and flips to top when near the viewport edge.
     * Default: 'auto'
     */
    public function placement(string $placement): self
    {
        $this->placement = $placement;
        return $this;
    }

    /**
     * Set the trigger event type.
     * Default: 'hover'. Use 'click' for click-to-toggle behaviour.
     */
    public function triggerOn(string $event): self
    {
        $this->triggerOn = $event;
        return $this;
    }

    /**
     * Set hover show/hide delays in milliseconds.
     * Default: show=200, hide=300.
     */
    public function delay(int $show = 200, int $hide = 300): self
    {
        $this->delayShow = $show;
        $this->delayHide = $hide;
        return $this;
    }

    /**
     * Load popover body content from a URL via AJAX.
     * Individual triggers may override this via data-popover-url.
     */
    public function remote(string $url): self
    {
        $this->remote = $url;
        return $this;
    }

    /**
     * Enable or disable per-URL caching of remote content.
     * When enabled (default), a URL is only fetched once per page load.
     */
    public function cache(bool $cache = true): self
    {
        $this->useCache = $cache;
        return $this;
    }

    /**
     * Set forced width for the popover (e.g. '300px', '20rem').
     * By default the popover sizes to its content up to the CSS max-width.
     */
    public function width(string $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Set the gap in pixels between the trigger element and the popover.
     * Default: 8.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'popover';
    }

    protected function renderHtml(): string
    {
        $id = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $classes = array_merge(['m-popover'], $this->getExtraClasses());
        $classAttr = htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');

        // Build data attributes consumed by popover.js
        $data = '';
        if ($this->trigger !== null) {
            $data .= ' data-trigger="' . htmlspecialchars($this->trigger, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->triggerSelector !== null) {
            $data .= ' data-trigger-selector="' . htmlspecialchars($this->triggerSelector, ENT_QUOTES, 'UTF-8') . '"';
        }
        $data .= ' data-placement="' . htmlspecialchars($this->placement, ENT_QUOTES, 'UTF-8') . '"';
        $data .= ' data-trigger-on="' . htmlspecialchars($this->triggerOn, ENT_QUOTES, 'UTF-8') . '"';
        $data .= ' data-delay-show="' . $this->delayShow . '"';
        $data .= ' data-delay-hide="' . $this->delayHide . '"';
        $data .= ' data-cache="' . ($this->useCache ? 'true' : 'false') . '"';
        $data .= ' data-offset="' . $this->offset . '"';
        if ($this->remote !== null) {
            $data .= ' data-remote="' . htmlspecialchars($this->remote, ENT_QUOTES, 'UTF-8') . '"';
        }

        $styleAttr = '';
        if ($this->width !== null) {
            $styleAttr = ' style="width:' . htmlspecialchars($this->width, ENT_QUOTES, 'UTF-8') . '"';
        }

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class', 'style'])
                    . $this->renderEventAttributes();

        $html  = '<div id="' . $id . '" class="' . $classAttr . '" role="tooltip"'
               . $data . $styleAttr . $extraAttrs . ' aria-hidden="true">';

        $html .= '<div class="m-popover-arrow" aria-hidden="true"></div>';

        if ($this->title !== '') {
            $html .= '<div class="m-popover-header">';
            $html .= '<span class="m-popover-title">'
                  . htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8')
                  . '</span>';
            $html .= '</div>';
        }

        $html .= '<div class="m-popover-body">';
        $html .= $this->content;
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
