<?php

class prettyPrettyAdmin extends Plugin
{
	/**
	 * Removes system admin stylesheet from all admin headers, replaces it with the one from this plugin.
	 *
	 */
	public function action_admin_header( $theme )
	{
		Stack::remove('admin_stylesheet', 'admin');
		Stack::add('admin_stylesheet', array($this->get_url(true) . 'admin.css', 'screen'));
	}
}

?>
