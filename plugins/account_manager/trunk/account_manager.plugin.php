<?php
class AccountManager extends Plugin {

	/**
	 * Filter function called by the plugin hook `rewrite_rules`
	 * Add a new rewrite rule to the database's rules.
	 *
	 * Call `AccountManager::act('host-meta')` when a request for the host-meta `/.well-known/host-meta` is received.
	 * Call `AccountManager::act('amcd')` when a request for the Account Manager Control Document `/amcd` is received.
	 *
	 * @param array $db_rules Array of rewrite rules compiled so far
	 * @return array Modified rewrite rules array, we added our custom rewrite rule
	 */
	public function filter_rewrite_rules( $db_rules )
	{
		$db_rules[]= RewriteRule::create_url_rule( '"amcd"', 'AccountManager', 'amcd' );
		// This could be used for when the extant forms require extra values to work than what client-side AM provides, otherwise remove:
		$db_rules[]= RewriteRule::create_url_rule( '"amcd"/method', 'AccountManager', 'amcd_method' );
		return $db_rules;
	}

	/**
	 *
	 */
	public function action_init( )
	{
		if(User::identify()->loggedin) {
			header("X-Account-Management-Status: active; name='" . User::identify()->username . "'");
		}
		else {
			header("X-Account-Management-Status: passive");
		}
		header('X-Account-Management: ' . URL::get('amcd'));
	}

	/**
	 * Act function called by the `Controller` class.
	 * Dispatches the request to the proper action handling function.
	 *
	 * @param string $action Action called by request, we only support 'amcd' and 'host-meta'
	 */
	public function act( $action )
	{
		switch ( $action )
		{
			case 'amcd':
				self::amcd();
				break;
			case 'amcd_method':
				self::amcd_method();
				break;
			case 'host-meta':
				self::hostmeta();
				break;
		}
	}
	
	/**
	 */
	public function amcd()
	{
		$json = array(
			'methods' => array(
				'username-password-form' => array ( // Username+Password profile
					'connect' => array(
						'method' => 'POST',
						'path' => URL::get('auth', array('page' => 'login')),
						'params' => array(
							'username' => 'habari_username',
							'password' => 'habari_password',
						),
					),
					'disconnect' => array(
						'method' => 'POST',
						'path' => URL::get('auth', array('page' => 'logout')),
					),
					'changepassword' => array(
						'method' => 'POST',
						'path' => URL::get('auth', array('page' => 'logout')),
					),
					'sessionstatus' => array(
						'method' => 'GET',
						'path' => URL::get('auth', array('page' => 'login')),
					),
					'accountstatus' => array(
						'method' => 'GET',
						'path' => URL::get('auth', array('page' => 'login')),
					),
				),
			),
		);
		
		/* Clean the output buffer, so we can output from the header/scratch. */
		ob_clean();
		header( 'Content-Type: application/json' );
		echo json_encode($json);
	}
	
	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'AccountManager', '7b0c466c-16fe-4668-8366-50af0ba0dc5a', $this->info->version );
	}
}
?>
