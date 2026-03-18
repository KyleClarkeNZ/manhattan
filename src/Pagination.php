<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Pagination Component
 *
 * Renders a pagination control that can drive other components in three modes:
 *
 *  - client  (default): All items are already in the DOM inside a `target` container.
 *                        JS hides/shows item slices on page change. No requests needed.
 *
 *  - server:             PHP renders real <a href="…"> links for full-page-reload navigation.
 *                        JS still fires events so hybrid (server-render + JS listeners) works.
 *
 *  - ajax:               JS fires m:pagination:change and, when a `url` and `target` are set,
 *                        auto-fetches the URL and injects the response HTML into the target.
 *                        The response may be plain HTML or JSON { html, total }.
 *
 * Basic usage — client-side list:
 *   <?= $m->list('taskList')->items($items) ?>
 *   <?= $m->pagination('taskPager')->target('taskList')->perPage(10) ?>
 *
 * Server-side with links:
 *   <?= $m->pagination('taskPager')
 *         ->total($total)->perPage(10)->currentPage($page)
 *         ->mode('server')
 *         ->url('/tasks?page={page}&perPage={perPage}') ?>
 *
 * AJAX driven:
 *   <?= $m->pagination('taskPager')
 *         ->total($total)->perPage(10)->currentPage($page)
 *         ->mode('ajax')
 *         ->url('/tasks/page?page={page}&perPage={perPage}')
 *         ->target('taskList') ?>
 *
 * Per-trigger data attributes accepted on trigger elements:
 *   data-m-pagination="pagerId"   Link any element to this pager (JS click calls goTo)
 */
final class Pagination extends Component
{
    private int $total = 0;
    private int $perPage = 10;
    private int $currentPage = 1;
    private ?string $target = null;
    private string $mode = 'client';
    private ?string $url = null;
    private bool $showInfo = false;
    private array $pageSizes = [];
    private string $align = 'center';
    private int $maxButtons = 7;
    private bool $showFirstLast = false;
    private string $size = '';        // '' | 'compact' | 'large'
    private bool $autoLoad = false;

    /**
     * Total number of items across all pages.
     * In client mode, leave as 0 to let JS count items in the target automatically.
     */
    public function total(int $total): self
    {
        $this->total = max(0, $total);
        return $this;
    }

    /** Number of items per page. Default: 10. */
    public function perPage(int $perPage): self
    {
        $this->perPage = max(1, $perPage);
        return $this;
    }

    /**
     * Current active page (1-based). Used for server-side rendering.
     * Default: 1.
     */
    public function currentPage(int $page): self
    {
        $this->currentPage = max(1, $page);
        return $this;
    }

    /**
     * ID of the container element whose children will be paginated (client/ajax modes).
     * In client mode all direct children (or [data-pagination-item] elements) are paged.
     * In ajax mode the target content is replaced with the fetched response.
     */
    public function target(string $id): self
    {
        $this->target = $id;
        return $this;
    }

    /**
     * Set pagination mode.
     *
     * - 'client' (default): JS shows/hides items already in the DOM.
     * - 'server': PHP renders <a> links for full-page navigation.
     * - 'ajax':  JS fetches pages and injects HTML into target.
     */
    public function mode(string $mode): self
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * URL template for page links/fetches. Tokens: {page}, {perPage}.
     * Required for 'server' mode. Optional for 'ajax' mode auto-fetch.
     *
     * Example: '/tasks?page={page}&perPage={perPage}'
     */
    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Show "Showing X–Y of Z items" info text.
     * Default: false.
     */
    public function showInfo(bool $show = true): self
    {
        $this->showInfo = $show;
        return $this;
    }

    /**
     * Show a per-page size selector with the given options.
     * Example: ->showSizeSelector([10, 25, 50, 100])
     *
     * @param int[] $sizes
     */
    public function showSizeSelector(array $sizes = [10, 25, 50]): self
    {
        $this->pageSizes = array_values(array_filter($sizes, 'is_int'));
        return $this;
    }

    /**
     * Horizontal alignment of the controls row.
     * 'center' (default) | 'left' | 'right'
     */
    public function align(string $align): self
    {
        $this->align = $align;
        return $this;
    }

    /**
     * Maximum number of page buttons to display (including first/last numbered buttons
     * and ellipsis slots). Default: 7.
     */
    public function maxButtons(int $max): self
    {
        $this->maxButtons = max(3, $max);
        return $this;
    }

