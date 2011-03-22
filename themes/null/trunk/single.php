<?php include 'header.php'; ?>
		<div id="content" role="main">
			<div id="posts" class="hfeed">
				<article id="<?php echo Post::type_name( $post->content_type ); ?>-<?php echo $post->slug; ?>" class="<?php echo $post->css_class(); ?>">
					<header>
						<h1 class="entry-title"><a href="<?php echo $post->permalink; ?>" rel="bookmark"><?php echo $post->title_out; ?></a></h1>
					</header>
					<section class="entry-content">
						<?php echo $post->content_out; ?>
					</section>
					<footer>
						<ul>
							<li class="entry-author">
								<address class="author vcard"><a href="<?php echo Utils::htmlspecialchars( URL::get( 'display_entries', array( 'user_id' => $post->author->id ) ) ); ?>" class="url fn nickname"><?php echo Utils::htmlspecialchars( $post->author->displayname ); ?></a></address>
							</li>
							<li class="entry-tags">
								<?php echo $post->tags_list; ?>
							</li>
							<li class="entry-date">
								<time datetime="<?php $post->pubdate->out( 'Y-m-d\TH:i:sP' ); ?>" pubdate><?php $post->pubdate->out( ); ?></time>
							</li>
							<?php if ( $post->comments->count || !$post->info->comments_disabled ) : ?>
							<li class="entry-comments-link">
								<a href="<?php echo $post->permalink; ?>#comment-public"><?php printf( _n( '%d Comment', '%d Comments', $post->comments->comments->moderated->count ), $post->comments->comments->moderated->count ); ?></a>
							</li>
							<?php endif; ?>
							<?php if ( $loggedin ) : ?>
							<li class="entry-edit-link">
								<a href="<?php echo $post->editlink; ?>"><?php _e( 'Edit' ); ?></a>
							</li>
							<?php endif; ?>
						</ul>
					</footer>
					<nav class="pagination">
						<ul>
							<?php if ( $prev = $post->descend( ) ) : ?>
							<li class="nav-older"><a href="<?php echo $prev->permalink; ?>">&larr; <?php echo Utils::htmlspecialchars( $prev->title ); ?></a></li>
							<?php endif; ?>
							<?php if ( $next = $post->ascend( ) ) : ?>
							<li class="nav-newer"><a href="<?php echo $next->permalink; ?>"><?php echo Utils::htmlspecialchars( $next->title ); ?> &rarr;</a></li>
							<?php endif; ?>
						</ul>
					</nav>
						<?php if ( count( $theme->get_blocks( 'comment_form', 0, $theme ) ) ): ?>
							<?php include 'comment_form.area.php'; ?>
						<?php else: ?>
							<?php include 'comments.php'; ?>
						<?php endif; ?>
				</article>
			</div>
		</div>
<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
