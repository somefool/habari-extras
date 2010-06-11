<?php

/**
 * Maintenance Mode plugin
 *
 **/

class Maintenance extends Plugin
{

	const OPTION_NAME = 'maint_mode';

	/**
	 * Set options when the plugin is activated
	 */
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Options::set( self::OPTION_NAME . '__text' , _t( "We're down for maintenance. Please return later." ) );
			Options::set( self::OPTION_NAME . '__in_maintenance', FALSE );
			Options::set( self::OPTION_NAME . '__display_feeds', FALSE );
		}
	}

	/**
	 * Remove options when the plugin is deactivated
	 */
	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Options::delete( self::OPTION_NAME . '__text' , _t( "We're down for maintenance. Please return later." ) );
			Options::delete( self::OPTION_NAME . '__in_maintenance', FALSE );
			Options::delete( self::OPTION_NAME . '__display_feeds', FALSE );
		}
	}

	/**
	 * Implement the simple plugin configuration.
	 * @return FormUI The configuration form
	 */
	public function configure()
	{
		$ui = new FormUI( 'maintenance_mode' );
		// Add a text control for the maintenance mode text
		$ui->append( 'textarea', 'mm_text', self::OPTION_NAME . '__text', _t('Display Text: ' ) );
		// Add checkbox to put in/out of maintenance mode
		$ui->append( 'checkbox', 'in_maintenance', self::OPTION_NAME . '__in_maintenance', _t( 'In Maintenance Mode' ) );
		$ui->append( 'checkbox', 'display_feeds', self::OPTION_NAME . '__display_feeds', _t( 'Display Feeds When In Maintenance Mode' ) );

		$ui->append( 'submit', 'save', _t( 'Save' ) );
		$ui->on_success( array( $this, 'updated_config' ) );
		$ui->out();
	}

	/**
	 * Save updated configuration
	 */
	public function updated_config( $ui )
	{
		$msg = _t( "Maintenance Mode configuration updated" );
		$msg .= "<br/>";
		if ( $ui->in_maintenance->value === FALSE ) {
			$msg .= _t( "The site is not in maintenance mode" );
		}
		else {
			$msg .= _t( "The site is in maintenance mode" );
		}
		Session::notice( $msg );
		$ui->save();
	}


	/**
	 * Filter requests
	 */
	public function filter_rewrite_request( $start_url )
	{
		// Just return if the site isn't maintenance mode
		if ( ! Options::get( self::OPTION_NAME . '__in_maintenance' ) ) {
			return $start_url;
		}

		// Display feeds if that option is checked
		if ( Options::get( self::OPTION_NAME . '__display_feeds' ) ) {
			if ( strpos( $start_url, 'atom' ) !== FALSE || strpos( $start_url, 'rss' ) !== FALSE  || strpos( $start_url, 'rsd' ) !== FALSE ) {
				return $start_url;
			}
		}

		// Put the site in maintenance mode, unless it's a request to login pages or the admin
		if ( ! User::identify()->loggedin ) {
			if ( strpos( $start_url, 'user/login' ) === FALSE &&
					 strpos( $start_url, 'auth/login' ) === FALSE &&
					 strpos( $start_url, 'admin' ) === FALSE  ) {
				$start_url = 'maintenance';
			}
		}
		return $start_url;
	}

	/**
	 * Add a rule to respond to redirected requests when the site is in maintenance mode
	 */
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
			'is_active' => ( User::identify()->loggedin ? 0 : 1 ),
			'description' => 'Displays the maintenance mode page'
		) );

		return $rules;
	}

	/**
	 * Respond to redirected requests when the site is in maintenance mode
	 */
	public function filter_theme_act_display_maintenance( $handled, $theme )
	{

		header("HTTP/1.1 503 Service Unavailable");
		header('Retry-After: 900');

		if ( $theme->template_exists('maintenance') ) {
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

	/**
	 * Check for updates to the plugin
	 */
	public function action_update_check()
	{
		Update::add( 'Maintenance Mode', '1F66810A-6CD5-11DD-BC10-8E2156D89593', $this->info->version );
	}

	/**
	 * Add help text to plugin configuration page
	 */
	public function help()
	{
		$help = _t( 'When \'In Maintenance Mode\' is checked, a maintenance message is displayed to anonymous users. The template named \'maintenance.php\' will be used to display the maintenance text, and if that template isn\'t found the plugin will try to incorporate the maintenance text into the site by inserting it between the theme\'s header and footer templates. Feeds can be excluded from maintenance mode by checking \'Display Feeds When In Maintenance Mode\'.' );
		return $help;
	}

}
?>
