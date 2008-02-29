<?php

/**
 * ThemeSwitcher - Allows visitors to change the theme of the site.
 */

class ThemeSwitcher extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'ThemeSwitcher',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Allows visitors to change the theme of the site.',
			'license' => 'Apache License 2.0',
		);
	}
	
	function action_init()
	{
		if (!empty($_GET['theme_dir']) || !empty($_POST['theme_dir'])) {
			$new_theme_dir= empty($_GET['theme_dir']) ? $_POST['theme_dir'] : $_GET['theme_dir'];
			if (!isset($_COOKIE['theme_dir']) || (isset($_COOKIE['theme_dir']) && ($_COOKIE['theme_dir'] != $new_theme_dir))) {
				$_COOKIE['theme_dir']= $new_theme_dir; // Without this, the cookie isn't get in time to change the theme NOW.
				setcookie( 'theme_dir', $new_theme_dir );
			}
		}
	}
	
	function action_theme_sidebar_bottom()
	{
		include('themeswitcher.php');
	}
	
	function theme_switcher() {
		include('themeswitcher.php');
	}
	
	function filter_option_get_value($value, $name)
	{
		if (($name == 'theme_dir') && isset($_COOKIE['theme_dir'])) {
			return $_COOKIE['theme_dir'];
		}
		else {
			return $value;
		}
	}

}

?>