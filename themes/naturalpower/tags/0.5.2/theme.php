<?php

/**
 * MyTheme is a custom Theme class for the K2 theme.
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
Format::apply( 'nice_date', 'post_pubdate_out', 'l, d F Y' );
// Apply Format::nice_date() to comment date...
Format::apply( 'nice_date', 'comment_date', 'l, d F Y' );

$header_text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt.';

// Remove the comment on the following line to limit post length on the home page to 2 paragraphs or 150 characters
//Format::apply_with_hook_params( 'more', 'post_content_out', 'Continue Reading &raquo;', 150, 2 );

// We must tell Habari to use MyTheme as the custom theme class:
define( 'THEME_CLASS', 'MyTheme' );

/**
 * A custom theme for K2 output
 */
class MyTheme extends Theme
{	

	public function add_template_vars()
	{
		//Theme Options
		$this->assign('header_text','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt.');
		
		if( !$this->template_engine->assigned( 'pages' ) ) {
			$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) ) );
		}
		if( !$this->template_engine->assigned( 'user' ) ) {
			$this->assign('user', User::identify() );
		}
		if( !$this->template_engine->assigned( 'page' ) ) {
			$this->assign('page', isset( $page ) ? $page : 1 );
		}
		
		if( !$this->template_engine->assigned( 'all_tags' ) ) {
		
		// List of all the tags
		$tags= DB::get_results( 'SELECT tag_text as tag, tag_slug as slug FROM ' . DB::table('tags') . ' ORDER BY tag_text ASC' );
		$this->assign('all_tags', $tags);}

		parent::add_template_vars();		
		//visiting page/2, /3 will offset to the next page of posts in the sidebar
		$page=Controller::get_var( 'page' );
		$pagination=Options::get('pagination');
		if ( $page == '' ) { $page= 1; }
		$this->assign( 'more_posts', Posts::get(array ( 'status' => 'published','content_type' => 'entry','offset' => ($pagination)*($page), 'limit' => 5,  ) ) );
			
	}
	
	/*public function gravatar($rating = false, $size = false, $default = false, $border = false) 
	{
		$out = "http://www.gravatar.com/avatar.php?gravatar_id=".md5( $posts->comments->moderated->email );
		if($rating && $rating != '')
			$out .= "&amp;rating=".$rating;
		if($size && $size != '')
			$out .="&amp;size=".$size;
		if($default && $default != '')
			$out .= "&amp;default=".urlencode($default);
		if($border && $border != '')
			$out .= "&amp;border=".$border;
		echo $out;
	}*/
}
?>
