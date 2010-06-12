<?php $theme->display( 'header'); ?>

<!-- plugin version -->

<div id="map" style="height: 500px; width: 972px;"></div>
<script type="text/javascript">
$(function(){
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
							'<div><b><?php echo $post->title; ?></b></div><div><?php echo $post->content_excerpt; ?></div>' );
				
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
<?php $theme->display( 'footer'); ?>