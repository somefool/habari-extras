<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="audioscrobbler">
	<h2><?php _e('Now Listening…', $this->class_name); ?></h2>
	<?php
		if ($track instanceof SimpleXMLElement) {
			printf('“<a href="%1$s">%2$s</a>” performed by %3$s', $track->url, $track->name, $track->artist);
		}
		else {
			echo $track;
		}
	?>
</div>