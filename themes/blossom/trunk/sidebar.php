<hr class="hide" />
<div id="ancillary">
	<div class="inside">
		<div class="first block">
			<?php if ( count($previous_posts) > 0 ): ?>
				<h3>Previously</h3>
				<ul class="dates">
					<?php
					foreach ( $previous_posts as $post ) {
						$date = Format::nice_date($post->pubdate, 'm.d');
						echo "<li><a href=\"{$post->permalink}\"><span class=\"date\">{$date}</span>{$post->title}</a></li>";
					}
					?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="block" id="interests">
			<!-- Will eventually be powered by the delicious plugin -->
			<h3>Interests</h3>
				<script type="text/javascript"
								src="http://del.icio.us/feeds/js/tags/<?php echo $delicious ?>?sort=freq;count=26;size=12-20;color=808080-ff91bc;">
				</script>
		</div>

		<div class="block">
			<!-- Will eventually be powered by the lifestream plugin -->
			<h3>In Other News</h3>
			<ul class="counts">
				<script type="text/javascript" src="http://del.icio.us/feeds/js/<?php echo $delicious ?>?extended;count=1"></script>
		</div>
		<div class="clear"></div>
	</div>
</div>
<!-- /#ancillary -->
