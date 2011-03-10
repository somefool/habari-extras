<?php
/**
 * TermMenus
 * 
 * @todo add domain to all _t() calls
 */
class TermMenus extends Plugin
{
	public function  __get($name)
	{
		switch ( $name ) {
			case 'vocabulary':
				if ( !isset($this->_vocabulary) ) {
					$this->_vocabulary = Vocabulary::get(self::$vocabulary);
				}
			return $this->_vocabulary;
		}
	}

	/**
	 * Create an admin token for editing menus
	 **/
	public function action_plugin_activation($file)
	{
		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			// create default access token
			ACL::create_token( 'manage_menus', _t('Manage menus'), 'Administration', false );
			$group = UserGroup::get_by_name( 'admin' );
			$group->grant( 'manage_menus' );
		}
	}

	/**
	 * Register the templates - one for the admin page, the other for the block.
	 **/
	public function action_init()
	{
		$this->add_template( 'menus_admin', dirname( __FILE__ ) . '/menus_admin.php' );
		$this->add_template( 'block.menu', dirname( __FILE__ ) . '/block.menu.php' );
	}

	/**
	 * Remove the admin token
	 **/
	public function action_plugin_deactivation($file)
	{
		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			// delete default access token
			ACL::destroy_token( 'manage_menus' );

			// delete menu vocabularies that were created
			$vocabs = DB::get_results( 'SELECT * FROM {vocabularies} WHERE name LIKE "menu_%"', array(), 'Vocabulary' );
			foreach( $vocabs as $vocab ) {
				// This should only delete the ones that are menu vocabularies, unless others have been named 'menu_xxxxx'
				$vocab->delete();
			}

			// delete blocks that were created
			$blocks = DB::get_results( 'SELECT * FROM {blocks} WHERE type = "menu"', array(), 'Block') ;
			foreach( $blocks as $block ) {
				$block->delete();
			}
		}
	}

	/**
	 * Add to the list of possible block types.
	 **/
	public function filter_block_list($block_list)
	{
		$block_list['menu'] = _t( 'Menu', 'termmenus' );
		return $block_list;
	}

	/**
	 * Produce the form to configure a menu
	 **/
	public function action_block_form_menu( $form, $block )
	{
		$form->append('select', 'menu_taxonomy', $block, _t( 'Menu Taxonomy' ), $this->get_menus(true));
	}

	/**
	 * Populate the block with some content
	 **/
	public function action_block_content_menu( $block ) {
		$vocab = Vocabulary::get_by_id($block->menu_taxonomy);
		$block->vocabulary = $vocab;
		$block->content = Format::term_tree( $vocab->get_tree(), $vocab->name, array( $this, 'render_menu_item' ) );
	}

	/**
	 * Add menus to the publish form
	 **/
	public function action_form_publish ( $form, $post )
	{
		$menus = $this->get_menus();
	
		$menulist = array();
		foreach($menus as $menu) {
			$menulist[$menu->id] = $menu->name;
		}

		$settings = $form->publish_controls->append('fieldset', 'menu_set', _t('Menus'));
		$settings->append('checkboxes', 'menus', 'null:null', _t('Menus'), $menulist);

		// If this is an existing post, see if it has categories already
		if ( 0 != $post->id ) {
			// Get the terms associated to this post
			$object_terms = Vocabulary::get_all_object_terms('post', $post->id);
			$menu_ids = array_keys($menulist);
			$value = array();
			// if the term is in a menu vocab, enable that checkbox
			foreach($object_terms as $term) {
				if(in_array($term->vocabulary_id, $menu_ids)) {
					$value[] = $term->vocabulary_id;
				}
			}
			
			$form->menus->value = $value;
		}
	}

	/**
	 * Process menus when the publish form is received
	 *
	 **/
	public function action_publish_post( $post, $form )
	{
		$term_title = $post->title;
		$selected_menus = $form->menus->value;
		foreach($this->get_menus() as $menu) {
			if(in_array($menu->id, $selected_menus)) {
				$terms = $menu->get_object_terms('post', $post->id);
				if(count($terms) == 0) {
					$term = new Term(array(
						'term_display' => $post->title,
						'term' => $post->slug,
					));
					$menu->add_term($term);
					$menu->set_object_terms('post', 
						$post->id, 
						array($term->term));
				}
			}
		}
	}

	/**
	 * Add creation and management links to the main menu
	 *
	 **/
	public function filter_adminhandler_post_loadplugins_main_menu( $menu ) {
		// obtain existing last submenu item
		$last_used = end( $menu[ 'create' ][ 'submenu' ]);
		// add a menu item at the bottom
		$menu[ 'create' ][ 'submenu' ][] = array( 
			'title' => _t( 'Create a new Menu', 'termmenus' ),
			'url' => URL::get( 'admin', array( 'page' => 'menus', 'action' => 'create' ) ),
			'text' => _t( 'Menu', 'termmenus' ),
			'hotkey' => $last_used[ 'hotkey' ] + 1, // next available hotkey is last used + 1
		);
		$last_used = end( $menu[ 'manage' ][ 'submenu' ]);
		$menu[ 'manage' ][ 'submenu' ][] = array( 
			'title' => _t( 'Manage Menus', 'termmenus' ),
			'url' => URL::get( 'admin', 'page=menus' ), // might as well make listing the existing menus the default
			'text' => _t( 'Menus', 'termmenus' ),
			'hotkey' => $last_used[ 'hotkey' ] + 1,
		);
		return $menu;
	}

	/**
	 * Handle GET and POST requests
	 *
	 **/
	public function alias()
	{
		return array(
			'action_admin_theme_get_menus' => 'action_admin_theme_post_menus'
		);
	}

	/**
	 * Restrict access to the admin page
	 *
	 **/
	public function filter_admin_access_tokens( array $require_any, $page )
	{
		switch ( $page ) {
			case 'menus':
				$require_any = array( 'manage_menus' => true );
				break;
		}
		return $require_any;
	}
	
	/**
	 * Prepare and display admin page
	 *
	 **/
	public function action_admin_theme_get_menus( AdminHandler $handler, Theme $theme )
	{
		$theme->page_content = '';
		$action = isset($_GET[ 'action' ]) ? $_GET[ 'action' ] : 'list';
		
		switch( $action ) {
			case 'edit': 
				$vocabulary = Vocabulary::get( $_GET[ 'menu' ] );
				if ( $vocabulary == false ) {
					$theme->page_content = _t( '<h2>Invalid Menu.</h2>', 'termmenus' );
					// that's it, we're done. Maybe we show the list of menus instead?
					break;
				}
				$form = new FormUI( 'edit_menu' );

				if ( !$vocabulary->is_empty() ) {
					// This doesn't work. Change it to something that does (or is it because there aren't any links in the menu I'm testing?)
					$form->append( 'tree', 'tree', $vocabulary->get_tree(), _t( 'Menu', 'termmenus') );
//						$form->tree->value = $vocabulary->get_root_terms();
					// append other needed controls, if there are any.
				}
				else {
					$form->append( 'static', 'message', _t( '<h2>No links yet.</h2>', 'termmenus' ) );
					// add another control here to add one by URL, maybe?
				}
				$form->append( 'submit', 'save', _t( 'Apply Changes', 'termmenus' ) );
				$theme->page_content = $form->get();
				break;

			case 'create':
				
				$form = new FormUI('create_menu');
				$form->append('text', 'menuname', 'null:null', 'Menu Name')
					->add_validator('validate_required', _t('You must supply a valid menu name'))
					->add_validator(array($this, 'validate_newvocab'));
				$form->append('submit', 'submit', _t('Create Menu'));
				$form->on_success(array($this, 'add_menu_form_save'));
				$theme->page_content = $form->get();
				
				break;
				
			case 'list':
				$menu_list = '';

				foreach ( $this->get_menus() as $menu ) {
					$menu_name = $menu->name;
					$edit_link = URL::get( 'admin', array( 
						'page' => 'menus',
						'action' => 'edit',
						'menu' => $menu_name, // already slugified
					) );
					$menu_list .= "<li><a href='$edit_link' title='Modify $menu_name'><b>$menu_name</b> {$menu->description} - {$menu->count_total()} items</a></li>";
				}
				if ( $menu_list != '' ) {
					$theme->page_content = "<ul>$menu_list</ul>";
				}
				else {
					$theme->page_content = _t( '<h2>No Menus have been created.</h2>', 'termmenus' );
				}
				break;
				
			default:
Utils::debug( $_GET, $action ); die();
		}

		$theme->display( 'menus_admin' );
		// End everything
		exit;
	}
	
	public function add_menu_form_save($form)
	{
		$params = array(
			'name' => $form->menuname->value,
			'description' => _t( 'A vocabulary for the "%s" menu', array( $form->menuname->value ) ), 
			'features' => array( 'term_menu' ), // a special feature that marks the vocabulary as a menu
		);
		$vocab = Vocabulary::create($params);
		Session::notice(_t('Created menu "%s".', array($form->menuname->value)));
		Utils::redirect(URL::get( 'admin', 'page=menus' ));
	}
	
	public function validate_newvocab($value, $control, $form)
	{
		if(Vocabulary::get($value) instanceof Vocabulary) {
			return array(_t('Please choose a vocabulary name that does not already exist.'));
		}
		return array();
	}
	
	public function get_menus($as_array = false)
	{
		$vocabularies = Vocabulary::get_all();
		$outarray = array();
		foreach ( $vocabularies as $index => $menu ) {
			if(!$menu->term_menu) { // check for the term_menu feature we added.
				unset($vocabularies[$index]);
			}
			else {
				if($as_array) {
					$outarray[$menu->id] = $menu->name;
				}
			}
		}
		if($as_array) {
			return $outarray;
		}
		else {
			return $vocabularies;
		}
	}

	/**
	 * Callback function for block output
	 *
	 **/
	public function render_menu_item( $term, $wrapper )
	{
		$title = $term->term_display;
		$objects = $term->object_types();
		foreach($objects as $object_id => $type) {
			switch($type) {
				case 'post':
					$post = Post::get(array('id' =>$object_id));
					if($post instanceof Post) {
						$link = URL::get( 'display_post', array( 'slug' => $post->slug ) );
					}
					return "<a href=\"$link\">$title</a>";
				case 'url':
					$link = $term->info->url;
					return "<a href=\"$link\">$title</a>";
			}
		}
	}

}

?>
