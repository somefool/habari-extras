<?php

/**
 * Lockdown plugin
 */

class LockdownPlugin extends Plugin
{
	/**
	 * Prevent users who are the demo user from being deleted
	 *
	 * @param boolean $allow true to allow the deletion of this user
	 * @param User $user The user object requested to delete
	 * @return boolea true to allow the deletion of this user, false to deny
	 */
	function filter_user_delete_allow( $allow, $user )
	{
		if($user->username == 'demo') {
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
		if($user->username == 'demo') {
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
		
		// the only options allowed to be changed
		if ( in_array( $name, array( 'theme_dir', 'theme_name', 'cron_running', 'pluggable_versions' ) ) ) {
			return $value;
		}
		
		// only allow active_plugins to change if this class is the only thing being added
		if ( $name == 'active_plugins' && is_array( $value ) ) {
			$diff = array_diff_key( $value, $oldvalue );
		
			if ( count( $diff ) == 1 && isset( $diff[ __CLASS__ ] ) ) {
				return $value;
			}
		}
		
		// otherwise, we throw our error and don't let the option change
		Session::notice('To maintain the integrity of the demo, option values can\'t be set.', 'lockdown_options');
		Session::notice('Option to set: '.$name);
		
		return $oldvalue;
		
		
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
		
		// allow the lockdown plugin to be activated
		if ( $file == __FILE__ ) {
			return true;
		}
		
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
	
	/**
	 * Prevent certain published content from causing problems.
	 **/	  		
	function filter_post_content( $content )
	{
		$newcontent = InputFilter::filter($content);
		if($content != $newcontent) {
			Session::notice('Certain content is filtered from posts to maintain the integrity of the demo.', 'lockdown_plugin');
		}
		return $newcontent;
	}
	
	/**
	 * Filter title, slug, and tags fields for HTML content
	 **/	 	
	function action_publish_post( $post, $form )
	{
		$post->title = htmlentities($post->title);
		$post->slug = urlencode($post->slug);
		$post->tags = htmlentities($form->tags);
	}

}

?>