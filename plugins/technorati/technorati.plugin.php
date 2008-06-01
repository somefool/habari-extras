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
			'version' => '0.3',
			'url' => 'http://habariproject.org/',
			'author' =>     'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Technorati plugin for Habari',
			'copyright' => '2007'
		);
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
					$technorati_apikey= $ui->add( 'text', 'apikey', _t( 'Technorati API Key (Get it from ' ) . '<a href="http://www.technorati.com/developers/apikey.html">' . _t( 'Developer Center' ) . '</a>)' );
					$technorati_apikey->add_validator( 'validate_required' );
					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
				break;
			}
		}
	}

	public function updated_config( $ui )
	{
		Session::notice( _t( 'Configuration saved' ) );
		return true;
	}

	public function filter_admin_modules( $modules )
	{
		$modules['technorati']= '<div class="modulecore">
			<h2>Technorati Stats</h2><div class="handle">&nbsp;</div>' . "\n" .
			$this->theme_technorati_stats() .
			'</div>';
		return $modules;
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

		$stats_table= '<ul class="items">'. "\n";
		foreach ( $stats as $key => $count ) {
			$stats_table.= '<li class="item clear">' . "\n";
			$stats_table.= '<span class="pct90">' . "{$key}</span>\n";
			$stats_table.= '<span class="comments pct10">' . "{$count}</span>\n";
			$stats_table.= "</li>\n";
		}
		$stats_table.= "</ul>\n";

		return $stats_table;
	}

	public function get_technorati_stats()
	{
		$technorati_stats= array();
		$technorati_url= 'http://api.technorati.com/bloginfo?key=' . Options::get( 'technorati:apikey' ) . '&url='. Site::get_url('habari');

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
