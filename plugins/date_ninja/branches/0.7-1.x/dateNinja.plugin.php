<?php
class dateNinja extends Plugin
{

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'DateNinja', 'e64e02e0-38f8-11dd-ae16-0800200c9a66', $this->info->version );
	}

	public function action_init() {
		// add some js to the admin header
		Stack::add( 'admin_header_javascript', $this->get_url() . '/date_ninja.js', 'dateninja' );
		Stack::add( 'admin_header_javascript', $this->get_url() . '/date.js', 'datejs' );
	}
}
?>
