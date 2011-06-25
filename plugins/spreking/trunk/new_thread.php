<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); } ?>
<?php $theme->display ('header'); ?>
<!-- entry.multiple -->
	<div class="content">
	<div id="primary">
		<div id="primarycontent">
			<h3><?php echo _t('Create New Thread In'); ?> <em><?php echo $forum->title_out; ?></em></h3>
			<?php $form->out(); ?>
		</div>

	</div>

	<hr>

	<div class="secondary">

<?php $theme->display ('sidebar'); ?>

	</div>

	<div class="clear"></div>
	</div>
<!-- /entry.multiple -->
<?php $theme->display ( 'footer'); ?>
