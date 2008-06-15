<div class="modulecore">
	<h2>Mollom Stats</h2><div class="handle">&nbsp;</div>
	<ul class=items">
		<li class="item clear">
			<span class="pct80"><?php _e(' Number of days Mollom has been used' ); ?></span><span class="comments pct20"><?php echo $stats['total_days']; ?></span>
		</li>
		<li class="item clear">
			<span class="pct80"><?php _e( 'Total number of spam messages caught' ); ?></span><span class="comments pct20"><?php echo $stats['total_rejected']; ?></span>
		</li>
		<li class="item clear">
			<span class="pct80"><?php _e( 'Total number of comments accepted' ); ?></span><span class="comments pct20"><?php echo $stats['total_accepted']; ?></span>
		</li>
		<li class="item clear">
			<span class="pct80"><?php _e( 'Percentage of spam messages' ); ?></span><span class="comments pct20"><?php echo $stats['avg']; ?>%</span>
		</li>
		<li class="item clear">
			<span class="pct80"><?php _e( 'Number of spam messages yesterday' ); ?></span><span class="comments pct20"><?php echo $stats['yesterday_rejected']; ?></span>
		</li>
		<li class="item clear">
			<span class="pct80"><?php _e( 'Number of comments accepted yesterday' ); ?></span><span class="comments pct20"><?php echo $stats['yesterday_accepted']; ?></span>
		</li>
		<li class="item clear">
			<span class="pct80"><?php _e( 'Number of spam messages today' ); ?></span><span class="comments pct20"><?php echo $stats['today_rejected']; ?></span>
		</li>
		<li class="item clear">
			<span class="pct80"><?php _e( 'Number of comments accepted today' ); ?></span><span class="comments pct20"><?php echo $stats['today_accepted']; ?></span>
		</li>
	</ul>
</div>
