<h3>Twitter</h3>
	<ul>
<?php foreach ( $content->tweets as $tweet ) : ?>
		<li>
			<?php echo $tweet->text . ' <small>@ ' . $tweet->time; ?></small>
		</li>
		<?php endforeach; ?>
		<li>via <a href="http://twitter.com/<?php echo urlencode( $content->username ); ?>">Twitter</a></li>
	</ul>
