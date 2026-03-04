<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Tabs Component
 *
 * Renders a tabbed interface with a tab strip and corresponding panels.
 * Supports icons, disabled tabs, and keyboard navigation via tabs.js.
 *
 * Usage:
 *   $tabs = $m->tabs('myTabs')
 *       ->tab('general', 'General')->icon('fa-cog')->content('<p>General settings</p>')->active()
 *       ->tab('advanced', 'Advanced')->icon('fa-sliders-h')->content('<p>Advanced</p>')
 *       ->tab('disabled', 'Disabled')->disabled();
 *
 * The fluent ->tab() method adds a new TabPanel and returns $this so you can
 * chain TabPanel methods (icon, content, active, disabled). Calling another
 * Tabs-level method (tab, addClass, etc.) or rendering ends the panel chain.
 */
class Tabs extends Component
{
    /** @var TabPanel[] */
    private array $panels = [];

    /** @var TabPanel|null The panel currently being configured via fluent chain */
    private ?TabPanel $currentPanel = null;

    /** @var string Visual style: 'default' | 'pills' | 'underline' */
    private string $style = 'default';

    /** @var array<string, int> Tab key => count badge */
    private array $badges = [];

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['style']) && is_string($options['style'])) {
            $this->style = $options['style'];
        }
    }

    /**
     * Add a new tab panel and begin its fluent configuration.
     *
     * @param string $key   Unique key for the tab
     * @param string $label Tab header text
     * @return self
     */
    public function tab(string $key, string $label): self
    {
        // Seal any previously-open panel
        $this->sealCurrentPanel();

        $panel = new TabPanel($key, $label);
        $this->currentPanel = $panel;
        return $this;
    }

    /**
     * Set an icon on the current tab panel.
     */
    public function icon(string $icon): self
    {
        if ($this->currentPanel !== null) {
            $this->currentPanel->icon($icon);
        }
        return $this;
    }

    /**
     * Set HTML content on the current tab panel.
     */
    public function content(string $html): self
    {
        if ($this->currentPanel !== null) {
            $this->currentPanel->content($html);
        }
        return $this;
    }

    /**
     * Mark the current tab panel as active.
     */
    public function active(bool $active = true): self
    {
        if ($this->currentPanel !== null) {
            $this->currentPanel->active($active);
        }
        return $this;
    }

    /**
     * Mark the current tab panel as disabled.
     */
    public function disabled(bool $disabled = true): self
    {
        if ($this->currentPanel !== null) {
            $this->currentPanel->disabled($disabled);
        }
        return $this;
    }

    /**
     * Add a numeric badge to a tab by its key.
     */
    public function badge(string $key, int $count): self
    {
        $this->sealCurrentPanel();
        $this->badges[$key] = $count;
        return $this;
    }

    /**
     * Set the visual style for the tabs strip.
     *
     * @param string $style 'default' | 'pills' | 'underline'
     */
    public function tabStyle(string $style): self
    {
        $this->sealCurrentPanel();
        $this->style = $style;
        return $this;
    }

    // ------------------------------------------------------------------
    // Rendering
    // ------------------------------------------------------------------

    protected function getComponentType(): string
    {
        return 'tabs';
    }

    protected function renderHtml(): string
    {
        // Seal any open panel
        $this->sealCurrentPanel();

        // Ensure at least one panel is active
        $hasActive = false;
        foreach ($this->panels as $panel) {
            if ($panel->isActive() && !$panel->isDisabled()) {
                $hasActive = true;
                break;
            }
        }
        if (!$hasActive) {
            foreach ($this->panels as $panel) {
                if (!$panel->isDisabled()) {
                    $panel->active(true);
                    break;
                }
            }
        }

        $idAttr = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $classes = array_merge(['m-tabs'], $this->getExtraClasses());
        if ($this->style !== 'default') {
            $classes[] = 'm-tabs-' . htmlspecialchars($this->style, ENT_QUOTES, 'UTF-8');
        }
        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');
        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);
        $eventAttrs = $this->renderEventAttributes();

        // --- Tab strip ---
        $tabButtons = '';
        foreach ($this->panels as $panel) {
            $key = htmlspecialchars($panel->key(), ENT_QUOTES, 'UTF-8');
            $panelId = $idAttr . '_panel_' . $key;
            $tabId = $idAttr . '_tab_' . $key;
            $label = htmlspecialchars($panel->getLabel(), ENT_QUOTES, 'UTF-8');

            $btnClasses = 'm-tabs-tab';
            if ($panel->isActive()) {
                $btnClasses .= ' m-active';
            }
            if ($panel->isDisabled()) {
                $btnClasses .= ' m-disabled';
            }

            $iconHtml = '';
            if ($panel->getIcon() !== null) {
                $iconObj = new Icon('', $panel->getIcon());
                $iconHtml = (string)$iconObj . ' ';
            }

            $disabledAttr = $panel->isDisabled() ? ' aria-disabled="true" tabindex="-1"' : '';
            $selectedAttr = $panel->isActive() ? 'true' : 'false';

            $badgeHtml = '';
            if (isset($this->badges[$panel->key()]) && $this->badges[$panel->key()] > 0) {
                $badgeCount = htmlspecialchars((string)$this->badges[$panel->key()], ENT_QUOTES, 'UTF-8');
                $badgeHtml = '<span class="m-tabs-badge">' . $badgeCount . '</span>';
            }

            $tabButtons .= <<<HTML
<button type="button" id="{$tabId}" class="{$btnClasses}" role="tab" aria-selected="{$selectedAttr}" aria-controls="{$panelId}" data-tab-key="{$key}"{$disabledAttr}>{$iconHtml}{$label}{$badgeHtml}</button>
HTML;
        }

        // --- Tab panels ---
        $tabPanels = '';
        foreach ($this->panels as $panel) {
            $key = htmlspecialchars($panel->key(), ENT_QUOTES, 'UTF-8');
            $panelId = $idAttr . '_panel_' . $key;
            $tabId = $idAttr . '_tab_' . $key;
            $hiddenAttr = $panel->isActive() ? '' : ' hidden';

            $tabPanels .= <<<HTML
<div id="{$panelId}" class="m-tabs-panel" role="tabpanel" aria-labelledby="{$tabId}" data-tab-key="{$key}"{$hiddenAttr}>{$panel->getContentHtml()}</div>
HTML;
        }

        return <<<HTML
<div id="{$idAttr}" class="{$classAttr}"{$eventAttrs}{$extraAttrs}>
<div class="m-tabs-strip" role="tablist">{$tabButtons}</div>
<div class="m-tabs-body">{$tabPanels}</div>
</div>
HTML;
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    /**
     * Seal the current panel: push it into the panels array and clear.
     */
    private function sealCurrentPanel(): void
    {
        if ($this->currentPanel !== null) {
            $this->panels[] = $this->currentPanel;
            $this->currentPanel = null;
        }
    }
}
