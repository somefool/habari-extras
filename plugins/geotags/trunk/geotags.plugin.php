<?php 


class GeoTags extends Plugin {

	private $config= array();

	/**
	 * Set priority to move inserted tags nearer to the end
	 * @return array
	 **/
	public function set_priorities()
	{
		return array(
			'theme_header' => 11,
		);
	}

	/**
	 * Adds needed files to the theme stacks (javascript and stylesheet)
	 **/
	public function action_init()
	{
		$class_name= strtolower( get_class( $this ) );
		$this->config[ 'lat' ] = Options::get( $class_name . '__lat' );
		$this->config[ 'long' ] = Options::get( $class_name . '__long' );
	}
	
	public function configure()
	{
		$class_name = strtolower( get_class( $this ) );
		$ui = new FormUI( $class_name );
	
		$lat = $ui->append( 'text', 'lat', 'geotags__lat', _t( 'Latitude (required)' ) );
		$lat->add_validator( 'validate_required' );

		$long = $ui->append( 'text', 'long','geotags__long', _t( 'Longitude (required)' ) );
		$long->add_validator( 'validate_required' );
							
		$ui->append( 'submit', 'save', 'save' );
		return $ui;
	}
	
	/**
	 * Add tags to headers.
	 * @return array
	 **/
	public function theme_header( $theme )
	{
		return $this->get_tags();
	}

	/**
	 * Generate tags for adding to headers.
	 * @return string Tags to add to headers.
	 **/
	private function get_tags()
	{
		$out = '';

		$lat = $this->config['lat'];
		$long = $this->config['long'];		

		$coords = "$lat, $long";
		$out = "\t<meta name=\"DC.title\" content=\"" . Options::get( 'title' ) . "\">\n";
		$out .= "\t<meta name=\"ICBM\" content=\"$coords\">\n";
		$out .= "\t<meta name=\"geo.position\" content=\"$coords\">\n";
		
		return $out;		
	}
}
?>
