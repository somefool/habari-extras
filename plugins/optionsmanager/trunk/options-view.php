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
</div>

<div class="container">
	<h2><?php _e('All known Habari options'); ?></h2>

		<div class="head clear"> 
			<span class="title pct25">Name</span> 
			<span class="title pct5">Genre</span> 
			<span class="title pct10">Plugin</span> 
			<span class="title pct5">Ser?</span> 
			<span class="title pct10">Active?</span> 
			<span class="title pct30">Value</span> 
			<span class="title pct15">Actions</span> 
		</div>

		<div class="manage view_options">
		<?php foreach($options as $option_name => $option): ?>
		<div class="item clear">
			<span class="message pct25 minor less">
				<strong><?php echo substr ( $option_name, 0, 35 ); ?></strong>
			</span>
			<span class="message pct25 major more">
				<strong><?php echo $option_name; ?></strong>
			</span>
			<span class="title pct5 minor">
				<?php _e( '%s', array( $option['genre'] ) );  ?>
			</span>
			<span class="title pct10 minor">
				<?php _e( '%s', array( $option['plugin_name'] ) );  ?>
			</span>
			<span class="title pct5 minor">
				<?php _e( '%s', array( ($option['type'] === '1' ? 'yes' : 'no') ) ); ?>
			</span>
			<span class="title pct10 minor">
				<?php _e( '%s', array( ($option['active'] === 'no' ? 'no' : $option['active']) ) ); ?>
			</span>
			<span class="message pct30 minor less">
				<?php echo substr( Utils::htmlspecialchars($option['value']), 0, 60 ); ?>
			</span>
			<span class="message pct30 minor more">
				<?php if ( isset( $option['value_unserialized'] ) && count( $option['value_unserialized'] ) > 0): ?>
				<div class="container settings">
					<h2>Unserialized version of the value</h2>
					<ul>
					<?php foreach ( $option['value_unserialized'] as $name => $value ): ?>
						<li class="item clear">
							<span class="title pct20 minor"><?php echo $name; ?></span>
							<span class="title pct80 minor"><?php echo Utils::htmlspecialchars($value); ?></span>
						</li>
					<?php endforeach; ?>
					</ul>
				</div>
				<?php 
					else:
						echo Utils::htmlspecialchars($option['value']);
					endif; 
				?>
			</span>
			<span class="select pct15 minor">
				<ul class="dropbutton">
					<li><a href="<?php URL::out('admin', array('page'=>'options_edit', 'action'=>'edit', 'option_name'=>$option_name)); ?>"><?php _e('Edit'); ?></a></li>
					<?php if ( $option['delete_allowed'] ): ?>
					<li><a href="<?php URL::out('admin', array('page'=>'options_view', 'action'=>'delete', 'option_name'=>$option_name)); ?>"><?php _e('Delete'); ?></a></li>
					<?php endif; ?>
				</ul>
			</span>
		</div>
	<?php endforeach; ?>
	</div>
</div>

<?php $theme->display('footer');?>
