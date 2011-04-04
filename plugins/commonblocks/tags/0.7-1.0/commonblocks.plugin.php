<?php

/** 
 * Create a block with arbitrary content.
 *
 */
class CommonBlocks extends Plugin
{
	private $allblocks = array(
		'validator_links' => 'Validator Links',
		'tag_cloud' => 'Tag Cloud',
		'category_archives' => 'Category Archives',
		'googlead' => 'Google Ad',
	);
	
	// See action_init for this initial value:
	private $validation_urls = array();

	/**
	 * Register the template.
	 **/
	function action_init()
	{
		$this->load_text_domain( 'commonblocks' );
		foreach ( array_keys( $this->allblocks ) as $blockname ) {
			$this->add_template( "block.$blockname", dirname( __FILE__ ) . "/block.$blockname.php" );
		}
		$this->add_template( "block.dropdown.category_archives", dirname( __FILE__ ) . "/block.dropdown.category_archives.php" );

		// This is here because you can't init a URL with dynamic values in the declaration
		$this->validation_urls = array(
			_t( 'XHTML 1.0 Transitional', 'commonblocks' ) => 'http://validator.w3.org/check?uri=referer',
			_t( 'CSS level 3', 'commonblocks' ) => 'http://jigsaw.w3.org/css-validator/check/referer?profile=css3',
				'HTML5' => 'http://html5.validator.nu/?doc=' . Site::get_url( 'habari' ),
			_t( 'Unicorn', 'commonblocks' ) => 'http://validator.w3.org/unicorn/check?ucn_task=conformance&amp;ucn_uri=referer',
			_t( 'Feed Validator', 'commonblocks' ) => 'http://beta.feedvalidator.org/check.cgi?url=' . Site::get_url( 'habari' ),
		);
	}

	/**
	 * Add to the list of possible block types.
	 **/
	public function filter_block_list( $block_list )
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
	public function action_block_form_validator_links( $form, $block )
	{
		$content = $form->append('checkboxes', 'links', $block, _t( 'Links to show:', 'commonblocks' ), array_flip( $this->validation_urls ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_tag_cloud( $form, $block )
	{
		$content = $form->append( 'text', 'minimum', $block, _t( 'Minimum entries to show tag (0 to show all):', 'commonblocks' ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_category_archives( $form, $block )
	{
		$content = $form->append( 'checkbox', 'show_counts', $block, _t( 'Append post count:', 'commonblocks' ) );
		$content = $form->append( 'select', 'style', $block, _t( 'Preferred Output Style:', 'commonblocks' ),
			 array('dropdown' => _t( 'Dropdown', 'commonblocks' ), 'list' => _t( 'List', 'commonblocks' ) ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	public function action_block_form_googlead( $form, $block )
	{
		$form->append( 'text', 'clientcode', $block, _t( 'Client Code: ', 'commonblocks' ) );
		$form->append( 'text', 'slot', $block, _t( 'Slot ID: ', 'commonblocks' ) );
		$form->append( 'text', 'width', $block, _t( 'Ad Width: ', 'commonblocks' ) );
		$form->append( 'text', 'height', $block, _t ( 'Ad Height: ', 'commonblocks' ) );
		$form->append( 'submit', 'save', _t( 'Save', 'commonblocks' ) );
	}

	/**
	 * Supply data to the block templates for output
	 **/
	public function action_block_content_validator_links( $block, $theme )
	{
		$list = array();
		$validation_urls = array_flip( $this->validation_urls );
		$links = $block->links;
		if ( count( $links ) > 0 ) {
			foreach( $links as $link ) {
				$list[$link] = $validation_urls[$link];
			}
		}
		$block->list = $list;
	}

	public function action_block_content_tag_cloud( $block, $theme )
	{
		$minimum = ( isset( $block->minimum ) ? $block->minimum : 0 );
		$items = '';
		$tags = Tags::vocabulary()->get_tree(); // does this need to specify published?
		$max = intval( Tags::vocabulary()->max_count() );
		foreach ( $tags as $tag ) {
			if ( $tag->count > $minimum ) {
			 $size = $tag->count * 15 / $max + 10;
			 $items .= 
				'<a href="' . URL::get( 'display_entries_by_tag', array( 'tag' => $tag->term ) ) .
				'" title="' . $tag->count . "\" style=\"font-size:{$size}pt;\" >" . $tag->term_display . "</a>\n";
			}
		}
		$block->cloud = $items;
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

	public function action_block_content_googlead( $block, $theme )
	{
		$block->ad = '';
		if ( $block->clientcode != '' && $block->slot != '' && $block->width != '' && $block->height != '' ) {
			$block->ad = <<<ENDAD
<p><script type="text/javascript"><!--
google_ad_client = "{$block->clientcode}";
google_ad_slot = "{$block->slot}";
google_ad_width = {$block->width};
google_ad_height = {$block->height};
//--></script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
ENDAD;
		}
	}
	
	/**
	 * Provide more specific templates for archive output
	 **/
	function filter_block_content_type_category_archives( $types, $block )
	{
		array_unshift( $types, $newtype = "block.{$block->style}.{$block->type}" );
		if ( isset( $block->title ) ) {
			array_unshift( $types, "block.{$block->style}.{$block->type}." . Utils::slugify( $block->title ) );
		}
		return $types;
	}
}

?>
