google.load("maps", "2");

google.setOnLoadCallback(function() {
    var googleMaps = {
        map: null,
        pano: null,
        panoClient: null,
        overlayInstance: null,
        marker: null,
    	init: function() {
            $('#googlemaps_streetview_canvas').hide();

            if (GBrowserIsCompatible()) {
                googleMaps.map = new google.maps.Map2($('#googlemaps_canvas').get(0), {size: new google.maps.Size(600, 300)});
                googleMaps.map.addControl(new google.maps.MapTypeControl());
                googleMaps.map.addControl(new google.maps.LargeMapControl());
                googleMaps.map.enableScrollWheelZoom();
                googleMaps.map.enableContinuousZoom();
                googleMaps.map.setCenter(new google.maps.LatLng(42.366662,-71.106262), 11);
                GEvent.addListener(googleMaps.map, 'click', function(overlay, point) {
                    if (!googleMaps.marker) return;
                    googleMaps.marker.setLatLng(point);
                    googleMaps.panoClient.getNearestPanorama(point, googleMaps.showPanoData);
                });

                $('#googlemaps_search').click(function () {
                    googleMaps.search();
                });
                $('#googlemaps_address').keypress(function (e) {
                    if (e.keyCode == 13) { // Enter
                        googleMaps.search();
                        return false;
                    }
                });
                $('#googlemaps_streetview_toggle').click(function () {
                    if (!googleMaps.overlayInstance) {
                        googleMaps.overlayInstance = new GStreetviewOverlay();
                        googleMaps.map.addOverlay(googleMaps.overlayInstance);

                        if (!googleMaps.pano) {
                            options = { latlng: googleMaps.map.getCenter() };
                            googleMaps.pano = new google.maps.StreetviewPanorama($('#googlemaps_streetview_canvas').get(0), options);
                            GEvent.addListener(googleMaps.pano, 'error', googleMaps.handleError);
                            googleMaps.panoClient = new GStreetviewClient();

                            var guyIcon = new google.maps.Icon(G_DEFAULT_ICON);
                            guyIcon.image = "http://maps.google.com/intl/en_us/mapfiles/cb/man_arrow-0.png";
                            guyIcon.transparent = "http://maps.google.com/intl/en_us/mapfiles/cb/man-pick.png";
                            guyIcon.imageMap = [
                                26,13, 30,14, 32,28, 27,28, 28,36, 18,35, 18,27, 16,26,
                                16,20, 16,14, 19,13, 22,8
                            ];
                            guyIcon.iconSize = new google.maps.Size(49, 52);
                            guyIcon.iconAnchor = new google.maps.Point(25, 35);
                            guyIcon.infoWindowAnchor = new google.maps.Point(25, 5);

                            googleMaps.marker = new google.maps.Marker(options.latlng, {icon: guyIcon, draggable: true});
                            googleMaps.map.addOverlay(googleMaps.marker);
                            GEvent.addListener(googleMaps.marker, 'dragend', function() {
                                var latlng = googleMaps.marker.getLatLng();
                                googleMaps.panoClient.getNearestPanorama(latlng, googleMaps.showPanoData);
                            });
                        }
                        $('#googlemaps_streetview_canvas').show();
                    } else {
                        googleMaps.map.removeOverlay(googleMaps.overlayInstance);
                        googleMaps.overlayInstance = null;
                        $('#googlemaps_streetview_canvas').hide();
                    }
                });
                $('#googlemaps_insert').click(function () {
                    googleMaps.insert();
                });
            }
    	},
        unload: function() {
            GUnload();
        },
        search: function() {
            var geocoder = new google.maps.ClientGeocoder();
            geocoder.getLatLng(
                $('#googlemaps_address').val(),
                function (point) {
                    if (!point) {
                        humanMsg.displayMsg('could not understand the location ' + $('#googlemaps_address').val());
                    } else {
                        googleMaps.map.setCenter(point, 13);
                    }
                }
            );
        },
        insert: function() {
            var maptype;
            var maptype_arg = googleMaps.map.getCurrentMapType().getUrlArg();

            var center = googleMaps.map.getCenter();
            var link = 'http://maps.google.com/?ie=UTF8&amp;ll=' + center.lat() + ',' + center.lng() + '&amp;z=' + googleMaps.map.getZoom() + '&amp;t=' + maptype_arg;
            if (googleMaps.pano) {
                var latlng = googleMaps.marker.getLatLng();
                var pov = googleMaps.pano.getPOV();
                link += '&amp;';
                link += 'layer=c&amp;';
                link += 'cbp=1,' + pov.yaw.toFixed(0) + ',,' + pov.zoom + ',' + pov.pitch.toFixed(0) + '&amp;';
                link += 'cbll=' + latlng.lat() + ',' + latlng.lng();
            }
            var tag = '<a href="' + link + '">Google Maps (Lat=' + center.lat() + ',Lng=' + center.lng() + ')</a>';
            habari.editor.insertSelection(tag);
        },
        handleError: function(errorCode)
        {
            if (errorCode == google.maps.FLASH_UNAVAILABLE) {
                humanMsg.displayMsg("Error: Flash doesn't appear to be supported by your browser");
                return;
            }
        },
        showPanoData: function(panoData)
        {
            if (panoData.code != 200) return;
            googleMaps.pano.setLocationAndPOV(panoData.location.latlng);
        }
    }

    googleMaps.init();
});

$(document).unload(function() {
    googleMaps.unload();
});
