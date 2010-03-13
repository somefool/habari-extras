<?php

	if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }

	if ( ! $post->info->comments_disabled ) {
	
		?>
		
			<h3><?php echo $post->comments->approved->count; ?> Comments so far</h3>
			<ol id="comments-list">
			
				<?php
				
					if ( $post->comments->approved->count ) {
						
						foreach ( $post->comments->approved as $comment ) {
							
							if ( $comment->url == '' ) {
								$comment_url = $comment->name_out;
							}
							else {
								$comment_url = '<a href="' . $comment->url . '" rel="external">' . $comment->name_out . '</a>';
							}
							
							?>
							
								<li id="comment-<?php echo $comment->id; ?>" class="comment">
									<p class="commenter"><cite><?php echo $comment_url; ?></cite>, on <a href="#comment-<?php echo $comment->id; ?>" title="Permalink to this post"><?php echo $comment->date_out; ?></a>, said:</p>
									<div class="response">
										<?php echo $comment->content_out; ?>
									</div>
									<p>
										<?php
										
											if ( $user ) {
											
												?>
												
													<a href="<?php URL::out( 'admin', 'page=comment&id=' . $comment->id); ?>" title="Edit Comment">Edit Comment</a>
													
												<?php
		
											}
		
										?>
										
									</p>
								</li>
			
							<?php
							
						}
						
					}
					else {
						
						?>
						
							<li><?php _e('There are currently no comments.'); ?></li>
							
						<?php

					}
					
				?>
				
			</ol>
			
			<div id="commentform">
				<?php $post->comment_form()->out(); ?>
			</div>
			
		<?php

	}
		
?>
