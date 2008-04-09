<h3><?php echo $blogroll_title; ?></h3>
<ul>
<?php foreach ( $blogroll_links as $item ) : ?>
	<li><a href="<?php echo $item['url']; ?>" title="<?php echo $item['title']; ?>" rel="<?php echo $item['rel']; ?>"><?php echo $item['title']; ?></a></li>
<?php endforeach; ?>
</ul>