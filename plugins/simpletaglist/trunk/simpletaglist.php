<!-- This file can be copied and modified in a theme directory -->
<ul id="tag-links">
<?php foreach( $theme->tag_links as $tag) { ?>

	<li><a href="<?php URL::out( 'display_entries_by_tag', array( 'tag'=> $tag->slug ), false ); ?>"><?php echo $tag->tag; ?></a></li>
<? 	} ?>
</ul>
