<?php $theme->display ('header'); ?>

<div id="container">
	<div id="content">
		<div class="hentry post error404">
			<h2 class="entry-title"><?php _e('Error!'); ?></h2>
			<div class="entry-content">
				<p><?php _e('The requested post was not found.'); ?></p>
			</div>
		</div><!-- .post -->

	</div><!-- #content -->
</div><!-- #container -->

<?php $theme->display ('sidebar'); ?>
<?php $theme->display ('footer'); ?>
