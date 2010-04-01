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
		'monthly_archives' => 'Monthly Archives',
//		'category_archives' => 'Category Archives',
//		'tag_archives' => 'Tag Archives',
//		'search_form' => 'Search Form',
//		'twitter_updates' => 'Twitter Updates',

	);
	
	
	// See action_init for this initial value:
	private $validation_urls = array();

	/**
	 * Register the template.
	 **/
	function action_init()
	{
		foreach ( array_keys( $this->allblocks ) as $blockname ) {
			$this->add_template( "block.$blockname", dirname(__FILE__) . "/block.$blockname.php" );
		}

		// This is here because you can't init a URL with dynamic values in the declaration
		$this->validation_urls = array(		
			'XHTML 1.0 Transitional' => 'http://validator.w3.org/check?uri=referer',
			'CSS level 3' => 'http://jigsaw.w3.org/css-validator/check/referer?profile=css3',
			'HTML5' => 'http://html5.validator.nu/?doc=' . Site::get_url('habari'),
			'Feed Validator' => 'http://beta.feedvalidator.org/check.cgi?url=' . Site::get_url('habari'),
		);
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

	public function action_block_form_w3c_validators( $form, $block )
	{
		$content = $form->append('checkboxes', 'links', $block, _t( 'Links to show:' ), array_flip($this->validation_urls) );
		$form->append('submit', 'save', 'Save');
	}

	public function action_block_form_monthly_archives( $form, $block )
	{
		$content = $form->append( 'checkbox', 'full_names', $block, _t( 'Display full month names:' ) );
		$content = $form->append( 'checkbox', 'show_counts', $block, _t( 'Append post count:' ) );
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
	public function action_block_content_recent_comments( $block, $theme )
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
	
	public function action_block_content_w3c_validators( $block, $theme )
	{
		$list = array();
		$validation_urls = array_flip($this->validation_urls);
		foreach( $block->links as $link ) {
			$list[$link] = $validation_urls[$link];
		}
		$block->list = $list;
	}

	public function action_block_content_monthly_archives( $block, $theme )
	{
		$months = array();
		$results = Posts::get( array(
			'content_type' => 'entry',
			'status' => 'published',
			'month_cts' => 1 )
			);

		foreach( $results as $result ) {
			if( $block->full_names ) {
				$display_month = HabariDateTime::date_create()->set_date( $result->year, $result->month, 1)->get( 'F' );
			}
			else {
				$display_month = HabariDateTime::date_create()->set_date( $result->year, $result->month, 1)->get( 'M' );
			}

			$count = '';
			if ( $block->show_counts ) {
				$count = " (" . $result->ct . ")";
			}

			$result->month = str_pad( $result->month, 2, 0, STR_PAD_LEFT );
			$url = URL::get( 'display_entries_by_date', array( 'year' => $result->year, 'month' => $result->month ) );
			$months[] = array(
				'display_month' => $display_month,
				'count' => $count,
				'year' => $result->year,
				'url' => $url,
				);
		}

		$block->months = $months;
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
