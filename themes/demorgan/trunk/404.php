<?php $theme->display('header'); ?>
<!-- 404 -->
	<div id="content">
		<div class="error">
			<h1><?php _e('Error!', 'demorgan'); ?></h1>
			<p><?php _e('The requested post was not found.', 'demorgan'); ?></p>
		</div>
	</div>
	<hr />
<!-- /404 -->
<?php $theme->display('sidebar'); ?>
<?php $theme->display('footer'); ?>
