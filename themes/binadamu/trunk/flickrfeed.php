<li id="widget-flickrfeed" class="widget">
	<h3>Flickr</h3>
	<ul>
	<?php
		if (is_array($flickrfeed)) {
			foreach ($flickrfeed as $flickrimage) {
				printf('<li class="flickr-image"><a href="%1$s" title="%2$s"><img src="%3$s" alt="%4$s" /></a></li>', $flickrimage['url'], strip_tags($flickrimage['description_raw']), $flickrimage['image_url'], htmlspecialchars($flickrimage['title']));
			}
		} else // Exceptions
		{
			echo '<li class="flickr-error">' . $flickrfeed . '</li>';
		}
	?>
	</ul>
</li>