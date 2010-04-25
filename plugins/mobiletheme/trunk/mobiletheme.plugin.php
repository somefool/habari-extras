<?php

class MobileTheme extends Plugin 
{
	private function is_mobile() {
		// Detection script courtesy http://papermashup.com/lightweight-mobile-browser-detection/
		$mobile_browser = 0;
		
		if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$mobile_browser++;
		}
		
		if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			$mobile_browser++;
		}    
		
		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
		$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda','xda-'
		);
		
		if(in_array($mobile_ua,$mobile_agents)) {
			$mobile_browser++;
		}
		
		if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
			$mobile_browser++;
		}
		
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
			$mobile_browser=0;
		}
		
		if($mobile_browser>0) {
			return true;
		}
		else {
			return false;
		}   
	} 

	function filter_get_theme_dir($theme_dir) {
		if(isset($_SESSION['mobile_theme'])) {
			if($_SESSION['mobile_theme']) {
				return Options::get('mobiletheme');
			}
			else {
				return $theme_dir;
			}
		}
		if($this->is_mobile()) {
			return Options::get('mobiletheme');
		}
		
		return $theme_dir;
	}
	
	function theme_is_mobile() {
		return $this->is_mobile();
	}
	
	public function configure( )
	{
		$theme_data = Themes::get_all_data();
		$themes = array();
		foreach($theme_data as $key => $theme) {
			$themes[$key] = (string)$theme['info']->name;
		}
	
		$ui = new FormUI( strtolower( get_class( $this ) ) );
		$ui->append( 'select', 'mobiletheme', 'mobiletheme', _t('Select the theme to use for detected mobile browsers:') );
		$ui->mobiletheme->options = $themes;

		$ui->append( 'submit', 'save', _t('Save') );
		return $ui;
	}
	
	public function filter_block_list($block_list)
	{
		$block_list['mobiletoggle'] = _t('Mobile Toggle');
		return $block_list;
	}
	
	public function action_block_content_mobiletoggle($block, $theme)
	{
		$block->mobile = $this->is_mobile();
		$block->mobile_on = Url::get('mobile_on'); 
		$block->mobile_off = Url::get('mobile_off'); 
	}
	
	public function action_block_form_mobiletoggle($form, $block)
	{
		$form->append('static', 'There are no settings for this form.');
	}
	
	public function action_init()
	{
		$this->add_rule('"mobile"/"on"', 'mobile_on');
		$this->add_rule('"mobile"/"off"', 'mobile_off');
		$this->add_template('block.mobiletoggle', dirname(__FILE__) . '/block.mobiletoggle.php');
	}
	
	public function action_plugin_act_mobile_on()
	{
		$_SESSION['mobile_theme'] = true;
		Utils::redirect(Site::get_url('habari'));
	}
	
	public function action_plugin_act_mobile_off()
	{
		$_SESSION['mobile_theme'] = false;
		Utils::redirect(Site::get_url('habari'));
	}
	
}

?>
