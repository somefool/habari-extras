<?php if ( $post->comments->comments->moderated->count ) : ?>
				<aside id="comments" class="hfeed">
					<h1><?php printf( _n( '%d Comment', '%d Comments', $post->comments->comments->moderated->count ), $post->comments->comments->moderated->count ); ?></h1>
					<?php foreach ( $post->comments->comments->moderated as $comment ) : ?>
					<article id="comment-<?php echo $comment->id; ?>" class="<?php echo $comment->css_class(); ?>">
						<section>
							<?php echo $comment->content_out; ?>
							<?php if ( $comment->status == Comment::STATUS_UNAPPROVED ) : ?>
							<p class="comment-notice"><em><?php _e( 'Your comment is awaiting moderation' ); ?></em></p>
							<?php endif; ?>
						</section>
						<footer>
							<ul>
								<?php if ( $comment->gravatar ): ?>
								<li class="comment-avatar">
									<img src="<?php echo $comment->gravatar; ?>" width="40" height="40" />
								</li>
								<?php endif; ?>
								<li class="comment-author">
									<?php if ( $comment->url ): ?>
									<address class="vcard author"><a href="<?php echo $comment->url_out; ?>" class="url fn nickname" rel="external"><?php echo $comment->name_out; ?></a></address>
									<?php else: ?>
									<address class="vcard author"><span class="fn nickname"><?php echo $comment->name_out; ?></span></address>
									<?php endif; ?>
								</li>
								<li class="comment-pubdate">
									<time datetime="<?php $comment->date->out( 'Y-m-d\TH:i:sP' ); ?>" pubdate><?php $comment->date->out( ); ?></time>
								</li>
								<?php if ( $loggedin ) : ?>
								<li class="comment-edit-link">
									<a href="<?php Site::out_url( 'admin', true ); ?>comment?id=<?php echo $comment->id; ?>">Edit</a>
								</li>
								<?php endif; ?>
							</ul>
						</footer>
					</article>
					<?php endforeach; ?>
				</aside>
				<?php endif; ?>
				<?php if ( $post->comments->pingbacks->approved->count ) : ?>
				<aside id="pingbacks" class="hfeed">
					<h1><?php printf( _n( '%d Pingback', '%d Pingbacks', $post->comments->pingbacks->approved->count), $post->comments->pingbacks->approved->count ); ?></h1>
					<?php foreach ( $post->comments->pingbacks->approved as $pingback ): ?>
					<ol>
						<li id="pingback-<?php echo $pingback->id; ?>" class="<?php echo $pingback->css_class(); ?>">
							<cite class="pingback-title"><a href="<?php echo $pingback->url; ?>" rel="external"><?php echo $pingback->name_out; ?></a></cite>
							<?php _e( 'on' ); ?>
							<a class="pingback-link" href="<?php echo $post->permalink; ?>#pingback-<?php echo $pingback->id; ?>" ><time datetime="<?php $pingback->date->out( 'Y-m-d\TH:i:sP' ); ?>" pubdate><?php echo $pingback->date->out( ); ?></time></a>
							<?php if ( $loggedin ) : ?>
							<a class="pingback-edit-link" href="<?php Site::out_url( 'admin', true ); ?>comment?id=<?php echo $pingback->id; ?>">Edit</a>
							<?php endif; ?>
						</li>
					</ol>
					<?php endforeach; ?>
				</aside>
				<?php endif; ?>
				<?php if ( !$post->info->comments_disabled ) : ?>
					<?php $post->comment_form( )->out( ); ?>
				<?php endif; ?>
