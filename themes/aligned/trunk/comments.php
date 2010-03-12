<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
	<div class="comments">
		<h3><?php echo $post->comments->moderated->count; ?> Responses to <em><?php echo $post->title; ?></em></h3>
   		<ol id="commentlist">
			<?php 
			if ( $post->comments->moderated->count ) {
				foreach ( $post->comments->moderated as $comment ) {
					if ( $comment->url_out == '' ) {						$comment_url = $comment->name_out;					}					else {						$comment_url = '<a href="' . $comment->url_out . '" rel="external">' . $comment->name_out . '</a>';					}
					$class= 'class="comment';
					if ( $comment->status == Comment::STATUS_UNAPPROVED ) {
						$class.= ' unapproved';
					}
					$class.= '"';
					?>
     						<li id="comment-<?php echo $comment->id; ?>" <?php echo $class; ?>>
								<h2 class="comment_author"><span><a class="date" href="#comment-<?php echo $comment->id; ?>" title="Time of this comment"><?php $comment->date->out(); ?></a> | <?php echo $comment_url; ?></span></h2>
      							<div class="comment_content">
       								<?php echo $comment->content_out; ?>
								</div>
     						</li>

					<?php 
					}
				}
			else { ?>
     				<li><div class="no_comments"><?php _e('There are currently no comments.'); ?></div></li>
			<?php } ?>
    		</ol>
<?php if ( ! $post->info->comments_disabled ) { include_once( 'commentform.php' ); } ?>    
	</div>
