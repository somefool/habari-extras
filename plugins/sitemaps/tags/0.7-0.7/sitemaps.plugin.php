<?php
class Sitemaps extends Plugin {
    /**
     * Filter function called by the plugin hook `rewrite_rules`
     * Add a new rewrite rule to the database's rules.
     *
     * Call `Sitemaps::act('Sitemap')` when a request for `sitemap.xml` is received.
     *
     * @param array $db_rules Array of rewrite rules compiled so far
     * @return array Modified rewrite rules array, we added our custom rewrite rule
     */
    public function filter_rewrite_rules( $db_rules )
    {
		$db_rules[]= RewriteRule::create_url_rule( '"sitemap.xml"', 'Sitemaps', 'Sitemap' );
		if ( function_exists( 'gzencode' ) ) {
		 	$db_rules[]= RewriteRule::create_url_rule( '"sitemap.xml.gz"', 'Sitemaps', 'SitemapGz' );
		}
		return $db_rules;
    }

    /**
     * Act function called by the `Controller` class.
     * Dispatches the request to the proper action handling function.
     *
     * @param string $action Action called by request, we only support 'Sitemap'
     */
    public function act( $action )
    {
		switch ( $action )
		{
		    case 'Sitemap':
				self::Sitemap();
				break;
			case 'SitemapGz': 
			 	self::SitemapGz(); 
			 	break;
		}
    }

    /**
     * Sitemap function called by the self `act` function.
     * Generates the `sitemap.xml` file to output.
     */
    public function Sitemap()
    {
		/* Clean the output buffer, so we can output from the header/scratch. */
		ob_clean();
		header( 'Content-Type: application/xml' );
		print self::SitemapBuild();
    }

	public function SitemapGz() 
	{ 
	 	ob_clean(); 
	 	header( 'Content-Type: application/x-gzip' ); 
	 	print gzencode( self::SitemapBuild() ); 
	}
	
	public function SitemapBuild() 
	{
		//return cached sitemap if exsist
		if ( Cache::has( 'sitemap' ) ){
		    $xml = Cache::get( 'sitemap' );
		}
		else {
		    //..or generate a new one
		    $xml = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="'.$this->get_url() .'/sitemap.xsl"?><urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';

		    $xml = new SimpleXMLElement( $xml );

		    // Retreive all published posts and pages from the database
		    $content['posts']= Posts::get( array( 'content_type' => 'entry', 'status' => 'published', 'nolimit' => 1 ) );
		    $content['pages']= Posts::get( array( 'content_type' => 'page', 'status' => 'published', 'nolimit' => 1 ) );

		    // Add the index page first
		    $url = $xml->addChild( 'url' );
		    $url_loc = $url->addChild( 'loc', Site::get_url( 'habari' ) );

		    // Generate the `<url>`, `<loc>`, `<lastmod>` markup for each post and page.
		    foreach ( $content as $entries ) {
			foreach ( $entries as $entry ) {
			    $url = $xml->addChild( 'url' );
			    $url_loc = $url->addChild( 'loc', $entry->permalink );
			    $url_lastmod = $url->addChild( 'lastmod', $entry->updated->get( 'c' ) );
			}
		    }
		    $xml = $xml->asXML();
		    Cache::set( 'sitemap', $xml );
		}
		return $xml;
	}

    /**
     * post_update_status action called when the post status field is updated.
     * Expires the cached sitemap if the status has been changed.
     *
     * @param Post $post The post being updated
     * @param string $new_status the status feild new value
     */
    public function action_post_update_status( $post, $new_status )
    {
		if ( $post->status != $new_status ){
		    Cache::expire( 'sitemap' );
		}
    }
}
?>
