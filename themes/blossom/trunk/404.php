<?php include 'header.php'; ?>

<div id="primary" class="twocol-stories">
	<div class="inside">

		<div class="story first">
			<h3>Unfortunately, I couldn't find the post you're looking for.</h3>
		</div>

		<div class="story">
			<h3>Here are some other things to try.</h3>
			<h4>Tags</h4>
			<?php foreach ( $tags as $tag ): ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/tag/<?php echo $tag->tag_slug; ?>/"><?php echo $tag->tag_text; ?></a>
			<?php endforeach; ?>
			<h4>Pages</h4>
			<?php foreach ( $pages as $page ): ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/<?php echo $page->slug; ?>/"><?php echo $page->title; ?></a>
			<?php endforeach; ?>
			</div>
		<div class="clear"></div>

	</div>
</div>

<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
