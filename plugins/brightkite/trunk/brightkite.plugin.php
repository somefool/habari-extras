<?php

/**
 * Brightkite Location plugin for Habari 0.6+
 * Shows the current checked in location with google map.
 * 
 * Usage: <?php $theme->bk_location(); ?>
 *
 **/

class Brightkite extends Plugin
{
	private $config = array();
	private $class_name = '';
	
	private static function default_options()
	{
		return array (
			'user_id' => '',
			'google_api_key' => '',
			'map_image_size' => '200x200',
			'cache_expiry' => 1800
			);
	}
	
	
	/**
	 * New configuration routine
	 **/
	public function configure() {
		$ui = new FormUI( $this->class_name );
		
		$user_id = $ui->append( 'text', 'user_id', 'option:' . $this->class_name . '__user_id', _t( 'Brightkite Username', $this->class_name ) );
		$user_id->add_validator( 'validate_bk_username' );
		$user_id->add_validator( 'validate_required' );
		
		$google_api_key = $ui->append( 'text', 'google_api_key', 'option:' . $this->class_name . '__google_api_key', _t( 'Google Maps API Key', $this->class_name ) );
		
		$map_image_size = $ui->append( 'text', 'map_image_size', 'option:' . $this->class_name . '__map_image_size', _t( 'Size of map image', $this->class_name ) );
		$map_image_size->add_validator( 'validate_regex', '/\d+x\d+/' );
		$map_image_size->add_validator( 'validate_required' );
		
		$cache_expiry = $ui->append( 'text', 'cache_expiry', 'option:' . $this->class_name . '__cache_expiry', _t( 'Cache Expiry (in seconds)', $this->class_name ) );
		$cache_expiry->add_validator( 'validate_uint' );
		$cache_expiry->add_validator( 'validate_required' );
		
		// When the form is successfully completed, call $this->updated_config()
		$ui->append( 'submit', 'save', _t( 'Save', $this->class_name ) );
		$ui->set_option( 'success_message', _t( 'Options saved', $this->class_name ) );
		
		return $ui;
	}
	
	public function validate_bk_username( $username )
	{
		if ( ! ctype_alnum( $username ) ) {
			return array( _t( 'Your Brightkite username is not valid.', $this->class_name ) );
		}
		return array();
	}
	
	public function validate_uint( $value )
	{
		if ( ! ctype_digit( $value ) || strstr( $value, '.' ) || $value < 0 ) {
			return array( _t( 'This field must be a positive integer.', $this->class_name ) );
		}
		return array();
	}
	
	public function plugin_configured( $params = array() )
	{
		if ( empty( $params['user_id'] ) || empty( $params['cache_expiry'] ) || empty( $params['map_image_size'] ) ) {
			return false;
		}
		return true;
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
		
		$this->add_template( 'block.brightkite', dirname( __FILE__ ) . '/block.brightkite.php' );
	}
	
	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Brightkite', '263F0954-4B2B-11DE-B433-958C55D89593', $this->info->version );
	}
	
	/**
	 * Get the brightkite feed for our user_id
	 **/
	private function load_bk_info ( $params = array() )
	{
		$cache_name = $this->class_name . '__' . md5( serialize( $params ) );
		
		if ( Cache::has( $cache_name ) ) {
			// Read from the cache
			return Cache::get( $cache_name );
		}
		else {
			// Time to fetch it.
			$url = 'http://brightkite.com/people/' . $params['user_id'] . '.json';
			
			try {
				$call = new RemoteRequest( $url );
				$call->set_timeout( 5 );
				$result = $call->execute();
				
				if ( Error::is_error( $result ) ) {
					throw Error::raise( _t( 'Unable to contact Brightkite.', $this->class_name ) );
				}
				
				$response = $call->get_response_body();
				
				// Decode the JSON
				$bkdata = json_decode( $response, true );
				if ( ! is_array( $bkdata ) ) {
					// Response is not JSON
					throw Error::raise( _t( 'Response is not correct, maybe Brightkite server is down or API changed.', $this->class_name ) );
				}
				
				// Do cache
				Cache::set( $cache_name, $bkdata, $params['cache_expiry'] );
				
				return $bkdata;
			}
			catch ( Exception $e ) {
				return $e->getMessage();
			}
		}
	}
		
	/**
	 * Add Brightkite block to the list of selectable blocks
	 **/ 
	public function filter_block_list( $block_list )
	{
		$block_list[ 'brightkite' ] = _t( 'Brightkite', 'brightkite' );
		return $block_list;
	}
	
	/**
	 * Configuration block on the theme admin screen.
	 **/
	public function action_block_form_brightkite( $form, $block )
	{

		$user_id = $form->append( 'text', 'user_id', 'option:' . $this->class_name . '__user_id', _t( 'Brightkite Username', $this->class_name ) );
		$user_id->add_validator( 'validate_bk_username' );
		$user_id->add_validator( 'validate_required' );
		
		// Should we allow this here? Or only in the main form?!
		$google_api_key = $form->append( 'text', 'google_api_key', 'option:' . $this->class_name . '__google_api_key', _t( 'Google Maps API Key', $this->class_name ) );
		
		$map_image_size = $form->append( 'text', 'map_image_size', 'option:' . $this->class_name . '__map_image_size', _t( 'Size of map image', $this->class_name ) );
		$map_image_size->add_validator( 'validate_regex', '/\d+x\d+/' );
		$map_image_size->add_validator( 'validate_required' );
		
		$cache_expiry = $form->append( 'text', 'cache_expiry', 'option:' . $this->class_name . '__cache_expiry', _t( 'Cache Expiry (in seconds)', $this->class_name ) );
		$cache_expiry->add_validator( 'validate_uint' );
		$cache_expiry->add_validator( 'validate_required' );
		
		$form->append( 'submit', 'save', _t( 'Save', $this->class_name ) );
		
	}
	
	/**
	 * Populate the block
	 **/
	public function action_block_content_brightkite( $block, $theme )
	{
		if ( $this->plugin_configured( $this->config ) ) {
			$block->bkinfo = $this->load_bk_info( $this->config );
			$block->gmapkey = $this->config['google_api_key'];
			$block->mapsize = $this->config['map_image_size'];
		}
		else {
			$block->bkinfo = _t( 'Brightkite Plugin is not configured properly.', $this->class_name );
		}
	}
	
	
}
?>