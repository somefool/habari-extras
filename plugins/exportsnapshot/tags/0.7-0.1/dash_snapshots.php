<ul class="items">

	<?php 
	
		foreach ( $snapshots as $snapshot ) {
			
			?>
			
				<li class="item clear">
				
					<span class="date pct20 minor"><?php echo $snapshot->date->friendly; ?></span>
					<span class="size pct55 minor">
						<?php echo _t( '%s snapshot', array( ucfirst( $snapshot->type ) ) ) . ': ' . Utils::human_size( $snapshot->size ); ?>
					</span>
					<span class="delete pct10 minor">
						<?php 
						
							if ( User::identify()->can( 'snapshot', 'delete' ) ) {
								$url = URL::get( 'snapshot_delete', array( 'ts' => $snapshot->date->int ) );
								echo '<a href="' . $url . '">' . _t( 'Delete', 'exportsnapshot' ) . '</a>';
							}
						
						?>
					</span>
					<span class="download pct15 minor">
						<?php 
						
							if ( User::identify()->can( 'snapshot', 'read' ) ) {
								$url = URL::get( 'snapshot_download', array( 'ts' => $snapshot->date->int ) );
								echo '<a href="' . $url . '">' . _t( 'Download', 'exportsnapshot' ) . '</a>';
							}
						
						?>
					</span>
				
				</li>
			
			<?php
			
		}
	
	?>

</ul>