<?php

/** 
 * Create a block with arbitrary content.
 *
 */
class CommonBlocks extends Plugin
{
	private $allblocks = array(
		'recent_comments' => 'Recent Comments',
		'validator_links' => 'Validator Links',
		'tag_cloud' => 'Tag Cloud',
		'monthly_archives' => 'Monthly Archives',
		'category_archives' => 'Category Archives',
		'tag_archives' => 'Tag Archives',
		'meta_links' => 'Meta Links',
		'search_form' => 'Search Form',

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
		$this->add_template( "block.dropdown.category_archives", dirname(__FILE__) . "/block.dropdown.category_archives.php" );
		$this->add_template( "block.dropdown.tag_archives", dirname(__FILE__) . "/block.dropdown.tag_archives.php" );
		$this->add_template( "block.dropdown.monthly_archives", dirname(__FILE__) . "/block.dropdown.monthly_archives.php" );

		// This is here because you can't init a URL with dynamic values in the declaration
		$this->validation_urls = array(
			'XHTML 1.0 Transitional' => 'http://validator.w3.org/check?uri=referer',
			'CSS level 3' => 'http://jigsaw.w3.org/css-validator/check/referer?profile=css3',
			'HTML5' => 'http://html5.validator.nu/?doc=' . Site::get_url('habari'),
			'Feed Validator' => 'http://beta.feedvalidator.org/check.cgi?url=' . Site::get_url('habari'),
		);

		$this->meta_urls = array(
			_t( 'Site Feed', 'commonblocks' ) => URL::get( 'atom_feed', array( 'index' => '1' ) ),
			_t( 'Comments Feed', 'commonblocks' ) => URL::get( 'atom_feed_comments' ),
			_t( 'Habari', 'commonblocks' ) => 'http://habariproject.org/',
		);
	}

	/**
	 * Add to the list of possible block types.
	 **/
	public function filter_block_list($block_list)
	{
		$allblocks = $this->allblocks;
		foreach ( $allblocks as $blockname => $nicename ) {
			$block_list[ $blockname ] = _t( $nicename, 'commonblocks' );
		}
		return $block_list;
	}

