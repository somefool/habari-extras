<?php include 'header.php'; ?>
<!-- home -->

<div id="primary" class="twocol-stories">
	<div class="inside">
		<?php
		$first = true;
		foreach ( $top_posts as $post ):
		?>
			<div class="story<?php if ($first) echo " first" ?>">
				<h3>
					<a href="<?php echo $post->permalink ?>" rel="bookmark" title="Permanent Link to <?php echo $post->title; ?>">
						<?php echo $post->title; ?>
					</a>
				</h3>
				<?php echo $post->content_excerpt; ?>
				<div class="details">
					Posted at <?php echo $post->pubdate_out ?> |
					<a href="<?php echo $post->permalink; ?>" title="Comments on this post">
						<?php echo $post->comments->approved->count; ?> <?php echo _n( 'comment', 'comments', $post->comments->approved->count ); ?>
					</a>
					<?php
						if ( is_array($post->tags) ) {
							echo " | Filed Under: {$post->tags_out}";
						}
					?>
				</div>
			</div>
		<?php
		$first = false;
		endforeach;
		?>
		<div class="clear"></div>
	</div>
</div> <!-- /#primary -->

<!-- /home -->

<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
