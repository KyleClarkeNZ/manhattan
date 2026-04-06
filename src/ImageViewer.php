<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan ImageViewer Component
 *
 * An accessible image/video gallery with two layout modes, thumbnail
 * navigation, auto-advance, and optional lightbox integration.
 *
 * Layouts:
 *   side  (default) – vertical thumbnail strip on the left, main stage on right
 *   below            – main stage above, horizontal thumbnail strip below
 *
 * Supports:
 *   - Images (with custom thumbnail or auto-uses full-size src)
 *   - Direct video files (.mp4, .webm, etc.)
 *   - YouTube share links (https://youtu.be/...) and watch links
 *   - Optional lightbox on main-image click
 *   - Auto-advance with configurable interval
 *   - Keyboard navigation (← →)
 *
 * Example:
 *   <?= $m->imageViewer('gallery')
 *         ->addImage('https://example.com/img1.jpg', null, 'Sunset')
 *         ->addImage('https://example.com/img2.jpg', 'https://example.com/thumb2.jpg')
 *         ->addVideo('https://youtu.be/dQw4w9WgXcQ', 'Great video')
 *         ->lightbox()
 *         ->autoAdvance()
 *         ->interval(5000) ?>
 */
class ImageViewer extends Component
{
    /** @var array<int, array<string, string>> */
    private array $items = [];

    /** @var string 'side' | 'below' */
    private string $layout = 'side';

    private bool $lightboxEnabled = false;

    /** When empty the component auto-generates an ID */
    private string $lightboxId = '';

    private bool $autoAdvance = false;

    private int $interval = 4000;

    private string $thumbWidth  = '80px';
    private string $thumbHeight = '60px';
    private string $stageHeight = '';

    protected function getComponentType(): string
    {
        return 'imageviewer';
    }

    // ── Layout ────────────────────────────────────────────────────────────────

    /**
     * Set the thumbnail strip layout.
     *
     * @param string $layout 'side' (default) or 'below'
     */
    public function layout(string $layout): self
    {
        $this->layout = ($layout === 'below') ? 'below' : 'side';
        return $this;
    }

    // ── Items ─────────────────────────────────────────────────────────────────

    /**
     * Add an image item.
     *
     * @param string      $src     Full-size image URL
     * @param string|null $thumb   Thumbnail URL (defaults to $src when null)
     * @param string      $caption Optional caption
     */
    public function addImage(string $src, ?string $thumb = null, string $caption = ''): self
    {
        $this->items[] = [
            'type'    => 'image',
            'src'     => $src,
            'thumb'   => $thumb ?? $src,
            'caption' => $caption,
        ];
        return $this;
    }

    /**
     * Add a video item — accepts a direct video URL or a YouTube share / watch link.
     *
     * YouTube URLs detected:
     *   https://youtu.be/VIDEO_ID
     *   https://www.youtube.com/watch?v=VIDEO_ID
     *   https://www.youtube.com/embed/VIDEO_ID
     *
     * @param string      $src     Video URL or YouTube link
     * @param string      $caption Optional caption
     * @param string|null $thumb   Thumbnail URL (auto-derived from YouTube when null)
     */
    public function addVideo(string $src, string $caption = '', ?string $thumb = null): self
    {
        $youtubeId = $this->extractYoutubeId($src);

        if ($youtubeId !== null) {
            $embedSrc = 'https://www.youtube.com/embed/' . $youtubeId . '?rel=0';
            $thumbSrc = $thumb ?? ('https://img.youtube.com/vi/' . $youtubeId . '/hqdefault.jpg');

            $this->items[] = [
                'type'    => 'youtube',
                'src'     => $embedSrc,
                'thumb'   => $thumbSrc,
                'caption' => $caption,
            ];
        } else {
            $this->items[] = [
                'type'    => 'video',
                'src'     => $src,
                'thumb'   => $thumb ?? '',
                'caption' => $caption,
            ];
        }

        return $this;
    }

    // ── Options ───────────────────────────────────────────────────────────────

    /**
     * Enable lightbox on main-image click.
     *
     * @param bool   $enabled    Default true
     * @param string $lightboxId Reuse an existing Lightbox component ID.
     *                           Leave empty to auto-generate a sibling lightbox.
     */
    public function lightbox(bool $enabled = true, string $lightboxId = ''): self
    {
        $this->lightboxEnabled = $enabled;
        $this->lightboxId      = $lightboxId;
        return $this;
    }

    /**
     * Enable auto-advance (default: off).
     */
    public function autoAdvance(bool $enabled = true): self
    {
        $this->autoAdvance = $enabled;
        return $this;
    }

    /**
     * Set the auto-advance interval in milliseconds (default: 4000).
     */
    public function interval(int $ms): self
    {
        $this->interval = max(500, $ms);
        return $this;
    }

    /**
     * Set thumbnail width and height (CSS length strings, e.g. '80px', '5rem').
     */
    public function thumbSize(string $width, string $height): self
    {
        $this->thumbWidth  = $width;
        $this->thumbHeight = $height;
        return $this;
    }

    /**
     * Set the stage (main image area) height. Defaults to 380px.
     * Controls both the stage and the side-layout thumbstrip height.
     *
     * @param string $height Any CSS length: '300px', '50vh', '25rem'
     */
    public function height(string $height): self
    {
        $this->stageHeight = $height;
        return $this;
    }

    // ── Rendering ─────────────────────────────────────────────────────────────

    protected function renderHtml(): string
    {
        $classes   = array_merge(
            ['m-imageviewer', 'm-imageviewer--' . $this->layout],
            $this->getExtraClasses()
        );
        $classAttr = implode(' ', $classes);

        $effectiveLbId = $this->lightboxEnabled
            ? ($this->lightboxId !== '' ? $this->lightboxId : $this->id . '-lightbox')
            : '';

        $dataAttrs  = ' data-m-layout="'       . htmlspecialchars($this->layout,             ENT_QUOTES, 'UTF-8') . '"';
        $dataAttrs .= ' data-m-autoadvance="'  . ($this->autoAdvance ? 'true' : 'false')     . '"';
        $dataAttrs .= ' data-m-interval="'     . $this->interval                             . '"';

        if ($effectiveLbId !== '') {
            $dataAttrs .= ' data-m-lightbox-id="' . htmlspecialchars($effectiveLbId, ENT_QUOTES, 'UTF-8') . '"';
        }

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class']);

        $html  = '<div';
        $html .= ' id="'    . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' class="' . $classAttr . '"';
        $html .= $dataAttrs;
        $html .= $extraAttrs;
        $html .= $this->renderEventAttributes();
        $html .= '>';

        // CSS custom properties for thumb dimensions and stage height
        $thumbStyle = '--m-iv-thumb-w:' . $this->thumbWidth . ';--m-iv-thumb-h:' . $this->thumbHeight;
        if ($this->stageHeight !== '') {
            $thumbStyle .= ';--m-iv-height:' . $this->stageHeight;
        }
        $html .= '<div class="m-imageviewer-body" style="' . $thumbStyle . '">';

        $html .= $this->renderThumbStrip();
        $html .= $this->renderStage();

        $html .= '</div>'; // .m-imageviewer-body
        $html .= '</div>'; // .m-imageviewer

        // Auto-generate a sibling lightbox when enabled without an external ID
        if ($this->lightboxEnabled && $this->lightboxId === '') {
            $lb = new Lightbox($effectiveLbId);
            // Pre-load image items into the lightbox for use when JS is unavailable
            foreach ($this->items as $item) {
                if ($item['type'] === 'image') {
                    $lb->addImage($item['src'], $item['caption']);
                }
            }
            $html .= (string) $lb;
        }

        return $html;
    }

    private function renderThumbStrip(): string
    {
        $html  = '<div class="m-imageviewer-thumbstrip" role="listbox" aria-label="Media thumbnails">';

        foreach ($this->items as $i => $item) {
            $isActive  = ($i === 0);
            $typeClass = ($item['type'] !== 'image') ? ' m-imageviewer-thumb--video' : '';
            $html .= '<div class="m-imageviewer-thumb' . ($isActive ? ' m-active' : '') . $typeClass . '"'
                   . ' role="option"'
                   . ' aria-selected="' . ($isActive ? 'true' : 'false') . '"'
                   . ' data-index="'   . $i . '"'
                   . ' tabindex="'     . ($isActive ? '0' : '-1') . '">';

            if ($item['thumb'] !== '') {
                $html .= '<img'
                       . ' src="'     . htmlspecialchars($item['thumb'],   ENT_QUOTES, 'UTF-8') . '"'
                       . ' alt="'     . htmlspecialchars($item['caption'], ENT_QUOTES, 'UTF-8') . '"'
                       . ' loading="lazy"'
                       . ' draggable="false">';
            } else {
                // Direct video with no generated thumb
                $html .= '<span class="m-imageviewer-thumb-placeholder" aria-hidden="true">'
                       . '<i class="fas fa-video"></i>'
                       . '</span>';
            }

            if ($item['type'] !== 'image') {
                $html .= '<span class="m-imageviewer-thumb-play" aria-hidden="true">'
                       . '<i class="fas fa-play"></i>'
                       . '</span>';
            }

            $html .= '</div>';
        }

        $html .= '</div>'; // .m-imageviewer-thumbstrip
        return $html;
    }

    private function renderStage(): string
    {
        $total = count($this->items);
        $html  = '<div class="m-imageviewer-stage">';

        // Prev / Next navigation
        $prevDisabled = ($total <= 1) ? ' disabled' : '';
        $html .= '<button class="m-imageviewer-nav m-imageviewer-nav--prev" type="button" aria-label="Previous"' . $prevDisabled . '>'
               . '<i class="fas fa-chevron-left" aria-hidden="true"></i>'
               . '</button>';
        $html .= '<button class="m-imageviewer-nav m-imageviewer-nav--next" type="button" aria-label="Next"' . $prevDisabled . '>'
               . '<i class="fas fa-chevron-right" aria-hidden="true"></i>'
               . '</button>';

        // Items
        foreach ($this->items as $i => $item) {
            $isActive = ($i === 0);
            $html .= '<div class="m-imageviewer-item' . ($isActive ? ' m-active' : '') . '"'
                   . ' data-index="' . $i . '"'
                   . ' data-type="'  . htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8') . '">';

            if ($item['type'] === 'image') {
                $html .= '<img'
                       . ' src="'     . htmlspecialchars($item['src'],     ENT_QUOTES, 'UTF-8') . '"'
                       . ' alt="'     . htmlspecialchars($item['caption'], ENT_QUOTES, 'UTF-8') . '"'
                       . ' class="m-imageviewer-img"'
                       . ' loading="lazy"'
                       . ' draggable="false">';
            } elseif ($item['type'] === 'youtube') {
                $html .= '<iframe'
                       . ' src="'          . htmlspecialchars($item['src'],     ENT_QUOTES, 'UTF-8') . '"'
                       . ' title="'        . htmlspecialchars($item['caption'], ENT_QUOTES, 'UTF-8') . '"'
                       . ' class="m-imageviewer-iframe"'
                       . ' frameborder="0"'
                       . ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"'
                       . ' allowfullscreen'
                       . ' loading="lazy">'
                       . '</iframe>';
            } elseif ($item['type'] === 'video') {
                $html .= '<video'
                       . ' src="'     . htmlspecialchars($item['src'], ENT_QUOTES, 'UTF-8') . '"'
                       . ' title="'   . htmlspecialchars($item['caption'], ENT_QUOTES, 'UTF-8') . '"'
                       . ' class="m-imageviewer-video"'
                       . ' controls preload="metadata">'
                       . 'Your browser does not support the video element.'
                       . '</video>';
            }

            $html .= '</div>';
        }

        // Caption overlay (updated by JS on navigation)
        $firstCaption = ($total > 0) ? $this->items[0]['caption'] : '';
        $html .= '<div class="m-imageviewer-caption"' . ($firstCaption === '' ? ' hidden' : '') . '>'
               . htmlspecialchars($firstCaption, ENT_QUOTES, 'UTF-8')
               . '</div>';

        // Counter
        $html .= '<div class="m-imageviewer-counter" aria-live="polite">'
               . ($total > 0 ? '1 / ' . $total : '')
               . '</div>';

        $html .= '</div>'; // .m-imageviewer-stage
        return $html;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Extract a YouTube video ID from common URL formats.
     * Returns null when the string is not a YouTube URL.
     */
    private function extractYoutubeId(string $url): ?string
    {
        // https://youtu.be/VIDEO_ID
        if (preg_match('#youtu\.be/([a-zA-Z0-9_\-]{11})#', $url, $matches)) {
            return $matches[1];
        }
        // https://www.youtube.com/watch?v=VIDEO_ID
        if (preg_match('#youtube\.com/watch\?.*v=([a-zA-Z0-9_\-]{11})#', $url, $matches)) {
            return $matches[1];
        }
        // https://www.youtube.com/embed/VIDEO_ID
        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_\-]{11})#', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
