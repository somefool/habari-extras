<?php $theme->display('header');?>

<div class="create">

	<?php echo $form; ?>

</div>

<div id="options_view" class="container settings">
	<div style="float:right">
		<?php
			_e( 'Options counts: Total [<strong>%d</strong>] Inactive [<strong>%d</strong>]',  array( $this->opts_count_total, $this->opts_count_inactive ) );
			_e( ' Core deletes: [<strong>%s</strong>]',  array( (empty($opts_local['allow_delete_core'])  ? 'disabled' : 'enabled') ) );
			_e( ' Other deletes: [<strong>%s</strong>]', array( (empty($opts_local['allow_delete_other']) ? 'disabled' : 'enabled') ) );
		?>
	</div>

	<h2><?php _e('All known Habari options'); ?></h2>
	<?php 
	foreach($options as $option_name => $option):
		$option_value = Utils::htmlspecialchars($option['value']);
	?>

	<div class="item plugin clear" id="title">
		<div class="head">
			<strong><?php echo $option_name; ?></strong>

			<ul class="dropbutton">

				<li><a href="<?php URL::out('admin', array('page'=>'options_edit', 'action'=>'edit', 'option_name'=>$option_name)); ?>"><?php _e('Edit'); ?></a></li>

				<?php if ( $option['delete_allowed'] ): ?>
				<li><a href="<?php URL::out('admin', array('page'=>'options_view', 'action'=>'delete', 'option_name'=>$option_name)); ?>"><?php _e('Delete'); ?></a></li>
				<?php endif; ?>

				<!--
				<li><a href="<?php URL::out('admin', array('page'=>'options_view', 'action'=>'delete_group', 'option_name'=>$option_name)); ?>"><?php _e('Edit Group'); ?></a></li>
				<li><a href="<?php URL::out('admin', array('page'=>'options_view', 'action'=>'delete_group', 'option_name'=>$option_name)); ?>"><?php _e('Delete Group'); ?></a></li>
				-->
			</ul>

		</div>
		
		<ul class="description pct50">
			<li><?php _e('Genre:');       ?> <?php _e( '%s', array( $option['genre'] ) );  ?></li>
			<li><?php _e('Plugin Name:'); ?> <?php _e( '%s', array( $option['plugin_name'] ) );  ?></li>
			<li><?php _e('Value:'      ); ?> <?php _e( '%s', array( strlen($option_value) > 76 ? substr($option_value, 0, 76) . ' ... ' : $option_value) ); ?></li>
			<li><?php _e('Type:'       ); ?> <?php _e( '%s', array( ($option['type'] === '1' ? 'serialized' : 'unserialized') ) ); ?></li>
			<li><?php _e('Active:'     ); ?> <?php _e( '<strong>%s</strong>', array( ($option['active'] === 'no' ? 'no' : $option['active']) ) ); ?></li>
		</ul>
	</div>

	<?php endforeach; ?>
</div>

<?php $theme->display('footer');?>
