<?php

define( 'THEME_CLASS', 'Unknown' );

class Unknown extends Theme
{

	public function action_init_theme()
	{
		// Apply Format::autop() to post content...
		Format::apply( 'autop', 'post_content_out' );
		// Only uses the <!--more--> tag, with the 'more' as the link to full post
		Format::apply_with_hook_params( 'more', 'post_content_out', 'more' );
		// Creates an excerpt option. echo $post->content_excerpt;
		Format::apply_with_hook_params( 'more', 'post_content_excerpt', 'more', 60, 1 );
		// Apply Format::autop() to comment content...
		Format::apply( 'autop', 'comment_content_out' );
		// Apply Format::tag_and_list() to post tags...
		Format::apply( 'tag_and_list', 'post_tags_out' );
		// Apply Format::nice_date() to comment date...
		Format::apply( 'nice_date', 'comment_date_out', 'F jS, Y' );
		// Apply Format::nice_date() to post date...
		Format::apply( 'nice_date', 'post_pubdate_out', 'F jS, Y' );
	}

	public function add_template_vars()
	{
		$this->assign('recent_comments', Comments::get( array('limit'=>5, 'status'=>Comment::STATUS_APPROVED, 'orderby'=>'date DESC' ) ) );
		$this->assign('recent_posts', Posts::get( array('limit'=>5, 'orderby'=>'pubdate DESC', 'content_type'=>1, 'status'=>2 ) ) );
		
		if ( '' != Controller::get_var('tag') ) {
		     $tag_text= DB::get_value('SELECT tag_text FROM {tags} WHERE tag_slug=?', array( Controller::get_var('tag') ) );
		     $this->assign('tag_text', $tag_text);
		}
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
	
	public function theme_header_image()
	{
		$imglist='';
		
		mt_srand((double)microtime()*1000);
		
		$imgs = dir(Site::get_dir('theme')."/headers/");
		
		while ($file = $imgs->read()) {
			if (eregi("gif", $file) || eregi("jpg", $file) || eregi("png", $file))
			$imglist .= "$file ";
		} closedir($imgs->handle);
		
		$imglist = explode(" ", $imglist);
		$no = sizeof($imglist)-2;
		$random = mt_rand(0, $no);
		
		return $imglist[$random];
	}

}

?>
