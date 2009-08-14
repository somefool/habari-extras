<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die (_t('Please do not load this page directly. Thanks!'));
?>

<!-- You can start editing here. -->

<?php
	/* This variable is for alternating comment background */
	$oddcomment = 'alt';
?>

<?php if ( $comments_number= $post->comments->approved->count ): ?>

	<ol id="comments">

	<?php foreach ( $post->comments->approved as $comment ): ?>
		<li id="comment-<?php echo $comment->id; ?>">
			<cite>
				<span class="author"><a href="<?php echo $comment->url; ?>" rel="external"><?php echo $comment->name; ?></a></span>
				<span class="date"><?php echo Format::nice_date($post->pubdate, 'Y.j.n') ?> / <?php echo Format::nice_date($post->pubdate, 'ga') ?></span>
			</cite>
			<div class="content">
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

<?php
else: // no approved comments

	if ( $post->info->comments_disabled ) {
		_e('<p class="nocomments">Comments are closed.</p>');
	} else { // comments are closed
		_e('<p>There are currently no comments.</p>');
	}

endif; ?>

<?php
if ( !$post->info->comment_disabled ):
	$commenter= User::commenter(); ?>

	<div id="comment-form">
		<h3 class="formhead">Have your say</h3>
		<form action="<?php URL::out('submit_feedback', array('id'=>$post->id) ); ?>" method="post" id="commentform">

			<input type="text" name="name" id="author" value="<?php echo $commenter['name']; ?>" class="textfield" tabindex="1" />
			<label for="name"><small>Name</small></label><br>

			<input type="text" name="email" id="email" value="<?php echo $commenter['email']; ?>" class="textfield" tabindex="2" />
			<label for="email">Email (will not be published)</label><br>

			<input type="text" name="url" id="url" value="<?php echo $commenter['url']; ?>" class="textfield" tabindex="3" />
			<label for="url">Website</label>

			<textarea name="content" id="comment" class="commentbox" tabindex="4"></textarea>
			<p>I reserve the right to delete any comments I don't like.</p>
			<div class="formactions">
				<input type="submit" name="submit" tabindex="5" class="submit" value="Add your comment" />
			</div>
		</form>
	</div>

<?php endif; ?>
