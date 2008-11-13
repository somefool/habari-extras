<?php

class LimitAccessPlugin extends Plugin
{

	private $fetch_real = false;

	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Limit Access',
			'url' => 'http://habariproject.org/',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net/',
			'version' => '1.0',
			'description' => 'Limits certain users to specific features - to eventually be replaced by the full core ACL implementation',
			'license' => 'Apache License 2.0',
		);
	}

	function action_theme_admin_user($user)
	{
		$configure = $user->info->limitaccess == 0 ? ' selected="selected" ' : '';
		$full = $user->info->limitaccess == 1 ? ' selected="selected" ' : '';
		$limited = $user->info->limitaccess == 2 ? ' selected="selected" ' : '';

		if($user->id == User::identify()->id) {
			return;
		}
		
		$editor = User::identify();
		if(isset($editor->info->limitaccess) && $editor->info->limitaccess > 0) {
			return;
		}

		echo <<< LIMIT_USER_UI
<div class="container settings regionalsettings" id="regionalsettings">
	<h2>Limit Access</h2>

	<div class="item clear" id="limit_access">
		<span class="pct20">
			<label for="timezone">Limit Access</label>
		</span>
		<span class="pct80">
			<select id="limitaccess" name="limitaccess">
				<option value="0" $configure>Full Access and Configure Limits</option>
				<option value="1" $full>Full Access</option>
				<option value="2" $limited>Limit Access</option>
			</select>
		</span>
	</div>
</div>		
LIMIT_USER_UI;
	}

	function filter_adminhandler_post_user_fields($fields)
	{
		$user = User::identify();
		if(isset($user->info->limitaccess) && $user->info->limitaccess == 0) {
			$fields['limitaccess'] = 'limitaccess';
		}
		return $fields;
	}

	function filter_adminhandler_post_loadplugins_main_menu($menus) 
	{
		$user = User::identify();
		
		if($this->fetch_real || $user->info->limitaccess <= 1) {
			return $menus;
		}
		
		$t_limited_menus = $this->get_menu();
		$limited_menus = array();
		foreach($t_limited_menus as $mk => $m2) {
			if(Options::get('limitaccess__' . $mk)) {
				$limited_menus[$mk] = $m2;
			}
		}
		
		$menus = array_intersect_key($menus, $limited_menus);
		return $menus;
	}

	public function filter_plugin_config($actions, $plugin_id)
	{
		$user = User::identify();

		if(isset($user->info->limitaccess) && $user->info->limitaccess > 0) {
			return $actions;
		}

		if ($plugin_id == $this->plugin_id()) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id()) {
			switch ($action) {
				case _t('Configure'):
					$ui = new FormUI('limitaccess');
					
					$menus = $this->get_menu();

					$ui->append('fieldset', 'accessmenus', 'Available Menus');
					$ui->accessmenus->append('static', 'accessdescription', 'Please select the menu options that will be available to users with limited access.');
					foreach($menus as $menu_key => $menu) {
						$ui->accessmenus->append('checkbox', $menu_key, 'limitaccess__' . $menu_key, $menu['text']);
					}

					$ui->append('submit', 'save', 'save');
					$ui->out();
					break;
			}
		}
	}

	protected function get_menu()
	{
		$createmenu = array();
		$managemenu = array();
		foreach( Post::list_active_post_types() as $type => $typeint ) {
			if ( $typeint == 0 ) {
				continue;
			}
			$createmenu['create_' . $typeint]= array( 'url' => 'page=publish&content_type=' . $type, 'text' => sprintf( _t( 'Create %s' ), ucwords( $type ) ) );
			$managemenu['manage_' . $typeint]= array( 'url' => 'page=posts&type=' . $typeint, 'text' => sprintf( _t( 'Manage %s' ), ucwords( $type ) ) );
		}

		$adminmenu = array(
			'comments' => array( 'url' => 'page=comments' , 'text' => _t( 'Comments' ), ),
			'tags' => array( 'url' => 'page=tags' , 'text' => _t( 'Tags' ), ),
			'dashboard' => array( 'url' => 'page=' , 'text' => _t( 'Dashboard' ), ),
			'options' => array( 'url' => 'page=options' , 'text' => _t( 'Options' ),  ),
			'themes' => array( 'url' => 'page=themes' , 'text' => _t( 'Themes' ), ),
			'plugins' => array( 'url' => 'page=plugins' , 'text' => _t( 'Plugins' ), ),
			'import' => array( 'url' => 'page=import' , 'text' => _t( 'Import' ), ),
			'users' => array( 'url' => 'page=users' , 'text' => _t( 'Users' ), ),
			'logs' => array( 'url' => 'page=logs', 'text' => _t( 'Logs' ), ) ,
			'logout' => array( 'url' => 'page=logout' , 'text' => _t( 'Logout' ), ),
			'user' => array( 'url' => 'page=user&userid=' . User::identify()->id , 'text' => _t( 'User\'s own profile page' ), ),
			'otheruser' => array( 'url' => 'page=user' , 'text' => _t( 'Other user\'s profile page' ), ),
		);

		$mainmenus = array_merge( $createmenu, $managemenu, $adminmenu );

		return $mainmenus;
	}
	
	public function action_before_act_admin()
	{
		$user = User::identify();
		
		if(isset($user->info->limitaccess) && $user->info->limitaccess == 0) {
			return;
		}

		Plugins::register(array($this, 'kill_admin_user'), 'action', 'admin_theme_get_user');
		Plugins::register(array($this, 'kill_admin_user'), 'action', 'admin_theme_post_user');

		$menus = $this->get_menu();
		
		foreach($menus as $mk => $m2) {
			if(!Options::get('limitaccess__' . $mk)) {
				$params = Utils::get_params($m2['url']);
				$page = $params['page'];
				unset($params['page']);
				$kill = true;
				foreach($params as $k => $v) {
					if(Controller::get_var($k) != $v) {
						$kill = false;
					}
				}
				if($page == 'user') {
					$kill = false;
				}
				if ($kill) {
					Plugins::register(array($this, 'kill_admin'), 'action', 'admin_theme_post_' . $page);
					Plugins::register(array($this, 'kill_admin'), 'action', 'admin_theme_get_' . $page);
				}
			}
		}
	}

	public function kill_admin_user($admin) 
	{
		// Special cases
		if(!Options::get('limitaccess__otheruser') && isset($admin->handler_vars['user'])) {
			die('This menu option is not available to you.');
		}
		if(!Options::get('limitaccess__user') && !isset($admin->handler_vars['user'])) {
			die('This menu option is not available to you.');
		}
	}
	
	protected function kill_admin() 
	{
		die('This menu option is not available to you.');
	}

}
?>