<h3>W3C validators</h3>
<ul>
	<?php $links = $content->list; foreach( $links as $label => $href): ?>
	<li><a href="<?php echo $label; ?>"><?php echo $href; ?></a></li>
	<?php endforeach; ?>
</ul>