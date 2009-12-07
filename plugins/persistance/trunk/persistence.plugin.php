<?php



/**
 * persistence
 *
 * Alows you to authenticate to your Habari installation via a cookie stored on your PC
 * 
 * @package persistence
 */




class persistence extends Plugin
{
	
	/**
	 * Add update beacon support
	 **/
	
	
	public function action_update_check()
	{
	 	Update::add( 'persistence', 'c0e3de6f-62f9-4f3d-9e98-5f29e4c2628d', $this->info->version );
	}
	


	/**
	 * function action_plugin_activation
	 * Registers an EventLog module and cron job for this plugin
	 * @param string The name of the plugin that is being activated
	**/
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			EventLog::register_type( 'authentification', 'persistence' );
			 CronTab::add_hourly_cron( 'persistence', 'persistence', 'Clean up stale persistent cookies' );
		}
	}

	/**
	 * function action_plugin_deactivation
	 * disables the cron job when the plugin is deactivated
	**/
	public function action_plugin_deactivation( $file )
	{
		 if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
		 	CronTab::delete_cronjob( 'persistence' );
		}
	}


	/**
	 * function filter_persistence
	 * cleans up the stale persistence cookies from the Options table
	**/
	public function filter_persistence()
	{
		$time= time();
		$delete= DB::query( 'DELETE FROM {userinfo} WHERE name LIKE "persistence_%" AND value < ?', array( $time ) );
	}

	/**
	 * function action_theme_loginform_controls
	 * add a checkbox to the login screen to control our cookie
	**/
	public function action_theme_loginform_controls()
	{
		_e( 'Remember me on this computer?' );
		echo ' <input type="checkbox" name="persistence">';
	}

	/**
	 * function action_user_authenticate_successful
	 * Saves a cookie for this user when they successfully authenticate
	 * @param User the user that authenticated
	**/
	public function action_user_authenticate_successful( $user )
	{
		if ( ! isset( $_POST['persistence'] ) ) {
			return;
		}
		$time= time() + 2592000;
		$value= Utils::crypt( $user->username . $user->id . $time );
		// set the cookie on the user's PC
		$cookiename= 'P_' . md5( Options::get( 'GUID' ) . '_Persistence' );
		setcookie( $cookiename, $value, $time, Site::get_path(' base', true ) );
		// store a userinfo record for this value
		$info= 'persistence_' . $value;
		$user->info->$info= $time;
		// commit the change
		$user->update();
	}

	/**
	 * function action_user_logout
	 * removes the persistent cookie when the user logs out
	 * @param user User the user that is logging out
	**/
	public function action_user_logout( $user )
	{
		$cookiename= 'P_' . md5( Options::get( 'GUID' ) . '_Persistence' );
		if ( ! isset( $_COOKIE[$cookiename] ) ) {
			return;
		}
		$cookie= $_COOKIE[$cookiename];
		$info= 'persistence_' . $cookie;
		// remove the info record for this cookie
		unset( $user->info->$info );
		// commit the change
		$user->update();
		// remove the cookie from the user's PC
		setcookie( $cookiename, 'empty', time() - 3600, Site::get_path(' base', true ) );
	}

	/**
	 * function action_plugins_loaded
	 * checks for the presence of our cookie, and uses that to establish the session for the user
	**/
	public function action_init()
	{
		// first, let's see if an active session is in effect
		$user= User::identify();
		if ( 0 < $user->id ) {
			// no sense processing any further if we already know
			// about this user.
			return;
		}
		// make sure we don't try to accidentially re-use the anonymous
		// user object later. probably unnecessary.
		unset( $user );

		// does our cookie exist on the user's PC?
		$cookiename= 'P_' . md5( Options::get( 'GUID' ) . '_Persistence' ); 
		if ( ! isset( $_COOKIE[$cookiename] ) ) {
			// no?  bail out
			return;
		}

		$cookie= $_COOKIE[$cookiename];
		$result= DB::get_row( 'SELECT user_id,value FROM {userinfo} WHERE name=?', array( 'persistence_' . $cookie ) );
		if ( ! $result ) {
			return;
		}

		if ( time() > $result->value ) {
			// the cookie has expired
			return;
		}

		$user= User::get_by_id( $result->user_id );
		// make sure the hash value stored in the cookie is correct for this user
		if ( ! Utils::crypt( $user->username . $user->id . $result->value, $cookie ) ) {
			// hash doesn't match, so bail out
			unset ( $user );
			return;
		}

		// we got here, so let's remember this user
		$user->remember();
		// and store a log entry
		EventLog::log( 'Successful login by cookie for ' . $user->username, 'info', 'authentication', 'persistence' );
	}

}
?>
