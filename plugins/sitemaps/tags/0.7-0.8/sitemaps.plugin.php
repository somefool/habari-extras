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

	/**
     * Sitemap function called by the self `act` function.
     * Generates the `sitemap.xml.gz` file to output.
     */
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
			$types = Options::get_group( __CLASS__ );
		    //..or generate a new one
			$xml = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="'.$this->get_url() .'/sitemap.xsl"?><urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
		    $xml = new SimpleXMLElement( $xml );

			if ( $types['any'] || empty( $types ) ) {
				// Retrieve all published content, regardless of the type
				$content['any'] = Posts::get( array( 'content_type' => 'any', 'status' => 'published', 'nolimit' => 1 ) );
			} else {
				// Retreive all published content for select content types
				$content['posts'] = Posts::get( array( 'content_type' => array_keys( $types, 1 ), 'status' => 'published', 'nolimit' => 1 ) );
			}
		    
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
	 * Add the configure option for the plugin
	 */
	public function filter_plugin_config( $actions )
    {
		$actions['configure'] = _t( 'Configure' );
        return $actions;
    }
	
	/**
	 * The configure form
	 */
	public function action_plugin_ui_configure()
	{
		$ui = new FormUI( strtolower( __CLASS__ ) );
		$ui->append( 'static', 'explanation', _t( 'Select which content types you would like to include in the sitemap.  By default, all public published content types are included.' ));
		// Get all content types
		$content_types = Post::list_active_post_types();
		// Display a checkbox for all content type
		foreach( array_keys( $content_types ) as $content_type ) {
			$opt = $ui->append( 'checkbox', 'include_'.$content_type, __CLASS__ . '__' . $content_type, _t( $content_type ) );
			if ( $content_type != "any" ) {
				$opt->class = "formcontrol sitexml";
			}
		}
		$ui->append( 'submit', 'save', _t( 'Save' ) );
		// Expire the cache when we save options
		$ui->on_success( Cache::expire( 'sitemap' ) );
		$ui->set_option( 'success_message', _t( 'Options successfully saved.' ) );
		$ui->out();
	}
	
	/**
	 * A bit of javascript for the configure page to enhance the functionality
	 */
	public function action_admin_footer( $theme )
    {        		
		if ( Controller::get_var( 'configure' ) == $this->plugin_id ) {
			echo <<< SITEXML
<script type="text/javascript">
	if ( $('#include_any input:checkbox').is(':checked') ) { 
		$('.sitexml input').attr('disabled', 'disabled');
	} 

	$('#include_any input:checkbox').change(function(){
		if ( $('#include_any input:checkbox').is(':checked') ) {
			$('.sitexml input').attr('disabled', 'disabled');
		} else {
			$('.sitexml input').removeAttr('disabled');
		}
	});
</script>
SITEXML;
		}
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
