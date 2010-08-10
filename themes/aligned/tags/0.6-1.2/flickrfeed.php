<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div class="block" id="flickr">
	<h3>Flickr Photostream</h3>
	<div class="clearfix images">
	<?php
		if (is_array($flickrfeed)) {
			foreach ($flickrfeed as $flickrimage) {
				printf('<a href="%1$s" title="%2$s"><img src="%3$s" alt="%4$s" /></a>', $flickrimage['url'], strip_tags($flickrimage['description_raw']), $flickrimage['image_url'], htmlspecialchars($flickrimage['title']));
			}
		} else // Exceptions
		{
			echo '<div class="flickr-error">' . $flickrfeed . '</div>';
		}
	?>
	</ul>
</div>