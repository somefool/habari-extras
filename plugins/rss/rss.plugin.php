<?php
 
class RSS extends Plugin {

	/**
	 * Produce the info required by Habari for identifying this plugin
	 * @return array Array of plugin info.	 
	 */	 	
	public function info()
	{
		return array (
			'name' => 'RSS 2.0',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Provides an RSS 2.0 feed for entries and comments.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Add additional rewrite rules so that Habari can answer requests for the feed URLs
	 * @param array $db_rules The array of default system rules to be filtered
	 * @return array The extended rule list
	 */	 	 	 	
	public function filter_default_rewrite_rules( $rules )
	{
		// Can't call RewriteRules::by_name() because that'll call this function.
		foreach($rules as $rule) {
			if( strpos($rule['name'], 'atom_') === 0 ) {
				$newrule= $rule;
				$newrule['name']= str_replace('atom', 'rss', $newrule['name']);
				$newrule['parse_regex']= str_replace('atom', 'rss', $newrule['parse_regex']);
				$newrule['build_str']= str_replace('atom', 'rss', $newrule['build_str']);
				$newrule['handler']= 'UserThemeHandler';
				$newrule['action']= 'rss_' . $newrule['action']; 
				$rules[]= $newrule;
			}
		}

		return $rules;
	}
	
	/**
	 * Creates a basic RSS-format XML structure with channel and items elements
	 * @return SimpleXMLElement The requested RSS document
	 */	 	 	
	public function create_rss_wrapper()
	{
		$xml= new SimpleXMLElement( '<?xml version="1.0"?><rss></rss>');
		$xml->addAttribute(  'version', '2.0' );
		$channel= $xml->addChild( 'channel' );
		$title= $channel->addChild( 'title', htmlspecialchars( Options::get('title') ) );
		$link= $channel->addChild( 'link', Site::get_url('habari') );
		if ( $tagline= Options::get( 'tagline' ) ) {
			$description= $channel->addChild( 'description', htmlspecialchars( $tagline ) );
		}
		$pubDate= $channel->addChild( 'lastBuildDate', date( DATE_RFC822, strtotime( Post::get()->pubdate ) ) );
		$generator= $channel->addChild( 'generator', 'Habari ' . Version::get_habariversion() . ' http://habariproject.org/' );
 
		return $xml;
	}
	
	/**
	 * Add posts as items in the provided xml structure
	 * @param SimpleXMLElement $xml The document to add to
	 * @param array $posts An array of Posts to add to the XML
	 * @return SimpleXMLElement The resultant XML with added posts
	 */	 	 	 	   	
	public function add_posts($xml, $posts)
	{
		$items = $xml->channel;
		foreach ( $posts as $post ) {
			if ($post instanceof Post) {
				$item= $items->addChild( 'item' );
				$title= $item->addChild( 'title', htmlspecialchars( $post->title ) );
				$link= $item->addChild( 'link', $post->permalink );
				$description= $item->addChild( 'description', htmlspecialchars( $post->content ) );
				$pubdate= $item->addChild ( 'pubDate', date( DATE_RFC822, strtotime( $post->pubdate ) ) );
				$guid= $item->addChild( 'guid', $post->guid );
				$guid->addAttribute( 'isPermaLink', 'false' );
			}
		}
		return $xml;
	}
	
	/**
	 * Add comments as items in the provided xml structure
	 * @param SimpleXMLElement $xml The document to add to
	 * @param array $comments An array of Comments to add to the XML
	 * @return SimpleXMLElement The resultant XML with added comments
	 */	 	 	 	   	
	public function add_comments($xml, $comments)
	{
		$items = $xml->channel;
		foreach ( $comments as $comment ) {
			$item= $items->addChild( 'item' );
			$title= $item->addChild( 'title', htmlspecialchars( sprintf( _t( '%1$s on "%2$s"' ), $comment->name, $comment->post->title ) ) );
			$link= $item->addChild( 'link', $comment->post->permalink );
			$description= $item->addChild( 'description', htmlspecialchars( $comment->content ) );
			$pubdate= $item->addChild ( 'pubDate', date( DATE_RFC822, strtotime( $comment->date ) ) );
			$guid= $item->addChild( 'guid', $comment->post->guid . '/' . $comment->id );
			$guid->addAttribute( 'isPermaLink', 'false' );
		}
		return $xml;
	}
 
	/**
	 * Respond to requests for the RSS feed
	 */	 
	public function action_handler_rss_collection()
	{
		$xml= $this->create_rss_wrapper();
		$xml= $this->add_posts($xml, Posts::get( array( 'status' => Post::status( 'published' ) ) ) );
		$xml= Plugins::filter( 'rss_collection', $xml );
		ob_clean();

		header( 'Content-Type: application/xml' );
		echo $xml->asXML();
		exit;
	}

	/**
	 * Respond to requests for the RSS feed for a specific tag
	 * @param array $vars Handler variables as passed in by rewrite rules 	 
	 */	 
	public function action_handler_rss_tag_collection($vars)
	{
		$tag= $vars['tag'];
		$posts= Posts::get( array('tag'=>$tag) );
		$xml= $this->create_rss_wrapper();
		$xml= $this->add_posts($xml, $posts);
		$xml= Plugins::filter( 'rss_collection', $xml );
		ob_clean();

		header( 'Content-Type: application/xml' );
		echo $xml->asXML();
		exit;
	}

	/**
	 * Respond to requests for the RSS feed for a single entry
	 * This is a weird one, since RSS doesn't usually do this, but Atom does.	 
	 * @param array $vars Handler variables as passed in by rewrite rules 	 
	 */	 
	public function action_handler_rss_entry($vars)
	{
		$slug= $vars['slug'];
		$posts= array( Post::get( $slug ) );
		$xml= $this->create_rss_wrapper();
		$xml= $this->add_posts($xml, $posts);
		$xml= Plugins::filter( 'rss_collection', $xml );
		ob_clean();

		header( 'Content-Type: application/xml' );
		echo $xml->asXML();
		exit;
	}
 
	/**
	 * Respond to requests for the RSS comments feed
	 */	 
	public function action_handler_rss_comments()
	{
		$xml= $this->create_rss_wrapper();
		$xml->channel->title = htmlspecialchars( sprintf( _t ( '%s Comments' ),  Options::get( 'title' ) ) );
		$xml= $this->add_comments( $xml, Comments::get( array( 'status' => Comment::STATUS_APPROVED ) ) );
		$xml= Plugins::filter( 'rss_comments', $xml );
		ob_clean();

		header( 'Content-Type: application/xml' );
		echo $xml->asXML();
		exit;
	}

	/**
	 * Respond to requests for the RSS comments feed for a specific entry
	 * @param array $vars Handler variables as passed in by rewrite rules
	 */	 	
	public function action_handler_rss_entry_comments($vars)
	{
		$slug= $vars['slug'];
		$post= Post::get($slug); 
		 
		$xml= $this->create_rss_wrapper();
		$xml->channel->title = htmlspecialchars( sprintf( _t ( 'Comments on %s' ),  $post->title ) );
		$xml= $this->add_comments( $xml, $post->comments->comments->approved );
		$xml= Plugins::filter( 'rss_entry_comments', $xml );
		ob_clean();

		header( 'Content-Type: application/xml' );
		echo $xml->asXML();
		exit;
	}
} 
?>
 
