<div class="block block-<?php echo Utils::slugify($block->title); ?><?php 
if($block->_first) echo " block-is_first";
if($block->_last) echo " block-is_last";
?>">
	<?php if($block->_show_title): ?>
	<h3><?php echo $block->title; ?></h3>
	<?php endif; ?>
	<div class="block_content"">
		<?php echo $content; ?>
	</div>
</div>
