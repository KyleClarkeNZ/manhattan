<?php
declare(strict_types=1);

namespace Manhattan;

/**
 * AddressProxy — server-side proxy for NZ address autocomplete.
 *
 * Queries two upstream sources in parallel:
 *   1. LINZ WFS layer-123113  — NZ street addresses (authoritative, macroned, with ASCII fallback)
 *   2. OpenStreetMap Nominatim — POIs and named buildings (libraries, schools, etc.)
 *
 * Performance optimisations:
 *   - Per-source file caching (optional, via {@see setCacheDir()}).
 *   - Early-exit: when LINZ returns ≥ 5 results the Nominatim request is aborted
 *     mid-flight, reducing TTFB to just the LINZ round-trip on the common case.
 *   - curl_multi with per-handle reaction rather than waiting for the slower source.
 *
 * Results are deduplicated by geographic proximity (~100 m) and ranked so that
 * prefix matches appear before substring matches.
 *
 * Attribution:
 *   LINZ data    — Toitū Te Whenua Land Information New Zealand, CC BY 4.0.
 *   OSM/Nominatim — © OpenStreetMap contributors, ODbL.
 *
 * Usage:
 * <code>
 *   $proxy = new \Manhattan\AddressProxy($linzApiKey, 'MyApp/1.0 (https://example.com)');
 *   $proxy->setCacheDir('/var/www/cache/address/');
 *   $suggestions = $proxy->suggest($query);  // array of suggestion rows
 * </code>
 */
class AddressProxy
{
    /** @var string */
    private $linzApiKey;

    /** @var string */
    private $userAgent;

    /** @var string|null */
    private $cacheDir = null;

    /** @var int */
    private $cacheTtl = 86400; // 24 hours

    /**
     * @param string $linzApiKey  LINZ API key. Pass an empty string to skip LINZ queries.
     * @param string $userAgent   User-Agent header sent to Nominatim.
     */
    public function __construct(string $linzApiKey, string $userAgent = 'Manhattan/1.0')
    {
        $this->linzApiKey = $linzApiKey;
        $this->userAgent  = $userAgent !== '' ? $userAgent : 'Manhattan/1.0';
    }

    /**
     * Enable file-based per-source caching for upstream API responses.
     *
     * The directory is created automatically if it does not exist.  Cache files
     * are named by MD5 hash and expire after $ttl seconds.
     *
     * @param string $dir Absolute path to the cache directory.
     * @param int    $ttl Cache lifetime in seconds (default: 86400 = 24 hours).
     */
    public function setCacheDir(string $dir, int $ttl = 86400): self
    {
        $this->cacheDir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        $this->cacheTtl = $ttl;
        return $this;
    }

