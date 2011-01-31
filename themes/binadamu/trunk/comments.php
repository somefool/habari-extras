<!-- comments -->
		<div id="comments">
<?php
	if ($post->comments->moderated->count) {
?>
			<h2><span class="comment-count"><?php printf(_n('%1$d Response', '%1$d Responses', $post->comments->moderated->count, 'binadamu'), $post->comments->moderated->count); ?></h2>
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
					<ul class="comment-meta vcard">
						<?php $theme->gravatar($comment); ?>
						<li class="comment-author">
<?php 			if ($comment->url) { ?>
							<a class="url" href="<?php echo $comment->url_out; ?>" rel="external">
<?php 			} ?>
								<cite class="fn n"><?php echo $comment->name; ?></cite>
<?php			if ($comment->url) { ?>
							</a>
<?php 			} ?>
						</li>
						<li class="comment-date">
							<a class="comment-permalink" href="<?php echo $post->permalink; ?>#comment-<?php echo $comment->id; ?>" title="<?php _e('Permanent Link to this comment', 'binadamu'); ?>" rel="bookmark">
								<abbr class="comment-date published" title="<?php echo $comment->date->out(HabariDateTime::ISO8601); ?>"><?php echo $comment->date->out('F j, Y ‒ g:i a'); ?><?php if ($comment->status === Comment::STATUS_UNAPPROVED) { ?> <em><?php _e('In moderation', 'binadamu'); ?></em><?php } ?></abbr>
							</a>
						</li>
					</ul>
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
				<?php
					printf(_t('%1$s on %2$s', 'binadamu'),
						'<a href="' . $pingback->url . '" rel="external">' . $pingback->name . '</a>',
						'<a class="pingback-permalink" href="' . $post->permalink . '#pingback-' . $pingback->id . '" title="' . _t('Permanent Link to this pingback', 'binadamu') . '" rel="bookmark">
							<abbr class="pingback-date published" title="' . $pingback->date->get('iso') . '">' . $pingback->date->out() . '</abbr>
						</a>');
				?>
				</li>
<?php 		} ?>
			</ul>
<?php 	}
	} ?>
		</div>
<!-- /comments -->
<?php $post->comment_form()->out(); ?>
