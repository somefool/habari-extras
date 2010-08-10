		<div class="comments">
			<h3 class="comment_title"><?php echo $post->comments->moderated->count; ?> Responses to <?php echo $post->title; ?></h3>
     		<ol id="commentlist">
				<?php 
				if ( $post->comments->moderated->count ) {
					foreach ( $post->comments->moderated as $comment ) {
						if ( $comment->url_out == '' ) {							$comment_url = $comment->name_out;						}						else {							$comment_url = '<a href="' . $comment->url_out . '" rel="external">' . $comment->name_out . '</a>';						}
						$class= 'class="comment';
						if ( $comment->status == Comment::STATUS_UNAPPROVED ) {
							$class.= '-unapproved';
						}
						$class.= '"';
						?>
      						<li id="comment-<?php echo $comment->id; ?>" <?php echo $class; ?>>
								<h2 class="comment_author"><?php echo $comment_url; ?></h2>
       							<h3 class="comment_meta"><a href="#comment-<?php echo $comment->id; ?>" title="Time of this comment"><?php $comment->date->out(); ?></a><?php if ( $comment->status == Comment::STATUS_UNAPPROVED ) : ?> &middot; in moderation<?php endif; ?></h3>
       							<div class="comment_content">
        							<?php echo $comment->content_out; ?>
								</div>
      						</li>

						<?php 
						}
					}
				else { ?>
      				<li><?php _e('There are currently no comments.'); ?></li>
				<?php } ?>
     		</ol>
<?php if ( ! $post->info->comments_disabled ) { include_once( 'commentform.php' ); } ?>    
	</div>
