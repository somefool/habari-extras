<?php 

/**
 * ArcticIceTheme is a custom Theme class for the Arctic Ice theme.
 * 
 */ 


/**
 * A custom theme for Arctic Ice output
 */ 
class ArcticIceTheme extends Theme
{
	public function action_init_theme( $theme )
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
		Format::apply_with_hook_params( 'more', 'post_content_excerpt', ' Read more...', 60, 1 );

		// Apply Format::nice_date() to post date...
		Format::apply( 'nice_date', 'post_pubdate_out', 'F j, Y' );
		Format::apply( 'nice_date', 'post_pubdate_datetime', 'c');
		// Apply Format::nice_time() to post date...
		//Format::apply( 'nice_time', 'post_pubdate_out', 'g:ia' );
		// Apply Format::nice_date() to comment date
		Format::apply( 'nice_date', 'comment_date_out', 'F j, Y g:ia');
	}

	/**
	 * Configuration form for the ArcticIceTheme
	 **/
	public function action_theme_ui( $theme )
	{
		$ui = new FormUI( __CLASS__ );
		$slugs = $ui->append( 'textarea', 'slugs', __CLASS__.'__slugs', _t( 'Terms to exclude from pages:' ) );
		$slugs->rows = 8;
		$slugs->class[] = 'resizable';
		$ui->append( 'submit', 'save', _t( 'Save' ) );
		$ui->set_option( 'success_message', _t( 'Options saved' ) );
		$ui->out();

//		$ui->append( 'text', 'tags_count', __CLASS__.'__tags_count', _t( 'Tag Cloud Count:' ), 'optionscontrol_text' );
//			$ui->tags_count->helptext = _t( 'Set to the number of tags to display on the default "cloud".' );
	}

	/**
	 * Add additional template variables to the template output.
	 * 
	 * This function gets executed *after* regular data is assigned to the
	 * template.  So the values here, unless checked, will overwrite any existing 
	 * values.
	 */
	public function add_template_vars()
	{
		parent::add_template_vars();
		$opts = Options::get_group( __CLASS__ );
		$terms = array();
		if( isset( $opts['slugs'] ) ) {
			$slugs = explode( '\r\n', $opts['slugs'] );
			foreach( $slugs as $slug ) {
				$terms[] = Tags::get_by_slug( $slug );
			}
		}
		if( !$this->template_engine->assigned( 'pages' ) ) {
//			$this->assign( 'pages', Posts::get( array( 'content_type' => 'page', 'status' => 'published', 'vocabulary' => array( 'not' => array( Tags::get_by_slug( 'site-policy' ) ) ), 'nolimit' => 1 ) ) );
//			$this->assign( 'pages', Posts::get( array( 'content_type' => 'page', 'status' => 'published', 'vocabulary' => array( 'tags:not:term' => 'site-policy' ), 'nolimit' => 1 ) ) );
			$this->assign( 'pages', Posts::get( array( 'content_type' => 'page', 'status' => 'published', 'vocabulary' => array( 'not' => $terms ), 'nolimit' => 1 ) ) );
		}
	}

	public function action_template_header( $theme )
	{
		// Add the stylesheets to the stack for output
		Stack::add( 'template_stylesheet', array( Site::get_url( 'theme') . '/style.css', 'screen') );
		Stack::add( 'template_stylesheet', array( Site::get_url( 'theme') . '/custom.css', 'screen') );
		Stack::add( 'template_stylesheet', array( Site::get_url( 'theme') . '/print.css', 'print') );
	}

	public function theme_title( $theme )
	{
		$out = '';
		if( $theme->request->display_entry || $theme->request->display_page && isset( $theme->post ) ) {
			$out = $theme->post->title . ' - ';
		}
		else if( $theme->request->display_entries_by_tag && isset( $theme->posts ) ) {
			$out = $theme->tag . ' - ';
		}
		$out .= Options::get( 'title' );
		return $out;
	}

	public function theme_next_post_link( $theme )
	{
		$next_link = '';
		if( isset( $theme->post ) ) {
			$next_post = $theme->post->ascend();
			if( ( $next_post instanceOf Post ) ) {
				$next_link = '<a href="' . $next_post->permalink. '" title="' . $next_post->title .'" >' . '&laquo; ' .$next_post->title . '</a>';
			}
		}

		return $next_link;
	}

	public function theme_prev_post_link( $theme )
	{
		$prev_link = '';

		if( isset( $theme->post ) ) {
		$prev_post = $theme->post->descend();
		if( ( $prev_post instanceOf Post) ) {
			$prev_link= '<a href="' . $prev_post->permalink. '" title="' . $prev_post->title .'" >' . $prev_post->title . ' &raquo;' . '</a>';
		}
		}
		return $prev_link;
	}

	public function theme_commenter_link( $comment )
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

	public function theme_comment_form( $theme )
	{
		$theme->post->comment_form()->out();
	}

	public function theme_all_entries( $theme )
	{
		$out = '';
		$posts = Posts::get( array( 'content_type' => 'entry', 'status' => 'published', 'nolimit' => 1 ) );
		foreach( $posts as $post ) {
			$out .= "<p><a href=\"{$post->permalink}\" rel=\"bookmark\" title=\"{$post->title}\">{$post->title}</a> ( {$post->comments->approved->count} )</p>";
		}
		return $out;
	}

	public function action_form_comment( $form, $post, $context )
	{
		$form->cf_content->cols = 57;
		$form->cf_commenter->size = 50;
		$form->cf_email->size = 50;
		$form->cf_url->size = 50;
	}

}

?>
