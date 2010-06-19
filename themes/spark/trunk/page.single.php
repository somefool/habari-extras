<?php $theme->display ('header'); ?>

<div class="page" id="post-<?php echo $post->id; ?>">
<h2><?php echo $post->title_out; ?></h2>
<?php echo $post->content_out; ?>
<?php if ( $loggedin ) { ?><p><a href="<?php echo $post->editlink; ?>"><?php _e('Edit'); ?> "<?php echo $post->title_out; ?>"</a></p><?php } ?>
</div>
<div class="clear"></div>

</div>
<?php $theme->display ( 'sidebar' ); ?>
<?php $theme->display ( 'footer' ); ?>