<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="twitterlitte">
	<ul>
	<?php
		if (is_array($tweets)) {
			foreach ($tweets as $tweet) {
				printf('<li class="twitter-message">%1$s <a href="%2$s"><abbr title="%3$s">#</abbr></a></li>', $tweet->message_out, $tweet->url, $tweet->created_at);
			}
			printf('<li class="twitter-more"><a href="%s">' . _t('Read more…', $this->class_name) . '</a></li>', $tweets[0]->user->profile_url);
		}
		else { // Exceptions
			echo '<li class="twitter-error">' . $tweets . '</li>';
		}
	?>
	</ul>
</div>