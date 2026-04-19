<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan FilterBar Component
 *
 * A reusable filter toolbar combining search, sort, and group/view controls.
 * Manages filter state client-side and fires a unified `m:filterbar:change`
 * event whenever any control changes.  Optionally linked to a Pagination
 * instance so the pager automatically resets to page 1 on every change.
 *
 * The JS API exposes `groupSlice()` — a utility for group-aware pagination
 * that packs grouped items into page buckets without ever splitting a group
 * across page boundaries.
 *
 * Basic usage:
 *   echo $m->filterBar('creditsFilter')
 *       ->search('Search titles, roles…')
 *       ->sort([
 *           ['value' => 'desc', 'icon' => 'fa-arrow-down-wide-short', 'tooltip' => 'Newest first', 'active' => true],
 *           ['value' => 'asc',  'icon' => 'fa-arrow-up-short-wide',   'tooltip' => 'Oldest first'],
 *       ])
 *       ->group([
 *           ['value' => 'year', 'icon' => 'fa-calendar',    'tooltip' => 'Group by year', 'active' => true],
 *           ['value' => 'role', 'icon' => 'fa-layer-group', 'tooltip' => 'Group by credit type'],
 *       ])
 *       ->pager('creditsPager');
 *
 * Filter buttons only (e.g. All / Active):
 *   echo $m->filterBar('userFilter')
 *       ->group([
 *           ['value' => 'all',    'label' => 'All',    'icon' => 'fa-users',        'active' => true],
 *           ['value' => 'active', 'label' => 'Active', 'icon' => 'fa-check-circle'],
 *       ]);
 */
final class FilterBar extends Component
{
    private bool $hasSearch = false;
    private string $searchPlaceholder = 'Search…';
    private array $sortOptions = [];
    private array $groupOptions = [];
    private ?string $pagerId = null;

    protected function getComponentType(): string
    {
        return 'filter-bar';
    }

    // ── Fluent configuration ──────────────────────────────────────────────────

    /**
     * Add a full-text search input.
     *
     * @param string $placeholder  Placeholder text shown inside the input.
     */
    public function search(string $placeholder = 'Search…'): self
    {
        $this->hasSearch         = true;
        $this->searchPlaceholder = $placeholder;
        return $this;
    }

    /**
     * Add a mutually-exclusive sort button group.
     *
     * Each element is an associative array with these keys:
     *   - value   (string, required) — emitted in m:filterbar:change detail.sort
     *   - icon    (string)           — Font Awesome icon, e.g. 'fa-arrow-down-wide-short'
     *   - label   (string)           — visible text label (can be combined with icon)
     *   - tooltip (string)           — hover tooltip
     *   - active  (bool)             — true on the initially-selected option
     *
     * @param array<int, array<string, mixed>> $options
     */
    public function sort(array $options): self
    {
        $this->sortOptions = $options;
        return $this;
    }

    /**
     * Add a mutually-exclusive group / view button group.
     * Accepts the same option shape as sort().
     *
     * @param array<int, array<string, mixed>> $options
     */
    public function group(array $options): self
    {
        $this->groupOptions = $options;
        return $this;
    }

    /**
     * Link this FilterBar to a Pagination instance.
     * When any filter changes the linked pager is automatically reset to page 1.
     *
     * @param string $pagerId  The id="" value of the target Pagination element.
     */
    public function pager(string $pagerId): self
    {
        $this->pagerId = $pagerId;
        return $this;
    }

    // ── Rendering ─────────────────────────────────────────────────────────────

