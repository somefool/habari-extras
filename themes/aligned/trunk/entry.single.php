<?php include 'header.php'; ?>
	<div class="post" id="post-<?php echo $post->id; ?>">
		<p class="post_date"><span><?php $post->pubdate->out(); ?> | <strong><?php echo $post->author->displayname; ?></strong></span></p>
		<h2 class="post_title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
		<div class="post_content">
			<?php echo $post->content_out; ?>
		</div>
		<p class="post_meta"><span><?php if ( count( $post->tags ) ) { ?>Tags: <?php echo $post->tags_out; ?> | <?php } ?><a href="<?php echo $post->permalink; ?>#comments" title="Comments to this post"><?php echo $post->comments->approved->count; ?>
		<?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></span></p>
	</div>
	<?php include 'comments.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
