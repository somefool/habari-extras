<?php include'header.php'; ?>
	
	<div id="content_wrap">
	
		<div id="content">
			
			<div class="post_wrap">
		
			<h2><a href="<?php echo $post->permalink; ?>" rel="bookmark" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
			<p class="post_details"><?php echo $post->pubdate_out; ?>. Published by <?php echo $post->author->displayname; ?> <?php if ( is_array( $post->tags ) ) :?>under <?php echo $post->tags_out;?><?php endif; ?>. 
				<a href="<?php echo $post->permalink; ?>#comments" title="Comments to this post"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'comentari', 'comentaris', $post->comments->approved->count ); ?></a>.
			
			<?php if ( $user ) { ?>
				<span class="entry-edit"> | <a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="Edit post">Edita</a> |</span>
			<?php } ?></p>
			
			<?php echo $post->content_out; ?>

			</div>
			
			<?php $theme->display ('comments'); ?>
		
		</div>
	
		<?php include'sidebar.php'; ?>
	
	</div>

	<?php include'footer.php'; ?>