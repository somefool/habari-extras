<ul id="category_archives">
  <?php $categories = $content->categories; if ( count( $categories ) > 0 ) foreach( $categories as $category ): ?>
    <li>
	<a href="<?php echo $category[ 'url' ]; ?>" title="View entries in the '<?php
		echo $category[ 'category' ];
	?>' category"><?php
		echo $category[ 'category' ] . $category[ 'count' ];
	?></a>
    </li>
  <?php endforeach; ?>
</ul>