    /**
     * Return NZ address/POI suggestions for the given query string.
     *
     * @param  string $query Raw user input (min 3 chars; shorter inputs return []).
     * @return array<int, array<string, string>>
     */
    public function suggest(string $query): array
    {
        $query = trim($query);
        if ($query === '' || mb_strlen($query) < 3) {
            return [];
        }

        // Strip control chars; escape for CQL ILIKE interpolation.
        $sanitized = (string)preg_replace('/[\x00-\x1F\x7F]/', '', $query);
        $escaped   = str_replace("'", "''", $sanitized);

        // Normalise for full_address_ascii: strip macrons then collapse double-vowels
        // so "Te Aatatu" finds "Te Ātātū" via the ASCII field.
        $macronMap   = ['ā'=>'a','ē'=>'e','ī'=>'i','ō'=>'o','ū'=>'u','Ā'=>'A','Ē'=>'E','Ī'=>'I','Ō'=>'O','Ū'=>'U'];
        $normalized  = strtr($sanitized, $macronMap);
        $normalized  = (string)preg_replace('/([aeiouAEIOU])\1+/u', '$1', $normalized);
        $escapedNorm = str_replace("'", "''", $normalized);

        $baseKey      = md5(mb_strtolower(trim($query)));
        $linzCacheKey = 'linz_' . $baseKey;
        $nomCacheKey  = 'nom_'  . $baseKey;

        // When the LINZ API key is absent, treat LINZ as already resolved (empty).
        $cachedLinz = $this->linzApiKey !== '' ? $this->cacheGet($linzCacheKey) : [];
        $cachedNom  = $this->cacheGet($nomCacheKey);

        if ($cachedLinz !== null && $cachedNom !== null) {
            // Both sources cached — zero API calls.
            return $this->merge($cachedLinz, $cachedNom, $query);
        }

        // ── Build curl handles only for sources that need a live call ───────
        $mh          = curl_multi_init();
        $chLinz      = null;
        $chNominatim = null;

        if ($cachedLinz === null) {
            // Filter to current addresses only, excluding retired/historical entries.
            $cql = "(full_address ILIKE '%" . $escaped . "%'"
                 . " OR full_address_ascii ILIKE '%" . $escapedNorm . "%')"
                 . " AND address_lifecycle = 'Current'";

            // Request only the 5 properties we use plus the geometry column (shape).
            // Omitting other columns cuts the response payload by ~60 %.
            // Note: postcode is not available in layer-123113 — NZ Post does not
            // publish postcode-level data through this feed.
            $propertyName = 'full_address,full_address_number,full_road_name,suburb_locality,town_city,shape';

            $linzUrl = 'https://data.linz.govt.nz/services;key=' . rawurlencode($this->linzApiKey) . '/wfs'
                . '?service=WFS&version=2.0.0&request=GetFeature'
                . '&typeNames=layer-123113&outputFormat=application%2Fjson'
                . '&count=10&srsName=CRS%3A84'
                . '&propertyName=' . rawurlencode($propertyName)
                . '&CQL_FILTER=' . rawurlencode($cql);

            $chLinz = curl_init($linzUrl);
            curl_setopt_array($chLinz, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_ENCODING       => '',
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            ]);
            curl_multi_add_handle($mh, $chLinz);
        }

