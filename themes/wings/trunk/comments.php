<!-- comments -->
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
<?php 	$post->comment_form()->out(); ?>
   	</div>
<!-- /comments -->
