<?php include 'header.php'; ?>

			<div id="main-posts">
				<?php $post=reset($posts); ?>
				<div class="<?php echo $post_class?>">
				<?php if ( is_array( $post->tags ) ) :?>
					<div class="post-tags">
						<?php echo $post->tags_out;?>
					</div>
				<?php endif; ?>
					<div class="post-title">
						<h3>
							<a href="<?php echo $post->permalink; ?>"title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a>
						</h3>
					</div>
					<div class="post-sup">
						<span class="post-date">
							<?php echo $post->pubdate_out; ?>
						</span>
						<span class="post-comments-link">
							<a href="<?php echo $post->permalink.'#comment-form'; ?>" title="Comments on this post"><?php $theme->post_comments_link( $post, 'No Comments', '%s Comment', '%s Comments' ); ?></a>
						</span>
						<br class="clear">
					</div>
					<div class="post-entry">
						<?php echo $post->content_out; ?>
					</div>
					<div class="post-footer">
						<?php if ( $user ) : ?>
							<span class="post-edit">
								<a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>"title="Edit post">Edit</a>
							</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<div id="top-secondary">
			<?php include'sidebar.php' ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="page-bottom">
	<div id="wrapper-bottom">
		<div id="bottom-primary">
			<div id="prev-posts">
			<?php while($post=next($posts)) : ?>
				<div class="prev-post">
					<div class="prev-post-title">
						<h2>
							<a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a>
						</h2>
					</div>
					<div class="prev-post-excerpt">
						<p>
							<?php echo $post->content_excerpt; ?>
							<a href="<?php echo $post->permalink; ?>" title="Continue reading <?php echo $post->title; ?>"><img src="<?php Site::out_url( 'theme' ); ?>/images/arrow.png" alt="more"></a>
						</p>
					</div>
				</div>
			<?php endwhile; ?>
			</div>
			<div id="prev-posts-footer">
				<span class="nav-next"><?php $theme->prev_page_link('Newer Posts'); ?></span>
				<span class="nav-prev"><?php $theme->next_page_link('Older Posts'); ?></span>
				<br class="clear">
			</div>
			<?php //$theme->prevnext($page, Utils::archive_pages($posts->count_all())); ?>
			<?php $theme->display_archives() ;?>
			
<?php include 'footer.php'; ?>
