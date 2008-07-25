<?php

/**
 * Lockdown plugin
 */

class LockdownPlugin extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Lockdown',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Prevents users from making changes that would disable a demo install',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Prevent users who are the demo user from being deleted
	 *
	 * @param boolean $allow true to allow the deletion of this user
	 * @param User $user The user object requested to delete
	 * @return boolea true to allow the deletion of this user, false to deny
	 */
	function filter_user_delete_allow( $allow, $user )
	{
		// Don't allow the update of user #1.
		if($user->id == 1) {
			Session::notice('To maintain the integrity of the demo, the demo user account can\'t be deleted.', 'lockdown_user_delete');
			return false;
		}
		return $allow;
	}

	/**
	 * Prevent users who are the demo user from being updated
	 *
	 * @param boolean $allow true to allow the update of this user
	 * @param User $user The user object requested to update
	 * @return boolea true to allow the update of this user, false to deny
	 */
	function filter_user_update_allow( $allow, $user )
	{
		// Don't allow the update of user #1.
		if($user->id == 1) {
			Session::notice('To maintain the integrity of the demo, the demo user account can\'t be updated.', 'lockdown_user_update');
			return false;
		}
		return $allow;
	}

	/**
	 * Permit only certain options to be updated.
	 *
	 * @param mixed $value The value of the option
	 * @param string $name The name of the option
	 * @param mixed $oldvalue The original value of the option
	 * @return
	 */
	function filter_option_set_value( $value, $name, $oldvalue )
	{
		switch($name) {
			case 'cron_running':
				return $value;
				break;
			default:
				Session::notice('To maintain the integrity of the demo, option values can\'t be set.', 'lockdown_options');
				Session::notice('Option to set: '.$name);
				return $oldvalue;
		}
	}

	/**
	 * Prevent plugins from being activated
	 *
	 * @param boolean $ok true if it's ok to activate this plugin
	 * @param string $file The filename of the plugin
	 * @return boolean false to prevent plugins from being activated
	 */
	function filter_activate_plugin( $ok, $file )
	{
		Session::notice('To maintain the integrity of the demo, plugins can\'t be activated.', 'lockdown_plugin');
		return false;
	}

	/**
	 * Prevent plugins from being deactivated
	 *
	 * @param boolean $ok true if it's ok to deactivate this plugin
	 * @param string $file The filename of the plugin
	 * @return boolean false to prevent plugins from being deactivated
	 */
	function filter_deactivate_plugin( $ok, $file )
	{
		Session::notice('To maintain the integrity of the demo, plugins can\'t be deactivated.', 'lockdown_plugin');
		return false;
	}

}

?>