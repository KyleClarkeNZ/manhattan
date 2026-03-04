<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * DataGrid Component
 *
 * Feature-rich data grid component. Supports local and remote data
 * binding, server-side or client-side pagination, sortable / resizable /
 * reorderable columns, row grouping, row selection, and a toolbar.
 *
 * Usage — local data:
 *   $m->dataGrid('myGrid')
 *       ->columns([
 *           ['field' => 'id',     'title' => '#',      'width' => 60],
 *           ['field' => 'name',   'title' => 'Name',   'sortable' => true],
 *           ['field' => 'status', 'title' => 'Status', 'template' => '<span class="badge">{status}</span>'],
 *       ])
 *       ->dataSource($rows)
 *       ->pageable(20)
 *       ->sortable()
 *       ->resizable()
 *       ->reorderable();
 *
 * Usage — remote data:
 *   $m->dataGrid('remoteGrid')
 *       ->columns([...])
 *       ->remoteUrl('/api/tasks', 'GET')
 *       ->pageable(25)
 *       ->sortable()
 *       ->resizable();
 */
final class DataGrid extends Component
{
    // ── Column definitions ────────────────────────────────────────────────────

    /** @var array<int,array<string,mixed>> */
    private array $columns = [];

    // ── Local data ────────────────────────────────────────────────────────────

    /** @var array<int,array<string,mixed>>|null */
    private ?array $localData = null;

    // ── Remote data ───────────────────────────────────────────────────────────

    private ?string $remoteDataUrl    = null;
    private string  $remoteDataMethod = 'GET';

    /**
     * Optional headers sent with every remote request.
     * @var array<string,string>
     */
    private array $remoteHeaders = [];

    // ── Pagination ────────────────────────────────────────────────────────────

    private bool $isPaginated = false;
    private int  $pageSize    = 20;
    /** 'local' = JS handles paging; 'remote' = server must honour page/pageSize params */
    private string $pageMode  = 'local';

    // ── Feature flags ─────────────────────────────────────────────────────────

    private bool $isSortable    = false;
    private bool $isResizable   = false;
    private bool $isReorderable = false;
    private bool $isGroupable   = false;
    private bool $isSelectable  = false;
    private bool $showToolbar   = false;

    // ── Appearance ────────────────────────────────────────────────────────────

    private ?string $height     = null;
    private bool    $isStriped  = false;
    private bool    $isBordered = true;

    // ── Toolbar buttons ───────────────────────────────────────────────────────

    /**
     * Each toolbar button is an array:
     *   ['text' => 'Add', 'icon' => 'fa-plus', 'click' => 'myFn()', 'class' => '']
     * @var array<int,array<string,string>>
     */
    private array $toolbarButtons = [];

    // ── Empty state ───────────────────────────────────────────────────────────

    private string  $emptyTitle   = 'No data available';
    private string  $emptyMessage = '';

    // ── Callbacks (JS function names or expressions) ──────────────────────────

    private ?string $onDataBound  = null;
    private ?string $onRowClick   = null;
    private ?string $onRowSelect  = null;
    private ?string $onRowExpand  = null;

    // ─────────────────────────────────────────────────────────────────────────
    // Builder API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Define the columns.
     *
     * Each column accepts:
     *   field      (string)       — data key
     *   title      (string)       — header text
     *   width      (int)          — initial pixel width (optional)
     *   template   (string)       — HTML template with {field} placeholders (optional)
     *   component  (array)        — embedded Manhattan component config (see below)
     *   sortable   (bool)         — per-column override; default follows ->sortable()
     *   resizable  (bool)         — per-column override; default follows ->resizable()
     *   groupable  (bool)         — column can be grouped by; default true when ->groupable() set
     *   hidden     (bool)         — hide column (still in data)
     *   format     (string)       — 'date', 'datetime', 'number', 'currency' (optional)
     *   align      (string)       — 'left'|'center'|'right' (default left)
     *   class      (string)       — extra CSS classes on the <td>
     *   frozen     (bool)         — stick column to the left during horizontal scroll
     *   wrap       (bool)         — allow cell text to wrap (disables nowrap/ellipsis)
     *
     * Component config keys (column['component']):
     *   type        (string)  required — 'progressBar'|'badge'|'label'|'icon'|'checkbox'|'rating'
     *
     *   Data-binding: any property ending in 'Bind' references a row field name.
     *   Literal properties provide fixed values.
     *
     *   progressBar: valueBind, maxBind, max, variant, variantBind, showPercent, label, labelBind, striped, animated
     *   badge:       textBind, text, variant, variantBind, icon, iconBind
     *   label:       textBind, text, variant, variantBind, icon, iconBind
     *   icon:        iconBind, icon
     *   checkbox:    valueBind, readonly (default true)
     *   rating:      valueBind, max, halfStars
     *
     * Example:
     *   ['field' => 'progress', 'title' => 'Progress', 'width' => 160, 'component' => [
     *       'type'        => 'progressBar',
     *       'valueBind'   => 'progress',
     *       'max'         => 100,
     *       'variant'     => 'success',
     *       'showPercent' => true,
     *   ]]
     *
     * @param array<int,array<string,mixed>> $columns
     */
    public function columns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Provide local (PHP-side) array data.
     * Each element is an associative array whose keys match column `field` values.
     *
     * @param array<int,array<string,mixed>> $data
     */
    public function dataSource(array $data): self
    {
        $this->localData = $data;
        return $this;
    }

