<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * TabPanel - Defines a single tab within a Tabs component.
 *
 * Not rendered directly; consumed by Tabs::renderHtml().
 */
class TabPanel
{
    /** @var string Unique key for this tab (used as panel id suffix) */
    private string $key;

    /** @var string Tab header text */
    private string $label;

    /** @var string|null Optional Font Awesome icon name */
    private ?string $icon = null;

    /** @var string Panel body HTML */
    private string $contentHtml = '';

    /** @var bool Whether this tab is the initially active tab */
    private bool $active = false;

    /** @var bool Whether this tab is disabled */
    private bool $disabled = false;

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getContentHtml(): string
    {
        return $this->contentHtml;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Set Font Awesome icon for the tab header
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set HTML content for the tab panel body
     */
    public function content(string $html): self
    {
        $this->contentHtml = $html;
        return $this;
    }

    /**
     * Mark this tab as the initially active/selected tab
     */
    public function active(bool $active = true): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Disable this tab
     */
    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }
}
