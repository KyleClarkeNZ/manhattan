<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Icon Component
 *
 * Renders a Font Awesome icon consistently across views/components.
 *
 * Accepts:
 * - 'fa-edit' (defaults to 'fas')
 * - 'far fa-circle' (explicit style)
 * - full FA class strings including modifiers (e.g. 'fas fa-spinner fa-spin')
 */
final class Icon extends Component
{
    private string $faName;
    private string $defaultStyle;
    private bool $ariaHidden = true;
    private ?string $ariaLabel = null;

    public function __construct(string $id, string $faName, array $options = [])
    {
        parent::__construct($id, $options);

        $this->faName = trim($faName);
        $this->defaultStyle = isset($options['style']) && is_string($options['style']) && $options['style'] !== ''
            ? trim($options['style'])
            : 'fas';

        if (isset($options['ariaHidden'])) {
            $this->ariaHidden = (bool)$options['ariaHidden'];
        }
        if (isset($options['ariaLabel']) && is_string($options['ariaLabel']) && trim($options['ariaLabel']) !== '') {
            $this->ariaLabel = trim($options['ariaLabel']);
            $this->ariaHidden = false;
        }
    }

    /**
     * Convenience renderer for inline usage.
     */
    public static function html(string $faName, array $options = []): string
    {
        return (new self('', $faName, $options))->render();
    }

    protected function getComponentType(): string
    {
        return 'icon';
    }

    protected function renderHtml(): string
    {
        if ($this->faName === '') {
            return '';
        }

        $normalizedClasses = $this->normalizeFaClasses($this->faName, $this->defaultStyle);

        $classes = array_merge(['m-icon'], $this->getExtraClasses());
        if ($normalizedClasses !== '') {
            $classes[] = $normalizedClasses;
        }

        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');

        $idAttr = '';
        if (trim($this->id) !== '') {
            $idAttr = ' id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8') . '"';
        }

        $ariaAttrs = '';
        if ($this->ariaLabel !== null) {
            $ariaAttrs .= ' role="img" aria-label="' . htmlspecialchars($this->ariaLabel, ENT_QUOTES, 'UTF-8') . '"';
        } elseif ($this->ariaHidden) {
            $ariaAttrs .= ' aria-hidden="true"';
        }

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class', 'style', 'ariaHidden', 'ariaLabel']);
        $eventAttrs = $this->renderEventAttributes();

        return "<i{$idAttr} class=\"{$classAttr}\"{$ariaAttrs}{$eventAttrs}{$extraAttrs}></i>";
    }

    private function normalizeFaClasses(string $faName, string $defaultStyle): string
    {
        $faName = trim($faName);
        if ($faName === '') {
            return '';
        }

        // If caller provided a full class list (e.g. "far fa-circle" or "fas fa-spinner fa-spin"), keep it.
        if (preg_match('/\s/', $faName) === 1) {
            // If it already includes a known style prefix, keep as-is; otherwise prefix default style.
            if (preg_match('/\b(fas|far|fab|fal|fad)\b/', $faName) === 1) {
                return $faName;
            }
            return trim($defaultStyle . ' ' . $faName);
        }

        // Single token: treat "fa-..." as the icon name.
        if (strncmp($faName, 'fa-', 3) === 0) {
            return trim($defaultStyle . ' ' . $faName);
        }

        // If someone passes "edit" instead of "fa-edit", be forgiving.
        return trim($defaultStyle . ' fa-' . $faName);
    }
}
