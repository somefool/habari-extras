<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2009
 */

class RSSBlock extends Plugin
{

	/**
	 * Required plugin information
	 * @return array The array of information
	 **/
	public function info()
	{
		return array(
			'name' => 'RSS Block',
			'version' => '1.0',
			'url' => 'http://redalt.com/plugins',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net/',
			'license' => 'Apache License 2.0',
			'description' => _t('Links from an RSS feed as a block'),
		);
	}

	public function filter_block_list($block_list)
	{
		$block_list['rssblock'] = _t('RSS Block');
		return $block_list;
	}

	public function action_block_content_rssblock($block)
	{
		$cachename = array('rssblock', md5($block->feed_url));
		if(Cache::has($cachename)) {
			$feed = Cache::get($cachename);
		}
		else {
			$feed = RemoteRequest::get_contents($block->feed_url);
			Cache::set($cachename, $feed);
		}
		
		$xml = new SimpleXMLElement($feed);
		$dns = $xml->getDocNamespaces();
		
		$items = array();
		$itemcount = 0;
		foreach($xml->channel->item as $xitem) {
			$item = new StdClass();
			
			foreach($xitem->children() as $child) {
				$item->{$child->getName()} = (string) $child;
			}

			foreach($dns as $ns => $nsurl) {
				foreach($xitem->children($nsurl) as $child) {
					$item->{$ns . '__' . $child->getName()} = (string) $child;
					foreach($child->attributes() as $name => $value) {
						$item->{$ns . '__' . $child->getName() . '__' . $name} = (string) $value;
					}
				}
			}

			$items[] = $item;
			$itemcount++;
			if($block->item_limit > 0 && $itemcount >= $block->item_limit) {
				break;
			}
		}
		
		$block->items = $items;
		
		$block->markup_id = Utils::slugify($block->title);
	}
	
	public function action_init()
	{
		$this->add_template('block.rssblock', dirname(__FILE__) . '/block.rssblock.php');
	}
	
}

?>