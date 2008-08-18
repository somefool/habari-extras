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

            canvas = $('<div class="googlemaps-canvas"></div>');
            $(this).replaceWith(canvas);

            if (layer == 'c') {
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
                map = new google.maps.Map2(canvas.get(0), {size: new GSize(600, 300)});
                map.addControl(new google.maps.MapTypeControl());
                map.addControl(new google.maps.LargeMapControl());
                map.enableScrollWheelZoom();
                map.enableContinuousZoom();
                map.setCenter(new google.maps.LatLng(lat, lng), parseInt(zoom));
            }
        });
	}
});

$(document).unload(function() {
    GUnload();
});
