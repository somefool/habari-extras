<div id="post-<?php echo $content->id; ?>" class="entry <?php echo $content->statusname; ?>">
	<div class="entry-head">
		<h2 class="entry-title"><a href="<?php echo $content->permalink; ?>" title="<?php echo $content->title; ?>"><?php echo $content->title_out; ?></a></h2>

		<div class="entry-meta">
			<span class="chronodata published"><?php echo $content->pubdate_ago; ?></span> &middot; 
			<span class="commentslink"><a href="<?php echo $content->permalink; ?>#comments" title="<?php _e('Comments on this post', 'resurrection'); ?>"><?php printf(_n( '%d Comment', '%d Comments', $content->comments->approved->count, 'resurrection' ), $content->comments->approved->count); ?></a></span>
			<?php if ( is_object($user) && $user->can('edit_post') ) : ?>
			 &middot; 	<span class="entry-edit"><a href="<?php echo $content->editlink; ?>" title="<?php _e('Edit post', 'resurrection'); ?>"><?php _e('Edit', 'resurrection'); ?></a></span>
			<?php endif; ?>
			<?php if ( is_array( $content->tags ) ) : ?>
			 &middot; 	<span class="entry-tags"><?php echo $content->tags_out; ?></span>
			<?php endif; ?>
		</div>
	</div>
	<div class="entry-content">
		<?php echo $content->content_out; ?>
	</div>
</div>