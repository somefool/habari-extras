<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); } ?>
<?php $theme->display ('header'); ?>
<!-- entry.multiple -->
	<div class="content">
	<div id="primary">
		<div id="primarycontent">
			<h3><?php echo $forum->title_out; ?></h3>
			<?php if( Spreking::has_permission('post_thread', $forum) ): ?><a href="<?php echo $forum->new_thread_link; ?>">Create New Thread</a><?php endif; ?>
			<ul>
			<?php foreach ( $threads as $post ) { ?>
				<li>
					<a href="<?php echo $post->permalink; ?>">
						<h4><?php echo $post->title_out; ?></h4>
						<p><?php echo $post->content_out; ?></p>
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
