<?php

/**
 * MyTheme is a custom Theme class for the Hemingway theme.
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
// Format::apply( 'nice_date', 'post_pubdate_out', 'm.j' );
// Format::apply( 'nice_date', 'post_pubdate_home', 'm/d/y' );
// Format::apply( 'nice_time', 'post_pubdate_time', 'ga' );
// Apply Format::nice_date() to comment date...
// Format::apply( 'nice_date', 'comment_date', 'F jS, Y' );

// Limit post length to 1 paragraph or 100 characters. As currently implemented
// in home.php and entry.multiple.php, the first post will be displayed in full
// and subsequent posts will be excerpts. search.php uses excerpts for all posts.
// Comment out this line to have full posts.
//Format::apply_with_hook_params( 'more', 'post_content_excerpt', '', 100, 1 );
Format::apply_with_hook_params( 'more', 'post_content_out', '', 100, 4 );

// We must tell Habari to use MyTheme as the custom theme class:
define( 'THEME_CLASS', 'MyTheme' );

/**
 * A custom theme for Hemingway output
 */
class MyTheme extends Theme
{
	//Set to 'white' or 'black' depending on your color scheme.
	const CSS_COLOR = 'black';
	
	//Set to 'header' or 'sidebar' depending on where you want to position your twitter plugin.
	const TWITTER_IN = 'sidebar';
	
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
		$this->assign('css_color', self::CSS_COLOR);
		$this->assign('twitter_in', self::TWITTER_IN);
		
		if( !$this->template_engine->assigned( 'pages' ) ) {
				$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) ) );
		}
		if( !$this->template_engine->assigned( 'user' ) ) {
				$this->assign('user', User::identify() );
		}
		if( !$this->template_engine->assigned( 'page' ) ) {
				$this->assign('page', isset( $page ) ? $page : 1 );
		}
		if( !$this->template_engine->assigned( 'tags' ) ) {
				$tags= DB::get_results( 'SELECT * FROM ' . DB::table('tags') );
				$tags= array_filter($tags, create_function('$tag', 'return (Posts::count_by_tag($tag->tag_slug, "published") > 0);'));
				$this->assign('tags', $tags);
		}

		$this->assign( 'nav_pages', Posts::get( array( 'limit' => 5, 'content_type' => 'page', 'status' => 'published', 'orderby' => 'slug ASC' )));
		$this->assign( 'home_recent_posts', Posts::get(array ( 'limit' => 2, 'content_type' => 'entry', 'status' => 'published', 'orderby' => 'pubdate DESC' )));
		$this->assign( 'recent_posts', Posts::get(array ( 'limit' => 3, 'content_type' => 'entry', 'status' => 'published', 'orderby' => 'pubdate DESC' )));

		parent::add_template_vars();
	}
	
	public function filter_post_tags_out($array)
	{	
		if ( empty($array) ) { return 'none'; }
		if ( ! is_array( $array ) ) {
			$array = array ( $array );
		}
		$fn = create_function('$a,$b', 'return "<a href=\\"" . URL::get("display_entries_by_tag", array( "tag" => $b) ) . "\\" rel=\\"tag\\">" . $a . "</a>";');
		$array = array_map($fn, $array, array_keys($array));
		$out = implode(' ', $array);
		return $out;
 	}    

}

?>
