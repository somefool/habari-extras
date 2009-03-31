<?php

/**
 * MyTheme is a custom Theme class for the Menyu theme.
 *
 * @package Habari
 */

// Apply Format::autop() to post content...
Format::apply( 'autop', 'post_content_out' );
// Apply Format::autop() to comment content...
Format::apply( 'autop', 'comment_content_out' );
// Apply Format::tag_and_list() to post tags...
Format::apply( 'tag_and_list', 'post_tags_out' );
// Apply Format::nice_date() to post date...
Format::apply( 'nice_date', 'post_pubdate_out', 'j F Y' );

// We must tell Habari to use MyTheme as the custom theme class:
define( 'THEME_CLASS', 'MyTheme' );

/**
 * A custom theme
 */
class MyTheme extends Theme
{
	public function add_template_vars()
	{
		// Jquery
                Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
                Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/ui.core.js', 'ui.core', array( 'jquery' ) );
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/ui.tabs.js', 'ui.tabs', array( 'ui.core' ) );
		Stack::add( 'template_header_javascript', Site::get_url('theme') . '/scripts/ui.accordion.js', 'ui.accordion', array( 'ui.core' ) );
		// CSS
		Stack::add( 'template_stylesheet', array( Site::get_url('3rdparty') . '/blueprint/screen.css', 'screen,projection') );
		Stack::add( 'template_stylesheet', array( Site::get_url('theme') . '/style.css'	, 'all' ) );

		parent::add_template_vars();
	}

	public function filter_theme_call_header( $return, $theme )
	{
		// Jquery
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
                Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/ui.core.js', 'ui.core', array( 'jquery' ) );
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/ui.tabs.js', 'ui.tabs', array( 'ui.core' ) );
		Stack::add( 'template_header_javascript', Site::get_url('theme') . '/scripts/ui.accordion.js', 'ui.accordion', array( 'ui.core' ) );

		return $return;
	}
	/**
	 * Returns an unordered list of all used Tags
	 */
	public function theme_show_tags ( $theme )
	{
		$sql ="
			SELECT t.tag_slug AS slug, t.tag_text AS text, count(tp.post_id) as ttl
			FROM {tags} t
			INNER JOIN {tag2post} tp
			ON t.id=tp.tag_id
			INNER JOIN {posts} p
			ON p.id=tp.post_id AND p.status = ?
			GROUP BY t.tag_slug
			ORDER BY t.tag_text
		";
		$tags = DB::get_results( $sql, array(Post::status('published')) );

		foreach ($tags as $index => $tag) {
			$tags[$index]->url = URL::get( 'display_entries_by_tag', array( 'tag' => $tag->slug ) );
		}
		$theme->taglist = $tags;
		
		return $theme->fetch( 'taglist' );
	}

}

?>

