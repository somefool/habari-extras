<?php include 'header.php'; ?>
<!-- home -->
<div id="main" class="push-4 span-16">

	<div id="content" class="">
<?php foreach ( $posts as $post ) { ?>
		<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">

<h2 class="post-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>">
<?php echo $post->title_out; ?></a></h2>
<h4><?php echo $post->pubdate_out; ?><?php if ( $user ) { ?> <a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="Edit post">(edit)</a><?php } ?></h4>


	<div>
<?php echo $post->content_out; ?>
	</div>

		<div class="meta">
		
			<p><?php if ( is_array( $post->tags ) ) { ?>
		Tags &brvbar; <?php echo $post->tags_out; ?>
<?php } ?><br /><a href="<?php echo $post->permalink; ?>#comments" title="Comments to this post">
<?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></p>
		</div>


	</div>

<?php } ?>
</div>

<div id="pagenav">
	<span>Page:</span> <?php $theme->prev_page_link('&laquo; ' . _t('Newer Posts')); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link('&raquo; ' . _t('Older Posts')); ?>
</div>


</div>

<?php include 'sidebar.php'; ?>



<?php include 'footer.php'; ?>

</div>

</body>

</html>
