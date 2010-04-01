<h3>Tag Archives</h3>
<ul>
  <?php foreach( $content->tags as $tag ): ?>
    <li>
	<a href="<?php echo $tag[ 'url' ]; ?>" title="View entries tagged '<?php
		echo $tag[ 'tag' ];
	?>'"><?php
		echo $tag[ 'tag' ] . $tag[ 'count' ];
	?></a>
    </li>
  <?php endforeach; ?>
</ul>