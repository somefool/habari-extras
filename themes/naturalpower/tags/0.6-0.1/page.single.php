<?php include'header.php'; ?>
	
	<div id="content_wrap">
	
		<div id="content">

			<h2><?php echo $post->title; ?></h2>
			<?php echo $post->content_out; ?>
			<?php if ( $user ) { ?>
| <a href="<?php echo $post->editlink; ?>" title="Edit post">Edit</a> |
<?php } ?>

		</div>
	
		<?php include'sidebar.php'; ?>
	
	</div>

	<?php include'footer.php'; ?>