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
	 * @param array $db_rules The array of existing rules to be filtered
	 * @return array The extended rule list
	 */	 	 	 	
	public function filter_rewrite_rules( $db_rules )
	{
		//' . Options::get( 'RSS:entries_feed' ) . '
		$db_rules[]= RewriteRule::create_url_rule( '"feed"/"rss"', 'UserThemeHandler', 'rss_feed' );
		//' . Options::get( 'RSS:comments_feed' ) . '
		$db_rules[]= RewriteRule::create_url_rule( '"feed"/"rss"/"comments"', 'UserThemeHandler', 'rss_comments' );
		return $db_rules;
	}
	
	/**
	 * Creates a basic RSS-format XML structure with channel and items elements
	 * @return SimpleXMLElement The requested RSS document
	 */	 	 	
	public function create_rss_wrapper()
	{
		$xml= new SimpleXMLElement( '<?xml version="1.0"?><rrs></rrs>');
		$xml->addAttribute(  'version', '2.0' );
		$channel= $xml->addChild( 'channel' );
		$title= $channel->addChild( 'title', htmlspecialchars( Options::get('title') ) );
		$link= $channel->addChild( 'link', Site::get_url('habari') );
		if ( $tagline= Options::get( 'tagline' ) ) {
			$description= $channel->addChild( 'description', htmlspecialchars( $tagline ) );
		}
		$pudDate= $channel->addChild( 'lastBuildDate', date( DATE_RFC822, strtotime( Post::get()->pubdate ) ) );
		$generator= $channel->addChild( 'generator', 'Habari ' . Version::get_habariversion() . ' http://habariproject.org/' );
		$items= $channel->addChild( 'items' );
 
		return $xml;
	}
	
	/**
	 * Add posts as item to the items element in the provided xml structure
	 * @param SimpleXMLElement $xml The document to add to
	 * @param array $posts An array of Posts to add to the XML
	 * @return SimpleXMLElement The resultant XML with added posts
	 */	 	 	 	   	
	public function add_posts($xml, $posts)
	{
		$items = $xml->channel->items;
		foreach ( $posts as $post ) {
			$item= $items->addChild( 'item' );
			$title= $item->addChild( 'title', htmlspecialchars( $post->title ) );
			$link= $item->addChild( 'link', $post->permalink );
			$description= $item->addChild( 'description', htmlspecialchars( $post->content ) );
			$pubdate= $item->addChild ( 'pubDate', date( DATE_RFC822, strtotime( $post->pubdate ) ) );
			$guid= $item->addChild( 'guid', $post->guid );

			if ( isset( $post->info->enclosure ) ) {
				$enclosure= $item->addChild( 'enclosure');
				$enclosure->addAttribute( 'url', $post->info->enclosure['url'] );
				$enclosure->addAttribute( 'length', $post->info->enclosure['length'] );
				$enclosure->addAttribute( 'type', $post->info->enclosure['type'] );
			}
		}
		return $xml;
	}
	
	/**
	 * Add comments as item to the items element in the provided xml structure
	 * @param SimpleXMLElement $xml The document to add to
	 * @param array $comments An array of Comments to add to the XML
	 * @return SimpleXMLElement The resultant XML with added comments
	 */	 	 	 	   	
	public function add_comments($xml, $comments)
	{
		$items = $xml->channel->items;
		foreach ( $comments as $comment ) {
			$item= $items->addChild( 'item' );
			$title= $item->addChild( 'title', htmlspecialchars( sprintf( _t( '%1$s on "%2$s"' ), $comment->name, $comment->post->title ) ) );
			$link= $item->addChild( 'link', $comment->post->permalink );
			$description= $item->addChild( 'description', htmlspecialchars( $comment->content ) );
			$pubdate= $item->addChild ( 'pubDate', date( DATE_RFC822, strtotime( $comment->date ) ) );
			$guid= $item->addChild( 'guid', $comment->post->guid . '/' . $comment->id );
		}
		return $xml;
	}
 
	/**
	 * Respond to requests for the RSS feed
	 */	 
	public function action_handler_rss_feed()
	{
		$xml= $this->create_rss_wrapper();
		$xml= $this->add_posts($xml, Posts::get());
		$xml= Plugins::filter( 'rss_feed', $xml );
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
 
} 
?>
 