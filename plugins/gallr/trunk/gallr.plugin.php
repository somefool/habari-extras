<?php

/**
 * Flickr Gallr - Display Flickr photos in a gallery on your site
 *
 */ 

class Gallr extends Plugin
{ 
	// Version info
	const VERSION = '0.1';
	
	// API key
	const KEY = '22595035de2c10ab4903b2c2633a2ba4';
	
	/**
	 * Required plugin info() implementation provides info to Habari about this plugin.
	 */ 
	public function info()
	{
		return array (
			'name' => 'Flickr Gallr',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => self::VERSION,
			'description' => 'Display Flickr photos in a gallery on your site.',
			'license' => 'ASL',
		);
	}

	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, '45D26E5A-64E5-11DD-92F3-D09255D89593', $this->info->version );
	}
	
	/**
	 * Plugin config
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
	  if ( $plugin_id == $this->plugin_id() ) {
	    $actions[]= _t('Configure');
	  }
	  return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					
					$ui->append( 'text', 'username', 'gallr__username', _t('User ID:') );
					$ui->append('submit', 'save', _t( 'Save' ) );
					$ui->set_option('success_message', _t('Options saved'));
					
					$ui->out();
					break;
			}
		}
	}
	
	public function grab($method, $vars = array()) {
		$url = 'http://api.flickr.com/services/rest/';
		
		$url.= '?method='.$method;
		
		foreach($vars as $key => $val) {
			$url.= '&'.$val.'='.$val;
		}
		
		Utils::debug($url);
	}

}	

?>
