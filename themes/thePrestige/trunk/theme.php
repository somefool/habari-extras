<?php 

/**
 * Prestige is a custom Theme class for the Prestige theme.
 * 
 */ 

/**
 * A custom theme for Prestige output
 */ 
class PrestigeTheme extends Theme
{
	function action_init_theme( $theme )
	{
		// Apply Format::autop() to post content...
		Format::apply( 'autop', 'post_content_out' );
		// Apply Format::autop() to comment content...
		Format::apply( 'autop', 'comment_content_out' );
		// Apply Format::tag_and_list() to post tags...
		Format::apply( 'tag_and_list', 'post_tags_out' );
		// Only uses the <!--more--> tag, with the 'more' as the link to full post
		Format::apply_with_hook_params( 'more', 'post_content_out', 'more' );
		// Creates an excerpt option. echo $post->content_excerpt;
		Format::apply( 'autop', 'post_content_excerpt');
		Format::apply_with_hook_params( 'more', 'post_content_excerpt', '<span class="more">Read more</span>', 150, 1 );
		// Excerpt for lead article
		Format::apply( 'autop', 'post_content_lead');
		Format::apply_with_hook_params( 'more', 'post_content_lead', '<span class="more">Read more</span>', 400, 1 );
		// Apply Format::nice_date() to post date...
		Format::apply( 'nice_date', 'post_pubdate_out', 'F j, Y' );
		// Apply Format::nice_time() to post date...
		//Format::apply( 'nice_time', 'post_pubdate_out', 'g:ia' );
		// Apply Format::nice_date() to comment date
		Format::apply( 'nice_date', 'comment_date_out', 'F j, Y g:ia');
	}

