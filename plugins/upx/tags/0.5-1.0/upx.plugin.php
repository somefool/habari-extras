<?php

/**
 * User Profile Exposure Plugin Class
 *
 **/

class UPX extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'UPX',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Exposes user profile information via xml entrypoint',
			'license' => 'Apache License 2.0',
		);
	}

	public function filter_rewrite_rules( $rules )
	{
		$rules[] = new RewriteRule(array(
			'name' => 'upx',
			'parse_regex' => '/^upx\/(?P<username>[^\/]+)\/?$/i',
			'build_str' => 'upx/{$username}',
			'handler' => 'UPX',
			'action' => 'display_user',
			'priority' => 7,
			'is_active' => 1,
		));
		return $rules;
	}

	public function action_handler_display_user($params)
	{
		$users = Users::get(array('info' => array('ircnick'=>$params['username'])));
		//Utils::debug($user->info);
		switch(count($users)) {
			case 0:
				$xml = new SimpleXMLElement('<error>No user with that IRC nickname.</error>');
				break;
			default:
				$xml = new SimpleXMLElement('<error>More than one user is registered under that nickname!</error>');
				break;
			case 1:
				$user = reset($users);
				$xml = new SimpleXMLElement('<userinfo></userinfo>');
				$xml['nickname'] = $params['username'];
				$xml->blog = $user->info->blog;
				break;

		}
		header('Content-type: text/plain');
		echo $xml->asXML();
	}

}

?>