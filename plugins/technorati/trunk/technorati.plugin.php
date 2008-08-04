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
			'version' => '0.5',
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
			Modules::add( 'Technorati' );
			Session::notice( _t( 'Please set your Technorati API Key in the configuration.' ) );
			Options::set( 'technorati__apikey', '' );
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Modules::remove_by_name( 'Technorati' );
		}
	}

	public function filter_dash_modules( $modules )
	{
		$modules[]= 'Technorati';
		$this->add_template( 'dash_technorati', dirname( __FILE__ ) . '/dash_technorati.php' );
		return $modules;
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

	public function filter_dash_module_technorati( $module, $module_id, $theme )
	{
		$theme->technorati_stats= $this->theme_technorati_stats();

		$module['content']= $theme->fetch( 'dash_technorati' );
		return $module;
	}

	public function theme_technorati_stats()
	{
		if ( Cache::has( array( Site::get_url( 'habari' ), 'technorati_stats' ) ) ) {
			$stats = Cache::get( array( Site::get_url( 'habari' ), 'technorati_stats' ) );
		}
		else {
			$stats = $this->get_technorati_stats();
			if ( count( $stats ) ) {
				Cache::set( array( Site::get_url( 'habari' ), 'technorati_stats' ), $stats );
			}
			else {
				$stats['Technorati is not available at this time'] = '';
			}
		}
		return $stats;
	}

	public function get_technorati_stats()
	{
		$technorati_stats= array();
		$technorati_url= 'http://api.technorati.com/bloginfo?key=' . Options::get( 'technorati__apikey' ) . '&url='. Site::get_url('habari');

		$response= RemoteRequest::get_contents( $technorati_url );
		if( $response !== FALSE ) {
			$xml= new SimpleXMLElement( $response );
			if( isset( $xml->document->result->weblog ) ) {
				$technorati_inbound_blogs = (int)$xml->document->result->weblog[0]->inboundblogs;
				$technorati_inbound_links = (int)$xml->document->result->weblog[0]->inboundlinks;
				$technorati_rank = (int)$xml->document->result->weblog[0]->rank;

				$technorati_stats['Rank'] = $technorati_rank;
				$technorati_stats['Inbound Links'] = $technorati_inbound_links;
				$technorati_stats['Inbound Blogs'] = $technorati_inbound_blogs;
			}
		}
		return $technorati_stats;
	}

  /**
   * Add update beacon support
   **/
  public function action_update_check()
  {
		Update::add( 'Technorati', '24205fa0-38f4-11dd-ae16-0800200c9a66', $this->info->version );
  }

}
?>
