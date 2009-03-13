<!-- comments -->
<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
	<div id="comments">
<?php if( $post->comments->moderated->count ) :
$count= 0; ?>

	<h4><?php echo $post->comments->moderated->count; ?> Responses to &#8220;<?php echo $post->title; ?>&#8221;</h4>
	<ol class="commentlist">
<?php foreach ( $post->comments->moderated as $comment ) :
	$count++;
		if ( 0 == ( $count % 2 ) ) {
			$class= '';
			} else {
			$class= 'alt';
			}
		if ( $post->author->email == $comment->email ) {
			$class= 'owner';
			}
		if ( $comment->status == Comment::STATUS_UNAPPROVED ) {
			$class= 'unapproved';
			}
?>
	<li id="comment-<?php echo $comment->id; ?>" class="<?php echo $class; ?>">

	   <span class="commentauthor"><a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name; ?></a></span>
	   <small class="commentmetadata"><a href="#comment-<?php echo $comment->id; ?>" title="Time of this comment"><?php echo $comment->date; ?></a> <?php if ( $comment->status == Comment::STATUS_UNAPPROVED ) { ?><em>Your comment is awaiting moderation.</em><?php } ?></small>

	   <?php echo $comment->content_out; ?>

	</li>
	
	<?php endforeach; { ?>
	</ol>

	<?php }
	endif;
	?>


<?php if ( ! $post->info->comments_disabled ) { include_once( 'commentform.php' ); } ?>

	</div>
<!-- comments -->