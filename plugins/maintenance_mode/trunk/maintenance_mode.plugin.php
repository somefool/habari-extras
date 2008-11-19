<?php

/**
 * Maintenance Mode plugin
 *
 **/

class Maintenance extends Plugin
{

	const OPTION_NAME = 'maint_mode';

	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Maintenance Mode',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '0.2',
			'description' => 'Redirects all requests to a maintenance mode page, with two exceptions. 
			The first is that the login page is available. 
			The second is that any user who is logged in can see any page on the site.',
			'license' => 'Apache License 2.0',
		);
	}

	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Options::set( self::OPTION_NAME . '__text' , _t( "We're down for maintenance. Please return later." ) );
			Options::set( self::OPTION_NAME . '__in_maintenance', FALSE );
		}
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure' );
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure' ) :
					$ui = new FormUI( 'maintenance_mode' );
					// Add a text control for the maintenance mode text
					$ui->append( 'textarea', 'mm_text', self::OPTION_NAME . '__text', _t('Display Text: ' ) );
					// Add checkbox to put in/out of maintenance mode
					$ui->append( 'checkbox', 'in_maintenance', self::OPTION_NAME . '__in_maintenance', _t( 'In Maintenance Mode' ) );

					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
					break;
			}
		}
	}

	public function updated_config( $ui )
	{
		$msg = _t( "Maintenance Mode configuration updated" );
		$msg .= "<br/>";
		if($ui->in_maintenance->value === FALSE ) {
			$msg .= _t( "The site is not in maintenance mode" );
		}
		else {
			$msg .= _t( "The site is in maintenance mode" );
		}
		Session::notice( $msg );
		$ui->save();
	}


	public function filter_rewrite_request( $start_url )
	{
		if( Options::get( self::OPTION_NAME . '__in_maintenance' ) ) {
			if( ! User::identify() ) {
					if ( strpos( $start_url, 'user/login' ) === FALSE  && strpos( $start_url, 'admin' ) === FALSE  ) {
						$start_url = 'maintenance';
					}
			}
		}
		return $start_url;
	}

	public function filter_rewrite_rules( $rules )
	{
		$rules[] = new RewriteRule ( array(
			'name' => 'maintenance',
			'parse_regex' => '%^maintenance%i',
			'build_str' => 'maintenance',
			'handler' => 'UserThemeHandler',
			'action' => 'display_maintenance',
			'priority' => 4,
			'rule_class' => RewriteRule::RULE_PLUGIN,
			'is_active' => ( User::identify() ? 0 : 1 ),
			'description' => 'Displays the maintenance mode page'
		) );

		return $rules;
	}

	public function filter_theme_act_display_maintenance( $handled, $theme )
	{

		header("HTTP/1.1 503 Service Unavailable");
		header('Retry-After: 900');

		if ($theme->template_exists('maintenance')) {
			$theme->maintenance_text = Options::get( self::OPTION_NAME . '__text' );
			$theme->display( 'maintenance' );
		}
		else {
			$theme->display('header');
			echo '<h2 id="maintenance_text">' . htmlspecialchars( Options::get( self::OPTION_NAME . '__text' ), ENT_COMPAT, 'UTF-8' ) . '</h2>';
			$theme->display('footer');
			die();
		}
		return TRUE;
	}

	public function action_update_check()
	{
		Update::add( 'Maintenance Mode', '1F66810A-6CD5-11DD-BC10-8E2156D89593', $this->info->version );
	}

}
?>
