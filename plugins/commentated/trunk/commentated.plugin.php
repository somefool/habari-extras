<?php

class Commentated extends Plugin
{
	public function action_init_theme() {
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/commentated.js', 'commentated' );
	}
}

?>