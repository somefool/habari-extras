<?php include 'header.php'; ?>
	<div class="post" id="post-<?php echo $post->id; ?>">
		<h2 class="post_title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
		<div class="post_content">
			<?php echo $post->content_out; ?>
		</div>
	</div>
<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>