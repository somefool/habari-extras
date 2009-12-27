<?php

/**
 * @todo generate nice report page in admin
 * @todo add email reports
 * @todo add cron to run DB optimization
 * @todo implement/include session manager code to keep session table clean
 * @todo check links in comments
 * @todo check for chosen type (html,xhtml,etc.) validation for post content_out
 * @todo lot's more
 */

class SiteMaintenance extends Plugin
{
	const CHECK_LINKS_CRON = 'sitemaintenance_checklinks';
	const TEXT_DOMAIN = 'sitemaintenance';
	
	public function action_init()
	{
		spl_autoload_register( array( __CLASS__, '_autoload') );
		
		// @todo move this to activation hook
		if ( ! CronTab::get_cronjob(self::CHECK_LINKS_CRON) ) {
			CronTab::add_weekly_cron( self::CHECK_LINKS_CRON, 'check_links_cron', _t('Check links in posts for errors', self::TEXT_DOMAIN) );
		}
	}
	
	public function filter_check_links_cron ( $result )
	{
		SiteMaintenanceLog::report_log('checking links', 'info', 'check_links', null);
		$this->check_links( Posts::get( 'content_type=' . Post::type('entry') ) );
	}
	
	public static function _autoload($class_name)
	{
		if ( strtolower( $class_name ) == 'sitemaintenancelog' ) {
			include( dirname(__FILE__) . '/sitemaintenancelog.php' );
		}
		elseif ( strtolower( $class_name ) == 'remoterequestsucks' ) {
			include( dirname(__FILE__) . '/remoterequestsucks.php' );
		}
	}
	
	/**
	 * Check if links are 404 or 302
	 */
	protected function check_links( Posts $posts )
	{
		foreach ( $posts as $post ) {
			$tokenizer = new HTMLTokenizer($post->content_out, false);
			$nodes = $tokenizer->parse();
			$urls = array();
			
			foreach ( $nodes as $node ) {
				if ( $node['type'] == HTMLTokenizer::NODE_TYPE_ELEMENT_OPEN && strtolower($node['name']) == 'a' ) {
					$urls[] = $node['attrs']['href'];
				}
			}
			$urls = array_unique($urls);
			if ( count($urls) > 0 ) {
				foreach ( $urls as $url ) {
					$request = new RemoteRequest($url, 'HEAD');
					$headers = RemoteRequestSucks::head($url);
					if ( $headers ) {
						$status = $headers['status'];
						// is it 404 not found?
						if ( $status == 404 ) {
							$message = _t("404 at %s in post %s, got: %s", array($url, $post->slug, $status), 'sitemaintenance');
							SiteMaintenanceLog::report_log($message, 'err', '404', serialize($headers));
						}
						// is it 301 moved permanently?
						elseif ( $status == 301 ) {
							if ( isset($headers['location']) ) {
								$location = $headers['location'];
							}
							else {
								$location = _t('unknown', self::TEXT_DOMAIN);
							}
							$message = _t("301 at %s in post %s, moved to: %s", array($url, $post->slug, $location), 'sitemaintenance');
							SiteMaintenanceLog::report_log($message, 'err', '301', serialize($headers));
						}
					}
				}
			}
		}
	}
}
?>
