<?php
class OpenSearch extends Plugin {
		
	/* Required Plugin Informations */
	public function info() {
		return array(
			'name' => 'OpenSearch 1.1',
			'version' => '0.1',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Add OpenSearch 1.1 support to Habari.',
			'copyright' => '2007'
		);
	}
	
	/* Filter function called by the plugin hook `rewrite_rules`
	 * Add a new rewrite rule to the database's rules. Call `OpenSearch::act('osDescription')` when a request for `content-search.xml` is received.
	 */
	public function filter_rewrite_rules( $db_rules )
	{
		$db_rules[]= RewriteRule::create_url_rule( '"opensearch.xml"', 'OpenSearch', 'osDescription' );
		$db_rules[]= new RewriteRule( array(
			'name' => 'search',
			'parse_regex' => '%^search(?:/(?P<criteria>[^/]+))?(?:/page/(?P<page>\d+))?(?:/count/(?P<count>\d+))?/atom/?$%i',
			'build_str' => 'search(/{$criteria})(/page/{$page})/atom',
			'handler' => 'OpenSearch',
			'action' => 'search',
			'priority' => 8,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'Searches posts'
			) );

		return $db_rules;
	}
	
	/* Output the Open Search description file */
	public function osDescription() {
		$template_url= Site::get_url( 'habari' ) . '/search/{searchTerms}/page/{startPage}/count/{count}/atom';
		
		$xml= '<?xml version="1.0" encoding="UTF-8"?><OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"></OpenSearchDescription>';

		$xml= new SimpleXMLElement( $xml );
		$xml_sn= $xml->addChild( 'ShortName', Options::get('title') );
		$xml_description= $xml->addChild( 'Description', Options::get('tagline') );
		$xml_tags= '';
		$xml_contact= '';
		$xml_url= $xml->addChild( 'Url' );
		$xml_url->addAttribute( 'type', 'text/html');
		$xml_url->addAttribute( 'template', $template_url );
		
		$xml= $xml->asXML();
 
		/* Clean the output buffer, so we can output from the header/scratch. */
		ob_clean();
		header( 'Content-Type: application/opensearchdescription+xml' );
		print $xml;
	}
	
	/* Act function called by the `Controller` class.
	 * Dispatches the request to the proper action handling function.
	 */
	public function act( $action )
	{
		self::$action();
	}
	
	/* Add the link element to the header */
	public function action_template_header( $theme ) {
		$search_url= Site::get_url('habari') . '/opensearch.xml';
		$site_title= Options::get('title');
		
		echo '<link rel="search" type="application/opensearchdescription+xml" href="' . $search_url . '" title="' . $site_title . '">'."\r\n";

		if ( Controller::get_action() == 'search' ) {
			$totalResults= $theme->posts->count_all();
			$startIndex= $theme->page;
			$itemsPerPage= isset( $this->handler_vars['count'] ) ? $this->handler_vars['count'] : Options::get( 'pagination' );
			echo '<meta name="totalResults" content="' . $totalResults . '">'."\r\n";
			echo '<meta name="startIndex" content="' . $startIndex . '">'."\r\n";
			echo '<meta name="itemsPerPage" content="' . $itemsPerPage . '">'."\r\n";
		}
	}
	
	/* Add the link element to the Atom feed */
	public function search() {
		$criteria= $this->handler_vars['criteria'];
		$page= isset( $this->handler_vars['page'] ) ? $this->handler_vars['page'] : 1;
		$limit= isset( $this->handler_vars['count'] ) ? $this->handler_vars['count'] : Options::get( 'pagination' );
		
		preg_match_all( '/(?<=")(\\w[^"]*)(?=")|(\\w+)/', $criteria, $matches );
		$words= $matches[0];
		
		$where= 'status = ?';
		$params= array( Post::status( 'published' ) );
		foreach ( $words as $word ) {
			$where .= " AND (title LIKE CONCAT('%',?,'%') OR content LIKE CONCAT('%',?,'%'))";
			$params[] = $word;
			$params[] = $word;  // Not a typo
		}

		$user_filters= array( 'where' => $where, 'params' => $params, 'page' => $page, 'criteria' => $criteria, 'limit' => $limit );
		$atomhandler= new AtomHandler();
		$atomhandler->handler_vars['index']= $page;
		$atomhandler->get_collection( $user_filters );
	}
	
	public function filter_atom_get_collection_namespaces( $xml_namespaces ) {
		$xml_namespaces['opensearch']= 'http://a9.com/-/spec/opensearch/1.1/';
		return $xml_namespaces;
	}
	
	public function filter_atom_get_collection( $xml, $params ) {
		$criteria= $params['criteria'];
		
		$totalResults= Posts::get( $params )->count_all();
		$startIndex= isset( $params['page'] ) ? $params['page'] : 1;
		$itemsPerPage= isset( $this->handler_vars['count'] ) ? $this->handler_vars['count'] : Options::get( 'pagination' );
		
		$xml->addChild( 'opensearch:totalResults', $totalResults);
		$xml->addChild( 'opensearch:startIndex', $startIndex );
		$xml->addChild( 'opensearch:itemsPerPage', $itemsPerPage );
		$xml_os_query= $xml->addChild( 'opensearch:Query' );
		$xml_os_query->addAttribute( 'role', 'request' );
		$xml_os_query->addAttribute( 'searchTerms', $criteria );
		$xml_os_query->addAttribute( 'startPage', 1 );
		
		return $xml;
	}
	/*
<?xml version="1.0" encoding="UTF-8"?>
 <feed xmlns="http://www.w3.org/2005/Atom" 
       xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/">
   <title>Example.com Search: New York history</title> 
   <link href="http://example.com/New+York+history"/>
   <updated>2003-12-13T18:30:02Z</updated>
   <author> 
     <name>Example.com, Inc.</name>
   </author> 
   <id>urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6</id>
   <opensearch:totalResults>4230000</opensearch:totalResults>
   <opensearch:startIndex>21</opensearch:startIndex>
   <opensearch:itemsPerPage>10</opensearch:itemsPerPage>
   <opensearch:Query role="request" searchTerms="New York History" startPage="1" />
   <link rel="alternate" href="http://example.com/New+York+History?pw=3" type="text/html"/>
   <link rel="self" href="http://example.com/New+York+History?pw=3&amp;format=atom" type="application/atom+xml"/>
   <link rel="first" href="http://example.com/New+York+History?pw=1&amp;format=atom" type="application/atom+xml"/>
   <link rel="previous" href="http://example.com/New+York+History?pw=2&amp;format=atom" type="application/atom+xml"/>
   <link rel="next" href="http://example.com/New+York+History?pw=4&amp;format=atom" type="application/atom+xml"/>
   <link rel="last" href="http://example.com/New+York+History?pw=42299&amp;format=atom" type="application/atom+xml"/>
   <link rel="search" type="application/opensearchdescription+xml" href="http://example.com/opensearchdescription.xml"/>
   <entry>
     <title>New York History</title>
     <link href="http://www.columbia.edu/cu/lweb/eguids/amerihist/nyc.html"/>
     <id>urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a</id>
     <updated>2003-12-13T18:30:02Z</updated>
     <content type="text">
       ... Harlem.NYC - A virtual tour and information on 
       businesses ...  with historic photos of Columbia's own New York 
       neighborhood ... Internet Resources for the City's History. ...
     </content>
   </entry>
 </feed> */

}
?>
