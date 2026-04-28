<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Chip Component
 *
 * Subtle inline status tags, category indicators, and metadata labels.
 * Lighter-weight than Badge (solid gradient) — uses a soft tinted background
 * with a coloured text instead of a filled pill.
 *
 * Usage:
 *   <?= $m->chip('status', 'Active')->success() ?>
 *   <?= $m->chip('cat', 'Design')->primary()->icon('fa-palette') ?>
 *
 * CSS classes: .m-chip  .m-chip-{variant}
 */
final class Chip extends Component
{
    private string $text = '';
    private string $variant = 'default';
    private ?string $icon = null;

    public function __construct(string $id, string $text = '', array $options = [])
    {
        parent::__construct($id, $options);
        $this->text = $text;
    }

    public function text(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param string $variant default|primary|success|warning|danger|purple|secondary|info
     */
    public function variant(string $variant): self
    {
        $this->variant = $variant;
        return $this;
    }

    public function primary(): self   { return $this->variant('primary'); }
    public function success(): self   { return $this->variant('success'); }
    public function warning(): self   { return $this->variant('warning'); }
    public function danger(): self    { return $this->variant('danger'); }
    public function purple(): self    { return $this->variant('purple'); }
    public function secondary(): self { return $this->variant('secondary'); }
    public function info(): self      { return $this->variant('info'); }

    public function icon(string $faIcon): self
    {
        $this->icon = $faIcon;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'chip';
    }

    protected function renderHtml(): string
    {
        $classes   = array_merge(['m-chip', 'm-chip-' . $this->variant], $this->getExtraClasses());
        $attrs     = $this->renderAdditionalAttributes(['id', 'class']) . $this->renderEventAttributes();
        $classAttr = htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');
        $idAttr    = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        $content = '';
        if ($this->icon !== null) {
            $content .= Icon::html($this->icon, ['ariaHidden' => true]) . ' ';
        }
        $content .= htmlspecialchars($this->text, ENT_QUOTES, 'UTF-8');

        return sprintf('<span id="%s" class="%s"%s>%s</span>', $idAttr, $classAttr, $attrs, $content);
    }

    public function __toString(): string
    {
        return $this->renderHtml();
    }
}
