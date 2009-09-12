<div id="twitter">
<h2>{hi:"Twittering"}</h2>
	<?php foreach ($tweets as $tweet) : ?>
	<p><?php echo $tweet->text; ?><br>
		<small>{hi:"via"} <a href="http://twitter.com/<?php echo urlencode( Options::get( 'twitter__username' )); ?>" title="<?php echo urlencode( Options::get( 'twitter__username' )); ?>">{hi:"Twitter"}</a></small></p>
	<?php endforeach; ?>
</div>
