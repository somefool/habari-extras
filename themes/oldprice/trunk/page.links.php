<?php $theme->display ('header'); ?>

<div id="container">
	<div id="content">

    <div id="post-<?php echo $post->id; ?>" class="hentry page <?php echo $post->statusname , ' ' , $post->tags_class; ?>">
     <h2 class="entry-title"><?php echo $post->title_out; ?></h2>
     <div class="entry-content">
      <?php echo $post->content_out; ?>
      <?php $theme->show_blogroll(); ?>
     </div>
	<?php if ( $user ) { ?>
	       <div class="entry-meta"><a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit'); ?></a></div>
	<?php } ?>
    </div>
<?php $theme->display ( 'comments' ); ?>
		</div><!-- #content -->
	</div><!-- #container -->
<?php $theme->display ('sidebar'); ?>
<?php $theme->display ('footer'); ?>
