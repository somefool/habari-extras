<?php

class Shelves extends Plugin
{
	private static $vocabulary = 'shelves';
	private static $content_type = 'entry';

	protected $_vocabulary;


	public function  __get( $name )
	{
		switch ( $name ) {
			case 'vocabulary':
				if ( !isset( $this->_vocabulary ) ) {
					$this->_vocabulary = Vocabulary::get( self::$vocabulary );
				}
				return $this->_vocabulary;
		}
	}
	/**
	 * Add the shelf vocabulary and create the admin token
	 *
	 **/
	public function action_plugin_activation( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file(__FILE__) ) {
			$params = array(
				'name' => self::$vocabulary,
				'description' => 'A vocabulary for describing Shelves',
				'features' => array( 'hierarchical' )
			);

			Vocabulary::create( $params );

			// create default access token
			ACL::create_token( 'manage_shelves', _t( 'Manage ') . Options::get( 'shelves__plural', _t( 'shelves', 'shelves' ) ), 'Administration', false );
			$group = UserGroup::get_by_name( 'admin' );
			$group->grant( 'manage_shelves' );
		}
	}

	/**
	 * Remove the admin token
	 *
	 **/
	public function action_plugin_deactivation( $file )
	{
			// delete default access token
			ACL::destroy_token( 'manage_shelves' );
	}

	/**
	 * Register admin template
	 **/
	public function action_init()
	{
		$this->add_template( 'shelves', dirname( $this->get_file() ) . '/shelves_admin.php' );
	}

	/**
	 * Check token to restrict access to the page
	 **/
	public function filter_admin_access_tokens( array $require_any, $page )
	{
		switch ( $page ) {
			case 'shelves':
				$require_any = array( 'manage_shelves' => true );
				break;
		}
		return $require_any;
	}

	/**
	 * Display the page
	 **/
	public function action_admin_theme_get_shelves( AdminHandler $handler, Theme $theme )
	{

		$all_terms = array();
		$all_terms = $this->vocabulary->get_tree();
		$one_shelf = ucfirst( Options::get( 'shelves__single', _t( 'shelf', 'shelves' ) ) );
		if (!isset( $_GET[ 'shelf' ] ) ) { // create new shelf form

			$form = new FormUI( 'shelf-new' );
			$form->set_option( 'form_action', URL::get( 'admin', 'page=shelves' ) );

			$create_fieldset = $form->append( 'fieldset', '', _t( 'Create a new %s', array( $one_shelf ) , 'shelves' ) );
			$shelf = $create_fieldset->append( 'text', 'shelf', 'null:null', $one_shelf, 'formcontrol_text' );
			$shelf->add_validator( 'validate_required' );

			$shelf->class = 'pct30';
			$parent = $create_fieldset->append( 'select', 'parent', 'null:null', _t( 'Parent', 'shelves' ), 'optionscontrol_select' ); // $template doesn't work
			$parent->class = 'pct50';
			$parent->options = array();
			$parent->options[''] = ''; // top should be blank
			$right = array();
			foreach( $all_terms as $term ) {
				while ( count( $right ) > 0 && $right[count( $right ) - 1] < $term->mptt_right ) {
					array_pop( $right );
				}
				$parent->options[ $term->id ] = str_repeat( ' - ', count( $right ) ) . $term->term_display;
				$right[] = $term->mptt_right;
			}
			$save_button = $create_fieldset->append( 'submit', 'save', _t( 'Create', 'shelves' ) );
			$save_button->class = 'pct20 last';

			$cancelbtn = $form->append( 'button', 'btn', _t( 'Cancel', 'shelves' ) );

			$form->on_success( array( $this, 'formui_create_submit' ) );

 		} 
		else { // edit form for existing shelf

			$which_shelf = $_GET[ 'shelf' ];
			$shelf_term = $this->vocabulary->get_term( $which_shelf );
			if ( !$shelf_term ) {
				exit;
			}

			$parent_term = $shelf_term->parent();
			if ( !$parent_term ) {
				$parent_term_display = _t( 'none', 'shelves' );
			}
			else {
				$parent_term_display = $parent_term->term_display;
			}

			$form = new FormUI( 'shelf-edit' );
			$form->set_option( 'form_action', URL::get( 'admin', 'page=shelves&shelf=' . $_GET[ 'shelf' ] ) );
			$shelf_id = $form->append( 'hidden', 'shelf_id' )->value = $shelf_term->id; // send this id, for seeing what has changed
			$edit_fieldset = $form->append( 'fieldset', '', _t( 'Edit %1$s: <b>%2$s</b>', array( $one_shelf, $shelf_term->term_display ), 'shelves' ) ) ;
			$shelf = $edit_fieldset->append( 'text', 'shelf', 'null:null', _t( 'Rename %s', array( $one_shelf ), 'shelves' ), 'formcontrol_text' );
			$shelf->value = $shelf_term->term_display;
			$shelf->add_validator( 'validate_required' );
			$shelf->class = 'pct30';

			$parent = $edit_fieldset->append( 'select', 'parent', 'null:null', sprintf( _t( 'Current Parent: <b>%1$s</b> Change Parent to:', 'shelves' ), $parent_term_display ), 'asdasdaoptionscontrol_select' );
			$parent->class = 'pct50';
			$parent->options = array();
			$parent->options[''] = ''; // top should be blank
			$right = array();
			foreach( $shelf_term->not_descendants() as $term ) {
				while ( count( $right ) > 0 && $right[count( $right ) - 1] < $term->mptt_right ) {
					array_pop( $right );
				}
				$parent->options[ $term->id ] = str_repeat( ' - ', count( $right ) ) . $term->term_display;
				$right[] = $term->mptt_right;
			}
			$parent->value = ( !$parent_term ? '': $parent_term->id ); // select the current parent
			$save_button = $edit_fieldset->append( 'submit', 'save', _t( 'Edit', 'shelves' ) );
			$save_button->class = 'pct20 last';

			$cancel_button = $form->append( 'submit', 'cancel_btn', _t( 'Cancel', 'shelves' ) );
	
			$form->on_success( array( $this, 'formui_edit_submit' ) );
		}
		$theme->form = $form->get();

		$theme->display( 'shelves' );
		// End everything
		exit;
	}

	public function formui_create_submit( FormUI $form )
	{
		if( isset( $form->shelf ) && ( $form->shelf->value <> '' ) ) {

			// time to create the new term.
			$form_parent = $form->parent->value;
			$new_term = $form->shelf->value;

			// If a new term has been set, add it to the shelves vocabulary
			if ( '' != $form_parent ) {
				// Make sure the parent term exists.
				$parent_term = $this->vocabulary->get_term( $form_parent );
					if ( null == $parent_term ) {
					// There's no term for the parent, add it as a top-level term
					$parent_term = $this->vocabulary->add_term( $form_parent );
				}

				$shelf_term = $this->vocabulary->add_term( $new_term, $parent_term );
			}
			else {
				$shelf_term = $this->vocabulary->add_term( $new_term );
			}
		}
		// redirect to the page to update the form
		Utils::redirect( URL::get( 'admin', array( 'page'=>'shelves' ) ), true );
	}

	public function formui_edit_submit( FormUI $form )
	{
		if( isset( $form->shelf ) && ( $form->shelf->value <> '' ) ) {
			if( isset( $form->shelf_id ) ) {
				$current_term = $this->vocabulary->get_term( $form->shelf_id->value );

				// If there's a changed parent, change the parent.
				$cur_parent = $current_term->parent();
				$new_parent = $this->vocabulary->get_term( $form->parent->value );

				if ( $cur_parent ) {
					if ( $cur_parent->id <> $form->parent->value ) {
						// change the parent to the new ID.
						$this->vocabulary->move_term( $current_term, $new_parent );
					}
				}
				else 	{
					// cur_parent is false, should mean $current_term is a root element
					$this->vocabulary->move_term( $current_term, $new_parent );
				}
				// If the shelf has been renamed, modify the term

			}
		}
		// redirect to the page to update the form
		Utils::redirect( URL::get( 'admin', array( 'page'=>'shelves' ) ), true );
	}

 	/**
	 * Cover both post and get requests for the page
	 **/
	public function alias()
	{
		return array( 
			'action_admin_theme_get_shelves'=> 'action_admin_theme_post_shelves' 
		);
	}
	
	/**
	 * Add menu item above 'dashboard'
	 **/
	public function filter_adminhandler_post_loadplugins_main_menu( array $menu )
	{
		$item_menu = array( 'shelves' => array(
			'url' => URL::get( 'admin', 'page=shelves' ),
			'title' => _t( 'Manage blog shelves' ),
			'text' => ucfirst( Options::get( 'shelves__plural', _t( 'shelves', 'shelves' ) ) ),
			'hotkey' => 'W',
			'selected' => false,
			'access' => array( 'manage_shelves' => true )
		) );
		
		$slice_point = array_search( 'dashboard', array_keys( $menu ) ); // Element will be inserted before "dashboard"
		$pre_slice = array_slice( $menu, 0, $slice_point );
		$post_slice = array_slice( $menu, $slice_point );
		
		$menu = array_merge( $pre_slice, $item_menu, $post_slice );
		
		return $menu;
	}

	/**
	 * Add shelves to the publish form
	 **/
	public function action_form_publish ( $form, $post )
	{

		if ( $form->content_type->value == Post::type( self::$content_type ) ) {
			$shelves_options = array( 0 => _t( '(none)', 'shelves' ) ) + $this->vocabulary->get_options();
	//		Utils::debug( $shelves_options ); die();
			$form->append( 'select', 'shelf', 'null:null', ucfirst( Options::get( 'shelves__single', _t( 'shelf', 'shelves' ) ) ), $shelves_options, 'tabcontrol_select' );
			$form->shelf->tabindex = $form->tags->tabindex + 1;
			$form->move_after( $form->shelf, $form->tags );
			$form->save->tabindex = $form->save->tabindex + 1;

			// If this is an existing post, see if it has a shelf already
			if ( 0 != $post->id ) {
				$form->shelf->value = $this->get_shelf( $post );
			}
		}
	}

	/**
	 * Process shelves when the publish form is received
	 *
	 **/
	public function action_publish_post( $post, $form )
	{
		if ( $post->content_type == Post::type( self::$content_type ) ) {
			$shelves = array();
			$shelf = $form->shelf->value;
			$this->vocabulary->set_object_terms( 'post', $post->id, array( $this->vocabulary->get_term( (int) $shelf ) ) );
		}
	}

	/**
	 * Simple plugin configuration
	 * @return FormUI The configuration form
	 **/
	public function configure()
	{
		$ui = new FormUI( 'shelves' );
		$ui->append( 'text', 'single', 'shelves__single', _t( 'Singular version of your "shelf" (e.g. "location"):', 'shelves' ) );
		$ui->single->add_validator( array( $this, 'validate_both' ) );
		$ui->append( 'text', 'plural', 'shelves__plural', _t( 'Plural version of your "shelves" (e.g. "locations"):', 'shelves' ) );
		$ui->plural->add_validator( array( $this, 'validate_both' ) );

		$ui->on_success( array( $this, 'updated_config' ) );

		$ui->append( 'submit', 'save', 'save' );

		return $ui;
	}

	/**
	 * Ensure either both or neither values is set.
	 **/
	public function validate_both( $input, $control, $form )
	{
		if ( ( $control->name == 'single' && $control->value == '' && $form->plural->value != '' )
			or
		( $control->name == 'plural' && $control->value == '' && $form->single->value != '' ) ) 		{
			// This wording isn't entirely accurate - if neither is set, 'shelf/shelves' will be used.
			return array( _t( 'Both singular and plural values must be set.', 'shelves' ) );
		}
		return array();
	}

	/**
	 * Lowercase the values and give the user a session message to confirm options were saved.
	 **/
	public function updated_config( $form )
	{
		$_POST[ $form->single->field ] = strtolower( $form->single->value ); // ugly hack, core ticket #931
		$_POST[ $form->plural->field ] = strtolower( $form->plural->value );
		Session::notice( _t( 'Shelves nomenclature saved.', 'shelves' ) );
		$form->save();
	}

	/** 
	 * return an array of shelves, having been cleaned up a bit. Taken from post.php r3907
	 * @param String $shelves Text from the Shelf text input
	 */
	public static function parse_shelves( $shelves )
	{
		if ( is_string( $shelves ) ) {
			if ( '' === $shelves ) {
				return array();
			}
			// just as dirrty as it is in post.php ;)
			$rez = array( '\\"'=>':__unlikely_quote__:', '\\\''=>':__unlikely_apos__:' );
			$zer = array( ':__unlikely_quote__:'=>'"', ':__unlikely_apos__:'=>"'" );
			// escape
			$catstr = str_replace( array_keys( $rez ), $rez, $shelves );
			// match-o-matic
			preg_match_all( '/((("|((?<= )|^)\')\\S([^\\3]*?)\\3((?=[\\W])|$))|[^,])+/', $catstr, $matches );
			// cleanup
			$shelves = array_map( 'trim', $matches[0] );
			$shelves = preg_replace( array_fill( 0, count( $shelves ), '/^(["\'])(((?!").)+)(\\1)$/'), '$2', $shelves );
			// unescape
			$shelves = str_replace( array_keys( $zer ), $zer, $shelves );
			// just as hooray as it is in post.php
			return $shelves;
		}
		elseif ( is_array( $shelves ) ) {
			return $shelves;
		}
	}

	/**
	 * Add a shelf rewrite rule
	 * @param Array $rules Current rewrite rules
	 **/
	public function filter_default_rewrite_rules( $rules ) {
		$shelf = Options::get( 'shelves__single', _t( 'shelf', 'shelves' ) );
		$rule = array( 	'name' => 'display_entries_by_shelf',
				'parse_regex' => '%^' . $shelf . '/(?P<shelf_slug>[^/]*)(?:/page/(?P<page>\d+))?/?$%i',
				'build_str' => $shelf .'/{$shelf_slug}(/page/{$page})',
				'handler' => 'UserThemeHandler', 
				'action' => 'display_entries_by_shelf', 
				'priority' => 5, 
				'description' => 'Return posts matching specified shelf.', 
		);

		$rules[] = $rule;	
		return $rules;
	}

	/**
	 * function filter_template_where_filters
	 * Limit the Posts::get call to shelves 
	 * (uses tag_slug because that's really term under the hood)
	 **/
	public function filter_template_where_filters( $filters ) {
		$vars = Controller::get_handler_vars();
		if( isset( $vars['shelf_slug'] ) ) {
			$term = $this->vocabulary->get_term( $vars['shelf_slug'] );
			if ( $term instanceof Term ) {
				$terms = (array)$term->descendants();
				$terms = array_map(create_function('$a', 'return $a->term;'), $terms);
				array_push($terms, $vars['shelf_slug']);
				$filters['vocabulary'] = array_merge( $filters['vocabulary'], array( self::$vocabulary . ':term' => $terms ) );
			}
		}
		return $filters;
	}

	/**
	 * function filter_theme_act_display_entries_by_shelf
	 * Helper function: Display the posts for a shelf. Probably should be more generic eventually.
	 * Does not appear to work currently.
	 */
	public function filter_theme_act_display_entries_by_shelf( $handled, $theme ) {
		$paramarray = array();
		$paramarray[ 'fallback' ] = array(
			'shelf.{$shelf}',
			'shelf',
			'multiple',
		);

		// Makes sure home displays only entries ... maybe not necessary. Probably not, in fact.
		$default_filters = array(
 			'content_type' => Post::type( self::$content_type ),
		);

		$paramarray[ 'user_filters' ] = $default_filters;

		$theme->act_display( $paramarray );
		return true;
	}

	/**
	 * function get_shelf
	 * Gets the shelf for the post
	 * @return int The shelf id for this post
	 */
	private function get_shelf( $post )
	{
		$result = $this->vocabulary->get_object_terms( 'post', $post->id );
		if( $result ) {
				// since this is not a 'multiple' vocabulary, there should be only one.
				return $result[0]->id;
		}
		return false;
	}

	/**
	 * function filter_post_get
	 * Allow post->shelf
	 * @return array The shelf for this post
	 **/
	public function filter_post_get( $out, $name, $post )
	{
		if( $name != 'shelf' ) {
			return $out;
		}
		$shelves = array();
		$result = $this->vocabulary->get_object_terms( 'post', $post->id );
		if( $result ) {
			// since this is not a 'multiple' vocabulary, there should be only one.
			return $result[0]->term_display;
		}
		return false;
	}

	/**
	 * function delete_shelf
	 * Deletes an existing shelf and all relations to it.
	 **/
	public static function delete_shelf( $shelf = '' )
	{
		$vocabulary = Vocabulary::get( self::$vocabulary );
		// should there be a Plugins::act( 'shelf_delete_before' ...?
		$term = $vocabulary->get_term( $shelf );

		if ( !$term ) {
			return false; // no match for shelf
		}

		$result = $vocabulary->delete_term( $term );

		if ( $result ) {
			EventLog::log( sprintf( _t( '%1$s \'%2$s\' deleted.' ), array( Options::get( 'shelves__singule', _t( 'shelf', 'shelves' ) ), $shelf ) ), 'info', 'content', 'shelves' );
		}
		// should there be a Plugins::act( 'shelf_delete_after' ...?
		return $result;
	}

	/**
	 * Allow searching by ($singular):whatever
	 **/

	public function filter_posts_search_to_get( $arguments, $flag, $value, $match, $search_string )
	{
		if ( Options::get( 'shelves__single', _t( 'shelf', 'shelves' ) ) == $flag ) {
			$arguments['vocabulary'][$this->vocabulary->name . ':term_display'][] = $value;
		}
		return $arguments;
	}
}

class ShelvesFormat extends Format {

	public static function link_shelf( $a, $b ) {
		return '<a href="' . URL::get( "display_entries_by_shelf", array( "shelf_slug" => $b ) ) . "\" rel=\"shelf\">$a</a>";
	}

}

?>