    protected function renderHtml(): string
    {
        $id    = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $extra = $this->renderAdditionalAttributes();

        // Build data attributes
        $data = '';
        if ($this->pagerId !== null) {
            $data .= ' data-pager="' . htmlspecialchars($this->pagerId, ENT_QUOTES, 'UTF-8') . '"';
        }
        foreach ($this->sortOptions as $opt) {
            if (!empty($opt['active'])) {
                $data .= ' data-default-sort="' . htmlspecialchars((string)($opt['value'] ?? ''), ENT_QUOTES, 'UTF-8') . '"';
                break;
            }
        }
        foreach ($this->groupOptions as $opt) {
            if (!empty($opt['active'])) {
                $data .= ' data-default-group="' . htmlspecialchars((string)($opt['value'] ?? ''), ENT_QUOTES, 'UTF-8') . '"';
                break;
            }
        }

        // Class list — base + any extras added via ->addClass()
        $classes = array_merge(['m-filter-bar', 'm-toolbar'], $this->getExtraClasses());
        $classAttr = implode(' ', $classes);

        $html = '<div id="' . $id . '" class="' . $classAttr . '"' . $data . $extra . '>';

        // ── Search ────────────────────────────────────────────────────────────
        if ($this->hasSearch) {
            $ph   = htmlspecialchars($this->searchPlaceholder, ENT_QUOTES, 'UTF-8');
            $html .= '<div class="m-filter-bar-search-wrap">'
                . '<i class="fas fa-magnifying-glass m-filter-bar-search-icon" aria-hidden="true"></i>'
                . '<input type="search" class="m-filter-bar-search" placeholder="' . $ph . '" autocomplete="off" aria-label="' . $ph . '">'
                . '</div>';
        }

        // ── Sort button group ─────────────────────────────────────────────────
        if (!empty($this->sortOptions)) {
            if ($this->hasSearch) {
                $html .= '<div class="m-button-group-sep" aria-hidden="true"></div>';
            }
            $html .= $this->renderButtonGroup($id . '-sort', 'sort', $this->sortOptions);
        }

        // ── Group / view button group ─────────────────────────────────────────
        if (!empty($this->groupOptions)) {
            if ($this->hasSearch || !empty($this->sortOptions)) {
                $html .= '<div class="m-button-group-sep" aria-hidden="true"></div>';
            }
            $html .= $this->renderButtonGroup($id . '-group', 'group', $this->groupOptions);
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render a mutually-exclusive button group for one filter dimension.
     *
     * @param string                          $groupId  HTML id="" for the group element
     * @param string                          $type     'sort' | 'group'
     * @param array<int, array<string, mixed>> $options  Button definitions
     */
    private function renderButtonGroup(string $groupId, string $type, array $options): string
    {
        $gId  = htmlspecialchars($groupId, ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');

        // Detect if any option has a text label (drives labeled variant sizing)
        $hasLabel = false;
        foreach ($options as $opt) {
            if (!empty($opt['label'])) {
                $hasLabel = true;
                break;
            }
        }

        $groupClasses = 'm-button-group m-filter-bar-group'
            . ($hasLabel ? ' m-button-group--labeled' : '');

        $html = '<div id="' . $gId . '" class="' . $groupClasses . '" role="group" data-filter-type="' . $type . '">';

        foreach ($options as $opt) {
            $value   = htmlspecialchars((string)($opt['value']   ?? ''), ENT_QUOTES, 'UTF-8');
            $icon    = (string)($opt['icon'] ?? '');
            $label   = htmlspecialchars((string)($opt['label']   ?? ''), ENT_QUOTES, 'UTF-8');
            $tooltip = htmlspecialchars((string)($opt['tooltip'] ?? ''), ENT_QUOTES, 'UTF-8');
            $active  = !empty($opt['active']);

            $btnClass = 'm-button-group-btn' . ($active ? ' m-button-group-active' : '');

            $html .= '<button type="button" class="' . $btnClass . '"'
                . ' data-value="' . $value . '"'
                . ($tooltip !== '' ? ' data-m-tooltip="' . $tooltip . '"' : '')
                . ' aria-pressed="' . ($active ? 'true' : 'false') . '">';

            if ($icon !== '') {
                $html .= '<i class="fas ' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i>';
                if ($label !== '') {
                    $html .= ' ';
                }
            }
            $html .= $label . '</button>';
        }

        $html .= '</div>';
        return $html;
    }
}
