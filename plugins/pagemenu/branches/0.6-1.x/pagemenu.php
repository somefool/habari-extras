<?php foreach($menupages as $page) : ?>
<li class="<?php echo $page->activeclass; ?>"><a href="<?php echo $page->permalink; ?>"><?php echo $page->title; ?></a></li>
<?php endforeach; ?>
