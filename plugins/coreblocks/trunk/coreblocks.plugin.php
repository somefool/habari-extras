<?php

/**
 * Create blocks commonly used by site owners.
 *
 */
class CoreBlocks extends Plugin
{
	private $allblocks = array(
		'recent_comments' => 'Recent Comments',
		'recent_posts' => 'Recent Posts',
		'validator_links' => 'Validator Links',
		'monthly_archives' => 'Monthly Archives',
		'category_archives' => 'Category Archives',
		'tag_archives' => 'Tag Archives',
		'meta_links' => 'Meta Links',
		'search_form' => 'Search Form',

	);

	// See action_init for this initial value:
	private $validation_urls = array();

	/**
	 * Register the templates.
	 **/
	function action_init()
	{
		foreach ( array_keys( $this->allblocks ) as $blockname ) {
			$this->add_template( "block.$blockname", dirname( __FILE__ ) . "/block.$blockname.php" );
		}
		$this->add_template( "block.dropdown.category_archives", dirname( __FILE__ ) . "/block.dropdown.category_archives.php" );
		$this->add_template( "block.dropdown.tag_archives", dirname( __FILE__ ) . "/block.dropdown.tag_archives.php" );
		$this->add_template( "block.dropdown.monthly_archives", dirname( __FILE__ ) . "/block.dropdown.monthly_archives.php" );

		// This is here because you can't init a URL with dynamic values in the declaration
		$this->validation_urls = array(
			_t( 'XHTML 1.0 Transitional' ) => 'http://validator.w3.org/check?uri=referer',
			_t( 'CSS level 3' ) => 'http://jigsaw.w3.org/css-validator/check/referer?profile=css3',
			'HTML5' => 'http://html5.validator.nu/?doc=' . Site::get_url('habari'),
 			_t( 'Unicorn' ) => 'http://validator.w3.org/unicorn/check?ucn_task=conformance&amp;ucn_uri=referer',
			_t( 'Feed Validator' ) => 'http://beta.feedvalidator.org/check.cgi?url=' . Site::get_url( 'habari' ),
		);

		$this->meta_urls = array(
			_t( 'Site Feed' ) => URL::get( 'atom_feed', array( 'index' => '1' ) ),
			_t( 'Comments Feed' ) => URL::get( 'atom_feed_comments' ),
			'Habari' => 'http://habariproject.org/',
		);
	}

	/**
	 * Add to the list of possible block types.
	 **/
	public function filter_block_list( $block_list )
	{
		$allblocks = $this->allblocks;
		foreach ( $allblocks as $blockname => $nicename ) {
			$block_list[ $blockname ] = _t( $nicename );
		}
		return $block_list;
	}

	/**
	 * Recent Comments
	 **/
	public function action_block_form_recent_comments( $form, $block )
	{
		$content = $form->append('text', 'quantity', $block, _t( 'Comments to show:' ) );
		$form->append( 'submit', 'save', _t( 'Save' ) );
	}

	public function action_block_content_recent_comments( $block, $theme )
	{
		if ( ! $limit = $block->quantity ) {
			$limit = 5;
		};

		$offset = 0;
		$published_posts = 0;
		$valid_comments = array();
		// prevent endless looping if there are fewer comments than $limit
		$comments_remain = true;

		while ( $published_posts < $limit && $comments_remain ) {
			$comments = Comments::get( array(
				'limit' => $limit - $published_posts,
				'status' => Comment::STATUS_APPROVED,
				'type' => Comment::COMMENT,
				'offset' => $offset,
				'orderby' => 'date DESC',
			) );
			// check the posts
			foreach ( $comments as $key => $comment ) {
				if ( ( $comment->post->status ) == Post::status( 'published' ) ) {
					$valid_comments[] = $comments[ $key ];
					++$published_posts;
				}
				++$offset;
			}
			// stop looping if out of comments
			if ( count( $comments ) === 0 ) {
				$comments_remain = false;
			}
		}

		$block->recent_comments = $valid_comments;
	}

		/**
	 * Recent Posts
	 **/
	public function action_block_content_recent_posts( $block, $theme )
	{
		if ( ! $limit = $block->quantity ) {
			$limit = 5;
		};

		$block->recent_posts = Posts::get( array(
			'limit'=>$limit,
			'status'=>Post::status( 'published' ),
			'content_type'=>Post::type( 'entry' ), // extend to allow more types.
			'orderby'=>'pubdate DESC',
		) );
	}

