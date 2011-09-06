<div id="lastfm">
	<ul id="lastrecent">
		<?php if ( isset( $content->lastfm_recent['error'] ) ) : ?>
		<li>
			<?php echo $content->lastfm_recent['error']; ?>
		</li>
		<?php else : 
			foreach ( $content->lastfm_recent as $track ) : ?>
		<li>
			<a href="<?php echo $track['url']; ?>"><?php if ( $track['image'] != '' ) : ?><img src="<?php echo $track['image'];?>" /><?php endif; ?><?php echo $track['name']; ?></a> by <?php echo $track['artist'];?>
		</li>		
		<?php endforeach;
		endif; ?>
	</ul>
</div>
