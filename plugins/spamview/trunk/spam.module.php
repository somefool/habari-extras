<ul class="items">

	<?php foreach($latestspam_comments as $comment): ?>
	<li class="item clear">
		<span class="date pct15 minor"><a href="#" title="<?php printf(_t('Written at %1$s'), $comment->date->get( 'g:m a \o\n F jS, Y' ) ); ?>"><?php $comment->date->out( 'M j' ); ?></a></span>
		<span class="who pct70"><a href="<?php echo $comment->url; ?>"><?php echo $comment->name; ?></a> <a class="minor" href="<?php echo $comment->post->permalink; ?>"> <?php _e('on'); ?> <?php echo $comment->post->title; ?></a></span>
		<span class="comments pct15"><a href="<?php echo URL::get( 'admin', array( 'page' => 'comment', 'id' => $comment->id) ); ?>">Approve</a></span>
	</li>
	<?php endforeach; ?>

</ul>
