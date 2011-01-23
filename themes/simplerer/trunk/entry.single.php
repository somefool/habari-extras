<?php include 'header.php'; ?>
		<div id="content">
			<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?> post">
				<h2 class="title"><?php echo $post->title_out; ?></h2>
				<div class="entry">
					<?php echo $post->content_out; ?>
				</div>
				<p class="meta">
					<a href="<?php echo $post->permalink; ?>" rel="bookmark" title="<?php _e('Permanent link to'); ?> <?php echo $post->title; ?>"><?php echo $post->pubdate->out('d-m-Y / H:i'); ?></a><?php if ( ! $post->info->comments_disabled ) { ?> | <a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments on this post'); ?>"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a><?php } ?><?php if ( $user->loggedin ) { ?> | <a href="<?php URL::out( 'admin', 'page=publish&id=' . $post->id); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit'); ?></a><?php } ?><br>
					<?php if ( count( $post->tags ) ) { ?> Tagged: <?php echo $post->tags_out; ?><?php } ?>
				</p>
				<div id="comments">
					<?php include 'comments.php'; ?>
				</div>
			</div>
			<div class="paging">
			<?php if ( $previous= $post->descend() ): ?>
				&laquo; <a href="<?php echo $previous->permalink; ?>" title="<?php echo _t('View') . ' ' . $previous->title_out; ?>"><?php echo $previous->title_out; ?></a>
			<?php endif; ?>
			<?php if ( $next= $post->ascend() && $previous= $post->descend() ): ?>
				&nbsp;&mdash;&nbsp;
			<?php endif; ?>
			<?php if ( $next= $post->ascend() ): ?>
				<a href="<?php echo $next->permalink; ?>" title="<?php echo _t('View') . ' ' . $next->title_out; ?>"><?php echo $next->title_out; ?></a> &raquo;
			<?php endif; ?>
			</div>
		</div>
<?php include 'footer.php'; ?>
