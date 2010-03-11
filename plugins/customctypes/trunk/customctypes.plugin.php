<?php

/**
 * Custom Content Types Plugin Class
 *
 **/

class CustomCTypes extends Plugin
{
	/**
	 * Add the Custom Content Types page to the admin menu
	 *
	 * @param array $menus The main admin menu
	 * @return array The altered admin menu
	 */
	function filter_adminhandler_post_loadplugins_main_menu( $menus )
	{
		$menus['content_types'] = array( 'url' => URL::get( 'admin', 'page=admin_cctypes' ), 'title' => _t( 'Manage custom content types' ), 'text' => _t( 'Custom Content Types' ), 'access'=>array('manage_plugins'=>true, 'manage_plugins_config' => true), 'selected' => FALSE );

		return $menus;
	}
	
	/**
	 * Restrict access to this admin page by token
	 * 
	 * @param array $require_any An array of tokens, of which any will grant access
	 * @param string $page The admin page name
	 * @param string $type The content type of the page
	 * @return array An array of tokens, of which any will grant access
	 */
	function filter_admin_access_tokens( $require_any, $page, $type )
	{
		if($page == 'admin_cctypes') {
			$require_any = array('manage_plugins'=>true, 'manage_plugins_config'=>true);
		}
		return $require_any;
	}

	/**
	 * On plugin init, add the admin_cctypes template to the admin theme
	 */
	function action_init()
	{
		$this->add_template('admin_cctypes', dirname(__FILE__) . '/cctypes.php');
		$this->add_template('admin_cctype_publish', dirname(__FILE__) . '/cctype_publish.php');
	}

	/**
	 * Respond to get requests on the admin_cctypes template
	 *
	 * @param AdminHandler $handler The admin handler object
	 * @param Theme $theme The admin theme object
	 */
	function action_admin_theme_get_admin_cctypes( $handler, $theme )
	{
		$posttypes = Post::list_active_post_types();
		unset($posttypes['any']);
		$posttypes = array_flip($posttypes);
		$theme->posttypes = $posttypes;

		if($edit_type = Controller::get_var('edit_type')) {
			$theme->edit_type = $edit_type;
			$theme->edit_type_name = $posttypes[$edit_type];
		}
		
	}

	/**
	 * Respond to post requests on the admin_cctypes template
	 *
	 * @param AdminHandler $handler The admin handler object
	 * @param Theme $theme The admin theme object
	 */
	function action_admin_theme_post_admin_cctypes( $handler, $theme )
	{
		$action = Controller::get_var('cct_action');
		switch($action) {
			case 'addtype':
				Post::add_new_type($_POST['newtype']);
				$typeid = Post::type($_POST['newtype']);
				$handled = Options::get('cctypes_types');
				if(!is_array($handled)) {
					$handled = array();
				}
				$handled[$typeid] = $typeid;
				array_unique($handled);
				Options::set('cctypes_types', $handled);
				Session::notice(_t('Added post type "'.$_POST['newtype'].'".'));
				break;
			case 'deletetype':
				$typename = Post::type_name($_POST['deltype']);
				Post::deactivate_post_type($_POST['deltype']);
				$handled = Options::get('cctypes_types');
				if(isset($handled[$_POST['deltype']])) {
					unset($handled[$_POST['deltype']]);
				}
				Options::set('cctypes_types', $handled);
				Session::notice(_t('Deactivated post type "'.$typename.'".'));
		}
		Utils::redirect();
	}

	/**
	 * Produce a URL that links to an editing page for a specific content type
	 *
	 * @param Theme $theme The admin theme object
	 * @param integer $type_id The type id of the post type to edit
	 * @return string The URL of the editing page for the requested post type.
	 */
	function theme_admin_edit_ctype_url($theme, $type_id)
	{
		return URL::get('admin', array('page' => 'admin_cctypes', 'edit_type' => $type_id));
	}


	/**
	 * Display a custom publish page for handled content types
	 *
	 * @param AdminHandler $handler The admin handler object
	 * @param Theme $theme The admin theme object
	 */
	function action_admin_theme_get_publish( $handler, $theme )
	{
		$handled = Options::get('cctypes_types');

		if ( isset( $handler->handler_vars['id'] ) ) {
			$post = Post::get( array( 'id' => $handler->handler_vars['id'], 'status' => Post::status( 'any' ) ) );
			$ctype = Post::type_name($post->content_type);
		}
		else if ( isset( $handler->handler_vars['content_type'] ) ) {
			$ctype = $handler->handler_vars['content_type'];
		}

		if(isset($ctype) && in_array($ctype, $handled)) {
			$template_name = 'admin_publish_' . $ctype;
			if($theme->template_exists( $template_name )) {
				$theme->display( $template_name );
				exit;
			}
		}
	}

	/**
	 * Handle posting of custom content types
	 *
	 * @param AdminHandler $handler The admin handler object
	 * @param Theme $theme The admin theme object
	 * @todo Get a list of fields that this plugin handles for this content type and map the data out of the form into the info fields
	 */
	function action_publish_post( $post, $form )
	{
		$handled = Options::get('cctypes_types');

		if(isset($handled[$post->content_type])) {
			// Utils::debug($form);
			// Put custom data fields into $post->info here
		}
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Custom Content Types', 'ed4d6659-cf5d-4772-bd78-9c1d164b4354', $this->info->version );
	}

}

?>
