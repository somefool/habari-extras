<?php 


class GeoTags extends Plugin {
	const VERSION= '0.2.1';

	private $config= array();

	public function info()
	{
		return array(
			'name' => 'Header GeoTags',
			'url' => 'http://mikelietz.org/code',
			'author' =>'Mike Lietz',
			'authorurl' => 'http://mikelietz.org/',
			'version' => self::VERSION,
			'description' => 'Adds Geocode data to headers.',
			'license' => 'Apache License 2.0',
		);
	}

	public function action_update_check()
	{
	 	Update::add( 'Geo Tags', '30840010-6e02-11dd-ad8b-0800200c9a66', $this->info->version );
	}

	function set_priorities()
	{
		return array(
			'theme_header' => 11,
		);
	}

	public function action_init()
	{
		$class_name= strtolower( get_class( $this ) );
		$this->config['lat']= Options::get( $class_name . '__lat' );
		$this->config['long']= Options::get( $class_name . '__long' );
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t( 'Configure' );
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure' ):
					$class_name= strtolower( get_class( $this ) );
					$ui= new FormUI( $class_name );

					$lat= $ui->append( 'text', 'lat', 'geotags__lat', _t( 'Latitude (required)' ) );
					$lat->add_validator( 'validate_required' );

					$long= $ui->append( 'text', 'long','geotags__long', _t( 'Longitude (required)' ) );
					$long->add_validator( 'validate_required' );
									
					$ui->append( 'submit', 'save', 'save' );
					$ui->out();
					break;
			}
		}
	}
	
	public function theme_header( $theme )
	{
		return $this->get_tags();
	}

	private function get_tags()
	{
		$out= '';

		$lat= $this->config['lat'];
		$long= $this->config['long'];		

		$coords= "$lat, $long";
		$out= "\t<meta name=\"DC.title\" content=\"" . Options::get( 'title' ) . "\">\n";
		$out.= "\t<meta name=\"ICBM\" content=\"$coords\">\n";
		$out.= "\t<meta name=\"geo.position\" content=\"$coords\">\n";
		
		return $out;		
	}

}
?>
