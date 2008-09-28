<?php
class GetClicky extends Plugin
{
	function info()
	{
    		return array(
      			'name' => 'GetClicky Analytics',
      			'version' => '1.3.1',
      			'url' => 'http://digitalspaghetti.me.uk/',
      			'author' => 'Tane Piper',
      			'authorurl' => 'http://digitalapghetti.me.uk',
      			'license' => 'MIT Licence',
      			'description' => 'Add\'s GetClicky analytics integration to your site',
    		);
	}
	
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Modules::add( 'GetClicky' );
			Options::set( 'getclicky__cachetime', '300' );
			Options::set( 'getclicky__loggedin', 1 );
		}
	}
	
	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Options::delete('getclicky__siteid');
			Options::delete('getclicky__sitekey');
			Options::delete('getclicky__sitedb');
			Options::delete('getclicky__loggedin');
			Options::delete('getclicky__cachetime');

			Cache::expire('site-rank');
			Cache::expire('visitors-online');
			Cache::expire('visitors-unique');
			Cache::expire('actions');
			Cache::expire('actions-average');
			Cache::expire('time-total-pretty');
			Cache::expire('time-average-pretty');

			Modules::remove_by_name( 'GetClicky' );
		}
	}

	function action_update_check() 
	{
		Update::add( 'GetClicky Analytics', '5F271634-89B7-11DD-BE47-289255D89593', $this->info->version ); 
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
      				$ui->append( 'text', 'siteid', 'getclicky__siteid', _t('Site ID:') );
				$ui->append( 'text', 'sitekey', 'getclicky__sitekey', _t('Site Key:') );
				$ui->append( 'text', 'sitedb', 'getclicky__sitedb', _t('Site DB:') );
				$ui->append( 'checkbox', 'loggedin', 'getclicky__loggedin', _t('Don\'t track this user?:') );
				$ui->append( 'text', 'cachetime', 'getclicky__cachetime', _t('Cache Dashboard statistics for (seconds):') );
				$ui->append('submit', 'save', _t( 'Save' ) );
                    $ui->set_option('success_message', _t('GetClicky Settings Saved'));
      				$ui->out();
      			break;
    		}
  		}
	}
	
	public function filter_dash_modules( $modules )
    {
    	$modules[]= 'GetClicky';
        $this->add_template( 'dash_getclicky', dirname( __FILE__ ) . '/dash_getclicky.php' );
        return $modules;
    }

    public function filter_dash_module_getclicky( $module, $module_id, $theme )
    {
    	$siteid = Options::get('getclicky__siteid');
        $sitekey = Options::get('getclicky__sitekey');
        $cachetime = Options::get('getclicky__cachetime');
    	
	$theme->site_rank = $this->fetchSingleStat('site-rank', $siteid, $sitekey, $cachetime);
        $theme->current_visitors = $this->fetchSingleStat('visitors-online', $siteid, $sitekey, $cachetime);
	$theme->unique_visitors = $this->fetchSingleStat('visitors-unique', $siteid, $sitekey, $cachetime);
	$theme->todays_actions = $this->fetchSingleStat('actions', $siteid, $sitekey, $cachetime);
	$theme->actions_average = $this->fetchSingleStat('actions-average', $siteid, $sitekey, $cachetime);
	$theme->time_total = $this->fetchSingleStat('time-total-pretty', $siteid, $sitekey, $cachetime);
	$theme->time_average = $this->fetchSingleStat('time-average-pretty', $siteid, $sitekey, $cachetime);
	$theme->siteid = $siteid;

		$module['content']= $theme->fetch( 'dash_getclicky' );
		return $module;
    }

	function theme_footer()
	{
		if ( URL::get_matched_rule()->entire_match == 'user/login') {
			// Login page; don't dipslay
			return;
		}
		if ( User::identify() ) {
			// Only track the logged in user if we were told to
			if ( !Options::get('getclicky__loggedin') ) {
				return;
			}
		}
		$siteid = Options::get('getclicky__siteid');
		$sitedb = Options::get('getclicky__sitedb');
		echo <<<ENDAD
<a title="Clicky Web Analytics" href="http://getclicky.com/{$siteid}">
	<img alt="Clicky Web Analytics" src="http://static.getclicky.com/media/links/badge.gif" border="0" />
</a>
<script src="http://static.getclicky.com/{$siteid}.js" type="text/javascript"></script>
<noscript>
	<p>
		<img alt="Clicky" src="http://static.getclicky.com/{$siteid}-{$sitedb}.gif" />
	</p>
</noscript>
ENDAD;
	}
	
	function fetchSingleStat($type, $siteid, $sitekey, $cachetime) {
		
		$value = "N/A";
		
		if ( Cache::has( 'feedburner_stat_'.$type ) ) {
			$value = Cache::get( 'feedburner_stat_'.$type );
		} else {
			$url = 'http://api.getclicky.com/stats/api3?site_id='.$siteid.'&sitekey='.$sitekey.'&type='.$type.'&output=json';
			$request = new RemoteRequest($url);
			if (!$request->execute()) {
				throw new XMLRPCException( 16 );
			}
			$data = json_decode($request->get_response_body());
			if (isset($data[0]->dates[0]->items[0]->value)) {
				$value = $data[0]->dates[0]->items[0]->value;	
			}
			Cache::set( 'feedburner_stat_'.$type, $value, $cachetime );
		}	
		return $value;
	}
}
?>
