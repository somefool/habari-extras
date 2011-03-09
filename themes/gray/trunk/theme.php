<?php

/**
 * ResurrectionTheme is a custom Theme class for teh Resurrection theme
 *
 * @package Habari
 */


/**
 * A custom theme for Resurrection output
 */
class GrayTheme extends Theme
{
	/**
	 * On theme activation, activate some default blocks
	 */
	public function action_theme_activated()
	{
		$blocks = $this->get_blocks('primary', '', $this);
		if(count($blocks) == 0) {
			$block = new Block(array(
				'title' => _t('Posts'),
				'type' => 'grayposts',
			));

			$block->add_to_area('primary');
			Session::notice(_t('Added default blocks to theme areas.'));
		}
	}

	/**
	 * Add default output filters
	 **/
	public function action_init_theme()
	{
		// Apply Format::autop() to post content...
		Format::apply( 'autop', 'post_content_out' );
		// Apply Format::autop() to comment content...
		Format::apply( 'autop', 'comment_content_out' );
		// Apply Format::tag_and_list() to post tags...
		Format::apply( 'tag_and_list', 'post_tags_out' );
		// Apply Format::nice_date() to post date...
		Format::apply( 'format_date', 'post_pubdate_out', '{F} {j}, {Y} {g}:{i}{a}' );
		// Apply Format::more to post content...
		Plugins::register( array($this, 'more'), 'filter', 'post_content_out');
		// Apply Format::search_highlight to post content...
		Format::apply( 'search_highlight', 'post_content_out' );
		
		//Session::error('Sample error', 'sample');
	}
	
	public function filter_post_pubdate_ago($pubdate) {
		$days_ago = round((HabariDateTime::date_create()->int - $pubdate->int) / 86400);
		return "{$days_ago} days ago";
	}
	
	public function more($content, $post)
	{
		$more_text = 'Read the rest &#8594;';
		$max_paragraphs = 1;
		
		$showmore = false;
		$matches = preg_split( '/<!--\s*more\s*-->/is', $content, 2, PREG_SPLIT_NO_EMPTY );
		if ( count($matches) > 1 ) {
			$summary = $matches[0];
			$remainder = $matches[1];
			if(trim($remainder) != '') {
				$showmore = true;
			}
		}
		else {
			$ht = new HtmlTokenizer($content, false);
			$set = $ht->parse();

			$stack = array();
			$para = 0;
			$token = $set->current();
			$summary = new HTMLTokenSet(false);
			$remainder = new HTMLTokenSet(false);
			$set->rewind(); 
			for($token = $set->current(); $set->valid(); $token = $set->next() ) {
				if($token['type'] == HTMLTokenizer::NODE_TYPE_ELEMENT_OPEN) {
					$stack[$token['name']] = $token['name'];
				}
				if($para < $max_paragraphs) {
					$summary[] = $token;
				}
				if($para >= $max_paragraphs) {
					$remainder[] = $token;
					$showmore = true;
				}
				if($token['type'] == HTMLTokenizer::NODE_TYPE_ELEMENT_CLOSE) {
					if(isset($stack[$token['name']])) {
						while(end($stack) != $token['name']) {
							array_pop($stack);
						}
						array_pop($stack);
					}
					if(count($stack) == 0) {
						$para++;
					}
				}
			}
			
		}
		if ( $post->slug == Controller::get_var('slug') ) {
			$content = $summary . '<div id="more" class="moreanchor">'. 'Continues here &#8594;' .'</div>' . $remainder;
		}
		elseif( $showmore == true ) {
			$content = $summary . '<p class="more"><a href="' . $post->permalink . '#more">' . $more_text . '</a></p>';
		}
		else {
			$content = $summary . $remainder;
		}
		return $content;
	}


	/**
	 * Add additional template variables to the template output.
	 */
	public function action_add_template_vars($theme)
	{
		// Set an SEO-friendly page title
		$this->page_title = (isset($this->post) && ($this->request->display_entry || $this->request->display_page)) ? $this->post->title . ' - ' . Options::get( 'title' ) : Options::get( 'title' ) ;

		// Set the site title
		$this->site_title = Options::get('title');

		// Set the YUI class for layout		
		$this->yui_class = Options::get('yui_class', 'yui-t1');
		$this->yui_id = Options::get('yui_id', 'doc');
		$this->align_class = Options::get('align_class', 'doc-center');
		if($this->yui_id == 'doc3') {
			$this->align_class = 'doc-center';
		}

		/*
		// Make posts an instance of Posts if it's just one
		if($this->posts instanceof Post) {
			$this->posts = new Posts(array($this->posts));
			$this->show_page_selector = false;
		}
		else {
			$this->show_page_selector = true;
		}
		*/

		// Add javascript support files
		Stack::add('template_header_javascript', Site::get_url('scripts', '/jquery.js') , 'jquery' );

		// Add the stylesheets
		Stack::add('template_stylesheet', array('http://yui.yahooapis.com/2.8.1/build/reset-fonts-grids/reset-fonts-grids.css', 'screen,projection'), 'yahoo');
		Stack::add('template_stylesheet', array(Site::get_url( 'theme', '/print.css' ) , 'print'), 'print');
		Stack::add('template_stylesheet', array('@import url(http://fonts.googleapis.com/css?family=Vollkorn&subset=latin);' , 'screen,projection'), 'font', 'yahoo');
		Stack::add('template_stylesheet', array(Site::get_url( 'theme', '/style.css' ) , 'screen,projection'), 'theme', 'yahoo');
	}

