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
 * Matching:
 *   - The query is split into words, so "1 queen st auckland" matches
 *     "1 Queen Street, Auckland Central, Auckland" regardless of commas or word order.
 *   - Words match at word boundaries against full_address_ascii, so macron-free input
 *     works and "1 Queen" does not match "21 Queen" or "101 Queen".
 *   - Common street-type abbreviations (St, Rd, Ave, …) match their full form, and a
 *     doubled vowel ("Aatatu") also matches as a macron substitute ("Ātātū").
 *   - LINZ returns an unordered page, so a wider page is fetched and ranked locally.
 *
 * Performance:
 *   - Per-source file caching (optional, via {@see setCacheDir()}). Only successful
 *     upstream responses are cached, so a rate-limit or outage is never cached as
 *     "no results".
 *   - Early-exit: when LINZ returns ≥ 5 results the Nominatim request is aborted
 *     mid-flight, reducing TTFB to just the LINZ round-trip on the common case.
 *   - Nominatim is throttled to one request per second per server, as its usage
 *     policy requires; LINZ-only results are returned while it is throttled.
 *
 * Results from different sources are deduplicated only when they are the same street
 * address; distinct LINZ addresses (units, neighbouring numbers) are never merged.
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
 *
 *   // On form submit, re-resolve the submitted id rather than trusting posted fields:
 *   $address = $proxy->lookup($_POST['address']['nz']['id']);  // row or null
 * </code>
 */
class AddressProxy
{
    /** Maximum suggestions returned to the client. */
    private const MAX_RESULTS = 10;

    /** LINZ page size. LINZ does not rank results, so fetch more than we show and rank locally. */
    private const LINZ_FETCH_COUNT = 50;

    /** Maximum query words turned into filter clauses (bounds the CQL/URL length). */
    private const MAX_TOKENS = 6;

    /** Minimum seconds between Nominatim requests (usage policy: max 1 req/s). */
    private const NOMINATIM_INTERVAL = 1.0;

    private const MACRON_MAP = [
        'ā' => 'a', 'ē' => 'e', 'ī' => 'i', 'ō' => 'o', 'ū' => 'u',
        'Ā' => 'A', 'Ē' => 'E', 'Ī' => 'I', 'Ō' => 'O', 'Ū' => 'U',
    ];

    /** Abbreviation => full form(s) as they appear in LINZ addresses. */
    private const ABBREVIATIONS = [
        'st'   => ['street', 'saint'],
        'rd'   => ['road'],
        'ave'  => ['avenue'],
        'av'   => ['avenue'],
        'dr'   => ['drive'],
        'pl'   => ['place'],
        'cres' => ['crescent'],
        'cr'   => ['crescent'],
        'tce'  => ['terrace'],
        'ln'   => ['lane'],
        'hwy'  => ['highway'],
        'blvd' => ['boulevard'],
        'ct'   => ['court'],
        'crt'  => ['court'],
        'gr'   => ['grove'],
        'gdns' => ['gardens'],
        'pde'  => ['parade'],
        'sq'   => ['square'],
        'cl'   => ['close'],
        'hts'  => ['heights'],
        'esp'  => ['esplanade'],
        'mt'   => ['mount'],
    ];

    /** Words users type that never appear in LINZ full_address. */
    private const IGNORED_WORDS = ['flat', 'unit', 'apartment', 'apt', 'nz'];

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
        $query = trim((string)preg_replace('/[\x00-\x1F\x7F]/', '', $query));
        if ($query === '' || mb_strlen($query) < 3) {
            return [];
        }

        $tokens = $this->tokenize($query);
        if ($tokens === []) {
            return [];
        }

        $baseKey      = md5($this->normalize($query) . ($this->endsWithSeparator($query) ? ' ' : ''));
        $linzCacheKey = 'linz_' . $baseKey;
        $nomCacheKey  = 'nom_'  . $baseKey;

        // When the LINZ API key is absent, treat LINZ as already resolved (empty).
        $cachedLinz = $this->linzApiKey !== '' ? $this->cacheGet($linzCacheKey) : [];
        $cachedNom  = $this->cacheGet($nomCacheKey);

