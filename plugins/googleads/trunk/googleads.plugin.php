<?php

class GoogleAds extends Plugin 
{
	/**
	 * When the plugin is initialized, register the block templates and set up supporting data.
	 */
	public function action_init()
	{
		$this->add_template( "block.googlead", dirname( __FILE__ ) . "/block.googlead.php" );
	}

	private function get_code()
	{
		$code = <<<ENDAD
<script type="text/javascript"><!--
google_ad_client = "CLIENTCODE";
google_ad_slot = "ADSLOT";
google_ad_width = ADWIDTH;
google_ad_height = ADHEIGHT;
//--></script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
ENDAD;
		return $code;
	}

	/**
	 * Ad Block
	 *
	 * Handle google ad block output
	 *
	 * @param Block $block The block instance to be configured
	 * @param Theme $theme The active theme
	 */
	public function action_block_content_googlead( $block, $theme )
	{
		$code = $this->get_code();
		$replace = array(
			'CLIENTCODE'	=> $block->clientcode,
			'ADSLOT'	=> $block->adslot,
			'ADWIDTH' 	=> $block->adwidth,
			'ADHEIGHT'	=> $block->adheight );
		$code = str_replace( array_keys( $replace ), array_values( $replace ), $code );

		$block->code = $code;
	}
	/**
	 * Allow configuration of the of the configuration setting for this ad block
	 *
	 * @param FormUI $form The configuration form for this block
	 * @param Block $block The block instance to be configured
	 */
	public function action_block_form_googlead( $form, $block )
	{
		$form->append( 'text', 'clientcode', $block, 'Ad Client Code: ' );
		$form->append( 'text', 'adslot',     $block,     'Ad Slot ID: ' );
		$form->append( 'text', 'adwidth',    $block,    'Ad Width: ');
		$form->append( 'text', 'adheight',   $block,   'Ad Height: ');
		$form->append( 'submit', 'save', _t( 'Save' ) );
	}

	/**
	 * Add available blocks to the list of possible block types.
	 *
	 * @param array $block_list an Associative array of the internal names and display names of blocks
	 *
	 * @return array The modified $block_list array
	 */
	public function filter_block_list( $block_list )
	{
		$block_list['googlead'] = _t( 'Google Ad' );
		return $block_list;
	}

	// This section is legacy
	public function configure()
	{
		$ui = new FormUI( strtolower( get_class( $this ) ) );
		$clientcode = $ui->append( 'text', 'clientcode', 'googleads__clientcode', _t( 'Ad Client Code' ) );
		$adslot = $ui->append( 'text', 'adslot', 'googleads__adslot', _t( 'Ad Slot ID' ) );
		$adwidth = $ui->append( 'text', 'adwidth', 'googleads__adwidth', _t( 'Ad Width' ) );
		$adheight = $ui->append( 'text', 'adheight', 'googleads__adheight', _t( 'Ad Height' ) );

		$ui->append( 'submit', 'save', _t('Save') );
		$ui->out();
	}

	private static function getvar( $var )
	{
		return Options::get( 'googleads__' . $var );
	}
	function action_theme_sidebar_bottom()
	{
		$code = $this->get_code();
		$replace = array(
			'CLIENTCODE'	=> self::getvar( 'clientcode' ),
			'ADSLOT'	=> self::getvar( 'adslot' ),
			'ADWIDTH' 	=> self::getvar( 'adwidth' ),
			'ADHEIGHT'	=> self::getvar( 'adheight' ) );
		$code = str_replace( array_keys( $replace ), array_values( $replace ), $code );
		echo $code;
	}
	// End legacy section
}
?>
