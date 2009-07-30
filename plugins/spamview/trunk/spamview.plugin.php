<?php

class Spamview extends Plugin
{ 

	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, 'BDF286FA-955E-11DD-9DD5-995D56D89593', $this->info->version );
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
	 * filter_dash_modules
	 * Registers the modules with the Modules class. 
	 */
	function filter_dash_modules( $modules )
	{
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
		$comments= Comments::get(array('status' => array(Comment::status('spam'), Comment::status('unapproved'))));

		$theme->latestspam_comments = $comments;
		
		$module['title'] = '<a href="' . Site::get_url('admin') . '/comments?status=' . Comment::status('spam') . '">' . _t('Latest Spam') . '</a>';
		$module['content'] = $theme->fetch( 'dash_spam' );
		return $module;
	}

}	

?>