        if ($cachedLinz !== null && $cachedNom !== null) {
            // Both sources cached — zero API calls.
            return $this->merge($cachedLinz, $cachedNom, $tokens);
        }

        // ── Build curl handles only for sources that need a live call ───────
        $mh          = curl_multi_init();
        $chLinz      = null;
        $chNominatim = null;

        if ($cachedLinz === null) {
            $chLinz = $this->curlHandle($this->linzUrl($this->buildLinzFilter($tokens), self::LINZ_FETCH_COUNT), 5);
            curl_multi_add_handle($mh, $chLinz);
        }

        if ($cachedNom === null && $this->acquireNominatimSlot()) {
            $nominatimUrl = 'https://nominatim.openstreetmap.org/search'
                . '?q=' . rawurlencode($query)
                . '&format=jsonv2&countrycodes=nz&limit=5&addressdetails=1';

            // Nominatim is supplementary — don't wait long.
            $chNominatim = $this->curlHandle($nominatimUrl, 2);
            curl_multi_add_handle($mh, $chNominatim);
        }

        // ── Execute with early-exit optimisation ────────────────────────────
        // curl_multi_info_read() lets us react to each handle the moment it
        // finishes.  When LINZ completes with ≥ 5 results we abort the
        // in-flight Nominatim request immediately to cut TTFB.
        $linzParsed = null;
        $nomParsed  = null;
        $running    = 0;

        do {
            $status = curl_multi_exec($mh, $running);

            while (($info = curl_multi_info_read($mh)) !== false) {
                if ($chLinz !== null && $info['handle'] === $chLinz) {
                    $linzParsed = $this->parseLinzResponse(
                        curl_multi_getcontent($chLinz),
                        (int)curl_getinfo($chLinz, CURLINFO_HTTP_CODE)
                    );
                    curl_multi_remove_handle($mh, $chLinz);
                    curl_close($chLinz);
                    $chLinz = null;

                    // Early-exit: enough LINZ results — skip Nominatim entirely.
                    if ($chNominatim !== null && $linzParsed !== null && count($linzParsed) >= 5) {
                        curl_multi_remove_handle($mh, $chNominatim);
                        curl_close($chNominatim);
                        $chNominatim = null;
                        break 2;
                    }
                }

                if ($chNominatim !== null && $info['handle'] === $chNominatim) {
                    $nomParsed = $this->parseNominatimResponse(
                        curl_multi_getcontent($chNominatim),
                        (int)curl_getinfo($chNominatim, CURLINFO_HTTP_CODE)
                    );
                    curl_multi_remove_handle($mh, $chNominatim);
                    curl_close($chNominatim);
                    $chNominatim = null;
                }
            }

            // curl_multi_select() returns -1 on some platforms when there is
            // nothing to wait on; sleep briefly rather than busy-looping.
            if ($running > 0 && curl_multi_select($mh, 0.5) === -1) {
                usleep(10000);
            }
        } while ($running > 0 && $status === CURLM_OK);

        foreach ([$chLinz, $chNominatim] as $ch) {
            if ($ch !== null) {
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
        }
        curl_multi_close($mh);

        // ── Cache per source — successful responses only ─────────────────────
        // A null parse result means the request failed (timeout, 4xx/5xx, bad JSON),
        // was aborted, or was throttled; none of those are "no results".
        if ($cachedLinz === null && $linzParsed !== null) {
            $this->cachePut($linzCacheKey, $linzParsed);
        }
        if ($cachedNom === null && $nomParsed !== null) {
            $this->cachePut($nomCacheKey, $nomParsed);
        }

        return $this->merge(
            $cachedLinz ?? $linzParsed ?? [],
            $cachedNom  ?? $nomParsed  ?? [],
            $tokens
        );
    }

