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
Format::apply( 'nice_date', 'post_pubdate_out', 'F j, Y g:ia' );
// Apply Format::nice_date() to comment date...
Format::apply( 'nice_date', 'comment_date_out', 'F j, Y g:ia' );

Format::apply_with_hook_params( 'more', 'post_content_out', 'more', 100, 1 );

// We must tell Habari to use MyTheme as the custom theme class: 
define( 'THEME_CLASS', 'MyTheme' );

/**
 * A custom theme for K2 output
 */ 
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
 		$this->add_template('formcontrol_text', dirname(__FILE__).'/forms/formcontrol_text.php', true);
		$this->add_template('formcontrol_textarea', dirname(__FILE__).'/forms/formcontrol_textarea.php', true);

		if( !$this->template_engine->assigned( 'pages' ) ) {
			$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) ) );
		}
		if( !$this->template_engine->assigned( 'user' ) ) {
			$this->assign('user', User::identify() );
		}
		if( !$this->template_engine->assigned( 'page' ) ) {
			$this->assign('page', isset( $page ) ? $page : 1 );
		}
		if( !$this->template_engine->assigned( 'feed_alternate' ) ) {
			$matched_rule= URL::get_matched_rule();
			switch ( $matched_rule->name ) {
				case 'display_entry':
				case 'display_page':
					$feed_alternate= URL::get( 'entry', array( 'slug' => Controller::get_var('slug') ) );
					break;
				case 'display_entries_by_tag':
					$feed_alternate= URL::get( 'tag_collection', array( 'tag' => Controller::get_var('tag') ) );
					break;
				case 'index_page':
				default:
					$feed_alternate= URL::get( 'collection', array( 'index' => '1' ) );
			}
			$this->assign('feed_alternate', $feed_alternate);
		}
		parent::add_template_vars();
	}
	
	public function header()
	{
		if ( User::identify() != FALSE ) {
			Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		}
	}

	public function action_form_comment( $form ) { 
		$form->cf_commenter->caption = '<strong>Name</strong>';
		$form->cf_email->caption = '<strong>Mail</strong> (will not be published)';
		$form->cf_url->caption = '<strong>Website</strong>';
	}

}

?>
