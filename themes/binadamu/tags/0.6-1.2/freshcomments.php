			<li id="widget-freshcomments" class="widget">
				<h3><?php _e('Comments', 'binadamu') ?></h3>
				<ul>
				<?php foreach ($freshcomments as $post): ?>
					<li>
						<a href="<?php echo $post['post']->permalink; ?>" rel="bookmark" class="comment-entry-title"><?php echo $post['post']->title_out; ?></a>
						<a href="<?php echo $post['post']->permalink; ?>#comments" class="comment-count" title="<?php printf(_n('%1$d comment', '%1$d comments', $post['post']->comments->approved->comments->count, 'binadamu'), $post['post']->comments->approved->comments->count); ?>"><?php echo $post['post']->comments->approved->comments->count; ?></a>
						<ul class="comment-authors">
						<?php foreach ($post['comments'] as $comment): ?>
							<li><a style="color:<?php echo $comment['color']; ?>" href="<?php echo $comment['comment']->post->permalink; ?>#comment-<?php echo $comment['comment']->id; ?>" title="<?php printf(_t('Posted at %1$s', 'binadamu'), HabariDateTime::date_create($comment['comment']->date)->get('g:m a \o\n F jS, Y')); ?>"><?php echo $comment['comment']->name; ?></a></li>
						<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach; ?>
				</ul>
			</li>
