google.load("maps", "2");

google.setOnLoadCallback(function() {
    if (GBrowserIsCompatible()) {
        $('a[href^="http://maps.google"]').each(function() {
            var qpos = this.href.indexOf('?');
            if (!qpos) return;
            var query = this.href.substring(qpos, this.href.length);

            var ll = query.match(/(\?|&amp;|&)ll=([\d\-\.]+),([\d\-\.]+)/);
            if (!ll || ll.length != 4) return;
            var lat = ll[2];
            var lng = ll[3];

            var z = query.match(/(\?|&amp;|&)z=(\d+)/);
            if (z) {
                z = z[2];
            } else {
                z = 13;
            }

            var t = query.match(/(\?|&amp;|&)t=([mkh])/);
            if (t) {
                t = t[2];
            } else {
                t = 'm';
            }

            canvas = $('<div class="googlemaps-canvas"></div>');
            $(this).replaceWith(canvas);
            map = new google.maps.Map2(canvas.get(0), {size: new GSize(600, 300)});
            map.addControl(new google.maps.MapTypeControl());
            map.addControl(new google.maps.LargeMapControl());
            map.enableScrollWheelZoom();
            map.enableContinuousZoom();
            map.setCenter(new GLatLng(lat, lng), parseInt(z));
        });
	}
});

$(document).unload(function() {
    GUnload();
});
