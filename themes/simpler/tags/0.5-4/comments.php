<?php

	if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }

	$cookie= 'comment_' . Options::get( 'GUID' );
	
	if ( $user ) {
		$commenter_name= $user->username;
		$commenter_email= $user->email;
		$commenter_url= Site::get_url('habari');
	}
	elseif ( isset( $_COOKIE[$cookie] ) ) {
		list( $commenter_name, $commenter_email, $commenter_url )= explode( '#', $_COOKIE[$cookie] );
	}
	else {
		$commenter_name= '';
		$commenter_email= '';
		$commenter_url= '';
	}
	
	if ( ! $post->info->comments_disabled ) {
	
		?>
		
			<h3><?php echo $post->comments->approved->count; ?> Comments so far</h3>
			<ol id="comments">
			
				<?php
				
					if ( $post->comments->approved->count ) {
						
						foreach ( $post->comments->approved as $comment ) {
							
							?>
							
								<li id="comment-<?php echo $comment->id; ?>" class="comment">
									<p class="commenter"><cite><a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name_out; ?></a></cite>, on <a href="#comment-<?php echo $comment->id; ?>" title="Permalink to this post"><?php echo $comment->date_out; ?></a>, said:</p>
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
				<form action="<?php URL::out( 'submit_feedback', array( 'id' => $post->id ) ); ?>" method="post">
					<fieldset>
						<legend>Leave a Comment?</legend>
							<p>
								<label for="comment">Comment:</label><br>
								<textarea name="content" id="content" rows="5" tabindex="1"></textarea>
							</p>
							<p>
								<label for="author">Name:</label>
								<input type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="2">
							</p>
							<p>
								<label for="email">Email:</label>
								<input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="3">
							<p>
								<label for="url">Website:</label>
								<input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="4">
							</p>
							<p>
								<input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" />
							</p>
					</fieldset>
				</form>
			</div>
			
		<?php

	}
		
?>
