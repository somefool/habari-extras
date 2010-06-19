<?php $theme->display ( 'header' ); ?>

<div class="post" id="post-<?php echo $post->id; ?>">
<h2><a href="<?php echo $post->permalink; ?>" rel="bookmark"><?php echo $post->title_out; ?></a></h2>
<p class="post-date"><?php echo $post->pubdate->out( 'd M, y' ); ?></p>
<?php echo $post->content_out; ?>

<div class="separator-meta"></div>

<div class="post-meta">
<p><a href="<?php echo $post->comment_feed_link; ?>" rel="alternate" type="application/rss+xml" class="atom"><?php _e('Feed for this entry'); ?></a>
<p><a href="<?php echo $post->permalink; ?>#comments"><span class="comment-meta"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></span></a></p>
<?php if ( count( $post->tags ) > 0 ) { ?><p><img src="<?php Site::out_url( 'theme' ); ?>/img/tag.png" alt="tags" class="tags"> <?php echo $post->tags_out; ?></p><?php } ?>
<?php if ( $loggedin ) { ?><a href="<?php echo $post->editlink; ?>"><?php _e('Edit'); ?> "<?php echo $post->title_out; ?>"</a><?php } ?>
</div>
<div id="comments"><?php $theme->display ( 'comments' ); ?></div>
</div>
<div class="clear"></div>
</div>

<?php $theme->display ( 'sidebar' ); ?>
<?php $theme->display ( 'footer' ); ?>

