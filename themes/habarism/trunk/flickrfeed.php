<div class="block" id="flickr">
	<div class="images clearfix">
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
	</div>
</div>