<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * Manhattan Map Component
 *
 * Renders an interactive map container powered by Leaflet/OSM (default, no API key)
 * or Google Maps (requires ->apiKey()).
 *
 * @example
 * // Leaflet map (free, no API key) — recommended
 * <?= $m->map('venueMap')
 *       ->provider('leaflet')
 *       ->center(-36.8485, 174.7633)
 *       ->zoom(15)
 *       ->marker(-36.8485, 174.7633, 'Auckland CBD')
 *       ->height('400px') ?>
 *
 * // Google Maps (requires API key)
 * <?= $m->map('venueMap')
 *       ->provider('google')
 *       ->apiKey($_ENV['GOOGLE_MAPS_KEY'])
 *       ->center(-36.8485, 174.7633)
 *       ->height('400px') ?>
 *
 * // JS API (same for both providers)
 * var map = m.map('venueMap');
 * map.setCenter(-36.8485, 174.7633);
 * map.addMarker(-36.8485, 174.7633, 'My Location');
 * map.fitMarkers();
 */
class Map extends Component
{
    /** @var string Map provider: 'leaflet' (default, free) or 'google' (requires API key) */
    protected string $provider = 'leaflet';

    /** @var string Google Maps JavaScript API key (only used when provider = 'google') */
    protected string $apiKey = '';

    /** @var string Custom tile URL for Leaflet (leave empty for OpenStreetMap default) */
    protected string $tileUrl = '';

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

    /** @var bool Whether to show the recenter / reset-view button on the map */
    protected bool $recenterButton = false;

    /**
     * Set the map provider.
     *
     * @param string $provider 'leaflet' (default, OpenStreetMap, no API key) or 'google'
     */
    public function provider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Set the Google Maps JavaScript API key (only required when provider = 'google').
     */
    public function apiKey(string $key): self
    {
        $this->apiKey = $key;
        return $this;
    }

    /**
     * Override the Leaflet tile URL (leave empty for OpenStreetMap default).
     *
     * Example: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
     */
    public function tileUrl(string $url): self
    {
        $this->tileUrl = $url;
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
     * Show a recenter / reset-view button on the map.
     *
     * When clicked, the button resets the map viewport to the original centre
     * coordinates and zoom level supplied at construction time.
     *
     * @param bool $enabled Default true; pass false to disable explicitly.
     */
    public function recenterButton(bool $enabled = true): self
    {
        $this->recenterButton = $enabled;
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
        $idEsc     = htmlspecialchars($this->id,       ENT_QUOTES, 'UTF-8');
        $keyEsc    = htmlspecialchars($this->apiKey,   ENT_QUOTES, 'UTF-8');
        $heightEsc = htmlspecialchars($this->height,   ENT_QUOTES, 'UTF-8');
        $provEsc   = htmlspecialchars($this->provider, ENT_QUOTES, 'UTF-8');

        $classes  = array_merge(['m-map'], $this->getExtraClasses());
        $classStr = htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8');

        $dataAttrs  = 'data-provider="' . $provEsc . '"';
        $dataAttrs .= ' data-api-key="' . $keyEsc . '"';
        $dataAttrs .= ' data-zoom="' . (int)$this->zoom . '"';

        if ($this->tileUrl !== '') {
            $dataAttrs .= ' data-tile-url="' . htmlspecialchars($this->tileUrl, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($this->centerLat !== null && $this->centerLng !== null) {
            $dataAttrs .= ' data-center-lat="' . htmlspecialchars((string)$this->centerLat, ENT_QUOTES, 'UTF-8') . '"';
            $dataAttrs .= ' data-center-lng="' . htmlspecialchars((string)$this->centerLng, ENT_QUOTES, 'UTF-8') . '"';
        }

        if (!empty($this->markers)) {
            $markersJson = htmlspecialchars(json_encode($this->markers), ENT_QUOTES, 'UTF-8');
            $dataAttrs  .= ' data-markers="' . $markersJson . '"';
        }

        if ($this->recenterButton) {
            $dataAttrs .= ' data-recenter-button="true"';
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
