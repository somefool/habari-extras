<?php
class AccountManager extends Plugin {

	/**
	 * Filter function called by the plugin hook `rewrite_rules`
	 * Add a new rewrite rule to the database's rules.
	 *
	 * Call `AccountManager::act('host-meta')` when a request for the host-meta `/.well-known/host-meta` is received.
	 * Call `AccountManager::act('amcd')` when a request for the Account Manager Control Document `/amcd` is received.
	 *
	 * @param array $db_rules Array of rewrite rules compiled so far
	 * @return array Modified rewrite rules array, we added our custom rewrite rule
	 */
	public function filter_rewrite_rules( $db_rules )
	{
		$db_rules[]= RewriteRule::create_url_rule( '".well-known/host-meta"', 'AccountManager', 'host-meta' );
		$db_rules[]= RewriteRule::create_url_rule( '"amcd"', 'AccountManager', 'amcd' );
		return $db_rules;
	}

	/**
	 *
	 */
	public function theme_header( $theme )
	{
		$this->theme = $theme;
		// "X-Account-Management-Status: active; name='" . User::identify()->username . "'";
		return $this->theme;	
	}

	/**
	 * Act function called by the `Controller` class.
	 * Dispatches the request to the proper action handling function.
	 *
	 * @param string $action Action called by request, we only support 'amcd' and 'host-meta'
	 */
	public function act( $action )
	{
		switch ( $action )
		{
			case 'amcd':
				self::amcd();
				break;
			case 'host-meta':
				self::hostmeta();
				break;
		}
	}
	
	/**
	 */
	public function amcd()
	{
		// return cached hostmeta if it exists
		if ( Cache::has( 'amcd' ) ){
			$xml = Cache::get( 'amcd' );
		}
		else {
			//..or generate a new one
			$xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
	
			$xml = new SimpleXMLElement( $xml );

			/* create the XML */

			$xml = $xml->asXML();
			Cache::set( 'amcd', $xml );
		}
		
		/* Clean the output buffer, so we can output from the header/scratch. */
		ob_clean();
		header( 'Content-Type: application/xml' );
		print $xml;
	}
	
	/**
	 */
	public function hostmeta()
	{
		// return cached hostmeta if it exists
		if ( Cache::has( 'host-meta' ) ){
			$xml = Cache::get( 'host-meta' );
		}
		else {
			//..or generate a new one
			$xml =<<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">
	<Link rel='http://services.mozilla.com/amcd/0.1' href="{Site::url_get( 'habari')}"/>
</XRD>
EOD;
			Cache::set( 'host-meta', $xml );
		}
		
		/* Clean the output buffer, so we can output from the header/scratch. */
		ob_clean();
		header( 'Content-Type: application/xml' );
		print $xml;
	}
	
	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'AccountManager', '7b0c466c-16fe-4668-8366-50af0ba0dc5a', $this->info->version );
	}
}
?>
