<?php

class prettyPrettyAdmin extends Plugin
{
	public function info()
	{
		return array (
			'name' => 'Pretty, Pretty Admin',
			'version' => '1',
			'author' => 'Randy Walker',
			'description' => 'Adds color to the admin area. License: Do what you want with this plugin. I\'d appreciate a link but you don\'t have to.'
		);
	}
	
	public function action_admin_header( $theme )
	{
		Stack::remove('admin_stylesheet', 'admin');
		Stack::add('admin_stylesheet', array($this->get_url(true) . 'admin.css', 'screen'));
	}
}

?>