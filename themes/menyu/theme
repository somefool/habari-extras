<?php $theme->display('header'); ?>
<!-- plugin.multiple.php -->

<div class="prepend-5 span-19 last">
<h2 class="plugin_title">Plugins</h2>
</div>
</div>

<div class="container">

<div class="column span-4">
<?php 	$theme->display('recent'); ?>
</div>

<div class="column span-16 prepend-1 last"><!-- rightside stuff -->
	<?php foreach( $posts as $post ): ?>

		<h2><a href="<?php echo $post->permalink; ?>"><?php echo $post->title_out; ?></a></h2>

		<?php echo $post->content_out; ?>

	<?php endforeach; ?>

</div><!-- /rightside stuff -->

	<hr>
	<p><?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?></p>
	
<!-- /plugin.multiple.php -->
<?php $theme->display('footer'); ?>

