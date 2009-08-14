<?php include 'header.php'; ?>
<!-- entry.single -->
	<div id="primary" class="single-post">
		<div class="inside">
			<div class="primary">
				<h1><?php echo $post->title; ?></h1>
				<?php echo $post->content_out; ?>
			</div>
			<hr class="hide" />
			<div class="secondary">
				<div>
					<b class="spiffy">
					<b class="spiffy1"><b></b></b>
					<b class="spiffy2"><b></b></b>
					<b class="spiffy3"></b>
					<b class="spiffy4"></b>
					<b class="spiffy5"></b>
					</b>
					<div class="spiffy_content">
						<div class="featured">
							<h2>About this entry</h2>
							<p>You&rsquo;re currently reading &ldquo;<?php echo $post->title; ?>&rdquo;, an entry on <?php Options::out( 'title' ) ; ?></p>
							<dl>
								<dt>Published:</dt>
								<dd><?php echo Format::nice_date($post->pubdate, 'Y.j.n') ?> / <?php echo Format::nice_date($post->pubdate, 'ga') ?></dd>
							</dl>
							<?php if ( is_array($post->tags) ) : ?>
							<dl>
								<dt>Tags:</dt>
								<dd><?php echo $post->tags_out ?></dd>
							</dl>
							<?php endif; ?>
							<?php if ( $loggedin ) : ?>
							<dl>
								<dt>Edit:</dt>
								<dd><a href="<?php URL::out( 'admin', 'page=publish&id=' . $post->id ); ?>" title="Edit post">Edit this entry.</a></dd>
							</dl>
							<?php endif; ?><br/>

						</div>
					</div>
					<b class="spiffy">
					<b class="spiffy5"></b>
					<b class="spiffy4"></b>
					<b class="spiffy3"></b>
					<b class="spiffy2"><b></b></b>
					<b class="spiffy1"><b></b></b>
					</b>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<!-- [END] #primary -->
	<hr class="hide" />
	<div id="secondary">
		<div class="inside">
			<?php if ($post->comments->approved->count > 0): ?>
				<div class="comment-head">
					<h2><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></h2>
					<span class="details"><a href="#comment-form">Jump to comment form</a> | <a href="#what-is-comment-rss" class="help">[?]</a></span>
				</div>
			<?php endif; ?>

			<?php include 'comments.php'; ?>

		</div>
	</div>
<!-- /entry.single -->
<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
