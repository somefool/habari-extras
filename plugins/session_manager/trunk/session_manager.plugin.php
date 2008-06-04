<?php

/**
 * Test Plugin Class
 *
 **/

class SessionManager extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Session Manager',
			'url' => 'http://habariproject.org/',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net/',
			'version' => '1.0',
			'description' => 'Prevents spiders from filling the session table.',
			'license' => 'Apache License 2.0',
		);
	}


	/**
	* Add actions to the plugin page for this plugin
	*
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()){
			$actions[] = 'Configure';
		}

		return $actions;
	}

	/**
	* Respond to the user selecting an action on the plugin page
	*
	* @param string $plugin_id The string id of the acted-upon plugin
	* @param string $action The action string supplied via the filter_plugin_config hook
	*/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id()){
			switch ($action){
				case 'Configure' :
					$ui = new FormUI(strtolower(get_class($this)));
					$spiders = $ui->append('textarea', 'spiders', 'session_manager__spiders', 'List spiders to ignore, one per line:');
					$ui->append( 'submit', 'save', _t('Save') );
					$ui->out();
					break;
			}
		}
	}

	public function filter_session_write($dowrite, $sessionid, $data)
	{
		$spiders = Options::get( 'session_manager__spiders' );
		$spiders = explode("\n", preg_quote($spiders, '%'));
		$spiders = array_map('trim', $spiders);
		$spiders = array_filter($spiders);
		$spider_regex = '%(' . implode('|', $spiders) . ')%i';
		if(preg_match($spider_regex, $_SERVER['HTTP_USER_AGENT'])) {
			$dowrite = false;
		}
		return $dowrite;
	}

}

?>
