<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
	<div id="content" class="with_title">
		<h3 class="page_title">Posts tagged with <?php echo $tag_text; ?></h3>
		<?php foreach ( $posts as $post ) { ?>
		<div class="post" id="post-<?php echo $post->id; ?>">
			<div class="post_head">
				<h2><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
				<p class="post_head_meta"><?php echo $post->author->info->displayname; ?> &middot; <?php echo $post->pubdate_out; ?></p>
			</div>
			<div class="post_content">
				<?php echo $post->content_out; ?>
			</div>
			<div class="post_meta">
				<p>This entry was posted on <span class="date"><?php echo $post->pubdate_out; ?></span>
				<?php if ( is_array( $post->tags ) ) { ?>and is filed under <?php echo $post->tags_out; ?><?php } ?>.
				There is <a href="<?php echo $post->permalink; ?>#comments" title="Comments to this post"><?php echo $post->comments->approved->count; ?>
				<?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a> on this post.</p>
			</div>
		</div>
		<?php } ?>
		<div class="page_navigation">
			<p>Page:<?php echo Utils::page_selector( $page, Utils::archive_pages( $posts->count_all() ), null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?></p>
		</div>
	</div>
<?php include 'footer.php'; ?>