<?php include'header.php'; ?>
	
	<div id="content_wrap">
	
		<div id="content">

			<h2><?php echo $post->title; ?></h2>
			<?php echo $post->content_out; ?>
			<?php if ( $user ) { ?>
| <a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="Edit post">Edita</a> |
<?php } ?>

		</div>
	
		<?php include'sidebar.php'; ?>
	
	</div>

	<?php include'footer.php'; ?>