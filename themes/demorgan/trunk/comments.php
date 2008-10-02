<!-- comments -->
		<div id="comments">
<?php
	if ($post->comments->moderated->count) {
?>
			<h2><?php printf(_n('%1$s Response', '%1$s Responses', $post->comments->moderated->count, 'demorgan'), '<span class="comment-count">' . $post->comments->moderated->count . '</span>'); ?></h2>
<?php
		//Show Comments
		if ($post->comments->comments->moderated->count) {
?>
			<ul id="comment-list">
<?php
			$i = 1;
			foreach ($post->comments->comments->moderated as $comment) {
				$class = 'comment';
				if ($comment->status === Comment::STATUS_UNAPPROVED) {
					$class .= '-unapproved';
				}
				//Using email to identify author
				if ($comment->email === $post->author->email) {
					$class .= ' author';
				} else {
					$class .= ' guest';
				}
				//Even or Odd
				$class .= ($i % 2 === 0) ? ' even' : ' odd' ;
				$i++;
?>
				<li id="comment-<?php echo $comment->id; ?>" class="<?php echo $class; ?>">
					<div class="comment-content">
						<?php echo $comment->content_out; ?>
					</div>
					<div class="comment-author vcard">
						<?php $theme->gravatar($comment); ?>
<?php 			if ($comment->url) { ?>
						<a class="url" href="<?php echo $comment->url; ?>" rel="external">
<?php 			} ?>
							<span class="fn n"><?php echo $comment->name; ?></span>
<?php			 if ($comment->url) { ?>
						</a>
<?php 			} ?>
						<?php _e('on', 'demorgan'); ?>
						<a class="comment-permalink" href="<?php echo $post->permalink; ?>#comment-<?php echo $comment->id; ?>" title="<?php _e('Permanent Link to this comment', 'demorgan'); ?>" rel="bookmark">
							<abbr class="comment-date published" title="<?php echo $comment->date->out(HabariDateTime::ISO8601); ?>"><?php echo $comment->date->out('F j, Y ‒ g:i a'); ?><?php if ($comment->status === Comment::STATUS_UNAPPROVED) { ?> <em><?php _e('In moderation', 'demorgan'); ?></em><?php } ?></abbr>
						</a>
					</div>
				</li>
<?php		} ?>
			</ul>
<?php
		}


		//Show Pingbacks
		if ($post->comments->pingbacks->approved->count) {
?>
			<ul id="pingback-list">
<?php
			foreach ($post->comments->pingbacks->approved as $pingback) {
?>
				<li id="pingback-<?php echo $pingback->id; ?>" class="pingback">
					<a href="<?php echo $pingback->url; ?>" rel="external"><?php echo $pingback->name; ?></a>
					<?php _e('on', 'demorgan'); ?>
					<a class="pingback-permalink" href="<?php echo $post->permalink; ?>#pingback-<?php echo $pingback->id; ?>" title="<?php _e('Permanent Link to this pingback', 'demorgan'); ?>" rel="bookmark">
						<abbr class="pingback-date published" title="<?php echo $pingback->date_iso; ?>"><?php echo $pingback->date_out; ?></abbr>
					</a>
				</li>
<?php 		} ?>
			</ul>
<?php 	}
	} ?>
		</div>
<!-- /comments -->
<?php if (!$post->info->comments_disabled) { $theme->display('commentform'); } ?>
