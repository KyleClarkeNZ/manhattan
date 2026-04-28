<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * EmptyState Component
 *
 * A centred illustration-free empty state: icon, heading, message, and optional CTA button.
 * Use wherever a list or data panel can have zero items.
 *
 * Usage:
 *   $m->emptyState('noTasks')
 *       ->icon('fa-clipboard-list')
 *       ->title('No tasks yet')
 *       ->message('Create your first task to get started.')
 *       ->action('Add Task', '/tasks/create', 'fa-plus')
 */
final class EmptyState extends Component
{
    private const ACTION_LINK   = 'link';
    private const ACTION_JS     = 'js';
    private const ACTION_FAB    = 'fab';

    private ?string $icon = null;
    private ?string $titleText = null;
    private ?string $messageText = null;
    private ?string $actionLabel = null;
    private ?string $actionIcon  = null;
    private ?string $actionType  = null;  // ACTION_LINK | ACTION_JS | ACTION_FAB
    private ?string $actionUrl   = null;  // used for ACTION_LINK
    private ?string $actionJs    = null;  // used for ACTION_JS (raw JS expression)
    private ?string $actionFab   = null;  // used for ACTION_FAB (data-fab-action value)
    private bool    $isCompact   = false;
    private bool    $isBordered  = false;

    public function icon(string $faIcon): self
    {
        $this->icon = $faIcon;
        return $this;
    }

    public function title(string $title): self
    {
        $this->titleText = $title;
        return $this;
    }

    public function message(string $message): self
    {
        $this->messageText = $message;
        return $this;
    }

    /**
     * CTA rendered as a link (<a href="...">).
     *
     * @param string $label  Button text
     * @param string $url    Href for the anchor
     * @param string|null $icon  Optional Font Awesome icon class
     */
    public function action(string $label, string $url, ?string $icon = null): self
    {
        $this->actionLabel = $label;
        $this->actionUrl   = $url;
        $this->actionIcon  = $icon;
        $this->actionType  = self::ACTION_LINK;
        return $this;
    }

    /**
     * CTA rendered as a <button onclick="…">.
     * Use for JavaScript-driven actions (e.g. opening a modal).
     *
     * @param string $label     Button text
     * @param string $onClick   Raw JavaScript expression for the onclick handler
     * @param string|null $icon Optional Font Awesome icon class
     */
    public function actionJs(string $label, string $onClick, ?string $icon = null): self
    {
        $this->actionLabel = $label;
        $this->actionJs    = $onClick;
        $this->actionIcon  = $icon;
        $this->actionType  = self::ACTION_JS;
        return $this;
    }

    /**
     * CTA rendered as a <button data-fab-action="…">.
     * Use to trigger a Floating Action Button menu item by its action name.
     *
     * @param string $label       Button text
     * @param string $fabAction   The data-fab-action value (e.g. 'task', 'activity')
     * @param string|null $icon   Optional Font Awesome icon class
     */
    public function actionFab(string $label, string $fabAction, ?string $icon = null): self
    {
        $this->actionLabel = $label;
        $this->actionFab   = $fabAction;
        $this->actionIcon  = $icon;
        $this->actionType  = self::ACTION_FAB;
        return $this;
    }

    /**
     * Compact (low-padding) variant for narrow columns or inline empty panels.
     * Reduces icon size and padding.
     */
    public function compact(): self
    {
        $this->isCompact = true;
        return $this;
    }

    /**
     * Bordered variant — adds a dashed 2-pixel border and a light tinted background.
     * Use inside panels that need a visual container (e.g. day-planner columns).
     * Can be combined with compact(): ->compact()->bordered()
     */
    public function bordered(): self
    {
        $this->isBordered = true;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'empty-state';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-empty-state'], $this->getExtraClasses());
        if ($this->isCompact) {
            $classes[] = 'm-empty-state-compact';
        }
        if ($this->isBordered) {
            $classes[] = 'm-empty-state-bordered';
        }
        $classAttr = implode(' ', $classes);
        $idAttr    = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);

        $iconHtml = '';
        if ($this->icon !== null) {
            $iconHtml = '<div class="m-empty-state-icon" aria-hidden="true">' . (new Icon('', $this->icon))->render() . '</div>';
        }

        $titleHtml = '';
        if ($this->titleText !== null) {
            $titleHtml = '<div class="m-empty-state-title">' . htmlspecialchars($this->titleText, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $messageHtml = '';
        if ($this->messageText !== null) {
            $messageHtml = '<div class="m-empty-state-message">' . htmlspecialchars($this->messageText, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $actionHtml = '';
        if ($this->actionLabel !== null && $this->actionType !== null) {
            switch ($this->actionType) {
                case self::ACTION_LINK:
                    // Anchor links retain <a> semantics — Button component renders <button>, not <a>.
                    $url           = htmlspecialchars((string)$this->actionUrl, ENT_QUOTES, 'UTF-8');
                    $actionIconHtml = $this->actionIcon !== null
                        ? Icon::html($this->actionIcon, ['ariaHidden' => true]) . ' '
                        : '';
                    $label         = htmlspecialchars($this->actionLabel, ENT_QUOTES, 'UTF-8');
                    $actionHtml    = '<a href="' . $url . '" class="m-button m-button-primary m-empty-state-action">'
                        . $actionIconHtml . $label . '</a>';
                    break;

                case self::ACTION_JS:
                    $btn = (new Button($this->id . '_cta', $this->actionLabel))
                        ->primary()
                        ->addClass('m-empty-state-action')
                        ->on('click', (string)$this->actionJs);
                    if ($this->actionIcon !== null) {
                        $btn->icon($this->actionIcon);
                    }
                    $actionHtml = (string)$btn;
                    break;

                case self::ACTION_FAB:
                    $btn = (new Button($this->id . '_cta', $this->actionLabel))
                        ->primary()
                        ->addClass('m-empty-state-action')
                        ->attr('data-fab-action', (string)$this->actionFab);
                    if ($this->actionIcon !== null) {
                        $btn->icon($this->actionIcon);
                    }
                    $actionHtml = (string)$btn;
                    break;
            }
        }

        return <<<HTML
<div id="{$idAttr}" class="{$classAttr}"{$extraAttrs}>
    {$iconHtml}
    {$titleHtml}
    {$messageHtml}
    {$actionHtml}
</div>
HTML;
    }
}
