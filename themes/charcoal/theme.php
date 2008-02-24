<?php

define( 'THEME_CLASS', 'charcoal' );

class charcoal extends Theme
{
	//Set to true to show the title image, false to display the title text.
	const SHOW_TITLE_IMAGE = false;

	//Set to whatever you want your fist tab text to be.
	const HOME_LABEL = 'Blog';

	//Set to true to show the paperclip graphic in posts, false to hide it.
	const SHOW_ENTRY_PAPERCLIP = true;

	//Set to true to show the paperclip graphic in pages, false to hide it.
	const SHOW_PAGE_PAPERCLIP = false;

	//Set to true to show the "powered by Habari" graphic in the sidebar, false to hide it.
	const SHOW_POWERED = true;
	
	//Set to true to show the Login/Logout link in the navigation bar, false to hide it.
	const DISPLAY_LOGIN = true;
	
	/**
	 * Execute on theme init to apply these filters to output
	 */
	public function action_init_theme()
	{
		// Apply Format::autop() to post content...
		Format::apply( 'autop', 'post_content_out' );
		// Apply Format::autop() to comment content...
		Format::apply( 'autop', 'comment_content_out' );
		// Apply Format::nice_date() to post date...
		Format::apply( 'nice_date', 'post_pubdate_out', 'F j, Y g:ia' );
		// Apply Format::nice_date() to comment date...
		Format::apply( 'nice_date', 'comment_date', 'F j, Y g:ia' );
		// Truncate content excerpt at "more" or 56 characters...
		Format::apply_with_hook_params( 'more', 'post_content_excerpt', '',56, 1 );
	}
	
	/**
	 * Add some variables to the template output
	 */
	public function add_template_vars()
	{
		// Use theme options to set values that can be used directly in the templates
		// Don't check for constant values in the template code itself
		$this->assign('show_title_image', self::SHOW_TITLE_IMAGE);
		$this->assign('home_label', self::HOME_LABEL);
		$this->assign('show_powered', self::SHOW_POWERED);
		$this->assign('display_login', self::DISPLAY_LOGIN);
		$this->assign('post_class', 'post' . ( ! self::SHOW_ENTRY_PAPERCLIP ? ' alt' : '' ) );
		$this->assign('page_class', 'post' . ( ! self::SHOW_PAGE_PAPERCLIP ? ' alt' : '' ) );
		
		if( !$this->template_engine->assigned( 'pages' ) ) {
			$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) ) );
		}
		if( !$this->template_engine->assigned( 'user' ) ) {
			$this->assign('user', User::identify() );
		}
		if( !$this->template_engine->assigned( 'page' ) ) {
			$this->assign('page', isset( $page ) ? $page : 1 );
		}
		$this->assign( 'post_id', ( isset($this->post) && $this->post->content_type == Post::type('page') ) ? $this->post->id : 0 );
		parent::add_template_vars();
	}
	/**
	 * returns the previous and/or next page links based on the current matched rule
	 */
	public function theme_prevnext($theme,$currentpage, $totalpages){
		//Retreive the current matched rule
		$rr= URL::get_matched_rule();
		// Retrieve arguments name the RewriteRule can use to build a URL.
		$rr_named_args= $rr->named_args;
		$rr_args= array_merge( $rr_named_args['required'], $rr_named_args['optional']  );
		// For each argument, check if the handler_vars array has that argument and if it does, use it.
		$rr_args_values= array();
		foreach ( $rr_args as $rr_arg ) {
			if ( !isset( $settings[$rr_arg] ) ) {
				$rr_arg_value= Controller::get_var( $rr_arg );
				if ( $rr_arg_value != '' ) {
					$settings[$rr_arg]= $rr_arg_value;
				}
			}
		}
		if ( !empty( $settings) )
		{
			$url= Site::get_url( 'habari', true ). $rr->build($settings) . '/page/';
		}
		else{
			$url=Site::get_url( 'habari', true ).'page/';
		}
		
		$out='';
		if ( $currentpage > $totalpages ) {
			$currentpage= $totalpages;
		}
		else if ( $currentpage < 1 ) {
			$currentpage= 1;
		}
		if ($currentpage < $totalpages){
			$out.='<span class="nav-prev"><a href="' . $url .($currentpage+1).'">Older Posts</a></span>';
		}
		if ($currentpage > 1){
			$out.='<span class="nav-next"><a href="'. $url .'page/'.($currentpage-1).'">Newer Posts</a></span>';
		}
		echo $out;
	}
	
	/**
	 * Convert a post's tags array into a usable list of links
	 *
	 * @param array $array The tags array from a Post object
	 * @return string The HTML of the linked tags
	 */
	public function filter_post_tags_out($array)
	{
		if ( ! is_array( $array ) ) {
			$array = array ( $array );
		}
		$fn = create_function('$a,$b', 'return "<a href=\\"" . URL::get("display_entries_by_tag", array( "tag" => $b) ) . "\\" rel=\\"tag\\">" . $a . "</a>";');
		$array = array_map($fn, $array, array_keys($array));
		$out = implode(' ', $array);
		return $out;
 	}
	
	public function theme_post_comments_link($theme, $post, $zero, $one, $more)
	{
		$c=$post->comments->approved->count;
		switch ($c) {
			case '0':
				return $zero;
				break;
			case '1':
				return str_replace('%s','1',$one);
				break;
			default :
				return str_replace('%s',$c,$more);
		}
	}
		
	public function filter_post_content_excerpt($return)
	{	
 		return strip_tags($return);
 	}

	
}
?>
