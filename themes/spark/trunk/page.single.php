<?php $theme->display ('header'); ?>

<div id="post-<?php echo $post->id; ?>">
<h2><?php echo $post->title_out; ?></h2>
<?php echo $post->content_out; ?>
</div>
<div class="clear"></div>

</div>
<?php $theme->display ( 'sidebar' ); ?>
<?php $theme->display ( 'footer' ); ?>