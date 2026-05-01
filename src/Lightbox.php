<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Lightbox Component
 *
 * Renders a full-screen overlay image viewer. Can be used standalone or
 * triggered by an ImageViewer component. Supports keyboard navigation
 * (Escape to close, ArrowLeft/Right), click-backdrop-to-close, and
 * can be optionally pre-populated with images via PHP.
 *
 * Usage (standalone):
 *   <?= $m->lightbox('myLb')
 *         ->addImage('/img/photo1.jpg', 'Caption one')
 *         ->addImage('/img/photo2.jpg', 'Caption two') ?>
 *
 *   <script>
 *   document.addEventListener('DOMContentLoaded', function() {
 *       var lb = m.lightbox('myLb');
 *       lb.show(0); // open at first image
 *   });
 *   </script>
 *
 * Usage (triggered by ImageViewer):
 *   <?= $m->imageViewer('gallery')->lightbox() ?> // auto-generates a lightbox
 */
class Lightbox extends Component
{
    /** @var array<int, array<string, string>> Pre-populated images */
    private array $images = [];

    protected function getComponentType(): string
    {
        return 'lightbox';
    }

    /**
     * Pre-populate the lightbox with an image.
     * Images can also be supplied dynamically via JS: lb.show(index, imageArray).
     *
     * @param string      $src     Full-size image URL
     * @param string      $caption Optional caption text
     * @param string|null $thumb   Thumbnail URL (defaults to $src; reserved for future use)
     */
    public function addImage(string $src, string $caption = '', ?string $thumb = null): self
    {
        $this->images[] = [
            'src'     => $src,
            'caption' => $caption,
            'thumb'   => $thumb ?? $src,
        ];
        return $this;
    }

    protected function renderHtml(): string
    {
        $classes   = array_merge(['m-lightbox'], $this->getExtraClasses());
        $classAttr = implode(' ', $classes);

        $imagesAttr = '';
        if (!empty($this->images)) {
            // json_encode produces valid JSON; htmlspecialchars escapes for attribute
            $json      = (string) json_encode(array_values($this->images), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $imagesAttr = ' data-m-images="' . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . '"';
        }

        $extraAttrs = $this->renderAdditionalAttributes(['id', 'class', 'data-m-images']);

        $html  = '<div';
        $html .= ' id="'    . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8') . '"';
        $html .= ' class="' . $classAttr . '"';
        $html .= ' role="dialog" aria-modal="true" aria-label="Lightbox image viewer"';
        $html .= ' hidden';
        $html .= $imagesAttr;
        $html .= $extraAttrs;
        $html .= $this->renderEventAttributes();
        $html .= '>';

        // Dark backdrop — clicking it closes the lightbox
        $html .= '<div class="m-lightbox-backdrop" aria-hidden="true"></div>';

        $html .= '<div class="m-lightbox-container">';

        // Close button
        $html .= '<button class="m-lightbox-close" type="button" aria-label="Close lightbox">';
        $html .= '<i class="fas fa-times" aria-hidden="true"></i>';
        $html .= '</button>';

        // Main image stage
        $html .= '<div class="m-lightbox-stage" aria-live="polite">';
        $html .= '<div class="m-lightbox-spinner" aria-hidden="true"></div>';
        $html .= '<img class="m-lightbox-img" src="" alt="" draggable="false">';
        $html .= '</div>';

        // Footer: caption + counter
        $html .= '<div class="m-lightbox-footer">';
        $html .= '<div class="m-lightbox-caption"></div>';
        $html .= '<div class="m-lightbox-counter" aria-live="polite"></div>';
        $html .= '</div>';

        $html .= '</div>'; // .m-lightbox-container

        // Prev / Next navigation (outside container so they're always edge-anchored)
        $html .= '<button class="m-lightbox-nav m-lightbox-nav--prev" type="button" aria-label="Previous image">';
        $html .= '<i class="fas fa-chevron-left" aria-hidden="true"></i>';
        $html .= '</button>';

        $html .= '<button class="m-lightbox-nav m-lightbox-nav--next" type="button" aria-label="Next image">';
        $html .= '<i class="fas fa-chevron-right" aria-hidden="true"></i>';
        $html .= '</button>';

        $html .= '</div>'; // .m-lightbox

        return $html;
    }
}
