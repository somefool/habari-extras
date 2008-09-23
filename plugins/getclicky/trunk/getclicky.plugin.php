<?php
class GetClicky extends Plugin
{
	function info()
	{
    		return array(
      			'name' => 'GetClicky Analytics',
      			'version' => '1.0',
      			'url' => 'http://digitalspaghetti.me.uk/',
      			'author' => 'Tane Piper',
      			'authorurl' => 'http://digitalapghetti.me.uk',
      			'license' => 'MIT Licence',
      			'description' => 'Add\'s GetClicky analytics integration to your site',
    		);
	}

	/* Admin Options*/
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
      					$ui->append( 'text', 'siteid', 'getclicky__siteid', _t('SiteID:') );
					$ui->append( 'text', 'sitedb', 'getclicky__sitedb', _t('SiteDB:') );
					$ui->append( 'checkbox', 'loggedin', 'getclicky__loggedin', _t('Don\'t track this user?:') );
					$ui->append('submit', 'save', _t( 'Save' ) );
                                        $ui->set_option('success_message', _t('GetClicky Settings Saved'));
      					$ui->out();
      				break;
    			}
  		}
	}

	function action_update_check() 
	{
		Update::add( 'GetClicky Analytics', '5F271634-89B7-11DD-BE47-289255D89593', $this->info->version ); 
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

}
?>
