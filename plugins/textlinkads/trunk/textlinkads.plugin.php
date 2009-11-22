<?php

/** 
 * 
 *
 */
class textlinkads extends Plugin
{
	public function filter_block_list($block_list)
	{
		$block_list['textlinkads'] = _t('Text Link Ads');
		return $block_list;
	}

	public function action_block_content_textlinkads($block)
	{
		if(!Cache::has('textlinkads')) {
			$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
			$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
			$inventory_key = 'NYWSW7HTCIN307VV7BBD';
			
			$tla_uri = 'http://www.text-link-ads.com/xml.php?inventory_key=' . $inventory_key . '&referer=' . urlencode($request_uri) .  '&user_agent=' . urlencode($user_agent);
			Cache::set('textlinkads', RemoteRequest::get_contents($tla_uri));
			Utils::debug('Cache set');
		}
		
		$xml = new SimpleXMLElement(Cache::get('textlinkads'));
		
		$links = array();
		foreach($xml->Link as $link) {
			$ad = new StdClass();
			$ad->before = (string) $link->BeforeText;
			$ad->after = (string) $link->AfterText;
			$ad->text = (string) $link->Text;
			$ad->url = (string) $link->URL;
			
			$links[(string) $link->LinkID] = $ad;
		}
		$block->links = $links;
	}	
	
	public function action_init()
	{
		$this->add_template('block.textlinkads', dirname(__FILE__) . '/block.textlinkads.php');
	}
}

?>
