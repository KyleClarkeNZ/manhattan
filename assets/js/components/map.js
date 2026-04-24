/**
 * Manhattan UI Framework - Map Component
 * Renders an embedded Google Maps instance.
 * GPS coordinates from the Address component or any source can be used to add pins.
 *
 * JS API:
 *   var map = m.map('myMapId');
 *   map.setCenter(lat, lng);
 *   map.setZoom(zoom);
 *   map.addMarker(lat, lng, title);   // returns the Google Maps Marker instance
 *   map.clearMarkers();
 *   map.getMarkers();                 // returns array of {lat, lng, title, marker}
 *   map.fitMarkers();                 // fit map bounds to all current markers
 */

(function(window) {
    'use strict';

    var m = window.m;
    if (!m || !m.utils) {
        console.warn('Manhattan: core not loaded before map module');
        return;
    }

    var utils = m.utils;

    // Tracks whether Google Maps script is loading / loaded
    var gmapsState = 'idle';   // 'idle' | 'loading' | 'ready'
    var gmapsCallbacks = [];

    function onGmapsReady(cb) {
        if (gmapsState === 'ready') {
            cb();
            return;
        }
        gmapsCallbacks.push(cb);
    }

    function loadGoogleMaps(apiKey, cb) {
        onGmapsReady(cb);

        if (gmapsState !== 'idle') {
            return;
        }

        gmapsState = 'loading';

        // Unique callback name to avoid conflicts
        var callbackName = '__manhattanMapsReady';
        window[callbackName] = function() {
            gmapsState = 'ready';
            var list = gmapsCallbacks.slice();
            gmapsCallbacks = [];
            for (var i = 0; i < list.length; i++) {
                list[i]();
            }
        };

        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js'
            + '?key=' + encodeURIComponent(apiKey)
            + '&callback=' + callbackName;
        script.async = true;
        script.defer = true;
        script.onerror = function() {
            gmapsState = 'idle';  // allow retry
            console.error('Manhattan: Failed to load Google Maps API. Check your API key.');
            gmapsCallbacks = [];
        };
        document.head.appendChild(script);
    }

    function initMap(el) {
        var apiKey  = el.getAttribute('data-api-key') || '';
        var zoom    = parseInt(el.getAttribute('data-zoom') || '14', 10);
        var centerLat = parseFloat(el.getAttribute('data-center-lat') || '0');
        var centerLng = parseFloat(el.getAttribute('data-center-lng') || '0');
        var hasCenterAttr = el.hasAttribute('data-center-lat') && el.hasAttribute('data-center-lng');
        var initialMarkersRaw = el.getAttribute('data-markers') || '[]';

        var markers = [];   // array of {lat, lng, title, marker}
        var googleMap = null;

        if (apiKey === '') {
            el.innerHTML = '<div class="m-map-error"><i class="fas fa-exclamation-triangle"></i> No Google Maps API key configured.</div>';
            return buildApi(null);
        }

        function parseInitialMarkers() {
            try {
                return JSON.parse(initialMarkersRaw) || [];
            } catch (e) {
                return [];
            }
        }

        function createMarker(lat, lng, title) {
            var position = { lat: lat, lng: lng };
            var markerOpts = {
                position: position,
                map: googleMap
            };
            if (title) {
                markerOpts.title = title;
            }
            var gm = new window.google.maps.Marker(markerOpts);
            if (title) {
                var infoWindow = new window.google.maps.InfoWindow({ content: '<span>' + title + '</span>' });
                gm.addListener('click', function() {
                    infoWindow.open(googleMap, gm);
                });
            }
            return gm;
        }

        function buildApi(gMap) {
            return {
                element: el,
                googleMap: gMap,

                setCenter: function(lat, lng) {
                    if (googleMap) {
                        googleMap.setCenter({ lat: lat, lng: lng });
                    }
                    return this;
                },

                setZoom: function(z) {
                    if (googleMap) {
                        googleMap.setZoom(z);
                    }
                    return this;
                },

                addMarker: function(lat, lng, title) {
                    if (!googleMap) { return null; }
                    var gm = createMarker(lat, lng, title || '');
                    markers.push({ lat: lat, lng: lng, title: title || '', marker: gm });
                    utils.trigger(el, 'm:map:markeradded', { lat: lat, lng: lng, title: title || '' });
                    return gm;
                },

                clearMarkers: function() {
                    for (var i = 0; i < markers.length; i++) {
                        markers[i].marker.setMap(null);
                    }
                    markers = [];
                    utils.trigger(el, 'm:map:markerscleared', {});
                    return this;
                },

                getMarkers: function() {
                    return markers.slice();
                },

                fitMarkers: function() {
                    if (!googleMap || markers.length === 0) { return this; }
                    var bounds = new window.google.maps.LatLngBounds();
                    for (var i = 0; i < markers.length; i++) {
                        bounds.extend({ lat: markers[i].lat, lng: markers[i].lng });
                    }
                    googleMap.fitBounds(bounds);
                    return this;
                }
            };
        }

        loadGoogleMaps(apiKey, function() {
            var center = hasCenterAttr
                ? { lat: centerLat, lng: centerLng }
                : { lat: -41.2865, lng: 174.7762 }; // Default: Wellington, NZ

            el.innerHTML = '';

            googleMap = new window.google.maps.Map(el, {
                center: center,
                zoom: zoom,
                mapTypeControl: true,
                streetViewControl: false,
                fullscreenControl: true
            });

            // Apply initial markers from data attribute
            var initMarkers = parseInitialMarkers();
            for (var i = 0; i < initMarkers.length; i++) {
                var im = initMarkers[i];
                if (typeof im.lat === 'number' && typeof im.lng === 'number') {
                    var gm = createMarker(im.lat, im.lng, im.title || '');
                    markers.push({ lat: im.lat, lng: im.lng, title: im.title || '', marker: gm });
                }
            }

            // Update the stored api reference
            el._manhattanMap = buildApi(googleMap);
            el._manhattanMap.googleMap = googleMap;

            utils.trigger(el, 'm:map:ready', { map: googleMap });
        });

        // Return a stub API that queues actions until the map is ready
        var stubApi = buildApi(null);
        return stubApi;
    }

    m.map = function(id) {
        var el = utils.getElement(id);
        if (!el) {
            console.warn('Manhattan: Map element not found:', id);
            return null;
        }

        if (el._manhattanMap) {
            return el._manhattanMap;
        }

        var api = initMap(el);
        el._manhattanMap = api;
        return api;
    };

    // Auto-initialize all map components
    document.addEventListener('DOMContentLoaded', function() {
        var maps = document.querySelectorAll('.m-map[id]');
        for (var i = 0; i < maps.length; i++) {
            m.map(maps[i].id);
        }
    });

})(window);
