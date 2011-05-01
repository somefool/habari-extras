<ul class="tag-cloud">
<?php foreach($taglist as $tag): ?>
	<li><a href="<?php echo URL::get( 'display_entries_by_tag', array( 'tag' => $tag->slug ) ); ?>" title="<?php echo $tag->tag; ?>" rel="tag" style="font-size: 125%;"><?php echo $tag->tag; ?></a></li>
<?php endforeach; ?>
</ul>
