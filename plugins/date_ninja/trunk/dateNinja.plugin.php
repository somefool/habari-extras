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
	
	public function action_init() {
		// add some js to the admin header
		Stack::add( 'admin_header_javascript', '/user/plugins/date_ninja/date_ninja.js', 'dateninja' );
		Stack::add( 'admin_header_javascript', '/user/plugins/date_ninja/date.js', 'datejs' );
	}
}
?>