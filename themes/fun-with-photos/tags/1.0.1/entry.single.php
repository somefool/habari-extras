<?php $theme->display( 'header'); ?>
	<?php
	 //hide the photo if details have been requested
	 $class_hidden_photo = ( isset($_GET['show_details']) && $_GET['show_details'] == true ) ? 'hidden' : ''; 
	 ?>
	<div id="post_content" class="<?php echo $class_hidden_photo; ?>">
		<div id="prev_post"><?php echo $theme->prev_post_link( $post ) ?></div>
		<?php echo $post->content_out; ?>
		<div id="next_post"><?php echo $theme->next_post_link( $post ); ?></div>
	</div>
	<div id="post_details_link" class="<?php echo $class_hidden_photo; ?>"><a href="<?php echo $post->permalink; ?>?show_details=true" title="View details and comments about <?php echo $post->title_out; ?>">[Details / Comments]</a></div>
		
		
		<?php
		 //hide the details if they haven't been requested
		 $class_hidden_details = ( isset($_GET['show_details']) && $_GET['show_details'] == true ) ? '' : 'hidden'; 
		 ?>
	<div id="post_content_link" class="<?php echo $class_hidden_details; ?>"><a href="<?php echo $post->permalink; ?>" title="View <?php echo $post->title_out; ?>">[View photo]</a></div>
	<div id="post_details" class="<?php echo $class_hidden_details; ?>">
		
		<div id="details">
			<h2>Photo Details</h2>
			<p class="tags">Tagged: <?php $tags = 0; foreach( $post->tags_out as $tag ) {$tags++; echo ( $tags > 1 ) ? ', '.$tag : $tag;} ?></p>
			<?php echo $theme->post_content_more; ?>
		</div>
		<div id="comments">
			<?php $theme->display ( 'comments' ); ?>
		</div>
		<div class="clear"></div>
	</div>	

		
		
		

		<div id="footer"><p>This site is powered by <a href="http://habariproject.org/en/" title="Habari Project">Habari</a></p></div>	
		</div>
	
	</div>
</body>

</html>