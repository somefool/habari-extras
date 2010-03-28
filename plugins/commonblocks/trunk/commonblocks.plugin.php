<?php

/** 
 * Create a block with arbitrary content.
 *
 */
class CommonBlocks extends Plugin
{
	private $allblocks = array(
		'recent_comments' => 'Recent Comments',
		'w3c_validators' => 'W3C Validators',
//		'tag_cloud' => 'Tag Cloud',
//		'monthly_archives' => 'Monthly Archives',
//		'category_archives' => 'Category Archives',
//		'tag_archives' => 'Tag Archives',
//		'search_form' => 'Search Form',
//		'twitter_updates' => 'Twitter Updates',

	);

	/**
	 * Register the template.
	 **/
	function action_init()
	{
		foreach ( array_keys( $this->allblocks ) as $blockname ) {
			$this->add_template( "block.$blockname", dirname(__FILE__) . "/block.$blockname.php" );
		}
	}

	/**
	 * Add to the list of possible block types.
	 **/
	public function filter_block_list($block_list)
	{
		foreach ( $this->allblocks as $blockname => $nicename ) {
			$block_list[ $blockname ] = _t( $nicename, 'commonblocks' );
		}
		return $block_list;
	}

	/**
	 * Configuration forms
	 **/
	public function action_block_form_recent_comments( $form, $block )
	{
		$content = $form->append('text', 'quantity', $block, _t( 'Comments to show:' ) );
		$form->append('submit', 'save', 'Save');
	}

	public function action_block_form_twitter_updates( $form, $block )
	{
		$content = $form->append('text', 'quantity', $block, _t( 'Tweets to show:' ) );
		$form->append('submit', 'save', 'Save');
	}

	/**
	 * Supply data to the block templates for output
	 **/
	public function action_block_content_recent_comments($block, $theme)
	{
		if ( ! $limit = $block->quantity ) { 
			$limit = 5;
		};

		$block->recent_comments = Comments::get( array(
			'limit'=>$block->quantity, 
			'status'=>Comment::STATUS_APPROVED, 
			'type'=>Comment::COMMENT, 
			'orderby'=>'date DESC',
		) );
	}
	
	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( 'Common Blocks', '7d823902-ce8d-4032-8ad7-bd19f9f47c6b', $this->info->version );
	}

}

?>
