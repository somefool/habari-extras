<ul id="twitter_litte">
	<?php if (count($content->tweets)): ?>
	<?php foreach ($content->tweets as $tweet): ?>
	<li class="twitter-message"><?php echo $tweet->message_out; ?> <a href="<?php echo $tweet->url; ?>"><abbr title="<?php echo $tweet->created_at; ?>">#</abbr></a></li>
	<?php endforeach; ?>
	<?php else: ?>
	<li class="twitter-error"><?php echo $tweets; ?></li>
	<?php endif; ?>
</ul>