    /**
     * Show « (first) and » (last) jump buttons alongside ‹ › (prev/next).
     * Default: false.
     */
    public function showFirstLast(bool $show = true): self
    {
        $this->showFirstLast = $show;
        return $this;
    }

    /** Compact (smaller) button variant. */
    public function compact(bool $compact = true): self
    {
        $this->size = $compact ? 'compact' : '';
        return $this;
    }

    /** Large button variant. */
    public function large(bool $large = true): self
    {
        $this->size = $large ? 'large' : '';
        return $this;
    }

    /**
     * In ajax mode: automatically fetch page 1 on JS init.
     * Useful when the target container starts empty and content is loaded on demand.
     * Default: false.
     */
    public function autoLoad(bool $load = true): self
    {
        $this->autoLoad = $load;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'pagination';
    }

    protected function renderHtml(): string
    {
        $totalPages  = $this->perPage > 0 ? (int)ceil($this->total / $this->perPage) : 1;
        $totalPages  = max(1, $totalPages);
        $currentPage = max(1, min($this->currentPage, $totalPages));

        // --- CSS classes ---
        $classes = ['m-pagination'];
        if ($this->align === 'left')  $classes[] = 'm-pagination-left';
        if ($this->align === 'right') $classes[] = 'm-pagination-right';
        if ($this->size === 'compact') $classes[] = 'm-pagination-compact';
        if ($this->size === 'large')   $classes[] = 'm-pagination-large';
        foreach ($this->getExtraClasses() as $c) {
            $classes[] = $c;
        }

        $id        = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $classAttr = htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');

        // --- Data attributes for JS ---
        $data  = ' data-mode="'         . htmlspecialchars($this->mode, ENT_QUOTES, 'UTF-8') . '"';
        $data .= ' data-total="'        . $this->total . '"';
        $data .= ' data-per-page="'     . $this->perPage . '"';
        $data .= ' data-current-page="' . $currentPage . '"';
        $data .= ' data-total-pages="'  . $totalPages . '"';
        $data .= ' data-max-buttons="'  . $this->maxButtons . '"';
        $data .= ' data-show-first-last="' . ($this->showFirstLast ? 'true' : 'false') . '"';
        $data .= ' data-auto-load="'    . ($this->autoLoad ? 'true' : 'false') . '"';

        if ($this->target !== null) {
            $data .= ' data-target="' . htmlspecialchars($this->target, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($this->url !== null) {
            $data .= ' data-url="' . htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8') . '"';
        }
        if (!empty($this->pageSizes)) {
            $data .= ' data-page-sizes="' . htmlspecialchars(implode(',', $this->pageSizes), ENT_QUOTES, 'UTF-8') . '"';
        }

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class'])
                    . $this->renderEventAttributes();

        // --- Render ---
        $html  = '<nav id="' . $id . '" class="' . $classAttr . '"' . $data . $extraAttrs;
        $html .= ' aria-label="Pagination">';

        // Info text column
        $html .= '<div class="m-pagination-info"';
        $html .= $this->showInfo ? '' : ' aria-hidden="true" style="visibility:hidden"';
        $html .= '>';
        if ($this->showInfo && $this->total > 0) {
            $from = ($currentPage - 1) * $this->perPage + 1;
            $to   = min($currentPage * $this->perPage, $this->total);
            $html .= 'Showing ' . $from . '–' . $to . ' of ' . $this->total;
        }
        $html .= '</div>';

        // Controls column
        $html .= '<div class="m-pagination-controls" role="list">';
        $html .= $this->renderControls($currentPage, $totalPages);
        $html .= '</div>';

        // Size selector column
        $html .= '<div class="m-pagination-sizes">';
        if (!empty($this->pageSizes)) {
            $sizeId = $this->id . '-size';
            $helper = \Manhattan\HtmlHelper::getInstance();
            $source = array_map(function (int $s): array {
                return ['value' => (string)$s, 'text' => (string)$s];
            }, $this->pageSizes);
            $html .= '<label class="m-pagination-size-label">Per page</label>';
            $html .= (string)$helper->dropdown($sizeId)
                ->dataSource($source)
                ->value((string)$this->perPage)
                ->addClass('m-pagination-size-select');
        }
        $html .= '</div>';

        $html .= '</nav>';

        return $html;
    }

    /**
     * Render the prev/page-buttons/next row for the given state.
     * In server mode, page buttons are <a> tags; otherwise <button> tags.
     */
    private function renderControls(int $currentPage, int $totalPages): string
    {
        $isServer = $this->mode === 'server';
        $html = '';

        $prevPage = max(1, $currentPage - 1);
        $nextPage = min($totalPages, $currentPage + 1);

        // « First
        if ($this->showFirstLast) {
            $html .= $this->renderNavBtn(1, $currentPage === 1, '&laquo;', 'First page', $isServer);
        }

        // ‹ Prev
        $html .= $this->renderNavBtn($prevPage, $currentPage === 1, '&lsaquo;', 'Previous page', $isServer);

        // Numbered page buttons
        $pageList = $this->buildPageList($currentPage, $totalPages, $this->maxButtons);
        foreach ($pageList as $entry) {
            if ($entry === '...') {
                $html .= '<span class="m-pagination-ellipsis" aria-hidden="true">&hellip;</span>';
            } else {
                $isActive = ((int)$entry === $currentPage);
                $html .= $this->renderPageBtn((int)$entry, $isActive, $isServer);
            }
        }

        // › Next
        $html .= $this->renderNavBtn($nextPage, $currentPage === $totalPages, '&rsaquo;', 'Next page', $isServer);

        // » Last
        if ($this->showFirstLast) {
            $html .= $this->renderNavBtn($totalPages, $currentPage === $totalPages, '&raquo;', 'Last page', $isServer);
        }

        return $html;
    }

    /** Render a prev/next/first/last navigation button. */
    private function renderNavBtn(int $page, bool $disabled, string $symbol, string $label, bool $asLink): string
    {
        $disabledClass = $disabled ? ' m-pagination-disabled' : '';

        if ($asLink && !$disabled && $this->url !== null) {
            $href = $this->buildUrl($page);
            return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"'
                . ' class="m-pagination-btn m-pagination-nav' . $disabledClass . '"'
                . ' aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
                . $symbol . '</a>';
        }

        return '<button type="button"'
            . ' class="m-pagination-btn m-pagination-nav' . $disabledClass . '"'
            . ' data-page="' . $page . '"'
            . ($disabled ? ' disabled aria-disabled="true"' : '')
            . ' aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">'
            . $symbol . '</button>';
    }

    /** Render a numbered page button. */
    private function renderPageBtn(int $page, bool $isActive, bool $asLink): string
    {
        $activeClass = $isActive ? ' m-pagination-active' : '';
        $ariaCurrent = $isActive ? ' aria-current="page"' : '';

        if ($asLink && $this->url !== null) {
            $href = $this->buildUrl($page);
            return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"'
                . ' class="m-pagination-btn' . $activeClass . '"'
                . $ariaCurrent . '>'
                . $page . '</a>';
        }

        return '<button type="button"'
            . ' class="m-pagination-btn' . $activeClass . '"'
            . ' data-page="' . $page . '"'
            . $ariaCurrent . '>'
            . $page . '</button>';
    }

    /** Replace {page} and {perPage} tokens in the URL template. */
    private function buildUrl(int $page): string
    {
        if ($this->url === null) {
            return '#';
        }
        return str_replace(
            ['{page}', '{perPage}'],
            [$page,    $this->perPage],
            $this->url
        );
    }

    /**
     * Build the list of pages to display.
     * Returns an array of integers and '...' strings.
     *
     * @return array<int|string>
     */
    private function buildPageList(int $current, int $total, int $max): array
    {
        if ($total <= $max) {
            return range(1, $total);
        }

        // Slots available for "center" page numbers (between first and last, excluding ellipsis)
        $centerCount = max(1, $max - 4);
        $half        = (int)floor($centerCount / 2);

        $start = max(2, $current - $half);
        $end   = $start + $centerCount - 1;

        // Clamp to [2, total-1] range and shift start if needed
        if ($end > $total - 1) {
            $end   = $total - 1;
            $start = max(2, $end - $centerCount + 1);
        }

        /** @var array<int|string> $pages */
        $pages = [1];

        if ($start > 2) {
            $pages[] = '...';
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($end < $total - 1) {
            $pages[] = '...';
        }

        $pages[] = $total;

        return $pages;
    }
}
