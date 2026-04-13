<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * ButtonGroup — a set of mutually exclusive toggle buttons (radio behaviour).
 *
 * Exactly one button is active at a time. Clicking a button activates it and
 * deactivates the others within the group.
 *
 * Buttons are icon-only by default. Add a 'label' key to any button definition
 * to show a text label beside the icon. When at least one button has a label the
 * group automatically receives the `m-button-group--labeled` modifier class,
 * which switches from fixed-size icon buttons to auto-sized pill buttons.
 *
 * Usage (icon-only):
 *   echo $m->buttonGroup('sortBar')
 *       ->buttons([
 *           ['value' => 'date-desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Newest first', 'active' => true],
 *           ['value' => 'date-asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Oldest first'],
 *       ]);
 *
 * Usage (with labels):
 *   echo $m->buttonGroup('topicType')
 *       ->buttons([
 *           ['value' => 'general',  'icon' => 'fa-comments',        'label' => 'General',  'active' => true],
 *           ['value' => 'question', 'icon' => 'fa-question-circle', 'label' => 'Question'],
 *           ['value' => 'issue',    'icon' => 'fa-bug',             'label' => 'Issue'],
 *       ]);
 *
 * JS events:
 *   m:buttongroup:change — fired on the group element when the selection changes.
 *   detail: { value: string }
 *
 * JS API (via m.buttonGroup(id)):
 *   .getActive()       — returns the currently active value (string|null)
 *   .setActive(value)  — programmatically activate a button by value
 */
final class ButtonGroup extends Component
{
    /**
     * @var array<int, array{value: string, icon: string, label?: string, tooltip?: string, active?: bool}>
     */
    private array $buttons = [];

    // -------------------------------------------------------------------------
    // Fluent API
    // -------------------------------------------------------------------------

    /**
     * Define the buttons in the group.
     *
     * Each button is an associative array with keys:
     *   'value'   (string, required) — the value emitted on change
     *   'icon'    (string, required) — FA icon name, e.g. 'fa-list-ul'
     *             Pass 'fas fa-icon' or just 'fa-icon' (auto-prefixed with 'fas')
     *   'label'   (string, optional) — visible text label shown beside the icon.
     *             When at least one button has a label the group switches to
     *             auto-sized pill buttons via the m-button-group--labeled modifier.
     *   'tooltip' (string, optional) — tooltip text shown on hover
     *   'active'  (bool, optional)   — whether this button starts active
     *
     * @param array<int, array{value: string, icon: string, label?: string, tooltip?: string, active?: bool}> $buttons
     */
    public function buttons(array $buttons): self
    {
        $this->buttons = $buttons;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Component interface
    // -------------------------------------------------------------------------

    protected function getComponentType(): string
    {
        return 'button-group';
    }

    protected function renderHtml(): string
    {
        $id        = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        // Detect whether any button carries a visible label
        $hasLabels = false;
        foreach ($this->buttons as $btn) {
            if (!empty($btn['label'])) {
                $hasLabels = true;
                break;
            }
        }

        $classes   = array_merge(['m-button-group'], $hasLabels ? ['m-button-group--labeled'] : [], $this->getExtraClasses());
        $classAttr = htmlspecialchars(implode(' ', array_filter($classes)), ENT_QUOTES, 'UTF-8');
        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class', 'data-component']);
        $eventAttrs = $this->renderEventAttributes();

        $buttonsHtml = '';
        foreach ($this->buttons as $btn) {
            $value       = htmlspecialchars((string)($btn['value']   ?? ''), ENT_QUOTES, 'UTF-8');
            $iconRaw     = (string)($btn['icon'] ?? 'fa-circle');
            $label       = (string)($btn['label'] ?? '');
            $tooltip     = (string)($btn['tooltip'] ?? '');
            $isActive    = !empty($btn['active']);
            $activeClass = $isActive ? ' m-button-group-active' : '';

            // Normalise: 'fa-save' → 'fas fa-save', 'far fa-save' → unchanged
            $iconClass = (strpos($iconRaw, ' ') === false) ? 'fas ' . $iconRaw : $iconRaw;
            $iconClass = htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8');

            $tooltipAttr  = $tooltip !== '' ? ' data-m-tooltip="' . htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') . '"' : '';
            $ariaLabel    = $tooltip !== '' ? ' aria-label="' . htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') . '"' : '';

            $labelHtml = $label !== '' ? '<span class="m-button-group-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>' : '';

            $buttonsHtml .= '<button type="button" class="m-button-group-btn' . $activeClass . '"'
                . ' data-value="' . $value . '"'
                . $tooltipAttr
                . $ariaLabel
                . '>'
                . '<i class="' . $iconClass . '" aria-hidden="true"></i>'
                . $labelHtml
                . '</button>';
        }

        return <<<HTML
<div id="{$id}" class="{$classAttr}" data-component="button-group"{$extraAttrs}{$eventAttrs}>
    {$buttonsHtml}
</div>
HTML;
    }
}
