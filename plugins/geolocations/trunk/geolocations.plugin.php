<?php

/**
 * Geo Location plugin for Habari
 *
 * Adds geo location information to posts and provides a simple interface for finding 
 * location information.
 *
 * To add support into your theme, use something along the lines of this:
 *
 *    <?php if ( $post->info->geolocation_enabled ) { ?>
 *        <p>
 *           <a href="http://maps.google.com/maps?q=<?php echo $post->info->geolocation_coords; ?>&t=h">
 *           <?php echo $post->info->geolocation_coords; ?>
 *           </a>
 *        </p>
 *    <?php } ?>
 *
 * @author Ryan Mullins
 **/

class GeoLocations extends Plugin
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
	 	Update::add( 'GeoLocation', '0bab4cb5-f236-40d3-bed1-f4ea56c00ede', $this->info->version );
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
	}
	
	/**
	 * Respond to the user selecting configure on the plugin page
	 **/
	public function configure()
	{
		$ui = new FormUI( $this->class_name );
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
		return $ui;
	}	
	
	/**
	 * Create our form for the publish page
	 **/
	public function action_form_publish( $form, $post )
	{
		$form->publish_controls->append( 'fieldset', 'geoloc_controls', _t('Geo Location') );
		
		$coords = $form->geoloc_controls->append( 'wrapper', 'coords_form' );
		$coords->class = 'container';
		$coords->append( 'checkbox', 'geolocation_enabled', 'null:null', 'Use GeoLocation?' );
		$coords->geolocation_enabled->template = 'tabcontrol_checkbox';
		$coords->geolocation_enabled->value = $post->info->geolocation_enabled;
		$coords->append( 'text', 'geolocation_coords', 'null:null', 'Coordinates:' );
		$coords->geolocation_coords->template = 'tabcontrol_text';
		$coords->geolocation_coords->value = $post->info->geolocation_coords;
		
		$html = '<p class="pct80"><input type="text" style="width: 100%" class="geoDefaultText" name="geo_address" title="Enter an address, hit the search button, and then drag the marker to tweak the location." /></p><p class="pct20"><input type="button" id="geo_search_button" class="button" value="Search Location" /></p>';
		
		$geo_search = $form->geoloc_controls->append( 'wrapper', 'geotags_form' );
		$geo_search->class = 'container formcontrol';
		$geo_search->append('static', 'geo_form', $html );
		
		$geo_map = $form->geoloc_controls->append( 'wrapper', 'geotags_map' );
		$geo_map->class = 'container formcontrol';
		$geo_map->append( 'static', 'geo_map_canvas', '<p class="pct100"><div id="geolocation_map_canvas"></div></p>' );
	}

	/**
	 * Now we need to save our custom entries
	 */
	public function action_publish_post( $post, $form )
	{
		$post->info->geolocation_enabled = $form->geolocation_enabled->value;
		// Only save coords if we are using them, otherwise clear them out
		if ( $form->geolocation_enabled->value ) {			
			$post->info->geolocation_coords   = $form->geolocation_coords->value;
		} else {
			$post->info->geolocation_coords = '';
		}
	}
	
	/**
	 * Add additional components to the admin header output.
	 **/
	public function action_admin_header($theme)
	{
		/**
		 * Google maps requires some dimensional computation for it's initialization.  It won't work inside a 'display: none' panel as
		 * those elements will report themselves as 0.
		 *
		 * As an easy work around, we use the off-left technique for hiding the inactive tab panels. 
		 **/
		if ( $theme->page == 'publish' ) {
			Stack::add('admin_stylesheet', array( '.ui-tabs-hide { position: absolute; left: -10000px; display:block }', 'screen') );
		}
		
		// @todo: move this to external css?
		if ( $theme->page == 'publish' ) {
			Stack::add('admin_stylesheet', array( '#geolocation_map_canvas { width:750px; height:300px; }', 'screen') );
			Stack::add('admin_stylesheet', array( '.geoDefaultText { width: 300px; } .geoDefaultTextActive { color: #a1a1a1; font-style: italic; }', 'screen' ) );
		}
		
		if ( $theme->page == 'publish' ) {
			// Load Google Maps API after jquery
			Stack::add('admin_header_javascript', 'http://maps.google.com/maps/api/js?sensor=false', 'googlemaps_api_v3', 'jquery' );
			
			// Now create our page ready javascript
			$coords = preg_split( '/,/', $this->config['coords'] );
			$js_defaults = sprintf( "$(function(){ defaults = { lat: %s, long: %s, zoom: %s, jumptoZoom: %s, mapTypeControlOptions: %s, mapTypeId: %s, navigationControl: %s, navigationControlOptions: %s }; });",
								$coords[0],
								$coords[1],
								$this->config['zoom'],
								$this->config['jumptoZoom'],
								'{ style: google.maps.MapTypeControlStyle.' . $this->config['mapControlType'] . ' }',
								'google.maps.MapTypeId.' . $this->config['mapTypeId'],
								( $this->config['mapNavControl'] ) ? 'true' : 'false',
								'{ style: google.maps.NavigationControlStyle.' . $this->config['mapNavControlStyle'] . '}'
								);
			// Load defaults after google api					
			Stack::add('admin_header_javascript', $js_defaults, 'geolocation_defaults', 'googlemaps_api_v3' );
			
			// Load our other javascript after the defaults
			Stack::add('admin_header_javascript', $this->get_url(true) . 'geolocations.js', 'geolocations', 'geolocation_defaults' );
			/*
			You can also load after a list by passing:
			Stack::add( 'admin_header_javascript', $myscript, 'myscript_name', array( 'required1', required2 ) );
			*/
		}
	}

}
?>