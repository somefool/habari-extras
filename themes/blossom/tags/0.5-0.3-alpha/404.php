<?php include 'header.php'; ?>

<div id="primary" class="twocol-stories">
	<div class="inside">

		<div class="story first">
			<h3>Unfortunately, I couldn't find the post you're looking for.</h3>
		</div>

		<div class="story">
			<?php
				$has_tags = ( count($tags) > 0 );
				$has_pages = ( count($pages) > 0 );
			?>
			<?php if ( $has_tags || $has_pages ): ?>
				<h3>Here are some other things to try.</h3>
				<?php if ( $has_tags ): ?>
					<h4>Tags</h4>
					<?php
						foreach ( $tags as $tag ) {
							echo "<a href=\"{$habari}/tag/{$tag->slug}/\">{$tag->tag}</a> ";
						}
					?>
				<?php endif; ?>
				<?php if ( $has_pages ): ?>
					<h4>Pages</h4>
					<?php
						foreach ( $pages as $page ) {
							echo "<a href=\"{$habari}/{$page->slug}/\">{$page->title}</a>";
						}
					?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="clear"></div>

	</div>
</div>

<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