    /**
     * Re-resolve a suggestion by the id returned from {@see suggest()}.
     *
     * Use this on form submission instead of trusting the posted hidden fields,
     * which the client can edit freely.
     *
     * @param  string $id A LINZ address_id (numeric) or an "osm-N123" style OSM id.
     * @return array<string, string>|null  The suggestion row, or null when not found
     *                                     or the upstream request failed.
     */
    public function lookup(string $id): ?array
    {
        $id = trim($id);
        $cacheKey = 'lookup_' . md5($id);

        $cached = $this->cacheGet($cacheKey);
        if ($cached !== null) {
            return $cached === [] ? null : $cached;
        }

        $rows = null;
        if (preg_match('/^[0-9]{1,12}$/', $id) === 1) {
            if ($this->linzApiKey === '') {
                return null;
            }
            [$body, $status] = $this->httpGet($this->linzUrl('address_id = ' . $id, 1), 5);
            $rows = $this->parseLinzResponse($body, $status);
        } elseif (preg_match('/^osm-([NWR][0-9]{1,15})$/', $id, $m) === 1) {
            if (!$this->acquireNominatimSlot()) {
                return null;
            }
            [$body, $status] = $this->httpGet(
                'https://nominatim.openstreetmap.org/lookup?osm_ids=' . $m[1] . '&format=jsonv2&addressdetails=1',
                3
            );
            $rows = $this->parseNominatimResponse($body, $status);
        } else {
            return null;
        }

        if ($rows === null) {
            return null; // Upstream failure — don't cache.
        }

        $row = $rows[0] ?? null;
        $this->cachePut($cacheKey, $row ?? []);
        return $row;
    }

    // ── Query building ─────────────────────────────────────────────────────────

    /**
     * Lower-case, strip macrons and reduce to space-separated words.
     */
    private function normalize(string $text): string
    {
        $text = mb_strtolower(strtr($text, self::MACRON_MAP));
        $text = (string)preg_replace("/[^a-z0-9'\\-]+/", ' ', $text);
        return trim((string)preg_replace('/\s+/', ' ', $text));
    }

    private function endsWithSeparator(string $text): bool
    {
        return preg_match("/[^\\p{L}\\p{N}'\\-]$/u", $text) === 1;
    }

    /**
     * Split a query into match tokens.
     *
     * Each token carries the alternative spellings it may match, whether it is a
     * complete word (anything but a last word the user is still typing) and
     * whether it is a pure number (matched as a whole word, so "1" ≠ "11").
     *
     * @return array<int, array{alts: string[], complete: bool, numeric: bool}>
     */
    private function tokenize(string $query): array
    {
        $words = array_values(array_filter(
            array_map(static function (string $w): string {
                return trim($w, "'-");
            }, explode(' ', $this->normalize($query))),
            static function (string $w): bool {
                return $w !== '' && !in_array($w, self::IGNORED_WORDS, true);
            }
        ));
        $words = array_slice($this->stripPostalParts($words), 0, self::MAX_TOKENS);

        $lastComplete = $this->endsWithSeparator($query);
        $tokens = [];
        $count  = count($words);

        foreach ($words as $i => $word) {
            $complete = $i < $count - 1 || $lastComplete;
            $alts     = [$word];

            if ($complete && isset(self::ABBREVIATIONS[$word])) {
                $alts = array_merge($alts, self::ABBREVIATIONS[$word]);
            }

            // A doubled vowel is a common macron substitute ("Aatatu" for "Ātātū").
            // Offer the collapsed form as an alternative; never replace the original,
            // which would break real double vowels ("Queen", "Moorhouse").
            $collapsed = (string)preg_replace('/([aeiou])\1+/', '$1', $word);
            if ($collapsed !== $word) {
                $alts[] = $collapsed;
            }

            $tokens[] = [
                'alts'     => array_values(array_unique($alts)),
                'complete' => $complete,
                'numeric'  => ctype_digit($word),
            ];
        }

        return $tokens;
    }

    /**
     * Drop postal-only parts that LINZ physical addresses never contain, so pasting a
     * full postal address still matches: a trailing postcode ("… Auckland 1010") and a
     * rural delivery number ("… RD 2 …"). LINZ has neither.
     *
     * @param  string[] $words
     * @return string[]
     */
    private function stripPostalParts(array $words): array
    {
        $out   = [];
        $count = count($words);
        for ($i = 0; $i < $count; $i++) {
            $word = $words[$i];
            if ($i > 1 && $word === 'rd' && isset($words[$i + 1]) && ctype_digit($words[$i + 1])) {
                $i++; // Skip "RD" and its number.
                continue;
            }
            if ($i > 1 && preg_match('/^rd[0-9]+$/', $word) === 1) {
                continue;
            }
            // A trailing postcode (or one still being typed) after the town. Numbers
            // only follow words in highway names ("State Highway 2").
            if ($i === $count - 1 && $i >= 3 && preg_match('/^[0-9]{1,4}$/', $word) === 1
                && !ctype_digit($words[$i - 1])
                && !in_array($words[$i - 1], ['highway', 'hwy', 'sh'], true)
            ) {
                continue;
            }
            $out[] = $word;
        }
        return $out;
    }

