<ul id="flickrfeed">
	<?php if ( !isset( $content->error ) ): ?>
	<?php foreach ( $content->images as $image ): ?>
	<li class="flickr-image"><a href="<?php echo $image[ 'url' ]; ?>"><img src="<?php echo $image[ 'image_url' ]; ?>" alt="<?php echo Utils::htmlspecialchars( $image[ 'title' ] ); ?>" /></a></li>
	<?php endforeach; ?>
	<?php else: ?>
	<li class="flickr-error"><?php echo $content->error; ?></li>
	<?php endif; ?>
</ul>