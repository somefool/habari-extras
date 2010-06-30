<?php

/** 
 * Redirect anonymous users to the login form.
 *
 */
class LockOut extends Plugin
{
	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( $this->info->name, $this->info->guid, $this->info->version );
	}

	
	/**
	 * Redirect to theme's login page and fall back on standard one.
	 **/
	public function action_template_header()
	{
		if ( ! User::identify()->loggedin ) {
			Utils::redirect( URL::get( 'auth', array( 'page' => 'login' ) ) );
		}
	}

}

?>
