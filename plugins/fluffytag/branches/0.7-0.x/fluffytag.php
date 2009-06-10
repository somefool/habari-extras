<h3 id="tagcloud"><?php _e('Tag cloud'); ?></h3>
<ul id="fluffytag">
<?php
foreach( $theme->fluffy as $tag ) {
	echo '<li class="step-' . $tag['step'] . '"><a href="' . 
	URL::get( 'display_entries_by_tag', array ( 'tag' => $tag['slug'] ), false ) . '" rel="tag" title="' . $tag['tag'] .'">' . $tag['tag'] . "</a></li>\n";  
}
?>
</ul>
