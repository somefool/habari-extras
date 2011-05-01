<!-- comments -->
<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
<div id="comments">
<?php
if ( ! $post->info->comments_disabled || $post->comments->moderated->count ) {
?>
    <div id="comments-list" class="comments">
     <h3 class="comments-count"><span><?php echo $post->comments->moderated->count; ?> <?php _e('Responses to'); ?> <?php echo $post->title; ?></span></h3>
	<div class="metalinks">
      <span class="commentsrsslink"><a href="<?php echo $post->comment_feed_link; ?>"><?php _e('Feed for this Entry'); ?></a></span>
     </div>

     <ol id="commentlist">
<?php 
if ( $post->comments->moderated->count ) {
	$count= 0;
	foreach ( $post->comments->moderated as $comment ) {
		$count++;
		if ( 0 == ( $count % 2 ) ) {
			$class= '';
		} else {
			$class= 'alt';
		}
		if( $comment->email == $post->author->email ) {
			$class= 'comment-author-admin';
		}
		if ( $comment->status == Comment::STATUS_UNAPPROVED ) {
			$class= 'unapproved';
		}
?>
      <li id="comment-<?php echo $comment->id; ?>" class="comment <?php echo $class; ?>">
		<?php if ( Plugins::is_loaded('Gravatar') ) { ?>
			<img src="<?php echo $comment->gravatar; ?>" class="gravatar" alt="gravatar" />
		<?php } else { ?>
			<img src="http://www.gravatar.com/avatar.php?gravatar_id=<?php echo md5( $comment->email ); ?>&amp;size=48&amp;rating=G" alt="gravatar" class="gravatar" />
		<?php } ?>
		<?php
		$date1 = strtotime( $comment->date );
		$date2 = $comment->date;
		?>
		<div class="comment-info">
			<span class="comment-author vcard"><a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name; ?></a></span> <?php _e('at'); ?> <span class="comment-meta"><a href="#comment-<?php echo $comment->id; ?>" title="<?php _e('Time of this Comment'); ?>"><?php if($date1==""){
				echo $comment->date->out('F j, Y g:ia');
			}else{
				echo $comment->date;
			} ?></a></span>
		</div>
       <div class="comment-content">
        <?php echo $comment->content_out; ?>
        <?php if ( $comment->status == Comment::STATUS_UNAPPROVED ) : ?> <em class="unapproved"><?php _e('Your comment is awaiting moderation'); ?></em><?php endif; ?>
       </div>
      </li>

<?php 
	}
}
?>
     </ol>
	</div>
<?php } else if($post->info->comments_disabled){ ?>
      <p class="nocomment"><?php _e('Comments are closed for this post.'); ?></p>
<?php } else { ?>
	<p class="nocomment"><?php _e('There are currently no comments.'); ?></p>
<?php }
	if ( ! $post->info->comments_disabled ) { include_once( 'commentform.php' ); } ?>
</div><!-- #comments -->
