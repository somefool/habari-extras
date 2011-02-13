<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="flickrfeed">
	<h3>Photos</h3>
	<ul>
	<?php	if (isset($content->error)): ?>
		<li class="flickr-error"><?php echo $content->error; ?></li>
	<?php else: ?>
	<?php foreach ($content->images as $flickrimage): ?>
		<li class="flickr-image"><a href="<?php echo $flickrimage['url']; ?>"><img src="<?php echo $flickrimage['image_url']; ?>" alt="<?php echo htmlspecialchars($flickrimage['title']); ?>" /></a></li>
	<?php endforeach; ?>	
	<?php endif; ?>
	</ul>
</div>