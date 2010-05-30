<?php

// Apply Format::tag_and_list() to post tags...
Format::apply( 'tag_and_list', 'post_tags_out' );

// Limit post length on the home page to 1 paragraph or 100 characters
Format::apply( 'autop', 'post_content_excerpt' );
Format::apply_with_hook_params( 'more', 'post_content_excerpt', '', 100, 1 );

// Apply Format::nice_date() to post and comment date...
Format::apply( 'nice_date', 'post_pubdate_out', 'F j, Y g:ia' );
Format::apply( 'nice_date', 'comment_date_out', 'F j, Y g:ia' );

define( 'THEME_CLASS', 'wings' );

class wings extends Theme
{

	public function add_template_vars()
	{
		if( !$this->template_engine->assigned( 'pages' ) ) {
			$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) ) );
		}
	}

	public function action_form_comment( $form )
	{
		$this->add_template('formcontrol_text', dirname(__FILE__).'/forms/formcontrol_text.php', true);
		$this->add_template('formcontrol_textarea', dirname(__FILE__).'/forms/formcontrol_textarea.php', true);

		$form->cf_commenter->caption = '<strong>Name</strong> *Required';
		$form->cf_email->caption = '<strong>Mail</strong> (will not be published) *Required';
		$form->cf_url->caption = '<strong>Website</strong>';
	}
}

?>
