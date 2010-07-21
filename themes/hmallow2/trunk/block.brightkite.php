	<!-- Brightkite plugin for habari -->
	<!-- To customize this template, copy it to your currently active theme directory and edit it -->
	<!-- $Id: block.brightkite.php 3152 2010-06-01 19:31:08Z ciscomonkey $ -->
	<div class="item">
		<h3>Last Seen</h3>
		<ul>
			<?php
			if ( is_array( $content->bkinfo ) ) {
				
				$updated = date( "M j, Y g:i A T", strtotime( $content->bkinfo['last_checked_in'] ) );
				$place = $content->bkinfo['place'];
				$lat  = $place['latitude'];
				$long = $place['longitude'];
				$address = $place['display_location'];
				$placeurl = 'http://brightkite.com/places/' . $place['id'];
				$name = $place['name'];
				
				// Google map
				$mapimg = "http://maps.google.com/staticmap?center=$lat,$long&zoom=12&sensor=false&markers=$lat,$long";
				$mapimg .= "&size=" . $content->mapsize;
				$mapimg .= "&key=" . $content->gmapkey;
				
				?>
				<li class="bkname">I was at <a href="<?php echo $placeurl; ?>"><?php echo $name; ?></a></li>
				<li class="bkaddress"><?php echo $address; ?></li>
				<li class="bkmap"><img alt="<?php echo $name; ?>" src="<?php echo $mapimg; ?>" /></li>
				<li class="bkupdate">Last updated: <?php echo $updated; ?></li>
				<?php
			}
			else { //Exceptions
				echo '<small>' . $content->bkinfo . '</small>';
			}
			?>
		</ul>
	</div>