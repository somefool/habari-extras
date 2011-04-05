<?php

class Aligned extends Theme
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
		$this->add_template('formcontrol_text', dirname(__FILE__).'/forms/formcontrol_text.php', true);
		$this->add_template('formcontrol_textarea', dirname(__FILE__).'/forms/formcontrol_textarea.php', true);

		$this->recent_comments = Comments::get( array('limit' => 5, 'status' => Comment::STATUS_APPROVED, 'orderby' => 'date DESC' ) );
		$this->recent_posts = Posts::get( array('limit' => 5, 'orderby' => 'pubdate DESC', 'content_type' => Post::type('entry'), 'status' => Post::status('published') ) );
		if ( !$this->template_engine->assigned( 'pages' ) ) {
			$this->pages = Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) );
		}
		parent::add_template_vars();
	}

	public function filter_theme_call_header( $return, $theme )
	{
		if ( User::identify() != false ) {
			Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		}
		return $return;
	}
	
	public function theme_header_image()
	{
		$imglist = '';
		
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

	public function action_form_comment( $form ) { 
		$form->cf_commenter->caption = 'Name';
		$form->cf_email->caption = 'Mail';
		$form->cf_url->caption = 'Website';
	}

}

?>