    /**
     * Use a remote URL for data fetching.
     * The JS layer will send GET/POST requests with params:
     *   page, pageSize, sortField, sortDir, groupField (when applicable)
     * Expected response: { data: [...], total: <int> }
     */
    public function remoteUrl(string $url, string $method = 'GET'): self
    {
        $this->remoteDataUrl    = $url;
        $this->remoteDataMethod = strtoupper($method);
        return $this;
    }

    /**
     * Additional HTTP headers to include with every remote request.
     * Useful for CSRF tokens: ->remoteHeaders(['X-CSRF-Token' => $csrfToken])
     *
     * @param array<string,string> $headers
     */
    public function remoteHeaders(array $headers): self
    {
        $this->remoteHeaders = $headers;
        return $this;
    }

    /**
     * Enable pagination.
     *
     * @param int    $pageSize  Rows per page (default 20)
     * @param string $mode      'local' (JS) or 'remote' (server)
     */
    public function pageable(int $pageSize = 20, string $mode = 'local'): self
    {
        $this->isPaginated = true;
        $this->pageSize    = max(1, $pageSize);
        $this->pageMode    = ($mode === 'remote') ? 'remote' : 'local';
        return $this;
    }

    /** Enable column sorting (applies to all columns unless overridden per-column). */
    public function sortable(): self
    {
        $this->isSortable = true;
        return $this;
    }

    /** Enable column resize by dragging header dividers. */
    public function resizable(): self
    {
        $this->isResizable = true;
        return $this;
    }

    /** Enable column reordering by dragging column headers. */
    public function reorderable(): self
    {
        $this->isReorderable = true;
        return $this;
    }

    /** Enable grouping: a drag-target row is shown above the grid. */
    public function groupable(): self
    {
        $this->isGroupable = true;
        return $this;
    }

    /** Enable single-row selection. */
    public function selectable(): self
    {
        $this->isSelectable = true;
        return $this;
    }

    /**
     * Set a fixed grid height (enables a scrollable body).
     * Example: '400px', '50vh'
     */
    public function height(string $height): self
    {
        $this->height = $height;
        return $this;
    }

    /** Render alternate-row striping. */
    public function striped(): self
    {
        $this->isStriped = true;
        return $this;
    }

    /** Remove the outer border. */
    public function borderless(): self
    {
        $this->isBordered = false;
        return $this;
    }

    /**
     * Add toolbar buttons.
     *
     * Each button:
     *   text   (string) required   — button label
     *   icon   (string) optional   — Font Awesome class e.g. 'fa-plus'
     *   click  (string) optional   — JS expression called on click
     *   class  (string) optional   — extra CSS classes
     *
     * @param array<int,array<string,string>> $buttons
     */
    public function toolbar(array $buttons): self
    {
        $this->toolbarButtons = $buttons;
        $this->showToolbar    = !empty($buttons);
        return $this;
    }

    /** Custom empty-state title and optional message. */
    public function emptyState(string $title, string $message = ''): self
    {
        $this->emptyTitle   = $title;
        $this->emptyMessage = $message;
        return $this;
    }

    /** JS callback fired after data is rendered. Receives (grid, data[]) */
    public function onDataBound(string $callback): self
    {
        $this->onDataBound = $callback;
        return $this;
    }

    /** JS callback fired on row click. Receives (rowElement, rowData) */
    public function onRowClick(string $callback): self
    {
        $this->onRowClick = $callback;
        return $this;
    }

    /** JS callback fired when a row selection changes. Receives (rowElement, rowData) */
    public function onRowSelect(string $callback): self
    {
        $this->onRowSelect = $callback;
        return $this;
    }