    /**
     * Build the LINZ CQL filter: every token must match a word in full_address_ascii.
     *
     * Tokens contain only [a-z0-9'-], so the only character needing escaping is the
     * single quote; ILIKE wildcards (% and _) in user input never reach the filter.
     *
     * @param array<int, array{alts: string[], complete: bool, numeric: bool}> $tokens
     */
    private function buildLinzFilter(array $tokens): string
    {
        $clauses = [];
        foreach ($tokens as $token) {
            $patterns = [];
            foreach ($token['alts'] as $alt) {
                $alt = str_replace("'", "''", $alt);
                if ($token['numeric'] && $token['complete']) {
                    // Whole-number match: preceded by start/space/slash/hyphen,
                    // followed by space/slash/comma/hyphen ("2/10", "10-12").
                    foreach (['', '% ', '%/', '%-'] as $before) {
                        foreach ([' %', '/%', ',%', '-%'] as $after) {
                            $patterns[] = $before . $alt . $after;
                        }
                    }
                } else {
                    // Word-prefix match; numbers can also follow a unit slash ("2/10").
                    $patterns[] = $alt . '%';
                    $patterns[] = '% ' . $alt . '%';
                    if ($token['numeric']) {
                        $patterns[] = '%/' . $alt . '%';
                    }
                }
            }

            $clauses[] = '(' . implode(' OR ', array_map(static function (string $p): string {
                return "full_address_ascii ILIKE '" . $p . "'";
            }, $patterns)) . ')';
        }

        return implode(' AND ', $clauses);
    }

    private function linzUrl(string $filter, int $count): string
    {
        // Filter to current addresses only, excluding retired/historical entries.
        $cql = '(' . $filter . ") AND address_lifecycle = 'Current'";

        // Request only the properties we use plus the geometry column (shape).
        // Omitting other columns cuts the response payload by ~60 %.
        // Note: postcode is not available in layer-123113 — NZ Post does not
        // publish postcode-level data through this feed.
        $propertyName = 'address_id,full_address,full_address_number,full_road_name,suburb_locality,town_city,shape';

        return 'https://data.linz.govt.nz/services;key=' . rawurlencode($this->linzApiKey) . '/wfs'
            . '?service=WFS&version=2.0.0&request=GetFeature'
            . '&typeNames=layer-123113&outputFormat=application%2Fjson'
            . '&count=' . $count . '&srsName=CRS%3A84'
            . '&propertyName=' . rawurlencode($propertyName)
            . '&CQL_FILTER=' . rawurlencode($cql);
    }

    // ── HTTP helpers ───────────────────────────────────────────────────────────

