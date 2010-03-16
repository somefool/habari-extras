<?php

/** 
 * Produce post output based on specific criteria as a block.
 *
 */
class PostBlock extends Plugin
{
	function action_init()
	{
		$this->add_template( 'block.postblock', dirname(__FILE__) . '/block.postblock.php' );
	}

	public function filter_block_list($block_list)
	{
		$block_list['postblock'] = _t('Post Block');
		return $block_list;
	}
	
	public function action_block_content_postblock($block, $theme)
	{
		$block->criteria = array(
			'content_type' => Post::type($block->content_type), 
			'status' => Post::status('published'), 
			'limit' => $block->limit, 
			'tag' => $block->tag
		);
		
		$block->posts = Posts::get($block->criteria);
	}
	
	public function action_block_form_postblock($form, $block)
	{
		$form->append('text', 'context', $block, 'Context:');
		$form->append('text', 'content_type', $block, 'Content Type:');
		$form->append('text', 'limit', $block, 'Limit:');
		$form->append('text', 'tag', $block, 'Tag:');

		$form->append('submit', 'save', 'Save');
	}
}

?>