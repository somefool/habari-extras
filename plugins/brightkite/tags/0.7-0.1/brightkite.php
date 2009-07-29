	<!-- Brightkite plugin for habari -->
	<!-- To customize this template, copy it to your currently active theme directory and edit it -->
	<!-- $Id$ -->
	<div class="item">
		<h3 class="subs">Last Seen</h3>
		<ul>
			<?php
			if ( is_array( $bkinfo ) ) {
				
				$updated = date( "M j, Y g:i A T", strtotime( $bkinfo['last_checked_in'] ) );
				$place = $bkinfo['place'];
				$lat  = $place['latitude'];
				$long = $place['longitude'];
				$address = $place['display_location'];
				$placeurl = 'http://brightkite.com/places/' . $place['id'];
				$name = $place['name'];
				
				// Google map
				$mapimg = "http://maps.google.com/staticmap?center=$lat,$long&zoom=12&sensor=false&markers=$lat,$long";
				$mapimg .= "&size=" . $theme->mapsize;
				$mapimg .= "&key=" . $theme->gmapkey;
				
				?>
				<li class="bkname">I was at <a href="<?php echo $placeurl; ?>"><?php echo $name; ?></a></li>
				<li class="bkaddress"><?php echo $address; ?></li>
				<li class="bkmap"><img alt="<?php echo $name; ?>" src="<?php echo $mapimg; ?>" /></li>
				<li class="bkupdate">Last updated: <?php echo $updated; ?></li>
				<?php
			}
			else { //Exceptions
				echo '<li>' . $bkinfo . '</li>';
			}
			?>
		</ul>
	</div>