    /**
     * @return resource|\CurlHandle
     */
    private function curlHandle(string $url, int $timeout)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_ENCODING       => '',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'User-Agent: ' . $this->userAgent,
            ],
        ]);
        return $ch;
    }

    /**
     * @return array{0: string|false, 1: int}
     */
    private function httpGet(string $url, int $timeout): array
    {
        $ch     = $this->curlHandle($url, $timeout);
        $body   = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [is_string($body) ? $body : false, $status];
    }

    /**
     * Claim the next Nominatim request slot, enforcing the usage policy's
     * one-request-per-second limit across all PHP processes on this server.
     *
     * @return bool false when a request was made less than a second ago.
     */
    private function acquireNominatimSlot(): bool
    {
        $dir = $this->cacheDir ?? rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        $fp = @fopen($dir . 'manhattan_nominatim.lock', 'c+');
        if ($fp === false) {
            return false;
        }

        $acquired = false;
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            $last = (float)stream_get_contents($fp);
            $now  = microtime(true);
            if ($now - $last >= self::NOMINATIM_INTERVAL) {
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, (string)$now);
                fflush($fp);
                $acquired = true;
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);

        return $acquired;
    }

    // ── Cache helpers ──────────────────────────────────────────────────────────

    /**
     * @return array<mixed>|null  null = absent or expired.
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
     * @param array<mixed> $data
     */
    private function cachePut(string $key, array $data): void
    {
        if ($this->cacheDir === null) {
            return;
        }

        if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0755, true) && !is_dir($this->cacheDir)) {
            return;
        }

        // Write-then-rename so concurrent readers never see a partial file.
        $path = $this->cacheDir . $key . '.json';
        $tmp  = $path . '.' . getmypid() . '.tmp';
        if (file_put_contents($tmp, (string)json_encode($data)) !== false && !rename($tmp, $path)) {
            @unlink($tmp);
        }
    }

    // ── Parsing helpers ────────────────────────────────────────────────────────

    /**
     * Parse a LINZ WFS GeoJSON response body into normalised suggestion rows.
     *
     * @param  string|false $body
     * @return array<int, array<string, string>>|null  null when the request failed.
     */
    private function parseLinzResponse($body, int $httpStatus): ?array
    {
        if ($body === false || $httpStatus !== 200) {
            return null;
        }

        $data = json_decode((string)$body, true);
        if (!is_array($data) || !isset($data['features']) || !is_array($data['features'])) {
            return null;
        }

        $suggestions = [];
        foreach ($data['features'] as $feature) {
            if (!is_array($feature)) {
                continue;
            }
            $props = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $geom  = is_array($feature['geometry']   ?? null) ? $feature['geometry']   : null;

            // full_address_number preserves unit/suffix (e.g. "5A", "2/10");
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
                'text'     => (string)($props['full_address'] ?? $line1),
                'id'       => (string)($props['address_id']   ?? ''),
                'source'   => 'linz',
                'name'     => '',   // LINZ provides addresses, not named POIs
                'line1'    => $line1,
                'suburb'   => (string)($props['suburb_locality'] ?? ''),
                'city'     => (string)($props['town_city']       ?? ''),
                'postcode' => '',   // Not published in layer-123113
                'lat'      => $lat,
                'lng'      => $lng,
            ];
        }

        return $suggestions;
    }

    /**
     * Parse a Nominatim JSON response body into normalised suggestion rows.
     * Nominatim supplements LINZ with POIs: libraries, schools, shops, etc.
     *
     * @param  string|false $body
     * @return array<int, array<string, string>>|null  null when the request failed.
     */
    private function parseNominatimResponse($body, int $httpStatus): ?array
    {
        if ($body === false || $httpStatus !== 200) {
            return null;
        }

        $data = json_decode((string)$body, true);
        if (!is_array($data)) {
            return null;
        }

        $suggestions = [];
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
            $city     = trim((string)($addr['city']         ?? $addr['town']    ?? $addr['village']       ?? ''));
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

            // Keep the OSM type (N/W/R) so the id can be re-resolved via /lookup.
            $osmType = strtoupper(substr((string)($item['osm_type'] ?? ''), 0, 1));
            $osmId   = (string)($item['osm_id'] ?? '');

            $suggestions[] = [
                'text'     => $text,
                'id'       => $osmType !== '' && $osmId !== '' ? 'osm-' . $osmType . $osmId : '',
                'source'   => 'osm',
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

    // ── Merging and ranking ────────────────────────────────────────────────────

    /**
     * Normalise a street line for cross-source comparison: "10 Queen St" and
     * "10 Queen Street" compare equal; "2/10 Queen Street" does not.
     */
    private function streetKey(string $line1): string
    {
        $words = explode(' ', $this->normalize($line1));
        foreach ($words as $i => $word) {
            if (isset(self::ABBREVIATIONS[$word])) {
                $words[$i] = self::ABBREVIATIONS[$word][0];
            }
        }
        return implode(' ', $words);
    }

    /**
     * Merge LINZ and Nominatim suggestions, rank them against the query and cap
     * the result at {@see MAX_RESULTS}.
     *
     * Every LINZ address is kept — units and neighbouring house numbers are distinct
     * addresses even when they share a location. A Nominatim result is dropped only
     * when it describes the same street address as a LINZ result; if it names a POI,
     * that LINZ row inherits the name so venue auto-fill works when the user searches
     * by address rather than POI name.
     *
     * @param  array<int, array<string, string>> $linz
     * @param  array<int, array<string, string>> $nominatim
     * @param  array<int, array{alts: string[], complete: bool, numeric: bool}> $tokens
     * @return array<int, array<string, string>>
     */
    private function merge(array $linz, array $nominatim, array $tokens): array
    {
        $merged   = [];
        $seenIds  = [];
        $byStreet = [];

        foreach ($linz as $item) {
            $id = (string)($item['id'] ?? '');
            if ($id !== '' && isset($seenIds[$id])) {
                continue;
            }
            $seenIds[$id] = true;
            $merged[] = $item;
            $key = $this->streetKey((string)($item['line1'] ?? ''));
            if ($key !== '' && !isset($byStreet[$key])) {
                $byStreet[$key] = count($merged) - 1;
            }
        }

        $seenText = [];
        foreach ($nominatim as $item) {
            $textKey = $this->normalize((string)$item['text']);
            if (isset($seenText[$textKey])) {
                continue;
            }
            $seenText[$textKey] = true;

            $key = $this->streetKey((string)($item['line1'] ?? ''));
            if ($key !== '' && isset($byStreet[$key])) {
                $idx = $byStreet[$key];
                // Same street address but a different town (e.g. two "1 Queen Street"s)
                // is not a duplicate; require the points to be within ~200 m.
                if ($this->isNear($merged[$idx], $item, 0.002)) {
                    if ($merged[$idx]['name'] === '' && $item['name'] !== '') {
                        $merged[$idx]['name'] = $item['name'];
                    }
                    continue;
                }
            }
            $merged[] = $item;
        }

        // Rank; the original index is the final tie-break so the order is stable on PHP 7.4.
        $scored = [];
        foreach ($merged as $i => $item) {
            $scored[] = [$this->score($item, $tokens), strlen((string)$item['text']), $i, $item];
        }
        usort($scored, static function (array $a, array $b): int {
            return [$b[0], $a[1], $a[2]] <=> [$a[0], $b[1], $b[2]];
        });

        return array_slice(array_column($scored, 3), 0, self::MAX_RESULTS);
    }

    /**
     * @param array<string, string> $a
     * @param array<string, string> $b
     */
    private function isNear(array $a, array $b, float $degrees): bool
    {
        if ($a['lat'] === '' || $a['lng'] === '' || $b['lat'] === '' || $b['lng'] === '') {
            return false;
        }
        return abs((float)$a['lat'] - (float)$b['lat']) < $degrees
            && abs((float)$a['lng'] - (float)$b['lng']) < $degrees;
    }

    /**
     * Score how well a suggestion matches the query tokens (higher is better).
     *
     * @param array<string, string> $item
     * @param array<int, array{alts: string[], complete: bool, numeric: bool}> $tokens
     */
    private function score(array $item, array $tokens): int
    {
        $words   = explode(' ', $this->normalize((string)$item['text']));
        $score   = 0;
        $lastPos = -1;
        $inOrder = true;

        foreach ($tokens as $t => $token) {
            $best    = -5; // Unmatched (e.g. a fuzzy Nominatim hit)
            $bestPos = -1;
            foreach ($words as $pos => $word) {
                foreach ($token['alts'] as $alt) {
                    if ($word === $alt) {
                        $points = 3;
                    } elseif (!$token['complete'] && strpos($word, $alt) === 0) {
                        $points = 2;
                    } elseif (!$token['numeric'] && strpos($word, $alt) === 0) {
                        $points = 1;
                    } else {
                        continue;
                    }
                    if ($points > $best) {
                        $best    = $points;
                        $bestPos = $pos;
                    }
                }
            }
            $score += $best;

            // The address starts with what the user typed first (usually the number).
            if ($t === 0 && $bestPos === 0) {
                $score += 4;
            }
            if ($bestPos < $lastPos) {
                $inOrder = false;
            }
            $lastPos = $bestPos;
        }

        if ($inOrder) {
            $score += 2;
        }
        if (($item['source'] ?? '') === 'linz') {
            $score += 1;
        }

        return $score;
    }
}
