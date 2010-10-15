<?php

class DraftDashModule extends Plugin
{
	private $theme;

	/**
	 * action_plugin_activation
	 * Registers the core modules with the Modules class. Add these modules to the
	 * dashboard if the dashboard is currently empty.
	 * @param string $file plugin file
	 */
	function action_plugin_activation( $file )
	{
		if( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			Modules::add( 'Latest Drafts' );
		}
	}

	/**
	 * action_plugin_deactivation
	 * Unregisters the core modules.
	 * @param string $file plugin file
	 */
	function action_plugin_deactivation( $file )
	{
		if( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			Modules::remove_by_name( 'Latest Drafts' );
		}
	}

	/**
	 * filter_dash_modules
	 * Registers the core modules with the Modules class. 
	 */
	function filter_dash_modules( $modules )
	{
		// Should we check a token here? Lest people see drafts they can't edit?
		$modules[] = 'Latest Drafts';
		
		$this->add_template( 'dash_latestdrafts', dirname( __FILE__ ) . '/dash_latestdrafts.php' );

		return $modules;
	}

	/**
	 * action_update_check
	 * Register GUID for updates
	 */
	public function action_update_check()
	{
	 	Update::add( $this->info->name, $this->info->guid, $this->info->version );
	}
}
?>