<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Carousel Component
 *
 * A tile-based horizontal scroll carousel with scroll-snap navigation,
 * prev/next buttons, and dot indicators.
 *
 * Supports both server-rendered tiles (PHP array) and client-side loading
 * from a remote JSON endpoint.
 *
 * Usage — server-side tiles:
 *   echo $m->carousel('relCarousel')
 *       ->tile('My Item', '/showcase/1/my-item', '/assets/thumb.jpg', 'Props')
 *       ->tile('Another', '/showcase/2/another',  '/assets/other.jpg')
 *       ->dots('below');
 *
 * Usage — bulk tiles:
 *   echo $m->carousel('featuredCarousel')
 *       ->tiles($itemsArray)    // [{title, href, imageUrl, caption}]
 *       ->tileWidth('200px')
 *       ->dots('above');
 *
 * Usage — remote datasource (client-side):
 *   echo $m->carousel('ajaxCarousel')
 *       ->remoteUrl('/api/carousel')
 *       ->perPage(10)
 *       ->dots('below');
 *
 * Dot placement options:
 *   ->dots('below')  — dots below the carousel (default)
 *   ->dots('above')  — dots above the carousel
 *   ->dots('none')   — no dot indicators
 */
final class Carousel extends Component
{
    /** @var array<int, array<string, mixed>> */
    private array $tiles = [];

    /** Remote URL for client-side tile loading. Endpoint returns JSON: { tiles: [{title,href,imageUrl,caption}] } */
    private ?string $remoteUrl = null;

    /** Tiles per remote fetch (0 = all at once). */
    private int $perPage = 0;

    /** Dot placement: 'below' | 'above' | 'none' */
    private string $dots = 'below';

    /** CSS width of each tile, e.g. '160px', '12rem'. */
    private string $tileWidth = '160px';

