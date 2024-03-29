<?php

/**
 * Live Help Plugin
 *
 **/

class LiveHelp extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Live Help',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '0.12',
			'description' => 'Allows users to connect to #habari on IRC from within the admin.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * add ACL tokens when this plugin is activated
	**/
	public function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			ACL::create_token( 'LiveHelp', 'Access the #habari IRC channel via the LiveHelp plugin', 'livehelp' );
		}
	}

	/**
	 * remove ACL tokens when this plugin is deactivated
	**/
	function action_plugin_deactivation( $plugin_file )
	{
		if( Plugins::id_from_file( __FILE__ ) == Plugins::id_from_file( $plugin_file  ) ) {
			ACL::destroy_token( 'LiveHelp' );
		}
	}

	/**
	 * Add the Live Help page to the admin menu
	 *
	 * @param array $menus The main admin menu
	 * @return array The altered admin menu
	 */
	function filter_adminhandler_post_loadplugins_main_menu( $menus )
	{
		if ( User::identify()->can('LiveHelp') ) {
			$menus['livehelp'] =  array( 'url' => URL::get( 'admin', 'page=livehelp'), 'title' => _t('Live Help'), 'text' => _t('Live Help'), 'selected' => false );
		}
		return $menus;
	}

	/**
	 * On plugin init, add the admin_livehelp template to the admin theme
	 */
	function action_init()
	{
		$this->add_template('livehelp', dirname(__FILE__) . '/livehelp.php');
	}

	public function action_add_template_vars( $theme )
	{
		if ($theme->admin_page == 'livehelp') {
			$user = User::identify();
			$nick = $user->username;
			$nick = $nick == 'admin' ? substr($user->email, 0, strpos($user->email, '@')) : $nick;
			$theme->assign('nick', $nick);
		}
	}

	public function action_admin_header( $theme ) {
		if ($theme->admin_page == 'livehelp') {
			Stack::add('admin_stylesheet', array($this->get_url() . '/livehelp.css', 'screen'));
		}
	}

	/**
	 * Implement the update notification feature
	 */
	public function action_update_check()
	{
		Update::add( 'Live Help', 'c2413ab2-7c79-4f92-b008-18e3d8e05b64',  $this->info->version );
	}

	/**
	 * filter the permissions so that admin users can use this plugin
	**/
	public function filter_admin_access_tokens( $require_any, $page, $type )
	{
		// we only need to filter if the Page request is for our page
		if ( 'livehelp' == $page ) {
			// we can safely clobber any existing $require_any
			// passed because our page didn't match anything in
			// the adminhandler case statement
			$require_any= array( 'super_user' => true, 'livehelp' => true );
		}
		return $require_any;
	}

}

?>
