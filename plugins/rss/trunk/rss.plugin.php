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
			'version' => '1.1',
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
				$newrule = $rule;
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
		$xml = new SimpleXMLElement( '<?xml version="1.0"?><rss></rss>');
		$xml->addAttribute(  'version', '2.0' );
		$channel = $xml->addChild( 'channel' );
		$title = $channel->addChild( 'title', htmlspecialchars( Options::get('title') ) );
		$link = $channel->addChild( 'link', Site::get_url('habari') );
		if ( $tagline = Options::get( 'tagline' ) ) {
			$description = $channel->addChild( 'description', htmlspecialchars( $tagline ) );
		}
		$pubDate = $channel->addChild( 'lastBuildDate', date( DATE_RFC822, strtotime( Post::get()->pubdate ) ) );
		$generator = $channel->addChild( 'generator', 'Habari ' . Version::get_habariversion() . ' http://habariproject.org/' );

		Plugins::act( 'rss_create_wrapper', $xml );
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
				$item = $items->addChild( 'item' );
				$title = $item->addChild( 'title', htmlspecialchars( $post->title ) );
				$link = $item->addChild( 'link', $post->permalink );
				$description = $item->addChild( 'description', htmlspecialchars( $post->content ) );
				$pubdate = $item->addChild ( 'pubDate', date( DATE_RFC822, strtotime( $post->pubdate ) ) );
				$guid = $item->addChild( 'guid', $post->guid );
				$guid->addAttribute( 'isPermaLink', 'false' );
				Plugins::act( 'rss_add_post', $item, $post );
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
			$item = $items->addChild( 'item' );
			$title = $item->addChild( 'title', htmlspecialchars( sprintf( _t( '%1$s on "%2$s"' ), $comment->name, $comment->post->title ) ) );
			$link = $item->addChild( 'link', $comment->post->permalink );
			$description = $item->addChild( 'description', htmlspecialchars( $comment->content ) );
			$pubdate = $item->addChild ( 'pubDate', date( DATE_RFC822, strtotime( $comment->date ) ) );
			$guid = $item->addChild( 'guid', $comment->post->guid . '/' . $comment->id );
			$guid->addAttribute( 'isPermaLink', 'false' );
			Plugins::act( 'rss_add_comment', $item, $comment );
		}
		return $xml;
	}

	/**
	 * Respond to requests for the RSS feed
	 */
	public function action_handler_rss_collection()
	{
		$xml = $this->create_rss_wrapper();
		$posts = Posts::get( array( 'status' => Post::status( 'published' ) ) );
		$xml = $this->add_posts($xml, $posts );
		Plugins::act( 'rss_collection', $xml, $posts );
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
		$tag = $vars['tag'];
		$posts = Posts::get( array('tag'=>$tag) );
		$xml = $this->create_rss_wrapper();
		$xml = $this->add_posts($xml, $posts);
		Plugins::act( 'rss_collection', $xml, $posts );
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
		$slug = $vars['slug'];
		$post = array( Post::get( $slug ) );
		$xml = $this->create_rss_wrapper();
		$xml = $this->add_posts($xml, $post);
		Plugins::act( 'rss_collection', $xml, $post );
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
		$xml = $this->create_rss_wrapper();
		$xml->channel->title = htmlspecialchars( sprintf( _t ( '%s Comments' ),  Options::get( 'title' ) ) );
		$comments = Comments::get( array( 'status' => Comment::STATUS_APPROVED ) );
		$xml = $this->add_comments( $xml, $comments );
		Plugins::act( 'rss_comments', $xml, $comments );
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
		if ( isset( $vars['slug'] ) ) {
			$slug = $vars['slug'];
			$post = Post::get( $slug );
		}
		else if ( isset( $vars['id'] ) ) {
			$id = $vars['id'];
			$post = Post::get( $id );
		}

		$xml = $this->create_rss_wrapper();
		$xml->channel->title = htmlspecialchars( sprintf( _t ( 'Comments on %s' ),  $post->title ) );
		$comments = $post->comments->comments->approved;
		$xml = $this->add_comments( $xml, $comments );
		$content_type = Post::type_name( $post->content_type );
		Plugins::act( "rss_{$content_type}_comments", $xml, $comments );
		ob_clean();

		header( 'Content-Type: application/xml' );
		echo $xml->asXML();
		exit;
	}
	
	/**
	 * Returns the appropriate alternate feed based on the currently matched rewrite rule.
	 *
	 * @param mixed $return Incoming return value from other plugins
	 * @param Theme $theme The current theme object
	 * @return string Link to the appropriate alternate Atom feed
	 */
	public function theme_feed_rss_alternate( $theme )
	{
		$matched_rule = URL::get_matched_rule();
		if ( is_object( $matched_rule ) ) {
			// This is not a 404
			$rulename = $matched_rule->name;
		}
		else {
			// If this is a 404 and no rewrite rule matched the request
			$rulename = '';
		}
		switch ( $rulename ) {
			case 'display_entry':
			case 'display_page':
				return URL::get( 'rss_entry', array( 'slug' => Controller::get_var( 'slug' ) ) );
				break;
			case 'display_entries_by_tag':
				return URL::get( 'rss_feed_tag', array( 'tag' => Controller::get_var( 'tag' ) ) );
				break;
			case 'display_home':
			default:
				return URL::get( 'rss_feed', array( 'index' => '1' ) );
		}
		return '';
	}
	
	/**
	 * Returns the permalink for this post's comments RSS feed
	 * @return string The permalink of this post's comments RSS feed
	 **/
	public function filter_post_comment_feed_rss_link( $out, $post )
	{
		$content_type = Post::type_name( $post->content_type );
		return URL::get( array( "rss_feed_{$content_type}_comments" ), $post, false );
	}
}
?>
