<?php
/**
 * Inserts the Javascript from http://browser-update.org.
 * Following versions should be able to configure for which browsers the notification appears.
 **/
class Browserupdate extends Plugin
{
	public function action_plugin_activation($file)
	{
		
	}

	/**
	 * Create plugin configuration menu entry
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	 * Create plugin configuration
	 **/
	public function action_plugin_ui($plugin_id, $action)
	{
		
	}
	
	/**
	 * Add help text to plugin configuration page
	 **/
	public function help()
	{
		$help = "";
		return $help;
	}

	/**
	 * Nothing to do here atm
	 **/
	public function action_init()
	{
		
	}
	
	/**
	 * Add the Javascript
	 **/
	public function action_template_header()
	{
		Stack::add('template_header_javascript', $this->get_url(true) . 'browserupdate.js', 'browserupdate');
	}
}
?>