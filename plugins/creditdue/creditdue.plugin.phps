<?php

/**
 * Credit Due plugin, allows output of theme and plugin information for Habari sites.
 */

class CreditDue extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Credit Due',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '0.1',
			'description' => 'Allows for output of information for active themes and plugins',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * function theme_show_credits
	 * retrieves information about the active theme and returns it for display by a theme.
	 * 
	 * Usage: This function is called using <?php $theme->show_credits(); ?>
	 * This displays the credits for the active theme and a list of credits for active plugins in an unordered list.
	 */
	public function theme_show_credits() {

		$theme_credits= Themes::get_active();
		$plugin_credits= Plugins::get_active();

		#Utils::debug($theme_credits);
		#Utils::debug($plugin_credits);
		
		$output= "<h3>Theme</h3>\n";
		$output.= "\t<ul id='theme_credits'>\n";
		
			$output.= "\t\t<li>" . $theme_credits->name . " version " . $theme_credits->version . " by <a href='" . $theme_credits->url . "'>" . $theme_credits->author . "</a></li>\n";
		
		$output.= "\t</ul>\n\n";

		$output.= "<h3>Plugins</h3>\n";
		$output.= "\t<ul id='plugin_credits'>\n";
		foreach ($plugin_credits as $plugin) {
			$output.= "\t\t<li><a href='" . $plugin->info->url . "'>" . $plugin->info->name . "</a> version " . $plugin->info->version;
			$output.= empty( $plugin->info->authorurl ) ? " by " . $plugin->info->author : " by <a href='" . $plugin->info->authorurl . "'>" . $plugin->info->author . "</a>";
		}
		$output.= "\t</ul>";

		return $output;
	}
}


?>