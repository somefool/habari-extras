<?php
class dateNinja extends Plugin
{
	public function info() {
		return array(
			'name' => 'Date Ninja',
			'version' => '1.0',
			'url' => 'http://www.chrisjdavis.org',
			'author' => 'Chris J. Davis',
			'authorurl' => 'http://www.chrisjdavis.org',
			'license' => 'MIT',
			'description' => 'Allows you to use plain language when selecting dates. Based on datejs.',
			'copyright' => '2008',
			);
	}

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
