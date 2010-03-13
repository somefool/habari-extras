<?php 
if ( $post->comments->moderated->count ) {?>
<h2 id="comments"><?php echo $post->comments->moderated->count; echo _n( ' comment', ' comments', $post->comments->moderated->count ); ?></h2>
<?php	foreach ( $post->comments->moderated as $comment ) {
?>


<div class="comments_wrap" id="comment-<?php echo $comment->id; ?>">
<div class="left">
<img src="<?php //$theme->gravatar('X', '35'); ?>http://www.gravatar.com/avatar.php?gravatar_id=<?php echo md5( $comment->email ); ?>&size=35" alt="<?php _e('Gravatar'); ?>" />
</div>
<div class="right">
<h4><b><a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name; ?></a></b>&nbsp; on 
<?php echo $comment->date_out; ?></h4>
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
$post->comment_form()->out();
?>
