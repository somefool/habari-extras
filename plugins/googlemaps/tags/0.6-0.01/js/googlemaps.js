google.load("maps", "2");

google.setOnLoadCallback(function() {
    if (GBrowserIsCompatible()) {
        $('a[href^="http://maps.google"]').each(function() {
            var qpos = this.href.indexOf('?');
            if (!qpos) return;
            var query = this.href.substring(qpos, this.href.length);

            var ll_r = query.match(/(\?|&amp;|&)ll=([\d\-\.]+),([\d\-\.]+)/);
            if (!ll_r || ll_r.length != 4) return;
            var lat = ll_r[2];
            var lng = ll_r[3];

            var zoom = 13;
            var zoom_r = query.match(/(\?|&amp;|&)z=(\d+)/);
            if (zoom_r) {
                zoom = zoom_r[2];
            }

            var type = 'm';
            var type_r = query.match(/(\?|&amp;|&)t=([mkh])/);
            if (type_r) {
                type = type_r[2];
            }

            var layer = '';
            var layer_r = query.match(/(\?|&amp;|&)layer=([c])/);
            if (layer_r) {
                layer = layer_r[2];
            }

            var cbp = '';
            var cbp_r = query.match(/(\?|&amp;|&)cbp=([\d\-\.,]+)/);
            if (cbp_r) {
                cbp = cbp_r[2].split(',');
            }

            var cblat = null;
            var cblng = null;
            var cbll_r = query.match(/(\?|&amp;|&)cbll=([\d\-\.]+),([\d\-\.]+)/);
            if (cbll_r) {
                cblat = cbll_r[2];
                cblng = cbll_r[3];
            }

			var width = 0;
			var height = 0;
			var _size_r = query.match(/(\?|&amp;|&)_size=(\d+)x(\d+)/);
			if (_size_r) {
				width = _size_r[2];
				height = _size_r[3];
			}

			var controls = '';
			var _controls_r = query.match(/(\?|&amp;|&)_controls=(none)/);
            if (_controls_r) {
                controls = _controls_r[2];
            }

			var markers = [];
			var _markers_r = query.match(/(\?|&amp;|&)_markers=([\d\-\.\,\|]+)/);
			if (_markers_r) {
				$.each(_markers_r[2].split('|'), function(k, marker) {
					marker = marker.split(',');
					markers.push(new google.maps.LatLng(marker[0], marker[1]));
				});
			}

            canvas = $('<div class="googlemaps-canvas"></div>');
            $(this).replaceWith(canvas);

            if (layer == 'c') {
				if (width == 0 || height == 0) {
					width = habari_googlemaps.streetview_width;
					height = habari_googlemaps.streetview_height;
				}
				canvas.width(width);
				canvas.height(height);
                var pov = { yaw: cbp[1], pitch: cbp[4], zoom: cbp[3] };
                options = { latlng: new google.maps.LatLng(cblat, cblng), pov: pov };
                pano = new google.maps.StreetviewPanorama(canvas.get(0), options);
                GEvent.addListener(pano, 'error', function(errorCode) {
                    if (errorCode == google.maps.FLASH_UNAVAILABLE) {
                        canvas.html("Error: Flash doesn't appear to be supported by your browser");
                        return;
                    }
                });
            } else {
				if (width == 0 || height == 0) {
					width = habari_googlemaps.map_width;
					height = habari_googlemaps.map_height;
				}
                map = new google.maps.Map2(canvas.get(0), {size: new GSize(width, height)});
                if (controls != 'none') {
	                map.addControl(new google.maps.MapTypeControl());
                    map.addControl(new google.maps.LargeMapControl());
                }
                map.enableScrollWheelZoom();
                map.enableContinuousZoom();
                map.setCenter(new google.maps.LatLng(lat, lng), parseInt(zoom));
				if (type == 'k') {
					map.setMapType(G_SATELLITE_MAP);
				} else if (type == 'h') {
					map.setMapType(G_HYBRID_MAP);
				} else {
					map.setMapType(G_NORMAL_MAP);
				}

				$.each(markers, function(k, marker) {
					map.addOverlay(new google.maps.Marker(marker));
				});
            }
        });
	}
});

$(document).unload(function() {
    GUnload();
});
