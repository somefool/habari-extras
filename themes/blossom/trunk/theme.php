<?php

/**
* Blossom is a custom Theme class for the Blossom theme.
*
* @package Habari
*/

/**
* @todo This stuff needs to move into the custom theme class:
*/

// Apply Format::autop() to post content...
Format::apply( 'autop', 'post_content_out' );
// Apply Format::autop() to post excerpt...
Format::apply( 'autop', 'post_content_excerpt' );
// Apply Format::autop() to comment content...
Format::apply( 'autop', 'comment_content_out' );
// Apply Format::tag_and_list() to post tags...
Format::apply( 'tag_and_list', 'post_tags_out' );

// Make sure one of the date styles is commented out
// @todo Update Habari's theme configurability code so this can be configured in the admin
// Apply Format::nice_date() to post date - European style
Format::apply( 'nice_date', 'post_pubdate_out', 'g:ia d/m/Y' );
// Apply Format::nice_date() to post date - US style
//Format::apply( 'nice_date', 'post_pubdate_out', 'g:ia m/d/Y' );

// Limit post length to 1 paragraph or 100 characters. This theme only works with excerpts.
Format::apply_with_hook_params( 'more', 'post_content_excerpt', '<span class="read-on">read on</span>', 100, 1 );

// We must tell Habari to use Blossom as the custom theme class:
define( 'THEME_CLASS', 'Blossom' );

/**
* A custom theme for Blossom output
*/
class Blossom extends Theme
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
		if ( !$this->posts ) {
			$this->posts = Posts::get( array( 'content_type' => 'entry', 'status' => Post::status('published') ) );
		}
		$this->top_posts = array_slice((array)$this->posts, 0, 2);
		$params = array( 'content_type' => 'entry', 'status' => Post::status('published'), 'limit' => 7 );
		$this->previous_posts = array_slice((array)Posts::get( $params ), 2, 5);
		if ( !$this->user ) {
			$this->user = User::identify();
		}
		if ( !$this->page ) {
			$this->page = isset( $page ) ? $page : 1;
		}
		// del.icio.us username. Eventually this won't be required and it will be powered by plugins.
		// @todo This should be configurable as well. Or better yet, there should be a delicious plugin.
		$this->assign('delicious', 'michael_c_harris' );

		parent::add_template_vars();
	}

}

?>
