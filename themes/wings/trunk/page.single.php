<?php $theme->display( 'header'); ?>
<!-- page -->
<div class="page" id="<?php echo $post->slug; ?>">
				<div id="left-col">
						<h2><?php echo $post->title_out; ?></h2>
						<?php echo $post->content_out; ?>
				</div>
				<div id="right-col">
					<?php $theme->display ( 'sidebar' ); ?>
				</div>
</div>
<!-- /page -->
<?php $theme->display ('footer'); ?>
