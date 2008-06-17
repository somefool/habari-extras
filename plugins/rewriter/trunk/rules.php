<?php include( HABARI_PATH . '/system/admin/header.php'); ?>

<div class="container rules">

	<div class="item">
		<div class="head clear">

		<span class="name pct20"><?php _e('Name'); ?></span>
		<span class="regex pct30"><?php _e('Regular Expression'); ?></span>
		<span class="action pct10"><?php _e('Action'); ?></span>
		<span class="priority pct10"><?php _e('Priority'); ?></span>
		<span class="description pct20"><?php _e('Description'); ?></span>
		
		</div>
	</div>
	
	<?php foreach( $rules as $rule) { ?>
	<div class="item rule clear" style="padding-top:3px; padding-bottom:2px">
		<span class="name pct20"><?php echo $rule->name; ?></span>
		<span class="regex pct30"><?php echo $rule->parse_regex; ?></span>
		<span class="action pct10"><?php echo $rule->action; ?></span>
		<span class="priority pct10"><?php echo $rule->priority; ?></span>
		<span class="description pct20 minor"><span><?php echo $rule->description; ?></span></span>
	</div>
	<?php	} ?>
</div>

<?php include( HABARI_PATH . '/system/admin/footer.php'); ?>
