<?php

class LoginRedirect extends Plugin
{
	public function filter_login_redirect_dest( $login_dest, $user, $login_session )
	{
		if(isset($login_session) && $user->info->login_redirect != '') {
			$login_dest = $user->info->login_redirect;
		}
		return $login_dest;
	}

	/**
	 * Add the configuration to the user page
	 **/
	public function action_form_user( $form, $user )
	{
		$fieldset = $form->append( 'wrapper', 'login_redirect_fieldset', 'Login Redirect' );
		$fieldset->class = 'container settings';
		$fieldset->append( 'static', 'login_redirect_title', '<h2>Login Redirect</h2>' );
	
		$activate = $fieldset->append( 'text', 'login_redirect', 'null:null', _t('Redirect to this URL after login:') );
		$activate->class[] = 'item clear';
		$activate->value = $user->info->login_redirect;
		
		$form->move_before( $fieldset, $form->page_controls );
	}
	
	/**
	 * Save authentication fields
	 **/
	public function filter_adminhandler_post_user_fields( $fields )
	{
		$fields[] = 'login_redirect';
	
		return $fields;
	}

}

?>