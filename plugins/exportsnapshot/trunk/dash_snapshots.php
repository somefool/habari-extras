<ul class="items">

	<?php 
	
		foreach ( $snapshots as $snapshot ) {
			
			?>
			
				<li class="item clear">
				
					<span class="date pct20 minor"><?php echo $snapshot->date->format('M d'); ?></span>
					<span class="size pct80"><?php echo Utils::human_size( $snapshot->size ); ?></span>
				
				</li>
			
			<?php
			
		}
	
	?>

</ul>