    /** Gap between tiles in pixels. */
    private int $tileGap = 12;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['dots'])) {
            $this->dots((string)$options['dots']);
        }
        if (isset($options['tileWidth'])) {
            $this->tileWidth = (string)$options['tileWidth'];
        }
        if (isset($options['tileGap'])) {
            $this->tileGap = max(0, (int)$options['tileGap']);
        }
        if (isset($options['remoteUrl'])) {
            $this->remoteUrl = (string)$options['remoteUrl'];
        }
        if (isset($options['perPage'])) {
            $this->perPage = max(0, (int)$options['perPage']);
        }
    }

    protected function getComponentType(): string
    {
        return 'carousel';
    }

    // ─── Fluent API ───────────────────────────────────────────────────────────

    /**
     * Add a single tile.
     *
     * @param string      $title    Tile title text.
     * @param string      $href     URL the tile links to.
     * @param string|null $imageUrl Optional thumbnail image URL.
     * @param string|null $caption  Optional secondary caption below the title.
     */
    public function tile(string $title, string $href, ?string $imageUrl = null, ?string $caption = null): self
    {
        $this->tiles[] = [
            'title'    => $title,
            'href'     => $href,
            'imageUrl' => $imageUrl,
            'caption'  => $caption,
        ];
        return $this;
    }

    /**
     * Add multiple tiles at once from an array.
     *
     * Each element may have keys: title, href, imageUrl, caption.
     *
     * @param array<int, array<string, mixed>> $tiles
     */
    public function tiles(array $tiles): self
    {
        foreach ($tiles as $t) {
            $this->tile(
                (string)($t['title']    ?? ''),
                (string)($t['href']     ?? '#'),
                isset($t['imageUrl']) && $t['imageUrl'] !== null ? (string)$t['imageUrl'] : null,
                isset($t['caption'])  && $t['caption']  !== null ? (string)$t['caption']  : null
            );
        }
        return $this;
    }

    /**
     * Set a remote URL for client-side tile loading.
     *
     * The endpoint must return JSON: { "tiles": [ {title, href, imageUrl, caption}, … ] }
     * A `perPage` query param is appended automatically when set via ->perPage().
     */
    public function remoteUrl(string $url): self
    {
        $this->remoteUrl = $url;
        return $this;
    }

    /**
     * Number of tiles per remote fetch page (0 = load all at once).
     */
    public function perPage(int $n): self
    {
        $this->perPage = max(0, $n);
        return $this;
    }

    /**
     * Dot indicator placement.
     *
     * @param string $placement  'below' (default), 'above', or 'none'.
     */
    public function dots(string $placement): self
    {
        $allowed = ['below', 'above', 'none'];
        $this->dots = in_array($placement, $allowed, true) ? $placement : 'below';
        return $this;
    }

    /**
     * CSS width of each tile (default: '160px').
     *
     * Accepts any valid CSS length: '160px', '12rem', '20%', etc.
     */
    public function tileWidth(string $width): self
    {
        $this->tileWidth = $width;
        return $this;
    }

    /**
     * Gap between tiles in pixels (default: 12).
     */
    public function tileGap(int $px): self
    {
        $this->tileGap = max(0, $px);
        return $this;
    }

    // ─── Rendering ────────────────────────────────────────────────────────────

    protected function renderHtml(): string
    {
        $config = [
            'remoteUrl' => $this->remoteUrl,
            'perPage'   => $this->perPage,
            'dots'      => $this->dots,
            'tileWidth' => $this->tileWidth,
            'tileGap'   => $this->tileGap,
        ];
        $configJson = htmlspecialchars(
            (string)(json_encode($config) ?: '{}'),
            ENT_QUOTES,
            'UTF-8'
        );

        // Inline CSS vars for tile sizing
        $styleAttr = sprintf(
            ' style="--m-carousel-tile-width:%s;--m-carousel-gap:%dpx"',
            htmlspecialchars($this->tileWidth, ENT_QUOTES, 'UTF-8'),
            $this->tileGap
        );

        // Extra classes / attributes from base class
        $extraClassArr = $this->getExtraClasses();
        $extraClassStr = !empty($extraClassArr) ? ' ' . implode(' ', $extraClassArr) : '';
        $extraAttrs    = $this->renderAdditionalAttributes();

        // Build server-side tile HTML
        $trackContent = '';
        foreach ($this->tiles as $tile) {
            $trackContent .= $this->renderTile($tile);
        }

        // Dots element
        $dotsPlacement = $this->dots;
        $dotsEl = ($dotsPlacement !== 'none')
            ? '<div class="m-carousel-dots' . ($dotsPlacement === 'above' ? ' m-carousel-dots--above' : '') . '" role="tablist" aria-label="Carousel navigation"></div>'
            : '';

        // Middle row: prev | viewport | next
        $navRow =
            '<div class="m-carousel-nav">'
            . '<button class="m-carousel-btn m-carousel-prev" type="button" aria-label="Previous" disabled>'
            . '<i class="fas fa-chevron-left" aria-hidden="true"></i>'
            . '</button>'
            . '<div class="m-carousel-viewport" tabindex="0" role="region" aria-roledescription="carousel">'
            . '<div class="m-carousel-track">'
            . $trackContent
            . '</div>'
            . '</div>'
            . '<button class="m-carousel-btn m-carousel-next" type="button" aria-label="Next">'
            . '<i class="fas fa-chevron-right" aria-hidden="true"></i>'
            . '</button>'
            . '</div>';

        // Assemble outer wrapper
        return sprintf(
            '<div id="%s" class="m-carousel%s" data-carousel-config="%s"%s%s>%s%s%s</div>',
            htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            $extraClassStr,
            $configJson,
            $styleAttr,
            $extraAttrs,
            ($dotsPlacement === 'above') ? $dotsEl : '',
            $navRow,
            ($dotsPlacement === 'below') ? $dotsEl : ''
        );
    }

    /**
     * Render a single tile element.
     *
     * @param array<string, mixed> $tile
     */
    private function renderTile(array $tile): string
    {
        $title    = htmlspecialchars((string)($tile['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $href     = htmlspecialchars((string)($tile['href']  ?? '#'), ENT_QUOTES, 'UTF-8');
        $imageUrl = isset($tile['imageUrl']) && $tile['imageUrl'] !== null
            ? (string)$tile['imageUrl']
            : null;
        $caption  = isset($tile['caption']) && $tile['caption'] !== null
            ? htmlspecialchars((string)$tile['caption'], ENT_QUOTES, 'UTF-8')
            : null;

        if ($imageUrl !== null && $imageUrl !== '') {
            $imgSrc  = htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8');
            $imgHtml = '<div class="m-carousel-tile-img">'
                . '<img src="' . $imgSrc . '" alt="' . $title . '" loading="lazy">'
                . '</div>';
        } else {
            $imgHtml = '<div class="m-carousel-tile-img m-carousel-tile-img--empty">'
                . '<i class="fas fa-image" aria-hidden="true"></i>'
                . '</div>';
        }

        $captionHtml = '<div class="m-carousel-tile-caption">'
            . '<span class="m-carousel-tile-title">' . $title . '</span>'
            . ($caption !== null ? '<span class="m-carousel-tile-sub">' . $caption . '</span>' : '')
            . '</div>';

        return '<div class="m-carousel-tile" role="group" aria-label="' . $title . '">'
            . '<a href="' . $href . '" class="m-carousel-tile-link" tabindex="0">'
            . $imgHtml
            . $captionHtml
            . '</a>'
            . '</div>';
    }
}
