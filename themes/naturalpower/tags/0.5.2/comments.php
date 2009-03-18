<?php 
if ( $post->comments->moderated->count ) {
	foreach ( $post->comments->moderated as $comment ) {
?>

<h2 id="comments"><?php echo $post->comments->moderated->count; ?> comments</h2>

<div class="comments_wrap" id="comment-<?php echo $comment->id; ?>">
<div class="left">
<img src="<?php //$theme->gravatar('X', '35'); ?>http://www.gravatar.com/avatar.php?gravatar_id=<?php echo md5( $comment->email ); ?>&size=35" alt="<?php _e('Gravatar'); ?>" />
</div>
<div class="right">
<h4><b><a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name; ?></a></b>&nbsp; on 
<?php echo $comment->date; ?></h4>
<?php echo $comment->content_out; ?>
	<?php if ( $comment->status == Comment::STATUS_UNAPPROVED ) {
		echo '<p><em>Your comment is awaiting moderation.</em></p>';
	} ?>
</div>
</div>


<?php }} ?>



<h2 class="lc">Leave a Comment</h2>


<?php
if ( Session::has_errors() ) {
	Session::messages_out();
}
?>



<form action="<?php URL::out( 'submit_feedback', array( 'id' => $post->id ) ); ?>" method="post" id="commentform">
<div>



<label for="name">Username :<br />
<input type="text" name="name" id="author" value="<?php echo $commenter_name; ?>" size="27" tabindex="1" /></label> 



<label for="email">Email :<br />
<input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="27" tabindex="2" /></label> 



<label for="url">Web Site :<br />
<input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="27" tabindex="3" /></label> 



<label for="comment">Comment :<br /></label> 
<textarea name="content" id="comment" cols="50" rows="8" tabindex="4"><?php echo $commenter_content; ?></textarea>



<input name="submit" type="submit" id="submit" tabindex="5" value="Submit" />



</div>
</form>