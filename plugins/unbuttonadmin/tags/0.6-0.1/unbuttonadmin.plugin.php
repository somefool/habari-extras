<?php

class unButtonAdmin extends Plugin
{
	public function info()
	{
		return array (
			'name' => 'Un-button Admin',
			'version' => '0.1',
			'author' => 'Habari Community',
			'license' => 'Apache License 2.0',
			'description' => 'Reverts the ugly admin buttons to the default OS widgets',
		);
	}
	
	public function action_admin_header( $theme )
	{
		// This is such a hack it's not even funny
		// But I am laughing inside. Laughing in a bad way.
		Stack::remove('admin_stylesheet', 'admin');
		$css = file_get_contents(Site::get_dir('admin_theme') . '/css/admin.css');
		$css = preg_replace(
			'@#page input\[type=button\], #page input\[type=submit\], #page button {([^}]+)}@',
			'',
			$css,
			1
		);
		$css = preg_replace(
			'@#page input\[type=button\]:hover, #page input\[type=submit\]:hover, #page button:hover {([^}]+)}@',
			'',
			$css,
			1
		);
		Stack::add(
			'admin_stylesheet',
			array(
				preg_replace('@../images/@', Site::get_url('admin_theme') . '/images/', $css),
				'screen'
			),
			'admin',
			'jquery'
		);
	}
}

?>