<?php

class Commentated extends Plugin
{
	function info()
	{
		return array(
			'name' => 'Commentated',
			'version' => '1.0',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Shows commenters a live preview of their comment',
		);
	}

	public function action_init_theme() {
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/commentated.js', 'commentated' );
	}
}

?>