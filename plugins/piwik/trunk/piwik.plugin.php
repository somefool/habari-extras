<?php
class Piwik extends Plugin {
	public function info()
	{
		return array(
			'url' => 'http://trac.habariproject.org/habari-extras/browser/plugins/piwik',
			'name' => 'Piwik',
			'description'   => 'Automatically adds Piwik tracking code to the theme footer.',
			'license' => 'Apache License 2.0',
			'author' => 'Andy Cowling',
			'authorurl' => 'http://nbrightside.com/blog/',
			'version' => '0.1'
		);
	}

	public function filter_plugin_config($actions, $plugin_id)
	{
		if ( $this->plugin_id() == $plugin_id ) {
			$actions[]= _t('Configure');
		}
		return $actions;
	}

	public function action_plugin_ui($plugin_id, $action)
	{
		if ( $this->plugin_id() == $plugin_id && $action == _t('Configure')){
		    $form = new FormUI(strtolower(get_class($this)));
		    $form->append('text', 'siteurl', 'option:piwik__siteurl', _t('Piwik site URL'));
		    $form->append('text', 'sitenum', 'option:piwik__sitenum', _t('Piwik site number'));
		    $form->append('checkbox', 'trackloggedin', 'option:piwik__trackloggedin', _t( 'Track logged-in users', 'piwik' ));
		    $form->append('submit', 'save', 'Save');
		    $form->on_success( array( $this, 'save_config' ) );
		    $form->out();
		}
	}

	/**
	 * Invoked when the before the plugin configurations are saved
	 *
	 * @param FormUI $form The configuration form being saved
	 * @return true
	 */
	public function save_config( $form )
	{
		$form->save();
		Session::notice('Piwik plugin configuration saved');
		return false;
	}

        public function action_plugin_deactivation( $file )
	{
	        if ( realpath( $file ) == __FILE__ ) {
		    Options::delete('piwik__siteurl');
		    Options::delete('piwik__sitenum');
		    Options::delete('piwik__trackloggedin');
		
		    Modules::remove_by_name( 'Piwik' );
		}
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Piwik', 'xxx', $this->info->version );
	}

	public function action_plugin_activation($file)
	{
		if (Plugins::id_from_file($file) != Plugins::id_from_file(__FILE__)) return;

		Options::set('piwik__trackloggedin', false);
	}

        public function theme_footer($theme)
	{
		$class= strtolower( get_class( $this ) );
		$siteurl = Options::get( $class . '__siteurl');
		$ssl_siteurl = str_replace("http://", "https://", $siteurl);
		$sitenum = Options::get( $class . '__sitenum');
		$trackloggedin = Options::get( $class . '__trackloggedin');

		echo $trackloggedin;

		if ( URL::get_matched_rule()->entire_match == 'user/login') {
			// Login page; don't dipslay
			return;
		}
		if ( User::identify()->loggedin ) {
			// Only track the logged in user if we were told to
			if ( !($trackloggedin) ) {
				return;
			}
		}
		echo <<<EOD
echo $trackloggedin
<!-- Piwik -->
<a href="http://piwik.org" title="Web analytics" onclick="window.open(this.href);return(false);">
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "${ssl_siteurl}" : "${siteurl}" );
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
piwik_action_name = '';
piwik_idsite = "${sitenum}";
piwik_url = pkBaseURL + "piwik.php";
piwik_log(piwik_action_name, piwik_idsite, piwik_url);
</script>
<object><noscript><p>Web analytics <img src="${siteurl}/piwik.php?idsite=${sitenum}" style="border:0" alt=""/></p></noscript></object></a>
<!-- End Piwik Tag -->
EOD;
	}

}
?>