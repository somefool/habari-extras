<?php

/**
 * AtomThreading Class
 *
 **/

class AtomThreading extends Plugin
{
	public function info()
	{
		return array(
			'name' => 'Atom Threading Extensions',
			'version' => '0.1',
			'url' => 'http://blog.bcse.info/',
			'author' => 'Joel Lee',
			'authorurl' => 'http://blog.bcse.info/',
			'license' => 'Apache License 2.0',
			'description' => 'Implement Atom threading extensions (RFC4685) on Habari. In other words, this plugin allows you to add Comments Count FeedFlare™ to your feed. '
			);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add('Atom Threading Extensions', 'a413fa7e-76cf-4edf-b7c5-53b8aa648eef', $this->info->version);
	}

	public function filter_atom_get_collection_namespaces($namespaces)
	{
		$namespaces['thr'] = 'http://purl.org/syndication/thread/1.0';
		return $namespaces;
	}

	public function action_atom_add_post($xml, $post)
	{
		$link = $xml->addChild('link');
		$link->addAttribute('rel', 'replies');
		//type="application/atom+xml" is default, could be omitted
		//$link->addAttribute('type', 'application/atom+xml');
		$link->addAttribute('href', URL::get('atom_feed_entry_comments', array('slug' => $post->slug)));
		$link->addAttribute('thr:count', $post->comments->approved->count, 'http://purl.org/syndication/thread/1.0');
		if ($post->comments->approved->count > 0)
			$link->addAttribute('thr:updated', date('c', strtotime(end($post->comments->approved)->date)), 'http://purl.org/syndication/thread/1.0');
		$xml->addChild('thr:total', $post->comments->approved->count, 'http://purl.org/syndication/thread/1.0');
	}

	public function action_atom_add_comment($xml, $comment)
	{
		$in_reply_to = $xml->addChild('thr:in-reply-to', NULL, 'http://purl.org/syndication/thread/1.0');
		$in_reply_to->addAttribute('ref', $comment->post->guid);
		$in_reply_to->addAttribute('href', $comment->post->permalink);
		$in_reply_to->addAttribute('type', 'text/html');
	}
}

?>