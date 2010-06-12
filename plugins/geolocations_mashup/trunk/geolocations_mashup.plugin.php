<?php

/**
 * Geo Location Mashup plugin for Habari
 *
 * Adds a Google Maps mashup consisting of geolocation enabled content.
 *
 * This is a work in progress, so expect this to be broken a lot.
 * @todo add in default map view options
 * @todo change javascript generation to support all of the configuration options
 * @todo filters on content_type to display
 * @todo custom markers based on content_type
 *
 * @author Ryan Mullins
 **/

class GeoLocations_Mashup extends Plugin
{
	private $config = array();
	private $class_name;
	
	/**
	 * default_options()
	 *
	 * Set the default options for the plugin
	 **/
	private static function default_options()
	{
		return array (
			);
	}
	
	/**
	 * Add update beacon support.
	 **/
	public function action_update_check()
	{
	 	Update::add( 'GeoLocation Mashup', 'e868d76c-aced-4620-ac7f-bb7b24dbd952', $this->info->version );
	}
	
	/**
	 * On plugin activation, set the default options
	 **/
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) === __FILE__ ) {
			$this->class_name = strtolower( get_class( $this ) );
			foreach ( self::default_options() as $name => $value ) {
				$current_value = Options::get( $this->class_name . '__' . $name );
				if ( is_null( $current_value ) ) {
					Options::set( $this->class_name . '__' . $name, $value );
				}
			}
		}
	}
	
	/**
	 * On plugin init
	 **/
	public function action_init()
	{
		$this->class_name = strtolower( get_class( $this ) );
		
		$this->add_template( 'page.map', dirname(__FILE__) . '/page.map.php' );
		$this->add_rule( '"map"', 'geolocation_mashup' );
	}
	
	/**
	 * On action geolocation_mashup
	 **/
	public function action_plugin_act_geolocation_mashup( $handler )
	{
		// Load Google Maps API
		Stack::add('template_header_javascript', 'http://maps.google.com/maps/api/js?sensor=false', 'googlemaps_api_v3' );
		// Load up jquery
		Stack::add('template_header_javascript', 'http://localhost/habari/scripts/jquery.js', 'jquery' );
		
		
		// Makes sure home displays only entries - for now...
		$default_filters = array(
			'content_type' => Post::type( 'entry' ),
		);

		
		$posts = Posts::get( $default_filters );
		$handler->theme->posts = $posts;
		
		$handler->theme->display( 'page.map' );
	}
	
	
}
?>