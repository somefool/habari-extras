<?php

class postPass extends Plugin
{
	public function info()
	{
		return array (
			'name' => 'Post Pass',
			'version' => '0.1',
			'author' => 'Habari Community',
			'license' => 'Apache License 2.0',
			'description' => 'Allows you to password protect your entries.',
		);
	}
	
	public function set_priorities()
	{
		return array(
			'filter_post_content' => 9,
		);
	}
	
	public function action_init()
	{
		$this->add_template( 'post_password_form', dirname($this->get_file()) . '/post_password_form.php' );
	}
	
	public function filter_post_content( $content, Post $post )
	{
		if ( $post->info->password ){
			// if user logged in, show post
			$user = User::identify();
			if ( $user instanceof User ) {
				return $content;
			}
			
			$session = Session::get_set('post_passwords', false);
			$token = Utils::crypt( '42' . $post->info->password . $post->id . Options::get('GUID') );
			
			// if password was submitted verify it
			if ( Controller::get_var('post_password') && Controller::get_var('post_password_id') == $post->id ) {
				$pass = InputFilter::filter(Controller::get_var('post_password'));
				if ( Utils::crypt($pass, $post->info->password) ) {
					Session::add_to_set('post_passwords', $token, $post->id);
					$session[$post->id] = $token;
				}
				else {
					Session::error( _t('That password was incorrect.', 'postpass') );
				}
			}
			
			// if password is stored in session verify it
			if ( isset($session[$post->id]) && $session[$post->id] == $token ) {
				return $content;
			}
			else {
				$theme = Themes::create();
				$theme->post = $post;
				return $theme->fetch('post_password_form');
			}
		}
		else {
			return $content;
		}
	}
	
	public function action_publish_post( Post $post, FormUI $form )
	{
		if ( $post->content_type == Post::type('entry') ) {
			$post->info->password = Utils::crypt($form->postpass->value);
		}
	}
	
	public function action_form_publish( FormUI $form, Post $post)
	{	
		if( $form->content_type->value == Post::type('entry') ) {
			// add password feild to settings splitter
			$settings = $form->settings;
			$settings->append('text', 'postpass', 'null:null', _t('Password', 'postpass'), 'tabcontrol_text');
			$settings->postpass->value = $post->info->password;
		}
	}
}
?>