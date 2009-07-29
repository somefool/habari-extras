<ul class="items">
	<?php foreach ( $data[1] as $k => $v ) { ?>
	<li class="item clear">
		<span class="message pct75 minor"><?php echo $k; ?></span>
		<span class="date pct15 minor"><?php echo $v; ?> views</span>
		<span class="comments pct10"><?php echo number_format( (($v / $data_total) * 100), 2); ?>%</span>
	</li>
	<?php } ?>
</ul>
