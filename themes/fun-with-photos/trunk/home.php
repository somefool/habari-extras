<?php $theme->display( 'header'); ?>
<?php
foreach( $posts as $post ){
?>
	<div id="post_content">
		<div id="prev_post"><?php echo $theme->prev_post_link( $post ) ?></div>
		<?php echo $post->content_out; ?>
		<div id="next_post"><?php echo $theme->next_post_link( $post ); ?></div>
	</div>
	<div id="post_details_link"><a href="<?php echo $post->permalink; ?>?show_details=true" title="View details and comments about <?php echo $post->title_out; ?>">[Details / Comments]</a></div>
<?php } ?>
		
		
		

		<div id="footer"><p>This site is powered by <a href="http://habariproject.org/en/" title="Habari Project">Habari</a></p></div>	
		</div>
	
	</div>
</body>

</html>