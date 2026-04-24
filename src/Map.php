<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Map Component
 *
 * Renders an embedded Google Maps container. GPS coordinates from the Address
 * component (or any other source) can be fed to the JS API to display pins.
 *
 * @example
 * // Static map with a pre-set centre and marker
 * <?= $m->map('venueMap')
 *       ->apiKey($_ENV['GOOGLE_MAPS_KEY'])
 *       ->center(-36.8485, 174.7633)
 *       ->zoom(14)
 *       ->marker(-36.8485, 174.7633, 'Auckland CBD')
 *       ->height('400px') ?>
 *
 * // Empty map — populate from address picker via JS
 * <?= $m->map('locationMap')->apiKey($_ENV['GOOGLE_MAPS_KEY'])->height('300px') ?>
 *
 * // JS API
 * var map = m.map('locationMap');
 * map.setCenter(-36.8485, 174.7633);
 * map.addMarker(-36.8485, 174.7633, 'My Location');
 */
class Map extends Component
{
    /** @var string Google Maps JavaScript API key */
    protected string $apiKey = '';

    /** @var float|null Default centre latitude */
    protected ?float $centerLat = null;

    /** @var float|null Default centre longitude */
    protected ?float $centerLng = null;

    /** @var int Default zoom level */
    protected int $zoom = 14;

    /** @var string Container height (CSS value) */
    protected string $height = '400px';

    /** @var array Pre-set markers [{lat, lng, title}] */
    protected array $markers = [];

    /**
     * Set the Google Maps JavaScript API key.
     */
    public function apiKey(string $key): self
    {
        $this->apiKey = $key;
        return $this;
    }

    /**
     * Set the default map centre coordinates.
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     */
    public function center(float $lat, float $lng): self
    {
        $this->centerLat = $lat;
        $this->centerLng = $lng;
        return $this;
    }

    /**
     * Set the default zoom level (1–21, Google Maps scale).
     */
    public function zoom(int $zoom): self
    {
        $this->zoom = $zoom;
        return $this;
    }

    /**
     * Set the container height (CSS value, e.g. '400px', '50vh').
     */
    public function height(string $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Add a single marker to the initial set of pins.
     *
     * @param float  $lat   Latitude
     * @param float  $lng   Longitude
     * @param string $title Optional info-window title / tooltip
     */
    public function marker(float $lat, float $lng, string $title = ''): self
    {
        $this->markers[] = ['lat' => $lat, 'lng' => $lng, 'title' => $title];
        return $this;
    }

    /**
     * Set the full array of initial markers (replaces any added via ->marker()).
     *
     * Each element must be an associative array with keys: lat, lng, title (optional).
     *
     * @param array $markers
     */
    public function markers(array $markers): self
    {
        $this->markers = $markers;
        return $this;
    }

    protected function getComponentType(): string
    {
        return 'm-map';
    }

    protected function renderHtml(): string
    {
        $idEsc    = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $keyEsc   = htmlspecialchars($this->apiKey, ENT_QUOTES, 'UTF-8');
        $heightEsc = htmlspecialchars($this->height, ENT_QUOTES, 'UTF-8');

        $classes = array_merge(['m-map'], $this->classes);
        $classStr = htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');

        $dataAttrs  = 'data-api-key="' . $keyEsc . '"';
        $dataAttrs .= ' data-zoom="' . (int)$this->zoom . '"';

        if ($this->centerLat !== null && $this->centerLng !== null) {
            $dataAttrs .= ' data-center-lat="' . htmlspecialchars((string)$this->centerLat, ENT_QUOTES, 'UTF-8') . '"';
            $dataAttrs .= ' data-center-lng="' . htmlspecialchars((string)$this->centerLng, ENT_QUOTES, 'UTF-8') . '"';
        }

        if (!empty($this->markers)) {
            $markersJson = htmlspecialchars(json_encode($this->markers), ENT_QUOTES, 'UTF-8');
            $dataAttrs  .= ' data-markers="' . $markersJson . '"';
        }

        // Render additional attributes from ->attr()
        $extraAttrs = '';
        foreach ($this->attributes as $name => $value) {
            $nameEsc  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $valueEsc = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $extraAttrs .= " {$nameEsc}=\"{$valueEsc}\"";
        }

        return '<div id="' . $idEsc . '" class="' . $classStr . '" style="height:' . $heightEsc . '" ' . $dataAttrs . $extraAttrs . '>'
             . '<div class="m-map-loading"><i class="fas fa-map-marked-alt"></i> Loading map…</div>'
             . '</div>';
    }
}
