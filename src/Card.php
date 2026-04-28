<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Card Component
 *
 * A simple container with optional header and footer.
 * Note: title/subtitle/content/footer accept HTML strings (intended for trusted view markup).
 */
class Card extends Component
{
    private ?string $titleHtml = null;
    private ?string $subtitleHtml = null;
    private ?string $contentHtml = null;
    private ?string $footerHtml = null;

    /** @var array<int, array{title: string, linkUrl: ?string, linkText: string}> */
    private array $sectionHeaders = [];

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['title'])) {
            $this->titleHtml = (string)$options['title'];
        }
        if (isset($options['subtitle'])) {
            $this->subtitleHtml = (string)$options['subtitle'];
        }
        if (isset($options['content'])) {
            $this->contentHtml = (string)$options['content'];
        }
        if (isset($options['footer'])) {
            $this->footerHtml = (string)$options['footer'];
        }
    }

    public function title(string $titleHtml): self
    {
        $this->titleHtml = $titleHtml;
        return $this;
    }

    public function subtitle(string $subtitleHtml): self
    {
        $this->subtitleHtml = $subtitleHtml;
        return $this;
    }

    public function content(string $contentHtml): self
    {
        $this->contentHtml = $contentHtml;
        return $this;
    }

    public function footer(string $footerHtml): self
    {
        $this->footerHtml = $footerHtml;
        return $this;
    }

    /**
     * Prepend a section-header divider with optional link before the card body.
     * Call multiple times to add multiple sections.
     *
     * @param string      $title        Trusted HTML string (icons allowed).
     * @param string|null $linkUrl      Optional href for the right-side action link.
     * @param string      $linkText     Trusted HTML for the link label (icons allowed).
     * @param bool        $linkExternal When true, adds target="_blank" rel="noopener noreferrer".
     */
    public function sectionHeader(string $title, ?string $linkUrl = null, string $linkText = 'View all', bool $linkExternal = false): self
    {
        $this->sectionHeaders[] = [
            'title'        => $title,
            'linkUrl'      => $linkUrl,
            'linkText'     => $linkText,
            'linkExternal' => $linkExternal,
        ];
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'card';
    }

    protected function renderHtml(): string
    {
        $classes = array_merge(['m-card', 'm-card-default'], $this->getExtraClasses());
        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');
        $idAttr = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);
        $eventAttrs = $this->renderEventAttributes();

        $headerHtml = '';
        if (($this->titleHtml !== null && trim($this->titleHtml) !== '') || ($this->subtitleHtml !== null && trim($this->subtitleHtml) !== '')) {
            $title = $this->titleHtml !== null ? $this->titleHtml : '';
            $subtitle = $this->subtitleHtml !== null ? '<div class="m-card-subtitle">' . $this->subtitleHtml . '</div>' : '';

            $titleBlock = $title !== '' ? '<div class="m-card-title">' . $title . '</div>' : '';

            $headerHtml = '<div class="m-card-header">' . $titleBlock . $subtitle . '</div>';
        }

        $sectionHeadersHtml = '';
        foreach ($this->sectionHeaders as $sh) {
            $shTitle = $sh['title']; // trusted HTML, not escaped (same as content/title)
            $shLink = '';
            if ($sh['linkUrl'] !== null) {
                $shLinkUrl  = htmlspecialchars($sh['linkUrl'], ENT_QUOTES, 'UTF-8');
                $shLinkText = $sh['linkText']; // trusted HTML — allows icons in the link label
                $shExtAttrs = !empty($sh['linkExternal']) ? ' target="_blank" rel="noopener noreferrer"' : '';
                $shLink = '<a class="m-card-section-link" href="' . $shLinkUrl . '"' . $shExtAttrs . '>' . $shLinkText . '</a>';
            }
            $sectionHeadersHtml .= '<div class="m-card-section-header"><span class="m-card-section-title">' . $shTitle . '</span>' . $shLink . '</div>';
        }

        $bodyHtml = $this->contentHtml !== null ? $this->contentHtml : '';
        $footerHtml = $this->footerHtml !== null && trim($this->footerHtml) !== ''
            ? '<div class="m-card-footer">' . $this->footerHtml . '</div>'
            : '';

        return <<<HTML
<div id="{$idAttr}" class="{$classAttr}"{$eventAttrs}{$extraAttrs}>
    {$headerHtml}
    {$sectionHeadersHtml}
    <div class="m-card-body">{$bodyHtml}</div>
    {$footerHtml}
</div>
HTML;
    }
}
