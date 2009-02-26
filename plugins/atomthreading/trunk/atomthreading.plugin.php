<?php

/**
 * AtomThreading Class
 *
 **/

class AtomThreading extends Plugin
{
	private $class_name = '';

	public function info()
	{
		return array(
			'name' => 'Atom Threading Extensions',
			'version' => '0.2',
			'url' => 'http://code.google.com/p/bcse/wiki/AtomThreadingExtensions',
			'author' => 'Joel Lee',
			'authorurl' => 'http://blog.bcse.info/',
			'license' => 'Apache License 2.0',
			'description' => _t('Implement Atom threading extensions (RFC4685) on Habari. In other words, this plugin allows you to add Comments Count !FeedFlare™ to your feed.', $this->class_name)
		);
	}

	/**
	 * On plugin activation, set the default options
	 */
	public function action_plugin_activation($file)
	{
		if (realpath($file) === __FILE__) {
			$this->class_name = strtolower(get_class($this));
		}
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init()
	{
		$this->class_name = strtolower(get_class($this));
		$this->load_text_domain($this->class_name);
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
			$link->addAttribute('thr:updated', HabariDateTime::date_create(end($post->comments->approved)->date)->get(HabariDateTime::ATOM), 'http://purl.org/syndication/thread/1.0');
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