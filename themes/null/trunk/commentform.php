<?php if ( !$post->info->comments_disabled ) : ?>
				<div id="respond">
					<h2><?php _e( 'Leave a Reply' ); ?></h2>
					<?php if ( Session::has_messages( ) ) Session::messages_out( ); ?>
					<?php $post->comment_form( )->out( ); ?>
				</div>
<?php else: ?>
				<div id="comments-closed">
					<p><?php _e( 'Comments are closed for this post' ); ?></p>
				</div>
<?php endif; ?>