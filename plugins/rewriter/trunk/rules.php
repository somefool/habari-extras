<?php include( HABARI_PATH . '/system/admin/header.php'); ?>

<div class="container rules">

	<div class="item">
		<div class="head clear">

		<span class="name pct20"><?php _e('Name'); ?></span>
		<span class="regex pct25"><?php _e('Regular Expression'); ?></span>
		<span class="action pct15"><?php _e('Action'); ?></span>
		<span class="priority pct10"><?php _e('Priority'); ?></span>
		<span class="description pct20"><?php _e('Description'); ?></span>
		
		</div>
	</div>
	
	<form name="rules" id="rules" method="post" action="<?php URL::get( 'admin', 'page=rules' ); ?>">
	<?php foreach( $rules as $rule) { ?>
	<div class="item rule view clear" style="padding-top:3px; padding-bottom:2px">
		<span class="name pct20"><?php echo $rule->name; ?></span>
		<span class="regex pct25"><?php echo $rule->parse_regex; ?></span>
		<span class="action pct15"><?php echo $rule->action; ?></span>
		<span class="priority pct10"><?php echo $rule->priority; ?></span>
		<span class="description pct20 minor"><span><?php echo $rule->description; ?></span></span>
	</div>
	<div class="item rule change clear" style="padding-top:3px; padding-bottom:2px">
		<span class="name pct20"><input name="names[<?php echo $rule->name; ?>]" type="text" value="<?php echo $rule->name; ?>"></span>
		<span class="regex pct25"><input name="regexes[<?php echo $rule->name; ?>]" type="text" value="<?php echo $rule->parse_regex; ?>"></span>
		<span class="action pct15"><input name="actions[<?php echo $rule->name; ?>]" type="text" value="<?php echo $rule->action; ?>"></span>
		<span class="priority pct10"><input name="priorities[<?php echo $rule->name; ?>]" type="text" value="<?php echo $rule->priority; ?>"></span>
		<span class="description pct20 minor"><input name="descriptions[<?php echo $rule->name; ?>]" type="text" value="<?php echo $rule->description; ?>"></span></span>
	</div>
	<?php } ?>
	
	<div class="item">
		<div class="foot clear">
		
		<input type="submit" name="submit" id="submit" class="apply button" value="<?php _e('Apply'); ?>">
		
		</div>
	</div>
	
	</form>
	
</div>

<?php include( HABARI_PATH . '/system/admin/footer.php'); ?>
