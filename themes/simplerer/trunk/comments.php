<?php

	if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }

	$cookie= 'comment_' . Options::get( 'GUID' );
	
	if ( $user ) {
		if ( $user->displayname != '' ) {
			$commenter_name = $user->displayname;
		}
		else {
			$commenter_name = $user->username;
		}
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
                        <h3><?php echo $post->comments->moderated->count; ?> <?php echo _n( 'Response', 'Responses', $post->comments->moderated->count ); ?></h3>
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
									<p class="commenter"><cite><?php echo $comment_url; ?></cite>, on <a href="#comment-<?php echo $comment->id; ?>" title="<?php _e('Permanent link to'); ?>"><?php echo $comment->date_out('d-m-Y / H:i'); ?></a>, said:</p>
									<div class="response">
										<?php echo $comment->content_out; ?>
									</div>
									<p>
										<?php
										
											if ( $user ) {
											
												?>
												
													<a href="<?php URL::out( 'admin', 'page=comment&id=' . $comment->id); ?>" title="<?php _e('Edit this comment'); ?>"><?php _e('Edit this comment'); ?></a>
													
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
						<legend><?php _e('Leave a Reply'); ?></legend>
							<p>
								<label for="comment"><?php _e('Comment'); ?>:</label><br>
								<textarea name="content" id="comment" rows="5" cols="25" tabindex="1"></textarea>
							</p>
							<p>
								<label for="name"><?php _e('Name <span class=\"required\">*Required</span>'); ?>:</label>
								<input type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="2">
							</p>
							<p>
								<label for="email"><?php _e('Email <span class=\"required\">*Required</span>'); ?>:</label>
								<input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="3">
							<p>
								<label for="url"><?php _e('Website'); ?>:</label>
								<input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="4">
							</p>
							<p>
								<input name="submit" type="submit" id="submit" tabindex="5" value="<?php _e('Submit'); ?>">
							</p>
					</fieldset>
				</form>
			</div>
			
		<?php

	}
		
?>