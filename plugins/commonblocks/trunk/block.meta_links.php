<h3><?php echo $content->title; ?></h3>
<ul id="meta_links">
	<?php $links = $content->list; foreach( $links as $label => $href): ?>
	<li><a href="<?php echo $label; ?>"><?php echo $href; ?></a></li>
	<?php endforeach; ?>
</ul>