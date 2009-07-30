<?php

class Spamview extends Plugin
{ 

	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, 'd346880b-67a6-43ab-b3aa-61250d86b7fe', $this->info->version );
	}
	
	/**
	 * Initialization, useful to check for options
	 *
	 * @return void
	 **/
	public function action_init()
	{		
		if( Options::get('spamview__spambutton') === NULL ) {
			Options::set('spamview__spambutton', true);
		}
	}
	
	/**
	 * action_plugin_activation
	 * Registers the core modules with the Modules class. Add these modules to the
	 * dashboard if the dashboard is currently empty.
	 * @param string $file plugin file
	 */
	function action_plugin_activation( $file )
	{
		if( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			Modules::add( 'Latest Spam' );
		}
	}

	/**
	 * action_plugin_deactivation
	 * Unregisters the core modules.
	 * @param string $file plugin file
	 */
	function action_plugin_deactivation( $file )
	{
		if( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			Modules::remove_by_name( 'Latest Spam' );
		}
	}

	/**
	 * Create plugin configuration
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	 * Create configuration panel
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
				
					$form = new FormUI( strtolower( get_class( $this ) ) );

					$form->append( 'checkbox', 'spambutton', 'spamview__spambutton', _t('Enable delete all spam button') );

					$form->append( 'submit', 'save', _t('Save') );
					$form->out();
					
					break;
			}
		}
	}

	/**
	 * filter_dash_modules
	 * Registers the modules with the Modules class. 
	 */
	function filter_dash_modules( $modules )
	{
		if(!User::identify()->can( 'manage_all_comments' )) {
			return $modules;
		}

		// 
		
		array_push( $modules, 'Latest Spam' );
		
		$this->add_template( 'dash_spam', dirname( __FILE__ ) . '/spam.module.php' );
		
		return $modules;
	}

	/**
	 * filter_dash_module_latest_spam
	 * Function used to set theme variables to the latest spam dashboard widget
	 * @param string $module_id
	 * @return string The contents of the module
	 */
	public function filter_dash_module_latest_spam( $module, $module_id, $theme )
	{
		$comments= Comments::get(array('status' => array(Comment::status('spam'), Comment::status('unapproved')), 'limit' => 8 ));

		$theme->latestspam_comments = $comments;
		$theme->spambutton = Options::get('spamview__spambutton');
		$theme->spamcount = Comments::count_total( Comment::STATUS_SPAM, FALSE );
		
		$module['title'] = '<a href="' . Site::get_url('admin') . '/comments?status=' . Comment::status('spam') . '">' . _t('Latest Spam') . '</a>';
		
		// $module['options'] = _t( 'You should not be here' );
		
		$module['content'] = $theme->fetch( 'dash_spam' );
		return $module;
	}
	
	/**
	 * Add CSS & JS to admin stack
	 *
	 * @return void
	 **/
	public function action_admin_header()
	{
		if( Options::get('spamview__spambutton') ) {
			Stack::add('admin_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/spamview.css', 'screen'), 'spamview');
			Stack::add( 'admin_header_javascript', URL::get_from_filesystem(__FILE__) . '/spamview.js', 'spamview', array('jquery', 'jquery.hotkeys') );
		}	
	}

	
	/**
	 * Handles spam deletion
	 *
	 * @return void
	 **/
	public function action_auth_ajax_deleteallspam( $handler )
	{
		if(!User::identify()->can( 'manage_all_comments' )) {
			return;
		}
		
		$total = Comments::count_total( Comment::STATUS_SPAM, FALSE );
		$result = array();
		
		Comments::delete_by_status( Comment::status('spam') );
		Session::notice( sprintf( _t( 'Deleted all %s spam comments.' ), $total ) );
				
		if( $handler->handler_vars['page'] == 'dashboard') {
			
			// Ideally, we would be able to display the module html here.
			
			// $admin = new AdminHandler;
			// 
			// $admin->fetch_dashboard_modules();
			
			// $result['modules'] = $admin->theme->fetch( 'dashboard_modules' );
		}
		else {
			$result['messages'] = Session::messages_get( true, 'array' );
		}
		
		echo json_encode( $result );
		
		return;
	}
	
	/**
	 * Inject the delete all button
	 *
	 * @return void
	 **/
	public function action_admin_info( $theme, $page )
	{
		if( $page == 'comments' ) {
			$spamcount = Comments::count_total( Comment::STATUS_SPAM, FALSE );
			echo '<a href="#" id="deleteallspam">' . sprintf( _t( 'Clear spam' ), $spamcount ) . '</a>';
		}
		
		return;
	}

}	

?>