<?php

/** 
 * Create a block with arbitrary content.
 *
 */
class SimpleBlock extends Plugin
{
	/**
	 * Register the template.
	 **/
	function action_init()
	{
		$this->add_template( 'block.simpleblock', dirname(__FILE__) . '/block.simpleblock.php' );
	}

	/**
	 * Add to the list of possible block types.
	 **/
	public function filter_block_list($block_list)
	{
		$block_list['simpleblock'] = _t('Simple Block');
		return $block_list;
	}

	/**
	 * Configuration form with one big textarea. Raw to allow JS/HTML/etc. Insert them at your own peril.
	 **/
	public function action_block_form_simpleblock($form, $block)
	{
		$content = $form->append('textarea', 'content', $block, _t( 'Content:' ) );
		$content->raw = true;
		$content->rows = 5;
		$form->append('submit', 'save', 'Save');
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( 'Simple Block', '50a5f6c5-8343-43ee-b9dd-aa783a7f07b8', $this->info->version );
	}

}

?>
