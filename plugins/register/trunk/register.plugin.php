<?php

class Register extends Plugin
{

	/**
	 * Add update beacon support
	 */
	public function action_update_check()
	{
		Update::add( 'Register', '57281af4-f709-46d0-8089-6bb327bab3e5', $this->info->version );
	}

	/**
	 * Add help text to plugin configuration page
	 */
	public function help()
	{
		$help = _t( 'Lets people register to become blog users. Administrators can specify which group new users should be placed in. Registration forms can exist on their own page or can be added to an existing theme template using, for example $theme->signup(\'registered\') to add users to the \'registered\' group.');
		return $help;
	}

	/**
	 * Create rewrite rule
	 */
	public function action_init()
	{

		$this->add_template('registration', dirname(__FILE__) . '/register.php');
		$this->add_template('registration.success', dirname(__FILE__) . '/success.php');

		if ( Options::get('register__standalone') ) {
			$this->add_rule('"user"/"register"', 'register_page');
			$this->add_rule('"user"/"register"/"success"', 'register_success');
		}

	}


	/**
	 * Handle register_page action
	 */
	public function action_plugin_act_register_page( $handler )
	{

		if ( User::identify()->loggedin ) {
			Session::notice( sprintf( _t( 'You are already logged in as %s' ), User::identify()->displayname ) );
			Utils::redirect( Site::get_url( 'admin' ), false);
		}

		$form = $this->get_form();
		$form->set_option('standalone', true);

		$handler->theme->form = $form;

		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery', array('jquery') );
		// Stack::add( 'template_header_javascript', Site::get_url('admin_theme') . "/js/admin.js", 'admin', array('jquery', 'registration') );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/registration.js', 'registration', array('jquery') );

		Stack::add('template_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/registration.css', 'screen'), 'registration', array() );

		$handler->theme->display('registration');

	}

	/**
	 * Handle register_success action
	 */
	public function action_plugin_act_register_success( $handler )
	{
		if ( !User::identify()->loggedin ) {
			Utils::redirect( URL::get('register_page'), false);
		}

		$handler->theme->user = User::identify();

		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery', array('jquery') );
		// Stack::add( 'template_header_javascript', Site::get_url('admin_theme') . "/js/admin.js", 'admin', array('jquery', 'registration') );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/registration.js', 'registration', array('jquery') );

		Stack::add('template_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/registration.css', 'screen'), 'registration', array() );

		$handler->theme->display('registration.success');

	}

	/**
	 * Create plugin configuration
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					$form = new FormUI( strtolower( get_class( $this ) ) );

					$form->append( 'checkbox', 'standalone', 'register__standalone', sprintf( _t( 'Show standalone <a href="%s">registration form</a>' ), URL::get('register_page') ) );

					$groups = UserGroups::get_all();
					$options = array();
					foreach ( $groups as $group ) {
						$options[$group->id] = $group->name;
					}
					$form->append( 'select', 'group', strtolower( get_class( $this ) ) . '__group', _t('Default group:'), $options );

					$form->append( 'submit', 'save', _t('Save') );
					$form->out();
					break;
			}
		}
	}

	public function get_form( $group = null)
	{

		if ( $group == null ) {
			$group = Options::get('register__group');
			if ( $group == null ) {
				$group = 'anonymous';
			}
		}

		$form = new FormUI('registration');
		$form->class[] = 'registration';

		$form->append('text', 'email', 'null:null', _t('Email'), 'formcontrol_text');
		$form->email->add_validator('validate_email');
		$form->append('text', 'username', 'null:null', _t('Username'), 'formcontrol_text');
		$form->username->add_validator('validate_required');
		$form->username->add_validator('validate_username');

		$form->append('password', 'password', 'null:null', _t('Password'), 'formcontrol_password');
		$form->password->add_validator('validate_required');

		$form->append('password', 'password_confirmation', 'null:null', _t('Confirm Password'), 'formcontrol_password');
		$form->password_confirmation->add_validator('validate_required');
		$form->password_confirmation->add_validator('validate_same', $form->password);

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

			if ( $form->get_option('standalone') ) {
				$user->remember();
				Utils::redirect(URL::get('register_success'), false);
			}
			else {
				Session::notice( sprintf( _t( "Added user '%s'" ), $form->username ) );
			}
		}
		else {
			$dberror = DB::get_last_error();
			Session::error( $dberror[2], 'adduser' );
		}
	}

	public function theme_registration( $theme, $group = null )
	{
		$this->get_form($group)->out();
	}

}

?>
