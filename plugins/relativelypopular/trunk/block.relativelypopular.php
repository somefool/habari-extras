<?php
if(is_object($content->relativelypopular)) {
	?>
<div id="relativelypopular_plugin">
<?php
	if($content->_show_title) {
?>
	<h4><?php _e($content->title) ?></h4>
<?php
	}
?>
<ul>
  <?php foreach( $content->relativelypopular as $p): ?>
    <li>
    <a href="<?php echo $p->permalink; ?>">
        <?php echo $p->title; ?>
    </a>
    </li>
  <?php endforeach; ?>
</ul>
</div>
<?php
}
?>
