<?php foreach($menupages as $page) : ?>
<li><a href="<?php echo $page->permalink; ?>"><?php echo $page->title; ?></a></li>
<?php endforeach; ?>