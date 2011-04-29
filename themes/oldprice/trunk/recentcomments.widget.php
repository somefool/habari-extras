			<li id="widget-recentcomments" class="widget widget_comments">
				<h3><?php _e('Comments'); ?></h3>
				<ul>
				<?php
					$entries= DB::get_results( 'SELECT {posts}.* FROM {comments}, {posts} WHERE {comments}.status = ? AND {comments}.type = ? AND {posts}.id = post_id GROUP BY post_id ORDER BY {comments}.date DESC, post_id DESC LIMIT 6', array( Comment::STATUS_APPROVED, Comment::COMMENT ), 'Post' );
					foreach( $entries as $entry ) {
				?>
					<li>
						<a href="<?php echo $entry->permalink; ?>" rel="bookmark" class="comment-entry-title"><?php echo $entry->title; ?></a>
						<a href="<?php echo $entry->permalink; ?>#comments" class="comment-count" title="<?php printf( _n('%1$d comment', '%1$d comments', $entry->comments->approved->comments->count), $entry->comments->approved->comments->count ); ?>"><?php echo $entry->comments->approved->comments->count; ?></a>
						<div class="comment-authors">
						<?php
							$comments= DB::get_results( 'SELECT * FROM {comments} WHERE post_id = ? AND status = ? AND type = ? ORDER BY date DESC LIMIT 5;', array( $entry->id, Comment::STATUS_APPROVED, Comment::COMMENT ), 'Comment' );
							$tcount = 0;
							foreach( $comments as $comment) {
								$tcount++;
								$comment_time= strtotime($comment->date);
								$time_span= round( ($_SERVER['REQUEST_TIME'] - $comment_time) / (30*24*60*60), 2 );
								$time_span= $time_span > 0.8 ? 0.2 : 1 - $time_span;
						?>
							<span><a href="<?php echo $comment->post->permalink; ?>#comment-<?php echo $comment->id; ?>" title="<?php printf(_t('Posted at %1$s'), date('g:m a \o\n F jS, Y', $comment_time)); ?>"><?php echo $comment->name; ?></a></span>
							<?php if($tcount!=5) echo ","; else echo "..."; ?>
						<?php
							}
						?>
						</div>
					</li>
				<?php
					}
				?>
				</ul>
			</li>