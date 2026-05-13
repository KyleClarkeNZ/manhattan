<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * CarouselBanner Component
 *
 * A full-width, accessible banner slideshow with rich slide support.
 *
 * Features:
 *   - Slide and fade animation types
 *   - Configurable animation speed
 *   - Auto-play with configurable interval
 *   - Manual navigation (prev/next arrows + dot indicators)
 *   - Touch/swipe gesture support for mobile
 *   - Lazy-loading images (native loading="lazy" + IntersectionObserver preload)
 *   - Pause on hover and pause on focus
 *   - Keyboard navigation (arrow keys)
 *   - ARIA live region for screen readers
 *   - Optional overlay text with title, subtitle, and CTA button
 *   - Looping (wraps from last slide to first) or non-looping
 *   - Configurable slide aspect ratio
 *   - Optional thumbnail navigation strip
 *
 * Usage:
 *   echo $m->carouselBanner('heroBanner')
 *       ->slide('/assets/banner1.jpg', 'Welcome to CallSheet', 'Your daily task tracker', '/signup', 'Get Started')
 *       ->slide('/assets/banner2.jpg', 'Track Your Day')
 *       ->autoPlay(5000)
 *       ->animation('fade')
 *       ->animationSpeed(600)
 *       ->dots('inside')
 *       ->arrows(true)
 *       ->loop(true)
 *       ->lazyLoad(true);
 *
 * Usage — no overlay text, full-bleed images, thumbnails:
 *   echo $m->carouselBanner('photoBanner')
 *       ->slides($slidesArray)
 *       ->thumbs(true)
 *       ->aspectRatio('21/9')
 *       ->animation('slide');
 */
final class CarouselBanner extends Component
{
    /** @var array<int, array<string, mixed>> */
    private array $slides = [];

    /** Animation type: 'slide' | 'fade' */
    private string $animation = 'slide';

    /** Animation duration in ms. */
    private int $animationSpeed = 500;

    /** Auto-play interval in ms. 0 = disabled. */
    private int $autoPlay = 0;

    /** Dot indicator position: 'inside' | 'below' | 'none' */
    private string $dots = 'inside';

    /** Show prev/next arrows. */
    private bool $arrows = true;

    /** Loop from last back to first. */
    private bool $loop = true;

    /** Pause auto-play on hover. */
    private bool $pauseOnHover = true;

    /** Enable lazy loading of images. */
    private bool $lazyLoad = true;

    /** Show thumbnail navigation strip. */
    private bool $thumbs = false;

    /** CSS aspect-ratio for the banner, e.g. '16/9', '21/9', '4/1'. */
    private string $aspectRatio = '16/9';

    /** Maximum banner height CSS value (overrides aspect ratio when set). */
    private ?string $maxHeight = null;

