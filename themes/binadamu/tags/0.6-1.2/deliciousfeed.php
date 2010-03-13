<li id="widget-deliciousfeed" class="widget">
	<h3>Notepad</h3>
	<ul>
	<?php
		if (is_array($deliciousfeed)) {
			foreach ($deliciousfeed as $post) {
				printf('<li class="delicious-post"><a href="%1$s" title="%2$s">%3$s</a></li>', $post->url, $post->desc, $post->title);
			}
		} else // Exceptions
		{
			echo '<li class="delicious-error">' . $deliciousfeed . '</li>';
		}
	?>
	</ul>
</li>