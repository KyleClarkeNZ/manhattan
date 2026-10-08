/**
 * Manhattan UI Framework - Map Component
 *
 * Supports two providers:
 *   - 'leaflet' (default) — Leaflet.js + OpenStreetMap tiles. No API key required.
 *   - 'google'            — Google Maps JS API. Requires data-api-key.
 *
 * JS API (identical for both providers):
 *   var map = m.map('myMapId');
 *   map.setCenter(lat, lng);
 *   map.setZoom(zoom);
 *   map.addMarker(lat, lng, title);  // returns the native marker (null if called before m:map:ready)
 *   map.clearMarkers();
 *   map.getMarkers();                // returns array of {lat, lng, title, marker}
 *   map.fitMarkers();                // fit map viewport to all current markers
 *   map.recenter();                  // reset to the initial centre + zoom
 *
 * Calls made before the provider script has loaded are queued and applied
 * once the map is ready, so m.map(id) can be used straight after page load.
 *
 * Events (fired on the container element):
 *   m:map:ready         — { map }          — map fully initialised
 *   m:map:markeradded   — { lat, lng, title }
 *   m:map:markerscleared — {}
 */

(function(window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before map module');
        return;
    }

    var utils = m.utils;

    // Default centre: Wellington, NZ
    var NZ_DEFAULT_LAT = -41.2865;
    var NZ_DEFAULT_LNG =  174.7762;

    // ── Leaflet CDN loader ─────────────────────────────────────────────────
    var leafletState = 'idle';   // 'idle' | 'loading' | 'ready'
    var leafletCbs   = [];

    function loadLeaflet(cb) {
        if (typeof window.L !== 'undefined') { cb(); return; }
        leafletCbs.push(cb);
        if (leafletState !== 'idle') { return; }
        leafletState = 'loading';

        var link  = document.createElement('link');
        link.rel  = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css';
        document.head.appendChild(link);

        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js';
        script.onload = function() {
            leafletState = 'ready';
            var list = leafletCbs.slice(); leafletCbs = [];
            for (var i = 0; i < list.length; i++) { list[i](); }
        };
        script.onerror = function() {
            leafletState = 'idle'; leafletCbs = [];
            console.error('Manhattan: Failed to load Leaflet from CDN.');
        };
        document.head.appendChild(script);
    }

    // ── Google Maps CDN loader ─────────────────────────────────────────────
    var gmapsState = 'idle';
    var gmapsCbs   = [];

    function loadGoogleMaps(apiKey, cb) {
        if (gmapsState === 'ready') { cb(); return; }
        gmapsCbs.push(cb);
        if (gmapsState !== 'idle') { return; }
        gmapsState = 'loading';

        var cbName = '__manhattanMapsReady';
        window[cbName] = function() {
            gmapsState = 'ready';
            var list = gmapsCbs.slice(); gmapsCbs = [];
            for (var i = 0; i < list.length; i++) { list[i](); }
        };
        var script = document.createElement('script');
        script.src   = 'https://maps.googleapis.com/maps/api/js'
                     + '?key=' + encodeURIComponent(apiKey)
                     + '&callback=' + cbName;
        script.async = true;
        script.defer = true;
        script.onerror = function() {
            gmapsState = 'idle'; gmapsCbs = [];
            console.error('Manhattan: Failed to load Google Maps API. Check your API key.');
        };
        document.head.appendChild(script);
    }

    // ── Shared helpers ─────────────────────────────────────────────────────
    var OSM_TILE_URL    = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
    var OSM_ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors';

    // Leaflet's default attribution prefix includes a flag emoji. Replace it
    // with a plain credit so the map carries no political symbol.
    var LEAFLET_PREFIX  = '<a href="https://leafletjs.com" target="_blank" rel="noopener">Leaflet</a>';

    function readConfig(el) {
        var tileUrl = el.getAttribute('data-tile-url') || '';
        return {
            provider      : el.getAttribute('data-provider')        || 'leaflet',
            apiKey        : el.getAttribute('data-api-key')         || '',
            zoom          : parseInt(el.getAttribute('data-zoom') || '14', 10),
            centerLat     : parseFloat(el.getAttribute('data-center-lat') || '0'),
            centerLng     : parseFloat(el.getAttribute('data-center-lng') || '0'),
            hasCenter     : el.hasAttribute('data-center-lat') && el.hasAttribute('data-center-lng'),
            tileUrl       : tileUrl || OSM_TILE_URL,
            // Custom tiles need their own credit; the OSM line only fits OSM tiles.
            attribution   : el.hasAttribute('data-attribution')
                                ? el.getAttribute('data-attribution')
                                : (tileUrl ? '' : OSM_ATTRIBUTION),
            markersRaw    : el.getAttribute('data-markers')         || '[]',
            recenterButton: el.getAttribute('data-recenter-button') === 'true',
        };
    }

    function parseMarkers(raw) {
        try { return JSON.parse(raw) || []; } catch (e) { return []; }
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /**
     * Build the public API around a provider adapter. The same object is
     * returned before and after the provider script loads: calls made before
     * the map is ready are queued and replayed once it is.
     *
     * adapter: { native, addMarker(lat, lng, title), removeMarker(mk),
     *            setCenter(lat, lng), setZoom(z), setView(lat, lng, z),
     *            fitBounds(points) }
     */
    function createApi(el, nativeKey, initialCenter, initialZoom) {
        var adapter = null;
        var queue   = [];
        var markers = [];

        function whenReady(fn) {
            if (adapter) { fn(); } else { queue.push(fn); }
        }

        var api = {
            element: el,

            setCenter: function(lat, lng) {
                whenReady(function() { adapter.setCenter(lat, lng); });
                return this;
            },

            setZoom: function(z) {
                whenReady(function() { adapter.setZoom(z); });
                return this;
            },

            recenter: function() {
                whenReady(function() { adapter.setView(initialCenter.lat, initialCenter.lng, initialZoom); });
                return this;
            },

            addMarker: function(lat, lng, title) {
                var entry = { lat: lat, lng: lng, title: title || '', marker: null };
                markers.push(entry);
                whenReady(function() {
                    // Skip if clearMarkers() ran before the map became ready
                    if (markers.indexOf(entry) === -1) { return; }
                    entry.marker = adapter.addMarker(lat, lng, entry.title);
                    utils.trigger(el, 'm:map:markeradded', { lat: lat, lng: lng, title: entry.title });
                });
                return entry.marker;
            },

            clearMarkers: function() {
                var old = markers;
                markers = [];
                whenReady(function() {
                    for (var i = 0; i < old.length; i++) {
                        if (old[i].marker) { adapter.removeMarker(old[i].marker); }
                    }
                    utils.trigger(el, 'm:map:markerscleared', {});
                });
                return this;
            },

            getMarkers: function() { return markers.slice(); },

            fitMarkers: function() {
                whenReady(function() {
                    if (markers.length === 0) { return; }
                    adapter.fitBounds(markers);
                });
                return this;
            },

            // Called by the provider initialiser once the native map exists
            _attach: function(a) {
                adapter = a;
                api[nativeKey] = a.native;
                var pending = queue; queue = [];
                for (var i = 0; i < pending.length; i++) { pending[i](); }
                utils.trigger(el, 'm:map:ready', { map: a.native });
            }
        };
        api[nativeKey] = null;
        return api;
    }

    function addInitialMarkers(api, raw) {
        var initMs = parseMarkers(raw);
        for (var i = 0; i < initMs.length; i++) {
            var im = initMs[i];
            if (typeof im.lat === 'number' && typeof im.lng === 'number') {
                api.addMarker(im.lat, im.lng, im.title || '');
            }
        }
    }

    // ── Leaflet map initialiser ────────────────────────────────────────────
    function initLeafletMap(el) {
        var cfg           = readConfig(el);
        var initialCenter = cfg.hasCenter ? { lat: cfg.centerLat, lng: cfg.centerLng } : { lat: NZ_DEFAULT_LAT, lng: NZ_DEFAULT_LNG };
        var api           = createApi(el, 'leafletMap', initialCenter, cfg.zoom);

        addInitialMarkers(api, cfg.markersRaw);

        loadLeaflet(function() {
            var L = window.L;

            el.innerHTML = '';
            var lMap = L.map(el, { zoomControl: true }).setView([initialCenter.lat, initialCenter.lng], cfg.zoom);
            lMap.attributionControl.setPrefix(LEAFLET_PREFIX);

            L.tileLayer(cfg.tileUrl, {
                attribution: cfg.attribution,
                maxZoom    : 19,
            }).addTo(lMap);

            if (cfg.recenterButton) {
                var RecentreControl = L.Control.extend({
                    options: { position: 'bottomleft' },
                    onAdd: function() {
                        var container = L.DomUtil.create('div', 'm-map-recenter leaflet-bar');
                        var btn       = L.DomUtil.create('button', 'm-map-recenter-btn', container);
                        btn.type      = 'button';
                        btn.title     = 'Recentre map';
                        btn.setAttribute('aria-label', 'Recentre map');
                        btn.innerHTML = '<i class="fas fa-crosshairs"></i>';
                        L.DomEvent.on(btn, 'click', function(e) {
                            L.DomEvent.stopPropagation(e);
                            api.recenter();
                        });
                        L.DomEvent.disableClickPropagation(container);
                        return container;
                    }
                });
                new RecentreControl().addTo(lMap);
            }

            api._attach({
                native: lMap,
                addMarker: function(lat, lng, title) {
                    var mk = L.marker([lat, lng]).addTo(lMap);
                    if (title) { mk.bindPopup('<strong>' + escapeHtml(title) + '</strong>').openPopup(); }
                    return mk;
                },
                removeMarker: function(mk) { lMap.removeLayer(mk); },
                setCenter   : function(lat, lng) { lMap.setView([lat, lng]); },
                setZoom     : function(z) { lMap.setZoom(z); },
                setView     : function(lat, lng, z) { lMap.setView([lat, lng], z); },
                fitBounds   : function(points) {
                    var latlngs = [];
                    for (var i = 0; i < points.length; i++) { latlngs.push([points[i].lat, points[i].lng]); }
                    lMap.fitBounds(latlngs, { maxZoom: 16 });
                }
            });
        });

        return api;
    }

    // ── Google Maps initialiser ────────────────────────────────────────────
    function initGoogleMap(el) {
        var cfg           = readConfig(el);
        var initialCenter = cfg.hasCenter ? { lat: cfg.centerLat, lng: cfg.centerLng } : { lat: NZ_DEFAULT_LAT, lng: NZ_DEFAULT_LNG };
        var api           = createApi(el, 'googleMap', initialCenter, cfg.zoom);

        if (cfg.apiKey === '') {
            el.innerHTML = '<div class="m-map-error">'
                + '<i class="fas fa-exclamation-triangle"></i>'
                + ' No Google Maps API key configured.'
                + ' Use <code>->provider(\'leaflet\')</code> for a free map.'
                + '</div>';
            return api;
        }

        addInitialMarkers(api, cfg.markersRaw);

        loadGoogleMaps(cfg.apiKey, function() {
            var g = window.google.maps;

            el.innerHTML = '';

            var gMap = new g.Map(el, {
                center           : initialCenter,
                zoom             : cfg.zoom,
                mapTypeControl   : true,
                streetViewControl: false,
                fullscreenControl: true,
            });

            if (cfg.recenterButton) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'm-map-recenter-btn m-map-recenter-btn--google';
                btn.title = 'Recentre map';
                btn.setAttribute('aria-label', 'Recentre map');
                btn.innerHTML = '<i class="fas fa-crosshairs"></i>';
                btn.addEventListener('click', function() { api.recenter(); });
                gMap.controls[g.ControlPosition.BOTTOM_LEFT].push(btn);
            }

            api._attach({
                native: gMap,
                addMarker: function(lat, lng, title) {
                    var gm = new g.Marker({
                        position: { lat: lat, lng: lng },
                        map     : gMap,
                        title   : title || undefined,
                    });
                    if (title) {
                        var iw = new g.InfoWindow({ content: '<span>' + escapeHtml(title) + '</span>' });
                        gm.addListener('click', function() { iw.open(gMap, gm); });
                    }
                    return gm;
                },
                removeMarker: function(gm) { gm.setMap(null); },
                setCenter   : function(lat, lng) { gMap.setCenter({ lat: lat, lng: lng }); },
                setZoom     : function(z) { gMap.setZoom(z); },
                setView     : function(lat, lng, z) { gMap.setCenter({ lat: lat, lng: lng }); gMap.setZoom(z); },
                fitBounds   : function(points) {
                    var bounds = new g.LatLngBounds();
                    for (var i = 0; i < points.length; i++) { bounds.extend({ lat: points[i].lat, lng: points[i].lng }); }
                    gMap.fitBounds(bounds);
                }
            });
        });

        return api;
    }

    // ── Entry point ────────────────────────────────────────────────────────
    function initMap(el) {
        var provider = el.getAttribute('data-provider') || 'leaflet';
        return provider === 'leaflet' ? initLeafletMap(el) : initGoogleMap(el);
    }

    m.map = function(id) {
        var el = utils.getElement(id);
        if (!el) {
            console.warn('Manhattan: Map element not found:', id);
            return null;
        }
        if (el._manhattanMap) { return el._manhattanMap; }
        var api = initMap(el);
        el._manhattanMap = api;
        return api;
    };

    // Auto-initialize all map components
    document.addEventListener('DOMContentLoaded', function() {
        var maps = document.querySelectorAll('.m-map[id]');
        for (var i = 0; i < maps.length; i++) { m.map(maps[i].id); }
    });

})(window);
