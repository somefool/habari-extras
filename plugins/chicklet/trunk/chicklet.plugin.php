<?php
class Chicklet extends Plugin
{

	public function action_init() {
		// Handle backwards compatability
		if(!is_array(Options::get('chicklet__feedname'))) {
			Options::set('chicklet__feedname', array(Options::get('chicklet__feedname')));
		}
	}
	
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
					$customvalue = $ui->append( 'textmulti', 'feedname', 'chicklet__feedname', _t('Feed Addresses:') );
					$customvalue = $ui->append( 'submit', 'submit', _t('Save') );
					$ui->out();
					break;
			}
		}
	}
	
	function action_add_template_vars( $theme )
	{
		$count = $this->fetch();
		$theme->subscribers = $count;
	}
	
	static public function fetch() {
		if(Cache::get('chickler_subscribercount') == NULL) {
			$count= 0;
			
			foreach(Options::get('chicklet__feedname') as $feed) {
				$url = "https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=" . $feed ;
				$remote = RemoteRequest::get_contents($url);

				@$xml = simplexml_load_string($remote);
				
				if($xml == false) {
					return 0;
				} else {
					$count = $count + intval($xml->feed->entry['circulation']);
				}
			}
						
			Cache::set('chickler_subscribercount', $count);
		} else {
			$count = Cache::get('chickler_subscribercount');
		}
		
		return $count;
	}
}
?>
