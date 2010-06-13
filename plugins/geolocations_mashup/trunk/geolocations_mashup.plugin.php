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
	
	private $arrMapTypeId = array(
		'ROADMAP'   => 'Road Map',
		'HYBRID'    => 'Hybrid Map',
		'SATELLITE' => 'Satellite Map',
		'TERRAIN'   => 'Terrain Map'
		);
	
	private $arrMapControlType = array(
		'DROPDOWN_MENU'  => 'Dropdown Menu',
		'HORIZONTAL_BAR' => 'Horizontal Bar',
		'DEFAULT'        => 'Default Style'
		);
		
	private $arrMapNavControlStyle = array(
		'SMALL'    => 'Small, Zoom Only',
		'ANDROID'  => 'Android look alike',
		'DEFAULT'  => 'Default style',
		'ZOOM_PAN' => 'Zoom and Pan'
		);
	
	/**
	 * default_options()
	 *
	 * Set the default options for the plugin
	 **/
	private static function default_options()
	{
		return array (
			'mapurl'            => 'map',
			'coords'            => '43.05183,-87.913971',
			'zoom'              => '10',
			'jumptoZoom'        => '15',
			'mapTypeId'         => 'ROADMAP',
			'mapControlType'    => 'DROPDOWN_MENU',
			'mapNavControl'     => true,
			'mapNavControlStyle' => 'SMALL'
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
		
		foreach ( self::default_options() as $name => $value ) {
			$this->config[$name] = Options::get( $this->class_name . '__' . $name );
		}
		
		$this->add_template( 'page.map', dirname(__FILE__) . '/page.map.php' );
		$this->add_rule( '"' . $this->config['mapurl'] . '"', 'geolocation_mashup' );
	}
	
	/**
	 * Add actions to the plugin page
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id === $this->plugin_id() ) {
			$actions[] = _t( 'Configure', $this->class_name );
		}
		
		return $actions;
	}
	
	/**
	 * Respond to the user selecting an action on the plugin page
	 **/
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id === $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure', $this->class_name ):
					// @todo add some validators for these
					$ui = new FormUI( $this->class_name );
					$ui->append( 'text', 'mapurl', 'option:' . $this->class_name . '__mapurl', _t( 'Map URL' ) );
					$ui->mapurl->add_validator('validate_required');
					$ui->append( 'text', 'coords', 'option:' . $this->class_name . '__coords', _t( 'Default Coordinates', $this->class_name ) );
					$ui->append( 'text', 'zoom', 'option:' . $this->class_name . '__zoom', _t( 'Default Zoom', $this->class_name ) );
					$ui->append( 'text', 'jumptoZoom', 'option:' . $this->class_name . '__jumptoZoom', _t( 'Jump to Zoom', $this->class_name ) );
					$ui->append( 'select', 'mapTypeId', 'option:' . $this->class_name . '__mapTypeId', _t( 'Map Type' ), 'optionscontrol_select' );
					$ui->mapTypeId->options = $this->arrMapTypeId;
					$ui->append( 'select', 'mapControlType', 'option:' . $this->class_name . '__mapControlType', _t( 'Map Control Type' ) );
					$ui->mapControlType->options = $this->arrMapControlType;
					$ui->append( 'checkbox', 'mapNavControl', 'option:' . $this->class_name . '__mapNavControl', _t( 'Show Navigation Controls?' ) );
					$ui->append( 'select', 'mapNavControlStyle', 'option:' . $this->class_name . '__mapNavControlStyle', _t( 'Navigation Control Style' ) );
					$ui->mapNavControlStyle->options = $this->arrMapNavControlStyle;
					$ui->append( 'submit', 'save', _t( 'Save', $this->class_name ) );
					$ui->set_option( 'success_message', _t( 'Options saved', $this->class_name ) );
					
					$ui->out();
					break;
			}
		}
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
		
		// Now create our page ready javascript
		$coords = preg_split( '/,/', $this->config['coords'] );
		$js_defaults = sprintf( "defaults = { lat: %s, long: %s, center: %s, zoom: %s, jumptoZoom: %s, mapTypeControlOptions: %s, mapTypeId: %s, navigationControl: %s, navigationControlOptions: %s };",
							$coords[0],
							$coords[1],
							'new google.maps.LatLng( ' . $coords[0] . ', ' . $coords[1] . ' )',
							$this->config['zoom'],
							$this->config['jumptoZoom'],
							'{ style: google.maps.MapTypeControlStyle.' . $this->config['mapControlType'] . ' }',
							'google.maps.MapTypeId.' . $this->config['mapTypeId'],
							( $this->config['mapNavControl'] ) ? 'true' : 'false',
							'{ style: google.maps.NavigationControlStyle.' . $this->config['mapNavControlStyle'] . '}'
							);
		// Load defaults after google api					
		//Stack::add('template_header_javascript', $js_defaults, 'geolocation_defaults', array( 'jquery', 'googlemaps_api_v3' ) );
		
		// Makes sure home displays only entries - for now...
		$default_filters = array(
			'content_type' => Post::type( 'entry' ),
		);

		
		$posts = Posts::get( $default_filters );
		$handler->theme->posts = $posts;
		
		$handler->theme->js_defaults = $js_defaults;
		$handler->theme->display( 'page.map' );
	}
	
	
}
?>