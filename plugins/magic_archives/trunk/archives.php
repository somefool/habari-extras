<div id="magicArchives">
	<div id="archive_controlbar">
		<div id="archive_search_label" class="section">
			<a class="toggle none" href="#"><?php _e('Search'); ?></a>
		</div>
		<div id="archive_tags_label" class="section open">
			<a class="toggle none" href="#"><?php _e('Tags'); ?></a>
		</div>
		<div id="archive_date_label" class="section">
			<a class="toggle none" href="#"><?php _e('Date'); ?></a>
		</div>
	</div>
	<div id="archive_controls">
		<div id="archive_search" class="section">
		</div>
		<div id="archive_tags" class="section open">
			<?php // Utils::debug($tags); ?>
			<?php
			$max = 10;
			function tag_weight( $count, $max )
			{
				return round( 10 * log($count + 1) / log($max + 1) );
			}
			?>
			<?php foreach ($tags as $tag) : ?>
					<span id="<?php echo 'tag_' . $tag->id ?>" class="item tag wt<?php echo tag_weight($tag->count, $max); ?>"> 
					 	<span class="checkbox"><input type="checkbox" class="checkbox" name="checkbox_ids[<?php echo $tag->id; ?>]" id="checkbox_ids[<?php echo $tag->id; ?>]"></span><label for="checkbox_ids[<?php echo $tag->id; ?>]"><?php echo $tag->tag; ?></label><sup><?php echo $tag->count; ?></sup> 
					 </span>
			<?php endforeach; ?>
		</div>
		<div id="archive_date" class="section">
		</div>
	</div>
</div>