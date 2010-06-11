$(function(){
	// setup everything
	map = null; 
	marker = null;
	
	// Parse out existing coordinates for the map
	if ( $('input:text[id=geolocation_coords]').val() ) {
		var c = $('input:text[id=geolocation_coords]').val().split(',');
		defaults.lat = c[0];
		defaults.long = c[1];
	}
	
	// Draw our initial map
	$('#geolocation_map_canvas').googleMap();
	
	// Add listener for our buttons
	$('#geo_search_button').click( function() {
		$('#geolocation_map_canvas').googleMap( $('input:text[name=geo_address]').val() );
	});
	
	// Add listener for manual update
	$('input:text[id=geolocation_coords]').change( function() {
		var llc = $('input:text[id=geolocation_coords]').val().split(',');
		var nc = new google.maps.LatLng( llc[0], llc[1] );
		var needZoom = (map.getZoom() < defaults.jumptoZoom);
		set_marker( nc, needZoom );
	})
	
	// Default text for search box
	create_default_text_events();

	// If we already have coordinates, then we should zoom into them.
	if ( $('input:checkbox[id=geolocation_enabled]').is(':checked') ) {
		map.setZoom( defaults.jumptoZoom );
	}
});

$.fn.googleMap = function(address, options) {
	
	var options = $.extend(defaults, options || {});	
	var center = new google.maps.LatLng(options.lat, options.long);

	map = new google.maps.Map(this.get(0), $.extend(options, { center: center }));
	var geocoder = new google.maps.Geocoder();
		
	geocoder.geocode({ address: address }, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK && results.length) {
			if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
				set_marker( results[0].geometry.location );
			}
		} else {
			set_marker( center, false );
		}
	}); 
};

function update_coordinates() {
	if ( marker ) {
		$('input:text[id=geolocation_coords]').val( marker.getPosition().toUrlValue() );
	}
}

function marker_listeners() {
	if ( marker ) {
		google.maps.event.addListener(marker, 'dragend', function() {
			update_coordinates( marker );
			map.setCenter( marker.getPosition() );
		});
		
		google.maps.event.addListener(marker, 'click', function() {
			map.setCenter( marker.getPosition() );
			if ( map.getZoom() < defaults.jumptoZoom ) {
				map.setZoom( defaults.jumptoZoom );
			}
		});
	}
}

function set_marker( coords, zoomto ) {
	var zoomto = (zoomto == null) ? true : false;
	
	map.setCenter( coords );
	
	if ( zoomto ) { 
		map.setZoom( defaults.jumptoZoom ); 
	}
	
	if ( marker ) {
		marker = null;
		//marker.setMap( null );
	}
	
	marker = new google.maps.Marker({
		position: coords,
		map: map,
		draggable: true
	});
	
	marker_listeners();
	update_coordinates();
}

function create_default_text_events() {
	// Handle the search box
	$(".geoDefaultText").focus( function(srcc) {
		if ($(this).val() == $(this)[0].title) {
			$(this).removeClass("geoDefaultTextActive");
			$(this).val("");
		}
	});
	
	$(".geoDefaultText").blur(function() {
		if ($(this).val() == "") {
			$(this).addClass("geoDefaultTextActive");
			$(this).val($(this)[0].title);
		}
	});
	
	$(".geoDefaultText").blur();
}