	public function filter_submit_comment_form($result, $form)
	{
		var_dump($form->get_values());

		return $result;
	}
	
	/**
	 * Respond to the user selecting 'configure' on the themes page
	 */
	public function action_theme_ui()
	{
		$form = new FormUI( 'gray_theme' );

		$form->append('fieldset', 'yui_fs', 'YUI Grid Settings');
		$form->yui_fs->append( 'select', 'yui_id', 'yui_id', 'Page width:' );
		$form->yui_id->options = array(
			'doc' => _t('750px', 'gray'), 
			'doc2' => _t('950px', 'gray'), 
			'doc3' => _t('100%', 'gray'), 
			'doc4' => _t('974px', 'gray'), 
		);
		$form->yui_fs->append( 'select', 'yui_class', 'yui_class', 'Sidebar size and position:' );
		$form->yui_class->options = array(
			'yui-t1' => _t('160px on left', 'gray'), 
			'yui-t2' => _t('180px on left', 'gray'), 
			'yui-t3' => _t('300px on left', 'gray'), 
			'yui-t4' => _t('180px on right', 'gray'), 
			'yui-t5' => _t('240px on right', 'gray'), 
			'yui-t6' => _t('300px on right', 'gray'), 
		);
		$form->yui_fs->append( 'select', 'align_class', 'align_class', 'Page Alignment:' );
		$form->align_class->options = array(
			'doc-left' => _t('Left', 'gray'), 
			'doc-center' => _t('Centered', 'gray'), 
			'doc-right' => _t('Right', 'gray'), 
		);

		$form->append( 'submit', 'save', _t( 'Save' ) );

		$form->set_option( 'success_message', _t( 'Configuration saved.', 'gray' ) );
		$form->out();
	}
	
	public function filter_block_list($block_list)
	{
		$block_list['grayposts'] = _t('Posts (From Gray)');
		return $block_list;
	}
	
	public function action_block_content_grayposts($block, $theme)
	{
		$criteria = new SuperGlobal(array());
		if ( User::identify()->loggedin ) {
			$criteria['status'] = isset( $_GET['preview'] ) ? Post::status( 'any' ) : Post::status( 'published' );
		}
		else {
			$criteria['status'] = Post::status( 'published' );
		}
		if($block->content_type != '') {
			$criteria['content_type'] = $block->content_type;
			if($block->content_type == 0) {
				unset($criteria['content_type']);
			}
		}
		if($block->limit != '') {
			$criteria['limit'] = $block->limit;
		}
		if($block->offset != '') {
			$criteria['offset'] = $block->offset;
		}
		if($block->tag != '') {
			$criteria['tag'] = $block->tag;
		}

		if($block->main) {
			$where_filters = Controller::get_handler()->handler_vars->filter_keys( $this->valid_filters );
			if ( array_key_exists( 'tag', $where_filters ) ) {
				$where_filters['tag_slug'] = Utils::slugify($where_filters['tag']);
				unset( $where_filters['tag'] );
			}
	
			$where_filters = Plugins::filter( 'template_where_filters', $where_filters );
			$criteria = $criteria->merge($where_filters);
		}
		
		$block->posts = Posts::get($criteria);
		//$block->posts = Posts::get('limit=5&status=2&content_type=0');
		$block->criteria = $criteria;
	}
	
	public function action_block_form_grayposts($form, $block)
	{
		$form->append('select', 'content_type', $block, 'Content Type:', array_flip(Post::list_active_post_types()));
		$form->append('text', 'limit', $block, 'Limit:');
		$form->limit->add_validator('validate_range', 1, 999);
		$form->append('text', 'offset', $block, 'Offset:');
		$form->offset->add_validator('validate_range', 0, 999);
		$form->append('text', 'tag', $block, 'Tag:');
		$form->append('checkbox', 'main', $block, 'This block changes based on URL paramters.');

		$form->append('submit', 'save', 'Save');
	}
	
	public function filter_get_scopes($scopes)
	{
		$rules = RewriteRules::get_active();
		$scopeid = 40000;
		foreach($rules as $rule) {
			$scope = new StdClass();
			$scope->id = $scopeid++;
			$scope->name = $rule->name;
			$scope->priority = 15;
			$scope->criteria = array(
				array('request', $rule->name),
			);
			$scopes[] = $scope;
		}

		return $scopes;
	}

	
	public function action_upgrade($oldversion)
	{
		Utils::debug('upgrade ' . $oldversion);die();
	}	
}

?>
