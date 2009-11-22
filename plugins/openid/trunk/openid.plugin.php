<?php
class OpenID extends Plugin
{
	public function filter_rewrite_rules( $db_rules )
	{
		$db_rules[] = new RewriteRule( array(
			'name' => 'openid',
			'parse_regex' => '%^openid/?(?P<user>[^/]*)/?$%i', // For Server, if previous matched, don't look.
			'build_str' => 'openid/({$user})',
			'handler' => 'OpenID',
			'action' => 'dispatch',
			'priority' => 1,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'OpenID Authentification'
			) );

		return $db_rules;
	}

	public function act( $action )
	{
		if ( isset( $_GET['openid_mode'] ) ) {
			switch ( $_GET['openid_mode'] ) {
				case 'id_res':
					self::openid_end();
					break;
				case 'cancel':
					EventLog::log( 'Authorization failed: User cancelled authorization.', 'info', 'authentication', 'OpenID' );
					throw new Exception( 'Authorization failed: User cancelled authorization.' );
					break;
			}
		}
		else if ( isset( $_POST['openid_url'] ) ) {
			self::openid_start();
		}
		else {
			EventLog::log( 'Authorization failed: unknown error.', 'err', 'authentication', 'OpenID' );
			throw new Exception( 'Authorization failed: unknown error.' );
		}
	}

	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			if ( !extension_loaded('curl') && !@dl('curl') ) {
				EventLog::log( 'Could not load CURL, which is needed for OpenID to work.', 'err', 'authentication', 'OpenID' );
				throw new Exception( 'Could not load CURL, which is needed for OpenID to work.' );
			}
			EventLog::register_type( 'authentification', 'OpenID' );
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			EventLog::unregister_type( 'OpenID' );
		}
	}

	public function action_init()
	{
		if ( session_id() == '' ) {
			session_start();
		}
		ini_set( 'include_path', dirname( __FILE__ ) );
		Stack::add( 'template_stylesheet', array( $this->get_url() . '/openid.css', 'screen' ), 'openid_style' );
	}

	public function action_theme_loginform_before()
	{
		if ( isset( $_GET['openid_url'] ) ) {
			echo '<hr><div class="alert"><strong>If you have an existing account</strong>, sign in so we can assign your OpenID identifer to it.</div>';
		}
	}

	public function action_theme_loginform_after()
	{
	 // @todo Remove the !isset( $_GET['openid_url'] ) once registration works in Habari.
		if ( ( Controller::get_action() != 'register' ) && !isset( $_GET['openid_url'] ) ) {
			if ( Controller::get_action() == 'login' ) {
				echo '
				<form method="post" action="'. URL::get( 'openid' ) .'" id="admin_openidform">
				<p>
				<label for="openid_url" class="incontent abovecontent">' . _t('OpenID Identifier') . '</label><input type="text" name="openid_url" id="openid_url"' . ( isset($openid_url) ? 'value="'. $openid_url . '"' : '' ) . ' placeholder="' . _t('openid identifier') . '" class="styledformelement">
				</p>
				<p>
				<input id="openid_submit" class="submit" type="submit" value="Sign in using OpenID">
				</p>
				</form>
				';
			}
			else {
				echo '
				<form method="post" action="'. URL::get( 'openid' ) .'" id="openidform">
				<p>
				<label for="openid_url">OpenID Identifier:</label>
				<input type="text" size="25" name="openid_url" id="openid_url">
				</p>
				<p>
				<input type="submit" value="Sign in using OpenID">
				</p>
				</form>
				';
			}
		}
	}

	public function action_theme_loginform_controls()
	{
		if ( isset( $_GET['openid_url'] ) ) {
			echo '<input type="hidden" value="'.$_GET['openid_url'].'" name="habari_openid_url">';
		}
	}

	/* Uncomment once registration is supported by Habari.
	public function action_theme_registerform_controls()
	{
		if ( isset( $_GET['openid_url'] ) ) {
			echo '<input type="hidden" value="'.$_GET['openid_url'].'" name="habari_openid_url">';
		}
	} */

	public function action_theme_admin_user( $user )
	{
		$openid_url = isset( $user->info->openid_url ) ? $user->info->openid_url : '';
		echo '
		<div class="container settings user openid" id="openid">
				<h2>' . _t('OpenID') . '</h2>
				<div class="item clear" id="openid_url">
				<span class="pct20">
						<label for="habari_openid_url">' . _t('OpenID Identifier') . '</label>
				</span>
				<span class="pct80">
						<input type="text" name="habari_openid_url" id="habari_openid_url" class="border" value="' . $openid_url . '" disabled>
					</span>
				</div>
		</div>';
	}

	public function action_user_identify()
	{
		if ( ( Controller::get_action() == 'login' ) && !empty( $_POST['openid_url'] ) ) {
			self::openid_start();
		}
	}

	// TODO: Add more security against form hijacking (for instance, check against server sent data)
	public function action_user_authenticate_successful( $user )
	{
		if ( !empty( $_POST['habari_openid_url'] ) ) {
			$user->info->openid_url = $_POST['habari_openid_url'];
		}
	}

	function action_admin_header( $theme )
	{
		// Add the css if this is the default login page
		if ( $theme->admin_page == 'login' ) {
			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/openid.css', 'screen' ), 'openid_style' );
		}
	}

	function getOpenIDURL()
	{
		if ( empty( $_POST['openid_url'] ) ) {
			EventLog::log( 'Expected an OpenID URL.', 'err', 'authentication', 'OpenID' );
			throw new Exception( 'Expected an OpenID URL.' );
		}

		return $_POST['openid_url'];
	}

	function getReturnTo()
	{
		return URL::get('openid');
	}

	function getTrustRoot()
	{
		return Site::get_url('habari');
	}

	function getStore()
	{
		$store_path = "/tmp/_php_consumer_test";

		if ( !file_exists( $store_path ) && !mkdir( $store_path ) ) {
			EventLog::log( 'Could not create the FileStore directory: ' . $store_path, 'err', 'authentication', 'OpenID' );
			throw new Exception( 'Could not create the FileStore directory: ' . $store_path . '. Please check the effective permissions.' );
		}

		return new Auth_OpenID_FileStore( $store_path );
	}

	function getConsumer()
	{
		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/FileStore.php";
		require_once "Auth/OpenID/SReg.php";
		$store = self::getStore();
		return new Auth_OpenID_Consumer( $store );
	}

	function openid_start()
	{
		$openid = self::getOpenIDURL();
		$consumer = self::getConsumer();

		$auth_request = $consumer->begin( $openid );

		if ( !$auth_request ) {
			EventLog::log( 'Authentication error: Not a valid OpenID.', 'err', 'authentication', 'OpenID' );
			throw new Exception( 'Authentication error: Not a valid OpenID.' );
		}

		$sreg_request = Auth_OpenID_SRegRequest::build( array( 'nickname' ), array( 'fullname', 'email' ) );

		if ( $sreg_request ) {
			$auth_request->addExtension( $sreg_request );
		}

		if ( $auth_request->shouldSendRedirect() ) {
			$redirect_url = $auth_request->redirectURL( self::getTrustRoot(), self::getReturnTo() );

			if ( Auth_OpenID::isFailure( $redirect_url ) ) {
				EventLog::log( 'Could not redirect to server: ' . $redirect_url->message, 'err', 'authentication', 'OpenID' );
				throw new Exception( 'Could not redirect to server: ' . $redirect_url->message );
			}
			else {
				header( "Location: ".$redirect_url );
			}
		}
		else {
			$form_id = 'openid_message';
			$form_html = $auth_request->formMarkup( self::getTrustRoot(), self::getReturnTo(), false, array( 'id' => $form_id ) );

			if ( Auth_OpenID::isFailure( $form_html ) ) {
				EventLog::log( 'Could not prepare redirection form: ' . $form_html->message, 'err', 'authentication', 'OpenID' );
				throw new Exception( 'Could not prepare redirection form: ' . $form_html->message );
			}
			else {
				echo '
					<html>
					<head>
					<title>OpenID transaction in progress</title>
					</head>
					<body onload="document.getElementById(\''.$form_id.'\').submit()">
					'.$form_html.'
					</body>
					</html>
					';
			}
		}
	}

	function openid_end()
	{
		$consumer = self::getConsumer();
		$return_to = self::getReturnTo();
		$response = $consumer->complete( $return_to, Auth_OpenId::getQuery( $_SERVER->raw( 'QUERY_STRING' ) ) );

		switch( $response->status ) {
			case Auth_OpenID_CANCEL:
				EventLog::log( 'Verification cancelled.', 'err', 'authentication', 'OpenID' );
				throw new Exception( 'Verification cancelled.' );
				break;
			case Auth_OpenID_FAILURE:
				EventLog::log( 'OpenID authentication failed: ' . $response->message, 'err', 'authentication', 'OpenID' );
				throw new Exception( 'OpenID authentication failed: ' . $response->message );
				break;
			case Auth_OpenID_SUCCESS:
				$openid = $response->getDisplayIdentifier();
				$esc_identity = htmlspecialchars( $openid, ENT_QUOTES );

				$user = Users::get_by_info( 'openid_url', $openid );
				if ( count( $user ) != 0 ) {
					if ( count( $user ) > 1 ) {
						EventLog::log( 'Authentication error: More than one user has this OpenID.', 'err', 'authentication', 'OpenID' );
						throw new Exception( 'Authentication error: More than one user has this OpenID.' );
					}
					$user[0]->remember();
					EventLog::log( 'Successful login for ' . $user[0]->username, 'info', 'authentication', 'OpenID' );

					header( "HTTP/1.1 100 Continue" );
					header( "Location: " . Site::get_url( 'admin' ) );
					header( "Connection: close" );
				}
				else {
					Utils::redirect( URL::get( 'user', array( 'page'=>'login', 'openid_url' => $openid ), true ) );
				}
			}
		}

}
?>
