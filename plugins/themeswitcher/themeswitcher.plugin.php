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
			'version' => '1.1',
			'description' => 'Allows visitors to change the theme of the site.',
			'license' => 'Apache License 2.0',
			'copyright' => '2008'
		);
	}
	
	function theme_header() {
		echo '<script src="' . Site::get_url('scripts') . '/jquery.js" type="text/javascript"></script>';
		echo <<< HEADER

<script type="text/javascript">
	$(document).ready(function(){
		$("input[@name='themeswitcher_submit']").hide();
		$("select[@name='theme_dir']").change( function() {
			$("form[@name='themeswitcher']").submit();
		});
	});
</script>

HEADER;
	}
	
	function action_init()
	{
		if (!empty($_GET['theme_dir']) || !empty($_POST['theme_dir'])) {
			$new_theme_dir= empty($_GET['theme_dir']) ? $_POST['theme_dir'] : $_GET['theme_dir'];
			$all_themes= Themes::get_all();
			if ( array_key_exists( $new_theme_dir, $all_themes ) ) {				
				if (!isset($_COOKIE['theme_dir']) || (isset($_COOKIE['theme_dir']) && ($_COOKIE['theme_dir'] != $new_theme_dir))) {					
					$_COOKIE['theme_dir']= $new_theme_dir; // Without this, the cookie isn't get in time to change the theme NOW.
					setcookie( 'theme_dir', $new_theme_dir );
				}
			}
		}
		
		$this->add_template('switcher', dirname(__FILE__) . '/themeswitcher.php');
	}
		
	function action_theme_sidebar_bottom()
	{
		return $theme->fetch('switcher');
	}
	
	function theme_switcher() {
		return $theme->fetch('switcher');
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