<?php
/**
 * HabminBar Plugin
 *
 * A plugin for Habari that displays and admin bar
 * on every page of your theme, for easy access to
 * the admin section.
 * 
 * @package habminbar
 */


class HabminBar extends Plugin
{
	const VERSION= '1.0';
	
	/**
	 * function info
	 *
	 * Returns information about this plugin
	 * @return array Plugin info array
	 */
	public function info()
	{
		return array (
			'name' => 'Habmin Bar',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => self::VERSION,
			'description' => 'An admin bar for Habari.',
			'license' => 'Apache License 2.0',
		);
	}
	
	/**
	 * Adds the admin bar stylesheet to the template_stylesheet Stack if the user is logged in.
	 */
	public function action_add_template_vars()
	{
		if ( User::identify() ) {
			Stack::add( 'template_stylesheet', array($this->get_url() . '/habminbar.css', 'screen'), 'habminbar.css' );
		}
	}
	
	/**
	 * Filters the habminbar via Plugin API to add the edit menu item.
	 *
	 * @param array $menu The Habminbar array
	 * @return array The modified Habminbar array
	 */
	public function filter_habminbar( $menu )
	{
		if ( Controller::get_var('slug') ) {
			$menu['write']= array( 'Edit', URL::get( 'admin', 'page=publish&slug=' . Controller::get_var('slug') ) );
		}
		return $menu;
	}
	
	/**
	 * Ouputs the default menu in the template footer, and runs the 'habmin_bar' plugin filter.
	 * You can add menu items via the filter. See the 'filter_habminbar' method for
	 * an example.
	 */
	public function action_template_footer()
	{
		if ( User::identify() ) {
			$bar= '<div id="habminbar"><div>';
			$bar.= '<div id="habminbar-name"><a href="' . Options::get('base_url') . '">' . Options::get('title') . '</a></div>';
			$bar.= '<ul>';
			
			$menu= array();
			$menu['dashboard']= array( 'Dasboard', URL::get( 'admin', 'page=dashboard' ) );
			$menu['write']= array( 'Write', URL::get( 'admin', 'page=publish' ) );
			$menu['option']= array( 'Options', URL::get( 'admin', 'page=options' ) );
			$menu['comment']= array( 'Moderate', URL::get( 'admin', 'page=moderate' ) );
			$menu['user']= array( 'Users', URL::get( 'admin', 'page=users' ) );
			$menu['plugin']= array( 'Plugins', URL::get( 'admin', 'page=plugins' ) );
			$menu['theme']= array( 'Themes', URL::get( 'admin', 'page=themes' ) );
			
			$menu= Plugins::filter( 'habminbar', $menu );
			
			foreach ( $menu as $name => $item ) {
				list( $label, $url )= $item;
				$bar.= "\n\t<li><a href=\"$url\" class=\"$name\">$label</a></li>";
			}
			$bar.= '</ul><br style="clear:both;" /></div></div>';
			
			echo $bar;
		}
	}
}

?>
