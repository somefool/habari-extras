<ul id="fluffytag">
<?php
foreach( $theme->fluffy as $tag ) {
	echo '<li class="step-' . $tag->step . '"><a href="' . 
	URL::get( 'display_entries_by_tag', array ( 'tag' => $tag->tag_slug ), false ) . '" rel="tag" title="' . $tag->tag_text .'">' . $tag->tag_text . "</a></li>\n";  
}
?>
</ul>
