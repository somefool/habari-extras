<?php include 'header.php'; ?>
		<div id="content">
<?php foreach ( $posts as $post ) { ?>
			<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?> post">
				<h2 class="title"><?php echo $post->title_out; ?></h2>
				<div class="entry">
					<?php echo $post->content_out; ?>

				</div>
				<p class="meta">
					<a href="<?php echo $post->permalink; ?>" rel="bookmark" title="<?php _e('Permanent link to'); ?> <?php echo $post->title_out; ?>"><?php echo $post->pubdate->out('d-m-Y / H:i'); ?></a> <?php if ( ! $post->info->comments_disabled ) { ?> | <a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments on this post'); ?>"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a><?php } ?><?php if ( $user->loggedin ) { ?> | <a href="<?php URL::out( 'admin', 'page=publish&id=' . $post->id); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit'); ?></a><?php } ?><br>
					<?php if ( $post->tags ) { ?> <?php _e('Tagged:'); ?> <?php echo $post->tags_out; ?><?php } ?>
				</p>
			</div>
<?php } ?>
			<div class="paging">
				<?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?>

			</div>
		</div>
<?php include 'footer.php'; ?>