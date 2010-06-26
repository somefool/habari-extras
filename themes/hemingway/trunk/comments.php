<?php // Do not delete these lines
  if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
    die (_t('Please do not load this page directly. Thanks!'));

    $oddcomment = 'alt';
?>

<!-- You can start editing here. -->
<?php if ($comments_number= $post->comments->approved->count) : ?>

	<ol id="comments">
	<?php foreach ($post->comments->approved as $comment) : ?>
		<li id="comment-<?php echo $comment->id; ?>">
			<cite>
				<span class="author"><a href="<?php echo $comment->url; ?>" rel="external nofollow"><?php echo $comment->name; ?></a></span>
				<span class="date"><?php echo $comment->date->out(); ?></span>
			</cite>
			<div class="content">
				<?php if ($comment->comment_approved == '0') : ?>
				<em>Your comment is awaiting moderation.</em>
				<?php endif; ?>
				<?php echo $comment->content_out; ?>
			</div>
			<div class="clear"></div>
		</li>
		<?php /* Changes every other comment to a different class */	
      if ('alt' == $oddcomment) $oddcomment = '';
      else $oddcomment = 'alt';
    ?>
	<?php endforeach; /* end for each comment */ ?>
	</ol>

 <?php else : // this is displayed if there are no comments so far ?>

  <?php if ($post->info->comments_disabled) : ?> 
		<!-- If comments are closed. -->
		<p class="nocomments">Comments are closed.</p>
	 <?php else : // just no comments yet ?>
		<!-- If comments are open, but there are no comments. -->
	<?php endif; ?>

<?php endif; ?>


<?php
 	if ( ! $post->info->comments_disabled ) { ?>
	<div id="comment-form">
	<h3 class="formhead">Have your say</h3>
	<?php $commenter= User::commenter(); ?>
	<?php
		if ( Session::has_messages() ) {
			Session::messages_out();
		}	
	$post->comment_form()->out();
	echo '</div>';
} ?>
