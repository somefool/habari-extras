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
	const VERSION = '1.0';

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Habmin Bar', '1db4ce60-3ca2-11dd-ae16-0800200c9a66', $this->info->version );
	}

	/**
	 * Adds the admin bar stylesheet to the template_stylesheet Stack if the user is logged in.
	 */
	public function action_add_template_vars()
	{
		if ( User::identify()->loggedin ) {
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
			$post = Post::get('slug=' . Controller::get_var('slug'));
			$menu['write']= array( 'Edit', URL::get( 'admin', 'page=publish&id=' . $post->id ) );
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
		if ( User::identify()->loggedin ) {
			$bar = '<div id="habminbar"><div>';
			$bar.= '<div id="habminbar-name"><a href="' . Options::get('base_url') . '">' . Options::get('title') . '</a></div>';
			$bar.= '<ul>';

			$menu = array();
			$menu['dashboard']= array( 'Dashboard', URL::get( 'admin', 'page=dashboard' ), "view the admin dashboard" );
			$menu['write']= array( 'Write', URL::get( 'admin', 'page=publish' ), "create a new entry" );
			$menu['option']= array( 'Options', URL::get( 'admin', 'page=options' ), "configure site options" );
			$menu['comment']= array( 'Moderate', URL::get( 'admin', 'page=comments' ),"moderate comments" );
			$menu['user']= array( 'Users', URL::get( 'admin', 'page=users' ), "administer users" );
			$menu['plugin']= array( 'Plugins', URL::get( 'admin', 'page=plugins' ), "activate and configure plugins" );
			$menu['theme']= array( 'Themes', URL::get( 'admin', 'page=themes' ), "select a theme" );

			$menu = Plugins::filter( 'habminbar', $menu );

			$menu['logout']= array( 'Logout', URL::get( 'user', 'page=logout' ), "logout" );

			foreach ( $menu as $name => $item ) {
				list( $label, $url, $tooltip )= array_pad( $item, 3, "" );
				$bar.= "\n\t<li><a href=\"$url\" class=\"$name\"" .
				( ( $tooltip ) ? " title=\"$tooltip\"" : "" ) .">$label</a></li>";
			}
			$bar.= '</ul><br style="clear:both;" /></div></div>';

			echo $bar;
		}
	}
}

?>
