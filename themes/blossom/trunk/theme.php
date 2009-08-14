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
		*  Also, this function gets executed *after* regular data is assigned to the
		*  template.  So the values here, unless checked, will overwrite any existing
		*  values.
		*/
	public function add_template_vars()
	{
		// Add FormUI template placing the input before the label
		$this->add_template( 'blossom_text', dirname(__FILE__) . '/formcontrol_text.php' );


		$this->habari = Site::get_url( 'habari' );
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
		if ( !$this->tags ) {
			$this->tags = Tags::get();
		}
		if ( !$this->pages ) {
			$this->pages = Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published') ) );
		}

		// Use the configured data format
		$date_format = Options::get('blossom_date_format');
		if ( $date_format == 'american' ) {
			// Apply Format::nice_date() to post date - US style
			Format::apply( 'nice_date', 'post_pubdate_out', 'g:ia m/d/Y' );
		}
		else {
			// Apply Format::nice_date() to post date - European style
			Format::apply( 'nice_date', 'post_pubdate_out', 'g:ia d/m/Y' );
		}

		// del.icio.us username.
		$delicious_username = Options::get('blossom_delicious_username');
		if ( $delicious_username == '' ) {
			$delicious_username = 'michael_c_harris';
		}
		$this->delicious = $delicious_username;

		// Default to hidden
		$this->show_interests = (bool) Options::get('show_interests');
		$this->show_other_news = (bool) Options::get('show_other_news');

		parent::add_template_vars();
	}

	/**
	* Make this theme configurable
	*
	* @param boolean $configurable Whether the theme is configurable
	* @param string $name The theme name
	* @return boolean Whether the theme is configurable
	*/

	public function filter_theme_config($configurable)
	{
		$configurable = true;
		return $configurable;
	}

	/**
	* Respond to the user selecting 'configure' on the themes page
	*
	*/
	public function action_theme_ui()
	{
		$form = new FormUI( 'blossom_theme' );
		$form->append('text', 'delicious_username', 'blossom_delicious_username', _t('Delicious Username:'));
		$form->append( 'select', 'date_format', 'blossom_date_format', 'Date format:' );
		$form->date_format->options = array('european' => 'European', 'american' => 'American');

		$form->append('fieldset', 'show_interests_fs', 'Show "Interests"?');
		$form->show_interests_fs->append('radio', 'show_interests', 'option:show_interests', 'Show "Interests"?', array("1" => "Yes", "0" => "No"));
		$form->append('fieldset', 'show_other_news_fs', 'Show "Other News"?');
		$form->show_other_news_fs->append('radio', 'show_other_news', 'option:show_other_news', 'Show "Other News"?', array("1" => "Yes", "0" => "No"));

		$form->append( 'submit', 'save', _t( 'Save' ) );

		$form->set_option( 'success_message', _t( 'Configuration saved' ) );
		$form->out();
	}

	/**
	 * Customize comment form layout.
	 */
	public function action_form_comment( $form ) { 
		$form->name = 'comment-form';
		$form->append( 'static','formhead', _t( '<h3 class="formhead">Have your say</h3>' ) );
		$form->move_before( $form->formhead, $form->commenter );
		$form->commenter->caption = '<small>' . _t( 'Name' ) . '</small>';
		$form->commenter->template = 'blossom_text';
		$form->email->caption = _t('Email (will not be published)');
		$form->email->template = 'blossom_text';
		$form->url->caption = _t('Website');
		$form->url->template = 'blossom_text';
	        $form->content->caption = '';
		$form->append( 'static','disclaimer', _t( '<p>I reserve the right to delete any comments I don\'t like.</p>' ) );
		$form->move_after( $form->disclaimer, $form->content );
		$form->submit->caption = _t( 'Add your comment' );
		$form->submit->class[] = 'formactions';
	}

}

?>
