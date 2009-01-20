<div id="magicArchives">
	<div id="archive_controller">
		<div class="section search">
			<a id="archive_previous" href="#previous" title="<?php _e('Older'); ?>">&laquo; <strong><?php _e('Older'); ?></strong></a>
			<label for="archive_search">Search</label><input type="text" name="archive_search" value="" id="archive_search">
			<a id="archive_next" href="#next" title="<?php _e('Newer'); ?>"><strong><?php _e('Newer'); ?> &raquo;</strong></a>
		</div>
		<div class="section toggle">
			<span id="archive_total">Total Entries: <strong>250</strong></span>
			<a id="archive_toggle_tags" href="#tags" title="</php _e('Show Tags'); ?>">Filter by Tags</a>
		</div>
		<div class="section tags" id="archive_tags">
			<?php
			// Utils::debug($tags);
			foreach ($tags as $tag) : ?>
					<span id="<?php echo 'tag_' . $tag->id ?>" class="item tag wt<?php echo MagicArchives::tag_weight($tag->count, 10); ?>"> 
					 	<span class="checkbox"><input type="checkbox" class="checkbox" name="checkbox_ids[<?php echo $tag->id; ?>]" id="checkbox_ids[<?php echo $tag->id; ?>]"></span><label for="checkbox_ids[<?php echo $tag->id; ?>]"><?php echo $tag->tag; ?></label><sup><?php echo $tag->count; ?></sup> 
					 </span>
			<?php endforeach; ?>
		</div>
	</div>
	<div id="archive_posts">
		<?php $theme->display('archive_posts'); ?>
	</div>
</div>