	/**
	 * Add additional template variables to the template output.
	 * 
	 * This function gets executed *after* regular data is assigned to the
	 * template.  So the values here, unless checked, will overwrite any existing 
	 * values.
	 */
	public function action_add_template_vars( $theme, $handler_vars )
	{
		if( !$this->template_engine->assigned( 'pages' ) ) {
			$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => true ) ) );
		}
		if( !$this->template_engine->assigned( 'asides' ) ) {
			//For Asides loop in sidebar.php
			$this->assign( 'asides', Posts::get( array( 'vocabulary' => array( 'tags:tag' => 'aside' ), 'limit'=>5) ) );
		}
		if( !$this->template_engine->assigned( 'recent_comments' ) ) {
			//for recent comments loop in sidebar.php
			$this->assign('recent_comments', Comments::get( array('limit'=>5, 'status'=>Comment::STATUS_APPROVED, 'orderby'=>'date DESC' ) ) );
		}
		if( !$this->template_engine->assigned( 'more_posts' ) ) {
			//Recent posts in sidebar.php
			//visiting page/2 will offset to the next page of posts in the footer /3 etc
			$pagination=Options::get('pagination');
			$this->assign( 'more_posts', Posts::get(array ( 'content_type' => 'entry', 'status' => 'published', 'vocabulary' => array( 'tags:not:tag' => 'asides' ),'offset' => ($pagination)*($this->page), 'limit' => 5 ) ) );
		}
		if( !$this->template_engine->assigned( 'all_tags' ) ) {
			// List of all the tags
			$this->assign('all_tags', Tags::vocabulary()->get_tree() );
		}
		if( !$this->template_engine->assigned( 'all_entries' ) ) {
			$this->assign( 'all_entries', Posts::get( array( 'content_type' => 'entry', 'status' => 'published', 'nolimit' => 1 ) ) );
		}
		
		Stack::add('template_header_javascript', Site::get_url('scripts') . "/jquery.js", 'jquery' );
		Stack::add('template_header_javascript', Site::get_url('theme') . "/js/jquery.bigframe.js", 'jquery.bigframe', 'jquery' );
		Stack::add('template_header_javascript', Site::get_url('theme') . "/js/jquery.dimensions.js", 'jquery.dimensions', 'jquery' );
		Stack::add('template_header_javascript', Site::get_url('theme') . "/js/jquery.tooltip.js", 'jquery.tooltip', 'jquery' );
		
	}

	public function act_display_home( $user_filters= array() )
	{
		//To exclude aside tag from main content loop
	    parent::act_display_home( array( 'not:tag' => 'aside' ) );
	}
	
	public function action_theme_header( $theme )
	{
	
	}

	public function theme_next_post_link( $theme )
	{
		$next_link = '';
		if( isset( $theme->post ) ) {
		$next_post= $theme->post->ascend();
		if( ( $next_post instanceOf Post ) ) {
			$next_link= 'Older <a href="' . $next_post->permalink. '" title="' . $next_post->title .'" >' . '&laquo; ' .$next_post->title . '</a>';
		}
		}
		return $next_link;
	}

	public function theme_prev_post_link( $theme )
	{
		$prev_link = '';

		if( isset( $theme->post ) ) {
		$prev_post= $theme->post->descend();
		if( ( $prev_post instanceOf Post) ) {
			$prev_link= 'Newer <a href="' . $prev_post->permalink. '" title="' . $prev_post->title .'" >' . $prev_post->title . ' &raquo;' . '</a>';
		}
		}
		return $prev_link;
	}

	public function theme_commenter_link($comment)
	{
		$link = '';
		if( strlen( $comment->url ) && $comment->url != 'http://' ) {
			$link = '<a href="' . $comment->url . '" >' . $comment->name . '</a>';
		}

		return $link;
	}
	
	public function theme_feed_site( $theme )
		{
			return URL::get( 'atom_feed', array( 'index' => '1' ) );
		}

	public function action_form_comment( $form, $post, $context )
	{
		$form->append( 'fieldset', 'content_box', _t( 'Add To The Discussion' ) );
		$form->cf_content->move_into( $form->content_box );
		$form->cf_content->cols = 70;
		$form->cf_content->control_title = 'Add to the discussion. Required.';

		$form->append('fieldset', 'commenter_info', _t( 'A Little Info About You' ) );

		$form->cf_commenter->move_into( $form->commenter_info );
		$form->cf_commenter->control_title = 'Your name. Required';
		$form->cf_commenter->caption = _t( 'Name' );

		$form->cf_email->move_into( $form->commenter_info );
		$form->cf_email->control_title = "Your email address. Required, but not published";
		$form->cf_email->caption = _t( 'Email' );

		$form->cf_url->move_into( $form->commenter_info );
		$form->cf_url->control_title = 'Enter your homepage.';
		$form->cf_url->caption = _t( 'Web Address' );

		$form->cf_submit->move_after( $form->commenter_info );
		$form->cf_submit->caption = _t( 'Say It' );

	}

	public function theme_comment_form( $theme )
	{
		$theme->post->comment_form()->out();
//		$ui = new FormUI( 'comments_form' );
//		$ui->set_option( 'form_action',  URL::get( 'submit_feedback', array( 'id' => $theme->post->id ) ) );

//		$comment_fieldset = $ui->append( 'fieldset', 'content_box', _t( 'Add To The Discussion' ) );
//		$commentContent = $comment_fieldset->append( 'textarea', 'commentContent', 'null:null', _t( 'Comment' ));
//		$commentContent->value = $theme->commenter_content;
//		$commentContent->id = 'comment_content';
//		$commentContent->control_title = 'Add to the discussion. Required.';
//		$commentContent->cols = 70;

//		$comment_info_fieldset = $ui->append('fieldset', 'commenter_info', _t( 'A Little Info About You' ) );
//		$name = $comment_info_fieldset->append( 'text', 'ename', 'null:null', _t( 'Name' ) );
//		$name->value = $theme->commenter_name;
//		$name->id = 'name';
//		$name->control_title = 'Your name. Required';

//		$email = $comment_info_fieldset->append( 'text', 'email', 'null:null', _t('Email' ));
//		$email->value = $theme->commenter_email;
//		$email->id = $email->name;
//		$email->control_title = "Your email address. Required, but not published";

//		$url = $comment_info_fieldset->append( 'text', 'url', 'null:null', _t( 'Web Address' ) );
//		$url->value = $theme->commenter_url;
//		$url->id = $url->name;
//		$url->control_title = 'Enter your homepage.';


//		$submit = $ui->append( 'submit', 'submit', _t( 'Say It' ) );
//		$submit->id = $submit->name;

//		$ui->out();

	}

}

?>
