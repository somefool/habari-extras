<!-- comments -->
<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
  	<div id="comments">
    	<h3 id="responses"><?php echo $post->comments->moderated->count; ?> Responses to &lsquo;<?php echo $post->title; ?>&rsquo;</h4>
     	<p class="comment-feed"><a href="<?php echo $post->comment_feed_link; ?>">Atom feed for this entry.</a></p>
       	<ol>
		<?php 
		
			if ($post->comments->moderated->count) {

				$comment_count = 0;

				foreach ($post->comments->moderated as $comment) {

		?>
      		<li>
				<span id="count"><?php $comment_count++; echo $comment_count; ?></span>
       			<h6><a href="<?php echo $comment->url; ?>" rel="external" target="_blank"><?php echo $comment->name; ?></a><span> // <?php echo $comment->date_out; ?><?php if ( $comment->status == Comment::STATUS_UNAPPROVED ) : ?> (In moderation)<?php endif; ?></span></h6>
       			<div class="comment-content">
       	 		<?php echo $comment->content_out; ?>
        		</div>
     	 	</li>
		<?php 
	
				}

			} else { 

		?>
      
			<li id="no-comments">There are currently no comments.</li>
		
		<?php } ?>
    
		</ol>

      	<h3 id="replay">Leave a Reply</h3>
		<form action="<?php URL::out( 'submit_feedback', array( 'id' => $post->id ) ); ?>" method="post" id="commentform">
       	<div id="comment-details">
		    <p>
		    	<input type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="1">
		     	<label for="name"><strong><?php _e('Name'); ?></strong><?php if (Options::get('comments_require_id') == 1) : ?> *Required<?php endif; ?></label>
		    </p>
		    <p>
		     	<input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="2">
		     	<label for="email"><strong><?php _e('Mail'); ?></strong> (will not be published)</small><span class="required"><?php if (Options::get('comments_require_id') == 1) : ?> *Required<?php endif; ?></span></label>
		    </p>
		    <p>
		     	<input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="3">
		     	<label for="url"><strong><?php _e('Website'); ?></strong></label>
		    </p>
       	</div>
       	<p><textarea name="content" id="content" cols="100" rows="10" tabindex="4"><?php echo $commenter_content; ?></textarea></p>
       	<p><input name="submit" type="submit" id="btn" tabindex="5" value="Submit"></p>
     	</form>
    
   	</div>
<!-- /comments -->
