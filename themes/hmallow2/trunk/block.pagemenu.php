<h3>Pages</h3>
<ul><?php foreach($content->menupages as $page) : ?>
<li class="<?php echo $page->activeclass; ?> block-<?php echo Utils::slugify($content->title); ?>"><a href="<?php echo $page->permalink; ?>"><?php echo $page->title; ?></a></li>
<?php endforeach; ?></ul>
