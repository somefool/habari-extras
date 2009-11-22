<div id="tla">
<h3>Sweet Links</h3>
<ul>
<?php foreach($content->links as $link): ?>
<li><?php echo $link->before; ?><a href="<?php echo $link->url; ?>"><?php echo $link->text; ?></a><?php echo $link->after; ?></li>
<?php endforeach; ?>
</ul>
</div>
