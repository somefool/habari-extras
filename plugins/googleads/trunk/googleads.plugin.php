<?php
class GoogleAds extends Plugin 
{
	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'GoogleAds', 'ad99b200-3ba0-11dd-ae16-0800200c9a66', $this->info->version );
	}
	
	public function configure()
	{
		$form = new FormUI( 'googleads' );
		$form->append( 'text', 'clientcode', 'option:googleads__clientcode', 'Ad Client Code: ' );
		$form->append( 'text', 'adslot',     'option:googleads__adslot',     'Ad Slot ID: ' );
		$form->append( 'text', 'adwidth',    'option:googleads__adwidth',    'Ad Width: ');
		$form->append( 'text', 'adheight',   'option:googleads__adheight',   'Ad Height: ');

		$form->append( 'submit', 'save', 'Save' );
		return $form;
	}

	private static function getvar( $var )
	{
		return Options::get( 'googleads__' . $var );
	}

	function action_theme_sidebar_bottom() 
	{
		$code = <<<ENDAD
<div class="sb-adsense">
<h2>Advertising</h2>
<p><script type="text/javascript"><!--
google_ad_client = "CLIENTCODE";
google_ad_slot = "ADSLOT";
google_ad_width = ADWIDTH;
google_ad_height = ADHEIGHT;
//--></script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script></p>
</div>
ENDAD;
		$replace = array(
			'CLIENTCODE'	=> self::getvar( 'clientcode' ),
			'ADSLOT'	=> self::getvar( 'adslot' ),
			'ADWIDTH' 	=> self::getvar( 'adwidth' ),
			'ADHEIGHT'	=> self::getvar( 'adheight' ) );
		$code = str_replace( array_keys( $replace ), array_values( $replace ), $code );
		echo $code;
	}
}
?>
