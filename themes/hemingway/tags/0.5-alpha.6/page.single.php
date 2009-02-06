<!-- page.single -->
<?php include 'header.php'; ?>

	<div id="primary">
		<div class="inside">
		<h1><?php echo $post->title; ?></h1>
		<?php echo $post->content_out; ?>
		</div>
	</div>
	
<?php include 'sidebar.php'; ?>

<?php include 'footer.php'; ?>
<!-- /page.single -->
