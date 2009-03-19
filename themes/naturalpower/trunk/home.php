<?php include'header.php'; ?>
	
	<div id="content_wrap">
	
		<div id="content">

			<?php foreach ( $posts as $post ) { ?>
			
			<div class="post_wrap">
		
			<h2><a href="<?php echo $post->permalink; ?>" rel="bookmark" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
			<p class="post_details"><?php echo $post->pubdate_out; ?>. Published by <?php echo $post->author->displayname; ?> <?php if ( is_array( $post->tags ) ) :?>under <?php echo $post->tags_out;?><?php endif; ?>. 
				<a href="<?php echo $post->permalink; ?>#comments" title="Comments to this post"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'comment', 'comments', $post->comments->approved->count ); ?></a>.
			
			<?php if ( $user ) { ?>
				<span class="entry-edit"> | <a href="<?php echo $post->editlink; ?>" title="Edit post">Edit</a> |</span>
			<?php } ?></p>
			
			<?php echo $post->content_out; ?>

			</div>
			
			<?php } ?>
			
			<div id="more_entries">
			<h2>
			<?php $theme->next_page_link('&laquo; Older Entries'); ?> &nbsp; <?php $theme->prev_page_link('Recent Entries &raquo;'); ?>
			</h2>
			</div>

		
		</div>
	
		<?php include'sidebar.php'; ?>
	
	</div>

	<?php include'footer.php'; ?>