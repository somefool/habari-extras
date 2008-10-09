<?php
/*
 * Woopra Plugin for Habari
 * 
 * Lets you add your Woopra tracking code to your theme footer.
 * your theme must include the $theme->footer call before the </body> tag in its footer.php
 * Refer to the readme.txt included with the plugin for the installation/configuration.
 * 
 * Author: Ali B. (http://www.awhitebox.com)
 * Licensed under the terms of Apache Software License 2.0
 */

class Woopra extends Plugin {
	
	/**
	 * Provides Plugin information.
	 **/	
	public function info()
	{
		return array(
			'name'=>'Woopra',
			'version'=>'0.4',
			'url'=>'http://www.awhitebox.com/woopra-plugin-for-habari',
			'author'=>'Ali B.',
			'authorurl'=>'http://www.awhitebox.com',
			'license'=>'Apache License 2.0',
			'description'=>'Add Woopra\'s tracking code to your site.'
		);
	}
	
	/**
	 * Implement the update notification feature
	 */
  	public function action_update_check()
  	{
    	Update::add( 'woopra', '9eee624f-8783-42d4-a87e-65d86baa2c1e',  $this->info->version );
  	}
  	
	public function action_plugin_activation($file)
	{
		if (Plugins::id_from_file($file) != Plugins::id_from_file(__FILE__)) return;
		
		Options::set('woopra__site_id', '');
		Options::set('woopra__tag_registered', false);
		Options::set('woopra__display_avatar', 'no');

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
			$actions[]= _t('Configure');		
		}
		return $actions;
	}
	
	/**
	 * Creates a UI form to handle the plguin configurations
	 *
	 * @param string $plugin_id The id of a plugin
	 * @param array $actions An array of actions that apply to this plugin
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id() == $plugin_id && $action == _t('Configure')){
			$form= new FormUI( strtolower(get_class( $this ) ) );
			$site_id= $form->append( 'text', 'site_id', 'option:woopra__site_id', _t('Woopra Site ID', 'woopra'));
			$label1= $form->append( 'label','label1', _t( 'You can find your site ID in ', 'woopra') . '<a href="http://www.woopra.com/members/">' . _t( 'Woopra member area', 'woopra' ) . '</a>' );
			$site_id->add_validator( 'validate_required' );
			$form->append( 'fieldset','fieldset1', _t( 'Tag Registered Users', 'woopra' ));
			$tag_registered= $form->fieldset1->append( 'checkbox', 'tag_registered', 'option:woopra__tag_registered', _t( 'Enabled', 'woopra' ));
			$label2= $form->fieldset1->append( 'label','label2', _t( 'Display users avatars', 'woopra') );
			$display_avatar= $form->fieldset1->append( 'radio', 'display_avatar', 'option:woopra__display_avatar',  _t( 'Display users avatars', 'woopra'), array( 'no'=>'Disabled', 'userimage'=>'Local user image', 'gravatar'=>'Gravatar' ));
			$form->append( 'fieldset','fieldset2', _t( 'Execlude Users', 'woopra' ) );
			$excluded_users= $form->fieldset2->append( 'textmulti', 'excluded_users', 'option:woopra__excluded_users', _t( 'Don\'t track visits from the following user names', 'woopra'));
			$form->append('submit', 'save', _t('Save'));
			$form->on_success( array( $this, 'save_config' ) );
			$form->out();
		}
	}
	
	/**
	 * Invoked when the before the plugin configurations are saved
	 *
	 * @param FormUI $form The configuration form being saved
	 * @return true
	 */
	public function save_config( $form )
	{   
		$form->save();
		Session::notice('Woopra plugin configuration saved!');
		return false;
	}
	
	public function theme_footer ($theme)
	{
		return $this->build_tracker_code();
	}
	
	private function build_tracker_code()
	{
		//Load plugin options
		$class= strtolower( get_class( $this ) );
		$site_id= Options::get( $class . '__site_id' );
		$tag_registered= Options::get( $class . '__tag_registered' );
		$display_avatar= Options::get( $class . '__display_avatar' );
		$excluded_users= Options::get( $class . '__excluded_users' );
		
		$code='';
		if ( $site_id != '' ) {
			$current_user= User::identify();
			$current_user_name= is_object($current_user) ? $current_user->username : '';
			if ( !( is_object($current_user) && in_array( $current_user_name, $excluded_users) ) ){
				$code= "<script type=\"text/javascript\">\nvar woopra_id = '" . $site_id . "';\n";
				if ( is_object($current_user) && $tag_registered ){
					$code.= "var woopra_visitor = new Array();\n";
					$code.= "woopra_visitor['name'] =\"" . $current_user->displayname . "\";\n";
					Switch ( $display_avatar ){
						case "userimage":
							$code.= "woopra_visitor['avatar'] = \"" . $current_user->info->imageurl . "\";\n";
							break;
						case "gravatar":
							$code.= "woopra_visitor['avatar'] = \"http://www.gravatar.com/avatar.php?gravatar_id=" . md5( strtolower( $current_user->email ) ) . "&size=60&default=http%3A%2F%2Fstatic.woopra.com%2Fimages%2Favatar.png\";\n";
							break;
					}
				}
				$code.= "</script>\n<script src=\"http://static.woopra.com/js/woopra.js\"></script>\n";	
			}
		}
		return $code;
	}
}
?>