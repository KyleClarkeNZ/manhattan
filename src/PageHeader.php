<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * PageHeader Component
 *
 * Standardised page title area: optional breadcrumb above, icon + h1 title, optional subtitle.
 * Eliminates bespoke .page-title-section markup scattered across views.
 *
 * Usage:
 *   $m->pageHeader('tasksHeader')
 *       ->breadcrumb($m->breadcrumb('nav')->home('/')->item('Tasks')->current())
 *       ->icon('fa-clipboard-list')
 *       ->title('My Tasks')
 *       ->subtitle('Manage tasks, plan your day, and track activities')
 */
final class PageHeader extends Component
{
    private ?string $titleText = null;
    private ?string $subtitleText = null;
    private ?string $icon = null;
    private ?Breadcrumb $breadcrumbComponent = null;

    public function title(string $title): self
    {
        $this->titleText = $title;
        return $this;
    }

    public function subtitle(string $subtitle): self
    {
        $this->subtitleText = $subtitle;
        return $this;
    }

    public function icon(string $faIcon): self
    {
        $this->icon = $faIcon;
        return $this;
    }

    public function breadcrumb(Breadcrumb $breadcrumb): self
    {
        $this->breadcrumbComponent = $breadcrumb;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'page-header';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-page-header'], $this->getExtraClasses());
        $classAttr = implode(' ', $classes);
        $idAttr = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);

        $breadcrumbHtml = $this->breadcrumbComponent !== null
            ? $this->breadcrumbComponent->render()
            : '';

        $iconHtml = '';
        if ($this->icon !== null) {
            $iconHtml = (new Icon('', $this->icon))->render() . ' ';
        }

        $titleHtml = '';
        if ($this->titleText !== null) {
            $titleHtml = '<h1>' . $iconHtml . htmlspecialchars($this->titleText, ENT_QUOTES, 'UTF-8') . '</h1>';
        }

        $subtitleHtml = '';
        if ($this->subtitleText !== null) {
            $subtitleHtml = '<p class="m-page-header-subtitle">' . htmlspecialchars($this->subtitleText, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        return <<<HTML
<div id="{$idAttr}" class="{$classAttr}"{$extraAttrs}>
    {$breadcrumbHtml}
    {$titleHtml}
    {$subtitleHtml}
</div>
HTML;
    }
}
