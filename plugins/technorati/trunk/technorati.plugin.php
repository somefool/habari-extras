<?php
class Technorati extends Plugin
{
	/* 
	 * Technorati API key
	 * You MUST change this have your Technorati API key
	 * which can be found on http://technorati.com/developers/apikey.html
	 *
	 */

	/* Required Plugin Informations */
	public function info() {
		return array(
			'name' => 'Technorati',
			'version' => '0.4',
			'url' => 'http://habariproject.org/',
			'author' =>     'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Technorati plugin for Habari',
			'copyright' => '2007'
		);
	}

	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Modules::register( 'Technorati' );
			Modules::add( 'Technorati' );
			Session::notice( _t( 'Please set your Technorati API Key in the configuration.' ) );
			Options::set( 'technorati__apikey', '' );
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Modules::unregister( 'Technorati' );
		}
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
					$ui= new FormUI( strtolower( get_class( $this ) ) );
					$technorati_apikey= $ui->append( 'text', 'apikey', 'option:technorati__apikey', _t( 'Technorati API Key (Get it from ' ) . '<a href="http://www.technorati.com/developers/apikey.html">' . _t( 'Developer Center' ) . '</a>)' );
					$technorati_apikey->add_validator( 'validate_required' );
					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->set_option( 'success_message', _t( 'Configuration saved' ) );
					$ui->out();
					break;
			}
		}
	}

	public function filter_dash_module_technorati( $module_id )
	{
		$theme= Themes::create( 'technorai', 'RawPHPEngine', dirname( __FILE__ ) . '/' );

		$theme->stats= $this->theme_technorati_stats();

		return $theme->fetch( 'dash_technorati' );
	}

	public function theme_technorati_stats()
	{
		if ( Cache::has( 'technorati_stats' ) ) {
			$stats= Cache::get( 'technorati_stats' );
		}
		else {
			$stats= $this->get_technorati_stats();
			Cache::set( 'technorati_stats', $stats );
		}

		return $stats;
	}

	public function get_technorati_stats()
	{
		$technorati_stats= array();
		$technorati_url= 'http://api.technorati.com/bloginfo?key=' . Options::get( 'technorati__apikey' ) . '&url='. Site::get_url('habari');

		$response= RemoteRequest::get_contents( $technorati_url );
		$xml= new SimpleXMLElement( $response );

		$technorati_inbound_blogs= ( $xml->document->result->weblog->inboundblogs[0] );
		$technorati_inbound_links= ( $xml->document->result->weblog->inboundlinks[0] );
		$technorati_rank= ( $xml->document->result->weblog->rank[0] );

		$technorati_stats['Rank']= $technorati_rank;
		$technorati_stats['Inbound Links']= $technorati_inbound_links;
		$technorati_stats['Inbound Blogs']= $technorati_inbound_blogs;
		return $technorati_stats;
	}

}
?>
