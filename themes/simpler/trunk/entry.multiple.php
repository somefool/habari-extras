<?php include 'header.php'; ?>
		<div id="content">
<?php foreach ( $posts as $post ) { ?>
			<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?> post">
				<h2 class="title""><a href="<?php echo $post->permalink; ?>" rel="bookmark" title="Permanent Link to <?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
				<div class="entry">
					<?php echo $post->content_out; ?>
				</div>
				<p class="meta"><?php echo $post->pubdate_out; ?><?php if ( is_array( $post->tags ) ) { ?> | <?php echo $post->tags_out; ?><?php } ?> <?php if ( ! $post->info->comments_disabled ) { ?> | <a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a><?php } ?><?php if ( $user ) { ?> | <a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="Edit post">Edit</a><?php } ?></p>
			</div>
<?php } ?>
			<div class="paging">
				<?php echo Utils::page_selector( $page, Utils::archive_pages( $posts->count_all() ) ); ?>
			</div>
<?php include 'footer.php'; ?>
