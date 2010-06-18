<?php $theme->display('header');?>

<div id="options_edit" class="container settings">
	<h2><?php _e( 'Option name: [%s]', array($option['name'] ) ); ?></h2>
	<h3><?php _e( 'Genre:  [%s]', array($option['genre'] ) ); ?></h3>
	<h3><?php _e( 'Plugin: [%s]', array($option['plugin_name'] ) ); ?></h3>

	<?php echo $form; ?>
</div>

<?php $theme->display('footer');?>
