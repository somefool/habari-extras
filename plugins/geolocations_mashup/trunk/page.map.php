<?php $theme->display( 'header'); ?>

<!-- to match charcoal theme -->
</div></div>

<div id="map" style="height: 500px;"></div>


<!-- to match charcoal theme -->
</div>



<script type="text/javascript">
$(function(){
	// Get the current viewport width and set the map canvas to be the full width
	var width = $(window).width();
	$('#map').width( width );
	
	map = null;
	infowindow = new google.maps.InfoWindow( );
	
	defaults = {
		center: new google.maps.LatLng( 43.05183,-87.913971 ),
		zoom: 10,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	
	map = new google.maps.Map(document.getElementById("map"), defaults);
	
	<?php foreach ( $theme->posts as $post ) {
		if ( $post->info->geolocation_enabled == 1 ) {
			?>
				new_marker( new google.maps.LatLng(<?php echo $post->info->geolocation_coords; ?>),
							'<div><b><?php echo $post->title; ?></b></div><div><?php echo $post->content_excerpt; ?></div><div><a href="<?php echo $post->permalink; ?>">More...</a></div>' );
				
			<?php
		}
	} ?>
});

function new_marker( coords, message ) {
	var marker = new google.maps.Marker(
		{
			position: coords,
			map: map
		});
	infowindow.setSize( new google.maps.Size(400,200) );
	google.maps.event.addListener( marker, 'click', function(){
		infowindow.close();
		infowindow.setContent( message );
		infowindow.open( map, marker );
	});
}
</script>

<!-- to match charcoal theme -->
<div id="page-bottom"><div id="wrapper-bottom"><div id="bottom-primary">

<?php $theme->display( 'footer'); ?>