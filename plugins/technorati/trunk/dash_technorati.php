<div class="close">&nbsp;</div>
<div class="modulecore">
	<h2>Technorati Stats</h2><div class="handle">&nbsp;</div>
	<ul class="items">
<?php	foreach ( $stats as $key => $count ) : ?>
		<li class="item clear">
			<span class="pct90"><?php echo $key; ?></span>
			<span class="comments pct10"><?php echo $count; ?></span>
		</li>
<?php endforeach ?>
	</ul>
</div>
