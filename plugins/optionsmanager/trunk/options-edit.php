<?php $theme->display('header');?>

<div id="options_edit" class="container settings">
	<h2><?php _e( 'Option name: [%s]', array($option['name'] ) ); ?></h2>
	<h3><?php _e( 'Genre:  [%s]', array($option['genre'] ) ); ?></h3>
	<h3><?php _e( 'Plugin: [%s]', array($option['plugin_name'] ) ); ?></h3>

	<?php echo $form; ?>
</div>

<?php if ( isset( $option['value_unserialized'] ) && count( $option['value_unserialized'] ) > 0): ?>
<div class="container settings">
	<h2>Unserialized version of the value</h2>
	<ul>
	<?php foreach ( $option['value_unserialized'] as $name => $value ): ?>
		<li class="item clear">
			<span class="message pct20 minor"><?php echo $name; ?></span>
			<span class="message pct80 minor"><?php echo $value; ?></span>
		</li>
	<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>

<?php $theme->display('footer');?>
