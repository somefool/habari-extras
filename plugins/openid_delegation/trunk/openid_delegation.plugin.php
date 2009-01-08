<?php 


class OpenID_Delegation extends Plugin {
	const VERSION= '0.5-0.1';

	private $config= array();

	public function info()
	{
		return array(
			'name' => 'OpenID Delegation',
			'url' => 'http://mikelietz.org/code',
			'author' =>'Mike Lietz',
			'authorurl' => 'http://mikelietz.org/',
			'version' => self::VERSION,
			'description' => 'Enables site address to be used as a OpenID identifier, when using a third-party OpenID provider.',
			'license' => 'Apache License 2.0',
		);
	}

	public function action_update_check()
	{
	 	Update::add( 'OpenID Delegation', 'DC735D26-9021-11DD-B91C-207A56D89593', $this->info->version );
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
		$this->config['provider'] = Options::get( $class_name . '__provider' );
		$this->config['identity'] = Options::get( $class_name . '__identity' );
		$this->config['is2'] = Options::get( $class_name . '__is2' );
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'Configure' );
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure' ):
					$class_name = strtolower( get_class( $this ) );
					$ui = new FormUI( $class_name );

					$provider = $ui->append( 
	'text', 'provider', $class_name . '__provider', _t( 'Address of your identity server (required)' ) );
					$provider->add_validator( 'validate_required' );
					$provider->add_validator( 'validate_url' );

					$identity = $ui->append( 
	'text', 'identity', $class_name . '__identity', _t( 'Your OpenID identifier with that identity provider (required)' ) );
					$identity->add_validator( 'validate_required' );
					$identity->add_validator( 'validate_url' );

					$is2 = $ui->append(
	'checkbox','is2', $class_name . '__is2', _t( 'Add links for OpenID 2.0 (must be supported by your provider)' ) );
					
					$ui->append( 'submit', 'save', 'save' );
					$ui->out();
					break;
			}
		}
	}
	
	public function theme_header( $theme )
	{
		if( isset($theme) && $theme->request->display_home ) {
			return $this->add_links();
		}
	}

	private function add_links()
	{
		$out = '';

		$provider = $this->config['provider'];
		$identity = $this->config['identity'];		
		$is2 = $this->config['is2'];		
		
		if ( isset( $provider) and isset( $identity ) ) {
			$out = "\t<link rel=\"openid.server\" href=\"" . $provider . "\">\n";
			$out .= "\t<link rel=\"openid.delegate\" href=\"" . $identity . "\">\n";
	
			if ($is2) { 
				$out .= "\t<link rel=\"openid2.provider\" href=\"" . $provider . "\">\n";
				$out .= "\t<link rel=\"openid2.local_id\" href=\"" . $identity . "\">\n";
			
			}
		
			return $out;		
		}
	}

}
?>
