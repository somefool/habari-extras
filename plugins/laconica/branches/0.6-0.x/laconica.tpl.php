<!-- This file can be copied and modified in a theme directory -->

<div id="laconicabox">
	<ul>
		<?php foreach ($notices as $notice) : ?>
		<li>
			<img src="<?php echo htmlspecialchars( $notice->image_url ); ?>" alt="<?php echo urlencode( Options::get( 'laconica__username' )); ?>">
			<?php echo $notice->text . ' @ ' . $notice->time; ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<p><small>via <a href="http://<?php echo Options::get('laconica__svc'); ?>/index.php?action=showstream&amp;nickname=<?php echo urlencode( Options::get( 'laconica__username' )); ?>"><?php echo Options::get('laconica__svc'); ?></a></small></p>
</div>
