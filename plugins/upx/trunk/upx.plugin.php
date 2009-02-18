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
				$xml->name = $user->info->displayname;
				$xml->nickname = $user->info->ircnick;
				break;

		}
		header('Content-type: text/xml');
		ob_clean();		// no idea why we get a blank line at the beginning, but it breaks XML parsing
		echo $xml->asXML();
	}
	
	public function filter_adminhandler_post_user_fields ( $fields ) {
		
		$fields['ircnick'] = 'ircnick';
		$fields['blog'] = 'blog';
		
		return $fields;
	
	}
	
	public function action_form_user ( $form, $edit_user ) {

		$ircnick = ( isset( $user->info->ircnick ) ) ? $user->info->ircnick : '';
		
		// insert the UPX block into the form above the page_controls section
		$upx = $form->insert('page_controls', 'wrapper', 'upx', _t('UPX'));
		$upx->class = 'container settings';
		$upx->append( 'static', 'upx', '<h2>' . htmlentities( _t('UPX'), ENT_COMPAT, 'UTF-8' ) . '</h2>' );
		
		$ircnick = $form->upx->append( 'text', 'ircnick', 'user:foo', _t( 'IRC Nick' ), 'optionscontrol_text' );
		$ircnick->class = 'item clear';
		$ircnick->value = $edit_user->info->ircnick;
		
		$blog = $form->upx->append( 'text', 'blog', 'null:null', _t( 'Blog URL' ), 'optionscontrol_text' );
		$blog->class = 'item clear';
		$blog->value = $edit_user->info->blog;
		
	}

}

?>