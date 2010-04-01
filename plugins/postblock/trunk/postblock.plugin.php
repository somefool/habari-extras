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
		$criteria = array(
			'status' => Post::status('published'), 
		);
		if($block->content_type != '') {
			$ctypes = array_flip(Post::list_active_post_types());
			$criteria['content_type'] = $ctypes[$block->content_type];
		}
		if($block->limit != '') {
			$criteria['limit'] = $block->limit;
		}
		if($block->tag != '') {
			$criteria['tag'] = $block->tag;
		}
		
		$block->posts = Posts::get($criteria);
		$block->criteria = $criteria;
	}
	
	public function action_block_form_postblock($form, $block)
	{
		$form->append('select', 'content_type', $block, 'Content Type:', array_flip(Post::list_active_post_types()));
		$form->append('text', 'limit', $block, 'Limit:')
			->add_validator('validate_regex', '%^(\d+)?$%', _t('Please enter a numeric value for the limit.'));
		$form->append('text', 'tag', $block, 'Tag:');

		$form->append('submit', 'save', 'Save');
	}
}

?>