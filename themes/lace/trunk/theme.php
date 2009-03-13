<?php

/**
 * MyTheme is a custom Theme class for the Lace theme.
 *
 * @package Habari
 */

/**
 * @todo This stuff needs to move into the custom theme class:
 */

// Apply Format::autop() to post content...
Format::apply( 'autop', 'post_content_out' );
// Apply Format::autop() to comment content...
Format::apply( 'autop', 'comment_content_out' );
// Apply Format::tag_and_list() to post tags...
Format::apply( 'tag_and_list', 'post_tags_out' );
// Apply Format::nice_date() to post date...
Format::apply( 'nice_date', 'post_pubdate_out', 'F j, Y g:ia' );

// Add calls for curvycorners and jquery
Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
Stack::add( 'template_header_javascript', Site::get_url('theme') . '/js/jquery.curvycorners.js');

// Remove the comment on the following line to limit post length on the home page to 1 paragraph or 100 characters
//Format::apply_with_hook_params( 'more', 'post_content_out', 'more', 100, 1 );

// We must tell Habari to use MyTheme as the custom theme class:
define( 'THEME_CLASS', 'MyTheme' );

class MyTheme extends Theme
{

	/**
	 * Add additional template variables to the template output.
	 *
	 *  You can assign additional output values in the template here, instead of
	 *  having the PHP execute directly in the template.  The advantage is that
	 *  you would easily be able to switch between template types (RawPHP/Smarty)
	 *  without having to port code from one to the other.
	 *
	 *  You could use this area to provide "recent comments" data to the template,
	 *  for instance.
	 *
	 *  Note that the variables added here should possibly *always* be added,
	 *  especially 'user'.
	 *
	 *  Also, this function gets executed *after* regular data is assigned to the
	 *  template.  So the values here, unless checked, will overwrite any existing
	 *  values.
	 */
	public function add_template_vars()
	{
		//Theme Options
		$this->assign('home_tab','Home'); //Set to whatever you want your first tab text to be.
		
		
		if( !$this->template_engine->assigned( 'pages' ) ) {
			$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) ) );
		}
		if( !$this->template_engine->assigned( 'user' ) ) {
			$this->assign('user', User::identify() );
		}
		if( !$this->template_engine->assigned( 'page' ) ) {
			$this->assign('page', isset( $page ) ? $page : 1 );
		}
		parent::add_template_vars();
	}

	public function filter_theme_call_header( $return, $theme )
	{
		if ( User::identify() != FALSE ) {
			Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
					}
		return $return;
	}

}

?>
