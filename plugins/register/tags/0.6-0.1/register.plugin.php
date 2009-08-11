<?php

class Register extends Plugin
{

	const VERSION= '0.1';

	/**
	 * Return plugin metadata for this plugin
	 *
	 * @return array Plugin metadata
	 */
	public function info()
	{
		return array(
			'url' => 'http://habariproject.org',
			'name' => 'Register',
			'license' => 'Apache License 2.0',
			'author' => 'Habari Community',
			'version' => self::VERSION,
			'description' => 'Lets people register to become blog users, and be placed in a group specified by the admin.'
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( 'Register', '57281af4-f709-46d0-8089-6bb327bab3e5', $this->info->version );
	}

	/**
	 * Add help text to plugin configuration page
	 **/
	public function help()
	{
		$help = _t( 'Lets people register to become blog users. Administrators can specify which group new users should be placed in. Registration forms can exist on their own page or can be added to an existing theme template using, for example $theme->signup(\'registered\') to add users to the \'registered\' group.');
		return $help;
	}

	/**
	 * Add the registration content type
	 **/
	public function action_plugin_activation( $plugin_file )
	{
		if ( Plugins::id_from_file(__FILE__) == Plugins::id_from_file($plugin_file) ) {
			// Store a secret key for hashing group names
			Options::set('register_secret', UUID::get());
		}
	}

	public function get_form( $group )
	{
		$form = new FormUI('registration');
		$form->class[] = 'registration';

		$form->append('text', 'email', 'null:null', _t('Email'), 'formcontrol_text');
		$form->email->add_validator('validate_email');
		$form->append('text', 'username', 'null:null', _t('Username'), 'formcontrol_text');
		$form->username->add_validator('validate_required');
		$form->username->add_validator('validate_username');

		$form->append('text', 'password', 'null:null', _t('Password'), 'formcontrol_text');
		$form->password->add_validator('validate_required');

		// Store the group to be added. This is stored locally, not retrieved from unsafe data.
		$form->set_option('group_name', $group);

		// Create the Register button
		$form->append('submit', 'register', _t('Register'), 'formcontrol_submit');

		$form->on_success( array( $this, 'register_user' ) );

		// Return the form object
		return $form;
	}

	public function register_user( $form )
	{
		$group = UserGroup::get($form->get_option('group_name'));

		$user = new User( array( 'username' => $form->username, 'email' => $form->email, 'password' => Utils::crypt( $form->password ) ) );
		if ( $user->insert() ) {
			$group->add($user);
			Session::notice( sprintf( _t( "Added user '%s'" ), $form->username ) );
		}
		else {
			$dberror = DB::get_last_error();
			Session::error( $dberror[2], 'adduser' );
		}
	}

	public function theme_registration( $theme, $group )
	{
		$this->get_form($group)->out();
	}

}

?>
