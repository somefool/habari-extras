<ul id="tag-cloud">
<?php
foreach( $theme->fluffytag as $tag ) {
	echo '<li class="step-' . $tag['step'] . '"><a href="' . 
	URL::get( 'display_entries_by_tag', array ( 'tag' => $tag['slug'] ), false ) . '" rel="tag" title="' . $tag['tag'] .'">' . $tag['tag'] . "</a></li>\n";  
}
?>
</ul>