	/**
	 * Validator Links
	 **/
	public function action_block_form_validator_links( $form, $block )
	{
		$content = $form->append('checkboxes', 'links', $block, _t( 'Links to show:' ), array_flip( $this->validation_urls ) );
		$form->append( 'submit', 'save', _t( 'Save' ) );
	}

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

	/**
	 * Monthly Archives
	 **/
	public function action_block_form_monthly_archives( $form, $block )
	{
		$content = $form->append( 'checkbox', 'full_names', $block, _t( 'Display full month names:' ) );
		$content = $form->append( 'checkbox', 'show_counts', $block, _t( 'Append post count:' ) );
		$content = $form->append( 'select', 'style', $block, _t( 'Preferred Output Style:' ),
			    array('dropdown' => _t( 'Dropdown' ), 'list' => _t( 'List' ) ) );
		$form->append( 'submit', 'save', _t( 'Save' ) );
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

	function filter_block_content_type_monthly_archives( $types, $block )
	{
		array_unshift( $types, $newtype = "block.{$block->style}.{$block->type}" );
		if ( isset( $block->title ) ) {
			array_unshift( $types, "block.{$block->style}.{$block->type}." . Utils::slugify( $block->title ) );
		}
		return $types;
	}


	/**
	 * Tag Archives
	 **/
	public function action_block_form_tag_archives( $form, $block )
	{
		$content = $form->append( 'checkbox', 'show_counts', $block, _t( 'Append post count:' ) );
		$content = $form->append( 'select', 'style', $block, _t( 'Preferred Output Style:' ),
			    array('dropdown' => _t( 'Dropdown' ), 'list' => _t( 'List' ) ) );
		$form->append( 'submit', 'save', _t( 'Save' ) );
	}

	public function action_block_content_tag_archives( $block, $theme )
	{
		$tags = array();
		$results = Tags::vocabulary()->get_tree();

		foreach( $results as $result ) {

			$count = '';
			if ( $block->show_counts ) {
				$count = " (" . Posts::count_by_tag( $result->slug, "published") . ")";
			}

			$url = URL::get( 'display_entries_by_tag', array( 'tag' => $result->term ) );
			$tags[] = array(
				'tag' => $result->term_display,
				'count' => $count,
				'url' => $url,
				);
		}

		$block->tags = $tags;
	}

	function filter_block_content_type_tag_archives( $types, $block )
	{
		array_unshift( $types, $newtype = "block.{$block->style}.{$block->type}" );
		if ( isset( $block->title ) ) {
			array_unshift( $types, "block.{$block->style}.{$block->type}." . Utils::slugify( $block->title ) );
		}
		return $types;
	}

	/**
	 * Meta Links
	 **/
	public function action_block_form_meta_links( $form, $block )
	{
		$content = $form->append('checkboxes', 'links', $block, _t( 'Links to show:' ), array_flip( $this->meta_urls ) );
		$form->append( 'submit', 'save', _t( 'Save' ) );
	}

	public function action_block_content_meta_links( $block, $theme )
	{
		$list = array();
		if ( User::identify()->loggedin ) {
			$list[ Site::get_url( 'logout' ) ] = _t( 'Logout' );
		}
		else {
			$list[ Site::get_url( 'login' ) ] = _t( 'Login' );
		}
		$meta_urls = array_flip( $this->meta_urls );
		$links = $block->links;
		if ( count( $links ) > 0 ) {
			foreach( $links as $link ) {
				$list[ $link ] = $meta_urls[ $link ];
			}
		}
		$block->list = $list;
	}
	
	/**
	 * Search Form
	 **/
	public function action_block_form_search_form( $form, $block )
	{
		$content = $form->append( 'text', 'button', $block, _t( 'Button:' ) );
		$form->append( 'submit', 'save', _t( 'Save' ) );
	}

	public function action_block_content_search_form( $block, $theme )
	{
		$block->form = '<form method="get" id="searchform" action="' . URL::get( 'display_search' ) .
			'"><p><input type="text" id="s" name="criteria" value="' . ( isset( $theme->criteria ) ? htmlentities( $theme->criteria, ENT_COMPAT, 'UTF-8' ) : '' ) .
			'"><input type="submit" id="searchsubmit" value="' . ( isset( $block->button ) ? $block->button : _t( 'Search' ) ) . '"></p></form>';
	}
}
?>
