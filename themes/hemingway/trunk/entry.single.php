<!-- entry.single -->
<?php include 'header.php'; ?>

	<div id="primary" class="single-post">
		<div class="inside">
    	<?php $single= true; ?>
			<div class="primary">
				<h1><?php echo $post->title; ?></h1>
        <?php echo $post->content_out; ?>
			</div>
			<hr class="hide" />
			<div class="secondary">
				<h2>About this entry</h2>
				<div class="featured">
					<p>You&rsquo;re currently reading &ldquo;<?php echo $post->title; ?>&rdquo;, an entry on <?php echo Options::out( 'title' ) ?></p>
					<dl>
						<dt>Published:</dt>
						<dd><?php echo $post->pubdate->out(); ?></dd>
					</dl>
					<dl>
						<dt>Tags:</dt>
						<dd><?php echo $post->tags_out; ?></dd>
					</dl>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<!-- end primary -->
	
	<hr class="hide" />
	<div id="secondary">
		<div class="inside">
      
      <?php if ( $post->info->comments_disabled ) { ?>
				<div class="comment-head">
					<h2>Comments are closed</h2>
					<span class="details">Comments are currently closed on this entry.</span>
				</div>	
      <?php } else { ?>
				<div class="comment-head">
					<h2><?php echo ($post->comments->approved->count == 0 ? 'No' : $post->comments->approved->count ); ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></h2>
					<span class="details"><a href="#comment-form">Jump to comment form</a> | <a href="<?php URL::out( 'atom_feed_comments' ); ?>">Comments rss</a></span>
				</div>
      <?php } ?>
			
      <?php include_once( 'comments.php' ); ?>
			
		</div>
	</div>

<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
<!-- /entry.single -->
