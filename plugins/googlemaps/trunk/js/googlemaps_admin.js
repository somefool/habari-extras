google.load("maps", "2");

var googleMaps = {
    map: null,
	init: function() {
        if (GBrowserIsCompatible()) {
            googleMaps.map = new google.maps.Map2($('#googlemaps_canvas').get(0), {size: new GSize(600, 300)});
            googleMaps.map.addControl(new google.maps.MapTypeControl());
            googleMaps.map.addControl(new google.maps.LargeMapControl());
            googleMaps.map.enableScrollWheelZoom();
            googleMaps.map.enableContinuousZoom();
            googleMaps.map.setCenter(new GLatLng(42.366662,-71.106262), 11);

            $('#googlemaps_search').click(function () {
                googleMaps.search();
            });
            $('#googlemaps_address').keypress(function (e) {
                if (e.keyCode == 13) { // Enter
                    googleMaps.search();
                    return false;
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
        var geocoder = new GClientGeocoder();
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
        if (maptype_arg == 'm') {
            maptype = 'NORMAL';
        } else if (maptype_arg == 'k') {
            maptype = 'SATELLITE';
        } else if (maptype_arg == 'h') {
            maptype = 'HYBRID';
        } else {
            maptype = 'NORMAL';
        }
        var center = googleMaps.map.getCenter();
        var tag = '<a href="http://maps.google.com/?ie=UTF8&amp;ll=' + center.lat() + ',' + center.lng() + '&amp;z=' + googleMaps.map.getZoom() + '&amp;t=' + maptype_arg + '">Google Maps (Lat=' + center.lat() + ',Lng=' + center.lng() + ')</a>';
        habari.editor.insertSelection(tag);
    }
}

google.setOnLoadCallback(function() {
    googleMaps.init();
});

$(document).unload(function() {
    googleMaps.unload();
});
