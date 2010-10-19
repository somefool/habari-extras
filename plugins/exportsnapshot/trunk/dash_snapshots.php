<ul class="items">

	<?php 
	
		foreach ( $snapshots as $snapshot ) {
			
			?>
			
				<li class="item clear">
				
					<span class="date pct20 minor"><?php echo $snapshot->date->friendly; ?></span>
					<span class="size pct55 minor"><?php echo Utils::human_size( $snapshot->size ); ?></span>
					<span class="delete pct10 minor"><a href="#">Delete</a></span>
					<span class="download pct15 minor"><a href="#">Download</a></span>
				
				</li>
			
			<?php
			
		}
	
	?>

</ul>