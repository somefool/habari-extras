<div id="comments" class="entry column span-24">
	<div class="left column span-6 first">
		<h2></h2>
	</div>
	<div class="center comments column span-13">
		<ol id="commentlist">
			<?php 
			if ( $post->comments->comments->approved->count ) {
				foreach ( $post->comments->comments->approved as $comment ) {
			?>
			<li id="comment-<?php echo $comment->id; ?>" class="comment">
				<span class="commentauthor">
				<strong><?php echo $comment->name; ?></strong><br>
				<a href="<?php echo $comment->url; ?>" rel="external">Stroll on over and visit <?php echo $comment->name; ?></a>
				<br>
				<a href="#comment-<?php echo $comment->id; ?>" title="Time of this comment" class="commentdate"><?php echo $comment->date_out; ?></a>
				</span>
		       <div class="commentcontent">
		        <?php echo $comment->content_out; ?>
		       </div>
		      </li>
		<?php 
		} }
		else { ?>
		      <li><?php _e('There are currently no comments, why don\'t you leave one?'); ?></li>
		<?php } ?>
		</ol>
			<?php if( $post->comments->pingbacks->count ) : ?>
				<h2>Pingbacks &amp; Trackbacks</h2>
					<div id="pings">
						<ol id="pinglist">
							<?php foreach ( $post->comments->pingbacks->approved as $pingback ) : ?>
								<li id="ping-<?php echo $pingback->id; ?>">
										<a href="<?php echo $pingback->url; ?>" title=""><?php echo $pingback->name; ?></a> &raquo; <?php echo $pingback->content; ?>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				<?php endif; ?>
	<?php if ( ! $post->info->comments_disabled ) { ?>

	     <div class="comments">
	      <h2 id="respond" class="reply">Leave a Reply</h2>
			<?php
			$class= '';
			$cookie= 'comment_' . Options::get( 'GUID' );
			$commenter_name= '';
			$commenter_email= '';
			$commenter_url= '';
			if ( $user ) {
				$commenter_name= $user->username;
				$commenter_email= $user->email;
				$commenter_url= Site::get_url('habari');
			}
			elseif ( isset( $_COOKIE[$cookie] ) ) {
				list( $commenter_name, $commenter_email, $commenter_url )= explode( '#', $_COOKIE[$cookie] );
			}
			elseif ( isset( $_SESSION['comment'] ) ) {
				$details= Session::get_set('comment');
				$commenter_name= $details['name'];
				$commenter_email= $details['email'];
				$commenter_url= $details['url'];
			}

			if ( Session::has_errors() ) {
				Session::messages_out();
			}
			?>
			<br>
	      <form action="<?php URL::out( 'submit_feedback', array( 'id' => $post->id ) ); ?>" method="post" id="commentform">
	       <div id="comment-personaldetails">
	        <p>
	         <input type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="1">
	         <label for="name"><small><strong>Name</strong></small></label>
	        </p>
	        <p>
	         <input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="2">
	         <label for="email"><small><strong>Mail</strong> (will not be published)</small></label>
	        </p>
	        <p>
	         <input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="3">
	         <label for="url"><small><strong>Website</strong></small></label>
	        </p>
	       </div>
	       <p>
	        <textarea name="content" id="content" cols="55" rows="10" tabindex="4">
	        	<?php if ( isset( $details['content'] ) ) { echo $details['content']; } ?>
	        </textarea>
	       </p>
			<p><input type="submit" value="Add your comment" id="submit"></p>
	      </form>
	     </div>
	<?php } ?>
	</div>
	<div class="right column span-5 last">
		<h2><a name="#comments">Comments</a></h2>
	</div>
</div>