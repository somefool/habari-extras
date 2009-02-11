<?php $theme->display('header');?>


<div id="crontab" class="container settings">
	<h2><?php _e('%s Cron Job', array($cron->name), 'crontabmanager'); ?></h2>

	<?php echo $form; ?>
</div>

<?php $theme->display('footer');?>
