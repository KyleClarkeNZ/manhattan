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
 *   map.addMarker(lat, lng, title);  // returns the native marker instance
 *   map.clearMarkers();
 *   map.getMarkers();                // returns array of {lat, lng, title, marker}
 *   map.fitMarkers();                // fit map viewport to all current markers
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
    function readConfig(el) {
        return {
            provider      : el.getAttribute('data-provider')        || 'google',
            apiKey        : el.getAttribute('data-api-key')         || '',
            zoom          : parseInt(el.getAttribute('data-zoom') || '14', 10),
            centerLat     : parseFloat(el.getAttribute('data-center-lat') || '0'),
            centerLng     : parseFloat(el.getAttribute('data-center-lng') || '0'),
            hasCenter     : el.hasAttribute('data-center-lat') && el.hasAttribute('data-center-lng'),
            tileUrl       : el.getAttribute('data-tile-url')        || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            markersRaw    : el.getAttribute('data-markers')         || '[]',
            recenterButton: el.getAttribute('data-recenter-button') === 'true',
        };
    }

    function parseMarkers(raw) {
        try { return JSON.parse(raw) || []; } catch (e) { return []; }
    }

    // ── Leaflet map initialiser ────────────────────────────────────────────
    function initLeafletMap(el) {
        var cfg           = readConfig(el);
        var lMap          = null;
        var markers       = [];
        var initialCenter = cfg.hasCenter ? [cfg.centerLat, cfg.centerLng] : [NZ_DEFAULT_LAT, NZ_DEFAULT_LNG];
        var initialZoom   = cfg.zoom;

        function buildApi(map) {
            return {
                element   : el,
                leafletMap: map,

                setCenter: function(lat, lng) {
                    if (map) { map.setView([lat, lng]); }
                    return this;
                },

                setZoom: function(z) {
                    if (map) { map.setZoom(z); }
                    return this;
                },

                recenter: function() {
                    if (map) { map.setView(initialCenter, initialZoom); }
                    return this;
                },

                addMarker: function(lat, lng, title) {
                    if (!map) { return null; }
                    var mk = window.L.marker([lat, lng]).addTo(map);
                    if (title) { mk.bindPopup('<strong>' + title + '</strong>').openPopup(); }
                    markers.push({ lat: lat, lng: lng, title: title || '', marker: mk });
                    utils.trigger(el, 'm:map:markeradded', { lat: lat, lng: lng, title: title || '' });
                    return mk;
                },

                clearMarkers: function() {
                    for (var i = 0; i < markers.length; i++) {
                        if (map) { map.removeLayer(markers[i].marker); }
                    }
                    markers = [];
                    utils.trigger(el, 'm:map:markerscleared', {});
                    return this;
                },

                getMarkers: function() { return markers.slice(); },

                fitMarkers: function() {
                    if (!map || markers.length === 0) { return this; }
                    var latlngs = [];
                    for (var i = 0; i < markers.length; i++) {
                        latlngs.push([markers[i].lat, markers[i].lng]);
                    }
                    map.fitBounds(latlngs, { maxZoom: 16 });
                    return this;
                }
            };
        }

        loadLeaflet(function() {
            var L      = window.L;
            var center = initialCenter;

            el.innerHTML = '';
            lMap = L.map(el, { zoomControl: true }).setView(center, cfg.zoom);

            L.tileLayer(cfg.tileUrl, {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors',
                maxZoom    : 19,
            }).addTo(lMap);

            var initMs = parseMarkers(cfg.markersRaw);
            for (var i = 0; i < initMs.length; i++) {
                var im = initMs[i];
                if (typeof im.lat === 'number' && typeof im.lng === 'number') {
                    var mk = L.marker([im.lat, im.lng]).addTo(lMap);
                    if (im.title) { mk.bindPopup('<strong>' + im.title + '</strong>').openPopup(); }
                    markers.push({ lat: im.lat, lng: im.lng, title: im.title || '', marker: mk });
                }
            }

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
                            lMap.setView(initialCenter, initialZoom);
                        });
                        L.DomEvent.disableClickPropagation(container);
                        return container;
                    }
                });
                new RecentreControl().addTo(lMap);
            }

            el._manhattanMap = buildApi(lMap);
            utils.trigger(el, 'm:map:ready', { map: lMap });
        });

        var stub = buildApi(null);
        return stub;
    }

    // ── Google Maps initialiser ────────────────────────────────────────────
    function initGoogleMap(el) {
        var cfg           = readConfig(el);
        var gMap          = null;
        var markers       = [];
        var initialCenter = cfg.hasCenter ? { lat: cfg.centerLat, lng: cfg.centerLng } : { lat: NZ_DEFAULT_LAT, lng: NZ_DEFAULT_LNG };
        var initialZoom   = cfg.zoom;

        if (cfg.apiKey === '') {
            el.innerHTML = '<div class="m-map-error">'
                + '<i class="fas fa-exclamation-triangle"></i>'
                + ' No Google Maps API key configured.'
                + ' Use <code>->provider(\'leaflet\')</code> for a free map.'
                + '</div>';
            return buildApi(null);
        }

        function createGMarker(lat, lng, title) {
            var gm = new window.google.maps.Marker({
                position: { lat: lat, lng: lng },
                map     : gMap,
                title   : title || undefined,
            });
            if (title) {
                var iw = new window.google.maps.InfoWindow({ content: '<span>' + title + '</span>' });
                gm.addListener('click', function() { iw.open(gMap, gm); });
            }
            return gm;
        }

        function buildApi(map) {
            return {
                element  : el,
                googleMap: map,

                setCenter: function(lat, lng) {
                    if (map) { map.setCenter({ lat: lat, lng: lng }); }
                    return this;
                },

                setZoom: function(z) {
                    if (map) { map.setZoom(z); }
                    return this;
                },

                recenter: function() {
                    if (map) { map.setCenter(initialCenter); map.setZoom(initialZoom); }
                    return this;
                },

                addMarker: function(lat, lng, title) {
                    if (!map) { return null; }
                    var gm = createGMarker(lat, lng, title || '');
                    markers.push({ lat: lat, lng: lng, title: title || '', marker: gm });
                    utils.trigger(el, 'm:map:markeradded', { lat: lat, lng: lng, title: title || '' });
                    return gm;
                },

                clearMarkers: function() {
                    for (var i = 0; i < markers.length; i++) { markers[i].marker.setMap(null); }
                    markers = [];
                    utils.trigger(el, 'm:map:markerscleared', {});
                    return this;
                },

                getMarkers: function() { return markers.slice(); },

                fitMarkers: function() {
                    if (!map || markers.length === 0) { return this; }
                    var bounds = new window.google.maps.LatLngBounds();
                    for (var i = 0; i < markers.length; i++) {
                        bounds.extend({ lat: markers[i].lat, lng: markers[i].lng });
                    }
                    map.fitBounds(bounds);
                    return this;
                }
            };
        }

        loadGoogleMaps(cfg.apiKey, function() {
            var center = initialCenter;

            el.innerHTML = '';

            gMap = new window.google.maps.Map(el, {
                center           : center,
                zoom             : cfg.zoom,
                mapTypeControl   : true,
                streetViewControl: false,
                fullscreenControl: true,
            });

            var initMs = parseMarkers(cfg.markersRaw);
            for (var i = 0; i < initMs.length; i++) {
                var im = initMs[i];
                if (typeof im.lat === 'number' && typeof im.lng === 'number') {
                    var gm = createGMarker(im.lat, im.lng, im.title || '');
                    markers.push({ lat: im.lat, lng: im.lng, title: im.title || '', marker: gm });
                }
            }

            if (cfg.recenterButton) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'm-map-recenter-btn m-map-recenter-btn--google';
                btn.title = 'Recentre map';
                btn.setAttribute('aria-label', 'Recentre map');
                btn.innerHTML = '<i class="fas fa-crosshairs"></i>';
                btn.addEventListener('click', function() {
                    gMap.setCenter(initialCenter);
                    gMap.setZoom(initialZoom);
                });
                gMap.controls[window.google.maps.ControlPosition.BOTTOM_LEFT].push(btn);
            }

            el._manhattanMap = buildApi(gMap);
            utils.trigger(el, 'm:map:ready', { map: gMap });
        });

        return buildApi(null);
    }

    // ── Entry point ────────────────────────────────────────────────────────
    function initMap(el) {
        var provider = el.getAttribute('data-provider') || 'google';
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
