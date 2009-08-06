<div id="timeline">

	<div class="years">
	<?php
	$year = false;
	foreach($timeline_items as $item) {
		if(date('Y', $item['time']) != $year) { ?>
			<?php if($year != false) { ?></div></div></div><?php } ?>
			<div class="year">
				<span><?php echo date('Y', $item['time']); ?></span>
				<div class="months">
			<?php
			$year = date('Y', $item['time']);
			$month = false;
		}
		if(date('m', $item['time']) != $month) { ?>
			<?php if($month != false) { ?></div><?php } ?>
				<div class="month">
					<span><?php echo date('M', $item['time']); ?></span>
			<?php
			$month = date('m', $item['time']);
		}
		?>
		<a class="item" href="<?php echo $item['url']; ?>" title="<?php echo $item['title']; ?> was published on <?php echo date('F j, Y'); ?>"><?php echo $item['title']; ?></a>
	<?php
	}
	?>
			</div>
		</div>
		</div>
		
	</div>

	<div class="track">
		<div class="handle">
			<span class="resizehandleleft"></span>
			<span class="resizehandleright"></span>

		</div>
	</div>

</div>