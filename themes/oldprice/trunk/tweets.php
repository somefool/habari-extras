<!-- This file can be copied and modified in a theme directory -->

<li id="widget-twitterbox" class="widget widget_twitterbox">
	<h3><a href="http://twitter.com/<?php echo urlencode( Options::get( 'twitter__username' )); ?>" title="follow me"><?php _e('Twitter'); ?></a></h3>
	<ul>
		<li class="tweet_text"><?php echo $tweet_text; ?></li>
		<li class="write"><a href="http://twitter.com/<?php echo urlencode( Options::get( 'twitter__username' )); ?>"><?php echo "at ".$tweet_time_since; ?></a></li>
	</ul>
</li>