	/**
	 * Configuration forms
	 **/
	public function action_block_form_recent_comments( $form, $block )
	{
		$content = $form->append('text', 'quantity', $block, _t( 'Comments to show:', 'commonblocks' ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_validator_links( $form, $block )
	{
		$content = $form->append('checkboxes', 'links', $block, _t( 'Links to show:', 'commonblocks' ), array_flip($this->validation_urls) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_tag_cloud( $form, $block )
	{
		$content = $form->append( 'text', 'minimum', $block, _t( 'Minimum entries to show tag (0 to show all):', 'commonblocks' ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_monthly_archives( $form, $block )
	{
		$content = $form->append( 'checkbox', 'full_names', $block, _t( 'Display full month names:', 'commonblocks' ) );
		$content = $form->append( 'checkbox', 'show_counts', $block, _t( 'Append post count:', 'commonblocks' ) );
		$content = $form->append( 'select', 'style', $block, _t( 'Preferred Output Style:', 'commonblocks' ),
			    array('dropdown' => _t( 'Dropdown', 'commonblocks' ), 'list' => _t( 'List', 'commonblocks' ) ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_category_archives( $form, $block )
	{
		$content = $form->append( 'checkbox', 'show_counts', $block, _t( 'Append post count:', 'commonblocks' ) );
		$content = $form->append( 'select', 'style', $block, _t( 'Preferred Output Style:', 'commonblocks' ),
			    array('dropdown' => _t( 'Dropdown', 'commonblocks' ), 'list' => _t( 'List', 'commonblocks' ) ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_tag_archives( $form, $block )
	{
		$content = $form->append( 'checkbox', 'show_counts', $block, _t( 'Append post count:', 'commonblocks' ) );
		$content = $form->append( 'select', 'style', $block, _t( 'Preferred Output Style:', 'commonblocks' ),
			    array('dropdown' => _t( 'Dropdown', 'commonblocks' ), 'list' => _t( 'List', 'commonblocks' ) ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_meta_links( $form, $block )
	{
		$content = $form->append('checkboxes', 'links', $block, _t( 'Links to show:', 'commonblocks' ), array_flip( $this->meta_urls ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_search_form( $form, $block )
	{
		$content = $form->append( 'text', 'button', $block, _t( 'Button:', 'commonblocks' ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
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
	
	public function action_block_content_validator_links( $block, $theme )
	{
		$list = array();
		$validation_urls = array_flip($this->validation_urls);
		$links = $block->links;
		foreach( $links as $link ) {
			$list[$link] = $validation_urls[$link];
		}
		$block->list = $list;
	}

	public function action_block_content_tag_cloud( $block, $theme )
	{
		$minimum = ( isset( $block->minimum ) ? $block->minimum : 0 );
		$items = '';
		$tags = Tags::get(); // does this need to specify published?
		$max = intval( Tags::max_count() );

		foreach ( $tags as $tag ) {
			if ( $tag->count > $minimum ) {
			    $size = $tag->count * 15 / $max + 10;
			    $items .= 
				'<a href="' . URL::get( 'display_entries_by_tag', array( 'tag' => $tag->tag_slug ) ) .
				'" title="' . $tag->count . "\" style=\"font-size:{$size}pt;\" >" . $tag->tag . "</a>\n";
			}
		}
		$block->cloud = $items;
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
				$display_month = HabariDateTime::date_create()->set_date( $result->year, $result->month, 1 )->get( 'F' );
			}
			else {
				$display_month = HabariDateTime::date_create()->set_date( $result->year, $result->month, 1 )->get( 'M' );
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

	public function action_block_content_category_archives( $block, $theme )
	{
		$categories = array();
		$v = Vocabulary::get( 'categories' );
		if ( $v ) {
			$results = $v->get_tree();
			if ( count( $results ) > 0 ) { /* must we? Shouldn't the foreach just fail gracefully? */
				foreach( $results as $result ) {
				    $count = '';
					if ( $block->show_counts ) {
						$count = Posts::get( array( 'tag_slug' => $result->term, 'count' => 'term') );
					}

					$url = URL::get( 'display_entries_by_category', array( 'category_slug' => $result->term ) );
					$categories[] = array(
						'category' => $result->term_display,
						'count' => $count,
						'url' => $url,
					);
				}
			}
			$block->categories = $categories;
		}
	}

	public function action_block_content_tag_archives( $block, $theme )
	{
		$tags = array();
		$results = Tags::get();

		foreach( $results as $result ) {

			$count = '';
			if ( $block->show_counts ) {
				$count = " (" . Posts::count_by_tag( $result->slug, "published") . ")";
			}

			$url = URL::get( 'display_entries_by_tag', array( 'tag' => $result->tag_slug ) );
			$tags[] = array(
				'tag' => $result->tag_text,
				'count' => $count,
				'url' => $url,
				);
		}

		$block->tags = $tags;
	}

	public function action_block_content_meta_links( $block, $theme )
	{
		$list = array();
		if ( User::identify()->loggedin ) {
			$list[ Site::get_url( 'logout' ) ] = _t( 'Logout', 'commonblocks' );
		}
		else {
			$list[ Site::get_url( 'login' ) ] = _t( 'Login', 'commonblocks' );
		}
		$meta_urls = array_flip( $this->meta_urls );
		$links = $block->links;
		foreach( $links as $link ) {
			$list[ $link ] = $meta_urls[ $link ];
		}
		$block->list = $list;
	}

	public function action_block_content_search_form( $block, $theme )
	{
		$block->form = '<form method="get" id="searchform" action="' . URL::get('display_search') .
			'"><p><input type="text" id="s" name="criteria" value="' . ( isset( $theme->criteria ) ? htmlentities( $theme->criteria, ENT_COMPAT, 'UTF-8' ) : '' ) .
			'"><input type="submit" id="searchsubmit" value="' . ( isset( $block->button ) ? $block->button : _t( 'Search', 'commonblocks' ) ) . '"></p>';
	}

	/**
	 * Provide more specific templates for archive output
	 **/

	function filter_block_content_type_monthly_archives( $types, $block )
	{
		array_unshift( $types, $newtype = "block.{$block->style}.{$block->type}");
		if ( isset( $block->title ) ) {
			array_unshift( $types, "block.{$block->style}.{$block->type}." . Utils::slugify( $block->title ) );
		}
		return $types;
	}

	function filter_block_content_type_category_archives( $types, $block )
	{
		array_unshift( $types, $newtype = "block.{$block->style}.{$block->type}");
		if ( isset( $block->title ) ) {
			array_unshift( $types, "block.{$block->style}.{$block->type}." . Utils::slugify( $block->title ) );
		}
		return $types;
	}

	function filter_block_content_type_tag_archives( $types, $block )
	{
		array_unshift( $types, $newtype = "block.{$block->style}.{$block->type}");
		if ( isset( $block->title ) ) {
			array_unshift( $types, "block.{$block->style}.{$block->type}." . Utils::slugify( $block->title ) );
		}
		return $types;
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
