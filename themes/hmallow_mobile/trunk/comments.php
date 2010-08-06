<!-- comments -->
<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
    
    <div class="comments">
     <h4><span id="comments"><?php echo $post->comments->moderated->count; ?> <?php _e('Responses to'); ?> <?php echo $post->title; ?></span></h4>
     
     <ol id="commentlist">
<?php 
if ( $post->comments->moderated->count ) {
	foreach ( $post->comments->moderated as $comment ) {

?>
      <li id="comment-<?php echo $comment->id; ?>" <?php echo $theme->k2_comment_class( $comment, $post ); ?>>
       <a href="#comment-<?php echo $comment->id; ?>" class="counter" title="<?php _e('Permanent Link to this Comment'); ?>"><?php echo $comment->id; ?></a>
       <span class="commentauthor"><a href="<?php echo $comment->url; ?>" rel="external"><?php if (Plugins::is_loaded('twitteravatar')) { ?>	<? echo $comment->twitter_avatar ?> <?php } ?> <?php if (Plugins::is_loaded('gravatar')) { ?>
<img class="twitter_avatar" src="<? echo $comment->gravatar ?>"> <?php } ?>
<?php echo $comment->name; ?></a></span><br/>
       <small class="comment-meta"><a href="#comment-<?php echo $comment->id; ?>" title="<?php _e('Time of this Comment'); ?>"><?php echo $comment->date->out();  ?></a><?php if ( $comment->status == Comment::STATUS_UNAPPROVED ) : ?> <em><?php _e('In moderation'); ?></em><?php endif; ?></small>
       
       <div class="comment-content">
        <?php echo $comment->content_out; ?>
        
       </div>
      </li>

<?php 
	}
}
else { ?>
<?php } ?>
     </ol>

<?php if ( ! $post->info->comments_disabled ) { include_once( 'commentform.php' ); } ?>

    
    </div>
<!-- /comments -->
