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
	
	public function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			CronTab::add_hourly_cron('update_rrs_blocks', 'rssblocks_update');
		}
	}
	
	public function filter_rssblocks_update($success)
	{
		EventLog::log('Running rrsblocks update');

		$blocks = DB::get_results('SELECT b.* FROM {blocks} b WHERE b.type = ?', array('rssblock'), 'Block');
		Plugins::act('get_blocks', $blocks);

		foreach($blocks as $block) {
			$cachename = array('rssblock', md5($block->feed_url));
			if(Cache::expired($cachename)) {
				$r = new RemoteRequest( $block->feed_url );
				$r->set_timeout( 10 );
				$r->execute();
				$feed = $r->get_response_body();
				if(is_string($feed)) {
					Cache::set($cachename, $feed, 30, true);
				}
			}
		}
		
		Session::notice('ran rssblocks update');
		
		return $success;
	}

	public function filter_block_list($block_list)
	{
		$block_list['rssblock'] = _t('RSS Block');
		return $block_list;
	}
	
	public function action_block_content_rssblock($block)
	{
		$items = array();
		$cachename = array('rssblock', md5($block->feed_url));
		if(Cache::expired($cachename)) {
			CronTab::add_single_cron('single_update_rrs_blocks', 'rrsblocks_update', HabariDateTime::date_create());
		}	
		$feed = Cache::get($cachename);
		$xml = new SimpleXMLElement($feed);
		$dns = $xml->getDocNamespaces();
		
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