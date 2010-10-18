<ul class="items">

	<?php 
	
		foreach ( $snapshots as $snapshot ) {
			
			?>
			
				<li class="item clear">
				
					<span class="date pct20 minor"><?php echo $snapshot->date; ?></span>
				
				</li>
			
			<?php
			
		}
	
	?>

</ul>