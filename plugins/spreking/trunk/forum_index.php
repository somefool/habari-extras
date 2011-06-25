<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); } ?>
<?php $theme->display ('header'); ?>
<!-- entry.multiple -->
	<div class="content">
	<div id="primary">
		<div id="primarycontent">
			<h3><?php echo _t('Discussion Forums'); ?></h3>
			<ul>
			<?php foreach ( $forums as $forum ) { ?>
				<li>
					<a href="<?php echo $forum->permalink; ?>">
						<h4><?php echo $forum->title_out; ?></h4>
						<p><?php echo $forum->content_out; ?></p>
					</a>
				</li>
			<?php } ?>
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
