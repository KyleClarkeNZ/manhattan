<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Label Component
 *
 * Renders a semantic HTML <label> element for use with form inputs.
 * Associates with any input by ID via `for` / the `->for()` fluent method.
 * Optionally marks required fields and displays a help icon.
 *
 * Usage:
 *   <?= $m->label('title-label', 'Title')->for('titleInput') ?>
 *   <?= $m->label('email-label', 'Email address')->for('emailInput')->required() ?>
 *   <?= $m->label('note-label', 'Notes')->for('notesArea')->hint('Optional') ?>
 */
final class Label extends Component
{
    private string $text = '';
    private string $for = '';
    private bool $required = false;
    private string $hint = '';
    private string $icon = '';

    public function __construct(string $id, string $text = '', array $options = [])
    {
        parent::__construct($id, $options);
        $this->text = $text;
    }

    /**
     * Set the label text.
     */
    public function text(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set the `for` attribute — the ID of the associated input element.
     */
    public function for(string $inputId): self
    {
        $this->for = $inputId;
        return $this;
    }

    /**
     * Mark the field as required (adds a red asterisk).
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Add optional hint/sub-label text rendered beside the label.
     */
    public function hint(string $hint): self
    {
        $this->hint = $hint;
        return $this;
    }

    /**
     * Prepend a Font Awesome icon to the label text.
     * Accepts the same icon strings as $m->icon(), e.g. 'fa-envelope', 'far fa-circle'.
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'label';
    }

    protected function renderHtml(): string
    {
        $classes   = array_merge(['m-label'], $this->getExtraClasses());
        $classAttr = htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');
        $idAttr    = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $forAttr   = $this->for !== ''
            ? ' for="' . htmlspecialchars($this->for, ENT_QUOTES, 'UTF-8') . '"'
            : '';
        $attrs     = $this->renderAdditionalAttributes(['id', 'class', 'for']) . $this->renderEventAttributes();

        $inner = $this->icon !== ''
            ? Icon::html($this->icon, ['ariaHidden' => true]) . ' '
            : '';
        $inner .= htmlspecialchars($this->text, ENT_QUOTES, 'UTF-8');

        if ($this->required) {
            $inner .= '<span class="m-label-required" aria-hidden="true">*</span>';
        }

        if ($this->hint !== '') {
            $inner .= ' <span class="m-label-hint">' . htmlspecialchars($this->hint, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        return sprintf(
            '<label id="%s" class="%s"%s%s>%s</label>',
            $idAttr,
            $classAttr,
            $forAttr,
            $attrs,
            $inner
        );
    }

    public function __toString(): string
    {
        return $this->renderHtml();
    }
}

