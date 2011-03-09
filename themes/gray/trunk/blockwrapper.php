<div class="block block-<?php echo Utils::slugify($block->title); ?><?php if( $block->_first ): ?> first<?php endif; if( $block->_last ):?> last<?php endif; echo ' block-index-' . $block->_area_index; ?>">
<?php if ( $block->_show_title ) :?>
	<h2><?php echo $block->title; ?></h2>
<?php endif; ?>
	<div class="block_content"">
		<?php echo $content; ?>
	</div>
</div>