    /** JS callback fired when a group row is expanded/collapsed. Receives (groupKey, isExpanded) */
    public function onRowExpand(string $callback): self
    {
        $this->onRowExpand = $callback;
        return $this;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Rendering
    // ─────────────────────────────────────────────────────────────────────────

    protected function getComponentType(): string
    {
        return 'datagrid';
    }

    protected function renderHtml(): string
    {
        $id    = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $extra = $this->renderAdditionalAttributes(['id', 'class', 'data-datagrid-config', 'data-datagrid-data']);

        $classes = array_merge(['m-datagrid'], $this->getExtraClasses());
        if ($this->isBordered)  { $classes[] = 'm-datagrid-bordered'; }
        if ($this->isStriped)   { $classes[] = 'm-datagrid-striped'; }
        if ($this->height)      { $classes[] = 'm-datagrid-fixed-height'; }
        if ($this->isGroupable) { $classes[] = 'm-datagrid-groupable'; }
        $classAttr = htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');

        // Build config object for JS
        $config = $this->buildConfig();
        $configJson = htmlspecialchars(
            json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP) ?: '{}',
            ENT_QUOTES,
            'UTF-8'
        );

        // Local data (may be large) goes in a separate attribute
        $dataJson = '';
        if ($this->localData !== null) {
            $dataJson = htmlspecialchars(
                json_encode($this->localData, JSON_HEX_TAG | JSON_HEX_AMP) ?: '[]',
                ENT_QUOTES,
                'UTF-8'
            );
        }

        $styleAttr = $this->height ? ' style="--m-datagrid-height:' . htmlspecialchars($this->height, ENT_QUOTES, 'UTF-8') . '"' : '';

        $dataAttr = $dataJson !== '' ? (' data-datagrid-data="' . $dataJson . '"') : '';

        return <<<HTML
<div id="{$id}" class="{$classAttr}" data-datagrid-config="{$configJson}"{$dataAttr}{$styleAttr}{$extra}></div>
HTML;
    }

    /**
     * Build the JSON configuration array consumed by the JS layer.
     *
     * @return array<string,mixed>
     */
    private function buildConfig(): array
    {
        return [
            'columns'      => $this->normalizeColumns(),
            'remote'       => $this->remoteDataUrl !== null ? [
                'url'     => $this->remoteDataUrl,
                'method'  => $this->remoteDataMethod,
                'headers' => $this->remoteHeaders,
            ] : null,
            'pageable'     => $this->isPaginated ? [
                'pageSize' => $this->pageSize,
                'mode'     => $this->pageMode,
            ] : false,
            'sortable'    => $this->isSortable,
            'resizable'   => $this->isResizable,
            'reorderable' => $this->isReorderable,
            'groupable'   => $this->isGroupable,
            'selectable'  => $this->isSelectable,
            'toolbar'     => $this->showToolbar ? $this->toolbarButtons : [],
            'emptyState'  => [
                'title'   => $this->emptyTitle,
                'message' => $this->emptyMessage,
            ],
            'callbacks'   => array_filter([
                'onDataBound' => $this->onDataBound,
                'onRowClick'  => $this->onRowClick,
                'onRowSelect' => $this->onRowSelect,
                'onRowExpand' => $this->onRowExpand,
            ]),
        ];
    }

    /**
     * Normalize columns — fill in defaults so the JS doesn't have to.
     *
     * @return array<int,array<string,mixed>>
     */
    private function normalizeColumns(): array
    {
        $out = [];
        foreach ($this->columns as $col) {
            $normalized = [
                'field'     => (string)($col['field']     ?? ''),
                'title'     => (string)($col['title']     ?? ucfirst((string)($col['field'] ?? ''))),
                'width'     => isset($col['width'])     ? (int)$col['width']           : null,
                'template'  => isset($col['template'])  ? (string)$col['template']     : null,
                'component' => isset($col['component']) && is_array($col['component'])  ? $col['component'] : null,
                'sortable'  => isset($col['sortable'])  ? (bool)$col['sortable']        : $this->isSortable,
                'resizable' => isset($col['resizable']) ? (bool)$col['resizable']       : $this->isResizable,
                'groupable' => isset($col['groupable']) ? (bool)$col['groupable']       : $this->isGroupable,
                'hidden'    => (bool)($col['hidden']    ?? false),
                'format'    => isset($col['format'])    ? (string)$col['format']        : null,
                'align'     => in_array($col['align'] ?? '',  ['left','center','right'], true) ? $col['align'] : 'left',
                'class'     => (string)($col['class']   ?? ''),
                'frozen'    => (bool)($col['frozen']    ?? false),
                'wrap'      => (bool)($col['wrap']      ?? false),
            ];
            // Inject the column field into the component config so renderers
            // can auto-fall-back to row[field] without requiring an explicit *Bind.
            if ($normalized['component'] !== null && !isset($normalized['component']['field'])) {
                $normalized['component']['field'] = $normalized['field'];
            }
            $out[] = $normalized;
        }
        return $out;
    }
}
