<div id="lastfm">
	<ul id="lastrecent">
		<?php if ( isset($lastfm_track['error'] ) ) : ?>
		<li>
			<?php echo $lastfm_track['error']; ?>
		</li>
		<?php else : 
			foreach ( $lastfm_tracks as $track ) : ?>
		<li>
			<a href="<?php echo $track['url']; ?>"><?php if ( $track['image'] != '' ) : ?><img src="<?php echo $track['image'];?>" /><?php endif; ?><?php echo $track['name']; ?></a> by <?php echo $track['artist'];?>
		</li>		
		<?php endforeach;
		endif; ?>
	</ul>
</div>
