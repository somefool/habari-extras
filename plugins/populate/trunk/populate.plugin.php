<?php

/**
 * Populate the database with users, groups and tokens to see how admin theme
 * and templates render, and check management functionality.
 *
 * @author ilo
 *
 **/

class Populate extends Plugin
{

	/**
	 * Habari integration functions.
	 */

	/**
	 * Plugin activation function. Populate tables with random data using
	 * default values.
	 * @param string $file the plugin being activated.
	 */
	function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			// Set default options for the module.
			Options::set( 'populate__tokens', 15);
			Options::set( 'populate__groups', 5);
			Options::set( 'populate__users', 25);

			// Avoid breaking in the middle of the operation
			$time_limit = ini_get('time_limit');
			ini_set( 'time_limit', 0);

			// Create random data
			$this->populate_tokens_add();
			$this->populate_groups_add();
			$this->populate_users_add();

			ini_set( 'time_limit', $time_limit);
		}
	}

	/**
	 * Plugin activation function. Remove all information created by this plugin.
	 * @param string $file the plugin being activated.
	 */
	function action_plugin_deactivation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			// Avoid breaking in the middle of the operation
			$time_limit = ini_get('time_limit');
			ini_set( 'time_limit', 0);

			// Destroy all data
			$this->populate_users_remove();
			$this->populate_groups_remove();
			$this->populate_tokens_remove();

			ini_set( 'time_limit', $time_limit);

			// Delete all options created by this plugin.
			Options::delete( 'populate__tokens' );
			Options::delete( 'populate__groups' );
			Options::delete( 'populate__users' );

		}
	}

	/**
	 * Adds a Configure action to the plugin
	 *
	 * @param array $actions An array of actions that apply to this plugin
	 * @param string $plugin_id The id of a plugin
	 * @return array The array of actions
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= 'Configure';
		}
		return $actions;
	}

	/**
	 * Handler for the user interface action.
	 * @param string $plugin_id The id of a plugin.
	 * @param array $actions An array of actions that apply to this plugin.
	 * @return FormUI
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id() == $plugin_id && $action=='Configure' ) {
			return $this->form_ui_configure_form();
		}
	}

	/**
	 * User interface functions.
	 */

	/**
	 * Builds the configuration form.
	 * return @FormUI
	 */
	private function form_ui_configure_form()
	{
		$form = new FormUI( strtolower(get_class( $this ) ) );
		$form->append( 'text', 'tokens', 'option:populate__tokens', _t('Number of Tokens:'));
		$form->append( 'text', 'groups', 'option:populate__groups', _t('Number of user Groups:'));
		$form->append( 'text', 'users', 'option:populate__users', _t('Number of Users:'));
		$form->tokens->add_validator( 'validate_required' );
		$form->groups->add_validator( 'validate_required' );
		$form->users->add_validator( 'validate_required' );
		$form->tokens->add_validator( 'validate_range', -1, 200 );
		$form->groups->add_validator( 'validate_range', -1, 200 );
		$form->users->add_validator( 'validate_range', -1, 200 );
		$form->append( 'submit', 'save', _t( 'Save' ) );
		$form->on_success( array( $this, 'form_ui_configure_success' ) );
		$form->out();
	}

	/**
	 * Automatically update the number of tokens, users and groups to the new
	 * configuration values. This function will add or remove items according to
	 * the configured values.
	 * @param FormUI $ui the form being submitted
	 */
	public function form_ui_configure_success( $ui ) {
		// Avoid breaking in the middle of the operation
		$time_limit = ini_get('time_limit');
		ini_set( 'time_limit', 0);

		// Fix the number of tokens.
		$tokens_diff = $ui->tokens->value - count( $this->populate_tokens_get()) ;
		if ( $tokens_diff > 0) {
			$this->populate_tokens_add( $tokens_diff );
		}
		else if ( $tokens_diff < 0) {
			$this->populate_tokens_remove( abs($tokens_diff) );
		}

		// Fix the number of groups.
		$groups_diff = $ui->groups->value - count( $this->populate_groups_get());
		if ( $groups_diff > 0) {
			$this->populate_groups_add( $groups_diff );
		}
		else if ( $groups_diff < 0) {
			$this->populate_groups_remove( abs($groups_diff) );
		}

		// Fix the number of groups.
		$users_diff = $ui->users->value - count( $this->populate_users_get());
		if ( $users_diff > 0) {
			$this->populate_users_add( $users_diff );
		}
		else if ( $users_diff < 0) {
			$this->populate_users_remove( abs($users_diff) );
		}

		ini_set( 'time_limit', $time_limit);
		SEssion::notice( _t( 'Finished updating populated data.' ) );
	}

	/**
	 * API functions
	 */

	/**
	 * Add tokens to the tokens table
	 * @param integer $num number of tokens to add, or configured value if null.
	 */
	private function populate_tokens_add( $num = null)
	{
		if ( !isset( $num ) ) {
			$num = Options::get('populate__tokens');
		}

		// Add all these tokens.
		$count = 0;
		while  ( $count++ < $num ) {
			// Create an ACL token.
			ACL::create_token( 'populate_token_' . strtolower( $this->helper_random_name(9)), 'Populate ' . $this->helper_random_name(), 'Administration', rand(0, 1) );
		}
		return;
	}

	/**
	 * Get tokens created by this module.
	 * @param integer $num number of tokens to get, or all if not specified.
	 * @return array tokens found.
	 */
	private function populate_tokens_get( $num = null )
	{
		// get all tokens
		$alltokens = ACL::all_tokens();

		// internal loop to get only our tokens.
		$tokens = array();
		$count  = 0;
		foreach ( $alltokens as $id => $token) {
			if ( strpos( $token->name, 'opulate_' ) ) {
				$tokens[] = $token;
				if ( isset( $num ) && $num == ++$count ) break;
			}
		}
		return $tokens;
	}

	/**
	 * Remove tokens from the tokens table.
	 * @param integer $num number of tokens to remove, or configured value if null.
	 */
	private function populate_tokens_remove( $num = null )
	{
		// Get our own tokens.
		$tokens = $this->populate_tokens_get( $num );

		// clean these tokens
		foreach ( $tokens as $id => $token) {
			ACL::destroy_token($token->id);
		}
		return;
	}

	/**
	 * Add user groups to the groups table, randomly assign them existing tokens.
	 * @param integer $num number of groups to add, or configured value if null.
	 */
	private function populate_groups_add( $num = null )
	{
		if ( !isset( $num ) ) {
			$num = Options::get('populate__groups');
		}

		// Get all tokens that we have created.
		$tokens = $this->populate_tokens_get();

		// Add all these groups.
		$count = 0;
		while  ( $count++ < $num ) {
			// Create a new group.
			$group = Usergroup::create( array( 'name' => 'populate_' . $this->helper_random_name() ) );

			// assign tokens to this new group.
			$max_tokens = count( $tokens ) - 1 ;
			$num_tokens = rand( 0, $max_tokens );
			while ($num_tokens-- > 0) {
				$token = rand(0, $max_tokens );
				ACL::grant_group( $group->id, $tokens[$token]->id );
			}
		}
		return;
	}

	/**
	 * Get groups created by this module.
	 * @param integer $num number of groups to get, or all if not specified.
	 * @return array groups found.
	 */
	private function populate_groups_get( $num = null )
	{
		// get all tokens
		$allgroups = Usergroups::get_all();

		// internal loop to get only our groups.
		$groups = array();
		$count  = 0;
		foreach ( $allgroups as $id => $group) {
			if ( strpos( $group->name, 'opulate_' ) ) {
				$groups[] = $group;
				if ( isset( $num ) && $num == ++$count ) break;
			}
		}
		return $groups;
	}

	/**
	 * Remove user groups to the groups table.
	 * @param integer $num number of groups to remove, or configured value if null.
	 */
	private function populate_groups_remove( $num = null )
	{
		// Get our own groups.
		$groups = $this->populate_groups_get( $num );

		// clean these groups.
		foreach ( $groups as $id => $group ) {
			$group->delete();
		}
		return;
	}

	/**
	 * Add users to the users table, randomly assign them to existing groups.
	 * @param integer $num number of users to add, or configured value if null.
	 */
	private function populate_users_add( $num = null )
	{
		if ( !isset( $num ) ) {
			$num = Options::get('populate__users');
		}

		// Get all groups.
		$groups = $this->populate_groups_get();

		// Add all these users.
		$count = 0;
		while  ( $count++ < $num ) {
			$name = $this->helper_random_name();
			$user = User::create(array (
				'username' => 'populate_' . $name,
				'email'    => $name . '@example.com',
				'password' => md5('q' . rand(0,65535)),
			));

			// Assign this user to groups.
			$max_groups = count( $groups ) - 1;
			$num_groups = rand( 0, $max_groups );
			while ($num_groups-- > 0) {
				$group = rand(0, $max_groups );
				$user->add_to_group($groups[$group]->id);
			}
		}
		return;
	}

	/**
	 * Get users created by this module.
	 * @param integer $num number of users to get, or all if not specified.
	 * @return array users found.
	 */
	private function populate_users_get( $num = null )
	{
		// get all tokens
		$allusers = Users::get_all();

		// internal loop to get only our users.
		$users = array();
		$count  = 0;
		foreach ( $allusers as $id => $user) {
			if ( strpos( $user->username, 'opulate_' ) ) {
				$users[] = $user;
				if ( isset( $num ) && $num == ++$count ) break;
			}
		}
		return $users;
	}

	/**
	 * Remove users from the users table.
	 * @param integer $num number of users to remove, or configured value if null.
	 */
	private function populate_users_remove( $num = null )
	{
		// Get our own users.
		$users = $this->populate_users_get( $num );

		// clean these users.
		foreach ( $users as $id => $user ) {
			$user->delete();
		}
		return;
	}


	/*
	 * Helper functions
	 */

	/**
	 * Generates a random string containing letters and numbers.
	 *
	 * The string will always start with a letter. The letters may be upper or
	 * lower case.
	 *
	 * @param $length
	 *   Length of random string to generate.
	 * @return
	 *   Randomly generated string.
	 */
	public function helper_random_name ( $length = 12 ) {
		$values = array_merge(range(65, 90), range(97, 122), range(48, 57));
		$max = count($values) - 1;
		$str = chr(mt_rand(97, 122));
		for ($i = 1; $i < $length; $i++) {
			$str .= chr($values[mt_rand(0, $max)]);
		}
		return $str;
	}

}

?>
