<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<?php
	if ($post->comments->tweetbacks->count > 0) {
?>
<ul id="tweetbacks">
<?php
	foreach ($post->comments->tweetbacks as $tweet) {
		printf('<li class="tweetback vcard"><a href="%1$s" class="url fn" rel="external">%2$s</a>: %3$s <abbr class="published" title="%4$s">%5$s</abbr></li>', $tweet->profile_url, $tweet->name_out, $tweet->content_out, $tweet->date->format(HabariDateTime::ISO8601), $tweet->date->format('F j, Y – g:i a'));
	}
?>
</ul>
<?php
	}
?>
