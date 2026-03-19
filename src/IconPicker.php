<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * IconPicker Component
 *
 * A dropdown-style picker that shows a configurable grid of Font Awesome icons.
 * Selecting an icon updates both the visible trigger and a hidden input for form
 * submission.
 *
 * Usage:
 *   echo $m->iconPicker('topicIcon')
 *       ->name('topic_icon')
 *       ->value('fa-comments')
 *       ->icons([
 *           ['value' => 'fa-comments', 'text' => 'Comments'],
 *           ['value' => 'fa-star',     'text' => 'Featured'],
 *       ])
 *       ->placeholder('Choose an icon…');
 */
final class IconPicker extends Component
{
    /** @var array<int, array{value: string, text: string}> */
    private array $icons = [];

    private ?string $value = null;
    private ?string $name = null;
    private string $placeholder = 'Select an icon…';
    private bool $disabled = false;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['icons']) && is_array($options['icons'])) {
            $this->icons = $options['icons'];
        }
        if (isset($options['value'])) {
            $this->value = (string)$options['value'];
        }
        if (isset($options['name'])) {
            $this->name = (string)$options['name'];
        }
        if (isset($options['placeholder'])) {
            $this->placeholder = (string)$options['placeholder'];
        }
        if (isset($options['disabled'])) {
            $this->disabled = (bool)$options['disabled'];
        }
    }

    /**
     * Set the icon list.
     *
     * @param array<int, array{value: string, text: string}> $icons
     *   Each entry must have 'value' (Font Awesome class, e.g. 'fa-star') and
     *   'text' (human-readable label).
     */
    public function icons(array $icons): self
    {
        $this->icons = $icons;
        return $this;
    }

    /**
     * Set the initially selected icon value (Font Awesome class, e.g. 'fa-star').
     */
    public function value(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set the form field name for the hidden input.
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Placeholder label shown when no icon is selected.
     */
    public function placeholder(string $text): self
    {
        $this->placeholder = $text;
        return $this;
    }

    /**
     * Disable the picker.
     */
    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'iconpicker';
    }

    protected function renderHtml(): string
    {
        $selectedValue = $this->value ?? '';

        // Resolve friendly label for the current value
        $selectedText = $this->placeholder;
        foreach ($this->icons as $icon) {
            if ((string)($icon['value'] ?? '') === $selectedValue) {
                $selectedText = (string)($icon['text'] ?? $selectedValue);
                break;
            }
        }

        $disabledAttr = $this->disabled ? ' disabled' : '';
        $nameAttr     = $this->name !== null
            ? ' name="' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8') . '"'
            : '';

        $valueEsc = htmlspecialchars($selectedValue,   ENT_QUOTES, 'UTF-8');
        $textEsc  = htmlspecialchars($selectedText,    ENT_QUOTES, 'UTF-8');
        $phEsc    = htmlspecialchars($this->placeholder, ENT_QUOTES, 'UTF-8');

        if ($selectedValue !== '') {
            $iconHtml = '<i class="fas ' . htmlspecialchars($selectedValue, ENT_QUOTES, 'UTF-8')
                . ' m-iconpicker-trigger-icon" aria-hidden="true"></i>';
        } else {
            $iconHtml = '<i class="fas fa-icons m-iconpicker-trigger-icon m-iconpicker-placeholder-icon" aria-hidden="true"></i>';
        }

        $html  = '<div';
        $html .= ' class="m-iconpicker"';
        $html .= ' id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' data-component="iconpicker"';
        $html .= ' data-value="' . $valueEsc . '"';
        $html .= ' data-placeholder="' . $phEsc . '"';
        $html .= '>';

        // Trigger button
        $html .= '<button type="button" class="m-iconpicker-trigger"'
            . $disabledAttr
            . ' aria-haspopup="listbox"'
            . ' aria-expanded="false">';
        $html .= $iconHtml;
        $html .= '<span class="m-iconpicker-trigger-label">' . $textEsc . '</span>';
        $html .= '<i class="fas fa-chevron-down m-iconpicker-caret" aria-hidden="true"></i>';
        $html .= '</button>';

        // Dropdown panel
        $html .= '<div class="m-iconpicker-panel" role="listbox" aria-label="Icon selection" hidden>';
        $html .= '<div class="m-iconpicker-grid">';
        foreach ($this->icons as $icon) {
            $v      = htmlspecialchars((string)($icon['value'] ?? ''), ENT_QUOTES, 'UTF-8');
            $t      = htmlspecialchars((string)($icon['text']  ?? ''), ENT_QUOTES, 'UTF-8');
            $isSel  = ((string)($icon['value'] ?? '') === $selectedValue);
            $selCls = $isSel ? ' m-iconpicker-selected' : '';
            $ariaSel = $isSel ? ' aria-selected="true"' : ' aria-selected="false"';
            $html  .= '<button type="button"'
                . ' class="m-iconpicker-btn' . $selCls . '"'
                . ' data-value="' . $v . '"'
                . ' data-label="' . $t . '"'
                . ' title="' . $t . '"'
                . ' role="option"'
                . $ariaSel
                . '>';
            $html .= '<i class="fas ' . $v . '" aria-hidden="true"></i>';
            $html .= '</button>';
        }
        $html .= '</div>';
        $html .= '</div>';

        // Hidden input for form submission
        $html .= '<input type="hidden"' . $nameAttr . ' class="m-iconpicker-input" value="' . $valueEsc . '">';

        $html .= '</div>';

        return $html;
    }
}
