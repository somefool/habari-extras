<div id="twitter">
<h2>{hi:"Twittering"}</h2>
	<p><?php echo $tweet_text ?><br>
		<small>{hi:"via"} <a href="http://twitter.com/<?php echo urlencode( Options::get( 'twitter__username' )); ?>" title="<?php echo urlencode( Options::get( 'twitter__username' )); ?>">{hi:"Twitter"}</a></small></p>
</div>