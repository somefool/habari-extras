<?php

/**
 * Keep Login plugin
 *
 **/

class KeepLogin extends Plugin
{
	/**
	 * Add output in the admin header
	 * Adds the necessary javascript to make periodic ajax calls to the admin.
	 */
	public function action_admin_header()
	{
		$ajaxurl = URL::get('auth_ajax', array('context'=>'keep_session'));
		$script = <<< HEADER_JS
$(document).ready(function(){
	window.setInterval(
		function(){
			$.post('$ajaxurl');
		},
		1000 * 60 * 5 // 5 minutes
	);
});
HEADER_JS;
		Stack::add( 'admin_header_javascript',  $script, 'keep_login', array('jquery') );
	}

	public function action_auth_ajax_keep_session()
	{
		echo time();
	}

}

?>