    /** Starting slide index (0-based). */
    private int $startIndex = 0;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id, $options);

        if (isset($options['animation'])) {
            $this->animation((string)$options['animation']);
        }
        if (isset($options['animationSpeed'])) {
            $this->animationSpeed = max(50, (int)$options['animationSpeed']);
        }
        if (isset($options['autoPlay'])) {
            $this->autoPlay = max(0, (int)$options['autoPlay']);
        }
        if (isset($options['dots'])) {
            $this->dots((string)$options['dots']);
        }
        if (isset($options['arrows'])) {
            $this->arrows = (bool)$options['arrows'];
        }
        if (isset($options['loop'])) {
            $this->loop = (bool)$options['loop'];
        }
        if (isset($options['pauseOnHover'])) {
            $this->pauseOnHover = (bool)$options['pauseOnHover'];
        }
        if (isset($options['lazyLoad'])) {
            $this->lazyLoad = (bool)$options['lazyLoad'];
        }
        if (isset($options['thumbs'])) {
            $this->thumbs = (bool)$options['thumbs'];
        }
        if (isset($options['aspectRatio'])) {
            $this->aspectRatio = (string)$options['aspectRatio'];
        }
        if (isset($options['maxHeight'])) {
            $this->maxHeight = (string)$options['maxHeight'];
        }
        if (isset($options['startIndex'])) {
            $this->startIndex = max(0, (int)$options['startIndex']);
        }
    }

    protected function getComponentType(): string
    {
        return 'carousel-banner';
    }

    // ─── Fluent API ───────────────────────────────────────────────────────────

    /**
     * Add a single slide.
     *
     * @param string      $imageUrl   Slide background image URL.
     * @param string|null $title      Optional overlay title text.
     * @param string|null $subtitle   Optional overlay subtitle text.
     * @param string|null $ctaUrl     Optional CTA button URL.
     * @param string|null $ctaLabel   CTA button label (defaults to 'Learn More').
     * @param string|null $altText    Image alt text for accessibility (defaults to $title).
     * @param string      $ctaVariant CTA button style: 'primary' | 'secondary' | 'light' (default: 'primary').
     * @param string|null $thumbUrl   Optional thumbnail URL (falls back to $imageUrl if not set).
     * @param string|null $overlayPosition  Overlay text position: 'bottom-left' | 'bottom-center' | 'center' | 'top-left' (default: 'bottom-left').
     * @param string|null $color      Optional CSS colour for the overlay text (e.g. '#fff'). Defaults to white.
     * @param string|null $caption    Optional small caption line shown below the CTA (e.g. photo credit, source note).
     */
    public function slide(
        string $imageUrl,
        ?string $title = null,
        ?string $subtitle = null,
        ?string $ctaUrl = null,
        ?string $ctaLabel = null,
        ?string $altText = null,
        string $ctaVariant = 'primary',
        ?string $thumbUrl = null,
        string $overlayPosition = 'bottom-left',
        ?string $color = null,
        ?string $caption = null
    ): self {
        $this->slides[] = [
            'imageUrl'        => $imageUrl,
            'title'           => $title,
            'subtitle'        => $subtitle,
            'ctaUrl'          => $ctaUrl,
            'ctaLabel'        => $ctaLabel ?? 'Learn More',
            'altText'         => $altText ?? $title ?? '',
            'ctaVariant'      => in_array($ctaVariant, ['primary', 'secondary', 'light'], true) ? $ctaVariant : 'primary',
            'thumbUrl'        => $thumbUrl,
            'overlayPosition' => in_array($overlayPosition, ['bottom-left', 'bottom-center', 'center', 'top-left'], true) ? $overlayPosition : 'bottom-left',
            'color'           => $color,
            'caption'         => $caption,
        ];
        return $this;
    }

    /**
     * Add multiple slides from an array.
     *
     * Each element may have keys: imageUrl, title, subtitle, ctaUrl, ctaLabel,
     * altText, ctaVariant, thumbUrl, overlayPosition, color, caption.
     *
     * @param array<int, array<string, mixed>> $slides
     */
    public function slides(array $slides): self
    {
        foreach ($slides as $s) {
            $this->slide(
                (string)($s['imageUrl'] ?? ''),
                isset($s['title'])           ? (string)$s['title']           : null,
                isset($s['subtitle'])        ? (string)$s['subtitle']        : null,
                isset($s['ctaUrl'])          ? (string)$s['ctaUrl']          : null,
                isset($s['ctaLabel'])        ? (string)$s['ctaLabel']        : null,
                isset($s['altText'])         ? (string)$s['altText']         : null,
                isset($s['ctaVariant'])      ? (string)$s['ctaVariant']      : 'primary',
                isset($s['thumbUrl'])        ? (string)$s['thumbUrl']        : null,
                isset($s['overlayPosition']) ? (string)$s['overlayPosition'] : 'bottom-left',
                isset($s['color'])           ? (string)$s['color']           : null,
                isset($s['caption'])         ? (string)$s['caption']         : null
            );
        }
        return $this;
    }

    /**
     * Set the transition animation type.
     *
     * @param string $type  'slide' (default) or 'fade'.
     */
    public function animation(string $type): self
    {
        $allowed = ['slide', 'fade'];
        $this->animation = in_array($type, $allowed, true) ? $type : 'slide';
        return $this;
    }

    /**
     * Set the animation duration in milliseconds (default: 500).
     */
    public function animationSpeed(int $ms): self
    {
        $this->animationSpeed = max(50, $ms);
        return $this;
    }

    /**
     * Enable auto-play with the given interval in milliseconds.
     * Pass 0 to disable auto-play (default).
     */
    public function autoPlay(int $ms): self
    {
        $this->autoPlay = max(0, $ms);
        return $this;
    }

    /**
     * Dot indicator position.
     *
     * @param string $position  'inside' (default), 'below', or 'none'.
     */
    public function dots(string $position): self
    {
        $allowed = ['inside', 'below', 'none'];
        $this->dots = in_array($position, $allowed, true) ? $position : 'inside';
        return $this;
    }

    /**
     * Show or hide prev/next arrow buttons (default: true).
     */
    public function arrows(bool $show = true): self
    {
        $this->arrows = $show;
        return $this;
    }

    /**
     * Loop from the last slide back to the first (default: true).
     */
    public function loop(bool $loop = true): self
    {
        $this->loop = $loop;
        return $this;
    }

    /**
     * Pause auto-play when the user hovers over the banner (default: true).
     */
    public function pauseOnHover(bool $pause = true): self
    {
        $this->pauseOnHover = $pause;
        return $this;
    }

    /**
     * Enable lazy loading of slide images (default: true).
     *
     * The first slide is always eagerly loaded. Subsequent slides use
     * native loading="lazy" plus an IntersectionObserver preload.
     */
    public function lazyLoad(bool $lazy = true): self
    {
        $this->lazyLoad = $lazy;
        return $this;
    }

    /**
     * Show a thumbnail navigation strip below the banner (default: false).
     */
    public function thumbs(bool $show = true): self
    {
        $this->thumbs = $show;
        return $this;
    }

    /**
     * Set the CSS aspect-ratio of the banner (default: '16/9').
     * E.g. '21/9' for ultra-wide, '4/1' for a short ribbon, '16/9' for standard.
     */
    public function aspectRatio(string $ratio): self
    {
        $this->aspectRatio = $ratio;
        return $this;
    }

    /**
     * Override the maximum height of the banner (CSS value, e.g. '500px', '60vh').
     * When set, this takes precedence over the aspect ratio.
     */
    public function maxHeight(string $height): self
    {
        $this->maxHeight = $height;
        return $this;
    }

    /**
     * Set the initial active slide index (0-based, default: 0).
     */
    public function startIndex(int $index): self
    {
        $this->startIndex = max(0, $index);
        return $this;
    }

    // ─── Rendering ────────────────────────────────────────────────────────────

    protected function renderHtml(): string
    {
        $config = [
            'animation'     => $this->animation,
            'animationSpeed'=> $this->animationSpeed,
            'autoPlay'      => $this->autoPlay,
            'dots'          => $this->dots,
            'arrows'        => $this->arrows,
            'loop'          => $this->loop,
            'pauseOnHover'  => $this->pauseOnHover,
            'lazyLoad'      => $this->lazyLoad,
            'thumbs'        => $this->thumbs,
            'startIndex'    => min($this->startIndex, max(0, count($this->slides) - 1)),
            'slideCount'    => count($this->slides),
        ];
        $configJson = htmlspecialchars(
            (string)(json_encode($config) ?: '{}'),
            ENT_QUOTES,
            'UTF-8'
        );

        // Inline CSS vars
        $styleAttr = ' style="--m-cb-aspect:' . htmlspecialchars($this->aspectRatio, ENT_QUOTES, 'UTF-8') . ';';
        if ($this->maxHeight !== null) {
            $styleAttr .= '--m-cb-max-height:' . htmlspecialchars($this->maxHeight, ENT_QUOTES, 'UTF-8') . ';';
        }
        $styleAttr .= '--m-cb-anim-speed:' . $this->animationSpeed . 'ms;"';

        // Extra classes
        $extraClassArr  = $this->getExtraClasses();
        $extraClassStr  = !empty($extraClassArr) ? ' ' . implode(' ', $extraClassArr) : '';
        $animClass      = ' m-cb--' . $this->animation;
        $extraAttrs     = $this->renderAdditionalAttributes();

        // Slides
        $slidesHtml = '';
        foreach ($this->slides as $i => $slide) {
            $slidesHtml .= $this->renderSlide($slide, $i);
        }

        // Arrows
        $arrowsHtml = '';
        if ($this->arrows && count($this->slides) > 1) {
            $arrowsHtml =
                '<button class="m-cb-btn m-cb-prev" type="button" aria-label="Previous slide">'
                . '<i class="fas fa-chevron-left" aria-hidden="true"></i>'
                . '</button>'
                . '<button class="m-cb-btn m-cb-next" type="button" aria-label="Next slide">'
                . '<i class="fas fa-chevron-right" aria-hidden="true"></i>'
                . '</button>';
        }

        // Dots (inside banner)
        $insideDotsHtml = '';
        if ($this->dots === 'inside' && count($this->slides) > 1) {
            $insideDotsHtml = '<div class="m-cb-dots m-cb-dots--inside" role="tablist" aria-label="Slide navigation">'
                . $this->renderDots()
                . '</div>';
        }

        // Auto-play progress bar
        $progressHtml = '';
        if ($this->autoPlay > 0) {
            $progressHtml = '<div class="m-cb-progress" aria-hidden="true"><div class="m-cb-progress-bar"></div></div>';
        }

        // Viewport
        $bannerHtml = sprintf(
            '<div class="m-cb-stage" role="region" aria-roledescription="banner slideshow" aria-label="Banner">'
            . '<div class="m-cb-track">%s</div>'
            . '%s%s%s'
            . '</div>',
            $slidesHtml,
            $arrowsHtml,
            $insideDotsHtml,
            $progressHtml
        );

        // Below dots
        $belowDotsHtml = '';
        if ($this->dots === 'below' && count($this->slides) > 1) {
            $belowDotsHtml = '<div class="m-cb-dots m-cb-dots--below" role="tablist" aria-label="Slide navigation">'
                . $this->renderDots()
                . '</div>';
        }

        // Thumbnails strip
        $thumbsHtml = '';
        if ($this->thumbs && count($this->slides) > 1) {
            $thumbsHtml = '<div class="m-cb-thumbs" role="tablist" aria-label="Slide thumbnails">';
            foreach ($this->slides as $i => $slide) {
                $thumbSrc = $slide['thumbUrl'] ?? $slide['imageUrl'];
                $alt      = htmlspecialchars((string)$slide['altText'], ENT_QUOTES, 'UTF-8');
                $active   = ($i === $this->startIndex) ? ' m-active' : '';
                $ariaSelected = ($i === $this->startIndex) ? 'true' : 'false';
                $loading  = ($i === 0 || !$this->lazyLoad) ? '' : ' loading="lazy"';
                $thumbsHtml .= sprintf(
                    '<button class="m-cb-thumb%s" type="button" role="tab" aria-selected="%s" aria-label="Go to slide %d" data-cb-index="%d">'
                    . '<img src="%s" alt="%s"%s>'
                    . '</button>',
                    $active,
                    $ariaSelected,
                    $i + 1,
                    $i,
                    htmlspecialchars($thumbSrc, ENT_QUOTES, 'UTF-8'),
                    $alt,
                    $loading
                );
            }
            $thumbsHtml .= '</div>';
        }

        return sprintf(
            '<div id="%s" class="m-carousel-banner%s%s" data-cb-config="%s"%s%s>%s%s%s</div>',
            htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            $animClass,
            $extraClassStr,
            $configJson,
            $styleAttr,
            $extraAttrs,
            $bannerHtml,
            $belowDotsHtml,
            $thumbsHtml
        );
    }

    /**
     * Render a single slide element.
     *
     * @param array<string, mixed> $slide
     * @param int                  $index
     */
    private function renderSlide(array $slide, int $index): string
    {
        $isFirst   = ($index === $this->startIndex);
        $loading   = ($isFirst || !$this->lazyLoad) ? '' : ' loading="lazy"';
        $activeClass = $isFirst ? ' m-active' : '';

        $altText   = htmlspecialchars((string)($slide['altText'] ?? ''), ENT_QUOTES, 'UTF-8');
        $imageSrc  = htmlspecialchars((string)$slide['imageUrl'], ENT_QUOTES, 'UTF-8');

        // Image (or div with background for CSS performance)
        $imgTag = sprintf(
            '<img class="m-cb-slide-img" src="%s" alt="%s"%s>',
            $imageSrc,
            $altText,
            $loading
        );

        // Overlay text
        $overlayHtml = '';
        $hasOverlay  = !empty($slide['title']) || !empty($slide['subtitle']) || !empty($slide['ctaUrl']) || !empty($slide['caption']);
        if ($hasOverlay) {
            $pos         = htmlspecialchars((string)($slide['overlayPosition'] ?? 'bottom-left'), ENT_QUOTES, 'UTF-8');
            $colorStyle  = '';
            if (!empty($slide['color'])) {
                $colorStyle = ' style="color:' . htmlspecialchars((string)$slide['color'], ENT_QUOTES, 'UTF-8') . '"';
            }
            $titleHtml    = !empty($slide['title'])
                ? '<h2 class="m-cb-slide-title">' . htmlspecialchars((string)$slide['title'], ENT_QUOTES, 'UTF-8') . '</h2>'
                : '';
            $subtitleHtml = !empty($slide['subtitle'])
                ? '<p class="m-cb-slide-subtitle">' . htmlspecialchars((string)$slide['subtitle'], ENT_QUOTES, 'UTF-8') . '</p>'
                : '';
            $ctaHtml = '';
            if (!empty($slide['ctaUrl'])) {
                $variant  = (string)($slide['ctaVariant'] ?? 'primary');
                $ctaLabel = htmlspecialchars((string)($slide['ctaLabel'] ?? 'Learn More'), ENT_QUOTES, 'UTF-8');
                $ctaHref  = htmlspecialchars((string)$slide['ctaUrl'], ENT_QUOTES, 'UTF-8');
                $ctaHtml  = sprintf(
                    '<a class="m-cb-cta m-cb-cta--%s" href="%s">%s</a>',
                    htmlspecialchars($variant, ENT_QUOTES, 'UTF-8'),
                    $ctaHref,
                    $ctaLabel
                );
            }
            $captionHtml = !empty($slide['caption'])
                ? '<p class="m-cb-slide-caption">' . htmlspecialchars((string)$slide['caption'], ENT_QUOTES, 'UTF-8') . '</p>'
                : '';
            $overlayHtml = sprintf(
                '<div class="m-cb-overlay m-cb-overlay--%s"%s>%s%s%s%s</div>',
                $pos,
                $colorStyle,
                $titleHtml,
                $subtitleHtml,
                $ctaHtml,
                $captionHtml
            );
        }

        return sprintf(
            '<div class="m-cb-slide%s" role="group" aria-roledescription="slide" aria-label="Slide %d of %d" data-cb-index="%d">%s%s</div>',
            $activeClass,
            $index + 1,
            count($this->slides),
            $index,
            $imgTag,
            $overlayHtml
        );
    }

    /**
     * Render dot buttons (used for both inside and below placements).
     */
    private function renderDots(): string
    {
        $html = '';
        foreach ($this->slides as $i => $slide) {
            $active      = ($i === $this->startIndex) ? ' m-active' : '';
            $ariaSelected = ($i === $this->startIndex) ? 'true' : 'false';
            $altText     = htmlspecialchars((string)($slide['altText'] ?? ''), ENT_QUOTES, 'UTF-8');
            $label       = $altText !== '' ? $altText : 'Slide ' . ($i + 1);
            $html .= sprintf(
                '<button class="m-cb-dot%s" type="button" role="tab" aria-selected="%s" aria-label="Go to: %s" data-cb-index="%d"></button>',
                $active,
                $ariaSelected,
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
                $i
            );
        }
        return $html;
    }
}
