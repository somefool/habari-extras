<?php
class NullTheme extends Theme
{
	/**
	 * Execute on theme init to apply these filters to output
	 */
	public function action_init_theme( )
	{
		// Apply Format::autop() to comment content...
		Format::apply( 'autop', 'comment_content_out' );
		// Truncate content excerpt at "more" or 140 characters...
		Format::apply( 'autop', 'post_content_excerpt' );
		Format::apply_with_hook_params( 'more', 'post_content_excerpt', '', 140, 1 );
	}

	public function add_template_vars( )
	{
		// Auto add FormControl templates
		$user_formcontrols = glob( dirname( __FILE__ ) . '/*.formcontrol.php' );
		foreach ( $user_formcontrols as $file ) {
			$name = substr( $file, strrpos( $file, '/' ) + 1, -1 * strlen( '.formcontrol.php' ) );
			$this->add_template( $this->name . '_' . $name, $file );
		}
		parent::add_template_vars( );
	}
	
	public function filter_body_class( $body_class, $theme )
	{
		if ( isset( $theme->posts ) && count( $theme->posts ) === 1 && $theme->posts instanceof Post ) {
			$body_class[] = Post::type_name( $theme->posts->content_type ) . '-' . $theme->posts->slug;
			$body_class[] = 'single';
		}
		return $body_class;
	}

	public function filter_post_tags_list( $tags )
	{
		if ( !count( $tags ) )
			return 'No tags';

		$tag_list = array();
		foreach ( $tags as $tag ) {
			$tag_list[] = sprintf( '<li><a href="%s" rel="tag">%s</a></li>', URL::get('display_entries_by_tag', array( 'tag' => $tag->term ) ), $tag->term_display );
		}
		return '<ul>' . implode( '', $tag_list ) . '</ul>';
	}

	public function action_form_comment( $form )
	{
		$form->append( 'fieldset', 'cf_fieldset', _t( 'Leave your thoughts' ) );

		$form->cf_fieldset->append( 'wrapper', 'cf_rules' );
		$form->cf_fieldset->cf_rules->append( 'static', 'comment_rules', '<ul><li>' . _t( 'You can use some HTML in your comment.' ) . '</li><li>' . _t( 'Your comment may not display immediately due to spam filtering. Please wait for moderation.' ) . '</li></ul>' );

		$form->cf_fieldset->append( 'wrapper', 'cf_inputarea' );

		$form->cf_content->move_before( $form->cf_commenter );
		$form->cf_content->move_into( $form->cf_fieldset->cf_inputarea );
		$form->cf_content->caption = _t( 'Your Comments' );
		$form->cf_content->placeholder = _t( 'Your Comments' );
		$form->cf_content->required = true;
		$form->cf_content->template = $this->name . '_textarea';

		$form->cf_commenter->move_into( $form->cf_fieldset->cf_inputarea );
		$form->cf_commenter->caption = _t( 'Name' );
		$form->cf_commenter->placeholder = _t( 'Name' );
		$form->cf_commenter->required = true;
		$form->cf_commenter->template = $this->name . '_text';

		$form->cf_email->move_into( $form->cf_fieldset->cf_inputarea );
		$form->cf_email->caption = _t( 'E-mail' );
		$form->cf_email->placeholder = _t( 'E-mail' );
		$form->cf_email->required = true;
		$form->cf_email->template = $this->name . '_email';

		$form->cf_url->move_into( $form->cf_fieldset->cf_inputarea );
		$form->cf_url->caption = _t( 'Website' );
		$form->cf_url->placeholder = _t( 'Website' );
		$form->cf_url->required = false;
		$form->cf_url->template = $this->name . '_url';

		$form->cf_submit->move_into( $form->cf_fieldset->cf_inputarea );
		$form->cf_submit->caption = _t( 'Send' );
		$form->cf_submit->placeholder = _t( 'Send' );
		$form->cf_submit->template = $this->name . '_submit';
	}
	
	public function theme_header( $theme )
	{
		Stack::add( 'template_stylesheet', array( Site::get_url('theme') . '/css/screen.css', 'screen' ), 'main' );
		Stack::out( 'template_stylesheet', array( 'Stack', 'styles' ) );
	}
}
?>