        if ($cachedNom === null) {
            $nominatimUrl = 'https://nominatim.openstreetmap.org/search'
                . '?q=' . rawurlencode($sanitized)
                . '&format=jsonv2&countrycodes=nz&limit=5&addressdetails=1';

            $chNominatim = curl_init($nominatimUrl);
            curl_setopt_array($chNominatim, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3,  // Nominatim is supplementary — don't wait long.
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_ENCODING       => '',
                CURLOPT_HTTPHEADER     => [
                    'Accept: application/json',
                    'User-Agent: ' . $this->userAgent,
                ],
            ]);
            curl_multi_add_handle($mh, $chNominatim);
        }

        // ── Execute with early-exit optimisation ────────────────────────────
        // curl_multi_info_read() lets us react to each handle the moment it
        // finishes.  When LINZ completes with ≥ 5 results we abort the
        // in-flight Nominatim request immediately to cut TTFB.
        $linzBody   = false;
        $linzStatus = 0;
        $nomBody    = false;
        $nomStatus  = 0;
        $nomAborted = false;
        $running    = null;

        do {
            curl_multi_exec($mh, $running);

            while (($info = curl_multi_info_read($mh)) !== false) {
                if ($chLinz !== null && $info['handle'] === $chLinz) {
                    $linzBody   = curl_multi_getcontent($chLinz);
                    $linzStatus = (int)curl_getinfo($chLinz, CURLINFO_HTTP_CODE);
                    curl_multi_remove_handle($mh, $chLinz);
                    curl_close($chLinz);
                    $chLinz = null;

                    // Early-exit: enough LINZ results — skip Nominatim entirely.
                    if ($chNominatim !== null
                        && count($this->parseLinzResponse($linzBody, $linzStatus)) >= 5
                    ) {
                        curl_multi_remove_handle($mh, $chNominatim);
                        curl_close($chNominatim);
                        $chNominatim = null;
                        $nomAborted  = true;
                        break 2;
                    }
                }

                if ($chNominatim !== null && $info['handle'] === $chNominatim) {
                    $nomBody   = curl_multi_getcontent($chNominatim);
                    $nomStatus = (int)curl_getinfo($chNominatim, CURLINFO_HTTP_CODE);
                    curl_multi_remove_handle($mh, $chNominatim);
                    curl_close($chNominatim);
                    $chNominatim = null;
                }
            }

            if ($running > 0) {
                curl_multi_select($mh, 0.5);
            }
        } while ($running > 0);

        curl_multi_close($mh);

        // ── Parse and cache per source ───────────────────────────────────────
        $linzSuggestions = $cachedLinz ?? $this->parseLinzResponse($linzBody, $linzStatus);
        $nomSuggestions  = $cachedNom  ?? ($nomAborted ? [] : $this->parseNominatimResponse($nomBody, $nomStatus));

        if ($cachedLinz === null && $linzBody !== false) {
            $this->cachePut($linzCacheKey, $linzSuggestions);
        }
        // Only cache Nominatim when we actually waited for a response (not aborted early).
        if ($cachedNom === null && !$nomAborted && $nomBody !== false) {
            $this->cachePut($nomCacheKey, $nomSuggestions);
        }

        return $this->merge($linzSuggestions, $nomSuggestions, $query);
    }

    // ── Cache helpers ──────────────────────────────────────────────────────────

    /**
     * @return array<int, array<string, string>>|null  null = absent or expired.
     */
    private function cacheGet(string $key): ?array
    {
        if ($this->cacheDir === null) {
            return null;
        }

        $path = $this->cacheDir . $key . '.json';

        if (!file_exists($path)) {
            return null;
        }

        if ((time() - (int)filemtime($path)) > $this->cacheTtl) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * @param array<int, array<string, string>> $data
     */
    private function cachePut(string $key, array $data): void
    {
        if ($this->cacheDir === null) {
            return;
        }

        if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0755, true) && !is_dir($this->cacheDir)) {
            return;
        }

        file_put_contents($this->cacheDir . $key . '.json', (string)json_encode($data));
    }

    // ── Parsing helpers ────────────────────────────────────────────────────────

    /**
     * Parse a LINZ WFS GeoJSON response body into normalised suggestion rows.
     *
     * @param  string|false $body
     * @return array<int, array<string, string>>
     */
    private function parseLinzResponse($body, int $httpStatus): array
    {
        $suggestions = [];
        if ($body === false || $httpStatus !== 200) {
            return $suggestions;
        }

        $data = json_decode((string)$body, true);
        if (!is_array($data) || !isset($data['features'])) {
            return $suggestions;
        }

        foreach ($data['features'] as $feature) {
            if (!is_array($feature)) {
                continue;
            }
            $props = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $geom  = is_array($feature['geometry']   ?? null) ? $feature['geometry']   : null;

            // full_address_number preserves unit/suffix (e.g. "5A", "Unit 2/10");
            // full_road_name includes the road type (e.g. "Matipo Road" not just "Matipo").
            $addrNum  = trim((string)($props['full_address_number'] ?? $props['address_number'] ?? ''));
            $roadName = trim((string)($props['full_road_name']      ?? $props['road_name']      ?? ''));
            $line1    = $addrNum !== '' && $roadName !== ''
                ? $addrNum . ' ' . $roadName
                : trim($addrNum . $roadName);

            $lat = '';
            $lng = '';
            if ($geom !== null
                && isset($geom['coordinates'][0], $geom['coordinates'][1])
                && is_numeric($geom['coordinates'][0])
                && is_numeric($geom['coordinates'][1])
            ) {
                $lng = (string)$geom['coordinates'][0]; // CRS:84 = [lng, lat]
                $lat = (string)$geom['coordinates'][1];
            }

            $suggestions[] = [
                'text'   => (string)($props['full_address'] ?? $line1),
                'id'     => (string)($props['address_id']   ?? ''),
                'name'   => '',   // LINZ provides addresses, not named POIs
                'line1'  => $line1,
                'suburb' => (string)($props['suburb_locality'] ?? ''),
                'city'   => (string)($props['town_city']       ?? ''),
                'lat'    => $lat,
                'lng'    => $lng,
            ];
        }

        return $suggestions;
    }

    /**
     * Parse a Nominatim JSON response body into normalised suggestion rows.
     * Nominatim supplements LINZ with POIs: libraries, schools, shops, etc.
     *
     * @param  string|false $body
     * @return array<int, array<string, string>>
     */
    private function parseNominatimResponse($body, int $httpStatus): array
    {
        $suggestions = [];
        if ($body === false || $httpStatus !== 200) {
            return $suggestions;
        }

        $data = json_decode((string)$body, true);
        if (!is_array($data)) {
            return $suggestions;
        }

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            $addr = is_array($item['address'] ?? null) ? $item['address'] : [];

            $poiName = trim((string)(
                $addr['amenity']  ?? $addr['building'] ?? $addr['tourism'] ??
                $addr['leisure'] ?? $addr['shop']     ?? $addr['office']   ?? ''
            ));

            $houseNo  = trim((string)($addr['house_number'] ?? ''));
            $road     = trim((string)($addr['road']         ?? ''));
            $suburb   = trim((string)($addr['suburb']       ?? $addr['quarter'] ?? $addr['neighbourhood'] ?? ''));
            $city     = trim((string)($addr['city']         ?? $addr['town']    ?? $addr['county']        ?? ''));
            $postcode = trim((string)($addr['postcode']     ?? ''));

            $line1 = $houseNo !== '' && $road !== ''
                ? $houseNo . ' ' . $road
                : ($houseNo !== '' ? $houseNo : $road);

            $parts = array_filter([
                $poiName,
                $line1,
                $suburb,
                trim($city . ($postcode !== '' ? ' ' . $postcode : '')),
            ]);
            $text = implode(', ', $parts);

            if ($text === '' || ($poiName === '' && $line1 === '')) {
                continue;
            }

            $suggestions[] = [
                'text'     => $text,
                'id'       => 'osm-' . ($item['osm_id'] ?? ''),
                'name'     => $poiName,  // Building/POI name — surfaced for venue auto-fill
                'line1'    => $line1,
                'suburb'   => $suburb,
                'city'     => $city,
                'postcode' => $postcode,
                'lat'      => (string)($item['lat'] ?? ''),
                'lng'      => (string)($item['lon'] ?? ''),
            ];
        }

        return $suggestions;
    }

    /**
     * Merge LINZ and Nominatim suggestion arrays, deduplicate by proximity (~100 m),
     * rank prefix matches above substring matches, and cap at 10 results.
     *
     * LINZ (street addresses) is processed first so its data wins deduplication;
     * Nominatim POIs that are not near a LINZ result are appended afterwards.
     *
     * @param  array<int, array<string, string>> $linz
     * @param  array<int, array<string, string>> $nominatim
     * @return array<int, array<string, string>>
     */
    private function merge(array $linz, array $nominatim, string $query): array
    {
        $merged = [];
        foreach (array_merge($linz, $nominatim) as $item) {
            $iLat  = (float)$item['lat'];
            $iLng  = (float)$item['lng'];
            $isDup    = false;
            $dupIndex = -1;
            if ($iLat !== 0.0 || $iLng !== 0.0) {
                foreach ($merged as $idx => $existing) {
                    if (abs($iLat - (float)$existing['lat']) < 0.001
                        && abs($iLng - (float)$existing['lng']) < 0.001
                    ) {
                        $isDup    = true;
                        $dupIndex = $idx;
                        break;
                    }
                }
            }
            if (!$isDup) {
                $merged[] = $item;
            } elseif ($dupIndex >= 0 && empty($merged[$dupIndex]['name']) && !empty($item['name'])) {
                // Nominatim POI matched a LINZ address — inherit the POI name so venue
                // auto-fill works when the user searches by address rather than POI name.
                $merged[$dupIndex]['name'] = $item['name'];
            }
        }

        $queryLower = mb_strtolower($query);
        usort($merged, static function (array $a, array $b) use ($queryLower): int {
            $aStarts = mb_strtolower(mb_substr($a['text'], 0, mb_strlen($queryLower))) === $queryLower ? 0 : 1;
            $bStarts = mb_strtolower(mb_substr($b['text'], 0, mb_strlen($queryLower))) === $queryLower ? 0 : 1;
            return $aStarts - $bStarts;
        });

        return array_slice($merged, 0, 10);
    }
}
