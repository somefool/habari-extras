<?php

class PluginSubmenu extends Plugin
{
	public function action_update_check()
	{
	 	Update::add( 'PluginSubmenu', '9afffc2f-557f-4bbb-87e3-ada7a1ae5444', $this->info->version );
	}

	public function filter_adminhandler_post_loadplugins_main_menu($menu)
	{
		$active_plugins = Plugins::get_active();

		$submenu_count = 0;
		foreach ( $active_plugins as $pluginobj ) {
			$plugin_actions = array();
			$plugin_actions = Plugins::filter( 'plugin_config', $plugin_actions, $pluginobj->plugin_id() );

			foreach ( $plugin_actions as $plugin_action => $plugin_action_caption ) {
				if ( is_numeric($plugin_action) ) {
					$plugin_action = $plugin_action_caption;
				}
				$urlparams = array('page' => 'plugins', 'configure' => $pluginobj->plugin_id(), 'configaction' => $plugin_action);
				$url = URL::get( 'admin', $urlparams );

				switch($plugin_action_caption) {
					case _t('?'):
						break;
					default:
						$menu['plugins']['submenu']['plugin_' . ++$submenu_count] = array( 'url' => $url, 'title' => _t( '%1$s: %2$s', array($pluginobj->info->name, $plugin_action_caption) ), 'text' => _t( '%1$s: %2$s', array($pluginobj->info->name, $plugin_action_caption) ), 'access' => true );
						break;
				}
			}
		}

		return $menu;
	}

}
?>