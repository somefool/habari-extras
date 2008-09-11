<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="jaiku">
	<ul>
	<?php
		if ($presences instanceof JaikuPresence) { // Single presence
			printf('<li class="jaiku-message">%1$s <a href="%2$s"><abbr title="%3$s">%4$s</abbr></a> (<a href="%2$s#comments">%5$s</a>)</li>', $presences->title, $presences->url, $presences->created_at, $presences->created_at_relative, count($presences->comments));
			printf('<li class="jaiku-more"><a href="%s">' . _t('Read more…', $this->class_name) . '</a></li>', $presences->user->url);
		} else
		if (is_array($presences)) { // Multiple presences
			foreach ($presences as $presence) {
				printf('<li class="jaiku-message">%1$s <a href="%2$s"><abbr title="%3$s">%4$s</abbr></a> (<a href="%2$s#comments">%5$s</a>)</li>'), $presence->title, $presence->url, $presence->created_at, $presence->created_at_relative, $presence->comments);
			}
			printf('<li class="jaiku-more"><a href="%s">' . _t('Read more…', $this->class_name) . '</a></li>', $presences[0]->user->url);
		} else // Exceptions
		{
			echo '<li class="jaiku-error">' . $presences . '</li>';
		}
	?>
	</ul>
</div>